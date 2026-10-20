<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin &amp; Staff ERP — Maya Graha Kencana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #080d1a;
            --bg-card: rgba(15, 23, 42, 0.88);
            --bg-card-input: rgba(11, 19, 38, 0.85);
            --border: #1e293b;
            --border-hover: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;
            
            /* Manager Theme: Cyan & Electric Indigo */
            --manager-primary: #06b6d4;
            --manager-hover: #0891b2;
            --manager-glow: rgba(6, 182, 212, 0.35);
            --manager-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #6366f1 100%);
            
            /* Staff Theme: Emerald & Mint */
            --staff-primary: #10b981;
            --staff-hover: #059669;
            --staff-glow: rgba(16, 185, 129, 0.35);
            --staff-gradient: linear-gradient(135deg, #10b981 0%, #0d9488 50%, #06b6d4 100%);

            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(at 0% 0%, rgba(6, 182, 212, 0.15) 0px, transparent 45%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(16, 185, 129, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.8) 0px, transparent 60%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient animated glow background elements */
        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.45;
            animation: pulse-slow 8s ease-in-out infinite alternate;
        }
        .ambient-orb-1 {
            width: 380px;
            height: 380px;
            background: rgba(6, 182, 212, 0.25);
            top: -100px;
            left: -100px;
        }
        .ambient-orb-2 {
            width: 420px;
            height: 420px;
            background: rgba(99, 102, 241, 0.25);
            bottom: -120px;
            right: -120px;
            animation-delay: -4s;
        }

        @keyframes pulse-slow {
            0% { transform: scale(1) translate(0, 0); opacity: 0.35; }
            100% { transform: scale(1.15) translate(20px, 20px); opacity: 0.6; }
        }

        /* Container & Card */
        .login-wrapper {
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 36px 32px 32px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.75), 0 0 40px rgba(6, 182, 212, 0.08);
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Top Accent Bar */
        .card-accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--manager-gradient);
            transition: background 0.4s ease;
        }
        .role-staff .card-accent-bar {
            background: var(--staff-gradient);
        }

        /* Brand Header */
        .brand-header {
            text-align: center;
            margin-bottom: 26px;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 13px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #38bdf8;
            margin-bottom: 12px;
        }

        .brand-pill-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #06b6d4;
            box-shadow: 0 0 8px #06b6d4;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .brand-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            line-height: 1.25;
            margin-bottom: 5px;
        }

        .brand-title span {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ========================================================
           ROLE SELECTOR CARDS (1. MANAGER vs 2. STAFF)
           ======================================================== */
        .role-section-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-subtle);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .role-card-btn {
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 14px 14px;
            cursor: pointer;
            text-align: left;
            position: relative;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .role-card-btn:hover {
            border-color: var(--border-hover);
            background: rgba(30, 41, 59, 0.5);
            transform: translateY(-2px);
        }

        .role-card-btn.active.manager-card {
            border-color: var(--manager-primary);
            background: linear-gradient(145deg, rgba(6, 182, 212, 0.12), rgba(15, 23, 42, 0.8));
            box-shadow: 0 0 24px var(--manager-glow), inset 0 0 16px rgba(6, 182, 212, 0.08);
        }

        .role-card-btn.active.staff-card {
            border-color: var(--staff-primary);
            background: linear-gradient(145deg, rgba(16, 185, 129, 0.12), rgba(15, 23, 42, 0.8));
            box-shadow: 0 0 24px var(--staff-glow), inset 0 0 16px rgba(16, 185, 129, 0.08);
        }

        .role-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .role-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: rgba(255, 255, 255, 0.06);
            transition: transform 0.2s ease;
        }

        .manager-card .role-icon-box { color: #38bdf8; }
        .staff-card .role-icon-box { color: #34d399; }

        .role-card-btn.active .role-icon-box {
            transform: scale(1.08);
        }

        .role-radio-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .role-radio-dot::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: transparent;
            transition: all 0.2s ease;
        }

        .role-card-btn.active.manager-card .role-radio-dot {
            border-color: var(--manager-primary);
        }
        .role-card-btn.active.manager-card .role-radio-dot::after {
            background: var(--manager-primary);
            box-shadow: 0 0 6px var(--manager-primary);
        }

        .role-card-btn.active.staff-card .role-radio-dot {
            border-color: var(--staff-primary);
        }
        .role-card-btn.active.staff-card .role-radio-dot::after {
            background: var(--staff-primary);
            box-shadow: 0 0 6px var(--staff-primary);
        }

        .role-title-text {
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .role-desc-text {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.35;
        }

        .role-chip-hint {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 6px;
            margin-top: 4px;
            align-self: flex-start;
        }

        .manager-card .role-chip-hint {
            background: rgba(6, 182, 212, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }

        .staff-card .role-chip-hint {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Role Active Info Banner */
        .role-info-banner {
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            transition: all 0.3s ease;
        }

        .role-manager .role-info-banner {
            background: rgba(6, 182, 212, 0.08);
            border: 1px solid rgba(6, 182, 212, 0.25);
            color: #bae6fd;
        }

        .role-staff .role-info-banner {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #a7f3d0;
        }

        /* Alerts */
        .alert-box {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        .alert-info {
            background: rgba(6, 182, 212, 0.12);
            border: 1px solid rgba(6, 182, 212, 0.35);
            color: #7dd3fc;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-helper-label {
            font-size: 11px;
            color: var(--text-subtle);
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 16px;
            pointer-events: none;
        }

        .form-input, .form-select {
            width: 100%;
            background: var(--bg-card-input);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px 12px 42px;
            color: #ffffff;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px;
        }

        .form-select option {
            background-color: #0f172a;
            color: #ffffff;
            padding: 8px;
        }

        .form-input:focus, .form-select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 1);
        }

        .role-staff .form-input:focus, .role-staff .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
            transition: color 0.2s;
        }

        .toggle-password-btn:hover {
            color: #cbd5e1;
        }

        /* Checkbox & Options */
        .form-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--text-muted);
            user-select: none;
        }

        .checkbox-label input {
            accent-color: #06b6d4;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .role-staff .checkbox-label input {
            accent-color: #10b981;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            background: var(--manager-gradient);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 20px var(--manager-glow);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .role-staff .btn-submit {
            background: var(--staff-gradient);
            box-shadow: 0 6px 20px var(--staff-glow);
        }

        .btn-submit:hover {
            opacity: 0.96;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--manager-glow);
        }

        .role-staff .btn-submit:hover {
            box-shadow: 0 8px 25px var(--staff-glow);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Quick Test / Demo Section */
        .quick-demo-container {
            margin-top: 24px;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.55);
            border: 1px dashed #334155;
            border-radius: 14px;
            text-align: center;
        }

        .quick-demo-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .quick-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .demo-chip {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11.5px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .demo-chip:hover {
            background: rgba(6, 182, 212, 0.15);
            border-color: #38bdf8;
            color: #38bdf8;
            transform: translateY(-1px);
        }

        .demo-chip.staff-chip:hover {
            background: rgba(16, 185, 129, 0.15);
            border-color: #34d399;
            color: #34d399;
        }

        /* Footer Links */
        .portal-links {
            margin-top: 22px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: center;
            font-size: 12.5px;
        }

        .portal-link-item {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .portal-link-item:hover {
            color: #38bdf8;
        }

        .role-staff .portal-link-item:hover {
            color: #34d399;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 28px 20px 24px;
                border-radius: 20px;
            }
            .brand-title {
                font-size: 21px;
            }
            .role-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="role-manager" id="bodyRoot">

    <div class="ambient-orb ambient-orb-1"></div>
    <div class="ambient-orb ambient-orb-2"></div>

    <div class="login-wrapper">
        <div class="login-card" id="loginCard">
            <div class="card-accent-bar"></div>

            <!-- Brand Header -->
            <div class="brand-header">
                <div class="brand-pill">
                    <span class="brand-pill-dot"></span>
                    <span>MGK ERP Enterprise Portal</span>
                </div>
                <h1 class="brand-title">Maya Graha <span>Kencana</span></h1>
                <p class="brand-subtitle">Pusat Otentikasi Administrator &amp; Staff Operasional</p>
            </div>

            <!-- Session / Flash Notifications -->
            @if(session('info'))
                <div class="alert-box alert-info">
                    <span>ℹ️</span>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert-box alert-success">
                    <span>✅</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box alert-error">
                    <span>⚠️</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- ROLE SELECTOR CARDS: 1. MANAGER vs 2. STAFF -->
            <div class="role-section-label">
                <span>Pilih Peran Otentikasi Anda:</span>
                <span id="roleBadgeCurrent" style="color: #38bdf8; font-weight: 800;">[ 1. MANAGER ]</span>
            </div>

            <div class="role-grid">
                <!-- 1. Manager Card -->
                <div class="role-card-btn active manager-card" id="btnRoleManager" onclick="selectRole('manager')">
                    <div class="role-card-top">
                        <div class="role-icon-box">👑</div>
                        <div class="role-radio-dot"></div>
                    </div>
                    <div class="role-title-text">1. Manager</div>
                    <div class="role-desc-text">Akses Penuh: Finansial, Master Data &amp; Konfigurasi</div>
                </div>

                <!-- 2. Staff Card -->
                <div class="role-card-btn staff-card" id="btnRoleStaff" onclick="selectRole('staff')">
                    <div class="role-card-top">
                        <div class="role-icon-box">💼</div>
                        <div class="role-radio-dot"></div>
                    </div>
                    <div class="role-title-text">2. Staff</div>
                    <div class="role-desc-text">Dashboard Profil Staff: Presensi, Penjualan &amp; Gaji</div>
                </div>
            </div>

            <!-- Role Context Dynamic Info Banner -->
            <div class="role-info-banner" id="roleInfoBanner">
                <span id="roleInfoIcon" style="font-size: 16px;">👑</span>
                <span id="roleInfoText">Mode <strong>Manager</strong>: Akses administrator tingkat penuh.</span>
            </div>

            <!-- MAIN LOGIN FORM -->
            <form method="POST" action="{{ route('admin.login.post') }}" id="loginForm">
                @csrf
                <input type="hidden" name="role" id="inputRole" value="{{ old('role', 'manager') }}">

                <!-- ==============================================
                     FIELDS FOR MANAGER ROLE
                     ============================================== -->
                <div id="sectionManagerFields">
                    <div class="form-group">
                        <div class="form-label-wrap">
                            <label class="form-label" for="managerIdentifier">
                                <span>👤</span> Identitas / Akun Manager
                            </label>
                            <span class="form-helper-label">Pilih akun terdaftar</span>
                        </div>
                        <div class="input-with-icon">
                            <span class="input-icon-left">🛡️</span>
                            <select id="managerIdentifier" name="manager_identifier" class="form-select">
                                @foreach($managers as $mgr)
                                    <option value="{{ $mgr->id }}">{{ $mgr->name }} — {{ $mgr->position ?? 'Manager' }} ({{ $mgr->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="manualManagerInputWrap" style="display: none; margin-top: 8px;">
                            <input 
                                type="text" 
                                id="manualManagerText" 
                                name="manual_manager_text" 
                                class="form-input" 
                                placeholder="Masukkan email (contoh: admin@mayanet.com) atau username manager..."
                            >
                        </div>
                    </div>
                </div>

                <!-- ==============================================
                     FIELDS FOR STAFF ROLE
                     ============================================== -->
                <div id="sectionStaffFields" style="display: none;">
                    <div class="form-group">
                        <div class="form-label-wrap">
                            <label class="form-label" for="staffIdentifier">
                                <span>👤</span> Pilih Akun Karyawan / Staff
                            </label>
                            <span class="form-helper-label">Sesuai data Presensi</span>
                        </div>
                        <div class="input-with-icon">
                            <span class="input-icon-left">🏷️</span>
                            <select id="staffIdentifier" name="staff_identifier" class="form-select">
                                <option value="" disabled selected>-- Pilih Nama Staff Operasional --</option>
                                @foreach($staffs as $stf)
                                    <option value="{{ $stf->id }}" {{ old('staff_identifier') == $stf->id ? 'selected' : '' }}>
                                        {{ $stf->name }} — {{ $stf->position ?? 'Staff' }} (NIK: {{ $stf->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="manualStaffInputWrap" style="display: none; margin-top: 8px;">
                            <input 
                                type="text" 
                                id="manualStaffText" 
                                name="manual_staff_text" 
                                class="form-input" 
                                placeholder="Ketik NIK (contoh: EMP-047) atau Username..."
                            >
                        </div>
                    </div>
                </div>

                <!-- ==============================================
                     SHARED / ROLE-SPECIFIC PASSWORD FIELD
                     ============================================== -->
                <div class="form-group">
                    <div class="form-label-wrap">
                        <label class="form-label" for="passwordInput" id="labelPassword">
                            <span>🔑</span> Kata Sandi Admin 
                        </label>
                        <span class="form-helper-label" id="hintPasswordText">Wajib diisi</span>
                    </div>
                    <div class="input-with-icon">
                        <span class="input-icon-left">🔒</span>
                        <input 
                            type="password" 
                            id="passwordInput" 
                            name="password" 
                            class="form-input" 
                            placeholder="Ketik kata sandi" 
                            value="{{ old('password') }}" 
                            required
                        >
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" title="Lihat/Sembunyikan Sandi">
                            👁️
                        </button>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;" id="passwordHelpSubtext">
                        Default kata sandi Manager: <code>maya123</code>
                    </div>
                </div>

                <!-- REMEMBER ME CHECKBOX -->
                <div class="form-options-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember" checked>
                        <span>Ingat sesi masuk di browser ini</span>
                    </label>
                </div>

                <!-- SUBMIT BUTTON -->
                <button type="submit" class="btn-submit" id="btnSubmitForm">
                    <span id="btnSubmitIcon">👑</span>
                    <span id="btnSubmitText">Masuk sebagai Manager ERP &rarr;</span>
                </button>
            </form>
    <script>
        let currentRole = '{{ old("role", "manager") }}';

        function selectRole(role) {
            currentRole = role;
            document.getElementById('inputRole').value = role;

            const root = document.getElementById('bodyRoot');
            const btnManager = document.getElementById('btnRoleManager');
            const btnStaff = document.getElementById('btnRoleStaff');
            const badge = document.getElementById('roleBadgeCurrent');
            
            const secManager = document.getElementById('sectionManagerFields');
            const secStaff = document.getElementById('sectionStaffFields');

            const infoIcon = document.getElementById('roleInfoIcon');
            const infoText = document.getElementById('roleInfoText');

            const labelPassword = document.getElementById('labelPassword');
            const passwordInput = document.getElementById('passwordInput');
            const passwordHelp = document.getElementById('passwordHelpSubtext');

            const btnText = document.getElementById('btnSubmitText');
            const btnIcon = document.getElementById('btnSubmitIcon');

            if (role === 'manager') {
                root.className = 'role-manager';
                btnManager.classList.add('active');
                btnStaff.classList.remove('active');
                badge.innerText = '[ 1. MANAGER ]';
                badge.style.color = '#38bdf8';

                secManager.style.display = 'block';
                secStaff.style.display = 'none';

                labelPassword.innerHTML = '<span>🔑</span> Kata Sandi Manager';
                passwordInput.placeholder = 'Ketik kata sandi...';
                passwordInput.value = '';
                passwordHelp.innerHTML = '';
;
            } else {
                root.className = 'role-staff';
                btnStaff.classList.add('active');
                btnManager.classList.remove('active');
                badge.innerText = '[ 2. STAFF ]';
                badge.style.color = '#34d399';

                secManager.style.display = 'none';
                secStaff.style.display = 'block';

                labelPassword.innerHTML = '<span>🔑</span> Password / PIN Presensi Staff';
                passwordInput.placeholder = 'Password atau PIN presensi karyawan...';
                if (passwordInput.value === '') {
                    passwordInput.value = '';
                }
                passwordHelp.innerHTML = '🔒 Password atau PIN 6-digit <strong>sama persis dengan yang Anda daftarkan di Presensi v2</strong>.';

                btnIcon.innerText = '💼';
                btnText.innerText = 'Masuk ke Dashboard Profil Staff →';
            }
        }

        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        function fillPassword(val) {
            document.getElementById('passwordInput').value = val;
        }

        function quickFillManager(identifier, pwd) {
            selectRole('manager');
            const select = document.getElementById('managerIdentifier');
            select.value = identifier;
            fillPassword(pwd);
        }

        function quickFillStaff(stfId) {
            selectRole('staff');
            const select = document.getElementById('staffIdentifier');
            select.value = stfId;
            document.getElementById('passwordInput').focus();
        }

        // Listener for manual manager dropdown option
        document.getElementById('managerIdentifier').addEventListener('change', function() {
            const wrap = document.getElementById('manualManagerInputWrap');
            if (this.value === 'manual_input') {
                wrap.style.display = 'block';
                document.getElementById('manualManagerText').focus();
            } else {
                wrap.style.display = 'none';
            }
        });

        // Listener for manual staff dropdown option
        document.getElementById('staffIdentifier').addEventListener('change', function() {
            const wrap = document.getElementById('manualStaffInputWrap');
            if (this.value === 'manual_staff') {
                wrap.style.display = 'block';
                document.getElementById('manualStaffText').focus();
            } else {
                wrap.style.display = 'none';
            }
        });

        // Initialize state on load
        document.addEventListener('DOMContentLoaded', function() {
            selectRole(currentRole);
        });
    </script>
</body>
</html>
