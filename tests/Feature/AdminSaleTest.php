<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\PcStation;
use Tests\TestCase;

class AdminSaleTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private PcStation $pcStation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Maya Graha Kencana',
            'code' => 'NET-PST',
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Rian Pratama',
            'code' => 'OP-001',
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->pcStation = PcStation::create([
            'branch_id' => $this->branch->id,
            'station_code' => 'PC-01',
            'station_type' => 'Reguler',
            'hourly_rate' => 6000,
            'status' => 'available',
        ]);
    }

    public function test_admin_penjualan_page_can_be_rendered(): void
    {
        CafeSale::create([
            'invoice_number' => 'INV-TEST-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Bagus Testing',
            'pc_station_id' => $this->pcStation->id,
            'total_billing' => 12000,
            'total_fnb' => 15000,
            'gross_amount' => 27000,
            'net_amount' => 27000,
            'amount_paid' => 30000,
            'change_amount' => 3000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $response = $this->get(route('admin.penjualan'));

        $response->assertStatus(200);
        $response->assertSee('DAFTAR TRANSAKSI PENJUALAN');
        $response->assertSee('INV-TEST-001');
        $response->assertSee('Bagus Testing');
    }

    public function test_admin_can_fetch_sales_via_json(): void
    {
        CafeSale::create([
            'invoice_number' => 'INV-JSON-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'User JSON',
            'gross_amount' => 20000,
            'net_amount' => 20000,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
        ]);

        $response = $this->getJson(route('admin.penjualan'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'invoice_number' => 'INV-JSON-001',
        ]);
    }

    public function test_admin_can_store_new_sale_transaction(): void
    {
        $payload = [
            'employee_id' => $this->employee->id,
            'customer_name' => 'Gamers Cafe Walkin',
            'pc_station_id' => $this->pcStation->id,
            'payment_method' => 'cash',
            'amount_paid' => 50000,
            'items' => [
                [
                    'name' => 'Paket Billing 3 Jam',
                    'item_type' => 'billing',
                    'qty' => 1,
                    'price' => 18000,
                ],
                [
                    'name' => 'Indomie Goreng Double Telur',
                    'item_type' => 'fnb',
                    'qty' => 1,
                    'price' => 15000,
                ],
            ],
        ];

        $response = $this->postJson(route('admin.penjualan.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('cafe_sales', [
            'customer_name' => 'Gamers Cafe Walkin',
            'employee_id' => $this->employee->id,
            'net_amount' => 33000,
            'total_billing' => 18000,
            'total_fnb' => 15000,
        ]);

        $this->assertDatabaseHas('cafe_sale_items', [
            'item_name' => 'Paket Billing 3 Jam',
            'qty' => 1,
        ]);

        $this->assertDatabaseHas('cafe_payments', [
            'amount_due' => 33000,
            'amount_paid' => 50000,
        ]);
    }

    public function test_admin_can_show_and_print_sale_receipt(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-RECEIPT-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Print Test User',
            'gross_amount' => 10000,
            'net_amount' => 10000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $showResponse = $this->getJson(route('admin.penjualan.show', $sale));
        $showResponse->assertStatus(200);
        $showResponse->assertJsonFragment([
            'invoice_number' => 'INV-RECEIPT-001',
        ]);

        $printResponse = $this->get(route('admin.penjualan.print', $sale));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('MAYA GRAHA KENCANA');
        $printResponse->assertSee('INV-RECEIPT-001');
    }

    public function test_admin_can_update_sale_status(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-STATUS-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Status Test',
            'gross_amount' => 15000,
            'net_amount' => 15000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $response = $this->postJson(route('admin.penjualan.status', $sale), [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_status' => 'cancelled',
        ]);

        $this->assertEquals('cancelled', $sale->fresh()->payment_status);
    }

    public function test_admin_can_delete_sale_transaction(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-DEL-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Delete Test',
            'gross_amount' => 5000,
            'net_amount' => 5000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $response = $this->deleteJson(route('admin.penjualan.destroy', $sale));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('cafe_sales', [
            'id' => $sale->id,
        ]);
    }
}
