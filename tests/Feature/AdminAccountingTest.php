<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ShiftSalesSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
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
    }

    public function test_admin_can_access_neraca_saldo(): void
    {
        $response = $this->get(route('admin.akuntansi.neraca-saldo'));

        $response->assertStatus(200);
        $response->assertSee('Neraca Saldo (Trial Balance)');
        $response->assertSee('PEMBUKUAN DINYATAKAN SEIMBANG');
    }

    public function test_admin_can_access_laba_rugi(): void
    {
        $response = $this->get(route('admin.akuntansi.laba-rugi'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Laba Rugi');
        $response->assertSee('LABA KOTOR (GROSS PROFIT)');
        $response->assertSee('LABA BERSIH OPERASIONAL');
    }

    public function test_admin_can_access_neraca_keuangan(): void
    {
        $response = $this->get(route('admin.akuntansi.neraca'));

        $response->assertStatus(200);
        $response->assertSee('Neraca Keuangan (Balance Sheet)');
        $response->assertSee('AKTIVA / ASET PERUSAHAAN');
        $response->assertSee('PASIVA (KEWAJIBAN');
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
}
