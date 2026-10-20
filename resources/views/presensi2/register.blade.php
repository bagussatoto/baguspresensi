@extends('layouts.presensi2')

@section('title', 'Pendaftaran & Daftar Ulang Karyawan — MGK v2')

@section('content')
<style>
    /* THEME VARIABLES */
    :root {
        --reg-bg-card: rgba(15, 23, 42, 0.85);
        --reg-card-border: rgba(255, 255, 255, 0.1);
        --reg-text-heading: #ffffff;
        --reg-text-body: #94a3b8;
        --reg-input-bg: rgba(10, 15, 29, 0.75);
        --reg-input-border: rgba(255, 255, 255, 0.12);
        --reg-input-text: #ffffff;
        --reg-info-bg: rgba(15, 23, 42, 0.65);
        --reg-info-border: rgba(255, 255, 255, 0.08);
        --reg-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 30px rgba(16, 185, 129, 0.12);
    }

    body.light-theme {
        --reg-bg-card: rgba(255, 255, 255, 0.96);
        --reg-card-border: rgba(226, 232, 240, 0.9);
        --reg-text-heading: #0f172a;
        --reg-text-body: #64748b;
        --reg-input-bg: #f8fafc;
        --reg-input-border: #cbd5e1;
        --reg-input-text: #0f172a;
        --reg-info-bg: #f1f5f9;
        --reg-info-border: #e2e8f0;
        --reg-shadow: 0 20px 45px -10px rgba(16, 185, 129, 0.12), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
    }

    .reg-auth-container {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 16px auto 36px auto;
        perspective: 1000px;
    }

    .ambient-glow-emerald {
        position: absolute;
        top: -30px;
        left: -30px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.35) 0%, transparent 70%);
        filter: blur(40px);
        pointer-events: none;
        z-index: 0;
    }

    .ambient-glow-blue {
        position: absolute;
        bottom: -20px;
        right: -30px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(2, 132, 199, 0.3) 0%, transparent 70%);
        filter: blur(45px);
        pointer-events: none;
        z-index: 0;
    }

    .reg-card {
        position: relative;
        z-index: 1;
        background: var(--reg-bg-card);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid var(--reg-card-border);
        border-radius: 26px;
        padding: 32px 28px;
        box-shadow: var(--reg-shadow);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .reg-card-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981, #06b6d4, #0284c7, #10b981);
        background-size: 300% 100%;
        animation: gradientSweep 8s linear infinite;
    }

    @keyframes gradientSweep {
        0% { background-position: 0% 50%; }
        100% { background-position: 300% 50%; }
    }

    .reg-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .reg-badge-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #10b981;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    body.light-theme .reg-badge-tag {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }

    .reg-brand-block {
        text-align: center;
        margin-bottom: 22px;
    }

    .reg-emblem {
        width: 68px;
        height: 68px;
        border-radius: 20px;
        background: linear-gradient(135deg, #10b981 0%, #0284c7 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 30px;
        box-shadow: 0 10px 25px -4px rgba(16, 185, 129, 0.5);
        margin-bottom: 12px;
    }

    .reg-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--reg-text-heading);
        letter-spacing: -0.025em;
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }

    .reg-subtitle {
        font-size: 13px;
        color: var(--reg-text-body);
        font-weight: 500;
    }

    .form-group-custom {
        margin-bottom: 16px;
        text-align: left;
    }

    .form-label-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        font-weight: 600;
        color: var(--reg-text-heading);
        margin-bottom: 7px;
    }

    .input-field-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-leading-icon {
        position: absolute;
        left: 14px;
        color: var(--reg-text-body);
        font-size: 16px;
        pointer-events: none;
    }

    .form-control-custom {
        width: 100%;
        background: var(--reg-input-bg);
        border: 1.5px solid var(--reg-input-border);
        border-radius: 12px;
        padding: 12px 16px 12px 42px;
        color: var(--reg-input-text);
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .form-control-custom:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.22);
    }

    .password-toggle-btn {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: var(--reg-text-body);
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pin-dots-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 8px;
    }

    .pin-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--reg-input-border);
        transition: all 0.2s ease;
    }

    .pin-dot.filled {
        background: #10b981;
        box-shadow: 0 0 8px #10b981;
        transform: scale(1.25);
    }

    .btn-reg-submit {
        width: 100%;
        background: linear-gradient(135deg, #10b981 0%, #059669 50%, #0284c7 100%);
        color: #ffffff;
        border: none;
        border-radius: 14px;
        padding: 14px 24px;
        font-size: 15px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        cursor: pointer;
        box-shadow: 0 8px 24px -4px rgba(16, 185, 129, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 22px;
        transition: all 0.25s ease;
    }

    .btn-reg-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -4px rgba(16, 185, 129, 0.55);
    }

    .reg-info-box {
        margin-top: 20px;
        background: var(--reg-info-bg);
        border: 1px solid var(--reg-info-border);
        border-radius: 14px;
        padding: 14px;
        font-size: 12px;
        color: var(--reg-text-body);
        line-height: 1.6;
        text-align: left;
    }

    .back-to-login {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 18px;
        font-size: 13px;
        font-weight: 600;
        color: #0284c7;
        text-decoration: none;
        transition: color 0.2s;
    }

    .back-to-login:hover {
        color: #38bdf8;
        text-decoration: underline;
    }
</style>

<div class="reg-auth-container">
    <div class="ambient-glow-emerald"></div>
    <div class="ambient-glow-blue"></div>

    <div class="reg-card">
        <div class="reg-card-accent"></div>

        <div class="reg-header-actions">
            <div class="reg-badge-tag">
                <span>📝 Form Pendaftaran &amp; Daftar Ulang</span>
            </div>

            <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" onclick="togglePortalTheme()" style="background: rgba(255, 255, 255, 0.08); border: 1px solid var(--reg-card-border); color: var(--reg-text-body); font-size: 11px; font-weight: 600; padding: 5px 10px; border-radius: 999px; cursor: pointer;">
                <span id="theme-icon">☀️</span>
                <span id="theme-label">Mode Terang</span>
            </button>
        </div>

        <div class="reg-brand-block">
            <div class="reg-emblem">📋</div>
            <h1 class="reg-title">Daftar &amp; Daftar Ulang</h1>
            <p class="reg-subtitle">Portal Presensi Karyawam Maya Graha Kencana</p>
        </div>

        @if($errors->any())
            <div style="background: rgba(239, 68, 68, 0.14); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 18px; text-align: left;">
                <span>⚠️</span> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('presensi2.register.post') }}" method="POST" id="form-register">
               <!-- 1. Nama Lengkap -->
            <div class="form-group-custom">
                <label for="full_name" class="form-label-custom">
                    <span>1. Nama Lengkap</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;">(Sesuai KTP / Identitas)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">📝</span>
                    <input 
                        type="text" 
                        name="full_name" 
                        id="full_name" 
                        class="form-control-custom" 
                        placeholder="Contoh: Budi Santoso"
                        value="{{ old('full_name') }}" 
                        required 
                        minlength="3"
                        autofocus
                    >
                </div>
            </div>

            <!-- 2. Username -->
            <div class="form-group-custom">
                <label for="username" class="form-label-custom">
                    <span>2. Username Pilihan</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;">(Untuk login akun)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">👤</span>
                    <input 
                        type="text" 
                        name="username" 
                        id="username" 
                        class="form-control-custom" 
                        placeholder="Contoh: budi_s"
                        value="{{ old('username') }}" 
                        required 
                        minlength="3"
                    >
                </div>
            </div>

            <!-- 3. PIN / Password -->
            <div class="form-group-custom">
                <label for="credential" class="form-label-custom">
                    <span>3. PIN atau Password</span>
                    <span style="font-size: 11px; color: #10b981; font-weight: normal;" id="pin-status-text">
                        (Rekomendasi 6-Digit Angka)
                    </span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">🔐</span>
                    <input 
                        type="password" 
                        name="credential" 
                        id="credential" 
                        class="form-control-custom" 
                        placeholder="Ketik PIN 6-Digit Angka" 
                        required 
                        minlength="4"
                        style="padding-right: 42px;"
                        oninput="handleCredentialInput(this.value)"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="toggleCredentialVisibility()" 
                        title="Tampilkan / Sembunyikan"
                    >
                        <span id="eye-icon">👁️</span>
                    </button>
                </div>

                <div class="pin-dots-indicator" id="pin-dots-container">
                    <div class="pin-dot" id="pdot-1"></div>
                    <div class="pin-dot" id="pdot-2"></div>
                    <div class="pin-dot" id="pdot-3"></div>
                    <div class="pin-dot" id="pdot-4"></div>
                    <div class="pin-dot" id="pdot-5"></div>
                    <div class="pin-dot" id="pdot-6"></div>
                </div>
            </div>

            <!-- 4. NO HP / WHATSAPP -->
            <div class="form-group-custom">
                <label for="phone" class="form-label-custom">
                    <span>4. Nomor HP / WhatsApp</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;">(Untuk verifikasi identitas)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">📱</span>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        class="form-control-custom" 
                        placeholder="Contoh: 081234567890" 
                        value="{{ old('phone') }}" 
                        required
                        minlength="8"
                    >
                </div>
            </div>

            <!-- 5. Email (Opsional) -->
            <div class="form-group-custom">
                <label for="email" class="form-label-custom">
                    <span>5. Alamat Email</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;">(Opsional)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">✉️</span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control-custom" 
                        placeholder="Contoh: nama@domain.com" 
                        value="{{ old('email') }}"
                    >
                </div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); border: 1px dashed rgba(16, 185, 129, 0.25); border-radius: 12px; padding: 12px 14px; margin-bottom: 20px; font-size: 12px; color: var(--reg-text-body); text-align: left; line-height: 1.5;">
                🛡️ <strong>Prinsip SoD &amp; ISO 27001:</strong> Penempatan Cabang, Jabatan, dan Hak Akses akan ditentukan oleh Admin/HR saat proses verifikasi persetujuan akun.
            </div>

            <button type="submit" class="btn-reg-submit" id="btn-submit-reg">
                <span>🚀</span>
                <span id="btn-text">Ajukan Pendaftaran Mandiri</span>
            </button>
        </form>

        <div class="reg-info-box">
            💡 <strong>Alur Maker-Checker:</strong> Setelah permohonan dikirim, akun Anda masuk ke antrean verifikasi Admin/HR. Anda dapat login dan melakukan presensi setelah akun disetujui.
        </div>

        <div style="margin-top: 18px; text-align: center;">
            <a href="{{ route('presensi2.login') }}" class="back-to-login">
                <span>←</span>
                <span>Sudah Punya Akun? Kembali ke Halaman Masuk</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function initPortalTheme() {
        const savedTheme = localStorage.getItem('mgk_portal_theme') || 'dark';
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
            updateThemeUI(true);
        } else {
            document.body.classList.remove('light-theme');
            updateThemeUI(false);
        }
    }

    function togglePortalTheme() {
        const isLight = document.body.classList.toggle('light-theme');
        localStorage.setItem('mgk_portal_theme', isLight ? 'light' : 'dark');
        updateThemeUI(isLight);
    }

    function updateThemeUI(isLight) {
        const icon = document.getElementById('theme-icon');
        const label = document.getElementById('theme-label');
        if (isLight) {
            icon.textContent = '🌙';
            label.textContent = 'Mode Gelap';
        } else {
            icon.textContent = '☀️';
            label.textContent = 'Mode Terang';
        }
    }

    function handleCredentialInput(val) {
        const isNumeric = /^\d+$/.test(val);
        const len = val.length;
        const statusText = document.getElementById('pin-status-text');

        for (let i = 1; i <= 6; i++) {
            const dot = document.getElementById(`pdot-${i}`);
            if (dot) {
                if (isNumeric && i <= len) {
                    dot.classList.add('filled');
                } else {
                    dot.classList.remove('filled');
                }
            }
        }

        if (isNumeric && len > 0) {
            statusText.textContent = `PIN Terdeteksi (${len}/6 Digit)`;
            statusText.style.color = '#10b981';
        } else if (len > 0) {
            statusText.textContent = 'Password Alfanumerik';
            statusText.style.color = 'var(--reg-text-body)';
        } else {
            statusText.textContent = '(Rekomendasi 6-Digit Angka)';
            statusText.style.color = '#10b981';
        }
    }

    function toggleCredentialVisibility() {
        const input = document.getElementById('credential');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = '🙈';
        } else {
            input.type = 'password';
            icon.textContent = '👁️';
        }
    }

    document.getElementById('form-register').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit-reg');
        const btnText = document.getElementById('btn-text');
        btnText.textContent = 'Menyimpan Data Karyawan...';
        btn.style.opacity = '0.85';
        btn.style.pointerEvents = 'none';
    });

    document.addEventListener('DOMContentLoaded', initPortalTheme);
</script>
@endpush
@endsection
