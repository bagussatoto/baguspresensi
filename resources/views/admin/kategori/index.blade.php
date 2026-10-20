<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Daftar Kategori</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #287a9e;
            --subnav-active: #1a5975;
            --subnav-active-bg: #ffffff;
            --sidebar-bg: #222d32;
            --sidebar-header: #1a2226;
            --sidebar-hover: #1e282c;
            --sidebar-text: #b8c7ce;
            --sidebar-active-text: #ffffff;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
            --btn-blue: #286090;
            --btn-cyan: #00bcd4;
            --btn-sky: #03a9f4;
            --btn-teal: #2aa89b;
            --table-header-teal: #33b0a6;
            --btn-red: #d9534f;
            --btn-green: #28a745;
            --btn-orange: #f0ad4e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            font-size: 13px;
            overflow-x: hidden;
        }

        /* TOP HEADER */
        .main-header {
            background: linear-gradient(to right, #246e8c, var(--header-blue));
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            color: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }

        .header-brand-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 3px;
            transition: background 0.2s;
        }

        .sidebar-toggle-btn:hover {
            background: rgba(255,255,255,0.15);
        }

        .header-right-tools {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
        }

        .header-link-btn {
            color: #e0f2fe;
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 3px;
            background: rgba(255,255,255,0.12);
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .header-link-btn:hover {
            background: rgba(255,255,255,0.25);
            color: #ffffff;
        }

        /* HORIZONTAL SUB-NAVIGATION BAR */
        .sub-navbar {
            background-color: var(--subnav-blue);
            min-height: 40px;
            position: fixed;
            top: 48px;
            left: 230px;
            right: 0;
            z-index: 1020;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 4px 10px;
            gap: 4px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            scrollbar-width: thin;
            transition: left 0.3s;
        }

        .subnav-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            color: #e8f4f8;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            border-radius: 3px;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }

        .subnav-item:hover {
            background: rgba(0,0,0,0.15);
            color: #ffffff;
        }

        .subnav-item.active {
            background: rgba(0, 0, 0, 0.25);
            color: #ffffff;
            font-weight: 700;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* LAYOUT & SIDEBAR */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: 96px;
        }

        .main-sidebar {
            width: 230px;
            background-color: var(--sidebar-bg);
            position: fixed;
            top: 48px;
            bottom: 0;
            left: 0;
            z-index: 1025;
            transition: transform 0.3s, width 0.3s;
            overflow-y: auto;
            border-right: 1px solid #1a2226;
        }

        /* USER PANEL IN SIDEBAR */
        .sidebar-user-panel {
            padding: 16px 14px;
            background-color: var(--sidebar-header);
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #1a2226;
        }

        .user-avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #3c8dbc;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            border: 2px solid rgba(255,255,255,0.2);
            flex-shrink: 0;
        }

        .user-info-text {
            line-height: 1.3;
        }

        .user-info-name {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
        }

        .user-info-role {
            font-size: 11px;
            color: #8aa4af;
        }

        .user-status-badge {
            font-size: 10px;
            color: #2ecc71;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 3px;
            font-family: monospace;
            font-weight: 700;
        }

        .status-dot-blink {
            width: 7px;
            height: 7px;
            background: #2ecc71;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 6px #2ecc71;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* SIDEBAR NAVIGATION */
        .sidebar-nav-header {
            padding: 12px 16px 6px;
            font-size: 10px;
            text-transform: uppercase;
            color: #4b646f;
            background: #1a2226;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 12.5px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar-menu li a:hover {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-active-text);
        }

        .sidebar-menu li.active > a {
            color: var(--sidebar-active-text);
            background: #1e282c;
            border-left-color: #3c8dbc;
            font-weight: 600;
        }

        .menu-item-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-badge {
            background-color: #dd4b39;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
        }

        /* MAIN CONTENT AREA */
        .content-wrapper {
            margin-left: 230px;
            flex: 1;
            padding: 12px 20px 30px;
            transition: margin-left 0.3s;
            min-height: calc(100vh - 96px);
        }

        /* CENTER PAGE TITLE & STATS RIBBON */
        .page-title-center {
            text-align: center;
            font-size: 20px;
            font-weight: 500;
            color: #333333;
            margin: 6px 0 4px;
            letter-spacing: 0.5px;
        }

        .page-subtitle-center {
            text-align: center;
            font-size: 12px;
            color: #666666;
            margin-bottom: 14px;
        }

        .stats-ribbon-bar {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-left: 4px solid var(--header-dark-blue);
            border-radius: 4px;
            padding: 8px 14px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }

        .stats-ribbon-left {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--header-dark-blue);
        }

        .stats-ribbon-chips {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }

        .stat-chip.chip-blue {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
        }

        .stat-chip.chip-green {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .stat-chip.chip-purple {
            background: #f3e8ff;
            border-color: #e9d5ff;
            color: #7e22ce;
        }

        /* FILTER & SEARCH TOOLBAR */
        .filter-toolbar-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .search-input-group {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #ccd0d4;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            max-width: 420px;
            flex: 1;
        }

        .search-input-group input {
            border: none;
            outline: none;
            padding: 7px 12px;
            font-size: 13px;
            width: 100%;
            background: transparent;
        }

        .search-input-group button {
            background: #f8f9fa;
            border: none;
            border-left: 1px solid #ccd0d4;
            padding: 7px 14px;
            color: #555555;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .search-input-group button:hover {
            background: #e9ecef;
            color: #222222;
        }

        .toolbar-right-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .action-tool-btn {
            background: #ffffff;
            border: 1px solid #ced4da;
            color: #495057;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.15s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            height: 32px;
        }

        .action-tool-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: #212529;
        }

        .action-tool-btn.active-filter {
            background: #17a2b8;
            color: #ffffff;
            border-color: #138496;
        }

        .select-filter-parent {
            height: 32px;
            padding: 4px 8px;
            font-size: 12px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            background-color: #ffffff;
            color: #495057;
            outline: none;
            cursor: pointer;
        }

        .select-filter-parent:focus {
            border-color: #17a2b8;
        }

        .btn-add-category {
            background: #00bcd4;
            border: 1px solid #00acc1;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            width: 34px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 188, 212, 0.4);
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-add-category:hover {
            background: #00acc1;
            transform: scale(1.05);
        }

        /* 3-COLUMN TABLE GRID LAYOUT */
        .category-columns-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            align-items: start;
        }

        .category-columns-container.layout-2col {
            grid-template-columns: repeat(2, 1fr);
        }

        .category-columns-container.layout-1col {
            grid-template-columns: 1fr;
        }

        .category-column-box {
            background: #ffffff;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #d2d6de;
            overflow: hidden;
        }

        .category-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .category-table thead th {
            background-color: var(--table-header-teal);
            color: #ffffff;
            font-weight: 700;
            padding: 8px 10px;
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.2);
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        .category-table thead th:last-child {
            border-right: none;
        }

        .category-table thead th.th-no {
            width: 42px;
            text-align: center;
        }

        .category-table thead th.th-kode {
            width: 100px;
            text-align: center;
        }

        .category-table thead th.th-nama {
            text-align: left;
        }

        .category-table thead th.th-status {
            width: 80px;
            text-align: center;
        }

        .category-table tbody tr {
            border-bottom: 1px solid #edf1f5;
            transition: background 0.15s;
        }

        .category-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .category-table tbody tr:hover {
            background-color: #eef7f8;
        }

        .category-table tbody td {
            padding: 7px 10px;
            vertical-align: middle;
            color: #333333;
        }

        .category-table tbody td.td-no {
            text-align: center;
            color: #555555;
            font-weight: 500;
        }

        .category-table tbody td.td-kode {
            text-align: center;
            font-weight: 600;
            color: #2c7ea1;
            letter-spacing: 0.5px;
            font-family: monospace, monospace;
            font-size: 12px;
        }

        .category-table tbody td.td-nama {
            font-weight: 500;
            color: #222222;
        }

        .category-table tbody td.td-status {
            text-align: center;
            white-space: nowrap;
        }

        .cat-name-main {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .cat-meta-badges {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 3px;
            flex-wrap: wrap;
        }

        .badge-parent-link {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 10px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: 500;
        }

        .badge-is-root {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 10px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: 500;
        }

        .badge-prod-count {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 10px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: 600;
        }

        .badge-inactive {
            display: inline-block;
            font-size: 10px;
            color: #721c24;
            background: #f8d7da;
            border-radius: 2px;
            padding: 1px 4px;
        }

        /* ROW ACTION BUTTONS */
        .btn-row-action {
            background: transparent;
            border: 1px solid transparent;
            padding: 3px 6px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            transition: all 0.15s;
        }

        .btn-row-action:hover {
            background: #e9ecef;
            color: #212529;
            border-color: #ced4da;
        }

        .btn-row-action.btn-del:hover {
            background: #f8d7da;
            color: #dc3545;
            border-color: #f5c6cb;
        }

        /* MODAL STYLES */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.55);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 4px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-top: 4px solid var(--header-blue);
            animation: modalFadeIn 0.2s ease-out;
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #edf1f5;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--header-dark-blue);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 20px;
            line-height: 1;
            color: #999999;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .modal-close-btn:hover {
            background: #f1f1f1;
            color: #333333;
        }

        .modal-body {
            padding: 18px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 8px 10px;
            font-size: 13px;
            border: 1px solid #ccd0d4;
            border-radius: 3px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #ffffff;
        }

        .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 2px rgba(60, 141, 188, 0.2);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 6px;
        }

        .form-hint {
            font-size: 11px;
            color: #718096;
            margin-top: 3px;
        }

        .modal-footer {
            padding: 12px 18px;
            background: #f8fafc;
            border-top: 1px solid #edf1f5;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-modal {
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
        }

        .btn-modal-secondary {
            background: #e2e8f0;
            color: #475569;
            border-color: #cbd5e1;
        }

        .btn-modal-secondary:hover {
            background: #cbd5e1;
            color: #1e293b;
        }

        .btn-modal-primary {
            background: #0284c7;
            color: #ffffff;
            border-color: #0369a1;
        }

        .btn-modal-primary:hover {
            background: #0369a1;
        }

        .btn-modal-danger {
            background: #dc2626;
            color: #ffffff;
            border-color: #b91c1c;
        }

        .btn-modal-danger:hover {
            background: #b91c1c;
        }

        /* TOAST NOTIFICATION */
        .toast-box {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 4px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            z-index: 3000;
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28), opacity 0.3s ease;
        }

        .toast-box.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-box.toast-success {
            border-left: 4px solid #22c55e;
        }

        .toast-box.toast-error {
            border-left: 4px solid #ef4444;
        }

        .toast-box.toast-info {
            border-left: 4px solid #0ea5e9;
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .category-columns-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sub-navbar {
                left: 0;
            }
            .main-sidebar {
                transform: translateX(-100%);
            }
            .content-wrapper {
                margin-left: 0;
            }
            body.sidebar-mobile-open .main-sidebar {
                transform: translateX(0);
            }
            .category-columns-container {
                grid-template-columns: 1fr;
            }
        }

        body.sidebar-collapsed .main-sidebar {
            width: 0;
            transform: translateX(-100%);
        }

        body.sidebar-collapsed .sub-navbar,
        body.sidebar-collapsed .content-wrapper {
            margin-left: 0;
            left: 0;
        }
    </style>
</head>
<body>

    @include('admin.partials.erp_navbar')

    <!-- APP WRAPPER -->
    <div class="app-wrapper">
        <!-- LEFT SIDEBAR -->
        <aside class="main-sidebar" id="mainSidebar">
            <!-- USER PANEL -->
            <div class="sidebar-user-panel">
                <div class="user-avatar-circle">B</div>
                <div class="user-info-text">
                    <div class="user-info-name">Bagus</div>
                    <div class="user-info-role">manager @PUSAT</div>
                    <div class="user-status-badge">
                        <span class="status-dot-blink"></span>
                        <span id="liveTimeBadge">10:37:48 ON</span>
                    </div>
                </div>
            </div>

            <!-- MENU -->
            <div class="sidebar-nav-header">MAIN NAVIGATION</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <span class="menu-item-left">📊 Dashboard Admin</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-nav-header">MASTER DATA & INVENTARIS</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.merk') }}">
                        <span class="menu-item-left">🏷️ Merk Produk</span>
                    </a>
                </li>
                <li class="active">
                    <a href="{{ route('admin.kategori') }}">
                        <span class="menu-item-left">📁 Kategori Produk</span>
                        <span class="menu-badge" id="menuBadgeCount">{{ $totalCount }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.supplier') }}">
                        <span class="menu-item-left">🏢 Supplier & Vendor</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.jadwal') }}">
                        <span class="menu-item-left">🕒 Jadwal Shift Kerja</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.karyawan') }}">
                        <span class="menu-item-left">👤 Karyawan & Staff</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-nav-header">TRANSAKSI & OPERASIONAL</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('admin.penjualan') }}">
                        <span class="menu-item-left">🛒 Penjualan & POS Kasir</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.presensi') }}">
                        <span class="menu-item-left">📅 Data Presensi Kerja</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('warnet.index') }}">
                        <span class="menu-item-left">🖥️ Billing & POS Warnet</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="content-wrapper">
            <!-- PAGE TITLE -->
            <h1 class="page-title-center">📁 DAFTAR KATEGORI PRODUK</h1>
            <p class="page-subtitle-center">Kelola hierarki kategori, pengelompokan produk, status aktif, dan data katalog MGK ERP</p>

            <!-- STATS RIBBON -->
            <div class="stats-ribbon-bar">
                <div class="stats-ribbon-left">
                    <span>🏢 Maya Graha Kencana</span>
                    <span style="color: #94a3b8;">/</span>
                    <span>Katalog Kategori</span>
                </div>
                <div class="stats-ribbon-chips">
                    <span class="stat-chip chip-blue">
                        📁 Total: <strong id="statTotalCount">{{ $totalCount }}</strong>
                    </span>
                    <span class="stat-chip chip-purple">
                        🌳 Induk Utama: <strong id="statParentCount">{{ $parentCategories->count() }}</strong>
                    </span>
                    <span class="stat-chip chip-green">
                        📦 Total Produk: <strong>{{ $categories->sum('products_count') }}</strong>
                    </span>
                </div>
            </div>

            <!-- TOOLBAR: SEARCH & ACTIONS -->
            <div class="filter-toolbar-box">
                <!-- SEARCH INPUT -->
                <div class="search-input-group">
                    <input type="text" id="searchInput" placeholder="Cari nama, kode, atau slug kategori..." oninput="handleSearch(this.value)">
                    <button type="button" title="Cari Kategori" onclick="handleSearch(document.getElementById('searchInput').value)">🔍</button>
                </div>

                <!-- RIGHT CONTROLS -->
                <div class="toolbar-right-actions">
                    <!-- FILTER LEVEL HIERARKI -->
                    <select class="select-filter-parent" id="selectHierarchyFilter" onchange="filterHierarchy(this.value)" title="Filter Tingkat Hierarki">
                        <option value="all">Semua Level</option>
                        <option value="root">Hanya Kategori Induk</option>
                        <option value="sub">Hanya Sub-Kategori</option>
                    </select>

                    <!-- FILTER PARENT KHUSUS -->
                    <select class="select-filter-parent" id="selectParentFilter" onchange="filterParentId(this.value)" title="Filter Berdasarkan Kategori Induk">
                        <option value="all">Semua Induk</option>
                        @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}">↳ {{ $parent->name }}</option>
                        @endforeach
                    </select>

                    <!-- TOGGLE LAYOUT -->
                    <button class="action-tool-btn" id="btnToggleLayout" onclick="toggleColumnLayout()" title="Ganti Layout Kolom (3 / 2 / 1 Kolom)">
                        <strong>=</strong>
                    </button>

                    <!-- FILTER STATUS -->
                    <button class="action-tool-btn" id="btnFilterActive" onclick="filterStatus('active')" title="Tampilkan Kategori Aktif">
                        👁️
                    </button>
                    <button class="action-tool-btn active-filter" id="btnFilterAll" onclick="filterStatus('all')" title="Tampilkan Semua / Reset Filter">
                        👁️‍🗨️
                    </button>

                    <!-- TOMBOL TAMBAH -->
                    <button class="btn-add-category" onclick="openAddCategoryModal()" title="Tambah Kategori Baru">
                        +
                    </button>
                </div>
            </div>

            <!-- 3-COLUMN TABLES CONTAINER -->
            <div class="category-columns-container" id="categoryGridContainer">
                <!-- Di-render dinamis oleh JavaScript -->
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH KATEGORI -->
    <div class="modal-overlay" id="addCategoryModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">📁 Tambah Kategori Baru</div>
                <button class="modal-close-btn" onclick="closeModal('addCategoryModal')">&times;</button>
            </div>
            <form id="formAddCategory" onsubmit="submitAddCategory(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="addCategoryName">Nama Kategori <span style="color:red;">*</span></label>
                        <input type="text" id="addCategoryName" name="name" class="form-control" required placeholder="Contoh: Aksesoris Komputer, Minuman Panas">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="addCategoryCode">Kode Kategori (Opsional)</label>
                        <input type="text" id="addCategoryCode" name="code" class="form-control" placeholder="Contoh: CAT-ACC, CAT-HOT, atau biarkan kosong untuk otomatis">
                        <div class="form-hint">Kosongkan agar kode dibuat otomatis format CAT-XXXX.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="addCategoryParent">Kategori Induk (Hierarki)</label>
                        <select id="addCategoryParent" name="parent_id" class="form-control">
                            <option value="">— Tanpa Induk (Kategori Induk Utama) —</option>
                            @foreach ($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code ?? 'NO-CODE' }})</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Pilih kategori induk jika kategori ini merupakan sub-kategori.</div>
                    </div>

                    <div class="form-group form-check">
                        <input type="checkbox" id="addCategoryActive" name="is_active" checked value="1">
                        <label for="addCategoryActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addCategoryModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitAdd">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KATEGORI -->
    <div class="modal-overlay" id="editCategoryModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">✏️ Edit Kategori</div>
                <button class="modal-close-btn" onclick="closeModal('editCategoryModal')">&times;</button>
            </div>
            <form id="formEditCategory" onsubmit="submitEditCategory(event)">
                <input type="hidden" id="editCategoryId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="editCategoryName">Nama Kategori <span style="color:red;">*</span></label>
                        <input type="text" id="editCategoryName" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editCategoryCode">Kode Kategori</label>
                        <input type="text" id="editCategoryCode" name="code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="editCategoryParent">Kategori Induk</label>
                        <select id="editCategoryParent" name="parent_id" class="form-control">
                            <option value="">— Tanpa Induk (Kategori Induk Utama) —</option>
                            @foreach ($parentCategories as $parent)
                                <option value="{{ $parent->id }}" data-id="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code ?? 'NO-CODE' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-check">
                        <input type="checkbox" id="editCategoryActive" name="is_active" value="1">
                        <label for="editCategoryActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editCategoryModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitEdit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal-overlay" id="deleteCategoryModal">
        <div class="modal-card" style="max-width: 420px; border-top-color: #dc2626;">
            <div class="modal-header">
                <div class="modal-title" style="color: #dc2626;">🗑️ Hapus Kategori</div>
                <button class="modal-close-btn" onclick="closeModal('deleteCategoryModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13px; line-height: 1.5; color: #333333;">
                    Apakah Anda yakin ingin menghapus kategori <strong id="deleteCategoryNameText" style="color: #dc2626;"></strong>?
                </p>
                <div id="deleteWarningBox" style="margin-top: 10px; padding: 8px 12px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; font-size: 12px; color: #856404; display: none;">
                    ⚠️ Kategori ini memiliki produk terkait. Menghapus kategori dapat mempengaruhi data katalog produk!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('deleteCategoryModal')">Batal</button>
                <button type="button" class="btn-modal btn-modal-danger" id="btnConfirmDelete" onclick="confirmDeleteCategory()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>

    <script>
        // Data inisial kategori dari server
        let allCategories = @json($categories);
        let parentCategoriesList = @json($parentCategories);
        let currentFilterStatus = 'all';
        let currentHierarchyFilter = 'all'; // 'all', 'root', 'sub'
        let currentParentFilter = 'all';
        let currentSearchQuery = '';
        let currentLayout = '3col'; // '3col', '2col', '1col'
        let pendingDeleteId = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const baseUrl = "{{ url('/') }}";

        // SIDEBAR TOGGLE
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        });

        // LIVE TIME CLOCK
        function updateLiveClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const badge = document.getElementById('liveTimeBadge');
            if (badge) {
                badge.innerText = `${hours}:${minutes}:${seconds} ON`;
            }
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // ESCAPE HELPERS
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeQuote(str) {
            if (!str) return '';
            return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        // TOAST HELPER
        let toastTimeout = null;
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toastBox');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');

            toast.classList.remove('toast-success', 'toast-error', 'toast-info');
            toast.classList.add(`toast-${type}`);

            if (type === 'success') icon.innerText = '✔';
            else if (type === 'error') icon.innerText = '✖';
            else icon.innerText = 'ℹ';

            msg.innerText = message;
            toast.classList.add('show');

            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }

        // RENDER 3-COLUMN TABLE GRID
        function renderCategoryGrid() {
            const container = document.getElementById('categoryGridContainer');
            if (!container) return;

            // Filter data
            let filtered = allCategories.filter(cat => {
                // Search query
                const q = currentSearchQuery.toLowerCase();
                const matchesSearch = !q ||
                    (cat.name && cat.name.toLowerCase().includes(q)) ||
                    (cat.code && cat.code.toLowerCase().includes(q)) ||
                    (cat.slug && cat.slug.toLowerCase().includes(q));

                // Status
                let matchesStatus = true;
                if (currentFilterStatus === 'active') {
                    matchesStatus = cat.is_active === true || cat.is_active === 1 || cat.is_active === '1';
                } else if (currentFilterStatus === 'inactive') {
                    matchesStatus = cat.is_active === false || cat.is_active === 0 || cat.is_active === '0';
                }

                // Hierarchy Level
                let matchesHierarchy = true;
                if (currentHierarchyFilter === 'root') {
                    matchesHierarchy = !cat.parent_id;
                } else if (currentHierarchyFilter === 'sub') {
                    matchesHierarchy = !!cat.parent_id;
                }

                // Parent Filter
                let matchesParent = true;
                if (currentParentFilter !== 'all') {
                    matchesParent = String(cat.parent_id) === String(currentParentFilter);
                }

                return matchesSearch && matchesStatus && matchesHierarchy && matchesParent;
            });

            container.innerHTML = '';

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: #fff; border-radius: 4px; border: 1px solid #d2d6de; color: #777;">
                        <div style="font-size: 32px; margin-bottom: 8px;">📁</div>
                        <div style="font-size: 15px; font-weight: 600;">Tidak ada kategori yang sesuai filter</div>
                        <div style="font-size: 12px; color: #999; margin-top: 4px;">Coba ubah kata kunci pencarian atau reset filter di toolbar atas.</div>
                    </div>
                `;
                return;
            }

            // Tentukan jumlah kolom
            let numCols = 3;
            if (currentLayout === '2col') numCols = 2;
            if (currentLayout === '1col') numCols = 1;

            const total = filtered.length;
            const itemsPerCol = Math.ceil(total / numCols);

            for (let c = 0; c < numCols; c++) {
                const startIdx = c * itemsPerCol;
                const endIdx = Math.min(startIdx + itemsPerCol, total);
                const colItems = filtered.slice(startIdx, endIdx);

                if (colItems.length === 0 && c > 0) continue;

                const colBox = document.createElement('div');
                colBox.className = 'category-column-box';

                let tableHtml = `
                    <table class="category-table">
                        <thead>
                            <tr>
                                <th class="th-no">No</th>
                                <th class="th-kode">Kode</th>
                                <th class="th-nama">Nama & Hierarki</th>
                                <th class="th-status">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                colItems.forEach((cat, idxInCol) => {
                    const globalNo = startIdx + idxInCol + 1;
                    const kodeDisplay = cat.code ? escapeHtml(cat.code) : '<span style="color:#aaa;">-</span>';
                    const isAct = cat.is_active === true || cat.is_active === 1 || cat.is_active === '1';
                    const inactiveBadge = !isAct ? `<span class="badge-inactive">Non-Aktif</span>` : '';

                    // Hierarchy badge
                    let parentBadge = '';
                    if (cat.parent) {
                        parentBadge = `<span class="badge-parent-link" title="Sub-Kategori dari ${escapeQuote(cat.parent.name)}">↳ ${escapeHtml(cat.parent.name)}</span>`;
                    } else if (!cat.parent_id) {
                        parentBadge = `<span class="badge-is-root" title="Kategori Induk Utama">Induk</span>`;
                    }

                    // Product count badge
                    const prodCount = cat.products_count !== undefined ? cat.products_count : 0;
                    const prodBadge = `<span class="badge-prod-count" title="${prodCount} produk terdaftar dalam kategori ini">📦 ${prodCount}</span>`;

                    tableHtml += `
                        <tr id="cat-row-${cat.id}">
                            <td class="td-no">${globalNo}</td>
                            <td class="td-kode">${kodeDisplay}</td>
                            <td class="td-nama">
                                <div class="cat-name-main">
                                    <span>${escapeHtml(cat.name)}</span>
                                    ${inactiveBadge}
                                </div>
                                <div class="cat-meta-badges">
                                    ${parentBadge}
                                    ${prodBadge}
                                </div>
                            </td>
                            <td class="td-status">
                                <button type="button" class="btn-row-action" title="Edit Kategori" onclick="openEditCategoryModal(${cat.id})">
                                    👁️
                                </button>
                                <button type="button" class="btn-row-action btn-del" title="Hapus Kategori" onclick="openDeleteCategoryModal(${cat.id}, '${escapeQuote(cat.name)}', ${prodCount})">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    `;
                });

                tableHtml += `
                        </tbody>
                    </table>
                `;

                colBox.innerHTML = tableHtml;
                container.appendChild(colBox);
            }
        }

        // TOGGLE LAYOUT
        function toggleColumnLayout() {
            const container = document.getElementById('categoryGridContainer');
            if (currentLayout === '3col') {
                currentLayout = '2col';
                container.classList.remove('layout-1col');
                container.classList.add('layout-2col');
                showToast('Layout diubah ke 2 Kolom', 'info');
            } else if (currentLayout === '2col') {
                currentLayout = '1col';
                container.classList.remove('layout-2col');
                container.classList.add('layout-1col');
                showToast('Layout diubah ke 1 Kolom Penuh', 'info');
            } else {
                currentLayout = '3col';
                container.classList.remove('layout-1col', 'layout-2col');
                showToast('Layout diubah ke 3 Kolom Standar', 'info');
            }
            renderCategoryGrid();
        }

        // FILTER STATUS
        function filterStatus(status) {
            currentFilterStatus = status;
            const btnActive = document.getElementById('btnFilterActive');
            const btnAll = document.getElementById('btnFilterAll');

            if (status === 'active') {
                btnActive.classList.add('active-filter');
                btnAll.classList.remove('active-filter');
                showToast('Menampilkan Kategori Aktif', 'info');
            } else {
                btnActive.classList.remove('active-filter');
                btnAll.classList.add('active-filter');
                showToast('Menampilkan Semua Kategori', 'info');
            }
            renderCategoryGrid();
        }

        // FILTER HIERARKI & PARENT
        function filterHierarchy(val) {
            currentHierarchyFilter = val;
            renderCategoryGrid();
        }

        function filterParentId(val) {
            currentParentFilter = val;
            renderCategoryGrid();
        }

        // SEARCH HANDLER
        function handleSearch(val) {
            currentSearchQuery = val ? val.trim() : '';
            renderCategoryGrid();
        }

        // MODAL HELPERS
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // TAMBAH KATEGORI
        function openAddCategoryModal() {
            document.getElementById('formAddCategory').reset();
            document.getElementById('addCategoryActive').checked = true;
            openModal('addCategoryModal');
            setTimeout(() => document.getElementById('addCategoryName').focus(), 150);
        }

        function submitAddCategory(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitAdd');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const name = document.getElementById('addCategoryName').value.trim();
            const code = document.getElementById('addCategoryCode').value.trim();
            const parentId = document.getElementById('addCategoryParent').value;
            const isActive = document.getElementById('addCategoryActive').checked ? 1 : 0;

            fetch("{{ route('admin.kategori.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    code: code,
                    parent_id: parentId || null,
                    is_active: isActive
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Kategori';

                if (data.success && data.category) {
                    allCategories.push(data.category);
                    // Urutkan alfabetis
                    allCategories.sort((a, b) => a.name.localeCompare(b.name));

                    // Update parent dropdown jika kategori baru ini adalah root
                    if (!data.category.parent_id) {
                        updateParentDropdowns(data.category);
                    }

                    closeModal('addCategoryModal');
                    renderCategoryGrid();
                    updateStatsBadges();
                    showToast(data.message || 'Kategori berhasil ditambahkan!', 'success');
                } else {
                    showToast(data.message || 'Gagal menyimpan kategori', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerText = 'Simpan Kategori';
                showToast('Terjadi kesalahan koneksi server', 'error');
            });
        }

        // EDIT KATEGORI
        function openEditCategoryModal(id) {
            const cat = allCategories.find(c => c.id === id);
            if (!cat) return;

            document.getElementById('editCategoryId').value = cat.id;
            document.getElementById('editCategoryName').value = cat.name;
            document.getElementById('editCategoryCode').value = cat.code || '';
            document.getElementById('editCategoryActive').checked = (cat.is_active === true || cat.is_active === 1 || cat.is_active === '1');

            // Set parent dropdown & nonaktifkan opsi dirinya sendiri
            const parentSelect = document.getElementById('editCategoryParent');
            Array.from(parentSelect.options).forEach(opt => {
                if (opt.getAttribute('data-id') == cat.id) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });

            parentSelect.value = cat.parent_id ? String(cat.parent_id) : '';

            openModal('editCategoryModal');
            setTimeout(() => document.getElementById('editCategoryName').focus(), 150);
        }

        function submitEditCategory(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const id = document.getElementById('editCategoryId').value;
            const name = document.getElementById('editCategoryName').value.trim();
            const code = document.getElementById('editCategoryCode').value.trim();
            const parentId = document.getElementById('editCategoryParent').value;
            const isActive = document.getElementById('editCategoryActive').checked ? 1 : 0;

            const updateUrl = `{{ url('admin/kategori') }}/${id}`;

            fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    code: code,
                    parent_id: parentId || null,
                    is_active: isActive
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Perubahan';

                if (data.success && data.category) {
                    const idx = allCategories.findIndex(c => c.id == id);
                    if (idx !== -1) {
                        allCategories[idx] = data.category;
                    }
                    allCategories.sort((a, b) => a.name.localeCompare(b.name));

                    closeModal('editCategoryModal');
                    renderCategoryGrid();
                    updateStatsBadges();
                    showToast(data.message || 'Kategori berhasil diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui kategori', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerText = 'Simpan Perubahan';
                showToast('Terjadi kesalahan jaringan', 'error');
            });
        }

        // DELETE KATEGORI
        function openDeleteCategoryModal(id, name, prodCount) {
            pendingDeleteId = id;
            document.getElementById('deleteCategoryNameText').innerText = `"${name}"`;
            const warningBox = document.getElementById('deleteWarningBox');
            if (prodCount > 0) {
                warningBox.style.display = 'block';
                warningBox.innerHTML = `⚠️ Kategori ini memiliki <strong>${prodCount} produk</strong> terkait. Menghapus kategori dapat menyebabkan produk kehilangan relasi kategorinya!`;
            } else {
                warningBox.style.display = 'none';
            }
            openModal('deleteCategoryModal');
        }

        function confirmDeleteCategory() {
            if (!pendingDeleteId) return;

            const btn = document.getElementById('btnConfirmDelete');
            btn.disabled = true;
            btn.innerText = 'Menghapus...';

            const deleteUrl = `{{ url('admin/kategori') }}/${pendingDeleteId}`;

            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Ya, Hapus';

                if (data.success) {
                    allCategories = allCategories.filter(c => c.id != pendingDeleteId);
                    closeModal('deleteCategoryModal');
                    pendingDeleteId = null;
                    renderCategoryGrid();
                    updateStatsBadges();
                    showToast(data.message || 'Kategori berhasil dihapus.', 'success');
                } else {
                    showToast(data.message || 'Gagal menghapus kategori', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerText = 'Ya, Hapus';
                showToast('Terjadi kesalahan jaringan saat menghapus', 'error');
            });
        }

        // UPDATE STATS & DROPDOWNS
        function updateStatsBadges() {
            const statTotal = document.getElementById('statTotalCount');
            const menuBadge = document.getElementById('menuBadgeCount');
            const statParent = document.getElementById('statParentCount');

            if (statTotal) statTotal.innerText = allCategories.length;
            if (menuBadge) menuBadge.innerText = allCategories.length;
            if (statParent) {
                const parents = allCategories.filter(c => !c.parent_id);
                statParent.innerText = parents.length;
            }
        }

        function updateParentDropdowns(newParent) {
            const addSelect = document.getElementById('addCategoryParent');
            const editSelect = document.getElementById('editCategoryParent');
            const filterSelect = document.getElementById('selectParentFilter');

            const opt1 = new Option(`${newParent.name} (${newParent.code || 'NO-CODE'})`, newParent.id);
            const opt2 = new Option(`${newParent.name} (${newParent.code || 'NO-CODE'})`, newParent.id);
            opt2.setAttribute('data-id', newParent.id);
            const opt3 = new Option(`↳ ${newParent.name}`, newParent.id);

            if (addSelect) addSelect.add(opt1);
            if (editSelect) editSelect.add(opt2);
            if (filterSelect) filterSelect.add(opt3);
        }

        // INITIAL RENDER
        document.addEventListener('DOMContentLoaded', function() {
            renderCategoryGrid();
        });
    </script>
</body>
</html>
