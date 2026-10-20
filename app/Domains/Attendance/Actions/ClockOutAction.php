<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Exceptions\NotClockedInException;
use App\Domains\Attendance\Exceptions\TooEarlyForClockOutException;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\AntiFraudService;
use App\Domains\Attendance\Services\GeoValidationService;
use App\Domains\Attendance\Services\SelfieStorageService;
use App\Domains\Attendance\Services\ShiftResolverService;
use App\Domains\Attendance\ValueObjects\Coordinate;
use Carbon\Carbon;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Action: Melakukan proses Clock-Out presensi pulang.
 * Menangani validasi radius pulang, durasi jam kerja aktif,
 * penguncian kuota jam kontrak fleksibel, tata kelola lembur pre-approval,
 * serta kalkulasi pulang cepat (early leave) dan lembur (overtime).
 */
class ClockOutAction
{
    public function __construct(
        protected GeoValidationService $geoValidationService,
        protected ShiftResolverService $shiftResolverService,
        protected AntiFraudService $antiFraudService,
        protected SelfieStorageService $selfieStorageService
    ) {}

    /**
     * Eksekusi presensi pulang (Clock Out).
     *
     * @throws MockLocationDetectedException
     * @throws NotClockedInException
     * @throws TooEarlyForClockOutException
     */
    public function execute(ClockOutDTO $dto): AttendanceRecord
    {
        // 1. Verifikasi deteksi manipulasi lokasi
        $this->antiFraudService->validateMockLocation($dto->isMockLocation, $dto->deviceInfo);

        // 2. Ambil entitas Karyawan
        $employee = Employee::findOrFail($dto->employeeId);

        // 3. Cari catatan clock-in aktif yang belum clock-out
        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->orderBy('clock_in_at', 'desc')
            ->first();

        if ($record === null) {
            throw new NotClockedInException;
        }

        // 4. Tentukan kantor cabang untuk validasi kepulangan
        $branchId = $dto->branchId ?? $record->branch_id;
        $branch = Branch::findOrFail($branchId);

        // 5. Koordinat GPS (Opsional saat pulang, tidak memblokir)
        $lat = $dto->latitude ?? (float) ($branch->latitude ?? -6.1585);
        $lng = $dto->longitude ?? (float) ($branch->longitude ?? 106.9080);
        $coordinate = new Coordinate($lat, $lng);
        try {
            $distanceMeters = $this->geoValidationService->calculateDistanceToBranch($coordinate, $branch);
        } catch (\Throwable) {
            $distanceMeters = 0.0;
        }

        // 6. Waktu scan presensi pulang
        $clockOutAt = $dto->clockOutAt ? Carbon::instance($dto->clockOutAt) : Carbon::now();

        // 8. Simpan foto selfie jika berformat base64 data URL
        $photoPath = $dto->photoPath;
        if ($photoPath !== null && str_starts_with($photoPath, 'data:image')) {
            $photoPath = $this->selfieStorageService->storeSelfie($photoPath, $employee->id, 'clock_out');
        }

        // 9. Hitung total durasi menit bekerja
        $clockInAt = Carbon::instance($record->clock_in_at);
        $durationMinutes = max(0, (int) round($clockInAt->diffInMinutes($clockOutAt)));
        $effectiveMinutes = $durationMinutes;

        // 10. Kalkulasi lembur (overtime) atau pulang lebih awal (early leave)
        $overtimeMinutes = 0;
        $earlyLeaveMinutes = 0;

        if ($record->work_type === 'flexible') {
            // Mode Fleksibel (Desain): Berbasis target jam kontrak harian & persetujuan lembur
            $targetHours = 8;
            if ($record->rosterJadwal !== null) {
                $targetHours = (int) $record->rosterJadwal->contract_hours;
            } elseif ($employee->default_contract_hours !== null) {
                $targetHours = (int) $employee->default_contract_hours;
            }
            $targetMinutes = $targetHours * 60;

            if ($durationMinutes < $targetMinutes) {
                // Jika belum memenuhi kuota kontrak, wajib ada persetujuan izin pulang awal
                if (! $record->isEarlyLeaveApproved() && ! $dto->forceEarlyLeave) {
                    throw new TooEarlyForClockOutException(
                        "Durasi kerja ({$durationMinutes} menit) belum memenuhi target kontrak ({$targetMinutes} menit). Silakan ajukan izin pulang awal terlebih dahulu."
                    );
                }
                $earlyLeaveMinutes = $targetMinutes - $durationMinutes;
                $effectiveMinutes = $durationMinutes;
                $overtimeMinutes = 0;
            } else {
                // Durasi >= target_minutes: Lembur hanya dicatat jika overtime_status === 'approved'
                if ($record->isOvertimeApproved()) {
                    $rawOvertime = $durationMinutes - $targetMinutes;
                    $approvedCap = $record->overtime_approved_minutes > 0 ? $record->overtime_approved_minutes : $rawOvertime;
                    $overtimeMinutes = min($rawOvertime, $approvedCap);
                    $effectiveMinutes = $targetMinutes + $overtimeMinutes;
                } else {
                    $overtimeMinutes = 0;
                    $effectiveMinutes = $targetMinutes; // Proteksi anggaran: jam efektif dibatasi maksimal durasi kontrak
                }
                $earlyLeaveMinutes = 0;
            }
        } elseif ($record->shift !== null) {
            // Mode Shift (Pabrik / Warnet): Berbasis jam selesai shift
            $workDate = Carbon::parse($record->work_date);
            $shiftEnd = $this->shiftResolverService->getShiftEndTimestamp($record->shift, $workDate);

            if ($clockOutAt->greaterThan($shiftEnd)) {
                $diff = (int) round($shiftEnd->diffInMinutes($clockOutAt));
                // Hanya hitung lembur jika melebihi batas 15 menit dari jam selesai shift
                $overtimeMinutes = $diff >= 15 ? $diff : 0;
            } elseif ($clockOutAt->lessThan($shiftEnd)) {
                $earlyLeaveMinutes = (int) round($clockOutAt->diffInMinutes($shiftEnd));
            }
        }

        // 11. Simpan perubahan ke record
        $updatedNotes = $record->notes;
        if ($dto->notes !== null && $dto->notes !== '') {
            $updatedNotes = $updatedNotes ? "{$updatedNotes} | {$dto->notes}" : $dto->notes;
        }

        $record->update([
            'clock_out_at' => $clockOutAt,
            'clock_out_lat' => $coordinate->latitude,
            'clock_out_lng' => $coordinate->longitude,
            'clock_out_distance_meters' => $distanceMeters,
            'clock_out_photo_path' => $photoPath ?? $record->clock_out_photo_path,
            'duration_minutes' => $durationMinutes,
            'effective_minutes' => $effectiveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'notes' => $updatedNotes,
        ]);

        return $record->fresh();
    }
}
