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

        /* PRINT STYLES FOR THERMAL SLIP */
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
                padding: 4mm;
                font-family: 'JetBrains Mono', monospace;
                font-size: 11px;
                color: #000000;
                background: #ffffff;
            }
        }
    </style>
</head>
<body>

    @php
        $handoverAmountVal = (float) ($salesSummary?->handoverAmount() ?? $cashInDrawer);
        $retainedDrawerVal = (float) ($salesSummary?->retained_drawer_cash ?? 100000);
        $isAlreadyDeposited = $salesSummary?->hasBeenDeposited() ?? false;
        $defaultDepositRecipient = $salesSummary?->handoverRecipientName() ?: ($nextShiftLabel ?? 'Kasir Shift Selanjutnya');
        $expectedCash = (float) ($systemExpectedCash ?? ($totalRevenue + 100000));
        $diffVal = $handoverAmountVal + $retainedDrawerVal - $expectedCash;
    @endphp

    {{-- TOP DESKTOP HEADER --}}
    <header class="pos-top-header">
        <div class="pos-header-left">
            <a href="{{ route('pos') }}" class="btn-nav-pill" title="Kembali ke Layar Penjualan POS Desktop">
                <span>←</span> <strong>POS Desktop</strong>
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
                    <div class="stat-row" style="background: #f8fafc; padding: 10px; margin: 6px -14px -6px -14px; border-top: 2px solid #e2e8f0;">
                        <div>
                            <strong style="color: #1e293b; font-size: 12.5px; display: block;">Total Target Kas Sistem</strong>
                            <small style="color: #64748b;">Modal Awal + Omset Shift</small>
                        </div>
                        <span class="stat-value" style="font-size: 16px; color: #0284c7;" id="disp-expected-cash">
                            Rp {{ number_format($expectedCash, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- BOX RINGKASAN AKUMULASI CABANG --}}
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-cyan">
                    <span>🏦 Akumulasi Siap Setor</span>
                    <span style="font-size: 11px;">{{ $readyToDepositSummaries->count() }} Shift</span>
                </div>
                <div class="panel-box-body">
                    <div style="font-size: 20px; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: #0284c7; margin-bottom: 4px;">
                        Rp {{ number_format($totalSiapSetor, 0, ',', '.') }}
                    </div>
                    <p style="font-size: 11.5px; color: #64748b; line-height: 1.4; margin-bottom: 10px;">
                        Total dana tunai yang sudah diserahterimakan dari shift sebelumnya dan menunggu disetor ke bank/kolektor.
                    </p>
                    <button type="button" onclick="openModalSetorKas({{ $salesSummary?->id ?? 'null' }}, {{ (int) $handoverAmountVal }})" class="btn-action-secondary" style="width: 100%; justify-content: center; background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                        <span>🏦</span> Setor Kas Sekarang (Bank / Kolektor)
                    </button>
                </div>
            </div>
        </section>

        {{-- KOLOM 2: PENGHITUNG PECAHAN FISIK KAS LACI (DENOMINASI) --}}
        <section class="st-col-mid">
            <div class="panel-box">
                <div class="panel-box-header panel-box-header-green">
                    <span>💵 Hitung Fisik Kas Laci (Denominasi Pecahan)</span>
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
                            <span class="stat-label">Target Kas Sistem</span>
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
                    <form action="{{ route('presensi2.serah-terima.store') }}" method="POST" id="form-serah-terima" onsubmit="return confirmSaveHandover()">
                        @csrf
                        <input type="hidden" name="summary_id" value="{{ $salesSummary?->id }}">
                        <input type="hidden" name="action_type" value="handover">
                        <input type="hidden" name="cash_denominations" id="form-cash-denominations" value="{{ json_encode($cashDenominations ?? []) }}">
                        <input type="hidden" name="blind_cash_count" id="form-blind-cash-count" value="{{ (int) ($handoverAmountVal + $retainedDrawerVal) }}">

                        {{-- TOTAL FISIK KAS LACI --}}
                        <div class="form-group">
                            <label class="form-label">Total Uang Fisik di Laci (Rp) *</label>
                            <input type="number" id="input-total-physical" required min="0" step="100"
                                   class="form-control form-control-money"
                                   style="background: #f8fafc; border-color: #94a3b8; color: #0f172a;"
                                   value="{{ (int) ($handoverAmountVal + $retainedDrawerVal) }}"
                                   oninput="updateHandoverDistribution()">
                            <small style="color: #64748b; font-size: 11px;">Total seluruh uang di laci (modal + hasil shift)</small>
                        </div>

                        {{-- MODAL LACI DITINGGAL --}}
                        <div class="form-group">
                            <label class="form-label">Modal Laci Ditinggal (Kembalian) (Rp) *</label>
                            <input type="number" name="retained_drawer_cash" id="input-retained-cash" required min="0" step="100"
                                   class="form-control form-control-money"
                                   style="color: #0284c7;"
                                   value="{{ (int) $retainedDrawerVal }}"
                                   oninput="updateHandoverDistribution()">
                            <small style="color: #64748b; font-size: 11px;">Uang receh yang ditinggal di laci untuk modal kasir selanjutnya</small>
                        </div>

                        {{-- KAS BERSIH DISERAHKAN (SIAP SETOR) --}}
                        <div class="form-group">
                            <label class="form-label" style="color: #008d4c;">Uang Bersih Diserahterimakan (Rp) *</label>
                            <input type="number" name="deposited_amount" id="input-handover-amount" required min="0" step="100"
                                   class="form-control form-control-money"
                                   style="border-color: #00a65a; background: #f0fdf4; color: #008d4c; font-size: 16px;"
                                   value="{{ (int) $handoverAmountVal }}">
                            <small style="color: #008d4c; font-size: 11px;">Nominal yang diserahkan ke kasir berikutnya / siap setor</small>
                        </div>

                        {{-- KASIR / SHIFT PENERIMA --}}
                        <div class="form-group">
                            <label class="form-label">Shift / Kasir Penerima *</label>
                            <input type="text" name="deposit_recipient" id="input-recipient" required
                                   class="form-control"
                                   placeholder="Contoh: Shift 2 / Grace"
                                   value="{{ $defaultDepositRecipient }}">
                        </div>

                        {{-- CATATAN SHIFT --}}
                        <div class="form-group">
                            <label class="form-label">Catatan Serah Terima (Opsional)</label>
                            <input type="text" name="deposit_notes" class="form-control"
                                   placeholder="Contoh: Stok rokok aman, printer baru ganti kertas"
                                   value="{{ $salesSummary?->handover_notes ?? $salesSummary?->deposit_notes ?? '' }}">
                        </div>

                        {{-- AUTO CLOCK OUT CHECKBOX --}}
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-bottom: 14px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0; font-size: 12px; color: #1e293b;">
                                <input type="checkbox" name="auto_clock_out" value="1" checked style="width: 17px; height: 17px; accent-color: #00a65a; cursor: pointer;">
                                <span><strong>Selesai Serah Terima</strong>: Otomatis lakukan <strong>Presensi Pulang (Clock Out)</strong></span>
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
                        <span>🤝</span> Serah Terima Shift Hari Ini ({{ $todayHandovers->count() }})
                    </button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-ready', this)">
                        <span>📥</span> Antrean Siap Setor ({{ $readyToDepositSummaries->count() }})
                    </button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-history', this)">
                        <span>🏦</span> Riwayat Setoran Bank / Kolektor ({{ $historyDepositSummaries->count() }})
                    </button>
                    <button type="button" class="tab-btn" onclick="switchTab('tab-stock', this)">
                        <span>📦</span> Audit Sisa Stok Fisik
                    </button>
                </div>
            </div>

            <div class="panel-box-body" style="padding: 0 16px 16px 16px;">
                {{-- TAB 1: SERAH TERIMA HARI INI --}}
                <div id="tab-handovers" class="tab-content active">
                    <table class="table-data">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Tanggal & Jam</th>
                                <th>Shift</th>
                                <th>Kasir Penyerah</th>
                                <th>Kasir Penerima</th>
                                <th style="text-align: right;">Total Omset</th>
                                <th style="text-align: right;">Kas Diserahkan</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayHandovers as $idx => $th)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
                                    <td style="font-family: monospace;">
                                        {{ $th->work_date?->format('d-m-Y') ?? date('d-m-Y') }}
                                        <small style="color: #64748b;">{{ ($th->handover_at ?? $th->updated_at)?->format('H:i') }}</small>
                                    </td>
                                    <td><span class="badge-shift">{{ $th->shiftNumber() }}</span></td>
                                    <td><strong>{{ strtoupper($th->employee->name ?? 'Kasir') }}</strong></td>
                                    <td style="color: #0284c7;"><strong>{{ $th->handoverRecipientName() }}</strong></td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace;">
                                        Rp {{ number_format((float)$th->total_revenue, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #00a65a;">
                                        Rp {{ number_format((float)$th->handoverAmount(), 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('presensi2.rekap', ['summary_id' => $th->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Cetak Bukti Slip Rekap" class="btn-nav-pill" style="padding: 3px 8px; font-size: 11px;">
                                            🖨️ Cetak
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">
                                        Belum ada rekaman serah terima shift hari ini.
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
                            @forelse($readyToDepositSummaries as $idx => $rtd)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
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
                            @forelse($historyDepositSummaries as $idx => $hs)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
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
                            @forelse($stockComparisons as $idx => $stk)
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
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

    {{-- MODAL PREVIEW PRINT THERMAL (58mm / 80mm) --}}
    <div id="modal-thermal-preview" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 420px;">
            <div class="pos-modal-header" style="background-color: #334155;">
                <span>🖨️ Bukti Serah Terima Kasir (Thermal)</span>
                <button type="button" onclick="closeThermalModal()" style="background: transparent; border: none; color: #fff; font-size: 18px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 16px; background: #f8fafc; max-height: 480px; overflow-y: auto;">
                <div id="thermal-print-area" style="background: #ffffff; border: 1px dashed #cbd5e1; padding: 14px; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; line-height: 1.45; color: #000;">
                    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                        <strong style="font-size: 13px;">MAYA GRAHA KENCANA</strong><br>
                        <span>Cabang: {{ strtoupper($currentEmployee->branch->name ?? 'MAYA 1') }}</span><br>
                        <span>BUKTI SERAH TERIMA SHIFT</span>
                    </div>

                    <div style="margin-bottom: 8px; font-size: 11px;">
                        <div>Tanggal : {{ date('d/m/Y H:i:s') }}</div>
                        <div>Shift   : {{ $activeShift?->id ?? '1' }} ({{ $activeShift?->name ?? 'Pagi' }})</div>
                        <div>Kasir   : {{ $currentEmployee->name }}</div>
                        <div>Penerima: <span id="slip-recipient">{{ $defaultDepositRecipient }}</span></div>
                    </div>

                    <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 6px 0; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Modal Awal Kas</span>
                            <span>Rp {{ number_format($openingCash ?? 100000, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Billing Internet</span>
                            <span>Rp {{ number_format($lockedBillingAmount, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Print Kertas ({{ $lockedPrintPages }} lbr)</span>
                            <span>Rp {{ number_format($lockedPrintAmount, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Omset F&B / POS</span>
                            <span>Rp {{ number_format($totalFnbSales, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 4px; border-top: 1px dotted #000; padding-top: 4px;">
                            <span>Target Sistem</span>
                            <span>Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; font-weight: bold;">
                            <span>Fisik Uang Laci</span>
                            <span id="slip-physical">Rp {{ number_format($handoverAmountVal + $retainedDrawerVal, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>- Modal Ditinggal</span>
                            <span id="slip-retained">Rp {{ number_format($retainedDrawerVal, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: bold; color: #000; border-top: 1px solid #000; padding-top: 2px;">
                            <span>= Kas Diserahkan</span>
                            <span id="slip-handover">Rp {{ number_format($handoverAmountVal, 0, ',', '.') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 10.5px; margin-top: 4px;">
                            <span>Selisih Kas</span>
                            <span id="slip-diff">{{ $diffVal >= 0 ? '+Rp '.number_format($diffVal, 0, ',', '.') : '-Rp '.number_format(abs($diffVal), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div style="text-align: center; border-top: 1px dashed #000; padding-top: 12px; margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <span>Yang Menyerahkan,</span><br><br><br>
                            <strong>( {{ $currentEmployee->name }} )</strong>
                        </div>
                        <div>
                            <span>Penerima Shift,</span><br><br><br>
                            <strong>( <span id="slip-sign-recipient">{{ $defaultDepositRecipient }}</span> )</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 12px 16px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeThermalModal()" class="btn-action-secondary">Tutup</button>
                <button type="button" onclick="window.print()" class="btn-action-primary" style="width: auto;">
                    🖨️ Cetak Slip Sekarang
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
