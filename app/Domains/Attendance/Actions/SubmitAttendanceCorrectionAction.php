<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Action: Mengajukan Permohonan Koreksi Presensi (Lupa Absen / Kendala HP).
 */
class SubmitAttendanceCorrectionAction
{
    /**
     * @param array{
     *     employee_id: int,
     *     work_date: string,
     *     correction_type: string,
     *     proposed_clock_in_at?: ?string,
     *     proposed_clock_out_at?: ?string,
     *     reason: string,
     *     attachment_path?: ?string,
     *     shift_id?: ?int,
     *     attendance_record_id?: ?int
     * } $data
     */
    public function execute(array $data): AttendanceCorrection
    {
        $employeeId = (int) $data['employee_id'];
        $workDate = Carbon::parse($data['work_date'])->toDateString();
        $correctionType = $data['correction_type'] ?? 'both';

        $proposedClockIn = ! empty($data['proposed_clock_in_at'])
            ? Carbon::parse($data['proposed_clock_in_at'])
            : null;

        $proposedClockOut = ! empty($data['proposed_clock_out_at'])
            ? Carbon::parse($data['proposed_clock_out_at'])
            : null;

        if ($correctionType === 'both' && ! $proposedClockIn && ! $proposedClockOut) {
            throw new InvalidArgumentException('Jam masuk atau jam pulang yang diusulkan wajib diisi.');
        }

        if ($correctionType === 'clock_in' && ! $proposedClockIn) {
            throw new InvalidArgumentException('Usulan jam masuk wajib diisi.');
        }

        if ($correctionType === 'clock_out' && ! $proposedClockOut) {
            throw new InvalidArgumentException('Usulan jam pulang wajib diisi.');
        }

        // Cari attendance record jika belum dipass eksplisit
        $attendanceRecordId = $data['attendance_record_id'] ?? null;
        if (! $attendanceRecordId) {
            $existingRecord = AttendanceRecord::where('employee_id', $employeeId)
                ->where('work_date', $workDate)
                ->first();
            $attendanceRecordId = $existingRecord?->id;
        }

        $shiftId = $data['shift_id'] ?? null;
        if (! $shiftId && $attendanceRecordId) {
            $record = AttendanceRecord::find($attendanceRecordId);
            $shiftId = $record?->shift_id;
        }

        return AttendanceCorrection::create([
            'employee_id' => $employeeId,
            'attendance_record_id' => $attendanceRecordId,
            'shift_id' => $shiftId,
            'work_date' => $workDate,
            'correction_type' => $correctionType,
            'proposed_clock_in_at' => $proposedClockIn,
            'proposed_clock_out_at' => $proposedClockOut,
            'reason' => $data['reason'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'status' => ApprovalStatus::Pending,
        ]);
    }
}
