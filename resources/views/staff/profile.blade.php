<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Profil Staff — {{ $employee->name }} | Maya Graha Kencana</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #080d1a;
            --bg-card: rgba(15, 23, 42, 0.82);
            --bg-card-subtle: rgba(22, 33, 56, 0.65);
            --bg-input: rgba(11, 19, 38, 0.85);
            --border: #1e293b;
            --border-hover: #334155;
            --border-focus: #10b981;
            
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-subtle: #64748b;
            
            /* Staff Theme: Vibrant Emerald, Mint & Cyan */
            --primary: #10b981;
            --primary-hover: #059669;
            --primary-glow: rgba(16, 185, 129, 0.35);
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #0d9488 50%, #06b6d4 100%);
            
            --cyan: #06b6d4;
            --indigo: #6366f1;
            --amber: #f59e0b;
            --danger: #ef4444;
            --purple: #a855f7;
            
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 8px;
            --shadow-card: 0 16px 36px rgba(0, 0, 0, 0.45);
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
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.12) 0px, transparent 40%),
                radial-gradient(at 100% 0%, rgba(6, 182, 212, 0.15) 0px, transparent 45%),
                radial-gradient(at 50% 100%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.8) 0px, transparent 60%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 24px 20px 60px;
            overflow-x: hidden;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Ambient glowing orbs */
        .ambient-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.35;
        }
        .ambient-orb-1 {
            width: 450px;
            height: 450px;
            background: rgba(16, 185, 129, 0.2);
            top: -150px;
            left: -150px;
        }
        .ambient-orb-2 {
            width: 480px;
            height: 480px;
            background: rgba(6, 182, 212, 0.2);
            bottom: -150px;
            right: -150px;
        }

        /* Top Navigation Header */
        .top-navbar {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(20px);
            margin-bottom: 24px;
            box-shadow: var(--shadow-card);
            flex-wrap: wrap;
            gap: 16px;
        }

        .navbar-brand-group {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo-badge {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .brand-text-wrap h1 {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #ffffff;
            line-height: 1.2;
        }

        .brand-text-wrap p {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-nav-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-nav-presensi {
            background: rgba(6, 182, 212, 0.15);
            border-color: rgba(6, 182, 212, 0.4);
            color: #38bdf8;
        }
        .btn-nav-presensi:hover {
            background: rgba(6, 182, 212, 0.25);
            transform: translateY(-1px);
        }

        .btn-nav-logout {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }
        .btn-nav-logout:hover {
            background: rgba(239, 68, 68, 0.28);
            transform: translateY(-1px);
        }

        /* Banner Flash Alerts */
        .alert-banner {
            position: relative;
            z-index: 10;
            padding: 14px 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slide-in 0.3s ease;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.5);
            color: #6ee7b7;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }
        @keyframes slide-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Hero Profile Card (1. Profil & 2. Username) */
        .profile-hero-card {
            position: relative;
            z-index: 10;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 28px;
            backdrop-filter: blur(20px);
            margin-bottom: 24px;
            box-shadow: var(--shadow-card);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 28px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .profile-hero-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        .avatar-container {
            position: relative;
            margin: 0 auto;
        }

        .staff-avatar {
            width: 96px;
            height: 96px;
            border-radius: 26px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 800;
            color: #ffffff;
            border: 3px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 30px var(--primary-glow);
        }

        .status-badge-online {
            position: absolute;
            bottom: -4px;
            right: -4px;
            background: #10b981;
            color: #042f2e;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 20px;
            border: 2px solid #080d1a;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.5);
        }
        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #042f2e;
        }

        .profile-hero-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .profile-name-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .staff-full-name {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .badge-role-staff {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34d399;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 8px;
            letter-spacing: 0.02em;
        }

        .badge-nik {
            background: rgba(6, 182, 212, 0.12);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #38bdf8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .profile-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .profile-chips-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .profile-chip-item {
            background: var(--bg-card-subtle);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 6px 12px;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            gap: 7px;
            color: #cbd5e1;
        }
        .profile-chip-item strong {
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
        }

        .hero-action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 200px;
        }

        .btn-action-primary {
            background: var(--primary-gradient);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--primary-glow);
            opacity: 0.95;
        }

        .btn-action-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: #e2e8f0;
            border-radius: 12px;
            padding: 11px 18px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-action-secondary:hover {
            background: rgba(255, 255, 255, 0.09);
            border-color: var(--border-hover);
        }

        /* 3-COLUMN METRICS HIGHLIGHT (Presensi, Penjualan, Gaji) */
        .metrics-overview-grid {
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 992px) {
            .metrics-overview-grid {
                grid-template-columns: 1fr;
            }
        }

        .metric-highlight-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 22px;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-card);
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
        }
        .metric-highlight-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-3px);
        }

        .card-presensi {
            border-top: 3px solid var(--cyan);
        }
        .card-penjualan {
            border-top: 3px solid var(--primary);
        }
        .card-gaji {
            border-top: 3px solid var(--amber);
        }

        .metric-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .metric-card-title {
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .metric-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .icon-box-presensi { background: rgba(6, 182, 212, 0.15); color: #38bdf8; }
        .icon-box-penjualan { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .icon-box-gaji { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }

        .metric-main-value {
            font-size: 30px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
            font-family: 'JetBrains Mono', monospace;
        }

        .metric-sub-detail {
            font-size: 12.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            margin-top: 12px;
        }

        .pill-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
        }
        .pill-cyan { background: rgba(6, 182, 212, 0.15); color: #38bdf8; }
        .pill-emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .pill-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .pill-danger { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }

        /* MAIN CONTENT LAYOUT (2 Columns) */
        .dashboard-main-grid {
            position: relative;
            z-index: 10;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .dashboard-main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Generic Section Card */
        .section-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.01em;
        }

        .section-title span.icon {
            font-size: 18px;
        }

        /* 3. BISA UBAH PASSWORD FORM CARD */
        .password-form-wrap {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-input-wrap {
            position: relative;
        }

        .form-input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 42px 12px 14px;
            color: #ffffff;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 12px var(--primary-glow);
        }

        .toggle-pwd-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 15px;
            padding: 4px;
        }

        .password-hint-box {
            background: rgba(16, 185, 129, 0.08);
            border: 1px dashed rgba(16, 185, 129, 0.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            color: #a7f3d0;
            line-height: 1.4;
        }

        /* 4. KOLOM JUMLAH PRESENSI TABLES */
        .presensi-stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 640px) {
            .presensi-stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .presensi-stat-mini {
            background: var(--bg-card-subtle);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 14px;
            text-align: center;
        }
        .presensi-stat-mini .label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }
        .presensi-stat-mini .val {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .custom-table th {
            text-align: left;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border);
        }
        .custom-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }
        .custom-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* 5. KOLOM PENDAPATAN DALAM PENJUALAN */
        .sales-breakdown-bar {
            display: flex;
            height: 10px;
            border-radius: 6px;
            overflow: hidden;
            margin: 16px 0 10px;
            background: #1e293b;
        }
        .bar-billing {
            background: var(--cyan);
            transition: width 0.5s ease;
        }
        .bar-fnb {
            background: var(--primary);
            transition: width 0.5s ease;
        }

        .sales-legend {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        /* 6. GAJI & KOMISI BREAKDOWN */
        .salary-summary-box {
            background: linear-gradient(145deg, rgba(245, 158, 11, 0.12), rgba(15, 23, 42, 0.7));
            border: 1px solid rgba(245, 158, 11, 0.35);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .salary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
            font-size: 13.5px;
        }
        .salary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .salary-row.total {
            border-top: 2px solid rgba(245, 158, 11, 0.5);
            border-bottom: none;
            margin-top: 8px;
            padding-top: 14px;
            font-size: 16px;
            font-weight: 800;
            color: #fbbf24;
        }

        .slip-print-box {
            text-align: center;
            padding-top: 10px;
        }

        /* Footer */
        .dashboard-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12.5px;
            color: var(--text-subtle);
        }

        /* Print Specific Styles */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
            }
            .ambient-orb, .top-navbar, .hero-action-buttons, .toggle-pwd-btn, .dashboard-footer, #cardUbahPassword {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
            }
            .section-card, .profile-hero-card, .metric-highlight-card {
                border: 1px solid #ccc !important;
                background: none !important;
                box-shadow: none !important;
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Orbs -->
    <div class="ambient-orb ambient-orb-1"></div>
    <div class="ambient-orb ambient-orb-2"></div>

    <div class="container">

        <!-- TOP NAVBAR -->
        <header class="top-navbar">
            <div class="navbar-brand-group">
                <div class="brand-logo-badge">💼</div>
                <div class="brand-text-wrap">
                    <h1>Portal Staff &amp; Profil Karyawan</h1>
                    <p>ERP Maya Graha Kencana — Sistem Presensi &amp; Kasir</p>
                </div>
            </div>

            <div class="navbar-actions">
                <a href="{{ route('presensi2.scan') }}" class="btn-nav-action btn-nav-presensi" title="Buka Scan Presensi">
                    <span>📷</span> Presensi Scan QR
                </a>

                <a href="{{ route('logout') }}" class="btn-nav-action btn-nav-logout" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();" title="Keluar">
                    <span>🚪</span> Keluar
                </a>
                <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </header>

        <!-- FLASH ALERTS -->
        @if(session('success'))
            <div class="alert-banner alert-success">
                <span style="font-size: 18px;">🎉</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('password_success'))
            <div class="alert-banner alert-success">
                <span style="font-size: 18px;">✅</span>
                <div>{{ session('password_success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-banner alert-error">
                <span style="font-size: 18px;">⚠️</span>
                <div>
                    <strong>Perhatian:</strong>
                    <ul style="margin-left: 20px; margin-top: 4px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- ========================================================
             1. PROFIL & 2. USERNAME (HERO CARD)
             ======================================================== -->
        <div class="profile-hero-card">
            <!-- Avatar -->
            <div class="avatar-container">
                <div class="staff-avatar">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>
                <div class="status-badge-online">
                    <span class="status-dot"></span> Aktif
                </div>
            </div>

            <!-- Detail Profil -->
            <div class="profile-hero-meta">
                <div class="profile-name-row">
                    <h2 class="staff-full-name">{{ $employee->name }}</h2>
                    <span class="badge-role-staff">Role: {{ $employee->position ?? 'Staff Operasional' }}</span>
                    <span class="badge-nik">NIK: {{ $employee->code }}</span>
                </div>
                <p class="profile-subtitle">
                    Penempatan: <strong>{{ $employee->branch->name ?? 'Maya Graha Kencana' }}</strong> &bull; Cabang: {{ $employee->branch->code ?? 'NET-PST' }}
                </p>

                <!-- 2. Username & Detail Kontak -->
                <div class="profile-chips-grid">
                    <div class="profile-chip-item">
                        <span>👤 Username:</span>
                        <strong>{{ $employee->username ?: strtolower(str_replace(' ', '', $employee->name)) }}</strong>
                    </div>
                    <div class="profile-chip-item">
                        <span>📧 Email:</span>
                        <span>{{ $employee->email ?: $employee->code . '@mayanet.com' }}</span>
                    </div>
                    <div class="profile-chip-item">
                        <span>📱 WhatsApp:</span>
                        <span>{{ $employee->phone ?: '0812-4455-6677' }}</span>
                    </div>
                    <div class="profile-chip-item">
                        <span>📜 Status:</span>
                        <strong style="color: #34d399; text-transform: uppercase;">{{ $employee->employment_status ?: 'Tetap' }}</strong>
                    </div>
                    <div class="profile-chip-item">
                        <span>⏰ Shift Terjadwal:</span>
                        <strong style="color: #38bdf8;">Shift 2 (07:00 - 13:00)</strong>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="hero-action-buttons">
                <a href="#cardUbahPassword" class="btn-action-primary" onclick="focusPasswordForm()">
                    <span>🔑</span> Ubah Password / PIN
                </a>
                <button type="button" class="btn-action-secondary" onclick="window.print()">
                    <span>🖨️</span> Cetak Slip / Rekap
                </button>
            </div>
        </div>

        <!-- ========================================================
             3-COLUMN METRICS HIGHLIGHT
             (4. Presensi, 5. Penjualan, 6. Gaji)
             ======================================================== -->
        <div class="metrics-overview-grid">
            <!-- 4. Metrik Presensi -->
            <div class="metric-highlight-card card-presensi">
                <div class="metric-card-top">
                    <div class="metric-card-title">
                        <span class="metric-icon-box icon-box-presensi">📅</span>
                        4. Total Presensi (Bulan Ini)
                    </div>
                    <span class="pill-badge pill-cyan">{{ $monthAttendanceCount }} Hari Hadir</span>
                </div>
                <div class="metric-main-value">{{ $effectiveAttendanceCount }} <span style="font-size: 16px; font-weight: 500; color: var(--text-muted);">Hari</span></div>
                <div class="metric-sub-detail">
                    <span>Tepat Waktu: <strong style="color: #34d399;">{{ $onTimeCount }} Hari</strong></span>
                    <span>Terlambat: <strong style="color: #f87171;">{{ $lateCount }} Kali</strong></span>
                </div>
            </div>

            <!-- 5. Metrik Penjualan -->
            <div class="metric-highlight-card card-penjualan">
                <div class="metric-card-top">
                    <div class="metric-card-title">
                        <span class="metric-icon-box icon-box-penjualan">💳</span>
                        5. Pendapatan Penjualan
                    </div>
                    <span class="pill-badge pill-emerald">{{ $totalSalesCount }} Transaksi POS</span>
                </div>
                <div class="metric-main-value">Rp {{ number_format($totalSalesRevenue, 0, ',', '.') }}</div>
                <div class="metric-sub-detail">
                    <span>Billing: <strong>Rp {{ number_format($totalBillingRevenue, 0, ',', '.') }}</strong></span>
                    <span>F&amp;B: <strong>Rp {{ number_format($totalFnBRevenue, 0, ',', '.') }}</strong></span>
                </div>
            </div>

            <!-- 6. Metrik Gaji & Komisi -->
            <div class="metric-highlight-card card-gaji">
                <div class="metric-card-top">
                    <div class="metric-card-title">
                        <span class="metric-icon-box icon-box-gaji">💰</span>
                        6. Estimasi Gaji &amp; Komisi
                    </div>
                    <span class="pill-badge pill-amber">Take Home Pay</span>
                </div>
                <div class="metric-main-value" style="color: #fbbf24;">Rp {{ number_format($totalTakeHomePay, 0, ',', '.') }}</div>
                <div class="metric-sub-detail">
                    <span>Status: <strong style="color: #34d399; text-transform: uppercase;">{{ $commissionStatus }}</strong></span>
                    <span>Periode: <strong>Oktober {{ now()->format('Y') }}</strong></span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             MAIN DASHBOARD CONTENT (2 COLUMNS)
             ======================================================== -->
        <div class="dashboard-main-grid">

            <!-- LEFT COLUMN: PRESENSI & TRANSAKSI PENJUALAN -->
            <div class="main-left-col">

                <!-- 4. KOLOM JUMLAH PRESENSI -->
                <div class="section-card" id="cardPresensi">
                    <div class="section-header">
                        <div class="section-title">
                            <span class="icon">📋</span>
                            <span>4. Kolom Jumlah &amp; Riwayat Presensi Staff</span>
                        </div>
                        <a href="{{ route('presensi2.scan') }}" class="pill-badge pill-cyan" style="text-decoration: none;">
                            + Catat Presensi
                        </a>
                    </div>

                    <!-- Mini KPI Presensi Row -->
                    <div class="presensi-stats-row">
                        <div class="presensi-stat-mini">
                            <div class="label">Total Hadir</div>
                            <div class="val" style="color: #38bdf8;">{{ $effectiveAttendanceCount }}</div>
                        </div>
                        <div class="presensi-stat-mini">
                            <div class="label">Tepat Waktu</div>
                            <div class="val" style="color: #34d399;">{{ $onTimeCount }}</div>
                        </div>
                        <div class="presensi-stat-mini">
                            <div class="label">Terlambat</div>
                            <div class="val" style="color: #f87171;">{{ $lateCount }}</div>
                        </div>
                        <div class="presensi-stat-mini">
                            <div class="label">Lembur (Jam)</div>
                            <div class="val" style="color: #fbbf24;">{{ $totalOvertimeHours }}h</div>
                        </div>
                    </div>

                    <!-- Tabel Log Presensi Terakhir -->
                    <div style="overflow-x: auto;">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Tanggal Kerja</th>
                                    <th>Shift</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendances as $rec)
                                    <tr>
                                        <td>
                                            <strong style="color: #ffffff;">{{ \Illuminate\Support\Carbon::parse($rec->work_date)->translatedFormat('d M Y') }}</strong>
                                        </td>
                                        <td>
                                            <span style="font-size: 11.5px; color: var(--text-muted);">
                                                {{ $rec->shift->name ?? 'Shift 2 (Pagi)' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-family: 'JetBrains Mono', monospace; color: #38bdf8;">
                                                {{ $rec->clock_in_at ? \Illuminate\Support\Carbon::parse($rec->clock_in_at)->format('H:i') : '--:--' }}
                                            </span>
                                            @if($rec->late_minutes > 0)
                                                <small style="color: #f87171; font-size: 10px;">(+{{ $rec->late_minutes }}m)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-family: 'JetBrains Mono', monospace; color: #cbd5e1;">
                                                {{ $rec->clock_out_at ? \Illuminate\Support\Carbon::parse($rec->clock_out_at)->format('H:i') : '--:--' }}
                                            </span>
                                        </td>
                                        <td style="font-family: 'JetBrains Mono', monospace;">
                                            {{ $rec->duration_minutes ? floor($rec->duration_minutes / 60) . 'j ' . ($rec->duration_minutes % 60) . 'm' : '6j 0m' }}
                                        </td>
                                        <td>
                                            @if($rec->late_minutes > 0)
                                                <span class="pill-badge pill-danger">Terlambat</span>
                                            @else
                                                <span class="pill-badge pill-emerald">Tepat Waktu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                            Belum ada log catatan presensi bulan berjalan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 5. KOLOM PENDAPATAN DALAM PENJUALAN -->
                <div class="section-card" id="cardPenjualan">
                    <div class="section-header">
                        <div class="section-title">
                            <span class="icon">🛒</span>
                            <span>5. Kolom Pendapatan Dalam Penjualan (Kasir POS)</span>
                        </div>
                        <span class="pill-badge pill-emerald">Omzet Kasir</span>
                    </div>

                    <!-- Progress Breakdown Bar -->
                    @php
                        $totRev = $totalSalesRevenue > 0 ? $totalSalesRevenue : 1;
                        $pctBill = round(($totalBillingRevenue / $totRev) * 100);
                        $pctFnb = 100 - $pctBill;
                    @endphp
                    <div class="sales-breakdown-bar">
                        <div class="bar-billing" style="width: {{ $pctBill }}%;" title="Billing Internet ({{ $pctBill }}%)"></div>
                        <div class="bar-fnb" style="width: {{ $pctFnb }}%;" title="F&amp;B Cafe ({{ $pctFnb }}%)"></div>
                    </div>

                    <div class="sales-legend">
                        <div class="legend-item">
                            <div class="legend-dot" style="background: var(--cyan);"></div>
                            <span>Sewa Billing Internet: <strong>Rp {{ number_format($totalBillingRevenue, 0, ',', '.') }}</strong> ({{ $pctBill }}%)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot" style="background: var(--primary);"></div>
                            <span>F&amp;B Cafe &amp; Warmindo: <strong>Rp {{ number_format($totalFnBRevenue, 0, ',', '.') }}</strong> ({{ $pctFnb }}%)</span>
                        </div>
                    </div>

                    <!-- Tabel Transaksi Penjualan Kasir Terakhir -->
                    <div style="overflow-x: auto;">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Invoice / Waktu</th>
                                    <th>Pelanggan</th>
                                    <th>PC / Meja</th>
                                    <th>Metode</th>
                                    <th>Nilai Transaksi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <div style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #ffffff;">
                                                {{ $sale->invoice_number }}
                                            </div>
                                            <small style="color: var(--text-muted); font-size: 11px;">
                                                {{ $sale->created_at ? $sale->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>{{ $sale->customer_name ?: 'Pelanggan Walk-In' }}</td>
                                        <td>
                                            <span class="pill-badge pill-cyan">
                                                {{ $sale->pcStation->station_code ?? 'PC-0' . rand(1, 9) }}
                                            </span>
                                        </td>
                                        <td style="text-transform: uppercase; font-size: 12px; font-weight: 700; color: #cbd5e1;">
                                            {{ $sale->payment_method ?: 'cash' }}
                                        </td>
                                        <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #34d399;">
                                            Rp {{ number_format($sale->net_amount, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <span class="pill-badge pill-emerald">Lunas</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                            Belum ada transaksi kasir yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: 3. UBAH PASSWORD & 6. GAJI & KOMISI -->
            <div class="main-right-col">

                <!-- 6. GAJI & KOMISI BREAKDOWN -->
                <div class="section-card" id="cardGaji">
                    <div class="section-header">
                        <div class="section-title">
                            <span class="icon">💵</span>
                            <span>6. Rincian Slip Gaji &amp; Komisi</span>
                        </div>
                        <span class="pill-badge pill-amber">Oktober {{ now()->format('Y') }}</span>
                    </div>

                    <div class="salary-summary-box">
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; text-transform: uppercase; font-weight: 700;">
                            Take Home Pay (Estimasi Bersih)
                        </div>
                        <div style="font-size: 28px; font-weight: 800; color: #fbbf24; font-family: 'JetBrains Mono', monospace;">
                            Rp {{ number_format($totalTakeHomePay, 0, ',', '.') }}
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px;">
                            <span style="color: #cbd5e1;">Status Pembayaran:</span>
                            <span class="pill-badge pill-emerald">✅ Disetujui Manager</span>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <div class="salary-row">
                            <span style="color: #cbd5e1;">Gaji Pokok Staff Operasional:</span>
                            <strong style="color: #ffffff; font-family: 'JetBrains Mono', monospace;">Rp {{ number_format($baseSalary, 0, ',', '.') }}</strong>
                        </div>
                        <div class="salary-row">
                            <span style="color: #cbd5e1;">Komisi F&amp;B ({{ $fnbCommissionRate }}% dari Rp {{ number_format($totalFnBRevenue, 0, ',', '.') }}):</span>
                            <strong style="color: #34d399; font-family: 'JetBrains Mono', monospace;">+ Rp {{ number_format($fnbCommissionAmount, 0, ',', '.') }}</strong>
                        </div>
                        <div class="salary-row">
                            <span style="color: #cbd5e1;">Komisi Billing ({{ $billingCommissionRate }}% dari Rp {{ number_format($totalBillingRevenue, 0, ',', '.') }}):</span>
                            <strong style="color: #38bdf8; font-family: 'JetBrains Mono', monospace;">+ Rp {{ number_format($billingCommissionAmount, 0, ',', '.') }}</strong>
                        </div>
                        <div class="salary-row">
                            <span style="color: #cbd5e1;">Bonus Shift &amp; Kedisiplinan:</span>
                            <strong style="color: #fbbf24; font-family: 'JetBrains Mono', monospace;">+ Rp {{ number_format($shiftBonusAmount, 0, ',', '.') }}</strong>
                        </div>
                        @if($overtimePay > 0)
                            <div class="salary-row">
                                <span style="color: #cbd5e1;">Uang Lembur ({{ $totalOvertimeHours }} Jam):</span>
                                <strong style="color: #a78bfa; font-family: 'JetBrains Mono', monospace;">+ Rp {{ number_format($overtimePay, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                        <div class="salary-row total">
                            <span>Total Gaji &amp; Komisi:</span>
                            <span>Rp {{ number_format($totalTakeHomePay, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="slip-print-box">
                        <button type="button" class="btn-action-primary" style="width: 100%; margin-top: 14px;" onclick="window.print()">
                            <span>📄</span> Cetak Slip Gaji Resmi
                        </button>
                    </div>
                </div>

                <!-- 3. BISA UBAH PASSWORD FORM -->
                <div class="section-card" id="cardUbahPassword">
                    <div class="section-header">
                        <div class="section-title">
                            <span class="icon">🔒</span>
                            <span>3. Ubah Kata Sandi &amp; PIN Staff</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.staff.password.update') }}" class="password-form-wrap">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                        <div class="form-group">
                            <label class="form-label" for="current_password">
                                Kata Sandi Saat Ini (Opsional)
                            </label>
                            <div class="form-input-wrap">
                                <input 
                                    type="password" 
                                    name="current_password" 
                                    id="current_password" 
                                    class="form-input" 
                                    placeholder="Masukkan sandi saat ini..."
                                >
                                <button type="button" class="toggle-pwd-btn" onclick="toggleInputVisibility('current_password')">👁️</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="new_password">
                                Kata Sandi / PIN Baru <span style="color: #f87171;">*</span>
                            </label>
                            <div class="form-input-wrap">
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    id="new_password" 
                                    class="form-input" 
                                    placeholder="Minimal 4 karakter atau digit PIN..." 
                                    required
                                >
                                <button type="button" class="toggle-pwd-btn" onclick="toggleInputVisibility('new_password')">👁️</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="new_password_confirmation">
                                Konfirmasi Kata Sandi Baru <span style="color: #f87171;">*</span>
                            </label>
                            <div class="form-input-wrap">
                                <input 
                                    type="password" 
                                    name="new_password_confirmation" 
                                    id="new_password_confirmation" 
                                    class="form-input" 
                                    placeholder="Ulangi kata sandi baru..." 
                                    required
                                >
                                <button type="button" class="toggle-pwd-btn" onclick="toggleInputVisibility('new_password_confirmation')">👁️</button>
                            </div>
                        </div>

                    
                        <button type="submit" class="btn-action-primary" style="width: 100%; margin-top: 6px;">
                            <span>💾</span> Simpan Perubahan Password
                        </button>
                    </form>
                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <footer class="dashboard-footer">
            <p>&copy; 2026 Maya Graha Kencana ERP &bull; Portal Staff &bull; Server: 192.168.5.10:8086/bagus</p>
        </footer>

    </div>

    <!-- JavaScript Interactive Helpers -->
    <script>
        function toggleInputVisibility(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        function focusPasswordForm() {
            const pwdCard = document.getElementById('cardUbahPassword');
            if (pwdCard) {
                pwdCard.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => {
                    const newPwd = document.getElementById('new_password');
                    if (newPwd) newPwd.focus();
                }, 500);
            }
        }
    </script>
</body>
</html>
