<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Action: Menyetujui Permohonan Koreksi Presensi & Memutakhirkan Record Presensi.
 */
class ApproveAttendanceCorrectionAction
{
    public function execute(AttendanceCorrection $correction, int $approverId): AttendanceCorrection
    {
        $correction->loadMissing(['employee', 'shift', 'attendanceRecord']);

        $employee = $correction->employee ?? Employee::findOrFail($correction->employee_id);
        $shift = $correction->shift ?? ($correction->shift_id ? Shift::find($correction->shift_id) : null);
        $branchId = $employee->branch_id ?? Branch::value('id');

        $record = $correction->attendanceRecord;

        if (! $record) {
            $record = AttendanceRecord::where('employee_id', $employee->id)
                ->where('work_date', $correction->work_date->toDateString())
                ->first();
        }

        if (! $record) {
            // Buat record presensi baru karena sebelumnya tidak ada absensi sama sekali (alpa/terlewat)
            $record = new AttendanceRecord;
            $record->employee_id = $employee->id;
            $record->branch_id = (int) $branchId;
            $record->shift_id = $shift?->id;
            $record->work_date = $correction->work_date->toDateString();
            $record->verification_method = VerificationMethod::AdminManual;
            $record->notes = 'Dibuat via persetujuan koreksi presensi ID #'.$correction->id;
        }

        // Terapkan jam yang diusulkan
        if ($correction->proposed_clock_in_at) {
            $record->clock_in_at = $correction->proposed_clock_in_at;
        }

        if ($correction->proposed_clock_out_at) {
            $record->clock_out_at = $correction->proposed_clock_out_at;
        }

        // Evaluasi status keterlambatan & grace period jika shift ada
        $status = AttendanceStatus::Present;
        $lateMinutes = 0;

        if ($shift && $record->clock_in_at) {
            $workDate = Carbon::parse($record->work_date);
            $shiftStart = Carbon::parse($workDate->toDateString().' '.$shift->start_time);
            $graceEnd = $shiftStart->copy()->addMinutes($shift->grace_period_minutes);

            if ($record->clock_in_at->gt($graceEnd)) {
                $status = AttendanceStatus::Late;
                $lateMinutes = (int) $shiftStart->diffInMinutes($record->clock_in_at);
            }
        }

        $record->status = $status;
        $record->late_minutes = $lateMinutes;

        // Hitung durasi kerja jika jam masuk dan pulang tersedia
        if ($record->clock_in_at && $record->clock_out_at) {
            $durationMinutes = (int) $record->clock_in_at->diffInMinutes($record->clock_out_at);
            $record->duration_minutes = max(0, $durationMinutes);

            // Hitung lembur jika melebihi shift end time
            if ($shift) {
                $shiftEnd = Carbon::parse($record->work_date->toDateString().' '.$shift->end_time);
                if ($shift->is_overnight) {
                    $shiftEnd->addDay();
                }

                if ($record->clock_out_at->gt($shiftEnd->copy()->addMinutes($shift->grace_period_minutes))) {
                    $record->overtime_minutes = (int) $shiftEnd->diffInMinutes($record->clock_out_at);
                } else {
                    $record->overtime_minutes = 0;
                }
            }
        }

        $record->verification_method = VerificationMethod::AdminManual;
        $record->notes = trim(($record->notes ?? '').' [Koreksi Disetujui ID #'.$correction->id.']');
        $record->save();

        // Update record koreksi
        $correction->update([
            'attendance_record_id' => $record->id,
            'status' => ApprovalStatus::Approved,
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
        ]);

        return $correction->fresh();
    }
}
