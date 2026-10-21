<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AdminRole;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class UserAccessLifecycleAndDeprovisioningTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected User $admin;

    protected Employee $activeEmployee;

    protected Employee $supervisorEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@mayagraha.com',
            'password' => Hash::make('password123'),
            'role' => AdminRole::SuperAdmin,
        ]);

        $this->activeEmployee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'code' => 'EMP-001',
            'username' => 'budi01',
            'role' => EmployeeRole::KASIR,
            'position' => 'Kasir Toko',
            'max_discount_approval_percent' => 5.0,
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $this->supervisorEmployee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Siti Supervisor',
            'code' => 'EMP-SPV',
            'username' => 'siti_spv',
            'role' => EmployeeRole::SUPERVISOR,
            'position' => 'Supervisor Cabang',
            'max_discount_approval_percent' => 20.0,
            'password' => Hash::make('spvpass123'),
            'pin' => Hash::make('999888'),
            'is_active' => true,
        ]);

        Shift::create([
            'name' => 'Shift Pagi',
            'code' => 'PAGI',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_deprovision_employee_with_instant_revocation_and_audit_trail(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.karyawan.toggle-status', $this->activeEmployee), [
                'reason' => 'Resign / Mengundurkan Diri',
            ]);

        $response->assertRedirect();

        $this->activeEmployee->refresh();
        $this->assertFalse($this->activeEmployee->is_active);
        $this->assertNotNull($this->activeEmployee->deactivated_at);
        $this->assertSame($this->admin->id, $this->activeEmployee->deactivated_by);
        $this->assertSame('Resign / Mengundurkan Diri', $this->activeEmployee->deactivation_reason);

        // Jejak WORM Audit Trail ISO 27001
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee_deprovisioned',
            'user_id' => $this->admin->id,
            'auditable_type' => Employee::class,
            'auditable_id' => $this->activeEmployee->id,
        ]);
    }

    public function test_admin_can_reactivate_deprovisioned_employee(): void
    {
        // Deactivate first
        $this->activeEmployee->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => $this->admin->id,
            'deactivation_reason' => 'Cuti di luar tanggungan',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.karyawan.toggle-status', $this->activeEmployee));

        $response->assertRedirect();

        $this->activeEmployee->refresh();
        $this->assertTrue($this->activeEmployee->is_active);
        $this->assertNull($this->activeEmployee->deactivated_at);
        $this->assertNull($this->activeEmployee->deactivated_by);
        $this->assertNull($this->activeEmployee->deactivation_reason);

        // Jejak WORM Audit Trail
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee_reactivated',
            'user_id' => $this->admin->id,
            'auditable_type' => Employee::class,
            'auditable_id' => $this->activeEmployee->id,
        ]);
    }

    public function test_deactivated_employee_session_is_instantly_revoked_on_next_portal_request(): void
    {
        // Simulasi karyawan login dan memiliki session
        $this->withSession(['presensi2_employee_id' => $this->activeEmployee->id]);

        // Status masih aktif -> akses halaman diperbolehkan
        $response = $this->get(route('presensi2.index'));
        $response->assertOk();

        // Admin mencabut akses (is_active = false)
        $this->activeEmployee->update(['is_active' => false]);

        // Pada request berikutnya, sesi seketika terputus dan dialihkan ke login
        $followUp = $this->get(route('presensi2.index'));
        $followUp->assertRedirect(route('presensi2.login'));
        $this->assertNull(session('presensi2_employee_id'));
    }

    public function test_admin_can_view_access_review_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.karyawan.access-review'));

        $response->assertOk();
        $response->assertSee('Tinjauan Hak Akses Pengguna');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Siti Supervisor');
        $response->assertSee('Klausul A.8.5');
    }

    public function test_admin_can_sign_off_periodic_access_review(): void
    {
        $this->assertNull($this->activeEmployee->last_access_review_at);
        $this->assertNull($this->supervisorEmployee->last_access_review_at);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.karyawan.access-review.sign-off'), [
                'notes' => 'Audit kuartalan akses staf MGK selesai tanpa temuan deviasi.',
            ]);

        $response->assertRedirect();

        $this->activeEmployee->refresh();
        $this->supervisorEmployee->refresh();

        $this->assertNotNull($this->activeEmployee->last_access_review_at);
        $this->assertNotNull($this->supervisorEmployee->last_access_review_at);

        // Audit Log pengesahan
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'access_rights_periodic_review',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_export_access_review_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.karyawan.access-review.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="berita-acara-audit-akses-pengguna-', (string) $response->headers->get('Content-Disposition'));
    }
}
