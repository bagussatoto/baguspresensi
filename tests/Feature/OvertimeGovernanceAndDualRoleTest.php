<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Enums\AdminRole;
use App\Domains\Attendance\Exceptions\TooEarlyForClockOutException;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class OvertimeGovernanceAndDualRoleTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Shift $shiftPagi;

    protected Employee $employeeDualRole;

    protected User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-001',
            'latitude' => -6.1585,
            'longitude' => 106.9080,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->shiftPagi = Shift::create([
            'name' => 'Shift Pagi Warnet',
            'code' => 'SFT-WRN-01',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->employeeDualRole = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Rian Operator & Desainer',
            'code' => 'EMP-DUAL-01',
            'pin' => Hash::make('123456'),
            'role' => 'operator',
            'position' => 'Operator Shift & Desainer Grafis',
            'default_work_type' => 'shift',
            'default_contract_hours' => 8,
            'default_cost_center' => '5-1001',
            'is_active' => true,
        ]);

        $this->supervisor = User::create([
            'name' => 'Pak Budi Supervisor',
            'email' => 'spv.budi@mayagraha.com',
            'password' => Hash::make('secret123'),
            'role' => AdminRole::BranchAdmin,
            'assigned_branch_id' => $this->branch->id,
        ]);
    }

    /**
     * Uji 1: Clock-out pada mode fleksibel sebelum jam kontrak (misal < 8 jam) melempar TooEarlyForClockOutException.
     */
    public function test_flexible_clock_out_before_contract_hours_throws_too_early_exception(): void
    {
        $today = Carbon::parse('2026-10-21 09:00:00');

        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $today,
            workType: 'flexible'
        );

        $actionIn = app(ClockInAction::class);
        $actionIn->execute($dtoIn);

        // Pulang jam 13:00 (hanya 4 jam = 240 menit dari target 8 jam = 480 menit)
        $clockOutTime = Carbon::parse('2026-10-21 13:00:00');
        $dtoOut = new ClockOutDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $clockOutTime
        );

        $this->expectException(TooEarlyForClockOutException::class);

        $actionOut = app(ClockOutAction::class);
        $actionOut->execute($dtoOut);
    }

    /**
     * Uji 2: Clock-out < kuota kontrak berhasil jika permohonan izin pulang awal telah disetujui supervisor.
     */
    public function test_flexible_clock_out_before_contract_hours_succeeds_when_early_leave_approved(): void
    {
        $today = Carbon::parse('2026-10-21 09:00:00');

        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $today,
            workType: 'flexible'
        );

        $actionIn = app(ClockInAction::class);
        $record = $actionIn->execute($dtoIn);

        // Supervisor menyetujui izin pulang awal
        $record->update([
            'early_leave_approved' => true,
            'early_leave_reason' => 'Urusan keluarga mendesak',
        ]);

        // Pulang jam 13:00 (4 jam = 240 menit)
        $clockOutTime = Carbon::parse('2026-10-21 13:00:00');
        $dtoOut = new ClockOutDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $clockOutTime
        );

        $actionOut = app(ClockOutAction::class);
        $recordOut = $actionOut->execute($dtoOut);

        $this->assertNotNull($recordOut->clock_out_at);
        $this->assertSame(240, $recordOut->duration_minutes);
        $this->assertSame(240, $recordOut->effective_minutes);
        $this->assertSame(240, $recordOut->early_leave_minutes);
        $this->assertSame(0, $recordOut->overtime_minutes);
    }

    /**
     * Uji 3: Mode fleksibel yang melebihi kuota kontrak tanpa persetujuan lembur dibatasi pada kuota kontrak (lembur = 0).
     */
    public function test_flexible_clock_out_over_contract_hours_caps_overtime_at_zero_without_approval(): void
    {
        $today = Carbon::parse('2026-10-21 08:00:00');

        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $today,
            workType: 'flexible'
        );

        $actionIn = app(ClockInAction::class);
        $actionIn->execute($dtoIn);

        // Pulang jam 18:00 (10 jam = 600 menit. Target 8 jam = 480 menit. Tanpa approval lembur)
        $clockOutTime = Carbon::parse('2026-10-21 18:00:00');
        $dtoOut = new ClockOutDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $clockOutTime
        );

        $actionOut = app(ClockOutAction::class);
        $recordOut = $actionOut->execute($dtoOut);

        $this->assertSame(600, $recordOut->duration_minutes);
        $this->assertSame(0, $recordOut->overtime_minutes); // Dibatasi: 0 karena belum disetujui
        $this->assertSame(480, $recordOut->effective_minutes); // Efektif dibatasi pada kontrak 8 jam = 480m
    }

    /**
     * Uji 4: Mode fleksibel dengan lembur pre-approved dihitung sesuai durasi lembur yang disetujui.
     */
    public function test_flexible_clock_out_over_contract_hours_awards_overtime_when_pre_approved(): void
    {
        $today = Carbon::parse('2026-10-21 08:00:00');

        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $today,
            workType: 'flexible'
        );

        $actionIn = app(ClockInAction::class);
        $record = $actionIn->execute($dtoIn);

        // Supervisor telah menyetujui lembur 120 menit
        $record->update([
            'overtime_status' => 'approved',
            'overtime_approved_minutes' => 120,
            'overtime_reason' => 'Deadline Desain Banner Promo Outlet',
            'overtime_approved_at' => Carbon::now(),
            'overtime_approved_by' => $this->supervisor->id,
        ]);

        // Pulang jam 18:00 (10 jam = 600 menit)
        $clockOutTime = Carbon::parse('2026-10-21 18:00:00');
        $dtoOut = new ClockOutDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $clockOutTime
        );

        $actionOut = app(ClockOutAction::class);
        $recordOut = $actionOut->execute($dtoOut);

        $this->assertSame(600, $recordOut->duration_minutes);
        $this->assertSame(120, $recordOut->overtime_minutes); // Disetujui 120m
        $this->assertSame(600, $recordOut->effective_minutes); // 480m + 120m = 600m
    }

    /**
     * Uji 5: Karyawan Multi-Peran (Dual-Role) dapat menjalankan sesi shift dan sesi fleksibel di hari yang sama dengan isolasi akun.
     */
    public function test_dual_role_employee_can_perform_shift_and_flexible_sessions_on_same_day(): void
    {
        $today = Carbon::parse('2026-10-21');

        // Sesi 1: Shift Pagi Warnet (08:00 - 16:00) -> HPP 5-1001
        $dtoShiftIn = new ClockInDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            shiftId: $this->shiftPagi->id,
            clockInAt: $today->copy()->setTime(8, 0),
            workType: 'shift'
        );

        $actionIn = app(ClockInAction::class);
        $recordShift = $actionIn->execute($dtoShiftIn);

        $actionOut = app(ClockOutAction::class);
        $dtoShiftOut = new ClockOutDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $today->copy()->setTime(16, 0)
        );
        $recordShift = $actionOut->execute($dtoShiftOut);

        $this->assertSame('shift', $recordShift->work_type);
        $this->assertSame('5-1001', $recordShift->cost_center);
        $this->assertSame(480, $recordShift->effective_minutes);

        // Sesi 2: Sesi Desain Fleksibel Malam (17:00 - 21:00) -> OPEX 5-1002
        $dtoFlexIn = new ClockInDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $today->copy()->setTime(17, 0),
            workType: 'flexible'
        );
        $recordFlex = $actionIn->execute($dtoFlexIn);

        // Setuju izin early leave karena hanya 4 jam
        $recordFlex->update(['early_leave_approved' => true]);

        $dtoFlexOut = new ClockOutDTO(
            employeeId: $this->employeeDualRole->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $today->copy()->setTime(21, 0)
        );
        $recordFlex = $actionOut->execute($dtoFlexOut);

        $this->assertSame('flexible', $recordFlex->work_type);
        $this->assertSame('5-1002', $recordFlex->cost_center);
        $this->assertSame(240, $recordFlex->effective_minutes);

        // Verifikasi kedua rekaman tersimpan pada hari yang sama
        $count = AttendanceRecord::where('employee_id', $this->employeeDualRole->id)
            ->whereDate('work_date', '2026-10-21')
            ->count();
        $this->assertSame(2, $count);
    }

    /**
     * Uji 6: Supervisor menyetujui permohonan lembur dan audit log forensik tercatat otomatis.
     */
    public function test_admin_can_approve_record_overtime_and_early_leave_with_audit_trail(): void
    {
        $today = Carbon::parse('2026-10-21 08:00:00');

        $record = AttendanceRecord::create([
            'employee_id' => $this->employeeDualRole->id,
            'branch_id' => $this->branch->id,
            'work_date' => '2026-10-21',
            'clock_in_at' => $today,
            'clock_out_at' => $today->copy()->addHours(10),
            'duration_minutes' => 600,
            'effective_minutes' => 480,
            'work_type' => 'flexible',
            'cost_center' => '5-1002',
            'overtime_status' => 'pending',
            'overtime_requested_minutes' => 120,
            'overtime_reason' => 'Percepatan desain materi katalog',
        ]);

        // Supervisor mengeksekusi approve lembur via endpoint web
        $response = $this->actingAs($this->supervisor)
            ->post(route('admin.presensi.record-overtime.approve', $record), [
                'approved_minutes' => 120,
                'admin_note' => 'Disetujui untuk pemenuhan deadline marketing',
            ]);

        $response->assertRedirect();
        $record->refresh();

        $this->assertTrue($record->isOvertimeApproved());
        $this->assertSame(120, $record->overtime_approved_minutes);
        $this->assertSame(120, $record->overtime_minutes);
        $this->assertSame(600, $record->effective_minutes);

        // Verifikasi pencatatan WORM Audit Log
        $auditLog = AuditLog::where('auditable_type', AttendanceRecord::class)
            ->where('auditable_id', $record->id)
            ->where('action', 'approve_overtime')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame($this->supervisor->id, $auditLog->user_id);
    }
}
