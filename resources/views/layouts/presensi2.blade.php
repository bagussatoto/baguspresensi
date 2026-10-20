<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0a0e1a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Presensi v2">
    <title>@yield('title', 'Presensi Karyawan') — MGK v2</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg-primary: #0a0e1a;
            --bg-surface: #111827;
            --bg-card: rgba(17, 24, 39, 0.85);
            --bg-card-hover: rgba(31, 41, 55, 0.9);
            --border-glass: rgba(255, 255, 255, 0.07);
            --border-active: rgba(139, 92, 246, 0.5);
            --accent-primary: #8b5cf6;
            --accent-glow: rgba(139, 92, 246, 0.25);
            --accent-emerald: #10b981;
            --accent-emerald-glow: rgba(16, 185, 129, 0.2);
            --accent-amber: #f59e0b;
            --accent-rose: #ef4444;
            --accent-cyan: #06b6d4;
            --text-main: #f1f5f9;
            --text-muted: #9ca3af;
            --text-sub: #6b7280;
            --bottom-nav-height: 70px;
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
                radial-gradient(at 20% 0%, rgba(139, 92, 246, 0.12) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(6, 182, 212, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(10, 14, 26, 0.6) 0px, transparent 100%);
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
            background: rgba(10, 14, 26, 0.88);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
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
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);
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
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid var(--border-glass);
            font-size: 12px;
            font-weight: 500;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .header-user-badge:hover {
            background: rgba(55, 65, 81, 0.8);
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
            background: rgba(10, 14, 26, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-glass);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1000;
            padding: 0 4px max(env(safe-area-inset-bottom), 6px) 4px;
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
            padding: 6px 6px;
            border-radius: 12px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            min-width: 46px;
            flex: 1;
            max-width: 72px;
        }

        .nav-item .nav-icon { font-size: 20px; margin-bottom: 3px; transition: transform 0.2s ease; }
        .nav-item .nav-label { font-size: 11px; font-weight: 600; letter-spacing: -0.01em; }

        .nav-item.active { color: #fff; }
        .nav-item.active .nav-icon { color: var(--accent-primary); transform: translateY(-2px); }
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

        /* FAB Scan */
        .nav-item-fab { position: relative; transform: translateY(-16px); }
        .nav-fab-btn {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.45);
            border: 3px solid rgba(10, 14, 26, 0.95);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
        }
        .nav-fab-btn:active { transform: scale(0.92); }
        .nav-item-fab.active .nav-fab-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.45);
        }
        .nav-item-fab .nav-label { margin-top: 4px; font-size: 11px; font-weight: 600; color: var(--text-muted); }
        .nav-item-fab.active .nav-label { color: #fff; }

        /* GLASS CARD */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-glass);
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35);
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
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
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

        .btn-rose {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
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

        /* FORM INPUTS */
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid var(--border-glass);
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-input::placeholder {
            color: var(--text-sub);
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 8px;
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
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; }
        .alert-info { background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); color: #c4b5fd; }

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

    @if(!request()->is('*login*') && !request()->routeIs('presensi2.login'))
    <!-- TOP BAR -->
    <header class="mobile-header">
        <a href="{{ route('presensi2.index') }}" class="header-brand">
            <div class="brand-logo-icon">🔐</div>
            <div class="brand-text">
                <h1>MGK Presensi v2</h1>
                <span>Verifikasi Kredensial</span>
            </div>
        </a>

        @if(session()->has('presensi2_employee_id') && isset($currentEmployee))
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="header-user-badge">
                    <span class="user-dot-online"></span>
                    <span>{{ Str::limit($currentEmployee->name, 12) }}</span>
                </span>
                <a href="{{ route('presensi2.logout') }}" class="header-user-badge" title="Keluar" style="color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); padding: 6px 10px; font-weight: 600;">
                    <span>🚪</span>
                </a>
            </div>
        @else
            <a href="{{ route('presensi2.login') }}" class="header-user-badge">
                <span>🔑 Masuk</span>
            </a>
        @endif
    </header>
    @endif

    <!-- CONTENT -->
    <main class="mobile-container {{ request()->is('*login*') || request()->routeIs('presensi2.login') ? 'is-login-page' : '' }}">
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

        @if(session('warning'))
            <div class="alert-box" id="flash-warning" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 13px;">
                <span>⚠️</span>
                <div>{{ session('warning') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @if(session()->has('presensi2_employee_id'))
    @php
        $navEmployeeId = session('presensi2_employee_id');
        $navHasClockedIn = false;
        $navHasCompletedStock = false;
        if ($navEmployeeId) {
            $navRecord = \App\Domains\Attendance\Models\AttendanceRecord::where('employee_id', $navEmployeeId)
                ->whereNull('clock_out_at')
                ->latest('id')
                ->first()
                ?: \App\Domains\Attendance\Models\AttendanceRecord::where('employee_id', $navEmployeeId)
                    ->whereDate('work_date', now()->toDateString())
                    ->latest('id')
                    ->first();

            $navHasClockedIn = (bool) ($navRecord && $navRecord->clock_in_at);
            if ($navHasClockedIn) {
                $navHasCompletedStock = \App\Models\ShiftHandoverChecklist::where('employee_id', $navEmployeeId)
                    ->whereDate('check_date', $navRecord->work_date)
                    ->where('status', 'completed')
                    ->exists();
            }
        }

        $navMenus = rescue(fn() => \App\Models\SystemMenu::getActiveByGroup('presensi2'), collect());
        $berandaMenu = $navMenus->firstWhere('code', 'presensi2_beranda');
        $stokMenu = $navMenus->firstWhere('code', 'presensi2_stok');
        $penjualanMenu = $navMenus->firstWhere('code', 'presensi2_penjualan');
        $rekapMenu = $navMenus->firstWhere('code', 'presensi2_rekap');
        $riwayatMenu = $navMenus->firstWhere('code', 'presensi2_riwayat');
    @endphp
    <!-- BOTTOM NAV BAR (TERINTEGRASI DATABASE NAVICAT SYSTEM_MENUS) -->
    <nav class="mobile-bottom-nav">
        <a href="{{ $berandaMenu?->url ?? route('presensi2.index') }}" class="nav-item {{ request()->routeIs('presensi2.index') ? 'active' : '' }}" title="{{ $berandaMenu?->description }}">
            <span class="nav-icon">{{ $berandaMenu?->icon ?? '🏠' }}</span>
            <span class="nav-label">{{ $berandaMenu?->name ?? 'Beranda' }}</span>
        </a>

        <a href="{{ $stokMenu?->url ?? route('presensi2.stok') }}" 
           class="nav-item {{ request()->routeIs('presensi2.stok*') ? 'active' : '' }}" 
           @if(!$navHasClockedIn)
               onclick="event.preventDefault(); showPresensiRequiredModal('stok');"
           @endif
           title="{{ $stokMenu?->description }}">
            <span class="nav-icon">{{ $stokMenu?->icon ?? '📦' }}</span>
            <span class="nav-label">{{ $stokMenu ? explode(' &', $stokMenu->name)[0] : 'Stok' }}</span>
        </a>

        <!-- FAB Presensi -->
        <a href="{{ route('presensi2.scan') }}" class="nav-item nav-item-fab {{ request()->routeIs('presensi2.scan') ? 'active' : '' }}">
            <div class="nav-fab-btn">🔐</div>
            <span class="nav-label">Presensi</span>
        </a>

        <a href="{{ $penjualanMenu?->url ?? route('presensi2.penjualan') }}" 
           class="nav-item {{ request()->routeIs('presensi2.penjualan*') ? 'active' : '' }}" 
           @if(!$navHasClockedIn)
               onclick="event.preventDefault(); showPresensiRequiredModal('penjualan');"
           @elseif(!$navHasCompletedStock)
               onclick="event.preventDefault(); showIncompleteModal('Pemeriksaan Stok Belum Lengkap!', 'Anda belum menyelesaikan pemeriksaan dan serah terima stok barang awal shift. Silakan lengkapi checklist stok terlebih dahulu.', '<span>📦</span> Lengkapi Stok Sekarang', '{{ route('presensi2.stok') }}');"
           @endif
           title="{{ $penjualanMenu?->description }}">
            <span class="nav-icon">{{ $penjualanMenu?->icon ?? '🛒' }}</span>
            <span class="nav-label">{{ $penjualanMenu ? explode(' &', $penjualanMenu->name)[0] : 'Penjualan' }}</span>
        </a>

        <a href="{{ $rekapMenu?->url ?? route('presensi2.rekap') }}" 
           class="nav-item {{ request()->routeIs('presensi2.rekap*') ? 'active' : '' }}" 
           title="{{ $rekapMenu?->description ?? 'Rekap Penjualan & Shift' }}">
            <span class="nav-icon">{{ $rekapMenu?->icon ?? '📊' }}</span>
            <span class="nav-label">{{ $rekapMenu ? explode(' &', $rekapMenu->name)[0] : 'Rekap' }}</span>
        </a>

        <a href="{{ $riwayatMenu?->url ?? route('presensi2.riwayat') }}" class="nav-item {{ request()->routeIs('presensi2.riwayat') ? 'active' : '' }}" title="{{ $riwayatMenu?->description }}">
            <span class="nav-icon">{{ $riwayatMenu?->icon ?? '📅' }}</span>
            <span class="nav-label">{{ $riwayatMenu?->name ?? 'Riwayat' }}</span>
        </a>
    </nav>

    <!-- MODAL NOTIFIKASI INTERAKTIF (PRESENSI DULU / BELUM LENGKAP) -->
    <div id="mgk-notification-modal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 20px;">
        <div class="glass-card" style="max-width: 420px; width: 100%; border-color: rgba(239, 68, 68, 0.4); box-shadow: 0 20px 40px rgba(0,0,0,0.6); text-align: center; padding: 24px 20px; margin-bottom: 0;">
            <div id="mgk-modal-icon" style="width: 60px; height: 60px; border-radius: 18px; background: rgba(239, 68, 68, 0.18); color: #f87171; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px auto; border: 1px solid rgba(239, 68, 68, 0.3);">
                ⚠️
            </div>
            <h3 id="mgk-modal-title" style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">
                Presensi Dulu!
            </h3>
            <p id="mgk-modal-desc" style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 20px;">
                Anda belum melakukan presensi masuk untuk shift hari ini. Silakan klik tombol presensi masuk terlebih dahulu sebelum membuka stok barang.
            </p>
            <div style="display: flex; gap: 10px; flex-direction: column;">
                <a id="mgk-modal-primary-btn" href="{{ route('presensi2.scan') }}" class="btn btn-emerald" style="padding: 13px; font-size: 14px; font-weight: 700;">
                    🔐 Presensi Masuk Sekarang
                </a>
                <button type="button" onclick="closeNotificationModal()" class="btn btn-ghost" style="padding: 11px; font-size: 13px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Auto-dismiss flash messages
        setTimeout(() => {
            document.querySelectorAll('.alert-box').forEach(el => {
                el.style.transition = 'opacity 0.4s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 400);
            });
        }, 4000);

        // Toast notification system
        function showToastNotification(message, type = 'error') {
            const existing = document.getElementById('mgk-toast-notification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'mgk-toast-notification';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.left = '50%';
            toast.style.transform = 'translateX(-50%) translateY(-20px)';
            toast.style.zIndex = '10000';
            toast.style.maxWidth = '90%';
            toast.style.width = '420px';
            toast.style.padding = '12px 18px';
            toast.style.borderRadius = '14px';
            toast.style.background = type === 'error' ? 'rgba(239, 68, 68, 0.95)' : (type === 'warning' ? 'rgba(245, 158, 11, 0.95)' : 'rgba(16, 185, 129, 0.95)');
            toast.style.color = '#fff';
            toast.style.fontSize = '13px';
            toast.style.fontWeight = '600';
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '10px';
            toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
            toast.style.backdropFilter = 'blur(10px)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
            toast.innerHTML = `<span style="font-size: 18px;">${type === 'error' ? '🚫' : (type === 'warning' ? '⚠️' : '✅')}</span> <span style="flex: 1;">${message}</span>`;

            document.body.appendChild(toast);
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(-50%) translateY(0)';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Modal triggers
        function showPresensiRequiredModal(target = 'stok') {
            const modal = document.getElementById('mgk-notification-modal');
            if (!modal) {
                alert('Presensi dulu! Silakan lakukan presensi masuk terlebih dahulu.');
                window.location.href = "{{ route('presensi2.scan') }}";
                return;
            }

            const title = document.getElementById('mgk-modal-title');
            const desc = document.getElementById('mgk-modal-desc');
            const icon = document.getElementById('mgk-modal-icon');
            const primaryBtn = document.getElementById('mgk-modal-primary-btn');

            icon.innerHTML = '⚠️';
            icon.style.background = 'rgba(239, 68, 68, 0.18)';
            icon.style.color = '#f87171';
            icon.style.borderColor = 'rgba(239, 68, 68, 0.3)';

            title.innerText = 'Presensi Dulu!';
            if (target === 'stok') {
                desc.innerText = 'Anda belum melakukan presensi masuk untuk shift hari ini. Silakan presensi dulu sebelum memeriksa stok barang & serah terima shift.';
            } else {
                desc.innerText = 'Anda belum melakukan presensi masuk untuk shift hari ini. Silakan presensi dulu sebelum membuka halaman penjualan & kasir shift.';
            }
            primaryBtn.style.display = 'inline-flex';
            primaryBtn.innerHTML = '<span>🔐</span> Presensi Masuk Sekarang';
            primaryBtn.href = "{{ route('presensi2.scan') }}";

            showToastNotification('Presensi dulu! Silakan lakukan presensi masuk terlebih dahulu.', 'error');
            modal.style.display = 'flex';
        }

        function showIncompleteModal(titleText, descText, actionBtnText = null, actionBtnUrl = null) {
            const modal = document.getElementById('mgk-notification-modal');
            if (!modal) {
                alert(titleText + '\n' + descText);
                return;
            }

            const title = document.getElementById('mgk-modal-title');
            const desc = document.getElementById('mgk-modal-desc');
            const icon = document.getElementById('mgk-modal-icon');
            const primaryBtn = document.getElementById('mgk-modal-primary-btn');

            icon.innerHTML = '📝';
            icon.style.background = 'rgba(245, 158, 11, 0.18)';
            icon.style.color = '#fbbf24';
            icon.style.borderColor = 'rgba(245, 158, 11, 0.3)';

            title.innerText = titleText || 'Pemeriksaan Belum Lengkap!';
            desc.innerText = descText || 'Mohon lengkapi seluruh item pemeriksaan terlebih dahulu.';

            if (actionBtnText && actionBtnUrl) {
                primaryBtn.style.display = 'inline-flex';
                primaryBtn.innerHTML = actionBtnText;
                primaryBtn.href = actionBtnUrl;
            } else {
                primaryBtn.style.display = 'none';
            }

            showToastNotification(titleText + ': ' + descText, 'warning');
            modal.style.display = 'flex';
        }

        function closeNotificationModal() {
            const modal = document.getElementById('mgk-notification-modal');
            if (modal) modal.style.display = 'none';
        }
    </script>
    @stack('scripts')
</body>
</html>
