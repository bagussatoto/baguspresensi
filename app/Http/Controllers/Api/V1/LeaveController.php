<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Services\AttendanceSettingService;
use App\Domains\Leave\Enums\LeaveType;
use App\Domains\Leave\Models\LeaveRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLeaveRequest;
use App\Http\Resources\Api\V1\LeaveResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Controller API RESTful V1: Pengajuan & Riwayat Cuti / Izin Karyawan.
 */
class LeaveController extends Controller
{
    /**
     * Ajukan permohonan cuti / izin baru.
     * POST /api/v1/leave/request
     */
    public function requestLeave(StoreLeaveRequest $request): JsonResponse
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        $settingService = app(AttendanceSettingService::class);
        $leaveType = LeaveType::from($request->input('leave_type'));
        $empId = (int) $request->input('employee_id');

        // 1. Kebijakan Kuota Cuti Tahunan
        if ($leaveType === LeaveType::Annual) {
            $quotaPolicy = $settingService->get('attendance.leave.quota_exhausted_policy', 'strict_block');
            $usedDays = (int) LeaveRequest::where('employee_id', $empId)
                ->where('leave_type', LeaveType::Annual)
                ->where('status', ApprovalStatus::Approved)
                ->whereYear('start_date', $startDate->year)
                ->sum('total_days');
            $maxQuota = 12;

            if ($quotaPolicy === 'strict_block' && ($usedDays + $totalDays) > $maxQuota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permohonan ditolak: Saldo cuti tahunan tidak mencukupi (Tersisa '.max(0, $maxQuota - $usedDays).' hari).',
                ], 422);
            }
        }

        // 2. Kebijakan Lampiran Surat Dokter
        if ($leaveType === LeaveType::Sick) {
            $sickPolicy = $settingService->get('attendance.leave.sick_attachment_policy', 'strict_always');
            $hasAttachment = $request->hasFile('attachment');

            if ($sickPolicy === 'strict_always' && ! $hasAttachment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lampiran surat keterangan dokter wajib diunggah untuk permohonan izin sakit.',
                ], 422);
            }

            if ($sickPolicy === 'grace_1_day' && $totalDays > 1 && ! $hasAttachment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin sakit lebih dari 1 hari wajib menyertakan surat keterangan dokter.',
                ], 422);
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            try {
                $uploadDir = public_path('uploads/leaves');
                File::ensureDirectoryExists($uploadDir);
                $file = $request->file('attachment');
                $fileName = 'leave_'.$request->input('employee_id').'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move($uploadDir, $fileName);
                $attachmentPath = "uploads/leaves/{$fileName}";
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $leave = LeaveRequest::create([
            'employee_id' => (int) $request->input('employee_id'),
            'leave_type' => $leaveType,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'reason' => (string) $request->input('reason'),
            'attachment_path' => $attachmentPath,
            'status' => ApprovalStatus::Pending,
        ]);

        $leave->load(['employee', 'approver']);

        return (new LeaveResource($leave))
            ->additional([
                'success' => true,
                'message' => 'Permohonan cuti/izin berhasil diajukan dan menunggu persetujuan.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Dapatkan riwayat permohonan cuti karyawan.
     * GET /api/v1/leave/history?employee_id=X
     */
    public function history(Request $request): JsonResponse
    {
        $employeeId = $request->query('employee_id');
        if (! $employeeId) {
            return response()->json(['message' => 'Parameter employee_id wajib disertakan.'], 400);
        }

        $leaves = LeaveRequest::with(['employee', 'approver'])
            ->where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return LeaveResource::collection($leaves)->response();
    }
}
