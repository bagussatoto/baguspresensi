@extends('layouts.presensi2')

@section('title', 'Portal Login Layanan & Presensi Tambahan')

@section('content')
<style>
    /* VARIABLES & PALETTE KHUSUS PORTAL LAYANAN */
    :root {
        --layanan-card-bg: rgba(15, 23, 42, 0.88);
        --layanan-border: rgba(255, 255, 255, 0.1);
        --color-gc: #f59e0b;
        --color-gc-glow: rgba(245, 158, 11, 0.25);
        --color-meeting: #0284c7;
        --color-meeting-glow: rgba(2, 132, 199, 0.25);
        --color-bo: #10b981;
        --color-bo-glow: rgba(16, 185, 129, 0.25);
        --color-stok: #a855f7;
        --color-stok-glow: rgba(168, 85, 247, 0.25);
    }

    .layanan-portal-wrap {
        max-width: 560px;
        margin: 0 auto 40px auto;
        padding: 0 12px;
    }

    .layanan-card {
        background: var(--layanan-card-bg);
        border: 1px solid var(--layanan-border);
        border-radius: 24px;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 40px rgba(139, 92, 246, 0.15);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        padding: 28px 24px;
        position: relative;
        overflow: hidden;
    }

    .layanan-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f59e0b, #0284c7, #10b981, #a855f7);
        background-size: 300% 100%;
        animation: glowSweep 6s linear infinite;
    }

    @keyframes glowSweep {
        0% { background-position: 0% 50%; }
        100% { background-position: 300% 50%; }
    }

    .nav-back-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .btn-portal-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.2s ease;
    }

    .btn-portal-back:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .badge-portal-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(139, 92, 246, 0.15);
        border: 1px solid rgba(139, 92, 246, 0.35);
        color: #c084fc;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* SERVICE SELECTOR TABS (4 TOMBOL UTAMA) */
    .service-tabs-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .service-tabs-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 22px;
    }

    .service-tab-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.09);
        color: #cbd5e1;
        cursor: pointer;
        text-align: left;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: inherit;
        position: relative;
    }

    .service-tab-btn:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .service-tab-btn .tab-icon {
        font-size: 22px;
        line-height: 1;
        flex-shrink: 0;
    }

    .service-tab-btn .tab-title {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.25;
    }

    .service-tab-btn .tab-sub {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* ACTIVE TAB STATES */
    .service-tab-btn.active-gc {
        border-color: var(--color-gc);
        background: rgba(245, 158, 11, 0.12);
        box-shadow: 0 0 20px var(--color-gc-glow);
        color: #fff;
    }
    .service-tab-btn.active-gc .tab-title { color: #fbbf24; }

    .service-tab-btn.active-meeting {
        border-color: var(--color-meeting);
        background: rgba(2, 132, 199, 0.14);
        box-shadow: 0 0 20px var(--color-meeting-glow);
        color: #fff;
    }
    .service-tab-btn.active-meeting .tab-title { color: #38bdf8; }

    .service-tab-btn.active-bo-training {
        border-color: var(--color-bo);
        background: rgba(16, 185, 129, 0.12);
        box-shadow: 0 0 20px var(--color-bo-glow);
        color: #fff;
    }
    .service-tab-btn.active-bo-training .tab-title { color: #34d399; }

    .service-tab-btn.active-ambil-stok {
        border-color: var(--color-stok);
        background: rgba(168, 85, 247, 0.14);
        box-shadow: 0 0 20px var(--color-stok-glow);
        color: #fff;
    }
    .service-tab-btn.active-ambil-stok .tab-title { color: #c084fc; }

    /* BANNER INFO LAYANAN AKTIF */
    .active-service-banner {
        padding: 14px 16px;
        border-radius: 14px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .active-service-banner.banner-gc {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .active-service-banner.banner-meeting {
        background: rgba(2, 132, 199, 0.1);
        border: 1px solid rgba(2, 132, 199, 0.3);
    }
    .active-service-banner.banner-bo-training {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .active-service-banner.banner-ambil-stok {
        background: rgba(168, 85, 247, 0.1);
        border: 1px solid rgba(168, 85, 247, 0.3);
    }

    /* FORM STYLES */
    .form-group-custom {
        margin-bottom: 18px;
    }

    .form-label-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 700;
        color: #f1f5f9;
        margin-bottom: 8px;
    }

    .input-box-wrap {
        position: relative;
    }

    .input-icon-left {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        pointer-events: none;
        color: #94a3b8;
    }

    .form-input-field {
        width: 100%;
        height: 48px;
        padding: 10px 14px 10px 44px;
        border-radius: 12px;
        background: rgba(10, 15, 29, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #fff;
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-input-field:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
    }

    .btn-toggle-eye {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 6px;
        font-size: 16px;
    }

    .btn-submit-layanan {
        width: 100%;
        height: 52px;
        border-radius: 14px;
        border: none;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 24px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-submit-layanan.btn-gc {
        background: linear-gradient(135deg, #d97706, #f59e0b);
        box-shadow: 0 10px 25px rgba(245, 158, 11, 0.35);
    }
    .btn-submit-layanan.btn-meeting {
        background: linear-gradient(135deg, #0284c7, #0ea5e9);
        box-shadow: 0 10px 25px rgba(2, 132, 199, 0.35);
    }
    .btn-submit-layanan.btn-bo-training {
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);
    }
    .btn-submit-layanan.btn-ambil-stok {
        background: linear-gradient(135deg, #7e22ce, #a855f7);
        box-shadow: 0 10px 25px rgba(168, 85, 247, 0.35);
    }

    .btn-submit-layanan:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
    }

    .footer-note {
        text-align: center;
        margin-top: 20px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .footer-note a {
        color: #38bdf8;
        text-decoration: none;
        font-weight: 600;
    }

    .footer-note a:hover {
        text-decoration: underline;
    }
</style>

<div class="layanan-portal-wrap">
    <div class="layanan-card">
        {{-- HEADER NAVIGATION --}}
        <div class="nav-back-row">
            <a href="{{ route('presensi2.login') }}" class="btn-portal-back">
                <span>&larr;</span> Portal Presensi Utama
            </a>
            <span class="badge-portal-type">
                <span>🔐</span> Portal Khusus Layanan
            </span>
        </div>

        {{-- TITLE & BRANDING --}}
        <div style="text-align: center; margin-bottom: 22px;">
            <div style="width: 56px; height: 56px; margin: 0 auto 12px auto; border-radius: 16px; background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.35); display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);">
                <span id="header-avatar-icon">
                    @if($service === 'gc') 🧹
                    @elseif($service === 'meeting') 🤝
                    @elseif($service === 'bo-training') 💼
                    @else 📦
                    @endif
                </span>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px;" id="header-portal-title">
                Login Presensi & Layanan Tambahan
            </h2>
            <p style="font-size: 13px; color: var(--text-muted);">
                Pilih layanan di bawah dan masukkan Password/PIN untuk login ke halaman tersebut.
            </p>
        </div>

        {{-- FLASH ALERTS --}}
        @if(session('error'))
            <div style="padding: 12px 14px; border-radius: 12px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div style="padding: 12px 14px; border-radius: 12px; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.35); color: #7dd3fc; font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                <span>ℹ️</span>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div style="padding: 12px 14px; border-radius: 12px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; font-size: 13px; margin-bottom: 18px;">
                @foreach ($errors->all() as $error)
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span>❌</span> <span>{{ $error }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- STEP 1: PILIH LAYANAN (4 TOMBOL DEDIKASI) --}}
        <div class="service-tabs-label">
            <span>1. Pilih Layanan yang Dituju:</span>
            <span style="font-size: 10px; color: #38bdf8;">Klik untuk berganti</span>
        </div>

        <div class="service-tabs-grid">
            <!-- 1. General Cleaning (GC) -->
            <button type="button" class="service-tab-btn {{ $service === 'gc' ? 'active-gc' : '' }}" id="tab-gc" onclick="selectLayanan('gc')">
                <span class="tab-icon">🧹</span>
                <div>
                    <div class="tab-title">General Cleaning</div>
                    <div class="tab-sub">Kebersihan & Sanitasi</div>
                </div>
            </button>

            <!-- 2. Meeting -->
            <button type="button" class="service-tab-btn {{ $service === 'meeting' ? 'active-meeting' : '' }}" id="tab-meeting" onclick="selectLayanan('meeting')">
                <span class="tab-icon">🤝</span>
                <div>
                    <div class="tab-title">Meeting</div>
                    <div class="tab-sub">Briefing & Rapat Shift</div>
                </div>
            </button>

            <!-- 3. Presnesi BO/Training -->
            <button type="button" class="service-tab-btn {{ $service === 'bo-training' ? 'active-bo-training' : '' }}" id="tab-bo-training" onclick="selectLayanan('bo-training')">
                <span class="tab-icon">💼</span>
                <div>
                    <div class="tab-title">BO / Training</div>
                    <div class="tab-sub">Staf BO & Pelatihan</div>
                </div>
            </button>

            <!-- 4. AMBIL STOK -->
            <button type="button" class="service-tab-btn {{ $service === 'ambil-stok' ? 'active-ambil-stok' : '' }}" id="tab-ambil-stok" onclick="selectLayanan('ambil-stok')">
                <span class="tab-icon">📦</span>
                <div>
                    <div class="tab-title">AMBIL STOK</div>
                    <div class="tab-sub">Logistik & Restock Outlet</div>
                </div>
            </button>
        </div>

        {{-- BANNER DESKRIPSI LAYANAN AKTIF --}}
        <div class="active-service-banner {{ 'banner-'.$service }}" id="service-info-banner">
            <div style="font-size: 26px;" id="banner-icon">
                @if($service === 'gc') 🧹
                @elseif($service === 'meeting') 🤝
                @elseif($service === 'bo-training') 💼
                @else 📦
                @endif
            </div>
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #fff;" id="banner-title">
                    @if($service === 'gc') Presensi General Cleaning (GC)
                    @elseif($service === 'meeting') Presensi Meeting & Briefing
                    @elseif($service === 'bo-training') Presensi Back Office (BO) & Training
                    @else Permintaan Ambil Stok & Logistik
                    @endif
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;" id="banner-desc">
                    @if($service === 'gc') Catat kegiatan pembersihan stasiun PC, AC, area cafe, toilet, dan kasir.
                    @elseif($service === 'meeting') Presensi evaluasi pergantian shift dan briefing tim operasional.
                    @elseif($service === 'bo-training') Presensi kegiatan staf Back Office serta modul workshop SOP.
                    @else Ajukan pengambilan stok barang kasir atau konfirmasi kiriman dari gudang.
                    @endif
                </div>
            </div>
        </div>

        {{-- FORM LOGIN KHUSUS LAYANAN --}}
        <form action="{{ route('presensi2.layanan.login.post') }}" method="POST" id="form-layanan-login">
            @csrf
            <input type="hidden" name="service" id="input_service" value="{{ $service }}">

            {{-- 1. IDENTITAS KARYAWAN --}}
            <div class="form-group-custom">
                <label for="employee_id" class="form-label-custom">
                    <span>👤 Identitas Karyawan:</span>
                    <span style="font-size: 11px; color: #38bdf8;">(Wajib Dipilih)</span>
                </label>
                <div class="input-box-wrap">
                    <span class="input-icon-left">👤</span>
                    <select name="employee_id" id="employee_id" class="form-input-field" required style="cursor: pointer; appearance: none; -webkit-appearance: none; padding-right: 38px;">
                        <option value="" disabled {{ old('employee_id') ? '' : 'selected' }}>-- Pilih Nama Anda --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->code ?? 'OP' }}) — {{ $emp->position ?? ($emp->role ? $emp->role->value : 'Staf') }}
                            </option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8; font-size: 12px;">▼</div>
                </div>
            </div>

            {{-- 2. PASSWORD / PIN 6-DIGIT --}}
            <div class="form-group-custom">
                <label for="credential" class="form-label-custom">
                    <span>🔐 Password Akun atau PIN 6-Digit:</span>
                    <span style="font-size: 11px; color: #fbbf24;">(Kasih Password)</span>
                </label>
                <div class="input-box-wrap">
                    <span class="input-icon-left">🔑</span>
                    <input type="password" name="credential" id="credential" class="form-input-field" placeholder="Masukkan Password atau PIN 6-Digit Anda" required minlength="4" autocomplete="current-password" autofocus>
                    <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility()" title="Lihat Password">
                        👁️
                    </button>
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                    Password akun diperlukan untuk mengotentikasi kehadiran Anda di layanan ini.
                </div>
            </div>

            {{-- SUBMIT BUTTON --}}
            <button type="submit" class="btn-submit-layanan {{ 'btn-'.$service }}" id="btn-submit-layanan">
                <span id="btn-icon">
                    @if($service === 'gc') 🧹
                    @elseif($service === 'meeting') 🤝
                    @elseif($service === 'bo-training') 💼
                    @else 📦
                    @endif
                </span>
                <span id="btn-label">
                    @if($service === 'gc') Masuk ke General Cleaning (GC)
                    @elseif($service === 'meeting') Masuk ke Presensi Meeting
                    @elseif($service === 'bo-training') Masuk ke BO / Training
                    @else Masuk ke AMBIL STOK
                    @endif
                </span>
            </button>
        </form>

        <div class="footer-note">
            Presensi shift kerja harian reguler? <a href="{{ route('presensi2.login') }}">Buka Portal Presensi Reguler &rarr;</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const serviceMeta = {
        'gc': {
            title: 'General Cleaning (GC)',
            bannerTitle: 'Presensi General Cleaning (GC)',
            bannerDesc: 'Catat kegiatan pembersihan stasiun PC, AC, area cafe, toilet, dan kasir.',
            icon: '🧹',
            activeClass: 'active-gc',
            bannerClass: 'banner-gc',
            btnClass: 'btn-gc',
            btnText: 'Masuk ke General Cleaning (GC)'
        },
        'meeting': {
            title: 'Meeting',
            bannerTitle: 'Presensi Meeting & Briefing',
            bannerDesc: 'Presensi evaluasi pergantian shift dan briefing tim operasional.',
            icon: '🤝',
            activeClass: 'active-meeting',
            bannerClass: 'banner-meeting',
            btnClass: 'btn-meeting',
            btnText: 'Masuk ke Presensi Meeting'
        },
        'bo-training': {
            title: 'Presensi BO / Training',
            bannerTitle: 'Presensi Back Office (BO) & Training',
            bannerDesc: 'Presensi kegiatan staf Back Office serta modul workshop SOP.',
            icon: '💼',
            activeClass: 'active-bo-training',
            bannerClass: 'banner-bo-training',
            btnClass: 'btn-bo-training',
            btnText: 'Masuk ke BO / Training'
        },
        'ambil-stok': {
            title: 'AMBIL STOK',
            bannerTitle: 'Permintaan Ambil Stok & Logistik',
            bannerDesc: 'Ajukan pengambilan stok barang kasir atau konfirmasi kiriman dari gudang.',
            icon: '📦',
            activeClass: 'active-ambil-stok',
            bannerClass: 'banner-ambil-stok',
            btnClass: 'btn-ambil-stok',
            btnText: 'Masuk ke AMBIL STOK'
        }
    };

    function selectLayanan(key) {
        if (!serviceMeta[key]) return;
        const meta = serviceMeta[key];

        // Update Hidden input
        document.getElementById('input_service').value = key;

        // Update tabs active state
        document.querySelectorAll('.service-tab-btn').forEach(btn => {
            btn.classList.remove('active-gc', 'active-meeting', 'active-bo-training', 'active-ambil-stok');
        });
        const activeTab = document.getElementById('tab-' + key);
        if (activeTab) activeTab.classList.add(meta.activeClass);

        // Update banner
        const banner = document.getElementById('service-info-banner');
        banner.className = 'active-service-banner ' + meta.bannerClass;
        document.getElementById('banner-icon').textContent = meta.icon;
        document.getElementById('banner-title').textContent = meta.bannerTitle;
        document.getElementById('banner-desc').textContent = meta.bannerDesc;

        // Update header icon
        document.getElementById('header-avatar-icon').textContent = meta.icon;

        // Update submit button
        const submitBtn = document.getElementById('btn-submit-layanan');
        submitBtn.className = 'btn-submit-layanan ' + meta.btnClass;
        document.getElementById('btn-icon').textContent = meta.icon;
        document.getElementById('btn-label').textContent = meta.btnText;

        // Update query string in browser without reload
        const url = new URL(window.location);
        url.searchParams.set('service', key);
        window.history.replaceState({}, '', url);

        // Focus credential if employee already chosen
        const emp = document.getElementById('employee_id');
        const cred = document.getElementById('credential');
        if (emp.value) {
            cred.focus();
        } else {
            emp.focus();
        }
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('credential');
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }

    document.getElementById('form-layanan-login').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit-layanan');
        const label = document.getElementById('btn-label');
        label.textContent = 'Memverifikasi Password...';
        btn.style.opacity = '0.85';
        btn.style.pointerEvents = 'none';
    });
</script>
@endpush
@endsection
