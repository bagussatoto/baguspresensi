<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Action: Persetujuan Supervisor Terhadap Permohonan Tukar Shift (Tahap 2 - Final).
 */
class ApproveShiftSwapSupervisorAction
{
    public function execute(ShiftSwapRequest $swap, int $approverId): ShiftSwapRequest
    {
        if ($swap->status !== ShiftSwapStatus::PendingSupervisor) {
            throw new InvalidArgumentException('Permohonan tukar shift harus disetujui oleh rekan kerja terlebih dahulu sebelum diverifikasi supervisor.');
        }

        $swap->update([
            'status' => ShiftSwapStatus::Approved,
            'approved_by' => $approverId,
            'approved_at' => Carbon::now(),
        ]);

        return $swap;
    }
}
