<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftHandoverChecklist;
use App\Models\ShiftSalesSummary;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Category;
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
        $resPenjualan->assertRedirect(route('presensi2.login'));
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
        $response->assertSee('Lanjut ke Halaman Penjualan');
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

        $response->assertRedirect(route('presensi2.penjualan'));
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

        $response->assertRedirect(route('presensi2.penjualan'));

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
}
