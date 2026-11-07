<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Kas\Domain\Services\AutoJournalService;
use Modules\MasterData\Domain\Models\CafeSale;

class SyncHistoricalAccountingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:sync-sales {--limit=100 : Jumlah transaksi yang diproses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi seluruh transaksi penjualan POS historis ke Jurnal Umum Akuntansi (General Journal)';

    /**
     * Execute the console command.
     */
    public function handle(AutoJournalService $journalService): int
    {
        $this->info('Memulai sinkronisasi transaksi penjualan POS ke Jurnal Umum Akuntansi...');

        $sales = CafeSale::with(['items.variant.product'])
            ->where('payment_status', '!=', 'cancelled')
            ->orderBy('id', 'asc')
            ->take((int) $this->option('limit'))
            ->get();

        $count = 0;
        foreach ($sales as $sale) {
            $journalService->journalSale($sale);
            $count++;
        }

        $this->info("Berhasil menjurnal {$count} transaksi penjualan ke Jurnal Umum!");

        $journalService->recalculateAllAccountBalances();
        $this->info('Seluruh saldo akun COA berhasil disinkronkan.');

        return Command::SUCCESS;
    }
}
