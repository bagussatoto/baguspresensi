<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasMasukContext;
use Modules\Kas\Domain\Enums\KasAccountType;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\Kas\Domain\Services\KasMasukService;
use Modules\MasterData\Domain\Exceptions\CreditLimitExceededException;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\Penjualan\Domain\Contexts\PosCheckoutContext;
use Modules\Penjualan\Domain\Contexts\PosShiftContext;
use Modules\Penjualan\Domain\Contexts\SalesOrderContext;
use Modules\Penjualan\Domain\Enums\SalesOrderStatus;
use Modules\Penjualan\Domain\Enums\ShiftStatus;
use Modules\Penjualan\Domain\Services\PosShiftService;
use Modules\Penjualan\Domain\Services\SalesOrderOrchestrator;
use Tests\TestCase;

class PenjualanModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Warehouse $warehouse;

    private KasAccount $kasLaci;

    private Customer $customer;

    private ProductVariant $variant;

    private PosShiftService $shiftService;

    private SalesOrderOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->branch = Branch::create([
            'code' => 'BR-SLS-01',
            'name' => 'Cabang Ritel Surabaya',
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'WH-SLS-01',
            'name' => 'Gudang Toko Surabaya',
            'type' => 'storage',
        ]);

        $this->kasLaci = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'KAS-LACI-01',
            'name' => 'Laci Kasir 1',
            'type' => KasAccountType::KasirToko,
            'current_balance' => 200_000.0,
        ]);

        $this->customer = Customer::create([
            'code' => 'CUST-B2B-01',
            'name' => 'Kontraktor Mitra Usaha',
            'credit_limit' => 5_000_000.0,
            'current_credit_used' => 0.0,
            'payment_term_days' => 14,
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD-CAT-01',
            'name' => 'Cat Tembok Nippon',
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'NIP-WHITE-5KG',
            'name' => 'Nippon Paint White 5kg',
            'base_price' => 100_000.0,
            'cost_price' => 70_000.0,
        ]);

        $this->shiftService = app(PosShiftService::class);
        $this->orchestrator = app(SalesOrderOrchestrator::class);
    }

    public function test_pos_checkout_with_cash_shift_and_kas_integration(): void
    {
        // 1. Setup stok di gudang toko: 50 kaleng
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 50,
        ]);

        // 2. Buka shift kasir dengan modal Rp 200.000
        $shift = $this->shiftService->openShift(new PosShiftContext(
            branchId: $this->branch->id,
            kasAccountId: $this->kasLaci->id,
            cashierId: $this->user->id,
            openingCash: 200_000.0
        ));

        $this->assertSame(ShiftStatus::Open, $shift->status);
        $this->assertEquals(200_000.0, $shift->expected_cash);

        // 3. Transaksi POS kasir: beli 5 kaleng @ Rp 100.000 = Rp 500.000. Bayar Rp 600.000 tunai.
        $checkoutContext = new PosCheckoutContext(
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            paymentMethod: PaymentMethodType::Tunai,
            cashPaid: 600_000.0,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 5,
                    'unit_price' => 100_000.0,
                ],
            ],
            posShiftId: $shift->id,
            cashierId: $this->user->id
        );

        $order = $this->orchestrator->processPosCheckout($checkoutContext);

        $this->assertNotNull($order->id);
        $this->assertStringStartsWith('POS-', $order->order_number);
        $this->assertEquals(500_000.0, $order->net_amount);
        $this->assertEquals(100_000.0, $order->change_given);
        $this->assertNotNull($order->kas_transaction_id);

        // Verifikasi stok toko berkurang: 50 - 5 = 45 kaleng
        $stock = StockInventory::where('warehouse_id', $this->warehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(45, $stock->qty_on_hand);

        // Verifikasi saldo kas laci bertambah: 200.000 (awal) + 500.000 = 700.000
        $this->kasLaci->refresh();
        $this->assertEquals(700_000.0, $this->kasLaci->current_balance);

        // Verifikasi expected cash di shift: 200.000 + 500.000 = 700.000
        $shift->refresh();
        $this->assertEquals(700_000.0, $shift->expected_cash);
        $this->assertEquals(500_000.0, $shift->total_sales);

        // 4. Tutup shift dengan Blind Count kasir: dihitung fisik Rp 698.000 (selisih minus Rp 2.000)
        $closedShift = $this->shiftService->closeShift($shift->id, 698_000.0, 'Selisih uang kembalian permen');

        $this->assertSame(ShiftStatus::Closed, $closedShift->status);
        $this->assertEquals(698_000.0, $closedShift->actual_cash);
        $this->assertEquals(-2_000.0, $closedShift->cash_difference);
    }

    public function test_b2b_sales_order_credit_limit_and_invoice_issuance(): void
    {
        // Setup stok di gudang: 100 kaleng
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 100,
        ]);

        // Buat Sales Order B2B Kredit: 20 kaleng @ Rp 100.000 = Rp 2.000.000 (+ PPN 11% Rp 220.000 = Rp 2.220.000)
        $soContext = new SalesOrderContext(
            customerId: $this->customer->id,
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 20,
                    'unit_price' => 100_000.0,
                ],
            ],
            createdBy: $this->user->id
        );

        $so = $this->orchestrator->createSalesOrder($soContext);

        $this->assertNotNull($so->id);
        $this->assertSame(SalesOrderStatus::Draft, $so->status);
        $this->assertEquals(2_000_000.0, $so->total_amount);
        $this->assertEquals(220_000.0, $so->tax_amount);
        $this->assertEquals(2_220_000.0, $so->net_amount);

        // Konfirmasi SO
        $this->orchestrator->confirmSalesOrder($so, $this->user->id);

        // Terbitkan Faktur Tagihan Penjualan (Sales Invoice)
        $invoice = $this->orchestrator->issueInvoiceFromSalesOrder($so, $this->user->id);

        $this->assertNotNull($invoice->id);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertSame('unpaid', $invoice->payment_status);
        $this->assertEquals(2_220_000.0, $invoice->remaining_balance);

        // 1. Stok gudang terpotong: 100 - 20 = 80
        $stock = StockInventory::where('warehouse_id', $this->warehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(80, $stock->qty_on_hand);

        // 2. Plafon kredit konsumen berkurang (terpakai Rp 2.220.000)
        $this->customer->refresh();
        $this->assertEquals(2_220_000.0, $this->customer->current_credit_used);
        $this->assertEquals(2_780_000.0, $this->customer->remainingCreditLimit());

        // 3. Pelunasan piutang via Modul Kas (KasMasukService)
        $kasMasukService = app(KasMasukService::class);
        $kasMasukContext = new KasMasukContext(
            kasAccountId: $this->kasLaci->id,
            branchId: $this->branch->id,
            paymentMethod: PaymentMethodType::BankTransfer,
            amount: 2_220_000.0,
            customerId: $this->customer->id,
            referenceNumber: $invoice->invoice_number,
            description: 'Pelunasan faktur B2B',
            lines: [
                [
                    'reference_type' => 'sales_invoices',
                    'reference_id' => $invoice->id,
                    'allocation_amount' => 2_220_000.0,
                ],
            ]
        );

        $kasMasukService->execute($kasMasukContext);

        // 4. Verifikasi Plafon kredit konsumen PULIH KEMBALI
        $this->customer->refresh();
        $this->assertEquals(0.0, $this->customer->current_credit_used);
        $this->assertEquals(5_000_000.0, $this->customer->remainingCreditLimit());

        // Update status invoice
        $invoice->recordPayment(2_220_000.0);
        $invoice->refresh();
        $this->assertSame('paid', $invoice->payment_status);
        $this->assertEquals(0.0, $invoice->remaining_balance);
    }

    public function test_pos_checkout_rejects_insufficient_stock(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Stok gudang hanya 2 kaleng
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 2,
        ]);

        // Coba beli 10 kaleng
        $this->orchestrator->processPosCheckout(new PosCheckoutContext(
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            paymentMethod: PaymentMethodType::Tunai,
            cashPaid: 1_000_000.0,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 10,
                ],
            ]
        ));
    }

    public function test_b2b_sales_order_rejects_when_credit_limit_exceeded(): void
    {
        $this->expectException(CreditLimitExceededException::class);

        // Plafon kredit customer hanya Rp 500.000
        $this->customer->update(['credit_limit' => 500_000.0]);

        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 100,
        ]);

        // Coba order senilai Rp 2.000.000 (melebihi limit)
        $this->orchestrator->createSalesOrder(new SalesOrderContext(
            customerId: $this->customer->id,
            branchId: $this->branch->id,
            warehouseId: $this->warehouse->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 20,
                    'unit_price' => 100_000.0,
                ],
            ]
        ));
    }

    public function test_pos_and_sales_order_api_endpoints(): void
    {
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 50,
        ]);

        // 1. Open shift via API
        $openShiftResponse = $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.open'), [
            'branch_id' => $this->branch->id,
            'kas_account_id' => $this->kasLaci->id,
            'opening_cash' => 150_000.0,
        ]);
        $openShiftResponse->assertStatus(201);
        $shiftId = $openShiftResponse->json('data.id');

        // 2. POS checkout via API
        $checkoutResponse = $this->actingAs($this->user)->postJson(route('penjualan.pos.checkout'), [
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'pos_shift_id' => $shiftId,
            'payment_method' => PaymentMethodType::Tunai->value,
            'cash_paid' => 200_000.0,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 2,
                    'unit_price' => 100_000.0,
                ],
            ],
        ]);
        $checkoutResponse->assertStatus(201);

        // 3. Close shift via API
        $closeResponse = $this->actingAs($this->user)->postJson(route('penjualan.pos.shifts.close', $shiftId), [
            'actual_cash' => 350_000.0,
        ]);
        $closeResponse->assertStatus(200);

        // 4. Create B2B Sales Order via API
        $soResponse = $this->actingAs($this->user)->postJson(route('penjualan.so.store'), [
            'customer_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 5,
                    'unit_price' => 100_000.0,
                ],
            ],
        ]);
        $soResponse->assertStatus(201);
        $soId = $soResponse->json('data.id');

        // 5. Confirm SO via API
        $this->actingAs($this->user)->postJson(route('penjualan.so.confirm', $soId))
            ->assertStatus(200);

        // 6. Issue Invoice via API
        $this->actingAs($this->user)->postJson(route('penjualan.so.issue-invoice', $soId))
            ->assertStatus(201);
    }
}
