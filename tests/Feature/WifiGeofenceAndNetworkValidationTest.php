<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Exceptions\OutsideGeofenceException;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class WifiGeofenceAndNetworkValidationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        // Cabang MGK Pusat di Monas Jakarta (-6.175392, 106.827153) dengan radius 100 meter
        $this->branch = Branch::create([
            'name' => 'Warnet MGK Net Pusat',
            'code' => 'NET-PST',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius_meters' => 100,
            'wifi_ssid' => 'MGK-Pusat-5G',
            'wifi_bssid' => '00:14:22:01:23:45',
            'allowed_network_ips' => ['103.145.22.10', '192.168.1.1'],
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bambang Operator',
            'code' => 'OP-BM-01',
            'username' => 'bambang',
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 30,
            'is_active' => true,
        ]);
    }

    /**
     * Skenario 1: Karyawan berada dalam radius GPS kantor (misal 20m dari pusat cabang).
     * Berhasil clock-in dengan metode standar GPS + Selfie.
     */
    public function test_employee_within_gps_radius_clocks_in_with_gps_selfie_method(): void
    {
        // Koordinat sangat dekat (-6.175300, 106.827150 ~10 meter)
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.175300,
            longitude: 106.827150,
            shiftId: $this->shift->id,
            photoPath: 'uploads/selfies/sample_selfie.jpg',
            verificationMethod: VerificationMethod::GpsSelfie,
            ipAddress: '114.122.1.5',
            notes: 'Clock-in normal',
            clockInAt: Carbon::today()->setTime(7, 10)
        );

        $action = app(ClockInAction::class);
        $record = $action->execute($dto);

        $this->assertNotNull($record);
        $this->assertEquals(AttendanceStatus::Present, $record->status);
        $this->assertEquals(VerificationMethod::GpsSelfie, $record->verification_method);
        $this->assertLessThanOrEqual(100.0, $record->clock_in_distance_meters);
    }

    /**
     * Skenario 2: Karyawan mengalami GPS drift di dalam gedung (terbaca 500 meter dari cabang),
     * namun BSSID Wi-Fi cocok dengan Access Point resmi cabang ('00:14:22:01:23:45').
     * Sistem meloloskan presensi dengan metode Multi-Factor Wi-Fi Geofence.
     */
    public function test_employee_outside_gps_radius_with_matching_wifi_bssid_successfully_clocks_in_with_wifi_geofence_method(): void
    {
        // Koordinat jauh (-6.179000, 106.827153 ~400 meter dari cabang)
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.179000,
            longitude: 106.827153,
            shiftId: $this->shift->id,
            photoPath: 'uploads/selfies/sample_selfie.jpg',
            verificationMethod: VerificationMethod::GpsSelfie,
            ipAddress: '114.122.1.5',
            wifiBssid: '00:14:22:01:23:45', // BSSID cocok dengan router toko!
            notes: 'HP di dalam ruko',
            clockInAt: Carbon::today()->setTime(7, 10)
        );

        $action = app(ClockInAction::class);
        $record = $action->execute($dto);

        $this->assertNotNull($record);
        $this->assertEquals(AttendanceStatus::Present, $record->status);
        $this->assertEquals(VerificationMethod::WifiGeofence, $record->verification_method);
        $this->assertStringContainsString('Tervalidasi via Multi-Factor Geofence', (string) $record->notes);
        $this->assertStringContainsString('BSSID Wi-Fi resmi cabang', (string) $record->notes);
    }

    /**
     * Skenario 3: Karyawan mengalami GPS drift, BSSID null, namun IP address client
     * cocok dengan IP Publik / Gateway terdaftar cabang ('103.145.22.10').
     * Sistem meloloskan presensi dengan metode Multi-Factor Wi-Fi Geofence.
     */
    public function test_employee_outside_gps_radius_with_matching_network_ip_successfully_clocks_in_with_wifi_geofence_method(): void
    {
        // Koordinat jauh
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.179500,
            longitude: 106.827153,
            shiftId: $this->shift->id,
            photoPath: 'uploads/selfies/sample_selfie.jpg',
            verificationMethod: VerificationMethod::GpsSelfie,
            ipAddress: '103.145.22.10', // IP terdaftar di allowed_network_ips cabang!
            wifiBssid: null,
            notes: 'PC Kiosk Indoor',
            clockInAt: Carbon::today()->setTime(7, 10)
        );

        $action = app(ClockInAction::class);
        $record = $action->execute($dto);

        $this->assertNotNull($record);
        $this->assertEquals(AttendanceStatus::Present, $record->status);
        $this->assertEquals(VerificationMethod::WifiGeofence, $record->verification_method);
        $this->assertStringContainsString('Tervalidasi via Multi-Factor Geofence', (string) $record->notes);
        $this->assertStringContainsString('IP Jaringan resmi cabang', (string) $record->notes);
    }

    /**
     * Skenario 4: Karyawan berada jauh di luar radius GPS (> 100m) DAN tidak terhubung ke Wi-Fi / IP cabang.
     * Sistem menolak presensi dengan OutsideGeofenceException (Mencegah absen titipan dari rumah).
     */
    public function test_employee_outside_gps_radius_without_matching_wifi_or_ip_is_rejected_with_outside_geofence_error(): void
    {
        $this->expectException(OutsideGeofenceException::class);

        // Koordinat jauh di rumah (~5 km)
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.200000,
            longitude: 106.850000,
            shiftId: $this->shift->id,
            photoPath: 'uploads/selfies/sample_selfie.jpg',
            verificationMethod: VerificationMethod::GpsSelfie,
            ipAddress: '36.88.10.99', // IP Provider Rumah
            wifiBssid: 'aa:bb:cc:dd:ee:ff', // Wi-Fi Rumah Pribadi
            notes: 'Coba absen dari rumah'
        );

        $action = app(ClockInAction::class);
        $action->execute($dto);
    }

    /**
     * Skenario 5: Pengecekan BSSID kebal terhadap format penulisan (case-insensitive & dengan/tanpa strip atau titik dua).
     */
    public function test_case_insensitive_bssid_matching_with_or_without_separators(): void
    {
        $this->assertTrue($this->branch->matchesWifiBssid('00:14:22:01:23:45'));
        $this->assertTrue($this->branch->matchesWifiBssid('00-14-22-01-23-45'));
        $this->assertTrue($this->branch->matchesWifiBssid('001422012345'));
        $this->assertTrue($this->branch->matchesWifiBssid('00:14:22:01:23:45'));

        // BSSID salah
        $this->assertFalse($this->branch->matchesWifiBssid('00:14:22:99:99:99'));
        $this->assertFalse($this->branch->matchesWifiBssid(''));
        $this->assertFalse($this->branch->matchesWifiBssid(null));
    }
}
