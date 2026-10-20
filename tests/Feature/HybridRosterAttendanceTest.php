<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Enums\AdminRole;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\RosterJadwal;
use App\Domains\Attendance\Services\RosterResolverService;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class HybridRosterAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Shift $shiftPagi;

    protected Employee $employeeShift;

    protected Employee $employeeDesign;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-001',
            'latitude' => -6.1585,
            'longitude' => 106.9080,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->shiftPagi = Shift::create([
            'name' => 'Shift Pagi Operasional',
            'code' => 'SFT-PAGI',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->employeeShift = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Joko Operator Pabrik',
            'code' => 'EMP-PBR-01',
            'pin' => Hash::make('123456'),
            'role' => 'operator',
            'default_work_type' => 'shift',
            'default_contract_hours' => 8,
            'default_cost_center' => '5-1001',
            'is_active' => true,
        ]);

        $this->employeeDesign = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Siti Desainer Kreatif',
            'code' => 'EMP-DSN-01',
            'pin' => Hash::make('654321'),
            'role' => 'operator',
            'position' => 'Desainer Kreatif',
            'default_work_type' => 'flexible',
            'default_contract_hours' => 8,
            'default_cost_center' => '5-1002',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Administrator Presensi',
            'email' => 'admin@mayagraha.com',
            'password' => Hash::make('password123'),
            'role' => AdminRole::SuperAdmin,
        ]);
    }

    /**
     * Uji 1: RosterResolverService memprioritaskan penugasan RosterJadwal khusus jika ada.
     */
    public function test_roster_resolver_uses_roster_jadwal_when_available(): void
    {
        $today = Carbon::parse('2026-10-20');

        $roster = RosterJadwal::create([
            'employee_id' => $this->employeeShift->id,
            'work_date' => $today->toDateString(),
            'work_type' => 'flexible',
            'shift_id' => null,
            'contract_hours' => 7,
            'cost_center' => '5-1002',
            'notes' => 'Tugas Desain Khusus Booth Event',
        ]);

        $service = app(RosterResolverService::class);
        $result = $service->resolve($this->employeeShift, $today);

        $this->assertSame('flexible', $result['work_type']);
        $this->assertSame('5-1002', $result['cost_center']);
        $this->assertSame(7, $result['contract_hours']);
        $this->assertSame($roster->id, $result['roster_id']);
    }

    /**
     * Uji 2: RosterResolverService auto-fallback ke profil karyawan jika tidak ada jadwal khusus.
     */
    public function test_roster_resolver_falls_back_to_employee_defaults_when_no_roster_scheduled(): void
    {
        $today = Carbon::parse('2026-10-20');
        $service = app(RosterResolverService::class);

        $resultShift = $service->resolve($this->employeeShift, $today);
        $this->assertSame('shift', $resultShift['work_type']);
        $this->assertSame('5-1001', $resultShift['cost_center']);
        $this->assertSame(8, $resultShift['contract_hours']);
        $this->assertNull($resultShift['roster_id']);

        $resultDesign = $service->resolve($this->employeeDesign, $today);
        $this->assertSame('flexible', $resultDesign['work_type']);
        $this->assertSame('5-1002', $resultDesign['cost_center']);
        $this->assertSame(8, $resultDesign['contract_hours']);
        $this->assertNull($resultDesign['roster_id']);
    }

    /**
     * Uji 3: Presensi Shift Pabrik dialokasikan ke Cost Center 5-1001 (Direct Labor COGS).
     */
    public function test_clock_in_and_clock_out_for_shift_mode_allocates_cogs_cost_center(): void
    {
        $clockInTime = Carbon::parse('2026-10-20 07:55:00');
        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeShift->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            shiftId: $this->shiftPagi->id,
            clockInAt: $clockInTime
        );

        $actionIn = app(ClockInAction::class);
        $record = $actionIn->execute($dtoIn);

        $this->assertSame('shift', $record->work_type);
        $this->assertSame('5-1001', $record->cost_center);
        $this->assertSame(AttendanceStatus::Present, $record->status);
        $this->assertSame(0, $record->late_minutes);

        // Clock Out 16:30 (lembur 30 menit)
        $clockOutTime = Carbon::parse('2026-10-20 16:30:00');
        $dtoOut = new ClockOutDTO(
            employeeId: $this->employeeShift->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $clockOutTime
        );

        $actionOut = app(ClockOutAction::class);
        $recordOut = $actionOut->execute($dtoOut);

        $this->assertNotNull($recordOut->clock_out_at);
        $this->assertSame(515, $recordOut->duration_minutes);
        $this->assertSame(515, $recordOut->effective_minutes);
        $this->assertSame(30, $recordOut->overtime_minutes);
    }

    /**
     * Uji 4: Presensi Fleksibel Desain dialokasikan ke Cost Center 5-1002 (OPEX) dan bebas penalti telat.
     */
    public function test_clock_in_and_clock_out_for_flexible_mode_allocates_opex_and_disables_late_penalty(): void
    {
        // Masuk jam 11:30 siang (di shift kaku ini akan dihitung telat ratusan menit)
        $clockInTime = Carbon::parse('2026-10-20 11:30:00');
        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeDesign->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $clockInTime
        );

        $actionIn = app(ClockInAction::class);
        $record = $actionIn->execute($dtoIn);

        $this->assertSame('flexible', $record->work_type);
        $this->assertSame('5-1002', $record->cost_center);
        $this->assertSame(AttendanceStatus::Present, $record->status);
        $this->assertSame(0, $record->late_minutes); // Penalti keterlambatan dinonaktifkan
    }

    /**
     * Uji 5: Mode Fleksibel menghitung lembur berdasarkan kelebihan jam kontrak harian (Stopwatch accumulation).
     */
    public function test_flexible_mode_over_contract_hours_records_overtime(): void
    {
        // Masuk jam 09:00 pagi
        $clockInTime = Carbon::parse('2026-10-20 09:00:00');
        $dtoIn = new ClockInDTO(
            employeeId: $this->employeeDesign->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockInAt: $clockInTime
        );

        $actionIn = app(ClockInAction::class);
        $record = $actionIn->execute($dtoIn);

        // Disetujui lembur oleh supervisor untuk kebutuhan desain
        $record->update([
            'overtime_status' => 'approved',
            'overtime_approved_minutes' => 150,
        ]);

        // Pulang jam 19:30 malam (10 jam 30 menit = 630 menit. Target: 8 jam = 480 menit. Lembur = 150 menit)
        $clockOutTime = Carbon::parse('2026-10-20 19:30:00');
        $dtoOut = new ClockOutDTO(
            employeeId: $this->employeeDesign->id,
            branchId: $this->branch->id,
            latitude: -6.1585,
            longitude: 106.9080,
            clockOutAt: $clockOutTime
        );

        $actionOut = app(ClockOutAction::class);
        $recordOut = $actionOut->execute($dtoOut);

        $this->assertSame(630, $recordOut->duration_minutes);
        $this->assertSame(630, $recordOut->effective_minutes);
        $this->assertSame(150, $recordOut->overtime_minutes);
        $this->assertSame(0, $recordOut->early_leave_minutes);
    }

    /**
     * Uji 6: Menu Rekap Bulanan & Ekspor CSV Admin memisahkan Cost Center HPP (5-1001) dan OPEX (5-1002).
     */
    public function test_admin_rekap_and_csv_export_separates_cogs_and_opex_cost_centers(): void
    {
        $workDate = Carbon::create(2026, 10, 15);

        // Record 1: Shift Pabrik (5-1001) 8 jam = 480 menit
        AttendanceRecord::create([
            'employee_id' => $this->employeeShift->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shiftPagi->id,
            'work_date' => $workDate->toDateString(),
            'clock_in_at' => $workDate->copy()->setTime(8, 0),
            'clock_out_at' => $workDate->copy()->setTime(16, 0),
            'status' => AttendanceStatus::Present,
            'work_type' => 'shift',
            'cost_center' => '5-1001',
            'duration_minutes' => 480,
            'effective_minutes' => 480,
            'overtime_minutes' => 0,
        ]);

        // Record 2: Desain Kreatif (5-1002) 6 jam = 360 menit
        AttendanceRecord::create([
            'employee_id' => $this->employeeDesign->id,
            'branch_id' => $this->branch->id,
            'shift_id' => null,
            'work_date' => $workDate->toDateString(),
            'clock_in_at' => $workDate->copy()->setTime(10, 0),
            'clock_out_at' => $workDate->copy()->setTime(16, 0),
            'status' => AttendanceStatus::Present,
            'work_type' => 'flexible',
            'cost_center' => '5-1002',
            'duration_minutes' => 360,
            'effective_minutes' => 360,
            'overtime_minutes' => 0,
        ]);

        // 1. Cek Web Rekap
        $response = $this->actingAs($this->admin)->get(route('admin.presensi.rekap', [
            'month' => 10,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('HPP 5-1001 (Jam)');
        $response->assertSee('OPEX 5-1002 (Jam)');
        $response->assertSee('Joko Operator Pabrik');
        $response->assertSee('Siti Desainer Kreatif');

        // 2. Cek Ekspor CSV Akuntansi
        $csvResponse = $this->actingAs($this->admin)->get(route('admin.presensi.rekap', [
            'month' => 10,
            'year' => 2026,
            'export' => 'csv',
        ]));

        $csvResponse->assertOk();
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $csvResponse->streamedContent();
        $this->assertStringContainsString('Jam HPP Cabang / Pabrik (5-1001)', $content);
        $this->assertStringContainsString('Jam OPEX Desain Kreatif (5-1002)', $content);
        $this->assertStringContainsString('EMP-PBR-01', $content);
        $this->assertStringContainsString('EMP-DSN-01', $content);
    }
}
