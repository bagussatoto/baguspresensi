<?php

declare(strict_types=1);

namespace App\Accounting\Controllers;

use App\Accounting\Services\FinancialReportOptimizationService;
use App\Http\Controllers\Admin\AdminAccountingController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\Branch;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Akuntansi SAK Teroptimasi (Enterprise Layer).
 * Menggantikan query loop N+1 dengan Single-Pass SQL Aggregation.
 */
class AppAdminAccountingController extends AdminAccountingController
{
    public function __construct(
        AutoJournalService $journalService,
        protected FinancialReportOptimizationService $optimizationService
    ) {
        parent::__construct($journalService);
    }

    /**
     * 3. Halaman Neraca Saldo (Trial Balance) Teroptimasi.
     */
    public function neracaSaldoIndex(Request $request): View|JsonResponse|StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfYear()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $selectedBranchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;

        $accounts = ChartOfAccount::where('is_header', false)->orderBy('account_code')->get();

        // Single-Pass SQL Aggregation (Menggantikan 80+ Query Looping N+1)
        $mutations = $this->optimizationService->getAccountMutations($startDate, $endDate, $selectedBranchId);

        $trialRows = [];
        $grandTotalDebit = 0.0;
        $grandTotalCredit = 0.0;

        $categoryTotals = [
            'asset' => 0.0,
            'liability' => 0.0,
            'equity' => 0.0,
            'revenue' => 0.0,
            'expense' => 0.0,
        ];

        foreach ($accounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);

            $isDebit = ($acc->normal_balance === NormalBalance::DEBIT || $acc->normal_balance->value === 'debit');
            $balance = (float) $acc->opening_balance;

            if ($isDebit) {
                $balance += ($sumDebit - $sumCredit);
            } else {
                $balance += ($sumCredit - $sumDebit);
            }

            $finalDebit = $isDebit ? max(0, $balance) : 0;
            $finalCredit = ! $isDebit ? max(0, $balance) : 0;

            if ($balance < 0) {
                if ($isDebit) {
                    $finalCredit = abs($balance);
                } else {
                    $finalDebit = abs($balance);
                }
            }

            $catKey = strtolower($acc->account_category->value ?? (string) $acc->account_category);
            if (isset($categoryTotals[$catKey])) {
                $categoryTotals[$catKey] += ($finalDebit > 0 ? $finalDebit : $finalCredit);
            }

            $trialRows[] = [
                'account' => $acc,
                'mutation_debit' => $sumDebit,
                'mutation_credit' => $sumCredit,
                'final_debit' => $finalDebit,
                'final_credit' => $finalCredit,
            ];

            $grandTotalDebit += $finalDebit;
            $grandTotalCredit += $finalCredit;
        }

        $isBalanced = abs($grandTotalDebit - $grandTotalCredit) < 0.01;

        if ($request->query('export') === 'csv') {
            $filename = 'neraca_saldo_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use ($trialRows, $startDate, $endDate, $grandTotalDebit, $grandTotalCredit, $isBalanced) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['NERACA SALDO (TRIAL BALANCE) MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Periode', $startDate.' s/d '.$endDate]);
                fputcsv($handle, ['Status Keseimbangan', $isBalanced ? 'SEIMBANG (BALANCED)' : 'SELISIH']);
                fputcsv($handle, []);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Kategori', 'Mutasi Debit', 'Mutasi Kredit', 'Saldo Akhir Debit', 'Saldo Akhir Kredit']);
                foreach ($trialRows as $row) {
                    if ($row['mutation_debit'] > 0 || $row['mutation_credit'] > 0 || $row['final_debit'] > 0 || $row['final_credit'] > 0) {
                        fputcsv($handle, [
                            $row['account']->account_code,
                            $row['account']->account_name,
                            strtoupper($row['account']->account_category->value ?? (string) $row['account']->account_category),
                            (string) $row['mutation_debit'],
                            (string) $row['mutation_credit'],
                            (string) $row['final_debit'],
                            (string) $row['final_credit'],
                        ]);
                    }
                }
                fputcsv($handle, ['TOTAL', '', '', '', '', (string) $grandTotalDebit, (string) $grandTotalCredit]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_balanced' => $isBalanced,
                'grand_total_debit' => $grandTotalDebit,
                'grand_total_credit' => $grandTotalCredit,
                'rows' => $trialRows,
            ]);
        }

        return view('admin.accounting.neraca_saldo', compact(
            'startDate',
            'endDate',
            'branches',
            'selectedBranchId',
            'trialRows',
            'categoryTotals',
            'grandTotalDebit',
            'grandTotalCredit',
            'isBalanced'
        ));
    }

    /**
     * 4. Halaman Laporan Laba Rugi (Income Statement) Teroptimasi.
     */
    public function labaRugiIndex(Request $request): View|JsonResponse|StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $selectedBranchId = $request->filled('branch_id') && $request->input('branch_id') !== 'all'
            ? (int) $request->input('branch_id')
            : null;

        // Single-Pass SQL Aggregation
        $mutations = $this->optimizationService->getAccountMutations($startDate, $endDate, $selectedBranchId);

        // 1. PENDAPATAN (REVENUE - 4-xxxxx)
        $revenueAccounts = ChartOfAccount::where('account_category', 'revenue')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $revenueList = [];
        $totalRevenue = 0.0;
        foreach ($revenueAccounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);
            $amt = $sumCredit - $sumDebit;
            $revenueList[] = ['account' => $acc, 'amount' => $amt];
            $totalRevenue += $amt;
        }

        // 2. HARGA POKOK PENJUALAN (COGS - 5-xxxxx)
        $cogsAccounts = ChartOfAccount::where('account_category', 'cogs')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $cogsList = [];
        $totalCogs = 0.0;
        foreach ($cogsAccounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);
            $amt = $sumDebit - $sumCredit;
            $cogsList[] = ['account' => $acc, 'amount' => $amt];
            $totalCogs += $amt;
        }

        // LABA KOTOR (GROSS PROFIT)
        $grossProfit = $totalRevenue - $totalCogs;

        // 3. BEBAN OPERASIONAL (EXPENSE - 6-xxxxx)
        $expenseAccounts = ChartOfAccount::where('account_category', 'expense')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $expenseList = [];
        $totalExpense = 0.0;
        foreach ($expenseAccounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);
            $amt = $sumDebit - $sumCredit;
            $expenseList[] = ['account' => $acc, 'amount' => $amt];
            $totalExpense += $amt;
        }

        // LABA BERSIH OPERASIONAL (NET PROFIT)
        $netProfit = $grossProfit - $totalExpense;

        if ($request->query('export') === 'csv') {
            $branchName = $selectedBranchId ? (Branch::find($selectedBranchId)?->name ?? 'Cabang '.$selectedBranchId) : 'Konsolidasi Seluruh Cabang';
            $filename = 'laba_rugi_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use ($revenueList, $totalRevenue, $cogsList, $totalCogs, $grossProfit, $expenseList, $totalExpense, $netProfit, $startDate, $endDate, $branchName) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['LAPORAN LABA RUGI (INCOME STATEMENT) MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Unit/Cabang', $branchName]);
                fputcsv($handle, ['Periode', $startDate.' s/d '.$endDate]);
                fputcsv($handle, []);

                fputcsv($handle, ['1. PENDAPATAN USAHA (REVENUE)']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Jumlah (Rp)']);
                foreach ($revenueList as $item) {
                    if ($item['amount'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['amount']]);
                    }
                }
                fputcsv($handle, ['TOTAL PENDAPATAN USAHA', '', (string) $totalRevenue]);
                fputcsv($handle, []);

                fputcsv($handle, ['2. HARGA POKOK PENJUALAN (COGS)']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Jumlah (Rp)']);
                foreach ($cogsList as $item) {
                    if ($item['amount'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['amount']]);
                    }
                }
                fputcsv($handle, ['TOTAL HARGA POKOK PENJUALAN', '', (string) $totalCogs]);
                fputcsv($handle, []);

                fputcsv($handle, ['LABA KOTOR (GROSS PROFIT)', '', (string) $grossProfit]);
                fputcsv($handle, []);

                fputcsv($handle, ['3. BEBAN OPERASIONAL (EXPENSES)']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Jumlah (Rp)']);
                foreach ($expenseList as $item) {
                    if ($item['amount'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['amount']]);
                    }
                }
                fputcsv($handle, ['TOTAL BEBAN OPERASIONAL', '', (string) $totalExpense]);
                fputcsv($handle, []);

                fputcsv($handle, ['LABA BERSIH OPERASIONAL (NET PROFIT)', '', (string) $netProfit]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'selected_branch_id' => $selectedBranchId,
                'total_revenue' => $totalRevenue,
                'total_cogs' => $totalCogs,
                'gross_profit' => $grossProfit,
                'total_expense' => $totalExpense,
                'net_profit' => $netProfit,
            ]);
        }

        return view('admin.accounting.laba_rugi', compact(
            'startDate',
            'endDate',
            'branches',
            'selectedBranchId',
            'revenueList',
            'totalRevenue',
            'cogsList',
            'totalCogs',
            'grossProfit',
            'expenseList',
            'totalExpense',
            'netProfit'
        ));
    }

    /**
     * 5. Halaman Neraca Keuangan (Balance Sheet) Teroptimasi.
     */
    public function neracaIndex(Request $request): View|JsonResponse|StreamedResponse
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->input('as_of_date'))->toDateString()
            : Carbon::today()->toDateString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $selectedBranchId = $request->filled('branch_id') && $request->input('branch_id') !== 'all'
            ? (int) $request->input('branch_id')
            : null;

        // Single-Pass SQL Aggregation kumulatif sampai asOfDate
        $mutations = $this->optimizationService->getAccountMutations(null, $asOfDate, $selectedBranchId);

        // 1. ASET (1-xxxxx)
        $assetAccounts = ChartOfAccount::where('account_category', 'asset')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $assetList = [];
        $totalAssets = 0.0;
        foreach ($assetAccounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);

            $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
            $bal = $openingBal + ($sumDebit - $sumCredit);
            $assetList[] = ['account' => $acc, 'balance' => $bal];
            $totalAssets += $bal;
        }

        // 2. KEWAJIBAN (2-xxxxx)
        $liabilityAccounts = ChartOfAccount::where('account_category', 'liability')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $liabilityList = [];
        $totalLiabilities = 0.0;
        foreach ($liabilityAccounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);

            $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
            $bal = $openingBal + ($sumCredit - $sumDebit);
            $liabilityList[] = ['account' => $acc, 'balance' => $bal];
            $totalLiabilities += $bal;
        }

        // 3. EKUITAS (3-xxxxx)
        $equityAccounts = ChartOfAccount::where('account_category', 'equity')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $equityList = [];
        $totalEquity = 0.0;
        foreach ($equityAccounts as $acc) {
            $sumDebit = (float) ($mutations[$acc->id]['debit'] ?? 0.0);
            $sumCredit = (float) ($mutations[$acc->id]['credit'] ?? 0.0);

            $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
            $bal = $openingBal + ($sumCredit - $sumDebit);
            $equityList[] = ['account' => $acc, 'balance' => $bal];
            $totalEquity += $bal;
        }

        // Hitung Laba Periode Berjalan Kumulatif langsung dari mutasi agregasi
        $revenueAccountsAll = ChartOfAccount::where('account_category', 'revenue')->pluck('id')->all();
        $cogsAccountsAll = ChartOfAccount::where('account_category', 'cogs')->pluck('id')->all();
        $expenseAccountsAll = ChartOfAccount::where('account_category', 'expense')->pluck('id')->all();

        $totRev = 0.0;
        foreach ($revenueAccountsAll as $accId) {
            $totRev += ((float) ($mutations[$accId]['credit'] ?? 0) - (float) ($mutations[$accId]['debit'] ?? 0));
        }

        $totCogs = 0.0;
        foreach ($cogsAccountsAll as $accId) {
            $totCogs += ((float) ($mutations[$accId]['debit'] ?? 0) - (float) ($mutations[$accId]['credit'] ?? 0));
        }

        $totExp = 0.0;
        foreach ($expenseAccountsAll as $accId) {
            $totExp += ((float) ($mutations[$accId]['debit'] ?? 0) - (float) ($mutations[$accId]['credit'] ?? 0));
        }

        $currentPeriodNetProfit = $totRev - $totCogs - $totExp;
        $totalEquityWithProfit = $totalEquity + $currentPeriodNetProfit;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquityWithProfit;

        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 1.0;

        if ($request->query('export') === 'csv') {
            $branchName = $selectedBranchId ? (Branch::find($selectedBranchId)?->name ?? 'Cabang '.$selectedBranchId) : 'Konsolidasi Seluruh Cabang';
            $filename = 'neraca_keuangan_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use ($assetList, $totalAssets, $liabilityList, $totalLiabilities, $equityList, $totalEquity, $currentPeriodNetProfit, $totalEquityWithProfit, $totalLiabilitiesAndEquity, $asOfDate, $branchName, $isBalanced) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['NERACA KEUANGAN (BALANCE SHEET) MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Unit/Cabang', $branchName]);
                fputcsv($handle, ['Posisi Per Tanggal', $asOfDate]);
                fputcsv($handle, ['Status Persamaan Akuntansi', $isBalanced ? 'SEIMBANG (ASET = KEWAJIBAN + EKUITAS)' : 'SELISIH']);
                fputcsv($handle, []);

                fputcsv($handle, ['1. AKTIVA / ASET']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Saldo Akhir (Rp)']);
                foreach ($assetList as $item) {
                    if ($item['balance'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['balance']]);
                    }
                }
                fputcsv($handle, ['TOTAL ASET', '', (string) $totalAssets]);
                fputcsv($handle, []);

                fputcsv($handle, ['2. KEWAJIBAN / LIABILITAS']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Saldo Akhir (Rp)']);
                foreach ($liabilityList as $item) {
                    if ($item['balance'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['balance']]);
                    }
                }
                fputcsv($handle, ['TOTAL KEWAJIBAN', '', (string) $totalLiabilities]);
                fputcsv($handle, []);

                fputcsv($handle, ['3. EKUITAS / MODAL']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Saldo Akhir (Rp)']);
                foreach ($equityList as $item) {
                    if ($item['balance'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['balance']]);
                    }
                }
                fputcsv($handle, ['Laba Bersih Berjalan Kumulatif', '', (string) $currentPeriodNetProfit]);
                fputcsv($handle, ['TOTAL EKUITAS', '', (string) $totalEquityWithProfit]);
                fputcsv($handle, []);

                fputcsv($handle, ['TOTAL KEWAJIBAN & EKUITAS', '', (string) $totalLiabilitiesAndEquity]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'as_of_date' => $asOfDate,
                'selected_branch_id' => $selectedBranchId,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquityWithProfit,
                'is_balanced' => $isBalanced,
            ]);
        }

        return view('admin.accounting.neraca', compact(
            'asOfDate',
            'branches',
            'selectedBranchId',
            'assetList',
            'totalAssets',
            'liabilityList',
            'totalLiabilities',
            'equityList',
            'totalEquity',
            'currentPeriodNetProfit',
            'totalEquityWithProfit',
            'totalLiabilitiesAndEquity',
            'isBalanced'
        ));
    }
}
