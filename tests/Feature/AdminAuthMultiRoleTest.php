<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AdminAuthMultiRoleTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $staffEmployee;

    private Employee $managerEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Admin MayaNet Jogja',
            'email' => 'admin@mayanet.com',
            'password' => Hash::make('maya123'),
        ]);

        $this->managerEmployee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus',
            'code' => 'EMP-043',
            'username' => 'bagus',
            'position' => 'Manager',
            'email' => 'bagusbudi1308@gmail.com',
            'password' => Hash::make('maya123'),
            'pin' => Hash::make('maya123'),
            'is_active' => true,
        ]);

        $this->staffEmployee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Grace',
            'code' => 'EMP-047',
            'username' => 'grace',
            'position' => 'Staff',
            'email' => 'grace@maya.com',
            'password' => Hash::make('presensi123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);
    }

    public function test_admin_login_page_renders_with_manager_and_staff_roles(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('Maya Graha');
        $response->assertSee('1. Manager');
        $response->assertSee('2. Staff');
        $response->assertSee('maya123');
        $response->assertSee('name="manager_identifier"', false);
        $response->assertSee('name="staff_identifier"', false);
    }

    public function test_login_route_alias_also_renders_admin_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('1. Manager');
        $response->assertSee('2. Staff');
    }

    public function test_manager_can_login_with_password_maya123(): void
    {
        $response = $this->post(route('admin.login.post'), [
            'role' => 'manager',
            'manager_identifier' => 'admin@mayanet.com',
            'password' => 'maya123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertEquals('Manager', session('admin_role'));
        $this->assertTrue(session('admin_logged_in'));
        $this->assertAuthenticated();
    }

    public function test_manager_login_fails_with_wrong_password(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'role' => 'manager',
            'manager_identifier' => 'admin@mayanet.com',
            'password' => 'salah_password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['password']);
        $this->assertNull(session('admin_role'));
    }

    public function test_staff_can_login_with_presensi2_password(): void
    {
        $response = $this->post(route('admin.login.post'), [
            'role' => 'staff',
            'staff_identifier' => $this->staffEmployee->id,
            'password' => 'presensi123',
        ]);

        $response->assertRedirect(route('admin.staff.profile'));
        $this->assertEquals('Staff', session('admin_role'));
        $this->assertEquals($this->staffEmployee->name, session('admin_user_name'));
        $this->assertEquals($this->staffEmployee->id, session('admin_employee_id'));
        $this->assertTrue(session('admin_logged_in'));
    }

    public function test_staff_can_login_with_presensi2_pin(): void
    {
        $response = $this->post(route('admin.login.post'), [
            'role' => 'staff',
            'staff_identifier' => 'EMP-047',
            'password' => '123456',
        ]);

        $response->assertRedirect(route('admin.staff.profile'));
        $this->assertEquals('Staff', session('admin_role'));
        $this->assertEquals('Grace', session('admin_user_name'));
    }

    public function test_staff_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.post'), [
            'role' => 'staff',
            'staff_identifier' => $this->staffEmployee->id,
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['password']);
        $this->assertNull(session('admin_role'));
    }

    public function test_admin_logout_clears_role_session(): void
    {
        // Login as Staff first
        $this->post(route('admin.login.post'), [
            'role' => 'staff',
            'staff_identifier' => $this->staffEmployee->id,
            'password' => 'presensi123',
        ]);

        $this->assertEquals('Staff', session('admin_role'));

        // Logout
        $logoutResponse = $this->post(route('admin.logout'));
        $logoutResponse->assertRedirect(route('login'));
        $this->assertNull(session('admin_role'));
        $this->assertNull(session('admin_user_name'));
    }
}
