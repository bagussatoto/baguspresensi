<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::firstOrCreate(
            ['code' => 'NET-PST'],
            ['name' => 'Maya Graha Kencana', 'is_active' => true]
        );

        $this->staff = Employee::firstOrCreate(
            ['code' => 'EMP-TEST-01'],
            [
                'branch_id' => $branch->id,
                'name' => 'Siti Nurhaliza',
                'username' => 'siti',
                'email' => 'siti@mayanet.com',
                'phone' => '081234567890',
                'position' => 'Staff Kasir',
                'password' => Hash::make('staff123'),
                'pin' => Hash::make('staff123'),
                'is_active' => true,
            ]
        );
    }

    public function test_staff_can_view_profile_dashboard(): void
    {
        $response = $this->withSession([
            'admin_role' => 'Staff',
            'admin_user_name' => $this->staff->name,
            'admin_employee_id' => $this->staff->id,
            'admin_logged_in' => true,
        ])->get(route('admin.staff.profile'));

        $response->assertStatus(200);
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('EMP-TEST-01');
        $response->assertSee('4. Total Presensi');
        $response->assertSee('5. Pendapatan Penjualan');
        $response->assertSee('6. Estimasi Gaji');
        $response->assertSee('3. Ubah Kata Sandi & PIN Staff');
    }

    public function test_staff_can_update_password_and_pin(): void
    {
        $response = $this->withSession([
            'admin_role' => 'Staff',
            'admin_user_name' => $this->staff->name,
            'admin_employee_id' => $this->staff->id,
            'admin_logged_in' => true,
        ])->post(route('admin.staff.password.update'), [
            'employee_id' => $this->staff->id,
            'current_password' => 'staff123',
            'new_password' => 'newSecretPass789',
            'new_password_confirmation' => 'newSecretPass789',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('password_success');

        $this->staff->refresh();
        $this->assertTrue(Hash::check('newSecretPass789', $this->staff->password));
        $this->assertTrue(Hash::check('newSecretPass789', $this->staff->pin));
    }

    public function test_staff_password_update_requires_matching_confirmation(): void
    {
        $response = $this->withSession([
            'admin_role' => 'Staff',
            'admin_user_name' => $this->staff->name,
            'admin_employee_id' => $this->staff->id,
            'admin_logged_in' => true,
        ])->post(route('admin.staff.password.update'), [
            'employee_id' => $this->staff->id,
            'current_password' => 'staff123',
            'new_password' => 'secret123',
            'new_password_confirmation' => 'mismatchPass',
        ]);

        $response->assertSessionHasErrors(['new_password']);
    }
}
