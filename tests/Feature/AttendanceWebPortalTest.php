<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Enums\LeaveType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AttendanceWebPortalTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift;

    private string $sampleBase64;

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
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        // 1x1 transparent PNG in base64
        $this->sampleBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get(route('presensi.index'));

        $response->assertRedirect(route('presensi.login'));
    }

    public function test_employee_can_view_login_page_and_authenticate(): void
    {
        $response = $this->get(route('presensi.login'));
        $response->assertStatus(200);
        $response->assertSee('Portal Presensi Karyawan');
        $response->assertSee($this->employee->name);

        $loginResponse = $this->post(route('presensi.login.post'), [
            'employee_id' => $this->employee->id,
        ]);

        $loginResponse->assertRedirect(route('presensi.index'));
        $this->assertEquals($this->employee->id, session('presensi_employee_id'));
    }

    public function test_authenticated_employee_can_view_dashboard_and_scan_pages(): void
    {
        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.index'));

        $response->assertStatus(200);
        $response->assertSee($this->employee->name);
        $response->assertSee('Belum Melakukan Clock-In');

        $scanResponse = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.scan'));

        $scanResponse->assertStatus(200);
        $scanResponse->assertSee($this->branch->name);
        $scanResponse->assertSee('Presensi Masuk (Clock-In)');
    }

    public function test_employee_can_clock_in_via_web_portal_within_geofence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 07:05:00'));

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->postJson(route('presensi.clock-in'), [
                'latitude' => -6.200005,
                'longitude' => 106.816668,
                'photo' => $this->sampleBase64,
                'shift_id' => $this->shift->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'present',
            ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'status' => 'present',
        ]);

        Carbon::setTestNow();
    }

    public function test_employee_clock_in_succeeds_without_gps_restriction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 07:05:00'));

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->postJson(route('presensi.clock-in'), [
                'latitude' => -6.300000,
                'longitude' => 106.816666,
                'photo' => $this->sampleBase64,
                'shift_id' => $this->shift->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Carbon::setTestNow();
    }

    public function test_employee_can_clock_out_via_web_portal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-12 07:00:00'));

        // Clock in first
        $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->postJson(route('presensi.clock-in'), [
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'photo' => $this->sampleBase64,
                'shift_id' => $this->shift->id,
            ]);

        // Clock out 6 hours later (at 13:00)
        Carbon::setTestNow(Carbon::parse('2026-10-12 13:00:00'));

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->postJson(route('presensi.clock-out'), [
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'photo' => $this->sampleBase64,
                'shift_id' => $this->shift->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'duration_minutes' => 360,
            ]);

        Carbon::setTestNow();
    }

    public function test_employee_can_submit_leave_and_view_history(): void
    {
        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->post(route('presensi.izin.submit'), [
                'leave_type' => LeaveType::Sick->value,
                'start_date' => '2026-10-15',
                'end_date' => '2026-10-16',
                'reason' => 'Demam dan flu perlu istirahat dokter',
            ]);

        $response->assertRedirect(route('presensi.izin'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'sick',
            'total_days' => 2,
        ]);

        $historyResponse = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.riwayat'));

        $historyResponse->assertStatus(200);
        $historyResponse->assertSee('Ringkasan Kehadiran');
    }

    public function test_employee_can_view_profile_and_logout(): void
    {
        $profileResponse = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.profile'));

        $profileResponse->assertStatus(200);
        $profileResponse->assertSee($this->employee->name);

        $logoutResponse = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->post(route('presensi.logout'));

        $logoutResponse->assertRedirect(route('presensi.login'));
        $this->assertNull(session('presensi_employee_id'));
    }
}
