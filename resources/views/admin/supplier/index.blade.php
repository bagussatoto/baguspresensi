<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Daftar Supplier</title>
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
            --sidebar-bg: #222d32;
            --sidebar-header: #1a2226;
            --sidebar-hover: #1e282c;
            --sidebar-text: #b8c7ce;
            --sidebar-active-text: #ffffff;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
            --btn-cyan: #00bcd4;
            --table-header-teal: #33b0a6;
            --orange-note: #d35400;
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

        /* BREADCRUMB / TOP RIGHT INFO */
        .top-info-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 11px;
            color: #777777;
            margin-bottom: 4px;
        }

        .top-info-bar span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* CENTER PAGE TITLE */
        .page-title-center {
            text-align: center;
            font-size: 20px;
            font-weight: 500;
            color: #333333;
            margin: 0 0 16px;
            letter-spacing: 0.5px;
        }

        /* FILTER & SEARCH TOOLBAR (ATAS) */
        .filter-toolbar-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
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
            max-width: 480px;
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

        .btn-add-item {
            background: #00bcd4;
            border: 1px solid #00acc1;
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
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

        .btn-add-item:hover {
            background: #00acc1;
            transform: scale(1.05);
        }

        /* DATATABLES BAR (ENTRIES & INLINE SEARCH) */
        .datatable-controls-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 12.5px;
            color: #333333;
        }

        .entries-select-wrap select {
            padding: 3px 6px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            outline: none;
            background: #ffffff;
            font-size: 12.5px;
            margin: 0 4px;
        }

        .table-search-inline {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-search-inline input {
            border: 1px solid #ced4da;
            border-radius: 3px;
            padding: 4px 8px;
            font-size: 12.5px;
            outline: none;
            width: 180px;
            transition: border-color 0.2s;
        }

        .table-search-inline input:focus {
            border-color: var(--header-blue);
        }

        /* MAIN SUPPLIER TABLE */
        .table-card-container {
            background: #ffffff;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #d2d6de;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .supplier-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            min-width: 900px;
        }

        .supplier-table thead th {
            background-color: var(--table-header-teal);
            color: #ffffff;
            font-weight: 700;
            padding: 9px 12px;
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.2);
            font-size: 12.5px;
            letter-spacing: 0.3px;
            white-space: nowrap;
            user-select: none;
            cursor: pointer;
        }

        .supplier-table thead th:hover {
            background-color: #2da198;
        }

        .supplier-table thead th:last-child {
            border-right: none;
            cursor: default;
        }

        .supplier-table thead th .sort-indicator {
            display: inline-block;
            margin-left: 4px;
            font-size: 10px;
            opacity: 0.8;
        }

        .supplier-table thead th.th-no { width: 50px; text-align: center; }
        .supplier-table thead th.th-kode { width: 75px; text-align: center; }
        .supplier-table thead th.th-nama { min-width: 180px; }
        .supplier-table thead th.th-kota { min-width: 160px; }
        .supplier-table thead th.th-telepon { width: 130px; text-align: center; }
        .supplier-table thead th.th-persone { width: 120px; text-align: center; }
        .supplier-table thead th.th-npwp { width: 120px; text-align: center; }
        .supplier-table thead th.th-nik { width: 90px; text-align: center; }
        .supplier-table thead th.th-action { width: 85px; text-align: center; }

        .supplier-table tbody tr {
            border-bottom: 1px solid #edf1f5;
            transition: background 0.15s;
        }

        .supplier-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .supplier-table tbody tr:hover {
            background-color: #eef7f8;
        }

        .supplier-table tbody td {
            padding: 8px 12px;
            vertical-align: middle;
            color: #333333;
        }

        .supplier-table tbody td.td-no { text-align: center; color: #555555; }
        .supplier-table tbody td.td-kode { text-align: center; font-weight: 500; }
        .supplier-table tbody td.td-nama {
            font-weight: 500;
            color: #2c7ea1;
            cursor: pointer;
        }
        .supplier-table tbody td.td-nama:hover {
            text-decoration: underline;
        }
        .supplier-table tbody td.td-kota {
            line-height: 1.35;
        }
        .supplier-table tbody td.td-telepon { text-align: center; }
        .supplier-table tbody td.td-persone { text-align: center; }
        .supplier-table tbody td.td-npwp { text-align: center; }
        .supplier-table tbody td.td-nik { text-align: center; }
        .supplier-table tbody td.td-action { text-align: center; white-space: nowrap; }

        .city-primary {
            font-weight: 500;
            color: #333333;
        }

        .city-subnote {
            font-size: 11px;
            color: var(--orange-note);
            display: block;
            margin-top: 2px;
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

        /* TABLE FOOTER & PAGINATION */
        .table-pagination-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 14px;
            gap: 4px;
        }

        .page-btn {
            background: #ffffff;
            border: 1px solid #ced4da;
            color: #495057;
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .page-btn:hover {
            background: #e9ecef;
        }

        .page-btn.active {
            background: #ffffff;
            border-color: #2c7ea1;
            color: #2c7ea1;
            font-weight: 700;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }

        .page-btn.disabled {
            color: #adb5bd;
            pointer-events: none;
            background: #f8f9fa;
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            background: linear-gradient(to right, #246e8c, var(--header-blue));
            color: #ffffff;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 20px;
            cursor: pointer;
            line-height: 1;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .modal-close-btn:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 18px 20px;
            max-height: 75vh;
            overflow-y: auto;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #444444;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 7px 11px;
            font-size: 13px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #2c7ea1;
            box-shadow: 0 0 0 2px rgba(44, 126, 161, 0.2);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
        }

        .modal-footer {
            padding: 12px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-modal {
            padding: 7px 16px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
        }

        .btn-modal-secondary {
            background: #ffffff;
            border-color: #ced4da;
            color: #495057;
        }

        .btn-modal-secondary:hover {
            background: #f1f3f5;
        }

        .btn-modal-primary {
            background: #2c7ea1;
            color: #ffffff;
        }

        .btn-modal-primary:hover {
            background: #236784;
        }

        .btn-modal-danger {
            background: #dc3545;
            color: #ffffff;
        }

        .btn-modal-danger:hover {
            background: #c82333;
        }

        /* TOAST NOTIFIKASI */
        .toast-box {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
            background: #333333;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
        }

        .toast-box.show {
            transform: translateX(0);
        }

        .toast-box.toast-success {
            background: #28a745;
        }

        .toast-box.toast-error {
            background: #dc3545;
        }

        /* COLLAPSED SIDEBAR */
        body.sidebar-collapsed .main-sidebar {
            width: 0;
            transform: translateX(-230px);
        }
        body.sidebar-collapsed .sub-navbar,
        body.sidebar-collapsed .content-wrapper {
            margin-left: 0;
            left: 0;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .main-sidebar {
                transform: translateX(-230px);
            }
            .sub-navbar,
            .content-wrapper {
                margin-left: 0;
                left: 0;
            }
            body.sidebar-mobile-open .main-sidebar {
                transform: translateX(0);
            }
            .form-row {
                grid-template-columns: 1fr;
            }
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
                <li class="active">
                    <a href="{{ route('admin.supplier') }}#supplier">
                        <span class="menu-item-left">🗄️ <span>data</span></span>
                    </a>
                </li>
                <li>
                    <a href="#pembelian">
                        <span class="menu-item-left">💳 <span>pembelian</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('warnet.index') }}#stocks">
                        <span class="menu-item-left">📦 <span>stok</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('warnet.index') }}#transfers">
                        <span class="menu-item-left">🚚 <span>distribusi</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.penjualan') }}">
                        <span class="menu-item-left">🛒 <span>penjualan</span></span>
                    </a>
                </li>
                <li>
                    <a href="#akunting">
                        <span class="menu-item-left">🧮 <span>akunting</span></span>
                    </a>
                </li>
                <li>
                    <a href="#laporan">
                        <span class="menu-item-left">📊 <span>laporan</span></span>
                    </a>
                </li>
                <li>
                    <a href="#speedtest">
                        <span class="menu-item-left">⚡ <span>speedtest</span></span>
                    </a>
                </li>
                <li>
                    <a href="#link">
                        <span class="menu-item-left">🔗 <span>link</span></span>
                    </a>
                </li>
                <li>
                    <a href="#setting">
                        <span class="menu-item-left">⚙️ <span>Setting</span></span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" onclick="document.getElementById('logoutFormSidebar').submit()">
                        <span class="menu-item-left">🔒 <span>Logout</span></span>
                    </a>
                    <form id="logoutFormSidebar" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <main class="content-wrapper">
            <!-- TOP RIGHT BREADCRUMB / DATA COUNTER -->
            <div class="top-info-bar">
                <span>🗄️ data &gt; <strong id="topDataCount">{{ $totalCount }}</strong></span>
            </div>

            <!-- PAGE TITLE (CENTER) -->
            <h1 class="page-title-center">daftar supplier</h1>

            <!-- TOOLBAR ATAS: SEARCH & ACTION BUTTONS -->
            <div class="filter-toolbar-box">
                <!-- SEARCH INPUT -->
                <div class="search-input-group">
                    <input type="text" id="topSearchInput" placeholder="Search..." oninput="handleSearch(this.value)">
                    <button type="button" title="Cari Supplier" onclick="handleSearch(document.getElementById('topSearchInput').value)">🔍</button>
                </div>

                <!-- RIGHT CONTROLS -->
                <div class="toolbar-right-actions">
                    <button class="action-tool-btn" id="btnToggleView" onclick="toggleListView()" title="Tampilan Tabel">
                        ☰
                    </button>
                    <button class="action-tool-btn" id="btnFilterActive" onclick="filterStatus('active')" title="Tampilkan Supplier Aktif">
                        👁️
                    </button>
                    <button class="action-tool-btn active-filter" id="btnFilterAll" onclick="filterStatus('all')" title="Tampilkan Semua Supplier">
                        👁️‍🗨️
                    </button>
                    <button class="btn-add-item" onclick="openAddSupplierModal()" title="Tambah Supplier Baru">
                        +
                    </button>
                </div>
            </div>

            <!-- DATATABLES CONTROL BAR (SHOW ENTRIES & INLINE SEARCH) -->
            <div class="datatable-controls-bar">
                <div class="entries-select-wrap">
                    Show 
                    <select id="entriesPerPageSelect" onchange="changeEntriesPerPage(this.value)">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select> 
                    entries
                </div>
                <div class="table-search-inline">
                    <label for="inlineSearchInput">Search:</label>
                    <input type="text" id="inlineSearchInput" oninput="handleSearch(this.value)">
                </div>
            </div>

            <!-- MAIN SUPPLIER TABLE CONTAINER -->
            <div class="table-card-container">
                <table class="supplier-table" id="supplierTable">
                    <thead>
                        <tr>
                            <th class="th-no" onclick="sortTable('no')">
                                No <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-kode" onclick="sortTable('code')">
                                Kode <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-nama" onclick="sortTable('name')">
                                Nama <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-kota" onclick="sortTable('city')">
                                Kota <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-telepon" onclick="sortTable('phone')">
                                Telephone <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-persone" onclick="sortTable('contact_person')">
                                Persone <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-npwp" onclick="sortTable('npwp')">
                                NPWP <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-nik" onclick="sortTable('nik')">
                                NIK <span class="sort-indicator">⇅</span>
                            </th>
                            <th class="th-action">
                                Action <span class="sort-indicator">⇅</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="supplierTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION FOOTER -->
            <div class="table-pagination-footer" id="paginationWrap">
                <button type="button" class="page-btn disabled" id="btnPrevPage" onclick="prevPage()">Previous</button>
                <button type="button" class="page-btn active" id="btnPage1">1</button>
                <button type="button" class="page-btn disabled" id="btnNextPage" onclick="nextPage()">Next</button>
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH SUPPLIER -->
    <div class="modal-overlay" id="addSupplierModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Tambah Supplier Baru</div>
                <button class="modal-close-btn" onclick="closeModal('addSupplierModal')">&times;</button>
            </div>
            <form id="formAddSupplier" onsubmit="submitAddSupplier(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="addSupplierCode">Kode Supplier</label>
                            <input type="text" id="addSupplierCode" name="code" class="form-control" placeholder="Contoh: 1, 2, SUP-01">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="addSupplierName">Nama Supplier <span style="color:red;">*</span></label>
                            <input type="text" id="addSupplierName" name="name" class="form-control" required placeholder="Contoh: softdrink, GENERIC">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="addSupplierCity">Kota</label>
                            <input type="text" id="addSupplierCity" name="city" class="form-control" placeholder="Contoh: yogyakarta">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="addSupplierCityNote">Subnote Kota / Keterangan</label>
                            <input type="text" id="addSupplierCityNote" name="city_note" class="form-control" placeholder="Contoh: maya, yogyakarta">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="addSupplierPhone">Telephone</label>
                            <input type="text" id="addSupplierPhone" name="phone" class="form-control" placeholder="Contoh: 123456">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="addSupplierContact">Persone (Kontak PIC)</label>
                            <input type="text" id="addSupplierContact" name="contact_person" class="form-control" placeholder="Contoh: aki / -">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="addSupplierNpwp">NPWP</label>
                            <input type="text" id="addSupplierNpwp" name="npwp" class="form-control" placeholder="Contoh: 123456 / -">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="addSupplierNik">NIK</label>
                            <input type="text" id="addSupplierNik" name="nik" class="form-control" placeholder="Opsional">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="addSupplierAddress">Alamat Lengkap</label>
                        <textarea id="addSupplierAddress" name="address" class="form-control" rows="2" placeholder="Alamat kantor / gudang supplier"></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="addSupplierActive" name="is_active" checked value="1">
                        <label for="addSupplierActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addSupplierModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitAdd">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT SUPPLIER -->
    <div class="modal-overlay" id="editSupplierModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Edit / Detail Data Supplier</div>
                <button class="modal-close-btn" onclick="closeModal('editSupplierModal')">&times;</button>
            </div>
            <form id="formEditSupplier" onsubmit="submitEditSupplier(event)">
                <input type="hidden" id="editSupplierId" name="id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editSupplierCode">Kode Supplier</label>
                            <input type="text" id="editSupplierCode" name="code" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editSupplierName">Nama Supplier <span style="color:red;">*</span></label>
                            <input type="text" id="editSupplierName" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editSupplierCity">Kota</label>
                            <input type="text" id="editSupplierCity" name="city" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editSupplierCityNote">Subnote Kota / Keterangan</label>
                            <input type="text" id="editSupplierCityNote" name="city_note" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editSupplierPhone">Telephone</label>
                            <input type="text" id="editSupplierPhone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editSupplierContact">Persone</label>
                            <input type="text" id="editSupplierContact" name="contact_person" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="editSupplierNpwp">NPWP</label>
                            <input type="text" id="editSupplierNpwp" name="npwp" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="editSupplierNik">NIK</label>
                            <input type="text" id="editSupplierNik" name="nik" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editSupplierAddress">Alamat Lengkap</label>
                        <textarea id="editSupplierAddress" name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="editSupplierActive" name="is_active" value="1">
                        <label for="editSupplierActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editSupplierModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitEdit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal-overlay" id="deleteSupplierModal">
        <div class="modal-card">
            <div class="modal-header" style="background: linear-gradient(to right, #c0392b, #e74c3c);">
                <div class="modal-title">Konfirmasi Hapus Supplier</div>
                <button class="modal-close-btn" onclick="closeModal('deleteSupplierModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13.5px; line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus supplier <strong id="deleteSupplierNameText" style="color: #c0392b;"></strong> dari sistem?
                </p>
                <input type="hidden" id="deleteSupplierId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('deleteSupplierModal')">Batal</button>
                <button type="button" class="btn-modal btn-modal-danger" id="btnConfirmDelete" onclick="executeDeleteSupplier()">Hapus</button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>

    <script>
        // Data supplier dari server
        let allSuppliers = @json($suppliers);
        let currentFilterStatus = 'all';
        let currentSearchQuery = '';
        let entriesPerPage = 50;
        let currentPage = 1;
        let sortColumn = 'id';
        let sortDirection = 'asc';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // TOGGLE SIDEBAR
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        });

        // LIVE TIME CLOCK (10:37:48 ON)
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

        // RENDER SUPPLIER TABLE
        function renderSupplierTable() {
            const tbody = document.getElementById('supplierTableBody');
            const topDataCount = document.getElementById('topDataCount');
            if (!tbody) return;

            // Filter data
            let filtered = allSuppliers.filter(s => {
                const query = currentSearchQuery.toLowerCase();
                const matchesSearch = !query ||
                    (s.name && s.name.toLowerCase().includes(query)) ||
                    (s.code && s.code.toLowerCase().includes(query)) ||
                    (s.city && s.city.toLowerCase().includes(query)) ||
                    (s.city_note && s.city_note.toLowerCase().includes(query)) ||
                    (s.phone && s.phone.toLowerCase().includes(query)) ||
                    (s.contact_person && s.contact_person.toLowerCase().includes(query)) ||
                    (s.npwp && s.npwp.toLowerCase().includes(query)) ||
                    (s.nik && s.nik.toLowerCase().includes(query));

                let matchesStatus = true;
                if (currentFilterStatus === 'active') {
                    matchesStatus = (s.is_active === true || s.is_active === 1);
                } else if (currentFilterStatus === 'inactive') {
                    matchesStatus = (s.is_active === false || s.is_active === 0);
                }

                return matchesSearch && matchesStatus;
            });

            if (topDataCount) {
                topDataCount.innerText = filtered.length;
            }

            // Sorting
            filtered.sort((a, b) => {
                let valA = a[sortColumn] ?? '';
                let valB = b[sortColumn] ?? '';

                if (typeof valA === 'string') valA = valA.toLowerCase();
                if (typeof valB === 'string') valB = valB.toLowerCase();

                if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
                if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            tbody.innerHTML = '';

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px 20px; color: #888;">
                            <div style="font-size: 28px; margin-bottom: 6px;">🏢</div>
                            <div style="font-size: 14px; font-weight: 600;">Tidak ada supplier ditemukan</div>
                            <div style="font-size: 12px; color: #999; margin-top: 4px;">Coba gunakan kata kunci pencarian yang lain.</div>
                        </td>
                    </tr>
                `;
                return;
            }

            // Pagination slice
            const total = filtered.length;
            const totalPages = Math.ceil(total / entriesPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * entriesPerPage;
            const endIdx = Math.min(startIdx + entriesPerPage, total);
            const pageItems = filtered.slice(startIdx, endIdx);

            pageItems.forEach((supplier, idx) => {
                const rowNo = startIdx + idx + 1;
                const cityPrimary = supplier.city ? escapeHtml(supplier.city) : '-';
                const citySubnote = supplier.city_note ? `<span class="city-subnote">${escapeHtml(supplier.city_note)}</span>` : '';
                const phoneDisplay = supplier.phone ? escapeHtml(supplier.phone) : '-';
                const contactDisplay = supplier.contact_person ? escapeHtml(supplier.contact_person) : '-';
                const npwpDisplay = supplier.npwp ? escapeHtml(supplier.npwp) : '-';
                const nikDisplay = supplier.nik ? escapeHtml(supplier.nik) : '';
                const codeDisplay = supplier.code ? escapeHtml(supplier.code) : '';

                const tr = document.createElement('tr');
                tr.id = `supplier-row-${supplier.id}`;
                tr.innerHTML = `
                    <td class="td-no">${rowNo}</td>
                    <td class="td-kode">${codeDisplay}</td>
                    <td class="td-nama" onclick="openEditSupplierModal(${supplier.id})">
                        ${escapeHtml(supplier.name)}
                    </td>
                    <td class="td-kota">
                        <span class="city-primary">${cityPrimary}</span>
                        ${citySubnote}
                    </td>
                    <td class="td-telepon">${phoneDisplay}</td>
                    <td class="td-persone">${contactDisplay}</td>
                    <td class="td-npwp">${npwpDisplay}</td>
                    <td class="td-nik">${nikDisplay}</td>
                    <td class="td-action">
                        <button type="button" class="btn-row-action" title="Lihat / Edit Supplier" onclick="openEditSupplierModal(${supplier.id})">
                            👁️
                        </button>
                        <button type="button" class="btn-row-action btn-del" title="Hapus Supplier" onclick="openDeleteSupplierModal(${supplier.id}, '${escapeQuote(supplier.name)}')">
                            🗑️
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update pagination buttons
            const btnPrev = document.getElementById('btnPrevPage');
            const btnNext = document.getElementById('btnNextPage');
            const btnP1 = document.getElementById('btnPage1');

            if (btnPrev && btnNext && btnP1) {
                btnP1.innerText = currentPage;
                if (currentPage <= 1) {
                    btnPrev.classList.add('disabled');
                } else {
                    btnPrev.classList.remove('disabled');
                }

                if (currentPage >= totalPages) {
                    btnNext.classList.add('disabled');
                } else {
                    btnNext.classList.remove('disabled');
                }
            }
        }

        // SORTING
        function sortTable(col) {
            if (sortColumn === col) {
                sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
            } else {
                sortColumn = col;
                sortDirection = 'asc';
            }
            renderSupplierTable();
        }

        // ENTRIES PER PAGE
        function changeEntriesPerPage(val) {
            entriesPerPage = parseInt(val, 10) || 50;
            currentPage = 1;
            renderSupplierTable();
        }

        function prevPage() {
            if (currentPage > 1) {
                currentPage--;
                renderSupplierTable();
            }
        }

        function nextPage() {
            const filteredCount = allSuppliers.length;
            const totalPages = Math.ceil(filteredCount / entriesPerPage) || 1;
            if (currentPage < totalPages) {
                currentPage++;
                renderSupplierTable();
            }
        }

        // SEARCH
        function handleSearch(val) {
            currentSearchQuery = val ? val.trim() : '';
            currentPage = 1;
            // Sync both search inputs
            const topInput = document.getElementById('topSearchInput');
            const inlineInput = document.getElementById('inlineSearchInput');
            if (topInput && topInput.value !== val) topInput.value = val;
            if (inlineInput && inlineInput.value !== val) inlineInput.value = val;

            renderSupplierTable();
        }

        // FILTER STATUS
        function filterStatus(status) {
            currentFilterStatus = status;
            const btnActive = document.getElementById('btnFilterActive');
            const btnAll = document.getElementById('btnFilterAll');

            if (status === 'active') {
                btnActive.classList.add('active-filter');
                btnAll.classList.remove('active-filter');
                showToast('Menampilkan Supplier Aktif', 'info');
            } else {
                btnActive.classList.remove('active-filter');
                btnAll.classList.add('active-filter');
                showToast('Menampilkan Semua Supplier', 'info');
            }
            currentPage = 1;
            renderSupplierTable();
        }

        function toggleListView() {
            showToast('Tampilan Daftar Aktif', 'info');
        }

        // MODAL HELPERS
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // TAMBAH SUPPLIER MODAL & AJAX
        function openAddSupplierModal() {
            document.getElementById('formAddSupplier').reset();
            document.getElementById('addSupplierActive').checked = true;
            document.getElementById('addSupplierContact').value = '-';
            document.getElementById('addSupplierNpwp').value = '-';
            openModal('addSupplierModal');
            setTimeout(() => document.getElementById('addSupplierName').focus(), 150);
        }

        function submitAddSupplier(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitAdd');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const payload = {
                code: document.getElementById('addSupplierCode').value.trim(),
                name: document.getElementById('addSupplierName').value.trim(),
                city: document.getElementById('addSupplierCity').value.trim(),
                city_note: document.getElementById('addSupplierCityNote').value.trim(),
                phone: document.getElementById('addSupplierPhone').value.trim(),
                contact_person: document.getElementById('addSupplierContact').value.trim(),
                npwp: document.getElementById('addSupplierNpwp').value.trim(),
                nik: document.getElementById('addSupplierNik').value.trim(),
                address: document.getElementById('addSupplierAddress').value.trim(),
                is_active: document.getElementById('addSupplierActive').checked
            };

            fetch("{{ route('admin.supplier.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Supplier';

                if (data.success && data.supplier) {
                    allSuppliers.push(data.supplier);
                    closeModal('addSupplierModal');
                    showToast(data.message || 'Supplier berhasil ditambahkan', 'success');
                    renderSupplierTable();
                } else {
                    showToast(data.message || 'Gagal menyimpan supplier', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Simpan Supplier';
                showToast('Terjadi kesalahan validasi atau jaringan', 'error');
            });
        }

        // EDIT SUPPLIER MODAL & AJAX
        function openEditSupplierModal(id) {
            const supplier = allSuppliers.find(s => s.id === id);
            if (!supplier) return;

            document.getElementById('editSupplierId').value = supplier.id;
            document.getElementById('editSupplierCode').value = supplier.code || '';
            document.getElementById('editSupplierName').value = supplier.name || '';
            document.getElementById('editSupplierCity').value = supplier.city || '';
            document.getElementById('editSupplierCityNote').value = supplier.city_note || '';
            document.getElementById('editSupplierPhone').value = supplier.phone || '';
            document.getElementById('editSupplierContact').value = supplier.contact_person || '';
            document.getElementById('editSupplierNpwp').value = supplier.npwp || '';
            document.getElementById('editSupplierNik').value = supplier.nik || '';
            document.getElementById('editSupplierAddress').value = supplier.address || '';
            document.getElementById('editSupplierActive').checked = (supplier.is_active === true || supplier.is_active === 1);

            openModal('editSupplierModal');
            setTimeout(() => document.getElementById('editSupplierName').focus(), 150);
        }

        function submitEditSupplier(event) {
            event.preventDefault();
            const id = document.getElementById('editSupplierId').value;
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const payload = {
                code: document.getElementById('editSupplierCode').value.trim(),
                name: document.getElementById('editSupplierName').value.trim(),
                city: document.getElementById('editSupplierCity').value.trim(),
                city_note: document.getElementById('editSupplierCityNote').value.trim(),
                phone: document.getElementById('editSupplierPhone').value.trim(),
                contact_person: document.getElementById('editSupplierContact').value.trim(),
                npwp: document.getElementById('editSupplierNpwp').value.trim(),
                nik: document.getElementById('editSupplierNik').value.trim(),
                address: document.getElementById('editSupplierAddress').value.trim(),
                is_active: document.getElementById('editSupplierActive').checked
            };

            fetch(`{{ url('admin/supplier') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Perubahan';

                if (data.success && data.supplier) {
                    const idx = allSuppliers.findIndex(s => s.id == id);
                    if (idx !== -1) {
                        allSuppliers[idx] = data.supplier;
                    }
                    closeModal('editSupplierModal');
                    showToast(data.message || 'Data supplier berhasil diperbarui', 'success');
                    renderSupplierTable();
                } else {
                    showToast(data.message || 'Gagal memperbarui supplier', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Simpan Perubahan';
                showToast('Terjadi kesalahan server saat memperbarui', 'error');
            });
        }

        // DELETE SUPPLIER MODAL & AJAX
        function openDeleteSupplierModal(id, name) {
            document.getElementById('deleteSupplierId').value = id;
            document.getElementById('deleteSupplierNameText').innerText = `"${name}"`;
            openModal('deleteSupplierModal');
        }

        function executeDeleteSupplier() {
            const id = document.getElementById('deleteSupplierId').value;
            const btn = document.getElementById('btnConfirmDelete');
            btn.disabled = true;
            btn.innerText = 'Menghapus...';

            fetch(`{{ url('admin/supplier') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Hapus';

                if (data.success) {
                    allSuppliers = allSuppliers.filter(s => s.id != id);
                    closeModal('deleteSupplierModal');
                    showToast(data.message || 'Supplier berhasil dihapus', 'success');
                    renderSupplierTable();
                } else {
                    showToast(data.message || 'Gagal menghapus supplier', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Hapus';
                showToast('Terjadi kesalahan saat menghapus', 'error');
            });
        }

        // TOAST NOTIFIKASI
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toastBox');
            const msgEl = document.getElementById('toastMessage');
            const iconEl = document.getElementById('toastIcon');

            toast.className = 'toast-box';
            if (type === 'success') {
                toast.classList.add('toast-success');
                iconEl.innerText = '✔';
            } else if (type === 'error') {
                toast.classList.add('toast-error');
                iconEl.innerText = '✖';
            } else {
                iconEl.innerText = 'ℹ';
            }

            msgEl.innerText = msg;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // UTILITY ESCAPE HTML
        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function escapeQuote(str) {
            if (!str) return '';
            return str.replace(/'/g, "\\'").replace(/"/g, "&quot;");
        }

        // CLOSE MODALS ON CLICK OUTSIDE OR ESCAPE
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
            }
        });

        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
            }
        });

        // INIT
        document.addEventListener('DOMContentLoaded', function() {
            renderSupplierTable();

            // Hash router checks
            if (window.location.hash === '#jadwal') {
                window.location.href = "{{ route('admin.jadwal') }}#jadwal";
            }
            if (window.location.hash === '#merk') {
                window.location.href = "{{ route('admin.merk') }}#merk";
            }
            if (window.location.hash === '#karyawan') {
                window.location.href = "{{ route('admin.karyawan') }}#karyawan";
            }
        });
    </script>
</body>
</html>
