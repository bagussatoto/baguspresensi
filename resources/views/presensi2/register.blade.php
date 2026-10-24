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

        <!-- Notifikasi Berhasil & Gagal -->
        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.16); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 14px 16px; border-radius: 14px; font-size: 13px; margin-bottom: 18px; text-align: left; display: flex; align-items: flex-start; gap: 10px; line-height: 1.5;">
                <span style="font-size: 18px; flex-shrink: 0;">🎉</span>
                <div>
                    <strong style="color: #10b981; display: block; margin-bottom: 2px;">Pendaftaran Berhasil Diajukan!</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24; padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 18px; text-align: left; display: flex; align-items: flex-start; gap: 10px; line-height: 1.5;">
                <span style="font-size: 16px; flex-shrink: 0;">⚠️</span>
                <div>{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.14); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 18px; text-align: left; display: flex; align-items: flex-start; gap: 10px; line-height: 1.5;">
                <span style="font-size: 16px; flex-shrink: 0;">❌</span>
                <div>
                    <strong style="color: #ef4444; display: block; margin-bottom: 2px;">Pendaftaran Gagal:</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div style="background: rgba(239, 68, 68, 0.14); border: 1.5px solid rgba(239, 68, 68, 0.45); color: #fca5a5; padding: 14px 18px; border-radius: 14px; font-size: 13px; margin-bottom: 18px; text-align: left; line-height: 1.5;">
                <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px;">
                    <span style="font-size: 18px; flex-shrink: 0;">⚠️</span>
                    <div>
                        <strong style="color: #ef4444; font-size: 14px; display: block; margin-bottom: 2px;">Pendaftaran Gagal: Belum Semua Data Diisi / Valid!</strong>
                        <span style="font-size: 12px; color: var(--reg-text-body);">Ditemukan {{ $errors->count() }} kolom yang perlu diperiksa atau diisi satu persatu:</span>
                    </div>
                </div>
                <ul style="margin: 4px 0 0 14px; padding: 0; list-style-type: none; font-size: 12.5px;">
                    @foreach($errors->all() as $err)
                        <li style="margin-bottom: 4px; display: flex; align-items: flex-start; gap: 6px;">
                            <span style="color: #ef4444;">❌</span>
                            <span>{{ $err }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('presensi2.register.post') }}" method="POST" id="form-register">
            @csrf

            <!-- Proteksi Honeypot Anti-Spam Bot (Tersembunyi bagi pengguna manusia, jebakan untuk bot otomatis) -->
            <div style="display: none !important; position: absolute !important; left: -9999px !important; top: -9999px !important; width: 0 !important; height: 0 !important; overflow: hidden !important;" aria-hidden="true">
                <label for="honeypot_website">Website / Profil Perusahaan (Kosongkan):</label>
                <input type="text" name="honeypot_website" id="honeypot_website" tabindex="-1" autocomplete="off" value="">
            </div>

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
                <div id="full_name-feedback" style="font-size: 12px; margin-top: 5px; display: none; line-height: 1.4;"></div>
            </div>

            <!-- 2. Username -->
            <div class="form-group-custom">
                <label for="username" class="form-label-custom">
                    <span>2. Username Pilihan</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;">(Huruf, angka, titik, strip, garis bawah)</span>
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
                        autocomplete="off"
                    >
                    <span id="username-spinner" style="position: absolute; right: 14px; display: none; font-size: 13px;">⏳</span>
                </div>
                <div id="username-feedback" style="font-size: 12px; margin-top: 5px; display: none; line-height: 1.4;"></div>
                <div id="username-suggestions" style="margin-top: 6px; display: none;"></div>
            </div>

            <!-- 3. PIN atau Password -->
            <div class="form-group-custom">
                <label for="credential" class="form-label-custom">
                    <span>3. PIN atau Password</span>
                    <span style="font-size: 11px; color: #10b981; font-weight: normal;" id="pin-status-text">
                        (PIN 6 Digit Angka ATAU Password 8 Karakter)
                    </span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">🔐</span>
                    <input 
                        type="password" 
                        name="credential" 
                        id="credential" 
                        class="form-control-custom" 
                        placeholder="PIN 6-digit angka ATAU Password min. 8 karakter (huruf/angka)" 
                        required 
                        minlength="6"
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
                <div id="credential-feedback" style="font-size: 12px; margin-top: 5px; display: none; line-height: 1.4;"></div>

                <div class="pin-dots-indicator" id="pin-dots-container">
                    <div class="pin-dot" id="pdot-1"></div>
                    <div class="pin-dot" id="pdot-2"></div>
                    <div class="pin-dot" id="pdot-3"></div>
                    <div class="pin-dot" id="pdot-4"></div>
                    <div class="pin-dot" id="pdot-5"></div>
                    <div class="pin-dot" id="pdot-6"></div>
                </div>

                <!-- Indikator Kekuatan Password Dinamis (Mode Password) -->
                <div id="password-meter-container" style="display: none; margin-top: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--reg-input-border); border-radius: 10px; padding: 10px 12px; text-align: left;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 11.5px;">
                        <span style="color: var(--reg-text-body);">Kekuatan Password:</span>
                        <span id="password-strength-label" style="font-weight: 700; color: #f59e0b;">Cukup</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 999px; overflow: hidden;">
                        <div id="password-strength-bar" style="width: 0%; height: 100%; border-radius: 999px; transition: all 0.3s ease; background: #ef4444;"></div>
                    </div>
                    <div id="password-strength-hint" style="font-size: 11px; color: var(--reg-text-body); margin-top: 5px; line-height: 1.35;"></div>
                </div>
            </div>

            <!-- Konfirmasi PIN / Password -->
            <div class="form-group-custom">
                <label for="confirm_credential" class="form-label-custom">
                    <span>Ulangi PIN atau Password</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;" id="confirm-status-text">
                        (Harus sama persis)
                    </span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">🛡️</span>
                    <input 
                        type="password" 
                        name="confirm_credential" 
                        id="confirm_credential" 
                        class="form-control-custom" 
                        placeholder="Ketik ulang PIN atau Password di atas" 
                        required 
                        minlength="6"
                        style="padding-right: 42px;"
                        oninput="handleConfirmCredentialInput(this.value)"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="toggleConfirmVisibility()" 
                        title="Tampilkan / Sembunyikan"
                    >
                        <span id="eye-icon-confirm">👁️</span>
                    </button>
                </div>
                <div id="confirm_credential-feedback" style="font-size: 12px; margin-top: 5px; display: none; line-height: 1.4;"></div>
            </div>

            <!-- 4. NO HP / WHATSAPP -->
            <div class="form-group-custom">
                <label for="phone" class="form-label-custom">
                    <span>4. Nomor HP / WhatsApp</span>
                    <span style="font-size: 11px; color: var(--reg-text-body); font-weight: normal;">(Nomor ponsel aktif, cth: 081234567890)</span>
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
                        minlength="10"
                        maxlength="16"
                        autocomplete="off"
                    >
                    <span id="phone-spinner" style="position: absolute; right: 14px; display: none; font-size: 13px;">⏳</span>
                </div>
                <div id="phone-feedback" style="font-size: 12px; margin-top: 5px; display: none; line-height: 1.4;"></div>
            </div>

            <!-- 5. Email -->
            <div class="form-group-custom">
                <label for="email" class="form-label-custom">
                    <span>5. Alamat Email</span>
                    <span style="font-size: 11px; color: #10b981; font-weight: normal;">(Wajib &amp; Unik)</span>
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
                        required
                        autocomplete="off"
                    >
                    <span id="email-spinner" style="position: absolute; right: 14px; display: none; font-size: 13px;">⏳</span>
                </div>
                <div id="email-feedback" style="font-size: 12px; margin-top: 5px; display: none; line-height: 1.4;"></div>
            </div>

            <!-- Cek List Kelengkapan & Ketersediaan Data (Daftar / Daftar Ulang) -->
            <div id="checklist-card" style="background: var(--reg-info-bg); border: 1.5px solid var(--reg-info-border); border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; text-align: left;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--reg-text-heading); display: flex; align-items: center; gap: 6px;">
                        
                        <span>Status Data Daftar Ulang</span>
                    </div>
                    <span id="checklist-counter" style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px; background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                        0 / 5 Lengkap
                    </span>
                </div>

                <!-- Progress Bar -->
                <div style="width: 100%; height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 999px; overflow: hidden; margin-bottom: 12px;">
                    <div id="checklist-progress" style="width: 0%; height: 100%; background: linear-gradient(90deg, #f59e0b, #10b981); transition: width 0.3s ease; border-radius: 999px;"></div>
                </div>

                

                <!-- Notifikasi Keseluruhan & Satu Persatu (Dynamic Alert) -->
                <div id="validation-missing-summary" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); border: 1.5px solid rgba(239, 68, 68, 0.35); font-size: 12px;">
                    <div style="color: #ef4444; font-weight: 700; margin-bottom: 5px; display: flex; align-items: center; gap: 6px;">
                        <span>⚠️</span>
                        <span>Pemberitahuan Keamanan: Formulir Belum Semua Diisi!</span>
                    </div>
                    <div style="color: var(--reg-text-body); font-size: 11.5px; margin-bottom: 6px;">
                        Mohon periksa dan lengkapi data satu persatu berikut ini agar akun Anda terlindungi:
                    </div>
                    <ul id="missing-fields-list" style="margin: 0; padding-left: 18px; color: var(--reg-text-heading); line-height: 1.6;"></ul>
                </div>

                <div id="validation-success-summary" style="display: none; margin-top: 12px; padding: 10px 14px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); border: 1.5px solid rgba(16, 185, 129, 0.35); font-size: 12px; color: #10b981; font-weight: 600;">
                    ✅ Semua 5 data lengkap, terverifikasi tersedia dan dapat digunakan untuk pendaftaran ulang!
                </div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); border: 1px dashed rgba(16, 185, 129, 0.25); border-radius: 12px; padding: 12px 14px; margin-bottom: 20px; font-size: 12px; color: var(--reg-text-body); text-align: left; line-height: 1.5;">
                🛡️ <strong>Prinsip SoD &amp; ISO 27001:</strong> Pengecekan silang otomatis dilakukan ke Master Data Karyawan dan Antrean Pendaftaran. Tombol pendaftaran aktif saat seluruh data valid.
            </div>

            <button type="submit" class="btn-reg-submit" id="btn-submit-reg">
                <span>🚀</span>
                <span id="btn-text">Daftar / Daftar Ulang</span>
            </button>
        </form>

        <div class="reg-info-box">
            ⏱️ <strong>Estimasi Verifikasi (Alur Maker-Checker):</strong> Estimasi waktu verifikasi oleh Admin/HR adalah <strong>1x24 jam kerja</strong> (Senin – Sabtu). Anda dapat langsung login dan presensi setelah akun disetujui.
        </div>

        <div style="margin-top: 18px; text-align: center;">
            <a href="{{ route('presensi2.login') }}" class="back-to-login">
                <span>←</span>
                <span>Sudah Punya Akun? Kembali ke Halaman Masuk</span>
            </a>
        </div>

        <div style="margin-top: 10px; text-align: center;">
            <a href="{{ route('presensi2.check-status') }}" style="color: var(--reg-text-body); font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <span>🔍</span>
                <span>Sudah mengajukan pendaftaran? <strong>Cek Status Verifikasi di Sini</strong></span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const checkAvailabilityUrl = "{{ route('presensi2.register.check') }}";
    const csrfToken = "{{ csrf_token() }}";

    const fieldStates = {
        fullName: false,
        username: false,
        credential: false,
        confirmCredential: false,
        phone: false,
        email: false
    };

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

    function isWeakPin(pin) {
        if (!/^\d{6}$/.test(pin)) return false;
        if (/^(\d)\1{5}$/.test(pin)) return true;
        if (/^(\d{2})\1{2}$/.test(pin)) return true;
        if (/^(\d{3})\1$/.test(pin)) return true;
        const asc = '0123456789';
        const desc = '9876543210';
        if (asc.includes(pin) || desc.includes(pin)) return true;
        const common = ['112233', '332211', '123321', '321123', '102030', '302010', '007007'];
        if (common.includes(pin)) return true;
        return false;
    }

    function calculatePasswordStrength(val) {
        let score = 0;
        const len = val.length;
        if (len >= 8) score += 1;
        if (len >= 10) score += 1;
        if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score += 1;
        if (/\d/.test(val)) score += 1;
        if (/[^a-zA-Z0-9]/.test(val)) score += 1;

        if (len < 8) {
            return {
                percent: Math.min(30, Math.round((len / 8) * 30)),
                label: 'Kurang (< 8 Karakter)',
                color: '#ef4444',
                hint: '⚠️ Password minimal 8 karakter angka ataupun huruf.'
            };
        }

        if (score <= 2) {
            return {
                percent: 45,
                label: 'Cukup',
                color: '#f59e0b',
                hint: '💡 Tips: Campurkan variasi huruf besar dan angka agar semakin kuat.'
            };
        } else if (score <= 3) {
            return {
                percent: 75,
                label: 'Kuat ✅',
                color: '#0284c7',
                hint: '👍 Kombinasi huruf dan angka sudah bagus dan aman.'
            };
        } else {
            return {
                percent: 100,
                label: 'Sangat Kuat 🛡️',
                color: '#10b981',
                hint: '🔒 Sangat aman! Memiliki kombinasi huruf besar, kecil, angka, dan simbol lengkap.'
            };
        }
    }

    function handleCredentialInput(val) {
        val = (val || '').trim();
        const isNumeric = /^\d+$/.test(val);
        const len = val.length;
        const statusText = document.getElementById('pin-status-text');
        const feedback = document.getElementById('credential-feedback');
        const dotsContainer = document.getElementById('pin-dots-container');
        const meterContainer = document.getElementById('password-meter-container');
        const input = document.getElementById('credential');

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

        if (len === 0) {
            statusText.textContent = '(PIN 6-Digit Angka ATAU Password 8 Karakter)';
            statusText.style.color = '#10b981';
            if (dotsContainer) dotsContainer.style.display = 'flex';
            if (meterContainer) meterContainer.style.display = 'none';
            if (feedback) feedback.style.display = 'none';
            if (input) input.style.borderColor = 'var(--reg-input-border)';
            fieldStates.credential = false;
        } else if (isNumeric) {
            // Mode PIN (Angka murni): Wajib pas 6 digit & tidak boleh lemah/pasaran
            if (dotsContainer) dotsContainer.style.display = 'flex';
            if (meterContainer) meterContainer.style.display = 'none';
            if (len === 6) {
                if (isWeakPin(val)) {
                    statusText.textContent = `PIN Terlalu Lemah / Pasaran ⚠️`;
                    statusText.style.color = '#ef4444';
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.innerHTML = `⚠️ <span style="color: #ef4444; font-weight: 500;">PIN terlalu lemah / mudah ditebak (hindari angka berurutan atau berulang seperti 123456, 111111, dll). Gunakan 6 angka acak yang aman.</span>`;
                    }
                    if (input) input.style.borderColor = '#ef4444';
                    fieldStates.credential = false;
                } else {
                    statusText.textContent = `PIN 6-Digit Kuat ✅`;
                    statusText.style.color = '#10b981';
                    if (feedback) {
                        feedback.style.display = 'block';
                        feedback.innerHTML = `✅ <span style="color: #10b981; font-weight: 500;">PIN 6-digit angka aman, tersedia dan dapat digunakan.</span>`;
                    }
                    if (input) input.style.borderColor = '#10b981';
                    fieldStates.credential = true;
                }
            } else if (len < 6) {
                statusText.textContent = `PIN Terdeteksi (${len}/6 Digit Angka)`;
                statusText.style.color = '#f59e0b';
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `⚠️ <span style="color: #f59e0b;">PIN angka wajib 6 digit (kurang ${6 - len} digit lagi).</span>`;
                }
                if (input) input.style.borderColor = '#f59e0b';
                fieldStates.credential = false;
            } else {
                statusText.textContent = `PIN Melebihi Batas (${len}/6 Digit)`;
                statusText.style.color = '#ef4444';
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `⚠️ <span style="color: #ef4444;">PIN angka maksimal 6 digit. Jika ingin lebih dari 6 karakter, sertakan huruf (Password).</span>`;
                }
                if (input) input.style.borderColor = '#ef4444';
                fieldStates.credential = false;
            }
        } else {
            // Mode Password (Angka dan/atau Huruf alfanumerik): Wajib minimal 8 karakter
            if (dotsContainer) dotsContainer.style.display = 'none';

            if (meterContainer) {
                meterContainer.style.display = 'block';
                const strength = calculatePasswordStrength(val);
                const bar = document.getElementById('password-strength-bar');
                const label = document.getElementById('password-strength-label');
                const hint = document.getElementById('password-strength-hint');

                if (bar) {
                    bar.style.width = strength.percent + '%';
                    bar.style.background = strength.color;
                }
                if (label) {
                    label.textContent = strength.label;
                    label.style.color = strength.color;
                }
                if (hint) {
                    hint.textContent = strength.hint;
                }
            }

            if (len >= 8) {
                statusText.textContent = `Password ${len} Karakter Valid ✅`;
                statusText.style.color = '#10b981';
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `✅ <span style="color: #10b981; font-weight: 500;">Password (${len} karakter angka/huruf) valid, tersedia dan dapat digunakan.</span>`;
                }
                if (input) input.style.borderColor = '#10b981';
                fieldStates.credential = true;
            } else {
                statusText.textContent = `Password Terdeteksi (${len}/8 Karakter)`;
                statusText.style.color = '#f59e0b';
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.innerHTML = `⚠️ <span style="color: #f59e0b;">Password minimal 8 karakter angka ataupun huruf (kurang ${8 - len} karakter lagi).</span>`;
                }
                if (input) input.style.borderColor = '#f59e0b';
                fieldStates.credential = false;
            }
        }

        const confirmInput = document.getElementById('confirm_credential');
        if (confirmInput && confirmInput.value) {
            handleConfirmCredentialInput(confirmInput.value);
        } else {
            updateChecklistUI(false);
        }
    }

    function handleConfirmCredentialInput(val) {
        val = (val || '').trim();
        const credVal = (document.getElementById('credential')?.value || '').trim();
        const statusText = document.getElementById('confirm-status-text');
        const feedback = document.getElementById('confirm_credential-feedback');
        const input = document.getElementById('confirm_credential');

        if (val.length === 0) {
            if (statusText) {
                statusText.textContent = '(Harus sama persis)';
                statusText.style.color = 'var(--reg-text-body)';
            }
            if (feedback) feedback.style.display = 'none';
            if (input) input.style.borderColor = 'var(--reg-input-border)';
            fieldStates.confirmCredential = false;
        } else if (val === credVal && fieldStates.credential) {
            if (statusText) {
                statusText.textContent = 'Cocok ✅';
                statusText.style.color = '#10b981';
            }
            if (feedback) {
                feedback.style.display = 'block';
                feedback.innerHTML = `✅ <span style="color: #10b981; font-weight: 500;">Konfirmasi cocok dan sesuai dengan PIN / Password di atas.</span>`;
            }
            if (input) input.style.borderColor = '#10b981';
            fieldStates.confirmCredential = true;
        } else {
            if (statusText) {
                statusText.textContent = 'Belum cocok ❌';
                statusText.style.color = '#ef4444';
            }
            if (feedback) {
                feedback.style.display = 'block';
                feedback.innerHTML = `⚠️ <span style="color: #ef4444;">Konfirmasi belum cocok dengan PIN atau Password di atas.</span>`;
            }
            if (input) input.style.borderColor = '#ef4444';
            fieldStates.confirmCredential = false;
        }

        updateChecklistUI(false);
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

    function toggleConfirmVisibility() {
        const input = document.getElementById('confirm_credential');
        const icon = document.getElementById('eye-icon-confirm');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.textContent = '🙈';
        } else {
            input.type = 'password';
            if (icon) icon.textContent = '👁️';
        }
    }

    const fieldChecking = {
        username: false,
        phone: false,
        email: false
    };

    const fieldDescriptions = {
        fullName: 'Nama Lengkap (Sesuai KTP / Identitas)',
        username: 'Username Pilihan (Huruf/angka/titik/strip)',
        credential: 'PIN 6-Digit Angka ATAU Password minimal 8 Karakter',
        phone: 'Nomor HP / WhatsApp Aktif',
        email: 'Alamat Email Terdaftar'
    };

    function updateChecklistUI(showWarnings = false) {
        const btn = document.getElementById('btn-submit-reg');
        const btnText = document.getElementById('btn-text');
        const counter = document.getElementById('checklist-counter');
        const progress = document.getElementById('checklist-progress');
        const missingSummary = document.getElementById('validation-missing-summary');
        const missingList = document.getElementById('missing-fields-list');
        const successSummary = document.getElementById('validation-success-summary');

        const keys = ['fullName', 'username', 'credential', 'phone', 'email'];
        const mapKeys = {
            fullName: { item: 'chk-item-name', icon: 'chk-icon-name', status: 'chk-status-name', input: 'full_name', fb: 'full_name-feedback' },
            username: { item: 'chk-item-username', icon: 'chk-icon-username', status: 'chk-status-username', input: 'username', fb: 'username-feedback' },
            credential: { item: 'chk-item-credential', icon: 'chk-icon-credential', status: 'chk-status-credential', input: 'credential', fb: 'credential-feedback' },
            confirmCredential: { item: null, icon: null, status: null, input: 'confirm_credential', fb: 'confirm_credential-feedback' },
            phone: { item: 'chk-item-phone', icon: 'chk-icon-phone', status: 'chk-status-phone', input: 'phone', fb: 'phone-feedback' },
            email: { item: 'chk-item-email', icon: 'chk-icon-email', status: 'chk-status-email', input: 'email', fb: 'email-feedback' }
        };

        let validCount = 0;
        const missingItems = [];

        keys.forEach(k => {
            const dom = mapKeys[k];
            const itemEl = document.getElementById(dom.item);
            const iconEl = document.getElementById(dom.icon);
            const statusEl = document.getElementById(dom.status);
            const inputEl = document.getElementById(dom.input);
            const val = inputEl ? inputEl.value.trim() : '';

            if (fieldStates[k]) {
                validCount++;
                if (iconEl) iconEl.textContent = '✅';
                if (statusEl) {
                    statusEl.textContent = 'Tersedia dan dapat digunakan';
                    statusEl.style.color = '#10b981';
                    statusEl.style.fontWeight = '600';
                }
                if (itemEl) {
                    itemEl.style.background = 'rgba(16, 185, 129, 0.08)';
                    itemEl.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                }
            } else if (fieldChecking[k]) {
                if (iconEl) iconEl.textContent = '⏳';
                if (statusEl) {
                    statusEl.textContent = 'Memeriksa ketersediaan...';
                    statusEl.style.color = '#f59e0b';
                    statusEl.style.fontWeight = 'normal';
                }
                if (itemEl) {
                    itemEl.style.background = 'rgba(245, 158, 11, 0.08)';
                    itemEl.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                }
            } else if (val.length > 0) {
                // Ada isi tapi belum valid
                if (iconEl) iconEl.textContent = '❌';
                if (statusEl) {
                    statusEl.textContent = 'Tidak tersedia / belum valid';
                    statusEl.style.color = '#ef4444';
                    statusEl.style.fontWeight = '600';
                }
                if (itemEl) {
                    itemEl.style.background = 'rgba(239, 68, 68, 0.08)';
                    itemEl.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                }
                missingItems.push({ key: k, label: fieldDescriptions[k], reason: 'Data tidak valid atau sudah digunakan.' });
            } else {
                // Masih kosong
                if (iconEl) iconEl.textContent = '⚪';
                if (statusEl) {
                    statusEl.textContent = 'Belum diisi';
                    statusEl.style.color = 'var(--reg-text-body)';
                    statusEl.style.fontWeight = 'normal';
                }
                if (itemEl) {
                    itemEl.style.background = 'rgba(255, 255, 255, 0.02)';
                    itemEl.style.borderColor = 'transparent';
                }
                missingItems.push({ key: k, label: fieldDescriptions[k], reason: 'Kolom ini masih kosong dan wajib diisi.' });
            }
        });

        // Validasi konfirmasi PIN / Password
        const confirmInput = document.getElementById('confirm_credential');
        const confirmVal = confirmInput ? confirmInput.value.trim() : '';
        if (!fieldStates.confirmCredential) {
            if (confirmVal.length > 0) {
                missingItems.push({ key: 'confirmCredential', label: 'Ulangi PIN atau Password', reason: 'Konfirmasi belum cocok dengan PIN atau Password di atas.' });
            } else if (showWarnings) {
                missingItems.push({ key: 'confirmCredential', label: 'Ulangi PIN atau Password', reason: 'Kolom ulangi PIN / Password wajib diisi dan harus sama.' });
            }
        }

        // Update Counter & Progress Bar
        if (counter) {
            counter.textContent = `${validCount} / 5 Lengkap`;
            if (validCount === 5) {
                counter.style.background = 'rgba(16, 185, 129, 0.2)';
                counter.style.color = '#10b981';
            } else if (validCount >= 2) {
                counter.style.background = 'rgba(245, 158, 11, 0.2)';
                counter.style.color = '#f59e0b';
            } else {
                counter.style.background = 'rgba(239, 68, 68, 0.2)';
                counter.style.color = '#ef4444';
            }
        }

        if (progress) {
            progress.style.width = `${(validCount / 5) * 100}%`;
        }

        // Tampilkan notifikasi keseluruhan & satu persatu
        const allReady = (validCount === 5) && fieldStates.confirmCredential;

        if (allReady) {
            if (missingSummary) missingSummary.style.display = 'none';
            if (successSummary) successSummary.style.display = 'block';
            if (btn) {
                btn.style.opacity = '1';
                btn.style.boxShadow = '0 10px 28px -4px rgba(16, 185, 129, 0.6)';
            }
            if (btnText) btnText.textContent = 'Ajukan Pendaftaran / Daftar Ulang';
        } else {
            if (successSummary) successSummary.style.display = 'none';

            if (showWarnings && missingSummary && missingList) {
                missingList.innerHTML = '';
                missingItems.forEach((item, idx) => {
                    const li = document.createElement('li');
                    li.style.marginBottom = '4px';
                    li.innerHTML = `<strong>${item.label}</strong>: <span style="color: #fca5a5;">${item.reason}</span>`;
                    missingList.appendChild(li);

                    // Beri highlight merah dan notifikasi satu persatu di input field
                    const dom = mapKeys[item.key];
                    if (dom) {
                        const inputEl = document.getElementById(dom.input);
                        const fbEl = document.getElementById(dom.fb);
                        if (inputEl) {
                            inputEl.style.borderColor = '#ef4444';
                        }
                        if (fbEl && !fbEl.innerHTML.includes('Peringatan Dini')) {
                            fbEl.style.display = 'block';
                            fbEl.innerHTML = `❌ <span style="color: #ef4444; font-weight: 500;">${item.reason}</span>`;
                        }
                    }
                });
                missingSummary.style.display = 'block';
            }
        }
    }

    let debounceTimers = {};
    function debounce(func, delay, key) {
        return function(...args) {
            clearTimeout(debounceTimers[key]);
            debounceTimers[key] = setTimeout(() => func.apply(this, args), delay);
        };
    }

    async function checkFieldAvailability(field, value) {
        const spinner = document.getElementById(`${field}-spinner`);
        const feedback = document.getElementById(`${field}-feedback`);
        const input = document.getElementById(field);
        const suggestionsBox = document.getElementById('username-suggestions');

        fieldChecking[field] = true;
        updateChecklistUI(false);

        if (spinner) spinner.style.display = 'inline';
        if (suggestionsBox && field === 'username') {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
        }

        try {
            const res = await fetch(checkAvailabilityUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field, value })
            });
            const data = await res.json();

            fieldChecking[field] = false;
            if (spinner) spinner.style.display = 'none';
            feedback.style.display = 'block';

            if (data.available) {
                feedback.innerHTML = `✅ <span style="color: #10b981; font-weight: 500;">${data.message}</span>`;
                input.style.borderColor = '#10b981';
                fieldStates[field] = true;
            } else {
                let alertHtml = `<div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 8px; padding: 8px 10px; margin-top: 4px;">`;
                alertHtml += `<div style="color: #ef4444; font-weight: 600; margin-bottom: 2px;">⚠️ Peringatan Dini:</div>`;
                alertHtml += `<div style="color: var(--reg-text-heading); font-size: 11.5px; line-height: 1.4;">${data.message}</div>`;
                if (data.login_url) {
                    alertHtml += `<div style="margin-top: 6px;"><a href="${data.login_url}" style="display: inline-block; background: #0284c7; color: #ffffff; padding: 3px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600;">👉 Masuk ke Halaman Login</a></div>`;
                }
                alertHtml += `</div>`;
                feedback.innerHTML = alertHtml;
                input.style.borderColor = '#ef4444';
                fieldStates[field] = false;

                if (field === 'username' && data.suggestions && data.suggestions.length > 0) {
                    renderUsernameSuggestions(data.suggestions);
                }
            }
        } catch (e) {
            fieldChecking[field] = false;
            if (spinner) spinner.style.display = 'none';
            feedback.style.display = 'block';
            feedback.innerHTML = `<span style="color: #f59e0b;">Gagal memeriksa ketersediaan secara langsung. Sistem akan memvalidasi saat kirim.</span>`;
            fieldStates[field] = true; // fallback
        }

        updateChecklistUI(false);
    }

    function renderUsernameSuggestions(suggestions) {
        const box = document.getElementById('username-suggestions');
        if (!box) return;

        let html = `<div style="font-size: 11px; color: var(--reg-text-body); margin-bottom: 4px;">💡 Saran username yang tersedia:</div><div style="display: flex; gap: 6px; flex-wrap: wrap;">`;
        suggestions.forEach(item => {
            html += `<button type="button" onclick="applySuggestedUsername('${item}')" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #10b981; border-radius: 6px; padding: 2px 8px; font-size: 11px; font-weight: 600; cursor: pointer;">${item}</button>`;
        });
        html += `</div>`;
        box.innerHTML = html;
        box.style.display = 'block';
    }

    function applySuggestedUsername(val) {
        const input = document.getElementById('username');
        input.value = val;
        checkFieldAvailability('username', val);
    }

    document.addEventListener('DOMContentLoaded', () => {
        initPortalTheme();

        const form = document.getElementById('form-register');
        const fullNameInput = document.getElementById('full_name');
        const usernameInput = document.getElementById('username');
        const credentialInput = document.getElementById('credential');
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');

        const fullNameFb = document.getElementById('full_name-feedback');
        const credentialFb = document.getElementById('credential-feedback');

        // 1. Nama Lengkap
        fullNameInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (val.length >= 3) {
                fieldStates.fullName = true;
                this.style.borderColor = '#10b981';
                fullNameFb.style.display = 'block';
                fullNameFb.innerHTML = `✅ <span style="color: #10b981; font-weight: 500;">Nama lengkap tersedia dan dapat digunakan.</span>`;
            } else {
                fieldStates.fullName = false;
                this.style.borderColor = val.length > 0 ? '#ef4444' : 'var(--reg-input-border)';
                if (val.length > 0) {
                    fullNameFb.style.display = 'block';
                    fullNameFb.innerHTML = `⚠️ <span style="color: #ef4444;">Nama lengkap minimal 3 karakter.</span>`;
                } else {
                    fullNameFb.style.display = 'none';
                }
            }
            updateChecklistUI(false);
        });

        if (fullNameInput.value.trim().length >= 3) {
            fieldStates.fullName = true;
            fullNameFb.style.display = 'block';
            fullNameFb.innerHTML = `✅ <span style="color: #10b981; font-weight: 500;">Nama lengkap tersedia dan dapat digunakan.</span>`;
        }

        // 2. PIN / Password
        credentialInput.addEventListener('input', function() {
            handleCredentialInput(this.value);
        });

        // Konfirmasi PIN / Password
        const confirmInput = document.getElementById('confirm_credential');
        if (confirmInput) {
            confirmInput.addEventListener('input', function() {
                handleConfirmCredentialInput(this.value);
            });
        }

        // 3. Username
        usernameInput.addEventListener('input', debounce(function() {
            const val = this.value.trim();
            if (val.length < 3) {
                const fb = document.getElementById('username-feedback');
                fb.style.display = 'block';
                fb.innerHTML = `⚠️ <span style="color: #ef4444;">Username minimal 3 karakter.</span>`;
                this.style.borderColor = '#ef4444';
                fieldStates.username = false;
                updateChecklistUI(false);
                return;
            }
            checkFieldAvailability('username', val);
        }, 450, 'username'));

        // 4. Nomor HP / WhatsApp
        phoneInput.addEventListener('input', debounce(function() {
            const val = this.value.trim();
            const cleaned = val.replace(/[^0-9]/g, '');
            const isPersonPhone = /^(08|628|\+628)[0-9]{8,12}$/.test(val) || (cleaned.length >= 10 && cleaned.length <= 15 && ['08', '62'].includes(cleaned.substring(0, 2)));

            if (!isPersonPhone) {
                const fb = document.getElementById('phone-feedback');
                fb.style.display = 'block';
                fb.innerHTML = `⚠️ <span style="color: #ef4444;">Format nomor HP tidak valid. Masukkan nomor ponsel aktif orang (contoh: 081234567890).</span>`;
                this.style.borderColor = '#ef4444';
                fieldStates.phone = false;
                updateChecklistUI(false);
                return;
            }
            checkFieldAvailability('phone', val);
        }, 450, 'phone'));

        // 5. Alamat Email
        emailInput.addEventListener('input', debounce(function() {
            const val = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(val)) {
                const fb = document.getElementById('email-feedback');
                fb.style.display = 'block';
                fb.innerHTML = `⚠️ <span style="color: #ef4444;">Format email tidak valid.</span>`;
                this.style.borderColor = '#ef4444';
                fieldStates.email = false;
                updateChecklistUI(false);
                return;
            }
            checkFieldAvailability('email', val);
        }, 450, 'email'));

        // Submit Handler dengan Notifikasi Lengkap & Satu Persatu
        form.addEventListener('submit', function(e) {
            if (confirmInput) {
                handleConfirmCredentialInput(confirmInput.value);
            }

            const allValid = fieldStates.fullName && fieldStates.username && fieldStates.credential && fieldStates.confirmCredential && fieldStates.phone && fieldStates.email;

            if (!allValid) {
                e.preventDefault();
                updateChecklistUI(true);

                // Scroll ke checklist notifikasi agar terlihat jelas
                const checklistCard = document.getElementById('checklist-card');
                if (checklistCard) {
                    checklistCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // Fokus pada input pertama yang belum valid
                const inputsToCheck = [
                    { state: fieldStates.fullName, el: fullNameInput },
                    { state: fieldStates.username, el: usernameInput },
                    { state: fieldStates.credential, el: credentialInput },
                    { state: fieldStates.confirmCredential, el: confirmInput },
                    { state: fieldStates.phone, el: phoneInput },
                    { state: fieldStates.email, el: emailInput }
                ];

                const firstInvalid = inputsToCheck.find(item => !item.state);
                if (firstInvalid && firstInvalid.el) {
                    firstInvalid.el.focus();
                }

                return false;
            }

            const btn = document.getElementById('btn-submit-reg');
            const btnText = document.getElementById('btn-text');
            btnText.textContent = 'Mengirim Permohonan...';
            btn.style.opacity = '0.85';
            btn.style.pointerEvents = 'none';
        });

        // Trigger validasi awal jika old value ada
        if (usernameInput.value.trim().length >= 3) {
            checkFieldAvailability('username', usernameInput.value.trim());
        }
        if (phoneInput.value.trim().length >= 10) {
            checkFieldAvailability('phone', phoneInput.value.trim());
        }
        if (emailInput.value.trim().length >= 5) {
            checkFieldAvailability('email', emailInput.value.trim());
        }
        if (credentialInput.value.length > 0) {
            handleCredentialInput(credentialInput.value);
        }

        updateChecklistUI(false);
    });
</script>
@endpush
@endsection
