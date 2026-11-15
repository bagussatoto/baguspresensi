<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\AccountingPeriod;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Models\JournalEntryLine;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\Branch;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Admin Akuntansi, Buku Besar, dan Laporan Keuangan Formal SAK.
 * Melayani:
 * - /admin/akuntansi/jurnal (Jurnal Umum)
 * - /admin/akuntansi/buku-besar (General Ledger)
 * - /admin/akuntansi/neraca-saldo (Trial Balance)
 * - /admin/akuntansi/laba-rugi (Income Statement / P&L)
 * - /admin/akuntansi/neraca (Balance Sheet)
 */
class AdminAccountingController extends Controller
{
    public function __construct(
        protected AutoJournalService $journalService
    ) {}

    /**
     * 1. Halaman Jurnal Umum (General Journal).
     */
    public function jurnalIndex(Request $request): View|JsonResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        $query = JournalEntry::with(['lines.chartOfAccount', 'branch', 'creator'])
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('entry_number', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $entries = $query->paginate(25)->withQueryString();
        $totalDebit = (float) JournalEntry::whereBetween('entry_date', [$startDate, $endDate])->sum('total_debit');
        $totalCredit = (float) JournalEntry::whereBetween('entry_date', [$startDate, $endDate])->sum('total_credit');

        $accounts = ChartOfAccount::where('is_header', false)->where('is_active', true)->orderBy('account_code')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'entries' => $entries,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);
        }

        return view('admin.accounting.jurnal', compact(
            'entries',
            'startDate',
            'endDate',
            'totalDebit',
            'totalCredit',
            'accounts',
            'branches'
        ));
    }

    /**
     * 2. Halaman Buku Besar (General Ledger).
     */
    public function bukuBesarIndex(Request $request): View|JsonResponse|StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $selectedBranchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;

        $accounts = ChartOfAccount::where('is_header', false)->where('is_active', true)->orderBy('account_code')->get();

        // Pilih akun pertama sebagai default jika tidak dipilih
        $selectedAccountId = $request->filled('account_id')
            ? (int) $request->input('account_id')
            : ($accounts->first()?->id ?? 0);

        $selectedAccount = $selectedAccountId > 0 ? ChartOfAccount::find($selectedAccountId) : null;

        $openingBalance = 0.0;
        $mutasiLines = collect();
        $endingBalance = 0.0;
        $totalDebitMutasi = 0.0;
        $totalCreditMutasi = 0.0;

        if ($selectedAccount) {
            $isDebit = ($selectedAccount->normal_balance === NormalBalance::DEBIT || $selectedAccount->normal_balance->value === 'debit');

            // 1. Hitung Saldo Awal sebelum tanggal start_date
            $priorLines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<', $startDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $selectedAccount->id)->get();

            $priorDebit = (float) $priorLines->sum('debit');
            $priorCredit = (float) $priorLines->sum('credit');

            $openingBalance = (float) $selectedAccount->opening_balance;
            if ($isDebit) {
                $openingBalance += ($priorDebit - $priorCredit);
            } else {
                $openingBalance += ($priorCredit - $priorDebit);
            }

            // 2. Ambil baris mutasi dalam rentang tanggal
            $mutasiLines = JournalEntryLine::with(['journalEntry'])
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })
                ->where('chart_of_account_id', $selectedAccount->id)
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->orderBy('journal_entries.entry_date', 'asc')
                ->orderBy('journal_entries.id', 'asc')
                ->select('journal_entry_lines.*')
                ->get();

            $runningBalance = $openingBalance;
            foreach ($mutasiLines as $line) {
                $d = (float) $line->debit;
                $c = (float) $line->credit;
                $totalDebitMutasi += $d;
                $totalCreditMutasi += $c;

                if ($isDebit) {
                    $runningBalance += ($d - $c);
                } else {
                    $runningBalance += ($c - $d);
                }
                $line->running_balance = $runningBalance;
            }

            $endingBalance = $runningBalance;
        }

        if ($request->query('export') === 'csv') {
            $accCodeSafe = $selectedAccount ? str_replace([' ', '/', '\\'], '_', $selectedAccount->account_code) : 'all';
            $filename = 'buku_besar_'.$accCodeSafe.'_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use ($selectedAccount, $startDate, $endDate, $openingBalance, $mutasiLines, $endingBalance, $totalDebitMutasi, $totalCreditMutasi) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['BUKU BESAR (GENERAL LEDGER) MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Akun', ($selectedAccount?->account_code ?? '').' - '.($selectedAccount?->account_name ?? '')]);
                fputcsv($handle, ['Periode', $startDate.' s/d '.$endDate]);
                fputcsv($handle, ['Saldo Awal', (string) $openingBalance]);
                fputcsv($handle, []);
                fputcsv($handle, ['Tanggal', 'No. Jurnal', 'Keterangan', 'Memo', 'Debit', 'Kredit', 'Saldo Berjalan']);
                foreach ($mutasiLines as $line) {
                    fputcsv($handle, [
                        $line->journalEntry->entry_date->format('Y-m-d'),
                        $line->journalEntry->entry_number,
                        $line->journalEntry->description,
                        $line->memo ?? '',
                        (string) $line->debit,
                        (string) $line->credit,
                        (string) $line->running_balance,
                    ]);
                }
                fputcsv($handle, ['TOTAL', '', '', '', (string) $totalDebitMutasi, (string) $totalCreditMutasi, (string) $endingBalance]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'account' => $selectedAccount,
                'opening_balance' => $openingBalance,
                'ending_balance' => $endingBalance,
                'total_debit' => $totalDebitMutasi,
                'total_credit' => $totalCreditMutasi,
                'lines' => $mutasiLines,
            ]);
        }

        return view('admin.accounting.buku_besar', compact(
            'accounts',
            'branches',
            'selectedBranchId',
            'selectedAccountId',
            'selectedAccount',
            'startDate',
            'endDate',
            'openingBalance',
            'endingBalance',
            'totalDebitMutasi',
            'totalCreditMutasi',
            'mutasiLines'
        ));
    }

    /**
     * 3. Halaman Neraca Saldo (Trial Balance).
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            $sumDebit = (float) $lines->sum('debit');
            $sumCredit = (float) $lines->sum('credit');

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
                // Posisi terbalik
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
     * 4. Halaman Laporan Laba Rugi (Income Statement / P&L).
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

        // 1. PENDAPATAN (REVENUE - 4-xxxxx)
        $revenueAccounts = ChartOfAccount::where('account_category', 'revenue')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $revenueList = [];
        $totalRevenue = 0.0;
        foreach ($revenueAccounts as $acc) {
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            // Pendapatan saldo normal kredit: Kredit - Debit
            $amt = (float) ($lines->sum('credit') - $lines->sum('debit'));
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            // HPP saldo normal debit: Debit - Kredit
            $amt = (float) ($lines->sum('debit') - $lines->sum('credit'));
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            // Beban saldo normal debit: Debit - Kredit
            $amt = (float) ($lines->sum('debit') - $lines->sum('credit'));
            $expenseList[] = ['account' => $acc, 'amount' => $amt];
            $totalExpense += $amt;
        }

        // LABA BERSIH OPERASIONAL (NET PROFIT)
        $netProfit = $grossProfit - $totalExpense;

        // EKSPOR CSV FORMAL
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
     * 5. Halaman Neraca Keuangan (Balance Sheet).
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

        // 1. ASET (1-xxxxx)
        $assetAccounts = ChartOfAccount::where('account_category', 'asset')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $assetList = [];
        $totalAssets = 0.0;
        foreach ($assetAccounts as $acc) {
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
            $bal = $openingBal + ((float) $lines->sum('debit') - (float) $lines->sum('credit'));
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
            $bal = $openingBal + ((float) $lines->sum('credit') - (float) $lines->sum('debit'));
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->where('chart_of_account_id', $acc->id)->get();

            $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
            $bal = $openingBal + ((float) $lines->sum('credit') - (float) $lines->sum('debit'));
            $equityList[] = ['account' => $acc, 'balance' => $bal];
            $totalEquity += $bal;
        }

        // Hitung Laba Periode Berjalan Kumulatif (Pendapatan - HPP - Beban hingga asOfDate)
        $revLines = JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', 'revenue'))
            ->whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->get();

        $cogsLines = JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', 'cogs'))
            ->whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->get();

        $expLines = JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', 'expense'))
            ->whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })->get();

        $currentPeriodNetProfit = ((float) $revLines->sum('credit') - (float) $revLines->sum('debit'))
            - ((float) $cogsLines->sum('debit') - (float) $cogsLines->sum('credit'))
            - ((float) $expLines->sum('debit') - (float) $expLines->sum('credit'));

        $totalEquityWithProfit = $totalEquity + $currentPeriodNetProfit;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquityWithProfit;

        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 1.0;

        // EKSPOR CSV FORMAL
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

                fputcsv($handle, ['2. KEWAJIBAN (LIABILITIES)']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Saldo Akhir (Rp)']);
                foreach ($liabilityList as $item) {
                    if ($item['balance'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['balance']]);
                    }
                }
                fputcsv($handle, ['TOTAL KEWAJIBAN', '', (string) $totalLiabilities]);
                fputcsv($handle, []);

                fputcsv($handle, ['3. EKUITAS & MODAL (EQUITY)']);
                fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Saldo Akhir (Rp)']);
                foreach ($equityList as $item) {
                    if ($item['balance'] != 0) {
                        fputcsv($handle, [$item['account']->account_code, $item['account']->account_name, (string) $item['balance']]);
                    }
                }
                fputcsv($handle, ['SUBTOTAL EKUITAS', '', (string) $totalEquity]);
                fputcsv($handle, ['Laba Bersih Periode Berjalan', '', (string) $currentPeriodNetProfit]);
                fputcsv($handle, ['TOTAL EKUITAS TERMASUK LABA', '', (string) $totalEquityWithProfit]);
                fputcsv($handle, []);

                fputcsv($handle, ['TOTAL PASIVA (KEWAJIBAN + EKUITAS)', '', (string) $totalLiabilitiesAndEquity]);
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

    /**
     * Simpan formulir entri jurnal manual baru.
     */
    public function storeManualJournal(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.chart_of_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.memo' => ['nullable', 'string', 'max:255'],
        ]);

        $lines = $request->input('lines', []);
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Entri jurnal tidak seimbang! Total Debit (Rp '.number_format($totalDebit, 0).') harus sama dengan Total Kredit (Rp '.number_format($totalCredit, 0).').');
        }

        try {
            $entry = $this->journalService->createManualJournal([
                'entry_date' => $request->input('entry_date'),
                'description' => $request->input('description'),
                'branch_id' => $request->input('branch_id'),
                'lines' => $lines,
            ]);
        } catch (RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jurnal {$entry->entry_number} berhasil dicatat.",
                'entry' => $entry,
            ]);
        }

        return redirect()->route('admin.akuntansi.jurnal')->with('success', "Jurnal {$entry->entry_number} berhasil dicatat dan diposting ke Buku Besar.");
    }

    /**
     * 6. Halaman Pivot Table & Analisis Transaksi Akuntansi SAK Multidimensi.
     */
    public function pivotIndex(Request $request): View|JsonResponse|StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        $selectedBranchId = $request->input('branch_id', 'all');
        $selectedCategory = $request->input('category', 'all');
        $rowDim = in_array($request->input('row_dim'), ['account', 'category', 'branch', 'source_type'], true)
            ? $request->input('row_dim')
            : 'account';

        $colDim = in_array($request->input('col_dim'), ['branch', 'month', 'category', 'source_type'], true)
            ? $request->input('col_dim')
            : ($rowDim === 'branch' ? 'month' : 'branch');

        // Mencegah dimensi baris dan kolom sama persis
        if ($rowDim === $colDim) {
            $colDim = $rowDim === 'branch' ? 'month' : 'branch';
        }

        $metric = in_array($request->input('metric'), ['net', 'debit', 'credit', 'both'], true)
            ? $request->input('metric')
            : 'net';

        // Base Query Baris Jurnal
        $linesQuery = JournalEntryLine::query()
            ->with(['journalEntry.branch', 'chartOfAccount'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                $q->where('status', 'posted')
                    ->whereDate('entry_date', '>=', $startDate)
                    ->whereDate('entry_date', '<=', $endDate);

                if ($selectedBranchId !== 'all' && is_numeric($selectedBranchId)) {
                    $q->where('branch_id', (int) $selectedBranchId);
                }
            });

        if ($selectedCategory !== 'all') {
            $linesQuery->whereHas('chartOfAccount', function ($q) use ($selectedCategory): void {
                $q->where('account_category', $selectedCategory);
            });
        }

        $rawLines = $linesQuery->get();

        // Kategori Map Human-Readable
        $categoryLabels = [
            'asset' => '1. Aset (Assets)',
            'liability' => '2. Kewajiban (Liabilities)',
            'equity' => '3. Ekuitas (Equity)',
            'revenue' => '4. Pendapatan (Revenue)',
            'cogs' => '5. Beban Pokok (COGS)',
            'expense' => '6. Beban Operasional (Expenses)',
        ];

        // Tipe Sumber Transaksi Map Human-Readable
        $sourceTypeLabels = [
            'pos_sale' => 'Penjualan Kasir POS',
            'manual_journal' => 'Jurnal Penyesuaian Manual',
            'void_sale' => 'Pembatalan Transaksi POS',
            'cash_handover' => 'Serah Terima Kasir Shift',
            'cash_deposit' => 'Setoran Kas ke Bank/Pusat',
            'closing_settlement' => 'Settlement Kas Harian',
            'inventory_adjustment' => 'Penyesuaian Stok Gudang',
        ];

        // Build Distinct Columns & Rows
        $matrix = [];
        $columns = [];
        $rows = [];

        $grandTotalDebit = 0.0;
        $grandTotalCredit = 0.0;
        $grandTotalNet = 0.0;
        $grandTotalCount = 0;

        foreach ($rawLines as $line) {
            $entry = $line->journalEntry;
            $acc = $line->chartOfAccount;
            if (! $entry || ! $acc) {
                continue;
            }

            // Extract Row Key & Label
            $rowKey = match ($rowDim) {
                'account' => $acc->account_code,
                'category' => (string) ($acc->account_category?->value ?? $acc->account_category),
                'branch' => (string) ($entry->branch_id ?? '0'),
                'source_type' => (string) ($entry->reference_type ?? 'manual_journal'),
            };

            $rowLabel = match ($rowDim) {
                'account' => "[{$acc->account_code}] {$acc->account_name}",
                'category' => $categoryLabels[(string) ($acc->account_category?->value ?? $acc->account_category)] ?? ucfirst((string) $acc->account_category),
                'branch' => $entry->branch?->name ?? 'Kantor Pusat / Umum',
                'source_type' => $sourceTypeLabels[(string) ($entry->reference_type ?? 'manual_journal')] ?? ucfirst(str_replace('_', ' ', (string) $entry->reference_type)),
            };

            // Extract Col Key & Label
            $colKey = match ($colDim) {
                'branch' => (string) ($entry->branch_id ?? '0'),
                'month' => Carbon::parse($entry->entry_date)->format('Y-m'),
                'category' => (string) ($acc->account_category?->value ?? $acc->account_category),
                'source_type' => (string) ($entry->reference_type ?? 'manual_journal'),
            };

            $colLabel = match ($colDim) {
                'branch' => $entry->branch?->name ?? 'Kantor Pusat / Umum',
                'month' => Carbon::parse($entry->entry_date)->translatedFormat('M Y'),
                'category' => $categoryLabels[(string) ($acc->account_category?->value ?? $acc->account_category)] ?? ucfirst((string) $acc->account_category),
                'source_type' => $sourceTypeLabels[(string) ($entry->reference_type ?? 'manual_journal')] ?? ucfirst(str_replace('_', ' ', (string) $entry->reference_type)),
            };

            if (! isset($rows[$rowKey])) {
                $rows[$rowKey] = [
                    'key' => $rowKey,
                    'label' => $rowLabel,
                    'category' => (string) ($acc->account_category?->value ?? $acc->account_category),
                    'total_debit' => 0.0,
                    'total_credit' => 0.0,
                    'total_net' => 0.0,
                    'count' => 0,
                ];
            }

            if (! isset($columns[$colKey])) {
                $columns[$colKey] = [
                    'key' => $colKey,
                    'label' => $colLabel,
                    'total_debit' => 0.0,
                    'total_credit' => 0.0,
                    'total_net' => 0.0,
                    'count' => 0,
                ];
            }

            if (! isset($matrix[$rowKey][$colKey])) {
                $matrix[$rowKey][$colKey] = [
                    'debit' => 0.0,
                    'credit' => 0.0,
                    'net' => 0.0,
                    'count' => 0,
                ];
            }

            $d = (float) $line->debit;
            $c = (float) $line->credit;

            // Perhitungan Net SAK:
            // Untuk Aset, Beban Pokok (COGS), dan Beban: normal Debit (Debit - Kredit)
            // Untuk Kewajiban, Ekuitas, Pendapatan: normal Kredit (Kredit - Debit)
            $cat = (string) ($acc->account_category?->value ?? $acc->account_category);
            $isDebitNormal = in_array($cat, ['asset', 'cogs', 'expense'], true);
            $netMovement = $isDebitNormal ? ($d - $c) : ($c - $d);

            $matrix[$rowKey][$colKey]['debit'] += $d;
            $matrix[$rowKey][$colKey]['credit'] += $c;
            $matrix[$rowKey][$colKey]['net'] += $netMovement;
            $matrix[$rowKey][$colKey]['count']++;

            $rows[$rowKey]['total_debit'] += $d;
            $rows[$rowKey]['total_credit'] += $c;
            $rows[$rowKey]['total_net'] += $netMovement;
            $rows[$rowKey]['count']++;

            $columns[$colKey]['total_debit'] += $d;
            $columns[$colKey]['total_credit'] += $c;
            $columns[$colKey]['total_net'] += $netMovement;
            $columns[$colKey]['count']++;

            $grandTotalDebit += $d;
            $grandTotalCredit += $c;
            $grandTotalNet += $netMovement;
            $grandTotalCount++;
        }

        // Sort rows by key/code
        ksort($rows);
        ksort($columns);

        // Ekspor ke CSV jika parameter format=csv
        if ($request->input('format') === 'csv') {
            $fileName = 'pivot_transaksi_akuntansi_'.$startDate.'_sd_'.$endDate.'.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ];

            $callback = function () use ($rows, $columns, $matrix, $metric, $rowDim, $grandTotalDebit, $grandTotalCredit, $grandTotalNet): void {
                $handle = fopen('php://output', 'w');
                // BOM UTF-8 for Excel compatibility
                fwrite($handle, "\xEF\xBB\xBF");

                // Header Row
                $csvHeaders = [ucwords(str_replace('_', ' ', $rowDim))];
                foreach ($columns as $col) {
                    $csvHeaders[] = $col['label'];
                }
                $csvHeaders[] = 'Total Debit (Rp)';
                $csvHeaders[] = 'Total Kredit (Rp)';
                $csvHeaders[] = 'Total Mutasi Netto (Rp)';
                fputcsv($handle, $csvHeaders);

                // Data Rows
                foreach ($rows as $rKey => $row) {
                    $rowVals = [$row['label']];
                    foreach ($columns as $cKey => $col) {
                        $cell = $matrix[$rKey][$cKey] ?? ['debit' => 0, 'credit' => 0, 'net' => 0];
                        $val = match ($metric) {
                            'debit' => $cell['debit'],
                            'credit' => $cell['credit'],
                            'net' => $cell['net'],
                            default => "D: {$cell['debit']} | K: {$cell['credit']}",
                        };
                        $rowVals[] = is_numeric($val) ? number_format((float) $val, 2, ',', '.') : $val;
                    }
                    $rowVals[] = number_format($row['total_debit'], 2, ',', '.');
                    $rowVals[] = number_format($row['total_credit'], 2, ',', '.');
                    $rowVals[] = number_format($row['total_net'], 2, ',', '.');
                    fputcsv($handle, $rowVals);
                }

                // Summary Row
                $summaryRow = ['GRAND TOTAL'];
                foreach ($columns as $col) {
                    $colVal = match ($metric) {
                        'debit' => $col['total_debit'],
                        'credit' => $col['total_credit'],
                        'net' => $col['total_net'],
                        default => "D: {$col['total_debit']} | K: {$col['total_credit']}",
                    };
                    $summaryRow[] = is_numeric($colVal) ? number_format((float) $colVal, 2, ',', '.') : $colVal;
                }
                $summaryRow[] = number_format($grandTotalDebit, 2, ',', '.');
                $summaryRow[] = number_format($grandTotalCredit, 2, ',', '.');
                $summaryRow[] = number_format($grandTotalNet, 2, ',', '.');
                fputcsv($handle, $summaryRow);

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'row_dim' => $rowDim,
                'col_dim' => $colDim,
                'metric' => $metric,
                'columns' => array_values($columns),
                'rows' => array_values($rows),
                'matrix' => $matrix,
                'summary' => [
                    'grand_total_debit' => $grandTotalDebit,
                    'grand_total_credit' => $grandTotalCredit,
                    'grand_total_net' => $grandTotalNet,
                    'grand_total_count' => $grandTotalCount,
                ],
            ]);
        }

        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounting.pivot', compact(
            'startDate',
            'endDate',
            'selectedBranchId',
            'selectedCategory',
            'rowDim',
            'colDim',
            'metric',
            'branches',
            'categoryLabels',
            'columns',
            'rows',
            'matrix',
            'grandTotalDebit',
            'grandTotalCredit',
            'grandTotalNet',
            'grandTotalCount'
        ));
    }

    /**
     * 7. Halaman Tutup Buku & Kunci Periode Akuntansi (Period Closing).
     */
    public function tutupBukuIndex(Request $request): View|JsonResponse
    {
        $periods = AccountingPeriod::with(['branch', 'closedBy', 'reopenedBy', 'closingJournalEntry'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $closedCount = $periods->where('status', 'closed')->count();
        $openCount = $periods->where('status', 'open')->count();
        $currentMonthLocked = AccountingPeriod::isDateLocked(Carbon::today()->toDateString());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'closed_count' => $closedCount,
                'open_count' => $openCount,
                'current_month_locked' => $currentMonthLocked,
                'periods' => $periods,
            ]);
        }

        return view('admin.accounting.tutup_buku', compact(
            'periods',
            'branches',
            'closedCount',
            'openCount',
            'currentMonthLocked'
        ));
    }

    /**
     * Proses tutup buku dan penguncian periode akuntansi.
     */
    public function closePeriod(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'generate_closing_journal' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $generateClosingJournal = $request->boolean('generate_closing_journal', false);

        try {
            $period = $this->journalService->closePeriod(
                (int) $request->input('year'),
                (int) $request->input('month'),
                $branchId,
                auth()->id(),
                $generateClosingJournal,
                $request->input('notes')
            );
        } catch (RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }

        $msg = "Periode {$period->period_name} berhasil ditutup & dikunci.".($period->closing_journal_entry_id ? ' Jurnal penutup otomatis telah dibuat.' : '');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'period' => $period]);
        }

        return redirect()->route('admin.akuntansi.tutup-buku')->with('success', $msg);
    }

    /**
     * Proses buka kembali periode akuntansi yang telah ditutup (Audit Controlled Reopen).
     */
    public function reopenPeriod(Request $request, AccountingPeriod $period): RedirectResponse|JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            $period = $this->journalService->reopenPeriod(
                $period,
                auth()->id(),
                (string) $request->input('reason')
            );
        } catch (RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        $msg = "Periode {$period->period_name} berhasil dibuka kembali (Reopened) dengan catatan audit.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'period' => $period]);
        }

        return redirect()->route('admin.akuntansi.tutup-buku')->with('success', $msg);
    }

    /**
     * 8. Halaman Laporan Arus Kas (Cash Flow Statement - Metode Langsung PSAK 202 / SAK EP).
     */
    public function arusKasIndex(Request $request): View|JsonResponse|StreamedResponse
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

        // Identifikasi akun Kas & Setara Kas (1-10xxx dan 1-11xxx)
        $cashAccounts = ChartOfAccount::where('is_header', false)
            ->where(function ($q): void {
                $q->where('account_code', 'like', '1-10%')
                    ->orWhere('account_code', 'like', '1-11%');
            })
            ->orderBy('account_code')
            ->get();

        $cashAccountIds = $cashAccounts->pluck('id')->all();

        // Hitung Saldo Kas Awal (sebelum $startDate) & Saldo Akhir (hingga $endDate) per akun
        $cashAccountSummaries = [];
        $openingCashBalance = 0.0;
        $actualEndingCashBalance = 0.0;

        foreach ($cashAccounts as $acc) {
            $baseOpening = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;

            $beforeLines = JournalEntryLine::where('chart_of_account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->where('entry_date', '<', $startDate);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->get();

            $accOpening = $baseOpening + ((float) $beforeLines->sum('debit') - (float) $beforeLines->sum('credit'));

            $periodLines = JournalEntryLine::where('chart_of_account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->get();

            $periodIn = (float) $periodLines->sum('debit');
            $periodOut = (float) $periodLines->sum('credit');
            $accEnding = $accOpening + ($periodIn - $periodOut);

            $openingCashBalance += $accOpening;
            $actualEndingCashBalance += $accEnding;

            $cashAccountSummaries[] = [
                'account' => $acc,
                'opening' => $accOpening,
                'inflow' => $periodIn,
                'outflow' => $periodOut,
                'ending' => $accEnding,
            ];
        }

        // Ambil semua Jurnal yang menyentuh akun Kas & Bank pada periode ini
        $cashJournals = empty($cashAccountIds)
            ? collect()
            : JournalEntry::with(['lines.chartOfAccount'])
                ->where('status', 'posted')
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
                ->whereHas('lines', fn ($q) => $q->whereIn('chart_of_account_id', $cashAccountIds))
                ->orderBy('entry_date')
                ->orderBy('id')
                ->get();

        $operatingItems = [];
        $investingItems = [];
        $financingItems = [];
        $internalTransferTotal = 0.0;

        foreach ($cashJournals as $journal) {
            $cashDebit = 0.0;
            $cashCredit = 0.0;
            $counterpartLines = [];

            foreach ($journal->lines as $line) {
                if (in_array($line->chart_of_account_id, $cashAccountIds, true)) {
                    $cashDebit += (float) $line->debit;
                    $cashCredit += (float) $line->credit;
                } else {
                    $counterpartLines[] = $line;
                }
            }

            $cashDelta = $cashDebit - $cashCredit;

            if (abs($cashDelta) < 0.01) {
                $internalTransferTotal += $cashDebit;

                continue;
            }

            // Tentukan akun lawan utama untuk klasifikasi aktivitas arus kas
            $primaryCounterpart = null;
            if ($cashDelta > 0) {
                // Kas masuk -> cari baris kredit non-kas terbesar (utamakan pendapatan/modal)
                usort($counterpartLines, fn ($a, $b) => ((float) $b->credit) <=> ((float) $a->credit));
                $primaryCounterpart = $counterpartLines[0]?->chartOfAccount ?? null;
            } else {
                // Kas keluar -> cari baris debit non-kas terbesar
                usort($counterpartLines, fn ($a, $b) => ((float) $b->debit) <=> ((float) $a->debit));
                $primaryCounterpart = $counterpartLines[0]?->chartOfAccount ?? null;
            }

            $code = $primaryCounterpart?->account_code ?? '0-00000';
            $name = $primaryCounterpart?->account_name ?? 'Transaksi Kas Lainnya';
            $cat = $primaryCounterpart ? strtolower($primaryCounterpart->account_category->value ?? (string) $primaryCounterpart->account_category) : 'other';

            if (str_contains((string) $journal->reference_type, 'CafeSale') || str_starts_with($code, '4-')) {
                $bucket = 'operating';
                $label = $cashDelta >= 0
                    ? 'Penerimaan Kas dari Penjualan POS & Billing'
                    : 'Pengembalian / VOID Penjualan POS';
                $key = $cashDelta >= 0 ? 'op_pos_in' : 'op_pos_void';
            } elseif (str_starts_with($code, '1-2') || str_starts_with($code, '1-3') && $cat === 'asset' && str_contains(strtolower($name), 'tetap') || str_starts_with($code, '1-4')) {
                $bucket = 'investing';
                $label = $name;
                $key = 'inv_'.$code;
            } elseif (str_starts_with($code, '3-') || str_starts_with($code, '2-2') || $cat === 'equity') {
                $bucket = 'financing';
                $label = $name;
                $key = 'fin_'.$code;
            } else {
                $bucket = 'operating';
                $label = $name;
                $key = 'op_'.$code;
            }

            if ($bucket === 'operating') {
                $operatingItems[$key]['label'] = $label;
                $operatingItems[$key]['code'] = $code;
                $operatingItems[$key]['amount'] = ($operatingItems[$key]['amount'] ?? 0.0) + $cashDelta;
            } elseif ($bucket === 'investing') {
                $investingItems[$key]['label'] = $label;
                $investingItems[$key]['code'] = $code;
                $investingItems[$key]['amount'] = ($investingItems[$key]['amount'] ?? 0.0) + $cashDelta;
            } else {
                $financingItems[$key]['label'] = $label;
                $financingItems[$key]['code'] = $code;
                $financingItems[$key]['amount'] = ($financingItems[$key]['amount'] ?? 0.0) + $cashDelta;
            }
        }

        $totalOperatingCashFlow = array_sum(array_column($operatingItems, 'amount'));
        $totalInvestingCashFlow = array_sum(array_column($investingItems, 'amount'));
        $totalFinancingCashFlow = array_sum(array_column($financingItems, 'amount'));

        $netCashChange = $totalOperatingCashFlow + $totalInvestingCashFlow + $totalFinancingCashFlow;
        $calculatedEndingCash = $openingCashBalance + $netCashChange;
        $isReconciled = abs($calculatedEndingCash - $actualEndingCashBalance) < 1.0;

        if ($request->query('export') === 'csv') {
            $branchName = $selectedBranchId ? (Branch::find($selectedBranchId)?->name ?? 'Cabang '.$selectedBranchId) : 'Konsolidasi Seluruh Cabang';
            $filename = 'arus_kas_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use (
                $branchName, $startDate, $endDate, $openingCashBalance,
                $operatingItems, $totalOperatingCashFlow,
                $investingItems, $totalInvestingCashFlow,
                $financingItems, $totalFinancingCashFlow,
                $netCashChange, $actualEndingCashBalance, $internalTransferTotal
            ) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['LAPORAN ARUS KAS (CASH FLOW STATEMENT) MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Unit/Cabang', $branchName]);
                fputcsv($handle, ['Periode', $startDate.' s/d '.$endDate]);
                fputcsv($handle, ['Saldo Kas Awal Periode', (string) $openingCashBalance]);
                fputcsv($handle, []);

                fputcsv($handle, ['A. ARUS KAS DARI AKTIVITAS OPERASIONAL']);
                fputcsv($handle, ['Kode Referensi', 'Keterangan', 'Arus Kas Bersih (Rp)']);
                foreach ($operatingItems as $item) {
                    fputcsv($handle, [$item['code'], $item['label'], (string) $item['amount']]);
                }
                fputcsv($handle, ['', 'Arus Kas Bersih Aktivitas Operasional', (string) $totalOperatingCashFlow]);
                fputcsv($handle, []);

                fputcsv($handle, ['B. ARUS KAS DARI AKTIVITAS INVESTASI']);
                foreach ($investingItems as $item) {
                    fputcsv($handle, [$item['code'], $item['label'], (string) $item['amount']]);
                }
                fputcsv($handle, ['', 'Arus Kas Bersih Aktivitas Investasi', (string) $totalInvestingCashFlow]);
                fputcsv($handle, []);

                fputcsv($handle, ['C. ARUS KAS DARI AKTIVITAS PENDANAAN']);
                foreach ($financingItems as $item) {
                    fputcsv($handle, [$item['code'], $item['label'], (string) $item['amount']]);
                }
                fputcsv($handle, ['', 'Arus Kas Bersih Aktivitas Pendanaan', (string) $totalFinancingCashFlow]);
                fputcsv($handle, []);

                fputcsv($handle, ['KENAIKAN / (PENURUNAN) KAS BERSIH', '', (string) $netCashChange]);
                fputcsv($handle, ['SALDO KAS AKHIR PERIODE', '', (string) $actualEndingCashBalance]);
                fputcsv($handle, ['Catatan Mutasi Internal Kas/Bank (Setoran Shift)', '', (string) $internalTransferTotal]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'selected_branch_id' => $selectedBranchId,
                'opening_cash_balance' => $openingCashBalance,
                'total_operating_cash_flow' => $totalOperatingCashFlow,
                'total_investing_cash_flow' => $totalInvestingCashFlow,
                'total_financing_cash_flow' => $totalFinancingCashFlow,
                'net_cash_change' => $netCashChange,
                'ending_cash_balance' => $actualEndingCashBalance,
                'is_reconciled' => $isReconciled,
            ]);
        }

        return view('admin.accounting.arus_kas', compact(
            'startDate',
            'endDate',
            'branches',
            'selectedBranchId',
            'cashAccountSummaries',
            'openingCashBalance',
            'operatingItems',
            'totalOperatingCashFlow',
            'investingItems',
            'totalInvestingCashFlow',
            'financingItems',
            'totalFinancingCashFlow',
            'netCashChange',
            'actualEndingCashBalance',
            'internalTransferTotal',
            'isReconciled'
        ));
    }

    /**
     * 9. Catatan atas Laporan Keuangan (CaLK) & Paket Cetak Laporan Lengkap.
     */
    public function calkIndex(Request $request): View|JsonResponse|StreamedResponse
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
        $branchName = $selectedBranchId
            ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang #'.$selectedBranchId)
            : 'Konsolidasi Seluruh Cabang';

        // 1. Data Laba Rugi
        $pl = $this->computeLabaRugiData($startDate, $endDate, $selectedBranchId);

        // 2. Data Neraca Keuangan (per endDate)
        $bs = $this->computeNeracaData($endDate, $selectedBranchId);

        // 3. Data Arus Kas
        $cf = $this->computeArusKasData($startDate, $endDate, $selectedBranchId);

        // 4. Rincian Akun Piutang & Persediaan untuk Catatan 4
        $receivableAndInventory = array_values(array_filter($bs['assetList'], function ($item): bool {
            $code = (string) $item['account']->account_code;

            return (str_starts_with($code, '1-12') || str_starts_with($code, '1-13')) && abs((float) $item['balance']) >= 0.01;
        }));

        // 5. Status Tutup Buku Periode
        $lockedPeriod = AccountingPeriod::getLockedPeriodForDate($endDate, $selectedBranchId);

        // 6. Statistik Operasional Jurnal pada periode ini
        $journalCount = JournalEntry::where('status', 'posted')
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->count();

        if ($request->query('export') === 'csv') {
            $filename = 'calk_paket_laporan_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use ($branchName, $startDate, $endDate, $pl, $bs, $cf, $lockedPeriod, $journalCount) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['CATATAN ATAS LAPORAN KEUANGAN (CaLK) & RINGKASAN PAKET LAPORAN - PT MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Unit / Cabang', $branchName]);
                fputcsv($handle, ['Periode Laporan', $startDate.' s/d '.$endDate]);
                fputcsv($handle, ['Standar Pelaporan', 'SAK EP / SAK ETAP (Basis Akrual & Double-Entry)']);
                fputcsv($handle, ['Status Tutup Buku', $lockedPeriod ? 'TERKUNCI ('.$lockedPeriod->period_name.')' : 'TERBUKA (AKTIF)']);
                fputcsv($handle, ['Jumlah Jurnal Terposting', (string) $journalCount]);
                fputcsv($handle, []);

                fputcsv($handle, ['I. RINGKASAN LABA RUGI']);
                fputcsv($handle, ['Total Pendapatan Usaha', (string) $pl['totalRevenue']]);
                fputcsv($handle, ['Total HPP', (string) $pl['totalCogs']]);
                fputcsv($handle, ['Laba Kotor', (string) $pl['grossProfit']]);
                fputcsv($handle, ['Total Beban Operasional', (string) $pl['totalExpense']]);
                fputcsv($handle, ['Laba Bersih Operasional', (string) $pl['netProfit']]);
                fputcsv($handle, []);

                fputcsv($handle, ['II. RINGKASAN NERACA KEUANGAN (PER '.$endDate.')']);
                fputcsv($handle, ['Total Aset', (string) $bs['totalAssets']]);
                fputcsv($handle, ['Total Kewajiban', (string) $bs['totalLiabilities']]);
                fputcsv($handle, ['Total Ekuitas & Laba Berjalan', (string) $bs['totalEquityWithProfit']]);
                fputcsv($handle, ['Status Persamaan Akuntansi', $bs['isBalanced'] ? 'SEIMBANG' : 'SELISIH']);
                fputcsv($handle, []);

                fputcsv($handle, ['III. RINGKASAN ARUS KAS']);
                fputcsv($handle, ['Saldo Kas Awal', (string) $cf['openingCashBalance']]);
                fputcsv($handle, ['Arus Kas Operasional', (string) $cf['totalOperatingCashFlow']]);
                fputcsv($handle, ['Arus Kas Investasi', (string) $cf['totalInvestingCashFlow']]);
                fputcsv($handle, ['Arus Kas Pendanaan', (string) $cf['totalFinancingCashFlow']]);
                fputcsv($handle, ['Saldo Kas Akhir', (string) $cf['actualEndingCashBalance']]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'branch_name' => $branchName,
                'journal_count' => $journalCount,
                'is_period_locked' => $lockedPeriod !== null,
                'laba_rugi' => [
                    'total_revenue' => $pl['totalRevenue'],
                    'total_cogs' => $pl['totalCogs'],
                    'gross_profit' => $pl['grossProfit'],
                    'total_expense' => $pl['totalExpense'],
                    'net_profit' => $pl['netProfit'],
                ],
                'neraca' => [
                    'total_assets' => $bs['totalAssets'],
                    'total_liabilities' => $bs['totalLiabilities'],
                    'total_equity' => $bs['totalEquityWithProfit'],
                    'is_balanced' => $bs['isBalanced'],
                ],
                'arus_kas' => [
                    'opening_cash' => $cf['openingCashBalance'],
                    'operating' => $cf['totalOperatingCashFlow'],
                    'investing' => $cf['totalInvestingCashFlow'],
                    'financing' => $cf['totalFinancingCashFlow'],
                    'ending_cash' => $cf['actualEndingCashBalance'],
                    'is_reconciled' => $cf['isReconciled'],
                ],
            ]);
        }

        return view('admin.accounting.calk', compact(
            'startDate',
            'endDate',
            'branches',
            'selectedBranchId',
            'branchName',
            'pl',
            'bs',
            'cf',
            'receivableAndInventory',
            'lockedPeriod',
            'journalCount'
        ));
    }

    /**
     * Helper terpusat untuk kalkulasi Laba Rugi.
     *
     * @return array<string, mixed>
     */
    private function computeLabaRugiData(string $startDate, string $endDate, ?int $selectedBranchId): array
    {
        $calcCategory = function (string $category, bool $creditNormal) use ($startDate, $endDate, $selectedBranchId): array {
            $accounts = ChartOfAccount::where('account_category', $category)
                ->where('is_header', false)
                ->orderBy('account_code')
                ->get();

            $list = [];
            $total = 0.0;

            foreach ($accounts as $acc) {
                $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->where('chart_of_account_id', $acc->id)->get();

                $amt = $creditNormal
                    ? (float) ($lines->sum('credit') - $lines->sum('debit'))
                    : (float) ($lines->sum('debit') - $lines->sum('credit'));

                $list[] = ['account' => $acc, 'amount' => $amt];
                $total += $amt;
            }

            return [$list, $total];
        };

        [$revenueList, $totalRevenue] = $calcCategory('revenue', true);
        [$cogsList, $totalCogs] = $calcCategory('cogs', false);
        [$expenseList, $totalExpense] = $calcCategory('expense', false);

        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalExpense;

        return compact('revenueList', 'totalRevenue', 'cogsList', 'totalCogs', 'grossProfit', 'expenseList', 'totalExpense', 'netProfit');
    }

    /**
     * Helper terpusat untuk kalkulasi Neraca Keuangan.
     *
     * @return array<string, mixed>
     */
    private function computeNeracaData(string $asOfDate, ?int $selectedBranchId): array
    {
        $calcCategory = function (string $category, bool $debitNormal) use ($asOfDate, $selectedBranchId): array {
            $accounts = ChartOfAccount::where('account_category', $category)
                ->where('is_header', false)
                ->orderBy('account_code')
                ->get();

            $list = [];
            $total = 0.0;

            foreach ($accounts as $acc) {
                $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->where('chart_of_account_id', $acc->id)->get();

                $openingBal = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;
                $bal = $debitNormal
                    ? $openingBal + ((float) $lines->sum('debit') - (float) $lines->sum('credit'))
                    : $openingBal + ((float) $lines->sum('credit') - (float) $lines->sum('debit'));

                $list[] = ['account' => $acc, 'balance' => $bal];
                $total += $bal;
            }

            return [$list, $total];
        };

        [$assetList, $totalAssets] = $calcCategory('asset', true);
        [$liabilityList, $totalLiabilities] = $calcCategory('liability', false);
        [$equityList, $totalEquity] = $calcCategory('equity', false);

        $sumCategory = function (string $cat) use ($asOfDate, $selectedBranchId) {
            return JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', $cat))
                ->whereHas('journalEntry', function ($q) use ($asOfDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->get();
        };

        $revLines = $sumCategory('revenue');
        $cogsLines = $sumCategory('cogs');
        $expLines = $sumCategory('expense');

        $currentPeriodNetProfit = ((float) $revLines->sum('credit') - (float) $revLines->sum('debit'))
            - ((float) $cogsLines->sum('debit') - (float) $cogsLines->sum('credit'))
            - ((float) $expLines->sum('debit') - (float) $expLines->sum('credit'));

        $totalEquityWithProfit = $totalEquity + $currentPeriodNetProfit;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquityWithProfit;
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 1.0;

        return compact(
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
        );
    }

    /**
     * Helper terpusat untuk kalkulasi Arus Kas.
     *
     * @return array<string, mixed>
     */
    private function computeArusKasData(string $startDate, string $endDate, ?int $selectedBranchId): array
    {
        $cashAccounts = ChartOfAccount::where('is_header', false)
            ->where(function ($q): void {
                $q->where('account_code', 'like', '1-10%')
                    ->orWhere('account_code', 'like', '1-11%');
            })
            ->orderBy('account_code')
            ->get();

        $cashAccountIds = $cashAccounts->pluck('id')->all();
        $cashAccountSummaries = [];
        $openingCashBalance = 0.0;
        $actualEndingCashBalance = 0.0;

        foreach ($cashAccounts as $acc) {
            $baseOpening = (! $selectedBranchId || $acc->branch_id === $selectedBranchId) ? (float) $acc->opening_balance : 0.0;

            $beforeLines = JournalEntryLine::where('chart_of_account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->where('entry_date', '<', $startDate);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->get();

            $accOpening = $baseOpening + ((float) $beforeLines->sum('debit') - (float) $beforeLines->sum('credit'));

            $periodLines = JournalEntryLine::where('chart_of_account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $selectedBranchId): void {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
                    if ($selectedBranchId) {
                        $q->where('branch_id', $selectedBranchId);
                    }
                })->get();

            $periodIn = (float) $periodLines->sum('debit');
            $periodOut = (float) $periodLines->sum('credit');
            $accEnding = $accOpening + ($periodIn - $periodOut);

            $openingCashBalance += $accOpening;
            $actualEndingCashBalance += $accEnding;

            $cashAccountSummaries[] = [
                'account' => $acc,
                'opening' => $accOpening,
                'inflow' => $periodIn,
                'outflow' => $periodOut,
                'ending' => $accEnding,
            ];
        }

        $cashJournals = empty($cashAccountIds)
            ? collect()
            : JournalEntry::with(['lines.chartOfAccount'])
                ->where('status', 'posted')
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
                ->whereHas('lines', fn ($q) => $q->whereIn('chart_of_account_id', $cashAccountIds))
                ->orderBy('entry_date')
                ->orderBy('id')
                ->get();

        $operatingItems = [];
        $investingItems = [];
        $financingItems = [];
        $internalTransferTotal = 0.0;

        foreach ($cashJournals as $journal) {
            $cashDebit = 0.0;
            $cashCredit = 0.0;
            $counterpartLines = [];

            foreach ($journal->lines as $line) {
                if (in_array($line->chart_of_account_id, $cashAccountIds, true)) {
                    $cashDebit += (float) $line->debit;
                    $cashCredit += (float) $line->credit;
                } else {
                    $counterpartLines[] = $line;
                }
            }

            $cashDelta = $cashDebit - $cashCredit;
            if (abs($cashDelta) < 0.01) {
                $internalTransferTotal += $cashDebit;

                continue;
            }

            if ($cashDelta > 0) {
                usort($counterpartLines, fn ($a, $b) => ((float) $b->credit) <=> ((float) $a->credit));
            } else {
                usort($counterpartLines, fn ($a, $b) => ((float) $b->debit) <=> ((float) $a->debit));
            }

            $primaryCounterpart = $counterpartLines[0]?->chartOfAccount ?? null;
            $code = $primaryCounterpart?->account_code ?? '0-00000';
            $name = $primaryCounterpart?->account_name ?? 'Transaksi Kas Lainnya';
            $cat = $primaryCounterpart ? strtolower($primaryCounterpart->account_category->value ?? (string) $primaryCounterpart->account_category) : 'other';

            if (str_contains((string) $journal->reference_type, 'CafeSale') || str_starts_with($code, '4-')) {
                $bucket = 'operating';
                $label = $cashDelta >= 0 ? 'Penerimaan Kas dari Penjualan POS & Billing' : 'Pengembalian / VOID Penjualan POS';
                $key = $cashDelta >= 0 ? 'op_pos_in' : 'op_pos_void';
            } elseif (str_starts_with($code, '1-2') || (str_starts_with($code, '1-3') && $cat === 'asset' && str_contains(strtolower($name), 'tetap')) || str_starts_with($code, '1-4')) {
                $bucket = 'investing';
                $label = $name;
                $key = 'inv_'.$code;
            } elseif (str_starts_with($code, '3-') || str_starts_with($code, '2-2') || $cat === 'equity') {
                $bucket = 'financing';
                $label = $name;
                $key = 'fin_'.$code;
            } else {
                $bucket = 'operating';
                $label = $name;
                $key = 'op_'.$code;
            }

            if ($bucket === 'operating') {
                $operatingItems[$key]['label'] = $label;
                $operatingItems[$key]['code'] = $code;
                $operatingItems[$key]['amount'] = ($operatingItems[$key]['amount'] ?? 0.0) + $cashDelta;
            } elseif ($bucket === 'investing') {
                $investingItems[$key]['label'] = $label;
                $investingItems[$key]['code'] = $code;
                $investingItems[$key]['amount'] = ($investingItems[$key]['amount'] ?? 0.0) + $cashDelta;
            } else {
                $financingItems[$key]['label'] = $label;
                $financingItems[$key]['code'] = $code;
                $financingItems[$key]['amount'] = ($financingItems[$key]['amount'] ?? 0.0) + $cashDelta;
            }
        }

        $totalOperatingCashFlow = array_sum(array_column($operatingItems, 'amount'));
        $totalInvestingCashFlow = array_sum(array_column($investingItems, 'amount'));
        $totalFinancingCashFlow = array_sum(array_column($financingItems, 'amount'));

        $netCashChange = $totalOperatingCashFlow + $totalInvestingCashFlow + $totalFinancingCashFlow;
        $calculatedEndingCash = $openingCashBalance + $netCashChange;
        $isReconciled = abs($calculatedEndingCash - $actualEndingCashBalance) < 1.0;

        return compact(
            'cashAccountSummaries',
            'openingCashBalance',
            'operatingItems',
            'totalOperatingCashFlow',
            'investingItems',
            'totalInvestingCashFlow',
            'financingItems',
            'totalFinancingCashFlow',
            'netCashChange',
            'actualEndingCashBalance',
            'internalTransferTotal',
            'isReconciled'
        );
    }
}
