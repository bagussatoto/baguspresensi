<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Enums\BranchType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class ReceiptAndClosingPrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed 4 basic shifts jika belum ada
        $shiftsData = [
            ['code' => 'SHIFT-1', 'name' => 'Shift 1 Pagi', 'start_time' => '07:00:00', 'end_time' => '12:00:00', 'is_active' => true],
            ['code' => 'SHIFT-2', 'name' => 'Shift 2 Siang', 'start_time' => '12:00:00', 'end_time' => '17:00:00', 'is_active' => true],
            ['code' => 'SHIFT-3', 'name' => 'Shift 3 Sore', 'start_time' => '17:00:00', 'end_time' => '22:00:00', 'is_active' => true],
            ['code' => 'SHIFT-4', 'name' => 'Shift 4 Malam', 'start_time' => '22:00:00', 'end_time' => '07:00:00', 'is_active' => true],
        ];

        foreach ($shiftsData as $sd) {
            Shift::firstOrCreate(['code' => $sd['code']], $sd);
        }
    }

    public function test_unauthenticated_user_cannot_access_closing_print(): void
    {
        $response = $this->get(route('presensi2.closing.print'));
        $response->assertRedirect(route('presensi2.login'));
    }

    public function test_authenticated_cashier_can_access_closing_print_document(): void
    {
        $branch = Branch::create([
            'code' => 'STR-TEST',
            'name' => 'Store Test Surabaya',
            'branch_type' => BranchType::Store->value,
            'address' => 'Jl. Pemuda No. 45 Surabaya',
            'phone' => '031-5551234',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'branch_id' => $branch->id,
            'code' => 'EMP-TEST-01',
            'name' => 'Budi Kasir Test',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->withSession([
            'presensi2_employee_id' => $employee->id,
            'presensi2_branch_id' => $branch->id,
        ])->get(route('presensi2.closing.print', ['date' => Carbon::today()->toDateString()]));

        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA CLOSING');
        $response->assertSee('SETTLEMENT KAS HARIAN');
        $response->assertSee('Store Test Surabaya');
        $response->assertSee('BA-CLS/STR-TEST/');
        $response->assertSee('Thermal 80mm');
        $response->assertSee('Budi Kasir Test');
    }

    public function test_admin_sale_print_shows_dynamic_branch_and_thermal_options(): void
    {
        $branch = Branch::create([
            'code' => 'STR-KELAPA',
            'name' => 'Store Kelapa Gading',
            'branch_type' => BranchType::Store->value,
            'address' => 'Jl. Boulevard Raya Blok LA',
            'phone' => '021-998877',
            'is_active' => true,
        ]);

        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-PRT-001',
            'branch_id' => $branch->id,
            'customer_name' => 'Bapak Ronald',
            'gross_amount' => 50000,
            'discount_amount' => 5000,
            'net_amount' => 45000,
            'amount_paid' => 50000,
            'change_amount' => 5000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $response = $this->get(route('admin.penjualan.print', $sale->id));

        $response->assertStatus(200);
        $response->assertSee('Store Kelapa Gading');
        $response->assertSee('Jl. Boulevard Raya Blok LA');
        $response->assertSee('58mm');
        $response->assertSee('80mm');
        $response->assertSee('INV-TEST-PRT-001');
    }
}
