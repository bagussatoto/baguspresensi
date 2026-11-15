<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ShiftSalesSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\AccountingPeriod;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class AdminAccountingTest extends TestCase
{
    use RefreshDatabase;

    protected ChartOfAccount $cashAccount;

    protected ChartOfAccount $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashAccount = ChartOfAccount::create([
            'account_code' => '1-10001',
            'account_name' => 'Kas Laci Kasir Utama',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
        ]);

        $this->revenueAccount = ChartOfAccount::create([
            'account_code' => '4-10001',
            'account_name' => 'Pendapatan Penjualan F&B',
            'account_category' => AccountCategory::REVENUE,
            'normal_balance' => NormalBalance::CREDIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);
    }

    public function test_admin_can_access_jurnal_umum(): void
    {
        $response = $this->get(route('admin.akuntansi.jurnal'));

        $response->assertStatus(200);
        $response->assertSee('Jurnal Umum (General Journal)');
        $response->assertSee('Daftar Jurnal Pembukuan');
    }

    public function test_admin_can_access_buku_besar(): void
    {
        $response = $this->get(route('admin.akuntansi.buku-besar', ['account_id' => $this->cashAccount->id]));

        $response->assertStatus(200);
        $response->assertSee('Buku Besar (General Ledger)');
        $response->assertSee('Kas Laci Kasir Utama');
        $response->assertSee('Unduh CSV');
    }

    public function test_admin_can_export_buku_besar_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.buku-besar', [
            'account_id' => $this->cashAccount->id,
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_neraca_saldo(): void
    {
        $response = $this->get(route('admin.akuntansi.neraca-saldo'));

        $response->assertStatus(200);
        $response->assertSee('Neraca Saldo (Trial Balance)');
        $response->assertSee('PEMBUKUAN DINYATAKAN SEIMBANG');
        $response->assertSee('1. Total Aset &amp; Kas', false);
        $response->assertSee('Unduh CSV');
    }

    public function test_admin_can_export_neraca_saldo_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.neraca-saldo', [
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_laba_rugi(): void
    {
        $response = $this->get(route('admin.akuntansi.laba-rugi'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Laba Rugi');
        $response->assertSee('LABA KOTOR (GROSS PROFIT)');
        $response->assertSee('LABA BERSIH OPERASIONAL');
        $response->assertSee('Unduh CSV');
        $response->assertSee('Cetak / PDF');
    }

    public function test_admin_can_filter_laba_rugi_by_branch(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang Ritel Sentral',
            'code' => 'CRS-01',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.akuntansi.laba-rugi', [
            'branch_id' => $branch->id,
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Cabang Ritel Sentral');
        $response->assertSee('Bulan Ini');
    }

    public function test_admin_can_export_laba_rugi_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.laba-rugi', [
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_neraca_keuangan(): void
    {
        $response = $this->get(route('admin.akuntansi.neraca'));

        $response->assertStatus(200);
        $response->assertSee('Neraca Keuangan (Balance Sheet)');
        $response->assertSee('AKTIVA / ASET PERUSAHAAN');
        $response->assertSee('PASIVA (KEWAJIBAN');
        $response->assertSee('Unduh CSV');
        $response->assertSee('Cetak / PDF');
    }

    public function test_admin_can_filter_neraca_keuangan_by_branch(): void
    {
        $branch = Branch::create([
            'name' => 'Cabang MGK Cyber Barat',
            'code' => 'MCB-01',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.akuntansi.neraca', [
            'branch_id' => $branch->id,
            'as_of_date' => date('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Cabang MGK Cyber Barat');
        $response->assertSee('Akhir Bulan Ini');
    }

    public function test_admin_can_export_neraca_keuangan_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.neraca', [
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_post_manual_balanced_journal(): void
    {
        $payload = [
            'entry_date' => date('Y-m-d'),
            'description' => 'Setoran Tunai Awal Uji Coba',
            'lines' => [
                [
                    'chart_of_account_id' => $this->cashAccount->id,
                    'debit' => 500000,
                    'credit' => 0,
                    'memo' => 'Debit Kas',
                ],
                [
                    'chart_of_account_id' => $this->revenueAccount->id,
                    'debit' => 0,
                    'credit' => 500000,
                    'memo' => 'Kredit Pendapatan',
                ],
            ],
        ];

        $response = $this->post(route('admin.akuntansi.jurnal.manual'), $payload);

        $response->assertRedirect(route('admin.akuntansi.jurnal'));
        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Setoran Tunai Awal Uji Coba',
            'total_debit' => 500000,
            'total_credit' => 500000,
        ]);
    }

    public function test_cannot_post_unbalanced_manual_journal(): void
    {
        $payload = [
            'entry_date' => date('Y-m-d'),
            'description' => 'Jurnal Pincang Tidak Seimbang',
            'lines' => [
                [
                    'chart_of_account_id' => $this->cashAccount->id,
                    'debit' => 500000,
                    'credit' => 0,
                ],
                [
                    'chart_of_account_id' => $this->revenueAccount->id,
                    'debit' => 0,
                    'credit' => 300000, // Selisih 200rb
                ],
            ],
        ];

        $response = $this->post(route('admin.akuntansi.jurnal.manual'), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('journal_entries', [
            'description' => 'Jurnal Pincang Tidak Seimbang',
        ]);
    }

    public function test_auto_journal_service_journals_shift_deposit_double_entry(): void
    {
        $branch = Branch::first() ?? Branch::create([
            'name' => 'MGK Cyber Outlet',
            'code' => 'MGK-01',
            'is_active' => true,
        ]);

        $employee = Employee::first() ?? Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Bagus Kasir',
            'code' => 'EMP-KSR-01',
            'position' => 'Kasir',
            'is_active' => true,
        ]);

        $summary = ShiftSalesSummary::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'work_date' => date('Y-m-d'),
            'total_revenue' => 350000,
            'cash_in_drawer' => 350000,
            'deposited_amount' => 350000,
            'deposit_receipt_no' => 'STR-20261114-TEST-99',
            'deposit_method' => 'bank_transfer',
            'deposit_status' => 'verified',
            'status' => 'closed',
        ]);

        $service = app(AutoJournalService::class);
        $entry = $service->journalShiftDeposit($summary, 350000, 'STR-20261114-TEST-99', 'bank_transfer');

        $this->assertNotNull($entry);
        $this->assertEquals(350000, (float) $entry->total_debit);
        $this->assertEquals(350000, (float) $entry->total_credit);
        $this->assertEquals('posted', $entry->status);

        // Baris Jurnal
        $lines = $entry->lines()->with('chartOfAccount')->get();
        $this->assertCount(2, $lines);

        $debitLine = $lines->firstWhere('debit', '>', 0);
        $creditLine = $lines->firstWhere('credit', '>', 0);

        $this->assertEquals('1-11001', $debitLine->chartOfAccount->account_code); // Bank BCA (Debit)
        $this->assertEquals('1-10001', $creditLine->chartOfAccount->account_code); // Kas Kasir (Kredit)
        $this->assertEquals(350000, (float) $debitLine->debit);
        $this->assertEquals(350000, (float) $creditLine->credit);

        // Idempotency: memanggil ulang tidak membuat jurnal ganda
        $reEntry = $service->journalShiftDeposit($summary, 350000, 'STR-20261114-TEST-99', 'bank_transfer');
        $this->assertEquals($entry->id, $reEntry->id);
    }

    public function test_admin_verifying_deposit_records_auto_journal(): void
    {
        $branch = Branch::first() ?? Branch::create([
            'name' => 'MGK Cyber Outlet',
            'code' => 'MGK-01',
            'is_active' => true,
        ]);

        $employee = Employee::first() ?? Employee::create([
            'branch_id' => $branch->id,
            'name' => 'Bagus Kasir',
            'code' => 'EMP-KSR-01',
            'position' => 'Kasir',
            'is_active' => true,
        ]);

        $summary = ShiftSalesSummary::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'work_date' => date('Y-m-d'),
            'total_revenue' => 200000,
            'cash_in_drawer' => 200000,
            'deposited_amount' => 200000,
            'deposit_receipt_no' => 'STR-20261114-VRF-01',
            'deposit_method' => 'cash_collector',
            'deposit_status' => 'pending',
            'status' => 'closed',
        ]);

        $response = $this->post(route('admin.penjualan.verify-setor', $summary->id), [
            'deposit_status' => 'verified',
            'notes' => 'Dana telah diterima oleh kasir pusat',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => ShiftSalesSummary::class,
            'reference_id' => $summary->id,
            'total_debit' => 200000,
            'total_credit' => 200000,
            'status' => 'posted',
        ]);
    }

    public function test_admin_can_access_tutup_buku_page(): void
    {
        $response = $this->get(route('admin.akuntansi.tutup-buku'));

        $response->assertStatus(200);
        $response->assertSee('Tutup Buku');
        $response->assertSee('Kunci &amp; Tutup Periode Akuntansi', false);
    }

    public function test_admin_can_close_accounting_period_and_generate_closing_journal(): void
    {
        // Catat transaksi pendapatan pada Oktober 2026
        $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => '2026-10-15',
            'description' => 'Pendapatan Oktober 2026',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 750000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 750000],
            ],
        ]);

        $response = $this->post(route('admin.akuntansi.tutup-buku.close'), [
            'year' => 2026,
            'month' => 10,
            'generate_closing_journal' => 1,
            'notes' => 'Tutup buku Oktober selesai diaudit',
        ]);

        $response->assertRedirect(route('admin.akuntansi.tutup-buku'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accounting_periods', [
            'year' => 2026,
            'month' => 10,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'reference_type' => 'period_closing',
            'total_debit' => 750000,
            'total_credit' => 750000,
            'status' => 'posted',
        ]);
    }

    public function test_cannot_post_manual_journal_into_locked_period(): void
    {
        $this->post(route('admin.akuntansi.tutup-buku.close'), [
            'year' => 2026,
            'month' => 10,
            'notes' => 'Kunci Oktober',
        ]);

        $response = $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => '2026-10-20',
            'description' => 'Percobaan Jurnal Backdated Terlarang',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 100000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('journal_entries', [
            'description' => 'Percobaan Jurnal Backdated Terlarang',
        ]);
    }

    public function test_admin_can_reopen_closed_accounting_period_with_audit_reason(): void
    {
        // Catat transaksi & tutup dengan jurnal penutup
        $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => '2026-10-12',
            'description' => 'Pendapatan Awal Oktober',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 300000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 300000],
            ],
        ]);

        $this->post(route('admin.akuntansi.tutup-buku.close'), [
            'year' => 2026,
            'month' => 10,
            'generate_closing_journal' => 1,
        ]);

        $period = AccountingPeriod::where('year', 2026)->where('month', 10)->firstOrFail();
        $closingJournalId = $period->closing_journal_entry_id;

        $response = $this->post(route('admin.akuntansi.tutup-buku.reopen', $period->id), [
            'reason' => 'Koreksi faktur pembelian supplier terlambat',
        ]);

        $response->assertRedirect(route('admin.akuntansi.tutup-buku'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('accounting_periods', [
            'id' => $period->id,
            'status' => 'open',
            'closing_journal_entry_id' => null,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'id' => $closingJournalId,
            'status' => 'reversed',
        ]);

        // Setelah dibuka kembali, jurnal manual tanggal Oktober 2026 bisa masuk lagi
        $retry = $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => '2026-10-20',
            'description' => 'Jurnal Koreksi Setelah Reopen',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 50000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 50000],
            ],
        ]);
        $retry->assertRedirect(route('admin.akuntansi.jurnal'));
        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Jurnal Koreksi Setelah Reopen',
        ]);
    }

    public function test_admin_can_access_arus_kas_and_reconcile_balances(): void
    {
        $expenseAccount = ChartOfAccount::create([
            'account_code' => '6-10001',
            'account_name' => 'Beban Listrik & Internet',
            'account_category' => AccountCategory::EXPENSE,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        $equityAccount = ChartOfAccount::create([
            'account_code' => '3-10001',
            'account_name' => 'Modal Disetor Pemilik',
            'account_category' => AccountCategory::EQUITY,
            'normal_balance' => NormalBalance::CREDIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'current_balance' => 0,
        ]);

        // Setoran modal bulan lalu -> membentuk Saldo Kas Awal periode bulan ini Rp 1.000.000
        $prevMonthDate = date('Y-m-d', strtotime('first day of previous month'));
        $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => $prevMonthDate,
            'description' => 'Setoran Modal Awal',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 1000000, 'credit' => 0],
                ['chart_of_account_id' => $equityAccount->id, 'debit' => 0, 'credit' => 1000000],
            ],
        ]);

        // Kas masuk Rp 400.000 dari Pendapatan bulan ini
        $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => date('Y-m-d'),
            'description' => 'Penerimaan Tunai POS',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 400000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 400000],
            ],
        ]);

        // Kas keluar Rp 100.000 untuk Beban Listrik
        $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => date('Y-m-d'),
            'description' => 'Bayar Tagihan Listrik',
            'lines' => [
                ['chart_of_account_id' => $expenseAccount->id, 'debit' => 100000, 'credit' => 0],
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 0, 'credit' => 100000],
            ],
        ]);

        $htmlResponse = $this->get(route('admin.akuntansi.arus-kas'));
        $htmlResponse->assertStatus(200);
        $htmlResponse->assertSee('Laporan Arus Kas (Cash Flow Statement)');
        $htmlResponse->assertSee('Unduh CSV');
        $htmlResponse->assertSee('Cetak / PDF');

        $jsonResponse = $this->getJson(route('admin.akuntansi.arus-kas'));
        $jsonResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'opening_cash_balance' => 1000000.0,
                'total_operating_cash_flow' => 300000.0,
                'net_cash_change' => 300000.0,
                'ending_cash_balance' => 1300000.0,
                'is_reconciled' => true,
            ]);
    }

    public function test_admin_can_export_arus_kas_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.arus-kas', [
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_calk_and_full_financial_package(): void
    {
        $this->post(route('admin.akuntansi.jurnal.manual'), [
            'entry_date' => date('Y-m-d'),
            'description' => 'Transaksi Paket CaLK',
            'lines' => [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 500000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 500000],
            ],
        ]);

        $htmlResponse = $this->get(route('admin.akuntansi.calk'));
        $htmlResponse->assertStatus(200);
        $htmlResponse->assertSee('Catatan atas Laporan Keuangan (CaLK)');
        $htmlResponse->assertSee('Catatan 1: Gambaran Umum Entitas');
        $htmlResponse->assertSee('Catatan 7: Status Pengendalian Internal');
        $htmlResponse->assertSee('Cetak Paket Lengkap (PDF)');
        $htmlResponse->assertSee('Manajer Keuangan');

        $jsonResponse = $this->getJson(route('admin.akuntansi.calk'));
        $jsonResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'journal_count' => 1,
                'laba_rugi' => [
                    'total_revenue' => 500000.0,
                    'net_profit' => 500000.0,
                ],
                'neraca' => [
                    'total_assets' => 500000.0,
                    'is_balanced' => true,
                ],
                'arus_kas' => [
                    'ending_cash' => 500000.0,
                    'is_reconciled' => true,
                ],
            ]);
    }

    public function test_admin_can_export_calk_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.calk', [
            'export' => 'csv',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
