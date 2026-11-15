@extends('admin.dashboard.layout')

@section('title', 'Laporan Laba Rugi (Income Statement)')

@section('content')
<style>
    @media print {
        .hero-banner-card, .tab-nav-pills, .filter-box-card, .btn-no-print, .btn-action-sm, #filterLabaRugiForm {
            display: none !important;
        }
        body {
            background: #fff !important;
            color: #000 !important;
        }
        .dashboard-section-card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            background: #fff !important;
            color: #000 !important;
            margin-bottom: 15px !important;
        }
        .stats-grid-row {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 10px !important;
            margin-bottom: 15px !important;
        }
        .metric-stat-card {
            background: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
            padding: 10px !important;
        }
        .print-header-only {
            display: block !important;
        }
        table tr td, table tr th {
            color: #000 !important;
            border-color: #cbd5e1 !important;
        }
        a {
            color: inherit !important;
            text-decoration: none !important;
        }
    }
    .print-header-only {
        display: none;
    }
</style>

<!-- PRINT-ONLY HEADER -->
<div class="print-header-only" style="margin-bottom: 20px; text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px;">
    <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a;">MAYA GRAHA KENCANA (MGK)</h2>
    <h3 style="margin: 4px 0; font-size: 16px; font-weight: 700; color: #334155;">LAPORAN LABA RUGI (INCOME STATEMENT)</h3>
    <p style="margin: 0; font-size: 12px; color: #64748b;">
        Unit/Cabang: {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang #'.$selectedBranchId) : 'Konsolidasi Seluruh Cabang' }} &bull; Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}
    </p>
</div>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Laporan Laba Rugi (Income Statement)</h1>
        <p class="hero-desc-text">
            Kinerja keuangan operasional ritel & warnet: Pendapatan Usaha dikurangi Beban Pokok Penjualan (HPP) menghasilkan Laba Kotor, kemudian dikurangi Beban Operasional untuk menghasilkan Laba Bersih Usaha.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.laba-rugi', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; text-decoration: none;">
            <span>📥</span> Unduh CSV
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #fff; cursor: pointer;">
            <span>🖨️</span> Cetak / PDF
        </button>
        <a href="{{ route('admin.akuntansi.neraca') }}" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
            <span>🏛️</span> Buka Neraca Keuangan
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER PERIODE & CABANG TERPADU -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form id="filterLabaRugiForm" method="GET" action="{{ route('admin.akuntansi.laba-rugi') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        @if(isset($branches) && $branches->count() > 0)
        <div>
            <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Cabang / Unit Bisnis</label>
            <select name="branch_id" class="form-input-custom" onchange="this.form.submit()" style="padding: 6px 12px; height: 38px;">
                <option value="">🏢 Semua Cabang (Konsolidasi)</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div style="display: flex; align-items: center; gap: 8px;">
            <div>
                <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Dari Tanggal</label>
                <input type="date" id="inputStartDate" name="start_date" value="{{ $startDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px; height: 38px;">
            </div>
            <div>
                <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Sampai Tanggal</label>
                <input type="date" id="inputEndDate" name="end_date" value="{{ $endDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px; height: 38px;">
            </div>
        </div>

        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="button" onclick="setPeriodToday()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Hari Ini</button>
            <button type="button" onclick="setPeriodThisMonth()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Bulan Ini</button>
            <button type="button" onclick="setPeriodYtd()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Tahun Ini (YTD)</button>
        </div>

        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 18px; height: 38px; cursor: pointer;">
            <span>🔍</span> Tampilkan Laba Rugi
        </button>
    </form>
</div>

<!-- 4 KARTU RINGKASAN P&L -->
<div class="stats-grid-row">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Total Pendapatan Usaha</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📈</div>
        </div>
        <div class="metric-stat-value" style="color: #38bdf8;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Penjualan F&B, Billing & Print</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Harga Pokok Penjualan (HPP)</span>
            <div class="metric-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">📦</div>
        </div>
        <div class="metric-stat-value" style="color: #fbbf24;">Rp {{ number_format($totalCogs, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Modal Bahan Baku & Kertas</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Laba Kotor (Gross Profit)</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">💎</div>
        </div>
        <div class="metric-stat-value" style="color: #34d399;">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Margin: {{ $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0 }}%</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Laba Bersih Operasional</span>
            <div class="metric-stat-icon" style="background: {{ $netProfit >= 0 ? 'rgba(16, 185, 129, 0.2); color: #10b981;' : 'rgba(244, 63, 94, 0.2); color: #f43f5e;' }}">💰</div>
        </div>
        <div class="metric-stat-value" style="color: {{ $netProfit >= 0 ? '#10b981;' : '#fb7185;' }};">
            Rp {{ number_format($netProfit, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">{{ $netProfit >= 0 ? 'Surplus Laba Periode Berjalan' : 'Defisit Operasional' }}</div>
    </div>
</div>

<!-- LAPORAN LABA RUGI FORMAL TABEL -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📊</div>
            <div>
                <h3 class="section-title">Laporan Laba Rugi Komprehensif</h3>
                <span class="section-subtitle">
                    {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang Terpilih') : 'Konsolidasi Seluruh Cabang' }} &bull; Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>
    </div>

    <div style="padding: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <!-- BAGIAN 1: PENDAPATAN USAHA -->
            <tr style="background: rgba(56, 189, 248, 0.1); font-weight: 700;">
                <td colspan="2" style="padding: 10px 14px; color: #38bdf8; font-size: 14px;">1. PENDAPATAN USAHA (REVENUE)</td>
                <td style="padding: 10px 14px; text-align: right; width: 180px;"></td>
            </tr>
            @forelse($revenueList as $item)
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="width: 130px; padding: 8px 24px; font-family: monospace;">
                    <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #38bdf8; text-decoration: none;" title="Buka Buku Besar Akun Ini">
                        {{ $item['account']->account_code }}
                    </a>
                </td>
                <td style="padding: 8px 10px;">
                    <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none;" title="Buka Buku Besar Akun Ini">
                        {{ $item['account']->account_name }} <span style="font-size: 11px; color: #38bdf8;">↗</span>
                    </a>
                </td>
                <td style="padding: 8px 14px; text-align: right; font-family: monospace; color: #e2e8f0;">
                    Rp {{ number_format($item['amount'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="padding: 12px 24px; color: #64748b;">Belum ada akun pendapatan.</td></tr>
            @endforelse
            <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700; border-top: 1px solid rgba(56, 189, 248, 0.3);">
                <td colspan="2" style="padding: 10px 24px; color: #fff;">TOTAL PENDAPATAN USAHA</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #38bdf8; font-size: 14px;">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </td>
            </tr>

            <!-- BAGIAN 2: HARGA POKOK PENJUALAN -->
            <tr style="background: rgba(245, 158, 11, 0.1); font-weight: 700;">
                <td colspan="2" style="padding: 10px 14px; color: #fbbf24; font-size: 14px; padding-top: 24px;">2. HARGA POKOK PENJUALAN (HPP)</td>
                <td style="padding: 10px 14px; text-align: right; padding-top: 24px;"></td>
            </tr>
            @forelse($cogsList as $item)
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="width: 130px; padding: 8px 24px; font-family: monospace;">
                    <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #fbbf24; text-decoration: none;" title="Buka Buku Besar Akun Ini">
                        {{ $item['account']->account_code }}
                    </a>
                </td>
                <td style="padding: 8px 10px;">
                    <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none;" title="Buka Buku Besar Akun Ini">
                        {{ $item['account']->account_name }} <span style="font-size: 11px; color: #fbbf24;">↗</span>
                    </a>
                </td>
                <td style="padding: 8px 14px; text-align: right; font-family: monospace; color: #e2e8f0;">
                    Rp {{ number_format($item['amount'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="padding: 12px 24px; color: #64748b;">Belum ada akun HPP.</td></tr>
            @endforelse
            <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700; border-top: 1px solid rgba(245, 158, 11, 0.3);">
                <td colspan="2" style="padding: 10px 24px; color: #fff;">TOTAL HARGA POKOK PENJUALAN</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #fbbf24; font-size: 14px;">
                    Rp {{ number_format($totalCogs, 0, ',', '.') }}
                </td>
            </tr>

            <!-- SUB-TOTAL: LABA KOTOR -->
            <tr style="background: rgba(16, 185, 129, 0.12); font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3);">
                <td colspan="2" style="padding: 12px 20px; color: #34d399; font-size: 15px;">LABA KOTOR (GROSS PROFIT)</td>
                <td style="padding: 12px 14px; text-align: right; font-family: monospace; color: #34d399; font-size: 16px;">
                    Rp {{ number_format($grossProfit, 0, ',', '.') }}
                </td>
            </tr>

            <!-- BAGIAN 3: BEBAN OPERASIONAL -->
            <tr style="background: rgba(244, 63, 94, 0.1); font-weight: 700;">
                <td colspan="2" style="padding: 10px 14px; color: #fb7185; font-size: 14px; padding-top: 24px;">3. BEBAN OPERASIONAL (OPERATING EXPENSES)</td>
                <td style="padding: 10px 14px; text-align: right; padding-top: 24px;"></td>
            </tr>
            @forelse($expenseList as $item)
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="width: 130px; padding: 8px 24px; font-family: monospace;">
                    <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #fb7185; text-decoration: none;" title="Buka Buku Besar Akun Ini">
                        {{ $item['account']->account_code }}
                    </a>
                </td>
                <td style="padding: 8px 10px;">
                    <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none;" title="Buka Buku Besar Akun Ini">
                        {{ $item['account']->account_name }} <span style="font-size: 11px; color: #fb7185;">↗</span>
                    </a>
                </td>
                <td style="padding: 8px 14px; text-align: right; font-family: monospace; color: #e2e8f0;">
                    Rp {{ number_format($item['amount'], 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="padding: 12px 24px; color: #64748b;">Belum ada akun beban tercatat.</td></tr>
            @endforelse
            <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700; border-top: 1px solid rgba(244, 63, 94, 0.3);">
                <td colspan="2" style="padding: 10px 24px; color: #fff;">TOTAL BEBAN OPERASIONAL</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #fb7185; font-size: 14px;">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </td>
            </tr>

            <!-- GRAND TOTAL: LABA BERSIH -->
            <tr style="background: linear-gradient(90deg, rgba(16, 185, 129, 0.25), rgba(6, 182, 212, 0.25)); font-weight: 800; border: 2px solid #10b981;">
                <td colspan="2" style="padding: 16px 20px; color: #fff; font-size: 16px; text-transform: uppercase;">
                    LABA BERSIH OPERASIONAL (NET PROFIT)
                </td>
                <td style="padding: 16px 14px; text-align: right; font-family: monospace; color: #34d399; font-size: 18px;">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
function setPeriodToday() {
    const now = new Date();
    const iso = now.toISOString().split('T')[0];
    document.getElementById('inputStartDate').value = iso;
    document.getElementById('inputEndDate').value = iso;
    document.getElementById('filterLabaRugiForm').submit();
}

function setPeriodThisMonth() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    document.getElementById('inputStartDate').value = `${y}-${m}-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    document.getElementById('filterLabaRugiForm').submit();
}

function setPeriodYtd() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    document.getElementById('inputStartDate').value = `${y}-01-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${d}`;
    document.getElementById('filterLabaRugiForm').submit();
}
</script>
@endsection
