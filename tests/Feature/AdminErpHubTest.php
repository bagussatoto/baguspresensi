<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ShiftSalesSummary;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Tests\TestCase;

class AdminErpHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_erp_hub_can_be_rendered(): void
    {
        $response = $this->get(route('admin.erp'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Kendali ERP Maya Graha Kencana');
        $response->assertSee('Pusat Antrean Otorisasi &amp; Tindakan Cepat', false);
        $response->assertSee('Antrean Otorisasi');
        $response->assertSee('id="erpFloatingBadge"', false);
        $response->assertSee('id="erpSoundToggleBtn"', false);
        $response->assertSee('id="erpToastContainer"', false);
        $response->assertSee('Semua Fitur');
        $response->assertSee('id="filterBadgeAll"', false);
        $response->assertSee('id="filterBadgeAllCount"', false);
        $response->assertSee('id="filterBtnUrgent"', false);
        $response->assertSee('id="urgentBtnLabel"', false);
        $response->assertSee('id="filterBadgePos"', false);
        $response->assertSee('id="filterBadgeSupply"', false);
        $response->assertSee('id="filterBadgeHr"', false);
        $response->assertSee('Master Data &amp; Katalog Terpadu', false);
        $response->assertSee('POS Desktop Kasir &amp; Billing', false);
        $response->assertSee('Bagan Akun (Chart of Accounts / COA)');
        $response->assertSee('Persediaan, Gudang &amp; Distribusi', false);
        $response->assertSee('Sumber Daya Manusia (HRM) &amp; Presensi', false);
        $response->assertSee('Keamanan, Tata Kelola &amp; Audit Trail', false);
        $response->assertSee('id="modal-data-purge"', false);
        $response->assertSee('Bersihkan Data Simulasi');
        $response->assertSee('Pembersihan Data Simulasi Sebelum Go-Live');
    }

    public function test_admin_erp_hub_alias_route_can_be_rendered(): void
    {
        $response = $this->get('/admin/ERP');
        $response->assertStatus(200);

        $responseLower = $this->get('/admin/erp');
        $responseLower->assertStatus(200);

        $responseBagus = $this->get('/bagus/admin/ERP');
        $responseBagus->assertStatus(200);
    }

    public function test_admin_erp_hub_can_fetch_json_metrics(): void
    {
        $response = $this->getJson(route('admin.erp'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'total_sku',
                'active_employees',
                'today_sales_count',
                'today_sales_amount',
                'total_stock',
                'coa_accounts',
                'total_pending_approvals',
                'pending_employee_approvals',
                'pending_shift_deposits',
                'pending_stock_transfers',
                'pending_stock_requests',
                'pending_billing_adjustments',
                'pending_registrations',
                'pending_password_resets',
            ],
            'pillars' => [
                'master_data',
                'pos_sales',
                'supply_chain',
                'human_resource',
                'finance_accounting',
                'security_governance',
            ],
        ]);
    }

    public function test_admin_erp_hub_can_scan_simulation_data(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Uji',
            'code' => 'CBG-01',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Bagus Tester',
            'code' => 'EMP-TEST-01',
            'position' => 'Kasir',
            'is_active' => true,
        ]);

        // Buat data simulasi
        CafeSale::create([
            'invoice_number' => 'INV-SIMULASI-001',
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'customer_name' => 'Simulasi Pelanggan',
            'cashier_notes' => 'Simulasi: Pembelian kasir demo',
            'total_fnb' => 10000.0,
            'gross_amount' => 10000.0,
            'net_amount' => 10000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        ShiftSalesSummary::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_revenue' => 10000.0,
            'handover_notes' => 'Simulasi: Uang fisik pas',
        ]);

        $response = $this->getJson(route('admin.erp.scan-simulation'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'simulation_summary' => [
                'sales_count' => 1,
                'summaries_count' => 1,
            ],
        ]);
    }

    public function test_admin_erp_hub_can_purge_simulation_data_and_protect_master_data(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Master Utama',
            'code' => 'CBG-REAL-01',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Karyawan Resmi',
            'code' => 'EMP-REAL-01',
            'position' => 'Kasir Tetap',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'code' => 'CAT-FNB',
            'name' => 'F&B',
            'slug' => 'fnb',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create([
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $cat->id,
            'code' => 'PRD-MASTER-01',
            'name' => 'Kopi Robusta Master',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'uom_id' => $uom->id,
            'sku' => 'SKU-MASTER-01',
            'name' => 'Kopi Robusta Dingin',
            'base_price' => 15000.0,
            'is_active' => true,
        ]);

        // Buat 1 data simulasi dan 1 data normal (riil)
        $simSale = CafeSale::create([
            'invoice_number' => 'INV-SIMULASI-999',
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'customer_name' => 'Testing Pelanggan',
            'cashier_notes' => 'Simulasi: transaksi uji coba',
            'total_fnb' => 15000.0,
            'gross_amount' => 15000.0,
            'net_amount' => 15000.0,
            'payment_status' => 'paid',
        ]);

        $realSale = CafeSale::create([
            'invoice_number' => 'INV-OFFICIAL-001',
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'customer_name' => 'Pelanggan Asli Toko',
            'cashier_notes' => 'Pembayaran lunas via kasir',
            'total_fnb' => 30000.0,
            'gross_amount' => 3000.0,
            'net_amount' => 30000.0,
            'payment_status' => 'paid',
        ]);

        // Eksekusi pembersihan
        $response = $this->postJson(route('admin.erp.purge-simulation'), [
            'confirm_purge' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'purged_counts' => [
                'sales' => 1,
            ],
        ]);

        // Verifikasi data simulasi telah terhapus
        $this->assertDatabaseMissing('cafe_sales', ['id' => $simSale->id]);

        // Verifikasi data riil dan Master Data tetap utuh
        $this->assertDatabaseHas('cafe_sales', ['id' => $realSale->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id]);
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);

        // Verifikasi WORM AuditLog ISO 27001 tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'simulation_data_purge',
        ]);
    }
}
