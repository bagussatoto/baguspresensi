<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Database\Seeders\InternetCafeSeeder;
use Modules\MasterData\Domain\Models\Attendance;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\EmployeeSchedule;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class InternetCafeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InternetCafeSeeder::class);
    }

    public function test_warnet_dashboard_loads_with_all_ten_erp_modules(): void
    {
        $response = $this->get('/warnet');

        $response->assertStatus(200);
        $response->assertSee('Maya Graha Kencana');
        $response->assertSee('Penjualan / POS Kasir');
        $response->assertSee('Billing & PC Stations', false);
        $response->assertSee('Produk & Kategori', false);
        $response->assertSee('Stok Fisik Inventaris');
        $response->assertSee('Distribusi Stok Barang');
        $response->assertSee('Data Kasir / Karyawan');
        $response->assertSee('Jadwal Shift');
        $response->assertSee('Presensi Karyawan');
        $response->assertSee('Komisi Karyawan');
    }

    public function test_root_route_redirects_or_renders_warnet_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('presensi2.index'));
    }

    public function test_pos_checkout_successfully_creates_sale_payment_and_deducts_fnb_stock(): void
    {
        $employee = Employee::first();
        $pc = PcStation::where('status', 'available')->first();
        $variant = ProductVariant::where('sku', 'like', 'SKU-FNB%')->first();
        $inventory = StockInventory::where('product_variant_id', $variant->id)->first();

        $initialStock = $inventory ? $inventory->qty_on_hand : 100;

        $payload = [
            'employee_id' => $employee->id,
            'customer_name' => 'Gamer Rizky',
            'pc_station_id' => $pc->id,
            'payment_method' => 'cash',
            'amount_paid' => 50000,
            'payment_reference' => 'CASH-TUNAI-50K',
            'items' => [
                [
                    'variant_id' => null,
                    'item_type' => 'billing',
                    'name' => 'Sewa Billing PC 2 Jam',
                    'qty' => 1,
                    'price' => 12000,
                ],
                [
                    'variant_id' => $variant->id,
                    'item_type' => 'fnb',
                    'name' => $variant->name,
                    'qty' => 2,
                    'price' => 10000,
                ],
            ],
        ];

        $response = $this->postJson(route('warnet.checkout'), $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'invoice_number',
            'payment_number',
            'amount_paid',
            'change_amount',
            'sale' => [
                'id',
                'invoice_number',
                'customer_name',
                'net_amount',
                'amount_paid',
                'change_amount',
                'items',
                'payments',
            ],
        ]);

        $this->assertDatabaseHas('cafe_sales', [
            'customer_name' => 'Gamer Rizky',
            'pc_station_id' => $pc->id,
            'payment_method' => 'cash',
            'net_amount' => 32000,
            'amount_paid' => 50000,
            'change_amount' => 18000,
        ]);

        $this->assertDatabaseHas('cafe_payments', [
            'amount_due' => 32000,
            'amount_paid' => 50000,
            'change_amount' => 18000,
            'status' => 'completed',
        ]);

        // Verifikasi PC Station berubah jadi occupied
        $pc->refresh();
        $this->assertEquals('occupied', $pc->status);
        $this->assertEquals('Gamer Rizky', $pc->current_user_name);

        // Verifikasi stok F&B berkurang 2
        if ($inventory) {
            $inventory->refresh();
            $this->assertEquals($initialStock - 2, $inventory->qty_on_hand);
        }
    }

    public function test_pos_checkout_with_flexible_item_types_and_qris_payment(): void
    {
        $employee = Employee::first();
        $variant = ProductVariant::where('sku', 'like', 'SKU-FNB%')->first();

        $payload = [
            'employee_id' => $employee->id,
            'customer_name' => 'Fajar Santoso',
            'payment_method' => 'qris',
            'amount_paid' => 24000,
            'payment_reference' => 'QRIS-GOPAY-88912',
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'item_type' => 'makanan',
                    'name' => 'Indomie Goreng Double',
                    'qty' => 2,
                    'price' => 12000,
                ],
            ],
        ];

        $response = $this->postJson(route('warnet.checkout'), $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('sale.payment_method', 'qris');
        $response->assertJsonPath('sale.net_amount', 24000);
        $response->assertJsonPath('sale.change_amount', 0);
    }

    public function test_pc_station_toggle_session(): void
    {
        $pc = PcStation::where('status', 'available')->first();

        // 1. Start Session
        $startResponse = $this->postJson(route('warnet.pc-toggle'), [
            'station_id' => $pc->id,
            'action' => 'start',
            'user_name' => 'Ahmad ProPlayer',
            'duration_minutes' => 120,
        ]);

        $startResponse->assertStatus(200);
        $pc->refresh();
        $this->assertEquals('occupied', $pc->status);
        $this->assertEquals('Ahmad ProPlayer', $pc->current_user_name);
        $this->assertEquals(120, $pc->duration_minutes);

        // 2. Stop Session
        $stopResponse = $this->postJson(route('warnet.pc-toggle'), [
            'station_id' => $pc->id,
            'action' => 'stop',
        ]);

        $stopResponse->assertStatus(200);
        $pc->refresh();
        $this->assertEquals('available', $pc->status);
        $this->assertNull($pc->current_user_name);
    }

    public function test_employee_attendance_clock_in_and_clock_out(): void
    {
        $employee = Employee::first();

        // Clock In
        $clockInResponse = $this->postJson(route('warnet.clock'), [
            'employee_id' => $employee->id,
            'type' => 'in',
            'notes' => 'Shift pagi tepat waktu',
        ]);

        $clockInResponse->assertStatus(200);
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->check_in_at);

        // Clock Out
        $clockOutResponse = $this->postJson(route('warnet.clock'), [
            'employee_id' => $employee->id,
            'type' => 'out',
            'notes' => 'Serah terima kas selesai',
        ]);

        $clockOutResponse->assertStatus(200);
        $attendance->refresh();
        $this->assertNotNull($attendance->check_out_at);
    }

    public function test_stock_transfer_dispatch_between_warehouses(): void
    {
        $warehouses = Warehouse::take(2)->get();
        $fromWarehouse = $warehouses->first();
        $toWarehouse = $warehouses->last();
        $variant = ProductVariant::where('sku', 'like', 'SKU-FNB%')->first();

        // Siapkan stok awal
        $fromStock = StockInventory::firstOrCreate(
            ['warehouse_id' => $fromWarehouse->id, 'product_variant_id' => $variant->id],
            ['qty_on_hand' => 50]
        );
        $fromStock->update(['qty_on_hand' => 50]);

        $toStock = StockInventory::firstOrCreate(
            ['warehouse_id' => $toWarehouse->id, 'product_variant_id' => $variant->id],
            ['qty_on_hand' => 10]
        );
        $toStock->update(['qty_on_hand' => 10]);

        $payload = [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_variant_id' => $variant->id,
            'qty' => 5,
            'operator_notes' => 'Pengisian display kasir depan',
        ];

        $response = $this->postJson(route('warnet.stock-transfer'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('stock_transfer_dispatches', [
            'from_warehouse_id' => $fromWarehouse->id,
            'to_warehouse_id' => $toWarehouse->id,
            'product_variant_id' => $variant->id,
            'qty' => 5,
        ]);

        $fromStock->refresh();
        $toStock->refresh();
        $this->assertEquals(45, $fromStock->qty_on_hand);
        $this->assertEquals(15, $toStock->qty_on_hand);
    }

    public function test_store_employee_schedule(): void
    {
        $employee = Employee::first();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $payload = [
            'employee_id' => $employee->id,
            'shift_name' => 'Shift Begadang (Malam)',
            'shift_date' => $tomorrow,
            'start_time' => '23:00',
            'end_time' => '07:00',
        ];

        $response = $this->postJson(route('warnet.schedule-store'), $payload);

        $response->assertStatus(200);
        $schedule = EmployeeSchedule::where('employee_id', $employee->id)
            ->where('shift_name', 'Shift Begadang (Malam)')
            ->first();

        $this->assertNotNull($schedule);
        $this->assertTrue(Carbon::parse($schedule->shift_date)->isSameDay(Carbon::parse($tomorrow)));
    }

    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Maya Graha');
        $response->assertSee('Kencana');
        $response->assertSee('admin@mayanet.com');
    }

    public function test_admin_login_authenticates_and_redirects(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mayanet.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertAuthenticated();
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mayanet.com',
            'password' => 'wrongpassword123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_logout(): void
    {
        $admin = User::where('email', 'admin@mayanet.com')->first();
        $this->actingAs($admin);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_karyawan_page_renders_successfully(): void
    {
        $response = $this->get('/admin/karyawan');

        $response->assertStatus(200);
        $response->assertSee('Maya Graha Kencana');
        $response->assertSee('pengaturan karyawan');
        $response->assertSee('Bagus');
        $response->assertSee('AAN');
    }

    public function test_operator_schedule_can_be_created_updated_and_deleted(): void
    {
        $employee = Employee::first();

        // 1. Create Schedule
        $createResponse = $this->postJson(route('warnet.schedule-store'), [
            'employee_id' => $employee->id,
            'shift_name' => 'Shift 2 (07.00 - 13.00)',
            'shift_date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
        ]);

        $createResponse->assertStatus(200);
        $createResponse->assertJsonPath('schedule.shift_name', 'Shift 2 (07.00 - 13.00)');
        $scheduleId = $createResponse->json('schedule.id');

        // 2. Update Schedule
        $updateResponse = $this->putJson(route('warnet.schedule-update', $scheduleId), [
            'shift_name' => 'Shift 3 (13.00 - 19.00)',
            'start_time' => '13:00:00',
            'end_time' => '19:00:00',
            'status' => 'completed',
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('schedule.status', 'completed');
        $updateResponse->assertJsonPath('schedule.shift_name', 'Shift 3 (13.00 - 19.00)');

        // 3. Delete Schedule
        $deleteResponse = $this->deleteJson(route('warnet.schedule-destroy', $scheduleId));

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('employee_schedules', ['id' => $scheduleId]);
    }
}
