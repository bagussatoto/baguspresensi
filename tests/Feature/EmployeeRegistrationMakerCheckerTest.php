<?php

namespace Tests\Feature;

use App\Models\EmployeeRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class EmployeeRegistrationMakerCheckerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Branch::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Outlet Pusat',
                'code' => 'PST-001',
                'is_active' => true,
            ]
        );
    }

    public function test_self_registration_creates_staging_record_with_pending_status_and_does_not_create_master_employee(): void
    {
        $response = $this->post(route('presensi2.register.post'), [
            'full_name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'credential' => '582914',
            'phone' => '081234567890',
            'email' => 'budi@wisata.id',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        // 1. Verifikasi Staging Table (Maker)
        $this->assertDatabaseHas('employee_registrations', [
            'full_name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'phone' => '081234567890',
            'status' => 'pending',
        ]);

        $registration = EmployeeRegistration::where('username', 'budi_santoso')->first();
        $this->assertNotNull($registration);
        $this->assertTrue(Hash::check('582914', $registration->pin));
        $this->assertNull($registration->approved_by);
        $this->assertNull($registration->approved_at);

        // 2. Verifikasi SoT: Master Data employees BELUM tercemar
        $this->assertDatabaseMissing('employees', [
            'username' => 'budi_santoso',
        ]);

        // 3. Verifikasi SoD: Karyawan TIDAK langsung otomatis login
        $this->assertNull(session('presensi2_employee_id'));
    }

    public function test_pending_registered_employee_cannot_login_or_clock_in(): void
    {
        $registration = EmployeeRegistration::factory()->create([
            'username' => 'calon_staf',
            'pin' => Hash::make('123456'),
            'password' => Hash::make('123456'),
            'status' => 'pending',
        ]);

        // Mencoba login ke portal presensi
        $response = $this->post(route('presensi2.login.post'), [
            'credential' => '123456',
        ]);

        // Login harus gagal karena belum disetujui admin dan belum ada di tabel employees
        $this->assertNull(session('presensi2_employee_id'));
    }

    public function test_registration_rejects_duplicate_username_already_existing_in_master_employees(): void
    {
        Employee::create([
            'branch_id' => 1,
            'name' => 'Karyawan Resmi',
            'code' => 'EMP-REG-01',
            'username' => 'karyawan_resmi',
            'phone' => '081999888777',
            'pin' => Hash::make('111222'),
            'password' => Hash::make('111222'),
            'position' => 'Staff',
            'is_active' => true,
        ]);

        $response = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Tiruan',
            'username' => 'karyawan_resmi',
            'credential' => '111222',
            'phone' => '081999888777',
            'email' => 'tiruan@mgk.id',
        ]);

        $response->assertRedirect(route('presensi2.register'));
        $response->assertSessionHasErrors('username');
    }

    public function test_registration_notifies_if_already_pending_in_staging(): void
    {
        EmployeeRegistration::factory()->create([
            'username' => 'antrean_pending',
            'phone' => '085566778899',
            'email' => 'antrean@mgk.id',
            'status' => 'pending',
        ]);

        $response = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Coba Daftar Lagi',
            'username' => 'antrean_pending',
            'credential' => '998877',
            'phone' => '085566778899',
            'email' => 'antrean@mgk.id',
        ]);

        $response->assertRedirect(route('presensi2.register'));
        $response->assertSessionHasErrors('username');
    }

    public function test_admin_can_access_verification_page_and_view_pending_queue(): void
    {
        $admin = User::factory()->create();

        EmployeeRegistration::factory()->create([
            'full_name' => 'Ahmad Kasir',
            'username' => 'ahmad_kasir',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.karyawan.verifikasi'));

        $response->assertOk();
        $response->assertSee('Verifikasi Pendaftaran Mandiri Karyawan');
        $response->assertSee('Ahmad Kasir');
        $response->assertSee('ahmad_kasir');
        $response->assertDontSee('Admin Manager');
        $response->assertSee($admin->name);
    }

    public function test_admin_can_approve_pending_registration_creating_master_employee_and_audit_log(): void
    {
        $admin = User::factory()->create();

        $registration = EmployeeRegistration::factory()->create([
            'full_name' => 'Dewi Rahma',
            'username' => 'dewi_rahma',
            'phone' => '081233445566',
            'password' => Hash::make('secretpass'),
            'pin' => Hash::make('778899'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.karyawan.verifikasi.approve', $registration), [
            'branch_id' => 1,
            'role' => 'kasir',
            'position' => 'Kasir Utama',
            'max_discount_approval_percent' => 10,
            'approval_notes' => 'Telah lolos interview & cek identitas',
        ]);

        $response->assertRedirect(route('admin.karyawan.verifikasi'));
        $response->assertSessionHas('success');

        // 1. Verifikasi Staging Table status berubah jadi approved
        $registration->refresh();
        $this->assertEquals('approved', $registration->status);
        $this->assertEquals($admin->id, $registration->approved_by);
        $this->assertNotNull($registration->approved_at);
        $this->assertNotNull($registration->created_employee_id);

        // 2. Verifikasi Master Data (SoT): Employee resmi tercipta
        $employee = Employee::find($registration->created_employee_id);
        $this->assertNotNull($employee);
        $this->assertEquals('Dewi Rahma', $employee->name);
        $this->assertEquals('dewi_rahma', $employee->username);
        $this->assertEquals('081233445566', $employee->phone);
        $this->assertEquals(1, $employee->branch_id);
        $this->assertEquals('kasir', $employee->role->value ?? (string) $employee->role);
        $this->assertEquals(10, $employee->max_discount_approval_percent);
        $this->assertTrue($employee->is_active);
        $this->assertTrue(Hash::check('778899', $employee->pin));

        // 3. Verifikasi Audit Trail ISO 27001
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'EMPLOYEE_REGISTRATION_APPROVED',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
        ]);
    }

    public function test_admin_cannot_self_approve_own_registration_under_sod_rule(): void
    {
        // Admin yang namanya sama dengan pendaftar mencoba meng-approve akunnya sendiri
        $admin = User::factory()->create(['name' => 'Bagus Admin']);

        $registration = EmployeeRegistration::factory()->create([
            'full_name' => 'Bagus Admin',
            'username' => 'bagus_admin',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.karyawan.verifikasi.approve', $registration), [
            'branch_id' => 1,
            'role' => 'supervisor',
        ]);

        $response->assertSessionHas('error');

        // Data tetap pending dan tidak masuk ke master employees
        $registration->refresh();
        $this->assertEquals('pending', $registration->status);
        $this->assertDatabaseMissing('employees', [
            'username' => 'bagus_admin',
        ]);
    }

    public function test_admin_can_reject_registration_with_audit_reason(): void
    {
        $admin = User::factory()->create();

        $registration = EmployeeRegistration::factory()->create([
            'full_name' => 'Orang Asing',
            'username' => 'orang_asing',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.karyawan.verifikasi.reject', $registration), [
            'rejection_reason' => 'Nomor WhatsApp palsu dan bukan karyawan resmi.',
        ]);

        $response->assertRedirect(route('admin.karyawan.verifikasi'));
        $response->assertSessionHas('info');

        $registration->refresh();
        $this->assertEquals('rejected', $registration->status);
        $this->assertEquals('Nomor WhatsApp palsu dan bukan karyawan resmi.', $registration->rejection_reason);

        // Verifikasi Jejak Audit Penolakan ISO 27001
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'EMPLOYEE_REGISTRATION_REJECTED',
            'auditable_type' => EmployeeRegistration::class,
            'auditable_id' => $registration->id,
        ]);
    }

    public function test_verification_page_displays_manager_name_and_never_generic_admin_manager(): void
    {
        $branch = Branch::create([
            'name' => 'Maya Graha Kencana - Pusat',
            'code' => 'MGK-PST',
            'is_active' => true,
        ]);

        Employee::create([
            'branch_id' => $branch->id,
            'code' => 'EMP-043',
            'name' => 'Bagus Satoto',
            'position' => 'Manager',
            'jabatan' => 'Manager Operasional',
            'role' => EmployeeRole::SUPERVISOR,
            'is_active' => true,
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.karyawan.verifikasi'));

        $response->assertOk();
        $response->assertDontSee('Admin Manager');
        $response->assertSee('Bagus Satoto');
    }
}
