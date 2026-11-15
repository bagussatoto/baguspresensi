<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--trigger=cli : Sumber pemicu backup (cli|scheduled|manual)}
                            {--retention=14 : Jumlah hari retensi file backup lama}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan backup database terkompresi (.sql.gz) dan membersihkan backup kadaluarsa.';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $trigger = (string) $this->option('trigger');
        $retentionDays = max(1, (int) $this->option('retention'));

        $this->info("Memulai backup database (trigger: {$trigger})...");
        $backup = $backupService->runBackup($trigger);

        if ($backup->status !== 'success') {
            $this->error('Backup gagal: '.($backup->error_message ?: 'Unknown error'));

            return self::FAILURE;
        }

        $pruned = $backupService->pruneExpiredBackups($retentionDays);
        $this->info("Backup berhasil: {$backup->file_name} ({$backup->formatted_size}, {$backup->duration_ms} ms). Dihapus kadaluarsa: {$pruned}.");

        return self::SUCCESS;
    }
}
