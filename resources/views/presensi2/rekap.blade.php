@extends('layouts.presensi2')

@section('title', 'Rekap Penjualan & Laporan Shift')

@section('content')
<div class="rekap-page">
    <style>
        .rekap-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-glass);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35);
        }
        .rekap-stat-card {
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .rekap-stat-card:hover {
            transform: translateY(-2px);
        }
        .rekap-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }
        .rekap-table th {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-muted);
            font-weight: 600;
            text-align: left;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border-glass);
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .rekap-table td {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-main);
            vertical-align: middle;
        }
        .rekap-table tr:last-child td {
            border-bottom: none;
        }
        .rekap-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        .filter-chip {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            border: 1px solid var(--border-glass);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .filter-chip.active, .filter-chip:hover {
            background: rgba(14, 165, 233, 0.18);
            color: #38bdf8;
            border-color: rgba(14, 165, 233, 0.4);
        }

        /* PRINT STYLES FOR THERMAL RECEIPT / REPORT */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .mobile-header, .mobile-bottom-nav, .no-print, #mgk-notification-modal {
                display: none !important;
            }
            .mobile-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .rekap-card, .rekap-stat-card {
                background: #ffffff !important;
                color: #000000 !important;
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
            }
            .rekap-table th, .rekap-table td {
                color: #000000 !important;
                border-bottom: 1px solid #ccc !important;
            }
            .print-receipt-only {
                display: block !important;
                width: 100%;
                max-width: 380px;
                margin: 0 auto;
                font-family: 'Courier New', Courier, monospace;
            }
        }
    </style>

    {{-- HEADER REKAPITULASI SHIFT --}}
    <div class="rekap-card" style="border-color: rgba(14, 165, 233, 0.3); background: linear-gradient(135deg, rgba(14, 165, 233, 0.06), rgba(139, 92, 246, 0.06));">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, #0284c7, #0ea5e9); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; box-shadow: 0 6px 18px rgba(14, 165, 233, 0.4); flex-shrink: 0;">
                    📊
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                            Rekapitulasi Shift & Buku Kasir
                        </span>
                        @if($salesSummary && $salesSummary->status === 'closed')
                            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 10px;">
                                ✅ Shift Ditutup ({{ $salesSummary->closed_at?->format('H:i') ?? '-' }})
                            </span>
                        @else
                            <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 10px;">
                                ⏳ Shift Sedang Berjalan
                            </span>
                        @endif
                    </div>
                    <h2 style="font-size: 19px; font-weight: 700; color: #fff; margin-top: 3px;">
                        Laporan Rekapitulasi Penjualan
                    </h2>
                </div>
            </div>

            <div class="no-print" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" onclick="window.print()" class="btn" style="padding: 8px 14px; font-size: 12.5px; width: auto; background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid var(--border-glass);">
                    <span>🖨️</span> Cetak Rekap
                </button>
                <a href="{{ route('presensi2.penjualan') }}" class="btn btn-emerald" style="padding: 8px 14px; font-size: 12.5px; width: auto; text-decoration: none;">
                    <span>🛒</span> Kasir Penjualan
                </a>
            </div>
        </div>

        {{-- INFO METADATA SHIFT --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-glass); font-size: 12px;">
            <div>
                <span style="color: var(--text-sub); display: block;">Tanggal Operasional:</span>
                <strong style="color: #e2e8f0; font-size: 13px;">{{ \Carbon\Carbon::parse($workDate)->translatedFormat('l, d F Y') }}</strong>
            </div>
            <div>
                <span style="color: var(--text-sub); display: block;">Shift Kerja:</span>
                <strong style="color: #38bdf8; font-size: 13px;">{{ $activeShift?->name ?? 'Shift Aktif' }} ({{ substr($activeShift?->start_time ?? '00:00', 0, 5) }} - {{ substr($activeShift?->end_time ?? '00:00', 0, 5) }})</strong>
            </div>
            <div>
                <span style="color: var(--text-sub); display: block;">Operator Bertugas:</span>
                <strong style="color: #c4b5fd; font-size: 13px;">{{ $currentEmployee->name }} ({{ $currentEmployee->code ?? 'OP' }})</strong>
            </div>
            <div>
                <span style="color: var(--text-sub); display: block;">Outlet / Cabang:</span>
                <strong style="color: #34d399; font-size: 13px;">📍 {{ $currentEmployee->branch->name ?? 'MGK Network' }}</strong>
            </div>
        </div>
    </div>

    {{-- FILTER TANGGAL (NO PRINT) --}}
    <div class="rekap-card no-print" style="padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">📅 Tanggal Rekap:</span>
            <form action="{{ route('presensi2.rekap') }}" method="GET" style="display: flex; align-items: center; gap: 6px;">
                <input type="date" name="date" value="{{ \Carbon\Carbon::parse($workDate)->format('Y-m-d') }}" 
                       onchange="this.form.submit()"
                       style="background: rgba(17, 24, 39, 0.9); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; padding: 6px 10px; font-size: 12px; font-family: inherit; outline: none;">
                @if($dateParam)
                    <a href="{{ route('presensi2.rekap') }}" class="filter-chip" title="Kembali ke Hari Ini">
                        Hari Ini
                    </a>
                @endif
            </form>
        </div>
        <div style="font-size: 12px; color: var(--text-muted);">
            Waktu Server: <span style="color: #38bdf8; font-family: monospace;">{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    {{-- RINGKASAN KEUANGAN SHIFT (METRIC CARDS) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 16px;">
        {{-- Total Omset Kotor --}}
        <div class="rekap-stat-card" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25);">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">Total Omset Kotor Shift</span>
                <div style="font-size: 24px; font-weight: 800; color: #34d399; font-family: 'Outfit', monospace; margin-top: 4px;">
                    Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div style="font-size: 11px; color: #6ee7b7; margin-top: 8px; display: flex; align-items: center; justify-content: space-between;">
                <span>F&B + Billing + Print</span>
                <span>{{ $totalFnbTransactions }} Transaksi F&B</span>
            </div>
        </div>

        {{-- Omset F&B --}}
        <div class="rekap-stat-card" style="background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.25);">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">1. Pendapatan F&B (Cafe)</span>
                <div style="font-size: 22px; font-weight: 800; color: #fbbf24; font-family: 'Outfit', monospace; margin-top: 4px;">
                    Rp {{ number_format($totalFnbIncome, 0, ',', '.') }}
                </div>
            </div>
            <div style="font-size: 11px; color: #fde68a; margin-top: 8px; display: flex; align-items: center; justify-content: space-between;">
                <span>Makanan & Minuman</span>
                <span>{{ $fnbItemSummary->sum('total_qty') }} pcs terjual</span>
            </div>
        </div>

        {{-- Omset Internet Warnet --}}
        <div class="rekap-stat-card" style="background: rgba(6, 182, 212, 0.08); border-color: rgba(6, 182, 212, 0.25);">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">2. Pendapatan Internet (Billing)</span>
                <div style="font-size: 22px; font-weight: 800; color: #22d3ee; font-family: 'Outfit', monospace; margin-top: 4px;">
                    Rp {{ number_format($totalInternetIncome, 0, ',', '.') }}
                </div>
            </div>
            <div style="font-size: 11px; color: #a5f3fc; margin-top: 8px; display: flex; align-items: center; justify-content: space-between;">
                <span>{{ $isBillingLocked ? '🔒 Nilai Terkunci' : '🔄 Server Otomatis' }}</span>
                <span>{{ $occupiedPcCount }}/{{ $totalPcCount }} PC Aktif</span>
            </div>
        </div>

        {{-- Omset Print Kertas --}}
        <div class="rekap-stat-card" style="background: rgba(139, 92, 246, 0.08); border-color: rgba(139, 92, 246, 0.25);">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">3. Pendapatan Print & Kertas</span>
                <div style="font-size: 22px; font-weight: 800; color: #c4b5fd; font-family: 'Outfit', monospace; margin-top: 4px;">
                    Rp {{ number_format($totalPrintIncome, 0, ',', '.') }}
                </div>
            </div>
            <div style="font-size: 11px; color: #ddd6fe; margin-top: 8px; display: flex; align-items: center; justify-content: space-between;">
                <span>{{ $isPrintLocked ? '🔒 Nilai Terkunci' : 'Otomatis' }}</span>
                <span>{{ $totalPrintPages }} Lembar</span>
            </div>
        </div>
    </div>

    {{-- KAS FISIK DI LACI VS SELISIH --}}
    <div class="rekap-card" style="border-color: rgba(14, 165, 233, 0.25); padding: 18px 20px; background: rgba(15, 23, 42, 0.6);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <h3 style="font-size: 14px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>💵</span> Rekonsiliasi Kas Laci (Blind Cash Count & Audit SoD)
            </h3>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; font-size: 10px;">
                    🛡️ Blind Count Verified
                </span>
                @if($salesSummary)
                    <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd; font-size: 10px;">
                        Kasir: {{ $salesSummary->employee->name ?? $currentEmployee->name }}
                    </span>
                @endif
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
            <div style="background: rgba(0, 0, 0, 0.25); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; color: var(--text-muted);">Uang Fisik Kas Laci (Dihitung)</span>
                <div style="font-size: 20px; font-weight: 800; color: #38bdf8; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($cashInDrawer, 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(0, 0, 0, 0.25); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; color: var(--text-muted);">Target Kas Sistem</span>
                <div style="font-size: 20px; font-weight: 800; color: #94a3b8; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($systemExpectedCash ?? $totalGrossRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(0, 0, 0, 0.25); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; color: var(--text-muted);">Status Rekonsiliasi Kas</span>
                <div style="font-size: 18px; font-weight: 800; font-family: 'Outfit', monospace; margin-top: 2px; color: {{ $cashDifference == 0 ? '#34d399' : ($cashDifference > 0 ? '#38bdf8' : '#f87171') }};">
                    @if($cashDifference == 0)
                        ✅ Pas (Rp 0)
                    @elseif($cashDifference > 0)
                        📈 Lebih: +Rp {{ number_format($cashDifference, 0, ',', '.') }}
                    @else
                        📉 Kurang: -Rp {{ number_format(abs($cashDifference), 0, ',', '.') }}
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($cashDenominations) && is_array($cashDenominations))
        <div style="margin-top: 12px; padding: 10px 12px; background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-glass); border-radius: 10px;">
            <span style="font-size: 11px; color: #94a3b8; font-weight: 600; display: block; margin-bottom: 6px;">💵 Rincian Pecahan Uang Fisik Terhitung:</span>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                @if(!empty($cashDenominations['100k']))
                    <span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 3px 8px; border-radius: 6px;">100k: <strong>{{ $cashDenominations['100k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['50k']))
                    <span style="background: rgba(59, 130, 246, 0.15); color: #93c5fd; padding: 3px 8px; border-radius: 6px;">50k: <strong>{{ $cashDenominations['50k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['20k']))
                    <span style="background: rgba(16, 185, 129, 0.15); color: #6ee7b7; padding: 3px 8px; border-radius: 6px;">20k: <strong>{{ $cashDenominations['20k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['10k']))
                    <span style="background: rgba(245, 158, 11, 0.15); color: #fde68a; padding: 3px 8px; border-radius: 6px;">10k: <strong>{{ $cashDenominations['10k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['5k']))
                    <span style="background: rgba(217, 70, 239, 0.15); color: #f5d0fe; padding: 3px 8px; border-radius: 6px;">5k: <strong>{{ $cashDenominations['5k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['2k']))
                    <span style="background: rgba(148, 163, 184, 0.15); color: #cbd5e1; padding: 3px 8px; border-radius: 6px;">2k: <strong>{{ $cashDenominations['2k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['1k']))
                    <span style="background: rgba(203, 213, 225, 0.15); color: #e2e8f0; padding: 3px 8px; border-radius: 6px;">1k: <strong>{{ $cashDenominations['1k'] }}x</strong></span>
                @endif
                @if(!empty($cashDenominations['coins']))
                    <span style="background: rgba(255, 255, 255, 0.08); color: #fff; padding: 3px 8px; border-radius: 6px;">Koin: <strong>Rp {{ number_format((float) $cashDenominations['coins'], 0, ',', '.') }}</strong></span>
                @endif
            </div>
        </div>
        @endif

        @if($salesSummary?->handover_notes)
        <div style="margin-top: 14px; padding: 12px 14px; background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 10px; font-size: 12.5px;">
            <strong style="color: #c4b5fd; display: block; margin-bottom: 3px;">📝 Catatan Serah Terima Shift:</strong>
            <span style="color: #e2e8f0;">{{ $salesSummary->handover_notes }}</span>
        </div>
        @endif
    </div>

    {{-- RINCIAN PENJUALAN F&B (ITEM BREAKDOWN) --}}
    <div class="rekap-card">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <span>🍔</span> Rincian Item Penjualan F&B Shift Ini
            </span>
            <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                {{ $fnbItemSummary->count() }} Jenis Produk Terjual
            </span>
        </h3>

        @if($fnbItemSummary->isNotEmpty())
            <div style="overflow-x: auto;">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Nama Menu / Produk</th>
                            <th style="text-align: center;">Jumlah Terjual</th>
                            <th style="text-align: right;">Harga Satuan</th>
                            <th style="text-align: right;">Total Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fnbItemSummary as $index => $item)
                        <tr>
                            <td style="text-align: center; color: var(--text-muted); font-size: 11px;">{{ $index + 1 }}</td>
                            <td>
                                <strong style="color: #fff; font-size: 13.5px;">{{ $item->item_name }}</strong>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; font-weight: 700; font-size: 12px;">
                                    {{ $item->total_qty }} pcs
                                </span>
                            </td>
                            <td style="text-align: right; color: var(--text-muted); font-family: monospace;">
                                Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #fbbf24; font-family: monospace; font-size: 13.5px;">
                                Rp {{ number_format((float) $item->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(255, 255, 255, 0.03);">
                            <td colspan="2" style="font-weight: 700; color: #fff; text-transform: uppercase; font-size: 12px;">Total F&B Shift Ini:</td>
                            <td style="text-align: center; font-weight: 700; color: #38bdf8;">{{ $fnbItemSummary->sum('total_qty') }} pcs</td>
                            <td></td>
                            <td style="text-align: right; font-weight: 800; color: #34d399; font-size: 15px; font-family: monospace;">
                                Rp {{ number_format($totalFnbIncome, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 24px; color: var(--text-muted); background: rgba(0, 0, 0, 0.2); border-radius: 12px;">
                <span style="font-size: 28px; display: block; margin-bottom: 6px;">🛒</span>
                Belum ada transaksi penjualan F&B yang tercatat pada tanggal/shift ini.
            </div>
        @endif
    </div>

    {{-- AUDIT SISA STOK BARANG & SERAH TERIMA --}}
    <div class="rekap-card">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <span>📦</span> Audit Stok Barang (Sebelum vs Terjual vs Sesudahnya)
            </span>
            <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd;">
                Serah Terima Shift
            </span>
        </h3>

        {{-- 1. Kertas Print --}}
        <div style="margin-bottom: 16px;">
            <div style="font-size: 12px; font-weight: 700; color: #38bdf8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em;">
                📄 Kertas Print & Fotokopi:
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px;">
                @foreach($stockComparisons['kertas'] ?? [] as $kertas)
                <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <strong style="color: #fff; font-size: 13px;">{{ $kertas['name'] }}</strong>
                        <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; font-size: 10.5px;">{{ $kertas['rim_info'] ?? '' }}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; text-align: center; font-size: 11.5px;">
                        <div style="background: rgba(255,255,255,0.03); padding: 6px; border-radius: 6px;">
                            <span style="color: var(--text-muted); display: block; font-size: 10px;">Awal:</span>
                            <span style="color: #e2e8f0; font-weight: 700;">{{ $kertas['sebelumnya'] }}</span>
                        </div>
                        <div style="background: rgba(239,68,68,0.08); padding: 6px; border-radius: 6px;">
                            <span style="color: #fca5a5; display: block; font-size: 10px;">Terpakai:</span>
                            <span style="color: #ef4444; font-weight: 700;">-{{ $kertas['terpakai'] }}</span>
                        </div>
                        <div style="background: rgba(16,185,129,0.08); padding: 6px; border-radius: 6px;">
                            <span style="color: #6ee7b7; display: block; font-size: 10px;">Sisa Akhir:</span>
                            <span style="color: #34d399; font-weight: 700;">{{ $kertas['sesudahnya'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 2. Ringkasan Makanan & Minuman --}}
        <div>
            <div style="font-size: 12px; font-weight: 700; color: #fbbf24; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em;">
                🍿 Ringkasan Stok Makanan & Minuman:
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px; text-align: center;">
                    <span style="font-size: 11px; color: var(--text-muted);">Total Makanan Terjual</span>
                    <div style="font-size: 20px; font-weight: 800; color: #fbbf24; margin-top: 3px;">
                        {{ $stockComparisons['total_makanan_terjual'] ?? 0 }} pcs
                    </div>
                </div>
                <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px; text-align: center;">
                    <span style="font-size: 11px; color: var(--text-muted);">Total Minuman Terjual</span>
                    <div style="font-size: 20px; font-weight: 800; color: #38bdf8; margin-top: 3px;">
                        {{ $stockComparisons['total_minuman_terjual'] ?? 0 }} botol/cup
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIWAYAT REKAPITULASI SHIFT SEBELUMNYA (10 SHIFT TERAKHIR) --}}
    <div class="rekap-card no-print">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <span>📑</span> Riwayat Rekapitulasi Shift Terakhir
            </span>
            <span class="badge" style="background: rgba(255, 255, 255, 0.08); color: var(--text-muted);">
                {{ $recentSummaries->count() }} Data Shift Tersimpan
            </span>
        </h3>

        @if($recentSummaries->isNotEmpty())
            <div style="overflow-x: auto;">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Shift & Operator</th>
                            <th style="text-align: right;">Omset F&B</th>
                            <th style="text-align: right;">Omset Internet</th>
                            <th style="text-align: right;">Total Omset</th>
                            <th style="text-align: right;">Kas Laci</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSummaries as $rec)
                        <tr>
                            <td>
                                <strong style="color: #fff; display: block;">{{ $rec->work_date?->format('d/m/Y') }}</strong>
                                <span style="font-size: 10.5px; color: var(--text-sub);">Tutup: {{ $rec->closed_at?->format('H:i') ?? '-' }}</span>
                            </td>
                            <td>
                                <span style="color: #38bdf8; font-weight: 600;">{{ $rec->shift->name ?? 'Shift' }}</span>
                                <span style="font-size: 11px; color: var(--text-muted); display: block;">{{ $rec->employee->name ?? 'Operator' }}</span>
                            </td>
                            <td style="text-align: right; color: #fbbf24; font-family: monospace;">
                                Rp {{ number_format((float) $rec->total_fnb_sales, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: #22d3ee; font-family: monospace;">
                                Rp {{ number_format((float) $rec->total_internet_sales, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #34d399; font-family: monospace;">
                                Rp {{ number_format((float) $rec->total_revenue, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #fff; font-family: monospace;">
                                Rp {{ number_format((float) $rec->cash_in_drawer, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($rec->status === 'closed')
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">
                                        Closed
                                    </span>
                                @else
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; font-size: 10px;">
                                        Open
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                Belum ada riwayat rekapitulasi shift yang tersimpan sebelumnya.
            </div>
        @endif
    </div>

    {{-- NAVIGASI AKSI BAWAH (NO PRINT) --}}
    <div class="no-print" style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
        <a href="{{ route('presensi2.penjualan') }}" class="btn btn-emerald" style="padding: 14px; font-size: 14px; flex: 1; min-width: 200px; text-decoration: none;">
            <span>🛒</span> Buka Kasir Penjualan Shift
        </a>
        <a href="{{ route('presensi2.scan') }}" class="btn btn-rose" style="padding: 14px; font-size: 14px; flex: 1; min-width: 200px; text-decoration: none;">
            <span>🏁</span> Check Out Presensi Pulang
        </a>
    </div>

</div>
@endsection
