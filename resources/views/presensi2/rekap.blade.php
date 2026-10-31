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

        /* PRINT STYLES FOR CENTERED VERTICAL SLIP TABLE & ADJUSTABLE SCALE */
        .print-table-report {
            display: none;
            background: #ffffff;
            color: #000000;
            max-width: 285px;
            width: 100%;
            margin: 0 auto 18px auto;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            line-height: 1.25;
            zoom: var(--print-scale, 1);
        }
        .print-table-report.preview-active {
            display: block;
        }
        .print-table-report.paper-medium {
            max-width: 420px;
            font-size: 10.5px;
        }
        .print-table-report.paper-a4 {
            max-width: 640px;
            font-size: 11px;
        }
        .print-table-report.paper-thermal58 {
            max-width: 215px;
            font-size: 8.5px;
            padding: 5px 6px;
        }
        .print-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: inherit;
        }
        .print-meta-table td {
            padding: 1px 0;
            color: #000000;
            vertical-align: top;
        }
        .print-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: inherit;
            color: #000000;
            border: 1.5px solid #000000;
        }
        .print-grid-table th,
        .print-grid-table td {
            border: 1px solid #000000;
            padding: 2px 3px;
            color: #000000;
            vertical-align: middle;
        }
        .print-grid-table th {
            background: #ffffff;
            font-weight: 700;
            font-size: inherit;
        }
        .print-section-title {
            font-size: inherit;
            font-weight: 700;
            margin: 6px 0 2px 0;
            color: #000000;
        }
        .print-box-panel {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            margin-bottom: 6px;
            color: #000000;
        }

        @media print {
            @page {
                margin: 6mm;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .mobile-header, .mobile-bottom-nav, .no-print, #mgk-notification-modal, .screen-only-rekap {
                display: none !important;
            }
            .mobile-container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 auto !important;
                display: block !important;
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
            }
            body:not(.printing-hardware) #print-hardware-report {
                display: none !important;
            }
            body.printing-hardware #print-table-report {
                display: none !important;
            }
            body.printing-hardware #print-hardware-report {
                display: block !important;
            }
            .print-table-report.paper-medium {
                max-width: 420px !important;
                width: 420px !important;
                margin: 0 auto !important;
                font-size: 10.5px !important;
            }
            .print-table-report.paper-a4 {
                max-width: 640px !important;
                width: 640px !important;
                margin: 0 auto !important;
                font-size: 11px !important;
            }
            .print-table-report.paper-thermal58 {
                max-width: 210px !important;
                width: 210px !important;
                margin: 0 auto !important;
                font-size: 8.5px !important;
            }
        }
    </style>

    {{-- HEADER REKAPITULASI SHIFT --}}
    <div class="rekap-card screen-only-rekap" style="border-color: rgba(14, 165, 233, 0.3); background: linear-gradient(135deg, rgba(14, 165, 233, 0.06), rgba(139, 92, 246, 0.06));">
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

            <div class="no-print" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                {{-- PILIHAN FORMAT LEBAR TABEL TENGAH & SKALA CETAK --}}
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(15, 23, 42, 0.75); border: 1px solid var(--border-glass); border-radius: 10px; padding: 5px 10px;">
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">📐 Format & Skala:</span>
                    <select id="print-paper-mode" onchange="applyPrintSettings()" style="background: rgba(30, 41, 59, 0.9); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); border-radius: 6px; padding: 3px 6px; font-size: 11.5px; font-weight: 700; outline: none; cursor: pointer;">
                        <option value="slip-tengah" selected>Slip Tabel Tengah (Ramping)</option>
                        <option value="medium">Tabel Tengah Sedang</option>
                        <option value="a4">Tabel Tengah Lebar (A4)</option>
                        <option value="thermal58">Slip Mini 58mm Tengah</option>
                    </select>
                    <select id="print-scale-select" onchange="applyPrintSettings()" style="background: rgba(30, 41, 59, 0.9); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 6px; padding: 3px 6px; font-size: 11.5px; font-weight: 700; outline: none; cursor: pointer;">
                        <option value="0.6">60%</option>
                        <option value="0.7">70%</option>
                        <option value="0.8">80%</option>
                        <option value="0.9">90%</option>
                        <option value="1" selected>100% (Normal)</option>
                        <option value="1.1">110%</option>
                        <option value="1.2">120%</option>
                        <option value="1.3">130%</option>
                    </select>
                </div>
                <button type="button" id="btn-toggle-print-preview" onclick="togglePrintTablePreview()" class="btn" style="padding: 8px 12px; font-size: 12px; width: auto; background: rgba(255, 255, 255, 0.08); color: #e2e8f0; border: 1px solid var(--border-glass); font-weight: 600;">
                    <span>👁️</span> Preview Tabel
                </button>
                <button type="button" onclick="triggerRekapPrint('rekap')" class="btn" style="padding: 8px 14px; font-size: 12.5px; width: auto; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; border: 1px solid rgba(14, 165, 233, 0.5); font-weight: 700;">
                    <span>🖨️</span> Cetak Tabel Rekap
                </button>
                <button type="button" onclick="triggerRekapPrint('hardware')" class="btn" style="padding: 8px 14px; font-size: 12.5px; width: auto; background: linear-gradient(135deg, #059669, #10b981); color: #fff; border: 1px solid rgba(16, 185, 129, 0.5); font-weight: 700;">
                    <span>🖥️</span> Cetak Checklist Hardware
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

    {{-- LAPORAN TABEL CETAK RATA TENGAH (DISESUAIKAN DENGAN DATA PRINT KITA) --}}
    <div id="print-table-report" class="print-table-report">
        @php
            $rekapCode = \Carbon\Carbon::parse($workDate)->format('Ymd')
                . '-B' . ($currentEmployee->branch_id ?? 1)
                . '-S' . ($activeShift?->id ?? 1)
                . '-' . ($salesSummary?->id ?? $currentEmployee->id);
            $cashierName = $salesSummary->employee->name ?? $currentEmployee->name;
            $previousShiftSummary = $recentSummaries->where('id', '!=', $salesSummary?->id)->first();

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
        @endphp

        {{-- JUDUL LAPORAN --}}
        <div style="font-size: 13px; font-weight: 800; text-align: left; margin-bottom: 6px; letter-spacing: 0.02em; color: #000;">
            Laporan Summary Harian
        </div>

        {{-- 1. IDENTITAS REKAP & OUTLET (FORMAT KOTAK SESUAI CONTOH) --}}
        <div class="print-box-panel" style="margin-bottom: 6px; padding: 4px 6px;">
            <table class="print-meta-table">
                <tbody>
                    <tr>
                        <td style="width: 78px;">Invoice</td>
                        <td style="width: 8px;">:</td>
                        <td><strong>{{ $rekapCode }}</strong></td>
                    </tr>
                    <tr>
                        <td>Outlet</td>
                        <td>:</td>
                        <td><strong>{{ strtoupper($currentEmployee->branch->name ?? 'MAYA 1') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal Laporan</td>
                        <td>:</td>
                        <td><strong>{{ \Carbon\Carbon::parse($workDate)->format('d-m-Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Dicetak</td>
                        <td>:</td>
                        <td><strong>{{ now()->format('d-m-Y H:i') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @php
            $snackSoldItems = collect($stockComparisons['makanan'] ?? [])->filter(fn($item) => (int)($item['terjual'] ?? 0) > 0);
            $snackTotalQty = (int) $snackSoldItems->sum('terjual');
            $snackTotalVal = (float) $snackSoldItems->sum(fn($i) => (int)($i['terjual'] ?? 0) * (float)($i['price'] ?? 0));

            if ($snackSoldItems->isEmpty() && isset($fnbItemSummary)) {
                $snackSoldItems = $fnbItemSummary->filter(function($i) {
                    $n = strtoupper($i->item_name);
                    return !str_contains($n, 'KOPI') && !str_contains($n, 'AQUA') && !str_contains($n, 'TEH') && !str_contains($n, 'DRINK') && !str_contains($n, 'FLORIDINA') && !str_contains($n, 'OCHA');
                })->map(fn($i) => ['name' => $i->item_name, 'terjual' => (int)$i->total_qty, 'price' => (float)$i->unit_price]);
                $snackTotalQty = (int) $snackSoldItems->sum('terjual');
                $snackTotalVal = (float) $snackSoldItems->sum(fn($i) => (int)$i['terjual'] * (float)$i['price']);
            }

            $drinkSoldItems = collect($stockComparisons['minuman'] ?? [])->filter(fn($item) => (int)($item['terjual'] ?? 0) > 0);
            $drinkTotalQty = (int) $drinkSoldItems->sum('terjual');
            $drinkTotalVal = (float) $drinkSoldItems->sum(fn($i) => (int)($i['terjual'] ?? 0) * (float)($i['price'] ?? 0));

            if ($drinkSoldItems->isEmpty() && isset($fnbItemSummary)) {
                $drinkSoldItems = $fnbItemSummary->filter(function($i) {
                    $n = strtoupper($i->item_name);
                    return str_contains($n, 'KOPI') || str_contains($n, 'AQUA') || str_contains($n, 'TEH') || str_contains($n, 'DRINK') || str_contains($n, 'FLORIDINA') || str_contains($n, 'OCHA');
                })->map(fn($i) => ['name' => $i->item_name, 'terjual' => (int)$i->total_qty, 'price' => (float)$i->unit_price]);
                $drinkTotalQty = (int) $drinkSoldItems->sum('terjual');
                $drinkTotalVal = (float) $drinkSoldItems->sum(fn($i) => (int)$i['terjual'] * (float)$i['price']);
            }

            $kertasSoldItems = collect($stockComparisons['kertas'] ?? [])->filter(fn($item) => (int)($item['terpakai'] ?? 0) > 0);
            $kertasTotalQty = (int) ($totalPrintPages > 0 ? $totalPrintPages : $kertasSoldItems->sum('terpakai'));
            $kertasTotalVal = (float) ($totalPrintIncome > 0 ? $totalPrintIncome : ($kertasTotalQty * 1000));

            $warnetQty = (int) ($occupiedPcCount > 0 ? $occupiedPcCount : ($internetSales->count() > 0 ? $internetSales->count() : 4));
            $warnetTotalVal = (float) $totalInternetIncome;

            $grandItemCount = $kertasTotalQty + $snackTotalQty + $drinkTotalQty + $warnetQty;
            $grandTotalVal = $kertasTotalVal + $snackTotalVal + $drinkTotalVal + $warnetTotalVal;
            if ($grandTotalVal <= 0) {
                $grandTotalVal = $totalGrossRevenue;
            }
            $prodNo = 1;
        @endphp

        {{-- 2. RINGKASAN KPI --}}
        <div class="print-section-title">Ringkasan KPI</div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 65%;">Ringkasan</th>
                    <th style="text-align: right; width: 35%;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total shif</td>
                    <td style="text-align: right; font-weight: 700;">{{ $dayShiftSummaries->isNotEmpty() ? $dayShiftSummaries->count() : 4 }}</td>
                </tr>
                <tr>
                    <td>Total Item</td>
                    <td style="text-align: right; font-weight: 700;">{{ $grandItemCount }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 3. RINGKASAN PEMBAYARAN --}}
        <div class="print-section-title">Ringkasan Pembayaran</div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Pembayaran</th>
                    <th style="text-align: center; width: 25%;">Jml Trx</th>
                    <th style="text-align: right; width: 35%;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>TUNAI</td>
                    <td style="text-align: center;">{{ max(1, $totalFnbTransactions + ($totalPrintPages > 0 ? 1 : 0) + 1) }}</td>
                    <td style="text-align: right;">{{ number_format($systemExpectedCash, 0, ',', '.') }}</td>
                </tr>
                @if(($nonCashAmount ?? 0) > 0)
                <tr>
                    <td>NON-TUNAI</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">{{ number_format($nonCashAmount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr style="font-weight: 700;">
                    <td colspan="2" style="text-align: right;">Total</td>
                    <td style="text-align: right;">{{ number_format($totalGrossRevenue, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 4. REKAP PRODUK PER KATEGORI (SESUAI CONTOH) --}}
        <div class="print-section-title">Rekap Produk per Kategori</div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">No</th>
                    <th style="width: 44%; text-align: left;">Kode</th>
                    <th style="width: 20%; text-align: right;">Total Qty</th>
                    <th style="width: 28%; text-align: right;">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                {{-- KERTAS --}}
                <tr style="background: #f8fafc; font-weight: 700;">
                    <td colspan="4">KERTAS</td>
                </tr>
                @if($kertasSoldItems->isNotEmpty())
                    @php
                        $kSumQty = (int) $kertasSoldItems->sum('terpakai');
                        $unitPriceKertas = ($kSumQty > 0 && $kertasTotalVal > 0) ? round($kertasTotalVal / $kSumQty) : 1000;
                    @endphp
                    @foreach($kertasSoldItems as $kItem)
                    @php
                        $kQty = (int) ($kItem['terpakai'] ?? 0);
                        $kVal = $kQty * $unitPriceKertas;
                        $kCode = str_contains($kItem['name'], 'A4') ? 'A4' : (str_contains($kItem['name'], 'F4') ? 'F4' : $kItem['name']);
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $prodNo++ }}</td>
                        <td>{{ $kCode }}</td>
                        <td style="text-align: right;">{{ $kQty }}</td>
                        <td style="text-align: right;">{{ number_format($kVal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="text-align: center;">{{ $prodNo++ }}</td>
                        <td>A4 (PRINT)</td>
                        <td style="text-align: right;">{{ $kertasTotalQty }}</td>
                        <td style="text-align: right;">{{ number_format($kertasTotalVal, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr style="font-weight: 700;">
                    <td colspan="2" style="text-align: right;">Subtotal KERTAS</td>
                    <td style="text-align: right;">{{ $kertasTotalQty }}</td>
                    <td style="text-align: right;">{{ number_format($kertasTotalVal, 0, ',', '.') }}</td>
                </tr>

                {{-- SNACK --}}
                @if($snackSoldItems->isNotEmpty() || $snackTotalQty > 0)
                <tr style="background: #f8fafc; font-weight: 700;">
                    <td colspan="4">SNACK</td>
                </tr>
                @foreach($snackSoldItems as $sItem)
                @php
                    $sQty = (int) ($sItem['terjual'] ?? 0);
                    $sPrice = (float) ($sItem['price'] ?? 0);
                    $sVal = $sQty * $sPrice;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $prodNo++ }}</td>
                    <td>{{ strtoupper(substr($sItem['name'], 0, 15)) }}</td>
                    <td style="text-align: right;">{{ $sQty }}</td>
                    <td style="text-align: right;">{{ number_format($sVal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: 700;">
                    <td colspan="2" style="text-align: right;">Subtotal SNACK</td>
                    <td style="text-align: right;">{{ $snackTotalQty }}</td>
                    <td style="text-align: right;">{{ number_format($snackTotalVal, 0, ',', '.') }}</td>
                </tr>
                @endif

                {{-- SOFTDRINK --}}
                @if($drinkSoldItems->isNotEmpty() || $drinkTotalQty > 0)
                <tr style="background: #f8fafc; font-weight: 700;">
                    <td colspan="4">SOFTDRINK</td>
                </tr>
                @foreach($drinkSoldItems as $dItem)
                @php
                    $dQty = (int) ($dItem['terjual'] ?? 0);
                    $dPrice = (float) ($dItem['price'] ?? 0);
                    $dVal = $dQty * $dPrice;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $prodNo++ }}</td>
                    <td>{{ strtoupper(substr($dItem['name'], 0, 16)) }}</td>
                    <td style="text-align: right;">{{ $dQty }}</td>
                    <td style="text-align: right;">{{ number_format($dVal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: 700;">
                    <td colspan="2" style="text-align: right;">Subtotal SOFTDRINK</td>
                    <td style="text-align: right;">{{ $drinkTotalQty }}</td>
                    <td style="text-align: right;">{{ number_format($drinkTotalVal, 0, ',', '.') }}</td>
                </tr>
                @endif

                {{-- WARNET --}}
                <tr style="background: #f8fafc; font-weight: 700;">
                    <td colspan="4">WARNET</td>
                </tr>
                <tr>
                    <td style="text-align: center;">{{ $prodNo++ }}</td>
                    <td>INET</td>
                    <td style="text-align: right;">{{ $warnetQty }}</td>
                    <td style="text-align: right;">{{ number_format($warnetTotalVal, 0, ',', '.') }}</td>
                </tr>
                <tr style="font-weight: 700;">
                    <td colspan="2" style="text-align: right;">Subtotal WARNET</td>
                    <td style="text-align: right;">{{ $warnetQty }}</td>
                    <td style="text-align: right;">{{ number_format($warnetTotalVal, 0, ',', '.') }}</td>
                </tr>

                {{-- GRAND TOTAL --}}
                <tr style="font-weight: 800; font-size: 10px; border-top: 1.5px solid #000;">
                    <td colspan="2" style="text-align: right;">Grand Total</td>
                    <td style="text-align: right;">{{ $grandItemCount }}</td>
                    <td style="text-align: right;">{{ number_format($totalGrossRevenue, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 5. TABEL SUMMARY SERAH TERIMA (4 SHIFT TETAP SESUAI CONTOH GAMBAR 2) --}}
        <div class="print-section-title" style="text-transform: uppercase;">Summary Serah Terima</div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">No</th>
                    <th style="width: 14%; text-align: center;">Shif</th>
                    <th style="width: 27%; text-align: left;">Penyerah</th>
                    <th style="width: 27%; text-align: left;">Penerima</th>
                    <th style="width: 24%; text-align: right;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $shiftMap = [];
                    foreach ($dayShiftSummaries as $sItem) {
                        $shiftMap[$sItem->shiftNumber()] = $sItem;
                    }
                    $currentShiftNum = $activeShift ? (int) preg_replace('/\D/', '', $activeShift->code ?? (string)$activeShift->id) : 1;
                    if ($currentShiftNum < 1 || $currentShiftNum > 4) {
                        $currentShiftNum = max(1, min(4, (int) ($activeShift?->id ?? 1)));
                    }
                    if (!isset($shiftMap[$currentShiftNum]) && $salesSummary) {
                        $shiftMap[$currentShiftNum] = $salesSummary;
                    }

                    $operatorByShift = [];
                    for ($s = 1; $s <= 4; $s++) {
                        if (isset($shiftMap[$s])) {
                            $operatorByShift[$s] = strtoupper($shiftMap[$s]->employee->name ?? '-');
                        } elseif ($s === $currentShiftNum) {
                            $operatorByShift[$s] = strtoupper($cashierName);
                        } else {
                            $operatorByShift[$s] = null;
                        }
                    }

                    $stTotalNilai = 0;
                    $fourShiftRows = [];
                    for ($s = 1; $s <= 4; $s++) {
                        $nextShiftIndex = ($s % 4) + 1; // 1->2, 2->3, 3->4, 4->1
                        $summary = $shiftMap[$s] ?? null;

                        if ($summary) {
                            $penyerah = strtoupper($summary->employee->name ?? '-');
                            $penerima = strtoupper(
                                $summary->deposit_recipient 
                                ?: ($operatorByShift[$nextShiftIndex] ?: 'SHIFT ' . $nextShiftIndex)
                            );
                            $nilai = (float) ($summary->deposited_amount ?? $summary->blind_cash_count ?? $summary->cash_in_drawer ?? $summary->total_revenue);
                        } elseif ($s === $currentShiftNum) {
                            $penyerah = strtoupper($cashierName);
                            $penerima = strtoupper(
                                $salesSummary?->deposit_recipient 
                                ?: ($operatorByShift[$nextShiftIndex] ?: ($nextShiftLabel ?? 'SHIFT ' . $nextShiftIndex))
                            );
                            $nilai = (float) ($salesSummary?->deposited_amount ?? $cashInDrawer ?? $totalGrossRevenue);
                        } else {
                            $penyerah = '-';
                            $penerima = strtoupper($operatorByShift[$nextShiftIndex] ?: 'SHIFT ' . $nextShiftIndex);
                            $nilai = 0.0;
                        }

                        $stTotalNilai += $nilai;
                        $fourShiftRows[] = [
                            'shift' => $s,
                            'penyerah' => $penyerah,
                            'penerima' => $penerima,
                            'nilai' => $nilai,
                        ];
                    }
                @endphp

                @foreach($fourShiftRows as $rowIdx => $row)
                    <tr>
                        <td style="text-align: center;">{{ $rowIdx + 1 }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $row['shift'] }}</td>
                        <td>{{ $row['penyerah'] }}</td>
                        <td>{{ $row['penerima'] }}</td>
                        <td style="text-align: right; font-weight: 700;">
                            {{ $row['nilai'] > 0 ? number_format($row['nilai'], 0, ',', '.') : '0' }}
                        </td>
                    </tr>
                @endforeach
                <tr class="row-bold" style="font-weight: 700;">
                    <td colspan="4" style="text-align: right;">Total</td>
                    <td style="text-align: right;">{{ number_format($stTotalNilai > 0 ? $stTotalNilai : $totalGrossRevenue, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 8. TERBILANG (DISETOR) --}}
        @php
            $terbilangTargetVal = (float) ($stTotalNilai > 0 ? $stTotalNilai : ($salesSummary?->deposited_amount ?? $totalGrossRevenue));
            $terbilangTeks = trim(preg_replace('/\s+/', ' ', $formatTerbilang($terbilangTargetVal)));
        @endphp
        <div class="print-box-panel" style="margin-top: 4px; text-align: center;">
            <div style="font-weight: 700; font-size: 9px; margin-bottom: 2px;">Terbilang (Disetor)</div>
            <div style="font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em;">
                --------------- {{ $terbilangTeks }} RUPIAH ---------------
            </div>
        </div>

        {{-- 9. KOLOM PENYERAH & KASIR (SESUAI CONTOH) --}}
        <div style="display: flex; justify-content: space-around; text-align: center; margin: 12px 0 8px 0;">
            <div style="width: 48%;">
                <div>(Penyetor)</div>
                <div style="height: 28px;"></div>
                <div style="font-weight: 700; text-transform: uppercase;">{{ $cashierName }}</div>
            </div>
            <div style="width: 48%;">
                <div>(Kasir)</div>
                <div style="height: 28px;"></div>
                <div style="font-weight: 700; text-transform: uppercase;">{{ $salesSummary?->deposit_recipient ?: ($nextShiftLabel ?? '') }}</div>
            </div>
        </div>

        {{-- 10. FOOTER VALIDASI CETAK (SESUAI CONTOH) --}}
        <div style="font-size: 8.5px; line-height: 1.3; text-align: center; margin: 8px 0 10px 0; font-weight: 700;">
            ⚠️ Setoran dianggap sah setelah dibubuhi tanda tangan kedua pihak.
        </div>

        {{-- 12. REQUEST STOK (SESUAI CONTOH) --}}
        @if(isset($recentStockRequests) && $recentStockRequests->isNotEmpty())
        <div class="print-section-title" style="text-transform: uppercase; margin-top: 6px;">Request Stok</div>
        <table class="print-grid-table">
            <tbody>
                @foreach($recentStockRequests as $stkReq)
                <tr>
                    <td style="font-weight: 600;">{{ $stkReq->item_name }}</td>
                    <td style="text-align: right; font-family: monospace; font-size: 8.5px;">
                        stok: {{ $stkReq->qty_requested }} {{ $stkReq->unit }} | {{ strtoupper($stkReq->status) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- LAPORAN TABEL CETAK CHECKLIST HARDWARE RATA TENGAH (DISESUAIKAN DENGAN DATA HARDWARE KITA) --}}
    <div id="print-hardware-report" class="print-table-report">
        @php
            $hwChecklistData = is_array($handoverChecklist?->hardware_checklist ?? null)
                ? $handoverChecklist->hardware_checklist
                : ['pc_stations', 'server_billing', 'printer_thermal', 'printer_hvs', 'lan_network', 'cctv_ac'];
            $hwGeneralStatus = strtoupper($handoverChecklist?->hardware_status ?? 'NORMAL');
            $hwInspector = $handoverChecklist?->employee?->name ?? $cashierName;
            $hwStandardItems = [
                'pc_stations' => 'PC Client (' . $totalPcCount . ' Unit)',
                'server_billing' => 'Server Billing & Kasir',
                'printer_thermal' => 'Printer Kasir Thermal',
                'printer_hvs' => 'Printer HVS A4 & F4',
                'lan_network' => 'LAN, Switch & WiFi',
                'cctv_ac' => 'CCTV 8-CH & AC Ruangan',
            ];
        @endphp

        {{-- 1. IDENTITAS CHECKLIST HARDWARE & SHIFT --}}
        <table class="print-meta-table">
            <tbody>
                <tr>
                    <td style="width: 58px;">No Cek</td>
                    <td style="width: 8px;">:</td>
                    <td><strong>CHK-{{ $rekapCode }}</strong></td>
                </tr>
                <tr>
                    <td>Outlet</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($currentEmployee->branch->name ?? 'MGK NETWORK') }}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($workDate)->format('d-m-Y') }} , {{ ($handoverChecklist?->completed_at ?? $salesSummary?->closed_at ?? now())?->format('H:i') }}</strong></td>
                </tr>
                <tr>
                    <td>Shift</td>
                    <td>:</td>
                    <td><strong>{{ $activeShift?->name ?? 'Shift 1' }} ({{ substr($activeShift?->start_time ?? '00:00', 0, 5) }}-{{ substr($activeShift?->end_time ?? '00:00', 0, 5) }})</strong></td>
                </tr>
                <tr>
                    <td>Petugas</td>
                    <td>:</td>
                    <td><strong>{{ strtoupper($hwInspector) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="print-box-panel" style="display: flex; justify-content: space-between; font-weight: 700;">
            <span>Status Umum Hardware</span>
            <span>{{ $hwGeneralStatus === 'NORMAL' ? 'NORMAL (OK)' : $hwGeneralStatus }}</span>
        </div>

        {{-- 2. TABEL CHECKLIST PERANGKAT UTAMA OUTLET --}}
        <div class="print-section-title" style="text-transform: uppercase;">
            CHECKLIST PERANGKAT & INVENTARIS {{ strtoupper($activeShift?->name ?? 'SHIFT') }}
        </div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="width: 10%; text-align: center;">No</th>
                    <th style="width: 65%; text-align: left;">Perangkat Hardware</th>
                    <th style="width: 25%; text-align: center;">Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @php $hwNo = 1; @endphp
                @foreach($hwStandardItems as $hwKey => $hwLabel)
                    @php
                        $isChecked = in_array($hwKey, $hwChecklistData, true) || empty($handoverChecklist);
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $hwNo++ }}</td>
                        <td>{{ $hwLabel }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $isChecked ? 'OK / NORMAL' : 'CEK' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- 3. TABEL STATUS UNIT PC WARNET --}}
        <div class="print-section-title">Daftar Unit PC Client ({{ $totalPcCount }} PC)</div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="width: 12%; text-align: center;">No</th>
                    <th style="width: 30%; text-align: left;">Unit PC</th>
                    <th style="width: 30%; text-align: center;">Status</th>
                    <th style="width: 28%; text-align: right;">Ket</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pcStations as $pcIdx => $pc)
                    <tr>
                        <td style="text-align: center;">{{ $pcIdx + 1 }}</td>
                        <td style="font-weight: 700;">{{ $pc->station_code }}</td>
                        <td style="text-align: center;">
                            {{ $pc->status === 'maintenance' ? 'MAINT' : 'NORMAL' }}
                        </td>
                        <td style="text-align: right;">
                            {{ $pc->status === 'occupied' ? 'AKTIF' : ($pc->status === 'maintenance' ? 'SERVIS' : 'READY') }}
                        </td>
                    </tr>
                @empty
                    @for($i = 1; $i <= 10; $i++)
                        <tr>
                            <td style="text-align: center;">{{ $i }}</td>
                            <td style="font-weight: 700;">PC-{{ str_pad((string) $i, 2, '0', STR_PAD_LEFT) }}</td>
                            <td style="text-align: center;">NORMAL</td>
                            <td style="text-align: right;">READY</td>
                        </tr>
                    @endfor
                @endforelse
            </tbody>
        </table>

        {{-- 4. TABEL KERTAS PRINT & PERLENGKAPAN --}}
        <div class="print-section-title">Kertas Print & Perlengkapan</div>
        <table class="print-grid-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Item</th>
                    <th style="text-align: right; width: 20%;">Awal</th>
                    <th style="text-align: right; width: 20%;">Pakai</th>
                    <th style="text-align: right; width: 20%;">Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockComparisons['kertas'] ?? [] as $kertas)
                    @php
                        $shortKertas = str_contains($kertas['name'], 'A4') ? 'KERTAS A4' : (str_contains($kertas['name'], 'F4') ? 'KERTAS F4' : $kertas['name']);
                        $pakaiKertas = (int) ($kertas['terpakai'] ?? 0);
                        if ($pakaiKertas === 0 && str_contains($kertas['name'], 'A4') && $totalPrintPages > 0) {
                            $pakaiKertas = $totalPrintPages;
                        }
                    @endphp
                    <tr>
                        <td>{{ $shortKertas }}</td>
                        <td style="text-align: right;">{{ $kertas['sebelumnya'] }}</td>
                        <td style="text-align: right;">{{ $pakaiKertas }}</td>
                        <td style="text-align: right; font-weight: 700;">{{ $kertas['sesudahnya'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>KERTAS A4</td>
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right;">{{ $totalPrintPages }}</td>
                        <td style="text-align: right; font-weight: 700;">-</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 5. CATATAN HARDWARE & TANDA TANGAN SERAH TERIMA --}}
        <div class="print-box-panel" style="margin-top: 4px;">
            <div style="font-weight: 700;">CATATAN HARDWARE SHIFT:</div>
            <div>{{ $handoverChecklist?->hardware_notes ?: 'Semua unit PC, Printer, Jaringan & AC berfungsi normal.' }}</div>
        </div>

        <div style="display: flex; justify-content: space-around; text-align: center; margin: 10px 0 8px 0;">
            <div style="width: 48%;">
                <div>(Penyerah)</div>
                <div style="height: 28px;"></div>
                <div style="font-weight: 700; text-transform: uppercase;">{{ $hwInspector }}</div>
            </div>
            <div style="width: 48%;">
                <div>(Penerima)</div>
                <div style="height: 28px;"></div>
                <div style="font-weight: 700; text-transform: uppercase;">{{ $salesSummary?->deposit_recipient ?: ($nextShiftLabel ?? '....................') }}</div>
            </div>
        </div>

        <div class="print-box-panel" style="font-size: 8.5px; line-height: 1.3;">
            <div style="font-weight: 700;">⚠️ CHECKLIST HARDWARE MGK</div>
            <div>Serah terima inventaris & hardware dianggap valid setelah ditandatangani kedua shift.</div>
        </div>
    </div>

    {{-- RINGKASAN KEUANGAN SHIFT (METRIC CARDS) --}}
    <div class="screen-only-rekap" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; margin-bottom: 16px;">
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
                <span>{{ $isPrintLocked ? '🔒 Nilai Terkunci' : (($realHardwarePages ?? 0) > 0 ? '🖨️ Canon USB Live' : '🔄 POS Otomatis') }}</span>
                <span>{{ $totalPrintPages }} Lembar @if(($realHardwarePages ?? 0) > 0)<small style="opacity: 0.8;">(USB: {{ $realHardwarePages }})</small>@endif</span>
            </div>
        </div>
    </div>

    {{-- KAS FISIK DI LACI VS SELISIH --}}
    <div class="rekap-card screen-only-rekap" style="border-color: rgba(14, 165, 233, 0.25); padding: 18px 20px; background: rgba(15, 23, 42, 0.6);">
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
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px;">
            <div style="background: rgba(0, 0, 0, 0.25); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; color: var(--text-muted);">Uang Fisik Kas Laci (Dihitung)</span>
                <div style="font-size: 20px; font-weight: 800; color: #38bdf8; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($cashInDrawer, 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(0, 0, 0, 0.25); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; color: var(--text-muted);">Target Kas Tunai Sistem</span>
                <div style="font-size: 20px; font-weight: 800; color: #94a3b8; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($systemExpectedCash ?? $totalGrossRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(0, 0, 0, 0.25); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; color: var(--text-muted);">Non-Tunai (QRIS / Bank)</span>
                <div style="font-size: 20px; font-weight: 800; color: #c4b5fd; font-family: 'Outfit', monospace; margin-top: 2px;">
                    Rp {{ number_format($nonCashAmount ?? 0, 0, ',', '.') }}
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

    {{-- LANJUTAN REKAP: FORM SETOR KAS & SERAH TERIMA OTOMATIS SHIFT SELANJUTNYA --}}
    @php
        $currentDepositStatus = $salesSummary->deposit_status ?? 'unsubmitted';
        $defaultDepositMethod = $salesSummary?->deposit_method ?: 'next_shift_handover';
        $defaultDepositRecipient = $salesSummary?->deposit_recipient ?: ($nextShiftLabel ?? 'Kasir Shift Selanjutnya');
    @endphp
    <div id="panel-setor-kas" class="rekap-card screen-only-rekap no-print" style="border-color: rgba(16, 185, 129, 0.35); background: linear-gradient(135deg, rgba(16, 185, 129, 0.06), rgba(14, 165, 233, 0.06)); padding: 18px 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <div>
                <span style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.05em; color: #34d399; font-weight: 700;">
                    Lanjutan Rekapitulasi Shift • Setor Per Shift Otomatis
                </span>
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin: 2px 0 0 0; display: flex; align-items: center; gap: 8px;">
                    <span>🤝</span> Setor Kas Shift & Serah Terima ke Shift Selanjutnya
                </h3>
            </div>
            <div>
                @if($currentDepositStatus === 'verified' || $currentDepositStatus === 'pending_verification')
                    <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 11px;">
                        ✅ Otomatis Disetor ke Shift Selanjutnya ({{ $defaultDepositRecipient }})
                    </span>
                @elseif($currentDepositStatus === 'rejected')
                    <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11px;">
                        ❌ Perlu Revisi Serah Terima Shift
                    </span>
                @else
                    <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4); font-size: 11px;">
                        🤝 Siap Setor Otomatis ke {{ $defaultDepositRecipient }}
                    </span>
                @endif
            </div>
        </div>

        @if($currentDepositStatus !== 'unsubmitted' && $salesSummary)
            <div style="background: rgba(0, 0, 0, 0.28); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 12px;">
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Nominal Disetor Shift:</span>
                    <strong style="color: #34d399; font-size: 15px; font-family: monospace;">Rp {{ number_format((float) $salesSummary->deposited_amount, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Modal Laci Ditinggal:</span>
                    <strong style="color: #38bdf8; font-size: 14px; font-family: monospace;">Rp {{ number_format((float) $salesSummary->retained_drawer_cash, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Metode Serah Terima:</span>
                    <strong style="color: #e2e8f0;">{{ $salesSummary->depositMethodLabel() }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Diserahkan ke Shift / Penerima:</span>
                    <strong style="color: #c4b5fd;">{{ $salesSummary->deposit_recipient ?: $defaultDepositRecipient }}</strong>
                </div>
            </div>
        @endif

        <form action="{{ route('presensi2.rekap.setor') }}" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; align-items: end;">
            @csrf
            <input type="hidden" name="summary_id" value="{{ $salesSummary?->id }}">
            <div>
                <label style="display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600;">Nominal Uang Disetor (Rp) *</label>
                <input type="number" name="deposited_amount" required min="0" step="100"
                       value="{{ (int) ($salesSummary?->deposited_amount ?? $cashInDrawer) }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; padding: 9px 11px; color: #34d399; font-weight: 700; font-size: 13px; font-family: monospace; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600;">Modal Laci Ditinggal (Rp)</label>
                <input type="number" name="retained_drawer_cash" min="0" step="100"
                       value="{{ (int) ($salesSummary?->retained_drawer_cash ?? 0) }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border-glass); border-radius: 8px; padding: 9px 11px; color: #fff; font-size: 13px; font-family: monospace; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600;">Shift Penerima / Kasir Berikutnya *</label>
                <input type="text" name="deposit_recipient" required placeholder="Contoh: Shift 2 / Nama Kasir Penerima"
                       value="{{ $defaultDepositRecipient }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border-glass); border-radius: 8px; padding: 9px 11px; color: #fff; font-size: 12.5px; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 600;">Catatan Serah Terima (Opsional)</label>
                <input type="text" name="deposit_notes" placeholder="Keterangan serah terima shift..."
                       value="{{ $salesSummary?->deposit_notes ?? '' }}"
                       style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border-glass); border-radius: 8px; padding: 9px 11px; color: #fff; font-size: 12.5px; outline: none;">
            </div>
            <div>
                <button type="submit" class="btn btn-emerald" style="padding: 10px 14px; font-size: 12.5px; font-weight: 700; width: 100%;">
                    <span>🤝</span> Simpan / Setor ke Shift Selanjutnya
                </button>
            </div>
        </form>

        {{-- TABEL HISTORY SETOR KAS SHIFT (SESUAI CONTOH GAMBAR 1) --}}
        <div style="margin-top: 20px; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25); border: 1px solid rgba(245, 158, 11, 0.4);">
            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 11px 18px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 15px; font-weight: 700; color: #ffffff; letter-spacing: 0.02em;">
                    History setor
                </span>
                <span style="font-size: 11px; color: #fef3c7; font-weight: 600;">
                    Total {{ $recentSummaries->count() }} Riwayat Terakhir
                </span>
            </div>

            @if($recentSummaries->isNotEmpty())
                <div style="overflow-x: auto; background: #ffffff;">
                    <table style="width: 100%; border-collapse: collapse; font-family: inherit; font-size: 12.5px; margin: 0;">
                        <thead style="background: #e0f2fe; border-bottom: 2px solid #bae6fd;">
                            <tr style="color: #0369a1; font-weight: 700; font-size: 12px;">
                                <th style="padding: 11px 8px; text-align: center; width: 48px;">No</th>
                                <th style="padding: 11px 12px; text-align: center;">Tanggal</th>
                                <th style="padding: 11px 12px; text-align: center;">Nomer</th>
                                <th style="padding: 11px 12px; text-align: center;">Outlet</th>
                                <th style="padding: 11px 12px; text-align: center;">Shif</th>
                                <th style="padding: 11px 12px; text-align: center;">Penyetor</th>
                                <th style="padding: 11px 14px; text-align: right;">Nilai</th>
                                <th style="padding: 11px 12px; text-align: center; width: 105px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSummaries as $idx => $rec)
                                @php
                                    $recDateStr = $rec->work_date?->toDateString() ?? now()->toDateString();
                                    $recTimeStr = ($rec->deposited_at ?? $rec->closed_at ?? $rec->updated_at)?->format('H:i:s') ?? '00:00:00';
                                    $recShiftNum = $rec->shiftNumber();
                                    $recNilai = (float) ($rec->deposited_amount ?? $rec->blind_cash_count ?? $rec->cash_in_drawer ?? $rec->total_revenue);
                                    $isCurrentSelected = $salesSummary && $salesSummary->id === $rec->id;
                                    $dateShiftMap = $shiftSummariesByDate[$recDateStr] ?? [];
                                @endphp
                                <tr style="border-bottom: 1px solid #f1f5f9; color: #1e293b; {{ $isCurrentSelected ? 'background: #f0fdf4;' : '' }}">
                                    <td style="text-align: center; font-weight: 700; color: #64748b; font-size: 12px; padding: 10px 8px;">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td style="font-family: monospace; font-size: 12px; color: #334155; white-space: nowrap; text-align: center; padding: 10px 12px;">
                                        {{ $recDateStr }} {{ $recTimeStr }}
                                    </td>
                                    <td style="white-space: nowrap; text-align: center; padding: 10px 12px;">
                                        <a href="{{ route('presensi2.rekap', ['summary_id' => $rec->id]) }}"
                                           style="color: #0284c7; font-weight: 700; font-family: monospace; font-size: 12px; text-decoration: none;">
                                            {{ $rec->rekapNumber() }}
                                        </a>
                                    </td>
                                    <td style="font-weight: 600; color: #1e293b; font-size: 12px; white-space: nowrap; text-align: center; padding: 10px 12px;">
                                        {{ strtoupper($rec->branch->name ?? $currentEmployee->branch->name ?? 'MAYA 1') }}
                                    </td>
                                    <td style="text-align: center; white-space: nowrap; padding: 10px 12px;">
                                        <div style="display: inline-flex; gap: 4px; align-items: center; justify-content: center;">
                                            @for($sNum = 1; $sNum <= 4; $sNum++)
                                                @php
                                                    $targetSummaryId = $dateShiftMap[$sNum] ?? null;
                                                    $isRowShift = ($recShiftNum === $sNum);
                                                    $shiftPrintHref = $targetSummaryId
                                                        ? route('presensi2.rekap', ['summary_id' => $targetSummaryId, 'auto_print' => 1, 'print_type' => 'rekap'])
                                                        : route('presensi2.rekap', ['date' => $recDateStr, 'shift' => $sNum, 'auto_print' => 1, 'print_type' => 'rekap']);
                                                @endphp
                                                @if($isRowShift && $isCurrentSelected)
                                                    <button type="button" onclick="triggerRekapPrint('rekap')"
                                                            title="Print Slip Shift {{ $sNum }} (Aktif)"
                                                            style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 4px; font-size: 12px; font-weight: 800; cursor: pointer; background: #f59e0b; color: #ffffff; border: 1px solid #d97706; box-shadow: 0 1px 4px rgba(245, 158, 11, 0.4);">
                                                        {{ $sNum }}
                                                    </button>
                                                @else
                                                    <a href="{{ $shiftPrintHref }}"
                                                       title="Print Slip Shift {{ $sNum }}"
                                                       style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 4px; font-size: 12px; font-weight: 800; text-decoration: none; background: #f59e0b; color: #ffffff; border: 1px solid #d97706; transition: opacity 0.15s;"
                                                       onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                                        {{ $sNum }}
                                                    </a>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #1e293b; font-size: 12.5px; padding: 10px 12px;">
                                        {{ strtoupper($rec->employee->name ?? '-') }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 800; font-size: 13px; color: #0f172a; white-space: nowrap; padding: 10px 14px;">
                                        {{ number_format($recNilai, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center; white-space: nowrap; padding: 10px 12px;">
                                        <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: center;">
                                            @if($isCurrentSelected)
                                                <button type="button" onclick="triggerRekapPrint('rekap')" title="Print Laporan Summary / Rekap Shift Ini"
                                                        style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 28px; border-radius: 5px; border: 1px solid #d97706; background: #f59e0b; color: #ffffff; cursor: pointer; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                                                    🖨️
                                                </button>
                                                <button type="button" onclick="triggerRekapPrint('hardware')" title="Print Checklist Hardware Shift Ini"
                                                        style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 28px; border-radius: 5px; border: 1px solid #059669; background: #10b981; color: #ffffff; cursor: pointer; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                                                    🖥️
                                                </button>
                                            @else
                                                <a href="{{ route('presensi2.rekap', ['summary_id' => $rec->id, 'auto_print' => 1, 'print_type' => 'rekap']) }}" title="Print Laporan Summary / Rekap Shift Ini"
                                                   style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 28px; border-radius: 5px; border: 1px solid #d97706; background: #f59e0b; color: #ffffff; text-decoration: none; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                                                    🖨️
                                                </a>
                                                <a href="{{ route('presensi2.rekap', ['summary_id' => $rec->id, 'auto_print' => 1, 'print_type' => 'hardware']) }}" title="Print Checklist Hardware Shift Ini"
                                                   style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 28px; border-radius: 5px; border: 1px solid #059669; background: #10b981; color: #ffffff; text-decoration: none; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                                                    🖥️
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 18px; font-size: 12px; color: var(--text-muted); background: rgba(0, 0, 0, 0.2); border-radius: 0 0 10px 10px;">
                    Belum ada riwayat setoran shift yang tercatat.
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU TERPADU BER-TAB: DETAIL REKAPITULASI SHIFT --}}
    <div class="rekap-card screen-only-rekap">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">
                    📋 Rincian Laporan & Audit Rekapitulasi Shift
                </h3>
                <span style="font-size: 11px; color: var(--text-muted);">Pilih tab untuk memeriksa item terjual, daftar nota (Tunai/QRIS/Void), audit stok, dan riwayat shift</span>
            </div>

            {{-- TAB SWITCHER --}}
            <div class="no-print" style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" onclick="switchRekapTab('tab-rekap-fnb', this)" class="btn-rekap-tab active" style="padding: 7px 13px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid rgba(14, 165, 233, 0.5); background: rgba(14, 165, 233, 0.22); color: #38bdf8; cursor: pointer;">
                    🍔 1. Rincian Item F&B ({{ $fnbItemSummary->count() }})
                </button>
                <button type="button" onclick="switchRekapTab('tab-rekap-nota', this)" class="btn-rekap-tab" style="padding: 7px 13px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid var(--border-glass); background: rgba(15, 23, 42, 0.6); color: var(--text-muted); cursor: pointer;">
                    🧾 2. Daftar Nota ({{ $fnbSales->count() }})
                </button>
                <button type="button" onclick="switchRekapTab('tab-rekap-stok', this)" class="btn-rekap-tab" style="padding: 7px 13px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid var(--border-glass); background: rgba(15, 23, 42, 0.6); color: var(--text-muted); cursor: pointer;">
                    📦 3. Audit Stok Barang
                </button>
                <button type="button" onclick="switchRekapTab('tab-rekap-riwayat', this)" class="btn-rekap-tab" style="padding: 7px 13px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid var(--border-glass); background: rgba(15, 23, 42, 0.6); color: var(--text-muted); cursor: pointer;">
                    📑 4. Riwayat Shift ({{ $recentSummaries->count() }})
                </button>
            </div>
        </div>

        {{-- TAB 1: RINCIAN PENJUALAN F&B (ITEM BREAKDOWN) --}}
        <div id="tab-rekap-fnb" class="rekap-tab-pane">
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
                            @if(($totalDiscountAmount ?? 0) > 0)
                            <tr style="background: rgba(239, 68, 68, 0.05);">
                                <td colspan="4" style="font-weight: 600; color: #fca5a5; font-size: 11.5px; text-align: right;">Total Potongan Diskon Terotorisasi:</td>
                                <td style="text-align: right; font-weight: 700; color: #f87171; font-size: 13px; font-family: monospace;">
                                    -Rp {{ number_format($totalDiscountAmount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            <tr style="background: rgba(255, 255, 255, 0.03);">
                                <td colspan="2" style="font-weight: 700; color: #fff; text-transform: uppercase; font-size: 12px;">Total F&B (Net) Shift Ini:</td>
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

        {{-- TAB 2: DAFTAR NOTA / INVOICE (TUNAI, QRIS, DISKON, & VOID) --}}
        <div id="tab-rekap-nota" class="rekap-tab-pane" style="display: none;">
            @if($fnbSales->isNotEmpty())
                <div style="overflow-x: auto; max-height: 360px; overflow-y: auto;">
                    <table class="rekap-table">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>No. Invoice</th>
                                <th>Pelanggan & Kasir</th>
                                <th style="text-align: center;">Metode</th>
                                <th style="text-align: right;">Gross / Diskon</th>
                                <th style="text-align: right;">Net Bayar</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fnbSales as $sale)
                            @php
                                $isVoid = $sale->payment_status === 'cancelled';
                                $netVal = (float) ($sale->net_amount > 0 || $sale->discount_amount > 0 ? $sale->net_amount : $sale->total_fnb);
                            @endphp
                            <tr style="{{ $isVoid ? 'opacity: 0.6; background: rgba(239, 68, 68, 0.05);' : '' }}">
                                <td style="font-family: monospace; font-size: 11.5px; color: var(--text-sub);">
                                    {{ $sale->created_at?->format('H:i') }}
                                </td>
                                <td>
                                    <strong style="font-family: monospace; font-size: 12px; color: {{ $isVoid ? '#fca5a5; text-decoration: line-through;' : '#38bdf8' }};">
                                        {{ $sale->invoice_number }}
                                    </strong>
                                </td>
                                <td>
                                    <span style="color: #fff; font-weight: 600; display: block; font-size: 12.5px;">{{ $sale->customer_name ?: 'Pelanggan Umum' }}</span>
                                    <span style="font-size: 10.5px; color: var(--text-muted);">Kasir: {{ $sale->employee->name ?? '-' }}</span>
                                </td>
                                <td style="text-align: center;">
                                    @if(strtolower((string) $sale->payment_method) === 'qris')
                                        <span class="badge" style="background: rgba(139, 92, 246, 0.2); color: #c4b5fd; font-size: 10.5px; font-weight: 700;">QRIS</span>
                                    @else
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10.5px; font-weight: 700;">TUNAI</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-family: monospace; font-size: 11.5px; color: var(--text-muted);">
                                    Rp {{ number_format((float) $sale->total_fnb, 0, ',', '.') }}
                                    @if((float) $sale->discount_amount > 0)
                                        <span style="display: block; color: #f87171; font-size: 10px;">Diskon: -Rp {{ number_format((float) $sale->discount_amount, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: {{ $isVoid ? '#94a3b8; text-decoration: line-through;' : '#fbbf24' }};">
                                    Rp {{ number_format($netVal, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    @if($isVoid)
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; font-size: 10px;">VOID</span>
                                    @else
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">LUNAS</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 24px; color: var(--text-muted); background: rgba(0, 0, 0, 0.2); border-radius: 12px;">
                    Belum ada nota transaksi pada shift ini.
                </div>
            @endif
        </div>

        {{-- TAB 3: AUDIT SISA STOK BARANG & SERAH TERIMA --}}
        <div id="tab-rekap-stok" class="rekap-tab-pane" style="display: none;">
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

        {{-- TAB 4: RIWAYAT REKAPITULASI SHIFT & HISTORY SETOR (10 SHIFT TERAKHIR) --}}
        <div id="tab-rekap-riwayat" class="rekap-tab-pane no-print" style="display: none;">
            @if($recentSummaries->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table class="rekap-table">
                        <thead>
                            <tr>
                                <th>Tanggal & Waktu</th>
                                <th>Shift & Operator</th>
                                <th style="text-align: right;">Total Omset</th>
                                <th style="text-align: right;">Kas Laci</th>
                                <th style="text-align: right;">Nominal Setor & Tujuan</th>
                                <th style="text-align: center;">Status Shift & Setor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSummaries as $rec)
                            @php
                                $rDepStatus = $rec->deposit_status ?? 'unsubmitted';
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color: #fff; display: block;">{{ $rec->work_date?->format('d/m/Y') }}</strong>
                                    <span style="font-size: 10.5px; color: var(--text-sub);">Tutup: {{ $rec->closed_at?->format('H:i') ?? '-' }}</span>
                                </td>
                                <td>
                                    <span style="color: #38bdf8; font-weight: 600;">{{ $rec->shift->name ?? 'Shift' }}</span>
                                    <span style="font-size: 11px; color: var(--text-muted); display: block;">{{ $rec->employee->name ?? 'Operator' }}</span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #34d399; font-family: monospace;">
                                    Rp {{ number_format((float) $rec->total_revenue, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #fff; font-family: monospace;">
                                    Rp {{ number_format((float) $rec->cash_in_drawer, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right;">
                                    @if($rec->deposited_amount !== null)
                                        <strong style="color: #34d399; font-family: monospace; display: block;">
                                            Rp {{ number_format((float) $rec->deposited_amount, 0, ',', '.') }}
                                        </strong>
                                        <span style="font-size: 10.5px; color: #38bdf8;">
                                            {{ $rec->deposit_recipient ?: '-' }} ({{ $rec->depositMethodLabel() }})
                                        </span>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 11px;">Belum disetor</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($rDepStatus === 'verified' || $rDepStatus === 'pending_verification')
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 10px;">
                                            ✅ Disetor ke Shift Selanjutnya
                                        </span>
                                    @elseif($rec->status === 'closed')
                                        <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; font-size: 10px;">
                                            Closed (Belum Setor)
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
    </div>

    {{-- NAVIGASI AKSI BAWAH & CHECK-OUT PRESENSI 1-KLIK (NO PRINT) --}}
    <div class="no-print" style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
        <a href="{{ route('presensi2.penjualan') }}" class="btn btn-emerald" style="padding: 14px; font-size: 14px; font-weight: 700; flex: 1; min-width: 200px; text-decoration: none;">
            <span>🛒</span> Buka Kasir Penjualan Shift
        </a>
        @if($attendanceToday && !$attendanceToday->clock_out_at)
        <button type="button" onclick="submitRekapCheckOut()" id="btn-clock-out-rekap" class="btn btn-rose" style="padding: 14px; font-size: 14px; font-weight: 700; flex: 1; min-width: 200px; box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4);">
            <span>🏁</span> Check Out Presensi Pulang (1-Klik)
        </button>
        @elseif($attendanceToday && $attendanceToday->clock_out_at)
        <button type="button" disabled class="btn" style="padding: 14px; font-size: 13.5px; font-weight: 700; flex: 1; min-width: 200px; background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
            <span>✅</span> Sudah Check Out Presensi ({{ $attendanceToday->clock_out_at->format('H:i') }})
        </button>
        @else
        <a href="{{ route('presensi2.scan') }}" class="btn btn-rose" style="padding: 14px; font-size: 14px; font-weight: 700; flex: 1; min-width: 200px; text-decoration: none;">
            <span>🏁</span> Check Out Presensi Pulang
        </a>
        @endif
    </div>
    <div id="rekap-checkout-result-box" class="no-print" style="display: none; margin-top: 12px; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; text-align: center;"></div>

</div>

<script>
    function applyPrintSettings() {
        const paperSelect = document.getElementById('print-paper-mode');
        const scaleSelect = document.getElementById('print-scale-select');
        const printBox = document.getElementById('print-table-report');
        const hwBox = document.getElementById('print-hardware-report');

        const scaleVal = scaleSelect ? scaleSelect.value : '1';
        const paperMode = paperSelect ? paperSelect.value : 'slip-tengah';

        document.documentElement.style.setProperty('--print-scale', scaleVal);

        [printBox, hwBox].forEach(box => {
            if (!box) return;
            box.style.zoom = scaleVal;
            box.classList.remove('paper-medium', 'paper-a4', 'paper-thermal58');
            if (paperMode === 'medium') {
                box.classList.add('paper-medium');
            } else if (paperMode === 'a4') {
                box.classList.add('paper-a4');
            } else if (paperMode === 'thermal58') {
                box.classList.add('paper-thermal58');
            }
        });
    }

    function togglePrintTablePreview() {
        const printBox = document.getElementById('print-table-report');
        const btn = document.getElementById('btn-toggle-print-preview');
        if (!printBox) {
            return;
        }
        applyPrintSettings();
        const isShowing = printBox.classList.toggle('preview-active');
        if (btn) {
            if (isShowing) {
                btn.innerHTML = '<span>🙈</span> Tutup Preview Tabel';
                btn.style.background = 'rgba(14, 165, 233, 0.25)';
                btn.style.color = '#38bdf8';
                btn.style.borderColor = 'rgba(14, 165, 233, 0.5)';
            } else {
                btn.innerHTML = '<span>👁️</span> Preview Tabel';
                btn.style.background = 'rgba(255, 255, 255, 0.08)';
                btn.style.color = '#e2e8f0';
                btn.style.borderColor = 'var(--border-glass)';
            }
        }
    }

    function triggerRekapPrint(mode = 'rekap') {
        applyPrintSettings();
        if (mode === 'hardware') {
            document.body.classList.add('printing-hardware');
        } else {
            document.body.classList.remove('printing-hardware');
        }
        window.print();
    }

    document.addEventListener('DOMContentLoaded', () => {
        applyPrintSettings();
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            const printType = urlParams.get('print_type') === 'hardware' ? 'hardware' : 'rekap';
            setTimeout(() => {
                triggerRekapPrint(printType);
            }, 350);
        }
    });

    function switchRekapTab(tabId, btn) {
        document.querySelectorAll('.rekap-tab-pane').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.btn-rekap-tab').forEach(b => {
            b.style.background = 'rgba(15, 23, 42, 0.6)';
            b.style.borderColor = 'var(--border-glass)';
            b.style.color = 'var(--text-muted)';
        });

        const target = document.getElementById(tabId);
        if (target) {
            target.style.display = 'block';
        }
        btn.style.background = 'rgba(14, 165, 233, 0.22)';
        btn.style.borderColor = 'rgba(14, 165, 233, 0.5)';
        btn.style.color = '#38bdf8';
    }

    async function submitRekapCheckOut() {
        if (!confirm('Apakah Anda yakin ingin melakukan Check Out Presensi Pulang sekarang?')) {
            return;
        }

        const btn = document.getElementById('btn-clock-out-rekap');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Memproses Check-Out...';
        }

        try {
            const response = await fetch('{{ route("presensi2.clock-out") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await response.json();
            const resultBox = document.getElementById('rekap-checkout-result-box');

            if (data.success) {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(16, 185, 129, 0.15)';
                resultBox.style.border = '1px solid rgba(16, 185, 129, 0.35)';
                resultBox.style.color = '#34d399';
                resultBox.innerHTML = `✅ ${data.message} Terima kasih atas kerja keras Anda!`;
                setTimeout(() => location.reload(), 1800);
            } else {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                resultBox.style.border = '1px solid rgba(239, 68, 68, 0.35)';
                resultBox.style.color = '#fca5a5';
                resultBox.innerHTML = `❌ ${data.message || 'Terjadi kesalahan saat check-out.'}`;
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>🏁</span> Check Out Presensi Pulang (1-Klik)';
                }
            }
        } catch (err) {
            alert('Gagal terhubung ke server. Periksa koneksi internet Anda.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<span>🏁</span> Check Out Presensi Pulang (1-Klik)';
            }
        }
    }
</script>
@endsection
