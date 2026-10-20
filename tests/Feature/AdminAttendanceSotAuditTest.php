<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftSalesSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AdminAttendanceSotAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift;

    private AttendanceRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Bagus Admin',
            'email' => 'admin@mayanet.com',
            'role' => 'super_admin',
        ]);

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Rian Kasir',
            'code' => 'EMP-007',
            'position' => 'Kasir POS',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift 1 Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $today = Carbon::today();
        $this->record = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => $today->toDateString(),
            'clock_in_at' => $today->copy()->setTime(7, 5, 0),
            'clock_in_lat' => -6.200050,
            'clock_in_lng' => 106.816700,
            'clock_in_distance_meters' => 15.5,
            'clock_in_photo_path' => 'storage/selfie/test_in.jpg',
            'clock_out_at' => $today->copy()->setTime(15, 30, 0),
            'clock_out_lat' => -6.200050,
            'clock_out_lng' => 106.816700,
            'clock_out_distance_meters' => 15.5,
            'clock_out_photo_path' => 'storage/selfie/test_out.jpg',
            'status' => AttendanceStatus::Present,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 30,
            'duration_minutes' => 505,
            'verification_method' => VerificationMethod::GpsSelfie,
            'ip_address' => '192.168.1.100',
            'is_mock_location_detected' => false,
        ]);
    }

    public function test_admin_can_view_presensi_sot_monitoring_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.presensi', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee('Presensi Harian (SoT)');
        $response->assertSee('Rian Kasir');
        $response->assertSee('Maya Graha Pusat');
        $response->assertSee('Shift 1 Pagi');
        $response->assertSee('Hadir Tepat Waktu');
        $response->assertSee('Unduh Audit CSV');
    }

    public function test_admin_can_fetch_sot_audit_detail_via_ajax_endpoint(): void
    {
        // 1. Tambahkan data POS kasir sesi terkait
        $pos = ShiftSalesSummary::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'blind_cash_count' => 350000.0,
            'system_expected_cash' => 350000.0,
            'cash_difference' => 0.0,
            'cash_in_drawer' => 350000.0,
            'total_sales' => 350000.0,
            'status' => 'closed',
            'handover_notes' => 'Kas cocok seimbang',
        ]);

        // 2. Tambahkan WORM audit log terkait
        AuditLog::create([
            'user_id' => $this->adminUser->id,
            'auditable_type' => AttendanceRecord::class,
            'auditable_id' => $this->record->id,
            'action' => 'attendance_verified_sot',
            'new_values' => ['status' => 'present', 'verified_by' => 'admin'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.presensi.detail', $this->record));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.record.id', $this->record->id);
        $response->assertJsonPath('data.employee.name', 'Rian Kasir');
        $response->assertJsonPath('data.is_geofence_valid', true);
        $response->assertJsonPath('data.pos_session.status', 'closed');
        $this->assertEquals(0, (float) $response->json('data.pos_session.cash_difference'));
        $this->assertCount(1, $response->json('data.audit_logs'));
    }

    public function test_admin_can_view_sot_audit_detail_html_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.presensi.detail', $this->record));

        $response->assertStatus(200);
        $response->assertSee('Laporan Audit SoT Presensi #'.$this->record->id);
        $response->assertSee('Rian Kasir');
        $response->assertSee('Maya Graha Pusat');
        $response->assertSee('15.5m');
    }

    public function test_admin_can_export_daily_sot_audit_csv(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.presensi.export-audit', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('audit-sot-presensi', (string) $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('SINGLE SOURCE OF TRUTH (SOT) PRESENSI ERP', $content);
        $this->assertStringContainsString('Rian Kasir', $content);
        $this->assertStringContainsString('Maya Graha Pusat', $content);
        $this->assertStringContainsString('Dalam Radius (Sah)', $content);
    }

    public function test_sot_detects_mock_location_and_displays_warning(): void
    {
        // Ubah rekaman menjadi terdeteksi Fake GPS
        $this->record->update([
            'is_mock_location_detected' => true,
            'clock_in_distance_meters' => 250.0, // di luar radius 100m
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.presensi', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee('FAKE GPS');
        $response->assertSee('Luar');

        // Dan di detail JSON
        $detailResponse = $this->actingAs($this->adminUser)
            ->getJson(route('admin.presensi.detail', $this->record));

        $detailResponse->assertStatus(200);
        $detailResponse->assertJsonPath('data.is_geofence_valid', false);
        $detailResponse->assertJsonPath('data.record.is_mock_location_detected', true);
    }
}
