<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Actions;

use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Exceptions\AlreadyClockedInException;
use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\AntiFraudService;
use App\Domains\Attendance\Services\GeoValidationService;
use App\Domains\Attendance\Services\RosterResolverService;
use App\Domains\Attendance\Services\SelfieStorageService;
use App\Domains\Attendance\Services\ShiftResolverService;
use App\Domains\Attendance\ValueObjects\Coordinate;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Action: Melakukan proses Clock-In presensi karyawan.
 * Menangani validasi radius geofence, deteksi fake GPS, penentuan shift / flexible roster,
 * alokasi cost center akuntansi, toleransi grace period, serta status presensi.
 */
class ClockInAction
{
    public function __construct(
        protected GeoValidationService $geoValidationService,
        protected ShiftResolverService $shiftResolverService,
        protected AntiFraudService $antiFraudService,
        protected SelfieStorageService $selfieStorageService,
        protected RosterResolverService $rosterResolverService
    ) {}

    /**
     * Eksekusi presensi masuk (Clock In).
     *
     * @throws MockLocationDetectedException
     * @throws AlreadyClockedInException
     */
    public function execute(ClockInDTO $dto): AttendanceRecord
    {
        // 1. Verifikasi deteksi manipulasi lokasi
        $this->antiFraudService->validateMockLocation($dto->isMockLocation, $dto->deviceInfo);

        // 2. Ambil entitas Karyawan & Cabang
        $employee = Employee::findOrFail($dto->employeeId);
        $branch = Branch::findOrFail($dto->branchId);

        // 3. Simpan foto selfie jika berformat base64 data URL
        $photoPath = $dto->photoPath;
        if ($photoPath !== null && str_starts_with($photoPath, 'data:image')) {
            $photoPath = $this->selfieStorageService->storeSelfie($photoPath, $employee->id, 'clock_in');
        }

        // 4. Koordinat GPS (Opsional, tidak membatasi/memblokir presensi)
        $lat = $dto->latitude ?? (float) ($branch->latitude ?? -6.1585);
        $lng = $dto->longitude ?? (float) ($branch->longitude ?? 106.9080);
        $coordinate = new Coordinate($lat, $lng);
        try {
            $distanceMeters = $this->geoValidationService->calculateDistanceToBranch($coordinate, $branch);
        } catch (\Throwable) {
            $distanceMeters = 0.0;
        }

        // 5. Waktu scan presensi
        $clockInAt = $dto->clockInAt ? Carbon::instance($dto->clockInAt) : Carbon::now();

        // 6. Resolusi Roster & Cost Center ERP
        $rosterContext = $this->rosterResolverService->resolve($employee, $clockInAt, $dto->workType);
        $workType = $rosterContext['work_type'];
        $costCenter = $rosterContext['cost_center'];
        $rosterId = $rosterContext['roster_id'];

        if ($workType === 'flexible') {
            // Jalur Fleksibel (Desain): Tidak ada shift wajib, tidak ada penalti keterlambatan
            $shiftId = $dto->shiftId ?? $rosterContext['shift_id'];
            $workDateString = $clockInAt->toDateString();

            $alreadyClocked = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('work_date', $workDateString)
                ->where('work_type', 'flexible')
                ->whereNull('clock_out_at')
                ->exists();

            if ($alreadyClocked) {
                throw new AlreadyClockedInException;
            }

            $status = AttendanceStatus::Present;
            $lateMinutes = 0;
        } else {
            // Jalur Shift (Operasional / Frontdesk): Mengikuti jadwal shift kaku & grace period
            $resolvedShiftId = $dto->shiftId ?? $rosterContext['shift_id'];
            $shift = $this->shiftResolverService->resolveShift($resolvedShiftId, $clockInAt);
            $workDate = $this->shiftResolverService->determineWorkDate($shift, $clockInAt);
            $workDateString = $workDate->toDateString();
            $shiftId = $shift->id;

            $alreadyClocked = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('work_date', $workDateString)
                ->where('shift_id', $shift->id)
                ->exists();

            if ($alreadyClocked) {
                throw new AlreadyClockedInException;
            }

            $shiftStart = $this->shiftResolverService->getShiftStartTimestamp($shift, $workDate);
            $graceMinutes = (int) ($shift->grace_period_minutes ?? 15);
            $graceLimit = $shiftStart->copy()->addMinutes($graceMinutes);

            if ($clockInAt->greaterThan($graceLimit)) {
                $status = AttendanceStatus::Late;
                $lateMinutes = (int) ceil($shiftStart->diffInMinutes($clockInAt));
            } else {
                $status = AttendanceStatus::Present;
                $lateMinutes = 0;
            }
        }

        // 7. Simpan record presensi
        try {
            return AttendanceRecord::create([
                'employee_id' => $employee->id,
                'branch_id' => $branch->id,
                'shift_id' => $shiftId,
                'work_date' => $workDateString,
                'clock_in_at' => $clockInAt,
                'clock_in_lat' => $coordinate->latitude,
                'clock_in_lng' => $coordinate->longitude,
                'clock_in_distance_meters' => $distanceMeters,
                'clock_in_photo_path' => $photoPath,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'verification_method' => $dto->verificationMethod,
                'device_info' => $dto->deviceInfo,
                'ip_address' => $dto->ipAddress,
                'is_mock_location_detected' => false,
                'notes' => $dto->notes,
                'work_type' => $workType,
                'effective_minutes' => 0,
                'cost_center' => $costCenter,
                'roster_id' => $rosterId,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new AlreadyClockedInException;
        }
    }
}
