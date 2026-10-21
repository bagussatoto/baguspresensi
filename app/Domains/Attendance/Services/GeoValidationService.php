<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Exceptions\OutsideGeofenceException;
use App\Domains\Attendance\ValueObjects\Coordinate;
use DomainException;
use Modules\MasterData\Domain\Models\Branch;

/**
 * Service untuk memvalidasi posisi GPS karyawan terhadap geofence cabang kantor
 * dengan dukungan Multi-Factor Geofencing (Wi-Fi BSSID & Whitelist IP Jaringan).
 */
class GeoValidationService
{
    /**
     * Validasi apakah posisi karyawan berada di dalam batas radius cabang
     * atau terhubung ke jaringan resmi outlet jika terjadi GPS drift di dalam ruangan.
     *
     * @return array{
     *     is_valid: bool,
     *     distance_meters: float,
     *     allowed_radius_meters: float,
     *     validated_by: string,
     *     verification_method: VerificationMethod,
     *     notes: ?string
     * }
     *
     * @throws OutsideGeofenceException
     * @throws DomainException
     */
    public function validateWithinBranch(
        Coordinate $employeeLocation,
        Branch $branch,
        ?string $wifiBssid = null,
        ?string $clientIp = null
    ): array {
        if ($branch->latitude === null || $branch->longitude === null) {
            throw new DomainException(
                "Kantor cabang '{$branch->name}' belum memiliki koordinat GPS yang tervalidasi."
            );
        }

        $branchLocation = new Coordinate((float) $branch->latitude, (float) $branch->longitude);
        $distance = $employeeLocation->distanceTo($branchLocation);
        $allowedRadius = (float) ($branch->radius_meters ?? 100.0);

        if ($distance <= $allowedRadius) {
            return [
                'is_valid' => true,
                'distance_meters' => $distance,
                'allowed_radius_meters' => $allowedRadius,
                'validated_by' => 'gps',
                'verification_method' => VerificationMethod::GpsSelfie,
                'notes' => null,
            ];
        }

        // Evaluasi toleransi GPS Drift via Multi-Factor Wi-Fi BSSID atau Whitelist IP Cabang
        $isWifiValid = $branch->matchesWifiBssid($wifiBssid);
        $isIpValid = $branch->matchesClientIp($clientIp);

        if ($isWifiValid || $isIpValid) {
            $reason = $isWifiValid ? 'BSSID Wi-Fi resmi cabang' : 'IP Jaringan resmi cabang';

            return [
                'is_valid' => true,
                'distance_meters' => $distance,
                'allowed_radius_meters' => $allowedRadius,
                'validated_by' => $isWifiValid ? 'wifi_bssid' : 'network_ip',
                'verification_method' => VerificationMethod::WifiGeofence,
                'notes' => "Tervalidasi via Multi-Factor Geofence ({$reason}) - Toleransi GPS Drift Indoor ({$distance}m > {$allowedRadius}m)",
            ];
        }

        throw new OutsideGeofenceException(
            distanceMeters: $distance,
            allowedRadiusMeters: $allowedRadius,
            branchName: $branch->name
        );
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
