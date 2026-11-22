<?php

declare(strict_types=1);

namespace App\Accounting\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Models\JournalEntryLine;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\CafeSale;
use App\Models\ShiftSalesSummary;

/**
 * Service Penjurnalan Otomatis Tingkat Enterprise (App Layer Extension).
 * Memperbaiki kalkulasi HPP ($item->qty & cost_price), routing akun kas vs bank (QRIS),
 * serta penjurnalan selisih kas fisik laci (cash discrepancy).
 */
class AppAutoJournalService extends AutoJournalService
{
    /**
     * Jurnal otomatis transaksi penjualan kasir POS (F&B, Billing Internet, Jasa Print).
     * Telah diperbaiki:
     * - Membaca $item->qty (sebelumnya typo quantity) dan $variant->cost_price.
     * - Mengarahkan pembayaran QRIS / Transfer ke Bank BCA (1-11001) alih-alih kas laci fisik.
     */
    public function journalSale(CafeSale $sale): JournalEntry
    {
        // Hindari duplikasi jurnal untuk transaksi yang sama
        $existing = JournalEntry::where('reference_type', CafeSale::class)
            ->where('reference_id', $sale->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($sale): JournalEntry {
            $entryDate = $sale->created_at ? Carbon::parse($sale->created_at)->toDateString() : Carbon::today()->toDateString();
            $entryNumber = $this->generateEntryNumberInternal('JRN-POS', Carbon::parse($entryDate));

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => CafeSale::class,
                'reference_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'creator_employee_id' => $sale->employee_id,
                'description' => "Penjualan POS Invoice #{$sale->invoice_number} ({$sale->customer_name})",
                'status' => 'posted',
                'total_debit' => 0,
                'total_credit' => 0,
            ]);

            $totalNet = (float) $sale->net_amount;
            $fnbAmount = (float) $sale->total_fnb;
            $billingAmount = (float) $sale->total_billing;

            // Jika rincian fnb/billing 0 tetapi ada net_amount, alokasikan ke pendapatan F&B sebagai default
            if ($fnbAmount <= 0 && $billingAmount <= 0) {
                $fnbAmount = $totalNet;
            }

            // 1. DEBIT: Deteksi Metode Pembayaran (Kas Laci 1-10001 vs Bank BCA 1-11001 untuk QRIS/Transfer)
            $payments = $sale->payments()->where('status', 'completed')->get();

            if ($payments->isNotEmpty()) {
                foreach ($payments as $payment) {
                    $pMethod = strtolower(trim((string) ($payment->payment_method ?? 'cash')));
                    $isNonCash = in_array($pMethod, ['qris', 'transfer', 'edc', 'bank_transfer', 'non_cash', 'digital'], true);
                    $paidAmount = (float) $payment->amount_due;
                    if ($paidAmount <= 0) {
                        $paidAmount = (float) ($payment->amount_paid - ($payment->change_amount ?? 0));
                    }
                    if ($paidAmount <= 0) {
                        $paidAmount = $totalNet;
                    }

                    if ($isNonCash) {
                        $destAccount = $this->getAccountByCodeInternal('1-11001', 'Rekening Bank BCA', 'asset', 'debit');
                        $memoDebit = "Penerimaan QRIS/Bank #{$payment->payment_number} ({$sale->invoice_number})";
                    } else {
                        $destAccount = $this->getAccountByCodeInternal('1-10001', 'Kas Laci Kasir (Outlet)', 'asset', 'debit');
                        $memoDebit = "Penerimaan Kasir Kas #{$payment->payment_number} ({$sale->invoice_number})";
                    }
                    $this->createLineInternal($entry, $destAccount, $paidAmount, 0, $memoDebit);
                }
            } else {
                $method = strtolower(trim((string) ($sale->payment_method ?? 'cash')));
                $isNonCash = in_array($method, ['qris', 'transfer', 'edc', 'bank_transfer', 'non_cash', 'digital'], true);

                if ($isNonCash) {
                    $destAccount = $this->getAccountByCodeInternal('1-11001', 'Rekening Bank BCA', 'asset', 'debit');
                    $memoDebit = "Penerimaan QRIS/Bank Invoice #{$sale->invoice_number}";
                } else {
                    $destAccount = $this->getAccountByCodeInternal('1-10001', 'Kas Laci Kasir (Outlet)', 'asset', 'debit');
                    $memoDebit = "Penerimaan Kasir Kas Invoice #{$sale->invoice_number}";
                }
                $this->createLineInternal($entry, $destAccount, $totalNet, 0, $memoDebit);
            }

            // 2. KREDIT: Pendapatan Penjualan F&B (4-10001) jika ada
            if ($fnbAmount > 0) {
                $fnbAccount = $this->getAccountByCodeInternal('4-10001', 'Pendapatan Penjualan F&B', 'revenue', 'credit');
                $this->createLineInternal($entry, $fnbAccount, 0, $fnbAmount, "Pendapatan F&B Invoice #{$sale->invoice_number}");
            }

            // 3. KREDIT: Pendapatan Billing Internet (4-10002) jika ada
            if ($billingAmount > 0) {
                $billingAccount = $this->getAccountByCodeInternal('4-10002', 'Pendapatan Billing Internet PC Station', 'revenue', 'credit');
                $this->createLineInternal($entry, $billingAccount, 0, $billingAmount, "Pendapatan Billing Warnet Invoice #{$sale->invoice_number}");
            }

            // 4. Jurnal HPP & Persediaan untuk item F&B (PERBAIKAN TYPO: $item->qty & $variant->cost_price)
            $totalCogs = 0.0;
            $items = $sale->items()->with(['variant.product'])->get();

            foreach ($items as $item) {
                $itemQty = (int) ($item->qty ?? $item->quantity ?? 0);
                if ($itemQty <= 0) {
                    continue;
                }

                $costPrice = (float) ($item->variant?->cost_price ?? $item->variant?->buy_price ?? 0);
                if ($costPrice <= 0 && $item->variant?->product) {
                    $costPrice = (float) ($item->variant->product->cost_price ?? $item->variant->product->buy_price ?? 0);
                }
                if ($costPrice <= 0) {
                    // Estimasi HPP wajar 60% dari harga jual satuan F&B jika modal belum tercatat
                    $costPrice = (float) ($item->unit_price ?? 0) * 0.6;
                }

                $totalCogs += ($costPrice * $itemQty);
            }

            if ($totalCogs > 0) {
                $cogsAccount = $this->getAccountByCodeInternal('5-10001', 'HPP Makanan & Minuman', 'cogs', 'debit');
                $inventoryAccount = $this->getAccountByCodeInternal('1-13001', 'Persediaan Barang Dagang FnB', 'asset', 'debit');
                $this->createLineInternal($entry, $cogsAccount, $totalCogs, 0, "HPP F&B Invoice #{$sale->invoice_number}");
                $this->createLineInternal($entry, $inventoryAccount, 0, $totalCogs, "Pengurangan Persediaan F&B Invoice #{$sale->invoice_number}");
            }

            $totalDebit = (float) $entry->lines()->sum('debit');
            $totalCredit = (float) $entry->lines()->sum('credit');

            $entry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return $entry;
        });
    }

    /**
     * Jurnal penyesuaian selisih fisik kas laci kasir (Blind Cash Count Discrepancy).
     *
     * Jika selisih minus (defisit fisik kas laci kasir):
     * - Debit: Beban Kerugian Selisih Kasir (6-10007) ATAU Piutang Selisih Kasir (1-12002) jika payroll deduction
     * - Kredit: Kas Laci Kasir (1-10001)
     *
     * Jika selisih plus (surplus fisik kas laci kasir):
     * - Debit: Kas Laci Kasir (1-10001)
     * - Kredit: Pendapatan Selisih Lebih Kas Laci Kasir (4-20002)
     */
    public function journalCashDiscrepancy(ShiftSalesSummary $summary, ?string $memo = null): ?JournalEntry
    {
        $diff = (float) $summary->cash_difference;

        // Jika selisih 0 atau mendekati nol (< Rp 1), tidak perlu jurnal penyesuaian
        if (abs($diff) < 0.01) {
            return null;
        }

        // Cek apakah jurnal selisih untuk shift summary ini sudah dibuat
        $existing = JournalEntry::where('reference_type', ShiftSalesSummary::class)
            ->where('reference_id', $summary->id)
            ->where('entry_number', 'like', 'JRN-DISC%')
            ->first();

        if ($existing) {
            // Jika ada perubahan resolusi (misal dari beban menjadi pemotongan gaji / piutang kasir)
            if ($summary->discrepancy_resolution_status === 'resolved' && $diff < 0) {
                $targetCode = $summary->discrepancy_resolution_action === 'payroll_deduction' ? '1-12002' : '6-10007';
                $targetAccount = $this->getAccountByCodeInternal(
                    $targetCode,
                    $targetCode === '1-12002' ? 'Piutang Selisih Kas Kasir' : 'Beban Kerugian Selisih Kasir',
                    $targetCode === '1-12002' ? 'asset' : 'expense',
                    'debit'
                );

                $debitLine = $existing->lines()->where('debit', '>', 0)->first();
                if ($debitLine && (int) $debitLine->chart_of_account_id !== (int) $targetAccount->id) {
                    $oldAccount = $debitLine->chartOfAccount;
                    if ($oldAccount) {
                        $this->updateCoaBalanceInternal($oldAccount, 0, (float) $debitLine->debit);
                    }
                    $debitLine->update([
                        'chart_of_account_id' => $targetAccount->id,
                        'memo' => "Resolusi Admin ({$summary->discrepancy_resolution_action}): ".($memo ?: $summary->discrepancy_resolution_notes ?: "Shift #{$summary->id}"),
                    ]);
                    $this->updateCoaBalanceInternal($targetAccount, (float) $debitLine->debit, 0);
                }
            }

            return $existing;
        }

        return DB::transaction(function () use ($summary, $diff, $memo): JournalEntry {
            $entryDate = $summary->closed_at
                ? Carbon::parse($summary->closed_at)->toDateString()
                : ($summary->work_date ? Carbon::parse($summary->work_date)->toDateString() : Carbon::today()->toDateString());

            $entryNumber = $this->generateEntryNumberInternal('JRN-DISC', Carbon::parse($entryDate));
            $diffType = $diff < 0 ? 'Defisit/Kurang' : 'Surplus/Lebih';
            $formattedAmount = number_format(abs($diff), 0, ',', '.');
            $description = "Penyesuaian Selisih Kas Shift #{$summary->id} ({$diffType} Rp {$formattedAmount})".($memo ? " - {$memo}" : '');

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => ShiftSalesSummary::class,
                'reference_id' => $summary->id,
                'branch_id' => $summary->branch_id,
                'creator_employee_id' => $summary->employee_id,
                'description' => $description,
                'status' => 'posted',
                'total_debit' => 0,
                'total_credit' => 0,
            ]);

            $cashAccount = $this->getAccountByCodeInternal('1-10001', 'Kas Laci Kasir (Outlet)', 'asset', 'debit');

            if ($diff < 0) {
                // KASUS 1: SELISIH KURANG (Uang fisik di laci kasir lebih sedikit dari saldo sistem)
                $absDiff = abs($diff);

                // Tentukan akun debit: jika ada resolusi payroll deduction, arahkan ke Piutang Kasir, selain itu Beban Selisih Kas
                if ($summary->discrepancy_resolution_action === 'payroll_deduction') {
                    $debitAccount = $this->getAccountByCodeInternal('1-12002', 'Piutang Selisih Kas Kasir', 'asset', 'debit');
                    $memoDebit = "Piutang Potong Gaji Selisih Kasir Shift #{$summary->id}";
                } else {
                    $debitAccount = $this->getAccountByCodeInternal('6-10007', 'Beban Kerugian Selisih Kasir', 'expense', 'debit');
                    $memoDebit = "Beban Selisih Kurang Kas Shift #{$summary->id}";
                }

                // 1. DEBIT Beban / Piutang
                $this->createLineInternal($entry, $debitAccount, $absDiff, 0, $memoDebit);

                // 2. KREDIT Kas Laci (Mengurangi saldo kas laci sesuai fisik riil)
                $this->createLineInternal($entry, $cashAccount, 0, $absDiff, "Penyesuaian Fisik Kas Laci Shift #{$summary->id}");
            } else {
                // KASUS 2: SELISIH LEBIH (Uang fisik di laci kasir lebih banyak dari sistem)
                $surplusAccount = $this->getAccountByCodeInternal('4-20002', 'Pendapatan Selisih Lebih Kas Laci', 'revenue', 'credit');

                // 1. DEBIT Kas Laci (Menambah saldo kas laci sesuai fisik riil)
                $this->createLineInternal($entry, $cashAccount, $diff, 0, "Penambahan Fisik Surplus Kas Laci Shift #{$summary->id}");

                // 2. KREDIT Pendapatan Selisih Lebih
                $this->createLineInternal($entry, $surplusAccount, 0, $diff, "Pendapatan Selisih Lebih Kas Shift #{$summary->id}");
            }

            $totalDebit = (float) $entry->lines()->sum('debit');
            $totalCredit = (float) $entry->lines()->sum('credit');

            $entry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return $entry;
        });
    }

    /**
     * Helper baris jurnal internal.
     */
    protected function createLineInternal(JournalEntry $entry, ChartOfAccount $account, float $debit, float $credit, ?string $memo): JournalEntryLine
    {
        $line = JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $account->id,
            'debit' => $debit,
            'credit' => $credit,
            'memo' => $memo,
        ]);

        $this->updateCoaBalanceInternal($account, $debit, $credit);

        return $line;
    }

    /**
     * Update saldo berjalan akun COA internal.
     */
    protected function updateCoaBalanceInternal(ChartOfAccount $account, float $debit, float $credit): void
    {
        $isDebit = ($account->normal_balance === NormalBalance::DEBIT || $account->normal_balance->value === 'debit');
        $diff = $isDebit ? ($debit - $credit) : ($credit - $debit);

        $account->increment('current_balance', $diff);
    }

    /**
     * Ambil akun COA internal atau buat jika belum ada.
     */
    protected function getAccountByCodeInternal(string $code, string $defaultName = '', string $category = 'asset', string $normal = 'debit'): ChartOfAccount
    {
        $account = ChartOfAccount::where('account_code', $code)->first();
        if ($account) {
            return $account;
        }

        return ChartOfAccount::create([
            'account_code' => $code,
            'account_name' => $defaultName ?: "Akun {$code}",
            'account_category' => $category,
            'normal_balance' => $normal,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Generate nomor entri jurnal internal.
     */
    protected function generateEntryNumberInternal(string $prefix, Carbon $date): string
    {
        $dateStr = $date->format('Ymd');
        $count = JournalEntry::whereDate('entry_date', $date->toDateString())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $dateStr, $count);
    }
}
