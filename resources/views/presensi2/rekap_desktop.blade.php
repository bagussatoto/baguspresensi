<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Penjualan & Laporan Shift (Desktop) — Maya Graha Kencana</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --pos-bg: #ecf0f5;
            --pos-card-bg: #ffffff;
            --pos-green: #00a65a;
            --pos-green-dark: #008d4c;
            --pos-green-light: #dff0d8;
            --pos-green-text: #3c763d;
            --pos-orange: #f39c12;
            --pos-amber-dark: #b45309;
            --pos-cyan: #00c0ef;
            --pos-cyan-dark: #0097bc;
            --pos-red: #dd4b39;
            --pos-red-light: #fbeae8;
            --pos-blue: #0284c7;
            --pos-border: #cbd5e1;
            --pos-dark: #2c3e50;
            --pos-slate: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: var(--pos-bg);
            color: #333333;
            font-size: 13px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* TOP HEADER */
        .pos-top-header {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            background: #ffffff;
            border-bottom: 1px solid #d2d6de;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            flex-wrap: wrap;
            gap: 12px;
        }

        .pos-header-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pos-top-title-group {
            text-align: center;
            flex: 1;
            min-width: 320px;
        }

        .pos-top-title {
            font-size: 18px;
            font-weight: 800;
            color: #2c3e50;
            letter-spacing: 0.3px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .pos-top-subtitle {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 2px;
            font-weight: 500;
        }

        .pos-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-nav-pill {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-nav-pill:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .btn-nav-pill.primary {
            background: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
        }
        .btn-nav-pill.primary:hover {
            background: #0369a1;
        }
        .btn-nav-pill.success {
            background: var(--pos-green);
            border-color: var(--pos-green);
            color: #ffffff;
        }
        .btn-nav-pill.success:hover {
            background: var(--pos-green-dark);
        }

        /* ALERT STATUS BANNER */
        .status-alert-bar {
            margin: 14px 24px 0 24px;
            padding: 10px 16px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .status-alert-bar.warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
        }
        .status-alert-bar.success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        /* FILTER & CONTROL TOOLBAR */
        .toolbar-container {
            margin: 12px 24px 0 24px;
            padding: 10px 16px;
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .filter-form-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-input {
            padding: 5px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 12px;
            color: #1e293b;
            background: #ffffff;
            outline: none;
        }

        /* METRIC CARDS ROW */
        .metric-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin: 14px 24px 0 24px;
        }

        @media (max-width: 1100px) {
            .metric-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 650px) {
            .metric-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .small-box {
            position: relative;
            display: block;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            color: #ffffff;
            padding: 14px 18px;
            background: #2c3e50;
        }
        .small-box.bg-green { background: linear-gradient(135deg, #00a65a, #008d4c); }
        .small-box.bg-orange { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .small-box.bg-cyan { background: linear-gradient(135deg, #00c0ef, #0097bc); }
        .small-box.bg-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .small-box-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
            font-weight: 700;
        }
        .small-box-val {
            font-size: 22px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            margin: 6px 0 4px 0;
        }
        .small-box-sub {
            font-size: 11px;
            opacity: 0.85;
            display: flex;
            justify-content: space-between;
        }

        /* MAIN 2-COLUMN LAYOUT */
        .rekap-desktop-container {
            display: grid;
            grid-template-columns: 390px minmax(0, 1fr);
            gap: 16px;
            padding: 16px 24px 28px 24px;
            flex: 1;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .rekap-desktop-container {
                grid-template-columns: 1fr;
            }
        }

        /* BOX / CARD STYLING */
        .panel-box {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 16px;
        }

        .panel-box-header {
            padding: 10px 16px;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }
        .panel-box-header-cyan {
            background-color: #e0f2fe;
            color: #0369a1;
            border-bottom-color: #bae6fd;
        }
        .panel-box-header-green {
            background-color: var(--pos-green-light);
            color: var(--pos-green-text);
            border-bottom-color: #c3e6cb;
        }
        .panel-box-header-slate {
            background-color: #f8fafc;
            color: #334155;
            border-bottom-color: #e2e8f0;
        }

        .panel-box-body {
            padding: 14px 16px;
        }

        /* STAT ROWS */
        .stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 12.5px;
        }
        .stat-row:last-child {
            border-bottom: none;
        }
        .stat-label {
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stat-value {
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            color: #1e293b;
            font-size: 13px;
        }

        /* TABLE STYLES */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .table-data th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 9px 12px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        .table-data td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table-data tr:hover td {
            background: #f8fafc;
        }

        /* TABS IN DESKTOP BOX */
        .tabs-header-nav {
            display: flex;
            gap: 2px;
            background: #f1f5f9;
            border-bottom: 1px solid #d2d6de;
            padding: 6px 12px 0 12px;
            flex-wrap: wrap;
        }
        .tab-nav-btn {
            padding: 9px 16px;
            font-size: 12.5px;
            font-weight: 700;
            border: 1px solid transparent;
            border-bottom: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .tab-nav-btn:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.5);
        }
        .tab-nav-btn.active {
            color: #0284c7;
            background: #ffffff;
            border-color: #d2d6de;
            border-bottom: 1px solid #ffffff;
            margin-bottom: -1px;
        }

        .tab-pane {
            display: none;
            padding: 16px;
        }
        .tab-pane.active {
            display: block;
        }

        /* BADGES */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-green { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-amber { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .badge-red { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-blue { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .badge-gray { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* BUTTONS */
        .btn-block-action {
            width: 100%;
            padding: 11px 16px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 8px;
        }
        .btn-block-action.blue {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-block-action.blue:hover {
            background: #0369a1;
        }
        .btn-block-action.green {
            background: var(--pos-green);
            color: #ffffff;
        }
        .btn-block-action.green:hover {
            background: var(--pos-green-dark);
        }
        .btn-block-action.secondary {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
        }
        .btn-block-action.secondary:hover {
            background: #f8fafc;
            color: #0f172a;
        }
        .btn-block-action.red {
            background: #ef4444;
            color: #ffffff;
        }
        .btn-block-action.red:hover {
            background: #dc2626;
        }

        /* PRINT STYLES FOR CENTERED VERTICAL SLIP TABLE */
        .print-table-report {
            display: none;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            @page {
                size: portrait;
                margin: 4mm 2mm;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .pos-top-header, .status-alert-bar, .toolbar-container, .metric-cards-grid,
            .rekap-desktop-container, .no-print, #mgk-notification-modal {
                display: none !important;
            }
            .print-table-report {
                max-width: 275px !important;
                width: 275px !important;
                margin: 0 auto !important;
                padding: 4px 6px !important;
                color: #000000 !important;
                background: #ffffff !important;
                border: 1px solid #d1d5db !important;
                box-shadow: none !important;
                zoom: var(--print-scale, 1);
            }
            body:not(.printing-hardware) #print-table-report {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            body.printing-hardware #print-table-report {
                display: none !important;
            }
            body.printing-hardware #print-hardware-report {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .print-table-report.paper-medium {
                max-width: 480px !important;
                width: 480px !important;
                padding: 10px 14px !important;
            }
            .print-table-report.paper-a4 {
                max-width: 100% !important;
                width: 100% !important;
                padding: 16px 20px !important;
            }
            .print-table-report.paper-thermal58 {
                max-width: 200px !important;
                width: 200px !important;
                padding: 2px 3px !important;
                font-size: 8.5px !important;
            }
            .print-box-panel {
                border: 1px solid #111827 !important;
                background: #ffffff !important;
            }
            .print-sub-box {
                border: 1px solid #374151 !important;
                background: #ffffff !important;
            }
            .print-grid-table th {
                background: #f3f4f6 !important;
                border: 1px solid #111827 !important;
                color: #000000 !important;
            }
            .print-grid-table td {
                border: 1px solid #111827 !important;
                color: #000000 !important;
            }
            .print-meta-table td {
                color: #000000 !important;
            }
        }

        .print-box-panel {
            border: 1px solid #111827;
            background: #ffffff;
            box-sizing: border-box;
        }
        .print-sub-box {
            border: 1px solid #374151;
            background: #ffffff;
            box-sizing: border-box;
        }
        .print-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            line-height: 1.25;
            color: #000000;
        }
        .print-meta-table td {
            padding: 1px 2px;
            vertical-align: top;
        }
        .print-grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            line-height: 1.2;
            color: #000000;
        }
        .print-grid-table th {
            border: 1px solid #111827;
            background: #f3f4f6;
            font-weight: 700;
            padding: 3px 4px;
            text-align: left;
        }
        .print-grid-table td {
            border: 1px solid #111827;
            padding: 2.5px 4px;
            vertical-align: middle;
        }
        .print-grid-table tr.total-row td {
            font-weight: 800;
            background: #f9fafb;
        }
        .print-val-num {
            font-family: 'JetBrains Mono', Courier, monospace;
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    {{-- 1. TOP HEADER --}}
    <header class="pos-top-header">
        <div class="pos-header-left">
            <a href="{{ Route::has('pos') ? route('pos') : (Route::has('presensi2.pos') ? route('presensi2.pos') : url('/pos')) }}" class="btn-nav-pill success">
                <span>🛒</span> POS Kasir Desktop
            </a>
            <a href="{{ route('presensi2.serah-terima.desktop', array_filter(['date' => request('date'), 'shift' => request('shift'), 'summary_id' => $salesSummary?->id])) }}" class="btn-nav-pill primary">
                <span>🤝</span> Serah Terima v1
            </a>
            <a href="{{ Route::has('presensi2.serah-terima.v2.desktop') ? route('presensi2.serah-terima.v2.desktop') : (Route::has('serah-terima.v2.desktop') ? route('serah-terima.v2.desktop') : url('/bagus/presensi2/serah-terima-v2')) }}" class="btn-nav-pill" style="color: #b45309; border-color: #fcd34d; background: #fffbeb;">
                <span>📋</span> Serah Terima v2
            </a>
            <a href="{{ route('presensi2.rekap', request()->query()) }}" class="btn-nav-pill">
                <span>📱</span> Versi Mobile
            </a>
            <button type="button" onclick="openPrevSalesModal()" class="btn-nav-pill" style="color: #4338ca; border-color: #c7d2fe; background: #eef2ff;" title="Lihat Riwayat & Rincian Penjualan Shift Sebelumnya">
                <span>🔍</span> Penjualan Shift Lalu
            </button>
        </div>

        <div class="pos-top-title-group">
            <h1 class="pos-top-title">
                <span>📊</span> REKAPITULASI PENJUALAN & SHIFT
            </h1>
            <div class="pos-top-subtitle">
                {{ strtoupper($currentEmployee->branch->name ?? 'MAYA GRAHA KENCANA') }} &bull; Tanggal: {{ \Carbon\Carbon::parse($workDate)->translatedFormat('d F Y') }} &bull; {{ $activeShift?->name ?? 'Shift Aktif' }}
            </div>
        </div>

        <div class="pos-header-right">
            <button type="button" onclick="triggerPrintTable()" class="btn-nav-pill">
                <span>🖨️</span> Cetak Slip
            </button>
            <button type="button" onclick="triggerPrintHardware()" class="btn-nav-pill">
                <span>🖨️</span> Slip Hardware
            </button>
            <div style="text-align: right; border-left: 1px solid #cbd5e1; padding-left: 10px;">
                <div style="font-weight: 700; color: #1e293b; font-size: 12px;">{{ $salesSummary?->employee?->name ?? $currentEmployee?->name ?? 'Kasir' }}</div>
                <div id="desktop-digital-clock" style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #0284c7; font-weight: 600;">--:--:-- WIB</div>
            </div>
        </div>
    </header>

    {{-- 2. STATUS ALERT BAR --}}
    @if($salesSummary && $salesSummary->status === 'closed')
        <div class="status-alert-bar success">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">✅</span>
                <span><strong>Shift Telah Ditutup & Disetujui:</strong> Rekapitulasi shift berhasil dikonfirmasi pada <strong>{{ $salesSummary->closed_at?->format('d/m/Y H:i') ?? '-' }}</strong>. Data terkunci untuk audit keuangan.</span>
            </div>
            <span class="badge badge-green">Shift Selesai (Closed)</span>
        </div>

        <div style="margin: 10px 24px 0 24px; padding: 14px 20px; border-radius: 8px; background: #eff6ff; border: 1.5px solid #3b82f6; color: #1e3a8a; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 26px;">📱</span>
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #1d4ed8;">Silakan Lakukan Check-Out Presensi Pulang Melalui HP / Mobile</div>
                    <div style="font-size: 12px; color: #3b82f6; margin-top: 2px;">Tugas operasional & rekapitulasi di PC Kasir telah selesai. Untuk validasi foto selfie & radius GPS lokasi presensi yang sah, silakan buka aplikasi di HP Anda untuk melakukan Clock-Out.</div>
                </div>
            </div>
            <a href="{{ route('pos.login') }}" class="btn-block-action blue" style="width: auto; margin-bottom: 0; padding: 8px 16px; text-decoration: none;">
                <span>🔒</span> Kunci & Keluar PC Kasir
            </a>
        </div>
    @else
        <div class="status-alert-bar warning">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">⏳</span>
                <span><strong>Shift Sedang Berjalan:</strong> Nilai penjualan F&B, billing PC station, dan perkiraan kas laci terupdate live secara realtime.</span>
            </div>
            <span class="badge badge-amber">Sedang Berjalan (Open)</span>
        </div>
    @endif

    {{-- 3. FILTER & SETTING TOOLBAR --}}
    <div class="toolbar-container no-print">
        <form method="GET" action="{{ url()->current() }}" class="filter-form-group">
            <span style="font-weight: 700; color: #475569; font-size: 12px;">📅 Pilih Tanggal:</span>
            <input type="date" name="date" value="{{ \Carbon\Carbon::parse($workDate)->toDateString() }}" class="filter-input" onchange="this.form.submit()">

            <span style="font-weight: 700; color: #475569; font-size: 12px; margin-left: 8px;">🕒 Shift:</span>
            <select name="shift" class="filter-input" onchange="this.form.submit()">
                <option value="1" {{ (string) request('shift', $activeShift?->id) === '1' ? 'selected' : '' }}>Shift 1 (Pagi)</option>
                <option value="2" {{ (string) request('shift', $activeShift?->id) === '2' ? 'selected' : '' }}>Shift 2 (Siang)</option>
                <option value="3" {{ (string) request('shift', $activeShift?->id) === '3' ? 'selected' : '' }}>Shift 3 (Malam)</option>
            </select>

            @if(request('date') || request('shift'))
                <a href="{{ url()->current() }}" class="btn-nav-pill" style="padding: 4px 8px; font-size: 11px;">
                    ✖ Reset Filter
                </a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 11px; color: #64748b; font-weight: 600;">📐 Format Cetak:</span>
            <select id="print-paper-mode" onchange="applyPrintSettings()" class="filter-input" style="font-weight: 600; color: #0284c7;">
                <option value="slip-tengah" selected>Slip Ramping Tengah (Default)</option>
                <option value="medium">Tabel Sedang</option>
                <option value="a4">Kertas A4 Penuh</option>
                <option value="thermal58">Slip Mini 58mm</option>
            </select>
            <select id="print-scale-select" onchange="applyPrintSettings()" class="filter-input" style="font-weight: 600; color: #166534;">
                <option value="0.7">70%</option>
                <option value="0.8">80%</option>
                <option value="0.9">90%</option>
                <option value="1" selected>100%</option>
                <option value="1.1">110%</option>
            </select>
        </div>
    </div>

    {{-- 4. 4 TOP METRIC SUMMARY CARDS --}}
    <div class="metric-cards-grid no-print">
        <div class="small-box bg-green">
            <div class="small-box-title">Total Omset Kotor Shift</div>
            <div class="small-box-val">Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}</div>
            <div class="small-box-sub">
                <span>F&B + Billing + Print</span>
                <span>{{ $totalFnbTransactions }} Transaksi Kasir</span>
            </div>
        </div>

        <div class="small-box bg-orange">
            <div class="small-box-title">Pendapatan F&B Kasir</div>
            <div class="small-box-val">Rp {{ number_format($totalFnbIncome, 0, ',', '.') }}</div>
            <div class="small-box-sub">
                <span>{{ $fnbItemSummary->sum('qty') }} Pcs Produk Terjual</span>
                <span>Diskon: Rp {{ number_format($totalDiscountAmount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="small-box bg-cyan">
            <div class="small-box-title">Pendapatan Billing Internet</div>
            <div class="small-box-val">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</div>
            <div class="small-box-sub">
                <span>{{ $occupiedPcCount }}/{{ $totalPcCount }} PC Aktif</span>
                <span>{{ $isBillingLocked ? 'Terkunci Aman' : 'Berjalan Otomatis' }}</span>
            </div>
        </div>

        <div class="small-box bg-purple">
            <div class="small-box-title">Pendapatan Jasa Cetak & Print</div>
            <div class="small-box-val">Rp {{ number_format($totalPrintIncome, 0, ',', '.') }}</div>
            <div class="small-box-sub">
                <span>Total {{ $totalPrintPages }} Lembar</span>
                <span>{{ $isPrintLocked ? 'Audit Terkunci' : 'Hardware Tracker' }}</span>
            </div>
        </div>
    </div>

    {{-- 5. MAIN 2-COLUMN DESKTOP CONTAINER --}}
    <main class="rekap-desktop-container no-print">

        {{-- KOLOM KIRI (390px): KAS LACI, PECAHAN & AKSI SHIFT --}}
        <div class="rekap-col-left">

            {{-- PANEL REKONSILIASI KAS LACI --}}
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-cyan">
                    <span>💵 Rekonsiliasi Kas Laci (Blind Count)</span>
                    <span class="badge badge-blue">Audit SoD</span>
                </div>
                <div class="panel-box-body">
                    <div class="stat-row">
                        <span class="stat-label">Modal Kas Awal:</span>
                        <span class="stat-value">Rp {{ number_format($salesSummary?->opening_cash ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Penjualan Kasir Tunai:</span>
                        <span class="stat-value">Rp {{ number_format(max(0, ($salesSummary?->total_fnb_sales ?? $totalFnbIncome) - ($nonCashAmount ?? 0)), 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Billing Internet Tunai:</span>
                        <span class="stat-value">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Jasa Print Tunai:</span>
                        <span class="stat-value">Rp {{ number_format($totalPrintIncome, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row" style="background: #f8fafc; margin: 4px -16px; padding: 8px 16px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                        <span class="stat-label" style="font-weight: 700; color: #1e293b;">Target Kas Sistem:</span>
                        <span class="stat-value" style="color: #0284c7; font-size: 14px;">Rp {{ number_format($systemExpectedCash, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label" style="font-weight: 700;">Fisik Kas Aktual di Laci:</span>
                        <span class="stat-value" style="color: #166534; font-size: 14px;">Rp {{ number_format($cashInDrawer, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label" style="font-weight: 700;">Status Selisih Kas:</span>
                        @if($cashDifference === 0.0 || $cashDifference == 0)
                            <span class="badge badge-green">✅ Cocok (Rp 0)</span>
                        @elseif($cashDifference < 0)
                            <span class="badge badge-red">⚠️ Kurang Rp {{ number_format(abs($cashDifference), 0, ',', '.') }}</span>
                        @else
                            <span class="badge badge-blue">ℹ️ Lebih Rp {{ number_format($cashDifference, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Non-Tunai (QRIS / Transfer):</span>
                        <span class="stat-value" style="color: #7c3aed;">Rp {{ number_format($nonCashAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- PANEL RINCIAN PECAHAN FISIK --}}
            @if(!empty($cashDenominations) && is_array($cashDenominations))
                <div class="panel-box">
                    <div class="panel-box-header panel-box-header-slate">
                        <span>🪙 Rincian Pecahan Uang Fisik</span>
                        <span class="badge badge-gray">Hitung Fisik</span>
                    </div>
                    <div class="panel-box-body" style="padding: 0;">
                        <table class="table-data" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>Pecahan</th>
                                    <th style="text-align: center;">Lembar/Keping</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $denomLabels = [
                                        '100000' => 'Rp 100.000',
                                        '50000' => 'Rp 50.000',
                                        '20000' => 'Rp 20.000',
                                        '10000' => 'Rp 10.000',
                                        '5000' => 'Rp 5.000',
                                        '2000' => 'Rp 2.000',
                                        '1000' => 'Rp 1.000',
                                        'koin' => 'Koin / Logam',
                                    ];
                                @endphp
                                @foreach($cashDenominations as $dKey => $dCount)
                                    @php
                                        $dVal = (int) $dKey;
                                        $dSubtotal = $dKey === 'koin' ? (float) $dCount : ((int) $dCount * $dVal);
                                    @endphp
                                    @if((float) $dCount > 0)
                                        <tr>
                                            <td style="font-weight: 600;">{{ $denomLabels[$dKey] ?? ('Rp ' . number_format((int)$dKey, 0, ',', '.')) }}</td>
                                            <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $dCount }}</td>
                                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                                Rp {{ number_format($dSubtotal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- PANEL AKSI & NAVIGASI SHIFT --}}
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-green">
                    <span>⚡ Tindakan & Penutupan Shift</span>
                </div>
                <div class="panel-box-body">
                    <a href="{{ route('presensi2.serah-terima.desktop', array_filter(['date' => request('date'), 'shift' => request('shift'), 'summary_id' => $salesSummary?->id])) }}" class="btn-block-action blue">
                        <span>🤝</span> Lanjut ke Serah Terima Kas Shift ➔
                    </a>

                    <button type="button" onclick="triggerPrintTable()" class="btn-block-action secondary">
                        <span>🖨️</span> Cetak Struk Laporan Summary Kasir
                    </button>

                    <button type="button" onclick="triggerPrintHardware()" class="btn-block-action secondary">
                        <span>🖨️</span> Cetak Struk Hardware Printer
                    </button>

                    @if($attendanceToday && !$attendanceToday->clock_out_at)
                        <button type="button" onclick="submitRekapCheckOut()" id="btn-clock-out-rekap" class="btn-block-action red" style="margin-top: 10px;">
                            <span>⏱️</span> Selesai Shift & Check-Out Pulang Presensi
                        </button>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: TAB DETAIL OPERASIONAL SHIFT --}}
        <div class="rekap-col-right">

            <div class="panel-box">
                <div class="tabs-header-nav">
                    <button type="button" onclick="switchDesktopTab('pane-fnb-items', this)" class="tab-nav-btn active">
                        <span>🍔</span> Rincian Item F&B ({{ $fnbItemSummary->count() }})
                    </button>
                    <button type="button" onclick="switchDesktopTab('pane-nota-sales', this)" class="tab-nav-btn">
                        <span>🧾</span> Daftar Nota Kasir ({{ $fnbSales->count() }})
                    </button>
                    <button type="button" onclick="switchDesktopTab('pane-stock-audit', this)" class="tab-nav-btn">
                        <span>📦</span> Audit Stok Barang (Kertas, Minuman, Makanan)
                    </button>
                    <button type="button" onclick="switchDesktopTab('pane-shift-history', this)" class="tab-nav-btn">
                        <span>🕒</span> Riwayat Shift Sebelumnya
                    </button>
                </div>

                {{-- TAB 1: RINCIAN ITEM F&B --}}
                <div id="pane-fnb-items" class="tab-pane active" style="padding: 0;">
                    @if($fnbItemSummary->isNotEmpty())
                        <table class="table-data">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">No</th>
                                    <th>Nama Menu / Produk F&B</th>
                                    <th>Kategori</th>
                                    <th style="text-align: center;">Qty Terjual</th>
                                    <th style="text-align: right;">Harga Rata-Rata</th>
                                    <th style="text-align: right;">Subtotal Omset</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fnbItemSummary as $index => $item)
                                    <tr>
                                        <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                                        <td style="font-weight: 700; color: #1e293b;">{{ $item['name'] }}</td>
                                        <td>
                                            <span class="badge {{ str_contains(strtolower($item['category'] ?? ''), 'minum') ? 'badge-blue' : 'badge-amber' }}">
                                                {{ $item['category'] ?? 'F&B' }}
                                            </span>
                                        </td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px;">
                                            {{ $item['qty'] }}
                                        </td>
                                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </td>
                                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #00a65a;">
                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc; font-weight: 800;">
                                    <td colspan="3" style="text-align: right; padding: 10px 12px;">TOTAL PENJUALAN F&B:</td>
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 14px;">{{ $fnbItemSummary->sum('qty') }}</td>
                                    <td></td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 14px; color: #00a65a;">
                                        Rp {{ number_format($fnbItemSummary->sum('subtotal'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div style="text-align: center; padding: 36px 20px; color: #64748b;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🍔</div>
                            <div>Belum ada transaksi item F&B pada shift ini.</div>
                        </div>
                    @endif
                </div>

                {{-- TAB 2: DAFTAR NOTA KASIR --}}
                <div id="pane-nota-sales" class="tab-pane" style="padding: 0;">
                    @if($fnbSales->isNotEmpty())
                        <table class="table-data">
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Waktu</th>
                                    <th>Item Belanja</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fnbSales as $sale)
                                    <tr>
                                        <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0284c7;">
                                            {{ $sale->invoice_number }}
                                        </td>
                                        <td style="color: #64748b; white-space: nowrap;">
                                            {{ $sale->created_at->format('H:i:s') }}
                                        </td>
                                        <td style="max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            @foreach($sale->items as $sItem)
                                                {{ $sItem->variant->name ?? $sItem->product_name ?? 'Item' }} ({{ $sItem->qty }}){{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge {{ $sale->payment_method === 'cash' ? 'badge-green' : 'badge-blue' }}">
                                                {{ strtoupper($sale->payment_method) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $sale->payment_status === 'paid' ? 'badge-green' : 'badge-amber' }}">
                                                {{ ucfirst($sale->payment_status) }}
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                            Rp {{ number_format($sale->net_amount > 0 ? $sale->net_amount : ($sale->total_fnb + $sale->total_billing), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="text-align: center; padding: 36px 20px; color: #64748b;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🧾</div>
                            <div>Belum ada transaksi nota penjualan pada shift ini.</div>
                        </div>
                    @endif
                </div>

                {{-- TAB 3: AUDIT STOK BARANG --}}
                <div id="pane-stock-audit" class="tab-pane">
                    {{-- KERTAS --}}
                    <div style="margin-bottom: 20px;">
                        <h4 style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>📄</span> Stok Kertas Print (A4 & F4)
                        </h4>
                        <table class="table-data" style="border: 1px solid #e2e8f0; border-radius: 4px;">
                            <thead>
                                <tr>
                                    <th>Kertas</th>
                                    <th style="text-align: center;">Awal Shift</th>
                                    <th style="text-align: center;">Terpakai</th>
                                    <th style="text-align: center;">Sisa Sistem</th>
                                    <th style="text-align: center;">Fisik Aktual</th>
                                    <th style="text-align: center;">Status Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockComparisons['kertas'] ?? [] as $k)
                                    @php $diff = ($k['physical_stock'] ?? 0) - ($k['sesudahnya'] ?? 0); @endphp
                                    <tr>
                                        <td style="font-weight: 700;">{{ $k['name'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $k['sebelumnya'] }} lbr</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #dd4b39;">{{ $k['terjual'] }} lbr</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ $k['sesudahnya'] }} lbr</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #0284c7;">{{ $k['physical_stock'] ?? 0 }} lbr</td>
                                        <td style="text-align: center;">
                                            @if($diff === 0)
                                                <span class="badge badge-green">Cocok</span>
                                            @elseif($diff < 0)
                                                <span class="badge badge-red">Kurang {{ abs($diff) }}</span>
                                            @else
                                                <span class="badge badge-blue">Lebih {{ $diff }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- MINUMAN --}}
                    <div style="margin-bottom: 20px;">
                        <h4 style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>🥤</span> Stok Minuman Dingin (Kulkas Kasir)
                        </h4>
                        <table class="table-data" style="border: 1px solid #e2e8f0; border-radius: 4px;">
                            <thead>
                                <tr>
                                    <th>Nama Minuman</th>
                                    <th style="text-align: center;">Awal Shift</th>
                                    <th style="text-align: center;">Terjual</th>
                                    <th style="text-align: center;">Sisa Sistem</th>
                                    <th style="text-align: center;">Fisik Aktual</th>
                                    <th style="text-align: center;">Status Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockComparisons['minuman'] ?? [] as $d)
                                    @php $diff = ($d['physical_stock'] ?? 0) - ($d['sesudahnya'] ?? 0); @endphp
                                    <tr>
                                        <td style="font-weight: 700;">{{ $d['name'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $d['sebelumnya'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #dd4b39;">{{ $d['terjual'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ $d['sesudahnya'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #0284c7;">{{ $d['physical_stock'] ?? 0 }}</td>
                                        <td style="text-align: center;">
                                            @if($diff === 0)
                                                <span class="badge badge-green">Cocok</span>
                                            @elseif($diff < 0)
                                                <span class="badge badge-red">Kurang {{ abs($diff) }}</span>
                                            @else
                                                <span class="badge badge-blue">Lebih {{ $diff }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" style="text-align: center; color: #64748b;">Tidak ada data produk minuman.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- MAKANAN --}}
                    <div>
                        <h4 style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>🍿</span> Stok Makanan / Snack Kasir
                        </h4>
                        <table class="table-data" style="border: 1px solid #e2e8f0; border-radius: 4px;">
                            <thead>
                                <tr>
                                    <th>Nama Makanan / Snack</th>
                                    <th style="text-align: center;">Awal Shift</th>
                                    <th style="text-align: center;">Terjual</th>
                                    <th style="text-align: center;">Sisa Sistem</th>
                                    <th style="text-align: center;">Fisik Aktual</th>
                                    <th style="text-align: center;">Status Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockComparisons['makanan'] ?? [] as $m)
                                    @php $diff = ($m['physical_stock'] ?? 0) - ($m['sesudahnya'] ?? 0); @endphp
                                    <tr>
                                        <td style="font-weight: 700;">{{ $m['name'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $m['sebelumnya'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #dd4b39;">{{ $m['terjual'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ $m['sesudahnya'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #0284c7;">{{ $m['physical_stock'] ?? 0 }}</td>
                                        <td style="text-align: center;">
                                            @if($diff === 0)
                                                <span class="badge badge-green">Cocok</span>
                                            @elseif($diff < 0)
                                                <span class="badge badge-red">Kurang {{ abs($diff) }}</span>
                                            @else
                                                <span class="badge badge-blue">Lebih {{ $diff }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" style="text-align: center; color: #64748b;">Tidak ada data produk makanan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 4: RIWAYAT SHIFT SEBELUMNYA --}}
                <div id="pane-shift-history" class="tab-pane" style="padding: 0;">
                    @if($recentSummaries->isNotEmpty())
                        <table class="table-data">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Shift</th>
                                    <th>Kasir</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Total Omset</th>
                                    <th style="text-align: right;">Fisik Laci</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSummaries as $hist)
                                    <tr>
                                        <td style="font-weight: 600;">{{ $hist->work_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $hist->shift->name ?? ('Shift ' . $hist->shiftNumber()) }}</td>
                                        <td>{{ $hist->employee->name ?? 'Kasir' }}</td>
                                        <td>
                                            <span class="badge {{ $hist->status === 'closed' ? 'badge-green' : 'badge-amber' }}">
                                                {{ ucfirst($hist->status) }}
                                            </span>
                                        </td>
                                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                            Rp {{ number_format($hist->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">
                                            Rp {{ number_format($hist->cash_in_drawer ?? $hist->blind_cash_count ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ Route::has('presensi2.rekap.desktop') ? route('presensi2.rekap.desktop', ['date' => $hist->work_date?->toDateString(), 'shift' => $hist->shiftNumber(), 'summary_id' => $hist->id]) : url('/bagus/presensi2/rekap-desktop?date=' . $hist->work_date?->toDateString() . '&shift=' . $hist->shiftNumber() . '&summary_id=' . $hist->id) }}" class="btn-nav-pill" style="padding: 3px 8px; font-size: 11px;">
                                                Buka
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="text-align: center; padding: 36px 20px; color: #64748b;">
                            <div style="font-size: 28px; margin-bottom: 8px;">🕒</div>
                            <div>Belum ada riwayat rekapitulasi shift sebelumnya.</div>
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </main>

    {{-- PRINT SLIP 1: LAPORAN SUMMARY HARIAN (RATA TENGAH & VERTIKAL) --}}
    <div id="print-table-report" class="print-table-report">
        @php
            $rekapCode = \Carbon\Carbon::parse($workDate)->format('Ymd')
                . '-B' . ($currentEmployee?->branch_id ?? 1)
                . '-S' . ($activeShift?->id ?? 1)
                . '-' . ($salesSummary?->id ?? $currentEmployee?->id ?? 1);
            $cashierName = $salesSummary?->employee?->name ?? $currentEmployee?->name ?? 'Kasir';

            $formatTerbilang = function ($angka) use (&$formatTerbilang) {
                $angka = abs((int) $angka);
                $b = ['', 'SATU', 'DUA', 'TIGA', 'EMPAT', 'LIMA', 'ENAM', 'TUJUH', 'DELAPAN', 'SEMBILAN', 'SEPULUH', 'SEBELAS'];
                if ($angka < 12) {
                    return $b[$angka];
                } elseif ($angka < 20) {
                    return $formatTerbilang($angka - 10) . ' BELAS';
                } elseif ($angka < 100) {
                    return $formatTerbilang((int) ($angka / 10)) . ' PULUH ' . $formatTerbilang($angka % 10);
                } elseif ($angka < 200) {
                    return 'SERATUS ' . $formatTerbilang($angka - 100);
                } elseif ($angka < 1000) {
                    return $formatTerbilang((int) ($angka / 100)) . ' RATUS ' . $formatTerbilang($angka % 100);
                } elseif ($angka < 2000) {
                    return 'SERIBU ' . $formatTerbilang($angka - 1000);
                } elseif ($angka < 1000000) {
                    return $formatTerbilang((int) ($angka / 1000)) . ' RIBU ' . $formatTerbilang($angka % 1000);
                } elseif ($angka < 1000000000) {
                    return $formatTerbilang((int) ($angka / 1000000)) . ' JUTA ' . $formatTerbilang($angka % 1000000);
                }
                return (string) $angka;
            };

            $snackSoldItems = collect($stockComparisons['makanan'] ?? [])->filter(fn($item) => (int)($item['terjual'] ?? 0) > 0);
            $snackTotalQty = (int) $snackSoldItems->sum('terjual');
            $snackTotalRp = (float) $snackSoldItems->sum(fn($i) => ((int)$i['terjual'] * (float)($i['price'] ?? 0)));

            $drinkSoldItems = collect($stockComparisons['minuman'] ?? [])->filter(fn($item) => (int)($item['terjual'] ?? 0) > 0);
            $drinkTotalQty = (int) $drinkSoldItems->sum('terjual');
            $drinkTotalRp = (float) $drinkSoldItems->sum(fn($i) => ((int)$i['terjual'] * (float)($i['price'] ?? 0)));

            $kertasA4 = collect($stockComparisons['kertas'] ?? [])->firstWhere('id', 'kertas-a4');
            $kertasF4 = collect($stockComparisons['kertas'] ?? [])->firstWhere('id', 'kertas-f4');
            $a4Sold = (int) ($kertasA4['terjual'] ?? 0);
            $f4Sold = (int) ($kertasF4['terjual'] ?? 0);
            $totalPaperPages = $a4Sold + $f4Sold;
            $totalPrintRp = (float) $totalPrintIncome;

            $cashInWords = $formatTerbilang($cashInDrawer) . ' RUPIAH';
        @endphp

        <div style="font-size: 13px; font-weight: 800; text-align: left; margin-bottom: 6px; letter-spacing: 0.02em; color: #000;">
            Laporan Summary Harian
        </div>

        <div class="print-box-panel" style="margin-bottom: 6px; padding: 4px 6px;">
            <table class="print-meta-table">
                <tbody>
                    <tr><td style="width: 78px;">Invoice</td><td style="width: 8px;">:</td><td><strong>{{ $rekapCode }}</strong></td></tr>
                    <tr><td>Outlet</td><td>:</td><td><strong>{{ strtoupper($currentEmployee->branch->name ?? 'MAYA 1') }}</strong></td></tr>
                    <tr><td>Tanggal Laporan</td><td>:</td><td><strong>{{ \Carbon\Carbon::parse($workDate)->format('d-m-Y') }}</strong></td></tr>
                    <tr><td>Dicetak</td><td>:</td><td><strong>{{ now()->format('d-m-Y H:i') }}</strong></td></tr>
                </tbody>
            </table>
        </div>

        {{-- TABEL ITEM PENJUALAN --}}
        <div style="margin-bottom: 6px;">
            <table class="print-grid-table">
                <thead>
                    <tr>
                        <th style="width: 20px; text-align: center;">No</th>
                        <th>Item Penjualan</th>
                        <th style="width: 32px; text-align: center;">Qty</th>
                        <th style="width: 70px; text-align: right;">Total Rp</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNum = 1; @endphp
                    @if($snackTotalQty > 0)
                        <tr>
                            <td style="text-align: center;">{{ $rowNum++ }}</td>
                            <td>SNACK / MAKANAN RINGAN</td>
                            <td style="text-align: center;">{{ $snackTotalQty }}</td>
                            <td class="print-val-num">Rp {{ number_format($snackTotalRp, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($drinkTotalQty > 0)
                        <tr>
                            <td style="text-align: center;">{{ $rowNum++ }}</td>
                            <td>MINUMAN</td>
                            <td style="text-align: center;">{{ $drinkTotalQty }}</td>
                            <td class="print-val-num">Rp {{ number_format($drinkTotalRp, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($totalPrintIncome > 0 || $totalPaperPages > 0)
                        <tr>
                            <td style="text-align: center;">{{ $rowNum++ }}</td>
                            <td>PRINT & SCAN</td>
                            <td style="text-align: center;">{{ $totalPaperPages }}</td>
                            <td class="print-val-num">Rp {{ number_format($totalPrintRp, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($totalInternetIncome > 0)
                        <tr>
                            <td style="text-align: center;">{{ $rowNum++ }}</td>
                            <td>BILLING INTERNET</td>
                            <td style="text-align: center;">1</td>
                            <td class="print-val-num">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;">TOTAL OMSET :</td>
                        <td class="print-val-num" style="font-weight: 800;">Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- KAS & TANDA TANGAN --}}
        <div class="print-box-panel" style="margin-bottom: 6px; padding: 4px 6px;">
            <table class="print-meta-table">
                <tbody>
                    <tr><td style="width: 90px;">Kas Laci Shift Ini</td><td style="width: 8px;">:</td><td class="print-val-num"><strong>Rp {{ number_format($cashInDrawer, 0, ',', '.') }}</strong></td></tr>
                    <tr><td>Non-Tunai (QRIS)</td><td>:</td><td class="print-val-num">Rp {{ number_format($nonCashAmount, 0, ',', '.') }}</td></tr>
                    <tr><td>Status Selisih</td><td>:</td><td><strong>{{ $cashDifference == 0 ? 'COCOK (Rp 0)' : ($cashDifference < 0 ? 'KURANG Rp ' . number_format(abs($cashDifference), 0, ',', '.') : 'LEBIH Rp ' . number_format($cashDifference, 0, ',', '.')) }}</strong></td></tr>
                    <tr><td>Terbilang</td><td>:</td><td><em># {{ $cashInWords }} #</em></td></tr>
                </tbody>
            </table>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 9.5px; text-align: center; margin-top: 8px;">
            <tbody>
                <tr>
                    <td style="width: 50%;">Petugas Kasir</td>
                    <td style="width: 50%;">Kasir Penerima / Shift Berikutnya</td>
                </tr>
                <tr>
                    <td style="height: 38px;"></td>
                    <td style="height: 38px;"></td>
                </tr>
                <tr>
                    <td><strong>( {{ strtoupper($cashierName) }} )</strong></td>
                    <td><strong>( {{ strtoupper($nextShiftLabel ?? 'SHIFT BERIKUTNYA') }} )</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- PRINT SLIP 2: LAPORAN HARDWARE PRINTER --}}
    <div id="print-hardware-report" class="print-table-report">
        <div style="font-size: 13px; font-weight: 800; text-align: left; margin-bottom: 6px; letter-spacing: 0.02em; color: #000;">
            Laporan Hardware & Counter Printer
        </div>
        <div class="print-box-panel" style="margin-bottom: 6px; padding: 4px 6px;">
            <table class="print-meta-table">
                <tbody>
                    <tr><td style="width: 78px;">Outlet</td><td style="width: 8px;">:</td><td><strong>{{ strtoupper($currentEmployee->branch->name ?? 'MAYA 1') }}</strong></td></tr>
                    <tr><td>Tanggal</td><td>:</td><td><strong>{{ \Carbon\Carbon::parse($workDate)->format('d-m-Y') }}</strong></td></tr>
                    <tr><td>Kasir</td><td>:</td><td><strong>{{ strtoupper($salesSummary?->employee?->name ?? $currentEmployee?->name ?? 'KASIR') }}</strong></td></tr>
                </tbody>
            </table>
        </div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th>Ukuran Kertas</th>
                    <th style="text-align: center;">Counter Hardware</th>
                    <th style="text-align: right;">Estimasi Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Kertas A4</td>
                    <td style="text-align: center;">{{ $realHardwarePages }} Lembar</td>
                    <td class="print-val-num">Rp {{ number_format($realHardwareAmount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        // LIVE DIGITAL CLOCK
        function updateClock() {
            const clockEl = document.getElementById('desktop-digital-clock');
            if (!clockEl) return;
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // TAB SWITCHER
        function switchDesktopTab(paneId, btn) {
            document.querySelectorAll('.tabs-header-nav .tab-nav-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.rekap-col-right .tab-pane').forEach(p => p.classList.remove('active'));

            btn.classList.add('active');
            const pane = document.getElementById(paneId);
            if (pane) pane.classList.add('active');
        }

        // PRINT SETTINGS
        function applyPrintSettings() {
            const paperSelect = document.getElementById('print-paper-mode');
            const scaleSelect = document.getElementById('print-scale-select');
            const printBox = document.getElementById('print-table-report');
            const hwBox = document.getElementById('print-hardware-report');

            if (!printBox || !paperSelect || !scaleSelect) return;

            const mode = paperSelect.value;
            const scale = scaleSelect.value;

            [printBox, hwBox].forEach(b => {
                if (!b) return;
                b.classList.remove('paper-medium', 'paper-a4', 'paper-thermal58');
                if (mode === 'medium') b.classList.add('paper-medium');
                else if (mode === 'a4') b.classList.add('paper-a4');
                else if (mode === 'thermal58') b.classList.add('paper-thermal58');
                b.style.setProperty('--print-scale', scale);
            });
        }

        function triggerPrintTable() {
            document.body.classList.remove('printing-hardware');
            applyPrintSettings();
            window.print();
        }

        function triggerPrintHardware() {
            document.body.classList.add('printing-hardware');
            applyPrintSettings();
            window.print();
        }

        // CHECK-OUT PRESENSI PULANG
        async function submitRekapCheckOut() {
            const btn = document.getElementById('btn-clock-out-rekap');
            if (!btn) return;

            if (!confirm('Apakah Anda yakin ingin menyelesaikan shift dan melakukan Presensi Pulang sekarang?')) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Memproses Presensi Pulang...';

            let latitude = null;
            let longitude = null;

            if (navigator.geolocation) {
                try {
                    const pos = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 7000,
                            maximumAge: 0
                        });
                    });
                    latitude = pos.coords.latitude;
                    longitude = pos.coords.longitude;
                } catch (e) {
                    console.warn('Geolocation check-out warning:', e);
                }
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('is_desktop_bypass', '1');
            if (latitude && longitude) {
                formData.append('latitude', latitude);
                formData.append('longitude', longitude);
            }

            try {
                const response = await fetch('{{ route("presensi2.clock-out") }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert('Presensi Pulang Berhasil! ' + (result.message || 'Shift kerja telah selesai.'));
                    window.location.reload();
                } else {
                    alert('Gagal Check-Out: ' + (result.message || 'Terjadi kesalahan server'));
                    btn.disabled = false;
                    btn.innerHTML = '<span>⏱️</span> Selesai Shift & Check-Out Pulang Presensi';
                }
            } catch (err) {
                console.error(err);
                alert('Koneksi terputus saat memproses check-out pulang.');
                btn.disabled = false;
                btn.innerHTML = '<span>⏱️</span> Selesai Shift & Check-Out Pulang Presensi';
            }
        }
    </script>
    @include('presensi2.partials.modal_shift_history_and_settlement')
</body>
</html>

