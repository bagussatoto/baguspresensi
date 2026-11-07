<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
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
}
