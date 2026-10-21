<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AdminRole;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Services\AuditLogService;
use App\Models\User;
use BadMethodCallException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AuditLogAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch1;

    protected Branch $branch2;

    protected User $superAdmin;

    protected User $branchAdmin;

    protected User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch1 = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'is_active' => true,
        ]);

        $this->branch2 = Branch::create([
            'name' => 'Maya Graha Surabaya',
            'code' => 'CBG-SBY',
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@mayagraha.com',
            'password' => Hash::make('secret123'),
            'role' => AdminRole::SuperAdmin,
        ]);

        $this->branchAdmin = User::create([
            'name' => 'Supervisor Cabang SBY',
            'email' => 'spv.sby@mayagraha.com',
            'password' => Hash::make('secret123'),
            'role' => AdminRole::BranchAdmin,
            'assigned_branch_id' => $this->branch2->id,
        ]);

        $this->auditor = User::create([
            'name' => 'Auditor ISO 27001',
            'email' => 'auditor@mayagraha.com',
            'password' => Hash::make('secret123'),
            'role' => AdminRole::Auditor,
        ]);
    }

    public function test_audit_log_service_records_action_and_sanitizes_credentials(): void
    {
        $service = app(AuditLogService::class);

        $employee = Employee::create([
            'branch_id' => $this->branch1->id,
            'name' => 'Bagus Karyawan',
            'code' => 'EMP-010',
            'is_active' => true,
        ]);

        $log = $service->log(
            action: 'employee_created',
            auditable: $employee,
            oldValues: null,
            newValues: [
                'name' => 'Bagus Karyawan',
                'password' => 'secret_plain_text',
                'pin' => '654321',
                'phone' => '08123456789',
            ],
            userId: $this->superAdmin->id
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'action' => 'employee_created',
            'user_id' => $this->superAdmin->id,
        ]);

        $log->refresh();
        $this->assertSame('[PROTECTED]', $log->new_values['password']);
        $this->assertSame('[PROTECTED]', $log->new_values['pin']);
        $this->assertSame('Bagus Karyawan', $log->new_values['name']);
        $this->assertSame('08123456789', $log->new_values['phone']);
    }

    public function test_audit_log_enforces_worm_immutability(): void
    {
        $log = AuditLog::create([
            'action' => 'security_event',
            'user_id' => $this->superAdmin->id,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('WORM');
        $log->update(['action' => 'tampered_action']);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'action' => 'immutable_event',
            'user_id' => $this->superAdmin->id,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('WORM');
        $log->delete();
    }

    public function test_admin_can_view_audit_logs_portal(): void
    {
        AuditLog::create([
            'action' => 'employee_created',
            'user_id' => $this->superAdmin->id,
            'ip_address' => '192.168.1.10',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.presensi.audit-logs'));

        $response->assertOk();
        $response->assertSee('Audit Trail');
        $response->assertSee('employee_created');
        $response->assertSee('192.168.1.10');
        $response->assertSee('WORM Guard');
    }

    public function test_admin_role_enum_helpers(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertFalse($this->superAdmin->isBranchAdmin());
        $this->assertFalse($this->superAdmin->isAuditor());

        $this->assertTrue($this->branchAdmin->isBranchAdmin());
        $this->assertFalse($this->branchAdmin->isSuperAdmin());

        $this->assertTrue($this->auditor->isAuditor());
        $this->assertFalse($this->auditor->isSuperAdmin());
    }

    public function test_branch_admin_has_assigned_branch_relationship(): void
    {
        $this->assertNotNull($this->branchAdmin->assignedBranch);
        $this->assertSame('Maya Graha Surabaya', $this->branchAdmin->assignedBranch->name);
    }

    public function test_auditor_can_view_read_only_pages(): void
    {
        $response = $this->actingAs($this->auditor)->get(route('admin.presensi.audit-logs'));
        $response->assertOk();
    }

    public function test_auditor_is_blocked_with_403_from_mutating_data(): void
    {
        $response = $this->actingAs($this->auditor)->post(route('admin.karyawan.store'), [
            'name' => 'Fictitious Employee',
            'branch_id' => $this->branch1->id,
            'role' => 'kasir',
        ]);

        $response->assertForbidden();
    }
}

