<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\JournalEntry;
use Modules\Kas\Domain\Models\JournalEntryLine;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Consignment;
use Modules\MasterData\Domain\Models\StockOpname;

/**
 * Service Penjurnalan Otomatis & Pembukuan Berpasangan (Double-Entry General Ledger).
 */
class AutoJournalService
{
    /**
     * Jurnal otomatis transaksi penjualan kasir POS (F&B, Billing Internet, Jasa Print).
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
            $entryNumber = $this->generateEntryNumber('JRN-POS', Carbon::parse($entryDate));

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

            // 1. DEBIT: Kas Laci Kasir (1-10001)
            $cashAccount = $this->getAccountByCode('1-10001');
            $this->createLine($entry, $cashAccount, $totalNet, 0, "Penerimaan Kasir Kas Invoice #{$sale->invoice_number}");

            // 2. KREDIT: Pendapatan Penjualan F&B (4-10001) jika ada
            if ($fnbAmount > 0) {
                $fnbAccount = $this->getAccountByCode('4-10001');
                $this->createLine($entry, $fnbAccount, 0, $fnbAmount, "Pendapatan F&B Invoice #{$sale->invoice_number}");
            }

            // 3. KREDIT: Pendapatan Billing Internet (4-10002) jika ada
            if ($billingAmount > 0) {
                $billingAccount = $this->getAccountByCode('4-10002');
                $this->createLine($entry, $billingAccount, 0, $billingAmount, "Pendapatan Billing Warnet Invoice #{$sale->invoice_number}");
            }

            // 4. Jurnal HPP & Persediaan untuk item F&B (jika ada harga modal / buy_price)
            $totalCogs = 0.0;
            if ($sale->relationLoaded('items') || method_exists($sale, 'items')) {
                foreach ($sale->items as $item) {
                    $buyPrice = (float) ($item->variant?->buy_price ?? 0);
                    if ($buyPrice <= 0 && $item->variant?->product) {
                        $buyPrice = (float) ($item->variant->product->buy_price ?? 0);
                    }
                    if ($buyPrice <= 0) {
                        // Estimasi HPP wajar 60% dari harga jual F&B jika modal tidak tercatat
                        $buyPrice = (float) $item->unit_price * 0.6;
                    }
                    $totalCogs += ($buyPrice * (int) $item->quantity);
                }
            }

            if ($totalCogs > 0) {
                $cogsAccount = $this->getAccountByCode('5-10001');
                $inventoryAccount = $this->getAccountByCode('1-13001');
                $this->createLine($entry, $cogsAccount, $totalCogs, 0, "HPP F&B Invoice #{$sale->invoice_number}");
                $this->createLine($entry, $inventoryAccount, 0, $totalCogs, "Pengurangan Persediaan F&B Invoice #{$sale->invoice_number}");
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
     * Buat jurnal balik saat transaksi penjualan dibatalkan (VOID).
     */
    public function journalVoidSale(CafeSale $sale, string $reason): ?JournalEntry
    {
        $originalEntry = JournalEntry::where('reference_type', CafeSale::class)
            ->where('reference_id', $sale->id)
            ->with('lines')
            ->first();

        if (! $originalEntry) {
            return null;
        }

        return DB::transaction(function () use ($sale, $originalEntry, $reason): JournalEntry {
            $entryDate = Carbon::today()->toDateString();
            $entryNumber = $this->generateEntryNumber('JRN-VOID', Carbon::parse($entryDate));

            $voidEntry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => CafeSale::class,
                'reference_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'creator_employee_id' => $sale->void_by ?? $sale->employee_id,
                'description' => "Jurnal Balik / VOID Invoice #{$sale->invoice_number}: {$reason}",
                'status' => 'posted',
                'total_debit' => $originalEntry->total_credit,
                'total_credit' => $originalEntry->total_debit,
            ]);

            // Balikkan seluruh baris jurnal (Debit jadi Kredit, Kredit jadi Debit)
            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $voidEntry->id,
                    'chart_of_account_id' => $line->chart_of_account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'memo' => "Pembalik: {$line->memo}",
                ]);

                // Update saldo COA
                $this->updateCoaBalance($line->chartOfAccount, (float) $line->credit, (float) $line->debit);
            }

            return $voidEntry;
        });
    }

    /**
     * Buat entri jurnal manual (oleh Admin / Akuntan).
     *
     * @param  array<string, mixed>  $data
     */
    public function createManualJournal(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data): JournalEntry {
            $entryDate = $data['entry_date'] ?? Carbon::today()->toDateString();
            $entryNumber = $this->generateEntryNumber('JRN-MAN', Carbon::parse($entryDate));

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => 'manual',
                'reference_id' => null,
                'branch_id' => $data['branch_id'] ?? null,
                'creator_employee_id' => $data['creator_employee_id'] ?? null,
                'description' => $data['description'] ?? 'Jurnal Penyesuaian Manual',
                'status' => 'posted',
                'total_debit' => 0,
                'total_credit' => 0,
            ]);

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($data['lines'] as $lineData) {
                $debit = (float) ($lineData['debit'] ?? 0);
                $credit = (float) ($lineData['credit'] ?? 0);
                if ($debit <= 0 && $credit <= 0) {
                    continue;
                }

                $account = ChartOfAccount::findOrFail($lineData['chart_of_account_id']);
                $this->createLine($entry, $account, $debit, $credit, $lineData['memo'] ?? null);

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            $entry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return $entry;
        });
    }

    /**
     * Hitung ulang seluruh saldo berjalan akun COA dari seluruh baris jurnal aktif.
     */
    public function recalculateAllAccountBalances(): void
    {
        DB::transaction(function (): void {
            $accounts = ChartOfAccount::all();
            foreach ($accounts as $account) {
                $lines = JournalEntryLine::whereHas('journalEntry', function ($q): void {
                    $q->where('status', 'posted');
                })->where('chart_of_account_id', $account->id)->get();

                $sumDebit = (float) $lines->sum('debit');
                $sumCredit = (float) $lines->sum('credit');

                $newBalance = (float) $account->opening_balance;
                if ($account->normal_balance === NormalBalance::DEBIT || $account->normal_balance->value === 'debit') {
                    $newBalance += ($sumDebit - $sumCredit);
                } else {
                    $newBalance += ($sumCredit - $sumDebit);
                }

                $account->update(['current_balance' => $newBalance]);
            }
        });
    }

    /**
     * Buat baris jurnal dan perbarui saldo berjalan akun COA.
     */
    private function createLine(JournalEntry $entry, ChartOfAccount $account, float $debit, float $credit, ?string $memo): JournalEntryLine
    {
        $line = JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'chart_of_account_id' => $account->id,
            'debit' => $debit,
            'credit' => $credit,
            'memo' => $memo,
        ]);

        $this->updateCoaBalance($account, $debit, $credit);

        return $line;
    }

    /**
     * Update saldo akun COA mengikuti aturan Saldo Normal SAK.
     */
    private function updateCoaBalance(ChartOfAccount $account, float $debit, float $credit): void
    {
        $diff = 0.0;
        $isDebit = ($account->normal_balance === NormalBalance::DEBIT || $account->normal_balance->value === 'debit');

        if ($isDebit) {
            $diff = $debit - $credit;
        } else {
            $diff = $credit - $debit;
        }

        $account->increment('current_balance', $diff);
    }

    /**
     * Dapatkan akun COA berdasarkan kode akun unik, dengan fallback pembuatan akun terstandarisasi.
     */
    private function getAccountByCode(string $code, string $defaultName = '', string $category = 'asset', string $normal = 'debit'): ChartOfAccount
    {
        $account = ChartOfAccount::where('account_code', $code)->first();
        if ($account) {
            return $account;
        }

        // Fallback jika belum ada di database
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
     * Jurnal otomatis penyesuaian nilai persediaan dari hasil audit fisik Stock Opname.
     */
    public function journalStockOpnameAdjustment(StockOpname $stockOpname): ?JournalEntry
    {
        // Hindari duplikasi jurnal untuk sesi opname yang sama
        $existing = JournalEntry::where('reference_type', StockOpname::class)
            ->where('reference_id', $stockOpname->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $varianceCost = (float) $stockOpname->total_variance_cost;
        if (abs($varianceCost) < 0.01) {
            // Jika tidak ada varian selisih biaya (nihil / 0), tidak ada penyesuaian finansial yang dibukukan
            return null;
        }

        return DB::transaction(function () use ($stockOpname, $varianceCost): JournalEntry {
            $entryDate = $stockOpname->opname_date ? Carbon::parse($stockOpname->opname_date)->toDateString() : Carbon::today()->toDateString();
            $entryNumber = $this->generateEntryNumber('JRN-ADJ', Carbon::parse($entryDate));

            $branchName = $stockOpname->branch->name ?? 'Cabang';
            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => StockOpname::class,
                'reference_id' => $stockOpname->id,
                'branch_id' => $stockOpname->branch_id,
                'creator_employee_id' => $stockOpname->approved_by,
                'description' => "Penyesuaian Persediaan Stock Opname #{$stockOpname->opname_number} ({$branchName})",
                'status' => 'posted',
                'total_debit' => 0,
                'total_credit' => 0,
            ]);

            $inventoryAccount = $this->getAccountByCode('1-13001', 'Persediaan Barang Dagang FnB & Ritel', 'asset', 'debit');

            if ($varianceCost < 0) {
                // KASUS 1: SELISIH KURANG (DEFISIT / KERUGIAN PERSEDIAAN / SHRINKAGE / RUSAK)
                // Debit: Beban Kerugian Selisih Stok Opname (6-10006)
                // Kredit: Persediaan Barang Dagang (1-13001) - saldo buku berkurang
                $amount = abs($varianceCost);
                $shrinkageAccount = $this->getAccountByCode('6-10006', 'Beban Kerugian Selisih Stok Opname', 'expense', 'debit');

                $this->createLine($entry, $shrinkageAccount, $amount, 0, "Beban Selisih Kurang Stok Opname #{$stockOpname->opname_number}");
                $this->createLine($entry, $inventoryAccount, 0, $amount, "Penurunan Nilai Persediaan Stok Opname #{$stockOpname->opname_number}");
            } else {
                // KASUS 2: SELISIH LEBIH (SURPLUS FISIK / TEMUAN INVENTARIS)
                // Debit: Persediaan Barang Dagang (1-13001) - saldo buku bertambah
                // Kredit: Pendapatan Selisih Lebih Stok Opname (4-20001)
                $amount = $varianceCost;
                $surplusAccount = $this->getAccountByCode('4-20001', 'Pendapatan Selisih Lebih Stok Opname', 'revenue', 'credit');

                $this->createLine($entry, $inventoryAccount, $amount, 0, "Peningkatan Nilai Persediaan Stok Opname #{$stockOpname->opname_number}");
                $this->createLine($entry, $surplusAccount, 0, $amount, "Pendapatan Selisih Lebih Stok Opname #{$stockOpname->opname_number}");
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
     * Jurnal otomatis rekonsiliasi dan setoran pelunasan konsinyasi cabang (Inward / Outward).
     */
    public function journalConsignmentSettlement(Consignment $consignment, float $settledAmount, ?string $memo = null): ?JournalEntry
    {
        if ($settledAmount <= 0.0) {
            return null;
        }

        return DB::transaction(function () use ($consignment, $settledAmount, $memo): JournalEntry {
            $entryDate = Carbon::today()->toDateString();
            $entryNumber = $this->generateEntryNumber('JRN-CSG', Carbon::parse($entryDate));

            $branchName = $consignment->branch->name ?? 'Cabang';
            $partnerName = $consignment->partner_name;
            $typeLabel = $consignment->isInward() ? 'Titipan Vendor' : 'Titipan Reseller';

            $desc = "Setoran Konsinyasi {$typeLabel} #{$consignment->consignment_number} ({$partnerName}) - {$branchName}";
            if ($memo) {
                $desc .= ": {$memo}";
            }

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $entryDate,
                'reference_type' => Consignment::class,
                'reference_id' => $consignment->id,
                'branch_id' => $consignment->branch_id,
                'creator_employee_id' => null,
                'description' => substr($desc, 0, 255),
                'status' => 'posted',
                'total_debit' => 0,
                'total_credit' => 0,
            ]);

            $cashAccount = $this->getAccountByCode('1-10001', 'Kas Laci Kasir (Outlet)', 'asset', 'debit');

            if ($consignment->isInward()) {
                // KONSINYASI MASUK (INWARD): Toko membayarkan setoran hak supplier/vendor
                // Debit: Hutang Titipan Konsinyasi Supplier (2-10003) - Hutang berkurang
                // Kredit: Kas Laci Kasir / Bank (1-10001) - Kas toko keluar
                $payableAccount = $this->getAccountByCode('2-10003', 'Hutang Konsinyasi Supplier', 'liability', 'credit');

                $this->createLine($entry, $payableAccount, $settledAmount, 0, "Pelunasan Hutang Konsinyasi {$partnerName}");
                $this->createLine($entry, $cashAccount, 0, $settledAmount, "Pengeluaran Kas Setoran Konsinyasi {$partnerName}");
            } else {
                // KONSINYASI KELUAR (OUTWARD): Toko menerima uang setoran hasil penjualan dari Mitra/Reseller
                // Debit: Kas Laci Kasir / Bank (1-10001) - Kas bertambah
                // Kredit: Pendapatan Penjualan Konsinyasi (4-10001) - Pendapatan diakui
                $revenueAccount = $this->getAccountByCode('4-10001', 'Pendapatan Penjualan Konsinyasi', 'revenue', 'credit');

                $this->createLine($entry, $cashAccount, $settledAmount, 0, "Penerimaan Kas Setoran Reseller {$partnerName}");
                $this->createLine($entry, $revenueAccount, 0, $settledAmount, "Pendapatan Konsinyasi Reseller {$partnerName}");
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
     * Generate format nomor jurnal urut harian.
     */
    private function generateEntryNumber(string $prefix, Carbon $date): string
    {
        $dateStr = $date->format('Ymd');
        $count = JournalEntry::whereDate('entry_date', $date->toDateString())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $dateStr, $count);
    }
}
