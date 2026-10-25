<?php

namespace Tests\Feature;

use App\Domains\Attendance\Models\AuditLog;
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

    public function test_rejected_registration_can_check_status_and_receive_signed_re_registration_url(): void
    {
        $registration = EmployeeRegistration::create([
            'full_name' => 'Doni Tata',
            'username' => 'doni_tata',
            'phone' => '081234567899',
            'email' => 'doni@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'rejected',
            'rejection_reason' => 'Nomor WhatsApp salah 1 digit, mohon perbaiki.',
        ]);

        $response = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'doni_tata',
        ]);

        $response->assertOk();
        $response->assertSee('Perlu Perbaikan / Ditolak');
        $response->assertSee('Nomor WhatsApp salah 1 digit, mohon perbaiki.');
        $response->assertSee('Daftar Ulang Sekarang');

        $expectedToken = hash_hmac('sha256', $registration->id.'|'.$registration->username.'|'.($registration->created_at?->timestamp ?? 0), config('app.key'));
        $expectedUrl = route('presensi2.register', ['re_reg' => $registration->id, 'token' => $expectedToken]);
        $response->assertSee(e($expectedUrl), false);
    }

    public function test_re_registration_form_loads_with_prefilled_data_and_rejection_reason(): void
    {
        $registration = EmployeeRegistration::create([
            'full_name' => 'Doni Tata',
            'username' => 'doni_tata',
            'phone' => '081234567899',
            'email' => 'doni@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'rejected',
            'rejection_reason' => 'Nomor WhatsApp salah 1 digit, mohon perbaiki.',
        ]);

        $token = hash_hmac('sha256', $registration->id.'|'.$registration->username.'|'.($registration->created_at?->timestamp ?? 0), config('app.key'));

        $response = $this->get(route('presensi2.register', ['re_reg' => $registration->id, 'token' => $token]));

        $response->assertOk();
        $response->assertSee('MODE DAFTAR ULANG &amp; PERBAIKAN DATA', false);
        $response->assertSee('Nomor WhatsApp salah 1 digit, mohon perbaiki.');
        $response->assertSee('value="Doni Tata"', false);
        $response->assertSee('value="doni_tata"', false);
        $response->assertSee('value="081234567899"', false);
        $response->assertSee('value="doni@wisata.id"', false);
        $response->assertSee('⚠️ Perlu Koreksi');
        $response->assertSee('Pastikan nomor WhatsApp aktif agar dapat dihubungi oleh tim Admin.');
        $response->assertSee('Perbarui &amp; Ajukan Ulang Pendaftaran', false);
    }

    public function test_submitting_re_registration_performs_in_place_update_and_sets_status_back_to_pending(): void
    {
        $registration = EmployeeRegistration::create([
            'full_name' => 'Doni Tata',
            'username' => 'doni_tata',
            'phone' => '081234567899',
            'email' => 'doni@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'rejected',
            'rejection_reason' => 'Nomor WhatsApp salah 1 digit, mohon perbaiki.',
        ]);

        $token = hash_hmac('sha256', $registration->id.'|'.$registration->username.'|'.($registration->created_at?->timestamp ?? 0), config('app.key'));

        $initialId = $registration->id;

        $response = $this->post(route('presensi2.register.post'), [
            're_registration_id' => $registration->id,
            're_registration_token' => $token,
            'full_name' => 'Doni Tata Wijaya',
            'username' => 'doni_tata',
            'credential' => '693821',
            'confirm_credential' => '693821',
            'phone' => '081234567800',
            'email' => 'doni.tata@wisata.id',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        // Pastikan TIDAK membuat row baru (In-Place Update Opsi B)
        $this->assertEquals(1, EmployeeRegistration::where('username', 'doni_tata')->count());

        $registration->refresh();
        $this->assertEquals($initialId, $registration->id);
        $this->assertEquals('Doni Tata Wijaya', $registration->full_name);
        $this->assertEquals('081234567800', $registration->phone);
        $this->assertEquals('doni.tata@wisata.id', $registration->email);
        $this->assertEquals('pending', $registration->status);
        $this->assertStringContainsString('[Sebelumnya Ditolak]', (string) $registration->rejection_reason);
        $this->assertTrue(Hash::check('693821', $registration->pin));

        // Verifikasi Jejak Audit ISO 27001
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee_registration_resubmitted',
            'auditable_type' => EmployeeRegistration::class,
            'auditable_id' => $initialId,
        ]);
    }

    public function test_admin_verification_table_displays_re_registration_badge_for_resubmitted_applications(): void
    {
        $registration = EmployeeRegistration::create([
            'full_name' => 'Rina Kartika',
            'username' => 'rina_k',
            'phone' => '081234567855',
            'email' => 'rina@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'pending',
            'rejection_reason' => '[Sebelumnya Ditolak]: Nama tidak sesuai KTP.',
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.karyawan.verifikasi'));

        $response->assertOk();
        $response->assertSee('Rina Kartika');
        $response->assertSee('🔄 Daftar Ulang');
        $response->assertSee('BERKAS DAFTAR ULANG / PERBAIKAN');
        $response->assertSee('Catatan Penolakan Sebelumnya:');
        $response->assertSee('Daftar Ulang (Prioritas)');
        $response->assertSee('Pendaftar Baru');
    }

    public function test_admin_verification_prioritizes_re_registrations_at_top_of_queue(): void
    {
        // 1. Pendaftar baru biasa (ID lebih besar, didaftarkan belakangan)
        $regNew = EmployeeRegistration::create([
            'full_name' => 'Karyawan Baru Biasa',
            'username' => 'karyawan_baru',
            'phone' => '081234567811',
            'email' => 'baru@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'pending',
            'rejection_reason' => null,
            'updated_at' => now(),
        ]);

        // 2. Pendaftar ulang (revisi dari penolakan)
        $regRereg = EmployeeRegistration::create([
            'full_name' => 'Pendaftar Ulang Prioritas',
            'username' => 'pendaftar_prioritas',
            'phone' => '081234567822',
            'email' => 'prioritas@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'pending',
            'rejection_reason' => '[Sebelumnya Ditolak]: Perbaiki nomor kontak.',
            'updated_at' => now()->subMinute(),
        ]);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.karyawan.verifikasi'));

        $response->assertOk();

        // Pastikan Pendaftar Ulang muncul LEBIH DAHULU di HTML daripada Pendaftar Baru (Prioritas Teratas)
        $content = $response->getContent();
        $posRereg = strpos($content, 'Pendaftar Ulang Prioritas');
        $posNew = strpos($content, 'Karyawan Baru Biasa');

        $this->assertNotFalse($posRereg);
        $this->assertNotFalse($posNew);
        $this->assertLessThan($posNew, $posRereg, 'Pendaftar ulang harus berada di urutan atas mendahului pendaftar baru.');
    }

    public function test_max_three_re_registration_limit_locks_online_retry_and_instructs_in_person_hr_visit(): void
    {
        $registration = EmployeeRegistration::create([
            'full_name' => 'Agus Kuncoro',
            'username' => 'agus_kuncoro',
            'phone' => '081234567833',
            'email' => 'agus@wisata.id',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'rejected',
            'rejection_reason' => 'Identitas tidak valid untuk ketiga kalinya.',
        ]);

        // Simulasikan sudah 3 kali resubmit sebelumnya tercatat di AuditLog
        for ($i = 1; $i <= 3; $i++) {
            AuditLog::create([
                'user_id' => null,
                'action' => 'employee_registration_resubmitted',
                'auditable_type' => EmployeeRegistration::class,
                'auditable_id' => $registration->id,
                'new_values' => ['attempt' => $i],
            ]);
        }

        // 1. Saat cek status, tombol daftar ulang dimatikan dan muncul arahan tatap muka
        $checkRes = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'agus_kuncoro',
        ]);

        $checkRes->assertOk();
        $checkRes->assertSee('Batas Perbaikan Tercapai ⚠️');
        $checkRes->assertSee('batas perbaikan mandiri secara online telah ditutup');
        $checkRes->assertDontSee('Daftar Ulang Sekarang');

        // 2. Jika mencoba submit paksa melalui register.post dengan id permohonan tersebut, ditolak
        $token = hash_hmac('sha256', $registration->id.'|'.$registration->username.'|'.($registration->created_at?->timestamp ?? 0), config('app.key'));

        $submitRes = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            're_registration_id' => $registration->id,
            're_registration_token' => $token,
            'full_name' => 'Agus Kuncoro Baru',
            'username' => 'agus_kuncoro',
            'credential' => '582914',
            'confirm_credential' => '582914',
            'phone' => '081234567833',
            'email' => 'agus@wisata.id',
        ]);

        $submitRes->assertRedirect(route('presensi2.register'));
        $submitRes->assertSessionHasErrors(['registration']);
    }
}
