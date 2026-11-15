@extends('admin.dashboard.layout')

@section('title', 'Buku Besar (General Ledger)')

@section('content')
<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Buku Besar (General Ledger) SAK</h1>
        <p class="hero-desc-text">
            Kartu pergerakan akun individual yang merangkum saldo awal, riwayat mutasi debit/kredit, dan saldo akhir berjalan per tanggal transaksi dengan audit trail lengkap.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.neraca-saldo') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>⚖️</span> Neraca Saldo
        </a>
        <a href="{{ route('admin.akuntansi.jurnal') }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8;">
            <span>📖</span> Buka Jurnal Umum
        </a>
        <a href="{{ route('admin.akuntansi.buku-besar', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399;">
            <span>📥</span> Unduh CSV
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #fff; cursor: pointer;">
            <span>🖨️</span> Cetak / PDF
        </button>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER PILIH AKUN, CABANG & PERIODE -->
<div class="dashboard-section-card" style="padding: 18px 22px; margin-bottom: 22px;">
    <form id="filterBukuBesarForm" method="GET" action="{{ route('admin.akuntansi.buku-besar') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 260px;">
            <label class="form-label-custom">Pilih Akun Buku Besar *</label>
            <select name="account_id" class="form-input-custom" onchange="this.form.submit()" style="height: 38px;">
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                    {{ $acc->account_code }} - {{ $acc->account_name }} ({{ strtoupper($acc->account_category->value ?? (string) $acc->account_category) }})
                </option>
                @endforeach
            </select>
        </div>

        @if(isset($branches) && $branches->count() > 0)
        <div>
            <label class="form-label-custom">Cabang / Unit Bisnis</label>
            <select name="branch_id" class="form-input-custom" onchange="this.form.submit()" style="padding: 6px 12px; height: 38px;">
                <option value="">🏢 Semua Cabang</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ ($selectedBranchId ?? null) == $b->id ? 'selected' : '' }}>
                    {{ $b->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div style="display: flex; align-items: center; gap: 8px;">
            <div>
                <label class="form-label-custom">Dari Tanggal</label>
                <input type="date" id="inputGlStartDate" name="start_date" value="{{ $startDate }}" class="form-input-custom" style="width: 140px; height: 38px;">
            </div>
            <div>
                <label class="form-label-custom">Sampai Tanggal</label>
                <input type="date" id="inputGlEndDate" name="end_date" value="{{ $endDate }}" class="form-input-custom" style="width: 140px; height: 38px;">
            </div>
        </div>

        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="button" onclick="setGlToday()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15);">Hari Ini</button>
            <button type="button" onclick="setGlThisMonth()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15);">Bulan Ini</button>
            <button type="button" onclick="setGlYtd()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15);">Tahun Ini (YTD)</button>
        </div>

        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; height: 38px;">
            <span>🔍</span> Tampilkan Kartu
        </button>
    </form>
</div>

@if($selectedAccount)
<!-- RINGKASAN SALDO KARTU AKUN -->
<div class="stats-grid-row" style="margin-bottom: 22px;">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Akun Terpilih</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">🏷️</div>
        </div>
        <div class="metric-stat-value" style="font-size: 16px;">{{ $selectedAccount->account_name }}</div>
        <div class="metric-stat-desc">{{ $selectedAccount->account_code }} &bull; Saldo Normal: {{ strtoupper($selectedAccount->normal_balance->value ?? (string) $selectedAccount->normal_balance) }}</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Saldo Awal</span>
            <div class="metric-stat-icon" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8;">📅</div>
        </div>
        <div class="metric-stat-value">Rp {{ number_format($openingBalance, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Per {{ date('d/m/Y', strtotime($startDate)) }}</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Mutasi Bersih Periode</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">⚡</div>
        </div>
        <div class="metric-stat-value" style="font-size: 18px;">
            D: {{ number_format($totalDebitMutasi, 0, ',', '.') }} <br>
            <span style="font-size: 12px; color: #fbbf24; font-weight: normal;">K: {{ number_format($totalCreditMutasi, 0, ',', '.') }}</span>
        </div>
        <div class="metric-stat-desc">{{ count($mutasiLines) }} Transaksi Terlibat</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Saldo Akhir Berjalan</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #10b981;">💰</div>
        </div>
        <div class="metric-stat-value" style="color: #34d399;">Rp {{ number_format($endingBalance, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Posisi Per {{ date('d/m/Y', strtotime($endDate)) }}</div>
    </div>
</div>

<!-- TABEL BUKU BESAR -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📑</div>
            <div>
                <h3 class="section-title">Kartu Buku Besar: [{{ $selectedAccount->account_code }}] {{ $selectedAccount->account_name }}</h3>
                <span class="section-subtitle">Rentang Transaksi: {{ date('d M Y', strtotime($startDate)) }} &mdash; {{ date('d M Y', strtotime($endDate)) }}</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Tanggal</th>
                    <th style="width: 140px;">No. Jurnal</th>
                    <th>Keterangan / Memo Transaksi</th>
                    <th style="width: 130px; text-align: right;">Debit (Rp)</th>
                    <th style="width: 130px; text-align: right;">Kredit (Rp)</th>
                    <th style="width: 150px; text-align: right;">Saldo Berjalan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- BARIS SALDO AWAL -->
                <tr style="background: rgba(15, 23, 42, 0.6); font-weight: 600;">
                    <td>{{ date('d/m/Y', strtotime($startDate)) }}</td>
                    <td style="color: #94a3b8;">-</td>
                    <td><em>SALDO AWAL PERIODE</em></td>
                    <td style="text-align: right; color: #64748b;">-</td>
                    <td style="text-align: right; color: #64748b;">-</td>
                    <td style="text-align: right; color: #38bdf8; font-family: monospace;">
                        Rp {{ number_format($openingBalance, 0, ',', '.') }}
                    </td>
                </tr>

                @forelse($mutasiLines as $line)
                <tr>
                    <td>{{ $line->journalEntry->entry_date->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.akuntansi.jurnal', ['search' => $line->journalEntry->entry_number]) }}" style="font-family: monospace; color: #38bdf8; font-size: 11px; text-decoration: none;" title="Buka Voucher Jurnal Umum">
                            {{ $line->journalEntry->entry_number }} ↗
                        </a>
                    </td>
                    <td>
                        <div style="font-weight: 500; color: #f1f5f9;">{{ $line->journalEntry->description }}</div>
                        @if($line->memo)
                        <div style="font-size: 11px; color: #64748b;">Memo: {{ $line->memo }}</div>
                        @endif
                    </td>
                    <td style="text-align: right; font-family: monospace; color: {{ $line->debit > 0 ? '#34d399' : '#64748b' }};">
                        {{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; color: {{ $line->credit > 0 ? '#fbbf24' : '#64748b' }};">
                        {{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #e2e8f0;">
                        Rp {{ number_format($line->running_balance, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">
                        Tidak ada transaksi mutasi untuk akun ini pada rentang tanggal yang dipilih.
                    </td>
                </tr>
                @endforelse

                <!-- BARIS SALDO AKHIR -->
                <tr style="background: rgba(16, 185, 129, 0.08); font-weight: 700; border-top: 2px solid rgba(16, 185, 129, 0.3);">
                    <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Mutasi & Saldo Akhir:</td>
                    <td style="text-align: right; color: #34d399; font-family: monospace;">Rp {{ number_format($totalDebitMutasi, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #fbbf24; font-family: monospace;">Rp {{ number_format($totalCreditMutasi, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #34d399; font-family: monospace; font-size: 14px;">
                        Rp {{ number_format($endingBalance, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<script>
function setGlToday() {
    const now = new Date();
    const iso = now.toISOString().split('T')[0];
    document.getElementById('inputGlStartDate').value = iso;
    document.getElementById('inputGlEndDate').value = iso;
    document.getElementById('filterBukuBesarForm').submit();
}

function setGlThisMonth() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    document.getElementById('inputGlStartDate').value = `${y}-${m}-01`;
    document.getElementById('inputGlEndDate').value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    document.getElementById('filterBukuBesarForm').submit();
}

function setGlYtd() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    document.getElementById('inputGlStartDate').value = `${y}-01-01`;
    document.getElementById('inputGlEndDate').value = `${y}-${m}-${d}`;
    document.getElementById('filterBukuBesarForm').submit();
}
</script>
@endsection

