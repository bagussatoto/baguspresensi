<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serah Terima Kas & Shift (Desktop) — Maya Graha Kencana</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

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
            --pos-blue-text: #2563eb;
            --pos-border: #cbd5e1;
            --pos-dark: #2c3e50;
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
            justify-content: center;
            padding: 12px 20px 10px 20px;
            background: #ffffff;
            border-bottom: 1px solid #d2d6de;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .pos-top-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 0.2px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pos-header-left {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pos-header-right {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-nav-pill {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 5px 11px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-nav-pill:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* ALERT STATUS BANNER */
        .status-alert-bar {
            margin: 12px 20px 0 20px;
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

        /* MAIN 3-COLUMN CONTAINER */
        .st-main-container {
            display: grid;
            grid-template-columns: 330px 1fr 360px;
            gap: 16px;
            padding: 14px 20px 20px 20px;
            flex: 1;
            align-items: start;
        }

        @media (max-width: 1200px) {
            .st-main-container {
                grid-template-columns: 1fr 1fr;
            }
            .st-main-container > .st-col-right {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .st-main-container {
                grid-template-columns: 1fr;
            }
        }

        /* BOX / CARD STYLING */
        .panel-box {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 14px;
        }

        .panel-box-header {
            padding: 9px 14px;
            font-weight: 700;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }

        .panel-box-header-green {
            background-color: var(--pos-green-light);
            color: var(--pos-green-text);
            border-bottom-color: #c3e6cb;
        }

        .panel-box-header-orange {
            background-color: #fef3c7;
            color: #92400e;
            border-bottom-color: #fde68a;
        }

        .panel-box-header-cyan {
            background-color: #e0f2fe;
            color: #0369a1;
            border-bottom-color: #bae6fd;
        }

        .panel-box-header-slate {
            background-color: #f8fafc;
            color: #334155;
            border-bottom-color: #e2e8f0;
        }

        .panel-box-body {
            padding: 14px;
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

        /* DENOMINATION TABLE */
        .table-denom {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .table-denom th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            padding: 7px 10px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
        }
        .table-denom td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table-denom tr:hover td {
            background: #f8fafc;
        }
        .input-denom {
            width: 75px;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            font-weight: 600;
            text-align: right;
            outline: none;
        }
        .input-denom:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        /* FORM ELEMENTS */
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
            letter-spacing: 0.3px;
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
            transition: border-color 0.15s ease;
        }
        .form-control:focus {
            border-color: #00a65a;
            box-shadow: 0 0 0 2px rgba(0, 166, 90, 0.15);
        }
        .form-control-money {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 14px;
        }

        /* BUTTONS */
        .btn-action-primary {
            width: 100%;
            background-color: var(--pos-green);
            color: #ffffff;
            border: none;
            padding: 11px 16px;
            font-size: 13.5px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0, 166, 90, 0.3);
            transition: background 0.15s ease;
        }
        .btn-action-primary:hover {
            background-color: var(--pos-green-dark);
        }

        .btn-action-secondary {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .btn-action-secondary:hover {
            background-color: #f1f5f9;
        }

        /* BOTTOM TABS */
        .tabs-nav {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid #cbd5e1;
            margin-bottom: 12px;
        }
        .tab-btn {
            padding: 8px 16px;
            font-size: 12.5px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.15s ease;
        }
        .tab-btn.active {
            color: #0284c7;
            border-bottom-color: #0284c7;
            background: #ffffff;
            border-radius: 4px 4px 0 0;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }

        /* TABLE GENERIC */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .table-data th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 8px 10px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        .table-data td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table-data tr:hover td {
            background: #f8fafc;
        }

        /* SHIFT BADGE */
        .badge-shift {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 11px;
            background: #0284c7;
            color: #ffffff;
        }

        /* MODAL */
        .pos-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .pos-modal-box {
            background: #ffffff;
            border-radius: 6px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .pos-modal-header {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            font-size: 14px;
            color: #ffffff;
        }

        /* PRINT STYLES FOR SUMMARY REPORT TABLE (REKAP FORMAT) */
        .print-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 9.5px;
            color: #000000;
            border: 1.5px solid #000000;
        }
        .print-grid-table th,
        .print-grid-table td {
            border: 1px solid #000000;
            padding: 2px 4px;
            color: #000000;
            vertical-align: middle;
        }
        .print-grid-table th {
            background: #f1f5f9;
            font-weight: 700;
        }
        .print-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 9.5px;
        }
        .print-meta-table td {
            padding: 1px 0;
            color: #000000;
            vertical-align: top;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #thermal-print-area, #thermal-print-area * {
                visibility: visible;
            }
            #thermal-print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                max-width: 100%;
                margin: 0 auto;
                padding: 4mm;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 9.5px;
                color: #000000;
                background: #ffffff;
            }
        }
    </style>
</head>
<body>

    @php
        $shiftIdStr = (string) ($activeShift?->id ?? '');
        $shiftNameUpper = strtoupper((string) ($activeShift?->name ?? ''));
        $isShift4 = in_array($shiftIdStr, ['4', 'SHIFT-4'], true) || str_contains($shiftNameUpper, 'SHIFT 4') || str_contains($shiftNameUpper, 'MALAM');

        $handoverAmountVal = (float) ($salesSummary?->handoverAmount() ?? $cashInDrawer);
        $retainedDrawerVal = (float) ($salesSummary?->retained_drawer_cash ?? ($isShift4 ? 150000 : 100000));
        $isAlreadyDeposited = $salesSummary?->hasBeenDeposited() ?? false;
        $currentShiftLoginStr = ($activeShift ? $activeShift->name : 'Shift Aktif') . ' — ' . $currentEmployee->name;
        $defaultDepositRecipient = $salesSummary?->handoverRecipientName() ?: $currentShiftLoginStr;
        $expectedCash = (float) ($systemExpectedCash ?? max(0.0, ($totalRevenue - ($nonCashAmount ?? 0)) + 100000));
        $diffVal = $handoverAmountVal + $retainedDrawerVal - $expectedCash;
    @endphp

    {{-- TOP DESKTOP HEADER --}}
    <header class="pos-top-header">
        <div class="pos-header-left">
            <a href="{{ route('pos') }}" class="btn-nav-pill" title="Kembali ke Layar Penjualan POS Desktop">
                <span>←</span> <strong>POS Desktop</strong>
            </a>
            <a href="{{ route('presensi2.rekap', ['summary_id' => $salesSummary?->id]) }}" class="btn-nav-pill" style="color: #0369a1; border-color: #bae6fd; background: #e0f2fe;" title="Buka Detail Rekapitulasi Lengkap">
                <span>📊</span> Rekap Pendapatan
            </a>
            <a href="{{ route('presensi2.serah-terima', array_merge(request()->query(), ['view' => 'mobile'])) }}" class="btn-nav-pill" title="Buka Mode Tampilan HP">
                <span>📱</span> Versi HP
            </a>
            <a href="{{ route('presensi2.stok') }}" class="btn-nav-pill" title="Cek Stok Fisik Shift">
                <span>📦</span> Cek Stok
            </a>
        </div>

        <h1 class="pos-top-title">
            <span>🤝</span> Serah Terima Kas & Shift POS
        </h1>

        <div class="pos-header-right">
            <span>Cabang: <strong>{{ strtoupper($currentEmployee->branch->name ?? 'MAYA GRAHA KENCANA') }}</strong></span>
            <span class="badge-shift" title="Shift Aktif">{{ $activeShift?->id ?? '1' }}</span>
            <span style="color: #00a65a; font-weight: 700;">{{ $currentEmployee->name }}</span>
            <span id="live-clock" style="font-family: monospace; font-weight: 700;">{{ date('H:i:s') }}</span>
        </div>
    </header>

    {{-- BILAH MODE SIMULASI & PENGUJIAN SHIFT KASIR (DESKTOP) --}}
    <div style="background: linear-gradient(90deg, #1e1b4b, #31104b); border-bottom: 1px solid rgba(168, 85, 247, 0.4); padding: 8px 16px; color: #fff;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="background: #7c3aed; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;">🧪 MODE SIMULASI POS</span>
                <span style="font-size: 12px; color: #e9d5ff;">Pilih Shift Pengujian:</span>
                <div style="display: inline-flex; gap: 4px; flex-wrap: wrap;">
                    @php
                        $currShiftCode = $activeShift?->code ?? ('SHIFT-' . ($activeShift?->id ?? 1));
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery(['shift' => 1]) }}" class="btn-nav-pill" style="padding: 3px 10px; font-size: 11px; {{ ($currShiftCode === 'SHIFT-1' || $activeShift?->id == 1) ? 'background: #10b981; color: #fff; font-weight: 800; border-color: #059669;' : 'background: rgba(255,255,255,0.08); color: #cbd5e1;' }}">
                        Shift 1 (07-15)
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['shift' => 2]) }}" class="btn-nav-pill" style="padding: 3px 10px; font-size: 11px; {{ ($currShiftCode === 'SHIFT-2' || $activeShift?->id == 2) ? 'background: #0284c7; color: #fff; font-weight: 800; border-color: #0369a1;' : 'background: rgba(255,255,255,0.08); color: #cbd5e1;' }}">
                        Shift 2 (15-22)
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['shift' => 3]) }}" class="btn-nav-pill" style="padding: 3px 10px; font-size: 11px; {{ ($currShiftCode === 'SHIFT-3' || $activeShift?->id == 3) ? 'background: #f59e0b; color: #fff; font-weight: 800; border-color: #d97706;' : 'background: rgba(255,255,255,0.08); color: #cbd5e1;' }}">
                        Shift 3 (17-24)
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['shift' => 4]) }}" class="btn-nav-pill" style="padding: 3px 10px; font-size: 11px; {{ ($currShiftCode === 'SHIFT-4' || $activeShift?->id == 4) ? 'background: #ec4899; color: #fff; font-weight: 800; border-color: #db2777;' : 'background: rgba(255,255,255,0.08); color: #cbd5e1;' }}">
                        Shift 4 (22-07 EOD)
                    </a>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <span style="font-size: 11.5px; color: #e2e8f0; font-weight: 600;">Skenario Kas Laci:</span>
                <button type="button" onclick="applyHandoverPreset('match')" class="btn-nav-pill" style="background: rgba(16, 185, 129, 0.2); border-color: #10b981; color: #34d399; font-size: 11px; padding: 3px 8px; font-weight: 700;" title="Simulasikan uang kas fisik pas dengan catatan sistem (Selisih Rp 0)">
                    🟢 Kas Pas (Cocok)
                </button>
                <button type="button" onclick="applyHandoverPreset('shortage')" class="btn-nav-pill" style="background: rgba(239, 68, 68, 0.2); border-color: #ef4444; color: #f87171; font-size: 11px; padding: 3px 8px; font-weight: 700;" title="Simulasikan kas laci kurang Rp 10.000">
                    🔴 Minus -Rp 10.000
                </button>
                <button type="button" onclick="applyHandoverPreset('surplus')" class="btn-nav-pill" style="background: rgba(59, 130, 246, 0.2); border-color: #3b82f6; color: #60a5fa; font-size: 11px; padding: 3px 8px; font-weight: 700;" title="Simulasikan kas laci lebih Rp 5.000">
                    🔵 Lebih +Rp 5.000
                </button>
                @if($isShift4)
                <button type="button" onclick="applyHandoverPreset('shift4')" class="btn-nav-pill" style="background: rgba(236, 72, 153, 0.2); border-color: #ec4899; color: #f472b6; font-size: 11px; padding: 3px 8px; font-weight: 700;" title="SOP Shift 4: Modal laci Rp 150.000 ditinggal untuk Shift 1 besok pagi">
                    🌙 Modal Laci Rp 150.000
                </button>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['action' => 'reset_handover']) }}" 
                   onclick="return confirm('Reset simulasi serah terima shift ini? Status akan dikembalikan menjadi terbuka (belum serah terima) agar form bersih untuk dicoba lagi.')" 
                   class="btn-nav-pill" 
                   style="background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); color: #f1f5f9; font-size: 11px; padding: 3px 9px;" 
                   title="Kembalikan form ke status awal sebelum serah terima">
                    🔄 Reset Simulasi
                </a>
            </div>
        </div>
    </div>

    {{-- ALERT STATUS KUNCI BILLING & PRINT --}}
    @if(!$isBillingLocked || !$isPrintLocked)
        <div class="status-alert-bar warning">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">⚠️</span>
                <div>
                    <strong>Peringatan Validasi Shift:</strong>
                    @if(!$isBillingLocked && !$isPrintLocked)
                        Data <strong>Billing Internet</strong> dan <strong>Print Kertas</strong> belum dikunci di POS Desktop. Disarankan kunci keduanya sebelum tutup kas agar angka omset terkunci valid.
                    @elseif(!$isBillingLocked)
                        Data <strong>Billing Internet</strong> belum dikunci di POS Desktop. Klik tombol Kunci Billing di POS Desktop.
                    @elseif(!$isPrintLocked)
                        Data <strong>Print Kertas</strong> belum dikunci di POS Desktop. Klik tombol Kunci Print di POS Desktop.
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ route('pos') }}" class="btn-nav-pill" style="background: #f59e0b; color: #fff; border-color: #d97706;">
                    🔒 Ke Layar POS untuk Kunci
                </a>
            </div>
        </div>
    @else
        <div class="status-alert-bar success">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">✅</span>
                <div>
                    <strong>Validasi Sempurna:</strong> Billing Internet Terkunci <strong>(Rp {{ number_format($lockedBillingAmount, 0, ',', '.') }})</strong> & Print Kertas Terkunci <strong>({{ $lockedPrintPages }} lbr / Rp {{ number_format($lockedPrintAmount, 0, ',', '.') }})</strong>. Siap hitung kas fisik dan serah terima ke kasir shift berikutnya.
                </div>
            </div>
            <div>
                <span style="font-size: 11px; color: #15803d; font-weight: 700; background: #dcfce7; padding: 4px 8px; border-radius: 4px;">
                    STATUS: SIAP TUTUP SHIFT
                </span>
            </div>
        </div>
    @endif

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div style="margin: 12px 20px 0 20px; padding: 10px 16px; background: #dcfce7; border: 1px solid #86efac; color: #166534; border-radius: 4px; font-weight: 600;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="margin: 12px 20px 0 20px; padding: 10px 16px; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 4px; font-weight: 600;">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- MAIN 3-COLUMN LAYOUT --}}
    <main class="st-main-container">

        {{-- KOLOM 1: RINGKASAN FINANSIAL TERKUNCI (SISTEM) --}}
        <section class="st-col-left">
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-orange">
                    <span>📊 Omset & Kas Target Sistem</span>
                    <span style="font-size: 11px; font-family: monospace;">Shift {{ $activeShift?->id ?? '1' }}</span>
                </div>
                <div class="panel-box-body">
                    <div class="stat-row">
                        <span class="stat-label"><span>🪙</span> Modal Awal Laci</span>
                        <span class="stat-value" id="disp-opening-cash">Rp {{ number_format($openingCash ?? 100000, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <span>🌐</span> Billing Internet
                            @if($isBillingLocked)
                                <small style="color: #00a65a; font-weight: 700;">(Terkunci)</small>
                            @else
                                <small style="color: #dd4b39; font-weight: 700;">(Belum Kunci)</small>
                            @endif
                        </span>
                        <span class="stat-value" style="color: #b45309;">Rp {{ number_format($lockedBillingAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <span>🖨️</span> Print Kertas
                            @if($isPrintLocked)
                                <small style="color: #00a65a; font-weight: 700;">({{ $lockedPrintPages }} lbr)</small>
                            @else
                                <small style="color: #dd4b39; font-weight: 700;">(Belum Kunci)</small>
                            @endif
                        </span>
                        <span class="stat-value" style="color: #0284c7;">Rp {{ number_format($lockedPrintAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label"><span>☕</span> Penjualan F&B / POS</span>
                        <span class="stat-value" style="color: #059669;">Rp {{ number_format($totalFnbSales, 0, ',', '.') }}</span>
                    </div>
                    @if(($nonCashAmount ?? 0) > 0)
                    <div class="stat-row" style="background: #fdf2f8; padding: 6px 10px; border-radius: 4px; margin: 4px 0;">
                        <span class="stat-label" style="color: #9d174d; font-size: 11.5px;"><span>📱</span> Non-Tunai / QRIS (Masuk Rekening)</span>
                        <span class="stat-value" style="color: #be185d; font-weight: 700;">- Rp {{ number_format($nonCashAmount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="stat-row" style="background: #f8fafc; padding: 10px; margin: 6px -14px -6px -14px; border-top: 2px solid #e2e8f0;">
                        <div>
                            <strong style="color: #1e293b; font-size: 12.5px; display: block;">Target Fisik Kas Tunai (Laci)</strong>
                            <small style="color: #64748b;">Modal Awal + Omset Tunai (Cash Only)</small>
                        </div>
                        <span class="stat-value" style="font-size: 16px; color: #0284c7;" id="disp-expected-cash">
                            Rp {{ number_format($expectedCash, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- BOX RINGKASAN AKSI: CETAK REKAP & STRUK SERAH TERIMA --}}
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-cyan">
                    <span>🖨️ Cetak Struk & Rekap Kasir</span>
                    <span style="font-size: 11px;">Shift {{ $activeShift?->id ?? '1' }}</span>
                </div>
                <div class="panel-box-body">
                    <div style="font-size: 11.5px; color: #475569; margin-bottom: 12px; line-height: 1.4;">
                        Setelah serah terima uang fisik kasir selesai, Anda dapat langsung mencetak bukti serah terima kasir atau membuka tabel rekapitulasi penjualan (Z-Report).
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <button type="button" onclick="previewThermalReceipt()" class="btn-action-primary" style="justify-content: center; background: #00a65a; border-color: #008d4c; color: #ffffff; padding: 8px 12px; font-weight: 700; width: 100%;">
                            <span>🧾</span> Cetak Struk Serah Terima Kas
                        </button>
                        <a href="{{ route('presensi2.rekap', ['summary_id' => $salesSummary?->id]) }}" class="btn-action-secondary" style="justify-content: center; background: #e0f2fe; color: #0369a1; border-color: #bae6fd; text-decoration: none; padding: 8px 12px; font-weight: 700; width: 100%;">
                            <span>📊</span> Buka Tabel Rekap Penjualan (Z-Report)
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- KOLOM 2: PENGHITUNG PECAHAN FISIK KAS LACI (DENOMINASI) --}}
        <section class="st-col-mid">
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-green">
                    <span>💵 Hitung Fisik Kas Laci (Tunai / Cash Only)</span>
                    <span style="font-size: 11px; font-weight: 600; color: #3c763d;">Kalkulator Laci Kasir</span>
                </div>
                <div class="panel-box-body" style="padding: 10px 14px;">
                    <table class="table-denom">
                        <thead>
                            <tr>
                                <th>Pecahan</th>
                                <th style="text-align: center; width: 90px;">Jumlah</th>
                                <th style="text-align: right;">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $denomsList = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
                                $savedDenoms = $cashDenominations ?? [];
                            @endphp
                            @foreach($denomsList as $nominal)
                                @php
                                    $savedQty = $savedDenoms[$nominal] ?? 0;
                                    $subtotal = $nominal * $savedQty;
                                @endphp
                                <tr>
                                    <td>
                                        <strong style="color: #1e293b;">Rp {{ number_format($nominal, 0, ',', '.') }}</strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" min="0" step="1"
                                               class="input-denom denom-qty-input"
                                               data-nominal="{{ $nominal }}"
                                               value="{{ $savedQty > 0 ? $savedQty : '' }}"
                                               placeholder="0"
                                               oninput="calcDenominations()">
                                    </td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #334155;" id="subtotal-{{ $nominal }}">
                                        {{ number_format($subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>
                                    <strong style="color: #1e293b;">Koin / Logam</strong>
                                </td>
                                <td colspan="2" style="text-align: right;">
                                    <input type="number" min="0" step="100"
                                           class="input-denom" style="width: 130px;"
                                           id="denom-coin-input"
                                           value="{{ $savedDenoms['coin'] ?? '' }}"
                                           placeholder="Rp Koin"
                                           oninput="calcDenominations()">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- REKAPITULASI FISIK VS TARGET SISTEM --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 12px; margin-top: 10px;">
                        <div class="stat-row" style="padding-top: 0;">
                            <span class="stat-label"><strong>Total Fisik Laci Terhitung</strong></span>
                            <span class="stat-value" style="font-size: 15px; color: #00a65a;" id="disp-calc-total">
                                Rp {{ number_format($handoverAmountVal + $retainedDrawerVal, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Target Kas Tunai (Cash Only)</span>
                            <span class="stat-value" style="color: #64748b;">
                                Rp {{ number_format($expectedCash, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="stat-row" style="border-bottom: none; padding-bottom: 0;">
                            <span class="stat-label"><strong>Status Selisih Kas</strong></span>
                            <div id="disp-diff-badge">
                                @if($diffVal == 0)
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px;">
                                        ✓ PAS (Sesuai)
                                    </span>
                                @elseif($diffVal > 0)
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px;">
                                        + LEBIH Rp {{ number_format($diffVal, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px;">
                                        - MINUS Rp {{ number_format(abs($diffVal), 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 10px; display: flex; gap: 8px;">
                        <button type="button" onclick="applyCalcToForm()" class="btn-action-secondary" style="flex: 1; justify-content: center; background: #f0fdf4; color: #166534; border-color: #86efac; font-weight: 700;">
                            <span>📋</span> Salin Hasil Hitung ke Form Serah Terima
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- KOLOM 3: FORMULIR SERAH TERIMA & PENYERAHAN SHIFT --}}
        <section class="st-col-right">
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-slate">
                    <span>📝 Form Serah Terima Shift</span>
                    <span style="font-size: 11px; color: #64748b;">Kasir Aktif</span>
                </div>
                <div class="panel-box-body">
                    @if($isShift4)
                        <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(6, 95, 70, 0.2)); border: 1.5px solid #10b981; border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; display: flex; align-items: flex-start; gap: 10px;">
                            <span style="font-size: 22px;">🌙</span>
                            <div>
                                <strong style="color: #059669; font-size: 13px;">SHIFT 4: PENUTUP HARI & SETTLEMENT EOD</strong>
                                <div style="font-size: 11.5px; color: #334155; margin-top: 2px; line-height: 1.4;">
                                    Sesuai SOP Akhir Hari MGK: Sisakan <strong>Modal Laci Kasir Rp 150.000</strong> untuk Kasir Shift 1 besok pagi. Seluruh sisa uang omset disetorkan ke <strong>Amplop Brankas (Safe Dropbox)</strong> atau <strong>Bank</strong>.
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" id="form-serah-terima" enctype="multipart/form-data" onsubmit="return confirmSaveHandover()">
                        @csrf
                        <input type="hidden" name="summary_id" value="{{ $salesSummary?->id }}">
                        <input type="hidden" name="action_type" value="handover">
                        <input type="hidden" name="cash_denominations" id="form-cash-denominations" value="{{ json_encode($cashDenominations ?? []) }}">
                        <input type="hidden" name="blind_cash_count" id="form-blind-cash-count" value="{{ (int) ($handoverAmountVal + $retainedDrawerVal) }}">

                        {{-- TOTAL FISIK KAS LACI --}}
                        <div class="form-group">
                            <label class="form-label">Total Uang Fisik di Laci (Cash Only) (Rp) *</label>
                            <input type="number" id="input-total-physical" required min="0" step="100"
                                   class="form-control form-control-money"
                                   style="background: #f8fafc; border-color: #94a3b8; color: #0f172a;"
                                   value="{{ (int) ($handoverAmountVal + $retainedDrawerVal) }}"
                                   oninput="updateHandoverDistribution()">
                            <small style="color: #64748b; font-size: 11px;">Murni uang cash fisik di laci kasir (QRIS tidak dihitung karena langsung masuk rekening)</small>
                        </div>

                        {{-- MODAL LACI DITINGGAL --}}
                        <div class="form-group">
                            <label class="form-label">Modal Laci Ditinggal (Kembalian) (Rp) *</label>
                            <input type="number" name="retained_drawer_cash" id="input-retained-cash" required min="0" step="100"
                                   class="form-control form-control-money"
                                   style="color: #0284c7;"
                                   value="{{ (int) $retainedDrawerVal }}"
                                   oninput="updateHandoverDistribution()">
                            <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                                <button type="button" onclick="setRetainedCashPreset(150000)" style="font-size: 11px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; border-radius: 4px; padding: 3px 8px; cursor: pointer; font-weight: 700;">
                                    Rp 150.000 (SOP Shift 1)
                                </button>
                                <button type="button" onclick="setRetainedCashPreset(200000)" style="font-size: 11px; background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 8px; cursor: pointer;">
                                    Rp 200.000
                                </button>
                                <button type="button" onclick="setRetainedCashPreset(100000)" style="font-size: 11px; background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 8px; cursor: pointer;">
                                    Rp 100.000
                                </button>
                            </div>
                            <small style="color: #64748b; font-size: 11px;">Uang receh yang ditinggal di laci untuk modal kasir selanjutnya</small>
                        </div>

                        {{-- KAS BERSIH DISERAHKAN (SIAP SETOR) --}}
                        <div class="form-group">
                            <label class="form-label" style="color: #008d4c;">Uang Bersih Tunai Diserahterimakan (Cash Only) (Rp) *</label>
                            <input type="number" name="deposited_amount" id="input-handover-amount" required min="0" step="100"
                                   class="form-control form-control-money"
                                   style="border-color: #00a65a; background: #f0fdf4; color: #008d4c; font-size: 16px;"
                                   value="{{ (int) $handoverAmountVal }}">
                            <small style="color: #008d4c; font-size: 11px;">Nominal tunai yang diserahkan ke kasir berikutnya / siap disetor</small>
                        </div>

                        {{-- METODE PENYETORAN KAS (CASH ONLY) --}}
                        <div class="form-group">
                            <label class="form-label">Metode Serah Terima Kas *</label>
                            <input type="hidden" name="deposit_method" value="next_shift_handover">
                            <div style="padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 12px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px;">🤝</span>
                                <span>Serah Terima Fisik Kas ke Kasir Selanjutnya (Cash Only)</span>
                            </div>
                            <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">Murni estafet fisik uang cash laci kasir secara tatap muka langsung.</small>
                        </div>

                        {{-- KASIR / SHIFT PENERIMA (SESUAI JADWAL LOGIN SHIFT KARYAWAN SAAT INI) --}}
                        <div class="form-group">
                            <label class="form-label">Shift / Kasir Penerima *</label>
                            <input type="hidden" name="deposit_recipient" id="input-recipient" value="{{ $currentShiftLoginStr }}">
                            <div style="padding: 10px 12px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; font-size: 13px; font-weight: 700; color: #0369a1; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">👤</span>
                                <span>{{ $currentShiftLoginStr }}</span>
                            </div>
                            <small style="color: #64748b; font-size: 11px; margin-top: 3px; display: block;">Otomatis sesuai nama kasir &amp; jadwal shift yang login saat ini</small>
                        </div>

                        {{-- CATATAN SHIFT --}}
                        <div class="form-group">
                            <label class="form-label">Catatan Serah Terima (Opsional)</label>
                            <input type="text" name="deposit_notes" class="form-control"
                                   placeholder="Contoh: Stok rokok aman, printer baru ganti kertas"
                                   value="{{ $salesSummary?->handover_notes ?? $salesSummary?->deposit_notes ?? '' }}">
                        </div>

                        {{-- HIDDEN INPUT SOURCE DESKTOP --}}
                        <input type="hidden" name="source" value="desktop">

                        {{-- AUTO CLOCK OUT CHECKBOX --}}
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-bottom: 14px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; font-size: 12px; color: #1e293b;">
                                <input type="checkbox" name="auto_clock_out" value="1" checked style="width: 17px; height: 17px; accent-color: #00a65a; cursor: pointer;">
                                <span><strong>Selesai Serah Terima</strong>: Otomatis lakukan <strong>Presensi Pulang (Clock Out)</strong> &amp; tetap di halaman ini untuk cek rekap</span>
                            </label>
                        </div>

                        {{-- TOMBOL SUBMIT --}}
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <button type="submit" class="btn-action-primary">
                                <span>🤝</span> Simpan Serah Terima Shift
                            </button>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                                <button type="button" onclick="previewThermalReceipt()" class="btn-action-secondary" style="justify-content: center;">
                                    <span>🖨️</span> Cetak Slip (58/80mm)
                                </button>
                                <button type="button" onclick="openModalSetorKas({{ $salesSummary?->id ?? 'null' }}, {{ (int) $handoverAmountVal }})" class="btn-action-secondary" style="justify-content: center; background: #fffbeb; color: #b45309; border-color: #fde68a;">
                                    <span>🏦</span> Setor ke Bank
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    {{-- BOTTOM TABS: RIWAYAT SERAH TERIMA, SIAP SETOR, HISTORY SETOR, & STOK --}}
    <section style="padding: 0 20px 24px 20px;">
        <div class="panel-box">
            <div style="padding: 12px 16px 0 16px;">
                <div class="tabs-nav">
                    <button type="button" class="tab-btn active" onclick="switchTab('tab-handovers', this)">
                        <span>🤝</span> Riwayat Estafet Kasir ({{ $handoverHistorySummaries->count() }})
                    </button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-stock', this)">
                        <span>📦</span> Audit Sisa Stok Fisik
                    </button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-fnb-breakdown', this)">
                        <span>☕</span> Rincian Penjualan POS ({{ $fnbItemSummary->count() ?? 0 }} Menu)
                    </button>
                </div>
            </div>

            <div class="panel-box-body" style="padding: 0 16px 16px 16px;">
                {{-- TAB 1: RIWAYAT ESTAFET SERAH TERIMA KASIR (CHAIN OF CUSTODY) --}}
                <div id="tab-handovers" class="tab-content active">
                    <table class="table-data">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Tanggal &amp; Jam WIB</th>
                                <th>Shift</th>
                                <th>👤 Kasir Penyerah</th>
                                <th>🤝 Kasir Penerima</th>
                                <th style="text-align: right;">💵 Kas Diserahkan (Cash)</th>
                                <th style="text-align: right;">🪙 Modal Ditinggal</th>
                                <th style="text-align: center;">Status Kas</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($handoverHistorySummaries as $hs)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $loop->iteration }}</td>
                                    <td style="font-family: monospace;">
                                        {{ $hs->handover_at ? $hs->handover_at->format('d-m-Y H:i') : ($hs->work_date?->format('d-m-Y') ?? '-') }}
                                    </td>
                                    <td><span class="badge-shift">{{ $hs->shiftNumber() }}</span></td>
                                    <td><strong>{{ strtoupper($hs->handover_by ?: ($hs->employee->name ?? 'Kasir')) }}</strong></td>
                                    <td style="color: #0284c7;"><strong>{{ $hs->handoverRecipientName() }}</strong></td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #00a65a;">
                                        Rp {{ number_format((float)$hs->handoverAmount(), 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; color: #0284c7;">
                                        Rp {{ number_format((float)($hs->retained_drawer_cash ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if($hs->cash_difference == 0)
                                            <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">✓ Pas</span>
                                        @elseif($hs->cash_difference > 0)
                                            <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">+{{ number_format($hs->cash_difference, 0, ',', '.') }}</span>
                                        @else
                                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">-{{ number_format(abs($hs->cash_difference), 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('presensi2.rekap', ['summary_id' => $hs->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Slip Rekap" class="btn-nav-pill" style="padding: 3px 8px; font-size: 11px;">
                                            🖨️ Struk
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        Belum ada rekaman riwayat serah terima kasir shift yang tersimpan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB 2: ANTREAN SIAP SETOR --}}
                <div id="tab-ready" class="tab-content">
                    <table class="table-data">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Kasir Penyerah</th>
                                <th>Kasir Penerima</th>
                                <th style="text-align: right;">Nilai Kas Siap Setor</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($readyToDepositSummaries as $rtd)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $loop->iteration }}</td>
                                    <td style="font-family: monospace;">{{ $rtd->work_date?->format('d-m-Y') ?? date('d-m-Y') }}</td>
                                    <td><span class="badge-shift">{{ $rtd->shiftNumber() }}</span></td>
                                    <td><strong>{{ strtoupper($rtd->employee->name ?? 'Kasir') }}</strong></td>
                                    <td style="color: #0284c7;"><strong>{{ $rtd->handoverRecipientName() }}</strong></td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0284c7;">
                                        Rp {{ number_format((float)$rtd->handoverAmount(), 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" onclick="openModalSetorKas({{ $rtd->id }}, {{ (int) $rtd->handoverAmount() }})" class="btn-nav-pill" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd; padding: 3px 8px; font-size: 11px;">
                                            🏦 Setor
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        Tidak ada antrean dana siap setor saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB 3: RIWAYAT SETORAN BANK --}}
                <div id="tab-history" class="tab-content">
                    <table class="table-data">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Tanggal & Waktu Setor</th>
                                <th>No Kwitansi Setor</th>
                                <th>Metode Setoran</th>
                                <th>Penerima / Tujuan</th>
                                <th style="text-align: right;">Nominal Disetor</th>
                                <th style="text-align: center;">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historyDepositSummaries as $hs)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $loop->iteration }}</td>
                                    <td style="font-family: monospace;">
                                        {{ $hs->deposited_at ? $hs->deposited_at->format('d-m-Y H:i') : ($hs->work_date?->format('d-m-Y') ?? '-') }}
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                                        {{ $hs->depositReceiptNumber() }}
                                    </td>
                                    <td><span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 11px;">{{ $hs->depositMethodLabel() }}</span></td>
                                    <td><strong>{{ $hs->deposit_recipient ?: 'BCA MayaNet' }}</strong></td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #b45309;">
                                        Rp {{ number_format((float)$hs->deposited_amount, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('presensi2.rekap', ['summary_id' => $hs->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Setor" class="btn-nav-pill" style="padding: 3px 8px; font-size: 11px;">
                                            🖨️ Cetak
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        Belum ada riwayat setoran yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB 4: AUDIT SISA STOK FISIK --}}
                <div id="tab-stock" class="tab-content">
                    <table class="table-data">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Produk / Barang</th>
                                <th>Kategori</th>
                                <th style="text-align: right;">Stok Awal</th>
                                <th style="text-align: right;">Terjual POS</th>
                                <th style="text-align: right;">Sisa Sistem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockComparisons as $stk)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $loop->iteration }}</td>
                                    <td><strong>{{ $stk['product_name'] ?? '-' }}</strong></td>
                                    <td><span style="color: #64748b; font-size: 11px;">{{ $stk['category'] ?? 'Produk' }}</span></td>
                                    <td style="text-align: right; font-family: monospace;">{{ $stk['opening_stock'] ?? 0 }}</td>
                                    <td style="text-align: right; font-family: monospace; color: #dd4b39; font-weight: 700;">-{{ $stk['sold_qty'] ?? 0 }}</td>
                                    <td style="text-align: right; font-family: monospace; color: #00a65a; font-weight: 700;">{{ $stk['calculated_stock'] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        Data audit stok fisik tidak tersedia untuk shift ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB 5: RINCIAN PENJUALAN PRODUK POS & F&B SHIFT --}}
                <div id="tab-fnb-breakdown" class="tab-content">
                    <table class="table-data">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Nama Menu / Produk F&B</th>
                                <th style="text-align: center; width: 100px;">Qty Terjual</th>
                                <th style="text-align: right; width: 140px;">Harga Satuan (Rp)</th>
                                <th style="text-align: right; width: 160px;">Total Omset (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fnbItemSummary ?? [] as $fnbItem)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $loop->iteration }}</td>
                                    <td><strong>{{ $fnbItem->item_name }}</strong></td>
                                    <td style="text-align: center; font-weight: 700; font-family: 'JetBrains Mono', monospace; color: #0284c7;">
                                        {{ $fnbItem->total_qty }}
                                    </td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">
                                        Rp {{ number_format((float) $fnbItem->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #00a65a;">
                                        Rp {{ number_format((float) $fnbItem->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        Belum ada rekaman penjualan produk F&B / POS pada shift ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL SETOR KAS (BANK / KOLEKTOR / BRANKAS) --}}
    <div id="modal-setor-kas" class="pos-modal-overlay">
        <div class="pos-modal-box">
            <div class="pos-modal-header" style="background-color: var(--pos-orange);">
                <span>🏦 Setor Kas Shift (Bank / Brankas / Kolektor)</span>
                <button type="button" onclick="closeModalSetorKas()" style="background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer; font-weight: 700;">✕</button>
            </div>
            <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" style="padding: 18px 20px;">
                @csrf
                <input type="hidden" name="action_type" value="setor">
                <input type="hidden" name="summary_id" id="modal-setor-summary-id" value="{{ $salesSummary?->id }}">

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
                    <input type="number" name="deposited_amount" id="modal-setor-amount" required min="0" step="100"
                           class="form-control form-control-money" style="color: #b45309;"
                           value="{{ (int) $handoverAmountVal }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Penerima / Nomor Rekening Tujuan *</label>
                    <input type="text" name="deposit_recipient" id="modal-setor-recipient" required
                           class="form-control"
                           placeholder="Contoh: BCA MayaNet 12345678 / Kolektor Pak Budi"
                           value="BCA MayaNet Pusat">
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Bukti Referensi (Opsional)</label>
                    <input type="text" name="deposit_notes" class="form-control" placeholder="No referensi transfer atau catatan serah setor...">
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px;">
                    <button type="button" onclick="closeModalSetorKas()" class="btn-action-secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn-action-primary" style="width: auto; background-color: var(--pos-orange);">
                        🏦 Simpan Setoran Kas
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL PREVIEW PRINT THERMAL (FORMAT TABEL SESUAI DATA NAVICAT REKAP) --}}
    <div id="modal-thermal-preview" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 460px;">
            <div class="pos-modal-header" style="background-color: #1e293b;">
                <span>🖨️ Bukti Serah Terima Kasir (Tabel Summary)</span>
                <button type="button" onclick="closeThermalModal()" style="background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 16px; background: #f8fafc; max-height: 520px; overflow-y: auto;">
                <div id="thermal-print-area" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 12px 14px; font-family: Arial, Helvetica, sans-serif; font-size: 9.5px; line-height: 1.3; color: #000000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    @php
                        $rekapCode = \Carbon\Carbon::parse($workDate)->format('Ymd')
                            . '-B' . ($currentEmployee->branch_id ?? 1)
                            . '-S' . ($activeShift?->id ?? 1)
                            . '-' . ($salesSummary?->id ?? 18);
                    @endphp

                    {{-- JUDUL LAPORAN --}}
                    <div style="font-size: 13px; font-weight: 800; text-align: left; margin-bottom: 6px; letter-spacing: 0.02em; color: #000;">
                        Laporan Summary Serah Terima Shift
                    </div>

                    {{-- 1. IDENTITAS REKAP & OUTLET --}}
                    <div style="border: 1px solid #cbd5e1; padding: 5px 7px; margin-bottom: 6px; background: #fff;">
                        <table class="print-meta-table">
                            <tbody>
                                <tr>
                                    <td style="width: 82px;">Invoice</td>
                                    <td style="width: 8px;">:</td>
                                    <td><strong>{{ $rekapCode }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Outlet</td>
                                    <td>:</td>
                                    <td><strong>{{ strtoupper($currentEmployee->branch->name ?? 'MAYA GRAHA KENCANA') }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Tanggal Laporan</td>
                                    <td>:</td>
                                    <td><strong>{{ \Carbon\Carbon::parse($workDate)->format('d-m-Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Shift Operasional</td>
                                    <td>:</td>
                                    <td><strong>{{ $activeShift?->name ?? 'Shift Aktif' }} ({{ substr($activeShift?->start_time ?? '00:00', 0, 5) }} - {{ substr($activeShift?->end_time ?? '00:00', 0, 5) }})</strong></td>
                                </tr>
                                <tr>
                                    <td>Kasir Penyerah</td>
                                    <td>:</td>
                                    <td><strong>{{ $currentEmployee->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Kasir Penerima</td>
                                    <td>:</td>
                                    <td><strong><span id="slip-recipient">{{ $defaultDepositRecipient }}</span></strong></td>
                                </tr>
                                <tr>
                                    <td>Waktu Dicetak</td>
                                    <td>:</td>
                                    <td><strong>{{ now()->format('d-m-Y H:i') }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- 2. RINGKASAN KPI --}}
                    <div style="font-weight: 700; margin: 6px 0 2px 0;">Ringkasan KPI</div>
                    <table class="print-grid-table">
                        <thead>
                            <tr>
                                <th style="text-align: left; width: 65%;">Ringkasan</th>
                                <th style="text-align: right; width: 35%;">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Total Shift Operasional</td>
                                <td style="text-align: right; font-weight: 700;">4 Shift</td>
                            </tr>
                            <tr>
                                <td>Total Item Terjual</td>
                                <td style="text-align: right; font-weight: 700;">{{ $fnbItemSummary ? $fnbItemSummary->sum('total_qty') : 0 }} item</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- 3. RINGKASAN PEMBAYARAN --}}
                    <div style="font-weight: 700; margin: 6px 0 2px 0;">Ringkasan Pembayaran</div>
                    <table class="print-grid-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Pembayaran</th>
                                <th style="text-align: right; width: 45%;">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>TUNAI (CASH)</td>
                                <td style="text-align: right; font-weight: 700;">Rp {{ number_format(max(0, $totalRevenue - ($nonCashAmount ?? 0)), 0, ',', '.') }}</td>
                            </tr>
                            @if(($nonCashAmount ?? 0) > 0)
                            <tr>
                                <td>NON-TUNAI / QRIS (Masuk Rekening)</td>
                                <td style="text-align: right; font-weight: 700;">Rp {{ number_format($nonCashAmount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr style="background: #f8fafc; font-weight: 800;">
                                <td>TOTAL OMSET KASIR</td>
                                <td style="text-align: right;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- 4. RINCIAN PENDAPATAN & SERAH TERIMA KAS FISIK LACI --}}
                    <div style="font-weight: 700; margin: 6px 0 2px 0;">Rekapitulasi Kas & Estafet Fisik Laci</div>
                    <table class="print-grid-table">
                        <tbody>
                            <tr>
                                <td>Billing Internet</td>
                                <td style="text-align: right;">Rp {{ number_format($lockedBillingAmount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Print Kertas ({{ $lockedPrintPages }} lbr)</td>
                                <td style="text-align: right;">Rp {{ number_format($lockedPrintAmount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Penjualan POS / F&B</td>
                                <td style="text-align: right;">Rp {{ number_format($totalFnbSales, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Modal Awal Kas Laci</td>
                                <td style="text-align: right;">Rp {{ number_format($openingCash ?? 100000, 0, ',', '.') }}</td>
                            </tr>
                            <tr style="background: #f1f5f9; font-weight: 700;">
                                <td>Target Fisik Kas Tunai Laci (Cash Only)</td>
                                <td style="text-align: right;">Rp {{ number_format($expectedCash, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Total Fisik Uang di Laci</td>
                                <td style="text-align: right; font-weight: 700;"><span id="slip-physical">Rp {{ number_format($handoverAmountVal + $retainedDrawerVal, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td>Modal Laci Ditinggal Kasir</td>
                                <td style="text-align: right;"><span id="slip-retained">Rp {{ number_format($retainedDrawerVal, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr style="background: #ecfdf5; font-weight: 800; border-top: 1.5px solid #000;">
                                <td>KAS TUNAI DISERAHKAN KE PENERIMA</td>
                                <td style="text-align: right; color: #065f46;"><span id="slip-handover">Rp {{ number_format($handoverAmountVal, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td>Status Kas Fisik Laci</td>
                                <td style="text-align: right; font-weight: 700;"><span id="slip-diff">{{ $diffVal >= 0 ? '+Rp '.number_format($diffVal, 0, ',', '.') : '-Rp '.number_format(abs($diffVal), 0, ',', '.') }}</span></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- 5. RINCIAN MENU POS TERJUAL (JIKA ADA DATA DI DATABASE NAVICAT) --}}
                    @if(isset($fnbItemSummary) && $fnbItemSummary->isNotEmpty())
                    <div style="font-weight: 700; margin: 6px 0 2px 0;">Rincian Menu POS Terjual</div>
                    <table class="print-grid-table">
                        <thead>
                            <tr>
                                <th style="width: 24px; text-align: center;">No</th>
                                <th style="text-align: left;">Nama Produk</th>
                                <th style="width: 32px; text-align: center;">Qty</th>
                                <th style="width: 58px; text-align: right;">Harga</th>
                                <th style="width: 65px; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fnbItemSummary as $idx => $fnbItem)
                            <tr>
                                <td style="text-align: center;">{{ $idx + 1 }}</td>
                                <td>{{ $fnbItem->item_name }}</td>
                                <td style="text-align: center;">{{ $fnbItem->total_qty }}</td>
                                <td style="text-align: right;">{{ number_format((float) $fnbItem->unit_price, 0, ',', '.') }}</td>
                                <td style="text-align: right; font-weight: 700;">{{ number_format((float) $fnbItem->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    {{-- 6. TANDA TANGAN ESTAFET SHIFT --}}
                    <table style="width: 100%; margin-top: 10px; border-collapse: collapse; text-align: center; font-size: 9.5px;">
                        <tbody>
                            <tr>
                                <td style="width: 50%; padding-bottom: 35px;">
                                    Yang Menyerahkan,
                                </td>
                                <td style="width: 50%; padding-bottom: 35px;">
                                    Penerima Shift,
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>( {{ $currentEmployee->name }} )</strong>
                                </td>
                                <td>
                                    <strong>( <span id="slip-sign-recipient">{{ $defaultDepositRecipient }}</span> )</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="padding: 12px 16px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeThermalModal()" class="btn-action-secondary">Tutup</button>
                <button type="button" onclick="window.print()" class="btn-action-primary" style="width: auto;">
                    🖨️ Cetak Tabel Summary
                </button>
            </div>
        </div>
                </button>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        var expectedCashValue = {{ (float) $expectedCash }};

        // 1. Live Clock
        setInterval(function() {
            var now = new Date();
            var hours = String(now.getHours()).padStart(2, '0');
            var mins = String(now.getMinutes()).padStart(2, '0');
            var secs = String(now.getSeconds()).padStart(2, '0');
            var el = document.getElementById('live-clock');
            if (el) el.innerText = hours + ':' + mins + ':' + secs;
        }, 1000);

        // 2. Denomination Calculator
        function calcDenominations() {
            var denoms = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
            var total = 0;
            var denomsObj = {};

            denoms.forEach(function(nom) {
                var input = document.querySelector('.denom-qty-input[data-nominal="' + nom + '"]');
                var qty = input ? parseInt(input.value) || 0 : 0;
                var subtotal = nom * qty;
                total += subtotal;
                denomsObj[nom] = qty;

                var subtotalEl = document.getElementById('subtotal-' + nom);
                if (subtotalEl) {
                    subtotalEl.innerText = formatNumber(subtotal);
                }
            });

            var coinInput = document.getElementById('denom-coin-input');
            var coinVal = coinInput ? parseInt(coinInput.value) || 0 : 0;
            total += coinVal;
            denomsObj['coin'] = coinVal;

            // Update JSON hidden field
            var hiddenDenom = document.getElementById('form-cash-denominations');
            if (hiddenDenom) {
                hiddenDenom.value = JSON.stringify(denomsObj);
            }

            // Update Display Total
            var calcTotalEl = document.getElementById('disp-calc-total');
            if (calcTotalEl) {
                calcTotalEl.innerText = 'Rp ' + formatNumber(total);
            }

            // Update Difference
            var diff = total - expectedCashValue;
            var diffBadge = document.getElementById('disp-diff-badge');
            if (diffBadge) {
                if (diff === 0) {
                    diffBadge.innerHTML = '<span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px;">✓ PAS (Sesuai)</span>';
                } else if (diff > 0) {
                    diffBadge.innerHTML = '<span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px;">+ LEBIH Rp ' + formatNumber(diff) + '</span>';
                } else {
                    diffBadge.innerHTML = '<span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-size: 12px;">- MINUS Rp ' + formatNumber(Math.abs(diff)) + '</span>';
                }
            }

            return { total: total, diff: diff, denoms: denomsObj };
        }

        // 3. Salin Hasil Hitung ke Form Serah Terima
        function applyCalcToForm() {
            var res = calcDenominations();
            var totalPhysicalInput = document.getElementById('input-total-physical');
            var blindCashInput = document.getElementById('form-blind-cash-count');

            if (totalPhysicalInput) {
                totalPhysicalInput.value = res.total;
            }
            if (blindCashInput) {
                blindCashInput.value = res.total;
            }

            updateHandoverDistribution();
            alert('Hasil hitung fisik laci (Rp ' + formatNumber(res.total) + ') berhasil disalin ke formulir serah terima!');
        }

        // 4. Update Pembagian Modal vs Uang Bersih Diserahterimakan
        function updateHandoverDistribution() {
            var physicalInput = document.getElementById('input-total-physical');
            var retainedInput = document.getElementById('input-retained-cash');
            var handoverInput = document.getElementById('input-handover-amount');
            var blindCashInput = document.getElementById('form-blind-cash-count');

            var physical = physicalInput ? parseInt(physicalInput.value) || 0 : 0;
            var retained = retainedInput ? parseInt(retainedInput.value) || 0 : 0;
            var handover = Math.max(0, physical - retained);

            if (handoverInput) {
                handoverInput.value = handover;
            }
            if (blindCashInput) {
                blindCashInput.value = physical;
            }

            // Sync with thermal preview slip
            var slipPhysical = document.getElementById('slip-physical');
            var slipRetained = document.getElementById('slip-retained');
            var slipHandover = document.getElementById('slip-handover');
            var slipDiff = document.getElementById('slip-diff');

            if (slipPhysical) slipPhysical.innerText = 'Rp ' + formatNumber(physical);
            if (slipRetained) slipRetained.innerText = 'Rp ' + formatNumber(retained);
            if (slipHandover) slipHandover.innerText = 'Rp ' + formatNumber(handover);

            var diff = physical - expectedCashValue;
            if (slipDiff) {
                slipDiff.innerText = diff >= 0 ? '+Rp ' + formatNumber(diff) : '-Rp ' + formatNumber(Math.abs(diff));
            }
        }

        function setRetainedCashPreset(amount) {
            var retainedInput = document.getElementById('input-retained-cash');
            if (retainedInput) {
                retainedInput.value = amount;
                updateHandoverDistribution();
            }
        }

        // Simulasi Skenario Kas Laci Instan
        function applyHandoverPreset(presetType) {
            var targetExpected = parseFloat("{{ (float) $expectedCash }}") || 0;
            var isShift4 = {{ $isShift4 ? 'true' : 'false' }};
            var defaultRetained = isShift4 ? 150000 : 100000;

            var retained = defaultRetained;
            var handover = Math.max(0, targetExpected - retained);
            var note = '';

            if (presetType === 'match') {
                note = 'Simulasi: Uang fisik di laci pas dan cocok dengan target sistem (Selisih Rp 0).';
            } else if (presetType === 'shortage') {
                handover = Math.max(0, handover - 10000);
                note = 'Simulasi: Terjadi selisih kurang kas fisik Rp 10.000 dari sistem (uang receh kembalian kurang).';
            } else if (presetType === 'surplus') {
                handover = handover + 5000;
                note = 'Simulasi: Terjadi selisih lebih kas fisik Rp 5.000 di laci (tip dari pelanggan).';
            } else if (presetType === 'shift4') {
                retained = 150000;
                handover = Math.max(0, targetExpected - retained);
                note = 'Simulasi Shift 4 Penutup Hari: Modal laci Rp 150.000 ditinggal untuk Shift 1 besok pagi.';
            }

            var handoverInput = document.getElementById('input-handover-amount');
            var retainedInput = document.getElementById('input-retained-cash');
            var notesInput = document.querySelector('input[name="deposit_notes"]');

            if (handoverInput) handoverInput.value = handover;
            if (retainedInput) retainedInput.value = retained;
            if (notesInput) notesInput.value = note;

            updateHandoverDistribution();
        }

        // 5. Tabs Switching
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });

            btn.classList.add('active');
            var target = document.getElementById(tabId);
            if (target) target.classList.add('active');
        }

        // 6. Modal Setor Kas
        function openModalSetorKas(summaryId, defaultAmount) {
            var modal = document.getElementById('modal-setor-kas');
            if (!modal) return;
            if (summaryId) {
                var sInput = document.getElementById('modal-setor-summary-id');
                if (sInput) sInput.value = summaryId;
            }
            if (defaultAmount !== undefined && defaultAmount !== null && defaultAmount > 0) {
                var aInput = document.getElementById('modal-setor-amount');
                if (aInput) aInput.value = defaultAmount;
            }
            modal.style.display = 'flex';
        }

        function closeModalSetorKas() {
            var modal = document.getElementById('modal-setor-kas');
            if (modal) modal.style.display = 'none';
        }

        // 7. Modal Thermal Receipt Preview
        function previewThermalReceipt() {
            var recipient = document.getElementById('input-recipient') ? document.getElementById('input-recipient').value : 'Kasir Selanjutnya';
            var sRec = document.getElementById('slip-recipient');
            var sSign = document.getElementById('slip-sign-recipient');
            if (sRec) sRec.innerText = recipient;
            if (sSign) sSign.innerText = recipient;

            updateHandoverDistribution();

            var modal = document.getElementById('modal-thermal-preview');
            if (modal) modal.style.display = 'flex';
        }

        function closeThermalModal() {
            var modal = document.getElementById('modal-thermal-preview');
            if (modal) modal.style.display = 'none';
        }

        // 8. Confirm Save
        function confirmSaveHandover() {
            var handoverAmount = document.getElementById('input-handover-amount').value;
            var recipient = document.getElementById('input-recipient').value;
            return confirm('Yakin ingin menyimpan serah terima shift ini sebesar Rp ' + formatNumber(handoverAmount) + ' kepada ' + recipient + '?\n\nJika dicentang, sistem akan otomatis melakukan Presensi Pulang (Clock Out).');
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Initial calc on page load
        document.addEventListener('DOMContentLoaded', function() {
            calcDenominations();
        });
    </script>
</body>
</html>

