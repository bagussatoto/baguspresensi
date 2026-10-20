<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Models\AttendanceCorrection;
use Carbon\Carbon;

/**
 * Action: Menolak Permohonan Koreksi Presensi dengan Catatan.
 */
class RejectAttendanceCorrectionAction
{
    public function execute(AttendanceCorrection $correction, int $approverId, string $rejectionNote): AttendanceCorrection
    {
        $correction->update([
            'status' => ApprovalStatus::Rejected,
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
            'rejection_note' => $rejectionNote,
        ]);

        return $correction->fresh();
    }
}
