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

<!-- PUSAT ANTREAN OTORISASI & TINDAKAN CEPAT (JIKA ADA ANTREAN) -->
@php
    $totalPendingActions = ($employeeApprovalsTotal ?? 0) + ($pendingShiftDepositsCount ?? 0) + ($billingPendingCount ?? 0);
@endphp
@if($totalPendingActions > 0)
<div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95)); border: 1px solid rgba(244, 63, 94, 0.4); border-radius: 14px; padding: 18px 22px; margin-bottom: 24px; box-shadow: 0 10px 25px rgba(244, 63, 94, 0.12);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(244, 63, 94, 0.2); color: #fb7185; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                🔔
            </div>
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Pusat Antrean Otorisasi & Verifikasi Admin</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Terdapat <strong>{{ $totalPendingActions }} tugas penting</strong> yang memerlukan tindakan dan persetujuan Anda segera:</span>
            </div>
        </div>
        <span class="badge-status badge-rose" style="font-size: 11px; padding: 4px 10px;">
            ⚠️ {{ $totalPendingActions }} Menunggu Tindakan
        </span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
        @if(($employeeApprovalsTotal ?? 0) > 0)
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-weight: 700; color: #38bdf8; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <span>📝</span> Permohonan Karyawan ({{ $employeeApprovalsTotal }})
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                    {{ $pendingLeavesCount ?? 0 }} Cuti/Sakit &bull; {{ $pendingShiftSwapsCount ?? 0 }} Tukar Shift &bull; {{ $pendingCorrectionsCount ?? 0 }} Koreksi
                </div>
            </div>
            <a href="{{ route('admin.presensi.approval') }}" class="btn-action-sm btn-action-primary" style="padding: 6px 12px; font-size: 11.5px; white-space: nowrap;">
                Tinjau →
            </a>
        </div>
        @endif

        @if(($pendingShiftDepositsCount ?? 0) > 0)
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-weight: 700; color: #34d399; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <span>📥</span> Setoran Kas Kasir ({{ $pendingShiftDepositsCount }})
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                    Uang fisik kas laci shift menunggu verifikasi masuk bank
                </div>
            </div>
            <a href="{{ route('admin.penjualan.serah-terima') }}" class="btn-action-sm btn-action-emerald" style="padding: 6px 12px; font-size: 11.5px; white-space: nowrap;">
                Verifikasi Kas →
            </a>
        </div>
        @endif

        @if(($billingPendingCount ?? 0) > 0)
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(244, 63, 94, 0.25); border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-weight: 700; color: #fb7185; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                    <span>⚡</span> Penyesuaian Billing & Print ({{ $billingPendingCount }})
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                    Klaim mati listrik / kertas print rusak kasir
                </div>
            </div>
            <a href="{{ route('admin.penjualan.penyesuaian-billing') }}" class="btn-action-sm btn-action-primary" style="padding: 6px 12px; font-size: 11.5px; white-space: nowrap; background: #e11d48; border-color: #f43f5e;">
                Otorisasi →
            </a>
        </div>
        @endif
    </div>
</div>
@endif

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
                    <span class="badge-status badge-emerald">Laba Kotor: {{ $profitAnalytics['overall']['margin'] ?? 0 }}%</span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    4. Laporan Rekapitulasi & Laba Kotor
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Analisis laba kotor F&B, pemakaian counter printer Canon USB, billing warnet per shift, dan audit uang tunai fisik laci kasir.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 700; padding: 3px 8px; border-radius: 6px;">📈 Laba Rp {{ number_format($profitAnalytics['overall']['gross_profit'] ?? 0, 0, ',', '.') }}</span>
                    <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-weight: 700; padding: 3px 8px; border-radius: 6px;">🖨️ {{ $profitAnalytics['print']['total_pages'] ?? 0 }} Lbr Print</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">💵 Audit Kasir</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass);">
                <a href="{{ route('admin.laporan') }}" class="btn-action-sm btn-action-emerald" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px;">
                    Buka Laporan Keuangan →
                </a>
            </div>
        </div>

        <!-- KARTU MODUL 5: PENYESUAIAN BILLING & PRINT -->
        <div class="dashboard-section-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(244, 63, 94, 0.15); color: #fb7185; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        ⚡
                    </div>
                    @php
                        $pendingCount = $billingPendingCount ?? \App\Models\ShiftSalesSummary::where(function($q) {
                            $q->where('billing_adjustment_status', 'pending_approval')
                              ->orWhere('print_adjustment_status', 'pending_approval');
                        })->count();
                    @endphp
                    <span class="badge-status {{ $pendingCount > 0 ? 'badge-rose' : 'badge-emerald' }}">
                        {{ $pendingCount > 0 ? $pendingCount . ' Menunggu Persetujuan' : 'Semua Terverifikasi' }}
                    </span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    5. Persetujuan Penyesuaian Billing & Print
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Otorisasi penyesuaian billing internet (kompensasi mati listrik) dan print kertas (kertas macet / tinta rusak / cetak toko) dengan dual-control anti-fraud guna mencegah kebocoran kas.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">🛡️ Dual-Control</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">⚡ Billing Internet</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">🖨️ Kertas Rusak & Toko</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">📝 Audit Trail</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass); display: flex; gap: 8px;">
                <a href="{{ route('admin.penjualan.penyesuaian-billing') }}" class="btn-action-sm btn-action-primary" style="flex: 1; justify-content: center; padding: 10px; font-size: 12px; background: linear-gradient(135deg, #e11d48, #be123c); border-color: #f43f5e;">
                    Penyesuaian Billing →
                </a>
                <a href="{{ route('admin.penjualan.serah-terima') }}" class="btn-action-sm btn-action-emerald" style="justify-content: center; padding: 10px 14px; font-size: 12px;" title="Verifikasi Penerimaan Setoran Kas Toko / Bank">
                    <span>📥</span> Kas: {{ $pendingShiftDepositsCount ?? 0 }}
                </a>
            </div>
        </div>

        <!-- KARTU MODUL 6: PERSETUJUAN PERMOHONAN KARYAWAN & TUKAR SHIFT -->
        <div class="dashboard-section-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        📝
                    </div>
                    @php
                        $pendingApprCount = $employeeApprovalsTotal ?? 0;
                    @endphp
                    <span class="badge-status {{ $pendingApprCount > 0 ? 'badge-rose' : 'badge-emerald' }}">
                        {{ $pendingApprCount > 0 ? $pendingApprCount . ' Menunggu Persetujuan' : 'Semua Disetujui' }}
                    </span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    6. Persetujuan Permohonan & Tukar Shift
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 16px;">
                    Otorisasi permohonan mandiri staf kasir: izin, sakit, cuti, tukar jadwal shift antar-karyawan, serta permohonan koreksi jam absensi.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">🏖️ Izin/Cuti: {{ $pendingLeavesCount ?? 0 }}</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">🔄 Tukar Shift: {{ $pendingShiftSwapsCount ?? 0 }}</span>
                    <span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 6px; color: #cbd5e1;">⏱️ Koreksi: {{ $pendingCorrectionsCount ?? 0 }}</span>
                </div>
            </div>
            <div style="padding: 14px 22px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid var(--border-glass);">
                <a href="{{ route('admin.presensi.approval') }}" class="btn-action-sm btn-action-primary" style="width: 100%; justify-content: center; padding: 10px; font-size: 13px; background: linear-gradient(135deg, #0284c7, #0ea5e9); border-color: #38bdf8;">
                    Buka Approval Permohonan Karyawan →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- LAPORAN AUDIT SERAH TERIMA SHIFT & SELISIH STOK FISIK -->
<!-- ============================================================== -->
<div class="dashboard-section-card" style="margin-bottom: 24px; border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);">
    <div class="section-card-header" style="background: rgba(139, 92, 246, 0.08); border-bottom: 1px solid rgba(139, 92, 246, 0.2);">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(139, 92, 246, 0.2); color: #c084fc; font-size: 20px;">🛡️</div>
            <div>
                <h3 class="section-title" style="display: flex; align-items: center; gap: 10px;">
                    Laporan Audit Serah Terima Shift & Rekonsiliasi Stok Fisik
                    @php $discToday = $stockAuditSummary['total_discrepancy_today'] ?? 0; @endphp
                    @if($discToday > 0)
                        <span class="badge-status badge-rose" style="font-size: 11px; padding: 3px 8px;">
                            ⚠️ {{ $discToday }} Selisih Hari Ini
                        </span>
                    @else
                        <span class="badge-status badge-emerald" style="font-size: 11px; padding: 3px 8px;">
                            ✓ Semua Stok & Kas Pas
                        </span>
                    @endif
                </h3>
                <span class="section-subtitle">
                    Bukti audit keaslian stok antar-shift: operator penyerah (shift lalu) vs penerima (shift sekarang) guna mencegah saling tuduh kasir.
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <button type="button" id="btnFilterDiscrepancy" onclick="toggleDiscrepancyFilter()" class="btn-action-sm" style="padding: 8px 14px; font-size: 12px; background: rgba(244, 63, 94, 0.2); border: 1px solid #f43f5e; color: #fca5a5; cursor: pointer; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <span>⚠️</span> <span id="btnFilterDiscrepancyText">Filter: Hanya Selisih</span>
            </button>
            <button type="button" onclick="openDailyZReportModal()" class="btn-action-sm" style="padding: 8px 14px; font-size: 12px; background: #059669; border: 1px solid #10b981; color: #ffffff; cursor: pointer; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <span>🖨️</span> Cetak Z-Report Harian (24 Jam)
            </button>
            <a href="{{ route('admin.stok') }}" class="btn-action-sm btn-action-primary" style="padding: 8px 14px; font-size: 12px; background: rgba(139, 92, 246, 0.2); border: 1px solid rgba(139, 92, 246, 0.5); color: #e9d5ff;">
                <span>📦</span> Buka Modul Stok →
            </a>
        </div>
    </div>
    <div class="section-card-body" style="padding: 0; overflow-x: auto;">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 130px;">Waktu & Tanggal</th>
                    <th>Shift & Cabang</th>
                    <th>⏮️ Penyerah (Shift Lalu)</th>
                    <th>⏭️ Penerima (Shift Sekarang)</th>
                    <th>Status Rekonsiliasi Fisik Barang</th>
                    <th>Selisih Kas Laci</th>
                    <th>Kelayakan Hardware</th>
                    <th>Catatan Kasir</th>
                    <th style="text-align: center; width: 110px;">Aksi Audit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockAuditSummary['reports'] ?? [] as $audit)
                @php
                    $hasDisc = (bool) $audit['has_discrepancy'];
                    $discCount = (int) $audit['discrepancy_count'];
                    $hwSt = $audit['hardware_status'] ?? 'normal';
                @endphp
                <tr data-has-discrepancy="{{ ($hasDisc || ($audit['cash_diff'] ?? 0) != 0) ? '1' : '0' }}" style="{{ $hasDisc ? 'background: rgba(239, 68, 68, 0.04);' : '' }}">
                    <td>
                        <div style="font-weight: 700; color: #fff; font-size: 12.5px;">
                            {{ $audit['completed_at_formatted'] }} WIB
                        </div>
                        <span style="font-size: 11px; color: var(--text-muted);">
                            {{ $audit['check_date_formatted'] }}
                        </span>
                    </td>
                    <td>
                        <span class="badge-status badge-sky" style="font-size: 10.5px; font-weight: 700;">
                            {{ $audit['shift_name'] }}
                        </span>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            {{ $audit['branch_name'] }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #38bdf8; font-size: 12.5px; display: flex; align-items: center; gap: 5px;">
                            <span>👤</span> {{ $audit['prev_operator'] }}
                        </div>
                        <span style="font-size: 10.5px; color: var(--text-muted);">
                            Shift: {{ $audit['prev_shift_name'] }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #a855f7; font-size: 12.5px; display: flex; align-items: center; gap: 5px;">
                            <span>🔎</span> {{ $audit['recipient_cashier'] }}
                        </div>
                        <span style="font-size: 10.5px; color: #34d399;">
                            ✓ Pemeriksa Masuk
                        </span>
                    </td>
                    <td>
                        @if($discCount > 0)
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @foreach($audit['discrepancies'] as $discItem)
                                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); padding: 3px 8px; border-radius: 6px; font-size: 11px;">
                                    <strong style="color: #fff;">{{ $discItem['name'] }}:</strong>
                                    <span style="color: #94a3b8;">Sistem: {{ $discItem['system_qty'] }} &bull; Fisik: {{ $discItem['physical_qty'] }}</span>
                                    <span style="color: {{ $discItem['diff'] < 0 ? '#f87171' : '#38bdf8' }}; font-weight: 800;">
                                        ({{ $discItem['diff'] > 0 ? '+'.$discItem['diff'] : $discItem['diff'] }} {{ $discItem['unit'] }})
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <span class="badge-status badge-emerald" style="font-size: 10.5px;">
                                ✓ Semua Stok Fisik Cocok
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($audit['cash_diff'] == 0)
                            <span style="font-size: 11.5px; color: #34d399; font-weight: 600;">
                                ✓ Pas (Rp {{ number_format($audit['actual_cash'], 0, ',', '.') }})
                            </span>
                        @elseif($audit['cash_diff'] < 0)
                            <span style="font-size: 11.5px; color: #f87171; font-weight: 800;">
                                ⚠️ Minus Rp {{ number_format(abs($audit['cash_diff']), 0, ',', '.') }}
                            </span>
                        @else
                            <span style="font-size: 11.5px; color: #38bdf8; font-weight: 800;">
                                +Rp {{ number_format($audit['cash_diff'], 0, ',', '.') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($hwSt === 'issue')
                            <span class="badge-status badge-rose" style="font-size: 10px; font-weight: 800;">
                                🔴 Butuh Maintenance
                            </span>
                        @elseif($hwSt === 'warning')
                            <span class="badge-status badge-amber" style="font-size: 10px; font-weight: 800;">
                                🟡 Ada Kendala
                            </span>
                        @else
                            <span class="badge-status badge-emerald" style="font-size: 10px;">
                                🟢 Normal (15 PC)
                            </span>
                        @endif
                        @if(!empty($audit['hardware_notes']))
                            <div style="font-size: 10px; color: #fbbf24; margin-top: 3px; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $audit['hardware_notes'] }}">
                                🖥️ {{ $audit['hardware_notes'] }}
                            </div>
                        @endif
                    </td>
                    <td style="max-width: 220px;">
                        @php
                            $notes = array_filter([$audit['drink_notes'], $audit['food_notes'], $audit['cash_notes'], $audit['hardware_notes']]);
                        @endphp
                        @if(!empty($notes))
                            <div style="font-size: 11.5px; color: #e2e8f0; background: rgba(15, 23, 42, 0.6); padding: 5px 8px; border-radius: 6px; border: 1px solid var(--border-glass); line-height: 1.35;">
                                {{ implode(' | ', $notes) }}
                            </div>
                        @else
                            <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">
                                Tidak ada catatan
                            </span>
                        @endif
                    </td>
                    <td style="text-align: center; white-space: nowrap;">
                        <button type="button" 
                                onclick='openAuditModal(@json($audit))'
                                class="btn-action-sm" 
                                style="padding: 5px 10px; font-size: 11px; font-weight: 700; background: rgba(139, 92, 246, 0.18); border: 1px solid rgba(139, 92, 246, 0.4); color: #c084fc; border-radius: 6px; cursor: pointer;">
                            <span>🔍</span> Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        Belum ada riwayat laporan serah terima shift atau audit stok yang tersimpan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================== -->
<!-- TABEL TRANSAKSI PENJUALAN POS KASIR TERKINI (TAUTAN ADMIN ERP) -->
<!-- ============================================================== -->
<div class="dashboard-section-card" style="margin-bottom: 24px;">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">💳</div>
            <div>
                <h3 class="section-title">Transaksi Penjualan POS Kasir Terkini</h3>
                <span class="section-subtitle">Daftar nota kasir shift aktif & sinkronisasi langsung ke modul Penjualan Admin ERP</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.penjualan') }}" class="btn-action-sm btn-action-primary" style="padding: 8px 14px; font-size: 12px;">
                <span>🛒</span> Buka Semua Penjualan ERP →
            </a>
        </div>
    </div>
    <div class="section-card-body" style="padding: 0; overflow-x: auto;">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 190px;">No. Invoice POS</th>
                    <th>Waktu / Tanggal</th>
                    <th>Kasir & Cabang</th>
                    <th>Item Pesanan</th>
                    <th style="text-align: right;">Total Bayar</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center; width: 175px;">Aksi Admin ERP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPosSales ?? [] as $sale)
                @php
                    $isVoid = ($sale->payment_status === 'cancelled');
                    $isPaid = ($sale->payment_status === 'paid');
                    $itemCount = $sale->items->count();
                    $firstItemName = $sale->items->first()?->item_name;
                    $itemSummary = $firstItemName ? ($itemCount > 1 ? $firstItemName . ' (+' . ($itemCount - 1) . ' item lainnya)' : $firstItemName) : 'Layanan / Kasir';
                @endphp
                <tr>
                    <td>
                        <span style="font-family: monospace; font-weight: 700; color: #38bdf8; font-size: 13px;">
                            {{ $sale->invoice_number }}
                        </span>
                    </td>
                    <td style="color: #cbd5e1; font-size: 12px; white-space: nowrap;">
                        {{ $sale->created_at ? $sale->created_at->format('H:i - d/m/Y') : '-' }}
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #fff; font-size: 12.5px;">
                            {{ $sale->employee?->name ?? 'Kasir Shift' }}
                        </div>
                        <span style="font-size: 11px; color: var(--text-muted);">
                            {{ $sale->branch?->name ?? 'Outlet MGK' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #e2e8f0; font-weight: 500;">
                            {{ $itemSummary }}
                        </span>
                        @if($sale->customer_name && $sale->customer_name !== 'Pelanggan Umum')
                            <div style="font-size: 11px; color: #94a3b8;">
                                Pelanggan: {{ $sale->customer_name }}
                            </div>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="font-weight: 700; font-family: 'Outfit', monospace; font-size: 13px; color: {{ $isVoid ? '#fca5a5' : '#34d399' }};">
                            Rp {{ number_format($sale->net_amount, 0, ',', '.') }}
                        </div>
                        <span style="font-size: 10px; text-transform: uppercase; color: var(--text-muted);">
                            {{ $sale->payment_method ?? 'Tunai' }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if($isPaid)
                            <span class="badge-status badge-emerald">Lunas</span>
                        @elseif($isVoid)
                            <span class="badge-status" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                                Void / Dibatalkan
                            </span>
                        @else
                            <span class="badge-status badge-amber">{{ ucfirst($sale->payment_status ?? 'Pending') }}</span>
                        @endif
                    </td>
                    <td style="text-align: center; white-space: nowrap;">
                        <a href="{{ route('admin.penjualan', ['q' => $sale->invoice_number]) }}" 
                           class="btn-action-sm btn-action-primary" 
                           style="padding: 6px 12px; font-size: 11.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 6px;"
                           title="Buka rincian invoice {{ $sale->invoice_number }} di Admin ERP">
                            <span>🔍</span> Buka di Admin ERP
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 22px;">
                        Belum ada transaksi penjualan kasir POS yang tercatat hari ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
<!-- MODAL DETAIL AUDIT SERAH TERIMA SHIFT -->
<div id="modal-audit-detail" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(6px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid rgba(139, 92, 246, 0.4); border-radius: 16px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; background: rgba(139, 92, 246, 0.1);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(139, 92, 246, 0.25); color: #c084fc; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    🛡️
                </div>
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Rincian Berita Acara Audit Serah Terima Shift</h4>
                    <span id="audit-modal-subtitle" style="font-size: 11px; color: var(--text-muted);">-</span>
                </div>
            </div>
            <button type="button" onclick="closeAuditModal()" style="background: transparent; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <!-- OPERATOR PAIR CARDS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 10px; padding: 12px;">
                    <span style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #38bdf8; display: block;">⏮️ Operator Penyerah (Shift Lalu)</span>
                    <div id="audit-prev-operator" style="font-size: 14px; font-weight: 700; color: #fff; margin-top: 3px;">-</div>
                    <span id="audit-prev-shift" style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 2px;">-</span>
                </div>
                <div style="background: rgba(168, 85, 247, 0.08); border: 1px solid rgba(168, 85, 247, 0.25); border-radius: 10px; padding: 12px;">
                    <span style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #c084fc; display: block;">⏭️ Operator Pemeriksa (Shift Ini)</span>
                    <div id="audit-recipient-operator" style="font-size: 14px; font-weight: 700; color: #fff; margin-top: 3px;">-</div>
                    <span id="audit-recipient-shift" style="font-size: 11px; color: #34d399; display: block; margin-top: 2px;">✓ Diverifikasi Saat Masuk</span>
                </div>
            </div>

            <!-- RINCIAN SELISIH BARANG -->
            <div style="margin-bottom: 16px;">
                <h5 style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #cbd5e1; font-weight: 700; margin-bottom: 8px;">
                    📦 Temuan Rekonsiliasi Fisik Barang:
                </h5>
                <div id="audit-items-container" style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 10px; padding: 12px;">
                    -
                </div>
            </div>

            <!-- SERAH TERIMA KAS -->
            <div style="margin-bottom: 16px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 10px; padding: 12px;">
                <h5 style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #cbd5e1; font-weight: 700; margin-bottom: 8px;">
                    💵 Serah Terima Uang Fisik Kasir:
                </h5>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted);">Uang Fisik Diterima:</span>
                        <div id="audit-actual-cash" style="font-size: 15px; font-weight: 800; color: #38bdf8; font-family: monospace;">-</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted);">Ekspektasi Sistem:</span>
                        <div id="audit-expected-cash" style="font-size: 15px; font-weight: 700; color: #cbd5e1; font-family: monospace;">-</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted);">Status Selisih Kas:</span>
                        <div id="audit-cash-diff" style="font-size: 15px; font-weight: 800; font-family: monospace;">-</div>
                    </div>
                </div>
            </div>

            <!-- CATATAN KASIR -->
            <div style="margin-bottom: 16px;">
                <h5 style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #cbd5e1; font-weight: 700; margin-bottom: 6px;">
                    📝 Catatan Kronologi dari Kasir:
                </h5>
                <div id="audit-cashier-notes" style="font-size: 12px; color: #e2e8f0; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; padding: 10px 12px; min-height: 40px;">
                    -
                </div>
            </div>

            <!-- KONDISI HARDWARE -->
            <div style="font-size: 11.5px; color: var(--text-muted); background: rgba(255, 255, 255, 0.03); border-radius: 8px; padding: 8px 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <strong>Status Hardware:</strong> <span id="audit-hardware-status">-</span> &bull; <span id="audit-hardware-notes">-</span>
            </div>
        </div>
        <div style="padding: 12px 20px; background: rgba(15, 23, 42, 0.5); border-top: 1px solid var(--border-glass); text-align: right;">
            <button type="button" onclick="closeAuditModal()" class="btn-action-sm" style="padding: 8px 16px; background: rgba(255,255,255,0.08); color: #fff;">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

<script>
function openAuditModal(data) {
    if (!data) return;
    document.getElementById('audit-modal-subtitle').textContent = `${data.branch_name} • ${data.shift_name} • ${data.check_date_formatted} (${data.completed_at_formatted} WIB)`;
    document.getElementById('audit-prev-operator').textContent = data.prev_operator || 'Shift Sebelumnya';
    document.getElementById('audit-prev-shift').textContent = `Shift: ${data.prev_shift_name || '-'}`;
    document.getElementById('audit-recipient-operator').textContent = data.recipient_cashier || 'Kasir';

    const itemsContainer = document.getElementById('audit-items-container');
    if (data.discrepancies && data.discrepancies.length > 0) {
        let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';
        data.discrepancies.forEach(item => {
            const isMinus = item.diff < 0;
            html += `
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; font-size: 12px;">
                    <div>
                        <strong style="color: #fff;">${item.name}</strong>
                        <span style="font-size: 10.5px; color: var(--text-muted); margin-left: 6px;">[${item.category}]</span>
                        <div style="font-size: 11px; color: #cbd5e1; margin-top: 2px;">
                            Sistem: <strong>${item.system_qty}</strong> &bull; Fisik Aktual: <strong>${item.physical_qty} ${item.unit}</strong>
                        </div>
                    </div>
                    <span style="font-weight: 800; font-size: 13px; color: ${isMinus ? '#f87171' : '#38bdf8'}; font-family: monospace;">
                        ${item.diff > 0 ? '+' + item.diff : item.diff} ${item.unit}
                    </span>
                </div>
            `;
        });
        html += '</div>';
        itemsContainer.innerHTML = html;
    } else {
        itemsContainer.innerHTML = '<span style="color: #34d399; font-weight: 600; font-size: 12px;">✓ Semua stok fisik minuman, makanan, dan kertas print cocok dengan catatan sistem.</span>';
    }

    document.getElementById('audit-actual-cash').textContent = 'Rp ' + Number(data.actual_cash || 0).toLocaleString('id-ID');
    document.getElementById('audit-expected-cash').textContent = 'Rp ' + Number(data.expected_cash || 0).toLocaleString('id-ID');
    
    const cashDiffEl = document.getElementById('audit-cash-diff');
    if (data.cash_diff == 0) {
        cashDiffEl.textContent = '✓ Pas (0)';
        cashDiffEl.style.color = '#34d399';
    } else if (data.cash_diff < 0) {
        cashDiffEl.textContent = '-Rp ' + Number(Math.abs(data.cash_diff)).toLocaleString('id-ID');
        cashDiffEl.style.color = '#f87171';
    } else {
        cashDiffEl.textContent = '+Rp ' + Number(data.cash_diff).toLocaleString('id-ID');
        cashDiffEl.style.color = '#38bdf8';
    }

    const notes = [data.drink_notes, data.food_notes, data.cash_notes].filter(Boolean);
    document.getElementById('audit-cashier-notes').textContent = notes.length > 0 ? notes.join(' | ') : 'Tidak ada catatan tambahan yang ditulis kasir.';

    document.getElementById('audit-hardware-status').textContent = (data.hardware_status || 'normal').toUpperCase();
    document.getElementById('audit-hardware-notes').textContent = data.hardware_notes || 'Semua hardware normal.';

    const modal = document.getElementById('modal-audit-detail');
    modal.style.display = 'flex';
}

function closeAuditModal() {
    const modal = document.getElementById('modal-audit-detail');
    if (modal) modal.style.display = 'none';
}

function toggleDiscrepancyFilter() {
    const rows = document.querySelectorAll('tr[data-has-discrepancy]');
    const btn = document.getElementById('btnFilterDiscrepancy');
    const btnText = document.getElementById('btnFilterDiscrepancyText');
    if (!btn) return;
    const isFiltered = btn.getAttribute('data-active') === 'true';

    if (!isFiltered) {
        rows.forEach(r => {
            if (r.getAttribute('data-has-discrepancy') === '0') {
                r.style.display = 'none';
            }
        });
        btn.setAttribute('data-active', 'true');
        btn.style.background = '#e11d48';
        btn.style.color = '#ffffff';
        if (btnText) btnText.textContent = 'Tampilkan Semua Shift';
    } else {
        rows.forEach(r => r.style.display = '');
        btn.setAttribute('data-active', 'false');
        btn.style.background = 'rgba(244, 63, 94, 0.2)';
        btn.style.color = '#fca5a5';
        if (btnText) btnText.textContent = 'Filter: Hanya Selisih';
    }
}
</script>

@include('admin.dashboard.partials.modal_z_report')
@endsection
