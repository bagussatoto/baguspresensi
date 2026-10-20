<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Employee $employee;

    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'NET-PST',
            'name' => 'Maya Graha Kencana',
            'is_headquarters' => true,
            'address' => 'Jl. Kelapa Gading',
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'code' => 'EMP-001',
            'name' => 'Budi Operator',
            'username' => 'budi_op',
            'role' => EmployeeRole::KASIR,
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'code' => 'SHIFT-2',
            'name' => 'Shift 2 (Pagi)',
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_shifts_endpoint_returns_active_shifts(): void
    {
        $response = $this->getJson(route('api.v1.attendance.shifts'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['code' => 'SHIFT-2', 'name' => 'Shift 2 (Pagi)']);
    }

    public function test_clock_in_api_succeeds_within_radius_returns_201(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'latitude' => -6.15860000,
            'longitude' => 106.90810000, // ~15 meter dari titik cabang
            'clock_in_at' => '2026-10-11 07:10:00',
            'notes' => 'Presensi masuk dinas pagi',
        ];

        $response = $this->postJson(route('api.v1.attendance.clock-in'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status.value', 'present')
            ->assertJsonPath('data.late_minutes', 0)
            ->assertJsonPath('data.employee.id', $this->employee->id);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'status' => 'present',
        ]);
    }

    public function test_clock_in_api_succeeds_even_when_outside_geofence(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'latitude' => -6.175392,
            'longitude' => 106.827153, // Monas (~9km)
        ];

        $response = $this->postJson(route('api.v1.attendance.clock-in'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_clock_in_api_fails_when_duplicate_clock_in_returns_409(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_in_at' => '2026-10-11 07:05:00',
        ];

        $this->postJson(route('api.v1.attendance.clock-in'), $payload)->assertStatus(201);

        $response = $this->postJson(route('api.v1.attendance.clock-in'), $payload);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'ALREADY_CLOCKED_IN');
    }

    public function test_clock_in_api_fails_when_mock_location_detected_returns_403(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'is_mock_location' => true,
        ];

        $response = $this->postJson(route('api.v1.attendance.clock-in'), $payload);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'MOCK_LOCATION_DETECTED');
    }

    public function test_clock_out_api_succeeds_and_calculates_duration_returns_200(): void
    {
        // 1. Clock In dulu
        $this->postJson(route('api.v1.attendance.clock-in'), [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_in_at' => '2026-10-11 07:00:00',
        ])->assertStatus(201);

        // 2. Clock Out jam 13:45 (Lembur 45 menit)
        $outPayload = [
            'employee_id' => $this->employee->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_out_at' => '2026-10-11 13:45:00',
        ];

        $response = $this->postJson(route('api.v1.attendance.clock-out'), $outPayload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.duration_minutes', 405)
            ->assertJsonPath('data.overtime_minutes', 45);
    }

    public function test_clock_out_api_fails_without_clock_in_returns_400(): void
    {
        $outPayload = [
            'employee_id' => $this->employee->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
        ];

        $response = $this->postJson(route('api.v1.attendance.clock-out'), $outPayload);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'NOT_CLOCKED_IN');
    }

    public function test_today_endpoint_returns_attendance_status(): void
    {
        // Belum clock in
        $response = $this->getJson(route('api.v1.attendance.today', ['employee_id' => $this->employee->id]));
        $response->assertStatus(200)
            ->assertJsonPath('has_clocked_in', false);

        // Clock in hari ini pada jam kerja shift 2
        $today = Carbon::today()->setTime(7, 10, 0)->toDateTimeString();
        $this->postJson(route('api.v1.attendance.clock-in'), [
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_in_at' => $today,
        ])->assertStatus(201);

        $response2 = $this->getJson(route('api.v1.attendance.today', ['employee_id' => $this->employee->id]));
        $response2->assertStatus(200)
            ->assertJsonPath('has_clocked_in', true)
            ->assertJsonPath('has_clocked_out', false);
    }

    public function test_leave_request_api_creates_pending_leave_returns_201(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-17',
            'reason' => 'Keperluan keluarga',
        ];

        $response = $this->postJson(route('api.v1.leave.request'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_days', 3)
            ->assertJsonPath('data.status.value', 'pending')
            ->assertJsonPath('data.leave_type.value', 'annual');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'total_days' => 3,
            'status' => 'pending',
        ]);
    }
}
