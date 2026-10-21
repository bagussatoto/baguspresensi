<!-- ============================================================== -->
<!-- REUSABLE ERP TOPBAR & SUBNAVBAR (MAYA GRAHA KENCANA)           -->
<!-- Sinkron 100% di semua halaman Admin ERP MGK                    -->
<!-- ============================================================== -->

<style>
    /* VARIABLES */
    :root {
        --erp-header-blue: #0284c7;
        --erp-header-dark: #0b2341;
        --erp-subnav-blue: #38bdf8;
        --erp-subnav-bg: #0369a1;
        --erp-subnav-active: #0c4a6e;
    }

    /* TOP MAIN HEADER */
    .main-header {
        background: linear-gradient(to right, #0b2341, #0284c7) !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0 16px !important;
        color: #ffffff !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1040 !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15) !important;
    }
    .header-brand-wrap {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
    }
    .brand-title {
        font-size: 17px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        text-decoration: none !important;
        letter-spacing: 0.3px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .brand-title:hover {
        color: #e0f2fe !important;
    }
    .sidebar-toggle-btn {
        background: transparent !important;
        border: none !important;
        color: #ffffff !important;
        font-size: 19px !important;
        cursor: pointer !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        line-height: 1 !important;
        transition: background 0.15s ease !important;
    }
    .sidebar-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.18) !important;
    }
    .header-right-tools, .header-right-user {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        font-size: 12px !important;
    }
    .header-user-status {
        font-size: 11.5px !important;
        color: #e0f2fe !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .header-user-badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        padding: 2px 7px !important;
        border-radius: 4px !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px !important;
    }
    .header-link-btn {
        color: #f0f9ff !important;
        text-decoration: none !important;
        padding: 4px 10px !important;
        border-radius: 4px !important;
        background: rgba(255, 255, 255, 0.15) !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        transition: all 0.15s ease !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .header-link-btn:hover {
        background: rgba(255, 255, 255, 0.28) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2) !important;
    }

    /* HORIZONTAL SUB-NAVBAR */
    .sub-navbar {
        background: linear-gradient(to right, #0284c7, #0ea5e9) !important;
        min-height: 38px !important;
        position: fixed !important;
        top: 48px !important;
        left: 230px !important;
        right: 0 !important;
        z-index: 1030 !important;
        display: flex !important;
        align-items: center !important;
        overflow-x: auto !important;
        white-space: nowrap !important;
        padding: 0 10px !important;
        gap: 4px !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.12) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        scrollbar-width: thin !important;
        scrollbar-color: rgba(255, 255, 255, 0.4) transparent !important;
        transition: left 0.3s ease !important;
    }
    .sub-navbar::-webkit-scrollbar {
        height: 4px !important;
    }
    .sub-navbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.4) !important;
        border-radius: 4px !important;
    }
    .subnav-item {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        padding: 5px 9px !important;
        color: #f0f9ff !important;
        text-decoration: none !important;
        font-size: 11.5px !important;
        font-weight: 500 !important;
        border-radius: 4px !important;
        white-space: nowrap !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
    }
    .subnav-item:hover {
        background: rgba(0, 0, 0, 0.2) !important;
        color: #ffffff !important;
    }
    .subnav-item.active {
        background: rgba(12, 74, 110, 0.75) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35) !important;
    }

    /* RESPONSIVE LAYOUT */
    @media (max-width: 768px) {
        .sub-navbar {
            left: 0 !important;
        }
        .main-sidebar {
            transform: translateX(-100%) !important;
            transition: transform 0.3s ease !important;
        }
        body.sidebar-open .main-sidebar {
            transform: translateX(0) !important;
        }
    }
    body.sidebar-collapsed .sub-navbar {
        left: 0 !important;
    }

    /* MODAL POPUPS FOR AC, STOP CODE, RATING */
    .erp-modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(11, 17, 32, 0.75);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .erp-modal-overlay.active {
        display: flex;
    }
    .erp-modal-card {
        background: #ffffff;
        width: 90%;
        max-width: 520px;
        border-radius: 8px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        border-top: 4px solid #0284c7;
        animation: erpModalIn 0.2s ease-out;
    }
    @keyframes erpModalIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .erp-modal-header {
        padding: 14px 18px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .erp-modal-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .erp-modal-close-btn {
        background: transparent;
        border: none;
        font-size: 20px;
        color: #64748b;
        cursor: pointer;
        padding: 0 6px;
    }
    .erp-modal-close-btn:hover {
        color: #0f172a;
    }
    .erp-modal-body {
        padding: 18px;
        font-size: 13px;
        color: #334155;
        line-height: 1.5;
    }
    .erp-modal-footer {
        padding: 12px 18px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .erp-modal-btn {
        padding: 7px 14px;
        border-radius: 4px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

<!-- TOP MAIN HEADER (MGK ERP) -->
<header class="main-header">
    <div class="header-brand-wrap">
        <button class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleErpSidebar()" title="Toggle Sidebar">
            ☰
        </button>
        <a href="{{ route('admin.index') }}" class="brand-title" title="Kembali ke Dashboard Utama MGK ERP">
            🏢 MGK ERP
        </a>
    </div>

    <div class="header-right-tools">
        <div class="header-user-status">
            <span>User:</span>
            <strong>{{ Auth::user()->name ?? ($profileData['name'] ?? 'Bagus') }}</strong>
            <span class="header-user-badge">
                {{ strtoupper(Auth::user()->role?->value ?? ($profileData['role'] ?? 'manager')) }} @PUSAT
            </span>
        </div>

        <a href="{{ route('warnet.index') }}" class="header-link-btn" title="Buka Point of Sale / Billing Warnet">
            🏪 POS Warnet
        </a>
        <a href="{{ route('admin.karyawan') }}" class="header-link-btn" title="Kelola Karyawan & Staff">
            👥 Karyawan
        </a>
        @php
            $pendingVerifikasiCount = \App\Models\EmployeeRegistration::where('status', 'pending')->count();
        @endphp
        <a href="{{ route('admin.karyawan.verifikasi') }}" class="header-link-btn" title="Verifikasi Pendaftaran Mandiri (Maker-Checker & SoD)" style="{{ $pendingVerifikasiCount > 0 ? 'background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4);' : '' }}">
            🛡️ Verifikasi
            @if($pendingVerifikasiCount > 0)
                <span style="background: #ef4444; color: #ffffff; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 999px; margin-left: 4px;">
                    {{ $pendingVerifikasiCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('admin.karyawan.access-review') }}" class="header-link-btn" title="Tinjauan Hak Akses & De-provisioning (ISO 27001 Klausul A.8.5)">
            🔒 Review Akses
        </a>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0; display: inline;">
            @csrf
            <button type="submit" class="header-link-btn" style="border: 1px solid rgba(255,255,255,0.15); cursor: pointer;" title="Logout Sesi Admin">
                🔒 Logout
            </button>
        </form>
    </div>
</header>

<!-- MODAL 1: DATA AC & FASILITAS OUTLET -->
<div id="dataAcModal" class="erp-modal-overlay">
    <div class="erp-modal-card">
        <div class="erp-modal-header">
            <div class="erp-modal-title">
                <span>❄️</span> Monitoring & Data AC Outlet Maya Graha Kencana
            </div>
            <button type="button" class="erp-modal-close-btn" onclick="closeErpModal('dataAcModal')">&times;</button>
        </div>
        <div class="erp-modal-body">
            <p style="margin-bottom: 12px; color: #64748b; font-size: 12px;">
                Daftar unit pendingin udara (AC) & jadwal servis rutin cabang MGK Pusat:
            </p>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 14px;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; text-align: left;">
                        <th style="padding: 6px 10px;">Unit AC</th>
                        <th style="padding: 6px 10px;">Lokasi</th>
                        <th style="padding: 6px 10px;">Status Suhu</th>
                        <th style="padding: 6px 10px;">Jadwal Servis</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 6px 10px; font-weight: 600;">AC Daikin 2 PK #1</td>
                        <td style="padding: 6px 10px;">Ruang Billing Depan</td>
                        <td style="padding: 6px 10px;"><span style="color: #10b981; font-weight: 700;">22°C (Optimal)</span></td>
                        <td style="padding: 6px 10px; color: #64748b;">Akhir Bulan</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 6px 10px; font-weight: 600;">AC Daikin 2 PK #2</td>
                        <td style="padding: 6px 10px;">Ruang Gaming VIP</td>
                        <td style="padding: 6px 10px;"><span style="color: #10b981; font-weight: 700;">20°C (Dingin)</span></td>
                        <td style="padding: 6px 10px; color: #64748b;">Akhir Bulan</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 6px 10px; font-weight: 600;">AC Panasonic 1 PK</td>
                        <td style="padding: 6px 10px;">Ruang Server & Kasir</td>
                        <td style="padding: 6px 10px;"><span style="color: #0284c7; font-weight: 700;">18°C (Stabil)</span></td>
                        <td style="padding: 6px 10px; color: #64748b;">Per 2 Pekan</td>
                    </tr>
                </tbody>
            </table>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px; font-size: 11.5px; color: #166534;">
                💡 <strong>Catatan GC:</strong> Filter udara AC dibersihkan berkala oleh staf shift 1 sesuai checklist General Cleaning (GC).
            </div>
        </div>
        <div class="erp-modal-footer">
            <button type="button" class="erp-modal-btn" style="background: #e2e8f0; color: #334155;" onclick="closeErpModal('dataAcModal')">Tutup</button>
            <a href="{{ route('presensi2.gc') }}" target="_blank" class="erp-modal-btn" style="background: #0284c7; color: #ffffff;">Buka Checklist GC &rarr;</a>
        </div>
    </div>
</div>

<!-- MODAL 2: STOP CODE & OTORISASI EMERGENCY KASIR -->
<div id="stopCodeModal" class="erp-modal-overlay">
    <div class="erp-modal-card">
        <div class="erp-modal-header" style="border-top-color: #ef4444;">
            <div class="erp-modal-title" style="color: #b91c1c;">
                <span>🛑</span> Stop Code & Protokol Otorisasi Darurat Kasir
            </div>
            <button type="button" class="erp-modal-close-btn" onclick="closeErpModal('stopCodeModal')">&times;</button>
        </div>
        <div class="erp-modal-body">
            <p style="margin-bottom: 12px; color: #64748b; font-size: 12px;">
                Kode darurat (Stop Code) dipergunakan untuk pembekuan billing kasir, lockdown station PC bila terjadi insiden, atau reset darurat sesi kasir:
            </p>
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 700; color: #991b1b; font-size: 13px;">Kode Stop Darurat Aktif:</span>
                    <span style="background: #dc2626; color: #fff; padding: 3px 8px; border-radius: 4px; font-family: monospace; font-weight: 700; font-size: 14px; letter-spacing: 2px;">
                        STOP-MGK-2026
                    </span>
                </div>
                <div style="font-size: 11px; color: #7f1d1d;">
                    Hanya dapat digunakan oleh Manager (Bagus) atau SPV Shift bertugas dengan konfirmasi fingerprint / PIN 6-digit.
                </div>
            </div>
            <ul style="margin: 0; padding-left: 20px; font-size: 11.5px; color: #475569; line-height: 1.6;">
                <li>Kunci seluruh PC Client warnet secara serentak bila ada pemadaman darurat genset.</li>
                <li>Simpan rekap ledger kas kasir sebelum sistem diistirahatkan.</li>
            </ul>
        </div>
        <div class="erp-modal-footer">
            <button type="button" class="erp-modal-btn" style="background: #e2e8f0; color: #334155;" onclick="closeErpModal('stopCodeModal')">Tutup</button>
            <a href="{{ route('warnet.index') }}" class="erp-modal-btn" style="background: #dc2626; color: #ffffff;">Buka Billing Warnet &rarr;</a>
        </div>
    </div>
</div>

<!-- MODAL 3: RATING & EVALUASI KEPUASAN -->
<div id="ratingModal" class="erp-modal-overlay">
    <div class="erp-modal-card">
        <div class="erp-modal-header" style="border-top-color: #f59e0b;">
            <div class="erp-modal-title" style="color: #b45309;">
                <span>⭐</span> Rating Kepuasan Pelanggan & Kualitas Layanan
            </div>
            <button type="button" class="erp-modal-close-btn" onclick="closeErpModal('ratingModal')">&times;</button>
        </div>
        <div class="erp-modal-body">
            <div style="text-align: center; padding: 10px 0 16px;">
                <div style="font-size: 40px; font-weight: 800; color: #f59e0b; line-height: 1;">4.9</div>
                <div style="font-size: 18px; color: #f59e0b; margin: 4px 0;">⭐⭐⭐⭐⭐</div>
                <div style="font-size: 11.5px; color: #64748b;">Berdasarkan 328 rating pelanggan bulan ini</div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #475569;">Kecepatan Internet & Latensi PC:</span>
                    <strong style="color: #10b981;">98% Puas</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #475569;">Kebersihan Meja & Ruangan (GC):</span>
                    <strong style="color: #10b981;">97% Puas</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #475569;">Keramahan Pelayanan Kasir:</span>
                    <strong style="color: #10b981;">99% Puas</strong>
                </div>
            </div>
        </div>
        <div class="erp-modal-footer">
            <button type="button" class="erp-modal-btn" style="background: #0284c7; color: #ffffff;" onclick="closeErpModal('ratingModal')">Selesai</button>
        </div>
    </div>
</div>

<script>
    function toggleErpSidebar() {
        const sidebar = document.getElementById('mainSidebar') || document.querySelector('.main-sidebar') || document.querySelector('.admin-sidebar');
        if (sidebar) {
            document.body.classList.toggle('sidebar-collapsed');
            document.body.classList.toggle('sidebar-open');
        }
    }

    function openErpModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('active');
        }
    }

    function closeErpModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Close modal on background click
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('erp-modal-overlay')) {
            e.target.classList.remove('active');
        }
    });
</script>
