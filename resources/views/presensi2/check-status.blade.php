@extends('layouts.presensi2')

@section('title', 'Cek Status Pengajuan Akun — MGK v2')

@section('content')
<style>
    :root {
        --cs-bg-card: rgba(15, 23, 42, 0.85);
        --cs-card-border: rgba(255, 255, 255, 0.1);
        --cs-text-heading: #ffffff;
        --cs-text-body: #94a3b8;
        --cs-input-bg: rgba(10, 15, 29, 0.75);
        --cs-input-border: rgba(255, 255, 255, 0.12);
        --cs-input-text: #ffffff;
        --cs-info-bg: rgba(15, 23, 42, 0.65);
        --cs-info-border: rgba(255, 255, 255, 0.08);
        --cs-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 30px rgba(56, 189, 248, 0.12);
    }

    body.light-theme {
        --cs-bg-card: rgba(255, 255, 255, 0.96);
        --cs-card-border: rgba(226, 232, 240, 0.9);
        --cs-text-heading: #0f172a;
        --cs-text-body: #64748b;
        --cs-input-bg: #f8fafc;
        --cs-input-border: #cbd5e1;
        --cs-input-text: #0f172a;
        --cs-info-bg: #f1f5f9;
        --cs-info-border: #e2e8f0;
        --cs-shadow: 0 20px 45px -10px rgba(2, 132, 199, 0.12), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
    }

    .check-status-container {
        width: 100%;
        max-width: 490px;
        margin: 0 auto;
        padding: 20px 14px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .cs-card {
        background: var(--cs-bg-card);
        border: 1px solid var(--cs-card-border);
        border-radius: 24px;
        padding: 32px 28px;
        box-shadow: var(--cs-shadow);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        position: relative;
        overflow: hidden;
    }

    .cs-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .cs-emblem {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: linear-gradient(135deg, #0284c7 0%, #06b6d4 50%, #10b981 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        margin-bottom: 12px;
        box-shadow: 0 10px 25px -4px rgba(2, 132, 199, 0.4);
    }

    .cs-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--cs-text-heading);
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }

    .cs-subtitle {
        font-size: 13px;
        color: var(--cs-text-body);
        font-weight: 500;
    }

    .form-group-custom {
        margin-bottom: 16px;
        text-align: left;
    }

    .input-field-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-leading-icon {
        position: absolute;
        left: 14px;
        color: var(--cs-text-body);
        font-size: 16px;
        pointer-events: none;
    }

    .form-control-custom {
        width: 100%;
        background: var(--cs-input-bg);
        border: 1.5px solid var(--cs-input-border);
        border-radius: 12px;
        padding: 12px 16px 12px 42px;
        color: var(--cs-input-text);
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .form-control-custom:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.22);
    }

    .btn-cs-submit {
        width: 100%;
        background: linear-gradient(135deg, #0284c7 0%, #06b6d4 100%);
        color: #ffffff;
        border: none;
        border-radius: 14px;
        padding: 13px 20px;
        font-size: 14.5px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 10px 25px -4px rgba(2, 132, 199, 0.45);
        transition: all 0.2s ease;
    }

    .btn-cs-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px -4px rgba(2, 132, 199, 0.55);
    }

    .theme-toggle-floating {
        position: absolute;
        top: 18px;
        right: 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--cs-card-border);
        border-radius: 20px;
        padding: 4px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 12px;
        color: var(--cs-text-body);
        transition: all 0.2s ease;
    }

    .back-nav-link {
        color: var(--cs-text-body);
        text-decoration: none;
        font-size: 12.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }

    .back-nav-link:hover {
        color: #38bdf8;
    }
</style>

<div class="check-status-container">
    <div class="cs-card">
        <button type="button" class="theme-toggle-floating" onclick="togglePortalTheme()" id="theme-toggle-btn" title="Ganti Tema">
            <span id="theme-icon">🌙</span>
            <span id="theme-label" style="font-size: 11px; font-weight: 600;">Mode Gelap</span>
        </button>

        <div class="cs-header">
            <div class="cs-emblem">🔍</div>
            <h1 class="cs-title">Cek Status Pengajuan</h1>
            <p class="cs-subtitle">Ketahui status verifikasi akun pendaftaran mandiri Anda</p>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.12); border: 1.5px solid rgba(239, 68, 68, 0.35); border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; font-size: 12.5px; color: #fca5a5; text-align: left;">
                <span>⚠️</span> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('presensi2.check-status.query') }}" method="POST" id="form-check-status">
            @csrf
            <div class="form-group-custom">
                <label for="keyword" style="display: block; font-size: 13px; font-weight: 600; color: var(--cs-text-heading); margin-bottom: 7px;">
                    Username atau Nomor HP / WhatsApp:
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">👤</span>
                    <input 
                        type="text" 
                        name="keyword" 
                        id="keyword" 
                        class="form-control-custom" 
                        placeholder="Contoh: budi_santoso atau 081234567890" 
                        value="{{ old('keyword', $keyword ?? '') }}"
                        required 
                        autofocus
                        autocomplete="off"
                    >
                </div>
                <div style="font-size: 11.5px; color: var(--cs-text-body); margin-top: 6px;">
                    💡 Masukkan username yang didaftarkan atau nomor ponsel WhatsApp aktif Anda.
                </div>
            </div>

            <button type="submit" class="btn-cs-submit" id="btn-submit-cs">
                <span>Periksa Status Akun</span>
                <span>→</span>
            </button>
        </form>

        @if(isset($result))
            <div id="result-box" style="margin-top: 22px; text-align: left; background: var(--cs-info-bg); border: 1.5px solid {{ $result['badge_color'] }}; border-radius: 16px; padding: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--cs-text-body); letter-spacing: 0.5px;">Status Pengajuan:</span>
                    <span style="background: {{ $result['badge_color'] }}22; color: {{ $result['badge_color'] }}; border: 1px solid {{ $result['badge_color'] }}; border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 700;">
                        {{ $result['badge'] }}
                    </span>
                </div>

                @if(!empty($result['full_name']))
                    <div style="font-size: 14.5px; font-weight: 700; color: var(--cs-text-heading); margin-bottom: 2px;">
                        {{ $result['full_name'] }}
                    </div>
                    <div style="font-size: 12px; color: var(--cs-text-body); margin-bottom: 10px;">
                        Username: <code style="color: #38bdf8; font-weight: 600;">{{ $result['username'] }}</code>
                    </div>
                @endif

                <div style="font-size: 12.5px; color: var(--cs-text-heading); line-height: 1.5; margin-bottom: 12px;">
                    {{ $result['message'] }}
                </div>

                @if(!empty($result['estimated_time']))
                    <div style="background: rgba(2, 132, 199, 0.12); border: 1px solid rgba(2, 132, 199, 0.3); border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">⏱️</span>
                        <div>
                            <div style="font-size: 10.5px; color: #38bdf8; font-weight: 700; text-transform: uppercase;">Estimasi Verifikasi</div>
                            <div style="font-size: 12.5px; font-weight: 600; color: var(--cs-text-heading);">{{ $result['estimated_time'] }}</div>
                        </div>
                    </div>
                @endif

                @if(!empty($result['rejection_reason']))
                    <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; color: #fca5a5; font-size: 12px; line-height: 1.4;">
                        <strong>Catatan Admin/HR:</strong> {{ $result['rejection_reason'] }}
                    </div>
                @endif

                <div style="margin-top: 14px;">
                    <a href="{{ $result['action_url'] }}" style="display: block; text-align: center; background: {{ $result['badge_color'] }}; color: #ffffff; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700;">
                        {{ $result['action_label'] }}
                    </a>
                </div>
            </div>
        @endif

        <div style="margin-top: 22px; display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid var(--cs-card-border);">
            <a href="{{ route('presensi2.login') }}" class="back-nav-link">
                <span>←</span>
                <span>Halaman Masuk (Login)</span>
            </a>
            <!-- <a href="{{ route('presensi2.register') }}" class="back-nav-link" style="color: #10b981;">
                <span>Daftar Akun Baru</span>
                <span>→</span>
            </a> -->
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
        if (icon && label) {
            if (isLight) {
                icon.textContent = '🌙';
                label.textContent = 'Mode Gelap';
            } else {
                icon.textContent = '☀️';
                label.textContent = 'Mode Terang';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initPortalTheme);
</script>
@endpush
@endsection
