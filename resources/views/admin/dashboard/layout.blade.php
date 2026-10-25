<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin Terpadu') — Maya Graha Kencana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg-body: #0b1120;
            --bg-surface: #0f172a;
            --bg-card: #1e293b;
            --bg-card-sub: #172033;
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(56, 189, 248, 0.25);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-sub: #64748b;
            --primary: #38bdf8;
            --primary-hover: #0284c7;
            --emerald: #10b981;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --indigo: #6366f1;
            --cyan: #06b6d4;
            --sidebar-width: 270px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* SIDEBAR */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-surface);
            border-right: 1px solid var(--border-glass);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-glass);
            background: rgba(15, 23, 42, 0.95);
        }

        .brand-logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
            flex-shrink: 0;
        }

        .brand-text-title {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .brand-text-sub {
            font-size: 11px;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-glass);
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(30, 41, 59, 0.4);
        }

        .user-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            color: #fff;
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, 0.15);
        }

        .user-meta-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
        }

        .user-meta-badge {
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--emerald);
            font-weight: 600;
            margin-top: 3px;
        }

        .sidebar-nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }

        .nav-category-header {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-sub);
            padding: 8px 12px 6px;
        }

        .sidebar-nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .nav-link-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .nav-link-item.active {
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.15), rgba(56, 189, 248, 0.03));
            color: #38bdf8;
            font-weight: 600;
            border-left: 3px solid #38bdf8;
        }

        .nav-item-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-sub-list {
            list-style: none;
            padding-left: 28px;
            margin-top: 2px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-sub-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            font-size: 12px;
            color: var(--text-sub);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .nav-sub-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-sub-link.active {
            color: #38bdf8;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border-glass);
            background: rgba(15, 23, 42, 0.8);
        }

        .btn-sidebar-logout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.25);
            color: #fb7185;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-sidebar-logout:hover {
            background: #f43f5e;
            color: #fff;
            border-color: #f43f5e;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.35);
        }

        /* MAIN CONTENT AREA */
        .admin-main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* TOPBAR */
        .admin-topbar {
            height: 64px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-glass);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .page-badge-location {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-badge-location strong {
            color: #fff;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-chip {
            padding: 6px 12px;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-portal-btn {
            padding: 6px 14px;
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #38bdf8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .topbar-portal-btn:hover {
            background: #38bdf8;
            color: #0b1120;
        }

        /* CONTENT CONTAINER */
        .admin-content-body {
            padding: 26px 28px 60px;
            flex: 1;
        }

        /* HERO CARD */
        .hero-banner-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.95));
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 18px;
            position: relative;
            overflow: hidden;
        }

        .hero-banner-card::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-title-text {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .hero-desc-text {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 650px;
            line-height: 1.5;
        }

        /* TOP METRIC STATS GRID */
        .stats-grid-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 1100px) {
            .stats-grid-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .stats-grid-row {
                grid-template-columns: 1fr;
            }
        }

        .metric-stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            padding: 18px 20px;
            position: relative;
            transition: transform 0.2s, border-color 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .metric-stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--border-highlight);
        }

        .metric-stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .metric-stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .metric-stat-value {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }

        .metric-stat-desc {
            font-size: 11px;
            color: var(--text-sub);
            margin-top: 6px;
        }

        /* SECTION CARDS */
        .dashboard-section-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-glass);
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .section-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .section-subtitle {
            font-size: 12px;
            color: var(--text-muted);
        }

        .section-card-body {
            padding: 22px 24px;
        }

        /* TAB NAVIGATION PILLS */
        .tab-nav-pills {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-glass);
            padding: 4px;
            border-radius: 10px;
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .tab-pill-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            text-decoration: none;
        }

        .tab-pill-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .tab-pill-btn.active {
            background: #38bdf8;
            color: #0b1120;
            box-shadow: 0 2px 8px rgba(56, 189, 248, 0.3);
        }

        /* TABLES */
        .custom-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .custom-data-table th {
            padding: 12px 14px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-glass);
            background: rgba(15, 23, 42, 0.3);
        }

        .custom-data-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-main);
            vertical-align: middle;
        }

        .custom-data-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* BADGES */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-emerald {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-sky {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .badge-amber {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-rose {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        /* SOP / ACCORDION CARDS */
        .sop-grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 16px;
        }

        .sop-card-item {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 18px 20px;
            transition: all 0.2s;
        }

        .sop-card-item:hover {
            border-color: var(--border-highlight);
            transform: translateY(-2px);
        }

        .sop-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .sop-code-badge {
            font-family: 'Outfit', monospace;
            font-size: 12px;
            font-weight: 700;
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.12);
            padding: 2px 8px;
            border-radius: 6px;
        }

        .sop-title-text {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .sop-summary-text {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .sop-step-list {
            padding-left: 18px;
            font-size: 12px;
            color: #cbd5e1;
            line-height: 1.6;
        }

        /* BUTTONS */
        .btn-action-sm {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-action-primary {
            background: #38bdf8;
            color: #0b1120;
        }

        .btn-action-primary:hover {
            background: #0284c7;
            color: #fff;
        }

        .btn-action-emerald {
            background: #10b981;
            color: #fff;
        }

        .btn-action-emerald:hover {
            background: #059669;
        }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(6px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 24px 28px;
            max-width: 440px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        /* ERP SYNCHRONIZED SUBNAVBAR */
        .erp-dashboard-subnav {
            background: linear-gradient(to right, #0284c7, #0ea5e9) !important;
            min-height: 38px !important;
            display: flex !important;
            align-items: center !important;
            overflow-x: auto !important;
            white-space: nowrap !important;
            padding: 0 12px !important;
            gap: 4px !important;
            border-bottom: 1px solid rgba(0,0,0,0.15) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 95 !important;
            scrollbar-width: thin !important;
            scrollbar-color: rgba(255,255,255,0.4) transparent !important;
        }
        .erp-dashboard-subnav::-webkit-scrollbar { height: 4px; }
        .erp-dashboard-subnav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); border-radius: 4px; }
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
            background: rgba(12, 74, 110, 0.8) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35) !important;
        }

        /* ERP MODAL STYLES */
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
        .erp-modal-overlay.active { display: flex; }
        .erp-modal-card {
            background: #ffffff;
            width: 90%;
            max-width: 520px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border-top: 4px solid #0284c7;
            animation: erpModalIn 0.2s ease-out;
            color: #334155;
            text-align: left;
        }
        .erp-modal-header {
            padding: 14px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .erp-modal-title { font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .erp-modal-close-btn { background: transparent; border: none; font-size: 20px; color: #64748b; cursor: pointer; padding: 0 6px; }
        .erp-modal-body { padding: 18px; font-size: 13px; color: #334155; line-height: 1.5; }
        .erp-modal-footer { padding: 12px 18px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px; }
        .erp-modal-btn { padding: 7px 14px; border-radius: 4px; font-size: 12.5px; font-weight: 600; cursor: pointer; border: 1px solid transparent; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    </style>
    @yield('extra-styles')
</head>
<body>

    <!-- SIDEBAR UTAMA ADMIN -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-icon">🏢</div>
            <div>
                <div class="brand-text-title">Maya Graha Kencana</div>
                <div class="brand-text-sub">ADMIN ERP TERPADU</div>
            </div>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar-circle">
                {{ substr($profileData['name'] ?? 'Bagus Satoto', 0, 1) }}
            </div>
            <div>
                <div class="user-meta-name">{{ $profileData['name'] ?? 'Bagus Satoto' }}</div>
                <div class="user-meta-badge">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    {{ $profileData['role'] ?? 'Manager' }} &bull; Online
                </div>
            </div>
        </div>

        <div class="sidebar-nav-container">
            <div class="nav-category-header">MENU UTAMA DASHBOARD</div>
            <ul class="sidebar-menu sidebar-nav-list">

                {{-- DASHBOARD OVERVIEW --}}
                <li>
                    <a href="{{ route('admin.index') }}" class="nav-link-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <span class="nav-item-left">
                            <span>🏠</span> <span>Dashboard Utama</span>
                        </span>
                    </a>
                </li>

                {{-- 1. DATA (DENGAN 6 SUB-ITEM) --}}
                <li>
                    <a href="{{ route('admin.data') }}" class="nav-link-item {{ request()->routeIs('admin.data') ? 'active' : '' }}" onclick="toggleSubMenu('data-submenu', event)">
                        <span class="nav-item-left">
                            <span>🗄️</span> <span>1. Data Terpadu</span>
                        </span>
                        <span style="font-size: 11px;">▾</span>
                    </a>
                    <ul class="nav-sub-list" id="data-submenu" style="{{ request()->routeIs('admin.data') || request()->routeIs('admin.karyawan.verifikasi') || request()->routeIs('admin.karyawan.reset-password') ? 'display: flex;' : 'display: none;' }}">
                        <li><a href="{{ route('admin.data', ['tab' => 'tab-profil']) }}" class="nav-sub-link {{ request('tab') == 'tab-profil' || (!request('tab') && request()->routeIs('admin.data')) ? 'active' : '' }}"><span>👤</span> Profil & Karyawan</a></li>
                        <li><a href="{{ route('admin.karyawan.verifikasi') }}" class="nav-sub-link {{ request()->routeIs('admin.karyawan.verifikasi') ? 'active' : '' }}"><span>🛡️</span> Verifikasi Pendaftaran Karyawan</a></li>
                        <li><a href="{{ route('admin.karyawan.reset-password') }}" class="nav-sub-link {{ request()->routeIs('admin.karyawan.reset-password') ? 'active' : '' }}"><span>🔑</span> Otorisasi Reset Password/PIN</a></li>
                        <li><a href="{{ route('admin.data', ['tab' => 'tab-jadwal']) }}" class="nav-sub-link {{ request('tab') == 'tab-jadwal' ? 'active' : '' }}"><span>📅</span> Jadwal Shift Karyawan</a></li>
                        <li><a href="{{ route('admin.data', ['tab' => 'tab-billing']) }}" class="nav-sub-link {{ request('tab') == 'tab-billing' ? 'active' : '' }}"><span>💰</span> Data Billing Setiap Shift</a></li>
                        <li><a href="{{ route('admin.data', ['tab' => 'tab-presensi']) }}" class="nav-sub-link {{ request('tab') == 'tab-presensi' ? 'active' : '' }}"><span>👥</span> Presensi Karyawan</a></li>
                        <li><a href="{{ route('admin.data', ['tab' => 'tab-jamkerja']) }}" class="nav-sub-link {{ request('tab') == 'tab-jamkerja' ? 'active' : '' }}"><span>⏱️</span> Jam Kerja Karyawan</a></li>
                        <li><a href="{{ route('admin.data', ['tab' => 'tab-sop']) }}" class="nav-sub-link {{ request('tab') == 'tab-sop' ? 'active' : '' }}"><span>📜</span> SOP Operasional</a></li>
                    </ul>
                </li>

                {{-- 2. PENERIMAAN STOK DISTRIBUSI --}}
                <li>
                    <a href="{{ route('admin.distribusi') }}" class="nav-link-item {{ request()->routeIs('admin.distribusi') ? 'active' : '' }}">
                        <span class="nav-item-left">
                            <span>🚚</span> <span>2. Penerimaan Distribusi</span>
                        </span>
                        @if(isset($stockTransfersCount) && $stockTransfersCount > 0)
                        <span class="badge-status badge-amber" style="font-size: 10px; padding: 2px 7px;">
                            {{ $stockTransfersCount }} Baru
                        </span>
                        @endif
                    </a>
                </li>

                {{-- 3. STOK TERDIRI KETERSEDIAAN PRODUK --}}
                <li>
                    <a href="{{ route('admin.stok') }}" class="nav-link-item {{ request()->routeIs('admin.stok') ? 'active' : '' }}">
                        <span class="nav-item-left">
                            <span>📦</span> <span>3. Stok (Ketersediaan)</span>
                        </span>
                        @if(isset($stockStatsTotalSku))
                        <span class="badge-status badge-emerald" style="font-size: 10px; padding: 2px 7px;">
                            {{ $stockStatsTotalSku }} SKU
                        </span>
                        @endif
                    </a>
                </li>

                {{-- 4. LAPORAN --}}
                <li>
                    <a href="{{ route('admin.laporan') }}" class="nav-link-item {{ request()->routeIs('admin.laporan') ? 'active' : '' }}">
                        <span class="nav-item-left">
                            <span>📊</span> <span>4. Laporan Keuangan</span>
                        </span>
                    </a>
                </li>

                {{-- 5. KELUAR --}}
                <li>
                    <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-link-item" style="color: #fb7185;">
                        <span class="nav-item-left">
                            <span>🚪</span> <span>5. Keluar</span>
                        </span>
                    </a>
                </li>
            </ul>

            <div class="nav-category-header" style="margin-top: 16px;">PINTASAN SISTEM</div>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="{{ route('presensi2.index') }}" target="_blank" class="nav-link-item">
                        <span class="nav-item-left"><span>📱</span> <span>Portal Presensi v2</span></span>
                        <span style="font-size: 11px;">↗</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('presensi2.stok') }}" target="_blank" class="nav-link-item">
                        <span class="nav-item-left"><span>📋</span> <span>Checklist Stok Kasir</span></span>
                        <span style="font-size: 11px;">↗</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('presensi2.penjualan') }}" target="_blank" class="nav-link-item">
                        <span class="nav-item-left"><span>🛒</span> <span>POS Kasir & Billing</span></span>
                        <span style="font-size: 11px;">↗</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <button onclick="openLogoutModal()" class="btn-sidebar-logout">
                <span>🚪</span> Keluar Sesi Admin
            </button>
        </div>
    </aside>

    <!-- WRAPPER UTAMA -->
    <div class="admin-main-wrapper">
        <!-- TOPBAR -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <div class="page-badge-location">
                    <span>Cabang:</span>
                    <strong>{{ $profileData['branch_name'] ?? 'MGK Pusat' }} ({{ $profileData['branch_code'] ?? 'MGK-PST' }})</strong>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-chip">
                    <span>🕒</span>
                    <span id="liveClock">{{ $profileData['server_time'] ?? date('H:i:s').' WIB' }}</span>
                </div>
                <a href="{{ route('presensi2.index') }}" target="_blank" class="topbar-portal-btn">
                    <span>📱 Buka Portal Presensi</span>
                </a>
            </div>
        </header>

        <!-- BODY CONTENT -->
        <main class="admin-content-body">
            @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; color: #34d399; font-size: 13px; display: flex; align-items: center; gap: 10px;">
                <span>✅</span> <div>{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; color: #fb7185; font-size: 13px; display: flex; align-items: center; gap: 10px;">
                <span>⚠️</span> <div>{{ session('error') }}</div>
            </div>
            @endif

            @if(isset($errors) && $errors->any())
            <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; color: #fb7185; font-size: 13px;">
                <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> Terdapat kesalahan validasi formulir:
                </div>
                <ul style="margin: 0; padding-left: 24px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- MODAL LOGOUT -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-box">
            <div style="font-size: 38px; margin-bottom: 12px;">🚪</div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">Keluar dari Sesi Admin?</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 22px;">
                Sesi otorisasi administratif Anda akan diakhiri. Anda perlu memasukkan kredensial login kembali untuk membuka akses.
            </p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="closeLogoutModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 10px 18px; font-size: 13px;">
                    Batal
                </button>
                <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-action-sm" style="background: #f43f5e; color: #fff; padding: 10px 20px; font-size: 13px; font-weight: 700;">
                        Ya, Keluar Sesi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 1: DATA AC & FASILITAS OUTLET -->
    <div id="dataAcModal" class="erp-modal-overlay">
        <div class="erp-modal-card">
            <div class="erp-modal-header">
                <div class="erp-modal-title"><span>❄️</span> Monitoring & Data AC Outlet Maya Graha Kencana</div>
                <button type="button" class="erp-modal-close-btn" onclick="closeErpModal('dataAcModal')">&times;</button>
            </div>
            <div class="erp-modal-body">
                <p style="margin-bottom: 12px; color: #64748b; font-size: 12px;">Daftar unit pendingin udara (AC) & jadwal servis rutin cabang MGK Pusat:</p>
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
                <div class="erp-modal-title" style="color: #b91c1c;"><span>🛑</span> Stop Code & Protokol Otorisasi Darurat Kasir</div>
                <button type="button" class="erp-modal-close-btn" onclick="closeErpModal('stopCodeModal')">&times;</button>
            </div>
            <div class="erp-modal-body">
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-weight: 700; color: #991b1b; font-size: 13px;">Kode Stop Darurat Aktif:</span>
                        <span style="background: #dc2626; color: #fff; padding: 3px 8px; border-radius: 4px; font-family: monospace; font-weight: 700; font-size: 14px; letter-spacing: 2px;">
                            STOP-MGK-2026
                        </span>
                    </div>
                    <div style="font-size: 11px; color: #7f1d1d;">Hanya dapat digunakan oleh Manager (Bagus) atau SPV Shift bertugas.</div>
                </div>
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
                <div class="erp-modal-title" style="color: #b45309;"><span>⭐</span> Rating Kepuasan Pelanggan & Kualitas Layanan</div>
                <button type="button" class="erp-modal-close-btn" onclick="closeErpModal('ratingModal')">&times;</button>
            </div>
            <div class="erp-modal-body">
                <div style="text-align: center; padding: 10px 0 16px;">
                    <div style="font-size: 40px; font-weight: 800; color: #f59e0b; line-height: 1;">4.9</div>
                    <div style="font-size: 18px; color: #f59e0b; margin: 4px 0;">⭐⭐⭐⭐⭐</div>
                    <div style="font-size: 11.5px; color: #64748b;">Berdasarkan 328 rating pelanggan bulan ini</div>
                </div>
            </div>
            <div class="erp-modal-footer">
                <button type="button" class="erp-modal-btn" style="background: #0284c7; color: #ffffff;" onclick="closeErpModal('ratingModal')">Selesai</button>
            </div>
        </div>
    </div>

    @yield('modals')

    <script>
        function openErpModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('active');
        }
        function closeErpModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('active');
        }
        // Toggle sub menu in sidebar
        function toggleSubMenu(id, e) {
            if (e && e.target && e.target.classList.contains('nav-sub-link')) {
                return;
            }
            const sub = document.getElementById(id);
            if (sub) {
                sub.style.display = sub.style.display === 'none' ? 'flex' : 'none';
            }
        }

        // Modal Logout
        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        // Live Clock Tick
        setInterval(() => {
            const now = new Date();
            const clockEl = document.getElementById('liveClock');
            if (clockEl) {
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                clockEl.textContent = now.toLocaleDateString('id-ID', options) + ' WIB';
            }
        }, 1000);
    </script>
    @yield('scripts')
</body>
</html>
