<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasKeluarContext;
use Modules\Kas\Domain\Enums\KasAccountType;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\Kas\Domain\Services\KasKeluarService;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\Pembelian\Domain\Contexts\GoodsReceiptContext;
use Modules\Pembelian\Domain\Contexts\PurchaseOrderContext;
use Modules\Pembelian\Domain\Enums\PurchaseBillStatus;
use Modules\Pembelian\Domain\Enums\PurchaseOrderStatus;
use Modules\Pembelian\Domain\Models\PurchaseBill;
use Modules\Pembelian\Domain\Services\PurchaseOrderOrchestrator;
use Tests\TestCase;

class PembelianModuleTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Warehouse $warehouse;

    private Supplier $supplier;

    private ProductVariant $variant;

    private User $user;

    private PurchaseOrderOrchestrator $orchestrator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->branch = Branch::create([
            'code' => 'BR-PEMBELIAN-01',
            'name' => 'Cabang Gudang Pusat',
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'WH-PUSAT-01',
            'name' => 'Gudang Utama Surabaya',
            'type' => 'storage',
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUPP-001',
            'name' => 'PT Semen Indonesia Distributor',
            'payment_term_days' => 30,
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD-SMN',
            'name' => 'Semen Gresik',
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SMN-GRS-50',
            'name' => 'Semen Gresik 50kg Sak',
            'base_price' => 70_000.0,
            'cost_price' => 50_000.0,
        ]);

        $this->orchestrator = app(PurchaseOrderOrchestrator::class);
    }

    public function test_can_create_and_approve_purchase_order(): void
    {
        $context = new PurchaseOrderContext(
            supplierId: $this->supplier->id,
            branchId: $this->branch->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_ordered' => 100,
                    'unit_price' => 52_000.0,
                ],
            ]
        );

        $po = $this->orchestrator->createPurchaseOrder($context);

        $this->assertNotNull($po->id);
        $this->assertStringStartsWith('PO-', $po->po_number);
        $this->assertSame(PurchaseOrderStatus::Draft, $po->status);
        $this->assertEquals(5_200_000.0, $po->total_amount);

        // Approve PO
        $approvedPo = $this->orchestrator->approvePurchaseOrder($po, $this->user->id);
        $this->assertSame(PurchaseOrderStatus::Approved, $approvedPo->status);
    }

    public function test_goods_receipt_increments_stock_updates_hpp_and_creates_bill(): void
    {
        // Setup initial stock: 100 sak @ Rp 50.000 (total Rp 5.000.000)
        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 100,
            'safety_stock' => 20,
        ]);

        // Buat & approve PO: 100 sak @ Rp 60.000
        $po = $this->orchestrator->createPurchaseOrder(new PurchaseOrderContext(
            supplierId: $this->supplier->id,
            branchId: $this->branch->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_ordered' => 100,
                    'unit_price' => 60_000.0,
                ],
            ]
        ));
        $po = $this->orchestrator->approvePurchaseOrder($po, $this->user->id);
        $poItem = $po->items->first();

        // Terima 100 sak fisik di gudang
        $receiptContext = new GoodsReceiptContext(
            supplierId: $this->supplier->id,
            warehouseId: $this->warehouse->id,
            items: [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 100,
                    'unit_cost' => 60_000.0,
                ],
            ],
            purchaseOrderId: $po->id,
            deliveryNoteNumber: 'SJ-SUPP-12345',
            createBill: true
        );

        $receipt = $this->orchestrator->receiveGoods($receiptContext);

        $this->assertNotNull($receipt->id);
        $this->assertStringStartsWith('GR-', $receipt->receipt_number);

        // 1. Verifikasi stok bertambah: 100 + 100 = 200 sak
        $stock = StockInventory::where('warehouse_id', $this->warehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(200, $stock->qty_on_hand);

        // 2. Verifikasi Moving Average HPP:
        // ((100 * 50.000) + (100 * 60.000)) / 200 = (5jt + 6jt) / 200 = 11jt / 200 = 55.000
        $this->variant->refresh();
        $this->assertEquals(55_000.0, $this->variant->cost_price);

        // 3. Verifikasi Status PO menjadi Closed (karena 100 dari 100 sudah diterima)
        $po->refresh();
        $this->assertSame(PurchaseOrderStatus::Closed, $po->status);

        // 4. Verifikasi Tagihan Hutang (PurchaseBill AP) tercipta otomatis
        $bill = PurchaseBill::where('purchase_order_id', $po->id)->first();
        $this->assertNotNull($bill);
        $this->assertStringStartsWith('BILL-', $bill->bill_number);
        $this->assertSame(PurchaseBillStatus::Unpaid, $bill->status);
        $this->assertEquals(6_000_000.0, $bill->total_amount);
        $this->assertEquals(6_000_000.0, $bill->remaining_balance);
    }

    public function test_purchase_bill_can_be_settled_via_kas_keluar_service(): void
    {
        // Akun Bank Operasional saldo 20jt
        $kasAccount = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'BANK-AP-TEST',
            'name' => 'Bank BCA Operasional',
            'type' => KasAccountType::Bank,
            'current_balance' => 20_000_000.0,
        ]);

        // Buat tagihan hutang supplier Rp 6jt
        $bill = PurchaseBill::create([
            'bill_number' => 'BILL-TEST-001',
            'supplier_id' => $this->supplier->id,
            'status' => PurchaseBillStatus::Unpaid,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 6_000_000.0,
            'paid_amount' => 0.0,
            'remaining_balance' => 6_000_000.0,
        ]);

        // Bayar tagihan via KasKeluarService (Modul Kas)
        $kasKeluarService = app(KasKeluarService::class);
        $kasKeluarContext = new KasKeluarContext(
            kasAccountId: $kasAccount->id,
            branchId: $this->branch->id,
            paymentMethod: PaymentMethodType::BankTransfer,
            amount: 6_000_000.0,
            supplierId: $this->supplier->id,
            referenceNumber: $bill->bill_number,
            description: 'Pelunasan tagihan semen',
            lines: [
                [
                    'reference_type' => 'purchase_bills',
                    'reference_id' => $bill->id,
                    'allocation_amount' => 6_000_000.0,
                ],
            ]
        );

        $kasTx = $kasKeluarService->execute($kasKeluarContext);
        $this->assertNotNull($kasTx->id);

        // Saldo kas bank berkurang: 20jt - 6jt = 14jt
        $kasAccount->refresh();
        $this->assertEquals(14_000_000.0, $kasAccount->current_balance);

        // Pelunasan pada PurchaseBill
        $bill->recordPayment(6_000_000.0);
        $bill->refresh();
        $this->assertSame(PurchaseBillStatus::Paid, $bill->status);
        $this->assertEquals(0.0, $bill->remaining_balance);
    }

    public function test_cannot_receive_more_qty_than_ordered_in_po(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $po = $this->orchestrator->createPurchaseOrder(new PurchaseOrderContext(
            supplierId: $this->supplier->id,
            branchId: $this->branch->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_ordered' => 50,
                    'unit_price' => 50_000.0,
                ],
            ]
        ));
        $po = $this->orchestrator->approvePurchaseOrder($po, $this->user->id);
        $poItem = $po->items->first();

        // Coba terima 60 sak padahal PO hanya 50 sak
        $this->orchestrator->receiveGoods(new GoodsReceiptContext(
            supplierId: $this->supplier->id,
            warehouseId: $this->warehouse->id,
            items: [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 60,
                    'unit_cost' => 50_000.0,
                ],
            ],
            purchaseOrderId: $po->id
        ));
    }
}
