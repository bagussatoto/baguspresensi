<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class EmployeePasswordResetApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $employee;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::create([
            'name' => 'Cabang Pusat MGK',
            'code' => 'MGK-PST',
            'address' => 'Jl. Pusat No. 1',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Bagus Satoto',
            'code' => 'EMP-001',
            'username' => 'bagus',
            'password' => Hash::make('oldsecret123'),
            'pin' => Hash::make('123456'),
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin MayaNet',
            'email' => 'admin@mayanet.com',
            'password' => Hash::make('admin123'),
        ]);
    }

    public function test_employee_submitting_forgot_password_creates_pending_request(): void
    {
        $response = $this->post(route('presensi2.forgot-password.post'), [
            'employee_name' => 'Bagus Satoto',
            'old_credential' => '123456',
            'new_credential' => '739281',
            'confirm_credential' => '739281',
        ]);

        $response->assertRedirect(route('presensi2.login'));
        $response->assertSessionHas('success');

        // Pastikan ada request berstatus pending
        $this->assertDatabaseHas('password_reset_requests', [
            'employee_id' => $this->employee->id,
            'employee_name' => 'Bagus Satoto',
            'status' => 'pending',
        ]);

        // Verifikasi bahwa password/PIN di tabel employees BELUM berubah sebelum disetujui
        $freshEmployee = $this->employee->fresh();
        $this->assertTrue(Hash::check('123456', $freshEmployee->pin));
        $this->assertFalse(Hash::check('739281', $freshEmployee->pin));
    }

    public function test_admin_can_view_pending_password_reset_requests(): void
    {
        PasswordResetRequest::create([
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_code' => $this->employee->code,
            'phone' => $this->employee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('654321'),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.karyawan.reset-password'));

        $response->assertOk();
        $response->assertSee('Otorisasi Reset Password');
        $response->assertSee('Bagus Satoto');
        $response->assertSee('081234567890');
        $response->assertSee('Setujui');
        $response->assertSee('Tolak');
    }

    public function test_admin_can_approve_password_reset_request(): void
    {
        $resetReq = PasswordResetRequest::create([
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_code' => $this->employee->code,
            'phone' => $this->employee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('654321'),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.karyawan.reset-password.approve', $resetReq->id));

        $response->assertRedirect(route('admin.karyawan.reset-password'));
        $response->assertSessionHas('success');

        // Pastikan status request berubah jadi approved
        $this->assertEquals('approved', $resetReq->fresh()->status);
        $this->assertEquals($this->admin->id, $resetReq->fresh()->approved_by);

        // Karyawan sekarang sudah bisa login dengan PIN baru
        $freshEmployee = $this->employee->fresh();
        $this->assertTrue(Hash::check('654321', $freshEmployee->pin));
    }

    public function test_admin_can_reject_password_reset_request(): void
    {
        $resetReq = PasswordResetRequest::create([
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_code' => $this->employee->code,
            'phone' => $this->employee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('654321'),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.karyawan.reset-password.reject', $resetReq->id), [
            'rejection_reason' => 'Nomor WhatsApp tidak aktif saat dikonfirmasi.',
        ]);

        $response->assertRedirect(route('admin.karyawan.reset-password'));
        $response->assertSessionHas('info');

        // Pastikan status request berubah jadi rejected dengan alasan
        $this->assertEquals('rejected', $resetReq->fresh()->status);
        $this->assertEquals('Nomor WhatsApp tidak aktif saat dikonfirmasi.', $resetReq->fresh()->rejection_reason);

        // Kredensial lama karyawan tetap aman tidak berubah
        $freshEmployee = $this->employee->fresh();
        $this->assertTrue(Hash::check('123456', $freshEmployee->pin));
    }

    public function test_admin_can_update_password_reset_notes_and_reason(): void
    {
        $resetReq = PasswordResetRequest::create([
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_code' => $this->employee->code,
            'phone' => $this->employee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('654321'),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.karyawan.reset-password.update', $resetReq->id), [
            'notes' => 'Telah diverifikasi via call langsung dengan karyawan.',
        ]);

        $response->assertRedirect(route('admin.karyawan.reset-password'));
        $response->assertSessionHas('success');

        $this->assertEquals('Telah diverifikasi via call langsung dengan karyawan.', $resetReq->fresh()->notes);
    }

    public function test_admin_can_delete_password_reset_record(): void
    {
        $resetReq = PasswordResetRequest::create([
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_code' => $this->employee->code,
            'phone' => $this->employee->phone,
            'requested_type' => 'pin',
            'new_credential' => Hash::make('654321'),
            'status' => 'rejected',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.karyawan.reset-password.destroy', $resetReq->id));

        $response->assertRedirect(route('admin.karyawan.reset-password'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('password_reset_requests', [
            'id' => $resetReq->id,
        ]);
    }

    public function test_employee_can_view_check_reset_status_page(): void
    {
        $response = $this->get(route('presensi2.check-reset-status'));
        $response->assertOk();
        $response->assertSee('Cek Status Reset');
    }

    public function test_employee_can_query_reset_status(): void
    {
        PasswordResetRequest::create([
            'employee_id' => $this->employee->id,
            'employee_name' => 'Bagus Satoto',
            'employee_code' => $this->employee->code,
            'phone' => '081234567890',
            'requested_type' => 'pin',
            'new_credential' => Hash::make('654321'),
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->post(route('presensi2.check-reset-status.query'), [
            'keyword' => 'Bagus Satoto',
        ]);

        $response->assertOk();
        $response->assertSee('Menunggu Otorisasi Admin');
        $response->assertSee('Bagus Satoto');
    }
}
