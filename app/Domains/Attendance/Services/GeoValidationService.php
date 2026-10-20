<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Exceptions\OutsideGeofenceException;
use App\Domains\Attendance\ValueObjects\Coordinate;
use DomainException;
use Modules\MasterData\Domain\Models\Branch;

/**
 * Service untuk memvalidasi posisi GPS karyawan terhadap geofence cabang kantor.
 */
class GeoValidationService
{
    /**
     * Validasi apakah posisi karyawan berada di dalam batas radius cabang.
     *
     * @return array{is_valid: bool, distance_meters: float, allowed_radius_meters: float}
     *
     * @throws OutsideGeofenceException
     * @throws DomainException
     */
    public function validateWithinBranch(Coordinate $employeeLocation, Branch $branch): array
    {
        if ($branch->latitude === null || $branch->longitude === null) {
            throw new DomainException(
                "Kantor cabang '{$branch->name}' belum memiliki koordinat GPS yang tervalidasi."
            );
        }

        $branchLocation = new Coordinate((float) $branch->latitude, (float) $branch->longitude);
        $distance = $employeeLocation->distanceTo($branchLocation);
        $allowedRadius = (float) ($branch->radius_meters ?? 100.0);

        if ($distance > $allowedRadius) {
            throw new OutsideGeofenceException(
                distanceMeters: $distance,
                allowedRadiusMeters: $allowedRadius,
                branchName: $branch->name
            );
        }

        return [
            'is_valid' => true,
            'distance_meters' => $distance,
            'allowed_radius_meters' => $allowedRadius,
        ];
    }

    /**
     * Hitung jarak meter antara posisi pegawai dengan kantor cabang.
     */
    public function calculateDistanceToBranch(Coordinate $employeeLocation, Branch $branch): float
    {
        if ($branch->latitude === null || $branch->longitude === null) {
            return 0.0;
        }

        $branchLocation = new Coordinate((float) $branch->latitude, (float) $branch->longitude);

        return $employeeLocation->distanceTo($branchLocation);
    }
}
