<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Exceptions\AlreadyClockedInException;
use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Exceptions\NotClockedInException;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AttendanceActionsTest extends TestCase
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

    public function test_clock_in_action_creates_present_record_when_on_time(): void
    {
        /** @var ClockInAction $action */
        $action = app(ClockInAction::class);

        // Karyawan hadir jam 07:10 (dalam grace period 15 menit)
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.15860000,
            longitude: 106.90810000,
            shiftId: $this->shift->id,
            clockInAt: Carbon::parse('2026-10-11 07:10:00')
        );

        $record = $action->execute($dto);

        $this->assertNotNull($record->id);
        $this->assertSame(AttendanceStatus::Present, $record->status);
        $this->assertSame(0, $record->late_minutes);
        $this->assertSame('2026-10-11', $record->work_date->toDateString());
        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'status' => 'present',
            'late_minutes' => 0,
        ]);
    }

    public function test_clock_in_action_marks_late_when_past_grace_period(): void
    {
        /** @var ClockInAction $action */
        $action = app(ClockInAction::class);

        // Karyawan hadir jam 07:35 (lewat 35 menit dari jam 07:00, grace period 15 menit)
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.15850000,
            longitude: 106.90800000,
            shiftId: $this->shift->id,
            clockInAt: Carbon::parse('2026-10-11 07:35:00')
        );

        $record = $action->execute($dto);

        $this->assertSame(AttendanceStatus::Late, $record->status);
        $this->assertSame(35, $record->late_minutes);
    }

    public function test_clock_in_action_rejects_duplicate_clock_in_on_same_shift(): void
    {
        /** @var ClockInAction $action */
        $action = app(ClockInAction::class);

        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.15850000,
            longitude: 106.90800000,
            shiftId: $this->shift->id,
            clockInAt: Carbon::parse('2026-10-11 07:05:00')
        );

        $action->execute($dto);

        $this->expectException(AlreadyClockedInException::class);
        $action->execute($dto);
    }

    public function test_clock_in_action_rejects_mock_location(): void
    {
        /** @var ClockInAction $action */
        $action = app(ClockInAction::class);

        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.15850000,
            longitude: 106.90800000,
            shiftId: $this->shift->id,
            isMockLocation: true
        );

        $this->expectException(MockLocationDetectedException::class);
        $action->execute($dto);
    }

    public function test_clock_in_action_allows_even_outside_geofence_when_gps_disabled(): void
    {
        /** @var ClockInAction $action */
        $action = app(ClockInAction::class);

        // Lokasi di Monas (~9km)
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.175392,
            longitude: 106.827153,
            shiftId: $this->shift->id
        );

        $record = $action->execute($dto);
        $this->assertNotNull($record->id);
    }

    public function test_clock_out_action_calculates_duration_and_overtime(): void
    {
        /** @var ClockInAction $inAction */
        $inAction = app(ClockInAction::class);
        /** @var ClockOutAction $outAction */
        $outAction = app(ClockOutAction::class);

        // 1. Clock in jam 07:00
        $inDto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.15850000,
            longitude: 106.90800000,
            shiftId: $this->shift->id,
            clockInAt: Carbon::parse('2026-10-11 07:00:00')
        );
        $inAction->execute($inDto);

        // 2. Clock out jam 13:45 (Shift selesai jam 13:00 -> lembur 45 menit, total durasi 405 menit)
        $outDto = new ClockOutDTO(
            employeeId: $this->employee->id,
            latitude: -6.15850000,
            longitude: 106.90800000,
            clockOutAt: Carbon::parse('2026-10-11 13:45:00')
        );
        $record = $outAction->execute($outDto);

        $this->assertNotNull($record->clock_out_at);
        $this->assertSame(405, $record->duration_minutes);
        $this->assertSame(45, $record->overtime_minutes);
        $this->assertSame(0, $record->early_leave_minutes);
    }

    public function test_clock_out_action_rejects_when_not_clocked_in(): void
    {
        /** @var ClockOutAction $outAction */
        $outAction = app(ClockOutAction::class);

        $outDto = new ClockOutDTO(
            employeeId: $this->employee->id,
            latitude: -6.15850000,
            longitude: 106.90800000
        );

        $this->expectException(NotClockedInException::class);
        $outAction->execute($outDto);
    }
}
