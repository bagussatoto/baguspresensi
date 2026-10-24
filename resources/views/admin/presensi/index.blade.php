<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Monitoring & Audit Presensi (SoT)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Leaflet.js untuk Visualisasi Geofence & Radar Presensi (Free OpenStreetMap) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #277799;
            --subnav-active: #195673;
            --sidebar-bg: #222d32;
            --sidebar-header: #1a2226;
            --sidebar-hover: #1e282c;
            --sidebar-text: #b8c7ce;
            --sidebar-active-text: #ffffff;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
            --btn-blue: #286090;
            --btn-cyan: #00bcd4;
            --btn-green: #28a745;
            --btn-orange: #f0ad4e;
            --btn-red: #d9534f;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            font-size: 13px;
            overflow-x: hidden;
        }

        /* HEADER */
        .main-header {
            background: linear-gradient(to right, #246e8c, var(--header-blue));
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            color: #ffffff;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .header-brand-wrap { display: flex; align-items: center; gap: 14px; }
        .sidebar-toggle-btn { background: none; border: none; color: #fff; font-size: 18px; cursor: pointer; padding: 4px 8px; border-radius: 3px; }
        .sidebar-toggle-btn:hover { background: rgba(255,255,255,0.15); }
        .brand-title { font-size: 18px; font-weight: 700; letter-spacing: 0.3px; }
        .header-right-user { display: flex; align-items: center; gap: 12px; font-size: 12px; }
        .header-link-btn { color: #e0f2fe; text-decoration: none; padding: 4px 10px; border-radius: 3px; background: rgba(255,255,255,0.12); display: flex; align-items: center; gap: 5px; }
        .header-link-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }

        /* SUBNAV */
        .sub-navbar {
            background-color: var(--subnav-blue);
            height: 38px;
            position: fixed;
            top: 48px; left: 230px; right: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            overflow-x: auto;
            white-space: nowrap;
            padding: 0 10px;
            gap: 4px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            scrollbar-width: thin;
            transition: left 0.3s;
        }
        .subnav-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            color: #e8f4f8;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            border-radius: 3px;
            transition: background 0.15s, color 0.15s;
        }
        .subnav-item:hover { background: rgba(0,0,0,0.15); color: #ffffff; }
        .subnav-item.active { background-color: var(--subnav-active); color: #ffffff; font-weight: 700; box-shadow: inset 0 1px 2px rgba(0,0,0,0.2); }

        /* LAYOUT */
        .app-wrapper { display: flex; min-height: 100vh; padding-top: 86px; }
        .main-sidebar {
            width: 230px;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 48px; bottom: 0; left: 0;
            overflow-y: auto;
            z-index: 1010;
            transition: width 0.3s;
        }
        .sidebar-collapse .main-sidebar { width: 0; }
        .sidebar-collapse .sub-navbar { left: 0; }
        .sidebar-collapse .content-wrapper { margin-left: 0; }

        .content-wrapper {
            margin-left: 230px;
            flex: 1;
            padding: 16px 20px;
            transition: margin-left 0.3s;
        }

        .sidebar-user-panel {
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--sidebar-header);
            border-bottom: 1px solid #29353a;
        }
        .user-avatar-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px;
        }
        .user-info-text { font-size: 12px; }
        .user-info-name { font-weight: 700; color: #fff; }
        .user-info-role { color: #94a3b8; font-size: 11px; }

        /* TABS INTERNAL */
        .internal-nav-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 14px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .internal-tab-link {
            padding: 6px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            background: #e2e8f0;
            transition: all 0.15s;
        }
        .internal-tab-link:hover { background: #cbd5e1; color: #1e293b; }
        .internal-tab-link.active { background: #2563eb; color: #fff; }

        /* KPI STATS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-left: 4px solid #94a3b8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card.card-present { border-left-color: #10b981; }
        .stat-card.card-late { border-left-color: #f59e0b; }
        .stat-card.card-overtime { border-left-color: #0ea5e9; }
        .stat-card.card-leave { border-left-color: #8b5cf6; }
        .stat-card.card-sot { border-left-color: #6366f1; background: linear-gradient(135deg, #ffffff, #f8fafc); }

        .stat-value { font-size: 20px; font-weight: 700; font-family: 'Outfit', sans-serif; color: #1e293b; }
        .stat-label { font-size: 11px; color: #64748b; margin-top: 2px; }
        .stat-icon { font-size: 24px; opacity: 0.85; }

        /* FILTER BOX */
        .filter-box {
            background: #fff;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }
        .filter-item { display: flex; flex-direction: column; gap: 4px; }
        .filter-label { font-size: 11px; font-weight: 600; color: #475569; }
        .filter-input, .filter-select {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 12px;
            background: #f8fafc;
        }
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #2563eb;
            background: #fff;
        }
        .btn-filter-submit {
            padding: 7px 16px;
            border: none;
            border-radius: 4px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-filter-submit:hover { background: #1d4ed8; }
        .btn-export-csv {
            padding: 7px 14px;
            border-radius: 4px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
            font-weight: 600;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.15s;
        }
        .btn-export-csv:hover { opacity: 0.9; }

        /* TABLE */
        .table-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .table-responsive { width: 100%; overflow-x: auto; }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }
        .attendance-table th {
            background: #f8fafc;
            color: #334155;
            padding: 10px 12px;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .attendance-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .attendance-table tr:hover { background-color: #f8fafc; }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .badge-present { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .badge-late { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .badge-absent { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .badge-gps-ok { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; font-size: 10px; }
        .badge-gps-warn { background: #fff1f2; color: #be123c; border-color: #fecdd3; font-size: 10px; }
        .badge-mock-gps { background: #fee2e2; color: #b91c1c; border-color: #f87171; font-weight: 700; animation: pulse 1.5s infinite; }
        .badge-pos-closed { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; font-size: 10px; }
        .badge-pos-open { background: #fef3c7; color: #b45309; border-color: #fde68a; font-size: 10px; }
        .badge-pos-diff { background: #fee2e2; color: #dc2626; border-color: #fca5a5; font-size: 10px; font-weight: 700; }

        /* ACTION BUTTONS */
        .btn-action-sot {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-action-sot:hover { background: #2563eb; color: #fff; }

        .btn-action-map {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #10b981;
            background: #f0fdf4;
            color: #047857;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-action-map:hover { background: #10b981; color: #fff; }

        /* SELFIE THUMBNAIL */
        .selfie-thumb {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .selfie-thumb:hover { transform: scale(1.15); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .selfie-placeholder {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border: 1px dashed #cbd5e1;
        }

        /* MODALS */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 12px;
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: fadeInDown 0.25s ease-out;
        }
        .modal-box-lg {
            max-width: 860px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-body-scroll {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        .modal-close-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 28px; height: 28px;
            border-radius: 50%;
            font-size: 14px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-close-btn:hover { background: rgba(239, 68, 68, 0.8); }

        /* SOT AUDIT GRID */
        .sot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }
        .sot-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
        }
        .sot-card-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sot-data-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .sot-data-row:last-child { border-bottom: none; }
        .sot-label { color: #64748b; }
        .sot-value { font-weight: 600; color: #0f172a; text-align: right; }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>

    <!-- TOP MAIN HEADER -->
    @include('admin.partials.erp_navbar')

    <!-- APP WRAPPER -->
    <div class="app-wrapper">
        <!-- SIDEBAR -->
        <aside class="main-sidebar">
            <div class="sidebar-user-panel">
                <div class="user-avatar-circle">B</div>
                <div class="user-info-text">
                    <div class="user-info-name">Bagus Administrator</div>
                    <div class="user-info-role">Manager Operasional @PUSAT</div>
                </div>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="content-wrapper">
            <!-- INTERNAL TABS -->
            <div class="internal-nav-tabs">
                <a href="{{ route('admin.presensi') }}" class="internal-tab-link active">📅 Presensi Harian (SoT)</a>
                <a href="{{ route('admin.presensi.rekap') }}" class="internal-tab-link">📊 Rekapitulasi Bulanan</a>
                <a href="{{ route('admin.presensi.approval') }}" class="internal-tab-link">
                    📝 Persetujuan Cuti & Lembur
                    @if($pendingLeaves + $pendingOvertimes > 0)
                        <span style="background:#e11d48;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;">{{ $pendingLeaves + $pendingOvertimes }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.presensi.audit-logs') }}" class="internal-tab-link">
                    🛡️ WORM Audit Trail (ISO 27001)
                    @if($todayLockouts > 0)
                        <span style="background:#ef4444;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;font-weight:bold;">🚨 {{ $todayLockouts }} Alert</span>
                    @endif
                </a>
            </div>

            @if($todayLockouts > 0)
                <!-- SECURITY ALERT WIDGET: EARLY WARNING LOCKOUT INCIDENTS -->
                <div style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05)); border: 1px solid #ef4444; border-radius: 8px; padding: 12px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="background: #ef4444; color: #ffffff; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);">
                            🚨
                        </div>
                        <div>
                            <div style="font-weight: 700; color: #b91c1c; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                                <span>Peringatan Dini Keamanan: Terdeteksi {{ $todayLockouts }} Percobaan Login Gagal (Lockout) Hari Ini</span>
                                <span style="background: #fee2e2; color: #991b1b; font-size: 10px; padding: 1px 6px; border-radius: 4px; font-weight: 800;">SECURITY ALERT</span>
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                Akun karyawan dikunci sementara selama 60 detik karena 3 kali salah memasukkan Password/PIN.
                                @if(isset($recentLockouts) && count($recentLockouts) > 0)
                                    Insiden terbaru: <strong style="color: #0f172a;">{{ $recentLockouts->first()->new_values['employee_name'] ?? 'Karyawan' }}</strong> (IP: {{ $recentLockouts->first()->ip_address }}) pada {{ \Carbon\Carbon::parse($recentLockouts->first()->created_at)->format('H:i') }} WIB.
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.presensi.audit-logs', ['action' => 'portal_login_lockout']) }}" style="background: #b91c1c; color: #ffffff; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s;">
                            <span>🔍 Periksa Jejak Forensik</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- KPI CARDS -->
            <div class="stats-grid">
                <div class="stat-card card-present">
                    <div>
                        <div class="stat-value">{{ $totalHadir }}</div>
                        <div class="stat-label">Hadir Tepat Waktu</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-card card-late">
                    <div>
                        <div class="stat-value">{{ $totalTelat }}</div>
                        <div class="stat-label">Terlambat ({{ $totalMenitTelat }} Menit)</div>
                    </div>
                    <div class="stat-icon">⏰</div>
                </div>
                <div class="stat-card card-overtime">
                    <div>
                        <div class="stat-value">{{ round($totalLemburMenit / 60, 1) }} Jam</div>
                        <div class="stat-label">Total Jam Lembur</div>
                    </div>
                    <div class="stat-icon">⚡</div>
                </div>
                <div class="stat-card card-leave">
                    <div>
                        <div class="stat-value">{{ $pendingLeaves }} Pending</div>
                        <div class="stat-label">Permohonan Izin / Cuti</div>
                    </div>
                    <div class="stat-icon">📋</div>
                </div>
                <div class="stat-card card-sot">
                    <div>
                        <div class="stat-value" style="color: #4f46e5;">{{ count($records) }} Rekaman</div>
                        <div class="stat-label">SoT Presensi Terverifikasi</div>
                    </div>
                    <div class="stat-icon">🛡️</div>
                </div>
            </div>

            <!-- FILTER TOOLBAR -->
            <form method="GET" action="{{ route('admin.presensi') }}" class="filter-box">
                <div class="filter-item">
                    <label class="filter-label">Tanggal Operasional</label>
                    <input type="date" name="date" class="filter-input" value="{{ $selectedDate }}">
                </div>
                <div class="filter-item">
                    <label class="filter-label">Kantor Cabang</label>
                    <select name="branch_id" class="filter-select">
                        <option value="all">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)$selectedBranch === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item">
                    <label class="filter-label">Shift Kerja</label>
                    <select name="shift_id" class="filter-select">
                        <option value="all">Semua Shift</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" {{ (string)$selectedShift === (string)$s->id ? 'selected' : '' }}>{{ $s->name }} ({{ substr($s->start_time, 0, 5) }}-{{ substr($s->end_time, 0, 5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-item">
                    <label class="filter-label">Status Kehadiran</label>
                    <select name="status" class="filter-select">
                        <option value="all">Semua Status</option>
                        <option value="present" {{ $selectedStatus === 'present' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                        <option value="late" {{ $selectedStatus === 'late' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>
                <div class="filter-item" style="flex:1;">
                    <label class="filter-label">Cari Nama / Kode</label>
                    <input type="text" name="q" class="filter-input" placeholder="Ketik nama karyawan..." value="{{ $search }}">
                </div>
                <button type="submit" class="btn-filter-submit">🔍 Terapkan Filter</button>
                <a href="{{ route('admin.presensi.export-audit', request()->query()) }}" class="btn-export-csv" title="Unduh Berita Acara Presensi Lengkap Format CSV">
                    📥 Unduh Audit CSV
                </a>
            </form>

            <!-- DATA TABLE -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th style="width: 35px;">No</th>
                                <th style="width: 50px;">Selfie</th>
                                <th>Karyawan</th>
                                <th>Cabang</th>
                                <th>Shift Kerja</th>
                                <th>Jam Tap</th>
                                <th>Status</th>
                                <th>Geofence & Radar</th>
                                <th>Sesi Kasir POS</th>
                                <th>Durasi Kerja</th>
                                <th style="text-align: center; width: 140px;">Inspeksi SoT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $index => $row)
                                @php
                                    $pos = $posSummaries->get($row->employee_id);
                                    $branchRadius = $row->branch?->radius_meters ?? 100;
                                    $inRadius = $row->clock_in_distance_meters !== null ? ($row->clock_in_distance_meters <= $branchRadius) : null;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($row->clock_in_photo_path)
                                            <img src="{{ asset($row->clock_in_photo_path) }}" alt="Selfie" class="selfie-thumb" onclick="openPhotoModal('{{ asset($row->clock_in_photo_path) }}', 'Selfie Masuk: {{ $row->employee?->name }}')">
                                        @else
                                            <div class="selfie-placeholder" title="Tidak ada foto">📷</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">{{ $row->employee?->name ?? '-' }}</div>
                                        <div style="color: #64748b; font-size: 11px;">{{ $row->employee?->code ?? '-' }} • {{ $row->employee?->position ?? 'Kasir' }}</div>
                                    </td>
                                    <td>{{ $row->branch?->name ?? '-' }}</td>
                                    <td>
                                        @if($row->shift)
                                            <span style="font-weight: 600;">{{ $row->shift->name }}</span>
                                            <div style="color: #64748b; font-size: 10px;">{{ substr($row->shift->start_time, 0, 5) }} - {{ substr($row->shift->end_time, 0, 5) }} @if($row->shift->is_overnight) 🌙 @endif</div>
                                        @else
                                            <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>Masuk: <strong>{{ $row->clock_in_at ? $row->clock_in_at->format('H:i') : '-' }}</strong></div>
                                        <div style="color: #64748b; font-size: 11px;">Pulang: {{ $row->clock_out_at ? $row->clock_out_at->format('H:i') : 'Belum Pulang' }}</div>
                                    </td>
                                    <td>
                                        @if($row->status->value === 'present')
                                            <span class="badge badge-present">Hadir Tepat Waktu</span>
                                        @elseif($row->status->value === 'late')
                                            <span class="badge badge-late">Telat {{ $row->late_minutes }}m</span>
                                        @else
                                            <span class="badge badge-absent">{{ $row->status->label() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->is_mock_location_detected)
                                            <div style="margin-bottom: 3px;">
                                                <span class="badge badge-mock-gps">⚠️ FAKE GPS</span>
                                            </div>
                                        @endif
                                        @if($row->clock_in_distance_meters !== null)
                                            <span class="badge {{ $inRadius ? 'badge-gps-ok' : 'badge-gps-warn' }}">
                                                📍 {{ number_format($row->clock_in_distance_meters, 0) }}m ({{ $inRadius ? 'Aman' : 'Luar' }})
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-size: 11px;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pos)
                                            @if($pos->status === 'closed')
                                                <span class="badge badge-pos-closed">Selesai Shift</span>
                                                <div style="font-size: 10px; margin-top: 2px;">
                                                    @if((float)$pos->cash_difference == 0)
                                                        <span style="color: #166534; font-weight: 700;">✓ Kas Fisik Cocok</span>
                                                    @else
                                                        <span class="badge badge-pos-diff">Selisih: Rp {{ number_format($pos->cash_difference, 0, ',', '.') }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge badge-pos-open">Shift Berjalan</span>
                                            @endif
                                        @else
                                            <span style="color: #94a3b8; font-size: 11px;">Bukan Kasir</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><strong>{{ round($row->duration_minutes / 60, 1) }} Jam</strong> ({{ $row->duration_minutes }}m)</div>
                                        @if($row->overtime_minutes > 0)
                                            <div style="color: #0284c7; font-weight: 600; font-size: 11px;">⚡ Lembur: +{{ $row->overtime_minutes }}m</div>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 4px; justify-content: center;">
                                            <button type="button" class="btn-action-sot" onclick="openSotAuditModal({{ $row->id }})" title="Inspeksi Detail 360 SoT Record">
                                                <span>🔍</span> SoT
                                            </button>
                                            @if($row->clock_in_lat && $row->clock_in_lng)
                                            <button type="button" class="btn-action-map" onclick="openGeofenceMap({{ $row->clock_in_lat }}, {{ $row->clock_in_lng }}, {{ $row->branch?->latitude ?? -6.2 }}, {{ $row->branch?->longitude ?? 106.8 }}, '{{ addslashes($row->employee?->name ?? 'Karyawan') }}', '{{ addslashes($row->branch?->name ?? 'Cabang') }}', {{ $row->branch?->radius_meters ?? 100 }}, {{ $row->clock_in_distance_meters ?? 0 }})" title="Tampilkan Radar Peta Geofence">
                                                <span>🗺️</span>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">📭</div>
                                        <div>Belum ada rekaman presensi pada tanggal dan filter yang dipilih.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL 1: INSPEKSI 360° SOT AUDIT RECORD -->
    <div class="modal-overlay" id="sotAuditModal">
        <div class="modal-box modal-box-lg">
            <div class="modal-header">
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #fff;" id="sotModalTitle">
                        🛡️ Inspeksi Single Source of Truth (SoT) Presensi
                    </h3>
                    <div style="font-size: 11px; color: #94a3b8;" id="sotModalSub">
                        Verifikasi Data Otoritatif Kehadiran & Jejak Audit ISO 27001
                    </div>
                </div>
                <button class="modal-close-btn" onclick="closeSotAuditModal()">✕</button>
            </div>
            <div class="modal-body-scroll" id="sotModalContent">
                <div style="text-align: center; padding: 30px; color: #64748b;">
                    <span>⏳ Memuat rincian SoT presensi...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: RADAR PETA GEOFENCE REALTIME (LEAFLET.JS) -->
    <div class="modal-overlay" id="geofenceModal">
        <div class="modal-box" style="max-width: 680px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #065f46, #047857);">
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #fff;">
                        📍 Radar Geofence Presensi Karyawan
                    </h3>
                    <div style="font-size: 11px; color: #a7f3d0;" id="geofenceModalSub">
                        Visualisasi Koordinat GPS vs Radius Toleransi Cabang
                    </div>
                </div>
                <button class="modal-close-btn" onclick="closeGeofenceModal()">✕</button>
            </div>
            <div style="padding: 16px;">
                <div id="leafletMap" style="height: 380px; width: 100%; border-radius: 8px; border: 1px solid #cbd5e1;"></div>
                <div id="geofenceLegend" style="margin-top: 12px; font-size: 12px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; background: #f8fafc; padding: 10px; border-radius: 6px;"></div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: PREVIEW FOTO SELFIE SEDERHANA -->
    <div class="modal-overlay" id="photoModal">
        <div class="modal-box">
            <div class="modal-header">
                <span id="modalTitle">Foto Selfie Presensi</span>
                <button class="modal-close-btn" onclick="closePhotoModal()">✕</button>
            </div>
            <div style="padding: 16px; text-align: center;">
                <img id="modalImg" src="" alt="Selfie Preview" style="max-width: 100%; max-height: 420px; border-radius: 6px; object-fit: contain;">
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sidebarToggleBtn')?.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapse');
        });

        // 1. Photo Modal Sederhana
        function openPhotoModal(src, title) {
            document.getElementById('modalImg').src = src;
            document.getElementById('modalTitle').textContent = title || 'Foto Selfie Presensi';
            document.getElementById('photoModal').classList.add('active');
        }
        function closePhotoModal() {
            document.getElementById('photoModal').classList.remove('active');
        }

        // 2. SoT 360 Inspection Modal
        async function openSotAuditModal(recordId) {
            const modal = document.getElementById('sotAuditModal');
            const container = document.getElementById('sotModalContent');
            modal.classList.add('active');
            container.innerHTML = '<div style="text-align: center; padding: 40px; color: #64748b;"><span>⏳ Mengambil data otentik SoT dari server...</span></div>';

            try {
                const response = await fetch(`{{ url('/admin/presensi') }}/${recordId}/detail`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const res = await response.json();
                if (!res.success) {
                    container.innerHTML = '<div style="color: #ef4444; padding: 20px;">Gagal memuat rincian SoT presensi.</div>';
                    return;
                }

                const d = res.data;
                const rec = d.record;
                const emp = d.employee || {};
                const branch = d.branch || {};
                const shift = d.shift || {};
                const pos = d.pos_session;
                const logs = d.audit_logs || [];

                document.getElementById('sotModalSub').innerText = `Rekaman #${rec.id} • ${emp.name || 'Karyawan'} (${emp.code || '-'}) • Tanggal: ${rec.work_date}`;

                let html = `
                    <!-- 1. IDENTITAS & STATUS RESMI -->
                    <div class="sot-grid">
                        <div class="sot-card">
                            <div class="sot-card-title">👤 Identitas Master Karyawan (SoT)</div>
                            <div class="sot-data-row"><span class="sot-label">Nama Lengkap:</span><span class="sot-value">${emp.name || '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">NIK / Kode Pegawai:</span><span class="sot-value">${emp.code || '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Jabatan & Peran:</span><span class="sot-value">${emp.position || '-'} (${emp.role || 'Staff'})</span></div>
                            <div class="sot-data-row"><span class="sot-label">Cabang Penempatan:</span><span class="sot-value">${branch.name || '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Status Akun:</span><span class="sot-value" style="color: #10b981;">Aktif & Terverifikasi</span></div>
                        </div>

                        <div class="sot-card">
                            <div class="sot-card-title">⏰ Jadwal Shift & Kepatuhan Waktu</div>
                            <div class="sot-data-row"><span class="sot-label">Shift Terjadwal:</span><span class="sot-value">${shift.name || '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Jam Jadwal Masuk - Pulang:</span><span class="sot-value">${shift.start_time ? shift.start_time.substring(0,5) : '-'} - ${shift.end_time ? shift.end_time.substring(0,5) : '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Clock-In Aktual:</span><span class="sot-value">${rec.clock_in_at ? new Date(rec.clock_in_at).toLocaleTimeString('id-ID') : '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Clock-Out Aktual:</span><span class="sot-value">${rec.clock_out_at ? new Date(rec.clock_out_at).toLocaleTimeString('id-ID') : 'Belum Pulang'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Status Presensi:</span><span class="sot-value" style="color: #2563eb; font-weight:700;">${rec.status ? rec.status.toUpperCase() : '-'}</span></div>
                        </div>
                    </div>

                    <!-- 2. BUKTI SELFIE BERDAMPINGAN -->
                    <div class="sot-card" style="margin-bottom: 16px;">
                        <div class="sot-card-title">📸 Bukti Otentikasi Visual (Selfie In & Out)</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; text-align: center;">
                            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                                <div style="font-size: 11px; font-weight: 700; color: #047857; margin-bottom: 8px;">Selfie Masuk (Clock-In)</div>
                                ${d.clock_in_photo_url ? `<img src="${d.clock_in_photo_url}" style="max-height: 180px; max-width: 100%; border-radius: 6px; object-fit: cover;" onclick="openPhotoModal('${d.clock_in_photo_url}', 'Selfie Masuk')">` : '<div style="height: 140px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:12px;">Tidak Ada Foto Masuk</div>'}
                            </div>
                            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                                <div style="font-size: 11px; font-weight: 700; color: #b91c1c; margin-bottom: 8px;">Selfie Pulang (Clock-Out)</div>
                                ${d.clock_out_photo_url ? `<img src="${d.clock_out_photo_url}" style="max-height: 180px; max-width: 100%; border-radius: 6px; object-fit: cover;" onclick="openPhotoModal('${d.clock_out_photo_url}', 'Selfie Pulang')">` : '<div style="height: 140px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:12px;">Belum Pulang / Tanpa Foto</div>'}
                            </div>
                        </div>
                    </div>

                    <!-- 3. GEOFENCE, ANTI-FRAUD, & TEKNIS -->
                    <div class="sot-grid">
                        <div class="sot-card">
                            <div class="sot-card-title">📍 Bukti Geofence & Anti-Mock GPS</div>
                            <div class="sot-data-row"><span class="sot-label">Koordinat Masuk:</span><span class="sot-value">${rec.clock_in_lat || '-'}, ${rec.clock_in_lng || '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Jarak ke Titik Cabang:</span><span class="sot-value">${rec.clock_in_distance_meters !== null ? Number(rec.clock_in_distance_meters).toFixed(1) + ' meter' : '-'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Batas Radius Aman Cabang:</span><span class="sot-value">${d.max_radius} meter</span></div>
                            <div class="sot-data-row"><span class="sot-label">Kepatuhan Geofence:</span><span class="sot-value" style="color: ${d.is_geofence_valid ? '#166534' : '#dc2626'}; font-weight:700;">${d.is_geofence_valid ? '✓ Dalam Radius Resmi' : '✗ Di Luar Radius Cabang'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Deteksi Fake / Mock GPS:</span><span class="sot-value" style="color: ${rec.is_mock_location_detected ? '#dc2626' : '#166534'}; font-weight:700;">${rec.is_mock_location_detected ? '⚠️ TERDETEKSI FAKE GPS' : '✓ Aman (Sensor Asli)'}</span></div>
                            <div class="sot-data-row"><span class="sot-label">Metode Verifikasi:</span><span class="sot-value">${rec.verification_method || 'gps_selfie'}</span></div>
                        </div>

                        <div class="sot-card">
                            <div class="sot-card-title">🏪 Keterhubungan Kasir POS & Rekonsiliasi Kas</div>
                            ${pos ? `
                                <div class="sot-data-row"><span class="sot-label">Status Sesi Kasir:</span><span class="sot-value" style="color: #0284c7; font-weight:700;">${pos.status === 'closed' ? 'Shift Selesai (Closed)' : 'Shift Masih Dibuka'}</span></div>
                                <div class="sot-data-row"><span class="sot-label">Kas Fisik (Blind Count):</span><span class="sot-value">Rp ${Number(pos.blind_cash_count || 0).toLocaleString('id-ID')}</span></div>
                                <div class="sot-data-row"><span class="sot-label">Target Kas Sistem:</span><span class="sot-value">Rp ${Number(pos.system_expected_cash || 0).toLocaleString('id-ID')}</span></div>
                                <div class="sot-data-row"><span class="sot-label">Selisih Kas Fisik:</span><span class="sot-value" style="color: ${Number(pos.cash_difference || 0) == 0 ? '#166534' : '#dc2626'}; font-weight:700;">Rp ${Number(pos.cash_difference || 0).toLocaleString('id-ID')} (${Number(pos.cash_difference || 0) == 0 ? 'Cocok' : 'Ada Selisih'})</span></div>
                                <div class="sot-data-row"><span class="sot-label">Catatan Serah Terima:</span><span class="sot-value">${pos.handover_notes || '-'}</span></div>
                            ` : `
                                <div style="text-align: center; padding: 20px; color: #94a3b8; font-size: 11px;">
                                    Pegawai ini tidak memegang sesi laci kasir POS pada tanggal kerja ini.
                                </div>
                            `}
                        </div>
                    </div>

                    <!-- 4. LOG AUDIT ISO 27001 (WORM) -->
                    <div class="sot-card">
                        <div class="sot-card-title">🛡️ Jejak WORM Audit Trail Terkait (ISO 27001)</div>
                        ${logs.length > 0 ? `
                            <table style="width: 100%; border-collapse: collapse; font-size: 11px; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #cbd5e1; color: #64748b;">
                                        <th style="padding: 6px;">Waktu</th>
                                        <th style="padding: 6px;">Aksi</th>
                                        <th style="padding: 6px;">Rincian Nilai Baru</th>
                                        <th style="padding: 6px;">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${logs.map(l => `
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 6px; white-space: nowrap;">${new Date(l.created_at).toLocaleString('id-ID')}</td>
                                            <td style="padding: 6px;"><span class="badge" style="background:#e0e7ff;color:#3730a3;">${l.action}</span></td>
                                            <td style="padding: 6px; font-family: monospace; font-size: 10px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${JSON.stringify(l.new_values || {})}</td>
                                            <td style="padding: 6px; color: #64748b;">${l.ip_address || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : `
                            <div style="color: #64748b; font-size: 11px; text-align: center; padding: 10px;">
                                Belum ada riwayat revisi atau eskalasi audit log pada rekaman presensi ini.
                            </div>
                        `}
                    </div>
                `;

                container.innerHTML = html;
            } catch (err) {
                container.innerHTML = '<div style="color: #ef4444; padding: 20px;">Terjadi kegagalan jaringan saat memuat rincian SoT.</div>';
            }
        }
        function closeSotAuditModal() {
            document.getElementById('sotAuditModal').classList.remove('active');
        }

        // 3. Geofence Map (Leaflet.js)
        let leafletMapInstance = null;
        function openGeofenceMap(lat, lng, branchLat, branchLng, empName, branchName, radius, distance) {
            const modal = document.getElementById('geofenceModal');
            modal.classList.add('active');

            document.getElementById('geofenceModalSub').innerText = `${empName} di ${branchName} • Jarak: ${Number(distance).toFixed(1)}m (Maksimal: ${radius}m)`;

            setTimeout(() => {
                if (!leafletMapInstance) {
                    leafletMapInstance = L.map('leafletMap');
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(leafletMapInstance);
                } else {
                    leafletMapInstance.eachLayer(layer => {
                        if (layer instanceof L.Marker || layer instanceof L.Circle || layer instanceof L.Polyline) {
                            leafletMapInstance.removeLayer(layer);
                        }
                    });
                }

                const branchCoords = [branchLat, branchLng];
                const empCoords = [lat, lng];

                // Marker Kantor Cabang (HQ)
                L.marker(branchCoords).addTo(leafletMapInstance)
                    .bindPopup(`<b>🏢 ${branchName}</b><br>Titik Pusat Kantor Cabang`).openPopup();

                // Lingkaran Radius Geofence (100m)
                const inRadius = distance <= radius;
                L.circle(branchCoords, {
                    color: inRadius ? '#10b981' : '#f59e0b',
                    fillColor: inRadius ? '#10b981' : '#f59e0b',
                    fillOpacity: 0.15,
                    radius: radius
                }).addTo(leafletMapInstance);

                // Marker Posisi Karyawan saat Clock-in
                L.marker(empCoords).addTo(leafletMapInstance)
                    .bindPopup(`<b>👤 ${empName}</b><br>Lokasi Clock-In (${Number(distance).toFixed(1)}m dari cabang)`);

                // Garis Penghubung Jarak
                L.polyline([branchCoords, empCoords], {
                    color: inRadius ? '#10b981' : '#ef4444',
                    dashArray: '5, 8'
                }).addTo(leafletMapInstance);

                // Fit Bounds agar kedua marker terlihat jelas
                const group = new L.featureGroup([L.marker(branchCoords), L.marker(empCoords)]);
                leafletMapInstance.fitBounds(group.getBounds().pad(0.3));

                // Legend Status
                document.getElementById('geofenceLegend').innerHTML = `
                    <div><strong>🏢 Titik Cabang:</strong> ${branchLat}, ${branchLng} (Radius: ${radius}m)</div>
                    <div><strong>📍 Posisi Karyawan:</strong> ${lat}, ${lng}</div>
                    <div><strong>Hasil Evaluasi:</strong> <span style="font-weight:700; color:${inRadius ? '#166534' : '#dc2626'}">${inRadius ? '✓ Berada di Dalam Geofence Cabang' : '✗ Di Luar Radius Toleransi Cabang'}</span></div>
                `;
            }, 200);
        }
        function closeGeofenceModal() {
            document.getElementById('geofenceModal').classList.remove('active');
        }

        // Close on background click
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
            }
        });
    </script>
</body>
</html>
