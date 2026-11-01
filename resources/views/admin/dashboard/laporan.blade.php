@extends('admin.dashboard.layout')

@section('title', '4. Laporan Rekapitulasi & Keuangan')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">📊</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">4. Laporan Rekapitulasi & Analitik Laba Kotor</h1>
                <span class="section-subtitle">Analisis Gross Profit F&B, Utilisasi Printer Canon USB, Billing Warnet, & Serah Terima Kasir</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button onclick="window.print()" class="btn-action-sm btn-action-primary" style="padding: 8px 14px;">
                <span>🖨️</span> Cetak / Simpan PDF
            </button>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- FILTER BAR PERIODE & CABANG -->
    <div style="padding: 16px 24px; border-bottom: 1px solid var(--border-glass); background: rgba(15, 23, 42, 0.4);">
        <form action="{{ route('admin.laporan') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 11.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Periode:</label>
                <select name="period" onchange="this.form.submit()" style="padding: 6px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12px; font-weight: 600;">
                    <option value="today" {{ ($period ?? 'today') === 'today' ? 'selected' : '' }}>Hari Ini ({{ \Carbon\Carbon::today()->format('d M Y') }})</option>
                    <option value="yesterday" {{ ($period ?? '') === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="7days" {{ ($period ?? '') === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="this_month" {{ ($period ?? '') === 'this_month' ? 'selected' : '' }}>Bulan Ini ({{ \Carbon\Carbon::today()->translatedFormat('F Y') }})</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <label style="font-size: 11.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Cabang:</label>
                <select name="branch_id" onchange="this.form.submit()" style="padding: 6px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12px; font-weight: 600;">
                    <option value="all">Semua Cabang / Outlet</option>
                    @if(isset($branches))
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (isset($branchId) && (int)$branchId === (int)$b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 6px 14px; font-size: 12px;">
                Filter Data
            </button>
        </form>
    </div>

    <!-- STAT SUMMARY ROW: ANALITIK LABA KOTOR & OMSET -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
            {{-- 1. TOTAL LABA KOTOR --}}
            <div style="background: rgba(16, 185, 129, 0.1); padding: 18px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.35);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Total Laba Kotor (Gross Profit)</span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.25); color: #34d399; font-size: 10px; font-weight: 800; padding: 2px 7px;">
                        {{ $profitAnalytics['overall']['margin'] ?? 0 }}% MARGIN
                    </span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin: 6px 0 2px;">
                    Rp {{ number_format($profitAnalytics['overall']['gross_profit'] ?? 0, 0, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: var(--text-muted); display: flex; justify-content: space-between;">
                    <span>Omset: Rp {{ number_format($profitAnalytics['overall']['revenue'] ?? 0, 0, ',', '.') }}</span>
                    <span>HPP: Rp {{ number_format($profitAnalytics['overall']['cogs'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- 2. LABA KOTOR F&B --}}
            <div style="background: rgba(15, 23, 42, 0.6); padding: 18px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Laba Kotor F&B Kasir</span>
                    <span class="badge" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                        {{ $profitAnalytics['fnb']['margin'] ?? 0 }}%
                    </span>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin: 6px 0 2px;">
                    Rp {{ number_format($profitAnalytics['fnb']['gross_profit'] ?? 0, 0, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: var(--text-muted); display: flex; justify-content: space-between;">
                    <span>Omset: Rp {{ number_format($profitAnalytics['fnb']['revenue'] ?? 0, 0, ',', '.') }}</span>
                    <span>HPP: Rp {{ number_format($profitAnalytics['fnb']['cost'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- 3. LABA KOTOR CETAK & PRINT CANON --}}
            <div style="background: rgba(15, 23, 42, 0.6); padding: 18px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">Laba Jasa Print & Kertas</span>
                    <span class="badge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                        {{ $profitAnalytics['print']['margin'] ?? 0 }}%
                    </span>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin: 6px 0 2px;">
                    Rp {{ number_format($profitAnalytics['print']['gross_profit'] ?? 0, 0, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: var(--text-muted); display: flex; justify-content: space-between;">
                    <span>{{ $profitAnalytics['print']['total_pages'] ?? 0 }} Lbr Dicetak</span>
                    <span>Omset: Rp {{ number_format($profitAnalytics['print']['revenue'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- 4. PENDAPATAN BILLING WARNET --}}
            <div style="background: rgba(15, 23, 42, 0.6); padding: 18px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #a78bfa; font-weight: 700;">Pendapatan Billing Warnet</span>
                    <span class="badge" style="background: rgba(167, 139, 250, 0.2); color: #a78bfa; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                        100% JASA
                    </span>
                </div>
                <div style="font-size: 24px; font-weight: 800; color: #a78bfa; font-family: 'Outfit'; margin: 6px 0 2px;">
                    Rp {{ number_format($profitAnalytics['billing']['revenue'] ?? ($reportSummary['total_billing_sales_today'] ?? 0), 0, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: var(--text-muted);">
                    Sewa PC Station 1–15 & Member Warnet
                </div>
            </div>
        </div>
    </div>

    <div class="section-card-body">
        <!-- AUDIT KAS DI LACI -->
        <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border-glass); border-radius: 12px; padding: 16px 20px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
            <div>
                <strong>Audit Serah Terima Kasir:</strong> Tercatat <strong>{{ $reportSummary['shift_handover_count'] }} sesi pergantian shift</strong> dengan total uang fisik laci kasir sebesar <strong style="color: #34d399; font-family: 'Outfit'; font-size: 15px;">Rp {{ number_format($reportSummary['total_drawer_cash'], 0, ',', '.') }}</strong>.
            </div>
            <a href="{{ route('admin.penjualan') }}" class="btn-action-sm btn-action-primary">
                <span>🔍</span> Buka Buku Penjualan Lengkap
            </a>
        </div>

        <!-- GRID 2 KOLOM: UTILISASI PRINTER CANON & TOP PRODUK F&B -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 18px; margin-bottom: 24px;">
            {{-- PANEL UTILISASI PRINTER CANON USB --}}
            <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">🖨️</span>
                        <div>
                            <h3 style="font-size: 14.5px; font-weight: 700; color: #fff; margin: 0;">Utilisasi Printer Canon USB</h3>
                            @if(!empty($profitAnalytics['print']['connected_printers']))
                                <div style="font-size: 11px; color: #38bdf8; font-weight: 600;">
                                    {{ implode(', ', $profitAnalytics['print']['connected_printers']) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 10px; font-weight: 700;">
                        ● SINKRON & TERHUBUNG
                    </span>
                </div>

                {{-- STATISTIK CETAK --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                    <div style="background: rgba(15, 23, 42, 0.7); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Total Lembar</span>
                        <div style="font-size: 18px; font-weight: 800; color: #38bdf8; font-family: 'Outfit';">
                            {{ $profitAnalytics['print']['total_pages'] ?? 0 }}
                        </div>
                    </div>
                    <div style="background: rgba(15, 23, 42, 0.7); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Hitam-Putih (B/W)</span>
                        <div style="font-size: 18px; font-weight: 800; color: #e2e8f0; font-family: 'Outfit';">
                            {{ $profitAnalytics['print']['bw_pages'] ?? 0 }}
                        </div>
                    </div>
                    <div style="background: rgba(15, 23, 42, 0.7); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <span style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Warna (Color)</span>
                        <div style="font-size: 18px; font-weight: 800; color: #f43f5e; font-family: 'Outfit';">
                            {{ $profitAnalytics['print']['color_pages'] ?? 0 }}
                        </div>
                    </div>
                </div>

                {{-- DAFTAR JOB TERBARU --}}
                <div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 6px; text-transform: uppercase;">
                        Riwayat Cetak Spooler Terakhir:
                    </span>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted); text-align: left;">
                                    <th style="padding: 6px 8px;">Dokumen</th>
                                    <th style="padding: 6px 8px;">PC Client</th>
                                    <th style="padding: 6px 8px; text-align: center;">Halaman</th>
                                    <th style="padding: 6px 8px; text-align: right;">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profitAnalytics['print']['recent_jobs'] ?? [] as $pj)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                    <td style="padding: 6px 8px; color: #e2e8f0; font-weight: 600;" title="{{ $pj->document_name }}">
                                        {{ Str::limit($pj->document_name, 40) }}
                                    </td>
                                    <td style="padding: 6px 8px; color: var(--text-muted);">
                                        {{ $pj->client_computer ?: 'Client POS' }}
                                    </td>
                                    <td style="padding: 6px 8px; text-align: center; color: #38bdf8; font-weight: 700;">
                                        {{ $pj->pages }} lbr
                                    </td>
                                    <td style="padding: 6px 8px; text-align: right; color: #34d399; font-weight: 700;">
                                        Rp {{ number_format($pj->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="padding: 12px; text-align: center; color: var(--text-muted);">
                                        Belum ada job cetak tercatat pada periode ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- PANEL TOP PRODUK F&B PALING MENGUNTUNGKAN --}}
            <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">🏆</span>
                        <h3 style="font-size: 14.5px; font-weight: 700; color: #fff; margin: 0;">Top 5 Kontributor Laba F&B</h3>
                    </div>
                    <span class="badge" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; font-size: 10px; font-weight: 700;">
                        PRODUK PALING PROFIT
                    </span>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted); text-align: left;">
                                <th style="padding: 7px 8px;">Menu F&B</th>
                                <th style="padding: 7px 8px; text-align: center;">Terjual</th>
                                <th style="padding: 7px 8px; text-align: right;">Omset</th>
                                <th style="padding: 7px 8px; text-align: right;">Laba Kotor</th>
                                <th style="padding: 7px 8px; text-align: center;">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profitAnalytics['fnb']['top_products'] ?? [] as $tp)
                            @php
                                $tpMargin = $tp['revenue'] > 0 ? round(($tp['profit'] / $tp['revenue']) * 100) : 0;
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td style="padding: 8px; color: #fff; font-weight: 600;">
                                    {{ $tp['name'] }}
                                </td>
                                <td style="padding: 8px; text-align: center; color: #93c5fd; font-weight: 700;">
                                    {{ $tp['qty'] }}x
                                </td>
                                <td style="padding: 8px; text-align: right; color: #e2e8f0;">
                                    Rp {{ number_format($tp['revenue'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 8px; text-align: right; color: #34d399; font-weight: 700;">
                                    Rp {{ number_format($tp['profit'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px; font-weight: 700;">
                                        {{ $tpMargin }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding: 16px; text-align: center; color: var(--text-muted);">
                                    Belum ada transaksi F&B pada periode terpilih.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN PENDAPATAN SHIFT OPERASIONAL -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                    <span>💵</span> Rekap Pendapatan per Shift Operasional ({{ $billingPerShift['date_formatted'] }})
                </h3>
            </div>

            <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Shift</th>
                            <th>Jam Operasional</th>
                            <th>Operator Bertugas</th>
                            <th>Billing Internet</th>
                            <th>Penjualan F&B Kasir</th>
                            <th>Total Omset Shift</th>
                            <th>Status Serah Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingPerShift['shifts'] as $bps)
                        <tr>
                            <td style="font-weight: 700; color: #38bdf8;">{{ $bps['shift_name'] }}</td>
                            <td style="font-family: monospace;">{{ $bps['time_range'] }}</td>
                            <td style="color: #fff; font-weight: 600;">{{ $bps['operator_name'] }}</td>
                            <td style="color: #38bdf8; font-weight: 700; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['billing_amount'], 0, ',', '.') }}
                            </td>
                            <td style="color: #fbbf24; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['fnb_amount'], 0, ',', '.') }}
                            </td>
                            <td style="color: #34d399; font-weight: 800; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['total_amount'], 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge-status badge-sky">{{ $bps['status'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
