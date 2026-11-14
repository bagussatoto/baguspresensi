<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AdminRole;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Models\ShiftSalesSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
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

/**
 * Feature Test Siklus Estafet Multi-Shift End-to-End:
 * Skenario Rantai Operasional:
 * Shift 1 Tutup & Serah Terima ➔ Shift 2 Masuk & Transaksi (Tidak Terkunci oleh Shift 1) ➔
 * Kasir 2 Ajukan Klaim Billing PLN (Mati Listrik) ➔ Admin Approve Penyesuaian Billing ➔
 * Admin Buka Kunci Shift ➔ Batch Setor Multi-Shift & Verifikasi Penerimaan Admin Pusat.
 */
class ShiftEstafetMultiShiftLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Shift $shift1;

    private Shift $shift2;

    private Employee $kasir1;

    private Employee $kasir2;

    private User $adminUser;

    private Warehouse $warehouse;

    private ProductVariant $variantKopi;

    private PcStation $station;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Chart of Accounts untuk Akuntansi GL
        ChartOfAccount::create([
            'account_code' => '1-10001',
            'account_name' => 'Kas Laci Kasir (Outlet)',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
        ]);

        ChartOfAccount::create([
            'account_code' => '1-11001',
            'account_name' => 'Rekening Bank BCA',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
        ]);

        // 2. Cabang & Shift
        $this->branch = Branch::create([
            'id' => 3,
            'name' => 'Maya Graha Pusat',
            'code' => 'CBG-PST',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->shift1 = Shift::create([
            'name' => 'Shift 1 Pagi',
            'code' => 'SHIFT-1',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->shift2 = Shift::create([
            'name' => 'Shift 2 Sore',
            'code' => 'SHIFT-2',
            'start_time' => '15:00:00',
            'end_time' => '23:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        // 3. Kasir Shift 1 dan Kasir Shift 2
        $this->kasir1 = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Bagus Kasir 1',
            'code' => 'EMP-001',
            'position' => 'Kasir Shift 1',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $this->kasir2 = Employee::create([
            'branch_id' => $this->branch->id,
            'name' => 'Grace Kasir 2',
            'code' => 'EMP-002',
            'position' => 'Kasir Shift 2',
            'password' => Hash::make('secret123'),
            'pin' => Hash::make('654321'),
            'is_active' => true,
        ]);

        // 4. Admin Pusat / Super Admin
        $this->adminUser = User::factory()->create([
            'name' => 'Manager Bagus Pusat',
            'email' => 'admin.pusat@mayagraha.com',
            'role' => AdminRole::SuperAdmin,
        ]);

        // 5. Gudang & Produk
        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'GDG-KSR-PST',
            'name' => 'Loker Kasir Utama',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $uomCup = UnitOfMeasurement::create([
            'code' => 'CUP',
            'name' => 'Cup',
            'symbol' => 'cup',
            'is_active' => true,
        ]);

        $catMinuman = Category::create([
            'name' => 'Minuman',
            'code' => 'CAT-DRK',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $productKopi = Product::create([
            'category_id' => $catMinuman->id,
            'uom_id' => $uomCup->id,
            'code' => 'PRD-KOPI',
            'name' => 'Kopi Susu Aren',
            'is_active' => true,
        ]);

        $this->variantKopi = ProductVariant::create([
            'product_id' => $productKopi->id,
            'sku' => 'SKU-KOPI-AREN',
            'name' => 'Es Kopi Susu Aren Dingin',
            'base_price' => 12000.00,
            'is_active' => true,
        ]);

        StockInventory::create([
            'warehouse_id' => $this->warehouse->id,
            'product_variant_id' => $this->variantKopi->id,
            'qty_on_hand' => 50,
            'safety_stock' => 5,
        ]);

        $this->station = PcStation::create([
            'branch_id' => $this->branch->id,
            'station_code' => 'PC-01',
            'station_type' => 'Reguler',
            'hourly_rate' => 6000,
            'status' => 'available',
            'is_active' => true,
        ]);
    }

    /**
     * Uji Siklus Estafet Penuh (Multi-Shift Lifecycle).
     */
    public function test_full_estafet_multi_shift_lifecycle(): void
    {
        // =========================================================================
        // TAHAP 1: SHIFT 1 BEROPERASI (CLOCK IN, TRANSAKSI POS, CLOSING & SERAH TERIMA)
        // =========================================================================

        // 1a. Kasir 1 Clock In
        $this->clockInEmployee($this->kasir1, $this->shift1);

        // 1b. Kasir 1 Transaksi POS (2 cup kopi = Rp 24.000)
        $saleResponse1 = $this->withSession(['presensi2_employee_id' => $this->kasir1->id])
            ->post(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'variant_id' => $this->variantKopi->id,
                'qty' => 2,
                'payment_method' => 'cash',
                'customer_name' => 'Pelanggan Pagi 01',
            ]);
        $saleResponse1->assertRedirect(route('presensi2.penjualan'));

        $sale1 = CafeSale::where('employee_id', $this->kasir1->id)->latest('id')->first();
        $this->assertNotNull($sale1);
        $this->assertEquals(24000.0, (float) $sale1->total_fnb);
        $this->assertFalse((bool) $sale1->is_locked); // Transaksi masih belum terkunci

        // 1c. Kasir 1 Melakukan Tutup Shift & Serah Terima Kas ke Kasir 2 (Grace)
        $summary1 = ShiftSalesSummary::create([
            'employee_id' => $this->kasir1->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $this->shift1->id,
            'work_date' => Carbon::today()->toDateString(),
            'total_fnb_sales' => 24000,
            'total_internet_sales' => 0,
            'total_revenue' => 24000,
            'system_expected_cash' => 24000,
            'blind_cash_count' => 24000,
            'cash_in_drawer' => 24000,
            'cash_difference' => 0,
            'status' => 'open',
        ]);

        $handoverResponse1 = $this->withSession(['presensi2_employee_id' => $this->kasir1->id])
            ->post(route('presensi2.serah-terima.store'), [
                'action_type' => 'handover',
                'source' => 'v2',
                'summary_id' => $summary1->id,
                'handover_amount' => 24000,
                'retained_drawer_cash' => 0,
                'handover_recipient' => $this->kasir2->name,
                'notes' => 'Kas shift 1 diserahkan lengkap ke Kasir 2',
            ]);
        $handoverResponse1->assertRedirect();

        $summary1->refresh();
        $this->assertEquals('closed', $summary1->status);
        $this->assertTrue((bool) $summary1->is_sales_locked);
        $this->assertEquals('completed', $summary1->handover_status);

        // Pastikan transaksi milik Shift 1 terkunci setelah closing
        $sale1->refresh();
        $this->assertTrue((bool) $sale1->is_locked);

        // =========================================================================
        // TAHAP 2: ESTAFET SHIFT 2 MASUK & TRANSAKSI (TIDAK TERKUNCI OLEH SHIFT 1)
        // =========================================================================

        // 2a. Kasir 2 Clock In
        $this->clockInEmployee($this->kasir2, $this->shift2);

        // 2b. Kasir 2 Buka Halaman Penjualan POS (Harus Aktif dan Bisa Diakses)
        $posPageResponse = $this->withSession(['presensi2_employee_id' => $this->kasir2->id])
            ->get(route('presensi2.penjualan'));
        $posPageResponse->assertOk();

        // 2c. Kasir 2 Melakukan Transaksi Baru di Shift 2 (1 cup kopi = Rp 12.000)
        $saleResponse2 = $this->withSession(['presensi2_employee_id' => $this->kasir2->id])
            ->post(route('presensi2.penjualan.transaksi'), [
                'transaction_type' => 'fnb',
                'variant_id' => $this->variantKopi->id,
                'qty' => 1,
                'payment_method' => 'cash',
                'customer_name' => 'Pelanggan Sore 01',
            ]);
        $saleResponse2->assertRedirect(route('presensi2.penjualan'));

        // 2d. VERIFIKASI KRITIS: Transaksi Shift 2 TIDAK TERKUNCI oleh closing Shift 1
        $sale2 = CafeSale::where('employee_id', $this->kasir2->id)->latest('id')->first();
        $this->assertNotNull($sale2);
        $this->assertEquals(12000.0, (float) $sale2->total_fnb);
        $this->assertFalse((bool) $sale2->is_locked, 'Transaksi Shift 2 tidak boleh terkunci oleh penutupan Shift 1.');

        // =========================================================================
        // TAHAP 3: KASIR 2 AJUKAN KLAIM PENYESUAIAN BILLING PLN (MATI LISTRIK)
        // =========================================================================
        $adjResponse = $this->withSession(['presensi2_employee_id' => $this->kasir2->id])
            ->postJson(route('presensi2.penjualan.penyesuaian-billing'), [
                'raw_billing_amount' => 150000,
                'billing_adjustment_amount' => 30000,
                'billing_adjustment_reason' => 'blackout',
                'billing_adjustment_notes' => 'Pemadaman listrik PLN 45 menit di outlet MGK',
                'billing_blackout_minutes' => 45,
                'billing_blackout_pcs' => 10,
            ]);

        $adjResponse->assertStatus(200);
        $adjResponse->assertJson([
            'success' => true,
            'is_approved' => false,
            'status' => 'pending_approval',
        ]);

        $summary2 = ShiftSalesSummary::where('employee_id', $this->kasir2->id)->latest('id')->first();
        $this->assertNotNull($summary2);
        $this->assertEquals('pending_approval', $summary2->billing_adjustment_status);

        // =========================================================================
        // TAHAP 4: ADMIN PUSAT APPROVE KLAIM BILLING PLN DI ADMIN ERP
        // =========================================================================
        $approveResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Super Admin', 'admin_logged_in' => true])
            ->post(route('admin.penjualan.penyesuaian-billing.approve', $summary2->id));

        $approveResponse->assertRedirect(route('admin.penjualan.penyesuaian-billing'));

        $summary2->refresh();
        $this->assertEquals('approved', $summary2->billing_adjustment_status);
        $this->assertEquals(120000.0, (float) $summary2->locked_billing_amount); // 150.000 - 30.000
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $summary2->id,
            'action' => 'shift_billing_adjustment_approved_by_admin',
        ]);

        // =========================================================================
        // TAHAP 5: ADMIN PUSAT BUKA KUNCI SHIFT (RE-OPEN SHIFT 1 DENGAN REASON AUDIT)
        // =========================================================================
        $unlockResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Super Admin', 'admin_logged_in' => true])
            ->post(route('admin.penjualan.unlock-sales', $summary1->id), [
                'reason' => 'Otorisasi koreksi transaksi Shift 1 oleh Manager Operasional',
            ]);

        $unlockResponse->assertRedirect();
        $summary1->refresh();
        $this->assertFalse($summary1->is_sales_locked);
        $this->assertEquals('open', $summary1->status);

        // Transaksi Shift 1 terbuka kembali
        $sale1->refresh();
        $this->assertFalse((bool) $sale1->is_locked);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $summary1->id,
            'action' => 'shift_sales_unlocked',
        ]);

        // Kunci kembali Shift 1 untuk persiapan setor
        $summary1->update([
            'status' => 'closed',
            'is_sales_locked' => true,
        ]);

        // =========================================================================
        // TAHAP 6: BATCH SETOR KAS MULTI-SHIFT (SHIFT 1 + SHIFT 2) & VERIFIKASI PUSAT
        // =========================================================================

        // 6a. Kasir 2 mengeksekusi setoran multi-shift (Shift 1 & Shift 2)
        $batchDepositResponse = $this->withSession(['presensi2_employee_id' => $this->kasir2->id])
            ->post(route('presensi2.serah-terima.store'), [
                'action_type' => 'setor',
                'source' => 'v2',
                'summary_id' => $summary2->id,
                'summary_ids' => [$summary1->id, $summary2->id],
                'deposit_method' => 'bank_transfer',
                'deposited_amount' => 144000, // 24.000 + 120.000
                'deposit_recipient' => 'BCA MayaNet Pusat',
                'deposit_notes' => 'Setoran multi-shift 1 dan 2 ke rekening BCA Pusat',
            ]);

        $batchDepositResponse->assertRedirect();

        $summary1->refresh();
        $summary2->refresh();

        // Verifikasi kedua shift memiliki receipt gabungan yang sama & status pending_verification
        $this->assertEquals('pending_verification', $summary1->deposit_status);
        $this->assertEquals('pending_verification', $summary2->deposit_status);
        $this->assertNotEmpty($summary1->deposit_receipt_no);
        $this->assertEquals($summary1->deposit_receipt_no, $summary2->deposit_receipt_no);
        $this->assertEquals($summary1->deposit_batch_id, $summary2->deposit_batch_id);

        // Kedua shift terlindungi dari buka kunci sembarangan
        $this->assertTrue($summary1->isDepositedToBank());
        $this->assertTrue($summary2->isDepositedToBank());

        // 6b. Admin Pusat Konfirmasi Penerimaan Dana Masuk Rekening di Hub ERP
        $verifyResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_role' => 'Super Admin', 'admin_logged_in' => true])
            ->post(route('admin.penjualan.verify-setor', $summary2->id), [
                'deposit_status' => 'verified',
                'notes' => 'Dana setoran multi-shift telah masuk mutasi rekening BCA',
            ]);

        $verifyResponse->assertRedirect();

        $summary1->refresh();
        $summary2->refresh();

        // Kedua shift otomatis terverifikasi bersamaan dalam 1 batch
        $this->assertEquals('verified', $summary1->deposit_status);
        $this->assertEquals('verified', $summary2->deposit_status);

        // 6c. Jurnal Umum Akuntansi SAK (GL Double-Entry) otomatis dibukukan
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => ShiftSalesSummary::class,
            'reference_id' => $summary1->id,
            'status' => 'posted',
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => ShiftSalesSummary::class,
            'reference_id' => $summary2->id,
            'status' => 'posted',
        ]);
    }

    /**
     * Helper untuk Clock In Karyawan.
     */
    private function clockInEmployee(Employee $emp, Shift $shift): AttendanceRecord
    {
        return AttendanceRecord::create([
            'employee_id' => $emp->id,
            'branch_id' => $this->branch->id,
            'shift_id' => $shift->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
            'status' => AttendanceStatus::Present,
            'verification_method' => VerificationMethod::AdminManual,
            'latitude' => $this->branch->latitude ?? -6.200000,
            'longitude' => $this->branch->longitude ?? 106.816666,
            'is_mock_location' => false,
        ]);
    }
}
