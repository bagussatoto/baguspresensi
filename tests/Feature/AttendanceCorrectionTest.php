<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Employee $admin;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Warnet MGK Net Pusat',
            'code' => 'NET-PST',
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Operator Hendra',
            'code' => 'OP-001',
            'username' => 'hendra',
            'is_active' => true,
        ]);

        $this->admin = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Manager Supervisor',
            'code' => 'MGR-001',
            'username' => 'supervisor',
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift Pagi (Shift 2)',
            'code' => 'SHIFT-2',
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_employee_is_redirected_to_login(): void
    {
        $response = $this->get(route('presensi.koreksi'));
        $response->assertRedirect(route('presensi.login'));
    }

    public function test_authenticated_employee_can_view_correction_form_and_history(): void
    {
        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.koreksi'));

        $response->assertOk()
            ->assertViewIs('presensi.koreksi')
            ->assertSee('Pengajuan Koreksi Presensi')
            ->assertSee('Riwayat Koreksi Presensi');
    }

    public function test_employee_can_submit_attendance_correction_request(): void
    {
        $workDate = Carbon::yesterday()->toDateString();

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->post(route('presensi.koreksi.submit'), [
                'work_date' => $workDate,
                'shift_id' => $this->shift->id,
                'correction_type' => 'both',
                'clock_in_time' => '07:05',
                'clock_out_time' => '13:10',
                'reason' => 'HP kehabisan baterai saat dinas shift pagi',
            ]);

        $response->assertRedirect(route('presensi.koreksi'))
            ->assertSessionHas('success');

        $correction = AttendanceCorrection::where('employee_id', $this->employee->id)
            ->where('shift_id', $this->shift->id)
            ->first();

        $this->assertNotNull($correction);
        $this->assertEquals($workDate, $correction->work_date->toDateString());
        $this->assertEquals('both', $correction->correction_type);
        $this->assertEquals('HP kehabisan baterai saat dinas shift pagi', $correction->reason);
        $this->assertEquals(ApprovalStatus::Pending, $correction->status);
    }

    public function test_submission_fails_if_future_date(): void
    {
        $futureDate = Carbon::tomorrow()->toDateString();

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->post(route('presensi.koreksi.submit'), [
                'work_date' => $futureDate,
                'correction_type' => 'both',
                'reason' => 'Alasan invalid',
            ]);

        $response->assertSessionHasErrors('work_date');
    }

    public function test_admin_can_view_pending_corrections_in_approval_screen(): void
    {
        $correction = AttendanceCorrection::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::yesterday()->toDateString(),
            'correction_type' => 'both',
            'proposed_clock_in_at' => Carbon::yesterday()->setTime(7, 5),
            'proposed_clock_out_at' => Carbon::yesterday()->setTime(13, 10),
            'reason' => 'HP mati saat dinas',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->get(route('admin.presensi.approval'));

        $response->assertOk()
            ->assertViewIs('admin.presensi.approval')
            ->assertSee('Operator Hendra')
            ->assertSee('HP mati saat dinas');
    }

    public function test_admin_can_approve_correction_and_updates_attendance_record(): void
    {
        $workDate = Carbon::yesterday()->toDateString();
        $clockIn = Carbon::yesterday()->setTime(7, 10); // Hadir tepat waktu (< 07:15)
        $clockOut = Carbon::yesterday()->setTime(13, 20);

        $correction = AttendanceCorrection::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'work_date' => $workDate,
            'correction_type' => 'both',
            'proposed_clock_in_at' => $clockIn,
            'proposed_clock_out_at' => $clockOut,
            'reason' => 'Kamera error saat clock-in',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->post(route('admin.presensi.correction.approve', $correction->id));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $correction->refresh();
        $this->assertEquals(ApprovalStatus::Approved, $correction->status);
        $this->assertNotNull($correction->approved_at);

        // Periksa bahwa attendance_records telah dibuat / disinkronkan
        $record = AttendanceRecord::where('employee_id', $this->employee->id)->first();

        $this->assertNotNull($record);
        $this->assertEquals($workDate, $record->work_date->toDateString());
        $this->assertEquals(AttendanceStatus::Present, $record->status);
        $this->assertEquals(VerificationMethod::AdminManual, $record->verification_method);
        $this->assertEquals(370, $record->duration_minutes);
    }

    public function test_admin_can_reject_correction_with_note(): void
    {
        $correction = AttendanceCorrection::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::yesterday()->toDateString(),
            'correction_type' => 'both',
            'proposed_clock_in_at' => Carbon::yesterday()->setTime(7, 0),
            'proposed_clock_out_at' => Carbon::yesterday()->setTime(13, 0),
            'reason' => 'Lupa absen',
            'status' => ApprovalStatus::Pending,
        ]);

        $response = $this->post(route('admin.presensi.correction.reject', $correction->id), [
            'rejection_note' => 'Log kasir menunjukkan Anda tidak berada di toko pada tanggal tersebut.',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $correction->refresh();
        $this->assertEquals(ApprovalStatus::Rejected, $correction->status);
        $this->assertEquals('Log kasir menunjukkan Anda tidak berada di toko pada tanggal tersebut.', $correction->rejection_note);
    }
}
