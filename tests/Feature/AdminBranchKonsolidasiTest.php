<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\BranchProduct;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class AdminBranchKonsolidasiTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $dc;

    protected Branch $store;

    protected Warehouse $dcWarehouse;

    protected Warehouse $storeWarehouse;

    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Distribution Center (DC)
        $this->dc = Branch::create([
            'code' => 'DC-LOGISTIK',
            'name' => 'Distribution Center Logistik Pusat',
            'type' => BranchType::Dc,
            'is_headquarters' => false,
            'is_active' => true,
        ]);
        $this->dcWarehouse = Warehouse::create([
            'branch_id' => $this->dc->id,
            'code' => 'WH-DC-01',
            'name' => 'Gudang Utama DC',
            'is_active' => true,
        ]);

        // 2. Buat Toko Cabang (Store)
        $this->store = Branch::create([
            'code' => 'STR-SURABAYA',
            'name' => 'Outlet Toko Surabaya',
            'type' => BranchType::Store,
            'is_headquarters' => false,
            'is_active' => true,
        ]);
        $this->storeWarehouse = Warehouse::create([
            'branch_id' => $this->store->id,
            'code' => 'WH-STR-01',
            'name' => 'Gudang Depan Toko Surabaya',
            'is_active' => true,
        ]);

        // 3. Buat Produk & Varian
        $product = Product::create([
            'code' => 'PRD-MKN-01',
            'name' => 'Roti Bakar MGK',
            'is_active' => true,
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-ROTI-01',
            'name' => 'Roti Bakar Keju Spesial',
            'cost_price' => 8000.0,
            'base_price' => 15000.0,
            'is_active' => true,
        ]);

        // 4. Setup Stok di DC & Store
        StockInventory::create([
            'warehouse_id' => $this->dcWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 200,
            'qty_reserved' => 0,
            'safety_stock' => 20,
        ]);

        StockInventory::create([
            'warehouse_id' => $this->storeWarehouse->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 5, // Di bawah ROP (10)
            'qty_reserved' => 0,
            'safety_stock' => 5,
        ]);

        // 5. Setup Lokalisasi Produk di Toko dengan ROP
        BranchProduct::create([
            'branch_id' => $this->store->id,
            'product_variant_id' => $this->variant->id,
            'bin_location' => 'Rak Makanan A-03',
            'min_stock' => 5,
            'max_stock' => 30,
            'reorder_point' => 10,
            'is_locally_active' => true,
        ]);

        // 6. Buat Transaksi Penjualan di Store
        CafeSale::create([
            'invoice_number' => 'INV-KONSOLIDASI-001',
            'branch_id' => $this->store->id,
            'customer_name' => 'Pembeli Konsolidasi',
            'gross_amount' => 30000,
            'discount_amount' => 0,
            'net_amount' => 30000,
            'amount_paid' => 30000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => Carbon::today()->setTime(10, 0, 0),
        ]);

        // 7. Setup Karyawan Toko & Buat Riwayat STO
        $employee = Employee::create([
            'branch_id' => $this->store->id,
            'code' => 'EMP-TEST-KONS',
            'name' => 'Kasir Surabaya Test',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        ShiftStockRequest::create([
            'request_number' => 'STO-TEST-001',
            'branch_id' => $this->store->id,
            'employee_id' => $employee->id,
            'item_category' => 'Makanan',
            'item_name' => 'Roti Bakar Keju Spesial',
            'qty_requested' => 25,
            'unit' => 'Pcs',
            'urgency' => 'urgent',
            'status' => 'draft',
            'notes' => 'Auto-Draft ROP dari DC',
        ]);
    }

    public function test_it_loads_konsolidasi_dashboard_html_view(): void
    {
        $response = $this->get(route('admin.cabang.konsolidasi', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Konsolidasi &amp; Monitoring Multi-Cabang', false);
        $response->assertSee('Distribution Center Logistik Pusat');
        $response->assertSee('Outlet Toko Surabaya');
        $response->assertSee('Matriks Komparasi Kinerja Penjualan');
        $response->assertSee('Radar Peringatan Kritis Reorder Point (ROP)');
        $response->assertSee('Rak Makanan A-03');
        $response->assertSee('SKU-ROTI-01');
        $response->assertSee('STO-TEST-001');
    }

    public function test_it_returns_complete_json_structure_for_konsolidasi(): void
    {
        $response = $this->getJson(route('admin.cabang.konsolidasi', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json();

        // 1. Assert Summary Structure
        $this->assertArrayHasKey('summary', $data);
        $this->assertEquals(2, $data['summary']['total_branches']);
        $this->assertEquals(1, $data['summary']['critical_rop_count']); // 1 SKU menyentuh ROP
        $this->assertEquals(30000, $data['summary']['today_sales']);
        $this->assertGreaterThan(0, $data['summary']['total_valuation']);
        $this->assertGreaterThan(0, $data['summary']['dc_valuation']);
        $this->assertGreaterThan(0, $data['summary']['store_valuation']);

        // 2. Assert Branch Matrix
        $this->assertArrayHasKey('branch_matrix', $data);
        $this->assertCount(2, $data['branch_matrix']);

        // 3. Assert Critical ROP Items
        $this->assertArrayHasKey('critical_rop_items', $data);
        $this->assertCount(1, $data['critical_rop_items']);
        $this->assertEquals(5, $data['critical_rop_items'][0]['current_stock']);
        $this->assertEquals(10, $data['critical_rop_items'][0]['reorder_point']);
        $this->assertEquals(25, $data['critical_rop_items'][0]['recommended_transfer']); // 30 - 5 = 25

        // 4. Assert STO list
        $this->assertArrayHasKey('stock_transfer_orders', $data);
        $this->assertCount(1, $data['stock_transfer_orders']);
        $this->assertEquals('STO-TEST-001', $data['stock_transfer_orders'][0]['request_number']);
    }
}
