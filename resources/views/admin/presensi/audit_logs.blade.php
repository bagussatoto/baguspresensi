<!DOCTYPE html>
<html lang="{{ $lang ?? 'id' }}">
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
        .container { max-width: 1450px; margin: 24px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; border: 1px solid var(--content-border); box-shadow: 0 2px 6px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 16px 20px; background: #fafbfc; border-bottom: 1px solid var(--content-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .card-title { font-size: 17px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .badge-iso { background: #0284c7; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 700; }
        .filter-form { padding: 16px 20px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .input-text, .select-box { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none; }
        .input-text:focus, .select-box:focus { border-color: #0284c7; }
        .btn-filter { background: #0284c7; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        th { background: #f8fafc; padding: 12px 14px; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px; }
        td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:hover td { background-color: #f8fafc; }
        .action-tag { font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 6px; display: inline-block; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; line-height: 1.3; }
        pre.json-diff { font-family: monospace; font-size: 11px; background: #0f172a; color: #38bdf8; padding: 10px; border-radius: 6px; max-width: 340px; max-height: 180px; overflow: auto; margin-top: 6px; border: 1px solid #334155; }
        .worm-tag { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; }

        /* GAYA HUMAN READABLE AUDIT ENTRIES */
        .audit-readable-wrap {
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            line-height: 1.4;
        }
        .audit-readable-wrap.readable-new {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .audit-readable-wrap.readable-old {
            background: #fff7ed;
            border-color: #fed7aa;
        }
        .audit-item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            border-bottom: 1px dashed rgba(203, 213, 225, 0.6);
            padding-bottom: 4px;
        }
        .audit-item-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .audit-item-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            flex-shrink: 0;
            max-width: 140px;
        }
        .audit-item-val {
            color: #0f172a;
            font-weight: 700;
            font-size: 11.5px;
            text-align: right;
            word-break: break-word;
        }
        .audit-item-val.val-new {
            color: #15803d;
        }
        .audit-item-val.val-empty {
            color: #94a3b8;
            font-weight: normal;
            font-style: italic;
        }

        /* TOMBOL BAHASA & TOGGLE */
        .btn-lang {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            transition: all 0.2s;
        }
        .btn-lang:hover, .btn-lang.active {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }
        .btn-toggle-view {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #94a3b8;
            background: #f1f5f9;
            color: #334155;
            transition: all 0.2s;
        }
        .btn-toggle-view:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
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
                    <span>🛡️ {{ ($lang ?? 'id') === 'id' ? 'Jejak Forensik Mutasi Sistem (Audit Trail Baku)' : 'System Audit Trail & Forensic Log' }}</span>
                    <span class="badge-iso">ISO/IEC 27001:2022 A.8.15</span>
                    <span class="worm-tag">WORM Guard: Active</span>
                </div>

                {{-- SWITCH BAHASA BAKU (INDONESIA / ENGLISH) & VIEW MODE TOGGLE --}}
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-size: 11.5px; color: #64748b; font-weight: 700;">
                        {{ ($lang ?? 'id') === 'id' ? 'Standar Bahasa:' : 'Language:' }}
                    </span>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'id']) }}" class="btn-lang {{ ($lang ?? 'id') === 'id' ? 'active' : '' }}" title="Format Bahasa Indonesia Baku">
                        🇮🇩 Indonesia (Baku)
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="btn-lang {{ ($lang ?? 'id') === 'en' ? 'active' : '' }}" title="Standard ISO English">
                        🇬🇧 English (Standard)
                    </a>
                    <button type="button" onclick="toggleViewMode()" id="btn-toggle-view" class="btn-toggle-view" title="Alihkan tampilan antara Bahasa Baku Human-Readable dan Raw JSON">
                        <span id="view-mode-icon">📋</span> <span id="view-mode-label">{{ ($lang ?? 'id') === 'id' ? 'Buka Raw JSON' : 'Show Raw JSON' }}</span>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.presensi.audit-logs') }}" method="GET" class="filter-form">
                <input type="hidden" name="lang" value="{{ $lang ?? 'id' }}">
                <input type="text" name="search" class="input-text" placeholder="{{ ($lang ?? 'id') === 'id' ? 'Cari aksi, IP, nama admin...' : 'Search action, IP, user name...' }}" value="{{ request('search') }}" style="min-width: 250px;">
                
                <select name="action" class="select-box">
                    <option value="">-- {{ ($lang ?? 'id') === 'id' ? 'Semua Jenis Aksi' : 'All Action Types' }} --</option>
                    @foreach($actionLabels as $key => $label)
                        <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>
                            {{ $label }} ({{ $key }})
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-filter">
                    {{ ($lang ?? 'id') === 'id' ? 'Saring Log' : 'Filter Logs' }}
                </button>

                @if(request('search') || request('action'))
                    <a href="{{ route('admin.presensi.audit-logs', ['lang' => $lang ?? 'id']) }}" style="font-size: 12px; color: #64748b; text-decoration: none; padding: 6px 10px;">
                        ✕ {{ ($lang ?? 'id') === 'id' ? 'Reset Saringan' : 'Clear Filter' }}
                    </a>
                @endif
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'Waktu (WIB)' : 'Timestamp' }}</th>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'Pelaku Mutasi' : 'Actor / User' }}</th>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'Aksi Audit' : 'Audit Action' }}</th>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'Model Target' : 'Target Entity' }}</th>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'Nilai Lama (Sebelum)' : 'Old Values (Before)' }}</th>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'Nilai Baru (Sesudah)' : 'New Values (After)' }}</th>
                            <th>{{ ($lang ?? 'id') === 'id' ? 'IP & Perangkat' : 'IP & User Agent' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td style="white-space: nowrap; font-weight: 600; font-size: 12px;">
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
                                    <span style="font-size: 11px; color: #475569; font-weight: 600;">
                                        {{ class_basename($log->auditable_type ?? 'None') }}
                                    </span>
                                    <div style="font-size: 10.5px; color: #94a3b8; font-family: monospace;">
                                        #{{ $log->auditable_id ?? '-' }}
                                    </div>
                                </td>

                                {{-- NILAI LAMA (OLD VALUES) HUMAN READABLE --}}
                                <td style="min-width: 240px; max-width: 320px;">
                                    @php
                                        $oldEntries = $log->formattedOldValues($lang ?? 'id');
                                    @endphp

                                    @if(!empty($oldEntries))
                                        <div class="audit-readable-wrap readable-old readable-view">
                                            @foreach($oldEntries as $entry)
                                                <div class="audit-item-row">
                                                    <span class="audit-item-label">{{ $entry['label'] }}:</span>
                                                    <span class="audit-item-val {{ $entry['is_empty'] ? 'val-empty' : '' }}">{{ $entry['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="audit-raw-json-view" style="display: none;">
                                            <pre class="json-diff">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic; font-size: 11px;">
                                            - ({{ ($lang ?? 'id') === 'id' ? 'Tidak Ada' : 'None' }})
                                        </span>
                                    @endif
                                </td>

                                {{-- NILAI BARU (NEW VALUES) HUMAN READABLE --}}
                                <td style="min-width: 260px; max-width: 350px;">
                                    @php
                                        $newEntries = $log->formattedNewValues($lang ?? 'id');
                                    @endphp

                                    @if(!empty($newEntries))
                                        <div class="audit-readable-wrap readable-new readable-view">
                                            @foreach($newEntries as $entry)
                                                <div class="audit-item-row">
                                                    <span class="audit-item-label">{{ $entry['label'] }}:</span>
                                                    <span class="audit-item-val val-new {{ $entry['is_empty'] ? 'val-empty' : '' }}">{{ $entry['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="audit-raw-json-view" style="display: none;">
                                            <pre class="json-diff" style="color: #34d399;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic; font-size: 11px;">
                                            - ({{ ($lang ?? 'id') === 'id' ? 'Tidak Ada' : 'None' }})
                                        </span>
                                    @endif
                                </td>

                                <td style="font-size: 11px; color: #64748b;">
                                    <div><strong>IP:</strong> {{ $log->ip_address }}</div>
                                    <div style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    {{ ($lang ?? 'id') === 'id' ? 'Belum ada catatan log audit yang tercatat.' : 'No audit log records found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    <script>
        let isJsonMode = false;

        function toggleViewMode() {
            isJsonMode = !isJsonMode;
            const readableEls = document.querySelectorAll('.readable-view');
            const jsonEls = document.querySelectorAll('.audit-raw-json-view');
            const iconEl = document.getElementById('view-mode-icon');
            const labelEl = document.getElementById('view-mode-label');
            const isId = "{{ ($lang ?? 'id') === 'id' }}";

            readableEls.forEach(el => {
                el.style.display = isJsonMode ? 'none' : 'flex';
            });
            jsonEls.forEach(el => {
                el.style.display = isJsonMode ? 'block' : 'none';
            });

            if (isJsonMode) {
                if (iconEl) iconEl.innerText = '📖';
                if (labelEl) labelEl.innerText = isId ? 'Tampilkan Bahasa Baku' : 'Show Human-Readable';
            } else {
                if (iconEl) iconEl.innerText = '📋';
                if (labelEl) labelEl.innerText = isId ? 'Buka Raw JSON' : 'Show Raw JSON';
            }
        }
    </script>
</body>
</html>
