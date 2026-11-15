<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunDatabaseBackupJob;
use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupService;
use App\Services\SystemMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller Monitoring Jadwal Cron, Queue Worker & Backup Database Otomatis.
 */
class AdminSystemMonitorController extends Controller
{
    public function __construct(
        protected SystemMonitoringService $monitoringService,
        protected DatabaseBackupService $backupService
    ) {}

    /**
     * Tampilkan dashboard monitoring sistem (Cron, Queue, Backup Database).
     */
    public function index(Request $request): View|JsonResponse
    {
        $cronStatuses = $this->monitoringService->getCronStatuses();
        $queueSummary = $this->monitoringService->getQueueSummary();
        $backupStats = $this->monitoringService->getBackupStats();
        $backups = Schema::hasTable('database_backups')
            ? DatabaseBackup::orderByDesc('created_at')->limit(20)->get()
            : collect();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cron_statuses' => $cronStatuses,
                'queue_summary' => $queueSummary,
                'backup_stats' => $backupStats,
                'backups' => $backups,
            ]);
        }

        return view('admin.erp.system_monitor', compact(
            'cronStatuses',
            'queueSummary',
            'backupStats',
            'backups'
        ));
    }

    /**
     * Memicu backup database secara manual (sinkron atau via Queue).
     */
    public function triggerBackup(Request $request): RedirectResponse
    {
        $this->forbidAuditor($request);

        $mode = (string) $request->input('mode', 'sync');
        $userId = $request->user()?->id;

        if ($mode === 'queue') {
            RunDatabaseBackupJob::dispatch('manual', $userId);
            $this->monitoringService->recordAuditLog('system_backup_queued', null, [
                'mode' => 'queue',
                'triggered_by' => $userId,
            ], $userId);

            return redirect()
                ->route('admin.erp.system-monitor')
                ->with('success', 'Tugas backup database telah dimasukkan ke antrean Queue Worker.');
        }

        $backup = $this->backupService->runBackup('manual', $userId);
        $this->monitoringService->recordAuditLog('system_backup_manual', $backup->id, [
            'file_name' => $backup->file_name,
            'status' => $backup->status,
            'size_bytes' => $backup->file_size_bytes,
            'duration_ms' => $backup->duration_ms,
        ], $userId);

        if ($backup->status !== 'success') {
            return redirect()
                ->route('admin.erp.system-monitor')
                ->with('error', 'Backup database gagal: '.($backup->error_message ?: 'Unknown error'));
        }

        return redirect()
            ->route('admin.erp.system-monitor')
            ->with('success', "Backup database berhasil dibuat: {$backup->file_name} ({$backup->formatted_size}).");
    }

    /**
     * Unduh file backup database terkompresi.
     */
    public function downloadBackup(Request $request, DatabaseBackup $backup): BinaryFileResponse
    {
        $this->forbidAuditor($request, 'Akses Ditolak (SoD): Akun Auditor tidak diizinkan mengunduh dump mentah database.');

        if (! $backup->fileExists()) {
            abort(404, 'File backup fisik tidak ditemukan pada penyimpanan server.');
        }

        $this->monitoringService->recordAuditLog('system_backup_downloaded', $backup->id, [
            'file_name' => $backup->file_name,
            'checksum_sha256' => $backup->checksum_sha256,
        ], $request->user()?->id);

        $absolutePath = Storage::disk($backup->disk ?: 'local')->path($backup->file_path);

        return response()->download($absolutePath, $backup->file_name);
    }

    /**
     * Jalankan ulang 1 failed job berdasarkan UUID.
     */
    public function retryFailedJob(Request $request, string $uuid): RedirectResponse
    {
        $this->forbidAuditor($request);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        $this->monitoringService->recordAuditLog('system_queue_retry', null, [
            'job_uuid' => $uuid,
        ], $request->user()?->id);

        return redirect()
            ->route('admin.erp.system-monitor')
            ->with('success', "Job {$uuid} berhasil dikirim ulang ke antrean Queue.");
    }

    /**
     * Bersihkan seluruh daftar failed_jobs.
     */
    public function flushFailedJobs(Request $request): RedirectResponse
    {
        $this->forbidAuditor($request);

        Artisan::call('queue:flush');

        $this->monitoringService->recordAuditLog('system_queue_flushed', null, [
            'flushed_at' => now()->toIso8601String(),
        ], $request->user()?->id);

        return redirect()
            ->route('admin.erp.system-monitor')
            ->with('success', 'Seluruh daftar failed jobs telah dibersihkan.');
    }

    private function forbidAuditor(
        Request $request,
        string $message = 'Akses Ditolak (SoD): Akun Auditor dibatasi hak akses baca murni (Read-Only).'
    ): void {
        $user = $request->user();
        if ($user && method_exists($user, 'isAuditor') && $user->isAuditor()) {
            abort(403, $message);
        }
    }
}
