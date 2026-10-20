<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Audit Trail Forensik (ISO 27001)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #277799;
            --subnav-active: #195673;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--body-bg); color: var(--text-dark); }
        .main-header { background: linear-gradient(to bottom, var(--header-blue), var(--header-dark-blue)); color: #fff; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
        .sub-navbar { background-color: var(--subnav-blue); padding: 0 20px; display: flex; overflow-x: auto; white-space: nowrap; }
        .subnav-item { color: #eaf2f8; text-decoration: none; padding: 10px 14px; font-size: 13px; font-weight: 500; text-transform: uppercase; border-right: 1px solid rgba(255,255,255,0.1); }
        .subnav-item:hover { background: rgba(0,0,0,0.15); color: #fff; }
        .subnav-item.active { background-color: var(--subnav-active); color: #fff; font-weight: 700; }
        .container { max-width: 1400px; margin: 24px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--content-border); box-shadow: 0 2px 6px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 16px 20px; background: #fafbfc; border-bottom: 1px solid var(--content-border); display: flex; justify-content: space-between; align-items: center; }
        .card-title { font-size: 17px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .badge-iso { background: #0284c7; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 700; }
        .filter-form { padding: 16px 20px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .input-text, .select-box { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; }
        .input-text:focus, .select-box:focus { border-color: #0284c7; }
        .btn-filter { background: #0284c7; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        th { background: #f8fafc; padding: 12px 14px; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:hover td { background-color: #f8fafc; }
        .action-tag { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; display: inline-block; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        pre.json-diff { font-family: monospace; font-size: 11px; background: #0f172a; color: #38bdf8; padding: 8px; border-radius: 6px; max-width: 320px; max-height: 140px; overflow: auto; margin-top: 4px; }
        .worm-tag { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; }
    </style>
</head>
<body>
    @include('admin.partials.erp_navbar')

    <div class="container" style="padding-top: 100px;">
        <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px;">
            <a href="{{ route('admin.presensi') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">📅 Presensi Hari Ini</a>
            <a href="{{ route('admin.presensi.rekap') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">📊 Rekapitulasi Bulanan</a>
            <a href="{{ route('admin.presensi.approval') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">📝 Approval Cuti & Lembur</a>
            <a href="{{ route('admin.presensi.audit-logs') }}" style="padding: 7px 14px; background: #0284c7; color: #ffffff; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 700;">🛡️ Audit Trail (ISO 27001)</a>
            <a href="{{ route('admin.presensi.pengaturan') }}" style="padding: 7px 14px; background: #e0f2fe; color: #0369a1; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">⚙️ Pengaturan Presensi</a>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>🛡️ Jejak Forensik Mutasi Sistem (Audit Trail)</span>
                    <span class="badge-iso">ISO/IEC 27001:2022 A.8.15</span>
                    <span class="worm-tag">WORM Guard: Active</span>
                </div>
            </div>

            <form action="{{ route('admin.presensi.audit-logs') }}" method="GET" class="filter-form">
                <input type="text" name="search" class="input-text" placeholder="Cari aksi, IP, nama admin..." value="{{ request('search') }}">
                
                <select name="action" class="select-box">
                    <option value="">-- Semua Jenis Aksi --</option>
                    @foreach($actionLabels as $key => $label)
                        <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>
                            {{ $label }} ({{ $key }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-filter">Filter Data</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu (WIB)</th>
                            <th>Pelaku Mutasi</th>
                            <th>Aksi Audit</th>
                            <th>Model / Target</th>
                            <th>Nilai Lama (Old Values)</th>
                            <th>Nilai Baru (New Values)</th>
                            <th>IP &amp; User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="white-space: nowrap; font-weight: 600;">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td>
                                    <strong>{{ $log->user?->name ?? 'System / Karyawan' }}</strong>
                                    <div style="font-size: 11px; color: #64748b;">
                                        {{ $log->user?->email ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="action-tag">{{ $actionLabels[$log->action] ?? $log->action }}</span>
                                </td>
                                <td>
                                    <span style="font-size: 11px; color: #475569;">
                                        {{ class_basename($log->auditable_type ?? 'None') }} #{{ $log->auditable_id ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->old_values)
                                        <pre class="json-diff">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">null</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->new_values)
                                        <pre class="json-diff" style="color: #34d399;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">null</span>
                                    @endif
                                </td>
                                <td style="font-size: 11px; color: #64748b;">
                                    <div><strong>IP:</strong> {{ $log->ip_address }}</div>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #94a3b8;">
                                    Belum ada catatan log audit yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding: 16px 20px;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</body>
</html>
