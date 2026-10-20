<?php

namespace Tests\Unit;

use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\AntiFraudService;
use App\Domains\Attendance\Services\SelfieStorageService;
use App\Domains\Attendance\ValueObjects\Coordinate;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AntiFraudAndSelfieTest extends TestCase
{
    public function test_mock_location_throws_when_flag_is_true(): void
    {
        $service = new AntiFraudService;

        $this->expectException(MockLocationDetectedException::class);
        $service->validateMockLocation(true);
    }

    public function test_mock_location_throws_when_device_info_indicates_mock_gps(): void
    {
        $service = new AntiFraudService;

        $this->expectException(MockLocationDetectedException::class);
        $service->validateMockLocation(false, ['is_mock_provider' => '1', 'device_model' => 'Xiaomi']);
    }

    public function test_teleportation_detects_impossible_speed_and_distance(): void
    {
        $service = new AntiFraudService;

        // Rekaman sebelumnya di Jakarta (-6.1585, 106.9080) pada jam 07:00
        $record = new AttendanceRecord([
            'clock_in_lat' => -6.15850000,
            'clock_in_lng' => 106.90800000,
            'clock_in_at' => Carbon::parse('2026-10-11 07:00:00'),
        ]);

        // Lokasi baru di Bandung (-6.9175, 107.6191) ~120 km hanya dalam selisih 5 menit (jam 07:05)
        $newLocation = new Coordinate(-6.9175, 107.6191);
        $newTime = Carbon::parse('2026-10-11 07:05:00');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('anomali perpindahan lokasi ekstrim');
        $service->validateTeleportation($newLocation, $newTime, $record);
    }

    public function test_teleportation_passes_for_reasonable_commute(): void
    {
        $service = new AntiFraudService;

        $record = new AttendanceRecord([
            'clock_in_lat' => -6.15850000,
            'clock_in_lng' => 106.90800000,
            'clock_in_at' => Carbon::parse('2026-10-11 07:00:00'),
        ]);

        // Jarak dekat di area yang sama (misal 50 meter)
        $nearby = new Coordinate(-6.15870000, 106.90820000);
        $newTime = Carbon::parse('2026-10-11 13:00:00');

        $service->validateTeleportation($nearby, $newTime, $record);
        $this->assertTrue(true);
    }

    public function test_selfie_storage_service_can_store_and_delete_base64_image(): void
    {
        $tempDir = sys_get_temp_dir().'/test_selfies_'.uniqid();
        $storage = new SelfieStorageService($tempDir);

        // Dummy base64 valid image (1x1 pixel PNG)
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $savedPath = $storage->storeSelfie($base64Image, 999, 'clock_in');

        $this->assertStringStartsWith('uploads/selfies/', $savedPath);
        $this->assertStringEndsWith('.png', $savedPath);

        // Bersihkan temp directory
        if (File::isDirectory($tempDir)) {
            File::deleteDirectory($tempDir);
        }
    }

    public function test_selfie_storage_service_validates_corrupted_base64(): void
    {
        $tempDir = sys_get_temp_dir().'/test_selfies_'.uniqid();
        $storage = new SelfieStorageService($tempDir);

        $this->expectException(DomainException::class);
        $storage->storeSelfie('data:image/jpeg;base64,corrupted_junk', 999, 'clock_in');
    }
}
