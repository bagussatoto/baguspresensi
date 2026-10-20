<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\OvertimeClaim;
use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Enums\LeaveType;
use App\Domains\Leave\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
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
            'name' => 'Operator Test',
            'code' => 'OP-999',
            'username' => 'optest',
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
    }

    public function test_admin_attendance_index_can_be_rendered(): void
    {
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(7, 5),
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        $response = $this->get(route('admin.presensi'));

        $response->assertStatus(200);
        $response->assertSee('Maya Graha Kencana');
        $response->assertSee('Monitoring Presensi Karyawan');
        $response->assertSee('Operator Test');
        $response->assertSee('Maya Graha Pusat');
    }

    public function test_admin_can_filter_attendance_by_branch_and_status(): void
    {
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => '2026-10-11',
            'clock_in_at' => Carbon::parse('2026-10-11 07:25:00'),
            'status' => AttendanceStatus::Late,
            'late_minutes' => 10,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        $response = $this->get(route('admin.presensi', [
            'date' => '2026-10-11',
            'branch_id' => $this->branch->id,
            'status' => AttendanceStatus::Late->value,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Operator Test');
        $response->assertSee('10 Menit');
    }

    public function test_admin_rekap_page_can_be_rendered(): void
    {
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(7, 0),
            'clock_out_at' => Carbon::today()->setTime(13, 0),
            'duration_minutes' => 360,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        $response = $this->get(route('admin.presensi.rekap'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi Presensi Karyawan');
        $response->assertSee('Operator Test');
    }

    public function test_admin_approval_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.presensi.approval'));

        $response->assertStatus(200);
        $response->assertSee('Persetujuan Cuti');
        $response->assertSee('Klaim Lembur Shift');
    }

    public function test_admin_can_approve_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => LeaveType::Annual,
            'start_date' => Carbon::today()->addDays(2),
            'end_date' => Carbon::today()->addDays(4),
            'total_days' => 3,
            'reason' => 'Keperluan keluarga di luar kota',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->post(route('admin.presensi.leave.approve', $leave->id));

        $response->assertRedirect();
        $this->assertEquals(ApprovalStatus::Approved, $leave->fresh()->status);
        $this->assertNotNull($leave->fresh()->approved_at);
    }

    public function test_admin_can_reject_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => LeaveType::Annual,
            'start_date' => Carbon::today()->addDays(1),
            'end_date' => Carbon::today()->addDays(2),
            'total_days' => 2,
            'reason' => 'Liburan',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->post(route('admin.presensi.leave.reject', $leave->id), [
            'rejection_note' => 'Jadwal shift sedang padat, silakan reschedule.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(ApprovalStatus::Rejected, $leave->fresh()->status);
        $this->assertEquals('Jadwal shift sedang padat, silakan reschedule.', $leave->fresh()->rejection_note);
    }

    public function test_admin_can_approve_overtime_claim(): void
    {
        $attendance = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(7, 0),
            'clock_out_at' => Carbon::today()->setTime(14, 30),
            'duration_minutes' => 450,
            'overtime_minutes' => 90,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        $overtime = OvertimeClaim::create([
            'attendance_record_id' => $attendance->id,
            'employee_id' => $this->employee->id,
            'requested_minutes' => 90,
            'reason' => 'Maintenance kabel LAN warnet',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->post(route('admin.presensi.overtime.approve', $overtime->id), [
            'approved_minutes' => 75,
        ]);

        $response->assertRedirect();
        $this->assertEquals(ApprovalStatus::Approved, $overtime->fresh()->status);
        $this->assertEquals(75, $overtime->fresh()->approved_minutes);
        $this->assertNotNull($overtime->fresh()->approved_at);
    }

    public function test_admin_can_reject_overtime_claim(): void
    {
        $attendance = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(7, 0),
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        $overtime = OvertimeClaim::create([
            'attendance_record_id' => $attendance->id,
            'employee_id' => $this->employee->id,
            'requested_minutes' => 60,
            'reason' => 'Main game',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->post(route('admin.presensi.overtime.reject', $overtime->id), [
            'rejection_note' => 'Bukan aktivitas kerja resmi.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(ApprovalStatus::Rejected, $overtime->fresh()->status);
        $this->assertEquals('Bukan aktivitas kerja resmi.', $overtime->fresh()->rejection_note);
    }

    public function test_admin_can_export_monthly_rekap_as_csv(): void
    {
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(7, 0),
            'clock_out_at' => Carbon::today()->setTime(13, 0),
            'duration_minutes' => 360,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        $response = $this->get(route('admin.presensi.rekap', [
            'month' => Carbon::today()->month,
            'year' => Carbon::today()->year,
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $streamedContent = $response->streamedContent();
        $this->assertStringContainsString('NIK / Kode', $streamedContent);
        $this->assertStringContainsString('Nama Karyawan', $streamedContent);
        $this->assertStringContainsString('Operator Test', $streamedContent);
        $this->assertStringContainsString('Total Jam Kerja Riil', $streamedContent);
    }

    public function test_close_daily_attendance_command_marks_absent_and_leave(): void
    {
        $today = Carbon::today()->toDateString();

        // 1. Karyawan 1: Sudah presensi
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => $today,
            'clock_in_at' => Carbon::today()->setTime(7, 0),
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::GpsSelfie,
        ]);

        // 2. Karyawan 2: Mengajukan Cuti & sudah Disetujui
        $empLeave = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Karyawan Cuti',
            'code' => 'OP-CUTI',
            'username' => 'opcuti',
            'is_active' => true,
        ]);
        LeaveRequest::create([
            'employee_id' => $empLeave->id,
            'leave_type' => LeaveType::Annual,
            'start_date' => $today,
            'end_date' => $today,
            'total_days' => 1,
            'reason' => 'Cuti libur',
            'status' => ApprovalStatus::Approved,
        ]);

        // 3. Karyawan 3: Tidak presensi dan tidak mengajukan izin
        $empAbsent = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Karyawan Alpa',
            'code' => 'OP-ALPA',
            'username' => 'opalpa',
            'is_active' => true,
        ]);

        // Jalankan Artisan Command
        $this->artisan('attendance:close-daily', ['--date' => $today])
            ->assertSuccessful();

        // Verifikasi Karyawan 3 ditandai Absent (Alpa)
        $absentRecord = AttendanceRecord::where('employee_id', $empAbsent->id)
            ->whereDate('work_date', $today)
            ->first();
        $this->assertNotNull($absentRecord);
        $this->assertEquals(AttendanceStatus::Absent, $absentRecord->status);
        $this->assertEquals(VerificationMethod::AdminManual, $absentRecord->verification_method);

        // Verifikasi Karyawan 2 ditandai OnLeave
        $leaveRecord = AttendanceRecord::where('employee_id', $empLeave->id)
            ->whereDate('work_date', $today)
            ->first();
        $this->assertNotNull($leaveRecord);
        $this->assertEquals(AttendanceStatus::OnLeave, $leaveRecord->status);

        // Verifikasi Karyawan 1 tetap Present
        $presentRecord = AttendanceRecord::where('employee_id', $this->employee->id)
            ->whereDate('work_date', $today)
            ->first();
        $this->assertEquals(AttendanceStatus::Present, $presentRecord->status);
    }

    public function test_admin_can_store_jam_kerja_karyawan(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'work_date' => '2026-10-18',
            'clock_in_time' => '07:05',
            'clock_out_time' => '13:00',
            'overtime_minutes' => 30,
            'status' => 'present',
            'notes' => 'Presensi manual shift pagi',
        ];

        $response = $this->post(route('admin.jamkerja.store'), $payload);
        $response->assertRedirect(route('admin.index').'#data-jamkerja');

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'overtime_minutes' => 30,
            'notes' => 'Presensi manual shift pagi',
        ]);

        $created = AttendanceRecord::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($created);
        $this->assertSame('2026-10-18', $created->work_date->format('Y-m-d'));
    }

    public function test_admin_can_update_jam_kerja_karyawan(): void
    {
        $record = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => '2026-10-18',
            'clock_in_at' => Carbon::parse('2026-10-18 08:00:00'),
            'clock_out_at' => Carbon::parse('2026-10-18 16:00:00'),
            'duration_minutes' => 480,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::AdminManual,
        ]);

        $updatePayload = [
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'work_date' => '2026-10-18',
            'clock_in_time' => '07:00',
            'clock_out_time' => '15:00',
            'overtime_minutes' => 45,
            'status' => 'present',
            'notes' => 'Koreksi jam masuk',
        ];

        $response = $this->put(route('admin.jamkerja.update', $record->id), $updatePayload);
        $response->assertRedirect(route('admin.index').'#data-jamkerja');

        $record->refresh();
        $this->assertSame(45, $record->overtime_minutes);
        $this->assertSame('Koreksi jam masuk', $record->notes);
        $this->assertSame('07:00', $record->clock_in_at->format('H:i'));
        $this->assertSame('15:00', $record->clock_out_at->format('H:i'));
    }

    public function test_admin_can_delete_jam_kerja_karyawan(): void
    {
        $record = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => '2026-10-18',
            'clock_in_at' => Carbon::parse('2026-10-18 08:00:00'),
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::AdminManual,
        ]);

        $response = $this->delete(route('admin.jamkerja.destroy', $record->id));
        $response->assertRedirect(route('admin.index').'#data-jamkerja');

        $this->assertDatabaseMissing('attendance_records', [
            'id' => $record->id,
        ]);
    }
}
