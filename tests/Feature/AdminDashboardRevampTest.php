<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\ActivityAttendance;
use App\Domains\Employee\Models\Shift;
use App\Models\PrinterPrintJob;
use App\Models\ShiftStockRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class AdminDashboardRevampTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Branch $branch;

    private Shift $shift;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Kencana Pusat',
            'code' => 'MGK-PST',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin MayaNet Jogja',
            'email' => 'admin@mayanet.com',
            'password' => bcrypt('maya123'),
            'role' => 'super_admin',
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift 1 (Dini Hari)',
            'code' => 'SHIFT-1',
            'start_time' => '01:00:00',
            'end_time' => '07:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus Operator',
            'code' => 'EMP-001',
            'position' => 'Operator',
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_can_be_accessed_and_contains_all_5_required_sections(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.index'));

        $response->assertStatus(200);

        // 1. Overview contains links / cards to all sections
        $response->assertSee('1. Data Terpadu');
        $response->assertSee('2. Penerimaan Stok Distribusi');
        $response->assertSee('3. Stok (Ketersediaan Produk)');
        $response->assertSee('4. Laporan');
        $response->assertSee('Keluar');

        // 2. Dedicated Data page contains all sub-modules
        $dataResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.data'));

        $dataResponse->assertStatus(200);
        $dataResponse->assertSee('1. Data');
        $dataResponse->assertSee('Profil');
        $dataResponse->assertSee('Admin Manager Operasional');
        $dataResponse->assertSee('Bagus');
        $dataResponse->assertSee('Manajemen Data Karyawan');
        $dataResponse->assertSee('Tambah Karyawan Baru');
        $dataResponse->assertSee('Jadwal Shift Karyawan');
        $dataResponse->assertSee('Data Billing Setiap Shift');
        $dataResponse->assertSee('Jumlah Presensi Karyawan');
        $dataResponse->assertSee('Jam Kerja Karyawan');
        $dataResponse->assertSee('Standard Operating Procedure');

        // 3. Dedicated Distribution page
        $distResponse = $this->actingAs($this->adminUser)->get(route('admin.distribusi'));
        $distResponse->assertStatus(200);
        $distResponse->assertSee('Penerimaan Stok Distribusi');

        // 4. Dedicated Stock page
        $stokResponse = $this->actingAs($this->adminUser)->get(route('admin.stok'));
        $stokResponse->assertStatus(200);
        $stokResponse->assertSee('Ketersediaan Produk');

        // 5. Dedicated Report page
        $laporanResponse = $this->actingAs($this->adminUser)->get(route('admin.laporan'));
        $laporanResponse->assertStatus(200);
        $laporanResponse->assertSee('Laporan');
    }

    public function test_admin_dashboard_displays_recent_pos_sales_table_with_erp_link(): void
    {
        // 1. Buat data penjualan kasir POS
        $sale = CafeSale::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'invoice_number' => 'INV-POS-20261031-B8995',
            'customer_name' => 'Pelanggan Uji Coba',
            'total_fnb' => 15000.0,
            'total_billing' => 0.0,
            'net_amount' => 15000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // 2. Akses Dashboard Utama Admin
        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.index'));

        $response->assertOk();
        $response->assertSee('Transaksi Penjualan POS Kasir Terkini');
        $response->assertSee('INV-POS-20261031-B8995');
        $response->assertSee('Buka di Admin ERP');
        $response->assertSee(route('admin.penjualan', ['q' => 'INV-POS-20261031-B8995']));
    }

    public function test_admin_can_add_and_delete_employee_with_redirect_to_data_profil(): void
    {
        // 1. Create Employee
        $storeResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.karyawan.store'), [
                'name' => 'Karyawan Baru MGK',
                'username' => 'karyawanbaru',
                'email' => 'karyawanbaru@mgk.com',
                'phone' => '089988776655',
                'position' => 'Operator',
                'branch_id' => $this->branch->id,
                'password' => '123456',
                'pin' => '123456',
                'redirect_to' => route('admin.index').'#data-profil',
            ]);

        $storeResponse->assertRedirect(route('admin.index').'#data-profil');
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'name' => 'Karyawan Baru MGK',
            'username' => 'karyawanbaru',
            'email' => 'karyawanbaru@mgk.com',
        ]);

        $newEmp = Employee::where('username', 'karyawanbaru')->first();
        $this->assertNotNull($newEmp);

        // 2. Delete Employee
        $destroyResponse = $this->actingAs($this->adminUser)
            ->delete(route('admin.karyawan.destroy', $newEmp->id), [
                'redirect_to' => route('admin.index').'#data-profil',
            ]);

        $destroyResponse->assertRedirect(route('admin.index').'#data-profil');
        $destroyResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('employees', [
            'id' => $newEmp->id,
        ]);
    }

    public function test_bagus_admin_url_serves_admin_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get('/bagus/admin');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Operasional');
    }

    public function test_admin_can_confirm_stock_transfer_dispatch(): void
    {
        $warehouse1 = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-PST',
            'name' => 'Gudang Pusat',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $warehouse2 = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-KSR',
            'name' => 'Loker Kasir',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create([
            'code' => 'PCS',
            'name' => 'Pcs',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'code' => 'CAT-DRK',
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $prod = Product::create([
            'code' => 'PRD-MIN',
            'name' => 'Le Minerale',
            'category_id' => $cat->id,
            'uom_id' => $uom->id,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $prod->id,
            'sku' => 'SKU-MINERAL',
            'name' => 'Le Minerale 600ml',
            'base_price' => 4000,
            'is_active' => true,
        ]);

        $transferId = DB::table('stock_transfer_dispatches')->insertGetId([
            'transfer_code' => 'TRF-TEST-001',
            'from_warehouse_id' => $warehouse1->id,
            'to_warehouse_id' => $warehouse2->id,
            'product_variant_id' => $variant->id,
            'qty' => 24,
            'status' => 'dispatched',
            'dispatched_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.distribusi.terima', $transferId));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_transfer_dispatches', [
            'id' => $transferId,
            'status' => 'received',
        ]);
    }

    public function test_admin_dashboard_renders_rekap_jam_kerja_per_shift_bulan(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.index', ['tab' => 'tab-presensi', 'rekap_month' => 10, 'rekap_year' => 2026]));

        $response->assertOk();
        $response->assertSee('Rekap Jam Kerja Per Shift Bulanan');
        $response->assertSee('Akumulasi Total Jam Kerja Berdasarkan Shift');
        $response->assertSee('Rincian Jam Kerja Karyawan per Shift');
        $response->assertViewHas('rekapBulanSummary');
    }

    public function test_admin_dashboard_renders_rekap_jam_kerja_per_outlet_and_kegiatan(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.index', ['tab' => 'tab-presensi', 'rekap_month' => 10, 'rekap_year' => 2026]));

        $response->assertOk();
        $response->assertSee('Rekap Jam Kerja Per Outlet Bulanan');
        $response->assertSee('Akumulasi Total Jam Kerja Berdasarkan Outlet');
        $response->assertSee('Rincian Jam Kerja Staf per Outlet');
        $response->assertSee('REKAP JAM KERJA PER SHIF BULAN');
        $response->assertSee('table-rekap-shift');
        $response->assertSee('Copy Tabel');
        $response->assertSee('Print Data');
        $response->assertSee('shif 1: (01-07)');
        $response->assertSee('Total Shift');
        $response->assertSee('BO (jam)');
        $response->assertSee('Telat');
        $response->assertSee('Rekap Kehadiran Kegiatan (Meeting, Pelatihan, GC)');
        $response->assertSee('Ringkasan per Kategori Kegiatan (Meeting, Pelatihan, GC)');
        $response->assertSee('Catatan Kehadiran Kegiatan Karyawan');
        $response->assertViewHas('rekapOutletSummary');
        $response->assertViewHas('rekapKegiatanSummary');
    }

    public function test_admin_can_create_update_and_delete_activity_attendance(): void
    {
        // 1. Create Activity
        $storeResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.kegiatan.store'), [
                'activity_type' => 'gc',
                'title' => 'General Cleaning Akhir Pekan',
                'employee_id' => $this->employee->id,
                'branch_id' => $this->branch->id,
                'activity_date' => '2026-10-18',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'duration_minutes' => 120,
                'status' => 'hadir',
                'notes' => 'Sanitasi meja & pembersihan filter AC',
                'redirect_to' => route('admin.index').'#data-presensi',
            ]);

        $storeResponse->assertRedirect(route('admin.index').'#data-presensi');
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('activity_attendances', [
            'activity_type' => 'gc',
            'title' => 'General Cleaning Akhir Pekan',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'status' => 'hadir',
        ]);

        $activity = ActivityAttendance::where('title', 'General Cleaning Akhir Pekan')->first();
        $this->assertNotNull($activity);

        // 2. Update Activity
        $updateResponse = $this->actingAs($this->adminUser)
            ->put(route('admin.kegiatan.update', $activity->id), [
                'activity_type' => 'pelatihan',
                'title' => 'Pelatihan SOP Hospitality & POS',
                'employee_id' => $this->employee->id,
                'branch_id' => $this->branch->id,
                'activity_date' => '2026-10-18',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'duration_minutes' => 120,
                'status' => 'hadir',
                'notes' => 'Selesai modul simulasi kasir',
                'redirect_to' => route('admin.index').'#data-presensi',
            ]);

        $updateResponse->assertRedirect(route('admin.index').'#data-presensi');
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('activity_attendances', [
            'id' => $activity->id,
            'activity_type' => 'pelatihan',
            'title' => 'Pelatihan SOP Hospitality & POS',
            'notes' => 'Selesai modul simulasi kasir',
        ]);

        // 3. Delete Activity
        $destroyResponse = $this->actingAs($this->adminUser)
            ->delete(route('admin.kegiatan.destroy', $activity->id), [
                'redirect_to' => route('admin.index').'#data-presensi',
            ]);

        $destroyResponse->assertRedirect(route('admin.index').'#data-presensi');
        $destroyResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('activity_attendances', [
            'id' => $activity->id,
        ]);
    }

    public function test_admin_laporan_page_renders_profit_analytics_and_printer_metrics(): void
    {
        $category = Category::create([
            'name' => 'Food Category',
            'code' => 'CAT-FOOD',
            'slug' => 'cat-food',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create([
            'name' => 'Pieces',
            'code' => 'PCS',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'name' => 'Nasi Goreng Spesial',
            'code' => 'PRD-NASGOR',
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Nasi Goreng Spesial Pedas',
            'sku' => 'SKU-NASGOR-01',
            'base_price' => 15000,
            'cost_price' => 9000,
            'is_active' => true,
        ]);

        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-LPR-01',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Pelanggan Laporan',
            'total_fnb' => 30000,
            'total_billing' => 12000,
            'gross_amount' => 42000,
            'net_amount' => 42000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $variant->id,
            'item_type' => 'fnb',
            'item_name' => $variant->name,
            'qty' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        PrinterPrintJob::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'printer_name' => 'Canon G3010 Series USB',
            'document_name' => 'Laporan Skripsi Bab 1-3.pdf',
            'client_computer' => 'PC-04',
            'pages' => 15,
            'color_mode' => 'color',
            'unit_price' => 1000,
            'total_amount' => 15000,
            'work_date' => now()->toDateString(),
            'printed_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.laporan'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Rekapitulasi', false);
        $response->assertSee('Total Laba Kotor', false);
        $response->assertSee('Laba Kotor F&B Kasir', false);
        $response->assertSee('Laba Jasa Print', false);
        $response->assertSee('Utilisasi Printer Canon USB', false);
        $response->assertSee('Top 5 Kontributor Laba', false);
        $response->assertSee('Nasi Goreng Spesial Pedas');
        $response->assertSee('Canon G3010 Series USB');
        $response->assertSee('Laporan Skripsi Bab 1-3.pdf');
    }

    public function test_admin_laporan_page_filters_by_period_and_branch(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.laporan', ['period' => '7days', 'branch_id' => $this->branch->id]));

        $response->assertStatus(200);
        $response->assertSee('Semua Cabang / Outlet');
        $response->assertSee($this->branch->name);
    }

    public function test_admin_can_submit_supply_stock_and_automatically_increments_inventory(): void
    {
        $warehouse1 = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-PST',
            'name' => 'Gudang Pusat',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $warehouse2 = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-KSR',
            'name' => 'Loker Kasir',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create(['name' => 'Rim', 'code' => 'RIM', 'symbol' => 'rim', 'is_active' => true]);
        $cat = Category::create(['name' => 'Kertas', 'code' => 'CAT-KTS', 'slug' => 'kertas', 'is_active' => true]);
        $prod = Product::create(['category_id' => $cat->id, 'uom_id' => $uom->id, 'name' => 'Kertas HVS A4', 'code' => 'PRD-A4', 'is_active' => true]);
        $variant = ProductVariant::create(['product_id' => $prod->id, 'name' => 'HVS A4 75gr', 'sku' => 'SKU-A4-75', 'base_price' => 55000, 'is_active' => true]);

        // Kirim Supply Stok Langsung Diterima (received)
        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->post(route('admin.distribusi.kirim'), [
                'product_variant_id' => $variant->id,
                'from_warehouse_id' => $warehouse1->id,
                'to_warehouse_id' => $warehouse2->id,
                'qty' => 500,
                'status' => 'received',
                'operator_notes' => 'Supply 1 Rim Kertas HVS A4 Toko Surya',
            ]);

        $response->assertRedirect(route('admin.distribusi'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stock_transfer_dispatches', [
            'product_variant_id' => $variant->id,
            'from_warehouse_id' => $warehouse1->id,
            'to_warehouse_id' => $warehouse2->id,
            'qty' => 500,
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('stock_inventories', [
            'warehouse_id' => $warehouse2->id,
            'product_variant_id' => $variant->id,
            'qty_on_hand' => 500,
        ]);
    }

    public function test_admin_can_fulfill_cashier_shift_stock_request(): void
    {
        $warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-KSR',
            'name' => 'Loker Kasir Utama',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create(['name' => 'Botol', 'code' => 'BTL', 'symbol' => 'btl', 'is_active' => true]);
        $cat = Category::create(['name' => 'Minuman', 'code' => 'CAT-DRK2', 'slug' => 'minuman2', 'is_active' => true]);
        $prod = Product::create(['category_id' => $cat->id, 'uom_id' => $uom->id, 'name' => 'Teh Pucuk Harum', 'code' => 'PRD-PUCUK', 'is_active' => true]);
        $variant = ProductVariant::create(['product_id' => $prod->id, 'name' => 'Teh Pucuk Harum 350ml', 'sku' => 'SKU-PUCUK', 'base_price' => 4000, 'is_active' => true]);

        $stockReq = ShiftStockRequest::create([
            'request_number' => 'REQ-TEST-PUCUK-01',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'item_category' => 'minuman',
            'item_name' => 'Teh Pucuk Harum 350ml',
            'qty_requested' => 12,
            'unit' => 'pcs',
            'urgency' => 'urgent',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->post(route('admin.distribusi.penuhi', $stockReq->id));

        $response->assertRedirect(route('admin.distribusi'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_stock_requests', [
            'id' => $stockReq->id,
            'status' => 'fulfilled',
        ]);

        $this->assertDatabaseHas('stock_inventories', [
            'warehouse_id' => $warehouse->id,
            'product_variant_id' => $variant->id,
            'qty_on_hand' => 12,
        ]);
    }

    public function test_admin_can_fulfill_stock_request_via_bagus_prefix_and_gracefully_handles_get(): void
    {
        $warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-KSR-2',
            'name' => 'Loker Kasir Cadangan',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create(['name' => 'Bungkus', 'code' => 'BKS', 'symbol' => 'bks', 'is_active' => true]);
        $cat = Category::create(['name' => 'Snack', 'code' => 'CAT-SNK2', 'slug' => 'snack2', 'is_active' => true]);
        $prod = Product::create(['category_id' => $cat->id, 'uom_id' => $uom->id, 'name' => 'Generic KACANG', 'code' => 'PRD-KACANG', 'is_active' => true]);
        $variant = ProductVariant::create(['product_id' => $prod->id, 'name' => 'Generic KACANG', 'sku' => 'SKU-GENERICKACANG-2', 'base_price' => 2000, 'is_active' => true]);

        $stockReq = ShiftStockRequest::create([
            'request_number' => 'REQ-TEST-KACANG-36',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'item_category' => 'snack',
            'item_name' => 'Generic KACANG',
            'qty_requested' => 5,
            'unit' => 'pcs',
            'urgency' => 'urgent',
            'status' => 'pending',
        ]);

        // GET request gracefully redirects with info
        $getResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get('/bagus/admin/distribusi/permintaan/'.$stockReq->id.'/penuhi');

        $getResponse->assertRedirect(route('admin.distribusi'));
        $getResponse->assertSessionHas('info');

        // POST request to /bagus/admin/distribusi/permintaan/{id}/penuhi fulfills it
        $postResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->post('/bagus/admin/distribusi/permintaan/'.$stockReq->id.'/penuhi');

        $postResponse->assertRedirect(route('admin.distribusi'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('shift_stock_requests', [
            'id' => $stockReq->id,
            'status' => 'fulfilled',
        ]);
    }
}
