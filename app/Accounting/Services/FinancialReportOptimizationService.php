<?php

declare(strict_types=1);

namespace App\Accounting\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service optimasi query pelaporan akuntansi SAK.
 * Menggantikan query loop N+1 dengan Single-Pass SQL Aggregation.
 */
class FinancialReportOptimizationService
{
    /**
     * Mengambil total mutasi debit & credit untuk seluruh akun dalam satu query SQL agregasi.
     *
     * @return array<int, array{debit: float, credit: float}>
     */
    public function getAccountMutations(?string $startDate, string $endDate, ?int $branchId = null): array
    {
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<=', $endDate);

        if ($startDate !== null) {
            $query->where('journal_entries.entry_date', '>=', $startDate);
        }

        if ($branchId !== null) {
            $query->where('journal_entries.branch_id', $branchId);
        }

        $results = $query->groupBy('journal_entry_lines.chart_of_account_id')
            ->select([
                'journal_entry_lines.chart_of_account_id',
                DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_entry_lines.credit), 0) as total_credit'),
            ])
            ->get();

        $map = [];
        foreach ($results as $row) {
            $map[(int) $row->chart_of_account_id] = [
                'debit' => (float) $row->total_debit,
                'credit' => (float) $row->total_credit,
            ];
        }

        return $map;
    }
}
