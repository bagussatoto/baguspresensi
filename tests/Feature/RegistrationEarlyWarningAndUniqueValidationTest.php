<?php

namespace Tests\Feature;

use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use App\Models\EmployeeRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class RegistrationEarlyWarningAndUniqueValidationTest extends TestCase
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

    public function test_check_availability_detects_username_taken_in_employees_and_gives_suggestions(): void
    {
        Employee::create([
            'branch_id' => 1,
            'name' => 'Karyawan Aktif',
            'code' => 'EMP-ACT-1',
            'username' => 'johndoe',
            'phone' => '081234567001',
            'email' => 'john@company.com',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('presensi2.register.check'), [
            'field' => 'username',
            'value' => 'johndoe',
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'available' => false,
        ]);
        $this->assertNotEmpty($response->json('suggestions'));
    }

    public function test_check_availability_detects_username_taken_in_pending_registration(): void
    {
        EmployeeRegistration::create([
            'full_name' => 'Calon Karyawan',
            'username' => 'budi_baru',
            'phone' => '081234567002',
            'email' => 'budi@company.com',
            'password' => bcrypt('123456'),
            'pin' => bcrypt('123456'),
            'status' => 'pending',
        ]);

        $response = $this->postJson(route('presensi2.register.check'), [
            'field' => 'username',
            'value' => 'budi_baru',
        ]);

        $response->assertOk();
        $response->assertJson([
            'valid' => true,
            'available' => false,
        ]);
        $this->assertNotEmpty($response->json('suggestions'));
    }

    public function test_check_availability_detects_phone_invalid_or_taken(): void
    {
        Employee::create([
            'branch_id' => 1,
            'name' => 'Karyawan Phone',
            'code' => 'EMP-PH-1',
            'username' => 'user_phone',
            'phone' => '081234567899',
            'email' => 'phone@company.com',
            'is_active' => true,
        ]);

        // 1. Phone tidak valid / bukan nomor orang
        $invalidResp = $this->postJson(route('presensi2.register.check'), [
            'field' => 'phone',
            'value' => '12345',
        ]);
        $invalidResp->assertJson([
            'valid' => false,
            'available' => false,
        ]);

        // 2. Phone sudah ada di database
        $takenResp = $this->postJson(route('presensi2.register.check'), [
            'field' => 'phone',
            'value' => '081234567899',
        ]);
        $takenResp->assertJson([
            'valid' => true,
            'available' => false,
        ]);

        // 3. Phone valid dan tersedia
        $validResp = $this->postJson(route('presensi2.register.check'), [
            'field' => 'phone',
            'value' => '081299887766',
        ]);
        $validResp->assertJson([
            'valid' => true,
            'available' => true,
        ]);
    }

    public function test_check_availability_detects_email_taken_in_two_points(): void
    {
        Employee::create([
            'branch_id' => 1,
            'name' => 'Karyawan Email',
            'code' => 'EMP-EM-1',
            'username' => 'user_email',
            'phone' => '081200001111',
            'email' => 'terdaftar@company.com',
            'is_active' => true,
        ]);

        // Terdaftar di master employees
        $empEmailResp = $this->postJson(route('presensi2.register.check'), [
            'field' => 'email',
            'value' => 'terdaftar@company.com',
        ]);
        $empEmailResp->assertJson([
            'valid' => true,
            'available' => false,
        ]);

        // Terdaftar di staging pending
        EmployeeRegistration::create([
            'full_name' => 'Pending Email',
            'username' => 'user_pending_em',
            'phone' => '081200002222',
            'email' => 'pending@company.com',
            'password' => bcrypt('123456'),
            'pin' => bcrypt('123456'),
            'status' => 'pending',
        ]);

        $regEmailResp = $this->postJson(route('presensi2.register.check'), [
            'field' => 'email',
            'value' => 'pending@company.com',
        ]);
        $regEmailResp->assertJson([
            'valid' => true,
            'available' => false,
        ]);

        // Tersedia
        $availResp = $this->postJson(route('presensi2.register.check'), [
            'field' => 'email',
            'value' => 'baru@company.com',
        ]);
        $availResp->assertJson([
            'valid' => true,
            'available' => true,
        ]);
    }

    public function test_register_submission_rejects_duplicate_email(): void
    {
        Employee::create([
            'branch_id' => 1,
            'name' => 'Karyawan A',
            'code' => 'EMP-DUP-1',
            'username' => 'karyawan_a',
            'phone' => '081211112222',
            'email' => 'sama@company.com',
            'is_active' => true,
        ]);

        $response = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan B',
            'username' => 'karyawan_b',
            'credential' => '582914',
            'phone' => '081233334444',
            'email' => 'sama@company.com',
        ]);

        $response->assertRedirect(route('presensi2.register'));
        $response->assertSessionHasErrors('email');
    }

    public function test_registration_page_renders_checklist_and_availability_components(): void
    {
        $response = $this->get(route('presensi2.register'));

        $response->assertOk();
        $response->assertSee('Status Data Daftar Ulang');
        $response->assertSee('PIN 6 Digit Angka ATAU Password 8 Karakter');
        $response->assertSee('Daftar / Daftar Ulang');
    }

    public function test_registration_submission_validates_and_reports_missing_fields_one_by_one(): void
    {
        $response = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => '',
            'username' => '',
            'credential' => '',
            'phone' => '',
            'email' => '',
        ]);

        $response->assertRedirect(route('presensi2.register'));
        $response->assertSessionHasErrors(['username', 'credential', 'phone', 'email']);
    }

    public function test_registration_requires_exactly_6_digits_for_numeric_pin(): void
    {
        // 5 digit angka -> ditolak
        $response5 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Baru Pin 5',
            'username' => 'user_pin5',
            'credential' => '12345',
            'phone' => '081234567891',
            'email' => 'pin5@company.com',
        ]);
        $response5->assertSessionHasErrors('credential');

        // 7 digit angka -> ditolak
        $response7 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Baru Pin 7',
            'username' => 'user_pin7',
            'credential' => '1234567',
            'phone' => '081234567892',
            'email' => 'pin7@company.com',
        ]);
        $response7->assertSessionHasErrors('credential');

        // 6 digit angka aman -> diterima
        $response6 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Baru Pin 6',
            'username' => 'user_pin6',
            'credential' => '582914',
            'phone' => '081234567893',
            'email' => 'pin6@company.com',
        ]);
        $response6->assertRedirect(route('presensi2.login'));
        $response6->assertSessionHas('success');
    }

    public function test_registration_requires_at_least_8_characters_for_alphanumeric_password(): void
    {
        // 7 karakter huruf/angka -> ditolak
        $response7 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Password Pendek',
            'username' => 'user_pass7',
            'credential' => 'Pass123',
            'phone' => '081234567894',
            'email' => 'pass7@company.com',
        ]);
        $response7->assertSessionHasErrors('credential');

        // 8 karakter huruf dan angka -> diterima
        $response8 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Password Pas',
            'username' => 'user_pass8',
            'credential' => 'Pass1234',
            'phone' => '081234567895',
            'email' => 'pass8@company.com',
        ]);
        $response8->assertRedirect(route('presensi2.login'));
        $response8->assertSessionHas('success');
    }

    public function test_registration_validates_confirm_credential_when_provided(): void
    {
        // Konfirmasi tidak cocok -> ditolak
        $responseMismatch = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Mismatch',
            'username' => 'user_mismatch',
            'credential' => '582914',
            'confirm_credential' => '984215',
            'phone' => '081234567896',
            'email' => 'mismatch@company.com',
        ]);
        $responseMismatch->assertSessionHasErrors('confirm_credential');

        // Konfirmasi cocok -> diterima
        $responseMatch = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Match',
            'username' => 'user_match',
            'credential' => '582914',
            'confirm_credential' => '582914',
            'phone' => '081234567897',
            'email' => 'match@company.com',
        ]);
        $responseMatch->assertRedirect(route('presensi2.login'));
        $responseMatch->assertSessionHas('success');
    }

    public function test_registration_rejects_weak_and_common_pin(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        $weakPins = [
            '123456', // berurutan naik
            '654321', // berurutan turun
            '111111', // kembar semua
            '000000', // kembar semua
            '121212', // pola berulang 2 digit
            '123123', // pola berulang 3 digit
        ];

        foreach ($weakPins as $idx => $weakPin) {
            $response = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
                'full_name' => 'Calon Karyawan Lemah '.$idx,
                'username' => 'user_weak_'.$idx,
                'credential' => $weakPin,
                'phone' => '0812345678'.str_pad((string) $idx, 2, '0', STR_PAD_LEFT),
                'email' => 'weak'.$idx.'@company.com',
            ]);
            $response->assertSessionHasErrors('credential');
        }

        // PIN acak yang kuat -> diterima
        $responseStrong = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Calon Karyawan Kuat',
            'username' => 'user_strong_pin',
            'credential' => '582914',
            'phone' => '081234567888',
            'email' => 'strong@company.com',
        ]);
        $responseStrong->assertRedirect(route('presensi2.login'));
        $responseStrong->assertSessionHas('success');
    }

    public function test_registration_silently_drops_spambot_filling_honeypot_field(): void
    {
        $response = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Spam Bot Automatic',
            'username' => 'spambot_user',
            'credential' => '582914',
            'phone' => '081299990000',
            'email' => 'spambot@spammer.org',
            'honeypot_website' => 'https://spam-site-promo.com',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        // Pastikan tidak ada data bot yang tersimpan di staging table maupun master employees
        $this->assertDatabaseMissing('employee_registrations', [
            'username' => 'spambot_user',
        ]);
        $this->assertDatabaseMissing('employees', [
            'username' => 'spambot_user',
        ]);
    }

    public function test_registration_displays_and_flashes_estimated_verification_time(): void
    {
        // 1. Pada form registrasi terlihat informasi estimasi waktu
        $pageResponse = $this->get(route('presensi2.register'));
        $pageResponse->assertSee('1x24 jam kerja');

        // 2. Saat registrasi berhasil, session memuat data estimasi verifikasi
        $regResponse = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Karyawan Baru Terencana',
            'username' => 'karyawan_terencana',
            'credential' => '582914',
            'confirm_credential' => '582914',
            'phone' => '081233445566',
            'email' => 'terencana@company.com',
        ]);

        $regResponse->assertRedirect(route('presensi2.login'));
        $regResponse->assertSessionHas('registration_submitted');
        $regInfo = session('registration_submitted');
        $this->assertEquals('karyawan_terencana', $regInfo['username']);
        $this->assertStringContainsString('1x24 Jam Kerja', $regInfo['estimated_time']);

        // 3. Halaman login menampilkan kartu estimasi waktu verifikasi
        $loginResponse = $this->get(route('presensi2.login'));
        $loginResponse->assertSee('Estimasi Waktu Verifikasi');
        $loginResponse->assertSee('1x24 Jam Kerja');
    }

    public function test_views_render_dynamic_password_strength_meter(): void
    {
        // 1. Pada halaman registrasi
        $regResponse = $this->get(route('presensi2.register'));
        $regResponse->assertOk();
        $regResponse->assertSee('password-meter-container');
        $regResponse->assertSee('password-strength-bar');
        $regResponse->assertSee('Kekuatan Password');

        // 2. Pada halaman lupa password
        $fpResponse = $this->get(route('presensi2.forgot-password'));
        $fpResponse->assertOk();
        $fpResponse->assertSee('password-meter-container');
        $fpResponse->assertSee('password-strength-bar');
        $fpResponse->assertSee('Kekuatan Password');
    }

    public function test_check_status_feature_workflow(): void
    {
        // 1. Kunjungan halaman cek status
        $page = $this->get(route('presensi2.check-status'));
        $page->assertOk();
        $page->assertSee('Cek Status Pengajuan');

        // 2. Query saat data tidak ada (not found)
        $respNotFound = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'tidak_ada_user_123',
        ]);
        $respNotFound->assertOk();
        $respNotFound->assertSee('Tidak Ditemukan');

        // 3. Query saat data berstatus PENDING
        EmployeeRegistration::create([
            'full_name' => 'Calon Pending Cek',
            'username' => 'user_pending_cek',
            'phone' => '081244556677',
            'email' => 'pending_cek@company.com',
            'password' => bcrypt('582914'),
            'pin' => bcrypt('582914'),
            'status' => 'pending',
        ]);

        $respPending = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'user_pending_cek',
        ]);
        $respPending->assertOk();
        $respPending->assertSee('Menunggu Verifikasi');
        $respPending->assertSee('1x24 Jam Kerja');

        // 4. Query saat data berstatus REJECTED
        EmployeeRegistration::create([
            'full_name' => 'Calon Rejected Cek',
            'username' => 'user_rejected_cek',
            'phone' => '081277889900',
            'email' => 'rejected_cek@company.com',
            'password' => bcrypt('582914'),
            'pin' => bcrypt('582914'),
            'status' => 'rejected',
            'rejection_reason' => 'Foto identitas buram.',
        ]);

        $respRejected = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'user_rejected_cek',
        ]);
        $respRejected->assertOk();
        $respRejected->assertSee('Perlu Perbaikan / Ditolak');
        $respRejected->assertSee('Foto identitas buram.');

        // 5. Query saat data sudah aktif di master employees
        Employee::create([
            'branch_id' => 1,
            'name' => 'Karyawan Aktif Cek',
            'code' => 'EMP-AKTIF-CEK',
            'username' => 'user_aktif_cek',
            'phone' => '081288990011',
            'is_active' => true,
        ]);

        $respActive = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'user_aktif_cek',
        ]);
        $respActive->assertOk();
        $respActive->assertSee('Aktif & Disetujui');

        // 6. Response via JSON API
        $respJson = $this->postJson(route('presensi2.check-status.query'), [
            'keyword' => 'user_pending_cek',
        ]);
        $respJson->assertOk();
        $respJson->assertJson([
            'found' => true,
            'status' => 'pending',
            'username' => 'user_pending_cek',
        ]);
    }

    public function test_pending_registration_expires_after_3_days_allowing_reregistration(): void
    {
        $oldRegistration = EmployeeRegistration::create([
            'full_name' => 'Budi Lama',
            'username' => 'budi_lama',
            'phone' => '081299887766',
            'email' => 'budi_lama@example.com',
            'password' => bcrypt('582914'),
            'pin' => bcrypt('582914'),
            'status' => 'pending',
        ]);

        // Manipulasi tanggal pendaftaran menjadi 4 hari yang lalu (> 3 hari)
        $oldRegistration->created_at = now()->subDays(4);
        $oldRegistration->save();

        $this->assertTrue($oldRegistration->isExpired(3));

        // 1. Cek availability: Harus available karena pendaftaran lama sudah kedaluwarsa (> 3 hari)
        $responseAvail = $this->postJson(route('presensi2.register.check'), [
            'field' => 'username',
            'value' => 'budi_lama',
        ]);
        $responseAvail->assertOk();
        $responseAvail->assertJson([
            'valid' => true,
            'available' => true,
        ]);

        // 2. Cek status pendaftaran mandiri: Menampilkan status expired
        $respStatus = $this->post(route('presensi2.check-status.query'), [
            'keyword' => 'budi_lama',
        ]);
        $respStatus->assertOk();
        $respStatus->assertSee('Kedaluwarsa');
        $respStatus->assertSee('Daftar Ulang Sekarang');

        // 3. Jalankan artisan command prune-expired
        $this->artisan('registrations:prune-expired')
            ->expectsOutputToContain('Berhasil memperbarui 1 pendaftaran yang kedaluwarsa (> 3 hari).')
            ->assertSuccessful();

        $this->assertDatabaseHas('employee_registrations', [
            'id' => $oldRegistration->id,
            'status' => 'expired',
        ]);

        // 4. Submit registrasi ulang dengan username yang sama berhasil
        $respRegister = $this->post(route('presensi2.register.post'), [
            'full_name' => 'Budi Pendaftaran Baru',
            'username' => 'budi_lama',
            'credential' => '719382',
            'confirm_credential' => '719382',
            'phone' => '081299887766',
            'email' => 'budi_lama@example.com',
        ]);

        $respRegister->assertRedirect(route('presensi2.login'));
        $respRegister->assertSessionHas('success');
    }

    public function test_registration_attempt_tracker_locks_out_after_three_repeated_conflict_failures(): void
    {
        // Siapkan karyawan aktif
        Employee::create([
            'branch_id' => 1,
            'name' => 'Target Karyawan',
            'code' => 'EMP-TRG-1',
            'username' => 'targetuser',
            'phone' => '081234567999',
            'email' => 'target@company.com',
            'is_active' => true,
        ]);

        RateLimiter::clear('reg_attempt_ip:127.0.0.1');

        // Percobaan 1 gagal
        $res1 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Imposter',
            'username' => 'targetuser',
            'credential' => '719382',
            'confirm_credential' => '719382',
            'phone' => '081234567111',
            'email' => 'imposter1@example.com',
        ]);
        $res1->assertSessionHasErrors(['username']);
        $this->assertStringContainsString('Kesempatan tersisa: 2x', session('errors')->first('username'));

        // Percobaan 2 gagal
        $res2 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Imposter',
            'username' => 'targetuser',
            'credential' => '719382',
            'confirm_credential' => '719382',
            'phone' => '081234567222',
            'email' => 'imposter2@example.com',
        ]);
        $res2->assertSessionHasErrors(['username']);
        $this->assertStringContainsString('Kesempatan tersisa: 1x', session('errors')->first('username'));

        // Percobaan 3 gagal -> Kunci pendaftaran dan simpan session lockout_seconds
        $res3 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Imposter',
            'username' => 'targetuser',
            'credential' => '719382',
            'confirm_credential' => '719382',
            'phone' => '081234567333',
            'email' => 'imposter3@example.com',
        ]);
        $res3->assertSessionHas('lockout_seconds');
        $res3->assertSessionHasErrors(['registration']);
        $this->assertStringContainsString('dibekukan selama 5 menit', session('errors')->first('registration'));

        // Percobaan ke-4 langsung ditolak oleh RateLimiter sebelum validasi
        $res4 = $this->from(route('presensi2.register'))->post(route('presensi2.register.post'), [
            'full_name' => 'Orang Lain',
            'username' => 'userbebas',
            'credential' => '719382',
            'confirm_credential' => '719382',
            'phone' => '081234567444',
            'email' => 'bebas@example.com',
        ]);
        $res4->assertSessionHas('lockout_seconds');
        $res4->assertSessionHasErrors(['registration']);
    }

    public function test_early_warning_service_is_invoked_on_registration_breach_and_approval(): void
    {
        $mockService = $this->createMock(EarlyWarningNotificationService::class);

        // Pastikan dipanggil saat breach 3x
        $mockService->expects($this->once())
            ->method('sendRegistrationBreachAlert')
            ->with($this->callback(function (array $details) {
                return $details['target_name'] === 'Target Staf'
                    && $details['target_phone'] === '081234567777'
                    && $details['duration_minutes'] === 5;
            }))
            ->willReturn(['whatsapp' => true, 'telegram' => true]);

        // Pastikan dipanggil saat registrasi baru sukses disubmit
        $mockService->expects($this->once())
            ->method('sendNewRegistrationApprovalAlert')
            ->with(
                $this->equalTo('Budi Baru Sukses'),
                $this->equalTo('budisukses'),
                $this->equalTo('081234567888')
            )
            ->willReturn(true);

        $this->app->instance(EarlyWarningNotificationService::class, $mockService);

        Employee::create([
            'branch_id' => 1,
            'name' => 'Target Staf',
            'code' => 'EMP-STF-1',
            'username' => 'targetstaf',
            'phone' => '081234567777',
            'email' => 'targetstaf@company.com',
            'is_active' => true,
        ]);

        RateLimiter::clear('reg_attempt_ip:127.0.0.1');

        // Picu 3 kali kegagalan menggunakan targetstaf dengan payload lengkap
        $this->post(route('presensi2.register.post'), ['full_name' => 'Budi X', 'username' => 'targetstaf', 'credential' => '719382', 'confirm_credential' => '719382', 'phone' => '081299990001', 'email' => 'x1@company.com']);
        $this->post(route('presensi2.register.post'), ['full_name' => 'Budi X', 'username' => 'targetstaf', 'credential' => '719382', 'confirm_credential' => '719382', 'phone' => '081299990002', 'email' => 'x2@company.com']);
        $this->post(route('presensi2.register.post'), ['full_name' => 'Budi X', 'username' => 'targetstaf', 'credential' => '719382', 'confirm_credential' => '719382', 'phone' => '081299990003', 'email' => 'x3@company.com']);

        // Bersihkan kunci untuk menguji pendaftaran sukses
        RateLimiter::clear('reg_attempt_ip:127.0.0.1');

        $resSuccess = $this->post(route('presensi2.register.post'), [
            'full_name' => 'Budi Baru Sukses',
            'username' => 'budisukses',
            'credential' => '719382',
            'confirm_credential' => '719382',
            'phone' => '081234567888',
            'email' => 'budisukses@company.com',
        ]);

        $resSuccess->assertRedirect(route('presensi2.login'));
        $resSuccess->assertSessionHas('success');
    }
}
