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
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Models\JournalEntryLine;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\Branch;
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
    public function bukuBesarIndex(Request $request): View|JsonResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

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
            $priorLines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate): void {
                $q->where('status', 'posted')->where('entry_date', '<', $startDate);
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
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate): void {
                    $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
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
    public function neracaSaldoIndex(Request $request): View|JsonResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfYear()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        $accounts = ChartOfAccount::where('is_header', false)->orderBy('account_code')->get();

        $trialRows = [];
        $grandTotalDebit = 0.0;
        $grandTotalCredit = 0.0;

        foreach ($accounts as $acc) {
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
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
            'trialRows',
            'grandTotalDebit',
            'grandTotalCredit',
            'isBalanced'
        ));
    }

    /**
     * 4. Halaman Laporan Laba Rugi (Income Statement / P&L).
     */
    public function labaRugiIndex(Request $request): View|JsonResponse
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        // 1. PENDAPATAN (REVENUE - 4-xxxxx)
        $revenueAccounts = ChartOfAccount::where('account_category', 'revenue')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $revenueList = [];
        $totalRevenue = 0.0;
        foreach ($revenueAccounts as $acc) {
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($startDate, $endDate): void {
                $q->where('status', 'posted')->whereBetween('entry_date', [$startDate, $endDate]);
            })->where('chart_of_account_id', $acc->id)->get();

            // Beban saldo normal debit: Debit - Kredit
            $amt = (float) ($lines->sum('debit') - $lines->sum('credit'));
            $expenseList[] = ['account' => $acc, 'amount' => $amt];
            $totalExpense += $amt;
        }

        // LABA BERSIH OPERASIONAL (NET PROFIT)
        $netProfit = $grossProfit - $totalExpense;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
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
    public function neracaIndex(Request $request): View|JsonResponse
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->input('as_of_date'))->toDateString()
            : Carbon::today()->toDateString();

        // 1. ASET (1-xxxxx)
        $assetAccounts = ChartOfAccount::where('account_category', 'asset')
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $assetList = [];
        $totalAssets = 0.0;
        foreach ($assetAccounts as $acc) {
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
            })->where('chart_of_account_id', $acc->id)->get();

            $bal = (float) $acc->opening_balance + ((float) $lines->sum('debit') - (float) $lines->sum('credit'));
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
            })->where('chart_of_account_id', $acc->id)->get();

            $bal = (float) $acc->opening_balance + ((float) $lines->sum('credit') - (float) $lines->sum('debit'));
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
            $lines = JournalEntryLine::whereHas('journalEntry', function ($q) use ($asOfDate): void {
                $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate);
            })->where('chart_of_account_id', $acc->id)->get();

            $bal = (float) $acc->opening_balance + ((float) $lines->sum('credit') - (float) $lines->sum('debit'));
            $equityList[] = ['account' => $acc, 'balance' => $bal];
            $totalEquity += $bal;
        }

        // Hitung Laba Periode Berjalan Kumulatif (Pendapatan - HPP - Beban hingga asOfDate)
        $revLines = JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', 'revenue'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate))->get();
        $cogsLines = JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', 'cogs'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate))->get();
        $expLines = JournalEntryLine::whereHas('chartOfAccount', fn ($q) => $q->where('account_category', 'expense'))
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $asOfDate))->get();

        $currentPeriodNetProfit = ((float) $revLines->sum('credit') - (float) $revLines->sum('debit'))
            - ((float) $cogsLines->sum('debit') - (float) $cogsLines->sum('credit'))
            - ((float) $expLines->sum('debit') - (float) $expLines->sum('credit'));

        $totalEquityWithProfit = $totalEquity + $currentPeriodNetProfit;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquityWithProfit;

        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 1.0;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'as_of_date' => $asOfDate,
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquityWithProfit,
                'is_balanced' => $isBalanced,
            ]);
        }

        return view('admin.accounting.neraca', compact(
            'asOfDate',
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

        $entry = $this->journalService->createManualJournal([
            'entry_date' => $request->input('entry_date'),
            'description' => $request->input('description'),
            'branch_id' => $request->input('branch_id'),
            'lines' => $lines,
        ]);

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
}
