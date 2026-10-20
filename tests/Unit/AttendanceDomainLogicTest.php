<?php

namespace Tests\Unit;

use App\Domains\Attendance\Exceptions\InvalidCoordinateException;
use App\Domains\Attendance\Exceptions\OutsideGeofenceException;
use App\Domains\Attendance\Services\GeoValidationService;
use App\Domains\Attendance\Services\ShiftResolverService;
use App\Domains\Attendance\ValueObjects\Coordinate;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Modules\MasterData\Domain\Models\Branch;
use PHPUnit\Framework\TestCase;

class AttendanceDomainLogicTest extends TestCase
{
    public function test_coordinate_validates_ranges(): void
    {
        $coord = new Coordinate(-6.1585, 106.9080);
        $this->assertSame(-6.1585, $coord->latitude);
        $this->assertSame(106.908, $coord->longitude);

        $this->expectException(InvalidCoordinateException::class);
        new Coordinate(95.0, 106.9080);
    }

    public function test_coordinate_throws_on_invalid_longitude(): void
    {
        $this->expectException(InvalidCoordinateException::class);
        new Coordinate(-6.1585, 190.0);
    }

    public function test_haversine_distance_calculation(): void
    {
        $c1 = new Coordinate(-6.15850000, 106.90800000);
        $c2 = new Coordinate(-6.15850000, 106.90800000);
        $this->assertSame(0.0, $c1->distanceTo($c2));

        // Jarak dekat (sekitar 30-35 meter)
        $c3 = new Coordinate(-6.15870000, 106.90820000);
        $dist = $c1->distanceTo($c3);
        $this->assertGreaterThan(25.0, $dist);
        $this->assertLessThan(40.0, $dist);
    }

    public function test_geo_validation_service_within_and_outside_radius(): void
    {
        $branch = new Branch([
            'name' => 'Maya Graha Kencana',
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'radius_meters' => 100,
        ]);

        $service = new GeoValidationService;

        // 1. Lokasi pegawai dalam radius 100m
        $nearby = new Coordinate(-6.15870000, 106.90820000); // ~31 meter
        $result = $service->validateWithinBranch($nearby, $branch);
        $this->assertTrue($result['is_valid']);
        $this->assertLessThan(100.0, $result['distance_meters']);

        // 2. Lokasi pegawai jauh (Monas ke Kelapa Gading ~9 km)
        $far = new Coordinate(-6.175392, 106.827153);
        $this->expectException(OutsideGeofenceException::class);
        $service->validateWithinBranch($far, $branch);
    }

    public function test_shift_resolver_determines_work_date_for_normal_shift(): void
    {
        $shift = new Shift([
            'code' => 'SHIFT-2',
            'name' => 'Shift 2 (Pagi)',
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
            'is_overnight' => false,
        ]);

        $resolver = new ShiftResolverService;
        $scanTime = Carbon::parse('2026-10-11 06:55:00');
        $workDate = $resolver->determineWorkDate($shift, $scanTime);

        $this->assertSame('2026-10-11', $workDate->toDateString());
    }

    public function test_shift_resolver_determines_work_date_for_overnight_shift(): void
    {
        $shift4 = new Shift([
            'code' => 'SHIFT-4',
            'name' => 'Shift 4 (Malam)',
            'start_time' => '19:00:00',
            'end_time' => '01:00:00',
            'is_overnight' => true,
        ]);

        $resolver = new ShiftResolverService;

        // Masuk dinas malam jam 19:10 pada 10 Oktober -> work_date = 2026-10-10
        $clockIn = Carbon::parse('2026-10-10 19:10:00');
        $workDateIn = $resolver->determineWorkDate($shift4, $clockIn);
        $this->assertSame('2026-10-10', $workDateIn->toDateString());

        // Pulang dinas malam jam 01:15 dini hari pada 11 Oktober -> work_date TETAP 2026-10-10
        $clockOut = Carbon::parse('2026-10-11 01:15:00');
        $workDateOut = $resolver->determineWorkDate($shift4, $clockOut);
        $this->assertSame('2026-10-10', $workDateOut->toDateString());

        // Jam mulai & selesai shift
        $start = $resolver->getShiftStartTimestamp($shift4, $workDateIn);
        $end = $resolver->getShiftEndTimestamp($shift4, $workDateIn);

        $this->assertSame('2026-10-10 19:00:00', $start->toDateTimeString());
        $this->assertSame('2026-10-11 01:00:00', $end->toDateTimeString());
    }

    public function test_shift_resolver_accurately_detects_active_shift_by_time(): void
    {
        $resolver = new ShiftResolverService;

        $s1 = new Shift(['code' => 'SHIFT-1', 'name' => 'Shift 1', 'start_time' => '01:00:00', 'end_time' => '07:00:00', 'is_overnight' => false]);
        $s2 = new Shift(['code' => 'SHIFT-2', 'name' => 'Shift 2', 'start_time' => '07:00:00', 'end_time' => '13:00:00', 'is_overnight' => false]);
        $s3 = new Shift(['code' => 'SHIFT-3', 'name' => 'Shift 3', 'start_time' => '13:00:00', 'end_time' => '19:00:00', 'is_overnight' => false]);
        $s4 = new Shift(['code' => 'SHIFT-4', 'name' => 'Shift 4', 'start_time' => '19:00:00', 'end_time' => '01:00:00', 'is_overnight' => true]);

        // Jam 13:00 harus Shift 3, BUKAN Shift 1 atau Shift 2
        $this->assertFalse($resolver->isCurrentTimeInShift($s1, '13:00:00'));
        $this->assertFalse($resolver->isCurrentTimeInShift($s2, '13:00:00'));
        $this->assertTrue($resolver->isCurrentTimeInShift($s3, '13:00:00'));
        $this->assertFalse($resolver->isCurrentTimeInShift($s4, '13:00:00'));

        // Jam 01:00:00 harus Shift 1, BUKAN Shift 4
        $this->assertTrue($resolver->isCurrentTimeInShift($s1, '01:00:00'));
        $this->assertFalse($resolver->isCurrentTimeInShift($s4, '01:00:00'));

        // Jam 07:00:00 harus Shift 2, BUKAN Shift 1
        $this->assertFalse($resolver->isCurrentTimeInShift($s1, '07:00:00'));
        $this->assertTrue($resolver->isCurrentTimeInShift($s2, '07:00:00'));

        // Jam 19:00:00 harus Shift 4, BUKAN Shift 3
        $this->assertFalse($resolver->isCurrentTimeInShift($s3, '19:00:00'));
        $this->assertTrue($resolver->isCurrentTimeInShift($s4, '19:00:00'));

        // Jam 00:30:00 harus Shift 4 (lintas hari malam)
        $this->assertTrue($resolver->isCurrentTimeInShift($s4, '00:30:00'));
        $this->assertFalse($resolver->isCurrentTimeInShift($s1, '00:30:00'));
    }
}
