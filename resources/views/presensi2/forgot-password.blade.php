@extends('layouts.presensi2')

@section('title', 'Lupa Password / Reset PIN — MGK v2')

@section('content')
<style>
    :root {
        --fp-bg-card: rgba(15, 23, 42, 0.85);
        --fp-card-border: rgba(255, 255, 255, 0.1);
        --fp-text-heading: #ffffff;
        --fp-text-body: #94a3b8;
        --fp-input-bg: rgba(10, 15, 29, 0.75);
        --fp-input-border: rgba(255, 255, 255, 0.12);
        --fp-input-text: #ffffff;
        --fp-info-bg: rgba(15, 23, 42, 0.65);
        --fp-info-border: rgba(255, 255, 255, 0.08);
        --fp-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 30px rgba(56, 189, 248, 0.12);
    }

    body.light-theme {
        --fp-bg-card: rgba(255, 255, 255, 0.96);
        --fp-card-border: rgba(226, 232, 240, 0.9);
        --fp-text-heading: #0f172a;
        --fp-text-body: #64748b;
        --fp-input-bg: #f8fafc;
        --fp-input-border: #cbd5e1;
        --fp-input-text: #0f172a;
        --fp-info-bg: #f1f5f9;
        --fp-info-border: #e2e8f0;
        --fp-shadow: 0 20px 45px -10px rgba(2, 132, 199, 0.12), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
    }

    .fp-auth-container {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 16px auto 36px auto;
        perspective: 1000px;
    }

    .ambient-glow-amber {
        position: absolute;
        top: -30px;
        left: -30px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.3) 0%, transparent 70%);
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

    .fp-card {
        position: relative;
        z-index: 1;
        background: var(--fp-bg-card);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid var(--fp-card-border);
        border-radius: 26px;
        padding: 32px 28px;
        box-shadow: var(--fp-shadow);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .fp-card-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f59e0b, #06b6d4, #0284c7, #f59e0b);
        background-size: 300% 100%;
        animation: gradientSweep 8s linear infinite;
    }

    @keyframes gradientSweep {
        0% { background-position: 0% 50%; }
        100% { background-position: 300% 50%; }
    }

    .fp-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .fp-badge-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.3);
        color: #f59e0b;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    body.light-theme .fp-badge-tag {
        background: #fffbeb;
        border-color: #fde68a;
        color: #b45309;
    }

    .fp-brand-block {
        text-align: center;
        margin-bottom: 22px;
    }

    .fp-emblem {
        width: 68px;
        height: 68px;
        border-radius: 20px;
        background: linear-gradient(135deg, #f59e0b 0%, #0284c7 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 30px;
        box-shadow: 0 10px 25px -4px rgba(245, 158, 11, 0.45);
        margin-bottom: 12px;
    }

    .fp-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--fp-text-heading);
        letter-spacing: -0.025em;
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }

    .fp-subtitle {
        font-size: 13px;
        color: var(--fp-text-body);
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
        color: var(--fp-text-heading);
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
        color: var(--fp-text-body);
        font-size: 16px;
        pointer-events: none;
    }

    .form-control-custom {
        width: 100%;
        background: var(--fp-input-bg);
        border: 1.5px solid var(--fp-input-border);
        border-radius: 12px;
        padding: 12px 16px 12px 42px;
        color: var(--fp-input-text);
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .form-control-custom:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.22);
    }

    .password-toggle-btn {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: var(--fp-text-body);
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
        background: var(--fp-input-border);
        transition: all 0.2s ease;
    }

    .pin-dot.filled {
        background: #f59e0b;
        box-shadow: 0 0 8px #f59e0b;
        transform: scale(1.25);
    }

    .btn-fp-submit {
        width: 100%;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #0284c7 100%);
        color: #ffffff;
        border: none;
        border-radius: 14px;
        padding: 14px 24px;
        font-size: 15px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        cursor: pointer;
        box-shadow: 0 8px 24px -4px rgba(245, 158, 11, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 22px;
        transition: all 0.25s ease;
    }

    .btn-fp-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -4px rgba(245, 158, 11, 0.55);
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

<div class="fp-auth-container">
    <div class="ambient-glow-amber"></div>
    <div class="ambient-glow-blue"></div>

    <div class="fp-card">
        <div class="fp-card-accent"></div>

        <div class="fp-header-actions">
            <div class="fp-badge-tag">
                <span>🔑 Pemulihan Kata Sandi</span>
            </div>

            <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" onclick="togglePortalTheme()" style="background: rgba(255, 255, 255, 0.08); border: 1px solid var(--fp-card-border); color: var(--fp-text-body); font-size: 11px; font-weight: 600; padding: 5px 10px; border-radius: 999px; cursor: pointer;">
                <span id="theme-icon">☀️</span>
                <span id="theme-label">Mode Terang</span>
            </button>
        </div>

        <div class="fp-brand-block">
            <div class="fp-emblem">🔐</div>
            <h1 class="fp-title">Lupa Password / PIN</h1>
            <p class="fp-subtitle">Reset Kredensial Presensi &bull; Maya Graha Kencana</p>
        </div>

        @if($errors->any())
            <div style="background: rgba(239, 68, 68, 0.14); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 18px; text-align: left;">
                <span>⚠️</span> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('presensi2.forgot-password.post') }}" method="POST" id="form-forgot">
            @csrf

            <!-- Proteksi Honeypot Anti-Spam Bot (Tersembunyi bagi pengguna manusia, jebakan untuk bot otomatis) -->
            <div style="display: none !important; position: absolute !important; left: -9999px !important; top: -9999px !important; width: 0 !important; height: 0 !important; overflow: hidden !important;" aria-hidden="true">
                <label for="honeypot_website">Website / Profil Perusahaan (Kosongkan):</label>
                <input type="text" name="honeypot_website" id="honeypot_website" tabindex="-1" autocomplete="off" value="">
            </div>

            <!-- 1. Tulis Nama Karyawan Manual -->
            <div class="form-group-custom">
                <label for="employee_name" class="form-label-custom">
                    <span>1. Tulis Nama Karyawan Anda</span>
                    <span style="font-size: 11px; color: #f59e0b; font-weight: normal;">(Input Manual)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">👤</span>
                    <input 
                        type="text" 
                        name="employee_name" 
                        id="employee_name" 
                        class="form-control-custom" 
                        placeholder="Ketik nama lengkap Anda (cth: Bagus Satoto)..." 
                        value="{{ old('employee_name') }}"
                        required 
                        autofocus
                        autocomplete="name"
                    >
                </div>
                <div style="font-size: 11.5px; color: var(--fp-text-body); margin-top: 6px; display: flex; align-items: center; gap: 5px;">
                    <span>ℹ️</span>
                    <span>Tulis nama lengkap Anda secara manual sesuai data akun terdaftar.</span>
                </div>
            </div>

            <!-- 2. Password atau PIN Lama (Verifikasi Keamanan) -->
            <div class="form-group-custom">
                <label for="old_credential" class="form-label-custom">
                    <span>2. Password atau PIN 6-Digit Lama</span>
                    <span style="font-size: 11px; color: #ef4444; font-weight: normal;">(Verifikasi Keamanan Akun)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">🔒</span>
                    <input 
                        type="password" 
                        name="old_credential" 
                        id="old_credential" 
                        class="form-control-custom" 
                        placeholder="Masukkan Password atau PIN lama Anda saat ini" 
                        required 
                        minlength="4"
                        style="padding-right: 42px;"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="toggleVisibility('old_credential', 'eye-icon-0')" 
                        title="Tampilkan / Sembunyikan"
                    >
                        <span id="eye-icon-0">👁️</span>
                    </button>
                </div>
                <div style="font-size: 11.5px; color: var(--fp-text-body); margin-top: 6px; display: flex; align-items: center; gap: 5px;">
                    <span>🛡️</span>
                    <span>Wajib memasukkan PIN / Password lama agar akun Anda terlindungi dan tidak dibobol pihak lain.</span>
                </div>
            </div>

            <!-- 3. PIN atau Password Baru -->
            <div class="form-group-custom">
                <label for="new_credential" class="form-label-custom">
                    <span>3. Password atau PIN 6-Digit Baru</span>
                    <span style="font-size: 11px; color: #f59e0b; font-weight: normal;" id="pin-status-text">
                        (Minimal 4 karakter)
                    </span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">🔑</span>
                    <input 
                        type="password" 
                        name="new_credential" 
                        id="new_credential" 
                        class="form-control-custom" 
                        placeholder="Masukkan PIN baru (cth: 123456) atau Password" 
                        required 
                        minlength="4"
                        style="padding-right: 42px;"
                        oninput="handleCredentialInput(this.value)"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="toggleVisibility('new_credential', 'eye-icon-1')" 
                        title="Tampilkan / Sembunyikan"
                    >
                        <span id="eye-icon-1">👁️</span>
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

                <!-- Indikator Kekuatan Password Dinamis (Mode Password) -->
                <div id="password-meter-container" style="display: none; margin-top: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--fp-input-border); border-radius: 10px; padding: 10px 12px; text-align: left;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 11.5px;">
                        <span style="color: var(--fp-text-body);">Kekuatan Password:</span>
                        <span id="password-strength-label" style="font-weight: 700; color: #f59e0b;">Cukup</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 999px; overflow: hidden;">
                        <div id="password-strength-bar" style="width: 0%; height: 100%; border-radius: 999px; transition: all 0.3s ease; background: #ef4444;"></div>
                    </div>
                    <div id="password-strength-hint" style="font-size: 11px; color: var(--fp-text-body); margin-top: 5px; line-height: 1.35;"></div>
                </div>
            </div>

            <!-- 4. Konfirmasi PIN / Password Baru -->
            <div class="form-group-custom">
                <label for="confirm_credential" class="form-label-custom">
                    <span>4. Ulangi Password atau PIN Baru</span>
                    <span style="font-size: 11px; color: var(--fp-text-body); font-weight: normal;">(Harus sama)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">🛡️</span>
                    <input 
                        type="password" 
                        name="confirm_credential" 
                        id="confirm_credential" 
                        class="form-control-custom" 
                        placeholder="Ketik ulang PIN / Password baru Anda" 
                        required 
                        minlength="4"
                        style="padding-right: 42px;"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        onclick="toggleVisibility('confirm_credential', 'eye-icon-2')" 
                        title="Tampilkan / Sembunyikan"
                    >
                        <!-- <span id="eye-icon-2">👁️</span> -->
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-fp-submit" id="btn-submit-fp">
                <span>🛡️</span>
                <span id="btn-text">Ajukan Permohonan Reset ke Admin</span>
            </button>
            <div style="font-size: 11.5px; color: var(--fp-text-body); margin-top: 10px; text-align: center; line-height: 1.4;">
                ⏳ <em>Permohonan reset akan diperiksa dan disetujui oleh Supervisor / Admin Cabang demi mencegah pembajakan akun.</em>
            </div>
        </form>

        <div style="margin-top: 18px; text-align: center; display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('presensi2.check-reset-status') }}" style="color: #f59e0b; font-size: 12.5px; font-weight: 600; text-decoration: none;">
                🔍 Sudah mengajukan? <u>Cek Status Permohonan Reset di Sini</u> &rarr;
            </a>

            <a href="{{ route('presensi2.login') }}" class="back-to-login">
                <span>←</span>
                <span>Ingat Password Anda? Kembali ke Halaman Masuk</span>
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
        const isNumeric = /^\d+$/.test(val);
        const len = val.length;
        const statusText = document.getElementById('pin-status-text');
        const dotsContainer = document.getElementById('pin-dots-container');
        const meterContainer = document.getElementById('password-meter-container');

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
            statusText.textContent = '(Minimal 4 karakter)';
            statusText.style.color = '#f59e0b';
            if (dotsContainer) dotsContainer.style.display = 'flex';
            if (meterContainer) meterContainer.style.display = 'none';
        } else if (isNumeric) {
            if (dotsContainer) dotsContainer.style.display = 'flex';
            if (meterContainer) meterContainer.style.display = 'none';

            if (len === 6) {
                if (isWeakPin(val)) {
                    statusText.textContent = 'PIN Terlalu Lemah / Pasaran ⚠️';
                    statusText.style.color = '#ef4444';
                } else {
                    statusText.textContent = 'PIN 6-Digit Kuat ✅';
                    statusText.style.color = '#10b981';
                }
            } else {
                statusText.textContent = `PIN Terdeteksi (${len}/6 Digit)`;
                statusText.style.color = '#f59e0b';
            }
        } else {
            // Mode Password
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
            } else {
                statusText.textContent = `Password Terdeteksi (${len}/8 Karakter)`;
                statusText.style.color = '#f59e0b';
            }
        }
    }

    function toggleVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = '🙈';
        } else {
            input.type = 'password';
            icon.textContent = '👁️';
        }
    }

    document.getElementById('form-forgot').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit-fp');
        const btnText = document.getElementById('btn-text');
        btnText.textContent = 'Memperbarui Kata Sandi...';
        btn.style.opacity = '0.85';
        btn.style.pointerEvents = 'none';
    });

    document.addEventListener('DOMContentLoaded', initPortalTheme);
</script>
@endpush
@endsection
