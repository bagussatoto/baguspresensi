<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use App\Models\EmployeeRegistration;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class RegistrationBreachAndApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $activeEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::firstOrCreate(
            ['id' => 1],
            ['name' => 'Cabang Pusat MGK', 'code' => 'MGK-PST', 'is_active' => true]
        );

        $this->activeEmployee = Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Bagus Satoto',
            'code' => 'EMP-001',
            'username' => 'bagussatoto',
            'phone' => '081234567890',
            'email' => 'bagus@mayanet.com',
            'is_active' => true,
        ]);

        RateLimiter::clear('reg_attempt_ip:127.0.0.1');
    }

    /**
     * Test 1: Bot / Orang iseng mencoba menggunakan username milik karyawan aktif sebanyak 3x
     * -> Terblokir di percobaan ke-3 (Lockout 5 menit)
     * -> Notifikasi WhatsApp ke pemilik akun terkirim
     * -> Notifikasi Security Alert ke Telegram Admin terkirim
     */
    public function test_bot_or_attacker_blocked_on_third_attempt_and_dispatches_security_alert(): void
    {
        $mockService = $this->createMock(EarlyWarningNotificationService::class);

        // Pastikan dipanggil tepat 1 kali pada percobaan ke-3
        $mockService->expects($this->once())
            ->method('sendRegistrationBreachAlert')
            ->with($this->callback(function (array $details) {
                return $details['target_name'] === 'Bagus Satoto'
                    && $details['target_phone'] === '081234567890'
                    && $details['target_code'] === 'EMP-001'
                    && $details['attempted_username'] === 'bagussatoto'
                    && $details['duration_minutes'] === 5;
            }))
            ->willReturn(['whatsapp' => true, 'telegram' => true]);

        $this->app->instance(EarlyWarningNotificationService::class, $mockService);

        // Percobaan 1: Gagal & muncul info sisa 2x
        $res1 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Hacker 1',
            'username' => 'bagussatoto',
            'credential' => '719283',
            'confirm_credential' => '719283',
            'phone' => '081299990001',
            'email' => 'hacker1@example.com',
        ]);
        $res1->assertSessionHasErrors(['username']);
        $this->assertStringContainsString('Kesempatan tersisa: 2x', session('errors')->first('username'));

        // Percobaan 2: Gagal & muncul info sisa 1x
        $res2 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Hacker 2',
            'username' => 'bagussatoto',
            'credential' => '719283',
            'confirm_credential' => '719283',
            'phone' => '081299990002',
            'email' => 'hacker2@example.com',
        ]);
        $res2->assertSessionHasErrors(['username']);
        $this->assertStringContainsString('Kesempatan tersisa: 1x', session('errors')->first('username'));

        // Percobaan 3: Gagal -> Akses dibekukan & session lockout_seconds diset
        $res3 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Hacker 3',
            'username' => 'bagussatoto',
            'credential' => '719283',
            'confirm_credential' => '719283',
            'phone' => '081299990003',
            'email' => 'hacker3@example.com',
        ]);
        $res3->assertSessionHas('lockout_seconds');
        $res3->assertSessionHasErrors(['registration']);
        $this->assertStringContainsString('dibekukan selama 5 menit', session('errors')->first('registration'));

        // Percobaan 4: Ditolak seketika oleh rate limiter sebelum menyentuh pengecekan database
        $res4 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Hacker 4',
            'username' => 'usernamemurni',
            'credential' => '719283',
            'confirm_credential' => '719283',
            'phone' => '081299990004',
            'email' => 'hacker4@example.com',
        ]);
        $res4->assertSessionHas('lockout_seconds');
        $res4->assertSessionHasErrors(['registration']);
    }

    /**
     * Test 2: Pendaftar sah mengisi data yang valid dan baru
     * -> Berhasil tersimpan di staging table (status pending)
     * -> Notifikasi approval (WhatsApp/Telegram) ke Admin terkirim dengan link verifikasi
     */
    public function test_legitimate_registration_succeeds_and_dispatches_approval_notification_to_admin(): void
    {
        $mockService = $this->createMock(EarlyWarningNotificationService::class);

        $mockService->expects($this->once())
            ->method('sendNewRegistrationApprovalAlert')
            ->with(
                $this->equalTo('Budi Calon Staf'),
                $this->equalTo('budicalon'),
                $this->equalTo('081288887777')
            )
            ->willReturn(true);

        $this->app->instance(EarlyWarningNotificationService::class, $mockService);

        $response = $this->post(route('presensi2.register.post'), [
            'full_name' => 'Budi Calon Staf',
            'username' => 'budicalon',
            'credential' => '719283',
            'confirm_credential' => '719283',
            'phone' => '081288887777',
            'email' => 'budicalon@example.com',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        // Pastikan masuk ke tabel staging status pending
        $this->assertDatabaseHas('employee_registrations', [
            'full_name' => 'Budi Calon Staf',
            'username' => 'budicalon',
            'phone' => '081288887777',
            'email' => 'budicalon@example.com',
            'status' => 'pending',
        ]);
    }

    /**
     * Test 3: Honeypot trap menangkal bot otomatis
     * -> Bot mengisi input tersembunyi honeypot_website
     * -> Ditolak diam-diam tanpa membebani database dan tanpa error trace
     */
    public function test_honeypot_trap_blocks_automated_bot_submission(): void
    {
        $response = $this->post(route('presensi2.register.post'), [
            'honeypot_website' => 'http://spam-site-bot.com',
            'full_name' => 'Spam Bot',
            'username' => 'spambot',
            'credential' => '719283',
            'phone' => '081299990099',
            'email' => 'bot@spam.com',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $this->assertDatabaseMissing('employee_registrations', [
            'username' => 'spambot',
        ]);
    }

    /**
     * Test 4: Ketika Admin menyetujui (Approve) pendaftaran mandiri,
     * sistem mengirim WhatsApp ke Karyawan Baru & Pembaruan Status ke Grup WhatsApp 🚨 Security & HRD Alert MGK.
     */
    public function test_admin_approving_registration_sends_whatsapp_to_employee_and_security_hrd_group(): void
    {
        config([
            'services.whatsapp.api_url' => 'https://api.fonnte.com/send',
            'services.whatsapp.api_token' => 'test-fonnte-token',
            'services.whatsapp.admin_group_id' => '120363999888777@g.us',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $admin = User::factory()->create(['name' => 'Rina HRD']);

        $registration = EmployeeRegistration::create([
            'full_name' => 'Andi Pratama',
            'username' => 'andi_pratama',
            'phone' => '081277776666',
            'email' => 'andi@mayanet.com',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.karyawan.verifikasi.approve', $registration), [
            'branch_id' => 1,
            'role' => 'kasir',
            'position' => 'Kasir Outlet',
            'max_discount_approval_percent' => 5,
            'approval_notes' => 'Berkas KTP sesuai.',
        ]);

        $response->assertRedirect(route('admin.karyawan.verifikasi'));
        $response->assertSessionHas('success');

        // Pastikan terkirim ke nomor WA karyawan baru
        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();

            return ($data['target'] ?? '') === '6281277776666'
                && str_contains($data['message'] ?? '', '[PENDAFTARAN AKUN DISETUJUI — MGK]')
                && str_contains($data['message'] ?? '', 'Andi Pratama')
                && str_contains($data['message'] ?? '', 'andi_pratama');
        });

        // Pastikan terkirim pembaruan status ke Grup WhatsApp 🚨 Security & HRD Alert MGK
        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($data['message'] ?? '', '[SECURITY & HRD ALERT MGK]')
                && str_contains($data['message'] ?? '', 'UPDATE STATUS: REGISTRASI KARYAWAN DISETUJUI')
                && str_contains($data['message'] ?? '', 'Andi Pratama')
                && str_contains($data['message'] ?? '', 'Rina HRD')
                && str_contains($data['message'] ?? '', 'AKTIF DI MASTER DATA');
        });
    }

    /**
     * Test 5: Ketika Admin menolak (Reject) pendaftaran mandiri,
     * sistem mengirim WhatsApp ke Calon Karyawan (beserta alasan & link daftar ulang)
     * dan Pembaruan Status ke Grup WhatsApp 🚨 Security & HRD Alert MGK.
     */
    public function test_admin_rejecting_registration_sends_whatsapp_to_applicant_and_security_hrd_group(): void
    {
        config([
            'services.whatsapp.api_url' => 'https://api.fonnte.com/send',
            'services.whatsapp.api_token' => 'test-fonnte-token',
            'services.whatsapp.admin_group_id' => '120363999888777@g.us',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $admin = User::factory()->create(['name' => 'Budi Supervisor']);

        $registration = EmployeeRegistration::create([
            'full_name' => 'Siti Aminah',
            'username' => 'siti_aminah',
            'phone' => '081355554444',
            'email' => 'siti@mayanet.com',
            'password' => Hash::make('582914'),
            'pin' => Hash::make('582914'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.karyawan.verifikasi.reject', $registration), [
            'rejection_reason' => 'Penulisan nama lengkap belum sesuai KTP.',
        ]);

        $response->assertRedirect(route('admin.karyawan.verifikasi'));
        $response->assertSessionHas('info');

        // Pastikan terkirim ke nomor WA calon karyawan beserta alasan dan tautan daftar ulang
        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();

            return ($data['target'] ?? '') === '6281355554444'
                && str_contains($data['message'] ?? '', '[PEMBERITAHUAN VERIFIKASI PENDAFTARAN — MGK]')
                && str_contains($data['message'] ?? '', 'Siti Aminah')
                && str_contains($data['message'] ?? '', 'Penulisan nama lengkap belum sesuai KTP.')
                && str_contains($data['message'] ?? '', 're_reg=');
        });

        // Pastikan terkirim pembaruan status ke Grup WhatsApp 🚨 Security & HRD Alert MGK
        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($data['message'] ?? '', '[SECURITY & HRD ALERT MGK]')
                && str_contains($data['message'] ?? '', 'UPDATE STATUS: REGISTRASI KARYAWAN DITOLAK / PERLU REVISI')
                && str_contains($data['message'] ?? '', 'Siti Aminah')
                && str_contains($data['message'] ?? '', 'Penulisan nama lengkap belum sesuai KTP.')
                && str_contains($data['message'] ?? '', 'Budi Supervisor')
                && str_contains($data['message'] ?? '', 'MENUNGGU PERBAIKAN / DAFTAR ULANG');
        });
    }

    /**
     * Test 6: Ketika calon karyawan mengajukan registrasi baru,
     * pesan ke Grup WhatsApp 🚨 Security & HRD Alert MGK memuat Nama, Username, WA, Email, Alamat IP, dan Jenis Pengajuan.
     */
    public function test_new_registration_sends_formatted_group_whatsapp_message_with_email_and_ip(): void
    {
        config([
            'services.whatsapp.api_url' => 'https://api.fonnte.com/send',
            'services.whatsapp.api_token' => 'test-fonnte-token',
            'services.whatsapp.admin_group_id' => '120363999888777@g.us',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $response = $this->post(route('presensi2.register.post'), [
            'full_name' => 'Rizky Saputra',
            'username' => 'rizky_saputra',
            'credential' => '719283',
            'confirm_credential' => '719283',
            'phone' => '081266667777',
            'email' => 'rizky@mayanet.com',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();
            $msg = (string) ($data['message'] ?? '');

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($msg, '[SECURITY & HRD ALERT MGK]')
                && str_contains($msg, 'PENGAJUAN REGISTRASI KARYAWAN (PERLU VERIFIKASI)')
                && str_contains($msg, 'Rizky Saputra')
                && str_contains($msg, 'rizky_saputra')
                && str_contains($msg, '081266667777')
                && str_contains($msg, 'Alamat Email:* rizky@mayanet.com')
                && str_contains($msg, 'Alamat IP:* 127.0.0.1')
                && str_contains($msg, 'Jenis Pengajuan:* Pendaftaran Baru');
        });
    }

    /**
     * Test 7: Ketika Admin menonaktifkan (De-provision) dan mengaktifkan kembali (Reactivate) akun karyawan,
     * sistem mengirim pembaruan status ke Grup WhatsApp 🚨 Security & HRD Alert MGK.
     */
    public function test_admin_toggling_employee_status_sends_deprovisioned_and_reactivated_group_whatsapp_alerts(): void
    {
        config([
            'services.whatsapp.api_url' => 'https://api.fonnte.com/send',
            'services.whatsapp.api_token' => 'test-fonnte-token',
            'services.whatsapp.admin_group_id' => '120363999888777@g.us',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $admin = User::factory()->create(['name' => 'Hendra Manager']);

        // 1. Nonaktifkan (De-provision) karyawan aktif
        $resDeactivate = $this->actingAs($admin)->postJson(
            route('admin.karyawan.toggle-status', $this->activeEmployee),
            ['reason' => 'Resign selesai masa kontrak.']
        );
        $resDeactivate->assertOk();
        $resDeactivate->assertJson(['success' => true, 'is_active' => false]);

        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();
            $msg = (string) ($data['message'] ?? '');

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($msg, 'UPDATE STATUS: AKUN KARYAWAN DINONAKTIFKAN (DE-PROVISIONED)')
                && str_contains($msg, 'Bagus Satoto')
                && str_contains($msg, 'EMP-001')
                && str_contains($msg, 'Resign selesai masa kontrak.')
                && str_contains($msg, 'Hendra Manager')
                && str_contains($msg, 'NON-AKTIF / AKSES DICABUT');
        });

        // 2. Aktifkan kembali (Reactivate) karyawan tersebut
        $resReactivate = $this->actingAs($admin)->postJson(
            route('admin.karyawan.toggle-status', $this->activeEmployee)
        );
        $resReactivate->assertOk();
        $resReactivate->assertJson(['success' => true, 'is_active' => true]);

        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();
            $msg = (string) ($data['message'] ?? '');

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($msg, 'UPDATE STATUS: AKUN KARYAWAN DIAKTIFKAN KEMBALI')
                && str_contains($msg, 'Bagus Satoto')
                && str_contains($msg, 'EMP-001')
                && str_contains($msg, 'Hendra Manager')
                && str_contains($msg, 'AKTIF KEMBALI');
        });
    }

    /**
     * Test 8: Ketika Admin menyetujui atau menolak permohonan Reset PIN/Password karyawan,
     * sistem mengirim WhatsApp ke Karyawan dan Pembaruan Status ke Grup WhatsApp 🚨 Security & HRD Alert MGK.
     */
    public function test_admin_approving_and_rejecting_password_reset_sends_whatsapp_alerts_to_employee_and_group(): void
    {
        config([
            'services.whatsapp.api_url' => 'https://api.fonnte.com/send',
            'services.whatsapp.api_token' => 'test-fonnte-token',
            'services.whatsapp.admin_group_id' => '120363999888777@g.us',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $admin = User::factory()->create(['name' => 'Dewi HRD']);

        // 1. Skenario Disetujui (Approved)
        $approveReq = PasswordResetRequest::create([
            'employee_id' => $this->activeEmployee->id,
            'employee_name' => $this->activeEmployee->name,
            'employee_code' => $this->activeEmployee->code,
            'phone' => $this->activeEmployee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('918273'),
            'status' => 'pending',
        ]);

        $resApprove = $this->actingAs($admin)->post(
            route('admin.karyawan.reset-password.approve', $approveReq)
        );
        $resApprove->assertRedirect(route('admin.karyawan.reset-password'));

        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();
            $msg = (string) ($data['message'] ?? '');

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($msg, 'UPDATE STATUS: RESET PIN KARYAWAN DISETUJUI')
                && str_contains($msg, 'Bagus Satoto')
                && str_contains($msg, 'EMP-001')
                && str_contains($msg, 'Dewi HRD')
                && str_contains($msg, 'KREDENSIAL BARU AKTIF');
        });

        // 2. Skenario Ditolak (Rejected)
        $rejectReq = PasswordResetRequest::create([
            'employee_id' => $this->activeEmployee->id,
            'employee_name' => $this->activeEmployee->name,
            'employee_code' => $this->activeEmployee->code,
            'phone' => $this->activeEmployee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('736451'),
            'status' => 'pending',
        ]);

        $resReject = $this->actingAs($admin)->post(
            route('admin.karyawan.reset-password.reject', $rejectReq),
            ['rejection_reason' => 'Belum konfirmasi langsung ke atasan cabang.']
        );
        $resReject->assertRedirect(route('admin.karyawan.reset-password'));

        Http::assertSent(function (HttpClientRequest $request) {
            $data = $request->data();
            $msg = (string) ($data['message'] ?? '');

            return ($data['target'] ?? '') === '120363999888777@g.us'
                && str_contains($msg, 'UPDATE STATUS: RESET PIN KARYAWAN DITOLAK')
                && str_contains($msg, 'Bagus Satoto')
                && str_contains($msg, 'Belum konfirmasi langsung ke atasan cabang.')
                && str_contains($msg, 'Dewi HRD')
                && str_contains($msg, 'DITOLAK / KREDENSIAL LAMA TETAP BERLAKU');
        });
    }
}
