<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Accounting\Services\AppAutoJournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Tests\TestCase;

class AutoJournalHppAndQrisTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Employee $employee;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-TEST',
            'name' => 'Cabang Test',
            'is_headquarters' => true,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'code' => 'EMP-TEST-01',
            'nik' => '3171000000000001',
            'name' => 'Kasir Test',
            'email' => 'kasir@test.com',
            'phone' => '081234567890',
            'password' => bcrypt('secret123'),
            'pin' => bcrypt('123456'),
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Minuman',
            'code' => 'CAT-DRK',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'PRD-KOPI',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-KOPI-01',
            'name' => 'Kopi Susu Regular',
            'cost_price' => 10000.0,
            'base_price' => 18000.0,
            'is_active' => true,
        ]);
    }

    /**
     * Uji bahwa AutoJournalService di-resolve ke AppAutoJournalService dan
     * berhasil menghitung HPP menggunakan $item->qty dan $variant->cost_price.
     */
    public function test_journal_sale_records_cogs_and_inventory_using_item_qty_and_variant_cost_price(): void
    {
        $service = app(AutoJournalService::class);
        $this->assertInstanceOf(AppAutoJournalService::class, $service);

        $sale = CafeSale::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'invoice_number' => 'INV-TEST-001',
            'customer_name' => 'Pelanggan Umum',
            'gross_amount' => 36000.0,
            'discount_amount' => 0.0,
            'net_amount' => 36000.0,
            'total_fnb' => 36000.0,
            'total_billing' => 0.0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variant->id,
            'item_type' => 'fnb',
            'item_name' => 'Kopi Susu Regular',
            'qty' => 2,
            'unit_price' => 18000.0,
            'subtotal' => 36000.0,
        ]);

        $entry = $service->journalSale($sale);

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals('posted', $entry->status);

        // 1. Baris Kas Laci (1-10001): Debit 36.000
        $cashLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-10001'))->first();
        $this->assertNotNull($cashLine);
        $this->assertEquals(36000.0, (float) $cashLine->debit);
        $this->assertEquals(0.0, (float) $cashLine->credit);

        // 2. Baris Pendapatan F&B (4-10001): Kredit 36.000
        $fnbLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '4-10001'))->first();
        $this->assertNotNull($fnbLine);
        $this->assertEquals(0.0, (float) $fnbLine->debit);
        $this->assertEquals(36000.0, (float) $fnbLine->credit);

        // 3. Baris HPP (5-10001): Debit 20.000 (2 qty * 10.000 cost_price)
        $cogsLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '5-10001'))->first();
        $this->assertNotNull($cogsLine, 'Baris jurnal HPP (5-10001) harus terbentuk');
        $this->assertEquals(20000.0, (float) $cogsLine->debit);
        $this->assertEquals(0.0, (float) $cogsLine->credit);

        // 4. Baris Persediaan Barang Dagang (1-13001): Kredit 20.000
        $invLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-13001'))->first();
        $this->assertNotNull($invLine, 'Baris jurnal Persediaan (1-13001) harus terbentuk');
        $this->assertEquals(0.0, (float) $invLine->debit);
        $this->assertEquals(20000.0, (float) $invLine->credit);

        // 5. Total Debit dan Kredit harus berimbang
        $this->assertEquals((float) $entry->lines()->sum('debit'), (float) $entry->lines()->sum('credit'));
        $this->assertEquals(56000.0, (float) $entry->total_debit);
        $this->assertEquals(56000.0, (float) $entry->total_credit);
    }

    /**
     * Uji bahwa pembayaran non-tunai (QRIS) mengarahkan debit ke Rekening Bank BCA (1-11001),
     * bukan Kas Laci Kasir (1-10001).
     */
    public function test_journal_sale_records_debit_to_bank_bca_when_payment_is_qris(): void
    {
        $service = app(AutoJournalService::class);

        $sale = CafeSale::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'invoice_number' => 'INV-TEST-QRIS-002',
            'customer_name' => 'Pelanggan QRIS',
            'gross_amount' => 18000.0,
            'discount_amount' => 0.0,
            'net_amount' => 18000.0,
            'total_fnb' => 18000.0,
            'total_billing' => 0.0,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
        ]);

        CafeSaleItem::create([
            'cafe_sale_id' => $sale->id,
            'product_variant_id' => $this->variant->id,
            'item_type' => 'fnb',
            'item_name' => 'Kopi Susu Regular',
            'qty' => 1,
            'unit_price' => 18000.0,
            'subtotal' => 18000.0,
        ]);

        $entry = $service->journalSale($sale);

        // Verifikasi bahwa debit masuk ke Rekening Bank BCA (1-11001)
        $bankLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-11001'))->first();
        $this->assertNotNull($bankLine, 'Pembayaran QRIS harus mendebit Rekening Bank BCA (1-11001)');
        $this->assertEquals(18000.0, (float) $bankLine->debit);

        // Verifikasi Kas Laci (1-10001) TIDAK didebit untuk transaksi QRIS
        $cashLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-10001'))->first();
        $this->assertNull($cashLine, 'Kas Laci Kasir tidak boleh bertambah untuk pembayaran non-tunai/QRIS');
    }

    /**
     * Uji bahwa jika terdapat record pada tabel cafe_payments, sistem membaca metode pembayaran
     * dari masing-masing pembayaran tersebut.
     */
    public function test_journal_sale_records_debit_from_cafe_payments_table(): void
    {
        $service = app(AutoJournalService::class);

        $sale = CafeSale::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'invoice_number' => 'INV-TEST-SPLIT-003',
            'customer_name' => 'Pelanggan Multi Payment',
            'gross_amount' => 50000.0,
            'discount_amount' => 0.0,
            'net_amount' => 50000.0,
            'total_fnb' => 50000.0,
            'total_billing' => 0.0,
            'payment_method' => 'split',
            'payment_status' => 'paid',
        ]);

        \Modules\MasterData\Domain\Models\CafePayment::create([
            'cafe_sale_id' => $sale->id,
            'payment_number' => 'PAY-001-CASH',
            'payment_method' => 'cash',
            'amount_due' => 20000.0,
            'amount_paid' => 20000.0,
            'change_amount' => 0.0,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        \Modules\MasterData\Domain\Models\CafePayment::create([
            'cafe_sale_id' => $sale->id,
            'payment_number' => 'PAY-002-QRIS',
            'payment_method' => 'qris',
            'amount_due' => 30000.0,
            'amount_paid' => 30000.0,
            'change_amount' => 0.0,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $entry = $service->journalSale($sale);

        // Kas Laci (1-10001) didebit 20.000
        $cashLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-10001'))->first();
        $this->assertNotNull($cashLine);
        $this->assertEquals(20000.0, (float) $cashLine->debit);

        // Rekening Bank BCA (1-11001) didebit 30.000
        $bankLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-11001'))->first();
        $this->assertNotNull($bankLine);
        $this->assertEquals(30000.0, (float) $bankLine->debit);
    }

    /**
     * Uji penjurnalan otomatis selisih kurang (defisit uang fisik di kas laci kasir).
     * Debit: 6-10007 (Beban Kerugian Selisih Kasir)
     * Kredit: 1-10001 (Kas Laci Kasir)
     */
    public function test_journal_cash_discrepancy_records_expense_and_credits_cash_on_deficit(): void
    {
        $service = app(AutoJournalService::class);

        $summary = \App\Models\ShiftSalesSummary::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'work_date' => now()->toDateString(),
            'system_expected_cash' => 500000.0,
            'blind_cash_count' => 450000.0,
            'cash_difference' => -50000.0,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $entry = $service->journalCashDiscrepancy($summary);

        $this->assertNotNull($entry);
        $this->assertEquals(50000.0, (float) $entry->total_debit);
        $this->assertEquals(50000.0, (float) $entry->total_credit);
        $this->assertTrue($entry->isBalanced());

        // Cek akun Beban Selisih Kas (6-10007) didebit 50.000
        $expenseLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '6-10007'))->first();
        $this->assertNotNull($expenseLine);
        $this->assertEquals(50000.0, (float) $expenseLine->debit);

        // Cek akun Kas Laci (1-10001) dikredit 50.000
        $cashLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-10001'))->first();
        $this->assertNotNull($cashLine);
        $this->assertEquals(50000.0, (float) $cashLine->credit);
    }

    /**
     * Uji penjurnalan otomatis selisih lebih (surplus uang fisik di kas laci kasir).
     * Debit: 1-10001 (Kas Laci Kasir)
     * Kredit: 4-20002 (Pendapatan Selisih Lebih Kas Laci)
     */
    public function test_journal_cash_discrepancy_records_revenue_and_debits_cash_on_surplus(): void
    {
        $service = app(AutoJournalService::class);

        $summary = \App\Models\ShiftSalesSummary::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'work_date' => now()->toDateString(),
            'system_expected_cash' => 300000.0,
            'blind_cash_count' => 325000.0,
            'cash_difference' => 25000.0,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $entry = $service->journalCashDiscrepancy($summary);

        $this->assertNotNull($entry);
        $this->assertEquals(25000.0, (float) $entry->total_debit);
        $this->assertEquals(25000.0, (float) $entry->total_credit);
        $this->assertTrue($entry->isBalanced());

        // Cek Kas Laci (1-10001) didebit 25.000
        $cashLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-10001'))->first();
        $this->assertNotNull($cashLine);
        $this->assertEquals(25000.0, (float) $cashLine->debit);

        // Cek Pendapatan Selisih Lebih (4-20002) dikredit 25.000
        $revenueLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '4-20002'))->first();
        $this->assertNotNull($revenueLine);
        $this->assertEquals(25000.0, (float) $revenueLine->credit);
    }

    /**
     * Uji pemutakhiran resolusi selisih kas menjadi payroll deduction (potong gaji/piutang kasir).
     */
    public function test_observer_triggers_discrepancy_journal_and_updates_on_payroll_resolution(): void
    {
        // 1. Kasir tutup shift dengan defisit kas Rp 100.000
        $summary = \App\Models\ShiftSalesSummary::create([
            'branch_id' => $this->branch->id,
            'employee_id' => $this->employee->id,
            'work_date' => now()->toDateString(),
            'system_expected_cash' => 1000000.0,
            'blind_cash_count' => 900000.0,
            'cash_difference' => -100000.0,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // Jurnal otomatis terbentuk via ShiftSalesSummaryObserver
        $entry = JournalEntry::where('reference_type', \App\Models\ShiftSalesSummary::class)
            ->where('reference_id', $summary->id)
            ->first();

        $this->assertNotNull($entry);
        $this->assertEquals(100000.0, (float) $entry->total_debit);

        // 2. Admin menyelesaikan investigasi dan memutuskan potong gaji (payroll_deduction)
        $summary->update([
            'discrepancy_resolution_status' => 'resolved',
            'discrepancy_resolution_action' => 'payroll_deduction',
            'discrepancy_resolution_notes' => 'Disetujui kasir dipotong pada slip gaji bulan ini',
            'discrepancy_resolved_by' => 'Admin ERP',
            'discrepancy_resolved_at' => now(),
        ]);

        $entry->refresh();
        $piutangLine = $entry->lines()->whereHas('chartOfAccount', fn ($q) => $q->where('account_code', '1-12002'))->first();
        $this->assertNotNull($piutangLine, 'Akun debit harus diperbarui ke 1-12002 (Piutang Selisih Kasir)');
        $this->assertEquals(100000.0, (float) $piutangLine->debit);
    }
}
