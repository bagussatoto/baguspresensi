@extends('admin.dashboard.layout')

@section('title', 'Dashboard Konsolidasi & Monitoring Multi-Cabang')

@section('content')
<style>
    .badge-hq { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-dc { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-store { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    
    .kpi-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(56, 189, 248, 0.2);
        border-radius: 14px;
        padding: 18px 20px;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        border-color: rgba(56, 189, 248, 0.4);
    }
    .kpi-title { font-size: 11.5px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-value { font-family: ui-monospace, monospace; font-size: 24px; font-weight: 800; color: #f8fafc; margin-top: 6px; }
    .kpi-sub { font-size: 11px; color: #64748b; margin-top: 5px; }

    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
        text-align: left;
    }
    .matrix-table th {
        background: rgba(15, 23, 42, 0.8);
        color: #94a3b8;
        font-weight: 700;
        padding: 12px 14px;
        border-bottom: 2px solid rgba(56, 189, 248, 0.2);
    }
    .matrix-table td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(51, 65, 85, 0.4);
        color: #e2e8f0;
        vertical-align: middle;
    }
    .matrix-table tr:hover td {
        background: rgba(51, 65, 85, 0.25);
    }

    .progress-bar-container {
        height: 10px;
        background: rgba(15, 23, 42, 0.6);
        border-radius: 6px;
        overflow: hidden;
        display: flex;
        border: 1px solid rgba(51, 65, 85, 0.5);
    }
    .progress-bar-dc { background: #38bdf8; height: 100%; transition: width 0.3s ease; }
    .progress-bar-store { background: #10b981; height: 100%; transition: width 0.3s ease; }

    @media print {
        .tab-nav-pills, .hero-banner-card form, .btn-action-sm, .admin-sidebar, header { display: none !important; }
        body { background: #ffffff !important; color: #000000 !important; font-size: 11px !important; }
        .hero-banner-card { border: none !important; padding: 0 !important; margin-bottom: 12px !important; }
        .hero-title-text { color: #000000 !important; font-size: 18px !important; }
        .hero-desc-text { color: #555555 !important; font-size: 10px !important; }
        .kpi-card { background: #f8fafc !important; border: 1px solid #cbd5e1 !important; padding: 10px !important; }
        .kpi-title { color: #475569 !important; }
        .kpi-value { color: #0f172a !important; font-size: 16px !important; }
        .kpi-sub { color: #64748b !important; }
        .matrix-table th { background: #e2e8f0 !important; color: #0f172a !important; padding: 6px 8px !important; }
        .matrix-table td { color: #0f172a !important; padding: 6px 8px !important; border-bottom: 1px solid #cbd5e1 !important; }
        @page { size: landscape; margin: 10mm; }
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card" style="margin-bottom: 20px;">
    <div>
        <h1 class="hero-title-text">Dashboard Konsolidasi & Monitoring Multi-Cabang</h1>
        <p class="hero-desc-text">
            Potret matriks terpadu performa omset seluruh gerai, alokasi valuasi persediaan di Distribution Center (DC) vs Toko (Store), radar stok kritis (ROP), dan pelacakan surat jalan transfer stok (STO).
        </p>
    </div>
    <form method="GET" action="{{ route('admin.cabang.konsolidasi') }}" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 6px; background: rgba(15, 23, 42, 0.6); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">
            <span style="font-size: 12px; color: #94a3b8;">📅 Tanggal:</span>
            <input type="date" name="date" value="{{ $summary['filter_date'] }}" onchange="this.form.submit()" style="background: transparent; border: none; color: #fff; font-size: 12.5px; font-family: monospace; outline: none; cursor: pointer;">
        </div>
        <a href="{{ route('admin.cabang.konsolidasi') }}" class="btn-action-sm btn-action-secondary" style="padding: 7px 12px;" title="Reset filter ke hari ini">
            <span>🔄</span> Hari Ini
        </a>
        <a href="{{ route('admin.cabang.konsolidasi', ['date' => $summary['filter_date'], 'export' => 'csv']) }}" class="btn-action-sm btn-action-emerald" style="padding: 7px 14px; text-decoration: none; font-weight: 700;" title="Unduh data konsolidasi ke file CSV (kompatibel Excel)">
            <span>📥</span> Unduh CSV (Excel)
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm btn-action-primary" style="padding: 7px 14px; cursor: pointer;" title="Cetak laporan konsolidasi">
            <span>🖨️</span> Cetak Laporan
        </button>
    </form>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.cabang.nav')

<!-- KPI CARDS (4 METRIC SUMMARY) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- 1. Total Valuasi Persediaan -->
    <div class="kpi-card" style="border-left: 4px solid #38bdf8;">
        <div class="kpi-title">Valuasi Persediaan Jaringan</div>
        <div class="kpi-value" style="color: #38bdf8;">
            Rp {{ number_format($summary['total_valuation'], 0, ',', '.') }}
        </div>
        <div class="kpi-sub">
            DC: <strong>Rp {{ number_format($summary['dc_valuation'], 0, ',', '.') }}</strong> ({{ $summary['dc_valuation_pct'] }}%)<br>
            Store: <strong>Rp {{ number_format($summary['store_valuation'], 0, ',', '.') }}</strong> ({{ $summary['store_valuation_pct'] }}%)
        </div>
    </div>

    <!-- 2. Omset Penjualan Hari Ini & MTD -->
    <div class="kpi-card" style="border-left: 4px solid #10b981;">
        <div class="kpi-title">Omset Kasir Hari Ini ({{ \Carbon\Carbon::parse($summary['filter_date'])->format('d M') }})</div>
        <div class="kpi-value" style="color: #10b981;">
            Rp {{ number_format($summary['today_sales'], 0, ',', '.') }}
        </div>
        <div class="kpi-sub">
            Omset Bulan Ini (MTD): <strong>Rp {{ number_format($summary['month_sales'], 0, ',', '.') }}</strong><br>
            Total Kasir Bertugas: <strong>{{ $summary['network_cashier_count'] }} Orang</strong>
        </div>
    </div>

    <!-- 3. Radar Peringatan Kritis ROP -->
    <div class="kpi-card" style="border-left: 4px solid {{ $summary['critical_rop_count'] > 0 ? '#ef4444' : '#10b981' }};">
        <div class="kpi-title">Radar Stok Kritis (ROP Watchlist)</div>
        <div class="kpi-value" style="color: {{ $summary['critical_rop_count'] > 0 ? '#f87171' : '#34d399' }};">
            {{ $summary['critical_rop_count'] }} SKU
        </div>
        <div class="kpi-sub">
            @if($summary['critical_rop_count'] > 0)
                <span style="color: #fca5a5; font-weight: 700;">⚠️ Mendesak butuh pasokan transfer dari DC!</span>
            @else
                <span style="color: #86efac;">✓ Seluruh stok cabang berada di atas ambang ROP aman</span>
            @endif
        </div>
    </div>

    <!-- 4. Status Surat Jalan / Transfer Stok -->
    <div class="kpi-card" style="border-left: 4px solid #a855f7;">
        <div class="kpi-title">Distribusi &amp; Transfer Stok (STO)</div>
        <div class="kpi-value" style="color: #c084fc;">
            {{ $summary['pending_sto_count'] }} Antrean
        </div>
        <div class="kpi-sub">
            Total Jaringan: <strong>{{ $summary['total_branches'] }} Titik Cabang</strong><br>
            Otomatisasi ROP DC Aktif Real-Time
        </div>
    </div>
</div>

<!-- VALUATION PROGRESS BAR -->
<div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <span style="font-size: 12px; font-weight: 700; color: #e2e8f0;">
            ⚖️ Proporsi Alokasi Nilai Persediaan: Distribution Center (DC) vs Gerai Penjualan (Store)
        </span>
        <span style="font-size: 11.5px; font-family: monospace; color: #94a3b8;">
            Total Aset: Rp {{ number_format($summary['total_valuation'], 0, ',', '.') }}
        </span>
    </div>
    <div class="progress-bar-container">
        <div class="progress-bar-dc" style="width: {{ $summary['dc_valuation_pct'] }}%;" title="Pusat Distribusi Logistik (DC): {{ $summary['dc_valuation_pct'] }}%"></div>
        <div class="progress-bar-store" style="width: {{ $summary['store_valuation_pct'] }}%;" title="Outlet Toko / Store Ritel: {{ $summary['store_valuation_pct'] }}%"></div>
    </div>
    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; color: #94a3b8;">
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 10px; height: 10px; background: #38bdf8; border-radius: 2px;"></span>
            Pusat Distribusi (DC): <strong>Rp {{ number_format($summary['dc_valuation'], 0, ',', '.') }} ({{ $summary['dc_valuation_pct'] }}%)</strong>
        </span>
        <span style="display: flex; align-items: center; gap: 6px;">
            <span style="width: 10px; height: 10px; background: #10b981; border-radius: 2px;"></span>
            Toko Cabang (Store): <strong>Rp {{ number_format($summary['store_valuation'], 0, ',', '.') }} ({{ $summary['store_valuation_pct'] }}%)</strong>
        </span>
    </div>
</div>

<!-- SECTION 1: MATRIKS KOMPARASI KINERJA CABANG -->
<div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; overflow: hidden; margin-bottom: 28px;">
    <div style="padding: 16px 20px; background: rgba(15, 23, 42, 0.8); border-bottom: 1px solid rgba(56, 189, 248, 0.2); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 15px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>🏢</span> Matriks Komparasi Kinerja Penjualan &amp; Persediaan Multi-Cabang
            </h3>
            <p style="font-size: 11.5px; color: #94a3b8; margin: 3px 0 0 0;">
                Perbandingan omset kasir, rata-rata keranjang belanja, serta total fisik stok per cabang.
            </p>
        </div>
        <span style="font-size: 12px; color: #38bdf8; font-weight: 700; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); padding: 4px 10px; border-radius: 6px;">
            {{ count($branchMatrix) }} Entitas Aktif
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th>Entitas Cabang</th>
                    <th>Tipe Jaringan</th>
                    <th style="text-align: right;">Unit Stok Fisik</th>
                    <th style="text-align: right;">Valuasi Persediaan</th>
                    <th style="text-align: center;">Trx Hari Ini</th>
                    <th style="text-align: right;">Omset Kasir Hari Ini</th>
                    <th style="text-align: right;">Rata-rata Trx</th>
                    <th style="text-align: right;">Omset Bulan Ini (MTD)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branchMatrix as $row)
                    @php
                        $b = $row['branch'];
                        $badgeClass = match($b->type->value ?? 'STORE') {
                            'HQ' => 'badge-hq',
                            'DC' => 'badge-dc',
                            default => 'badge-store',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #f8fafc; font-size: 13px;">{{ $b->name }}</div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">Kode: {{ $b->code }} • {{ $b->employees_count }} Kasir</div>
                        </td>
                        <td>
                            <span class="{{ $badgeClass }}">
                                {{ $b->type->label() ?? 'Store' }}
                            </span>
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 700; color: #38bdf8;">
                            {{ number_format($row['stock_qty']) }} Pcs
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 700; color: #f1f5f9;">
                            Rp {{ number_format($row['valuation'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: center; font-family: monospace;">
                            {{ $row['today_trx_count'] }} Nota
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 800; color: #10b981;">
                            Rp {{ number_format($row['today_sales'], 0, ',', '.') }}
                            <div style="font-size: 10px; color: #64748b; font-weight: normal;">
                                Tunai: {{ number_format($row['today_cash'], 0, ',', '.') }} • QRIS: {{ number_format($row['today_qris'], 0, ',', '.') }}
                            </div>
                        </td>
                        <td style="text-align: right; font-family: monospace; color: #94a3b8;">
                            Rp {{ number_format($row['avg_basket'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: 700; color: #c084fc;">
                            Rp {{ number_format($row['month_sales'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">
                            Belum ada entitas cabang terdaftar dalam sistem.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: rgba(15, 23, 42, 0.95); font-weight: 800;">
                    <td colspan="2" style="text-align: right; padding: 14px; color: #f8fafc;">TOTAL KONSOLIDASI:</td>
                    <td style="text-align: right; font-family: monospace; color: #38bdf8;">
                        {{ number_format(collect($branchMatrix)->sum('stock_qty')) }} Pcs
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #f1f5f9;">
                        Rp {{ number_format($summary['total_valuation'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: center; font-family: monospace; color: #f8fafc;">
                        {{ collect($branchMatrix)->sum('today_trx_count') }} Nota
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #10b981; font-size: 13.5px;">
                        Rp {{ number_format($summary['today_sales'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #94a3b8;">-</td>
                    <td style="text-align: right; font-family: monospace; color: #c084fc; font-size: 13.5px;">
                        Rp {{ number_format($summary['month_sales'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- SECTION 2: RADAR PERINGATAN KRITIS ROP & REPLENISHMENT WATCHLIST -->
<div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 14px; overflow: hidden; margin-bottom: 28px;">
    <div style="padding: 16px 20px; background: rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.25); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 15px; font-weight: 700; color: #fca5a5; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>🚨</span> Radar Peringatan Kritis Reorder Point (ROP) &amp; Kebutuhan Pasok dari DC
            </h3>
            <p style="font-size: 11.5px; color: #fecaca; margin: 3px 0 0 0;">
                Daftar SKU produk di Toko Cabang (Store) yang stok fisiknya telah menyentuh atau berada di bawah ambang batas ROP.
            </p>
        </div>
        <span style="font-size: 12px; color: #ef4444; font-weight: 800; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); padding: 4px 10px; border-radius: 6px;">
            {{ count($criticalRopItems) }} Item Perlu Pasokan
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th>Toko Cabang</th>
                    <th>Produk / Varian SKU</th>
                    <th>Lokasi Rak (Bin)</th>
                    <th style="text-align: center;">Sisa Stok Fisik</th>
                    <th style="text-align: center;">Ambang ROP</th>
                    <th style="text-align: center;">Stok Max</th>
                    <th style="text-align: center;">Kebutuhan Pasok</th>
                    <th style="text-align: center;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($criticalRopItems as $crit)
                    <tr>
                        <td>
                            <strong style="color: #f8fafc;">{{ $crit['branch']->name ?? 'Store' }}</strong>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">Kode: {{ $crit['branch']->code ?? '-' }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #f87171;">{{ $crit['variant']->name ?? 'Produk' }}</div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">SKU: {{ $crit['variant']->sku ?? '-' }}</div>
                        </td>
                        <td>
                            <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 7px; border-radius: 4px; font-size: 11px; font-family: monospace;">
                                📍 {{ $crit['bin_location'] }}
                            </span>
                        </td>
                        <td style="text-align: center; font-family: monospace; font-size: 14px; font-weight: 800; color: #ef4444;">
                            {{ $crit['current_stock'] }} Pcs
                        </td>
                        <td style="text-align: center; font-family: monospace; color: #f59e0b; font-weight: 700;">
                            ≤ {{ $crit['reorder_point'] }} Pcs
                        </td>
                        <td style="text-align: center; font-family: monospace; color: #94a3b8;">
                            {{ $crit['max_stock'] }} Pcs
                        </td>
                        <td style="text-align: center;">
                            <span style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); padding: 3px 8px; border-radius: 4px; font-family: monospace; font-weight: 800;">
                                +{{ $crit['recommended_transfer'] }} Pcs
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.distribusi') }}" class="btn-action-sm btn-action-emerald" style="padding: 5px 10px; font-size: 11.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Buka menu distribusi DC untuk transfer stok">
                                <span>🚚</span> Penuhi dari DC
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #86efac; padding: 24px; font-weight: 600;">
                            ✓ Aman! Tidak ada varian produk toko yang berada di bawah ambang batas Reorder Point (ROP).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 3: PELACAKAN SURAT JALAN & TRANSFER STOK (STO LIVE TRACKING) -->
<div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; overflow: hidden; margin-bottom: 24px;">
    <div style="padding: 16px 20px; background: rgba(15, 23, 42, 0.8); border-bottom: 1px solid rgba(56, 189, 248, 0.2); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 15px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>🚚</span> Pelacakan Surat Transfer Stok (STO) Antar-Cabang Terkini
            </h3>
            <p style="font-size: 11.5px; color: #94a3b8; margin: 3px 0 0 0;">
                Monitoring status alur logistik dari Distribution Center (DC) ke Toko Ritel.
            </p>
        </div>
        <a href="{{ route('admin.distribusi') }}" class="btn-action-sm btn-action-primary" style="padding: 6px 14px; font-size: 12px; text-decoration: none;">
            <span>📦</span> Kelola Distribusi DC
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th>No. Dokumen STO</th>
                    <th>Cabang Pemohon</th>
                    <th>Item &amp; Kategori</th>
                    <th style="text-align: center;">Jumlah Pasok</th>
                    <th>Tingkat Urgensi</th>
                    <th>Status Distribusi</th>
                    <th>Waktu Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockTransferOrders as $sto)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">
                            {{ $sto->request_number }}
                        </td>
                        <td>
                            <strong style="color: #f8fafc;">{{ $sto->branch->name ?? 'Cabang Toko' }}</strong>
                            <div style="font-size: 11px; color: #94a3b8;">Pemohon: {{ $sto->employee->name ?? 'Kasir' }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #f1f5f9;">{{ $sto->item_name }}</div>
                            <div style="font-size: 11px; color: #64748b;">{{ $sto->item_category }}</div>
                        </td>
                        <td style="text-align: center; font-family: monospace; font-weight: 800; color: #f8fafc;">
                            {{ $sto->qty_requested }} {{ $sto->unit ?? 'Pcs' }}
                        </td>
                        <td>
                            @if($sto->urgency === 'urgent')
                                <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                                    🔴 Mendesak
                                </span>
                            @else
                                <span style="background: rgba(100, 116, 139, 0.2); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.4); padding: 2px 7px; border-radius: 4px; font-size: 11px;">
                                    Normal
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($sto->status === 'fulfilled')
                                <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                    ✓ Selesai Dipenuhi
                                </span>
                            @elseif($sto->status === 'draft')
                                <span style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                    🤖 Auto-Draft ROP
                                </span>
                            @else
                                <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                    ⏳ Menunggu Pengiriman DC
                                </span>
                            @endif
                        </td>
                        <td style="font-size: 11.5px; color: #94a3b8;">
                            {{ $sto->created_at ? $sto->created_at->format('d/m/Y H:i') : '-' }} WIB
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">
                            Belum ada riwayat transfer stok (STO) antar-cabang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
