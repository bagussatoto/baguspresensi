<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Action: Respon Rekan Kerja Terhadap Permohonan Tukar Shift (Tahap 1).
 */
class RespondShiftSwapPeerAction
{
    public function execute(
        ShiftSwapRequest $swap,
        int $targetEmployeeId,
        bool $agree,
        ?string $note = null
    ): ShiftSwapRequest {
        if ($swap->target_employee_id !== $targetEmployeeId) {
            throw new InvalidArgumentException('Anda tidak memiliki wewenang untuk menanggapi permohonan tukar shift ini.');
        }

        if ($swap->status !== ShiftSwapStatus::PendingPeer) {
            throw new InvalidArgumentException('Permohonan tukar shift ini sudah tidak dalam status menunggu persetujuan rekan kerja.');
        }

        $swap->update([
            'status' => $agree ? ShiftSwapStatus::PendingSupervisor : ShiftSwapStatus::PeerRejected,
            'peer_responded_at' => Carbon::now(),
            'peer_note' => $note !== null ? trim($note) : null,
        ]);

        return $swap;
    }
}
