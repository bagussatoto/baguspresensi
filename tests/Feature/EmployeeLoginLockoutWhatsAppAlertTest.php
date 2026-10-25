<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class EmployeeLoginLockoutWhatsAppAlertTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Branch::create([
            'id' => 1,
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius' => 100,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => 1,
            'name' => 'Dimas Wahyu Pratama',
            'code' => 'MGK-014',
            'username' => 'dimas_wahyu',
            'pin' => Hash::make('123456'),
            'password' => Hash::make('password123'),
            'phone' => '081298765499',
            'email' => 'dimas@mgk.co.id',
            'is_active' => true,
        ]);
    }

    public function test_three_failed_login_attempts_triggers_whatsapp_alert_to_admin_with_format_opsi_a(): void
    {
        config(['services.whatsapp.admin_phone' => '081299887766']);
        config(['services.whatsapp.api_url' => 'https://api.fonnte.com/send']);
        config(['services.whatsapp.api_token' => 'dummy_token_123']);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        // Percobaan 1: Gagal
        $res1 = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'dimas_wahyu',
            'credential' => 'wrong_pin_1',
        ]);
        $res1->assertSessionHasErrors(['credential']);

        // Percobaan 2: Gagal
        $res2 = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'dimas_wahyu',
            'credential' => 'wrong_pin_2',
        ]);
        $res2->assertSessionHasErrors(['credential']);

        // Percobaan 3: Gagal -> Lockout & Trigger Early Warning WhatsApp
        $res3 = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'dimas_wahyu',
            'credential' => 'wrong_pin_3',
        ]);
        $res3->assertSessionHasErrors(['credential']);
        $res3->assertSessionHas('lockout_seconds');

        // Verifikasi HTTP request terkirim ke WhatsApp Admin dengan format Opsi A
        Http::assertSent(function ($request) {
            $body = $request->data();
            $target = $body['target'] ?? '';
            $message = $body['message'] ?? '';

            // Pastikan terkirim ke nomor WhatsApp Admin
            if ($target === '6281299887766') {
                return str_contains($message, 'SECURITY ALERT: PERCOBAAN TIDAK SAH 3X')
                    && str_contains($message, 'Dimas Wahyu Pratama')
                    && str_contains($message, 'dimas_wahyu')
                    && str_contains($message, 'admin/karyawan-unlock');
            }

            return false;
        });

        // Verifikasi AuditLog tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'portal_login_lockout',
            'auditable_type' => Employee::class,
            'auditable_id' => $this->employee->id,
        ]);
    }

    public function test_admin_can_view_unlock_approval_page_with_valid_hmac_token(): void
    {
        $admin = User::factory()->create();
        $ip = '192.168.1.100';
        $expires = now()->addMinutes(30)->timestamp;
        $token = hash_hmac('sha256', $this->employee->id.'|'.$ip.'|'.$expires, config('app.key'));

        $url = route('admin.karyawan.unlock.show', [
            'employee' => $this->employee->id,
            'ip' => $ip,
            'expires' => $expires,
            'token' => $token,
        ]);

        $response = $this->actingAs($admin)->get($url);

        $response->assertOk();
        $response->assertSee('Otorisasi Pembukaan Blokir Akun');
        $response->assertSee('Dimas Wahyu Pratama');
        $response->assertSee('dimas_wahyu');
        $response->assertSee('SETUJUI &amp; BUKA BLOKIR SEKARANG', false);
    }

    public function test_unlock_approval_rejects_expired_or_tampered_token(): void
    {
        $admin = User::factory()->create();
        $ip = '192.168.1.100';

        // 1. Token dimodifikasi (tampered)
        $tamperedUrl = route('admin.karyawan.unlock.show', [
            'employee' => $this->employee->id,
            'ip' => $ip,
            'expires' => now()->addMinutes(30)->timestamp,
            'token' => 'palsu_dan_salah_hash',
        ]);
        $resTampered = $this->actingAs($admin)->get($tamperedUrl);
        $resTampered->assertOk();
        $resTampered->assertSee('Token Tanda Tangan Tidak Valid');

        // 2. Token kedaluwarsa (expired)
        $expiredTime = now()->subMinute()->timestamp;
        $expiredToken = hash_hmac('sha256', $this->employee->id.'|'.$ip.'|'.$expiredTime, config('app.key'));
        $expiredUrl = route('admin.karyawan.unlock.show', [
            'employee' => $this->employee->id,
            'ip' => $ip,
            'expires' => $expiredTime,
            'token' => $expiredToken,
        ]);
        $resExpired = $this->actingAs($admin)->get($expiredUrl);
        $resExpired->assertOk();
        $resExpired->assertSee('Tautan Approval Telah Kedaluwarsa');
    }

    public function test_admin_can_approve_unlock_and_employee_can_immediately_login(): void
    {
        $admin = User::factory()->create(['name' => 'HRD Manager']);
        $ip = '127.0.0.1';

        // 1. Kunci akun karyawan dengan RateLimiter
        $userKey = 'emp_'.$this->employee->id;
        $throttleKey = 'presensi2_login:'.$userKey.'|'.$ip;
        RateLimiter::hit($throttleKey, 300);
        RateLimiter::hit($throttleKey, 300);
        RateLimiter::hit($throttleKey, 300);
        $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey, 3));

        // 2. Admin melakukan submit approval unlock
        $expires = now()->addMinutes(30)->timestamp;
        $token = hash_hmac('sha256', $this->employee->id.'|'.$ip.'|'.$expires, config('app.key'));

        $processResponse = $this->actingAs($admin)->post(route('admin.karyawan.unlock.process', $this->employee), [
            'ip' => $ip,
            'expires' => $expires,
            'token' => $token,
            'notes' => 'Telah dikonfirmasi langsung oleh HRD via telepon.',
        ]);

        $processResponse->assertRedirect(route('admin.karyawan'));
        $processResponse->assertSessionHas('success');

        // 3. Verifikasi RateLimiter telah di-clear (buka blokir berhasil)
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, 3));

        // 4. Verifikasi AuditLog ISO 27001 mencatat aksi UNLOCKED oleh Admin
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN',
            'auditable_type' => Employee::class,
            'auditable_id' => $this->employee->id,
            'user_id' => $admin->id,
        ]);

        // 5. Karyawan langsung dapat login dengan sukses
        $loginRes = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'dimas_wahyu',
            'credential' => '123456',
        ]);
        $loginRes->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
    }

    public function test_admin_can_view_unlock_incident_index_and_sees_data_master_link(): void
    {
        $admin = User::factory()->create(['name' => 'IT Security Admin']);

        $response = $this->actingAs($admin)->get(route('admin.karyawan.unlock'));

        $response->assertOk();
        $response->assertSee(route('admin.data'));
        $response->assertSee('Buka Data Master ERP (admin/data)');
    }

    public function test_admin_can_view_reset_password_page_and_sees_data_master_link(): void
    {
        $admin = User::factory()->create(['name' => 'HRD Admin']);

        $response = $this->actingAs($admin)->get(route('admin.karyawan.reset-password'));

        $response->assertOk();
        $response->assertSee(route('admin.data'));
        $response->assertSee('Buka Data Master ERP (admin/data)');
    }
}
