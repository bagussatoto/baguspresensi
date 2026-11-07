<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Models\PrinterPrintJob;
use App\Models\ShiftHandoverChecklist;
use App\Models\ShiftSalesSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class ShiftStockAndSalesFlowTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private Shift $shift;

    private Warehouse $warehouse;

    private ProductVariant $variantA4;

    private ProductVariant $variantF4;

    private ProductVariant $variantKopi;

    private PcStation $station;

    private Employee $supervisor;

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
            'name' => 'Bagus Operator',
            'code' => 'EMP-001',
            'position' => 'Operator',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('654321'),
            'is_active' => true,
        ]);

        $this->supervisor = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Anton Supervisor',
            'code' => 'SPV-001',
            'position' => 'Supervisor',
            'role' => EmployeeRole::SUPERVISOR,
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('999888'),
            'is_active' => true,
        ]);

        $this->shift = Shift::create([
            'name' => 'Shift 1 Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-KASIR-01',
            'name' => 'Loker & Etalase Kasir Warnet',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uomRim = UnitOfMeasurement::create([
            'code' => 'RIM',
            'name' => 'Rim',
            'symbol' => 'rim',
            'is_active' => true,
        ]);

        $uomCup = UnitOfMeasurement::create([
            'code' => 'CUP',
            'name' => 'Cup',
            'symbol' => 'cup',
            'is_active' => true,
        ]);

        $catPaper = Category::create([
            'code' => 'CAT-PAPER',
            'name' => 'Kertas & ATK',
            'slug' => 'kertas-atk',
            'is_active' => true,
        ]);

        $catDrink = Category::create([
            'code' => 'CAT-DRINK',
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $prodA4 = Product::create([
            'code' => 'PRD-A4',
            'name' => 'Kertas A4 70gsm',
            'category_id' => $catPaper->id,
            'uom_id' => $uomRim->id,
            'is_active' => true,
        ]);

        $this->variantA4 = ProductVariant::create([
            'product_id' => $prodA4->id,
            'sku' => 'SKU-KERTAS-A4-RIM',
            'name' => 'Kertas HVS A4 1 Rim',
            'base_price' => 45000,
            'is_active' => true,
        ]);

        $prodF4 = Product::create([
            'code' => 'PRD-F4',
            'name' => 'Kertas F4 70gsm',
            'category_id' => $catPaper->id,
            'uom_id' => $uomRim->id,
            'is_active' => true,
        ]);

        $this->variantF4 = ProductVariant::create([
            'product_id' => $prodF4->id,
            'sku' => 'SKU-KERTAS-F4-RIM',
            'name' => 'Kertas HVS F4 1 Rim',
            'base_price' => 50000,
            'is_active' => true,
        ]);

        $prodKopi = Product::create([
            'code' => 'PRD-KOPI',
            'name' => 'Es Kopi Susu Aren',
            'category_id' => $catDrink->id,
            'uom_id' => $uomCup->id,
            'is_active' => true,
        ]);

        $this->variantKopi = ProductVariant::create([
            'product_id' => $prodKopi->id,
            'sku' => 'SKU-KOPI-AREN',
            'name' => 'Es Kopi Susu Aren Dingin',
            'base_price' => 12000,
            'is_active' => true,
        ]);

        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variantA4->id,
            'qty_on_hand' => 15,
            'safety_stock' => 2,
        ]);

        $this->station = PcStation::create([
            'branch_id' => $this->branch->id,
            'station_code' => 'PC-01',
            'station_type' => 'Reguler',
            'hourly_rate' => 6000,
            'status' => 'available',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_stok_or_penjualan(): void
    {
        $resStok = $this->get(route('presensi2.stok'));
        $resStok->assertRedirect(route('presensi2.login'));

        $resPenjualan = $this->get(route('presensi2.penjualan'));
        $this->assertTrue($resPenjualan->isRedirect());
    }

    private function clockInStaff(): AttendanceRecord
    {
        return AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::AdminManual,
            'latitude' => $this->branch->latitude,
            'longitude' => $this->branch->longitude,
            'is_mock_location' => false,
        ]);
    }

    public function test_staff_cannot_view_stok_page_before_presensi(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok'));

        $response->assertRedirect(route('presensi2.index'));
        $response->assertSessionHas('error');
    }

    public function test_authenticated_staff_can_view_stok_page_with_required_elements(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok'));

        $response->assertStatus(200);
        $response->assertSee('Pemeriksaan Stok');
        $response->assertSee('1. Stok Kertas Print');
        $response->assertSee('Kertas HVS A4');
        $response->assertSee('Kertas HVS F4');
        $response->assertSee('2. Stok Minuman');
        $response->assertSee('3. Stok Makanan');
        $response->assertDontSee('Minuman Bebas');
        $response->assertDontSee('Makanan Bebas');
        $response->assertSee('Cek Kelayakan Hardware');
        $response->assertSee('Cek Daftar Billing Internet');
        $response->assertSee('Shift Sebelumnya');
        $response->assertSee('6. Selesai');
        $response->assertSee('Buka POS Kasir Desktop');
        $response->assertSee('Restock');
        $response->assertSee('modal-quick-restock');
    }

    public function test_submitting_stok_checklist_fails_if_checklist_incomplete(): void
    {
        $this->clockInStaff();

        // 1. Gagal jika verifikasi billing kasir belum dicentang
        $responseBilling = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'paper_a4' => '1',
                'paper_f4' => '1',
                'hardware_checklist' => ['pc_stations'],
                'billing_verified' => '0',
            ]);

        $responseBilling->assertSessionHas('error');

        // 2. Gagal jika hardware checklist kosong
        $responseHardware = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'paper_a4' => '1',
                'paper_f4' => '1',
                'hardware_checklist' => [],
                'billing_verified' => '1',
            ]);

        $responseHardware->assertSessionHas('error');
    }

    public function test_submitting_stok_checklist_saves_data_and_redirects_to_penjualan(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'paper_a4' => '1',
                'paper_a4_qty' => 500,
                'paper_f4' => '1',
                'paper_f4_qty' => 500,
                'drink_free_checked' => '1',
                'drink_items' => ['Es Kopi Susu Aren'],
                'drink_notes' => 'Minuman display lengkap',
                'food_free_checked' => '1',
                'food_items' => ['Indomie Goreng'],
                'food_notes' => 'Snack aman',
                'hardware_status' => 'normal',
                'hardware_checklist' => ['pc_stations', 'printer_thermal', 'lan_network'],
                'hardware_notes' => 'Semua unit PC dan printer normal',
                'billing_shift_income' => 350000,
                'actual_cash_received' => 350000,
                'cash_handover_notes' => 'Uang fisik di laci pas dan sesuai',
                'billing_verified' => '1',
            ]);

        $response->assertRedirect(route('pos'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_handover_checklists', [
            'employee_id' => $this->employee->id,
            'paper_a4' => true,
            'paper_f4' => true,
            'drink_free_checked' => true,
            'food_free_checked' => true,
            'hardware_status' => 'normal',
            'status' => 'completed',
        ]);

        $savedChecklist = ShiftHandoverChecklist::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($savedChecklist);
        $this->assertEquals(350000, $savedChecklist->billing_shift_income);
        $this->assertIsArray($savedChecklist->billing_details);
        $this->assertEquals(350000, $savedChecklist->billing_details['actual_cash_received']);
        $this->assertEquals('Uang fisik di laci pas dan sesuai', $savedChecklist->billing_details['cash_notes']);

        $savedChecklist = ShiftHandoverChecklist::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($savedChecklist);
        $this->assertEquals(350000, $savedChecklist->billing_shift_income);
        $this->assertIsArray($savedChecklist->billing_details);
        $this->assertEquals(350000, $savedChecklist->billing_details['actual_cash_received']);
        $this->assertEquals('Uang fisik di laci pas dan sesuai', $savedChecklist->billing_details['cash_notes']);

        // Ketika kasir membuka kembali halaman stok di cabang yang sama hari ini, muncul banner Mode Cepat "Stok Cabang Hari Ini Sudah Diverifikasi"
        $revisitResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok'));

        $revisitResponse->assertOk();
        $revisitResponse->assertSee('Stok Cabang Hari Ini Sudah Diverifikasi');
        $revisitResponse->assertSee('Langsung ke Kasir Penjualan');
    }

    public function test_clock_in_returns_redirect_url_to_stok_page(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 8, 0, 0));

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.clock-in'), [
                'shift_id' => $this->shift->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('presensi2.stok'),
        ]);

        Carbon::setTestNow();
    }

    public function test_authenticated_staff_can_view_penjualan_page(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.penjualan'));

        $response->assertStatus(200);
        $response->assertSee('Halaman Penjualan');
        $response->assertSee('Orderan POS');
        $response->assertSee('2. Pendapatan Internet');
        $response->assertSee('4. Selesai (Rekap Shift)');
        $response->assertSee('5. Check Out Presensi');
        $response->assertSee('Audit Perbandingan Stok');
        $response->assertSee('Sebelumnya');
        $response->assertSee('Sesudahnya');
        $response->assertSee('pcs');
        $response->assertSee('Rincian data billing', false);
        $response->assertSee('Shif saya', false);
        $response->assertSee('Shif selanjutnya', false);
        $response->assertSee('Total', false);
        $response->assertSee('Netto', false);
    }

    public function test_can_record_fnb_sale_on_penjualan_page(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'variant_id' => $this->variantKopi->id,
                'qty' => 2,
                'payment_method' => 'cash',
                'customer_name' => 'Fajar PC-01',
            ]);

        $response->assertRedirect(route('presensi2.penjualan'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cafe_sales', [
            'employee_id' => $this->employee->id,
            'total_fnb' => 24000.00,
            'payment_method' => 'cash',
        ]);
    }

    public function test_can_record_fnb_sale_via_json_cart_payload(): void
    {
        $this->clockInStaff();

        $initialStock = (int) StockInventory::where('product_variant_id', $this->variantKopi->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty_on_hand');

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'cart_items' => [
                    [
                        'variant_id' => $this->variantKopi->id,
                        'qty' => 2,
                    ],
                ],
                'customer_name' => 'Pelanggan Meja 4',
                'payment_method' => 'cash',
                'amount_paid' => 24000,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'invoice_number',
            'sale',
            'total_fnb',
        ]);

        $this->assertDatabaseHas('cafe_sales', [
            'employee_id' => $this->employee->id,
            'customer_name' => 'Pelanggan Meja 4',
            'total_fnb' => 24000.00,
            'net_amount' => 24000.00,
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('cafe_payments', [
            'amount_due' => 24000.00,
            'amount_paid' => 24000.00,
            'status' => 'completed',
        ]);

        $newStock = (int) StockInventory::where('product_variant_id', $this->variantKopi->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty_on_hand');

        $this->assertEquals($initialStock - 2, $newStock);
    }

    public function test_can_record_fnb_sale_with_registered_customer_member(): void
    {
        $this->clockInStaff();

        $customer = Customer::create([
            'code' => 'CUST-MBR-001',
            'name' => 'Budi Member Setia',
            'customer_type' => CustomerType::MEMBER,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'cart_items' => [
                    [
                        'variant_id' => $this->variantKopi->id,
                        'qty' => 1,
                    ],
                ],
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'payment_method' => 'cash',
                'amount_paid' => 12000,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'sale' => [
                'customer_id' => $customer->id,
                'customer_name' => 'Budi Member Setia',
            ],
        ]);

        $this->assertDatabaseHas('cafe_sales', [
            'employee_id' => $this->employee->id,
            'customer_id' => $customer->id,
            'customer_name' => 'Budi Member Setia',
            'total_fnb' => 12000.00,
            'payment_status' => 'paid',
        ]);
    }

    public function test_penjualan_page_renders_registered_customers_in_datalist(): void
    {
        $this->clockInStaff();

        $customer = Customer::create([
            'code' => 'CUST-MBR-002',
            'name' => 'Siti Langganan Kafe',
            'customer_type' => CustomerType::MEMBER,
            'phone' => '08987654321',
            'is_active' => true,
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.penjualan'));

        $response->assertOk();
        $response->assertSee('Siti Langganan Kafe');
        $response->assertSee('CUST-MBR-002');
        $response->assertSee('registered-customers-datalist');
    }

    public function test_can_record_fnb_sale_with_exact_cash_when_amount_paid_omitted(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'cart_items' => [
                    [
                        'variant_id' => $this->variantKopi->id,
                        'qty' => 1,
                    ],
                ],
                'payment_method' => 'cash',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('cafe_sales', [
            'employee_id' => $this->employee->id,
            'net_amount' => 12000.00,
            'amount_paid' => 12000.00,
            'change_amount' => 0.00,
        ]);
    }

    public function test_can_record_internet_billing_on_penjualan_page(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'internet',
                'pc_station_id' => $this->station->id,
                'hours' => 3,
                'billing_amount' => 16000,
                'payment_method' => 'qris',
                'customer_name' => 'Member Gaming',
            ]);

        $response->assertRedirect(route('presensi2.penjualan'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cafe_sales', [
            'employee_id' => $this->employee->id,
            'pc_station_id' => $this->station->id,
            'total_billing' => 16000.00,
            'payment_method' => 'qris',
        ]);
    }

    public function test_can_submit_stock_request_for_paper_or_supplies(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.permintaan-stok'), [
                'item_category' => 'kertas',
                'item_name' => 'Kertas HVS A4 70gsm',
                'qty_requested' => 5,
                'unit' => 'Rim',
                'urgency' => 'normal',
                'notes' => 'Stok loker kasir tersisa 1 rim',
            ]);

        $response->assertRedirect(route('presensi2.penjualan'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_stock_requests', [
            'employee_id' => $this->employee->id,
            'item_category' => 'kertas',
            'item_name' => 'Kertas HVS A4 70gsm',
            'qty_requested' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_quick_restock_via_ajax_endpoint_returns_json_and_records_shift_stock_request(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.permintaan-stok'), [
                'item_category' => 'minuman',
                'item_name' => 'Kopi Kapal Api Panas',
                'qty_requested' => 2,
                'unit' => 'Dus',
                'urgency' => 'high',
                'notes' => 'Permintaan cepat dari halaman stok',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'request' => [
                'item_name' => 'Kopi Kapal Api Panas',
                'qty_requested' => 2,
                'urgency' => 'high',
            ],
        ]);

        $this->assertDatabaseHas('shift_stock_requests', [
            'employee_id' => $this->employee->id,
            'item_category' => 'minuman',
            'item_name' => 'Kopi Kapal Api Panas',
            'qty_requested' => 2,
            'urgency' => 'high',
        ]);
    }

    public function test_can_submit_selesai_shift_sales_summary(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.selesai'), [
                'cash_in_drawer' => 450000,
                'handover_notes' => 'Kas fisik laci kasir cocok dengan rekap sistem',
            ]);

        $response->assertRedirect(route('presensi2.rekap'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shift_sales_summaries', [
            'employee_id' => $this->employee->id,
            'cash_in_drawer' => 450000,
            'status' => 'closed',
        ]);

        $summary = ShiftSalesSummary::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertNotNull($summary->stock_audit);
        $this->assertArrayHasKey('kertas', $summary->stock_audit);
        $this->assertArrayHasKey('makanan', $summary->stock_audit);
        $this->assertArrayHasKey('minuman', $summary->stock_audit);
    }

    public function test_stok_page_tracks_stok_sebelumnya_dan_sesudahnya(): void
    {
        $this->clockInStaff();

        // 1. Submit checklist stok awal (sebelumnya)
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'paper_a4' => '1',
                'paper_a4_qty' => 500,
                'paper_f4' => '1',
                'paper_f4_qty' => 500,
                'drink_free_checked' => '1',
                'drink_items' => ['Es Kopi Susu Aren'],
                'drink_stock_initial' => [$this->variantKopi->id => 30],
                'food_free_checked' => '1',
                'food_items' => ['Indomie Goreng Telur'],
                'food_stock_initial' => [10 => 25],
                'hardware_status' => 'normal',
                'hardware_checklist' => ['pc_stations'],
                'billing_verified' => '1',
            ]);

        $response->assertRedirect(route('pos'));

        // 2. View Halaman Penjualan dan cek komparasi stok
        $viewResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.penjualan'));

        $viewResponse->assertStatus(200);
        $stockComparisons = $viewResponse->viewData('stockComparisons');
        $this->assertNotNull($stockComparisons);
        $this->assertArrayHasKey('kertas', $stockComparisons);
        $this->assertArrayHasKey('makanan', $stockComparisons);
        $this->assertArrayHasKey('minuman', $stockComparisons);
    }

    public function test_can_clock_out_from_penjualan_flow(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 7, 5, 0));

        // Clock in dulu
        $dto = new ClockInDTO(
            employeeId: $this->employee->id,
            branchId: $this->branch->id,
            latitude: -6.200000,
            longitude: 106.816666,
            shiftId: $this->shift->id,
            photoPath: null,
            verificationMethod: VerificationMethod::AdminManual,
            deviceInfo: ['user_agent' => 'PHPUnit'],
            ipAddress: '127.0.0.1',
            isMockLocation: false,
        );
        app(ClockInAction::class)->execute($dto);

        // Majukan waktu ke jam pulang
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 15, 0, 0));

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.clock-out'), [
                'shift_id' => $this->shift->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'status' => AttendanceStatus::Present->value,
        ]);

        $this->assertNotNull(AttendanceRecord::first()->clock_out_at);

        Carbon::setTestNow();
    }

    public function test_can_cancel_fnb_order_and_restores_inventory(): void
    {
        // Pastikan inventaris awal kopi ada di warehouse
        StockInventory::updateOrCreate(
            [
                'warehouse_id' => $this->warehouse->id,
                'product_variant_id' => $this->variantKopi->id,
            ],
            [
                'qty_on_hand' => 20,
                'safety_stock' => 5,
            ]
        );

        $initialStock = (int) StockInventory::where('product_variant_id', $this->variantKopi->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty_on_hand');

        $this->clockInStaff();

        // 1. Buat transaksi penjualan F&B (3x Kopi)
        $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'variant_id' => $this->variantKopi->id,
                'qty' => 3,
                'payment_method' => 'cash',
                'customer_name' => 'Fajar PC-01',
            ]);

        $sale = CafeSale::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('paid', $sale->payment_status);

        $stockAfterSale = (int) StockInventory::where('product_variant_id', $this->variantKopi->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty_on_hand');
        $this->assertEquals($initialStock - 3, $stockAfterSale);

        // 2. Batalkan pesanan invoice melalui route presensi2.penjualan.batal (masuk ke admin) dengan otorisasi Supervisor PIN (SoD)
        $cancelResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.batal', $sale), [
                'reason' => 'Pelanggan salah pilih menu kopi',
                'supervisor_pin' => '999888',
            ]);

        $cancelResponse->assertRedirect(route('admin.penjualan', ['q' => $sale->invoice_number]));
        $cancelResponse->assertSessionHas('success');

        $sale->refresh();
        $this->assertEquals('cancelled', $sale->payment_status);
        $this->assertStringContainsString('Pelanggan salah pilih menu kopi', $sale->cashier_notes);

        // 3. Stok kembali utuh seperti sebelum penjualan
        $stockAfterCancel = (int) StockInventory::where('product_variant_id', $this->variantKopi->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('qty_on_hand');
        $this->assertEquals($initialStock, $stockAfterCancel);

        // 4. Halaman penjualan menampilkan status DIBATALKAN dan nomor invoice
        $pageResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.penjualan'));
        $pageResponse->assertSee($sale->invoice_number);
        $pageResponse->assertSee('DIBATALKAN');
    }

    public function test_pos_desktop_can_cancel_fnb_order_via_json_with_supervisor_pin(): void
    {
        $this->clockInStaff();

        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-JSON-VOID',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Pelanggan POS',
            'total_fnb' => 20000,
            'gross_amount' => 20000,
            'net_amount' => 20000,
            'amount_paid' => 20000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'cashier_notes' => 'Kasir POS',
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variantKopi->id,
            'item_name' => 'Kopi Kapal Api',
            'qty' => 2,
            'unit_price' => 10000,
            'subtotal' => 20000,
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.batal', $sale), [
                'supervisor_pin' => '999888',
                'reason' => 'Salah input menu / pesanan',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'payment_status' => 'cancelled',
        ]);

        $sale->refresh();
        $this->assertEquals('cancelled', $sale->payment_status);
        $this->assertStringContainsString('Salah input menu', $sale->cashier_notes);
    }

    public function test_cancelling_already_cancelled_order_is_handled_gracefully(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-CANCEL-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Tester',
            'total_fnb' => 15000,
            'gross_amount' => 15000,
            'net_amount' => 15000,
            'amount_paid' => 15000,
            'payment_method' => 'cash',
            'payment_status' => 'cancelled',
            'cashier_notes' => 'Sudah batal',
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.batal', $sale), [
                'reason' => 'Mencoba batal ulang',
            ]);

        $response->assertRedirect(route('admin.penjualan', ['q' => $sale->invoice_number]));
        $response->assertSessionHas('warning');
    }

    public function test_can_cancel_order_via_get_url_and_redirects_to_admin(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-GET-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'customer_name' => 'Tester GET',
            'total_fnb' => 20000,
            'gross_amount' => 20000,
            'net_amount' => 20000,
            'amount_paid' => 20000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $response = $this->get(route('presensi2.penjualan.batal', [
            'sale' => $sale,
            'supervisor_pin' => '999888',
            'reason' => 'Batal via URL Admin',
        ]));

        $response->assertRedirect(route('admin.penjualan', ['q' => $sale->invoice_number]));
        $response->assertSessionHas('success');

        $sale->refresh();
        $this->assertEquals('cancelled', $sale->payment_status);
    }

    public function test_can_record_printer_job_via_api_and_sync_counter(): void
    {
        $this->clockInStaff();

        $response = $this->postJson(route('api.v1.printer.log-job'), [
            'branch_id' => $this->branch->id,
            'printer_name' => 'Canon G2000 Series',
            'document_name' => 'Laporan_Tugas.pdf',
            'client_computer' => 'PC-KASIR-01',
            'pages' => 4,
            'color_mode' => 'bw',
            'unit_price' => 1000,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'pages' => 4,
            'total_shift_pages' => 4,
            'total_shift_amount' => 4000,
        ]);

        $this->assertDatabaseHas('printer_print_jobs', [
            'printer_name' => 'Canon G2000 Series',
            'pages' => 4,
            'total_amount' => 4000,
        ]);

        // Test sinkron manual di halaman POS
        $syncResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.printer.manual-sync'));

        $syncResponse->assertOk();
        $syncResponse->assertJson([
            'success' => true,
            'pages' => 4,
            'amount' => 4000,
        ]);

        // Test status printer real-time endpoint
        $statusResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->getJson(route('presensi2.penjualan.printer.status'));

        $statusResponse->assertOk();
        $statusResponse->assertJson([
            'success' => true,
            'is_online' => true,
            'total_pages' => 4,
            'hardware_pages' => 4,
            'hardware_amount' => 4000,
            'total_print_pages' => 4,
            'total_print_income' => 4000,
            'is_print_locked' => false,
        ]);
        $statusResponse->assertJsonStructure([
            'success',
            'is_online',
            'printer_name',
            'total_pages',
            'total_amount',
            'hardware_pages',
            'hardware_amount',
            'total_print_pages',
            'total_print_income',
            'is_print_locked',
            'latest_jobs' => [
                '*' => ['id', 'doc', 'pages', 'time', 'amount', 'source'],
            ],
        ]);

        // Test download agent script
        $agentResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.penjualan.printer.agent-script', ['format' => 'bat']));

        $agentResponse->assertOk();
        $this->assertStringContainsString('MGK Canon USB Printer Agent', $agentResponse->getContent());
    }

    public function test_rekap_page_and_closing_sales_synchronizes_canon_usb_printer_jobs(): void
    {
        $this->clockInStaff();

        $workDate = Carbon::today();

        // 1. Simulasikan print job dari Canon USB Spooler
        PrinterPrintJob::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'printer_name' => 'Canon G2000 Series',
            'document_name' => 'Skripsi_Final.docx',
            'client_computer' => 'PC-KASIR-01',
            'pages' => 7,
            'color_mode' => 'bw',
            'unit_price' => 1000,
            'total_amount' => 7000,
            'work_date' => $workDate->toDateString(),
            'printed_at' => Carbon::now(),
        ]);

        // 2. Akses halaman rekap harian
        $rekapResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.rekap'));

        $rekapResponse->assertOk();
        $rekapResponse->assertViewHas('totalPrintPages', 7);
        $rekapResponse->assertViewHas('totalPrintIncome', 7000.0);
        $rekapResponse->assertViewHas('realHardwarePages', 7);
        $rekapResponse->assertViewHas('realHardwareAmount', 7000.0);
        $rekapResponse->assertSee('7 Lembar');
        $rekapResponse->assertSee('Canon USB Live');

        // 3. Simulasikan Penjualan Selesai (closing shift)
        $closeResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.penjualan.selesai'), [
                'cash_in_drawer' => 157000,
                'system_expected_cash' => 157000,
            ]);

        $closeResponse->assertRedirect(route('presensi2.rekap'));

        $this->assertDatabaseHas('shift_sales_summaries', [
            'employee_id' => $this->employee->id,
            'locked_print_pages' => 7,
            'locked_print_amount' => 7000,
            'is_print_locked' => true,
        ]);
    }

    public function test_stok_checklist_records_physical_discrepancy_for_drink_and_food(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 7, 5, 0));

        $this->clockInStaff();

        // Submit form stok dengan input fisik aktual Aqua / Kopi kurang 1 (sistem misal ada stok, fisik diinput lebih sedikit)
        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'billing_verified' => '1',
                'hardware_checklist' => ['pc_stations', 'server_billing'],
                'hardware_status' => 'normal',
                'drink_physical' => [
                    $this->variantKopi->id => 9,
                ],
                'drink_notes' => 'Aqua / Kopi kurang 1 botol dari shift sebelumnya',
                'food_physical' => [
                    10 => 14,
                ],
                'food_notes' => 'Chiki kurang 1 pcs',
            ]);

        $response->assertSessionHas('success');

        $checklist = ShiftHandoverChecklist::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($checklist);
        $this->assertIsArray($checklist->drink_items);

        $kopiItem = collect($checklist->drink_items)->firstWhere('variant_id', $this->variantKopi->id);
        $this->assertNotNull($kopiItem);
        $this->assertEquals(9, $kopiItem['physical_stock']);
        $this->assertArrayHasKey('discrepancy', $kopiItem);
        $this->assertArrayHasKey('status', $kopiItem);

        // Revisit stok page and check rendered inputs
        $viewResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok'));

        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('drink_physical['.$this->variantKopi->id.']', false);
        $viewResponse->assertSee('value="9"', false);
    }

    public function test_stok_simulation_can_be_reset_via_action_reset_stok(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 7, 5, 0));

        $this->clockInStaff();

        // 1. Buat checklist awal
        ShiftHandoverChecklist::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'check_date' => Carbon::now()->toDateString(),
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('shift_handover_checklists', [
            'employee_id' => $this->employee->id,
            'status' => 'completed',
        ]);

        // 2. Panggil action reset_stok
        $resetResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok', ['action' => 'reset_stok']));

        $resetResponse->assertRedirect(route('presensi2.stok'));
        $resetResponse->assertSessionHas('status');

        $this->assertDatabaseMissing('shift_handover_checklists', [
            'employee_id' => $this->employee->id,
            'status' => 'completed',
        ]);
    }

    public function test_stok_page_displays_previous_shift_physical_checklist_and_settlement_progress(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 13, 0, 0));

        // 1. Shift 1 Operator menyelesaikan checklist & settlement
        $shift1 = Shift::firstOrCreate(
            ['code' => 'SHIFT-1'],
            ['name' => 'Shift 1 Pagi', 'start_time' => '07:00:00', 'end_time' => '12:00:00', 'is_active' => true]
        );
        $shift2 = Shift::firstOrCreate(
            ['code' => 'SHIFT-2'],
            ['name' => 'Shift 2 Siang', 'start_time' => '12:00:00', 'end_time' => '17:00:00', 'is_active' => true]
        );

        $employee1 = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Operator Shift 1 Budi',
            'code' => 'EMP-002',
            'position' => 'Operator',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('654321'),
        ]);

        // Simpan checklist fisik dari Shift 1
        ShiftHandoverChecklist::create([
            'employee_id' => $employee1->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift1->id,
            'check_date' => Carbon::today()->toDateString(),
            'paper_a4' => true,
            'paper_a4_qty' => 450,
            'paper_f4' => true,
            'paper_f4_qty' => 480,
            'drink_items' => [
                [
                    'variant_id' => $this->variantKopi->id,
                    'name' => $this->variantKopi->name,
                    'physical_stock' => 9,
                    'sesudahnya' => 10,
                    'discrepancy' => -1,
                    'status' => 'kurang',
                ],
            ],
            'drink_notes' => 'Kopi kurang 1 botol dari shift pagi',
            'hardware_status' => 'warning',
            'hardware_notes' => 'PC-04 headset kiri kresek-kresek',
            'status' => 'completed',
            'completed_at' => Carbon::now()->subHour(),
        ]);

        // Simpan settlement kas dari Shift 1
        ShiftSalesSummary::create([
            'employee_id' => $employee1->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift1->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_internet_sales' => 85000,
            'deposited_amount' => 85000,
            'blind_cash_count' => 85000,
            'cash_difference' => 0,
            'status' => 'closed',
        ]);

        // 2. Staff 2 clock-in pada Shift 2
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok', ['shift' => $shift2->id]));

        $response->assertStatus(200);

        // Verifikasi Widget 4 Shift Settlement
        $response->assertSee('Status Settlement 4 Shift Hari Ini');
        $response->assertSee('Akumulasi omset harian');
        $response->assertSee('Progres:');
        $response->assertSee('Shift 1');
        $response->assertSee('Shift 2');

        // Verifikasi Serah Terima Fisik dari Shift Sebelumnya
        $response->assertSee('Serah Terima Fisik dari');
        $response->assertSee('Kopi kurang 1 botol dari shift pagi');
        $response->assertSee('⏮️ Fisik:');
        $response->assertSee('9');

        // Verifikasi Catatan Hardware dari Shift Sebelumnya di Seksi 4
        $response->assertSee('Catatan Hardware Shift Lalu');
        $response->assertSee('PC-04 headset kiri kresek-kresek');

        // Verifikasi Kartu Ringkasan & Tombol Aksi Serah Terima Stok di Bawah Tabel 1-3
        $response->assertSee('Berita Acara Rekonsiliasi Stok Fisik Antar-Shift');
        $response->assertSee('Konfirmasi Serah Terima Stok');
        $response->assertSee('stat-total-items');
    }

    public function test_admin_dashboard_shows_hardware_status_in_shift_audit_reports(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 14, 0, 0));

        $adminUser = User::create([
            'name' => 'Admin Owner MGK',
            'email' => 'owner@mgk.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        ShiftHandoverChecklist::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'check_date' => Carbon::today()->toDateString(),
            'hardware_status' => 'warning',
            'hardware_notes' => 'PC-04 headset rusak',
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelayakan Hardware');
        $response->assertSee('Ada Kendala');
        $response->assertSee('PC-04 headset rusak');

        $stokResponse = $this->actingAs($adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.stok'));

        $stokResponse->assertStatus(200);
        $stokResponse->assertSee('Kelayakan Hardware');
        $stokResponse->assertSee('Ada Kendala');
    }

    public function test_admin_dashboard_shows_daily_z_report_modal_and_data(): void
    {
        $adminUser = User::create([
            'name' => 'Owner MGK Z-Report',
            'email' => 'owner_z@mgk.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        ShiftSalesSummary::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 50000,
            'total_internet_sales' => 120000,
            'total_revenue' => 170000,
            'cash_in_drawer' => 320000,
            'retained_drawer_cash' => 150000,
            'deposited_amount' => 170000,
            'deposit_status' => 'verified',
            'deposit_method' => 'safe_dropbox',
            'status' => 'closed',
        ]);

        $response = $this->actingAs($adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertSee('Cetak Z-Report Harian (24 Jam)');
        $response->assertSee('BERITA ACARA SETTLEMENT HARIAN (Z-REPORT 24 JAM)');
        $response->assertSee('Kasir Penutup (Shift 4)');
        $response->assertSee('Kasir Penerima (Shift 1 Besok)');
        $response->assertSee('Supervisor / Owner MGK');

        $stokResponse = $this->actingAs($adminUser)
            ->withSession(['admin_role' => 'Manager', 'admin_logged_in' => true])
            ->get(route('admin.stok'));

        $stokResponse->assertStatus(200);
        $stokResponse->assertSee('Cetak Z-Report Harian (24 Jam)');
    }

    public function test_shift_4_serah_terima_shows_eod_settlement_banner_and_retained_cash_preset(): void
    {
        $shift4 = Shift::create([
            'name' => 'Shift 4 (Malam)',
            'code' => 'SHIFT-4',
            'start_time' => '22:00:00',
            'end_time' => '07:00:00',
            'is_active' => true,
        ]);

        // Simulasikan waktu malam shift 4
        Carbon::setTestNow(Carbon::parse('2026-11-07 23:30:00'));

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.serah-terima'));

        $response->assertStatus(200);
        $response->assertSee('SHIFT 4: PENUTUP HARI');
        $response->assertSee('150.000');
        $response->assertSee('Amplop Brankas (Safe Dropbox)');

        Carbon::setTestNow();
    }

    public function test_serah_terima_with_deposit_proof_upload_saves_file_and_updates_summary(): void
    {
        Storage::fake('public');
        $fakeImage = UploadedFile::fake()->image('struk_atm.jpg');

        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 45000,
            'total_internet_sales' => 100000,
            'total_revenue' => 145000,
            'cash_in_drawer' => 295000,
            'status' => 'closed',
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.serah-terima.store'), [
                'summary_id' => $summary->id,
                'deposited_amount' => 145000,
                'retained_drawer_cash' => 150000,
                'deposit_method' => 'safe_dropbox',
                'deposit_recipient' => 'Brankas Pusat',
                'deposit_notes' => 'Amplop sudah di dropbox',
                'deposit_proof' => $fakeImage,
                'action_type' => 'setor',
            ]);

        $response->assertRedirect();

        $summary->refresh();
        $this->assertEquals(145000, (float) $summary->deposited_amount);
        $this->assertEquals(150000, (float) $summary->retained_drawer_cash);
        $this->assertEquals('safe_dropbox', $summary->deposit_method);
        $this->assertStringContainsString('Bukti Setor: uploads/deposits/deposit_', (string) $summary->deposit_notes);
        $this->assertNotNull($summary->depositProofUrl());
    }

    public function test_section_5_cash_handover_expects_only_retained_cash_when_previous_shift_deposited_to_safe(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-08 08:00:00'));

        $shift4 = Shift::create([
            'name' => 'Shift 4 Malam',
            'code' => 'SHIFT-4',
            'start_time' => '22:00:00',
            'end_time' => '07:00:00',
            'is_active' => true,
        ]);

        // Shift 4 kemarin sudah setor Rp 500.000 ke brankas, menyisakan Rp 150.000 di laci
        ShiftSalesSummary::create([
            'employee_id' => $this->supervisor->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift4->id,
            'work_date' => Carbon::now()->subDay()->toDateString(),
            'total_fnb_sales' => 100000,
            'total_internet_sales' => 400000,
            'total_revenue' => 500000,
            'deposited_amount' => 500000,
            'retained_drawer_cash' => 150000,
            'deposit_status' => 'verified',
            'deposit_method' => 'safe_dropbox',
            'status' => 'closed',
        ]);

        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok'));

        $response->assertStatus(200);
        $response->assertSee('Setor ke Brankas/Bank');
        $response->assertSee('Wajib Ada di Laci Sekarang');
        $response->assertSee('value="150000"', false);
    }

    public function test_submitting_stok_checklist_requires_notes_if_drawer_cash_has_discrepancy(): void
    {
        $this->clockInStaff();

        // 1. Submit selisih kas tanpa catatan -> Harus GAGAL
        $responseFail = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'paper_a4' => '1',
                'paper_f4' => '1',
                'hardware_checklist' => ['pc_stations'],
                'hardware_status' => 'normal',
                'billing_verified' => '1',
                'actual_cash_received' => 50000, // Misal ekspektasi default 150.000
                'cash_handover_notes' => '', // Kosong
            ]);

        $responseFail->assertSessionHas('error');

        // 2. Submit dengan catatan kronologi -> Berhasil
        $responseSuccess = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->post(route('presensi2.stok.store'), [
                'paper_a4' => '1',
                'paper_f4' => '1',
                'hardware_checklist' => ['pc_stations'],
                'hardware_status' => 'normal',
                'billing_verified' => '1',
                'actual_cash_received' => 50000,
                'cash_handover_notes' => 'Uang receh di laci kurang Rp 100.000 sudah konfirmasi ke shift lalu',
            ]);

        $responseSuccess->assertRedirect(route('pos'));

        $checklist = ShiftHandoverChecklist::where('employee_id', $this->employee->id)->latest('id')->first();
        $this->assertNotNull($checklist);
        $this->assertEquals(50000, $checklist->billing_details['actual_cash_received']);
        $this->assertEquals('Uang receh di laci kurang Rp 100.000 sudah konfirmasi ke shift lalu', $checklist->billing_details['cash_notes']);
    }

    public function test_stok_page_renders_two_step_wizard_and_supports_step_2_navigation(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok'));

        $response->assertStatus(200);
        $response->assertSee('Tahap 1: Cek Stok Barang', false);
        $response->assertSee('Tahap 2: Konfirmasi Kas', false);
        $response->assertSee('id="step-1-container"', false);
        $response->assertSee('id="step-2-container"', false);
        $response->assertSee('Lanjut ke Tahap 2: Konfirmasi Kas & Serah Terima Laci', false);

        // Pastikan elemen-elemen Tahap 2 ada di dalam HTML
        $response->assertSee('name="actual_cash_received"', false);
        $response->assertSee('name="billing_verified"', false);
        $response->assertSee('name="cash_handover_notes"', false);
        $response->assertSee('id="denom-calculator-panel"', false);
        $response->assertSee('goToStep2(false)', false);
        $response->assertSee('goToStep2(true)', false);

        // Buka langsung via parameter ?step=2
        $responseStep2 = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.stok', ['step' => 2]));

        $responseStep2->assertStatus(200);
        $responseStep2->assertSee('Tahap 2: Konfirmasi Kas', false);
    }

    public function test_pos_desktop_page_renders_simulation_panel_and_presets(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('Mode Simulasi & Pengujian Transaksi POS', false);
        $response->assertSee('1. Beli Aqua (Tunai)', false);
        $response->assertSee('2. Print + Minum (QRIS)', false);
        $response->assertSee('3. Isi Keranjang Uji Coba', false);
        $response->assertSee('Reset Transaksi Testing', false);
        $response->assertSee('runPosSimulation', false);
    }

    public function test_pos_desktop_supports_resetting_dummy_sales_and_restoring_stock(): void
    {
        $this->clockInStaff();

        $stock = StockInventory::firstOrCreate(
            ['warehouse_id' => $this->warehouse->id, 'product_variant_id' => $this->variantKopi->id],
            ['qty_on_hand' => 10, 'safety_stock' => 5]
        );
        $stock->update(['qty_on_hand' => 10]);

        // Simulasikan transaksi penjualan POS
        $checkoutResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'cart_items' => [
                    [
                        'id' => $this->variantKopi->id,
                        'name' => 'Kopi Good Day Panas',
                        'qty' => 2,
                        'price' => 5000,
                    ],
                ],
                'customer_name' => 'Tester Simulasi',
                'payment_method' => 'cash',
                'cash_received' => 10000,
            ]);

        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertJson(['success' => true]);

        // Stok seharusnya berkurang menjadi 8
        $this->assertEquals(8, $stock->fresh()->qty_on_hand);
        $this->assertDatabaseHas('cafe_sales', ['customer_name' => 'Tester Simulasi']);

        // Jalankan Reset Transaksi Testing POS
        $resetResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('pos', ['action' => 'reset_sales']));

        $resetResponse->assertRedirect(route('pos'));
        $resetResponse->assertSessionHas('status');

        // Pastikan stok kembali utuh ke 10 dan data transaksi testing terhapus
        $this->assertEquals(10, $stock->fresh()->qty_on_hand);
        $this->assertDatabaseMissing('cafe_sales', ['customer_name' => 'Tester Simulasi']);
    }

    public function test_pos_desktop_only_offers_cash_and_qris_options(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('<option value="cash">💵 Tunai (Cash)</option>', false);
        $response->assertSee('<option value="qris">📱 QRIS / E-Wallet</option>', false);
        $response->assertDontSee('<option value="debit">', false);
    }

    public function test_serah_terima_calculates_cash_only_excluding_qris_sales(): void
    {
        $this->clockInStaff();

        // 1. Transaksi Tunai Rp 10.000
        $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'cart_items' => [
                    ['id' => $this->variantKopi->id, 'name' => 'Kopi Good Day', 'qty' => 2, 'price' => 5000],
                ],
                'customer_name' => 'Pembeli Tunai',
                'payment_method' => 'cash',
                'cash_received' => 10000,
            ]);

        // 2. Transaksi QRIS Rp 15.000 (Masuk rekening, tidak ada fisik uangnya di laci)
        $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'cart_items' => [
                    ['id' => $this->variantKopi->id, 'name' => 'Kopi Good Day', 'qty' => 3, 'price' => 5000],
                ],
                'customer_name' => 'Pembeli QRIS',
                'payment_method' => 'qris',
                'cash_received' => 15000,
            ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.serah-terima.desktop'));

        $response->assertStatus(200);
        $this->assertEquals(36000, $response->viewData('nonCashAmount'));
        // Modal awal 100.000 + Tunai 24.000 = Target Cash 124.000 (QRIS 36.000 dikecualikan dari fisik kas laci)
        $response->assertSee('Non-Tunai / QRIS (Masuk Rekening)', false);
        $response->assertSee('- Rp 36.000', false);
        $response->assertSee('Target Fisik Kas Tunai (Laci)', false);
        $response->assertSee('Total Uang Fisik di Laci (Cash Only)', false);
    }

    public function test_serah_terima_recipient_automatically_uses_current_logged_in_shift_and_employee_without_select_dropdown(): void
    {
        $this->clockInStaff();

        // 1. Cek tampilan mobile
        $responseMobile = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.serah-terima'));

        $responseMobile->assertStatus(200);
        $expectedRecipient = $this->shift->name.' — '.$this->employee->name;
        $responseMobile->assertSee($expectedRecipient);
        $responseMobile->assertDontSee('<select name="deposit_recipient"', false);
        $responseMobile->assertSee('name="deposit_recipient" id="input-recipient" value="'.$expectedRecipient.'"', false);

        // 2. Cek tampilan desktop
        $responseDesktop = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.serah-terima.desktop'));

        $responseDesktop->assertStatus(200);
        $responseDesktop->assertSee($expectedRecipient);
        $responseDesktop->assertDontSee('<select name="deposit_recipient"', false);
        $responseDesktop->assertSee('name="deposit_recipient" id="input-recipient" value="'.$expectedRecipient.'"', false);
    }

    public function test_serah_terima_desktop_displays_simulation_bar_and_preset_buttons(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.serah-terima.desktop'));

        $response->assertStatus(200);
        $response->assertSee('MODE SIMULASI POS');
        $response->assertSee('Shift 1 (07-15)');
        $response->assertSee('Shift 2 (15-22)');
        $response->assertSee('Shift 3 (17-24)');
        $response->assertSee('Shift 4 (22-07 EOD)');
        $response->assertSee('Kas Pas (Cocok)');
        $response->assertSee('Minus -Rp 10.000');
        $response->assertSee('Lebih +Rp 5.000');
        $response->assertSee('Reset Simulasi');
    }

    public function test_serah_terima_reset_simulation_action_clears_handover_data(): void
    {
        $this->clockInStaff();

        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => now()->toDateString(),
            'total_fnb_sales' => 50000,
            'total_internet_sales' => 100000,
            'total_revenue' => 150000,
            'deposited_amount' => 150000,
            'handover_status' => 'completed',
            'status' => 'closed',
        ]);

        $this->assertEquals('completed', $summary->handover_status);

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('presensi2.serah-terima.desktop', ['action' => 'reset_handover', 'summary_id' => $summary->id]));

        $response->assertRedirect(route('presensi2.serah-terima.desktop', ['summary_id' => $summary->id, 'view' => 'desktop']));

        $summary->refresh();
        $this->assertEquals('open', $summary->status);
        $this->assertEquals('pending', $summary->handover_status);
        $this->assertEquals('unsubmitted', $summary->deposit_status);
    }

    public function test_pos_desktop_order_saves_directly_without_forced_modal_print(): void
    {
        $this->clockInStaff();

        $response = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->get(route('pos'));

        $response->assertStatus(200);
        $response->assertSee('Simpan Order Penjualan');
        $response->assertSee('pos-success-toast');
        $response->assertSee('btn-print-last-receipt');

        // Lakukan submit transaksi POS langsung
        $postResponse = $this->withSession(['presensi2_employee_id' => $this->employee->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'cart_items' => [
                    ['id' => $this->variantKopi->id, 'name' => 'Kopi Good Day', 'qty' => 1, 'price' => 5000],
                ],
                'customer_name' => 'Pelanggan Langsung',
                'payment_method' => 'cash',
                'cash_received' => 10000,
            ]);

        $postResponse->assertStatus(200);
        $postResponse->assertJson(['success' => true]);
        $this->assertDatabaseHas('cafe_sale_items', [
            'item_name' => 'Es Kopi Susu Aren Dingin',
            'qty' => 1,
            'unit_price' => 12000,
        ]);
    }
}
