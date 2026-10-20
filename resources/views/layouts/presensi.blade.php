<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MGK Presensi">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico">
    <title>@yield('title', 'Presensi Karyawan') — Maya Graha Kencana</title>

    <!-- Google Fonts Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg-primary: #0b1120;
            --bg-surface: #131d33;
            --bg-card: rgba(30, 41, 59, 0.7);
            --bg-card-hover: rgba(51, 65, 85, 0.8);
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-active: rgba(99, 102, 241, 0.4);
            --accent-primary: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.25);
            --accent-emerald: #10b981;
            --accent-emerald-glow: rgba(16, 185, 129, 0.25);
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --accent-cyan: #06b6d4;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-sub: #64748b;
            --bottom-nav-height: 72px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.5) 0px, transparent 100%);
            background-attachment: fixed;
        }

        h1, h2, h3, h4, .brand-font {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
        }

        /* TOP APP BAR */
        .mobile-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-glass);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .brand-text h1 {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
            color: #fff;
        }

        .brand-text span {
            font-size: 11px;
            color: var(--text-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid var(--border-glass);
            font-size: 12px;
            font-weight: 500;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .header-user-badge:hover {
            background: rgba(51, 65, 85, 0.8);
            border-color: var(--border-active);
        }

        .user-dot-online {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--accent-emerald);
            box-shadow: 0 0 8px var(--accent-emerald);
        }

        /* CONTAINER */
        .mobile-container {
            flex: 1;
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            padding: 16px 16px calc(var(--bottom-nav-height) + 24px) 16px;
        }

        /* BOTTOM NAVIGATION BAR */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--bottom-nav-height);
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-glass);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1000;
            padding: 0 8px max(env(safe-area-inset-bottom), 6px) 8px;
            max-width: 820px;
            margin: 0 auto;
            border-radius: 20px 20px 0 0;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-sub);
            padding: 8px 12px;
            border-radius: 12px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            min-width: 64px;
        }

        .nav-item .nav-icon {
            font-size: 20px;
            margin-bottom: 3px;
            transition: transform 0.2s ease;
        }

        .nav-item .nav-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .nav-item.active {
            color: #fff;
        }

        .nav-item.active .nav-icon {
            color: var(--accent-primary);
            transform: translateY(-2px);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            top: 2px;
            width: 16px;
            height: 3px;
            border-radius: 999px;
            background: var(--accent-primary);
            box-shadow: 0 0 10px var(--accent-primary);
        }

        /* FAB Scan Button */
        .nav-item-fab {
            position: relative;
            transform: translateY(-16px);
        }

        .nav-fab-btn {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #3b82f6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.45);
            border: 3px solid rgba(15, 23, 42, 0.95);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
        }

        .nav-fab-btn:active {
            transform: scale(0.92);
        }

        .nav-item-fab.active .nav-fab-btn {
            background: linear-gradient(135deg, #10b981, #06b6d4);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45);
        }

        .nav-item-fab .nav-label {
            margin-top: 4px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .nav-item-fab.active .nav-label {
            color: #fff;
        }

        /* CARD UI COMPONENT */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-glass);
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        /* BADGES */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-present { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-late { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-absent { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
        .badge-leave { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
        }
        .btn-primary:active { transform: scale(0.98); }

        .btn-emerald {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
        }
        .btn-emerald:active { transform: scale(0.98); }

        .btn-amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            border: 1px solid var(--border-glass);
        }
        .btn-ghost:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        /* ALERT MESSAGES */
        .alert-box {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7; }
        .alert-error { background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: #fda4af; }
        .alert-info { background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); color: #a5b4fc; }

        @media (min-width: 640px) {
            .mobile-bottom-nav {
                border-left: 1px solid var(--border-glass);
                border-right: 1px solid var(--border-glass);
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- TOP BAR -->
    <header class="mobile-header">
        <a href="{{ route('presensi.index') }}" class="header-brand" id="brand-link">
            <div class="brand-logo-icon">🏢</div>
            <div class="brand-text">
                <h1>MGK Presensi</h1>
                <span>Maya Graha Kencana</span>
            </div>
        </a>

        @if(session()->has('presensi_employee_id') && isset($currentEmployee))
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="{{ route('presensi.profile') }}" class="header-user-badge" id="current-user-badge" title="Profil Karyawan">
                    <span class="user-dot-online"></span>
                    <span>{{ Str::limit($currentEmployee->name, 12) }}</span>
                </a>
                <a href="{{ route('presensi.logout') }}" class="header-user-badge" id="header-logout-btn" title="Keluar dari sesi" style="color: #fb7185; border-color: rgba(244, 63, 94, 0.3); padding: 6px 10px; font-weight: 600;">
                    <span>🚪 Keluar</span>
                </a>
            </div>
        @else
            <a href="{{ route('presensi.login') }}" class="header-user-badge" id="login-nav-btn">
                <span>🔑 Masuk</span>
            </a>
        @endif
    </header>

    <!-- CONTENT -->
    <main class="mobile-container">
        @if(session('success'))
            <div class="alert-box alert-success" id="flash-success">
                <span>✅</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error" id="flash-error">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- BOTTOM NAV BAR -->
    <nav class="mobile-bottom-nav" id="mobile-bottom-nav">
        <a href="{{ route('presensi.index') }}" class="nav-item {{ request()->routeIs('presensi.index') ? 'active' : '' }}" id="nav-btn-home">
            <span class="nav-icon">🏠</span>
            <span class="nav-label">Beranda</span>
        </a>

        <a href="{{ route('presensi.izin') }}" class="nav-item {{ request()->routeIs('presensi.izin') ? 'active' : '' }}" id="nav-btn-izin">
            <span class="nav-icon">📝</span>
            <span class="nav-label">Izin / Cuti</span>
        </a>

        <!-- FAB SCAN BUTTON -->
        <a href="{{ route('presensi.scan') }}" class="nav-item nav-item-fab {{ request()->routeIs('presensi.scan') ? 'active' : '' }}" id="nav-btn-scan">
            <div class="nav-fab-btn">📸</div>
            <span class="nav-label">Scan</span>
        </a>

        <a href="{{ route('presensi.riwayat') }}" class="nav-item {{ request()->routeIs('presensi.riwayat') ? 'active' : '' }}" id="nav-btn-riwayat">
            <span class="nav-icon">📅</span>
            <span class="nav-label">Riwayat</span>
        </a>

        <a href="{{ route('presensi.profile') }}" class="nav-item {{ request()->routeIs('presensi.profile') ? 'active' : '' }}" id="nav-btn-profile">
            <span class="nav-icon">👤</span>
            <span class="nav-label">Profil</span>
        </a>
    </nav>

    <script>
        // Register PWA Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration error:', err);
                });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
