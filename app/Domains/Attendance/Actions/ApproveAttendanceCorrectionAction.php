<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\AuditLogService;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Action: Menyetujui Permohonan Koreksi Presensi & Memutakhirkan Record Presensi.
 * Menegakkan SoD (Anti Self-Approval), SoT (Atomic Update ke attendance_records), dan ISO 27001 (AuditLog WORM).
 */
class ApproveAttendanceCorrectionAction
{
    public function __construct(
        public AuditLogService $auditLogService
    ) {}

    public function execute(AttendanceCorrection $correction, int $approverId): AttendanceCorrection
    {
        $correction->loadMissing(['employee', 'shift', 'attendanceRecord']);

        $employee = $correction->employee ?? Employee::findOrFail($correction->employee_id);
        $shift = $correction->shift ?? ($correction->shift_id ? Shift::find($correction->shift_id) : null);
        $branchId = $employee->branch_id ?? Branch::value('id');

        // Proteksi Anti Self-Approval (Prinsip SoD Maker-Checker)
        $approverUser = User::find($approverId);
        if ($approverUser) {
            $isSelf = false;
            if (! empty($employee->email) && ! empty($approverUser->email) && strtolower((string) $approverUser->email) === strtolower((string) $employee->email)) {
                $isSelf = true;
            }
            if (! empty($employee->username) && ! empty($approverUser->username) && strtolower((string) $approverUser->username) === strtolower((string) $employee->username)) {
                $isSelf = true;
            }
            if (! empty($employee->name) && ! empty($approverUser->name) && strtolower((string) $approverUser->name) === strtolower((string) $employee->name)) {
                $isSelf = true;
            }
            if ($isSelf) {
                throw new AuthorizationException('Pelanggaran SoD: Anda tidak diizinkan menyetujui permohonan koreksi presensi Anda sendiri.');
            }
        } else {
            // Approver adalah Employee langsung (Supervisor ID)
            if ($approverId === (int) $correction->employee_id) {
                throw new AuthorizationException('Pelanggaran SoD: Anda tidak diizinkan menyetujui permohonan koreksi presensi Anda sendiri.');
            }
        }

        return DB::transaction(function () use ($correction, $approverId, $employee, $shift, $branchId): AttendanceCorrection {
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

            // Penanda struktural SoT penyesuaian koreksi
            $record->is_adjusted = true;
            $record->adjusted_by = $approverId;
            $record->adjusted_at = Carbon::now();
            $record->save();

            // Update record koreksi
            $correction->update([
                'attendance_record_id' => $record->id,
                'status' => ApprovalStatus::Approved,
                'approved_by' => $approverId,
                'approved_at' => Carbon::now(),
            ]);

            // Perekaman Jejak Forensik WORM ISO/IEC 27001:2022
            $this->auditLogService->log(
                action: 'attendance_correction_approved',
                auditable: $correction,
                oldValues: ['status' => ApprovalStatus::Pending->value],
                newValues: [
                    'status' => ApprovalStatus::Approved->value,
                    'attendance_record_id' => $record->id,
                    'clock_in_at' => $record->clock_in_at?->toIso8601String(),
                    'clock_out_at' => $record->clock_out_at?->toIso8601String(),
                    'late_minutes' => $record->late_minutes,
                    'duration_minutes' => $record->duration_minutes,
                    'is_adjusted' => true,
                ],
                userId: $approverId,
            );

            return $correction->fresh();
        });
    }
}
