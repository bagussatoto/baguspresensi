<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Consignment;
use Modules\MasterData\Domain\Models\ConsignmentItem;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\Penjualan\Domain\Models\SalesInvoice;
use Tests\TestCase;

class AdminAgingReceivableAndConsignmentTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Customer $customerGrosir;

    protected Customer $customerReseller;

    protected Supplier $supplier;

    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-AGING-01',
            'name' => 'Cabang Surabaya Aging Test',
            'type' => BranchType::Store,
            'is_active' => true,
        ]);

        $this->customerGrosir = Customer::create([
            'code' => 'CUST-GROSIR-01',
            'name' => 'Toko Grosir Maju Jaya',
            'customer_type' => CustomerType::GROSIR,
            'credit_limit' => 50000000,
            'current_credit_used' => 15000000,
            'payment_term_days' => 30,
            'is_active' => true,
        ]);

        $this->customerReseller = Customer::create([
            'code' => 'CUST-RESELLER-01',
            'name' => 'Mitra Reseller Waru',
            'customer_type' => CustomerType::RESELLER,
            'credit_limit' => 10000000,
            'current_credit_used' => 5000000,
            'payment_term_days' => 14,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUPP-SNACK-01',
            'name' => 'CV Camilan Nusantara',
            'payment_term_days' => 30,
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD-CSG-01',
            'name' => 'Keripik Tempe Renyah',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-TEMPE-01',
            'name' => 'Keripik Tempe 250gr',
            'cost_price' => 12000,
            'base_price' => 18000,
            'is_active' => true,
        ]);

        // Buat faktur piutang dengan rentang umur berbeda
        // 1. Lancar (Current: jatuh tempo 10 hari lagi)
        SalesInvoice::create([
            'invoice_number' => 'INV-AGING-001',
            'customer_id' => $this->customerGrosir->id,
            'branch_id' => $this->branch->id,
            'invoice_date' => Carbon::today()->subDays(10)->toDateString(),
            'due_date' => Carbon::today()->addDays(20)->toDateString(),
            'total_amount' => 5000000,
            'net_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_balance' => 5000000,
            'payment_status' => 'unpaid',
        ]);

        // 2. Jatuh tempo 45 hari lalu (Masuk bucket 31 - 60 Hari)
        SalesInvoice::create([
            'invoice_number' => 'INV-AGING-002',
            'customer_id' => $this->customerGrosir->id,
            'branch_id' => $this->branch->id,
            'invoice_date' => Carbon::today()->subDays(75)->toDateString(),
            'due_date' => Carbon::today()->subDays(45)->toDateString(),
            'total_amount' => 10000000,
            'net_amount' => 10000000,
            'paid_amount' => 0,
            'remaining_balance' => 10000000,
            'payment_status' => 'unpaid',
        ]);

        // 3. Jatuh tempo 100 hari lalu (Masuk bucket > 90 Hari / Macet)
        SalesInvoice::create([
            'invoice_number' => 'INV-AGING-003',
            'customer_id' => $this->customerReseller->id,
            'branch_id' => $this->branch->id,
            'invoice_date' => Carbon::today()->subDays(114)->toDateString(),
            'due_date' => Carbon::today()->subDays(100)->toDateString(),
            'total_amount' => 5000000,
            'net_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_balance' => 5000000,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_can_render_aging_receivable_and_consignment_dashboard(): void
    {
        $response = $this->get(route('admin.cabang.aging'));

        $response->assertOk();
        $response->assertSee('Aging Piutang Grosir &amp; Saldo Konsinyasi Cabang', false);
        $response->assertSee('Matriks Umur Piutang Usaha');
        $response->assertSee('Toko Grosir Maju Jaya');
        $response->assertSee('Mitra Reseller Waru');
    }

    public function test_aging_buckets_are_calculated_accurately(): void
    {
        $response = $this->getJson(route('admin.cabang.aging'));

        $response->assertOk();
        $data = $response->json();

        $this->assertEquals(20000000.0, (float) $data['ar_kpi']['total_receivable']);
        $this->assertEquals(5000000.0, (float) $data['ar_kpi']['current_amount']);
        $this->assertEquals(10000000.0, (float) $data['ar_kpi']['days_31_60']);
        $this->assertEquals(5000000.0, (float) $data['ar_kpi']['days_over_90']);
        $this->assertEquals(2, $data['ar_kpi']['total_customers']);
    }

    public function test_can_register_new_consignment_contract(): void
    {
        $response = $this->post(route('admin.cabang.consignments.store'), [
            'branch_id' => $this->branch->id,
            'type' => 'inward',
            'partner_name' => 'CV Camilan Nusantara',
            'supplier_id' => $this->supplier->id,
            'start_date' => now()->toDateString(),
            'due_settlement_date' => now()->addDays(30)->toDateString(),
            'notes' => 'Titipan keripik tempe 100 bungkus di toko cabang.',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'unit_price' => 12000,
                    'qty_consigned' => 100,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $consignment = Consignment::where('branch_id', $this->branch->id)->latest('id')->first();
        $this->assertNotNull($consignment);
        $this->assertEquals('inward', $consignment->type);
        $this->assertEquals(1, $consignment->total_items_count);
        $this->assertEquals(1200000.0, (float) $consignment->total_consigned_value);
        $this->assertEquals('active', $consignment->status);

        $item = $consignment->items()->first();
        $this->assertEquals(100, $item->qty_remaining);
        $this->assertEquals(1200000.0, (float) $item->subtotal_consigned);
    }

    public function test_can_settle_consignment_sold_items_and_payment(): void
    {
        // 1. Buat kontrak konsinyasi
        $consignment = Consignment::create([
            'consignment_number' => 'CSG-TEST-001',
            'branch_id' => $this->branch->id,
            'type' => 'outward',
            'partner_name' => 'Mitra Reseller Waru',
            'customer_id' => $this->customerReseller->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'total_items_count' => 1,
            'total_consigned_value' => 1800000,
            'total_sold_value' => 0,
            'total_settled_amount' => 0,
            'remaining_settlement_due' => 0,
        ]);

        $item = ConsignmentItem::create([
            'consignment_id' => $consignment->id,
            'product_variant_id' => $this->variant->id,
            'unit_price' => 18000,
            'qty_consigned' => 100,
            'qty_sold' => 0,
            'qty_returned' => 0,
            'qty_remaining' => 100,
            'subtotal_consigned' => 1800000,
            'subtotal_sold' => 0,
        ]);

        // 2. Rekonsiliasi: 40 pcs laku terjual, setoran Rp 720.000
        $response = $this->post(route('admin.cabang.consignments.settle', $consignment->id), [
            'settled_amount' => 720000,
            'notes' => 'Setoran laku 40 pcs via transfer.',
            'items' => [
                [
                    'id' => $item->id,
                    'qty_sold_now' => 40,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $consignment->refresh();
        $this->assertEquals(720000.0, (float) $consignment->total_sold_value);
        $this->assertEquals(720000.0, (float) $consignment->total_settled_amount);
        $this->assertEquals(0.0, (float) $consignment->remaining_settlement_due);

        $item->refresh();
        $this->assertEquals(40, $item->qty_sold);
        $this->assertEquals(60, $item->qty_remaining); // 100 - 40 = 60 sisa fisik
    }

    public function test_can_export_aging_and_consignment_csv(): void
    {
        // Ekspor Aging CSV
        $agingResponse = $this->get(route('admin.cabang.aging.export-ar'));
        $agingResponse->assertOk();
        $agingResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('AGING SCHEDULE PIUTANG GROSIR', $agingResponse->streamedContent());
        $this->assertStringContainsString('Toko Grosir Maju Jaya', $agingResponse->streamedContent());

        // Ekspor Konsinyasi CSV
        $consignResponse = $this->get(route('admin.cabang.consignments.export'));
        $consignResponse->assertOk();
        $consignResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('SALDO KONSINYASI MULTI-CABANG', $consignResponse->streamedContent());
    }
}
