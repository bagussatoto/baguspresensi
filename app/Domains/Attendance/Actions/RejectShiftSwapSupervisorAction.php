<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Action: Penolakan Supervisor Terhadap Permohonan Tukar Shift.
 */
class RejectShiftSwapSupervisorAction
{
    public function execute(
        ShiftSwapRequest $swap,
        int $approverId,
        ?string $rejectionNote = null
    ): ShiftSwapRequest {
        if ($swap->status !== ShiftSwapStatus::PendingSupervisor) {
            throw new InvalidArgumentException('Permohonan tukar shift ini tidak dalam status menunggu persetujuan supervisor.');
        }

        $swap->update([
            'status' => ShiftSwapStatus::Rejected,
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
            'rejection_note' => $rejectionNote !== null ? trim($rejectionNote) : null,
        ]);

        return $swap;
    }
}
