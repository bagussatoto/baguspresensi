<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Models\LeaveRequest;
use App\Models\ShiftSalesSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AdminApprovalAndAliasesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Kencana',
            'code' => 'MGK-01',
            'address' => 'Jl. Kaliurang KM 5',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin MayaNet Jogja',
            'email' => 'admin@mayanet.com',
            'password' => bcrypt('maya123'),
            'role' => 'super_admin',
        ]);
    }

    public function test_bagus_admin_dashboard_shows_approval_hub_and_deposit_badges(): void
    {
        $employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Karyawan Uji',
            'code' => 'EMP-TEST-01',
            'position' => 'Kasir Shift',
            'is_active' => true,
        ]);

        // Create pending leave
        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'sick',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'days_count' => 1,
            'reason' => 'Demam flu',
            'status' => 'pending',
        ]);

        $shift = Shift::create([
            'branch_id' => $this->branch->id,
            'name' => 'Shift 1 Pagi',
            'code' => 'S1-PG',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_active' => true,
        ]);

        // Create pending shift deposit
        ShiftSalesSummary::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_revenue' => 500000,
            'actual_cash_counted' => 500000,
            'deposit_status' => 'pending_verification',
            'status' => 'closed',
            'is_sales_locked' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get('/bagus/admin');

        $response->assertStatus(200);
        $response->assertSee('Pusat Antrean Otorisasi');
        $response->assertSee('Permohonan Karyawan');
        $response->assertSee('Setoran Kas Kasir');
        $response->assertSee('6. Persetujuan Permohonan');
        $response->assertSee('Filter: Hanya Selisih');
    }

    public function test_bagus_admin_approval_and_subroutes_are_accessible_without_404(): void
    {
        $subroutes = [
            '/bagus/admin/presensi/approval',
            '/bagus/admin/penjualan/penyesuaian-billing',
            '/bagus/admin/penjualan/serah-terima',
            '/bagus/admin/karyawan/verifikasi',
            '/bagus/admin/karyawan/reset-password',
            '/admin/karyawan/verifikasi',
            '/admin/karyawan/reset-password',
            '/bagus/admin/stok-limit',
            '/bagus/admin/cabang/opname',
            '/bagus/admin/cabang',
            '/bagus/admin/cabang/produk',
            '/bagus/admin/cabang/zonasi-harga',
            '/bagus/admin/cabang/konsolidasi',
            '/bagus/admin/cabang/aging',
            '/bagus/admin/merk',
            '/bagus/admin/konsumen',
            '/bagus/admin/jadwal',
            '/bagus/admin/presensi/audit-logs',
        ];

        foreach ($subroutes as $url) {
            $response = $this->actingAs($this->adminUser)
                ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
                ->get($url);

            $this->assertEquals(200, $response->getStatusCode(), "Route {$url} should return 200");
        }
    }

    public function test_leave_can_be_approved_via_both_get_and_post(): void
    {
        $employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus Satoto',
            'code' => 'EMP-MGR-01',
            'position' => 'Manager',
            'is_active' => true,
        ]);

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => 'sick',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today(),
            'days_count' => 1,
            'reason' => 'Istirahat dokter',
            'status' => 'pending',
        ]);

        // Test GET approve
        $responseGet = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get("/bagus/admin/presensi/leave/{$leave->id}/approve");

        $responseGet->assertRedirect(route('admin.presensi.approval'));
        $this->assertEquals('approved', $leave->fresh()->status->value);
        $this->assertEquals($employee->id, $leave->fresh()->approved_by);

        // Reset and test POST approve
        $leave->update(['status' => 'pending', 'approved_by' => null]);

        $responsePost = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->post("/bagus/admin/presensi/leave/{$leave->id}/approve");

        $responsePost->assertRedirect(route('admin.presensi.approval'));
        $this->assertEquals('approved', $leave->fresh()->status->value);
    }
}
