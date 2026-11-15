<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\DatabaseBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunDatabaseBackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(
        public readonly string $triggerType = 'manual',
        public readonly ?int $triggeredBy = null,
        public readonly int $retentionDays = 14
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DatabaseBackupService $backupService): void
    {
        $backup = $backupService->runBackup($this->triggerType, $this->triggeredBy);

        if ($backup->status === 'success') {
            $backupService->pruneExpiredBackups($this->retentionDays);
        }
    }
}
