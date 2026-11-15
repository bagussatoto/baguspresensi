<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CronHeartbeat;
use App\Models\DatabaseBackup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Service agregasi status Cron Scheduler, Queue Worker (jobs & failed_jobs),
 * serta statistik Backup Database untuk dashboard Pusat Kendali ERP.
 */
class SystemMonitoringService
{
    /**
     * Daftar jadwal cron resmi yang terdaftar pada routes/console.php.
     *
     * @return array<string, array{label: string, schedule: string, description: string}>
     */
    public function getScheduledCommandsCatalog(): array
    {
        return [
            'db:backup' => [
                'label' => 'Backup Database Otomatis',
                'schedule' => '06:00 & 23:30 WIB (Harian)',
                'description' => 'Dump database PostgreSQL terkompresi (.sql.gz) + retensi 14 hari.',
            ],
            'attendance:close-daily' => [
                'label' => 'Tutup Presensi Harian',
                'schedule' => '23:59 WIB (Harian)',
                'description' => 'Menutup sesi presensi harian & menandai ketidakhadiran otomatis.',
            ],
            'registrations:prune-expired' => [
                'label' => 'Pembersihan Registrasi Kadaluarsa',
                'schedule' => '01:00 WIB (Harian)',
                'description' => 'Menghapus pengajuan registrasi karyawan yang melewati masa berlaku.',
            ],
        ];
    }

    /**
     * Ambil status gabungan seluruh jadwal cron.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCronStatuses(): array
    {
        $catalog = $this->getScheduledCommandsCatalog();
        $heartbeats = Schema::hasTable('cron_heartbeats')
            ? CronHeartbeat::all()->keyBy('command_name')
            : collect();

        $rows = [];
        foreach ($catalog as $commandName => $meta) {
            /** @var CronHeartbeat|null $hb */
            $hb = $heartbeats->get($commandName);
            $rows[] = [
                'command_name' => $commandName,
                'label' => $meta['label'],
                'schedule' => $meta['schedule'],
                'description' => $meta['description'],
                'status' => $hb?->status ?? 'idle',
                'duration_ms' => $hb?->duration_ms,
                'output_summary' => $hb?->output_summary ?? 'Belum ada catatan eksekusi.',
                'last_started_at' => $hb?->last_started_at,
                'last_finished_at' => $hb?->last_finished_at,
            ];
        }

        return $rows;
    }

    /**
     * Ambil ringkasan antrean Queue (jobs & failed_jobs).
     *
     * @return array{connection: string, pending_count: int, failed_count: int, failed_jobs: Collection<int, object>}
     */
    public function getQueueSummary(): array
    {
        $connection = (string) config('queue.default', 'database');
        $pendingCount = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $failedCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        $failedJobs = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->orderByDesc('failed_at')->limit(20)->get()
            : collect();

        return [
            'connection' => $connection,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
            'failed_jobs' => $failedJobs,
        ];
    }

    /**
     * Ambil statistik ringkas backup database dan kapasitas penyimpanan.
     *
     * @return array<string, mixed>
     */
    public function getBackupStats(): array
    {
        $totalCount = Schema::hasTable('database_backups') ? DatabaseBackup::count() : 0;
        $successCount = Schema::hasTable('database_backups') ? DatabaseBackup::where('status', 'success')->count() : 0;
        $failedCount = Schema::hasTable('database_backups') ? DatabaseBackup::where('status', 'failed')->count() : 0;
        $latestSuccess = Schema::hasTable('database_backups')
            ? DatabaseBackup::where('status', 'success')->latest('finished_at')->first()
            : null;
        $totalBytes = Schema::hasTable('database_backups')
            ? (int) DatabaseBackup::where('status', 'success')->sum('file_size_bytes')
            : 0;

        $backupDir = Storage::disk('local')->path('backups/database');
        $diskPath = is_dir($backupDir) ? $backupDir : storage_path('app');
        $freeBytes = @disk_free_space($diskPath) ?: 0;

        return [
            'total_count' => $totalCount,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'latest_success' => $latestSuccess,
            'total_bytes' => $totalBytes,
            'formatted_total_size' => $this->formatBytes($totalBytes),
            'free_disk_bytes' => (int) $freeBytes,
            'formatted_free_disk' => $this->formatBytes((int) $freeBytes),
        ];
    }

    /**
     * Catat aksi sensitif ke WORM Audit Log (audit_logs).
     *
     * @param  array<string, mixed>  $newValues
     */
    public function recordAuditLog(string $action, ?int $auditableId, array $newValues, ?int $userId = null): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        DB::table('audit_logs')->insert([
            'user_id' => $userId ?? auth()->id(),
            'auditable_type' => DatabaseBackup::class,
            'auditable_id' => $auditableId ?? 0,
            'action' => $action,
            'new_values' => json_encode($newValues),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'Admin ERP System',
            'created_at' => now(),
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 2).' '.$units[$power];
    }
}
