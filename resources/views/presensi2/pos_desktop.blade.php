<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orderan POS — Maya Graha Kencana</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --pos-bg: #ecf0f5;
            --pos-card-bg: #ffffff;
            --pos-green: #00a65a;
            --pos-green-dark: #008d4c;
            --pos-green-light: #dff0d8;
            --pos-green-text: #3c763d;
            --pos-orange: #f39c12;
            --pos-cyan: #00c0ef;
            --pos-red: #dd4b39;
            --pos-blue-text: #2563eb;
            --pos-border: #cbd5e1;
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

        /* TOP POS HEADER */
        .pos-top-header {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px 8px 20px;
            background: var(--pos-bg);
        }

        .pos-top-title {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 0.3px;
            margin: 0;
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
            font-size: 11.5px;
            font-family: monospace;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-nav-pill {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.15s ease;
        }
        .btn-nav-pill:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* MAIN TWO-COLUMN GRID */
        .pos-main-container {
            display: grid;
            grid-template-columns: 370px 1fr;
            gap: 16px;
            padding: 4px 20px 14px 20px;
            flex: 1;
        }

        @media (max-width: 900px) {
            .pos-main-container {
                grid-template-columns: 1fr;
            }
        }

        /* LEFT COLUMN */
        .pos-left-col {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .search-box-wrap {
            display: flex;
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
        }

        .search-input {
            flex: 1;
            border: none;
            padding: 7px 10px;
            font-size: 13px;
            color: #333;
            outline: none;
        }

        .search-btn {
            border: none;
            background: #f8fafc;
            border-left: 1px solid #e2e8f0;
            padding: 0 12px;
            cursor: pointer;
            color: #64748b;
            font-size: 13px;
        }

        /* 4 BUTTONS 2x2 GRID */
        .pos-quick-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .btn-quick-action {
            border: none;
            color: #ffffff;
            padding: 7px 8px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 3px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            line-height: 1.25;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.15);
            transition: filter 0.15s ease;
        }
        .btn-quick-action:hover {
            filter: brightness(1.08);
        }

        .btn-qa-orange { background-color: var(--pos-orange); }
        .btn-qa-green { background-color: var(--pos-green); }
        .btn-qa-cyan { background-color: var(--pos-cyan); }

        /* TERLARIS BOX */
        .panel-box {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }

        .panel-box-header-green {
            background-color: var(--pos-green-light);
            color: var(--pos-green-text);
            font-weight: 700;
            font-size: 12.5px;
            padding: 6px 12px;
            border-bottom: 1px solid #d6e9c6;
        }

        .terlaris-list-wrap {
            max-height: 340px;
            overflow-y: auto;
            background: #ffffff;
        }

        .terlaris-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.15s;
        }
        .terlaris-item:hover {
            background: #f8fafc;
        }

        .terlaris-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--pos-blue-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .badge-cyan-stock {
            background: var(--pos-cyan);
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
        }

        .badge-red-price {
            background: var(--pos-red);
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: monospace;
        }

        /* STATUS PENJUALAN BOX */
        .panel-box-header-yellow {
            background-color: #fcf8e3;
            color: #8a6d3b;
            font-weight: 700;
            font-size: 12px;
            padding: 6px 12px;
            border-bottom: 1px solid #faebcc;
        }

        .status-cat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 12px;
            border-bottom: 1px solid #f8fafc;
            font-size: 12px;
            color: #334155;
        }

        .btn-cat-open {
            background-color: var(--pos-green);
            color: #ffffff;
            border: none;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 2px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .btn-cat-open:hover {
            background-color: var(--pos-green-dark);
        }

        /* RIGHT COLUMN (KERANJANG BELANJA) */
        .cart-panel-card {
            background: #ffffff;
            border: 1px solid var(--pos-green);
            border-radius: 3px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            min-height: 480px;
        }

        .cart-panel-header {
            background-color: var(--pos-green);
            color: #ffffff;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 700;
        }

        .cart-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-cart-tool {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            padding: 0 4px;
        }

        .cart-body-content {
            flex: 1;
            padding: 16px;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        /* EXACT "data kosong" PLACEHOLDER */
        .cart-empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 320px;
            color: #64748b;
        }

        .cart-empty-text {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
        }

        /* CART BOTTOM CONTROLS */
        .cart-footer-bar {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .btn-cart-clear {
            background-color: #8bc34a;
            color: #ffffff;
            border: none;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-cart-clear:hover {
            background-color: #7cb342;
        }

        .btn-cart-submit {
            background-color: var(--pos-green-dark);
            color: #ffffff;
            border: none;
            padding: 8px 24px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .btn-cart-submit:hover {
            background-color: #00733e;
        }

        /* BOTTOM ACCORDION BAR (HISTORY PENJUALAN POS) */
        .pos-bottom-container {
            padding: 0 20px 16px 20px;
        }

        .history-bar-header {
            background-color: var(--pos-cyan);
            color: #ffffff;
            padding: 8px 14px;
            font-size: 13.5px;
            font-weight: 700;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .history-accordion-body {
            display: none;
            background: #ffffff;
            border: 1px solid var(--pos-cyan);
            border-top: none;
            padding: 12px;
            border-radius: 0 0 3px 3px;
            overflow-x: auto;
        }

        /* MODAL STYLES */
        .pos-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .pos-modal-box {
            background: #ffffff;
            border-radius: 4px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #333333;
        }

        .pos-modal-header {
            padding: 12px 16px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            font-size: 14px;
        }

        /* THERMAL RECEIPT PRINT AREA */
        .thermal-slip {
            width: 280px;
            margin: 0 auto;
            background: #ffffff;
            color: #000000;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            padding: 10px;
            border: 1px dashed #cbd5e1;
            text-align: left;
            line-height: 1.3;
        }
        .thermal-center { text-align: center; }
        .thermal-divider { border-bottom: 1px dashed #000; margin: 5px 0; }
        .thermal-row { display: flex; justify-content: space-between; margin: 2px 0; }

        @media print {
            body * { visibility: hidden !important; }
            #printable-thermal-area, #printable-thermal-area * {
                visibility: visible !important;
            }
            #printable-thermal-area {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                border: none !important;
            }
        }

        /* POS SIMULATION BAR & PRESETS */
        .pos-simulation-bar {
            margin: 0 20px 14px 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #8b5cf6;
            border-radius: 8px;
            padding: 10px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .btn-pos-preset {
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .btn-pos-preset:hover {
            transform: translateY(-1px);
            filter: brightness(0.96);
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

    {{-- TOP POS HEADER: Centered "Orderan POS" & Right "penjualan > 1;" --}}
    <header class="pos-top-header">
        <div class="pos-header-left">
            <a href="{{ route('presensi2.penjualan') }}" class="btn-nav-pill" title="Buka Versi Tampilan Mobile / HP">
                <span>📱</span> Versi HP
            </a>
            <a href="{{ route('presensi2.stok') }}" class="btn-nav-pill" title="Cek Stok Fisik Shift">
                <span>📦</span> Cek Stok
            </a>
            <a href="{{ route('presensi2.serah-terima') }}" class="btn-nav-pill" style="color: #b45309; border-color: #fcd34d; background: #fef3c7;" title="Tutup Shift & Serah Terima Kasir">
                <span>🏁</span> Tutup Shift
            </a>
            <button type="button" onclick="togglePosSimulation()" class="btn-nav-pill" style="color: #7c3aed; border-color: #c4b5fd; background: #f5f3ff;" title="Buka / Tutup Panel Uji Coba Transaksi">
                <span>🧪</span> <span id="btn-toggle-sim-label">Mode Simulasi</span>
            </button>
        </div>

        <h1 class="pos-top-title">Orderan POS</h1>

        <div class="pos-header-right">
            <span>👤</span>
            <span>penjualan &gt; {{ $activeShift?->id ?? '1' }};</span>
            <span style="color: #059669; font-weight: bold;">({{ $currentEmployee->name }})</span>
            <a href="{{ route('presensi2.logout', ['mode' => 'pos']) }}" class="btn-nav-pill" style="color: #dc2626; border-color: #fca5a5; background: #fff5f5; padding: 3px 8px; font-size: 11px;" title="Keluar / Ganti Kasir POS" onclick="return confirm('Keluar dari sesi POS kasir?')">
                <span>🚪</span> Keluar
            </a>
        </div>
    </header>

    {{-- ALERT STATUS / NOTIFIKASI RESET --}}
    @if (session('status'))
        <div style="background: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 10px 16px; border-radius: 6px; margin: 0 20px 12px 20px; font-weight: 500; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">✅</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #166534;">✕</button>
        </div>
    @endif
    @if (session('error'))
        <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 10px 16px; border-radius: 6px; margin: 0 20px 12px 20px; font-weight: 500; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #991b1b;">✕</button>
        </div>
    @endif

    {{-- PANEL MODE SIMULASI & PENGUJIAN TRANSAKSI POS --}}
    <div id="pos-simulation-container" class="pos-simulation-bar" style="display: block;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">🧪</span>
                <div>
                    <div style="font-size: 12.5px; font-weight: 700; color: #6d28d9; display: flex; align-items: center; gap: 6px;">
                        <span>Mode Simulasi & Pengujian Transaksi POS</span>
                        <span style="background: #ede9fe; color: #7c3aed; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">TESTING & TRAINING</span>
                    </div>
                    <div style="font-size: 11px; color: #64748b;">
                        Uji coba alur transaksi instan & buktikan efeknya ke stok fisik dan rekap serah terima shift tanpa repot input manual.
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                {{-- PRESET 1: AQUA 600ML TUNAI --}}
                <button type="button" onclick="runPosSimulation('aqua_cash')" class="btn-pos-preset" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 6px 11px; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="Langsung checkout 1 Aqua 600ml tunai dan cetak struk">
                    <span>⚡ 1. Beli Aqua (Tunai)</span>
                </button>

                {{-- PRESET 2: PRINT A4 + MINUMAN QRIS --}}
                <button type="button" onclick="runPosSimulation('print_qris')" class="btn-pos-preset" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 6px 11px; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="Langsung checkout 2 Lembar Print + 1 Minuman via QRIS">
                    <span>⚡ 2. Print + Minum (QRIS)</span>
                </button>

                {{-- PRESET 3: ISI KERANJANG SAJA --}}
                <button type="button" onclick="runPosSimulation('fill_cart')" class="btn-pos-preset" style="background: #faf5ff; border: 1px solid #e9d5ff; color: #6b21a8; padding: 6px 11px; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;" title="Hanya isi 2 produk ke keranjang untuk dicoba kasir manual">
                    <span>🛒 3. Isi Keranjang Uji Coba</span>
                </button>

                {{-- RESET TRANSAKSI TESTING HARI INI --}}
                <a href="{{ request()->fullUrlWithQuery(['action' => 'reset_sales']) }}"
                   onclick="return confirm('PERINGATAN UJI COBA:\n\nApakah Anda yakin ingin me-reset seluruh transaksi penjualan shift hari ini?\n\n• Seluruh transaksi testing hari ini akan dihapus dari sistem\n• Stok barang di gudang/kulkas akan dikembalikan utuh\n• Rekap omset shift akan kembali bersih Rp 0')"
                   class="btn-pos-preset"
                   style="background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 6px 11px; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 5px;"
                   title="Bersihkan seluruh riwayat transaksi dummy hari ini & kembalikan stok fisik">
                    <span>🔄 Reset Transaksi Testing</span>
                </a>
            </div>
        </div>
    </div>

    {{-- MAIN 2-COLUMN GRID --}}
    <main class="pos-main-container">

        {{-- KOLOM KIRI (Search, 4 Quick Buttons, Terlaris, Status Penjualan) --}}
        <section class="pos-left-col">

            {{-- 1. PENCARIAN PRODUK --}}
            <div class="search-box-wrap">
                <input type="text" id="pos-search-input" oninput="filterPosMenu()" placeholder="cari produk" class="search-input">
                <button type="button" onclick="filterPosMenu()" class="search-btn">🔍</button>
            </div>

            {{-- 2. 4 TOMBOL AKSI CEPAT (2x2 GRID PERSIS DI CONTOH SCREENSHOT) --}}
            <div class="pos-quick-actions-grid">
                {{-- Tombol 1: Kunci Billing (Orange / Dark Amber jika Terkunci) --}}
                @if($isBillingLocked)
                    <button type="button" class="btn-quick-action" style="background-color: #b45309; cursor: not-allowed; opacity: 0.9;" title="Data billing shift sudah DIKUNCI. Operator tidak dapat membuka kembali." onclick="alert('Data billing shift sudah DIKUNCI sebesar Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}. Operator tidak dapat membuka kunci kembali.')">
                        <span>🔒</span> Billing Terkunci<br><small style="font-size: 9px; font-weight: normal;">(Rp {{ number_format($totalInternetIncome, 0, ',', '.') }})</small>
                    </button>
                @else
                    <button type="button" class="btn-quick-action btn-qa-orange" onclick="confirmLockBilling({{ (int) $totalInternetIncome }})" title="Kunci data billing shift saat ini">
                        <span>🔒</span> Kunci Billing<br><small style="font-size: 9px; font-weight: normal;">(Rp {{ number_format($totalInternetIncome, 0, ',', '.') }})</small>
                    </button>
                @endif

                {{-- Tombol 2: Cek Data Billing (Hijau) --}}
                <button type="button" class="btn-quick-action btn-qa-green" onclick="openModalBilling()" title="Cek Status PC & Pendapatan Billing">
                    <span>👁️</span> cek data billing
                </button>

                {{-- Tombol 3: Kunci Print (Cyan / Dark Cyan jika Terkunci) --}}
                @if($isPrintLocked)
                    <button type="button" class="btn-quick-action" style="background-color: #0e7490; cursor: not-allowed; opacity: 0.9;" title="Data print kertas shift sudah DIKUNCI. Operator tidak dapat membuka kembali." onclick="alert('Data print kertas shift sudah DIKUNCI sebesar {{ $totalPrintPages }} lembar (Rp {{ number_format($totalPrintIncome, 0, ',', '.') }}). Operator tidak dapat membuka kunci kembali.')">
                        <span>🔒</span> Print Terkunci<br><small style="font-size: 9px; font-weight: normal;">({{ $totalPrintPages }} lbr / Rp {{ number_format($totalPrintIncome, 0, ',', '.') }})</small>
                    </button>
                @else
                    <button type="button" class="btn-quick-action btn-qa-cyan" onclick="confirmLockPrint({{ (int) $totalPrintPages }}, {{ (int) $totalPrintIncome }})" title="Kunci data print kertas shift saat ini">
                        <span>🔒</span> Kunci Print<br><small style="font-size: 9px; font-weight: normal;">({{ $totalPrintPages }} lbr / Rp {{ number_format($totalPrintIncome, 0, ',', '.') }})</small>
                    </button>
                @endif

                {{-- Tombol 4: Cek Printing (Hijau) --}}
                <button type="button" class="btn-quick-action btn-qa-green" onclick="openModalPrintKertas()" title="Cek Data Cetak & Printer Canon">
                    <span>👁️</span> cek printing
                </button>
            </div>

            {{-- 3. BOX TERLARIS (TABEL PRODUK) --}}
            <div class="panel-box">
                <div class="panel-box-header-green">
                    Terlaris
                </div>
                <div id="terlaris-items-list" class="terlaris-list-wrap">
                    @forelse($fnbProducts as $fp)
                        @php
                            $catCode = strtolower($fp->product?->category?->code ?? 'snack');
                            $stockQty = (int) ($fp->stock_qty ?? 20);
                            $priceFmt = number_format($fp->base_price, 0, ',', '.');
                        @endphp
                        <div class="terlaris-item" 
                             id="terlaris-row-{{ $fp->id }}"
                             data-id="{{ $fp->id }}"
                             data-name="{{ strtoupper($fp->name) }}"
                             data-cat="{{ $catCode }}"
                             onclick="addToCart({{ $fp->id }})"
                             title="Klik untuk masukkan ke Keranjang Belanja">
                            <span class="terlaris-name">{{ strtoupper($fp->name) }}</span>
                            <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                                <span class="badge-cyan-stock" title="Stok Tersedia">{{ $stockQty }}</span>
                                <span class="badge-red-price" title="Harga">{{ $priceFmt }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                            Tidak ada produk terdaftar.
                        </div>
                    @endforelse

                    <div id="pos-search-empty-notice" style="display: none; padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                        Produk tidak ditemukan.
                    </div>
                </div>
            </div>

            {{-- 4. BOX STATUS PENJUALAN --}}
            <div class="panel-box">
                <div class="panel-box-header-yellow">
                    Status Penjualan
                </div>
                <div>
                    <div class="status-cat-row">
                        <span>Hardware</span>
                        <button type="button" class="btn-cat-open" onclick="filterByCategory('hardware')">📂Open</button>
                    </div>
                    <div class="status-cat-row">
                        <span>Softdrink</span>
                        <button type="button" class="btn-cat-open" onclick="filterByCategory('minuman')">📂Open</button>
                    </div>
                    <div class="status-cat-row">
                        <span>Kertas</span>
                        <button type="button" class="btn-cat-open" onclick="filterByCategory('kertas')">📂Open</button>
                    </div>
                    <div class="status-cat-row">
                        <span>Snack</span>
                        <button type="button" class="btn-cat-open" onclick="filterByCategory('snack')">📂Open</button>
                    </div>
                    <div class="status-cat-row">
                        <span>Internet</span>
                        <button type="button" class="btn-cat-open" onclick="openModalBilling()">📂Open</button>
                    </div>
                </div>
            </div>

        </section>

        {{-- KOLOM KANAN: KERANJANG BELANJA --}}
        <section class="cart-panel-card">
            
            {{-- HEADER KERANJANG HIJAU SOLID (#00a65a) --}}
            <div class="cart-panel-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>🛒</span>
                    <span>Keranjang Belanja</span>
                    <span id="cart-item-count-badge" style="background: rgba(255,255,255,0.25); font-size: 11px; padding: 1px 7px; border-radius: 10px; font-weight: normal;">0 item</span>
                </div>
                <div class="cart-header-actions">
                    <button type="button" class="btn-cart-tool" onclick="toggleCartBody()" title="Minimize / Expand">─</button>
                    <button type="button" class="btn-cart-tool" onclick="clearCart()" title="Kosongkan Keranjang">✕</button>
                </div>
            </div>

            {{-- BODY KERANJANG --}}
            <div id="cart-body-wrap" class="cart-body-content">
                
                {{-- DATA KOSONG PLACEHOLDER (PERSIS CONTOH SCREENSHOT) --}}
                <div id="cart-empty-state" class="cart-empty-state">
                    <span class="cart-empty-text">data kosong</span>
                </div>

                {{-- ISI KERANJANG (TABEL SAAT ADA PRODUK DITAMBAHKAN) --}}
                <div id="cart-populated-state" style="display: none; flex-direction: column; flex: 1;">
                    <div style="flex: 1; overflow-y: auto; max-height: 280px; margin-bottom: 12px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #333;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                                    <th style="padding: 6px 8px; text-align: left;">Produk</th>
                                    <th style="padding: 6px 8px; text-align: center; width: 100px;">Qty</th>
                                    <th style="padding: 6px 8px; text-align: right; width: 90px;">Harga</th>
                                    <th style="padding: 6px 8px; text-align: right; width: 100px;">Subtotal</th>
                                    <th style="padding: 6px 4px; text-align: center; width: 30px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-table-body"></tbody>
                        </table>
                    </div>

                    {{-- DETAIL PEMBAYARAN & PELANGGAN --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-top: auto;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Pelanggan / Member / Unit PC:</label>
                                <input type="text" id="cart-customer-input" value="Pelanggan Umum" placeholder="Nama / PC..." style="width: 100%; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Metode Pembayaran:</label>
                                <select id="cart-payment-select" onchange="onPaymentMethodChanged(this.value)" style="width: 100%; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px; font-weight: 600;">
                                    <option value="cash">💵 Tunai (Cash)</option>
                                    <option value="qris">📱 QRIS / E-Wallet</option>
                                </select>
                            </div>
                        </div>

                        {{-- TOTAL TAGIHAN & NOMINAL BAYAR --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 6px; border-top: 1px dashed #cbd5e1;">
                            <div>
                                <span style="font-size: 11px; color: #64748b;">Total Bayar:</span>
                                <div id="cart-total-display" style="font-size: 20px; font-weight: 800; color: #008d4c; font-family: monospace;">
                                    Rp 0
                                </div>
                            </div>
                            <div id="cash-change-container" style="display: flex; align-items: center; gap: 8px;">
                                <div>
                                    <span style="font-size: 10.5px; color: #64748b;">Uang Diterima:</span>
                                    <input type="number" id="cart-cash-input" min="0" placeholder="0" oninput="calculateChange()" style="width: 110px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px; font-family: monospace; text-align: right;">
                                </div>
                                <div>
                                    <span style="font-size: 10.5px; color: #64748b;">Kembalian:</span>
                                    <div id="cart-change-display" style="font-size: 14px; font-weight: 700; color: #0369a1; font-family: monospace;">
                                        Rp 0
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FOOTER KERANJANG (TOMBOL CLEAR, SIMPAN ORDER PENJUALAN LANGSUNG, & CETAK STRUK TERAKHIR OPSIONAL) --}}
            <div class="cart-footer-bar">
                <button type="button" class="btn-cart-clear" onclick="clearCart()" title="Bersihkan keranjang">
                    <span>🔄</span> clear
                </button>
                <button type="button" id="btn-save-pos-order" class="btn-cart-submit" onclick="submitPosOrder()" title="Simpan pesanan langsung tanpa harus cetak struk">
                    Simpan Order Penjualan
                </button>
                <button type="button" id="btn-print-last-receipt" class="btn-cart-clear" style="display: none; background: #0284c7; border: 1px solid #0369a1; color: #fff; width: auto; padding: 0 12px; font-weight: 700;" onclick="printLastReceipt()" title="Cetak struk pesanan terakhir yang baru saja disimpan">
                    <span>🖨️</span> Struk
                </button>
            </div>

        </section>

    </main>

    {{-- BOTTOM ACCORDION BAR: HISTORY PENJUALAN POS --}}
    <section class="pos-bottom-container">
        <div class="history-bar-header" onclick="toggleHistoryAccordion()">
            <span>History penjualan pos</span>
            <span id="history-toggle-icon" style="font-size: 16px; font-weight: bold;">+</span>
        </div>
        
        <div id="history-accordion-content" class="history-accordion-body">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; color: #475569; background: #f8fafc;">
                        <th style="padding: 7px 10px;">Jam</th>
                        <th style="padding: 7px 10px;">No. Invoice</th>
                        <th style="padding: 7px 10px;">Item Pesanan</th>
                        <th style="padding: 7px 10px;">Pelanggan</th>
                        <th style="padding: 7px 10px; text-align: right;">Total</th>
                        <th style="padding: 7px 10px; text-align: center;">Metode</th>
                        <th style="padding: 7px 10px; text-align: center;">Status</th>
                        <th style="padding: 7px 10px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fnbSales as $sale)
                        @php
                            $isCancelled = $sale->payment_status === 'cancelled';
                            $saleTotal = (float) ($sale->net_amount > 0 ? $sale->net_amount : $sale->total_fnb);
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; {{ $isCancelled ? 'background: #fff1f2; opacity: 0.7;' : '' }}">
                            <td style="padding: 8px 10px; color: #64748b;">
                                {{ $sale->created_at ? $sale->created_at->format('H:i') : '-' }}
                            </td>
                            <td style="padding: 8px 10px; font-family: monospace; font-weight: bold; color: {{ $isCancelled ? '#ef4444' : '#0284c7' }};">
                                <span style="{{ $isCancelled ? 'text-decoration: line-through;' : '' }}">{{ $sale->invoice_number }}</span>
                            </td>
                            <td style="padding: 8px 10px;">
                                @foreach($sale->items as $it)
                                    <div>{{ $it->qty }}x {{ $it->item_name }}</div>
                                @endforeach
                            </td>
                            <td style="padding: 8px 10px;">
                                {{ $sale->customer_name ?: ($sale->customer?->name ?? 'Umum') }}
                            </td>
                            <td style="padding: 8px 10px; text-align: right; font-weight: 700; color: {{ $isCancelled ? '#94a3b8' : '#059669' }};">
                                Rp {{ number_format($saleTotal, 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px 10px; text-align: center;">
                                <span style="background: #e2e8f0; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 3px;">
                                    {{ strtoupper($sale->payment_method ?? 'CASH') }}
                                </span>
                            </td>
                            <td style="padding: 8px 10px; text-align: center;">
                                @if($isCancelled)
                                    <span style="background: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 3px;">VOID</span>
                                @else
                                    <span style="background: #dcfce7; color: #15803d; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 3px;">LUNAS</span>
                                @endif
                            </td>
                            <td style="padding: 8px 10px; text-align: center;">
                                <button type="button" onclick='reprintFromHistory(@json($sale))' style="padding: 3px 8px; font-size: 11px; background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; border-radius: 3px; cursor: pointer;">
                                    🖨️ Cetak
                                </button>
                                @if(!$isCancelled)
                                    <button type="button" onclick="promptCancelSale({{ $sale->id }}, '{{ $sale->invoice_number }}')" style="padding: 3px 8px; font-size: 11px; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 3px; cursor: pointer; margin-left: 4px;">
                                        ✕ Batal
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 20px; text-align: center; color: #94a3b8;">
                                Belum ada transaksi penjualan pada shift ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- TOAST NOTIFIKASI TRANSAKSI BERHASIL DISIMPAN (OPSI 1: LANGSUNG SIMPAN TANPA WAJIB PRINT) --}}
    <div id="pos-success-toast" style="display: none; position: fixed; top: 75px; right: 24px; z-index: 9999; background: #0f172a; color: #ffffff; border: 1.5px solid #10b981; border-radius: 12px; padding: 14px 18px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); max-width: 380px; width: calc(100% - 48px);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">✅</span>
                <div>
                    <strong style="font-size: 14px; color: #34d399; display: block;">Pesanan Berhasil Disimpan!</strong>
                    <span style="font-size: 11.5px; color: #94a3b8;" id="toast-invoice-num">Invoice: -</span>
                </div>
            </div>
            <button type="button" onclick="hideSuccessToast()" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer; line-height: 1;">✕</button>
        </div>
        <div style="margin-top: 10px; padding: 8px 10px; background: rgba(255,255,255,0.06); border-radius: 6px; font-size: 12.5px; display: flex; justify-content: space-between; align-items: center;">
            <span>Total: <strong id="toast-total" style="color: #f8fafc;">Rp 0</strong></span>
            <span style="background: #15803d; color: #fff; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 12px;" id="toast-change-wrap">
                Kembalian: <span id="toast-change">Rp 0</span>
            </span>
        </div>
        <div style="margin-top: 10px; display: flex; gap: 8px; justify-content: flex-end;">
            <button type="button" onclick="printLastReceipt()" class="btn-nav-pill" style="background: #0284c7; color: #fff; border-color: #0369a1; font-size: 12px; font-weight: 700; padding: 5px 12px;">
                🖨️ Cetak Struk
            </button>
            <button type="button" onclick="hideSuccessToast()" class="btn-nav-pill" style="background: rgba(255,255,255,0.1); color: #e2e8f0; border-color: rgba(255,255,255,0.2); font-size: 12px; padding: 5px 12px;">
                Tutup
            </button>
        </div>
    </div>

    {{-- MODAL 1: PREVIEW & CETAK STRUK THERMAL --}}
    <div id="modal-receipt" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 360px;">
            <div class="pos-modal-header" style="background: var(--pos-green);">
                <span>🧾 Struk Transaksi Kasir</span>
                <button type="button" onclick="closeReceiptModal()" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 16px; background: #f8fafc; text-align: center;">
                <div id="printable-thermal-area" class="thermal-slip">
                    <div class="thermal-center" style="font-weight: bold; font-size: 13px;">MAYA GRAHA KENCANA</div>
                    <div class="thermal-center" style="font-size: 10px;">Cabang {{ $currentEmployee->branch->name ?? 'Pusat' }}</div>
                    <div class="thermal-divider"></div>
                    <div class="thermal-row">
                        <span>Invoice:</span>
                        <span id="receipt-invoice-num" style="font-weight: bold;">-</span>
                    </div>
                    <div class="thermal-row">
                        <span>Waktu:</span>
                        <span id="receipt-date">-</span>
                    </div>
                    <div class="thermal-row">
                        <span>Kasir:</span>
                        <span id="receipt-cashier">{{ $currentEmployee->name }}</span>
                    </div>
                    <div class="thermal-row">
                        <span>Pelanggan:</span>
                        <span id="receipt-customer">Umum</span>
                    </div>
                    <div class="thermal-divider"></div>
                    <div id="receipt-items-list" style="margin: 4px 0;"></div>
                    <div class="thermal-divider"></div>
                    <div class="thermal-row" style="font-weight: bold; font-size: 12px;">
                        <span>TOTAL:</span>
                        <span id="receipt-total">Rp 0</span>
                    </div>
                    <div class="thermal-row">
                        <span>Metode:</span>
                        <span id="receipt-method">TUNAI</span>
                    </div>
                    <div class="thermal-row" id="receipt-cash-row">
                        <span>Bayar:</span>
                        <span id="receipt-cash">Rp 0</span>
                    </div>
                    <div class="thermal-row" id="receipt-change-row">
                        <span>Kembali:</span>
                        <span id="receipt-change">Rp 0</span>
                    </div>
                    <div class="thermal-divider"></div>
                    <div class="thermal-center" style="font-size: 10px; margin-top: 6px;">
                        Terima Kasih Atas Kunjungan Anda<br>
                        *** MGK POS System ***
                    </div>
                </div>
            </div>
            <div style="padding: 10px 16px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeReceiptModal()" style="padding: 6px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 3px; font-size: 12px; cursor: pointer;">
                    Tutup (Esc)
                </button>
                <button type="button" onclick="printReceipt()" style="padding: 6px 16px; border: none; background: var(--pos-green); color: #fff; font-weight: 700; border-radius: 3px; cursor: pointer;">
                    🖨️ Cetak Struk (Enter)
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL 2: GANTI BILLING (PENYESUAIAN MATI LISTRIK) --}}
    <div id="modal-penyesuaian-billing" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 520px;">
            <div class="pos-modal-header" style="background: var(--pos-orange);">
                <span>🔒 Ganti / Penyesuaian Billing (Mati Listrik)</span>
                <button type="button" onclick="closeModalPenyesuaianBilling()" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">✕</button>
            </div>
            <form id="form-penyesuaian-billing" onsubmit="submitPenyesuaianBilling(event)" style="padding: 16px;">
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; font-size: 11px; color: #92400e;">
                    Potongan billing akibat mati lampu PLN tidak akan dibebankan ke kasir saat serah terima.
                </div>
                <div style="margin-bottom: 8px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">1. Omset Billing Terbaca (Rp):</label>
                    <input type="number" id="adj-raw-billing" required min="0" value="{{ (int) $totalInternetIncome }}" oninput="recalculateNetBilling()" style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px;">
                </div>
                <div style="margin-bottom: 8px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">2. Alasan Kompensasi:</label>
                    <select id="adj-reason" style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px;">
                        <option value="blackout">⚡ Pemadaman Listrik (Mati Lampu PLN)</option>
                        <option value="isp_down">🌐 Gangguan Jaringan ISP</option>
                        <option value="manual_correction">✏️ Koreksi Kasir Manual</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #dc2626; display: block; margin-bottom: 3px;">3. Potongan Kompensasi (Rp):</label>
                        <input type="number" id="adj-deduction-amount" required min="0" value="{{ (int) ($salesSummary?->billing_adjustment_amount ?? 0) }}" oninput="recalculateNetBilling()" style="width: 100%; padding: 6px 8px; border: 1px solid #f87171; border-radius: 3px; font-size: 12px; font-weight: bold; color: #b91c1c;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #15803d; display: block; margin-bottom: 3px;">4. Billing Bersih Disetor (Rp):</label>
                        <input type="number" id="adj-net-billing" readonly value="{{ (int) $totalInternetIncome }}" style="width: 100%; padding: 6px 8px; border: 1px solid #86efac; border-radius: 3px; font-size: 12px; font-weight: bold; color: #15803d; background: #f0fdf4;">
                    </div>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">5. Catatan / Kronologi Padam:</label>
                    <textarea id="adj-notes" required rows="2" placeholder="Jam mati lampu, jumlah PC..." style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 11.5px;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" onclick="closeModalPenyesuaianBilling()" style="padding: 6px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 3px; font-size: 12px;">Batal</button>
                    <button type="submit" style="padding: 6px 16px; border: none; background: var(--pos-orange); color: #fff; font-weight: 700; border-radius: 3px; font-size: 12px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: CEK DATA BILLING --}}
    <div id="modal-billing" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 600px;">
            <div class="pos-modal-header" style="background: var(--pos-green);">
                <span>💻 Data Billing Internet Shift</span>
                <button type="button" onclick="closeModal('modal-billing')" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 16px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 14px; text-align: center;">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px; border-radius: 4px;">
                        <span style="font-size: 10px; color: #166534;">Omset Billing:</span>
                        <div style="font-size: 15px; font-weight: bold; color: #15803d;">Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}</div>
                    </div>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 8px; border-radius: 4px;">
                        <span style="font-size: 10px; color: #991b1b;">PC Sedang Main:</span>
                        <div style="font-size: 15px; font-weight: bold; color: #dc2626;">{{ $occupiedPcCount ?? 0 }} Unit</div>
                    </div>
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px; border-radius: 4px;">
                        <span style="font-size: 10px; color: #065f46;">PC Tersedia:</span>
                        <div style="font-size: 15px; font-weight: bold; color: #059669;">{{ $availablePcCount ?? 0 }} Unit</div>
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">Status Stasiun Komputer:</div>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 6px 8px; text-align: left;">Station PC</th>
                                <th style="padding: 6px 8px; text-align: center;">Status</th>
                                <th style="padding: 6px 8px; text-align: right;">Durasi / Billing</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pcStations as $pc)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 6px 8px; font-weight: 600;">{{ $pc->station_code }}</td>
                                    <td style="padding: 6px 8px; text-align: center;">
                                        @if($pc->is_occupied)
                                            <span style="background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold;">Terpakai</span>
                                        @else
                                            <span style="background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold;">Kosong</span>
                                        @endif
                                    </td>
                                    <td style="padding: 6px 8px; text-align: right; color: #64748b;">
                                        {{ $pc->is_occupied ? 'Aktif Main' : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding: 12px; text-align: center; color: #94a3b8;">Tidak ada data PC.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="padding: 10px 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                <button type="button" onclick="closeModal('modal-billing')" style="padding: 6px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px;">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL 4: GANTI DATA PRINT (PENYESUAIAN KERTAS MACET) --}}
    <div id="modal-penyesuaian-print" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 520px;">
            <div class="pos-modal-header" style="background: var(--pos-cyan);">
                <span>🖨️ Ganti Data Print (Kertas Macet / Rusak)</span>
                <button type="button" onclick="closeModalPenyesuaianPrint()" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">✕</button>
            </div>
            <form id="form-penyesuaian-print" onsubmit="submitPenyesuaianPrint(event)" style="padding: 16px;">
                <div style="background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; font-size: 11px; color: #0369a1;">
                    Lembar kertas rusak / tinta bergaris dipotong dari tagihan kasir agar tidak tekor saat serah terima.
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">Lembar Rusak Dipotong:</label>
                        <input type="number" id="adj-print-pages" required min="0" value="{{ (int) ($salesSummary?->print_adjustment_pages ?? 0) }}" style="width: 100%; padding: 6px 8px; border: 1px solid #f87171; border-radius: 3px; font-size: 12px; font-weight: bold; color: #b91c1c;">
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">Nominal Potongan (Rp):</label>
                        <input type="number" id="adj-print-amount" required min="0" value="{{ (int) ($salesSummary?->print_adjustment_amount ?? 0) }}" style="width: 100%; padding: 6px 8px; border: 1px solid #f87171; border-radius: 3px; font-size: 12px; font-weight: bold; color: #b91c1c;">
                    </div>
                </div>
                <div style="margin-bottom: 8px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">Alasan Gangguan Cetak:</label>
                    <select id="adj-print-reason" style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px;">
                        <option value="paper_jam">📄 Kertas Macet / Tersangkut (Paper Jam)</option>
                        <option value="bad_ink">🖨️ Tinta Rusak / Bergaris</option>
                        <option value="wrong_doc">❌ Dibatalkan Pelanggan</option>
                    </select>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 3px;">Catatan Kasir:</label>
                    <textarea id="adj-print-notes" required rows="2" placeholder="Keterangan lembar rusak..." style="width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 11.5px;"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" onclick="closeModalPenyesuaianPrint()" style="padding: 6px 12px; border: 1px solid #cbd5e1; background: #fff; border-radius: 3px; font-size: 12px;">Batal</button>
                    <button type="submit" style="padding: 6px 16px; border: none; background: var(--pos-cyan); color: #fff; font-weight: 700; border-radius: 3px; font-size: 12px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 5: CEK PRINTING --}}
    <div id="modal-print-kertas" class="pos-modal-overlay">
        <div class="pos-modal-box" style="max-width: 580px;">
            <div class="pos-modal-header" style="background: var(--pos-green);">
                <span>🖨️ Data Cetak & Printer Canon</span>
                <button type="button" onclick="closeModal('modal-print-kertas')" style="background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 16px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px; text-align: center;">
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px; border-radius: 4px;">
                        <span style="font-size: 10px; color: #166534;">Total Halaman Dicetak:</span>
                        <div style="font-size: 16px; font-weight: bold; color: #15803d;">{{ $totalPrintPages }} Lembar</div>
                    </div>
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px; border-radius: 4px;">
                        <span style="font-size: 10px; color: #065f46;">Total Omset Cetak:</span>
                        <div style="font-size: 16px; font-weight: bold; color: #059669;">Rp {{ number_format($totalPrintIncome, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">Riwayat Print Terakhir:</div>
                <div style="max-height: 180px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 6px 8px; text-align: left;">Waktu</th>
                                <th style="padding: 6px 8px; text-align: left;">Dokumen</th>
                                <th style="padding: 6px 8px; text-align: center;">Lembar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($printerJobs as $job)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 6px 8px; color: #64748b;">{{ $job->created_at ? $job->created_at->format('H:i') : '-' }}</td>
                                    <td style="padding: 6px 8px;">{{ $job->document_name ?? 'Dokumen Print' }}</td>
                                    <td style="padding: 6px 8px; text-align: center; font-weight: bold;">{{ $job->page_count ?? 1 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding: 12px; text-align: center; color: #94a3b8;">Belum ada log print hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="padding: 10px 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                <button type="button" onclick="closeModal('modal-print-kertas')" style="padding: 6px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px; font-size: 12px;">Tutup</button>
            </div>
        </div>
    </div>

    {{-- SCRIPTS JAVASCRIPT --}}
    @php
        $catalogJsonData = json_encode($fnbProducts->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => strtoupper($p->name),
                'price' => (float) $p->base_price,
                'stock' => (int) ($p->stock_qty ?? 20),
                'category' => strtolower($p->product?->category?->code ?? 'snack'),
            ];
        })->values());
    @endphp
    <script>
        const fnbCatalog = {!! $catalogJsonData !!};

        let posCart = {};
        let lastSalePayload = null;

        // 1. ADD TO CART
        function addToCart(variantId) {
            const prod = fnbCatalog.find(p => p.id === variantId);
            if (!prod) return;

            if (!posCart[variantId]) {
                posCart[variantId] = {
                    id: prod.id,
                    name: prod.name,
                    price: prod.price,
                    qty: 1,
                    stock: prod.stock
                };
            } else {
                posCart[variantId].qty += 1;
            }

            renderCart();
            highlightRow(variantId);
        }

        function highlightRow(variantId) {
            const row = document.getElementById('terlaris-row-' + variantId);
            if (row) {
                row.style.background = '#dff0d8';
                setTimeout(() => { row.style.background = '#ffffff'; }, 200);
            }
        }

        function updateCartQty(variantId, delta) {
            if (!posCart[variantId]) return;
            posCart[variantId].qty += delta;
            if (posCart[variantId].qty <= 0) {
                delete posCart[variantId];
            }
            renderCart();
        }

        function removeFromCart(variantId) {
            if (posCart[variantId]) {
                delete posCart[variantId];
                renderCart();
            }
        }

        function clearCart() {
            posCart = {};
            const cashInput = document.getElementById('cart-cash-input');
            if (cashInput) cashInput.value = '';
            renderCart();
        }

        // 2. RENDER CART
        function renderCart() {
            const emptyState = document.getElementById('cart-empty-state');
            const populatedState = document.getElementById('cart-populated-state');
            const tbody = document.getElementById('cart-table-body');
            const badgeCount = document.getElementById('cart-item-count-badge');
            const totalDisplay = document.getElementById('cart-total-display');

            const items = Object.values(posCart);
            let totalQty = 0;
            let grandTotal = 0;

            if (items.length === 0) {
                if (emptyState) emptyState.style.display = 'flex';
                if (populatedState) populatedState.style.display = 'none';
                if (tbody) tbody.innerHTML = '';
                if (badgeCount) badgeCount.innerText = '0 item';
                if (totalDisplay) totalDisplay.innerText = 'Rp 0';
                calculateChange();
                return;
            }

            if (emptyState) emptyState.style.display = 'none';
            if (populatedState) populatedState.style.display = 'flex';
            if (tbody) tbody.innerHTML = '';

            items.forEach(item => {
                const subtotal = item.price * item.qty;
                totalQty += item.qty;
                grandTotal += subtotal;

                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #f1f5f9';
                tr.innerHTML = `
                    <td style="padding: 6px 8px; font-weight: 600; color: #1e293b;">
                        ${item.name}
                    </td>
                    <td style="padding: 6px 4px; text-align: center;">
                        <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 3px; overflow: hidden; background: #fff;">
                            <button type="button" onclick="updateCartQty(${item.id}, -1)" style="border: none; background: #f1f5f9; width: 20px; height: 20px; cursor: pointer; font-weight: bold;">-</button>
                            <span style="min-width: 24px; text-align: center; font-size: 11.5px; font-weight: bold;">${item.qty}</span>
                            <button type="button" onclick="updateCartQty(${item.id}, 1)" style="border: none; background: #f1f5f9; width: 20px; height: 20px; cursor: pointer; font-weight: bold;">+</button>
                        </div>
                    </td>
                    <td style="padding: 6px 8px; text-align: right; color: #64748b; font-family: monospace;">
                        Rp ${item.price.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 6px 8px; text-align: right; font-weight: bold; color: #008d4c; font-family: monospace;">
                        Rp ${subtotal.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 6px 4px; text-align: center;">
                        <button type="button" onclick="removeFromCart(${item.id})" style="border: none; background: none; color: #ef4444; cursor: pointer; font-size: 13px;">✕</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            if (badgeCount) badgeCount.innerText = `${totalQty} item`;
            if (totalDisplay) totalDisplay.innerText = `Rp ${grandTotal.toLocaleString('id-ID')}`;

            calculateChange();
        }

        // 3. HITUNG KEMBALIAN
        function calculateChange() {
            const items = Object.values(posCart);
            const total = items.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const cashInput = document.getElementById('cart-cash-input');
            const changeDisplay = document.getElementById('cart-change-display');

            if (!cashInput || !changeDisplay) return;

            const cash = parseFloat(cashInput.value) || 0;
            const change = Math.max(0, cash - total);
            changeDisplay.innerText = `Rp ${change.toLocaleString('id-ID')}`;
        }

        function onPaymentMethodChanged(method) {
            const container = document.getElementById('cash-change-container');
            if (container) {
                container.style.visibility = (method === 'cash') ? 'visible' : 'hidden';
            }
        }

        // 4. FILTER MENU / CARI PRODUK
        function filterPosMenu() {
            const query = (document.getElementById('pos-search-input')?.value || '').trim().toUpperCase();
            const rows = document.querySelectorAll('.terlaris-item');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                if (name.includes(query)) {
                    row.style.display = 'flex';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const notice = document.getElementById('pos-search-empty-notice');
            if (notice) notice.style.display = (visibleCount === 0) ? 'block' : 'none';
        }

        function filterByCategory(cat) {
            const rows = document.querySelectorAll('.terlaris-item');
            let visibleCount = 0;

            rows.forEach(row => {
                const itemCat = row.getAttribute('data-cat') || '';
                const name = row.getAttribute('data-name') || '';

                let match = false;
                if (cat === 'minuman') {
                    match = name.includes('KOPI') || name.includes('TEH') || name.includes('AQUA') || name.includes('DRINK') || name.includes('OCHA') || name.includes('FLORIDINA');
                } else if (cat === 'kertas') {
                    match = name.includes('A4') || name.includes('F4') || name.includes('PRINT') || name.includes('KERTAS');
                } else if (cat === 'snack') {
                    match = name.includes('BENG') || name.includes('ROTI') || name.includes('AOKA') || itemCat.includes('snack');
                } else {
                    match = itemCat.includes(cat);
                }

                if (match) {
                    row.style.display = 'flex';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const notice = document.getElementById('pos-search-empty-notice');
            if (notice) notice.style.display = (visibleCount === 0) ? 'block' : 'none';
        }

        // 5. ACCORDION & MINIMIZE CART
        function toggleHistoryAccordion() {
            const body = document.getElementById('history-accordion-content');
            const icon = document.getElementById('history-toggle-icon');
            if (!body) return;

            if (body.style.display === 'block') {
                body.style.display = 'none';
                if (icon) icon.innerText = '+';
            } else {
                body.style.display = 'block';
                if (icon) icon.innerText = '─';
            }
        }

        function toggleCartBody() {
            const body = document.getElementById('cart-body-wrap');
            if (body) {
                body.style.display = (body.style.display === 'none') ? 'flex' : 'none';
            }
        }

        let lastCashAmount = 0;
        let toastTimeout = null;

        function showSuccessToast(sale, cashReceived) {
            const toast = document.getElementById('pos-success-toast');
            if (!toast) return;

            document.getElementById('toast-invoice-num').innerText = 'Invoice: ' + (sale.invoice_number || '-');
            const total = Number(sale.total_fnb || 0);
            document.getElementById('toast-total').innerText = 'Rp ' + total.toLocaleString('id-ID');

            const cashVal = cashReceived || total;
            const changeVal = Math.max(0, cashVal - total);
            document.getElementById('toast-change').innerText = 'Rp ' + changeVal.toLocaleString('id-ID');

            const btnLastPrint = document.getElementById('btn-print-last-receipt');
            if (btnLastPrint) {
                btnLastPrint.style.display = 'inline-flex';
            }

            toast.style.display = 'block';

            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                hideSuccessToast();
            }, 8000);
        }

        function hideSuccessToast() {
            const toast = document.getElementById('pos-success-toast');
            if (toast) toast.style.display = 'none';
        }

        function printLastReceipt() {
            if (lastSalePayload) {
                showReceiptModal(lastSalePayload, lastCashAmount);
            } else {
                alert('Belum ada transaksi terakhir yang tercatat.');
            }
        }

        // 6. SIMPAN ORDER PENJUALAN
        async function submitPosOrder() {
            const items = Object.values(posCart);
            if (items.length === 0) {
                alert('Keranjang belanja masih kosong! Silakan pilih produk terlebih dahulu.');
                return;
            }

            const btn = document.getElementById('btn-save-pos-order');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const customerName = document.getElementById('cart-customer-input')?.value || 'Pelanggan Umum';
            const paymentMethod = document.getElementById('cart-payment-select')?.value || 'cash';
            const cashInput = document.getElementById('cart-cash-input');
            const total = items.reduce((sum, item) => sum + (item.price * item.qty), 0);
            const cashAmount = parseFloat(cashInput?.value) || total;

            const payload = {
                cart_items: items.map(i => ({
                    id: i.id,
                    qty: i.qty,
                    price: i.price,
                    name: i.name
                })),
                customer_name: customerName,
                payment_method: paymentMethod,
                cash_received: cashAmount,
                discount_percent: 0,
                discount_amount: 0
            };

            try {
                const response = await fetch('{{ route("presensi2.penjualan.transaksi") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    lastSalePayload = data.sale;
                    lastCashAmount = cashAmount;
                    clearCart();
                    showSuccessToast(data.sale, cashAmount);
                } else {
                    alert('Gagal menyimpan pesanan: ' + (data.message || 'Terjadi kesalahan sistem'));
                }
            } catch (err) {
                console.error(err);
                alert('Koneksi terputus saat menyimpan pesanan.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Simpan Order Penjualan';
            }
        }

        // 7. STRUK THERMAL MODAL
        function showReceiptModal(sale, cashReceived) {
            const modal = document.getElementById('modal-receipt');
            if (!modal) return;

            document.getElementById('receipt-invoice-num').innerText = sale.invoice_number || '-';
            document.getElementById('receipt-date').innerText = (new Date()).toLocaleString('id-ID');
            document.getElementById('receipt-customer').innerText = sale.customer_name || 'Umum';
            document.getElementById('receipt-total').innerText = 'Rp ' + Number(sale.total_fnb || 0).toLocaleString('id-ID');
            document.getElementById('receipt-method').innerText = (sale.payment_method || 'CASH').toUpperCase();

            const cashVal = cashReceived || Number(sale.total_fnb || 0);
            const changeVal = Math.max(0, cashVal - Number(sale.total_fnb || 0));
            document.getElementById('receipt-cash').innerText = 'Rp ' + cashVal.toLocaleString('id-ID');
            document.getElementById('receipt-change').innerText = 'Rp ' + changeVal.toLocaleString('id-ID');

            const itemsContainer = document.getElementById('receipt-items-list');
            itemsContainer.innerHTML = '';
            if (sale.items && sale.items.length > 0) {
                sale.items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'thermal-row';
                    div.innerHTML = `
                        <span>${item.qty}x ${item.item_name}</span>
                        <span>Rp ${Number(item.subtotal || 0).toLocaleString('id-ID')}</span>
                    `;
                    itemsContainer.appendChild(div);
                });
            }

            modal.style.display = 'flex';
        }

        function closeReceiptModal() {
            const modal = document.getElementById('modal-receipt');
            if (modal) modal.style.display = 'none';
        }

        // TOGGLE & RUN MODE SIMULASI POS
        function togglePosSimulation() {
            const panel = document.getElementById('pos-simulation-container');
            const label = document.getElementById('btn-toggle-sim-label');
            if (!panel) return;
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
                if (label) label.innerText = 'Tutup Simulasi';
                localStorage.setItem('pos_simulation_visible', '1');
            } else {
                panel.style.display = 'none';
                if (label) label.innerText = 'Mode Simulasi';
                localStorage.setItem('pos_simulation_visible', '0');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const panel = document.getElementById('pos-simulation-container');
            const label = document.getElementById('btn-toggle-sim-label');
            if (!panel) return;
            const stored = localStorage.getItem('pos_simulation_visible');
            if (stored === '0') {
                panel.style.display = 'none';
                if (label) label.innerText = 'Mode Simulasi';
            } else {
                panel.style.display = 'block';
                if (label) label.innerText = 'Tutup Simulasi';
            }
        });

        async function runPosSimulation(scenario) {
            if (!Array.isArray(fnbCatalog) || fnbCatalog.length === 0) {
                alert('Katalog produk belum dimuat atau kosong.');
                return;
            }

            if (scenario === 'fill_cart') {
                clearCart();
                addToCart(fnbCatalog[0].id);
                if (fnbCatalog.length > 1) {
                    addToCart(fnbCatalog[1].id);
                }
                const notif = document.createElement('div');
                notif.style = "position:fixed;bottom:24px;right:24px;background:#6d28d9;color:#ffffff;padding:12px 18px;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:99999;font-weight:700;font-size:12.5px;display:flex;align-items:center;gap:8px;";
                notif.innerHTML = '<span>🛒</span> <span>Keranjang belanja berhasil diisi 2 item produk pengujian!</span>';
                document.body.appendChild(notif);
                setTimeout(() => notif.remove(), 2500);
                return;
            }

            if (scenario === 'aqua_cash') {
                let target = fnbCatalog.find(p => (p.name || '').includes('AQUA')) || fnbCatalog[0];
                if (!target) {
                    alert('Produk tidak ditemukan di katalog POS.');
                    return;
                }

                clearCart();
                posCart[target.id] = {
                    id: target.id,
                    name: target.name,
                    price: target.price,
                    qty: 1,
                    stock: target.stock
                };
                renderCart();

                const customerInput = document.getElementById('cart-customer-input');
                if (customerInput) customerInput.value = 'Simulasi Tester (Aqua)';
                const paymentSelect = document.getElementById('cart-payment-select');
                if (paymentSelect) paymentSelect.value = 'cash';
                const cashInput = document.getElementById('cart-cash-input');
                if (cashInput) cashInput.value = target.price;

                if (confirm(`Jalankan simulasi beli 1x ${target.name} seharga Rp ${Number(target.price).toLocaleString('id-ID')} via Tunai?`)) {
                    await submitPosCheckout();
                }
                return;
            }

            if (scenario === 'print_qris') {
                let paper = fnbCatalog.find(p => (p.name || '').includes('A4') || (p.category || '').includes('paper')) || fnbCatalog[0];
                let drink = fnbCatalog.find(p => p.id !== paper?.id) || fnbCatalog[1] || paper;

                clearCart();
                posCart[paper.id] = {
                    id: paper.id,
                    name: paper.name,
                    price: paper.price,
                    qty: 2,
                    stock: paper.stock
                };
                if (drink && drink.id !== paper.id) {
                    posCart[drink.id] = {
                        id: drink.id,
                        name: drink.name,
                        price: drink.price,
                        qty: 1,
                        stock: drink.stock
                    };
                }
                renderCart();

                const customerInput = document.getElementById('cart-customer-input');
                if (customerInput) customerInput.value = 'Simulasi QRIS (Print + Minum)';
                const paymentSelect = document.getElementById('cart-payment-select');
                if (paymentSelect) paymentSelect.value = 'qris';
                const total = Object.values(posCart).reduce((sum, item) => sum + (item.price * item.qty), 0);
                const cashInput = document.getElementById('cart-cash-input');
                if (cashInput) cashInput.value = total;

                if (confirm(`Jalankan simulasi order ${Object.values(posCart).length} item seharga Rp ${Number(total).toLocaleString('id-ID')} via QRIS?`)) {
                    await submitPosCheckout();
                }
                return;
            }
        }

        function printReceipt() {
            window.print();
        }

        function reprintFromHistory(sale) {
            showReceiptModal(sale, Number(sale.total_fnb || 0));
        }

        // 8. VOID / BATAL PESANAN
        async function promptCancelSale(saleId, invoiceNum) {
            const pin = prompt(`Batalkan Nota ${invoiceNum}?\nMasukkan PIN Supervisor / Admin SoD:`);
            if (pin === null) return;
            if (!pin.trim()) {
                alert('PIN Supervisor wajib diisi.');
                return;
            }

            try {
                const response = await fetch(`{{ url('/presensi2/penjualan/transaksi') }}/${saleId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ supervisor_pin: pin })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    alert('Nota ' + invoiceNum + ' berhasil dibatalkan (VOID).');
                    window.location.reload();
                } else {
                    alert('Gagal membatalkan nota: ' + (data.message || 'PIN Supervisor salah.'));
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            }
        }

        // 9. MODALS UTILITY
        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'none';
        }

        function openModalBilling() {
            const modal = document.getElementById('modal-billing');
            if (modal) modal.style.display = 'flex';
        }

        function openModalPrintKertas() {
            const modal = document.getElementById('modal-print-kertas');
            if (modal) modal.style.display = 'flex';
        }

        function openModalPenyesuaianBilling() {
            const modal = document.getElementById('modal-penyesuaian-billing');
            if (modal) modal.style.display = 'flex';
        }

        function closeModalPenyesuaianBilling() {
            closeModal('modal-penyesuaian-billing');
        }

        function openModalPenyesuaianPrint() {
            const modal = document.getElementById('modal-penyesuaian-print');
            if (modal) modal.style.display = 'flex';
        }

        function closeModalPenyesuaianPrint() {
            closeModal('modal-penyesuaian-print');
        }

        // KUNCI BILLING & KUNCI PRINT LANGSUNG (ANTI-BUKA KEMBALI OLEH OPERATOR)
        async function confirmLockBilling(currentAmount) {
            if (!confirm(`Kunci data billing shift sebesar Rp ${currentAmount.toLocaleString('id-ID')}?\n\nPERINGATAN: Setelah dikunci, data billing shift terkunci permanen dan operator tidak dapat membuka kembali!`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("presensi2.penjualan.kunci-billing") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'lock',
                        locked_billing_amount: currentAmount
                    })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    alert('Data billing shift BERHASIL DIKUNCI sebesar Rp ' + currentAmount.toLocaleString('id-ID') + '.\nData tidak dapat diubah kembali oleh operator.');
                    window.location.reload();
                } else {
                    alert('Gagal mengunci billing: ' + (data.message || 'Terjadi kesalahan sistem.'));
                }
            } catch (err) {
                alert('Koneksi terputus saat mengunci billing.');
            }
        }

        async function confirmLockPrint(pages, currentAmount) {
            if (!confirm(`Kunci data cetak print shift (${pages} lembar, Rp ${currentAmount.toLocaleString('id-ID')})?\n\nPERINGATAN: Setelah dikunci, data print shift terkunci permanen dan operator tidak dapat membuka kembali!`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("presensi2.penjualan.kunci-print") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'lock',
                        locked_print_pages: pages,
                        locked_print_amount: currentAmount
                    })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    alert(`Data print shift BERHASIL DIKUNCI (${pages} lembar, Rp ${currentAmount.toLocaleString('id-ID')}).\nData tidak dapat diubah kembali oleh operator.`);
                    window.location.reload();
                } else {
                    alert('Gagal mengunci print: ' + (data.message || 'Terjadi kesalahan sistem.'));
                }
            } catch (err) {
                alert('Koneksi terputus saat mengunci print.');
            }
        }

        function recalculateNetBilling() {
            const rawEl = document.getElementById('adj-raw-billing');
            const dedEl = document.getElementById('adj-deduction-amount');
            const netEl = document.getElementById('adj-net-billing');
            if (rawEl && dedEl && netEl) {
                const raw = parseFloat(rawEl.value) || 0;
                const ded = parseFloat(dedEl.value) || 0;
                netEl.value = Math.max(0, raw - ded);
            }
        }

        async function submitPenyesuaianBilling(e) {
            e.preventDefault();
            const raw = parseFloat(document.getElementById('adj-raw-billing').value) || 0;
            const ded = parseFloat(document.getElementById('adj-deduction-amount').value) || 0;
            const reason = document.getElementById('adj-reason').value;
            const notes = document.getElementById('adj-notes').value;

            try {
                const res = await fetch('{{ route("presensi2.penjualan.penyesuaian-billing") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        raw_billing_amount: raw,
                        billing_adjustment_amount: ded,
                        billing_adjustment_reason: reason,
                        billing_adjustment_notes: notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Penyesuaian billing berhasil disimpan.');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (err) {
                alert('Koneksi gagal.');
            }
        }

        async function submitPenyesuaianPrint(e) {
            e.preventDefault();
            const pages = parseInt(document.getElementById('adj-print-pages').value) || 0;
            const amount = parseFloat(document.getElementById('adj-print-amount').value) || 0;
            const reason = document.getElementById('adj-print-reason').value;
            const notes = document.getElementById('adj-print-notes').value;

            try {
                const res = await fetch('{{ route("presensi2.penjualan.penyesuaian-print") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        print_adjustment_pages: pages,
                        print_adjustment_amount: amount,
                        print_adjustment_reason: reason,
                        print_adjustment_notes: notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Penyesuaian print berhasil disimpan.');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            } catch (err) {
                alert('Koneksi gagal.');
            }
        }

        // KEYBOARD SHORTCUTS
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptModal();
                closeModalPenyesuaianBilling();
                closeModalPenyesuaianPrint();
                closeModal('modal-billing');
                closeModal('modal-print-kertas');
            } else if (e.key === 'Enter') {
                const receiptModal = document.getElementById('modal-receipt');
                if (receiptModal && receiptModal.style.display === 'flex') {
                    e.preventDefault();
                    printReceipt();
                }
            } else if (e.key === 'F2') {
                e.preventDefault();
                const search = document.getElementById('pos-search-input');
                if (search) search.focus();
            }
        });
    </script>
</body>
</html>
