@extends('admin.dashboard.layout')

@section('title', 'Pusat Kendali ERP Terpadu (Unified ERP Hub)')

@section('content')
<style>
    .erp-hero-banner {
        background: radial-gradient(120% 120% at 50% 0%, rgba(56, 189, 248, 0.15) 0%, rgba(15, 23, 42, 0.95) 70%), #0f172a;
        border: 1px solid rgba(56, 189, 248, 0.25);
        border-radius: 18px;
        padding: 28px 32px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .erp-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        background: rgba(56, 189, 248, 0.15);
        border: 1px solid rgba(56, 189, 248, 0.35);
        color: #38bdf8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
    }

    .erp-hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.5px;
        line-height: 1.2;
        margin-bottom: 8px;
    }

    .erp-hero-desc {
        color: #94a3b8;
        font-size: 13px;
        line-height: 1.6;
        max-width: 680px;
    }

    .erp-quick-actions-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-erp-quick {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .btn-erp-primary {
        background: linear-gradient(135deg, #0284c7, #38bdf8);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
    }
    .btn-erp-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(56, 189, 248, 0.5);
    }

    .btn-erp-emerald {
        background: linear-gradient(135deg, #059669, #10b981);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
    }
    .btn-erp-emerald:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
    }

    .btn-erp-outline {
        background: rgba(30, 41, 59, 0.8);
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .btn-erp-outline:hover {
        background: rgba(51, 65, 85, 0.9);
        color: #ffffff;
        border-color: rgba(56, 189, 248, 0.4);
        transform: translateY(-2px);
    }

    /* VITALS METRIC ROW */
    .erp-vitals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .erp-vital-card {
        background: #1e293b;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 16px 18px;
        position: relative;
        overflow: hidden;
        transition: all 0.2s;
    }
    .erp-vital-card:hover {
        border-color: rgba(56, 189, 248, 0.3);
        transform: translateY(-2px);
    }

    .erp-vital-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .erp-vital-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
    }
    .erp-vital-icon {
        font-size: 16px;
    }
    .erp-vital-value {
        font-family: 'Outfit', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.1;
    }
    .erp-vital-sub {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
    }

    /* SEARCH & FILTER CONTROLS */
    .erp-control-bar {
        background: #1e293b;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 26px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .erp-search-input-box {
        position: relative;
        width: 100%;
    }
    .erp-search-input-box span.search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #64748b;
    }
    .erp-search-input {
        width: 100%;
        padding: 12px 18px 12px 46px;
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        color: #f8fafc;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .erp-search-input:focus {
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
    }

    .erp-filter-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .erp-pill-btn {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #94a3b8;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .erp-pill-btn:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
    }
    .erp-pill-btn.active {
        background: #38bdf8;
        color: #0b1120;
        border-color: #38bdf8;
        box-shadow: 0 2px 10px rgba(56, 189, 248, 0.35);
    }

    /* PILLAR SECTION */
    .erp-pillar-container {
        margin-bottom: 32px;
    }
    .erp-pillar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .erp-pillar-title-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .erp-pillar-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: rgba(56, 189, 248, 0.1);
        border: 1px solid rgba(56, 189, 248, 0.25);
    }
    .erp-pillar-name {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
    }
    .erp-pillar-desc {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* MODULES GRID */
    .erp-modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 16px;
    }

    .erp-module-card {
        background: #1e293b;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
        position: relative;
    }
    .erp-module-card:hover {
        transform: translateY(-3px);
        border-color: rgba(56, 189, 248, 0.35);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.3);
    }
    .erp-module-card.featured-card {
        border-color: rgba(16, 185, 129, 0.4);
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.06) 0%, rgba(30, 41, 59, 1) 100%);
    }

    .erp-module-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .erp-module-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .erp-module-badges {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }
    .badge-code {
        font-family: monospace;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.06);
        color: #94a3b8;
    }
    .badge-stat {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 6px;
        background: rgba(56, 189, 248, 0.15);
        color: #38bdf8;
    }
    .badge-stat.stat-emerald {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
    }
    .badge-stat.stat-amber {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
    }

    .erp-module-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 6px;
        line-height: 1.3;
    }
    .erp-module-desc {
        font-size: 12px;
        color: #94a3b8;
        line-height: 1.5;
        margin-bottom: 18px;
        min-height: 36px;
    }

    .erp-module-bottom {
        padding-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .erp-module-roles {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
    }
    .role-tag {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(15, 23, 42, 0.7);
        color: #64748b;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .erp-module-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-launch-mod {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 8px;
        background: #0284c7;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-launch-mod:hover {
        background: #38bdf8;
        color: #0b1120;
    }
    .btn-launch-ext {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-launch-ext:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }

    /* WORKFLOW LAUNCHPAD */
    .erp-workflow-card {
        background: #1e293b;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 24px;
        margin-top: 36px;
    }
    .workflow-steps-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }
    .workflow-step-box {
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 16px;
        position: relative;
    }
    .workflow-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #38bdf8;
        color: #0b1120;
        font-weight: 800;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
    }
    .workflow-title {
        font-size: 13px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 4px;
    }
    .workflow-desc {
        font-size: 11px;
        color: #94a3b8;
        line-height: 1.4;
    }

    /* ERP TRIAGE QUEUE BANNER */
    .erp-triage-banner {
        background: radial-gradient(100% 100% at 0% 0%, rgba(244, 63, 94, 0.12) 0%, rgba(15, 23, 42, 0.95) 100%), #0f172a;
        border: 1px solid rgba(244, 63, 94, 0.35);
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    }
    .erp-triage-banner.all-clean {
        background: radial-gradient(100% 100% at 0% 0%, rgba(16, 185, 129, 0.12) 0%, rgba(15, 23, 42, 0.95) 100%), #0f172a;
        border: 1px solid rgba(16, 185, 129, 0.35);
    }
    .triage-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .triage-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .triage-pulse-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #f43f5e;
        box-shadow: 0 0 10px #f43f5e;
        animation: erpPulse 1.8s infinite;
    }
    .triage-pulse-dot.clean {
        background: #10b981;
        box-shadow: 0 0 10px #10b981;
        animation: none;
    }
    @keyframes erpPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.3); }
    }
    .triage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 12px;
    }
    .triage-item-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        text-decoration: none;
        color: #f1f5f9;
        transition: all 0.2s ease;
    }
    .triage-item-card:hover {
        transform: translateY(-2px);
        background: rgba(51, 65, 85, 0.9);
        border-color: rgba(56, 189, 248, 0.4);
    }
    .triage-item-card.has-pending {
        border-color: rgba(244, 63, 94, 0.4);
        background: rgba(244, 63, 94, 0.08);
    }
    .triage-item-card.has-pending:hover {
        border-color: #f43f5e;
        background: rgba(244, 63, 94, 0.16);
    }

    /* FLOATING QUEUE WIDGET & SOUND TOGGLE */
    #erpFloatingQueueWidget {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #erpSoundToggleBtn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #1e293b;
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
        transition: all 0.2s ease;
        font-size: 1.15rem;
    }
    #erpSoundToggleBtn:hover {
        transform: scale(1.08);
        border-color: rgba(56, 189, 248, 0.5);
        background: #334155;
    }
    #erpSoundToggleBtn.muted {
        opacity: 0.6;
        filter: grayscale(1);
    }
    #erpFloatingBadge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 9999px;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(244, 63, 94, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.25s ease;
    }
    #erpFloatingBadge:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 8px 24px rgba(244, 63, 94, 0.6);
        color: #ffffff;
    }
    @keyframes bellRing {
        0%, 100% { transform: rotate(0); }
        10%, 30% { transform: rotate(14deg); }
        20%, 40% { transform: rotate(-14deg); }
        50% { transform: rotate(0); }
    }
    .bell-animated {
        display: inline-block;
        animation: bellRing 2.2s infinite ease-in-out;
    }
</style>

<!-- 1. HERO BANNER PUSAT ERP -->
<div class="erp-hero-banner">
    <div>
        <div class="erp-hero-badge">
            <span>🏛️</span> UNIFIED ENTERPRISE RESOURCE PLANNING (ERP)
        </div>
        <h1 class="erp-hero-title">Pusat Kendali ERP Maya Graha Kencana</h1>
        <p class="erp-hero-desc">
            Seluruh ekosistem operasional ritel, kasir POS, manajemen logistik gudang, absensi & jadwal staf, hingga akuntansi bagan akun (COA) telah terpusat dalam satu portal tunggal terintegrasi.
        </p>
    </div>
    <div class="erp-quick-actions-row">
        <a href="{{ route('presensi2.penjualan') }}" target="_blank" class="btn-erp-quick btn-erp-emerald">
            <span>🛒</span> Buka POS Kasir
        </a>
        <a href="{{ route('presensi2.stok') }}" target="_blank" class="btn-erp-quick btn-erp-primary">
            <span>📋</span> Checklist Stok
        </a>
        <a href="{{ route('admin.coa') }}" class="btn-erp-quick btn-erp-outline">
            <span>🏛️</span> Bagan Akun (COA)
        </a>
        <a href="{{ route('presensi2.index') }}" target="_blank" class="btn-erp-quick btn-erp-outline">
            <span>📱</span> Presensi v2
        </a>
    </div>
</div>

<!-- 1.5 PUSAT ANTREAN OTORISASI & TRIAGE TINDAKAN CEPAT -->
<div id="erpTriageSection" class="erp-triage-banner {{ $totalTriagePending == 0 ? 'all-clean' : '' }}">
    <div class="triage-header-row">
        <div class="triage-title-wrap">
            <span id="triagePulseDot" class="triage-pulse-dot {{ $totalTriagePending == 0 ? 'clean' : '' }}"></span>
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
                    Pusat Antrean Otorisasi &amp; Tindakan Cepat
                    <span id="triagePendingBadge">
                        @if($totalTriagePending > 0)
                            <span style="background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.4); font-size: 0.72rem; padding: 2px 8px; border-radius: 9999px;">
                                {{ $totalTriagePending }} Perlu Tindakan
                            </span>
                        @else
                            <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; padding: 2px 8px; border-radius: 9999px;">
                                ✅ Seluruh Operasional Lancar
                            </span>
                        @endif
                    </span>
                </h3>
                <p style="margin: 4px 0 0; font-size: 0.8rem; color: #94a3b8;">
                    Deteksi otomatis permohonan staf, selisih setoran kasir, transfer logistik &amp; permohonan restock cabang yang menunggu verifikasi pimpinan.
                </p>
            </div>
        </div>
        <div>
            <span style="font-size: 0.75rem; color: #64748b; font-family: monospace;">Real-time Operational Triage</span>
        </div>
    </div>

    <div class="triage-grid">
        <!-- 1. SDM & Permohonan Staf -->
        <a href="{{ route('admin.presensi.approval') }}" class="triage-item-card {{ $employeeApprovalsTotal > 0 ? 'has-pending' : '' }}">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.4rem;">👥</span>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #f1f5f9;">Persetujuan SDM &amp; Jadwal</div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $pendingLeavesCount }} Cuti &bull; {{ $pendingShiftSwapsCount }} Tukar &bull; {{ $pendingCorrectionsCount }} Koreksi &bull; {{ $pendingOvertimesCount }} Lembur</div>
                </div>
            </div>
            <div style="text-align: right;">
                <span id="triageEmpCount" style="display: inline-block; min-width: 28px; padding: 3px 8px; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-align: center; background: {{ $employeeApprovalsTotal > 0 ? '#f43f5e' : 'rgba(255,255,255,0.06)' }}; color: #ffffff;">
                    {{ $employeeApprovalsTotal }}
                </span>
            </div>
        </a>

        <!-- 2. Setoran Kas Kasir POS -->
        <a href="{{ route('admin.penjualan.serah-terima') }}" class="triage-item-card {{ $pendingShiftDepositsCount > 0 ? 'has-pending' : '' }}">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.4rem;">💵</span>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #f1f5f9;">Verifikasi Setoran Kas Kasir</div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">Validasi Fisik Kas Laci vs Rekap POS</div>
                </div>
            </div>
            <div style="text-align: right;">
                <span id="triageDepositCount" style="display: inline-block; min-width: 28px; padding: 3px 8px; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-align: center; background: {{ $pendingShiftDepositsCount > 0 ? '#f43f5e' : 'rgba(255,255,255,0.06)' }}; color: #ffffff;">
                    {{ $pendingShiftDepositsCount }}
                </span>
            </div>
        </a>

        <!-- 3. Logistik & Distribusi Gudang -->
        <a href="{{ route('admin.distribusi') }}" class="triage-item-card {{ ($pendingStockTransfers + $pendingStockRequestsCount) > 0 ? 'has-pending' : '' }}">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.4rem;">🚚</span>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #f1f5f9;">Logistik &amp; Distribusi Gudang</div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $pendingStockTransfers }} Transfer &bull; {{ $pendingStockRequestsCount }} Permintaan Kasir</div>
                </div>
            </div>
            <div style="text-align: right;">
                <span id="triageStockCount" style="display: inline-block; min-width: 28px; padding: 3px 8px; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-align: center; background: {{ ($pendingStockTransfers + $pendingStockRequestsCount) > 0 ? '#f59e0b' : 'rgba(255,255,255,0.06)' }}; color: #ffffff;">
                    {{ $pendingStockTransfers + $pendingStockRequestsCount }}
                </span>
            </div>
        </a>

        <!-- 4. Koreksi Billing & Akun Staf -->
        <a href="{{ route('admin.penjualan.penyesuaian-billing') }}" class="triage-item-card {{ ($pendingBillingAdjustments + $pendingRegistrations + $pendingPasswordResets) > 0 ? 'has-pending' : '' }}">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.4rem;">🔐</span>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #f1f5f9;">Otorisasi Akun &amp; Billing</div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $pendingBillingAdjustments }} Billing &bull; {{ $pendingRegistrations }} Daftar &bull; {{ $pendingPasswordResets }} Reset</div>
                </div>
            </div>
            <div style="text-align: right;">
                <span id="triageBillingCount" style="display: inline-block; min-width: 28px; padding: 3px 8px; font-weight: 700; font-size: 0.82rem; border-radius: 8px; text-align: center; background: {{ ($pendingBillingAdjustments + $pendingRegistrations + $pendingPasswordResets) > 0 ? '#8b5cf6' : 'rgba(255,255,255,0.06)' }}; color: #ffffff;">
                    {{ $pendingBillingAdjustments + $pendingRegistrations + $pendingPasswordResets }}
                </span>
            </div>
        </a>
    </div>
</div>

<!-- 2. LIVE VITALS KPI PULSE -->
<div class="erp-vitals-grid">
    <div class="erp-vital-card">
        <div class="erp-vital-top">
            <span class="erp-vital-label">Katalog Produk</span>
            <span class="erp-vital-icon">🏷️</span>
        </div>
        <div class="erp-vital-value">{{ $totalSku }} SKU</div>
        <div class="erp-vital-sub">{{ $totalCategories }} Kategori &bull; {{ $totalBrands }} Brand</div>
    </div>

    <div class="erp-vital-card">
        <div class="erp-vital-top">
            <span class="erp-vital-label">Karyawan &amp; Presensi</span>
            <span class="erp-vital-icon">👥</span>
        </div>
        <div class="erp-vital-value">{{ $activeEmployees }} Staf</div>
        <div class="erp-vital-sub" style="color: #34d399;">{{ $todayAttendance }} Hadir Hari Ini</div>
    </div>

    <div class="erp-vital-card">
        <div class="erp-vital-top">
            <span class="erp-vital-label">Penjualan Hari Ini</span>
            <span class="erp-vital-icon">🛒</span>
        </div>
        <div class="erp-vital-value">{{ $todaySalesCount }} Transaksi</div>
        <div class="erp-vital-sub" style="color: #38bdf8;">Rp {{ number_format($todaySalesAmount, 0, ',', '.') }}</div>
    </div>

    <div class="erp-vital-card">
        <div class="erp-vital-top">
            <span class="erp-vital-label">Persediaan Gudang</span>
            <span class="erp-vital-icon">📦</span>
        </div>
        <div class="erp-vital-value">{{ number_format($totalPhysicalStock, 0, ',', '.') }}</div>
        <div class="erp-vital-sub">{{ $pendingStockTransfers }} Transfer Antar-Gudang</div>
    </div>

    <div class="erp-vital-card">
        <div class="erp-vital-top">
            <span class="erp-vital-label">Bagan Akun (COA)</span>
            <span class="erp-vital-icon">🏛️</span>
        </div>
        <div class="erp-vital-value">{{ $coaAccountsCount }} Akun</div>
        <div class="erp-vital-sub">Kas/Bank Aktif SAK</div>
    </div>

    <div class="erp-vital-card">
        <div class="erp-vital-top">
            <span class="erp-vital-label">Antrean Otorisasi</span>
            <span class="erp-vital-icon">⚡</span>
        </div>
        <div class="erp-vital-value" id="vitalTriagePendingValue" style="{{ $totalTriagePending > 0 ? 'color: #fb7185;' : 'color: #34d399;' }}">
            {{ $totalTriagePending }}
        </div>
        <div class="erp-vital-sub" id="vitalTriagePendingSub">
            @if($totalTriagePending > 0)
                {{ $employeeApprovalsTotal }} SDM &bull; {{ $pendingShiftDepositsCount + $pendingStockTransfers + $pendingStockRequestsCount }} Logistik/Kas
            @else
                Semua Antrean Selesai
            @endif
        </div>
    </div>
</div>

<!-- 3. LIVE SEARCH & CATEGORY FILTER BAR -->
<div class="erp-control-bar">
    <div class="erp-search-input-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="erpFeatureSearch" class="erp-search-input" placeholder="Cari fitur ERP cepat... (contoh: kasir, coa, stok, jadwal, pelanggan, supplier, transfer, password, audit)" autocomplete="off">
    </div>

    <div class="erp-filter-pills">
        <button type="button" class="erp-pill-btn active" data-filter="all">
            <span>🌐</span> Semua Modul (26)
        </button>
        <button type="button" class="erp-pill-btn" data-filter="master_data">
            <span>🗄️</span> 1. Master Data
        </button>
        <button type="button" class="erp-pill-btn" data-filter="pos_sales">
            <span>🛒</span> 2. POS Kasir & Billing
        </button>
        <button type="button" class="erp-pill-btn" data-filter="supply_chain">
            <span>📦</span> 3. Gudang & Logistik
        </button>
        <button type="button" class="erp-pill-btn" data-filter="human_resource">
            <span>👥</span> 4. SDM & Presensi
        </button>
        <button type="button" class="erp-pill-btn" data-filter="finance_accounting">
            <span>💰</span> 5. Keuangan & COA
        </button>
        <button type="button" class="erp-pill-btn" data-filter="security_governance">
            <span>🛡️</span> 6. Keamanan & RBAC
        </button>
    </div>
</div>

<!-- 4. GRID MODUL ERP PER PILAR -->
<div id="erpPillarsList">
    @foreach($erpModules as $pillarKey => $pillar)
    <div class="erp-pillar-container" data-pillar-key="{{ $pillarKey }}">
        <div class="erp-pillar-header">
            <div class="erp-pillar-title-group">
                <div class="erp-pillar-icon" style="color: {{ $pillar['color'] }};">
                    {{ $pillar['icon'] }}
                </div>
                <div>
                    <h2 class="erp-pillar-name">{{ $pillar['pillar_name'] }}</h2>
                    <p class="erp-pillar-desc">{{ $pillar['description'] }}</p>
                </div>
            </div>
            <span class="badge-status" style="background: rgba(255, 255, 255, 0.05); color: #94a3b8; font-size: 11px;">
                {{ count($pillar['modules']) }} Modul
            </span>
        </div>

        <div class="erp-modules-grid">
            @foreach($pillar['modules'] as $mod)
            <div class="erp-module-card {{ !empty($mod['is_featured']) ? 'featured-card' : '' }}" 
                 data-title="{{ strtolower($mod['title']) }}" 
                 data-desc="{{ strtolower($mod['desc']) }}" 
                 data-code="{{ strtolower($mod['code']) }}"
                 data-pillar="{{ $pillarKey }}">
                
                <div>
                    <div class="erp-module-top">
                        <div class="erp-module-icon-wrap">
                            {{ $mod['icon'] }}
                        </div>
                        <div class="erp-module-badges">
                            <span class="badge-code">{{ $mod['code'] }}</span>
                            @if(!empty($mod['stat_badge']))
                            <span class="badge-stat {{ str_contains(strtolower($mod['stat_badge']), 'aktif') || str_contains(strtolower($mod['stat_badge']), 'hadir') ? 'stat-emerald' : (str_contains(strtolower($mod['stat_badge']), 'pending') || str_contains(strtolower($mod['stat_badge']), 'menunggu') ? 'stat-amber' : '') }}">
                                {{ $mod['stat_badge'] }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <h3 class="erp-module-title">{{ $mod['title'] }}</h3>
                    <p class="erp-module-desc">{{ $mod['desc'] }}</p>
                </div>

                <div class="erp-module-bottom">
                    <div class="erp-module-roles">
                        @foreach($mod['roles'] as $role)
                        <span class="role-tag">{{ $role }}</span>
                        @endforeach
                    </div>
                    <div class="erp-module-actions">
                        <a href="{{ $mod['url'] }}" class="btn-launch-mod">
                            <span>Buka</span> <span>&rarr;</span>
                        </a>
                        <a href="{{ $mod['url'] }}" target="_blank" title="Buka di Tab Baru" class="btn-launch-ext">
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<!-- 5. WORKFLOW LAUNCHPAD TERPADU -->
<div class="erp-workflow-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase;">
                <span>⚡</span> PANDUAN ALUR OPERASIONAL HARIAN
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 700; color: #ffffff; margin-top: 4px;">
                Alur Kerja Kasir & Tutup Shift Terpadu
            </h3>
        </div>
        <span style="font-size: 12px; color: #94a3b8;">Single Source of Truth ERP</span>
    </div>

    <div class="workflow-steps-row">
        <div class="workflow-step-box">
            <div class="workflow-num">1</div>
            <div class="workflow-title">Presensi & Buka Shift</div>
            <div class="workflow-desc">Kasir melakukan selfie liveness di Portal Presensi v2, sistem memvalidasi geofence dan wifi kantor.</div>
        </div>
        <div class="workflow-step-box">
            <div class="workflow-num">2</div>
            <div class="workflow-title">Checklist Opname Awal</div>
            <div class="workflow-desc">Kasir menghitung fisik stok etalase (Dual Control) sebelum membuka kas kasir di halaman Checklist Kasir.</div>
        </div>
        <div class="workflow-step-box">
            <div class="workflow-num">3</div>
            <div class="workflow-title">Transaksi POS Desktop</div>
            <div class="workflow-desc">Penjualan F&B, billing internet warnet otomatis, printer nota kasir, dan koreksi void dengan otorisasi PIN.</div>
        </div>
        <div class="workflow-step-box">
            <div class="workflow-num">4</div>
            <div class="workflow-title">Tutup Shift & Z-Report</div>
            <div class="workflow-desc">Blind cash count, pencocokan selisih billing/print, cetak Z-Report, dan penyetoran uang ke rekening Kasir/Bank.</div>
        </div>
        <div class="workflow-step-box">
            <div class="workflow-num">5</div>
            <div class="workflow-title">Buku Besar & COA</div>
            <div class="workflow-desc">Seluruh transaksi otomatis terangkum dalam bagan akun Chart of Accounts (1-10001 Kas Kasir vs 4-10001 Penjualan).</div>
        </div>
    </div>
</div>

<!-- 6. FLOATING ACTION BADGE & TOAST CONTAINER -->
<div id="erpToastContainer" style="position: fixed; top: 24px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>

<div id="erpFloatingQueueWidget">
    <!-- Sound Toggle Button -->
    <button type="button" id="erpSoundToggleBtn" title="Suara Notifikasi: AKTIF (Klik untuk membisukan)">
        <span id="soundIcon">🔔</span>
    </button>

    <!-- Floating Badge Button -->
    <a href="#erpTriageSection" id="erpFloatingBadge" style="{{ $totalTriagePending == 0 ? 'background: rgba(30, 41, 59, 0.9); border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 4px 14px rgba(0,0,0,0.3);' : '' }}">
        <span id="floatingBadgeBell" class="{{ $totalTriagePending > 0 ? 'bell-animated' : '' }}">
            {{ $totalTriagePending > 0 ? '🔔' : '✅' }}
        </span>
        <span id="floatingBadgeText">
            @if($totalTriagePending > 0)
                Antrean: <span id="floatingBadgeCount">{{ $totalTriagePending }}</span> Perlu Tindakan
            @else
                <span id="floatingBadgeCount" style="color: #34d399;">Operasional Lancar</span>
            @endif
        </span>
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('erpFeatureSearch');
    const filterPills = document.querySelectorAll('.erp-pill-btn');
    const moduleCards = document.querySelectorAll('.erp-module-card');
    const pillarContainers = document.querySelectorAll('.erp-pillar-container');

    let currentFilter = 'all';

    function filterModules() {
        const query = searchInput.value.toLowerCase().trim();

        pillarContainers.forEach(pillar => {
            const pillarKey = pillar.dataset.pillarKey;
            const cardsInPillar = pillar.querySelectorAll('.erp-module-card');
            let visibleCount = 0;

            cardsInPillar.forEach(card => {
                const title = card.dataset.title;
                const desc = card.dataset.desc;
                const code = card.dataset.code;
                const matchesSearch = !query || title.includes(query) || desc.includes(query) || code.includes(query);
                const matchesPillar = currentFilter === 'all' || currentFilter === pillarKey;

                if (matchesSearch && matchesPillar) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount > 0) {
                pillar.style.display = 'block';
            } else {
                pillar.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterModules);

    filterPills.forEach(pill => {
        pill.addEventListener('click', function () {
            filterPills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterModules();
        });
    });

    // --- REALTIME AUDIO CHIME & TRIAGE QUEUE WATCHER ---
    let lastPendingCount = {{ (int) $totalTriagePending }};
    let soundEnabled = localStorage.getItem('erp_queue_sound') !== 'false';
    let audioCtx = null;

    const soundToggleBtn = document.getElementById('erpSoundToggleBtn');
    const soundIcon = document.getElementById('soundIcon');
    const toastContainer = document.getElementById('erpToastContainer');

    function updateSoundUI() {
        if (!soundToggleBtn || !soundIcon) return;
        if (soundEnabled) {
            soundToggleBtn.classList.remove('muted');
            soundToggleBtn.title = 'Suara Notifikasi: AKTIF (Klik untuk membisukan)';
            soundIcon.textContent = '🔔';
        } else {
            soundToggleBtn.classList.add('muted');
            soundToggleBtn.title = 'Suara Notifikasi: BISU (Klik untuk mengaktifkan)';
            soundIcon.textContent = '🔕';
        }
    }
    updateSoundUI();

    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function playQueueChime() {
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;

            // Tone 1: 587.33 Hz (D5) - gentle bell chime
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now);
            gain1.gain.setValueAtTime(0.12, now);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Tone 2: 880 Hz (A5) - sweet resonance
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.12);
            gain2.gain.setValueAtTime(0.15, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.6);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.6);
        } catch (e) {
            console.warn('Audio chime notice error:', e);
        }
    }

    if (soundToggleBtn) {
        soundToggleBtn.addEventListener('click', function () {
            getAudioContext();
            soundEnabled = !soundEnabled;
            localStorage.setItem('erp_queue_sound', soundEnabled ? 'true' : 'false');
            updateSoundUI();
            if (soundEnabled) {
                playQueueChime();
                showQueueToast('Suara notifikasi antrean telah AKTIF 🔔');
            } else {
                showQueueToast('Suara notifikasi antrean telah DIBISUKAN 🔕');
            }
        });
    }

    function showQueueToast(message, count = null) {
        if (!toastContainer) return;
        const toast = document.createElement('div');
        toast.style.cssText = `
            background: #0f172a;
            color: #f8fafc;
            border: 1px solid rgba(244, 63, 94, 0.45);
            border-left: 4px solid #f43f5e;
            border-radius: 12px;
            padding: 12px 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            pointer-events: auto;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            opacity: 0;
            max-width: 380px;
        `;
        toast.innerHTML = `
            <span style="font-size: 1.3rem;">🔔</span>
            <div style="flex: 1;">
                <div style="font-weight: 700; color: #fb7185;">Antrean Otorisasi ERP</div>
                <div style="font-size: 0.78rem; color: #cbd5e1; margin-top: 2px;">${message}</div>
            </div>
            <a href="#erpTriageSection" style="background: rgba(244, 63, 94, 0.2); color: #fb7185; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">Lihat</a>
        `;

        toastContainer.appendChild(toast);
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });

        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 6000);
    }

    function updateQueueUI(metrics) {
        const total = metrics.total_pending_approvals ?? 0;
        const sdm = metrics.pending_employee_approvals ?? 0;
        const deposits = metrics.pending_shift_deposits ?? 0;
        const transfers = metrics.pending_stock_transfers ?? 0;

        // 1. Triage Banner Header
        const banner = document.getElementById('erpTriageSection');
        const dot = document.getElementById('triagePulseDot');
        const badgeWrap = document.getElementById('triagePendingBadge');

        if (banner && dot && badgeWrap) {
            if (total > 0) {
                banner.classList.remove('all-clean');
                dot.classList.remove('clean');
                badgeWrap.innerHTML = `<span style="background: rgba(244, 63, 94, 0.2); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.4); font-size: 0.72rem; padding: 2px 8px; border-radius: 9999px;">${total} Perlu Tindakan</span>`;
            } else {
                banner.classList.add('all-clean');
                dot.classList.add('clean');
                badgeWrap.innerHTML = `<span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.72rem; padding: 2px 8px; border-radius: 9999px;">✅ Seluruh Operasional Lancar</span>`;
            }
        }

        // 2. Triage Cards Count Badges
        const empEl = document.getElementById('triageEmpCount');
        if (empEl) {
            empEl.textContent = sdm;
            empEl.style.background = sdm > 0 ? '#f43f5e' : 'rgba(255,255,255,0.06)';
        }

        const depEl = document.getElementById('triageDepositCount');
        if (depEl) {
            depEl.textContent = deposits;
            depEl.style.background = deposits > 0 ? '#f43f5e' : 'rgba(255,255,255,0.06)';
        }

        const stkWarpEl = document.getElementById('triageStockCount');
        if (stkWarpEl) {
            stkWarpEl.textContent = transfers;
            stkWarpEl.style.background = transfers > 0 ? '#f59e0b' : 'rgba(255,255,255,0.06)';
        }

        // 3. Vital KPI Card
        const vitalVal = document.getElementById('vitalTriagePendingValue');
        const vitalSub = document.getElementById('vitalTriagePendingSub');
        if (vitalVal) {
            vitalVal.textContent = total;
            vitalVal.style.color = total > 0 ? '#fb7185' : '#34d399';
        }
        if (vitalSub) {
            vitalSub.innerHTML = total > 0 
                ? `${sdm} SDM &bull; ${deposits + transfers} Logistik/Kas` 
                : 'Semua Antrean Selesai';
        }

        // 4. Floating Badge
        const floatBadge = document.getElementById('erpFloatingBadge');
        const floatBell = document.getElementById('floatingBadgeBell');
        const floatText = document.getElementById('floatingBadgeText');

        if (floatBadge && floatBell && floatText) {
            if (total > 0) {
                floatBadge.style.background = 'linear-gradient(135deg, #f43f5e, #e11d48)';
                floatBadge.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                floatBadge.style.boxShadow = '0 6px 20px rgba(244, 63, 94, 0.45)';
                floatBell.textContent = '🔔';
                floatBell.className = 'bell-animated';
                floatText.innerHTML = `Antrean: <span id="floatingBadgeCount">${total}</span> Perlu Tindakan`;
            } else {
                floatBadge.style.background = 'rgba(30, 41, 59, 0.9)';
                floatBadge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                floatBadge.style.boxShadow = '0 4px 14px rgba(0,0,0,0.3)';
                floatBell.textContent = '✅';
                floatBell.className = '';
                floatText.innerHTML = `<span id="floatingBadgeCount" style="color: #34d399;">Operasional Lancar</span>`;
            }
        }
    }

    function pollQueueMetrics() {
        fetch("{{ route('admin.erp') }}", {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.success || !data.metrics) return;
            const metrics = data.metrics;
            const currentCount = metrics.total_pending_approvals ?? 0;

            updateQueueUI(metrics);

            if (currentCount > lastPendingCount) {
                const diff = currentCount - lastPendingCount;
                if (soundEnabled) {
                    playQueueChime();
                }
                showQueueToast(`Terdapat ${diff} permohonan baru yang membutuhkan verifikasi pimpinan.`, currentCount);
            }

            lastPendingCount = currentCount;
        })
        .catch(err => {
            // Ignore temporary network or polling error
        });
    }

    // Polling setiap 30 detik untuk deteksi otomatis di background
    setInterval(pollQueueMetrics, 30000);
});
</script>
@endsection

