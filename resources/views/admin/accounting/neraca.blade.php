@extends('admin.dashboard.layout')

@section('title', 'Neraca Keuangan (Balance Sheet)')

@section('content')
<style>
    @media print {
        .hero-banner-card, .tab-nav-pills, .filter-box-card, .btn-no-print, .btn-action-sm, #filterNeracaForm {
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
    <h3 style="margin: 4px 0; font-size: 16px; font-weight: 700; color: #334155;">NERACA KEUANGAN (BALANCE SHEET)</h3>
    <p style="margin: 0; font-size: 12px; color: #64748b;">
        Unit/Cabang: {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang #'.$selectedBranchId) : 'Konsolidasi Seluruh Cabang' }} &bull; Posisi Per Tanggal: {{ date('d F Y', strtotime($asOfDate)) }}
    </p>
</div>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Neraca Keuangan (Balance Sheet)</h1>
        <p class="hero-desc-text">
            Potret posisi kekayaan dan struktur permodalan perusahaan pada tanggal tertentu: **Total Aset = Total Kewajiban + Total Ekuitas Modal**.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.neraca', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; text-decoration: none;">
            <span>📥</span> Unduh CSV
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #fff; cursor: pointer;">
            <span>🖨️</span> Cetak / PDF
        </button>
        <a href="{{ route('admin.akuntansi.laba-rugi') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📈</span> Buka Laba Rugi
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER PER TANGGAL & CABANG TERPADU -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form id="filterNeracaForm" method="GET" action="{{ route('admin.akuntansi.neraca') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
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
                <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Posisi Per Tanggal (Cut-off)</label>
                <input type="date" id="inputAsOfDate" name="as_of_date" value="{{ $asOfDate }}" class="form-input-custom" style="padding: 6px 12px; width: 160px; height: 38px;">
            </div>
        </div>

        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="button" onclick="setAsOfToday()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Hari Ini</button>
            <button type="button" onclick="setAsOfEndOfMonth()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Akhir Bulan Ini</button>
            <button type="button" onclick="setAsOfEndOfYear()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Akhir Tahun Ini</button>
        </div>

        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 18px; height: 38px; cursor: pointer;">
            <span>🔍</span> Hitung Posisi Neraca
        </button>
    </form>
</div>

<!-- STATUS KESEIMBANGAN PERSAMAAN AKUNTANSI -->
<div style="background: {{ $isBalanced ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)' }}; border: 1px solid {{ $isBalanced ? 'rgba(16, 185, 129, 0.35)' : 'rgba(244, 63, 94, 0.35)' }}; border-radius: 14px; padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <span style="font-size: 28px;">{{ $isBalanced ? '🏛️' : '⚠️' }}</span>
        <div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 700; color: #fff;">
                PERSAMAAN DASAR AKUNTANSI: ASET = KEWAJIBAN + EKUITAS
            </h3>
            <p style="font-size: 12px; color: #94a3b8;">
                {{ $isBalanced ? 'Posisi aset seimbang sempurna dengan akumulasi kewajiban dan modal pemilik (termasuk laba periode berjalan).' : 'Terdapat ketidakseimbangan antara nilai aset dan kewajiban/ekuitas.' }}
            </p>
        </div>
    </div>
    <div style="display: flex; gap: 20px;">
        <div style="text-align: right;">
            <div style="font-size: 11px; color: #94a3b8;">TOTAL ASET:</div>
            <div style="font-family: monospace; font-size: 18px; font-weight: 800; color: #38bdf8;">
                Rp {{ number_format($totalAssets, 0, ',', '.') }}
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 11px; color: #94a3b8;">KEWAJIBAN + EKUITAS:</div>
            <div style="font-family: monospace; font-size: 18px; font-weight: 800; color: {{ $isBalanced ? '#34d399' : '#fb7185' }};">
                Rp {{ number_format($totalLiabilitiesAndEquity, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<!-- FORMAT NERACA T-ACCOUNT 2 KOLOM -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- SISI KIRI: ASET -->
    <div class="dashboard-section-card">
        <div class="section-card-header" style="background: rgba(56, 189, 248, 0.1);">
            <div class="section-header-left">
                <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8;">📥</div>
                <div>
                    <h3 class="section-title" style="color: #38bdf8;">AKTIVA / ASET PERUSAHAAN</h3>
                    <span class="section-subtitle">
                        {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang Terpilih') : 'Konsolidasi Seluruh Cabang' }}
                    </span>
                </div>
            </div>
        </div>

        <div style="padding: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                @foreach($assetList as $item)
                @if($item['balance'] != 0)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="width: 110px; padding: 8px 10px; font-family: monospace;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #38bdf8; text-decoration: none;" title="Buka Buku Besar">
                            {{ $item['account']->account_code }}
                        </a>
                    </td>
                    <td style="padding: 8px 10px;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none;" title="Buka Buku Besar">
                            {{ $item['account']->account_name }} <span style="font-size: 11px; color: #38bdf8;">↗</span>
                        </a>
                    </td>
                    <td style="padding: 8px 10px; text-align: right; font-family: monospace; color: #e2e8f0;">
                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                @endforeach
            </table>
        </div>

        <div style="padding: 16px 20px; background: rgba(15, 23, 42, 0.8); border-top: 2px solid #38bdf8; display: flex; justify-content: space-between; align-items: center;">
            <strong style="color: #fff; text-transform: uppercase;">TOTAL KEKAYAAN ASET:</strong>
            <span style="font-family: monospace; font-size: 16px; font-weight: 800; color: #38bdf8;">
                Rp {{ number_format($totalAssets, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- SISI KANAN: KEWAJIBAN & EKUITAS -->
    <div class="dashboard-section-card">
        <div class="section-card-header" style="background: rgba(16, 185, 129, 0.1);">
            <div class="section-header-left">
                <div class="section-header-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">🏛️</div>
                <div>
                    <h3 class="section-title" style="color: #34d399;">PASIVA (KEWAJIBAN & EKUITAS)</h3>
                    <span class="section-subtitle">
                        {{ $selectedBranchId ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang Terpilih') : 'Konsolidasi Seluruh Cabang' }}
                    </span>
                </div>
            </div>
        </div>

        <div style="padding: 20px;">
            <!-- SUB-SECTION 1: KEWAJIBAN -->
            <div style="font-size: 12px; font-weight: 700; color: #fbbf24; text-transform: uppercase; margin-bottom: 8px; border-bottom: 1px solid rgba(245, 158, 11, 0.3); padding-bottom: 4px;">
                I. KEWAJIBAN / HUTANG USAHA
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px;">
                @forelse($liabilityList as $item)
                @if($item['balance'] != 0)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="width: 110px; padding: 6px 10px; font-family: monospace;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #fbbf24; text-decoration: none;" title="Buka Buku Besar">
                            {{ $item['account']->account_code }}
                        </a>
                    </td>
                    <td style="padding: 6px 10px;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none;" title="Buka Buku Besar">
                            {{ $item['account']->account_name }} <span style="font-size: 11px; color: #fbbf24;">↗</span>
                        </a>
                    </td>
                    <td style="padding: 6px 10px; text-align: right; font-family: monospace; color: #e2e8f0;">
                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="3" style="padding: 6px 10px; color: #64748b;">Tidak ada saldo kewajiban.</td></tr>
                @endforelse
                <tr style="font-weight: 700; color: #fff;">
                    <td colspan="2" style="padding: 6px 10px;">Subtotal Kewajiban:</td>
                    <td style="padding: 6px 10px; text-align: right; font-family: monospace; color: #fbbf24;">
                        Rp {{ number_format($totalLiabilities, 0, ',', '.') }}
                    </td>
                </tr>
            </table>

            <!-- SUB-SECTION 2: EKUITAS -->
            <div style="font-size: 12px; font-weight: 700; color: #34d399; text-transform: uppercase; margin-bottom: 8px; border-bottom: 1px solid rgba(16, 185, 129, 0.3); padding-bottom: 4px; margin-top: 16px;">
                II. EKUITAS & MODAL PEMILIK
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                @foreach($equityList as $item)
                @if($item['balance'] != 0)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="width: 110px; padding: 6px 10px; font-family: monospace;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #34d399; text-decoration: none;" title="Buka Buku Besar">
                            {{ $item['account']->account_code }}
                        </a>
                    </td>
                    <td style="padding: 6px 10px;">
                        <a href="{{ route('admin.akuntansi.buku-besar', ['account_id' => $item['account']->id, 'end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #f8fafc; text-decoration: none;" title="Buka Buku Besar">
                            {{ $item['account']->account_name }} <span style="font-size: 11px; color: #34d399;">↗</span>
                        </a>
                    </td>
                    <td style="padding: 6px 10px; text-align: right; font-family: monospace; color: #e2e8f0;">
                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                @endforeach
                <!-- LABA BERJALAN -->
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(16, 185, 129, 0.08);">
                    <td style="width: 110px; padding: 6px 10px; font-family: monospace;">
                        <a href="{{ route('admin.akuntansi.laba-rugi', ['end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #34d399; text-decoration: none;" title="Buka Laba Rugi">
                            3-30001
                        </a>
                    </td>
                    <td style="padding: 6px 10px;">
                        <a href="{{ route('admin.akuntansi.laba-rugi', ['end_date' => $asOfDate, 'branch_id' => $selectedBranchId]) }}" style="color: #34d399; font-weight: 600; text-decoration: none;" title="Buka Laporan Laba Rugi Komprehensif">
                            Laba Bersih Periode Berjalan (P&L) <span style="font-size: 11px; color: #34d399;">↗</span>
                        </a>
                    </td>
                    <td style="padding: 6px 10px; text-align: right; font-family: monospace; color: #34d399; font-weight: 700;">
                        Rp {{ number_format($currentPeriodNetProfit, 0, ',', '.') }}
                    </td>
                </tr>
                <tr style="font-weight: 700; color: #fff;">
                    <td colspan="2" style="padding: 6px 10px;">Subtotal Ekuitas & Laba:</td>
                    <td style="padding: 6px 10px; text-align: right; font-family: monospace; color: #34d399;">
                        Rp {{ number_format($totalEquityWithProfit, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        <div style="padding: 16px 20px; background: rgba(15, 23, 42, 0.8); border-top: 2px solid #34d399; display: flex; justify-content: space-between; align-items: center;">
            <strong style="color: #fff; text-transform: uppercase;">TOTAL KEWAJIBAN & EKUITAS:</strong>
            <span style="font-family: monospace; font-size: 16px; font-weight: 800; color: #34d399;">
                Rp {{ number_format($totalLiabilitiesAndEquity, 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

<script>
function setAsOfToday() {
    const now = new Date();
    const iso = now.toISOString().split('T')[0];
    document.getElementById('inputAsOfDate').value = iso;
    document.getElementById('filterNeracaForm').submit();
}

function setAsOfEndOfMonth() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    document.getElementById('inputAsOfDate').value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    document.getElementById('filterNeracaForm').submit();
}

function setAsOfEndOfYear() {
    const now = new Date();
    const y = now.getFullYear();
    document.getElementById('inputAsOfDate').value = `${y}-12-31`;
    document.getElementById('filterNeracaForm').submit();
}
</script>
@endsection
