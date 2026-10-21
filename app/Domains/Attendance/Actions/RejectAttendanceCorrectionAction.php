<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Services\AuditLogService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * Action: Menolak Permohonan Koreksi Presensi dengan Catatan Resmi & Jejak Audit.
 */
class RejectAttendanceCorrectionAction
{
    public function __construct(
        public AuditLogService $auditLogService
    ) {}

    public function execute(AttendanceCorrection $correction, int $approverId, string $rejectionNote): AttendanceCorrection
    {
        $correction->loadMissing(['employee']);
        $employee = $correction->employee;

        // Proteksi Anti Self-Approval / Self-Action (Prinsip SoD Maker-Checker)
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
                throw new AuthorizationException('Pelanggaran SoD: Anda tidak diizinkan menolak permohonan koreksi presensi Anda sendiri.');
            }
        } else {
            // Approver adalah Employee langsung (Supervisor ID)
            if ($approverId === (int) $correction->employee_id) {
                throw new AuthorizationException('Pelanggaran SoD: Anda tidak diizinkan menolak permohonan koreksi presensi Anda sendiri.');
            }
        }

        return DB::transaction(function () use ($correction, $approverId, $rejectionNote): AttendanceCorrection {
            $correction->update([
                'status' => ApprovalStatus::Rejected,
                'approved_by' => $approverId,
                'approved_at' => Carbon::now(),
                'rejection_note' => $rejectionNote,
            ]);

            // Perekaman Jejak Forensik WORM ISO/IEC 27001:2022
            $this->auditLogService->log(
                action: 'attendance_correction_rejected',
                auditable: $correction,
                oldValues: ['status' => ApprovalStatus::Pending->value],
                newValues: [
                    'status' => ApprovalStatus::Rejected->value,
                    'rejection_note' => $rejectionNote,
                ],
                userId: $approverId,
            );

            return $correction->fresh();
        });
    }
}
