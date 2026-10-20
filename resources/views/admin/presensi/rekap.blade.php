<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Rekapitulasi Presensi Karyawan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --subnav-blue: #277799;
            --subnav-active: #195673;
            --sidebar-bg: #222d32;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
            --btn-blue: #286090;
            --btn-green: #28a745;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            font-size: 13px;
        }

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
        .brand-title { font-size: 18px; font-weight: 700; letter-spacing: 0.3px; }
        .header-right-user { display: flex; align-items: center; gap: 12px; font-size: 12px; }
        .header-link-btn { color: #e0f2fe; text-decoration: none; padding: 4px 10px; border-radius: 3px; background: rgba(255,255,255,0.12); display: flex; align-items: center; gap: 5px; }
        .header-link-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }

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
        }
        .subnav-item:hover { background: rgba(0,0,0,0.15); color: #fff; }
        .subnav-item.active { background-color: var(--subnav-active); color: #fff; font-weight: 700; }

        .app-wrapper { display: flex; min-height: 100vh; padding-top: 86px; }
        .main-sidebar {
            width: 230px;
            background-color: var(--sidebar-bg);
            position: fixed;
            top: 48px; bottom: 0; left: 0;
            z-index: 1025;
            transition: width 0.3s;
        }
        body.sidebar-collapse .main-sidebar { width: 50px; }
        body.sidebar-collapse .sub-navbar, body.sidebar-collapse .content-wrapper { left: 50px; margin-left: 50px; }
        .content-wrapper { flex: 1; margin-left: 230px; padding: 16px 20px 40px 20px; transition: margin-left 0.3s; }

        .sidebar-user-panel { padding: 12px 14px; display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.12); border-bottom: 1px solid #1a2226; }
        .user-avatar-circle { width: 34px; height: 34px; border-radius: 50%; background: #3c8dbc; color: #fff; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .user-info-name { color: #fff; font-weight: 600; font-size: 13px; }
        .user-info-role { color: #8aa4af; font-size: 11px; }

        .internal-nav-tabs { display: flex; gap: 4px; border-bottom: 2px solid #cbd5e1; margin-bottom: 16px; }
        .internal-tab-link {
            padding: 8px 16px;
            text-decoration: none;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px 4px 0 0;
            background: #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .internal-tab-link.active { background: #fff; color: var(--header-blue); border: 1px solid #cbd5e1; border-bottom: 2px solid #fff; margin-bottom: -2px; }

        .filter-box {
            background: #fff;
            border-radius: 4px;
            padding: 12px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filter-item { display: flex; flex-direction: column; gap: 4px; }
        .filter-label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; }
        .filter-select { border: 1px solid #cbd5e1; padding: 6px 10px; border-radius: 3px; font-size: 12px; outline: none; background: #fff; }
        .btn-filter { background: var(--btn-blue); color: #fff; border: none; padding: 7px 14px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: 600; align-self: flex-end; }
        .btn-export { background: #0284c7; color: #fff; text-decoration: none; padding: 7px 14px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: 600; align-self: flex-end; display: inline-flex; align-items: center; gap: 5px; }
        .btn-export:hover { background: #0369a1; color: #fff; }
        .btn-print { background: var(--btn-green); color: #fff; border: none; padding: 7px 14px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: 600; align-self: flex-end; }

        .table-card { background: #fff; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden; }
        .table-responsive { width: 100%; overflow-x: auto; }
        .rekap-table { width: 100%; border-collapse: collapse; font-size: 12px; text-align: left; }
        .rekap-table th { background: #f8fafc; color: #334155; padding: 10px 12px; font-weight: 600; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
        .rekap-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .rekap-table tr:hover { background-color: #f8fafc; }

        @media print {
            .main-header, .sub-navbar, .main-sidebar, .filter-box, .internal-nav-tabs, .btn-print, .btn-export { display: none !important; }
            .content-wrapper { margin-left: 0 !important; padding: 0 !important; }
            .app-wrapper { padding-top: 0 !important; }
        }
    </style>
</head>
<body>

    @include('admin.partials.erp_navbar')

    <div class="app-wrapper">
        <aside class="main-sidebar">
            <div class="sidebar-user-panel">
                <div class="user-avatar-circle">B</div>
                <div class="user-info-text">
                    <div class="user-info-name">Bagus</div>
                    <div class="user-info-role">manager @PUSAT</div>
                </div>
            </div>
        </aside>

        <main class="content-wrapper">
            <div class="internal-nav-tabs">
                <a href="{{ route('admin.presensi') }}" class="internal-tab-link">📅 Presensi Harian</a>
                <a href="{{ route('admin.presensi.rekap') }}" class="internal-tab-link active">📊 Rekapitulasi Bulanan</a>
                <a href="{{ route('admin.presensi.approval') }}" class="internal-tab-link">📝 Persetujuan Cuti & Lembur</a>
            </div>

            <!-- FILTER BULANAN -->
            <form method="GET" action="{{ route('admin.presensi.rekap') }}" class="filter-box">
                <div class="filter-item">
                    <label class="filter-label">Bulan</label>
                    <select name="month" class="filter-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="filter-item">
                    <label class="filter-label">Tahun</label>
                    <select name="year" class="filter-select">
                        @for($y = 2024; $y <= 2028; $y++)
                            <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="filter-item">
                    <label class="filter-label">Cabang</label>
                    <select name="branch_id" class="filter-select">
                        <option value="all">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)$selectedBranch === (string)$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-filter">Tampilkan Rekap</button>
                <div style="margin-left: auto; display: flex; gap: 8px;">
                    <a href="{{ route('admin.presensi.rekap', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-export">📥 Unduh CSV Rekap</a>
                    <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak / Print Rekap</button>
                </div>
            </form>

            <!-- TABEL REKAPITULASI -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="rekap-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>Nama Karyawan</th>
                                <th>Kantor Cabang</th>
                                <th style="text-align: center;">Total Hadir</th>
                                <th style="text-align: center;">Tepat Waktu</th>
                                <th style="text-align: center;">Terlambat</th>
                                <th style="text-align: center;">Total Telat (Menit)</th>
                                <th style="text-align: center;">Alpa</th>
                                <th style="text-align: center;">Cuti / Izin (Hari)</th>
                                <th style="text-align: center;">Total Lembur (Jam)</th>
                                <th style="text-align: center;">Total Jam Kerja</th>
                                <th style="text-align: center; background: #ecfdf5; color: #065f46;">HPP 5-1001 (Jam)</th>
                                <th style="text-align: center; background: #eff6ff; color: #1e40af;">OPEX 5-1002 (Jam)</th>
                                <th style="text-align: center; background: #f8fafc; color: #0f172a;">Jam Efektif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapKaryawan as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>{{ $item['employee']->name }}</strong>
                                        <div style="color: #64748b; font-size: 11px;">{{ $item['employee']->code }} ({{ $item['employee']->position ?? 'Kasir' }})</div>
                                    </td>
                                    <td>{{ $item['employee']->branch?->name ?? '-' }}</td>
                                    <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $item['total_kehadiran'] }}x</td>
                                    <td style="text-align: center; color: #166534; font-weight: 600;">{{ $item['total_hadir'] }}</td>
                                    <td style="text-align: center; color: #b45309; font-weight: 600;">{{ $item['total_telat'] }}</td>
                                    <td style="text-align: center; color: {{ $item['menit_telat'] > 0 ? '#b91c1c' : '#64748b' }};">
                                        {{ $item['menit_telat'] }}m
                                    </td>
                                    <td style="text-align: center; color: {{ $item['total_alpa'] > 0 ? '#dc2626' : '#64748b' }}; font-weight: {{ $item['total_alpa'] > 0 ? '700' : 'normal' }};">
                                        {{ $item['total_alpa'] }}
                                    </td>
                                    <td style="text-align: center;">{{ $item['total_cuti'] }} Hari</td>
                                    <td style="text-align: center; font-weight: 600; color: #0284c7;">{{ $item['jam_lembur'] }} Jam</td>
                                    <td style="text-align: center; font-weight: 700;">{{ $item['total_jam_kerja'] }} Jam</td>
                                    <td style="text-align: center; font-weight: 600; color: #059669; background: #f0fdf4;">{{ $item['jam_cogs'] }} Jam</td>
                                    <td style="text-align: center; font-weight: 600; color: #2563eb; background: #f0f9ff;">{{ $item['jam_opex'] }} Jam</td>
                                    <td style="text-align: center; font-weight: 700; color: #0f172a; background: #f8fafc;">{{ $item['jam_efektif'] }} Jam</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        Belum ada data rekapitulasi untuk periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('sidebarToggleBtn').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapse');
        });
    </script>
</body>
</html>
