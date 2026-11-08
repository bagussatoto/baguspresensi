<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftHandoverChecklist;
use App\Models\ShiftSalesSummary;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class ShiftSalesLockingAndPreviousShiftTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift1;

    private Shift $shift2;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-MGK',
            'name' => 'MGK Pusat',
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-MGK',
            'name' => 'Gudang MGK',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $this->shift1 = Shift::create([
            'code' => 'SHIFT-1',
            'name' => 'Shift 1 Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_active' => true,
        ]);

        $this->shift2 = Shift::create([
            'code' => 'SHIFT-2',
            'name' => 'Shift 2 Sore',
            'start_time' => '15:00:00',
            'end_time' => '22:00:00',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'code' => 'EMP-001',
            'nik' => 'EMP-001',
            'name' => 'Bagus Kasir',
            'email' => 'bagus.kasir@mgk.com',
            'phone' => '081234567890',
            'role' => 'operator',
            'pin' => '1234',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'code' => 'CAT-DRINK',
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $prod = Product::create([
            'category_id' => $cat->id,
            'code' => 'PRD-AQUA',
            'name' => 'Aqua 600ml',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $prod->id,
            'sku' => 'AQUA-600',
            'name' => 'Aqua 600ml',
            'base_price' => 5000,
            'cost_price' => 3000,
            'is_active' => true,
        ]);
    }

    public function test_sales_are_locked_when_rekap_is_submitted(): void
    {
        $today = Carbon::today();

        // 1. Catat transaksi penjualan
        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'total_fnb' => 15000,
            'net_amount' => 15000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'is_locked' => false,
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variant->id,
            'item_name' => 'Aqua 600ml',
            'qty' => 3,
            'unit_price' => 5000,
            'subtotal' => 15000,
        ]);

        // 2. Submit Rekap Setoran Shift
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.rekap.setor'), [
                'deposited_amount' => 15000,
                'retained_drawer_cash' => 50000,
                'deposit_method' => 'cash_collector',
                'deposit_notes' => 'Rekap selesai tutup shift',
            ]);

        $response->assertRedirect();

        // 3. Verifikasi shift summary dan cafe sale berstatus locked
        $summary = ShiftSalesSummary::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertTrue((bool) $summary->is_sales_locked);
        $this->assertEquals('closed', $summary->status);

        $sale->refresh();
        $this->assertTrue((bool) $sale->is_locked);
        $this->assertNotNull($sale->locked_at);
    }

    public function test_cannot_add_transaction_to_a_locked_shift(): void
    {
        $today = Carbon::today();

        // Buat absensi clock-in
        AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift1->id,
            'work_date' => $today->toDateString(),
            'clock_in_at' => $today->copy()->setTime(7, 5),
        ]);

        // Buat summary yang sudah locked / closed
        ShiftSalesSummary::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift1->id,
            'work_date' => $today->toDateString(),
            'total_revenue' => 50000,
            'status' => 'closed',
            'is_sales_locked' => true,
            'locked_sales_amount' => 50000,
            'locked_sales_at' => now(),
        ]);

        // Coba checkout baru
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'variant_id' => $this->variant->id,
                'qty' => 1,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
        ]);
        $response->assertSee('DIKUNCI');
    }

    public function test_cannot_void_a_locked_transaction(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-LOCKED-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'total_fnb' => 10000,
            'net_amount' => 10000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'is_locked' => true,
            'locked_at' => now(),
        ]);

        // Coba void nota yang terkunci
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi.cancel', $sale), [
                'supervisor_pin' => '9999',
                'void_reason' => 'Salah input',
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
        $response->assertSee('DIKUNCI');
    }

    public function test_previous_shift_checklist_and_sales_can_be_retrieved(): void
    {
        $yesterday = Carbon::yesterday();

        // Rekam checklist shift kemarin
        $checklist = ShiftHandoverChecklist::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift1->id,
            'check_date' => $yesterday->toDateString(),
            'paper_a4_qty' => 450,
            'paper_f4_qty' => 200,
            'hardware_status' => 'ok',
            'status' => 'completed',
            'completed_at' => $yesterday->copy()->setTime(15, 0),
        ]);

        // Rekam sales shift kemarin
        CafeSale::create([
            'invoice_number' => 'INV-PREV-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'total_fnb' => 25000,
            'net_amount' => 25000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => $yesterday->copy()->setTime(10, 0),
        ]);

        // Buka halaman POS Desktop
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('Checklist Shift Lalu');
        $response->assertSee('Penjualan Shift Lalu');
        $response->assertSee('Settlement Harian');
        $response->assertSee('modal-prev-checklist');
        $response->assertSee('modal-prev-sales');
        $response->assertSee('modal-daily-settlement');
    }
}
