@extends('layouts.presensi2')

@section('title', 'Portal Login - Presensi & Admin MGK')

@section('content')
<style>
    /* THEME VARIABLES & DYNAMIC PALETTES */
    :root {
        --login-bg-card: rgba(15, 23, 42, 0.82);
        --login-card-border: rgba(255, 255, 255, 0.1);
        --login-card-border-glow: rgba(6, 182, 212, 0.3);
        --login-text-heading: #ffffff;
        --login-text-body: #94a3b8;
        --login-input-bg: rgba(10, 15, 29, 0.75);
        --login-input-border: rgba(255, 255, 255, 0.12);
        --login-input-text: #ffffff;
        --login-info-bg: rgba(15, 23, 42, 0.65);
        --login-info-border: rgba(255, 255, 255, 0.08);
        --login-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 30px rgba(6, 182, 212, 0.12);
    }

    body.light-theme {
        --login-bg-card: rgba(255, 255, 255, 0.95);
        --login-card-border: rgba(226, 232, 240, 0.9);
        --login-card-border-glow: rgba(2, 132, 199, 0.25);
        --login-text-heading: #0f172a;
        --login-text-body: #64748b;
        --login-input-bg: #f8fafc;
        --login-input-border: #cbd5e1;
        --login-input-text: #0f172a;
        --login-info-bg: #f1f5f9;
        --login-info-border: #e2e8f0;
        --login-shadow: 0 20px 45px -10px rgba(2, 132, 199, 0.12), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
    }

    body.light-theme {
        background-color: #f0f6fc !important;
        background-image: 
            radial-gradient(at 10% 10%, rgba(2, 132, 199, 0.12) 0px, transparent 50%),
            radial-gradient(at 90% 90%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
            radial-gradient(at 50% 50%, #f8fafc 0px, transparent 100%) !important;
        color: #0f172a !important;
    }

    /* WRAPPER & AMBIENT GLOWS */
    .portal-auth-container {
        position: relative;
        width: 100%;
        max-width: 460px;
        margin: 20px auto 40px auto;
        perspective: 1000px;
    }

    .ambient-glow-1 {
        position: absolute;
        top: -30px;
        left: -30px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.35) 0%, transparent 70%);
        filter: blur(40px);
        pointer-events: none;
        animation: floatGlow 6s ease-in-out infinite alternate;
        z-index: 0;
    }

    .ambient-glow-2 {
        position: absolute;
        bottom: -20px;
        right: -30px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
        filter: blur(45px);
        pointer-events: none;
        animation: floatGlow 8s ease-in-out infinite alternate-reverse;
        z-index: 0;
    }

    @keyframes floatGlow {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(15px, 20px) scale(1.15); }
    }

    /* THE CARD */
    .portal-card {
        position: relative;
        z-index: 1;
        background: var(--login-bg-card);
        backdrop-filter: blur(28px);
        -webkit-backdrop-filter: blur(28px);
        border: 1px solid var(--login-card-border);
        border-radius: 26px;
        padding: 34px 28px 28px 28px;
        box-shadow: var(--login-shadow);
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, background 0.3s ease;
    }

    /* TOP ACCENT LINE (with subtle cyber glow) */
    .portal-card-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0284c7, #06b6d4, #38bdf8, #6366f1, #0284c7);
        background-size: 300% 100%;
        animation: gradientSweep 8s linear infinite;
    }

    @keyframes gradientSweep {
        0% { background-position: 0% 50%; }
        100% { background-position: 300% 50%; }
    }

    /* CARD HEADER & THEME TOGGLE */
    .portal-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .portal-version-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(6, 182, 212, 0.12);
        border: 1px solid rgba(6, 182, 212, 0.3);
        color: #06b6d4;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 999px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    body.light-theme .portal-version-tag {
        background: rgba(2, 132, 199, 0.1);
        border-color: rgba(2, 132, 199, 0.25);
        color: #0284c7;
    }

    .portal-version-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #06b6d4;
        box-shadow: 0 0 8px #06b6d4;
        animation: pulseDot 2s infinite;
    }

    @keyframes pulseDot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }

    .theme-toggle-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid var(--login-card-border);
        color: var(--login-text-body);
        font-size: 11px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }

    body.light-theme .theme-toggle-btn {
        background: rgba(0, 0, 0, 0.04);
        border-color: #cbd5e1;
    }

    .theme-toggle-btn:hover {
        transform: translateY(-1px);
        color: var(--login-text-heading);
        border-color: #06b6d4;
    }

    /* CENTER EMBLEM & BIOMETRIC SCANNER */
    .portal-brand-block {
        text-align: center;
        margin-bottom: 24px;
    }

    .biometric-emblem-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 14px;
    }

    .biometric-emblem {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        background: linear-gradient(135deg, #0284c7 0%, #06b6d4 50%, #4f46e5 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        box-shadow: 0 10px 25px -4px rgba(2, 132, 199, 0.5), 0 0 35px rgba(6, 182, 212, 0.3);
        position: relative;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
    }

    .biometric-emblem-wrap:hover .biometric-emblem {
        transform: scale(1.05) rotate(1deg);
        box-shadow: 0 14px 30px -4px rgba(2, 132, 199, 0.6), 0 0 45px rgba(6, 182, 212, 0.45);
    }

    /* Dynamic Laser Scanline across fingerprint */
    .biometric-scanline {
        position: absolute;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #38bdf8, #ffffff, #38bdf8, transparent);
        box-shadow: 0 0 10px #38bdf8, 0 0 20px #06b6d4;
        animation: scanlineAnim 2.8s ease-in-out infinite alternate;
        pointer-events: none;
    }

    @keyframes scanlineAnim {
        0% { top: 8%; opacity: 0.2; }
        15% { opacity: 0.9; }
        85% { opacity: 0.9; }
        100% { top: 88%; opacity: 0.2; }
    }

    .biometric-svg {
        width: 38px;
        height: 38px;
        fill: none;
        stroke: #ffffff;
        stroke-width: 2.2;
        stroke-linecap: round;
        stroke-linejoin: round;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));
    }

    .portal-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--login-text-heading);
        letter-spacing: -0.025em;
        margin-bottom: 5px;
        font-family: 'Outfit', sans-serif;
    }

    .portal-subtitle {
        font-size: 13px;
        color: var(--login-text-body);
        font-weight: 500;
    }

    /* SEGMENTED MODE TABS */
    .mode-tab-group {
        display: flex;
        background: var(--login-input-bg);
        border: 1px solid var(--login-input-border);
        border-radius: 14px;
        padding: 4px;
        margin-bottom: 22px;
        position: relative;
    }

    .mode-tab-btn {
        flex: 1;
        background: transparent;
        border: none;
        padding: 9px 8px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--login-text-body);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-family: inherit;
    }

    .mode-tab-btn.active {
        background: linear-gradient(135deg, #0284c7, #06b6d4);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
    }

    body.light-theme .mode-tab-btn.active {
        background: #0284c7;
        color: #ffffff;
    }

    /* FORM GROUPS & INPUTS */
    .form-group-custom {
        margin-bottom: 18px;
        text-align: left;
    }

    .form-label-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        font-weight: 600;
        color: var(--login-text-heading);
        margin-bottom: 8px;
    }

    .live-role-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        transition: all 0.25s ease;
        display: none;
    }

    .live-role-badge.admin-detected {
        display: inline-flex;
        background: rgba(2, 132, 199, 0.18);
        border: 1px solid rgba(2, 132, 199, 0.4);
        color: #38bdf8;
    }

    .live-role-badge.karyawan-detected {
        display: inline-flex;
        background: rgba(16, 185, 129, 0.18);
        border: 1px solid rgba(16, 185, 129, 0.4);
        color: #34d399;
    }

    .input-field-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-leading-icon {
        position: absolute;
        left: 14px;
        color: var(--login-text-body);
        font-size: 17px;
        pointer-events: none;
        transition: color 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-control-custom {
        width: 100%;
        background: var(--login-input-bg);
        border: 1.5px solid var(--login-input-border);
        border-radius: 12px;
        padding: 13px 44px 13px 44px;
        color: var(--login-input-text);
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
        opacity: 0.75;
    }

    .form-control-custom:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.22), inset 0 2px 4px rgba(0, 0, 0, 0.04);
        background: var(--login-input-bg);
    }

    .input-field-wrap:focus-within .input-leading-icon {
        color: #0284c7;
    }

    /* PASSWORD TOGGLE BUTTON */
    .password-toggle-btn {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: var(--login-text-body);
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: color 0.2s, background 0.2s;
    }

    .password-toggle-btn:hover {
        color: var(--login-text-heading);
        background: rgba(255, 255, 255, 0.08);
    }

    /* 6-DIGIT PIN VISUAL DOTS */
    .pin-dots-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 8px;
        margin-bottom: 4px;
    }

    .pin-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--login-input-border);
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .pin-dot.filled {
        background: #06b6d4;
        box-shadow: 0 0 8px #06b6d4;
        transform: scale(1.25);
    }

    /* REMEMBER ME & HELP ROW */
    .options-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 14px;
        margin-bottom: 22px;
        font-size: 13px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 9px;
        cursor: pointer;
        color: var(--login-text-body);
        user-select: none;
        transition: color 0.2s;
    }

    .checkbox-label:hover {
        color: var(--login-text-heading);
    }

    .checkbox-input-custom {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 1.5px solid var(--login-input-border);
        border-radius: 6px;
        background: var(--login-input-bg);
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        display: inline-grid;
        place-content: center;
    }

    .checkbox-input-custom:checked {
        background: #0284c7;
        border-color: #0284c7;
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
    }

    .checkbox-input-custom:checked::before {
        content: "";
        width: 9px;
        height: 5px;
        border-left: 2px solid #ffffff;
        border-bottom: 2px solid #ffffff;
        transform: rotate(-45deg) translate(1px, -1px);
    }

    .forgot-link {
        color: #0284c7;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
        transition: color 0.2s;
    }

    .forgot-link:hover {
        color: #38bdf8;
        text-decoration: underline;
    }

    /* PRIMARY SUBMIT BUTTON */
    .btn-portal-submit {
        width: 100%;
        background: linear-gradient(135deg, #0284c7 0%, #2563eb 50%, #06b6d4 100%);
        background-size: 200% auto;
        color: #ffffff;
        border: none;
        border-radius: 14px;
        padding: 15px 24px;
        font-size: 15px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        letter-spacing: 0.01em;
        cursor: pointer;
        box-shadow: 0 8px 24px -4px rgba(2, 132, 199, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-portal-submit:hover {
        background-position: right center;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px -4px rgba(2, 132, 199, 0.55), 0 0 20px rgba(6, 182, 212, 0.3);
    }

    .btn-portal-submit:active {
        transform: translateY(0);
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
    }

    /* Shiny light bar across button */
    .btn-portal-submit::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 40%;
        height: 200%;
        background: linear-gradient(
            60deg, 
            rgba(255, 255, 255, 0) 0%, 
            rgba(255, 255, 255, 0.25) 50%, 
            rgba(255, 255, 255, 0) 100%
        );
        transform: rotate(25deg);
        transition: all 0.75s ease;
    }

    .btn-portal-submit:hover::after {
        left: 120%;
    }

    /* PETUNJUK AKSES AKUN CARD */
    .portal-info-box {
        margin-top: 24px;
        background: var(--login-info-bg);
        border: 1px solid var(--login-info-border);
        border-radius: 16px;
        padding: 16px;
        text-align: left;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .portal-info-title {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--login-text-body);
        margin-bottom: 12px;
        border-bottom: 1px dashed var(--login-input-border);
        padding-bottom: 8px;
    }

    .portal-info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 12px;
        line-height: 1.5;
        color: var(--login-text-body);
    }

    .portal-info-row:last-child {
        margin-bottom: 0;
    }

    .role-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .role-pill-admin {
        background: rgba(2, 132, 199, 0.18);
        border: 1px solid rgba(2, 132, 199, 0.35);
        color: #38bdf8;
    }

    body.light-theme .role-pill-admin {
        background: #e0f2fe;
        border-color: #bae6fd;
        color: #0369a1;
    }

    .role-pill-karyawan {
        background: rgba(148, 163, 184, 0.16);
        border: 1px solid rgba(148, 163, 184, 0.3);
        color: #cbd5e1;
    }

    body.light-theme .role-pill-karyawan {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #475569;
    }

    /* QUICK TEST DEMO CHIPS */
    .quick-test-section {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed var(--login-input-border);
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .quick-test-label {
        font-size: 11px;
        color: var(--login-text-body);
        font-weight: 600;
    }

    .demo-chip-btn {
        background: rgba(2, 132, 199, 0.1);
        border: 1px solid rgba(2, 132, 199, 0.25);
        color: #38bdf8;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 9px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    body.light-theme .demo-chip-btn {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
    }

    .demo-chip-btn:hover {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        transform: translateY(-1px);
    }

    /* BACK LINK */
    .footer-links-wrap {
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        font-size: 12px;
    }

    .portal-back-link {
        color: var(--login-text-body);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color 0.2s;
    }

    .portal-back-link:hover {
        color: #0284c7;
    }

    /* ALERT STYLING */
    .auth-alert-box {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: shakeAlert 0.4s ease-in-out;
    }

    .auth-alert-error {
        background: rgba(239, 68, 68, 0.14);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #fca5a5;
    }

    body.light-theme .auth-alert-error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .auth-alert-success {
        background: rgba(16, 185, 129, 0.14);
        border: 1px solid rgba(16, 185, 129, 0.35);
        color: #6ee7b7;
    }

    body.light-theme .auth-alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
    }

    @keyframes shakeAlert {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-4px); }
        40%, 80% { transform: translateX(4px); }
    }

    /* QUICK ACTION BUTTONS: GC, MEETING, BO/TRAINING, AMBIL STOK */
    .quick-services-divider {
        display: flex;
        align-items: center;
        margin: 24px 0 16px 0;
        gap: 12px;
    }
    .quick-services-divider .line {
        flex: 1;
        height: 1px;
        background: var(--login-card-border);
    }
    .quick-services-divider .label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--login-text-body);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .quick-services-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    .service-quick-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 12px;
        border-radius: 14px;
        background: var(--login-input-bg);
        border: 1px solid var(--login-input-border);
        text-decoration: none;
        color: var(--login-text-heading);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .service-quick-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px -6px rgba(0, 0, 0, 0.4);
    }
    .service-quick-card.gc-card:hover {
        border-color: rgba(245, 158, 11, 0.5);
        background: rgba(245, 158, 11, 0.08);
    }
    .service-quick-card.meeting-card:hover {
        border-color: rgba(14, 165, 233, 0.5);
        background: rgba(14, 165, 233, 0.08);
    }
    .service-quick-card.training-card:hover {
        border-color: rgba(16, 185, 129, 0.5);
        background: rgba(16, 185, 129, 0.08);
    }
    .service-quick-card.stok-card:hover {
        border-color: rgba(168, 85, 247, 0.5);
        background: rgba(168, 85, 247, 0.08);
    }
    .service-icon-wrap {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        border: 1px solid;
        flex-shrink: 0;
    }
    .service-text-wrap {
        flex: 1;
        min-width: 0;
    }
    .service-card-title {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    body.light-theme .service-card-title {
        color: #0f172a;
    }
    .service-card-sub {
        font-size: 10px;
        color: var(--login-text-body);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .service-arrow {
        font-size: 13px;
        color: var(--login-text-body);
        transition: transform 0.2s ease, color 0.2s ease;
    }
    .service-quick-card:hover .service-arrow {
        transform: translateX(3px);
        color: #38bdf8;
    }
</style>

<div class="portal-auth-container">
    <!-- Ambient glowing backdrop orbs -->
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="portal-card">
        <!-- Top Multi-Stop Animated Gradient Line -->
        <div class="portal-card-accent"></div>

        <!-- Header Actions: Version Pill, Menu Daftar & Theme Switcher -->
        <div class="portal-header-actions">
            <div class="portal-version-tag">
                <span class="portal-version-dot"></span>
                <span>Portal Presensi v2</span>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" onclick="togglePortalTheme()" title="Ganti Tampilan">
                    <span id="theme-icon">☀️</span>
                    <span id="theme-label">Mode Terang</span>
                </button>
            </div>
        </div>

        <!-- Center Biometric Emblem & Branding -->
        <div class="portal-brand-block">
            <div class="biometric-emblem-wrap">
                <div class="biometric-emblem" id="biometric-emblem">
                    <div class="biometric-scanline"></div>
                    <!-- Detailed SVG Biometric Fingerprint Icon -->
                    <svg class="biometric-svg" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.87-3.13-7-7-7z" style="display:none;"></path>
                        <!-- Authentic Curved Biometric Fingerprint Lines -->
                        <path d="M12 4.5c-3.5 0-6 2.5-6 6 0 3.2 2 5.5 2 7.5"></path>
                        <path d="M9.5 7.5c-1.5 1-2 2.5-2 4.5 0 2.2 1.5 4.5 2.5 6"></path>
                        <path d="M14.5 7.5c1.5 1 2 2.5 2 4.5 0 2.5-1.5 4.5-2.5 6"></path>
                        <path d="M18 10.5c0-4-3-6-6-6"></path>
                        <path d="M12 11c0 2.2-.5 4.5-1 6.5"></path>
                        <path d="M12 8a2.5 2.5 0 0 1 2.5 2.5c0 1.5-.5 3-1 4.5"></path>
                    </svg>
                </div>
            </div>

            <h1 class="portal-title">Portal Login</h1>
            <p class="portal-subtitle">Maya Graha Kencana</p>
        </div>

        <!-- Flash Notifications -->
        @if(session('registration_submitted'))
            @php $regInfo = session('registration_submitted'); @endphp
            <div style="background: rgba(16, 185, 129, 0.1); border: 1.5px solid rgba(16, 185, 129, 0.35); border-radius: 16px; padding: 16px; margin-bottom: 18px; text-align: left; box-shadow: 0 4px 18px rgba(16, 185, 129, 0.12);">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 20px;">🎉</span>
                    <strong style="color: #10b981; font-size: 14.5px;">Pendaftaran Mandiri Berhasil Diajukan!</strong>
                </div>
                <div style="font-size: 12.5px; color: var(--login-text-body); line-height: 1.5; margin-bottom: 12px;">
                    Permohonan akun untuk <strong>{{ $regInfo['full_name'] ?? $regInfo['username'] }}</strong> (<code style="color: #38bdf8;">{{ $regInfo['username'] }}</code>) telah masuk ke antrean verifikasi.
                </div>
                <div style="background: rgba(2, 132, 199, 0.12); border: 1px solid rgba(2, 132, 199, 0.3); border-radius: 10px; padding: 10px 12px; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">⏱️</span>
                    <div>
                        <div style="font-size: 11px; color: #38bdf8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Estimasi Waktu Verifikasi Admin/HR</div>
                        <div style="font-size: 13.5px; font-weight: 700; color: var(--login-text-heading);">{{ $regInfo['estimated_time'] ?? '1x24 Jam Kerja (Maksimal 3 Hari)' }}</div>
                    </div>
                </div>
                <div style="font-size: 11.5px; color: var(--login-text-body); line-height: 1.4; margin-bottom: 12px;">
                    💡 <em>Petunjuk:</em> Tim Admin/HR akan memverifikasi kesesuaian data Anda. Setelah akun disetujui, Anda dapat langsung masuk dengan Username dan PIN / Password yang telah didaftarkan.
                </div>
                <div style="text-align: right;">
                    <a href="{{ route('presensi2.check-status') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #38bdf8; text-decoration: none; padding: 6px 14px; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.28); border-radius: 8px; transition: all 0.2s ease;">
                        <span>🔍</span>
                        <span>Pantau Status Verifikasi &rarr;</span>
                    </a>
                </div>
            </div>
        @elseif(session('success'))
            <div class="auth-alert-box auth-alert-success" style="margin-bottom: 14px; text-align: left;">
                <span style="font-size: 16px;">🎉</span>
                <div>
                    <strong style="color: #10b981; display: block; margin-bottom: 2px;">Berhasil!</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="auth-alert-box auth-alert-success">
                <span>ℹ️</span>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="auth-alert-box" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); color: #fbbf24; margin-bottom: 14px;">
                <span>⚠️</span>
                <div>{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('lockout_seconds'))
            <div class="auth-alert-box auth-alert-error" id="lockout-alert-box" style="background: rgba(239, 68, 68, 0.18); border: 2px solid #ef4444; border-radius: 16px; color: #fca5a5; padding: 16px; flex-direction: column; align-items: stretch; gap: 10px; box-shadow: 0 0 25px rgba(239, 68, 68, 0.35);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: #ef4444; font-size: 15px;">
                        <span style="font-size: 20px;">🛡️</span>
                        <span>SISTEM TERKUNCI (UI LOCKOUT)</span>
                    </div>
                    <span style="background: rgba(239, 68, 68, 0.3); color: #fecaca; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: bold;">
                        Anti Brute-Force
                    </span>
                </div>
                <div style="font-size: 13px; line-height: 1.5; color: #fee2e2;">
                    {{ $errors->first() }}
                </div>
                
                <!-- Digital Countdown Display -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: rgba(0, 0, 0, 0.45); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px;">
                    <span style="font-size: 12px; color: #cbd5e1; font-weight: 600;">Waktu tunggu tersisa:</span>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span id="lockout-timer" data-seconds="{{ session('lockout_seconds') }}" style="font-size: 24px; font-weight: 900; font-family: monospace; color: #fde047;">
                            {{ session('lockout_seconds') }}
                        </span>
                        <span id="lockout-timer-unit" style="font-size: 12px; color: #fef08a; font-weight: bold;">detik</span>
                    </div>
                </div>

                <!-- Animated Progress Bar -->
                <div style="width: 100%; height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 10px; overflow: hidden;">
                    <div id="lockout-progress-bar" style="width: 100%; height: 100%; background: linear-gradient(90deg, #ef4444, #f59e0b); transition: width 1s linear;"></div>
                </div>
            </div>
        @elseif(session('remaining_attempts') === 1)
            <!-- Critical Early Warning: Percobaan Terakhir -->
            <div class="auth-alert-box" style="background: rgba(239, 68, 68, 0.2); border: 2px solid #ef4444; color: #fecaca; flex-direction: column; align-items: flex-start; gap: 6px; box-shadow: 0 0 15px rgba(239, 68, 68, 0.3); animation: pulseAlert 1.5s infinite alternate;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: #f87171; font-size: 14px;">
                    <span>🚨</span>
                    <span>EARLY WARNING: KESEMPATAN TERAKHIR!</span>
                </div>
                <div style="font-size: 13px; line-height: 1.4; color: #fef2f2;">
                    {{ $errors->first() }}
                </div>
                <div style="margin-top: 4px; font-size: 12px; color: #fde047;">
                    💡 <em>Lupa PIN atau Password? Klik link <strong>"Lupa Password?"</strong> di bawah daripada akun terkunci.</em>
                </div>
            </div>
            <style>
                @keyframes pulseAlert {
                    0% { transform: scale(1); box-shadow: 0 0 10px rgba(239, 68, 68, 0.2); }
                    100% { transform: scale(1.01); box-shadow: 0 0 20px rgba(239, 68, 68, 0.45); }
                }
            </style>
        @elseif(session('remaining_attempts') === 2)
            <!-- Mild Early Warning: Percobaan ke-1 Gagal -->
            <div class="auth-alert-box" style="background: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.45); color: #fde68a; flex-direction: column; align-items: flex-start; gap: 6px;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #fbbf24;">
                    <span>⚠️</span>
                    <span>Peringatan Dini: Password Salah</span>
                </div>
                <div style="font-size: 13px; line-height: 1.4;">
                    {{ $errors->first() }}
                </div>
            </div>
        @elseif(isset($errors) && $errors->any())
            <div class="auth-alert-box auth-alert-error">
                <span>⚠️</span>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <!-- Unified Login Form -->
        <form action="{{ route('presensi2.login.post') }}" method="POST" id="form-portal-login">
            @csrf

            <!-- Tulis Identitas Karyawan (Murni Input Teks) -->
            <div class="form-group-custom" id="group-list-input">
                <label for="employee_name" class="form-label-custom">
                    <span>Identitas Karyawan</span>
                    <span style="font-size: 11px; color: #06b6d4; font-weight: normal;">(Tulis Nama Karyawan)</span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">👤</span>
                    <input 
                        type="text"
                        name="employee_name" 
                        id="employee_name" 
                        class="form-control-custom"
                        placeholder="Ketik nama karyawan Anda..."
                        value="{{ old('employee_name') }}"
                        required
                        autofocus
                        autocomplete="off"
                    >
                </div>
            </div>

            <!-- Input 2: Password atau PIN 6-Digit (With Eye Toggle & PIN Tracker) -->
            <div class="form-group-custom">
                <label for="credential" class="form-label-custom">
                    <span>Password atau PIN 6-Digit</span>
                    <span style="font-size: 11px; color: var(--login-text-body); font-weight: normal;" id="pin-status-text">
                        Kredensial Keamanan
                    </span>
                </label>
                <div class="input-field-wrap">
                    <span class="input-leading-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                    <input 
                        type="password" 
                        name="credential" 
                        id="credential" 
                        class="form-control-custom" 
                        placeholder="••••••••" 
                        required 
                        minlength="4"
                        autocomplete="current-password"
                        oninput="handleCredentialInput(this.value)"
                    >
                    <button 
                        type="button" 
                        class="password-toggle-btn" 
                        id="toggle-credential-btn" 
                        onclick="toggleCredentialVisibility()" 
                        title="Tampilkan / Sembunyikan"
                    >
                        <svg id="eye-icon-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eye-icon-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>

                <!-- 6-Digit PIN visual dots tracker (animates when digits are typed) -->
                <div class="pin-dots-indicator" id="pin-dots-container">
                    <div class="pin-dot" id="pdot-1"></div>
                    <div class="pin-dot" id="pdot-2"></div>
                    <div class="pin-dot" id="pdot-3"></div>
                    <div class="pin-dot" id="pdot-4"></div>
                    <div class="pin-dot" id="pdot-5"></div>
                    <div class="pin-dot" id="pdot-6"></div>
                </div>
            </div>

            <!-- Options: Remember Me & Lupa Password -->
            <div class="options-row">
                <label class="checkbox-label" for="remember">
                    <input type="checkbox" name="remember" id="remember" class="checkbox-input-custom" value="1" checked>
                    <span>Ingat Saya di Perangkat Ini</span>
                </label>

                <a href="{{ route('presensi2.forgot-password') }}" class="forgot-link" style="color: #38bdf8; font-weight: 600;">
                    Lupa Password?
                </a>
            </div>

            <!-- Primary Action Button: "Masuk ke Sistem" -->
            <button type="submit" class="btn-portal-submit" id="btn-submit-portal">
                <span id="btn-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                </span>
                <span id="btn-text">Masuk ke Sistem</span>
            </button>
        </form>
        <!-- Quick Action Buttons: GC, Meeting, Presnesi BO/Training, AMBIL STOK -->
        <!-- <div class="quick-services-divider">
            <div class="line"></div>
            <span class="label">
                <span>⚡</span> Portal Login Layanan & Presensi Tambahan
            </span>
            <div class="line"></div>
        </div>

        <div class="quick-services-grid"> -->
            <!-- 1. General Cleaning (GC) -->
            <!-- <a href="{{ route('presensi2.layanan.login', ['service' => 'gc']) }}" class="service-quick-card gc-card" id="btn-quick-gc" title="Buka Portal Login General Cleaning (GC)">
                <div class="service-icon-wrap" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.35);">
                    🧹
                </div>
                <div class="service-text-wrap">
                    <div class="service-card-title">General Cleaning (GC)</div>
                    <div class="service-card-sub">Portal Login Khusus GC</div>
                </div>
                <span class="service-arrow">&rarr;</span>
            </a> -->

            <!-- 2. Meeting -->
            <!-- <a href="{{ route('presensi2.layanan.login', ['service' => 'meeting']) }}" class="service-quick-card meeting-card" id="btn-quick-meeting" title="Buka Portal Login Meeting & Rapat">
                <div class="service-icon-wrap" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border-color: rgba(14, 165, 233, 0.35);">
                    🤝
                </div>
                <div class="service-text-wrap">
                    <div class="service-card-title">Meeting</div>
                    <div class="service-card-sub">Portal Login Khusus Meeting</div>
                </div>
                <span class="service-arrow">&rarr;</span>
            </a> -->

            <!-- 3. Presnesi BO/Training -->
            <!-- <a href="{{ route('presensi2.layanan.login', ['service' => 'bo-training']) }}" class="service-quick-card training-card" id="btn-quick-training" title="Buka Portal Login BO/Training & Workshop SOP">
                <div class="service-icon-wrap" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.35);">
                    💼
                </div>
                <div class="service-text-wrap">
                   <div class="service-card-title">Presnesi BO/Training</div>
                    <div class="service-card-sub">Portal Login Khusus BO/Training</div>
                </div>
                <span class="service-arrow">&rarr;</span>
            </a> -->

            <!-- 4. AMBIL STOK -->
            <!-- <a href="{{ route('presensi2.layanan.login', ['service' => 'ambil-stok']) }}" class="service-quick-card stok-card" id="btn-quick-ambil-stok" title="Buka Portal Login Ambil Stok & Logistik Outlet">
                <div class="service-icon-wrap" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border-color: rgba(168, 85, 247, 0.35);">
                    📦
                </div>
                <div class="service-text-wrap">
                    <div class="service-card-title">AMBIL STOK</div>
                    <div class="service-card-sub">Portal Login Khusus Ambil Stok</div>
                </div>
                <span class="service-arrow">&rarr;</span>
            </a>
        </div> -->

        <!-- Menu Daftar / Daftar Ulang Link -->
        <div style="margin-top: 10px; margin-bottom: 6px; text-align: center;">
            <a href="{{ route('presensi2.register') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 100%; gap: 8px; color: #38bdf8; font-size: 13px; font-weight: 600; text-decoration: none; padding: 12px 18px; border-radius: 12px; background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); transition: all 0.2s ease;">
                <span>📝</span>
                <span>Karyawan Baru / Ingin Daftar Ulang? <strong>Klik di Sini</strong> &rarr;</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // State management for Theme
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

    // Credential Input Handler: Updates 6-Digit PIN Dots Tracker
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
            statusText.style.color = '#06b6d4';
        } else if (len > 0) {
            statusText.textContent = 'Password Alfanumerik';
            statusText.style.color = 'var(--login-text-body)';
        } else {
            statusText.textContent = 'Kredensial Keamanan';
            statusText.style.color = 'var(--login-text-body)';
        }
    }

    // Password Toggle Visibility
    function toggleCredentialVisibility() {
        const input = document.getElementById('credential');
        const eyeOpen = document.getElementById('eye-icon-open');
        const eyeClosed = document.getElementById('eye-icon-closed');

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'inline-block';
        } else {
            input.type = 'password';
            eyeOpen.style.display = 'inline-block';
            eyeClosed.style.display = 'none';
        }
    }

    function showAccessHelp() {
        alert("Bantuan Akses Presensi:\n\n1. Tulis Nama Karyawan Anda.\n2. Masukkan Password atau PIN 6-digit terdaftar.\n\nJika lupa PIN/Password, silakan hubungi Admin atau HRD.");
    }

    // Form Submit visual loading
    document.getElementById('form-portal-login').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit-portal');
        const btnText = document.getElementById('btn-text');
        const btnIcon = document.getElementById('btn-icon');
        btnText.textContent = 'Memverifikasi Sistem...';
        btnIcon.innerHTML = '⏳';
        btn.style.opacity = '0.85';
        btn.style.pointerEvents = 'none';
    });

    // Lockout countdown timer handler
    function initLockoutTimer() {
        const timerEl = document.getElementById('lockout-timer');
        if (!timerEl) return;

        let initialSeconds = parseInt(timerEl.getAttribute('data-seconds'), 10);
        let seconds = initialSeconds;
        if (isNaN(seconds) || seconds <= 0) return;

        const btn = document.getElementById('btn-submit-portal');
        const btnText = document.getElementById('btn-text');
        const credInput = document.getElementById('credential');
        const progressBar = document.getElementById('lockout-progress-bar');
        const unitEl = document.getElementById('lockout-timer-unit');

        function formatTimeDisplay(totalSec) {
            if (totalSec >= 60) {
                const m = Math.floor(totalSec / 60);
                const s = totalSec % 60;
                return `${m}m ${s < 10 ? '0' : ''}${s}s`;
            }
            return `${totalSec}`;
        }

        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            btn.style.filter = 'grayscale(100%)';
            if (btnText) btnText.textContent = `Terkunci (${formatTimeDisplay(seconds)})`;
        }
        if (credInput) {
            credInput.disabled = true;
            credInput.placeholder = 'Input dikunci sementara...';
            credInput.style.backgroundColor = 'rgba(239, 68, 68, 0.08)';
            credInput.style.borderColor = 'rgba(239, 68, 68, 0.35)';
        }

        if (seconds >= 60 && unitEl) {
            unitEl.textContent = '';
        }

        const interval = setInterval(() => {
            seconds--;
            if (timerEl) {
                timerEl.textContent = formatTimeDisplay(seconds);
            }
            if (unitEl) {
                unitEl.textContent = seconds < 60 ? 'detik' : '';
            }
            if (btnText) {
                btnText.textContent = `Terkunci (${formatTimeDisplay(seconds)})`;
            }
            if (progressBar && initialSeconds > 0) {
                const percent = Math.max(0, (seconds / initialSeconds) * 100);
                progressBar.style.width = percent + '%';
            }

            if (seconds <= 0) {
                clearInterval(interval);
                if (btn) {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.style.filter = 'none';
                    if (btnText) btnText.textContent = 'Masuk ke Sistem';
                }
                if (credInput) {
                    credInput.disabled = false;
                    credInput.placeholder = 'Masukkan Password atau PIN 6-digit';
                    credInput.style.backgroundColor = '';
                    credInput.style.borderColor = '';
                }
                const box = document.getElementById('lockout-alert-box');
                if (box) {
                    box.style.background = 'rgba(34, 197, 94, 0.15)';
                    box.style.borderColor = 'rgba(34, 197, 94, 0.4)';
                    box.style.color = '#86efac';
                    box.style.boxShadow = '0 0 15px rgba(34, 197, 94, 0.25)';
                    box.innerHTML = '<div style="display: flex; align-items: center; gap: 8px; font-weight: bold; color: #4ade80;"><span>✅</span><span>Waktu tunggu selesai. Silakan masukkan password/PIN Anda kembali.</span></div>';
                }
            }
        }, 1000);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        initPortalTheme();
        initLockoutTimer();
    });
</script>
@endpush
@endsection
