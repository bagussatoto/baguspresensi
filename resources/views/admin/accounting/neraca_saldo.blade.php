@extends('admin.dashboard.layout')

@section('title', 'Neraca Saldo (Trial Balance)')

@section('content')
<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Dashboard Neraca Saldo (Trial Balance) SAK</h1>
        <p class="hero-desc-text">
            Validasi matematis keseimbangan total debit dan total kredit seluruh akun buku besar per cabang &amp; konsolidasi, terhubung langsung ke mutasi kasir dan pembukuan jurnal otomatis.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.buku-besar') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📑</span> Kartu Buku Besar
        </a>
        <a href="{{ route('admin.akuntansi.neraca-saldo', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8;">
            <span>📥</span> Unduh CSV
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #fff; cursor: pointer;">
            <span>🖨️</span> Cetak / PDF
        </button>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- STATUS KESEIMBANGAN BANNER -->
<div style="background: {{ $isBalanced ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)' }}; border: 1px solid {{ $isBalanced ? 'rgba(16, 185, 129, 0.35)' : 'rgba(244, 63, 94, 0.35)' }}; border-radius: 14px; padding: 18px 24px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <span style="font-size: 32px;">{{ $isBalanced ? '⚖️' : '⚠️' }}</span>
        <div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 17px; font-weight: 700; color: #fff; margin: 0 0 4px;">
                {{ $isBalanced ? 'PEMBUKUAN DINYATAKAN SEIMBANG (BALANCED 100%)' : 'PERINGATAN: TERDAPAT SELISIH KESEIMBANGAN PEMBUKUAN' }}
            </h3>
            <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                {{ $isBalanced ? 'Total saldo debit sama persis dengan total saldo kredit sesuai standar akuntansi berpasangan (Double-Entry SAK).' : 'Periksa kembali jurnal yang belum seimbang atau akun yang belum terpetakan.' }}
            </p>
        </div>
    </div>
    <div style="text-align: right;">
        <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600;">Total Saldo Berimbang:</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: {{ $isBalanced ? '#34d399' : '#fb7185' }};">
            Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
        </div>
    </div>
</div>

<!-- STATS GRID RINGKASAN POSISI KEUANGAN -->
<div class="stats-grid-row" style="margin-bottom: 22px;">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">1. Total Aset &amp; Kas</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">💰</div>
        </div>
        <div class="metric-stat-value" style="color: #34d399; font-size: 18px;">
            Rp {{ number_format($categoryTotals['asset'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Kas, Bank, Piutang &amp; Persediaan</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">2. Kewajiban &amp; Utang</span>
            <div class="metric-stat-icon" style="background: rgba(244, 63, 94, 0.15); color: #fb7185;">💳</div>
        </div>
        <div class="metric-stat-value" style="color: #fb7185; font-size: 18px;">
            Rp {{ number_format($categoryTotals['liability'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Utang Usaha &amp; Titipan Modal Kasir</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">3. Ekuitas &amp; Modal</span>
            <div class="metric-stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;">🏛️</div>
        </div>
        <div class="metric-stat-value" style="color: #c084fc; font-size: 18px;">
            Rp {{ number_format($categoryTotals['equity'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Modal Disetor &amp; Laba Ditahan</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">4. Pendapatan Usaha</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📈</div>
        </div>
        <div class="metric-stat-value" style="color: #38bdf8; font-size: 18px;">
            Rp {{ number_format($categoryTotals['revenue'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Akumulasi Penjualan &amp; Jasa Periode</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">5. Beban &amp; Biaya</span>
            <div class="metric-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">📉</div>
        </div>
        <div class="metric-stat-value" style="color: #fbbf24; font-size: 18px;">
            Rp {{ number_format($categoryTotals['expense'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">HPP, Operasional &amp; Selisih Toleransi</div>
    </div>
</div>

<!-- FILTER PERIODE & CABANG TERPADU -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 22px;">
    <form id="filterNeracaSaldoForm" method="GET" action="{{ route('admin.akuntansi.neraca-saldo') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        @if(isset($branches) && $branches->count() > 0)
        <div>
            <label class="form-label-custom">Cabang / Unit Bisnis</label>
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
                <label class="form-label-custom">Dari Tanggal</label>
                <input type="date" id="inputStartDate" name="start_date" value="{{ $startDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px; height: 38px;">
            </div>
            <div>
                <label class="form-label-custom">Sampai Tanggal</label>
                <input type="date" id="inputEndDate" name="end_date" value="{{ $endDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px; height: 38px;">
            </div>
        </div>

        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="button" onclick="setPeriodToday()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15);">Hari Ini</button>
            <button type="button" onclick="setPeriodThisMonth()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15);">Bulan Ini</button>
            <button type="button" onclick="setPeriodYtd()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15);">Tahun Ini (YTD)</button>
        </div>

        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 18px; height: 38px;">
            <span>🔍</span> Hitung Neraca Saldo
        </button>

        <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
            <input type="text" id="trialSearchInput" placeholder="Cari nama / kode akun..." class="form-input-custom" style="width: 200px; height: 38px; font-size: 12px; padding: 6px 12px;" oninput="filterTrialTable()">
        </div>
    </form>
</div>

<!-- TABEL NERACA SALDO INTERAKTIF (DRILL-DOWN KE BUKU BESAR) -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">⚖️</div>
            <div>
                <h3 class="section-title">Neraca Saldo Buku Besar Maya Graha Kencana</h3>
                <span class="section-subtitle">Posisi Per {{ date('d M Y', strtotime($endDate)) }} &bull; Klik nama akun untuk membuka Kartu Buku Besar (Drill-Down)</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table" id="trialBalanceTable">
            <thead>
                <tr>
                    <th style="width: 110px;">Kode Akun</th>
                    <th>Nama Akun Buku Besar (Klik untuk Drill-Down)</th>
                    <th style="width: 120px;">Kategori</th>
                    <th style="width: 140px; text-align: right;">Mutasi Debit</th>
                    <th style="width: 140px; text-align: right;">Mutasi Kredit</th>
                    <th style="width: 150px; text-align: right;">Saldo Akhir Debit (Rp)</th>
                    <th style="width: 150px; text-align: right;">Saldo Akhir Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trialRows as $row)
                @if($row['mutation_debit'] > 0 || $row['mutation_credit'] > 0 || $row['final_debit'] > 0 || $row['final_credit'] > 0)
                <tr class="trial-row" data-code="{{ strtolower($row['account']->account_code) }}" data-name="{{ strtolower($row['account']->account_name) }}">
                    <td style="font-family: monospace; font-weight: 700;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $row['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="color: #38bdf8; text-decoration: none;" title="Buka Kartu Buku Besar">
                            {{ $row['account']->account_code }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $row['account']->id, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $selectedBranchId]) }}" style="font-weight: 600; color: #f8fafc; text-decoration: none;" title="Buka Kartu Buku Besar Akun Ini">
                            {{ $row['account']->account_name }} <span style="font-size: 11px; color: #38bdf8;">↗</span>
                        </a>
                    </td>
                    <td>
                        <span class="badge-status badge-cyan" style="font-size: 10px;">
                            {{ strtoupper($row['account']->account_category->value ?? (string) $row['account']->account_category) }}
                        </span>
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #94a3b8;">
                        {{ $row['mutation_debit'] > 0 ? number_format($row['mutation_debit'], 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; color: #94a3b8;">
                        {{ $row['mutation_credit'] > 0 ? number_format($row['mutation_credit'], 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: {{ $row['final_debit'] > 0 ? '#34d399' : '#64748b' }};">
                        {{ $row['final_debit'] > 0 ? number_format($row['final_debit'], 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: {{ $row['final_credit'] > 0 ? '#fbbf24' : '#64748b' }};">
                        {{ $row['final_credit'] > 0 ? number_format($row['final_credit'], 0, ',', '.') : '-' }}
                    </td>
                </tr>
                @endif
                @endforeach

                <!-- GRAND TOTAL -->
                <tr style="background: rgba(15, 23, 42, 0.95); font-weight: 800; border-top: 2px solid #38bdf8; font-size: 14px;">
                    <td colspan="5" style="text-align: right; text-transform: uppercase; color: #fff; padding: 14px 16px;">
                        TOTAL NERACA SALDO:
                    </td>
                    <td style="text-align: right; color: #34d399; font-family: monospace; padding: 14px 16px;">
                        Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; color: #fbbf24; font-family: monospace; padding: 14px 16px;">
                        Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTrialTable() {
    const q = document.getElementById('trialSearchInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.trial-row');
    rows.forEach(r => {
        const code = r.dataset.code || '';
        const name = r.dataset.name || '';
        if (!q || code.includes(q) || name.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function setPeriodToday() {
    const now = new Date();
    const iso = now.toISOString().split('T')[0];
    document.getElementById('inputStartDate').value = iso;
    document.getElementById('inputEndDate').value = iso;
    document.getElementById('filterNeracaSaldoForm').submit();
}

function setPeriodThisMonth() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    document.getElementById('inputStartDate').value = `${y}-${m}-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    document.getElementById('filterNeracaSaldoForm').submit();
}

function setPeriodYtd() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    document.getElementById('inputStartDate').value = `${y}-01-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${d}`;
    document.getElementById('filterNeracaSaldoForm').submit();
}
</script>
@endsection

