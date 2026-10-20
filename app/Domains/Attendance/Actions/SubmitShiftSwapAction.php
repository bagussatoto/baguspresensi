<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use InvalidArgumentException;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Action: Mengajukan Permohonan Tukar Shift Antar Rekan Kerja Satu Cabang.
 */
class SubmitShiftSwapAction
{
    /**
     * @param array{
     *     requester_id: int,
     *     target_employee_id: int,
     *     requester_shift_id: int,
     *     requester_date: string,
     *     target_shift_id: int,
     *     target_date: string,
     *     reason: string
     * } $data
     */
    public function execute(array $data): ShiftSwapRequest
    {
        $requesterId = (int) $data['requester_id'];
        $targetEmployeeId = (int) $data['target_employee_id'];

        if ($requesterId === $targetEmployeeId) {
            throw new InvalidArgumentException('Anda tidak dapat mengajukan tukar shift dengan diri sendiri.');
        }

        $requester = Employee::findOrFail($requesterId);
        $targetEmployee = Employee::findOrFail($targetEmployeeId);

        if (! $requester->branch_id || ! $targetEmployee->branch_id || $requester->branch_id !== $targetEmployee->branch_id) {
            throw new InvalidArgumentException('Tukar shift hanya dapat dilakukan antar rekan kerja pada cabang yang sama.');
        }

        $requesterDate = Carbon::parse($data['requester_date'])->toDateString();
        $targetDate = Carbon::parse($data['target_date'])->toDateString();
        $today = Carbon::today()->toDateString();

        if ($requesterDate < $today) {
            throw new InvalidArgumentException('Tanggal shift yang diajukan tidak boleh di masa lampau.');
        }

        $requesterShift = Shift::findOrFail((int) $data['requester_shift_id']);
        $targetShift = Shift::findOrFail((int) $data['target_shift_id']);

        $existingPending = ShiftSwapRequest::where('requester_id', $requesterId)
            ->where('requester_date', $requesterDate)
            ->whereIn('status', [ShiftSwapStatus::PendingPeer, ShiftSwapStatus::PendingSupervisor])
            ->exists();

        if ($existingPending) {
            throw new InvalidArgumentException('Anda sudah memiliki permohonan tukar shift yang sedang aktif untuk tanggal tersebut.');
        }

        return ShiftSwapRequest::create([
            'branch_id' => $requester->branch_id,
            'requester_id' => $requesterId,
            'target_employee_id' => $targetEmployeeId,
            'requester_shift_id' => $requesterShift->id,
            'requester_date' => $requesterDate,
            'target_shift_id' => $targetShift->id,
            'target_date' => $targetDate,
            'reason' => trim($data['reason']),
            'status' => ShiftSwapStatus::PendingPeer,
        ]);
    }
}
