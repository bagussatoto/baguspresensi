<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use App\Domains\Employee\Models\Shift;
use App\Models\EmployeeRegistration;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AttendanceWebV2PortalTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus Karyawan',
            'code' => 'EMP-001',
            'position' => 'Operator',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('654321'),
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_v2_login_when_accessing_dashboard(): void
    {
        $response = $this->get(route('presensi2.index'));

        $response->assertRedirect(route('presensi2.login'));
    }

    public function test_can_access_v2_login_page_and_alias_login2(): void
    {
        $response = $this->get(route('presensi2.login'));
        $response->assertOk();
        $response->assertSee('Portal Presensi v2');
        $response->assertSee('name="employee_name"', false);

        $aliasResponse = $this->get('/presensi/login2');
        $aliasResponse->assertOk();
        $aliasResponse->assertSee('Portal Presensi v2');
        $aliasResponse->assertSee('name="employee_name"', false);
    }

    public function test_employee_can_login_by_selecting_from_list(): void
    {
        $response = $this->post(route('presensi2.login.post'), [
            'employee_id' => $this->employee->id,
            'credential' => '654321',
        ]);

        $response->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
    }

    public function test_employee_can_login_with_password(): void
    {
        $response = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'secret123',
        ]);

        $response->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
    }

    public function test_employee_can_login_with_pin(): void
    {
        $response = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => '654321',
        ]);

        $response->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHasErrors(['credential']);
        $this->assertNull(session('presensi2_employee_id'));
    }

    public function test_progressive_lockout_after_repeated_three_failed_attempts(): void
    {
        // 1. Percobaan 1 gagal -> 2 kesempatan tersisa
        $res1 = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass1',
        ]);
        $res1->assertSessionHas('remaining_attempts', 2);

        // 2. Percobaan 2 gagal -> 1 kesempatan tersisa (Peringatan Terakhir)
        $res2 = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass2',
        ]);
        $res2->assertSessionHas('remaining_attempts', 1);

        // 3. Percobaan 3 gagal -> Kunci 1 Menit (60 detik)
        $res3 = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass3',
        ]);
        $res3->assertSessionHas('lockout_seconds', fn ($sec) => in_array($sec, [59, 60], true));

        // Simulasikan 1 menit berlalu dengan membersihkan RateLimiter throttle (tier tetap tersimpan)
        $throttleKey = 'presensi2_login:emp_'.$this->employee->id.'|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // 4. Setelah masa tunggu selesai, mencoba lagi dan salah 3 kali lagi
        $res4 = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass4',
        ]);
        $res4->assertSessionHas('remaining_attempts', 2);

        $res5 = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass5',
        ]);
        $res5->assertSessionHas('remaining_attempts', 1);

        // 5. Percobaan ke-3 pada putaran kedua -> Kunci meningkat menjadi 2 Menit (120 detik)
        $res6 = $this->from(route('presensi2.login'))->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'wrongpass6',
        ]);
        $res6->assertSessionHas('lockout_seconds', fn ($sec) => in_array($sec, [119, 120]));

        // 6. Jika memasukkan password yang benar setelah kuncian reda, status tier dan throttle direset
        RateLimiter::clear($throttleKey);
        $resSuccess = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'EMP-001',
            'credential' => 'secret123',
        ]);
        $resSuccess->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
        $this->assertNull(cache()->get('presensi2_login_tier:emp_'.$this->employee->id.'|127.0.0.1'));
    }

    public function test_early_warning_notification_service_dispatches_alerts_on_lockout(): void
    {
        $mockService = $this->createMock(EarlyWarningNotificationService::class);
        $mockService->expects($this->once())
            ->method('sendLoginLockoutAlert')
            ->with(
                $this->callback(fn ($emp) => $emp && $emp->id === $this->employee->id),
                $this->callback(function (array $details) {
                    return $details['employee_code'] === 'EMP-001'
                        && $details['tier'] === 1
                        && $details['duration_seconds'] === 60;
                })
            )
            ->willReturn(['whatsapp' => true, 'telegram' => true]);

        $this->app->instance(EarlyWarningNotificationService::class, $mockService);

        // Buat 3 kali gagal login
        $this->post(route('presensi2.login.post'), ['employee_code' => 'EMP-001', 'credential' => 'wrong1']);
        $this->post(route('presensi2.login.post'), ['employee_code' => 'EMP-001', 'credential' => 'wrong2']);
        $res = $this->post(route('presensi2.login.post'), ['employee_code' => 'EMP-001', 'credential' => 'wrong3']);

        $res->assertSessionHas('lockout_seconds', 60);
    }

    public function test_v2_login_view_renders_portal_masuk_terpadu_and_help_text(): void
    {
        $response = $this->get(route('presensi2.login'));
        $response->assertOk();
        $response->assertSee('Portal Login');
        $response->assertSee('Maya Graha Kencana');
        $response->assertSee('Identitas Karyawan');
        $response->assertSee('Tulis Nama Karyawan');
        $response->assertSee('Password atau PIN 6-Digit');
    }

    public function test_employee_can_login_by_typing_employee_name(): void
    {
        $response = $this->post(route('presensi2.login.post'), [
            'employee_name' => $this->employee->name,
            'credential' => '654321',
        ]);

        $response->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
    }

    public function test_admin_can_login_through_v2_portal_with_email_and_password(): void
    {
        $admin = User::create([
            'name' => 'Admin MayaNet',
            'email' => 'admin@mayanet.com',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->post(route('presensi2.login.post'), [
            'employee_code' => 'admin@mayanet.com',
            'credential' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_employee_can_login_with_direct_navicat_plaintext_pin(): void
    {
        // Simulasi jika user memasukkan PIN biasa di Navicat tanpa bcrypt
        $this->employee->update(['pin' => '998877']);

        $response = $this->post(route('presensi2.login.post'), [
            'employee_code' => $this->employee->code,
            'credential' => '998877',
        ]);

        $response->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));

        // Pastikan sekarang ter-rehash dengan aman
        $this->employee->refresh();
        $this->assertTrue(Hash::check('998877', $this->employee->pin));
    }

    public function test_employee_can_access_dashboard_when_authenticated(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.index'));

        $response->assertOk();
        $response->assertSee('Bagus Karyawan');
    }

    public function test_employee_can_clock_in_via_v2_credential_verification(): void
    {
        Carbon::setTestNow('2026-10-13 07:05:00');

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.clock-in'), [
                'credential' => '654321',
                'shift_id' => $this->shift->id,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
        ]);
        $record = AttendanceRecord::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals('2026-10-13', Carbon::parse($record->work_date)->toDateString());

        Carbon::setTestNow();
    }

    public function test_employee_can_clock_out_via_v2_credential_verification(): void
    {
        Carbon::setTestNow('2026-10-13 07:00:00');

        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => '2026-10-13',
            'clock_in_at' => Carbon::now(),
            'clock_in_lat' => -6.200000,
            'clock_in_lng' => 106.816666,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::AdminManual,
        ]);

        Carbon::setTestNow('2026-10-13 15:05:00');

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.clock-out'), [
                'credential' => 'secret123',
                'shift_id' => $this->shift->id,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $record = AttendanceRecord::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($record->clock_out_at);

        Carbon::setTestNow();
    }

    public function test_employee_can_clock_in_and_out_directly_without_credential(): void
    {
        Carbon::setTestNow('2026-10-13 07:05:00');

        // Clock In langsung tanpa password/pin
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.clock-in'), [
                'shift_id' => $this->shift->id,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $record = AttendanceRecord::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($record);

        // Clock Out langsung tanpa password/pin
        Carbon::setTestNow('2026-10-13 15:05:00');

        $outResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.clock-out'), [
                'shift_id' => $this->shift->id,
            ]);

        $outResponse->assertOk();
        $outResponse->assertJson([
            'success' => true,
        ]);

        $record->refresh();
        $this->assertNotNull($record->clock_out_at);

        Carbon::setTestNow();
    }

    public function test_employee_can_view_riwayat(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.riwayat'));

        $response->assertOk();
        $response->assertSee('Riwayat Presensi');
    }

    public function test_employee_can_logout(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.logout'));

        $response->assertRedirect(route('presensi2.login'));
        $this->assertNull(session('presensi2_employee_id'));
    }

    public function test_can_access_register_page(): void
    {
        $response = $this->get(route('presensi2.register'));
        $response->assertOk();
        $response->assertSee('Daftar');
        $response->assertSee('Username');
        $response->assertSee('PIN atau Password');
        $response->assertSee('Email');
        $response->assertSee('Nomor HP');

        $aliasResponse = $this->get('/presensi/daftar2');
        $aliasResponse->assertOk();
    }

    public function test_new_employee_can_register(): void
    {
        $response = $this->post(route('presensi2.register.post'), [
            'username' => 'karyawan_baru',
            'credential' => '889900',
            'email' => 'baru@wisata.id',
            'phone' => '081299998888',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('employee_registrations', [
            'username' => 'karyawan_baru',
            'email' => 'baru@wisata.id',
            'phone' => '081299998888',
            'status' => 'pending',
        ]);

        $reg = EmployeeRegistration::where('username', 'karyawan_baru')->first();
        $this->assertNotNull($reg);
        $this->assertTrue(Hash::check('889900', $reg->pin));
    }

    public function test_existing_employee_email_cannot_be_re_registered(): void
    {
        $this->employee->update(['email' => 'bagus_old@wisata.id', 'username' => 'bagus_old']);

        $response = $this->post(route('presensi2.register.post'), [
            'username' => 'bagus_updated',
            'credential' => '582914',
            'email' => 'bagus_old@wisata.id',
            'phone' => '089988776655',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_new_employee_can_register_with_custom_branch(): void
    {
        $branch2 = Branch::create([
            'name' => 'Maya Graha Surabaya',
            'code' => 'CBG-SBY',
            'latitude' => -7.2575,
            'longitude' => 112.7521,
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $response = $this->post(route('presensi2.register.post'), [
            'username' => 'staff_surabaya',
            'credential' => '334455',
            'email' => 'sby@wisata.id',
            'phone' => '081234567899',
            'branch_id' => $branch2->id,
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $reg = EmployeeRegistration::where('username', 'staff_surabaya')->first();
        $this->assertNotNull($reg);
        $this->assertEquals('pending', $reg->status);
    }

    public function test_register_creates_pending_registration_record(): void
    {
        $response = $this->post(route('presensi2.register.post'), [
            'username' => 'user_baru_unik',
            'credential' => '556677',
            'email' => 'unik@wisata.id',
            'phone' => '087711223344',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $created = EmployeeRegistration::where('username', 'user_baru_unik')->first();
        $this->assertNotNull($created);
        $this->assertEquals('pending', $created->status);
    }

    public function test_login_page_renders_lupa_password_link(): void
    {
        $response = $this->get(route('presensi2.login'));
        $response->assertOk();
        $response->assertSee('Lupa Password?');
    }

    public function test_can_access_forgot_password_page(): void
    {
        $response = $this->get(route('presensi2.forgot-password'));
        $response->assertOk();
        $response->assertSee('Lupa Password');
        $response->assertSee('Tulis Nama Karyawan Anda');
        $response->assertSee('Password atau PIN 6-Digit Lama');
        $response->assertDontSee('<select');

        $aliasResponse = $this->get('/presensi/lupa-password');
        $aliasResponse->assertOk();
        $aliasResponse->assertSee('Lupa Password');
    }

    public function test_employee_can_reset_password_through_forgot_password(): void
    {
        $response = $this->post(route('presensi2.forgot-password.post'), [
            'employee_id' => $this->employee->id,
            'old_credential' => '654321',
            'new_credential' => 'newpass654',
            'confirm_credential' => 'newpass654',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        // Dalam alur Maker-Checker, permohonan tersimpan di antrean pending
        $this->assertDatabaseHas('password_reset_requests', [
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        $resetReq = PasswordResetRequest::where('employee_id', $this->employee->id)->latest()->first();
        $this->assertNotNull($resetReq);

        // Sebelum disetujui admin, kredensial di tabel employees belum berubah
        $this->employee->refresh();
        $this->assertFalse(Hash::check('newpass654', $this->employee->pin));

        // Setelah disetujui admin
        $admin = User::first() ?? User::create(['name' => 'Admin Test', 'email' => 'adm@test.com', 'password' => 'secret']);
        $this->actingAs($admin)->post(route('admin.karyawan.reset-password.approve', $resetReq->id));

        $this->employee->refresh();
        $this->assertTrue(Hash::check('newpass654', $this->employee->pin));
        $this->assertTrue(Hash::check('newpass654', $this->employee->password));

        // Pastikan sekarang bisa login dengan PIN baru
        $loginResponse = $this->post(route('presensi2.login.post'), [
            'employee_id' => $this->employee->id,
            'credential' => 'newpass654',
        ]);
        $loginResponse->assertRedirect(route('presensi2.index'));
        $this->assertEquals($this->employee->id, session('presensi2_employee_id'));
    }

    public function test_employee_can_reset_password_through_forgot_password_with_manual_name(): void
    {
        $response = $this->post(route('presensi2.forgot-password.post'), [
            'employee_name' => $this->employee->name,
            'old_credential' => '654321',
            'new_credential' => 'newpass789',
            'confirm_credential' => 'newpass789',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('password_reset_requests', [
            'employee_id' => $this->employee->id,
            'status' => 'pending',
        ]);

        $resetReq = PasswordResetRequest::where('employee_id', $this->employee->id)->latest()->first();
        $admin = User::first() ?? User::create(['name' => 'Admin Test', 'email' => 'adm@test.com', 'password' => 'secret']);
        $this->actingAs($admin)->post(route('admin.karyawan.reset-password.approve', $resetReq->id));

        $this->employee->refresh();
        $this->assertTrue(Hash::check('newpass789', $this->employee->pin));
        $this->assertTrue(Hash::check('newpass789', $this->employee->password));
    }

    public function test_employee_cannot_reset_password_with_wrong_old_credential(): void
    {
        $response = $this->post(route('presensi2.forgot-password.post'), [
            'employee_name' => $this->employee->name,
            'old_credential' => 'wrongpass999',
            'new_credential' => 'newpass789',
            'confirm_credential' => 'newpass789',
        ]);

        $response->assertSessionHasErrors('old_credential');
        $this->employee->refresh();
        $this->assertTrue(Hash::check('654321', $this->employee->pin));
    }

    public function test_root_url_and_bagus_alias_redirect_to_presensi2(): void
    {
        $responseRoot = $this->get('/');
        $responseRoot->assertRedirect(route('presensi2.index'));

        $responseBagus = $this->get('/bagus');
        $responseBagus->assertRedirect(route('presensi2.index'));
    }

    public function test_login_page_displays_all_four_new_quick_buttons(): void
    {
        $response = $this->get(route('presensi2.login'));
        $response->assertOk();
        $response->assertSee('General Cleaning (GC)');
        $response->assertSee('Meeting');
        $response->assertSee('Presnesi BO/Training');
        $response->assertSee('AMBIL STOK');
        $response->assertSee(route('presensi2.layanan.login', ['service' => 'gc']));
        $response->assertSee(route('presensi2.layanan.login', ['service' => 'meeting']));
        $response->assertSee(route('presensi2.layanan.login', ['service' => 'bo-training']));
        $response->assertSee(route('presensi2.layanan.login', ['service' => 'ambil-stok']));
    }

    public function test_guest_is_redirected_to_dedicated_layanan_login_when_accessing_services(): void
    {
        $services = ['gc', 'meeting', 'bo-training', 'ambil-stok'];
        foreach ($services as $service) {
            $response = $this->get(route('presensi2.'.$service));
            $response->assertRedirect(route('presensi2.layanan.login', ['service' => $service]));
        }
    }

    public function test_can_access_dedicated_layanan_login_portal(): void
    {
        $response = $this->get(route('presensi2.layanan.login', ['service' => 'gc']));
        $response->assertOk();
        $response->assertSee('Portal Login Layanan & Presensi Tambahan');
        $response->assertSee('General Cleaning');
        $response->assertSee('Meeting');
        $response->assertSee('BO / Training');
        $response->assertSee('AMBIL STOK');
        $response->assertSee('name="employee_name"', false);
    }

    public function test_employee_can_login_through_dedicated_layanan_portal(): void
    {
        $response = $this->post(route('presensi2.layanan.login.post'), [
            'service' => 'gc',
            'employee_id' => $this->employee->id,
            'credential' => 'secret123',
        ]);

        $response->assertRedirect(route('presensi2.gc'));
        $this->assertEquals($this->employee->id, session('presensi2_layanan_employee_id'));
    }

    public function test_can_view_and_submit_general_cleaning_presensi_with_valid_credentials(): void
    {
        $response = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->get(route('presensi2.gc'));
        $response->assertOk();
        $response->assertSee('General Cleaning (GC)');
        $response->assertSee('Waktu Presensi GC (WIB)');
        $response->assertSee('Bagus Karyawan');
        $response->assertSee('Password atau PIN 6-Digit Anda');

        // Test submission with invalid credential fails
        $failResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.gc.store'), [
                'credential' => 'wrongpass',
                'title' => 'Pembersihan Debu PC Station & Peripheral',
            ]);
        $failResponse->assertSessionHasErrors(['credential']);

        // Test submission with valid password / PIN
        $postResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.gc.store'), [
                'credential' => 'secret123',
                'title' => 'Pembersihan Debu PC Station & Peripheral',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'duration_minutes' => 60,
                'notes' => 'Sanitasi seluruh mouse, keyboard, dan layar PC.',
            ]);

        $postResponse->assertRedirect(route('presensi2.gc'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('activity_attendances', [
            'activity_type' => 'gc',
            'employee_id' => $this->employee->id,
            'title' => 'Pembersihan Debu PC Station & Peripheral',
            'duration_minutes' => 60,
        ]);
    }

    public function test_can_view_and_submit_meeting_presensi_with_valid_credentials(): void
    {
        $response = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->get(route('presensi2.meeting'));
        $response->assertOk();
        $response->assertSee('Presensi Meeting');
        $response->assertSee('Waktu Presensi Meeting (WIB)');
        $response->assertSee('Bagus Karyawan');

        // Test submission with invalid credential fails
        $failResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.meeting.store'), [
                'credential' => 'wrongpin',
                'title' => 'Briefing Operasional Shift & Evaluasi Kasir',
            ]);
        $failResponse->assertSessionHasErrors(['credential']);

        $postResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.meeting.store'), [
                'credential' => '654321', // PIN 6 digit
                'title' => 'Briefing Operasional Shift & Evaluasi Kasir',
                'start_time' => '07:30',
                'end_time' => '08:00',
                'duration_minutes' => 30,
                'notes' => 'Pembahasan SOP penerimaan pesanan dan pelayanan ramah.',
            ]);

        $postResponse->assertRedirect(route('presensi2.meeting'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('activity_attendances', [
            'activity_type' => 'meeting',
            'employee_id' => $this->employee->id,
            'title' => 'Briefing Operasional Shift & Evaluasi Kasir',
            'duration_minutes' => 30,
        ]);
    }

    public function test_can_view_and_submit_bo_and_training_presensi_with_valid_credentials(): void
    {
        $response = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->get(route('presensi2.bo-training'));
        $response->assertOk();
        $response->assertSee('Presnesi BO/Training');
        $response->assertSee('Waktu Presensi BO / Training (WIB)');
        $response->assertSee('Bagus Karyawan');

        // Test submission with invalid credential fails
        $failResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.bo-training.store'), [
                'credential' => 'invalid-cred',
                'title' => 'Workshop SOP Layanan Hospitality',
            ]);
        $failResponse->assertSessionHasErrors(['credential']);

        $postResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.bo-training.store'), [
                'credential' => 'secret123',
                'sub_type' => 'pelatihan',
                'title' => 'Workshop SOP Layanan Hospitality & Troubleshooting POS',
                'start_time' => '13:00',
                'end_time' => '15:00',
                'duration_minutes' => 120,
                'notes' => 'Staf menguasai alur pembatalan transaksi dan rekap kasir.',
            ]);

        $postResponse->assertRedirect(route('presensi2.bo-training'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('activity_attendances', [
            'activity_type' => 'pelatihan',
            'employee_id' => $this->employee->id,
            'title' => 'Workshop SOP Layanan Hospitality & Troubleshooting POS',
            'duration_minutes' => 120,
        ]);
    }

    public function test_can_view_and_submit_ambil_stok_with_valid_credentials(): void
    {
        $response = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->get(route('presensi2.ambil-stok'));
        $response->assertOk();
        $response->assertSee('AMBIL STOK');
        $response->assertSee('Waktu Operasional Ambil Stok (WIB)');
        $response->assertSee('Bagus Karyawan');

        // Test submission with invalid credential fails
        $failResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.ambil-stok.store'), [
                'credential' => 'wrong',
                'item_category' => 'Kertas & ATK',
                'item_name' => 'Kertas Print A4 70gr (1 Rim)',
                'qty_requested' => 2,
                'unit' => 'Rim',
            ]);
        $failResponse->assertSessionHasErrors(['credential']);

        $postResponse = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->post(route('presensi2.ambil-stok.store'), [
                'credential' => '654321',
                'item_category' => 'Kertas & ATK',
                'item_name' => 'Kertas Print A4 70gr (1 Rim)',
                'qty_requested' => 2,
                'unit' => 'Rim',
                'urgency' => 'high',
                'notes' => 'Diambil untuk persediaan mesin printer kasir depan.',
            ]);

        $postResponse->assertRedirect(route('presensi2.ambil-stok'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('shift_stock_requests', [
            'employee_id' => $this->employee->id,
            'item_name' => 'Kertas Print A4 70gr (1 Rim)',
            'qty_requested' => 2,
            'unit' => 'Rim',
        ]);
    }

    public function test_employee_can_logout_from_layanan_portal(): void
    {
        $response = $this->withSession(['presensi2_layanan_employee_id' => $this->employee->id])
            ->get(route('presensi2.layanan.logout', ['service' => 'gc']));

        $response->assertRedirect(route('presensi2.layanan.login', ['service' => 'gc']));
        $this->assertNull(session('presensi2_layanan_employee_id'));
    }

    public function test_guest_cannot_access_rekap_page(): void
    {
        $response = $this->get(route('presensi2.rekap'));
        $response->assertRedirect(route('presensi2.login'));
    }

    public function test_authenticated_employee_can_access_rekap_page(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.rekap'));

        $response->assertOk();
        $response->assertSee('Laporan Rekapitulasi Penjualan');
        $response->assertSee('Total Omset Kotor Shift');
        $response->assertSee('Rekonsiliasi Kas Laci');
        $response->assertSee('Buka Kasir Penjualan Shift');
    }
}
