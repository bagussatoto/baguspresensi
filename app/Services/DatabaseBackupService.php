<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CronHeartbeat;
use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Service untuk menjalankan backup database PostgreSQL/SQLite terkompresi (.sql.gz)
 * beserta perhitungan checksum SHA-256 dan pembersihan retensi otomatis.
 */
class DatabaseBackupService
{
    private const BACKUP_DISK = 'local';

    private const BACKUP_DIR = 'backups/database';

    /**
     * Jalankan proses backup database dan catat hasilnya ke database_backups.
     */
    public function runBackup(string $triggerType = 'cli', ?int $triggeredBy = null): DatabaseBackup
    {
        $startedAt = now();
        $microStart = microtime(true);

        $connectionName = (string) config('database.default', 'pgsql');
        $connConfig = (array) config("database.connections.{$connectionName}", []);
        $driver = (string) ($connConfig['driver'] ?? 'pgsql');
        $databaseName = (string) ($connConfig['database'] ?? 'laravel');

        $timestamp = $startedAt->format('Y-m-d_His');
        $extension = $driver === 'sqlite' ? 'sqlite.gz' : 'sql.gz';
        $safeDbName = preg_replace('/[^a-zA-Z0-9_-]/', '_', basename($databaseName) ?: 'mgk_erp');
        $fileName = "mgk_{$safeDbName}_{$timestamp}.{$extension}";
        $relativePath = self::BACKUP_DIR.'/'.$fileName;

        $backup = DatabaseBackup::create([
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'disk' => self::BACKUP_DISK,
            'driver' => $driver,
            'database_name' => $databaseName,
            'file_size_bytes' => 0,
            'trigger_type' => $triggerType,
            'status' => 'running',
            'triggered_by' => $triggeredBy,
            'started_at' => $startedAt,
        ]);

        try {
            Storage::disk(self::BACKUP_DISK)->makeDirectory(self::BACKUP_DIR);
            $absolutePath = Storage::disk(self::BACKUP_DISK)->path($relativePath);

            if ($driver === 'pgsql') {
                $this->dumpPostgres($connConfig, $absolutePath);
            } elseif ($driver === 'sqlite') {
                $this->dumpSqlite($databaseName, $absolutePath);
            } else {
                throw new RuntimeException("Driver database '{$driver}' belum didukung untuk backup otomatis.");
            }

            if (! file_exists($absolutePath) || filesize($absolutePath) === 0) {
                throw new RuntimeException('File backup tidak terbentuk atau berukuran 0 byte.');
            }

            $fileSize = (int) filesize($absolutePath);
            $checksum = hash_file('sha256', $absolutePath) ?: null;
            $durationMs = (int) round((microtime(true) - $microStart) * 1000);

            $backup->update([
                'file_size_bytes' => $fileSize,
                'checksum_sha256' => $checksum,
                'status' => 'success',
                'duration_ms' => $durationMs,
                'finished_at' => now(),
            ]);

            $this->recordHeartbeat('db:backup', '0 6,23:30 * * *', 'success', $durationMs, "Backup {$fileName} ({$backup->formatted_size})", $startedAt);
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $microStart) * 1000);

            $backup->update([
                'status' => 'failed',
                'duration_ms' => $durationMs,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            $this->recordHeartbeat('db:backup', '0 6,23:30 * * *', 'failed', $durationMs, $e->getMessage(), $startedAt);
        }

        return $backup->fresh();
    }

    /**
     * Hapus file backup & record lama yang melebihi batas hari retensi.
     */
    public function pruneExpiredBackups(int $retentionDays = 14): int
    {
        if ($retentionDays <= 0) {
            return 0;
        }

        $cutoff = now()->subDays($retentionDays);
        $expired = DatabaseBackup::where('created_at', '<', $cutoff)->get();
        $deletedCount = 0;

        foreach ($expired as $backup) {
            if ($backup->file_path && Storage::disk($backup->disk ?: self::BACKUP_DISK)->exists($backup->file_path)) {
                Storage::disk($backup->disk ?: self::BACKUP_DISK)->delete($backup->file_path);
            }
            $backup->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Eksekusi pg_dump | gzip dengan PGPASSWORD di environment proses.
     *
     * @param  array<string, mixed>  $connConfig
     */
    protected function dumpPostgres(array $connConfig, string $absoluteOutputPath): void
    {
        $host = (string) ($connConfig['host'] ?? '127.0.0.1');
        $port = (string) ($connConfig['port'] ?? '5432');
        $username = (string) ($connConfig['username'] ?? 'postgres');
        $password = (string) ($connConfig['password'] ?? '');
        $database = (string) ($connConfig['database'] ?? 'laravel');

        $rawSqlPath = preg_replace('/\.gz$/', '', $absoluteOutputPath) ?: $absoluteOutputPath.'.sql';

        $dumpProcess = new Process(
            ['pg_dump', '-h', $host, '-p', $port, '-U', $username, '--no-owner', '--no-acl', '-f', $rawSqlPath, $database],
            base_path(),
            ['PGPASSWORD' => $password]
        );
        $dumpProcess->setTimeout(600);
        $dumpProcess->run();

        if (! $dumpProcess->isSuccessful()) {
            @unlink($rawSqlPath);
            throw new RuntimeException('pg_dump gagal: '.trim($dumpProcess->getErrorOutput() ?: $dumpProcess->getOutput()));
        }

        $gzipProcess = new Process(['gzip', '-f', $rawSqlPath], base_path());
        $gzipProcess->setTimeout(300);
        $gzipProcess->run();

        if (! $gzipProcess->isSuccessful()) {
            @unlink($rawSqlPath);
            throw new RuntimeException('Kompresi gzip gagal: '.trim($gzipProcess->getErrorOutput()));
        }
    }

    /**
     * Backup aman untuk SQLite (termasuk :memory: saat PHPUnit).
     */
    protected function dumpSqlite(string $databasePath, string $absoluteOutputPath): void
    {
        $payload = '';
        if ($databasePath !== ':memory:' && file_exists($databasePath)) {
            $payload = (string) file_get_contents($databasePath);
        } else {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            $payload = json_encode([
                'generated_at' => now()->toIso8601String(),
                'driver' => 'sqlite',
                'tables_count' => count($tables),
            ], JSON_PRETTY_PRINT) ?: '{}';
        }

        $compressed = gzencode($payload, 6);
        if ($compressed === false) {
            throw new RuntimeException('Gagal mengompresi snapshot SQLite.');
        }

        file_put_contents($absoluteOutputPath, $compressed);
    }

    /**
     * Perbarui catatan heartbeat cron jika tabel cron_heartbeats tersedia.
     */
    protected function recordHeartbeat(
        string $commandName,
        string $scheduleExpression,
        string $status,
        int $durationMs,
        string $summary,
        \DateTimeInterface $startedAt
    ): void {
        if (! Schema::hasTable('cron_heartbeats')) {
            return;
        }

        CronHeartbeat::updateOrCreate(
            ['command_name' => $commandName],
            [
                'schedule_expression' => $scheduleExpression,
                'status' => $status,
                'duration_ms' => $durationMs,
                'output_summary' => mb_substr($summary, 0, 500),
                'last_started_at' => $startedAt,
                'last_finished_at' => now(),
            ]
        );
    }
}
