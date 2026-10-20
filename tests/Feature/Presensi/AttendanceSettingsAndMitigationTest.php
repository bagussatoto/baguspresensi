<?php

declare(strict_types=1);

namespace Tests\Feature\Presensi;

use App\Domains\Attendance\Enums\AdminRole;
use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Services\AttendanceSettingService;
use App\Domains\Leave\Enums\LeaveType;
use App\Domains\Leave\Models\LeaveRequest;
use App\Models\User;
use BadMethodCallException;
use Database\Seeders\AttendanceSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AttendanceSettingsAndMitigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Employee $employee;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AttendanceSettingsSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-001',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@mayagraha.com',
            'password' => Hash::make('password123'),
            'role' => AdminRole::SuperAdmin,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Andi Wijaya',
            'code' => 'EMP-001',
            'pin' => Hash::make('123456'),
            'role' => 'operator',
            'is_active' => true,
        ]);
    }

    /**
     * Uji 1: AttendanceSettingService menyimpan cache in-memory dan melakukan invalidasi saat update.
     */
    public function test_attendance_settings_service_caches_values_and_invalidates_on_update(): void
    {
        $service = app(AttendanceSettingService::class);

        // Ambil nilai awal dari seeder
        $initial = $service->get('attendance.shift.grace_period_minutes');
        $this->assertSame(15, $initial);

        // Update nilai baru
        $service->set('attendance.shift.grace_period_minutes', 20);

        // Ambil kembali dan verifikasi cache di-refresh
        $updated = $service->get('attendance.shift.grace_period_minutes');
        $this->assertSame(20, $updated);
    }

    /**
     * Uji 2: Panel Web Admin dapat melihat dan memperbarui pengaturan presensi serta mencatat audit log.
     */
    public function test_admin_can_access_and_update_attendance_settings_via_web_panel(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.presensi.pengaturan'));
        $response->assertOk();
        $response->assertSee('Pengaturan Presensi');

        // Kirim pembaruan pengaturan
        $postResponse = $this->actingAs($this->admin)->post(route('admin.presensi.pengaturan.update'), [
            'settings' => [
                'attendance.shift.grace_period_minutes' => 25,
                'attendance.approval.rejection_note_mandatory' => '1',
            ],
        ]);

        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('success');

        $service = app(AttendanceSettingService::class);
        $this->assertSame(25, $service->get('attendance.shift.grace_period_minutes'));

        // Cek audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'attendance_setting_updated',
            'user_id' => $this->admin->id,
        ]);
    }

    /**
     * Uji 3: NoOverlappingLeaveRule memblokir permohonan cuti baru jika bertabrakan dengan izin aktif/pending sebelumnya.
     */
    public function test_no_overlapping_leave_rule_prevents_conflicting_leave_requests(): void
    {
        // Izin yang sudah ada: 10 Okt s/d 15 Okt 2026
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => LeaveType::Annual,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'total_days' => 6,
            'reason' => 'Cuti liburan keluarga',
            'status' => ApprovalStatus::Approved,
        ]);

        // Coba ajukan izin baru yang tumpang tindih: 14 Okt s/d 18 Okt 2026
        $response = $this->postJson(route('api.v1.leave.request'), [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-18',
            'reason' => 'Cuti tambahan',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }

    /**
     * Uji 4: Kebijakan strict_always pada izin sakit menolak permohonan tanpa surat dokter.
     */
    public function test_sick_leave_requires_doctor_attachment_when_policy_is_strict_always(): void
    {
        $response = $this->postJson(route('api.v1.leave.request'), [
            'employee_id' => $this->employee->id,
            'leave_type' => 'sick',
            'start_date' => '2026-10-25',
            'end_date' => '2026-10-26',
            'reason' => 'Demam flu',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Lampiran surat keterangan dokter wajib diunggah untuk permohonan izin sakit.',
        ]);
    }

    /**
     * Uji 5: Kebijakan strict_block menolak cuti tahunan jika melebihi kuota 12 hari per tahun.
     */
    public function test_annual_leave_rejects_when_quota_exhausted_and_policy_is_strict_block(): void
    {
        // Buat rekaman cuti terdahulu yang menghabiskan 11 hari di tahun 2026
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => LeaveType::Annual,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-11',
            'total_days' => 11,
            'reason' => 'Cuti lebaran',
            'status' => ApprovalStatus::Approved,
        ]);

        // Ajukan cuti 3 hari lagi (total 11 + 3 = 14 > 12)
        $response = $this->postJson(route('api.v1.leave.request'), [
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'reason' => 'Cuti akhir tahun',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('Saldo cuti tahunan tidak mencukupi', $response->json('message'));
    }

    /**
     * Uji 6: Wajib catatan penolakan jika setting attendance.approval.rejection_note_mandatory bernilai aktif.
     */
    public function test_mandatory_rejection_note_policy_blocks_empty_rejection_notes(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => LeaveType::Annual,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
            'total_days' => 2,
            'reason' => 'Cuti singkat',
            'status' => ApprovalStatus::Pending,
        ]);

        // Supervisor mencoba menolak tanpa catatan penolakan
        $response = $this->actingAs($this->admin)->post(route('admin.presensi.leave.reject', $leave), [
            'rejection_note' => '',
        ]);

        $response->assertSessionHasErrors(['rejection_note']);
        $this->assertSame(ApprovalStatus::Pending, $leave->fresh()->status);
    }

    /**
     * Uji 7: Proteksi WORM Immutability Guard pada AuditLog mencegah pembaruan dan penghapusan data.
     */
    public function test_audit_log_worm_guard_blocks_update_and_delete_when_active(): void
    {
        $auditLog = AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'employee_created',
            'created_at' => now(),
        ]);

        // Percobaan Update ditolak
        try {
            $auditLog->update(['action' => 'modified_action']);
            $this->fail('Harusnya melempar BadMethodCallException saat update AuditLog');
        } catch (BadMethodCallException $e) {
            $this->assertStringContainsString('WORM', $e->getMessage());
        }

        // Percobaan Delete ditolak
        try {
            $auditLog->delete();
            $this->fail('Harusnya melempar BadMethodCallException saat delete AuditLog');
        } catch (BadMethodCallException $e) {
            $this->assertStringContainsString('WORM', $e->getMessage());
        }
    }
}
