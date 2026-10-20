@extends('admin.dashboard.layout')

@section('title', 'Dashboard Utama')

@section('content')
<!-- HERO OVERVIEW BANNER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Dashboard Operasional & Administrasi</h1>
        <p class="hero-desc-text">
            Pusat kendali manajemen terpadu Maya Graha Kencana. Seluruh modul utama telah dipisahkan ke dalam halaman khusus yang fokus, rapi, dan cepat diakses.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.data') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 16px; font-size: 12px;">
            <span>🗄️</span> Buka Data Terpadu
        </a>
        <a href="{{ route('admin.distribusi') }}" class="btn-action-sm btn-action-emerald" style="padding: 10px 16px; font-size: 12px;">
            <span>🚚</span> Penerimaan Distribusi
        </a>
    </div>
</div>

<!-- TOP 4 KPI METRICS -->
<div class="stats-grid-row">
    <a href="{{ route('admin.data', ['tab' => 'tab-presensi']) }}" class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Presensi Hari Ini</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">👥</div>
        </div>
        <div class="metric-stat-value">{{ $attendanceSummary['present_count'] }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">/ {{ $attendanceSummary['total_employees'] }} Staff</span></div>
        <div class="metric-stat-desc">
            <span style="color: #34d399;">{{ $attendanceSummary['percentage'] }}% Hadir</span> &bull; {{ $attendanceSummary['late_count'] }} Terlambat
        </div>
    </a>

    <a href="{{ route('admin.data', ['tab' => 'tab-billing']) }}" class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Billing Warnet Hari Ini</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">💰</div>
        </div>
        <div class="metric-stat-value">Rp {{ number_format($billingPerShift['total_daily_billing'], 0, ',', '.') }}</div>
        <div class="metric-stat-desc">
            Akumulasi 4 shift operasional warnet
        </div>
    </a>

    <a href="{{ route('admin.distribusi') }}" class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Penerimaan Distribusi</span>
            <div class="metric-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">🚚</div>
        </div>
        <div class="metric-stat-value">{{ $stockTransfers->where('status', 'received')->count() }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Diterima</span></div>
        <div class="metric-stat-desc">
            {{ $stockTransfersCount }} batch dalam pengiriman
        </div>
    </a>

    <a href="{{ route('admin.stok') }}" class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Ketersediaan Produk</span>
            <div class="metric-stat-icon" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">📦</div>
        </div>
        <div class="metric-stat-value">{{ $stockStats['total_sku'] }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">SKU</span></div>
        <div class="metric-stat-desc">
            <span style="color: #fbbf24;">{{ $stockStats['low_stock'] }} Menipis</span> &bull; <span style="color: #fb7185;">{{ $stockStats['out_of_stock'] }} Kosong</span>
        </div>
    </a>
</div>

<!-- ============================================================== -->
<!-- NAVIGASI MODUL UTAMA DASHBOARD (TERPISAH PER HALAMAN) -->
<!-- ============================================================== -->
<div style="margin-bottom: 24px;">
    <div style="margin-bottom: 14px;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff;">
            Pilih Halaman Menu Utama
        </h2>
        <span style="font-size: 12px; color: var(--text-muted);">
            Setiap modul memiliki halaman mandiri yang bersih tanpa menumpuk ke bawah
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <!-- KARTU MODUL 1: DATA -->
        <div class="dashboard-section-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        🗄️
                    </div>
                    <span class="badge-status badge-sky">6 Sub-Menu</span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    1. Data Terpadu
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Manajemen profil admin & cabang, pengelolaan data staf karyawan, pengaturan jadwal shift, data pendapatan billing, pemantauan presensi, rekap jam kerja, dan SOP.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">👤 Profil</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">📅 Jadwal Shift</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">💰 Billing</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">⏱️ Jam Kerja</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">📜 SOP</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass);">
                <a href="{{ route('admin.data') }}" class="btn-action-sm btn-action-primary" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px;">
                    Buka Halaman Data Terpadu →
                </a>
            </div>
        </div>

        <!-- KARTU MODUL 2: DISTRIBUSI -->
        <div class="dashboard-section-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        🚚
                    </div>
                    <span class="badge-status {{ $stockTransfersCount > 0 ? 'badge-amber' : 'badge-emerald' }}">
                        {{ $stockTransfersCount }} Batch Menunggu
                    </span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    2. Penerimaan Stok Distribusi
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Penerimaan fisik barang transfer stok dari gudang pusat ke loker meja kasir cabang MGK dan monitoring permintaan tambahan stok kasir.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">📦 Transfer Masuk</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">✓ Verifikasi Terima</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">📝 Request Kasir</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass);">
                <a href="{{ route('admin.distribusi') }}" class="btn-action-sm btn-action-emerald" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px;">
                    Buka Halaman Distribusi →
                </a>
            </div>
        </div>

        <!-- KARTU MODUL 3: STOK -->
        <div class="dashboard-section-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        📦
                    </div>
                    <span class="badge-status badge-emerald">{{ $stockStats['total_sku'] }} SKU</span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    3. Stok (Ketersediaan Produk)
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Ketersediaan fisik inventaris produk seperti kertas HVS A4 & F4, kulkas showcase minuman, etalase makanan ringan, dan suku cadang hardware.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #34d399;">Aman: {{ $stockStats['safe_stock'] }}</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #fbbf24;">Menipis: {{ $stockStats['low_stock'] }}</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #fb7185;">Habis: {{ $stockStats['out_of_stock'] }}</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass);">
                <a href="{{ route('admin.stok') }}" class="btn-action-sm btn-action-primary" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px;">
                    Buka Ketersediaan Stok →
                </a>
            </div>
        </div>

        <!-- KARTU MODUL 4: LAPORAN -->
        <div class="dashboard-section-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        📊
                    </div>
                    <span class="badge-status badge-emerald">Audit Kasir</span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    4. Laporan Rekapitulasi
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Rekapitulasi pendapatan harian, penjualan makanan & minuman kasir, billing warnet per shift, dan audit uang tunai fisik laci kasir.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">💰 Omset Hari Ini</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">🖨️ Cetak / PDF</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">💵 Kas Laci</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass);">
                <a href="{{ route('admin.laporan') }}" class="btn-action-sm btn-action-emerald" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px;">
                    Buka Laporan Keuangan →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- RINGKASAN AKTIVITAS TERKINI (SNAPSHOT CEPAT HARI INI) -->
<!-- ============================================================== -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px;">
    <!-- LOG PRESENSI TERBARU -->
    <div class="dashboard-section-card" style="margin-bottom: 0;">
        <div class="section-card-header">
            <div class="section-header-left">
                <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">👥</div>
                <div>
                    <h3 class="section-title">Presensi Staf Terkini</h3>
                    <span class="section-subtitle">Aktivitas clock-in presensi hari ini</span>
                </div>
            </div>
            <a href="{{ route('admin.data', ['tab' => 'tab-presensi']) }}" class="btn-action-sm btn-action-primary">
                Lihat Semua
            </a>
        </div>
        <div class="section-card-body" style="padding: 0;">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Shift</th>
                        <th>Clock-In</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceSummary['today_records']->take(5) as $rec)
                    <tr>
                        <td style="font-weight: 600; color: #fff;">{{ $rec->employee->name ?? 'Karyawan' }}</td>
                        <td>{{ $rec->shift->name ?? '-' }}</td>
                        <td style="font-family: monospace; color: #34d399;">{{ $rec->clock_in_at ? $rec->clock_in_at->format('H:i') : '-' }} WIB</td>
                        <td>
                            @php
                                $statusVal = $rec->status instanceof \BackedEnum ? $rec->status->value : (string) $rec->status;
                            @endphp
                            @if($statusVal === 'present')
                                <span class="badge-status badge-emerald">Tepat Waktu</span>
                            @elseif($statusVal === 'late')
                                <span class="badge-status badge-amber">Terlambat</span>
                            @else
                                <span class="badge-status badge-sky">{{ ucfirst($statusVal) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 18px;">
                            Belum ada presensi staf hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PENGIRIMAN DISTRIBUSI TERAKHIR -->
    <div class="dashboard-section-card" style="margin-bottom: 0;">
        <div class="section-card-header">
            <div class="section-header-left">
                <div class="section-header-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">🚚</div>
                <div>
                    <h3 class="section-title">Distribusi Masuk Terkini</h3>
                    <span class="section-subtitle">Pengiriman dari gudang pusat</span>
                </div>
            </div>
            <a href="{{ route('admin.distribusi') }}" class="btn-action-sm btn-action-primary">
                Lihat Semua
            </a>
        </div>
        <div class="section-card-body" style="padding: 0;">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Item</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockTransfers->take(5) as $st)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">{{ $st->transfer_code }}</td>
                        <td style="font-weight: 600; color: #fff;">{{ $st->variant_name ?? 'Produk' }}</td>
                        <td style="font-weight: 700; color: #34d399; font-family: 'Outfit';">+{{ $st->qty }} pcs</td>
                        <td>
                            @if($st->status === 'received')
                                <span class="badge-status badge-emerald">Diterima</span>
                            @else
                                <span class="badge-status badge-amber">Pengiriman</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 18px;">
                            Tidak ada pengiriman stok aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
