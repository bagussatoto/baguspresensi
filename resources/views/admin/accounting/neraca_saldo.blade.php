@extends('admin.dashboard.layout')

@section('title', 'Neraca Saldo (Trial Balance)')

@section('content')
<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Neraca Saldo (Trial Balance)</h1>
        <p class="hero-desc-text">
            Daftar saldo penutup seluruh akun buku besar untuk memverifikasi kebenaran matematis bahwa Total Debit tepat sama dengan Total Kredit (*Prinsip Keseimbangan SAK*).
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.laba-rugi') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📈</span> Laba Rugi
        </a>
        <a href="{{ route('admin.akuntansi.neraca') }}" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
            <span>🏛️</span> Neraca Keuangan
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER PERIODE -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.akuntansi.neraca-saldo') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; color: #94a3b8;">Dari Tanggal:</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px;">
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; color: #94a3b8;">Sampai Tanggal:</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px;">
        </div>
        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 16px;">
            <span>🔍</span> Hitung Neraca Saldo
        </button>
    </form>
</div>

<!-- STATUS KESEIMBANGAN BANNER -->
<div style="background: {{ $isBalanced ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)' }}; border: 1px solid {{ $isBalanced ? 'rgba(16, 185, 129, 0.35)' : 'rgba(244, 63, 94, 0.35)' }}; border-radius: 14px; padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <span style="font-size: 28px;">{{ $isBalanced ? '⚖️' : '⚠️' }}</span>
        <div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 700; color: #fff;">
                {{ $isBalanced ? 'PEMBUKUAN DINYATAKAN SEIMBANG (BALANCED)' : 'PERINGATAN: TERDAPAT SELISIH PEMBUKUAN' }}
            </h3>
            <p style="font-size: 12px; color: #94a3b8;">
                {{ $isBalanced ? 'Total saldo debit sama persis dengan total saldo kredit sesuai standar akuntansi berpasangan.' : 'Periksa kembali jurnal yang belum seimbang atau akun yang belum terpetakan.' }}
            </p>
        </div>
    </div>
    <div style="text-align: right;">
        <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Total Saldo Berimbang:</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: {{ $isBalanced ? '#34d399' : '#fb7185' }};">
            Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
        </div>
    </div>
</div>

<!-- TABEL NERACA SALDO -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">⚖️</div>
            <div>
                <h3 class="section-title">Neraca Saldo Buku Besar Maya Graha Kencana</h3>
                <span class="section-subtitle">Posisi Per {{ date('d M Y', strtotime($endDate)) }}</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Kode Akun</th>
                    <th>Nama Akun Buku Besar</th>
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
                <tr>
                    <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">{{ $row['account']->account_code }}</td>
                    <td style="font-weight: 600; color: #f8fafc;">{{ $row['account']->account_name }}</td>
                    <td>
                        <span class="badge-status badge-cyan" style="font-size: 10px;">
                            {{ strtoupper($row['account']->account_category->value ?? $row['account']->account_category) }}
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
                <tr style="background: rgba(15, 23, 42, 0.9); font-weight: 800; border-top: 2px solid #38bdf8; font-size: 14px;">
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
@endsection

