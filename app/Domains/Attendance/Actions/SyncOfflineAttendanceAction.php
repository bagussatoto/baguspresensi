<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\AuditLogService;
use App\Domains\Attendance\Services\SelfieStorageService;
use App\Domains\Attendance\Services\ShiftResolverService;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Action: Sinkronisasi Presensi Offline PWA dengan Proteksi Anti-Replay & Idempotensi (ISO 27001).
 *
 * Menerima buffer antrean presensi saat perangkat karyawan kembali online.
 * Melakukan verifikasi nonce kriptografis sekali pakai, toleransi time-window,
 * penyimpanan foto selfie offline, serta perekaman audit trail WORM.
 */
class SyncOfflineAttendanceAction
{
    public function __construct(
        protected ShiftResolverService $shiftResolverService,
        protected SelfieStorageService $selfieStorageService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Eksekusi sinkronisasi antrean batch / single offline attendance.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function execute(array $payload, ?int $actorUserId = null): array
    {
        $items = isset($payload['items']) && is_array($payload['items'])
            ? $payload['items']
            : [$payload];

        $results = [];
        $syncedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($items as $index => $item) {
            $nonce = trim((string) ($item['offline_nonce'] ?? ''));

            if (empty($nonce)) {
                $results[] = [
                    'index' => $index,
                    'status' => 'failed',
                    'message' => 'Parameter offline_nonce wajib diisi untuk menjamin integritas anti-replay.',
                ];
                $failedCount++;

                continue;
            }

            // 1. Anti-Replay & Idempotency Guard: Cek apakah nonce sudah pernah diproses sebelumnya
            $existingNonceRecord = AttendanceRecord::where('offline_nonce', $nonce)->first();
            if ($existingNonceRecord !== null) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'already_synced',
                    'record_id' => $existingNonceRecord->id,
                    'message' => 'Data presensi dengan nonce ini telah tersinkronisasi sebelumnya (Idempotent).',
                ];
                $skippedCount++;

                continue;
            }

            // 2. Validasi Employee
            $employeeId = (int) ($item['employee_id'] ?? 0);
            $employee = Employee::find($employeeId);
            if (! $employee) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'failed',
                    'message' => "Karyawan dengan ID {$employeeId} tidak ditemukan di sistem.",
                ];
                $failedCount++;

                continue;
            }

            // 3. Validasi & Parsing Waktu Clock-In Offline
            $clockInRaw = $item['clock_in_at'] ?? null;
            if (! $clockInRaw) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'failed',
                    'message' => 'Waktu presensi offline (clock_in_at) wajib disertakan.',
                ];
                $failedCount++;

                continue;
            }

            try {
                $clockInAt = Carbon::parse($clockInRaw);
            } catch (\Throwable) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'failed',
                    'message' => 'Format waktu clock_in_at tidak valid.',
                ];
                $failedCount++;

                continue;
            }

            // Time-window integrity check: Tolak jika timestamp di masa depan (> 5 menit)
            if ($clockInAt->greaterThan(now()->addMinutes(5))) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'failed',
                    'message' => 'Manipulasi waktu terdeteksi: Waktu presensi berada di masa depan.',
                ];
                $failedCount++;

                continue;
            }

            $workDate = $item['work_date'] ?? $clockInAt->toDateString();

            // Cek apakah karyawan sudah memiliki absensi di tanggal tersebut
            $existingDayRecord = AttendanceRecord::where('employee_id', $employee->id)
                ->where('work_date', $workDate)
                ->first();

            if ($existingDayRecord !== null) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'already_clocked_in',
                    'record_id' => $existingDayRecord->id,
                    'message' => "Karyawan sudah tercatat presensi pada tanggal {$workDate}.",
                ];
                $skippedCount++;

                continue;
            }

            // 4. Resolusi Cabang & Shift
            $branchId = (int) ($item['branch_id'] ?? $employee->branch_id ?? 1);
            $branch = Branch::find($branchId) ?? Branch::first();

            $shiftId = ! empty($item['shift_id']) ? (int) $item['shift_id'] : null;
            $shift = $shiftId ? Shift::find($shiftId) : null;
            if (! $shift) {
                $shift = $this->shiftResolverService->resolveShiftForEmployee($employee, $clockInAt);
            }

            // Hitung status keterlambatan
            $lateMinutes = 0;
            $status = AttendanceStatus::Present;

            if ($shift) {
                $shiftStart = $this->shiftResolverService->getShiftStartTimestamp($shift, $clockInAt);
                $graceMinutes = (int) ($shift->grace_period_minutes ?? 15);
                $graceLimit = $shiftStart->copy()->addMinutes($graceMinutes);

                if ($clockInAt->greaterThan($graceLimit)) {
                    $status = AttendanceStatus::Late;
                    $lateMinutes = (int) ceil($shiftStart->diffInMinutes($clockInAt));
                }
            }

            // 5. Simpan Foto Selfie jika format base64
            $photoPath = $item['photo_path'] ?? $item['photo'] ?? null;
            if ($photoPath !== null && str_starts_with($photoPath, 'data:image')) {
                $photoPath = $this->selfieStorageService->storeSelfie($photoPath, $employee->id, 'offline_sync');
            }

            // 6. Transaksi Penyimpanan & WORM Audit Trail
            try {
                $record = DB::transaction(function () use (
                    $employee,
                    $branch,
                    $shift,
                    $workDate,
                    $clockInAt,
                    $photoPath,
                    $status,
                    $lateMinutes,
                    $nonce,
                    $item,
                    $actorUserId
                ): AttendanceRecord {
                    $livenessScore = isset($item['liveness_score']) && $item['liveness_score'] !== null
                        ? (float) $item['liveness_score']
                        : null;

                    $record = AttendanceRecord::create([
                        'employee_id' => $employee->id,
                        'branch_id' => $branch?->id ?? 1,
                        'shift_id' => $shift?->id,
                        'work_date' => $workDate,
                        'clock_in_at' => $clockInAt,
                        'clock_in_lat' => isset($item['latitude']) ? (float) $item['latitude'] : null,
                        'clock_in_lng' => isset($item['longitude']) ? (float) $item['longitude'] : null,
                        'clock_in_distance_meters' => isset($item['distance_meters']) ? (float) $item['distance_meters'] : 0.0,
                        'clock_in_photo_path' => $photoPath,
                        'status' => $status,
                        'late_minutes' => $lateMinutes,
                        'verification_method' => VerificationMethod::OfflineSync,
                        'device_info' => $item['device_info'] ?? [
                            'user_agent' => Request::userAgent(),
                            'platform' => 'PWA Offline Buffer Sync',
                        ],
                        'ip_address' => $item['ip_address'] ?? Request::ip(),
                        'is_mock_location_detected' => (bool) ($item['is_mock_location'] ?? false),
                        'liveness_score' => $livenessScore,
                        'is_offline_sync' => true,
                        'offline_nonce' => $nonce,
                        'sync_recorded_at' => Carbon::now(),
                        'notes' => trim(($item['notes'] ?? '').' [PWA Offline Sync - Anti-Replay Protected]'),
                        'work_type' => $item['work_type'] ?? 'shift',
                        'effective_minutes' => 0,
                    ]);

                    // Perekaman jejak audit forensik WORM (ISO 27001 A.8.15)
                    $validUserId = ($actorUserId !== null && User::where('id', $actorUserId)->exists())
                        ? $actorUserId
                        : null;

                    $this->auditLogService->log(
                        action: 'offline_attendance_synced',
                        auditable: $record,
                        oldValues: null,
                        newValues: [
                            'offline_nonce' => $nonce,
                            'clock_in_at' => $clockInAt->toIso8601String(),
                            'sync_recorded_at' => Carbon::now()->toIso8601String(),
                            'liveness_score' => $livenessScore,
                            'status' => $status->value,
                            'employee_id' => $employee->id,
                            'branch_id' => $branch?->id ?? 1,
                        ],
                        userId: $validUserId
                    );

                    return $record;
                });

                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'synced',
                    'record_id' => $record->id,
                    'status_label' => $record->status->label(),
                    'clock_in_at' => $record->clock_in_at?->format('H:i:s'),
                    'liveness_score' => $record->liveness_score,
                ];
                $syncedCount++;
            } catch (\Throwable $e) {
                $results[] = [
                    'index' => $index,
                    'offline_nonce' => $nonce,
                    'status' => 'failed',
                    'message' => 'Gagal memproses transaksi: '.$e->getMessage(),
                ];
                $failedCount++;
            }
        }

        return [
            'success' => $syncedCount > 0 || ($skippedCount > 0 && $failedCount === 0),
            'summary' => [
                'total' => count($items),
                'synced' => $syncedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
            ],
            'results' => $results,
        ];
    }
}
