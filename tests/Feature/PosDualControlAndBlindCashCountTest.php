<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftSalesSummary;
use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafePayment;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class PosDualControlAndBlindCashCountTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $cashier;

    private Employee $supervisor;

    private Shift $shift;

    private Warehouse $warehouse;

    private ProductVariant $variantFood;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::firstOrCreate(
            ['id' => 3],
            [
                'name' => 'MGK Cyber Outlet',
                'code' => 'MGK-01',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
                'is_active' => true,
            ]
        );

        $this->cashier = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus Kasir',
            'code' => 'EMP-KSR-01',
            'position' => 'Kasir',
            'role' => EmployeeRole::KASIR,
            'max_discount_approval_percent' => 5.0, // Maksimal diskon mandiri kasir 5%
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $this->supervisor = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Pak Joko Supervisor',
            'code' => 'EMP-SPV-01',
            'position' => 'Supervisor',
            'role' => EmployeeRole::SUPERVISOR,
            'max_discount_approval_percent' => 50.0, // Supervisor berhak diskon s/d 50%
            'password' => Hash::make('spvpass123'),
            'pin' => Hash::make('998877'),
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
            'code' => 'GDG-01',
            'name' => 'Etalase Kasir',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uom = UnitOfMeasurement::create([
            'code' => 'PCS',
            'name' => 'Pcs',
            'symbol' => 'pcs',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'code' => 'CAT-FOOD',
            'name' => 'Makanan Ringan',
            'slug' => 'makanan-ringan',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $cat->id,
            'code' => 'PRD-INDOMIE',
            'name' => 'Indomie Goreng Spesial',
            'is_active' => true,
        ]);

        $this->variantFood = ProductVariant::create([
            'product_id' => $product->id,
            'uom_id' => $uom->id,
            'sku' => 'SKU-MIE-01',
            'name' => 'Indomie Goreng Spesial',
            'base_price' => 10000.00,
            'is_active' => true,
        ]);

        StockInventory::create([
            'product_variant_id' => $this->variantFood->id,
            'warehouse_id' => $this->warehouse->id,
            'qty_on_hand' => 50,
            'qty_reserved' => 0,
        ]);

        // Kasir wajib terverifikasi clock-in sebelum dapat memproses transaksi kasir POS (Aturan SoT Presensi)
        AttendanceRecord::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::now()->subHours(2),
            'status' => AttendanceStatus::Present,
        ]);
    }

    /**
     * Test 1: Blind Cash Count - Kasir memasukkan kas fisik dan sistem menghitung selisih serta WORM AuditLog.
     */
    public function test_blind_cash_count_calculates_discrepancy_and_logs_audit_trail(): void
    {
        // 1. Buat penjualan di sistem senilai Rp 100.000
        $sale = CafeSale::create([
            'invoice_number' => 'INV-TEST-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Pelanggan Umum',
            'total_fnb' => 100000.0,
            'total_billing' => 0.0,
            'gross_amount' => 100000.0,
            'net_amount' => 100000.0,
            'amount_paid' => 100000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // 2. Kasir menutup shift via Blind Cash Count dengan kas fisik Rp 95.000 (selisih kurang Rp 5.000)
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.penjualan.selesai'), [
                'blind_cash_count' => 95000,
                'auto_internet_income' => 0,
                'handover_notes' => 'Fisik kas laci terhitung Rp 95.000 (selisih kurang Rp 5.000 modal receh)',
            ]);

        $response->assertRedirect(route('presensi2.rekap'));
        $response->assertSessionHas('success');

        // 3. Verifikasi Database ShiftSalesSummary
        $summary = ShiftSalesSummary::where('employee_id', $this->cashier->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertEquals(95000.00, (float) $summary->blind_cash_count);
        $this->assertEquals(95000.00, (float) $summary->cash_in_drawer);
        $this->assertEquals(-5000.00, (float) $summary->cash_difference);
        $this->assertEquals('closed', $summary->status);

        // 4. Verifikasi WORM AuditLog untuk selisih kas fisik vs sistem
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_cash_discrepancy',
        ]);
    }

    /**
     * Test 2: Blind Cash Count - Penghitungan pecahan uang otomatis menjumlahkan total kas fisik.
     */
    public function test_blind_cash_count_with_denominations_breakdown(): void
    {
        $denominations = [
            '100k' => 2, // 200.000
            '50k' => 3,  // 150.000
            '20k' => 2,  // 40.000
            '10k' => 1,  // 10.000
            'coins' => 2500, // 2.500
        ]; // Total = 402.500

        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.penjualan.selesai'), [
                'cash_denominations' => json_encode($denominations),
                'handover_notes' => 'Rincian fisik pecahan kas laci dihitung lengkap',
            ]);

        $response->assertRedirect(route('presensi2.rekap'));

        $summary = ShiftSalesSummary::where('employee_id', $this->cashier->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertEquals(402500.00, (float) $summary->blind_cash_count);
        $this->assertIsArray($summary->cash_denominations);
        $this->assertEquals(2, $summary->cash_denominations['100k']);
        $this->assertEquals(3, $summary->cash_denominations['50k']);
    }

    /**
     * Test 3: Dual-Control Void - Kasir biasa dilarang membatalkan nota tanpa PIN Supervisor.
     */
    public function test_cashier_cannot_void_pos_sale_without_supervisor_pin(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-VOID-001',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Budi Santoso',
            'total_fnb' => 20000.0,
            'gross_amount' => 20000.0,
            'net_amount' => 20000.0,
            'amount_paid' => 20000.0,
            'payment_status' => 'paid',
        ]);

        // Coba batalkan tanpa menyertakan PIN Supervisor
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.batal', ['sale' => $sale->id]), [
                'reason' => 'Salah pesan oleh pembeli',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'require_supervisor' => true,
        ]);

        // Status penjualan tetap 'paid' (tidak dibatalkan)
        $this->assertEquals('paid', $sale->fresh()->payment_status);
        $this->assertNull($sale->fresh()->void_by);
    }

    /**
     * Test 4: Dual-Control Void - Ditolak jika PIN yang dimasukkan bukan Supervisor / Otorisator.
     */
    public function test_cashier_cannot_void_with_invalid_or_non_supervisor_pin(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-VOID-002',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'total_fnb' => 20000.0,
            'gross_amount' => 20000.0,
            'net_amount' => 20000.0,
            'payment_status' => 'paid',
        ]);

        // Masukkan PIN milik kasir sendiri (bukan supervisor)
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.batal', ['sale' => $sale->id]), [
                'reason' => 'Salah pesan',
                'supervisor_pin' => '123456', // PIN kasir
            ]);

        $response->assertStatus(403);
        $this->assertEquals('paid', $sale->fresh()->payment_status);
    }

    /**
     * Test 5: Dual-Control Void - Supervisor PIN berhasil membatalkan nota, mengembalikan stok, dan mencatat AuditLog WORM.
     */
    public function test_supervisor_pin_successfully_voids_pos_sale_and_returns_stock(): void
    {
        $sale = CafeSale::create([
            'invoice_number' => 'INV-VOID-003',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'total_fnb' => 20000.0,
            'gross_amount' => 20000.0,
            'net_amount' => 20000.0,
            'payment_status' => 'paid',
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variantFood->id,
            'item_type' => 'fnb',
            'item_name' => $this->variantFood->name,
            'qty' => 2,
            'unit_price' => 10000.0,
            'subtotal' => 20000.0,
        ]);

        CafePayment::create([
            'cafe_sale_id' => $sale->id,
            'payment_number' => 'PAY-VOID-003',
            'payment_method' => 'cash',
            'amount_due' => 20000.0,
            'amount_paid' => 20000.0,
            'status' => 'completed',
        ]);

        // Simulasikan stok sebelum void: 48 (karena 2 terjual)
        StockInventory::where('product_variant_id', $this->variantFood->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->update(['qty_on_hand' => 48]);

        // Eksekusi Void dengan PIN Supervisor yang valid ('998877')
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.batal', ['sale' => $sale->id]), [
                'reason' => 'Pelanggan membatalkan pesanan karena buru-buru',
                'supervisor_pin' => '998877',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_status' => 'cancelled',
        ]);

        // 1. Verifikasi Status Penjualan & Kolom Audit Dual-Control
        $freshSale = $sale->fresh();
        $this->assertEquals('cancelled', $freshSale->payment_status);
        $this->assertEquals($this->supervisor->id, $freshSale->void_by);
        $this->assertEquals('Pelanggan membatalkan pesanan karena buru-buru', $freshSale->void_reason);
        $this->assertNotNull($freshSale->void_at);

        // 2. Verifikasi Pengembalian Stok ke Inventaris (48 + 2 = 50)
        $stock = StockInventory::where('product_variant_id', $this->variantFood->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(50, $stock->qty_on_hand);

        // 3. Verifikasi WORM AuditLog ISO 27001
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => CafeSale::class,
            'auditable_id' => $sale->id,
            'action' => 'pos_sale_void',
        ]);
    }

    /**
     * Test 6: Dual-Control Diskon - Kasir dapat memberikan diskon mandiri jika dalam batas ambang (<= 5%).
     */
    public function test_cashier_can_apply_discount_within_threshold_without_supervisor_pin(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'cart_items' => [
                    ['id' => $this->variantFood->id, 'qty' => 1],
                ], // Gross = Rp 10.000
                'discount_percent' => 5, // 5% (sama dengan batas mandiri kasir 5%)
                'customer_name' => 'Member Reguler',
                'payment_method' => 'cash',
                'amount_paid' => 9500, // 10.000 - 500 = 9.500
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $sale = CafeSale::where('employee_id', $this->cashier->id)->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(10000.0, $sale->gross_amount);
        $this->assertEquals(5.0, $sale->discount_percent);
        $this->assertEquals(500.0, $sale->discount_amount);
        $this->assertEquals(9500.0, $sale->net_amount);
        $this->assertEquals($this->cashier->id, $sale->discount_authorized_by); // Kasir self-authorized
    }

    /**
     * Test 7: Dual-Control Diskon - Ditolak jika kasir memberi diskon melebihi ambang batas (> 5%) tanpa PIN Supervisor.
     */
    public function test_cashier_cannot_apply_discount_exceeding_threshold_without_supervisor_pin(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'cart_items' => [
                    ['id' => $this->variantFood->id, 'qty' => 1],
                ],
                'discount_percent' => 15, // 15% > batas mandiri kasir 5%
                'payment_method' => 'cash',
                'amount_paid' => 8500,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'require_supervisor' => true,
        ]);

        $this->assertDatabaseMissing('cafe_sales', [
            'employee_id' => $this->cashier->id,
            'discount_percent' => 15,
        ]);
    }

    /**
     * Test 8: Dual-Control Diskon - Diskon di atas ambang batas berhasil dengan otorisasi PIN Supervisor & AuditLog.
     */
    public function test_supervisor_pin_successfully_authorizes_exceeded_discount_with_audit_log(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'cart_items' => [
                    ['id' => $this->variantFood->id, 'qty' => 2],
                ], // Gross = Rp 20.000
                'discount_percent' => 20, // 20% diskon
                'supervisor_pin' => '998877', // PIN Supervisor valid
                'payment_method' => 'cash',
                'amount_paid' => 16000, // 20.000 - 4.000 = 16.000
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $sale = CafeSale::where('employee_id', $this->cashier->id)->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(20000.0, $sale->gross_amount);
        $this->assertEquals(20.0, $sale->discount_percent);
        $this->assertEquals(4000.0, $sale->discount_amount);
        $this->assertEquals(16000.0, $sale->net_amount);
        $this->assertEquals($this->supervisor->id, $sale->discount_authorized_by); // Supervisor authorized

        // Verifikasi WORM AuditLog ISO 27001
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => CafeSale::class,
            'auditable_id' => $sale->id,
            'action' => 'pos_sale_discount_authorized',
        ]);
    }

    /**
     * Test 9: Isolasi Data Transaksi per Cabang (Branch Isolation) pada Halaman Penjualan, Tutup Shift, dan Rekap.
     * Semua kasir di cabang yang sama melihat & menggabungkan transaksi cabang tersebut pada hari yang sama,
     * namun terpisah sepenuhnya dari transaksi cabang lain.
     */
    public function test_branch_isolation_combines_same_branch_sales_and_excludes_other_branch_sales(): void
    {
        // Buat Cabang Kedua (MGK Cabang 2) dan Kasir Cabang 2
        $otherBranch = Branch::create([
            'name' => 'MGK Cabang Kedua',
            'code' => 'MGK-02',
            'latitude' => -6.210000,
            'longitude' => 106.820000,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $sameBranchSecondCashier = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Rina Kasir Cabang 1',
            'code' => 'EMP-KSR-02',
            'position' => 'Kasir',
            'role' => EmployeeRole::KASIR,
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $otherBranchCashier = Employee::create([
            'branch_id' => $otherBranch->id,
            'name' => 'Doni Kasir Cabang 2',
            'code' => 'EMP-KSR-03',
            'position' => 'Kasir',
            'role' => EmployeeRole::KASIR,
            'password' => Hash::make('password123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        // 1. Transaksi Kasir 1 di Cabang 1: Rp 30.000
        CafeSale::create([
            'invoice_number' => 'INV-CBG1-A',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Pelanggan Cabang 1A',
            'total_fnb' => 30000.0,
            'total_billing' => 0.0,
            'gross_amount' => 30000.0,
            'net_amount' => 30000.0,
            'amount_paid' => 30000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // 2. Transaksi Kasir 2 di Cabang 1 (Cabang yang sama): Rp 45.000
        CafeSale::create([
            'invoice_number' => 'INV-CBG1-B',
            'branch_id' => $this->branch->id,
            'employee_id' => $sameBranchSecondCashier->id,
            'customer_name' => 'Pelanggan Cabang 1B',
            'total_fnb' => 45000.0,
            'total_billing' => 0.0,
            'gross_amount' => 45000.0,
            'net_amount' => 45000.0,
            'amount_paid' => 45000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // 3. Transaksi Kasir 3 di Cabang 2 (Cabang berbeda): Rp 200.000
        CafeSale::create([
            'invoice_number' => 'INV-CBG2-X',
            'branch_id' => $otherBranch->id,
            'employee_id' => $otherBranchCashier->id,
            'customer_name' => 'Pelanggan Cabang 2X',
            'total_fnb' => 200000.0,
            'total_billing' => 0.0,
            'gross_amount' => 200000.0,
            'net_amount' => 200000.0,
            'amount_paid' => 200000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // Buka halaman penjualan sebagai Kasir Cabang 1
        $pageResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.penjualan'));

        $pageResponse->assertOk();
        // Harus menampilkan kedua invoice Cabang 1 (30.000 + 45.000 = 75.000) dan TIDAK menampilkan invoice Cabang 2
        $pageResponse->assertSee('INV-CBG1-A');
        $pageResponse->assertSee('INV-CBG1-B');
        $pageResponse->assertDontSee('INV-CBG2-X');
        $this->assertEquals(75000.0, (float) $pageResponse->viewData('totalFnbIncome'));

        // Tutup shift pada Cabang 1 -> total_fnb_sales harus Rp 75.000 (bukan Rp 275.000)
        $closeResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.penjualan.selesai'), [
                'blind_cash_count' => 75000,
                'auto_internet_income' => 0,
            ]);

        $closeResponse->assertRedirect(route('presensi2.rekap'));

        $summary = ShiftSalesSummary::where('branch_id', $this->branch->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertEquals(75000.0, (float) $summary->total_fnb_sales);
        $this->assertEquals(0.0, (float) $summary->cash_difference);
    }

    /**
     * Test 10: Pemisahan Pembayaran Tunai (Cash) vs Non-Tunai (QRIS) & Potongan Diskon saat Tutup Shift (Blind Cash Count).
     * Uang fisik di laci (system_expected_cash) hanya mengharapkan uang tunai bersih (setelah diskon) dan tidak membebankan transaksi QRIS ke laci kasir.
     */
    public function test_shift_close_reconciles_net_cash_excluding_qris_and_discounts(): void
    {
        // 1. Transaksi Tunai dengan Diskon 10% (Gross Rp 50.000, Diskon Rp 5.000, Net Tunai Rp 45.000)
        CafeSale::create([
            'invoice_number' => 'INV-CASH-DISC-01',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Pelanggan Tunai Diskon',
            'total_fnb' => 50000.0,
            'total_billing' => 0.0,
            'gross_amount' => 50000.0,
            'discount_percent' => 10.0,
            'discount_amount' => 5000.0,
            'discount_authorized_by' => $this->supervisor->id,
            'net_amount' => 45000.0,
            'amount_paid' => 45000.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // 2. Transaksi Non-Tunai QRIS (Gross Rp 30.000, Net QRIS Rp 30.000 -> Masuk rekening bank, bukan laci kasir)
        CafeSale::create([
            'invoice_number' => 'INV-QRIS-01',
            'branch_id' => $this->branch->id,
            'employee_id' => $this->cashier->id,
            'customer_name' => 'Pelanggan QRIS',
            'total_fnb' => 30000.0,
            'total_billing' => 0.0,
            'gross_amount' => 30000.0,
            'discount_percent' => 0.0,
            'discount_amount' => 0.0,
            'net_amount' => 30000.0,
            'amount_paid' => 30000.0,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);

        // Total Omset F&B Bersih = 45.000 + 30.000 = 75.000
        // Non-Tunai (QRIS) = 30.000
        // Target Kas Tunai di Laci (system_expected_cash) = 45.000 (dengan auto_internet_income = 0)
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.penjualan.selesai'), [
                'blind_cash_count' => 45000,
                'auto_internet_income' => 0,
                'handover_notes' => 'Uang tunai di laci Rp 45.000 pas, QRIS Rp 30.000 masuk rekening.',
            ]);

        $response->assertRedirect(route('presensi2.rekap'));

        $summary = ShiftSalesSummary::where('branch_id', $this->branch->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertEquals(75000.0, (float) $summary->total_fnb_sales);
        $this->assertEquals(75000.0, (float) $summary->total_revenue);
        $this->assertEquals(30000.0, (float) $summary->non_cash_amount);
        $this->assertEquals(45000.0, (float) $summary->system_expected_cash);
        $this->assertEquals(45000.0, (float) $summary->blind_cash_count);
        $this->assertEquals(0.0, (float) $summary->cash_difference);

        // Verifikasi tampilan halaman Rekap menampilkan kartu ber-tab (Rincian Item F&B, Daftar Nota, Audit Stok, Riwayat Shift)
        $rekapPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.rekap'));

        $rekapPage->assertOk();
        $rekapPage->assertSee('1. Rincian Item F&B', false);
        $rekapPage->assertSee('2. Daftar Nota', false);
        $rekapPage->assertSee('3. Audit Stok Barang', false);
        $rekapPage->assertSee('4. Riwayat Shift', false);
        $rekapPage->assertSee('INV-CASH-DISC-01');
        $rekapPage->assertSee('INV-QRIS-01');
        $rekapPage->assertSee('QRIS');
        $rekapPage->assertSee('TUNAI');
        $rekapPage->assertSee('Check Out Presensi Pulang (1-Klik)');
        $rekapPage->assertSee('print-table-report', false);
        $rekapPage->assertSee('print-scale-select', false);
        $rekapPage->assertSee('print-paper-mode', false);
        $rekapPage->assertSee('Cetak Tabel Rekap', false);
    }

    /**
     * Test 11: Modul Admin Penjualan menampilkan Audit Selisih Kas (Blind Cash Count) & dapat menyetujui Permintaan Stok Kasir Shift.
     */
    public function test_admin_can_view_shift_cash_audit_and_approve_stock_request(): void
    {
        $stockRequest = ShiftStockRequest::create([
            'request_number' => 'REQ-STK-TEST-01',
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'item_category' => 'kertas',
            'item_name' => 'Kertas HVS A4 70gsm',
            'qty_requested' => 4,
            'unit' => 'Rim',
            'urgency' => 'high',
            'notes' => 'Stok kertas A4 tinggal setengah rim',
            'status' => 'pending',
        ]);

        ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => now()->toDateString(),
            'total_fnb_sales' => 120000,
            'total_internet_sales' => 80000,
            'total_revenue' => 200000,
            'non_cash_amount' => 50000,
            'system_expected_cash' => 150000,
            'blind_cash_count' => 150000,
            'cash_in_drawer' => 150000,
            'cash_difference' => 0,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // 1. Buka halaman Admin Penjualan
        $adminResponse = $this->get(route('admin.penjualan'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Audit Selisih Kas & Rekap Tutup Shift (Blind Cash Count)', false);
        $adminResponse->assertSee('Persetujuan Permintaan Stok Kasir Shift', false);
        $adminResponse->assertSee('REQ-STK-TEST-01');

        // 2. Admin menyetujui permintaan stok kasir
        $approveResponse = $this->postJson(route('admin.penjualan.stock-request.status', $stockRequest->id), [
            'status' => 'fulfilled',
            'notes' => 'Stok 4 Rim A4 dikirim dari gudang pusat',
        ]);

        $approveResponse->assertOk();
        $approveResponse->assertJson(['success' => true]);

        $this->assertEquals('fulfilled', $stockRequest->fresh()->status);
        $this->assertNotNull($stockRequest->fresh()->fulfilled_at);
    }

    /**
     * Test 12: Kasir dapat melakukan Setor Kas Shift (Lanjutan Rekap), melihat History Setor di Beranda & Rekap, serta Admin dapat memverifikasi setoran.
     */
    public function test_cashier_can_submit_shift_deposit_and_view_deposit_history_in_presensi2_and_rekap(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => now()->toDateString(),
            'total_fnb_sales' => 100000,
            'total_internet_sales' => 150000,
            'total_revenue' => 250000,
            'non_cash_amount' => 50000,
            'system_expected_cash' => 200000,
            'blind_cash_count' => 200000,
            'cash_in_drawer' => 200000,
            'cash_difference' => 0,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // 1. Kasir submit Setor Kas dari halaman Rekap (otomatis selesai / disetor ke shift selanjutnya tanpa menunggu verifikasi admin)
        $depositResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.rekap.setor'), [
                'summary_id' => $summary->id,
                'deposited_amount' => 180000,
                'retained_drawer_cash' => 20000,
                'deposit_method' => 'next_shift_handover',
                'deposit_recipient' => 'Shift 2 (Grace)',
                'deposit_notes' => 'Setor uang tunai Rp 180.000 otomatis ke shift selanjutnya, sisa Rp 20.000 receh di laci',
            ]);

        $depositResponse->assertRedirect(route('presensi2.rekap'));

        $summary->refresh();
        $this->assertEquals('verified', $summary->deposit_status);
        $this->assertEquals(180000.0, (float) $summary->deposited_amount);
        $this->assertEquals(20000.0, (float) $summary->retained_drawer_cash);
        $this->assertEquals('Shift 2 (Grace)', $summary->deposit_recipient);
        $this->assertNotNull($summary->deposit_verified_at);

        // 2. Beranda presensi2/ bersih dari tabel History Setor Kas (fokus ke presensi murni)
        $indexPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.index'));

        $indexPage->assertOk();
        $indexPage->assertDontSee('Status & History Setor Kas', false);
        $indexPage->assertDontSee('Lanjutan Rekapitulasi Shift', false);

        // 3. Halaman rekap mengarahkan ke Serah Terima, dan Serah Terima menampilkan status handover kasir
        $rekapPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.rekap'));

        $rekapPage->assertOk();
        $rekapPage->assertSee('Buka Halaman Serah Terima Shift');

        $serahTerimaPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.serah-terima'));

        $serahTerimaPage->assertOk();
        $serahTerimaPage->assertSee('Shift 2 (Grace)');
    }

    public function test_rekap_displays_two_separate_tables_for_handover_and_deposit_and_direct_handover_flow(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => now()->toDateString(),
            'total_fnb_sales' => 150000,
            'total_internet_sales' => 100000,
            'total_revenue' => 250000,
            'cash_in_drawer' => 250000,
            'system_expected_cash' => 250000,
            'blind_cash_count' => 250000,
            'cash_difference' => 0,
            'status' => 'closed',
            'closed_at' => now(),
            'deposit_status' => 'unsubmitted',
        ]);

        // 1. Simpan Serah Terima Shift (langsung ke serah terima, jangan langsung ke setor)
        $handoverResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.rekap.setor'), [
                'summary_id' => $summary->id,
                'action_type' => 'handover',
                'deposited_amount' => 200000,
                'retained_drawer_cash' => 50000,
                'deposit_recipient' => 'Shift 2 (Grace)',
                'deposit_notes' => 'Kas fisik diserahterimakan langsung ke shift 2, modal kembalian Rp 50.000 ditinggal',
            ]);

        $handoverResponse->assertRedirect(route('presensi2.rekap'));

        $summary->refresh();
        // Langsung ke serah terima:
        $this->assertEquals('completed', $summary->handover_status);
        $this->assertEquals(200000.0, (float) $summary->handover_amount);
        $this->assertEquals(50000.0, (float) $summary->retained_drawer_cash);
        $this->assertEquals('Shift 2 (Grace)', $summary->handover_recipient);
        $this->assertNotNull($summary->handover_at);
        // Jangan langsung ke setor:
        $this->assertEquals('unsubmitted', $summary->deposit_status);
        $this->assertNull($summary->deposited_amount);

        // 2. Cek bahwa di halaman rekap ada tombol arahan ke Serah Terima,
        // dan Tabel Serah Terima Shift serta Tabel History Setor berada di halaman serah-terima
        $rekapResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.rekap'));

        $rekapResponse->assertOk();
        $rekapResponse->assertSee('Buka Halaman Serah Terima Shift');

        $serahTerimaResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.serah-terima'));

        $serahTerimaResponse->assertOk();
        $serahTerimaResponse->assertSee('Serah Terima Shift');
        $serahTerimaResponse->assertSee('Siap Setor');
        $serahTerimaResponse->assertSee('History setor');
        $serahTerimaResponse->assertSee('Total Nilai Rekap / Siap Setor');

        // 3. Sekarang coba aksi Setor Kas eksplisit (misal setor ke bank)
        $setorResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.rekap.setor'), [
                'summary_id' => $summary->id,
                'action_type' => 'setor',
                'deposit_method' => 'bank_transfer',
                'deposited_amount' => 200000,
                'deposit_recipient' => 'BCA MayaNet 12345678',
                'deposit_notes' => 'Transfer kas laci shift 1 ke rekening owner',
            ]);

        $setorResponse->assertRedirect(route('presensi2.rekap'));

        $summary->refresh();
        $this->assertEquals('verified', $summary->deposit_status);
        $this->assertEquals(200000.0, (float) $summary->deposited_amount);
        $this->assertEquals('bank_transfer', $summary->deposit_method);
        $this->assertEquals('BCA MayaNet 12345678', $summary->deposit_recipient);
        $this->assertNotNull($summary->deposited_at);
    }

    /**
     * Test 14: Halaman Serah Terima Khusus Setelah Rekap (/presensi2/serah-terima).
     * Memastikan alur serah terima shift mandiri, verifikasi stok fisik, dan opsi auto clock-out pulang.
     */
    public function test_dedicated_handover_page_and_store_workflow(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift->id,
            'work_date' => Carbon::today()->toDateString(),
            'status' => 'closed',
            'opening_cash' => 50000,
            'total_cash_sales' => 150000,
            'total_cash_expected' => 200000,
            'actual_cash_drawer' => 200000,
            'variance_amount' => 0,
            'deposit_status' => 'unsubmitted',
            'handover_status' => 'completed',
            'handover_amount' => 150000,
            'retained_drawer_cash' => 50000,
            'handover_recipient' => 'Shift 2 (Maya Kasir)',
            'handover_at' => Carbon::now(),
        ]);

        // 1. GET /presensi2/serah-terima
        $getResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.serah-terima', ['summary_id' => $summary->id]));

        $getResponse->assertOk();
        $getResponse->assertSee('Serah Terima Shift');
        $getResponse->assertSee('Siap Setor');
        $getResponse->assertSee('History setor');
        $getResponse->assertSee('Total Nilai Rekap');
        $getResponse->assertSee('Print Hardware');

        // 2. POST /presensi2/serah-terima (Simpan form serah terima dengan auto check-out pulang)
        $postResponse = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->post(route('presensi2.serah-terima.store'), [
                'summary_id' => $summary->id,
                'handover_amount' => 160000,
                'retained_drawer_cash' => 40000,
                'handover_recipient' => 'Shift 2 - Budi Santoso',
                'handover_notes' => 'Serah terima uang laci & audit stok minuman selesai aman',
                'auto_clock_out' => '1',
            ]);

        $postResponse->assertRedirect(route('presensi2.index'));

        $summary->refresh();
        $this->assertEquals('completed', $summary->handover_status);
        $this->assertEquals(160000.0, (float) $summary->handover_amount);
        $this->assertEquals(40000.0, (float) $summary->retained_drawer_cash);
        $this->assertEquals('Shift 2 - Budi Santoso', $summary->handover_recipient);
        $this->assertEquals('unsubmitted', $summary->deposit_status); // Tetap belum setor kas ke bank

        // Cek bahwa presensi kasir di-clock-out otomatis
        $attendance = AttendanceRecord::where('employee_id', $this->cashier->id)
            ->whereDate('work_date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_out_at);
    }

    /**
     * Test 15: Penyesuaian Billing Internet Shift - Dual Control Anti-Fraud.
     * Jika diajukan kasir tanpa PIN supervisor, status menjadi pending_approval dan saldo kas BELUM dipotong.
     */
    public function test_cashier_submitting_billing_adjustment_without_pin_is_pending_approval(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-billing'), [
                'raw_billing_amount' => 350000,
                'billing_adjustment_amount' => 50000,
                'billing_adjustment_reason' => 'blackout',
                'billing_adjustment_notes' => 'Pemadaman listrik PLN mendadak pkl 14:15 - 14:45 WIB (30 menit). 12 PC pelanggan mati serentak.',
                'billing_blackout_minutes' => 30,
                'billing_blackout_pcs' => 12,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_approved' => false,
            'status' => 'pending_approval',
            'net_billing_amount' => 350000, // Anti-maling: Saldo kasir belum dipotong sampai disetujui Admin!
            'billing_adjustment_amount' => 50000,
            'raw_billing_amount' => 350000,
        ]);

        $summary = ShiftSalesSummary::where('employee_id', $this->cashier->id)
            ->whereDate('work_date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($summary);
        $this->assertTrue($summary->is_billing_locked);
        $this->assertEquals('pending_approval', $summary->billing_adjustment_status);
        $this->assertNull($summary->billing_adjustment_approved_by);
        $this->assertEquals(350000.0, (float) $summary->raw_billing_amount);
        $this->assertEquals(50000.0, (float) $summary->billing_adjustment_amount);
        // Billing terkunci pada raw amount (350.000) untuk mencegah kasir mengambil selisih uang
        $this->assertEquals(350000.0, (float) $summary->locked_billing_amount);
        $this->assertEquals(350000.0, (float) $summary->total_internet_sales);

        // Pastikan Audit Log ISO 27001 tercatat
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_billing_adjustment_requested',
        ]);

        // Verifikasi di Halaman POS menampilkan badge 'MENUNGGU PERSETUJUAN'
        $posPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.penjualan'));

        $posPage->assertOk();
        $posPage->assertSee('MENUNGGU PERSETUJUAN');
        $posPage->assertSee('Pemadaman Listrik PLN');
        $posPage->assertSee('30 mnt');

        // Kasir TIDAK boleh melihat tombol persetujuan di Rekap (hanya status pengajuan)
        $rekapPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.rekap'));

        $rekapPage->assertOk();
        $rekapPage->assertDontSee('Setujui Pemotongan');

        // Supervisor melihat banner persetujuan di Rekap
        $supervisorRekap = $this->withSession(['presensi2_employee_id' => $this->supervisor->id])
            ->get(route('presensi2.rekap', ['summary_id' => $summary->id]));

        $supervisorRekap->assertOk();
        $supervisorRekap->assertSee('PERSETUJUAN ADMIN');
        $supervisorRekap->assertSee('Setujui Pemotongan');
    }

    /**
     * Test 16: Admin / Supervisor Menyetujui (Approve) Pengajuan Penyesuaian Billing Mati Listrik.
     */
    public function test_admin_can_approve_billing_adjustment_and_deduct_sales(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 100000,
            'total_internet_sales' => 350000,
            'raw_billing_amount' => 350000,
            'billing_adjustment_amount' => 50000,
            'billing_adjustment_reason' => 'blackout',
            'billing_adjustment_notes' => 'Listrik padam 30 menit',
            'billing_blackout_minutes' => 30,
            'billing_blackout_pcs' => 10,
            'billing_adjustment_status' => 'pending_approval',
            'is_billing_locked' => true,
            'locked_billing_amount' => 350000,
            'status' => 'open',
        ]);

        // Supervisor menyetujui pengajuan
        $response = $this->withSession(['presensi2_employee_id' => $this->supervisor->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-billing.approve', ['summary' => $summary->id]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $summary->refresh();
        $this->assertEquals('approved', $summary->billing_adjustment_status);
        $this->assertEquals($this->supervisor->name, $summary->billing_adjustment_approved_by);
        $this->assertNotNull($summary->billing_adjustment_approved_at);
        // Setelah disetujui: omset resmi terpotong menjadi 300.000
        $this->assertEquals(300000.0, (float) $summary->locked_billing_amount);
        $this->assertEquals(300000.0, (float) $summary->total_internet_sales);
        $this->assertEquals(400000.0, (float) $summary->total_revenue); // 100k F&B + 300k Billing

        // Audit Log WORM tercatat
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_billing_adjustment_approved_by_admin',
        ]);
    }

    /**
     * Test 17: Admin Menolak (Reject) Penyesuaian Billing Jika Diduga Manipulasi Kas.
     */
    public function test_admin_can_reject_billing_adjustment_demanding_full_sales(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 100000,
            'total_internet_sales' => 350000,
            'raw_billing_amount' => 350000,
            'billing_adjustment_amount' => 100000,
            'billing_adjustment_reason' => 'blackout',
            'billing_adjustment_notes' => 'Klaim mati listrik tapi genset menyala',
            'billing_adjustment_status' => 'pending_approval',
            'is_billing_locked' => true,
            'locked_billing_amount' => 350000,
            'status' => 'open',
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->supervisor->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-billing.reject', ['summary' => $summary->id]), [
                'rejection_reason' => 'Genset otomatis aktif saat padam listrik, tidak ada PC yang mati.',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $summary->refresh();
        $this->assertEquals('rejected', $summary->billing_adjustment_status);
        $this->assertEquals($this->supervisor->name, $summary->billing_adjustment_rejected_by);
        $this->assertEquals('Genset otomatis aktif saat padam listrik, tidak ada PC yang mati.', $summary->billing_adjustment_rejection_reason);
        // Omset tetap penuh 350.000 (kasir wajib setor penuh tanpa potongan)
        $this->assertEquals(350000.0, (float) $summary->locked_billing_amount);
        $this->assertEquals(350000.0, (float) $summary->total_internet_sales);
    }

    /**
     * Test 18: Kasir Mengajukan Penyesuaian dengan PIN Supervisor Valid Langsung Disetujui (Instan).
     */
    public function test_cashier_with_valid_supervisor_pin_is_instantly_approved(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-billing'), [
                'raw_billing_amount' => 350000,
                'billing_adjustment_amount' => 50000,
                'billing_adjustment_reason' => 'blackout',
                'billing_adjustment_notes' => 'Disetujui langsung oleh Supervisor di outlet.',
                'billing_blackout_minutes' => 30,
                'billing_blackout_pcs' => 12,
                'supervisor_pin' => '998877', // PIN Supervisor valid
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_approved' => true,
            'status' => 'approved',
            'net_billing_amount' => 300000,
        ]);

        $summary = ShiftSalesSummary::where('employee_id', $this->cashier->id)
            ->whereDate('work_date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($summary);
        $this->assertEquals('approved', $summary->billing_adjustment_status);
        $this->assertEquals($this->supervisor->name, $summary->billing_adjustment_approved_by);
        $this->assertEquals(300000.0, (float) $summary->locked_billing_amount);
    }

    /**
     * Test 19: Kasir biasa tidak boleh menyetujui pengajuan penyesuaian billing miliknya sendiri.
     */
    public function test_cashier_cannot_approve_own_billing_adjustment(): void
    {
        $summary = $this->makePendingBillingAdjustment();

        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-billing.approve', ['summary' => $summary->id]));

        $response->assertStatus(403);
        $this->assertEquals('pending_approval', $summary->fresh()->billing_adjustment_status);
        $this->assertEquals(350000.0, (float) $summary->fresh()->locked_billing_amount);
    }

    /**
     * Test 20: Halaman admin menampilkan antrean pengajuan penyesuaian billing.
     */
    public function test_admin_page_lists_pending_billing_adjustments(): void
    {
        $this->makePendingBillingAdjustment();

        $response = $this->get(route('admin.penjualan.penyesuaian-billing'));

        $response->assertOk();
        $response->assertSee('Persetujuan Penyesuaian Billing Kasir');
        $response->assertSee($this->cashier->name);
        $response->assertSee('Klaim mati listrik');
        $response->assertSee('-Rp 50.000');
    }

    /**
     * Test 21: Admin menyetujui pengajuan dari halaman admin.
     */
    public function test_admin_page_can_approve_billing_adjustment(): void
    {
        $summary = $this->makePendingBillingAdjustment();

        $response = $this->post(route('admin.penjualan.penyesuaian-billing.approve', ['summary' => $summary->id]));

        $response->assertRedirect(route('admin.penjualan.penyesuaian-billing'));

        $summary->refresh();
        $this->assertEquals('approved', $summary->billing_adjustment_status);
        $this->assertEquals(300000.0, (float) $summary->locked_billing_amount);
        $this->assertEquals(300000.0, (float) $summary->total_internet_sales);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $summary->id,
            'action' => 'shift_billing_adjustment_approved_by_admin',
        ]);

        // Pengajuan yang sudah diproses tidak bisa diproses ulang
        $again = $this->postJson(route('admin.penjualan.penyesuaian-billing.reject', ['summary' => $summary->id]), [
            'rejection_reason' => 'Terlambat',
        ]);
        $again->assertStatus(422);
        $this->assertEquals('approved', $summary->fresh()->billing_adjustment_status);
    }

    /**
     * Test 22: Admin menolak pengajuan wajib dengan alasan, omset kembali penuh.
     */
    public function test_admin_page_can_reject_billing_adjustment_with_reason(): void
    {
        $summary = $this->makePendingBillingAdjustment();

        $this->post(route('admin.penjualan.penyesuaian-billing.reject', ['summary' => $summary->id]), [])
            ->assertSessionHasErrors('rejection_reason');
        $this->assertEquals('pending_approval', $summary->fresh()->billing_adjustment_status);

        $this->post(route('admin.penjualan.penyesuaian-billing.reject', ['summary' => $summary->id]), [
            'rejection_reason' => 'Genset menyala, PC tidak mati.',
        ])->assertRedirect(route('admin.penjualan.penyesuaian-billing'));

        $summary->refresh();
        $this->assertEquals('rejected', $summary->billing_adjustment_status);
        $this->assertEquals('Genset menyala, PC tidak mati.', $summary->billing_adjustment_rejection_reason);
        $this->assertEquals(350000.0, (float) $summary->locked_billing_amount);
        $this->assertEquals(350000.0, (float) $summary->total_internet_sales);
    }

    public function test_cashier_cannot_manually_lock_or_alter_print_counter(): void
    {
        // Kasir biasa mencoba mengirimkan override angka print manual
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.kunci-print'), [
                'action' => 'lock',
                'locked_print_pages' => 0,
                'locked_print_amount' => 0,
            ]);

        $response->assertStatus(403);
    }

    public function test_cashier_submitting_print_adjustment_without_pin_is_pending_approval(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-print'), [
                'raw_print_pages' => 20,
                'raw_print_amount' => 20000,
                'print_adjustment_pages' => 5,
                'print_adjustment_amount' => 5000,
                'print_adjustment_reason' => 'paper_jam',
                'print_adjustment_notes' => 'Kertas tersangkut di roll printer 5 lembar.',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'pending_approval']);

        $summary = ShiftSalesSummary::where('employee_id', $this->cashier->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertEquals('pending_approval', $summary->print_adjustment_status);
        $this->assertEquals(5, $summary->print_adjustment_pages);
        $this->assertEquals(5000.0, (float) $summary->print_adjustment_amount);
        // Anti-Fraud: omset target kasir BELUM dipotong saat pending
        $this->assertEquals(20000.0, (float) $summary->locked_print_amount);
        $this->assertEquals(20, $summary->locked_print_pages);

        // Halaman POS menampilkan badge status menunggu persetujuan
        $posPage = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->get(route('presensi2.penjualan'));
        $posPage->assertOk();
        $posPage->assertSee('MENUNGGU PERSETUJUAN');
        $posPage->assertSee('Kertas Macet');
    }

    public function test_supervisor_approves_print_adjustment_successfully(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 50000,
            'total_internet_sales' => 100000,
            'raw_print_pages' => 30,
            'raw_print_amount' => 30000,
            'print_adjustment_pages' => 10,
            'print_adjustment_amount' => 10000,
            'print_adjustment_reason' => 'bad_ink',
            'print_adjustment_notes' => 'Tinta bergaris hitam rusak',
            'print_adjustment_status' => 'pending_approval',
            'is_print_locked' => true,
            'locked_print_pages' => 30,
            'locked_print_amount' => 30000,
            'status' => 'open',
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->supervisor->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-print.approve', ['summary' => $summary->id]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $summary->refresh();
        $this->assertEquals('approved', $summary->print_adjustment_status);
        $this->assertEquals(20, $summary->locked_print_pages);
        $this->assertEquals(20000.0, (float) $summary->locked_print_amount);
    }

    public function test_supervisor_rejects_print_adjustment_successfully(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 50000,
            'total_internet_sales' => 100000,
            'raw_print_pages' => 20,
            'raw_print_amount' => 20000,
            'print_adjustment_pages' => 5,
            'print_adjustment_amount' => 5000,
            'print_adjustment_reason' => 'paper_jam',
            'print_adjustment_notes' => 'Klaim kertas macet',
            'print_adjustment_status' => 'pending_approval',
            'is_print_locked' => true,
            'locked_print_pages' => 20,
            'locked_print_amount' => 20000,
            'status' => 'open',
        ]);

        $response = $this->withSession(['presensi2_employee_id' => $this->supervisor->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-print.reject', ['summary' => $summary->id]), [
                'rejection_reason' => 'Fisik kertas rusak tidak ditemukan di tempat sampah printer.',
            ]);

        $response->assertStatus(200);

        $summary->refresh();
        $this->assertEquals('rejected', $summary->print_adjustment_status);
        $this->assertEquals('Fisik kertas rusak tidak ditemukan di tempat sampah printer.', $summary->print_adjustment_rejection_reason);
        $this->assertEquals(20, $summary->locked_print_pages);
        $this->assertEquals(20000.0, (float) $summary->locked_print_amount);
    }

    public function test_cashier_with_valid_supervisor_pin_print_adjustment_is_instantly_approved(): void
    {
        $response = $this->withSession(['presensi2_employee_id' => $this->cashier->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-print'), [
                'raw_print_pages' => 15,
                'raw_print_amount' => 15000,
                'print_adjustment_pages' => 5,
                'print_adjustment_amount' => 5000,
                'print_adjustment_reason' => 'internal_use',
                'print_adjustment_notes' => 'Cetak jadwal shift karyawan operasional toko.',
                'supervisor_pin' => '998877', // PIN Supervisor valid
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_approved' => true, 'status' => 'approved']);

        $summary = ShiftSalesSummary::where('employee_id', $this->cashier->id)->latest('id')->first();
        $this->assertNotNull($summary);
        $this->assertEquals('approved', $summary->print_adjustment_status);
        $this->assertEquals(10, $summary->locked_print_pages);
        $this->assertEquals(10000.0, (float) $summary->locked_print_amount);
    }

    public function test_admin_can_resolve_shift_cash_discrepancy(): void
    {
        $summary = ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 100000,
            'total_internet_sales' => 200000,
            'total_revenue' => 300000,
            'system_expected_cash' => 300000,
            'blind_cash_count' => 280000,
            'cash_in_drawer' => 280000,
            'cash_difference' => -20000,
            'is_sales_locked' => true,
            'status' => 'closed',
            'discrepancy_resolution_status' => 'unresolved',
        ]);

        $this->assertFalse($summary->isDiscrepancyResolved());

        $response = $this->post(route('admin.penjualan.resolve-discrepancy', $summary->id), [
            'action' => 'reimbursed_by_cashier',
            'notes' => 'Kasir mengganti Rp 20.000 tunai langsung ke laci toko.',
        ]);

        $response->assertRedirect();

        $summary->refresh();
        $this->assertTrue($summary->isDiscrepancyResolved());
        $this->assertEquals('resolved', $summary->discrepancy_resolution_status);
        $this->assertEquals('reimbursed_by_cashier', $summary->discrepancy_resolution_action);
        $this->assertEquals('Diganti Tunai oleh Kasir', $summary->discrepancyActionLabel());
        $this->assertEquals('Kasir mengganti Rp 20.000 tunai langsung ke laci toko.', $summary->discrepancy_resolution_notes);
        $this->assertNotNull($summary->discrepancy_resolved_at);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $summary->id,
            'action' => 'shift_discrepancy_resolved',
        ]);
    }

    private function makePendingBillingAdjustment(): ShiftSalesSummary
    {
        return ShiftSalesSummary::create([
            'employee_id' => $this->cashier->id,
            'branch_id' => $this->branch->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 100000,
            'total_internet_sales' => 350000,
            'raw_billing_amount' => 350000,
            'billing_adjustment_amount' => 50000,
            'billing_adjustment_reason' => 'blackout',
            'billing_adjustment_notes' => 'Klaim mati listrik 30 menit',
            'billing_blackout_minutes' => 30,
            'billing_blackout_pcs' => 10,
            'billing_adjustment_status' => 'pending_approval',
            'is_billing_locked' => true,
            'locked_billing_amount' => 350000,
            'status' => 'open',
        ]);
    }
}
