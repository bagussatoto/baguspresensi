<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Accounting\Controllers\AppAdminAccountingController;
use App\Accounting\Services\FinancialReportOptimizationService;
use App\Http\Controllers\Admin\AdminAccountingController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Models\JournalEntryLine;
use Modules\MasterData\Domain\Models\Branch;
use Tests\TestCase;

class FinancialReportOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-TEST-ACC',
            'name' => 'Cabang Test Akuntansi',
            'is_headquarters' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Uji bahwa controller AdminAccountingController di-resolve ke AppAdminAccountingController.
     */
    public function test_admin_accounting_controller_resolves_to_optimized_app_controller(): void
    {
        $controller = app(AdminAccountingController::class);

        $this->assertInstanceOf(AppAdminAccountingController::class, $controller);
    }

    /**
     * Uji bahwa service FinancialReportOptimizationService menghasilkan mutasi agregasi yang akurat.
     */
    public function test_optimization_service_aggregates_account_mutations_accurately(): void
    {
        $service = app(FinancialReportOptimizationService::class);

        $cash = ChartOfAccount::create([
            'account_code' => '1-10001',
            'account_name' => 'Kas Laci Kasir',
            'account_category' => 'asset',
            'normal_balance' => 'debit',
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
        ]);

        $revenue = ChartOfAccount::create([
            'account_code' => '4-10001',
            'account_name' => 'Pendapatan FnB',
            'account_category' => 'revenue',
            'normal_balance' => 'credit',
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
        ]);

        $entry = JournalEntry::create([
            'entry_number' => 'JRN-TEST-001',
            'entry_date' => now()->toDateString(),
            'branch_id' => $this->branch->id,
            'description' => 'Test Jurnal Penjualan',
            'status' => 'posted',
            'total_debit' => 150000.0,
            'total_credit' => 150000.0,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $cash->id,
            'debit' => 150000.0,
            'credit' => 0.0,
            'memo' => 'Debit Kas Laci',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $revenue->id,
            'debit' => 0.0,
            'credit' => 150000.0,
            'memo' => 'Kredit Pendapatan',
        ]);

        $mutations = $service->getAccountMutations(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString());

        $this->assertArrayHasKey($cash->id, $mutations);
        $this->assertEquals(150000.0, $mutations[$cash->id]['debit']);
        $this->assertEquals(0.0, $mutations[$cash->id]['credit']);

        $this->assertArrayHasKey($revenue->id, $mutations);
        $this->assertEquals(0.0, $mutations[$revenue->id]['debit']);
        $this->assertEquals(150000.0, $mutations[$revenue->id]['credit']);
    }
}
