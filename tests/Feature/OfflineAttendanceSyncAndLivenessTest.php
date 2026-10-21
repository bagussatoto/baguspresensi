<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\SyncOfflineAttendanceAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class OfflineAttendanceSyncAndLivenessTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'MGK Office Pusat',
            'code' => 'MGK-PST',
            'latitude' => -6.175392,
            'longitude' => 106.827153,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Doni Teknisi',
            'code' => 'EMP-DN-01',
            'username' => 'doni',
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_employee_can_clock_in_with_liveness_score_recorded_to_sot(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 08:05:00'));

        $action = app(ClockInAction::class);
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.175392,
            longitude: 106.827153,
            shiftId: $this->shift->id,
            verificationMethod: VerificationMethod::GpsSelfie,
            deviceInfo: ['device' => 'PWA Mobile Android'],
            livenessScore: 0.98
        );

        $record = $action->execute($dto);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'employee_id' => $this->employee->id,
            'liveness_score' => 0.98,
            'status' => AttendanceStatus::Present->value,
        ]);
    }

    public function test_offline_attendance_sync_action_stores_record_with_nonce_and_worm_audit(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 10:00:00'));

        $action = app(SyncOfflineAttendanceAction::class);
        $nonce = 'nonce_offline_test_'.uniqid();

        $payload = [
            'offline_nonce' => $nonce,
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => '2026-10-21 08:10:00',
            'liveness_score' => 0.96,
            'notes' => 'Presensi offline dari area blankspot',
        ];

        $response = $action->execute($payload);

        $this->assertTrue($response['success']);
        $this->assertEquals(1, $response['summary']['synced']);

        // Verifikasi masuk ke Single Source of Truth attendance_records
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'offline_nonce' => $nonce,
            'is_offline_sync' => true,
            'verification_method' => VerificationMethod::OfflineSync->value,
            'status' => AttendanceStatus::Present->value,
            'liveness_score' => 0.96,
        ]);

        $record = AttendanceRecord::where('offline_nonce', $nonce)->first();
        $this->assertNotNull($record);
        $this->assertNotNull($record->sync_recorded_at);

        // Verifikasi jejak audit forensik WORM (ISO 27001 A.8.15)
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'offline_attendance_synced',
            'auditable_type' => AttendanceRecord::class,
            'auditable_id' => $record->id,
        ]);

        $auditLog = AuditLog::where('action', 'offline_attendance_synced')
            ->where('auditable_id', $record->id)
            ->first();

        $this->assertEquals($nonce, $auditLog->new_values['offline_nonce']);
    }

    public function test_offline_attendance_sync_prevents_replay_attack_idempotency(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 11:00:00'));

        $action = app(SyncOfflineAttendanceAction::class);
        $nonce = 'nonce_replay_guard_12345';

        $payload = [
            'offline_nonce' => $nonce,
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => '2026-10-21 08:00:00',
            'liveness_score' => 0.99,
        ];

        // Pengiriman pertama (sukses tersinkronisasi)
        $firstResponse = $action->execute($payload);
        $this->assertTrue($firstResponse['success']);
        $this->assertEquals(1, $firstResponse['summary']['synced']);

        // Pengiriman kedua dengan nonce yang sama (Replay Attack)
        $secondResponse = $action->execute($payload);
        $this->assertTrue($secondResponse['success']); // Idempotent success
        $this->assertEquals(0, $secondResponse['summary']['synced']);
        $this->assertEquals(1, $secondResponse['summary']['skipped']);
        $this->assertEquals('already_synced', $secondResponse['results'][0]['status']);

        // Pastikan tidak ada data ganda di attendance_records
        $this->assertEquals(1, AttendanceRecord::where('offline_nonce', $nonce)->count());
    }

    public function test_offline_attendance_sync_rejects_future_timestamp_manipulation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 08:00:00'));

        $action = app(SyncOfflineAttendanceAction::class);
        $nonce = 'nonce_future_guard_'.uniqid();

        $payload = [
            'offline_nonce' => $nonce,
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => '2026-10-21 12:00:00', // 4 jam di masa depan
            'liveness_score' => 0.95,
        ];

        $response = $action->execute($payload);

        $this->assertFalse($response['success']);
        $this->assertEquals(1, $response['summary']['failed']);
        $this->assertStringContainsString('Manipulasi waktu terdeteksi', $response['results'][0]['message']);

        // Data tidak boleh masuk ke database
        $this->assertDatabaseMissing('attendance_records', [
            'offline_nonce' => $nonce,
        ]);
    }

    public function test_offline_attendance_sync_batch_endpoint_presensi2(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 12:00:00'));

        $nonce = 'nonce_web_batch_'.uniqid();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.sync-offline'), [
                'items' => [
                    [
                        'offline_nonce' => $nonce,
                        'clock_in_at' => '2026-10-21 08:02:00',
                        'shift_id' => $this->shift->id,
                        'liveness_score' => 0.97,
                        'notes' => 'Batch sync via PWA',
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'synced' => 1,
            ],
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'offline_nonce' => $nonce,
            'is_offline_sync' => true,
        ]);
    }

    public function test_offline_attendance_sync_api_v1_endpoint(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 14:00:00'));

        $anotherEmployee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Faisal Kurir',
            'code' => 'EMP-FS-02',
            'username' => 'faisal',
            'is_active' => true,
        ]);

        $nonce = 'nonce_api_v1_'.uniqid();

        $response = $this->postJson(route('api.v1.attendance.sync-offline'), [
            'offline_nonce' => $nonce,
            'employee_id' => $anotherEmployee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'clock_in_at' => '2026-10-21 08:00:00',
            'liveness_score' => 0.95,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'synced' => 1,
            ],
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $anotherEmployee->id,
            'offline_nonce' => $nonce,
            'is_offline_sync' => true,
        ]);
    }

    public function test_offline_attendance_sync_endpoint_presensi1(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 16:00:00'));

        $nonce = 'nonce_v1_sync_'.uniqid();

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->postJson(route('presensi.sync-offline'), [
                'offline_nonce' => $nonce,
                'clock_in_at' => '2026-10-21 08:05:00',
                'shift_id' => $this->shift->id,
                'liveness_score' => 0.96,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'synced' => 1,
            ],
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'offline_nonce' => $nonce,
            'is_offline_sync' => true,
        ]);
    }
}
