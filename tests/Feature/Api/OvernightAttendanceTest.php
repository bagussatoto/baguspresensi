<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class OvernightAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Employee $nightOperator;

    protected Employee $morningOperator;

    protected Shift $shift4Overnight;

    protected Shift $shift1EarlyMorning;

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

        $this->nightOperator = Employee::create([
            'branch_id' => $this->branch->id,
            'code' => 'OP-MALAM',
            'name' => 'Slamet Operator Malam',
            'role' => EmployeeRole::KASIR,
            'is_active' => true,
        ]);

        $this->morningOperator = Employee::create([
            'branch_id' => $this->branch->id,
            'code' => 'OP-SUBUH',
            'name' => 'Doni Operator Subuh',
            'role' => EmployeeRole::KASIR,
            'is_active' => true,
        ]);

        // Shift 4: 19:00 malam s/d 01:00 dini hari berikutnya
        $this->shift4Overnight = Shift::create([
            'code' => 'SHIFT-4',
            'name' => 'Shift 4 (Malam - Lintas Hari)',
            'start_time' => '19:00:00',
            'end_time' => '01:00:00',
            'is_overnight' => true,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        // Shift 1: 01:00 dini hari s/d 07:00 pagi
        $this->shift1EarlyMorning = Shift::create([
            'code' => 'SHIFT-1',
            'name' => 'Shift 1 (Dini Hari)',
            'start_time' => '01:00:00',
            'end_time' => '07:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_overnight_shift_4_clock_in_and_next_day_clock_out_flow(): void
    {
        // 1. Operator malam masuk jam 19:05 pada 10 Oktober 2026
        $inPayload = [
            'employee_id' => $this->nightOperator->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift4Overnight->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_in_at' => '2026-10-10 19:05:00',
            'notes' => 'Masuk dinas shift 4 malam minggu',
        ];

        $inResponse = $this->postJson(route('api.v1.attendance.clock-in'), $inPayload);

        $inResponse->assertStatus(201)
            ->assertJsonPath('data.work_date', '2026-10-10')
            ->assertJsonPath('data.status.value', 'present')
            ->assertJsonPath('data.late_minutes', 0);

        // 2. Operator malam pulang jam 01:15 dini hari pada 11 Oktober 2026 (hari berikutnya)
        $outPayload = [
            'employee_id' => $this->nightOperator->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_out_at' => '2026-10-11 01:15:00',
            'notes' => 'Selesai serah terima shift',
        ];

        $outResponse = $this->postJson(route('api.v1.attendance.clock-out'), $outPayload);

        $outResponse->assertStatus(200)
            ->assertJsonPath('data.work_date', '2026-10-10') // Tetap terikat ke work date 10 Oktober
            ->assertJsonPath('data.duration_minutes', 370)   // 19:05 s/d 01:15 = 370 menit (6 jam 10 menit)
            ->assertJsonPath('data.overtime_minutes', 15);   // Lewat 15 menit dari jam 01:00

        // 3. Pada saat yang sama (01:00 dini hari 11 Oktober), Operator Subuh masuk untuk Shift 1
        $morningInPayload = [
            'employee_id' => $this->morningOperator->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift1EarlyMorning->id,
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'clock_in_at' => '2026-10-11 00:58:00',
        ];

        $morningInResponse = $this->postJson(route('api.v1.attendance.clock-in'), $morningInPayload);

        $morningInResponse->assertStatus(201)
            ->assertJsonPath('data.work_date', '2026-10-11')
            ->assertJsonPath('data.status.value', 'present');

        // Pastikan kedua record tercatat dengan benar di database
        /** @var AttendanceRecord $nightRecord */
        $nightRecord = AttendanceRecord::where('employee_id', $this->nightOperator->id)->first();
        $this->assertNotNull($nightRecord);
        $this->assertSame('2026-10-10', $nightRecord->work_date->toDateString());
        $this->assertSame(370, $nightRecord->duration_minutes);
        $this->assertSame(15, $nightRecord->overtime_minutes);

        /** @var AttendanceRecord $morningRecord */
        $morningRecord = AttendanceRecord::where('employee_id', $this->morningOperator->id)->first();
        $this->assertNotNull($morningRecord);
        $this->assertSame('2026-10-11', $morningRecord->work_date->toDateString());
        $this->assertSame('present', $morningRecord->status->value);
    }
}
