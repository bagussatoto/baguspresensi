@extends('admin.dashboard.layout')

@section('title', 'Catatan atas Laporan Keuangan (CaLK) & Paket Cetak Lengkap')

@section('content')
<style>
    @media print {
        .hero-banner-card, .tab-nav-pills, .filter-box-card, .btn-no-print, .btn-action-sm, #filterCalkForm {
            display: none !important;
        }
        body {
            background: #fff !important;
            color: #000 !important;
            font-size: 11pt !important;
        }
        .dashboard-section-card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            background: #fff !important;
            color: #000 !important;
            margin-bottom: 16px !important;
            break-inside: avoid;
        }
        .print-page-break {
            page-break-before: always;
        }
        .print-header-only {
            display: block !important;
        }
        .print-signature-block {
            display: grid !important;
        }
        table tr td, table tr th {
            color: #000 !important;
            border-color: #cbd5e1 !important;
        }
        h2, h3, h4, p, li, span, strong {
            color: #0f172a !important;
        }
    }
    .print-header-only {
        display: none;
    }
</style>

<!-- PRINT-ONLY COVER / HEADER -->
<div class="print-header-only" style="margin-bottom: 24px; text-align: center; border-bottom: 3px double #0f172a; padding-bottom: 16px;">
    <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">PT MAYA GRAHA KENCANA (MGK)</h2>
    <h3 style="margin: 6px 0 4px; font-size: 16px; font-weight: 700; color: #1e293b;">PAKET LAPORAN KEUANGAN LENGKAP &amp; CATATAN ATAS LAPORAN KEUANGAN (CaLK)</h3>
    <p style="margin: 0; font-size: 12px; color: #475569;">
        Entitas / Unit: {{ $branchName }} &bull; Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }} &bull; Standar: SAK EP / SAK ETAP
    </p>
</div>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Catatan atas Laporan Keuangan (CaLK) &amp; Paket Cetak</h1>
        <p class="hero-desc-text">
            Bundel pelaporan keuangan formal siap cetak untuk keperluan audit eksternal, perbankan, maupun pajak: menyatukan <strong>Laba Rugi, Neraca Keuangan, Arus Kas,</strong> dan <strong>7 Catatan Kebijakan &amp; Rincian Akun</strong> dalam satu dokumen terpadu.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.calk', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; text-decoration: none;">
            <span>📥</span> Unduh Ringkasan CSV
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm btn-action-primary" style="padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer;">
            <span>🖨️</span> Cetak Paket Lengkap (PDF)
        </button>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER PERIODE & CABANG TERPADU -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 22px;">
    <form id="filterCalkForm" method="GET" action="{{ route('admin.akuntansi.calk') }}" style="display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap;">
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
            <button type="button" onclick="setPeriodThisMonth()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Bulan Ini</button>
            <button type="button" onclick="setPeriodYtd()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.06); color: #cbd5e1; height: 38px; padding: 0 12px; border: 1px solid rgba(255, 255, 255, 0.15); cursor: pointer;">Tahun Ini (YTD)</button>
        </div>

        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 18px; height: 38px; cursor: pointer;">
            <span>🔍</span> Susun Paket Laporan
        </button>
    </form>
</div>

<!-- RINGKASAN EKSEKUTIF PAKET LAPORAN -->
<div class="stats-grid-row" style="margin-bottom: 22px;">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Laba Bersih Usaha</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">📈</div>
        </div>
        <div class="metric-stat-value" style="color: {{ $pl['netProfit'] >= 0 ? '#34d399' : '#fb7185' }};">
            Rp {{ number_format($pl['netProfit'], 0, ',', '.') }}
        </div>
        <div class="metric-stat-desc">Pendapatan Rp {{ number_format($pl['totalRevenue'], 0, ',', '.') }}</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Total Aset Perusahaan</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">🏛️</div>
        </div>
        <div class="metric-stat-value" style="color: #38bdf8;">Rp {{ number_format($bs['totalAssets'], 0, ',', '.') }}</div>
        <div class="metric-stat-desc">{{ $bs['isBalanced'] ? '✅ Neraca Seimbang (Balanced)' : '⚠️ Cek Keseimbangan' }}</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Saldo Kas &amp; Bank Akhir</span>
            <div class="metric-stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;">💵</div>
        </div>
        <div class="metric-stat-value" style="color: #c084fc;">Rp {{ number_format($cf['actualEndingCashBalance'], 0, ',', '.') }}</div>
        <div class="metric-stat-desc">{{ $cf['isReconciled'] ? '✅ Arus Kas Terekonisiliasi' : '⚠️ Selisih Arus Kas' }}</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Status Audit &amp; Tutup Buku</span>
            <div class="metric-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">🔒</div>
        </div>
        <div class="metric-stat-value" style="color: {{ $lockedPeriod ? '#fbbf24' : '#34d399' }}; font-size: 17px;">
            {{ $lockedPeriod ? 'TERKUNCI (AUDITED)' : 'TERBUKA (UNAUDITED)' }}
        </div>
        <div class="metric-stat-desc">{{ $journalCount }} Jurnal Terposting pada periode</div>
    </div>
</div>

<!-- BAGIAN 1: RINGKASAN 3 LAPORAN KEUANGAN UTAMA -->
<div class="dashboard-section-card" style="margin-bottom: 24px;">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📊</div>
            <div>
                <h3 class="section-title">I. Ikhtisar Tiga Laporan Keuangan Utama ({{ $branchName }})</h3>
                <span class="section-subtitle">Periode {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</span>
            </div>
        </div>
    </div>

    <div style="padding: 22px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
        <!-- 1. Laba Rugi -->
        <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
            <h4 style="margin: 0 0 12px; color: #38bdf8; font-size: 14px; border-bottom: 1px solid rgba(56,189,248,0.25); padding-bottom: 6px;">1. Laporan Laba Rugi</h4>
            <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Pendapatan Usaha</td><td style="text-align: right; font-family: monospace; color: #fff;">Rp {{ number_format($pl['totalRevenue'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Harga Pokok Penjualan (HPP)</td><td style="text-align: right; font-family: monospace; color: #fbbf24;">(Rp {{ number_format($pl['totalCogs'], 0, ',', '.') }})</td></tr>
                <tr style="border-top: 1px solid rgba(255,255,255,0.1); font-weight: 700;"><td style="padding: 6px 0; color: #34d399;">Laba Kotor</td><td style="text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($pl['grossProfit'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Beban Operasional</td><td style="text-align: right; font-family: monospace; color: #fb7185;">(Rp {{ number_format($pl['totalExpense'], 0, ',', '.') }})</td></tr>
                <tr style="border-top: 2px solid #10b981; font-weight: 800;"><td style="padding: 8px 0 0; color: #fff;">Laba Bersih Operasional</td><td style="padding: 8px 0 0; text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($pl['netProfit'], 0, ',', '.') }}</td></tr>
            </table>
        </div>

        <!-- 2. Neraca Keuangan -->
        <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
            <h4 style="margin: 0 0 12px; color: #34d399; font-size: 14px; border-bottom: 1px solid rgba(16,185,129,0.25); padding-bottom: 6px;">2. Neraca Keuangan (Per {{ date('d/m/Y', strtotime($endDate)) }})</h4>
            <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                <tr style="font-weight: 700;"><td style="padding: 4px 0; color: #38bdf8;">Total Aset / Aktiva</td><td style="text-align: right; font-family: monospace; color: #38bdf8;">Rp {{ number_format($bs['totalAssets'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Total Kewajiban / Utang</td><td style="text-align: right; font-family: monospace; color: #fbbf24;">Rp {{ number_format($bs['totalLiabilities'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Ekuitas &amp; Modal Disetor</td><td style="text-align: right; font-family: monospace; color: #fff;">Rp {{ number_format($bs['totalEquity'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Laba Periode Berjalan</td><td style="text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($bs['currentPeriodNetProfit'], 0, ',', '.') }}</td></tr>
                <tr style="border-top: 2px solid #34d399; font-weight: 800;"><td style="padding: 8px 0 0; color: #fff;">Total Kewajiban + Ekuitas</td><td style="padding: 8px 0 0; text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($bs['totalLiabilitiesAndEquity'], 0, ',', '.') }}</td></tr>
            </table>
        </div>

        <!-- 3. Arus Kas -->
        <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
            <h4 style="margin: 0 0 12px; color: #c084fc; font-size: 14px; border-bottom: 1px solid rgba(168,85,247,0.25); padding-bottom: 6px;">3. Laporan Arus Kas</h4>
            <table style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Saldo Kas Awal Periode</td><td style="text-align: right; font-family: monospace; color: #94a3b8;">Rp {{ number_format($cf['openingCashBalance'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Arus Kas Operasional</td><td style="text-align: right; font-family: monospace; color: #38bdf8;">Rp {{ number_format($cf['totalOperatingCashFlow'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Arus Kas Investasi</td><td style="text-align: right; font-family: monospace; color: #fbbf24;">Rp {{ number_format($cf['totalInvestingCashFlow'], 0, ',', '.') }}</td></tr>
                <tr><td style="padding: 4px 0; color: #cbd5e1;">Arus Kas Pendanaan</td><td style="text-align: right; font-family: monospace; color: #c084fc;">Rp {{ number_format($cf['totalFinancingCashFlow'], 0, ',', '.') }}</td></tr>
                <tr style="border-top: 2px solid #c084fc; font-weight: 800;"><td style="padding: 8px 0 0; color: #fff;">Saldo Kas Akhir Periode</td><td style="padding: 8px 0 0; text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($cf['actualEndingCashBalance'], 0, ',', '.') }}</td></tr>
            </table>
        </div>
    </div>
</div>

<!-- BAGIAN 2: CATATAN ATAS LAPORAN KEUANGAN (CaLK) -->
<div class="dashboard-section-card print-page-break">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">📑</div>
            <div>
                <h3 class="section-title">II. Catatan atas Laporan Keuangan (CaLK)</h3>
                <span class="section-subtitle">Penjelasan kebijakan akuntansi dan rincian saldo pos-pos laporan keuangan</span>
            </div>
        </div>
    </div>

    <div style="padding: 24px; line-height: 1.7; font-size: 13px; color: #e2e8f0;">
        <!-- Catatan 1 -->
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 6px; color: #38bdf8; font-size: 14px;">Catatan 1: Gambaran Umum Entitas</h4>
            <p style="margin: 0; color: #cbd5e1;">
                <strong>PT Maya Graha Kencana (MGK)</strong> adalah badan usaha komersial yang bergerak di bidang penjualan ritel makanan &amp; minuman (F&amp;B), layanan komputasi &amp; akses internet (<em>warnet/cyber outlet</em>), serta jasa cetak (<em>printing</em>). Laporan ini menyajikan posisi dan kinerja keuangan untuk unit/cakupan: <strong>{{ $branchName }}</strong> pada periode <strong>{{ date('d F Y', strtotime($startDate)) }}</strong> hingga <strong>{{ date('d F Y', strtotime($endDate)) }}</strong>.
            </p>
        </div>

        <!-- Catatan 2 -->
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 6px; color: #38bdf8; font-size: 14px;">Catatan 2: Ikhtisar Kebijakan Akuntansi Penting</h4>
            <ul style="margin: 0; padding-left: 20px; color: #cbd5e1;">
                <li><strong>Dasar Penyusunan:</strong> Laporan keuangan disusun berdasarkan Standar Akuntansi Keuangan Entitas Privat (SAK EP / SAK ETAP) dengan basis akrual (<em>accrual basis</em>) dan sistem pencatatan berpasangan (<em>Double-Entry General Ledger</em>).</li>
                <li><strong>Kas &amp; Setara Kas:</strong> Mencakup uang tunai di laci kasir outlet (<code style="color:#38bdf8;">1-10001</code>), kas operasional kantor (<code style="color:#38bdf8;">1-10002</code>), dan saldo rekening bank (<code style="color:#38bdf8;">1-11xxx</code>).</li>
                <li><strong>Persediaan &amp; HPP:</strong> Nilai persediaan barang dagang F&amp;B dicatat sebesar harga perolehan. Harga Pokok Penjualan (HPP) diakui bersamaan saat transaksi penjualan POS diposting.</li>
                <li><strong>Pengakuan Pendapatan:</strong> Pendapatan penjualan F&amp;B, billing, dan print diakui pada saat transaksi kasir selesai dibayar. Pembatalan transaksi (VOID) dicatat sebagai pembalikan jurnal beserta slip fisik thermal.</li>
            </ul>
        </div>

        <!-- Catatan 3 -->
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 8px; color: #38bdf8; font-size: 14px;">Catatan 3: Rincian Kas &amp; Setara Kas (Per {{ date('d M Y', strtotime($endDate)) }})</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.15); color: #94a3b8;">
                        <th style="text-align: left; padding: 6px 8px;">Kode Akun</th>
                        <th style="text-align: left; padding: 6px 8px;">Nama Akun Kas / Bank</th>
                        <th style="text-align: right; padding: 6px 8px;">Saldo Akhir (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cf['cashAccountSummaries'] as $row)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                        <td style="padding: 6px 8px; font-family: monospace;">{{ $row['account']->account_code }}</td>
                        <td style="padding: 6px 8px;">{{ $row['account']->account_name }}</td>
                        <td style="padding: 6px 8px; text-align: right; font-family: monospace;">Rp {{ number_format($row['ending'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr style="font-weight: 700; border-top: 1px solid rgba(56,189,248,0.4);">
                        <td colspan="2" style="padding: 8px;">Total Kas &amp; Setara Kas</td>
                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #34d399;">Rp {{ number_format($cf['actualEndingCashBalance'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Catatan 4 -->
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 8px; color: #38bdf8; font-size: 14px;">Catatan 4: Piutang &amp; Persediaan Barang Dagang</h4>
            @if(count($receivableAndInventory) > 0)
            <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                <tbody>
                    @foreach($receivableAndInventory as $item)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                        <td style="width: 120px; padding: 6px 8px; font-family: monospace;">{{ $item['account']->account_code }}</td>
                        <td style="padding: 6px 8px;">{{ $item['account']->account_name }}</td>
                        <td style="padding: 6px 8px; text-align: right; font-family: monospace;">Rp {{ number_format($item['balance'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p style="margin: 0; color: #94a3b8;">Tidak terdapat saldo piutang atau persediaan tercatat pada posisi tanggal laporan ini.</p>
            @endif
        </div>

        <!-- Catatan 5 -->
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 6px; color: #38bdf8; font-size: 14px;">Catatan 5: Kewajiban, Ekuitas &amp; Laba Periode Berjalan</h4>
            <p style="margin: 0; color: #cbd5e1;">
                Total kewajiban jangka pendek dan titipan modal kasir per tanggal laporan sebesar <strong>Rp {{ number_format($bs['totalLiabilities'], 0, ',', '.') }}</strong>. Total ekuitas pemilik dan laba ditahan tercatat sebesar <strong>Rp {{ number_format($bs['totalEquity'], 0, ',', '.') }}</strong>, ditambah akumulasi Laba/(Rugi) periode berjalan sebesar <strong>Rp {{ number_format($bs['currentPeriodNetProfit'], 0, ',', '.') }}</strong>, sehingga Total Kewajiban &amp; Ekuitas mencapai <strong>Rp {{ number_format($bs['totalLiabilitiesAndEquity'], 0, ',', '.') }}</strong>.
            </p>
        </div>

        <!-- Catatan 6 -->
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 6px; color: #38bdf8; font-size: 14px;">Catatan 6: Kinerja Pendapatan, Margin &amp; Efisiensi Beban</h4>
            @php
                $grossMarginPct = $pl['totalRevenue'] > 0 ? round(($pl['grossProfit'] / $pl['totalRevenue']) * 100, 1) : 0;
                $netMarginPct = $pl['totalRevenue'] > 0 ? round(($pl['netProfit'] / $pl['totalRevenue']) * 100, 1) : 0;
            @endphp
            <p style="margin: 0; color: #cbd5e1;">
                Selama periode pelaporan, perusahaan membukukan Pendapatan Usaha sebesar <strong>Rp {{ number_format($pl['totalRevenue'], 0, ',', '.') }}</strong> dengan HPP sebesar <strong>Rp {{ number_format($pl['totalCogs'], 0, ',', '.') }}</strong> (Margin Laba Kotor: <strong>{{ $grossMarginPct }}%</strong>). Setelah dikurangi Beban Operasional sebesar <strong>Rp {{ number_format($pl['totalExpense'], 0, ',', '.') }}</strong>, diperoleh Laba Bersih Operasional sebesar <strong>Rp {{ number_format($pl['netProfit'], 0, ',', '.') }}</strong> (Margin Laba Bersih: <strong>{{ $netMarginPct }}%</strong>).
            </p>
        </div>

        <!-- Catatan 7 -->
        <div style="margin-bottom: 24px;">
            <h4 style="margin: 0 0 6px; color: #38bdf8; font-size: 14px;">Catatan 7: Status Pengendalian Internal &amp; Tutup Buku</h4>
            <p style="margin: 0; color: #cbd5e1;">
                Seluruh transaksi pada paket laporan ini bersumber dari <strong>{{ $journalCount }} entri jurnal</strong> berstatus <em>posted</em> yang tercatat dalam Audit Trail sistem ERP. Status periode per {{ date('d M Y', strtotime($endDate)) }} saat ini adalah: <strong>{{ $lockedPeriod ? 'TERKUNCI / CLOSED ('.$lockedPeriod->period_name.')' : 'TERBUKA / AKTIF' }}</strong>.
            </p>
        </div>

        <!-- BLOK TANDA TANGAN PENGESAHAN (TAMPIL SAAT CETAK & PREVIEW) -->
        <div class="print-signature-block" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 36px; padding-top: 20px; border-top: 1px dashed rgba(255,255,255,0.2); text-align: center;">
            <div>
                <div style="font-size: 12px; color: #94a3b8; margin-bottom: 56px;">Disiapkan Oleh,</div>
                <div style="font-weight: 700; border-top: 1px solid #64748b; display: inline-block; padding-top: 4px; min-width: 150px;">Staf Akuntansi</div>
            </div>
            <div>
                <div style="font-size: 12px; color: #94a3b8; margin-bottom: 56px;">Diperiksa Oleh,</div>
                <div style="font-weight: 700; border-top: 1px solid #64748b; display: inline-block; padding-top: 4px; min-width: 150px;">Manajer Keuangan</div>
            </div>
            <div>
                <div style="font-size: 12px; color: #94a3b8; margin-bottom: 56px;">Disetujui Oleh,</div>
                <div style="font-weight: 700; border-top: 1px solid #64748b; display: inline-block; padding-top: 4px; min-width: 150px;">Direktur / Pimpinan MGK</div>
            </div>
        </div>
    </div>
</div>

<script>
function setPeriodThisMonth() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
    document.getElementById('inputStartDate').value = `${y}-${m}-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    document.getElementById('filterCalkForm').submit();
}

function setPeriodYtd() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    document.getElementById('inputStartDate').value = `${y}-01-01`;
    document.getElementById('inputEndDate').value = `${y}-${m}-${d}`;
    document.getElementById('filterCalkForm').submit();
}
</script>
@endsection
