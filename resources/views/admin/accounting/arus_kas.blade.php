@extends('admin.dashboard.layout')

@section('title', 'Laporan Arus Kas (Cash Flow Statement)')

@section('content')
<style>
    @media print {
        .hero-banner-card, .tab-nav-pills, .filter-box-card, .btn-no-print, .btn-action-sm, #filterArusKasForm {
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
    <h3 style="margin: 4px 0; font-size: 16px; font-weight: 700; color: #334155;">LAPORAN ARUS KAS (CASH FLOW STATEMENT)</h3>
    <p style="margin: 0; font-size: 12px; color: #64748b;">
        Unit/Cabang: {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang #'.$selectedBranchId) : 'Konsolidasi Seluruh Cabang' }} &bull; Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}
    </p>
</div>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Laporan Arus Kas (Cash Flow Statement)</h1>
        <p class="hero-desc-text">
            Aliran kas masuk &amp; keluar riil berdasarkan Metode Langsung (PSAK 202 / SAK EP) yang dikelompokkan menjadi Aktivitas Operasional, Investasi, dan Pendanaan, serta rekonsiliasi saldo Kas &amp; Bank.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.arus-kas', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; text-decoration: none;">
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
    <form id="filterArusKasForm" method="GET" action="{{ route('admin.akuntansi.arus-kas') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
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
            <span>🔍</span> Tampilkan Arus Kas
        </button>
    </form>
</div>

<!-- 4 KARTU KPI ARUS KAS -->
<div class="stats-grid-row" style="margin-bottom: 22px;">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Saldo Kas Awal Periode</span>
            <div class="metric-stat-icon" style="background: rgba(148, 163, 184, 0.15); color: #cbd5e1;">🏦</div>
        </div>
        <div class="metric-stat-value" style="color: #cbd5e1;">Rp {{ number_format($openingCashBalance, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Posisi Kas &amp; Bank sebelum {{ date('d M Y', strtotime($startDate)) }}</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Arus Kas Operasional</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">⚡</div>
        </div>
        <div class="metric-stat-value" style="color: {{ $totalOperatingCashFlow >= 0 ? '#38bdf8' : '#fb7185' }};">
            Rp {{ number_format($totalOperatingCashFlow, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Dari penjualan POS, stok &amp; beban usaha</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Kenaikan / (Penurunan) Kas</span>
            <div class="metric-stat-icon" style="background: {{ $netCashChange >= 0 ? 'rgba(16, 185, 129, 0.15); color: #34d399;' : 'rgba(244, 63, 94, 0.15); color: #fb7185;' }}">📊</div>
        </div>
        <div class="metric-stat-value" style="color: {{ $netCashChange >= 0 ? '#34d399' : '#fb7185' }};">
            {{ $netCashChange >= 0 ? '+' : '' }}Rp {{ number_format($netCashChange, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Total pergerakan kas bersih periode berjalan</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Saldo Kas Akhir Periode</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">💰</div>
        </div>
        <div class="metric-stat-value" style="color: #10b981;">Rp {{ number_format($actualEndingCashBalance, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">
            {{ $isReconciled ? '✅ Reconciled 100% dengan Buku Besar Kas/Bank' : '⚠️ Perlu pengecekan rekonsiliasi' }}
        </div>
    </div>
</div>

<!-- LAPORAN ARUS KAS FORMAL -->
<div class="dashboard-section-card" style="margin-bottom: 24px;">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">💵</div>
            <div>
                <h3 class="section-title">Laporan Arus Kas Komprehensif (Metode Langsung)</h3>
                <span class="section-subtitle">
                    {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang Terpilih') : 'Konsolidasi Seluruh Cabang' }} &bull; Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>
    </div>

    <div style="padding: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <!-- BAGIAN A: AKTIVITAS OPERASIONAL -->
            <tr style="background: rgba(56, 189, 248, 0.1); font-weight: 700;">
                <td colspan="2" style="padding: 10px 14px; color: #38bdf8; font-size: 14px;">A. ARUS KAS DARI AKTIVITAS OPERASIONAL</td>
                <td style="padding: 10px 14px; text-align: right; width: 200px;"></td>
            </tr>
            @forelse($operatingItems as $item)
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="width: 130px; padding: 8px 24px; font-family: monospace; color: #94a3b8;">{{ $item['code'] }}</td>
                <td style="padding: 8px 10px; color: #f8fafc;">{{ $item['label'] }}</td>
                <td style="padding: 8px 14px; text-align: right; font-family: monospace; color: {{ $item['amount'] >= 0 ? '#34d399' : '#fb7185' }};">
                    {{ $item['amount'] < 0 ? '(' : '' }}Rp {{ number_format(abs($item['amount']), 0, ',', '.') }}{{ $item['amount'] < 0 ? ')' : '' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="padding: 12px 24px; color: #64748b;">Tidak ada transaksi kas aktivitas operasional pada periode ini.</td></tr>
            @endforelse
            <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700; border-top: 1px solid rgba(56, 189, 248, 0.3);">
                <td colspan="2" style="padding: 10px 24px; color: #fff;">ARUS KAS BERSIH DARI AKTIVITAS OPERASIONAL</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #38bdf8; font-size: 14px;">
                    {{ $totalOperatingCashFlow < 0 ? '(' : '' }}Rp {{ number_format(abs($totalOperatingCashFlow), 0, ',', '.') }}{{ $totalOperatingCashFlow < 0 ? ')' : '' }}
                </td>
            </tr>

            <!-- BAGIAN B: AKTIVITAS INVESTASI -->
            <tr style="background: rgba(245, 158, 11, 0.1); font-weight: 700;">
                <td colspan="2" style="padding: 10px 14px; color: #fbbf24; font-size: 14px; padding-top: 24px;">B. ARUS KAS DARI AKTIVITAS INVESTASI</td>
                <td style="padding: 10px 14px; text-align: right; padding-top: 24px;"></td>
            </tr>
            @forelse($investingItems as $item)
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="width: 130px; padding: 8px 24px; font-family: monospace; color: #94a3b8;">{{ $item['code'] }}</td>
                <td style="padding: 8px 10px; color: #f8fafc;">{{ $item['label'] }}</td>
                <td style="padding: 8px 14px; text-align: right; font-family: monospace; color: {{ $item['amount'] >= 0 ? '#34d399' : '#fb7185' }};">
                    {{ $item['amount'] < 0 ? '(' : '' }}Rp {{ number_format(abs($item['amount']), 0, ',', '.') }}{{ $item['amount'] < 0 ? ')' : '' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="padding: 12px 24px; color: #64748b;">Tidak ada transaksi kas aktivitas investasi pada periode ini.</td></tr>
            @endforelse
            <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700; border-top: 1px solid rgba(245, 158, 11, 0.3);">
                <td colspan="2" style="padding: 10px 24px; color: #fff;">ARUS KAS BERSIH DARI AKTIVITAS INVESTASI</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #fbbf24; font-size: 14px;">
                    {{ $totalInvestingCashFlow < 0 ? '(' : '' }}Rp {{ number_format(abs($totalInvestingCashFlow), 0, ',', '.') }}{{ $totalInvestingCashFlow < 0 ? ')' : '' }}
                </td>
            </tr>

            <!-- BAGIAN C: AKTIVITAS PENDANAAN -->
            <tr style="background: rgba(168, 85, 247, 0.1); font-weight: 700;">
                <td colspan="2" style="padding: 10px 14px; color: #c084fc; font-size: 14px; padding-top: 24px;">C. ARUS KAS DARI AKTIVITAS PENDANAAN</td>
                <td style="padding: 10px 14px; text-align: right; padding-top: 24px;"></td>
            </tr>
            @forelse($financingItems as $item)
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <td style="width: 130px; padding: 8px 24px; font-family: monospace; color: #94a3b8;">{{ $item['code'] }}</td>
                <td style="padding: 8px 10px; color: #f8fafc;">{{ $item['label'] }}</td>
                <td style="padding: 8px 14px; text-align: right; font-family: monospace; color: {{ $item['amount'] >= 0 ? '#34d399' : '#fb7185' }};">
                    {{ $item['amount'] < 0 ? '(' : '' }}Rp {{ number_format(abs($item['amount']), 0, ',', '.') }}{{ $item['amount'] < 0 ? ')' : '' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="padding: 12px 24px; color: #64748b;">Tidak ada transaksi kas aktivitas pendanaan pada periode ini.</td></tr>
            @endforelse
            <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 700; border-top: 1px solid rgba(168, 85, 247, 0.3);">
                <td colspan="2" style="padding: 10px 24px; color: #fff;">ARUS KAS BERSIH DARI AKTIVITAS PENDANAAN</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #c084fc; font-size: 14px;">
                    {{ $totalFinancingCashFlow < 0 ? '(' : '' }}Rp {{ number_format(abs($totalFinancingCashFlow), 0, ',', '.') }}{{ $totalFinancingCashFlow < 0 ? ')' : '' }}
                </td>
            </tr>

            <!-- REKONSILIASI AKHIR -->
            <tr style="border-top: 2px solid rgba(255,255,255,0.15); font-weight: 700;">
                <td colspan="2" style="padding: 14px 20px; color: #f8fafc;">KENAIKAN / (PENURUNAN) KAS BERSIH (A + B + C)</td>
                <td style="padding: 14px 14px; text-align: right; font-family: monospace; color: {{ $netCashChange >= 0 ? '#34d399' : '#fb7185' }}; font-size: 15px;">
                    {{ $netCashChange < 0 ? '(' : '' }}Rp {{ number_format(abs($netCashChange), 0, ',', '.') }}{{ $netCashChange < 0 ? ')' : '' }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                <td colspan="2" style="padding: 10px 20px; color: #cbd5e1;">SALDO KAS &amp; SETARA KAS AWAL PERIODE</td>
                <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: #cbd5e1;">
                    Rp {{ number_format($openingCashBalance, 0, ',', '.') }}
                </td>
            </tr>
            <tr style="background: linear-gradient(90deg, rgba(16, 185, 129, 0.25), rgba(6, 182, 212, 0.25)); font-weight: 800; border: 2px solid #10b981;">
                <td colspan="2" style="padding: 16px 20px; color: #fff; font-size: 16px; text-transform: uppercase;">
                    SALDO KAS &amp; SETARA KAS AKHIR PERIODE
                </td>
                <td style="padding: 16px 14px; text-align: right; font-family: monospace; color: #34d399; font-size: 18px;">
                    Rp {{ number_format($actualEndingCashBalance, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        @if($internalTransferTotal > 0)
        <div style="margin-top: 16px; padding: 12px 16px; background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; font-size: 12px; color: #cbd5e1;">
            ℹ️ <strong>Catatan Mutasi Internal Kas/Bank (Non-Arus Kas Bersih):</strong> Terdapat perpindahan dana internal antar laci kasir ke bank/kas kantor (seperti Setoran Shift Kasir) sebesar <strong style="color: #38bdf8;">Rp {{ number_format($internalTransferTotal, 0, ',', '.') }}</strong>. Transaksi ini tidak mengubah total saldo Kas &amp; Setara Kas konsolidasi.
        </div>
        @endif
    </div>
</div>

<!-- RINCIAN SALDO PER AKUN KAS & BANK -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">🏦</div>
            <div>
                <h3 class="section-title">Rincian Posisi Akun Kas &amp; Bank</h3>
                <span class="section-subtitle">Klik nama akun untuk membuka Buku Besar (Drill-Down)</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 120px;">Kode Akun</th>
                    <th>Nama Akun Kas / Bank</th>
                    <th style="text-align: right;">Saldo Awal</th>
                    <th style="text-align: right;">Kas Masuk (Debit)</th>
                    <th style="text-align: right;">Kas Keluar (Kredit)</th>
                    <th style="text-align: right;">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashAccountSummaries as $row)
                <tr>
                    <td style="font-family: monospace;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $row['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #38bdf8; text-decoration: none; font-weight: 700;">
                            {{ $row['account']->account_code }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $row['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none; font-weight: 600;">
                            {{ $row['account']->account_name }} <span style="font-size: 11px; color: #38bdf8;">↗</span>
                        </a>
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #94a3b8;">Rp {{ number_format($row['opening'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($row['inflow'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-family: monospace; color: #fb7185;">Rp {{ number_format($row['outflow'], 0, ',', '.') }}</td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #fff;">Rp {{ number_format($row['ending'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function setPeriodToday() {
    const now = new Date();
    const iso = now.toISOString().split('T')[0];
    document.getElementById('inputStartDate').value = iso;
    document.getElementById('inputEndDate').value = iso;
    document.getElementById('filterArusKasForm').submit();
}

function setPeriodThisMonth() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    document.getElementById('inputStartDate').value = `${y}-${m}-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    document.getElementById('filterArusKasForm').submit();
}

function setPeriodYtd() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    document.getElementById('inputStartDate').value = `${y}-01-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${d}`;
    document.getElementById('filterArusKasForm').submit();
}
</script>
@endsection
