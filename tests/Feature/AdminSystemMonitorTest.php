<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\AdminRole;
use App\Models\DatabaseBackup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSystemMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_system_monitor_dashboard_and_json(): void
    {
        $response = $this->get(route('admin.erp.system-monitor'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Jadwal Cron, Queue');
        $response->assertSee('db:backup');
        $response->assertSee('attendance:close-daily');
        $response->assertSee('registrations:prune-expired');

        $jsonResponse = $this->getJson(route('admin.erp.system-monitor'));
        $jsonResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'cron_statuses',
                'queue_summary' => ['connection', 'pending_count', 'failed_count', 'failed_jobs'],
                'backup_stats',
                'backups',
            ]);
    }

    public function test_admin_can_trigger_manual_database_backup_and_download_it(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => AdminRole::SuperAdmin]);

        $response = $this->actingAs($admin)->post(route('admin.erp.system-monitor.backup'), [
            'mode' => 'sync',
        ]);

        $response->assertRedirect(route('admin.erp.system-monitor'));
        $response->assertSessionHas('success');

        $backup = DatabaseBackup::latest('id')->first();
        $this->assertNotNull($backup);
        $this->assertSame('success', $backup->status);
        $this->assertSame('manual', $backup->trigger_type);
        $this->assertGreaterThan(0, $backup->file_size_bytes);
        $this->assertNotNull($backup->checksum_sha256);
        Storage::disk('local')->assertExists($backup->file_path);

        $this->assertDatabaseHas('cron_heartbeats', [
            'command_name' => 'db:backup',
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system_backup_manual',
            'auditable_id' => $backup->id,
        ]);

        $downloadResponse = $this->actingAs($admin)->get(route('admin.erp.system-monitor.download', $backup));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-disposition');
    }

    public function test_db_backup_command_prunes_expired_backups(): void
    {
        Storage::fake('local');

        $expiredPath = 'backups/database/expired_backup.sql.gz';
        Storage::disk('local')->put($expiredPath, 'old-dump');

        $expired = DatabaseBackup::create([
            'file_name' => 'expired_backup.sql.gz',
            'file_path' => $expiredPath,
            'disk' => 'local',
            'driver' => 'sqlite',
            'database_name' => ':memory:',
            'file_size_bytes' => 8,
            'trigger_type' => 'scheduled',
            'status' => 'success',
        ]);
        $expired->forceFill(['created_at' => now()->subDays(20)])->save();

        $this->artisan('db:backup', ['--trigger' => 'scheduled', '--retention' => 14])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('database_backups', ['id' => $expired->id]);
        Storage::disk('local')->assertMissing($expiredPath);
        $this->assertSame(1, DatabaseBackup::where('status', 'success')->count());
    }

    public function test_auditor_is_read_only_and_cannot_trigger_or_download_backup(): void
    {
        Storage::fake('local');

        $backupPath = 'backups/database/protected_backup.sql.gz';
        Storage::disk('local')->put($backupPath, gzencode('sensitive-dump') ?: 'data');

        $backup = DatabaseBackup::create([
            'file_name' => 'protected_backup.sql.gz',
            'file_path' => $backupPath,
            'disk' => 'local',
            'driver' => 'sqlite',
            'database_name' => ':memory:',
            'file_size_bytes' => 20,
            'trigger_type' => 'scheduled',
            'status' => 'success',
        ]);

        $auditor = User::factory()->create(['role' => AdminRole::Auditor]);

        $this->actingAs($auditor)
            ->get(route('admin.erp.system-monitor'))
            ->assertStatus(200)
            ->assertSee('Mode Auditor (Read-Only)');

        $this->actingAs($auditor)
            ->post(route('admin.erp.system-monitor.backup'), ['mode' => 'sync'])
            ->assertStatus(403);

        $this->actingAs($auditor)
            ->get(route('admin.erp.system-monitor.download', $backup))
            ->assertStatus(403);

        $this->actingAs($auditor)
            ->post(route('admin.erp.system-monitor.queue-flush'))
            ->assertStatus(403);
    }
}
