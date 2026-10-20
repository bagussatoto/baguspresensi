<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AdminEmployeeTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

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
            'name' => 'Bagus',
            'code' => 'MGR-001',
            'username' => 'bagus',
            'email' => 'bagus@mayagraha.com',
            'phone' => '081234567890',
            'position' => 'Manager',
            'jabatan' => 'mana',
            'employment_status' => 'tetap',
            'seniority' => 'senior',
            'evaluation' => 'Passed',
            'max_discount_approval_percent' => 50.0,
            'is_active' => true,
        ]);
    }

    public function test_admin_employee_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.karyawan'));

        $response->assertStatus(200);
        $response->assertSee('pengaturan karyawan');
        $response->assertSee('Profil Karyawan');
        $response->assertSee('Bagus');
        $response->assertSee('MGR-001');
    }

    public function test_admin_karyawan_profile_route_redirects_to_hash(): void
    {
        $response = $this->get(route('admin.karyawan.profile'));

        $response->assertRedirect(route('admin.karyawan').'#profile');
    }

    public function test_admin_can_fetch_employee_detail_json_with_stats_and_attendance(): void
    {
        $shift = Shift::create([
            'name' => 'Shift 2 Siang',
            'code' => 'SHIFT-2',
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
            'is_overnight' => false,
            'is_active' => true,
        ]);

        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(7, 0),
            'clock_out_at' => Carbon::today()->setTime(13, 0),
            'duration_minutes' => 360,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::WebKiosk,
        ]);

        $response = $this->getJson(route('admin.karyawan.show', $this->employee->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'employee' => [
                'id' => $this->employee->id,
                'name' => 'Bagus',
                'code' => 'MGR-001',
            ],
            'stats' => [
                'total_hadir' => 1,
                'total_telat' => 0,
            ],
        ]);
        $response->assertJsonStructure([
            'success',
            'employee',
            'stats' => [
                'total_hadir',
                'total_telat',
                'menit_telat',
                'total_alpa',
                'total_cuti',
                'jam_lembur',
                'total_jam_kerja',
            ],
            'recent_attendance',
        ]);
    }

    public function test_admin_can_update_employee_profile_via_api(): void
    {
        $response = $this->putJson(route('admin.karyawan.update', $this->employee->id), [
            'name' => 'Bagus Wijaya',
            'phone' => '089988776655',
            'position' => 'General Manager',
            'seniority' => 'senior',
            'max_discount_approval_percent' => 75.0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->employee->refresh();
        $this->assertEquals('Bagus Wijaya', $this->employee->name);
        $this->assertEquals('089988776655', $this->employee->phone);
        $this->assertEquals('General Manager', $this->employee->position);
        $this->assertEquals(75.0, $this->employee->max_discount_approval_percent);
    }
}
