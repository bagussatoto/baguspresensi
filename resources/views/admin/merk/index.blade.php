<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Daftar Merk</title>
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

        /* CENTER PAGE TITLE */
        .page-title-center {
            text-align: center;
            font-size: 20px;
            font-weight: 500;
            color: #333333;
            margin: 6px 0 16px;
            letter-spacing: 0.5px;
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

        .btn-add-brand {
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

        .btn-add-brand:hover {
            background: #00acc1;
            transform: scale(1.05);
        }

        /* 3-COLUMN TABLE GRID LAYOUT */
        .merk-columns-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            align-items: start;
        }

        .merk-columns-container.layout-2col {
            grid-template-columns: repeat(2, 1fr);
        }

        .merk-columns-container.layout-1col {
            grid-template-columns: 1fr;
        }

        .merk-column-box {
            background: #ffffff;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #d2d6de;
            overflow: hidden;
        }

        .merk-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .merk-table thead th {
            background-color: var(--table-header-teal);
            color: #ffffff;
            font-weight: 700;
            padding: 8px 10px;
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.2);
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        .merk-table thead th:last-child {
            border-right: none;
        }

        .merk-table thead th.th-no {
            width: 45px;
            text-align: center;
        }

        .merk-table thead th.th-kode {
            width: 85px;
            text-align: center;
        }

        .merk-table thead th.th-nama {
            text-align: left;
        }

        .merk-table thead th.th-status {
            width: 75px;
            text-align: center;
        }

        .merk-table tbody tr {
            border-bottom: 1px solid #edf1f5;
            transition: background 0.15s;
        }

        .merk-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .merk-table tbody tr:hover {
            background-color: #eef7f8;
        }

        .merk-table tbody td {
            padding: 6px 10px;
            vertical-align: middle;
            color: #333333;
        }

        .merk-table tbody td.td-no {
            text-align: center;
            color: #555555;
            font-weight: 500;
        }

        .merk-table tbody td.td-kode {
            text-align: center;
            font-weight: 600;
            color: #2c7ea1;
            letter-spacing: 0.5px;
        }

        .merk-table tbody td.td-nama {
            font-weight: 500;
            color: #222222;
        }

        .merk-table tbody td.td-status {
            text-align: center;
            white-space: nowrap;
        }

        /* ROW ACTION BUTTONS */
        .btn-row-action {
            background: transparent;
            border: 1px solid transparent;
            padding: 2px 5px;
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

        .badge-inactive {
            display: inline-block;
            font-size: 10px;
            color: #721c24;
            background: #f8d7da;
            border-radius: 2px;
            padding: 1px 4px;
            margin-left: 4px;
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
            max-width: 460px;
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
            padding: 20px;
        }

        .form-group {
            margin-bottom: 14px;
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
            padding: 8px 12px;
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

        /* TOAST NOTIFICATION */
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
        @media (max-width: 1200px) {
            .merk-columns-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-sidebar {
                transform: translateX(-230px);
            }
            .sub-navbar,
            .content-wrapper {
                margin-left: 0;
                left: 0;
            }
            .merk-columns-container {
                grid-template-columns: 1fr;
            }
            body.sidebar-mobile-open .main-sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

    <!-- TOP HEADER (PERSIS SCREENSHOT MGK ERP) -->
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
                    <a href="{{ route('admin.merk') }}#merk">
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
            <!-- PAGE TITLE (CENTER) -->
            <h1 class="page-title-center">daftar merk</h1>

            <!-- TOOLBAR: SEARCH & ACTIONS -->
            <div class="filter-toolbar-box">
                <!-- SEARCH INPUT -->
                <div class="search-input-group">
                    <input type="text" id="searchInput" placeholder="Search..." oninput="handleSearch(this.value)">
                    <button type="button" title="Cari Merk" onclick="handleSearch(document.getElementById('searchInput').value)">🔍</button>
                </div>

                <!-- RIGHT CONTROLS -->
                <div class="toolbar-right-actions">
                    <button class="action-tool-btn" id="btnToggleLayout" onclick="toggleColumnLayout()" title="Ganti Layout Kolom (3 / 2 / 1 Kolom)">
                        <strong>=</strong>
                    </button>
                    <button class="action-tool-btn" id="btnFilterActive" onclick="filterStatus('active')" title="Tampilkan Merk Aktif">
                        👁️
                    </button>
                    <button class="action-tool-btn" id="btnFilterAll" onclick="filterStatus('all')" title="Tampilkan Semua / Reset Filter">
                        👁️‍🗨️
                    </button>
                    <button class="btn-add-brand" onclick="openAddBrandModal()" title="Tambah Merk Baru">
                        +
                    </button>
                </div>
            </div>

            <!-- 3-COLUMN TABLES CONTAINER -->
            <div class="merk-columns-container" id="merkGridContainer">
                <!-- Di-render dinamis oleh JavaScript agar terdistribusi merata dalam 3 kolom persis seperti di screenshot -->
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH MERK -->
    <div class="modal-overlay" id="addBrandModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Tambah Merk Baru</div>
                <button class="modal-close-btn" onclick="closeModal('addBrandModal')">&times;</button>
            </div>
            <form id="formAddBrand" onsubmit="submitAddBrand(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="addBrandName">Nama Merk <span style="color:red;">*</span></label>
                        <input type="text" id="addBrandName" name="name" class="form-control" required placeholder="Contoh: Kingston, Logitech, Advance">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="addBrandCode">Kode Merk (Opsional)</label>
                        <input type="text" id="addBrandCode" name="code" class="form-control" placeholder="Contoh: RA, ISOTONIK, BRD-01">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="addBrandActive" name="is_active" checked value="1">
                        <label for="addBrandActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addBrandModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitAdd">Simpan Merk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT / DETAIL MERK -->
    <div class="modal-overlay" id="editBrandModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Edit / Detail Data Merk</div>
                <button class="modal-close-btn" onclick="closeModal('editBrandModal')">&times;</button>
            </div>
            <form id="formEditBrand" onsubmit="submitEditBrand(event)">
                <input type="hidden" id="editBrandId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="editBrandName">Nama Merk <span style="color:red;">*</span></label>
                        <input type="text" id="editBrandName" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editBrandCode">Kode Merk (Opsional)</label>
                        <input type="text" id="editBrandCode" name="code" class="form-control">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" id="editBrandActive" name="is_active" value="1">
                        <label for="editBrandActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editBrandModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitEdit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal-overlay" id="deleteBrandModal">
        <div class="modal-card">
            <div class="modal-header" style="background: linear-gradient(to right, #c0392b, #e74c3c);">
                <div class="modal-title">Konfirmasi Hapus Merk</div>
                <button class="modal-close-btn" onclick="closeModal('deleteBrandModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13.5px; line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus merk <strong id="deleteBrandNameText" style="color: #c0392b;"></strong> dari sistem?
                </p>
                <input type="hidden" id="deleteBrandId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('deleteBrandModal')">Batal</button>
                <button type="button" class="btn-modal btn-modal-danger" id="btnConfirmDelete" onclick="executeDeleteBrand()">Hapus</button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>

    <script>
        // Data inisial merk dari server
        let allBrands = @json($brands);
        let currentFilterStatus = 'all';
        let currentSearchQuery = '';
        let currentLayout = '3col'; // '3col', '2col', '1col'

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

        // RENDER 3-COLUMN TABLE GRID
        function renderBrandGrid() {
            const container = document.getElementById('merkGridContainer');
            if (!container) return;

            // Filter data sesuai search dan status
            let filtered = allBrands.filter(b => {
                const matchesSearch = !currentSearchQuery || 
                    (b.name && b.name.toLowerCase().includes(currentSearchQuery.toLowerCase())) ||
                    (b.code && b.code.toLowerCase().includes(currentSearchQuery.toLowerCase()));

                let matchesStatus = true;
                if (currentFilterStatus === 'active') {
                    matchesStatus = b.is_active === true || b.is_active === 1;
                } else if (currentFilterStatus === 'inactive') {
                    matchesStatus = b.is_active === false || b.is_active === 0;
                }

                return matchesSearch && matchesStatus;
            });

            container.innerHTML = '';

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: #fff; border-radius: 4px; border: 1px solid #d2d6de; color: #777;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🏷️</div>
                        <div style="font-size: 15px; font-weight: 600;">Tidak ada merk ditemukan</div>
                        <div style="font-size: 12px; color: #999; margin-top: 4px;">Coba ubah kata kunci pencarian atau reset filter.</div>
                    </div>
                `;
                return;
            }

            // Tentukan jumlah kolom berdasarkan layout aktif
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
                colBox.className = 'merk-column-box';

                let tableHtml = `
                    <table class="merk-table">
                        <thead>
                            <tr>
                                <th class="th-no">No</th>
                                <th class="th-kode">Kode</th>
                                <th class="th-nama">Nama</th>
                                <th class="th-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                colItems.forEach((brand, idxInCol) => {
                    const globalNo = startIdx + idxInCol + 1;
                    const kodeDisplay = brand.code ? escapeHtml(brand.code) : '';
                    const inactiveBadge = (!brand.is_active) ? `<span class="badge-inactive">Non-Aktif</span>` : '';

                    tableHtml += `
                        <tr id="brand-row-${brand.id}">
                            <td class="td-no">${globalNo}</td>
                            <td class="td-kode">${kodeDisplay}</td>
                            <td class="td-nama">
                                ${escapeHtml(brand.name)} ${inactiveBadge}
                            </td>
                            <td class="td-status">
                                <button type="button" class="btn-row-action" title="Lihat / Edit Merk" onclick="openEditBrandModal(${brand.id})">
                                    👁️
                                </button>
                                <button type="button" class="btn-row-action btn-del" title="Hapus Merk" onclick="openDeleteBrandModal(${brand.id}, '${escapeQuote(brand.name)}')">
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

        // TOGGLE LAYOUT (3-kolom / 2-kolom / 1-kolom)
        function toggleColumnLayout() {
            const container = document.getElementById('merkGridContainer');
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
            renderBrandGrid();
        }

        // FILTER STATUS
        function filterStatus(status) {
            currentFilterStatus = status;
            const btnActive = document.getElementById('btnFilterActive');
            const btnAll = document.getElementById('btnFilterAll');

            if (status === 'active') {
                btnActive.classList.add('active-filter');
                btnAll.classList.remove('active-filter');
                showToast('Menampilkan Merk Aktif', 'info');
            } else {
                btnActive.classList.remove('active-filter');
                btnAll.classList.add('active-filter');
                showToast('Menampilkan Semua Merk', 'info');
            }
            renderBrandGrid();
        }

        // SEARCH HANDLER
        function handleSearch(val) {
            currentSearchQuery = val ? val.trim() : '';
            renderBrandGrid();
        }

        // MODAL HELPERS
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // TAMBAH MERK MODAL & AJAX
        function openAddBrandModal() {
            document.getElementById('formAddBrand').reset();
            document.getElementById('addBrandActive').checked = true;
            openModal('addBrandModal');
            setTimeout(() => document.getElementById('addBrandName').focus(), 150);
        }

        function submitAddBrand(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitAdd');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const name = document.getElementById('addBrandName').value.trim();
            const code = document.getElementById('addBrandCode').value.trim();
            const isActive = document.getElementById('addBrandActive').checked;

            fetch("{{ route('admin.merk.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: name,
                    code: code,
                    is_active: isActive
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Merk';

                if (data.success && data.brand) {
                    allBrands.push(data.brand);
                    allBrands.sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }));
                    closeModal('addBrandModal');
                    showToast(data.message || 'Merk berhasil ditambahkan', 'success');
                    renderBrandGrid();
                } else {
                    showToast(data.message || 'Gagal menyimpan merk', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Simpan Merk';
                showToast('Terjadi kesalahan jaringan atau validasi', 'error');
            });
        }

        // EDIT MERK MODAL & AJAX
        function openEditBrandModal(id) {
            const brand = allBrands.find(b => b.id === id);
            if (!brand) return;

            document.getElementById('editBrandId').value = brand.id;
            document.getElementById('editBrandName').value = brand.name || '';
            document.getElementById('editBrandCode').value = brand.code || '';
            document.getElementById('editBrandActive').checked = (brand.is_active === true || brand.is_active === 1);

            openModal('editBrandModal');
            setTimeout(() => document.getElementById('editBrandName').focus(), 150);
        }

        function submitEditBrand(event) {
            event.preventDefault();
            const id = document.getElementById('editBrandId').value;
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const name = document.getElementById('editBrandName').value.trim();
            const code = document.getElementById('editBrandCode').value.trim();
            const isActive = document.getElementById('editBrandActive').checked;

            fetch(`{{ url('admin/merk') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: name,
                    code: code,
                    is_active: isActive
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Perubahan';

                if (data.success && data.brand) {
                    const idx = allBrands.findIndex(b => b.id == id);
                    if (idx !== -1) {
                        allBrands[idx] = data.brand;
                        allBrands.sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }));
                    }
                    closeModal('editBrandModal');
                    showToast(data.message || 'Data merk berhasil diperbarui', 'success');
                    renderBrandGrid();
                } else {
                    showToast(data.message || 'Gagal memperbarui merk', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Simpan Perubahan';
                showToast('Terjadi kesalahan koneksi server', 'error');
            });
        }

        // DELETE MERK MODAL & AJAX
        function openDeleteBrandModal(id, name) {
            document.getElementById('deleteBrandId').value = id;
            document.getElementById('deleteBrandNameText').innerText = `"${name}"`;
            openModal('deleteBrandModal');
        }

        function executeDeleteBrand() {
            const id = document.getElementById('deleteBrandId').value;
            const btn = document.getElementById('btnConfirmDelete');
            btn.disabled = true;
            btn.innerText = 'Menghapus...';

            fetch(`{{ url('admin/merk') }}/${id}`, {
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
                    allBrands = allBrands.filter(b => b.id != id);
                    closeModal('deleteBrandModal');
                    showToast(data.message || 'Merk berhasil dihapus', 'success');
                    renderBrandGrid();
                } else {
                    showToast(data.message || 'Gagal menghapus merk', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Hapus';
                showToast('Terjadi kesalahan server saat menghapus', 'error');
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
            renderBrandGrid();

            // Handle hashtag in URL jika user akses #supplier, #jadwal, atau #karyawan
            if (window.location.hash === '#supplier') {
                window.location.href = "{{ route('admin.supplier') }}#supplier";
            }
            if (window.location.hash === '#jadwal') {
                window.location.href = "{{ route('admin.jadwal') }}#jadwal";
            }
            if (window.location.hash === '#karyawan') {
                window.location.href = "{{ route('admin.karyawan') }}#karyawan";
            }
        });
    </script>
</body>
</html>
