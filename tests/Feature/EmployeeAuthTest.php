<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class EmployeeAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected Employee $employee;

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
            'name' => 'Bagus Karyawan',
            'code' => 'EMP-001',
            'username' => 'bagus_karyawan',
            'position' => 'Operator',
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_employee_login(): void
    {
        $response = $this->get(route('presensi.index'));

        $response->assertRedirect(route('presensi.login'));
    }

    public function test_employee_can_view_login_page(): void
    {
        $response = $this->get(route('presensi.login'));

        $response->assertOk();
        $response->assertSee($this->employee->name);
    }

    public function test_employee_can_login_via_post(): void
    {
        $response = $this->post(route('presensi.login.post'), [
            'employee_id' => $this->employee->id,
        ]);

        $response->assertRedirect(route('presensi.index'));
        $this->assertEquals($this->employee->id, session('presensi_employee_id'));
    }

    public function test_employee_guard_can_authenticate_model(): void
    {
        Auth::guard('employee')->login($this->employee);

        $this->assertTrue(Auth::guard('employee')->check());
        $this->assertEquals($this->employee->id, Auth::guard('employee')->id());
    }

    public function test_employee_logout_clears_session(): void
    {
        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.logout'));

        $response->assertRedirect(route('presensi.login'));
        $this->assertNull(session('presensi_employee_id'));
    }

    public function test_inactive_employee_cannot_authenticate(): void
    {
        $this->employee->update(['is_active' => false]);

        $response = $this->withSession(['presensi_employee_id' => $this->employee->id])
            ->get(route('presensi.index'));

        $response->assertRedirect(route('presensi.login'));
    }
}
