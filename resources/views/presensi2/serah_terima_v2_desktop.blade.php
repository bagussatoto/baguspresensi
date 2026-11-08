<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serah Terima & Setoran Kas v2 (Desktop) — Maya Graha Kencana</title>
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
            --pos-orange: #f39c12;
            --pos-orange-dark: #e67e22;
            --pos-cyan: #00c0ef;
            --pos-cyan-dark: #0097bc;
            --pos-red: #dd4b39;
            --pos-red-light: #fbeae8;
            --pos-blue: #0284c7;
            --pos-border: #cbd5e1;
            --pos-dark: #2c3e50;
            --pos-header-slate: #f1f5f9;
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 24px;
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

        .pos-header-right {
            display: flex;
            align-items: center;
            gap: 12px;
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

        /* TOOLBAR FILTER & SUMMARY */
        .v2-toolbar {
            margin: 14px 24px 0 24px;
            padding: 10px 16px;
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 5px;
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

        /* MAIN CONTENT AREA */
        .v2-main-content {
            padding: 14px 24px 28px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
        }

        /* STACKED CARD / PANEL BOX */
        .v2-panel-box {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        /* CARD HEADERS MATCHING THE REFERENCE */
        .v2-box-header-gray {
            background: #f8fafc;
            color: #334155;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }

        .v2-box-header-cyan {
            background: #00c0ef;
            color: #ffffff;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .v2-box-header-orange {
            background: #f39c12;
            color: #ffffff;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* V2 DATA TABLE */
        .v2-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            background: #ffffff;
        }
        .v2-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            padding: 9px 12px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
            font-size: 11.5px;
            letter-spacing: 0.2px;
        }
        .v2-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }
        .v2-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .v2-table-empty {
            text-align: center;
            padding: 24px 16px;
            color: #94a3b8;
            font-size: 13px;
        }

        /* FOOTER / SUMMARY ROW */
        .v2-table-summary-row td {
            background: #f8fafc;
            font-weight: 800;
            border-top: 2px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }

        /* SQUARE SHIFT PILLS (EXACT STYLE OF REFERENCE IMAGE) */
        .shift-badges-group {
            display: inline-flex;
            gap: 4px;
            align-items: center;
        }
        .badge-shift-sq {
            display: inline-block;
            width: 22px;
            height: 22px;
            line-height: 22px;
            text-align: center;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 800;
            background: #f1f5f9;
            color: #94a3b8;
            border: 1px solid #cbd5e1;
        }
        .badge-shift-sq.active {
            background: #f39c12;
            color: #ffffff;
            border-color: #e67e22;
            box-shadow: 0 1px 2px rgba(0,0,0,0.15);
        }

        /* ACTION BUTTONS */
        .btn-act-orange {
            background: #f39c12;
            color: #ffffff;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: all 0.15s ease;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-act-orange:hover {
            background: #e67e22;
        }

        .btn-act-green {
            background: #00a65a;
            color: #ffffff;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: all 0.15s ease;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-act-green:hover {
            background: #008d4c;
        }

        .btn-bottom-action-green {
            background: #2dd4bf;
            color: #0f766e;
            border: 1px solid #14b8a6;
            padding: 7px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-bottom-action-green:hover {
            background: #14b8a6;
            color: #ffffff;
        }

        .btn-bottom-action-setor {
            background: #f87171;
            color: #ffffff;
            border: 1px solid #ef4444;
            padding: 7px 18px;
            border-radius: 4px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 3px rgba(239, 68, 68, 0.3);
            transition: all 0.15s ease;
        }
        .btn-bottom-action-setor:hover {
            background: #dc2626;
        }

        /* MODAL STYLES */
        .pos-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            z-index: 1050;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .pos-modal-overlay.active {
            display: flex;
        }
        .pos-modal-box {
            background: #ffffff;
            border-radius: 6px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .pos-modal-header {
            padding: 12px 18px;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .form-control {
            width: 100%;
            padding: 8px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 13px;
            color: #1e293b;
            background: #ffffff;
            outline: none;
        }
        .form-control:focus {
            border-color: #0284c7;
        }
    </style>
</head>
<body>

    {{-- 1. TOP HEADER --}}
    <header class="pos-top-header">
        <div class="pos-header-left">
            <a href="{{ Route::has('pos') ? route('pos') : (Route::has('presensi2.pos') ? route('presensi2.pos') : url('/pos')) }}" class="btn-nav-pill success">
                <span>🛒</span> POS Desktop
            </a>
            <a href="{{ Route::has('presensi2.rekap.desktop') ? route('presensi2.rekap.desktop') : url('/bagus/presensi2/rekap-desktop') }}" class="btn-nav-pill">
                <span>📊</span> Rekap Desktop
            </a>
            <a href="{{ route('presensi2.serah-terima.desktop') }}" class="btn-nav-pill">
                <span>🤝</span> Versi v1 (Form Kas)
            </a>
            <a href="{{ route('presensi2.serah-terima') }}" class="btn-nav-pill">
                <span>📱</span> Versi HP
            </a>
            <button type="button" onclick="openPrevSalesModal()" class="btn-nav-pill" style="color: #4338ca; border-color: #c7d2fe; background: #eef2ff;" title="Lihat Riwayat & Rincian Penjualan Shift Sebelumnya">
                <span>🔍</span> Penjualan Shift Lalu
            </button>
        </div>

        <div class="pos-top-title-group">
            <h1 class="pos-top-title">
                <span>🤝</span> SERAH TERIMA & SETORAN KAS (v2)
            </h1>
            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                {{ strtoupper($currentEmployee->branch->name ?? 'MAYA GRAHA KENCANA') }} &bull; Tanggal: {{ \Carbon\Carbon::parse($workDate)->translatedFormat('d F Y') }} &bull; Shift {{ $activeShift?->id ?? 1 }}
            </div>
        </div>

        <div class="pos-header-right">
            <button type="button" onclick="openModalHandoverBaru()" class="btn-nav-pill primary">
                <span>+</span> Input Serah Terima Shift
            </button>
            <div style="text-align: right; border-left: 1px solid #cbd5e1; padding-left: 10px;">
                <div style="font-weight: 700; color: #1e293b; font-size: 12px;">{{ $salesSummary?->employee?->name ?? $currentEmployee->name }}</div>
                <div id="live-clock" style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #0284c7; font-weight: 600;">--:--:-- WIB</div>
            </div>
        </div>
    </header>

    {{-- 2. TOOLBAR FILTER & SUMMARY CHIPS --}}
    <div class="v2-toolbar">
        <form method="GET" action="{{ url()->current() }}" class="filter-form-group">
            <span style="font-weight: 700; color: #475569; font-size: 12px;">📅 Tanggal Operasional:</span>
            <input type="date" name="date" value="{{ \Carbon\Carbon::parse($workDate)->toDateString() }}" class="filter-input" onchange="this.form.submit()">

            <span style="font-weight: 700; color: #475569; font-size: 12px; margin-left: 8px;">🕒 Shift:</span>
            <select name="shift" class="filter-input" onchange="this.form.submit()">
                <option value="1" {{ (string) request('shift', $activeShift?->id) === '1' ? 'selected' : '' }}>Shift 1 (Pagi)</option>
                <option value="2" {{ (string) request('shift', $activeShift?->id) === '2' ? 'selected' : '' }}>Shift 2 (Siang)</option>
                <option value="3" {{ (string) request('shift', $activeShift?->id) === '3' ? 'selected' : '' }}>Shift 3 (Sore)</option>
                <option value="4" {{ (string) request('shift', $activeShift?->id) === '4' ? 'selected' : '' }}>Shift 4 (Malam)</option>
            </select>

            @if(request('date') || request('shift'))
                <a href="{{ url()->current() }}" class="btn-nav-pill" style="padding: 4px 8px; font-size: 11px;">
                    ✖ Reset
                </a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 12px; font-size: 12px;">
            <div style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 700;">
                💵 Total Siap Setor: Rp {{ number_format($totalSiapSetor, 0, ',', '.') }}
            </div>
            <div style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 4px; font-weight: 700;">
                ⏳ {{ $readyToDepositSummaries->count() }} Shift Menunggu Setor
            </div>
        </div>
    </div>

    {{-- FLASH MESSAGES & MOBILE CHECK-OUT NOTIFICATION --}}
    @if(session('success'))
        <div style="margin: 12px 24px 0 24px; padding: 12px 18px; background: #dcfce7; border: 1px solid #86efac; color: #166534; border-radius: 6px; font-weight: 600;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin: 12px 24px 0 24px; padding: 12px 18px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 6px; font-weight: 600;">
            ✕ {{ session('error') }}
        </div>
    @endif

    @if(($salesSummary && ($salesSummary->status === 'closed' || $salesSummary->handover_status === 'completed')) || session('success'))
        <div style="margin: 12px 24px 0 24px; padding: 14px 20px; border-radius: 8px; background: #eff6ff; border: 1.5px solid #3b82f6; color: #1e3a8a; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; box-shadow: 0 2px 6px rgba(59, 130, 246, 0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 26px;">📱</span>
                <div>
                    <div style="font-weight: 800; font-size: 14px; color: #1d4ed8;">Silakan Lakukan Check-Out Presensi Pulang Melalui HP / Mobile</div>
                    <div style="font-size: 12px; color: #3b82f6; margin-top: 2px;">Tugas & serah terima di PC Kasir telah dicatat. Untuk validasi foto selfie & radius GPS lokasi presensi yang sah, silakan buka aplikasi di HP Anda untuk melakukan Clock-Out.</div>
                </div>
            </div>
            <a href="{{ route('pos.login') }}" class="btn-nav-pill primary" style="background: #2563eb; color: #fff; text-decoration: none; padding: 8px 16px; font-weight: 700; border-radius: 6px;">
                <span>🔒</span> Kunci & Keluar PC Kasir
            </a>
        </div>
    @endif

    {{-- 3. MAIN CONTENT: 3 STACKED CARDS (SERAH TERIMA, SIAP SETOR, HISTORY SETOR) --}}
    <main class="v2-main-content">

        {{-- ==================== CARD 1: SERAH TERIMA ==================== --}}
        <div class="v2-panel-box">
            <div class="v2-box-header-gray">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>🤝</span>
                    <span>Serah terima</span>
                </div>
                <div style="font-size: 11px; color: #64748b; font-weight: 500;">
                    penjualan &gt; shift aktif hari ini
                </div>
            </div>

            <table class="v2-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Tanggal</th>
                        <th>Invoice</th>
                        <th>Penyerah</th>
                        <th>Penerima</th>
                        <th style="text-align: center;">payment</th>
                        <th style="text-align: right;">Bruto</th>
                        <th style="text-align: right;">Netto</th>
                        <th style="text-align: center; width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayHandovers as $idx => $th)
                        @php
                            $thBruto = (float) ($th->total_revenue ?? 0);
                            $thNetto = (float) $th->handoverAmount();
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                            <td style="font-family: 'JetBrains Mono', monospace;">
                                {{ $th->handover_at ? $th->handover_at->format('Y-m-d H:i:s') : $th->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0284c7;">
                                {{ $th->invoiceNumber() }}
                            </td>
                            <td style="font-weight: 700;">{{ strtoupper($th->handover_by ?: ($th->employee->name ?? 'Kasir')) }}</td>
                            <td style="color: #0284c7; font-weight: 700;">{{ strtoupper($th->handoverRecipientName()) }}</td>
                            <td style="text-align: center;">
                                <span style="background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 3px; font-size: 10.5px; font-weight: 700;">
                                    tunai
                                </span>
                            </td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">
                                {{ number_format($thBruto, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #00a65a;">
                                {{ number_format($thNetto, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('presensi2.rekap', ['summary_id' => $th->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Serah Terima" class="btn-act-orange">
                                    🖨️
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="v2-table-empty">
                                kosong
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ==================== CARD 2: SIAP SETOR (CYAN HEADER) ==================== --}}
        <div class="v2-panel-box">
            <div class="v2-box-header-cyan">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>💵</span>
                    <span>Siap setor</span>
                </div>
                <div style="font-size: 11.5px; font-weight: 600; opacity: 0.95;">
                    Dana Kas Terkumpul Siap Disetor ke Bank / Kolektor
                </div>
            </div>

            <table class="v2-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Tanggal</th>
                        <th style="text-align: center;">shif</th>
                        <th>Invoice</th>
                        <th>Penyerah</th>
                        <th>Penerima</th>
                        <th style="text-align: center;">payment</th>
                        <th style="text-align: right;">Nilai</th>
                        <th style="text-align: center; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readyToDepositSummaries as $idx => $rtd)
                        @php
                            $rtdNilai = (float) $rtd->handoverAmount();
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                            <td style="font-family: 'JetBrains Mono', monospace;">
                                {{ $rtd->handover_at ? $rtd->handover_at->format('Y-m-d H:i:s') : ($rtd->work_date?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s')) }}
                            </td>
                            <td style="text-align: center; font-weight: 800; font-family: 'JetBrains Mono', monospace;">
                                {{ $rtd->shiftNumber() }}
                            </td>
                            <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0284c7;">
                                {{ $rtd->invoiceNumber() }}
                            </td>
                            <td style="font-weight: 700;">{{ strtoupper($rtd->handover_by ?: ($rtd->employee->name ?? 'Kasir')) }}</td>
                            <td style="color: #0284c7; font-weight: 700;">{{ strtoupper($rtd->handoverRecipientName()) }}</td>
                            <td style="text-align: center;">
                                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 3px; font-size: 10.5px; font-weight: 700;">
                                    tunai
                                </span>
                            </td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px;">
                                {{ number_format($rtdNilai, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('presensi2.rekap', ['summary_id' => $rtd->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Slip Rekap" class="btn-act-orange">
                                    🖨️
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="v2-table-empty">
                                Tidak ada antrean dana siap setor saat ini.
                            </td>
                        </tr>
                    @endforelse

                    {{-- TOTAL ROW EXACTLY AS IN REFERENCE --}}
                    @if($readyToDepositSummaries->isNotEmpty())
                        <tr class="v2-table-summary-row">
                            <td colspan="7" style="text-align: center; font-size: 13px; font-weight: 800; padding: 12px 14px;">
                                Total Nilai Rekap
                            </td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 800; color: #1e293b;">
                                {{ number_format($totalSiapSetor, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- BOTTOM BAR IN CARD 2 (PRINT HARDWARE & SETOR BUTTONS) --}}
            <div style="padding: 12px 16px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <button type="button" onclick="triggerPrintHardware()" class="btn-bottom-action-green">
                    <span>🖨️</span> Print Hardware
                </button>

                <button type="button" onclick="openModalSetorSemua()" class="btn-bottom-action-setor">
                    <span>Setor</span>
                </button>
            </div>
        </div>

        {{-- ==================== CARD 3: HISTORY SETOR (ORANGE HEADER) ==================== --}}
        <div class="v2-panel-box">
            <div class="v2-box-header-orange">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>📜</span>
                    <span>History setor</span>
                </div>
                <div style="font-size: 11.5px; font-weight: 600; opacity: 0.95;">
                    Arsip Setoran Kas Bank & Kolektor yang Telah Selesai
                </div>
            </div>

            <table class="v2-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th>Tanggal</th>
                        <th>Nomer</th>
                        <th>Outlet</th>
                        <th style="text-align: center;">Shif</th>
                        <th>Penyetor</th>
                        <th style="text-align: right;">Nilai</th>
                        <th style="text-align: center; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyDepositSummaries as $idx => $hs)
                        @php
                            $hsNilai = (float) ($hs->deposited_amount ?: $hs->handoverAmount());
                            $hsShiftNum = (int) $hs->shiftNumber();
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                            <td style="font-family: 'JetBrains Mono', monospace;">
                                {{ $hs->deposited_at ? $hs->deposited_at->format('Y-m-d H:i:s') : ($hs->work_date?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s')) }}
                            </td>
                            <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0284c7;">
                                {{ $hs->depositReceiptNumber() }}
                            </td>
                            <td style="font-weight: 700; color: #475569;">
                                {{ strtoupper($hs->branch->name ?? 'MAYA 1') }}
                            </td>
                            {{-- SHIFT BADGES EXACTLY AS IN THE REFERENCE IMAGE --}}
                            <td style="text-align: center;">
                                <div class="shift-badges-group">
                                    @for($s = 1; $s <= 4; $s++)
                                        <span class="badge-shift-sq {{ $hsShiftNum === $s ? 'active' : '' }}">
                                            {{ $s }}
                                        </span>
                                    @endfor
                                </div>
                            </td>
                            <td style="font-weight: 700;">
                                {{ strtoupper($hs->deposit_recipient ?: ($hs->employee->name ?? 'Kasir')) }}
                            </td>
                            <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13px;">
                                {{ number_format($hsNilai, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <a href="{{ route('presensi2.rekap', ['summary_id' => $hs->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Kwitansi" class="btn-act-orange">
                                        🖨️
                                    </a>
                                    <button type="button" onclick="openDetailSetorModal({{ $hs->id }})" title="Lihat Detail Bukti" class="btn-act-green">
                                        📄
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="v2-table-empty">
                                Belum ada arsip riwayat setoran kas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>

    {{-- MODAL 1: FORM SERAH TERIMA SHIFT BARU --}}
    <div id="modal-handover-baru" class="pos-modal-overlay">
        <div class="pos-modal-box">
            <div class="pos-modal-header" style="background-color: #0284c7;">
                <span>🤝 Form Serah Terima Kasir Shift</span>
                <button type="button" onclick="closeModalHandoverBaru()" style="background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" style="padding: 16px 20px;">
                @csrf
                <input type="hidden" name="summary_id" value="{{ $salesSummary?->id }}">
                <input type="hidden" name="action_type" value="handover">

                <div class="form-group">
                    <label class="form-label">Shift Berjalan</label>
                    <input type="text" class="form-control" readonly value="{{ $activeShift?->name ?? 'Shift 1' }} — {{ $currentEmployee->name }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Kasir Penerima Shift Berikutnya *</label>
                    <select name="handover_recipient" required class="form-control">
                        @foreach($branchEmployees as $emp)
                            @if($emp->id !== $currentEmployee->id)
                                <option value="{{ $emp->name }}">{{ $emp->name }}</option>
                            @endif
                        @endforeach
                        <option value="{{ $nextShiftLabel }}">{{ $nextShiftLabel }} (Otomatis)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal Uang Kas Fisik di Laci (Rp) *</label>
                    <input type="number" name="handover_amount" required min="0" step="100" class="form-control" style="font-family: 'JetBrains Mono', monospace; font-size: 15px; font-weight: 700; color: #0284c7;" value="{{ (int) $cashInDrawer }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Modal Kas Ditinggal di Laci (Rp)</label>
                    <input type="number" name="retained_drawer_cash" min="0" step="100" class="form-control" style="font-family: 'JetBrains Mono', monospace;" value="{{ $activeShift?->id == 4 ? 150000 : 100000 }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Serah Terima (Opsional)</label>
                    <input type="text" name="handover_notes" class="form-control" placeholder="Kondisi laci, titipan uang kembalian, dll...">
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px;">
                    <button type="button" onclick="closeModalHandoverBaru()" class="btn-nav-pill">Batal</button>
                    <button type="submit" class="btn-nav-pill primary">Simpan Serah Terima</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: EKSEKUSI SETOR KAS --}}
    <div id="modal-setor-kas" class="pos-modal-overlay">
        <div class="pos-modal-box">
            <div class="pos-modal-header" style="background-color: #f39c12;">
                <span>🏦 Form Setoran Kas ke Bank / Kolektor</span>
                <button type="button" onclick="closeModalSetorKas()" style="background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" style="padding: 16px 20px;">
                @csrf
                <input type="hidden" name="action_type" value="setor">
                <input type="hidden" name="summary_id" id="modal-setor-summary-id" value="{{ $readyToDepositSummaries->first()?->id }}">

                <div class="form-group">
                    <label class="form-label">Metode Setoran Kas *</label>
                    <select name="deposit_method" required class="form-control">
                        <option value="bank_transfer">Transfer Rekening Bank (BCA / Mandiri)</option>
                        <option value="cash_collector">Tunai ke Kolektor / SPV</option>
                        <option value="safe_dropbox">Brankas / Drop Box Outlet</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal Uang Disetor (Rp) *</label>
                    <input type="number" name="deposited_amount" id="modal-setor-amount" required min="0" step="100" class="form-control" style="font-family: 'JetBrains Mono', monospace; font-size: 16px; font-weight: 800; color: #b45309;" value="{{ (int) $totalSiapSetor }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Penerima / Nomor Rekening Tujuan *</label>
                    <input type="text" name="deposit_recipient" id="modal-setor-recipient" required class="form-control" placeholder="Contoh: BCA MayaNet Pusat / Kolektor Pak Budi" value="BCA MayaNet Pusat">
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Bukti Referensi</label>
                    <input type="text" name="deposit_notes" class="form-control" placeholder="No referensi transfer atau catatan...">
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px;">
                    <button type="button" onclick="closeModalSetorKas()" class="btn-nav-pill">Batal</button>
                    <button type="submit" class="btn-nav-pill success" style="background: #f39c12; border-color: #f39c12;">Simpan & Cetak Bukti Setoran</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // LIVE DIGITAL CLOCK
        function updateClock() {
            const clockEl = document.getElementById('live-clock');
            if (!clockEl) return;
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // MODAL HANDOVER
        function openModalHandoverBaru() {
            document.getElementById('modal-handover-baru').classList.add('active');
        }
        function closeModalHandoverBaru() {
            document.getElementById('modal-handover-baru').classList.remove('active');
        }

        // MODAL SETOR
        function openModalSetorSemua() {
            document.getElementById('modal-setor-kas').classList.add('active');
        }
        function closeModalSetorKas() {
            document.getElementById('modal-setor-kas').classList.remove('active');
        }

        function triggerPrintHardware() {
            window.open('{{ route("presensi2.rekap", ["auto_print" => 1, "print_type" => "hardware"]) }}', '_blank');
        }

        function openDetailSetorModal(id) {
            window.open('{{ url("presensi2/rekap") }}?summary_id=' + id + '&auto_print=1', '_blank');
        }
    </script>
    @include('presensi2.partials.modal_shift_history_and_settlement')
</body>
</html>

