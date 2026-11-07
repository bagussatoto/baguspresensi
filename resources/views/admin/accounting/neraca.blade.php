@extends('admin.dashboard.layout')

@section('title', 'Neraca Keuangan (Balance Sheet)')

@section('content')
<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Neraca Keuangan (Balance Sheet)</h1>
        <p class="hero-desc-text">
            Potret posisi kekayaan dan struktur permodalan perusahaan pada tanggal tertentu: **Total Aset = Total Kewajiban + Total Ekuitas Modal**.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.laba-rugi') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📈</span> Buka Laba Rugi
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER PER TANGGAL (AS OF DATE) -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.akuntansi.neraca') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; color: #94a3b8;">Posisi Per Tanggal (Cut-off):</label>
            <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-input-custom" style="padding: 6px 12px; width: 160px;">
        </div>
        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 16px;">
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
                <h3 class="section-title" style="color: #38bdf8;">AKTIVA / ASET PERUSAHAAN</h3>
            </div>
        </div>

        <div style="padding: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                @foreach($assetList as $item)
                @if($item['balance'] != 0)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="width: 110px; padding: 8px 10px; font-family: monospace; color: #94a3b8;">{{ $item['account']->account_code }}</td>
                    <td style="padding: 8px 10px; color: #f8fafc;">{{ $item['account']->account_name }}</td>
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
                <h3 class="section-title" style="color: #34d399;">PASIVA (KEWAJIBAN & EKUITAS)</h3>
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
                    <td style="width: 110px; padding: 6px 10px; font-family: monospace; color: #94a3b8;">{{ $item['account']->account_code }}</td>
                    <td style="padding: 6px 10px; color: #f8fafc;">{{ $item['account']->account_name }}</td>
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
                    <td style="width: 110px; padding: 6px 10px; font-family: monospace; color: #94a3b8;">{{ $item['account']->account_code }}</td>
                    <td style="padding: 6px 10px; color: #f8fafc;">{{ $item['account']->account_name }}</td>
                    <td style="padding: 6px 10px; text-align: right; font-family: monospace; color: #e2e8f0;">
                        Rp {{ number_format($item['balance'], 0, ',', '.') }}
                    </td>
                </tr>
                @endif
                @endforeach
                <!-- LABA BERJALAN -->
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); background: rgba(16, 185, 129, 0.08);">
                    <td style="width: 110px; padding: 6px 10px; font-family: monospace; color: #34d399;">3-30001</td>
                    <td style="padding: 6px 10px; color: #34d399; font-weight: 600;">Laba Bersih Periode Berjalan (P&L)</td>
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
@endsection

