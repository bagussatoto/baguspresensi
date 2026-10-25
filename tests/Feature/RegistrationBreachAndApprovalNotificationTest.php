<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
