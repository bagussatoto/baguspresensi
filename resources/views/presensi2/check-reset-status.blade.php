@extends('layouts.presensi2')

@section('title', 'Cek Status Permohonan Reset Password / PIN — MGK v2')

@section('content')
<style>
    :root {
        --crs-bg-card: rgba(15, 23, 42, 0.85);
        --crs-card-border: rgba(255, 255, 255, 0.1);
        --crs-text-heading: #ffffff;
        --crs-text-body: #94a3b8;
        --crs-input-bg: rgba(10, 15, 29, 0.75);
        --crs-input-border: rgba(255, 255, 255, 0.12);
        --crs-input-text: #ffffff;
        --crs-info-bg: rgba(15, 23, 42, 0.65);
        --crs-info-border: rgba(255, 255, 255, 0.08);
        --crs-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 30px rgba(245, 158, 11, 0.12);
    }

    body.light-theme {
        --crs-bg-card: rgba(255, 255, 255, 0.96);
        --crs-card-border: rgba(226, 232, 240, 0.9);
        --crs-text-heading: #0f172a;
        --crs-text-body: #64748b;
        --crs-input-bg: #f8fafc;
        --crs-input-border: #cbd5e1;
        --crs-input-text: #0f172a;
        --crs-info-bg: #f1f5f9;
        --crs-info-border: #e2e8f0;
        --crs-shadow: 0 20px 45px -10px rgba(245, 158, 11, 0.12), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
    }

    .check-reset-container {
        width: 100%;
        max-width: 490px;
        margin: 0 auto;
        padding: 20px 14px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .crs-card {
        background: var(--crs-bg-card);
        border: 1px solid var(--crs-card-border);
        border-radius: 24px;
        padding: 32px 28px;
        box-shadow: var(--crs-shadow);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        position: relative;
        overflow: hidden;
    }

    .crs-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .crs-emblem {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #0284c7 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        margin-bottom: 12px;
        box-shadow: 0 10px 25px -4px rgba(245, 158, 11, 0.4);
    }

    .crs-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--crs-text-heading);
        margin-bottom: 4px;
        font-family: 'Outfit', sans-serif;
    }

    .crs-subtitle {
        font-size: 13px;
        color: var(--crs-text-body);
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
        color: var(--crs-text-body);
        font-size: 16px;
        pointer-events: none;
    }

    .form-control-custom {
        width: 100%;
        background: var(--crs-input-bg);
        border: 1.5px solid var(--crs-input-border);
        border-radius: 12px;
        padding: 12px 16px 12px 42px;
        color: var(--crs-input-text);
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .form-control-custom:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.22);
    }

    .btn-crs-submit {
        width: 100%;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #0284c7 100%);
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
        box-shadow: 0 10px 25px -4px rgba(245, 158, 11, 0.45);
        transition: all 0.2s ease;
    }

    .btn-crs-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px -4px rgba(245, 158, 11, 0.55);
    }

    .theme-toggle-floating {
        position: absolute;
        top: 18px;
        right: 18px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--crs-card-border);
        border-radius: 20px;
        padding: 4px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 12px;
        color: var(--crs-text-body);
        transition: all 0.2s ease;
    }

    .back-nav-link {
        color: var(--crs-text-body);
        text-decoration: none;
        font-size: 12.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s;
    }

    .back-nav-link:hover {
        color: #f59e0b;
    }
</style>

<div class="check-reset-container">
    <div class="crs-card">
        <button type="button" class="theme-toggle-floating" onclick="togglePortalTheme()" id="theme-toggle-btn" title="Ganti Tema">
            <span id="theme-icon">🌙</span>
            <span id="theme-label" style="font-size: 11px; font-weight: 600;">Mode Gelap</span>
        </button>

        <div class="crs-header">
            <div class="crs-emblem">🔑</div>
            <h1 class="crs-title">Cek Status Reset</h1>
            <p class="crs-subtitle">Ketahui status persetujuan Admin atas permohonan reset kredensial Anda</p>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.12); border: 1.5px solid rgba(239, 68, 68, 0.35); border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; font-size: 12.5px; color: #fca5a5; text-align: left;">
                <span>⚠️</span> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('presensi2.check-reset-status.query') }}" method="POST" id="form-check-reset">
            @csrf
            <div class="form-group-custom">
                <label for="keyword" style="display: block; font-size: 13px; font-weight: 600; color: var(--crs-text-heading); margin-bottom: 7px;">
                    Nama Karyawan, NIP, atau Nomor WhatsApp:
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">👤</span>
                    <input 
                        type="text" 
                        name="keyword" 
                        id="keyword" 
                        class="form-control-custom" 
                        placeholder="Contoh: Bagus Satoto atau 081234567890" 
                        value="{{ old('keyword', $keyword ?? '') }}"
                        required 
                        autofocus
                        autocomplete="off"
                    >
                </div>
                <div style="font-size: 11.5px; color: var(--crs-text-body); margin-top: 6px;">
                    💡 Masukkan nama lengkap sesuai Master Data atau nomor WhatsApp yang diajukan.
                </div>
            </div>

            <button type="submit" class="btn-crs-submit" id="btn-submit-crs">
                <span>Periksa Status Reset</span>
                <span>→</span>
            </button>
        </form>

        @if(isset($result))
            <div id="result-box" style="margin-top: 22px; text-align: left; background: var(--crs-info-bg); border: 1.5px solid {{ $result['badge_color'] }}; border-radius: 16px; padding: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--crs-text-body); letter-spacing: 0.5px;">Status Permohonan:</span>
                    <span style="background: {{ $result['badge_color'] }}22; color: {{ $result['badge_color'] }}; border: 1px solid {{ $result['badge_color'] }}; border-radius: 999px; padding: 4px 12px; font-size: 12px; font-weight: 700;">
                        {{ $result['badge'] }}
                    </span>
                </div>

                @if(!empty($result['employee_name']))
                    <div style="font-size: 15px; font-weight: 700; color: var(--crs-text-heading); margin-bottom: 2px;">
                        {{ $result['employee_name'] }}
                    </div>
                    <div style="font-size: 12px; color: var(--crs-text-body); margin-bottom: 12px;">
                        Kode/NIP: <code style="color: #38bdf8; font-weight: 600;">{{ $result['employee_code'] }}</code> &bull; Cabang: <span style="color: #a78bfa;">{{ $result['branch_name'] }}</span>
                    </div>
                @endif

                <div style="font-size: 12.5px; color: var(--crs-text-heading); line-height: 1.5; margin-bottom: 14px;">
                    {{ $result['message'] }}
                </div>

                <!-- Info Waktu & Estimasi -->
                <div style="display: grid; grid-template-columns: 1fr; gap: 8px; margin-bottom: 14px;">
                    @if(!empty($result['submitted_at']))
                        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 8px 12px; font-size: 11.5px; color: var(--crs-text-body);">
                            🕒 Waktu Pengajuan: <strong style="color: var(--crs-text-heading);">{{ $result['submitted_at'] }}</strong>
                        </div>
                    @endif

                    @if(!empty($result['approved_at']))
                        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 8px 12px; font-size: 11.5px; color: #34d399;">
                            ✅ Disetujui Pada: <strong>{{ $result['approved_at'] }}</strong> (Oleh: {{ $result['approver_name'] ?? 'Admin' }})
                        </div>
                    @endif

                    @if(!empty($result['rejection_reason']))
                        <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 10px 12px; color: #fca5a5; font-size: 12px; line-height: 1.4;">
                            <strong>Alasan Penolakan Admin:</strong><br>
                            {{ $result['rejection_reason'] }}
                        </div>
                    @endif
                </div>

                <div style="margin-top: 14px;">
                    <a href="{{ $result['action_url'] }}" style="display: block; text-align: center; background: {{ $result['badge_color'] }}; color: #ffffff; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700;">
                        {{ $result['action_label'] }}
                    </a>
                </div>
            </div>
        @endif

        <div style="margin-top: 22px; display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid var(--crs-card-border);">
            <a href="{{ route('presensi2.login') }}" class="back-nav-link">
                <span>←</span>
                <span>Halaman Masuk (Login)</span>
            </a>
            <!-- <a href="{{ route('presensi2.forgot-password') }}" class="back-nav-link" style="color: #f59e0b;">
                <span>Ajukan Reset</span>
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
