<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\ValueObjects\Coordinate;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Http\Request;

/**
 * Service untuk mendeteksi indikasi kecurangan presensi:
 * Fake GPS / Mock Location provider, teleportasi lokasi tidak wajar, dan audit trail perangkat.
 */
class AntiFraudService
{
    /**
     * Ambang batas kecepatan perpindahan maksimal yang wajar (350 km/jam).
     */
    public const MAX_REASONABLE_SPEED_KMH = 350.0;

    /**
     * Ambang batas jarak minimal untuk memicu kalkulasi kecepatan tidak wajar (30 km).
     */
    public const MIN_TELEPORT_DISTANCE_METERS = 30000.0;

    /**
     * Validasi apakah terdeteksi provider Mock Location / Fake GPS dari perangkat client.
     *
     * @throws MockLocationDetectedException
     */
    public function validateMockLocation(bool $isMockProvider, ?array $deviceInfo = null): void
    {
        if ($isMockProvider) {
            throw new MockLocationDetectedException('Perangkat melaporkan flag lokasi palsu (is_mock_provider = true).');
        }

        if ($deviceInfo !== null) {
            $mockKeys = ['is_mock', 'mock_location', 'is_mock_provider', 'mock_gps', 'allow_mock_location'];
            foreach ($mockKeys as $key) {
                if (isset($deviceInfo[$key]) && filter_var($deviceInfo[$key], FILTER_VALIDATE_BOOLEAN)) {
                    throw new MockLocationDetectedException("Parameter perangkat '{$key}' mengindikasikan penggunaan Fake GPS.");
                }
            }
        }
    }

    /**
     * Validasi kemungkinan anomali teleportasi fisik (kecepatan perpindahan antar presensi yang mustahil).
     *
     * @throws DomainException
     */
    public function validateTeleportation(
        Coordinate $newLocation,
        ?CarbonInterface $newTime = null,
        ?AttendanceRecord $previousRecord = null
    ): void {
        if ($previousRecord === null) {
            return;
        }

        $prevLat = $previousRecord->clock_out_lat ?? $previousRecord->clock_in_lat;
        $prevLng = $previousRecord->clock_out_lng ?? $previousRecord->clock_in_lng;

        if ($prevLat === null || $prevLng === null) {
            return;
        }

        $prevCoord = new Coordinate((float) $prevLat, (float) $prevLng);
        $distanceMeters = $newLocation->distanceTo($prevCoord);

        if ($distanceMeters < self::MIN_TELEPORT_DISTANCE_METERS) {
            return;
        }

        $currentTime = $newTime ? Carbon::instance($newTime) : Carbon::now();
        $prevTime = $previousRecord->clock_out_at ?? $previousRecord->clock_in_at;

        if ($prevTime === null) {
            return;
        }

        $diffMinutes = max(1, (int) abs($currentTime->diffInMinutes($prevTime)));
        $diffHours = $diffMinutes / 60.0;
        $speedKmH = ($distanceMeters / 1000.0) / $diffHours;

        if ($speedKmH > self::MAX_REASONABLE_SPEED_KMH) {
            $distanceKm = round($distanceMeters / 1000.0, 1);
            $speedFormatted = round($speedKmH, 1);
            throw new DomainException(
                "Terdeteksi anomali perpindahan lokasi ekstrim: {$distanceKm} km dalam {$diffMinutes} menit ".
                "(Kecepatan estimasi: {$speedFormatted} km/jam). Presensi diblokir untuk mencegah manipulasi GPS."
            );
        }
    }

    /**
     * Ekstrak informasi audit perangkat dari HTTP Request.
     *
     * @return array<string, mixed>
     */
    public function extractAuditInfo(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_brand' => $request->header('X-Device-Brand', $request->input('device_brand')),
            'device_model' => $request->header('X-Device-Model', $request->input('device_model')),
            'app_version' => $request->header('X-App-Version', $request->input('app_version')),
            'platform' => $request->header('X-Device-Platform', $request->input('device_platform', 'web')),
            'is_mock_provider' => (bool) $request->input('is_mock_location', false),
        ];
    }
}
