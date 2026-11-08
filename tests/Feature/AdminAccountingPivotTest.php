<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Models\JournalEntryLine;
use Modules\MasterData\Domain\Models\Branch;
use Tests\TestCase;

class AdminAccountingPivotTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected ChartOfAccount $cashAccount;

    protected ChartOfAccount $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Sudirman',
            'code' => 'SDR',
            'is_active' => true,
        ]);

        $this->cashAccount = ChartOfAccount::create([
            'account_code' => '1-10001',
            'account_name' => 'Kas Laci Kasir Sudirman',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'opening_balance' => 500000,
            'current_balance' => 500000,
        ]);

        $this->revenueAccount = ChartOfAccount::create([
            'account_code' => '4-10001',
            'account_name' => 'Pendapatan Penjualan Kopi',
            'account_category' => AccountCategory::REVENUE,
            'normal_balance' => NormalBalance::CREDIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        // Buat satu transaksi jurnal posted
        $entry = JournalEntry::create([
            'entry_number' => 'JRN-202611-0001',
            'entry_date' => Carbon::now()->toDateString(),
            'reference_type' => 'pos_sale',
            'branch_id' => $this->branch->id,
            'description' => 'Penjualan Kopi POS Kasir',
            'status' => 'posted',
            'total_debit' => 150000,
            'total_credit' => 150000,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $this->cashAccount->id,
            'debit' => 150000,
            'credit' => 0,
            'memo' => 'Penerimaan Kas Kopi',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $this->revenueAccount->id,
            'debit' => 0,
            'credit' => 150000,
            'memo' => 'Pendapatan Kopi Kasir',
        ]);
    }

    public function test_admin_can_access_accounting_pivot_page(): void
    {
        $response = $this->get(route('admin.akuntansi.pivot'));

        $response->assertStatus(200);
        $response->assertSee('Pivot Analisis Transaksi SAK');
        $response->assertSee('Kas Laci Kasir Sudirman');
        $response->assertSee('150.000');
    }

    public function test_admin_can_change_pivot_dimensions_and_metrics(): void
    {
        $response = $this->get(route('admin.akuntansi.pivot', [
            'row_dim' => 'category',
            'col_dim' => 'branch',
            'metric' => 'both',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Matriks Pivot Transaksi');
        $response->assertSee('1. Aset (Assets)');
        $response->assertSee('Cabang Sudirman');
    }

    public function test_admin_can_query_pivot_as_json_api(): void
    {
        $response = $this->getJson(route('admin.akuntansi.pivot', [
            'row_dim' => 'account',
            'col_dim' => 'branch',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'start_date',
            'end_date',
            'row_dim',
            'col_dim',
            'metric',
            'columns',
            'rows',
            'matrix',
            'summary' => [
                'grand_total_debit',
                'grand_total_credit',
                'grand_total_net',
                'grand_total_count',
            ],
        ]);

        $this->assertEquals(150000, $response->json('summary.grand_total_debit'));
        $this->assertEquals(150000, $response->json('summary.grand_total_credit'));
    }

    public function test_admin_can_export_pivot_table_to_csv(): void
    {
        $response = $this->get(route('admin.akuntansi.pivot', ['format' => 'csv']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('pivot_transaksi_akuntansi_', (string) $response->headers->get('content-disposition'));
    }
}
