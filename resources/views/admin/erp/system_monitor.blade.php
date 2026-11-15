@extends('admin.dashboard.layout')

@section('title', 'Monitoring Cron, Queue & Backup Database')

@section('content')
@php
    $isAuditor = auth()->user() && method_exists(auth()->user(), 'isAuditor') && auth()->user()->isAuditor();
@endphp

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Monitoring Jadwal Cron, Queue &amp; Backup Database</h1>
        <p class="hero-desc-text">
            Pemantauan kesehatan <strong>Laravel Scheduler</strong>, antrean <strong>Queue Worker</strong> (<code>{{ $queueSummary['connection'] }}</code>), serta riwayat <strong>Backup PostgreSQL</strong> otomatis sebelum &amp; sesudah operasional cabang.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        @if(!$isAuditor)
            <form method="POST" action="{{ route('admin.erp.system-monitor.backup') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="mode" value="sync">
                <button type="submit" class="btn-action-sm" style="padding: 10px 16px; font-size: 13px; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.45); color: #34d399; cursor: pointer;">
                    <span>💾</span> Backup Sekarang
                </button>
            </form>
            <form method="POST" action="{{ route('admin.erp.system-monitor.backup') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="mode" value="queue">
                <button type="submit" class="btn-action-sm" style="padding: 10px 16px; font-size: 13px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; cursor: pointer;">
                    <span>⚡</span> Antrekan via Queue
                </button>
            </form>
        @else
            <span style="padding: 8px 14px; border-radius: 10px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24; font-size: 12px; font-weight: 700;">
                🔒 Mode Auditor (Read-Only)
            </span>
        @endif
        <a href="{{ route('admin.erp') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 16px; font-size: 13px;">
            <span>🏛️</span> Pusat Kendali ERP
        </a>
    </div>
</div>

@if(session('success'))
<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
    ✅ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
    ⚠️ {{ session('error') }}
</div>
@endif

<!-- KPI SUMMARY GRID -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 14px; padding: 18px;">
        <div style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 6px;">BACKUP SUKSES TERAKHIR</div>
        <div style="font-size: 20px; font-weight: 800; color: #f8fafc;">
            {{ $backupStats['latest_success']?->finished_at?->format('d M Y H:i') ?? 'Belum Ada' }}
        </div>
        <div style="font-size: 12px; color: #34d399; margin-top: 4px;">
            {{ $backupStats['latest_success'] ? $backupStats['latest_success']->formatted_size.' ('.$backupStats['latest_success']->driver.')' : 'Jalankan backup perdana' }}
        </div>
    </div>

    <div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 14px; padding: 18px;">
        <div style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 6px;">TOTAL ARSIP BACKUP</div>
        <div style="font-size: 20px; font-weight: 800; color: #f8fafc;">
            {{ $backupStats['success_count'] }} Berhasil <span style="font-size: 13px; color: #fb7185; font-weight: 600;">/ {{ $backupStats['failed_count'] }} Gagal</span>
        </div>
        <div style="font-size: 12px; color: #38bdf8; margin-top: 4px;">
            Terpakai: {{ $backupStats['formatted_total_size'] }} &bull; Disk Kosong: {{ $backupStats['formatted_free_disk'] }}
        </div>
    </div>

    <div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 14px; padding: 18px;">
        <div style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 6px;">ANTREAN QUEUE AKTIF</div>
        <div style="font-size: 20px; font-weight: 800; color: #f8fafc;">
            {{ $queueSummary['pending_count'] }} Job Menunggu
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
            Driver: <code>{{ $queueSummary['connection'] }}</code>
        </div>
    </div>

    <div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 14px; padding: 18px;">
        <div style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 6px;">QUEUE FAILED JOBS</div>
        <div style="font-size: 20px; font-weight: 800; color: {{ $queueSummary['failed_count'] > 0 ? '#fb7185' : '#34d399' }};">
            {{ $queueSummary['failed_count'] }} Gagal
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
            {{ $queueSummary['failed_count'] > 0 ? 'Perlu peninjauan / retry' : 'Semua worker berjalan bersih' }}
        </div>
    </div>
</div>

<!-- JADWAL CRON AKTIF -->
<div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #f8fafc;">⏱️ Jadwal Cron Terdaftar (Laravel Scheduler)</h2>
        <span style="font-size: 12px; color: #94a3b8;">Sumber: <code>routes/console.php</code></span>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.2); color: #94a3b8; text-align: left;">
                    <th style="padding: 10px 12px;">Command</th>
                    <th style="padding: 10px 12px;">Jadwal</th>
                    <th style="padding: 10px 12px;">Status Terakhir</th>
                    <th style="padding: 10px 12px;">Eksekusi Terakhir</th>
                    <th style="padding: 10px 12px;">Ringkasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cronStatuses as $cron)
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.1);">
                    <td style="padding: 12px;">
                        <div style="font-weight: 700; color: #f8fafc;">{{ $cron['label'] }}</div>
                        <code style="font-size: 11px; color: #38bdf8;">{{ $cron['command_name'] }}</code>
                    </td>
                    <td style="padding: 12px; color: #cbd5e1;">{{ $cron['schedule'] }}</td>
                    <td style="padding: 12px;">
                        @if($cron['status'] === 'success')
                            <span style="padding: 4px 10px; border-radius: 999px; background: rgba(16, 185, 129, 0.18); color: #34d399; font-weight: 700; font-size: 11px;">SUKSES</span>
                        @elseif($cron['status'] === 'failed')
                            <span style="padding: 4px 10px; border-radius: 999px; background: rgba(244, 63, 94, 0.18); color: #fb7185; font-weight: 700; font-size: 11px;">GAGAL</span>
                        @else
                            <span style="padding: 4px 10px; border-radius: 999px; background: rgba(148, 163, 184, 0.18); color: #cbd5e1; font-weight: 700; font-size: 11px;">MENUNGGU</span>
                        @endif
                    </td>
                    <td style="padding: 12px; color: #cbd5e1;">
                        {{ $cron['last_finished_at'] ? $cron['last_finished_at']->format('d M Y H:i:s') : '-' }}
                        @if($cron['duration_ms'] !== null)
                            <div style="font-size: 11px; color: #94a3b8;">{{ $cron['duration_ms'] }} ms</div>
                        @endif
                    </td>
                    <td style="padding: 12px; color: #94a3b8; max-width: 320px;">{{ $cron['output_summary'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- RIWAYAT BACKUP DATABASE -->
<div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #f8fafc;">💾 Riwayat Backup Database (20 Terbaru)</h2>
        <span style="font-size: 12px; color: #94a3b8;">Retensi otomatis: 14 hari</span>
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.2); color: #94a3b8; text-align: left;">
                    <th style="padding: 10px 12px;">File Backup</th>
                    <th style="padding: 10px 12px;">Pemicu</th>
                    <th style="padding: 10px 12px;">Ukuran</th>
                    <th style="padding: 10px 12px;">Checksum (SHA-256)</th>
                    <th style="padding: 10px 12px;">Status</th>
                    <th style="padding: 10px 12px;">Waktu</th>
                    <th style="padding: 10px 12px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups as $backup)
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.1);">
                    <td style="padding: 12px;">
                        <div style="font-weight: 700; color: #f8fafc;">{{ $backup->file_name }}</div>
                        <div style="font-size: 11px; color: #94a3b8;">{{ $backup->driver }} &bull; {{ $backup->database_name }}</div>
                    </td>
                    <td style="padding: 12px; color: #cbd5e1; text-transform: uppercase; font-size: 11px; font-weight: 700;">{{ $backup->trigger_type }}</td>
                    <td style="padding: 12px; color: #cbd5e1;">{{ $backup->formatted_size }}</td>
                    <td style="padding: 12px;">
                        @if($backup->checksum_sha256)
                            <code style="font-size: 11px; color: #38bdf8;" title="{{ $backup->checksum_sha256 }}">{{ substr($backup->checksum_sha256, 0, 16) }}...</code>
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px;">
                        @if($backup->status === 'success')
                            <span style="padding: 4px 10px; border-radius: 999px; background: rgba(16, 185, 129, 0.18); color: #34d399; font-weight: 700; font-size: 11px;">SUKSES</span>
                        @else
                            <span style="padding: 4px 10px; border-radius: 999px; background: rgba(244, 63, 94, 0.18); color: #fb7185; font-weight: 700; font-size: 11px;" title="{{ $backup->error_message }}">GAGAL</span>
                        @endif
                    </td>
                    <td style="padding: 12px; color: #cbd5e1;">
                        {{ $backup->created_at?->format('d M Y H:i') }}
                        @if($backup->duration_ms !== null)
                            <div style="font-size: 11px; color: #94a3b8;">{{ $backup->duration_ms }} ms</div>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: right;">
                        @if(!$isAuditor && $backup->status === 'success' && $backup->fileExists())
                            <a href="{{ route('admin.erp.system-monitor.download', $backup) }}" class="btn-action-sm" style="padding: 6px 12px; font-size: 12px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; text-decoration: none;">
                                📥 Unduh
                            </a>
                        @elseif($isAuditor)
                            <span style="font-size: 11px; color: #64748b;">Dibatasi (SoD)</span>
                        @else
                            <span style="font-size: 11px; color: #64748b;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding: 20px; text-align: center; color: #94a3b8;">
                        Belum ada riwayat backup database. Klik <strong>Backup Sekarang</strong> untuk membuat snapshot perdana.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- QUEUE FAILED JOBS -->
<div style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 16px; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #f8fafc;">⚠️ Antrean Gagal (Failed Jobs)</h2>
        @if(!$isAuditor && $queueSummary['failed_count'] > 0)
            <form method="POST" action="{{ route('admin.erp.system-monitor.queue-flush') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-action-sm" style="padding: 7px 14px; font-size: 12px; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185; cursor: pointer;">
                    🗑️ Bersihkan Semua
                </button>
            </form>
        @endif
    </div>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.2); color: #94a3b8; text-align: left;">
                    <th style="padding: 10px 12px;">UUID</th>
                    <th style="padding: 10px 12px;">Queue</th>
                    <th style="padding: 10px 12px;">Error Ringkas</th>
                    <th style="padding: 10px 12px;">Waktu Gagal</th>
                    <th style="padding: 10px 12px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($queueSummary['failed_jobs'] as $job)
                <tr style="border-bottom: 1px solid rgba(148, 163, 184, 0.1);">
                    <td style="padding: 12px;"><code style="font-size: 11px; color: #38bdf8;">{{ $job->uuid }}</code></td>
                    <td style="padding: 12px; color: #cbd5e1;">{{ $job->queue }}</td>
                    <td style="padding: 12px; color: #fb7185; max-width: 420px;">{{ \Illuminate\Support\Str::limit($job->exception, 140) }}</td>
                    <td style="padding: 12px; color: #cbd5e1;">{{ $job->failed_at }}</td>
                    <td style="padding: 12px; text-align: right;">
                        @if(!$isAuditor)
                            <form method="POST" action="{{ route('admin.erp.system-monitor.queue-retry', $job->uuid) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-action-sm" style="padding: 6px 12px; font-size: 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; cursor: pointer;">
                                    🔄 Retry
                                </button>
                            </form>
                        @else
                            <span style="font-size: 11px; color: #64748b;">Read-Only</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 18px; text-align: center; color: #94a3b8;">
                        Tidak ada failed job saat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
