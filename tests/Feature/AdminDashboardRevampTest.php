<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\ActivityAttendance;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Models\Branch;
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

        // 1. Overview contains links / cards to all 5 sections
        $response->assertSee('1. Data Terpadu');
        $response->assertSee('2. Penerimaan Stok Distribusi');
        $response->assertSee('3. Stok (Ketersediaan Produk)');
        $response->assertSee('4. Laporan');
        $response->assertSee('5. Keluar');

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

    public function test_bagus_admin_url_redirects_to_admin_index(): void
    {
        $response = $this->get('/bagus/admin');
        $response->assertRedirect(route('admin.index'));
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
        $response->assertSee('Mepet');
        $response->assertSee('Telat');
        $response->assertSee('Komisi');
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
}
