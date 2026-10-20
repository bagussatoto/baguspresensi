<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Penjualan & Kasir POS</title>
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

        /* HORIZONTAL SUB-NAVBAR */
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

        /* USER PANEL */
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
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .stat-chip.chip-green {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .stat-chip.chip-blue {
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            color: #0369a1;
        }

        .stat-chip.chip-orange {
            background: #ffedd5;
            border: 1px solid #fed7aa;
            color: #c2410c;
        }

        .stat-chip.chip-purple {
            background: #f3e8ff;
            border: 1px solid #e9d5ff;
            color: #7e22ce;
        }

        /* FILTER & SEARCH TOOLBAR */
        .filter-toolbar-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
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
            max-width: 380px;
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

        .select-filter-item {
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

        .select-filter-item:focus {
            border-color: #17a2b8;
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

        .btn-add-sale {
            background: #00bcd4;
            border: 1px solid #00acc1;
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 188, 212, 0.4);
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            height: 32px;
        }

        .btn-add-sale:hover {
            background: #00acc1;
            transform: translateY(-1px);
        }

        /* SALES TABLE CARD */
        .table-card-box {
            background: #ffffff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #d2d6de;
            overflow: hidden;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .sales-table thead th {
            background-color: var(--table-header-teal);
            color: #ffffff;
            font-weight: 700;
            padding: 9px 10px;
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.2);
            font-size: 12px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .sales-table thead th:last-child {
            border-right: none;
        }

        .sales-table tbody tr {
            border-bottom: 1px solid #edf1f5;
            transition: background 0.15s;
        }

        .sales-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .sales-table tbody tr:hover {
            background-color: #eef7f8;
        }

        .sales-table tbody td {
            padding: 8px 10px;
            vertical-align: middle;
            color: #333333;
        }

        .td-invoice {
            font-family: monospace, monospace;
            font-weight: 700;
            color: #0284c7;
            font-size: 12px;
            white-space: nowrap;
        }

        .td-datetime {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .badge-method {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-method-cash {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-method-qris {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-method-transfer {
            background: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }

        .badge-method-debit {
            background: #ffedd5;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-status-paid {
            background: #28a745;
            color: #ffffff;
        }

        .badge-status-pending {
            background: #ffc107;
            color: #212529;
        }

        .badge-status-cancelled {
            background: #dc3545;
            color: #ffffff;
        }

        .item-chip-mini {
            display: inline-block;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 10.5px;
            margin: 1px 2px 1px 0;
        }

        .item-chip-billing {
            background: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }

        /* ACTION BUTTONS */
        .btn-action-sm {
            background: transparent;
            border: 1px solid transparent;
            padding: 3px 6px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            transition: all 0.15s;
        }

        .btn-action-sm:hover {
            background: #e2e8f0;
            color: #1e293b;
            border-color: #cbd5e1;
        }

        .btn-action-sm.btn-del:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        /* MODALS */
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
            width: 92%;
            max-width: 650px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
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
            overflow-y: auto;
            flex: 1;
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

        .form-row-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #475569;
        }

        .form-control {
            width: 100%;
            padding: 7px 10px;
            font-size: 13px;
            border: 1px solid #ccd0d4;
            border-radius: 3px;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #3c8dbc;
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

        /* RECEIPT PREVIEW STYLING */
        .receipt-card {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            border-radius: 4px;
            color: #1e293b;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .receipt-company {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .receipt-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 12px 0;
        }

        .receipt-table th {
            text-align: left;
            border-bottom: 1px dashed #cbd5e1;
            padding: 4px 0;
        }

        .receipt-table td {
            padding: 4px 0;
        }

        .receipt-summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            padding: 3px 0;
        }

        .receipt-summary-row.total-bold {
            font-weight: 700;
            font-size: 14px;
            border-top: 1px dashed #cbd5e1;
            border-bottom: 1px dashed #cbd5e1;
            padding: 6px 0;
            margin: 6px 0;
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
        @media (max-width: 768px) {
            .sub-navbar { left: 0; }
            .main-sidebar { transform: translateX(-100%); }
            .content-wrapper { margin-left: 0; }
            body.sidebar-mobile-open .main-sidebar { transform: translateX(0); }
            .form-row-grid { grid-template-columns: 1fr; }
        }

        body.sidebar-collapsed .main-sidebar { width: 0; transform: translateX(-100%); }
        body.sidebar-collapsed .sub-navbar,
        body.sidebar-collapsed .content-wrapper { margin-left: 0; left: 0; }
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
                <li>
                    <a href="{{ route('admin.kategori') }}">
                        <span class="menu-item-left">📁 Kategori Produk</span>
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
                <li class="active">
                    <a href="{{ route('admin.penjualan') }}">
                        <span class="menu-item-left">🛒 Penjualan & POS Kasir</span>
                        <span class="menu-badge" id="menuBadgeCount">{{ $sales->count() }}</span>
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
            <h1 class="page-title-center">🛒 DAFTAR TRANSAKSI PENJUALAN & POS KASIR</h1>
            <p class="page-subtitle-center">Rekapitulasi penjualan billing warnet, pesanan F&B, ledger kasir & pembayaran Maya Graha Kencana ERP</p>

            <!-- STATS RIBBON -->
            <div class="stats-ribbon-bar">
                <div class="stats-ribbon-left">
                    <span>🏢 Maya Graha Kencana</span>
                    <span style="color: #94a3b8;">/</span>
                    <span>Ledger Penjualan Kasir</span>
                </div>
                <div class="stats-ribbon-chips">
                    <span class="stat-chip chip-green">
                        💰 Total Omzet: <strong>Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}</strong>
                    </span>
                    <span class="stat-chip chip-blue">
                        🖥️ Billing PC: <strong>Rp {{ number_format($metrics['total_billing'], 0, ',', '.') }}</strong>
                    </span>
                    <span class="stat-chip chip-orange">
                        🍔 F&B: <strong>Rp {{ number_format($metrics['total_fnb'], 0, ',', '.') }}</strong>
                    </span>
                    <span class="stat-chip chip-purple">
                        🧾 Total Trx: <strong>{{ $metrics['total_count'] }} ({{ $metrics['paid_count'] }} Lunas)</strong>
                    </span>
                </div>
            </div>

            <!-- TOOLBAR: SEARCH & ACTIONS -->
            <div class="filter-toolbar-box">
                <!-- SEARCH INPUT -->
                <div class="search-input-group">
                    <input type="text" id="searchInput" placeholder="Cari invoice, pelanggan, kasir, item..." oninput="handleSearch(this.value)">
                    <button type="button" title="Cari Transaksi" onclick="handleSearch(document.getElementById('searchInput').value)">🔍</button>
                </div>

                <!-- RIGHT CONTROLS -->
                <div class="toolbar-right-actions">
                    <!-- FILTER STATUS -->
                    <select class="select-filter-item" id="selectStatusFilter" onchange="filterStatus(this.value)">
                        <option value="all">Semua Status</option>
                        <option value="paid">Lunas (Paid)</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>

                    <!-- FILTER METODE BAYAR -->
                    <select class="select-filter-item" id="selectMethodFilter" onchange="filterMethod(this.value)">
                        <option value="all">Semua Metode</option>
                        <option value="cash">💵 Cash / Tunai</option>
                        <option value="qris">📱 QRIS BCA/Mandiri</option>
                        <option value="transfer">💳 Transfer Bank</option>
                        <option value="debit">🏧 Kartu Debit</option>
                    </select>

                    <!-- FILTER KASIR -->
                    <select class="select-filter-item" id="selectCashierFilter" onchange="filterCashier(this.value)">
                        <option value="all">Semua Kasir</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>

                    <!-- TOMBOL TAMBAH PENJUALAN BARU -->
                    <button class="btn-add-sale" onclick="openAddSaleModal()" title="Input Penjualan / Transaksi Kasir Baru">
                        + Transaksi Baru
                    </button>
                </div>
            </div>

            <!-- SALES TABLE CONTAINER -->
            <div class="table-card-box">
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 170px;">Invoice & Waktu</th>
                            <th>Pelanggan & Unit</th>
                            <th>Kasir</th>
                            <th>Item Pesanan</th>
                            <th style="text-align: right;">Billing</th>
                            <th style="text-align: right;">F&B</th>
                            <th style="text-align: right; width: 110px;">Total Bersih</th>
                            <th style="text-align: center; width: 95px;">Metode</th>
                            <th style="text-align: center; width: 90px;">Status</th>
                            <th style="text-align: center; width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        <!-- Di-render dinamis oleh JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL DETAIL TRANSAKSI & STRUK KASIR -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">🧾 Struk Bukti Transaksi Penjualan</div>
                <button class="modal-close-btn" onclick="closeModal('detailModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="receipt-card" id="receiptContent">
                    <div class="receipt-header">
                        <div class="receipt-company">MAYA GRAHA KENCANA</div>
                        <div class="receipt-sub">Jl. Boulevard Kelapa Gading Blok M No. 18, Jakarta Utara</div>
                        <div class="receipt-sub">Telp: 021-45879900 • NPWP: 01.445.678.9-012.000</div>
                    </div>
                    <div style="font-size: 11px; margin-bottom: 8px; line-height: 1.5;">
                        <div>No. Invoice : <strong id="recInvoice">INV-NET-...</strong></div>
                        <div>Tanggal     : <span id="recDate">-</span></div>
                        <div>Kasir       : <span id="recCashier">-</span></div>
                        <div>Pelanggan   : <span id="recCustomer">-</span></div>
                        <div>Unit / PC   : <span id="recStation">-</span></div>
                    </div>
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="text-align: center;">Qty</th>
                                <th style="text-align: right;">Harga</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="recItemsBody">
                            <!-- Items -->
                        </tbody>
                    </table>
                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                        <div class="receipt-summary-row">
                            <span>Total Billing PC:</span>
                            <span id="recTotalBilling">Rp 0</span>
                        </div>
                        <div class="receipt-summary-row">
                            <span>Total F&B / Snack:</span>
                            <span id="recTotalFnb">Rp 0</span>
                        </div>
                        <div class="receipt-summary-row total-bold">
                            <span>TOTAL TAGIHAN:</span>
                            <span id="recGrandTotal">Rp 0</span>
                        </div>
                        <div class="receipt-summary-row">
                            <span>Metode Bayar:</span>
                            <span id="recMethod">Cash</span>
                        </div>
                        <div class="receipt-summary-row">
                            <span>Nominal Dibayar:</span>
                            <span id="recAmountPaid">Rp 0</span>
                        </div>
                        <div class="receipt-summary-row">
                            <span>Kembalian:</span>
                            <span id="recChange">Rp 0</span>
                        </div>
                    </div>
                    <div style="text-align: center; font-size: 11px; color: #64748b; margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 10px;">
                        Terima Kasih atas Kunjungan Anda di MGK Warnet & Cafe!<br>
                        <em>Barang yang sudah dibeli tidak dapat ditukar.</em>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('detailModal')">Tutup</button>
                <button type="button" class="btn-modal btn-modal-primary" id="btnReceiptPrint" onclick="printReceiptPopup()">🖨️ Cetak Struk</button>
            </div>
        </div>
    </div>

    <!-- MODAL TRANSAKSI PENJUALAN BARU -->
    <div class="modal-overlay" id="addSaleModal">
        <div class="modal-card" style="max-width: 680px;">
            <div class="modal-header">
                <div class="modal-title">🛒 Input Penjualan / Transaksi Kasir Baru</div>
                <button class="modal-close-btn" onclick="closeModal('addSaleModal')">&times;</button>
            </div>
            <form id="formAddSale" onsubmit="submitAddSale(event)">
                <div class="modal-body">
                    <div class="form-row-grid">
                        <div class="form-group">
                            <label class="form-label" for="addCashier">Kasir / Operator Bertugas <span style="color:red;">*</span></label>
                            <select id="addCashier" name="employee_id" class="form-control" required>
                                @foreach ($employees as $emp)
                                    @php
                                        $roleValue = $emp->role instanceof \BackedEnum ? $emp->role->value : (string) $emp->role;
                                    @endphp
                                    <option value="{{ $emp->id }}" {{ $roleValue === 'kasir' ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ strtoupper($roleValue) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="addCustomer">Nama Pelanggan <span style="color:red;">*</span></label>
                            <input type="text" id="addCustomer" name="customer_name" class="form-control" required value="Pelanggan Walk-in">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-group">
                            <label class="form-label" for="addPcStation">Unit PC Billing (Opsional)</label>
                            <select id="addPcStation" name="pc_station_id" class="form-control">
                                <option value="">— Bukan Billing PC / Non-PC —</option>
                                @foreach ($pcStations as $pc)
                                    <option value="{{ $pc->id }}">{{ $pc->station_code }} ({{ $pc->station_type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="addPaymentMethod">Metode Pembayaran <span style="color:red;">*</span></label>
                            <select id="addPaymentMethod" name="payment_method" class="form-control" required>
                                <option value="cash">💵 Cash / Tunai</option>
                                <option value="qris">📱 QRIS BCA / Mandiri</option>
                                <option value="transfer">💳 Transfer Bank</option>
                                <option value="debit">🏧 Kartu Debit</option>
                            </select>
                        </div>
                    </div>

                    <!-- DAFTAR ITEM PESANAN -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <strong style="font-size: 13px; color: var(--header-dark-blue);">📦 Rincian Item Penjualan (Billing / F&B)</strong>
                            <button type="button" class="btn-modal btn-modal-secondary" style="padding: 3px 8px; font-size: 11px;" onclick="addSaleItemRow()">+ Tambah Baris Item</button>
                        </div>
                        <div id="saleItemsContainer">
                            <!-- Baris item dinamis -->
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-group">
                            <label class="form-label" for="addAmountPaid">Nominal Diterima Kasir (Rp)</label>
                            <input type="number" id="addAmountPaid" name="amount_paid" class="form-control" min="0" oninput="calculateChange()" placeholder="Kosongkan jika pas">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kembalian (Rp)</label>
                            <input type="text" id="addChangeDisplay" class="form-control" readonly value="Rp 0" style="background: #f1f5f9; font-weight: 700; color: #16a34a;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="addCashierNotes">Catatan Kasir (Opsional)</label>
                        <input type="text" id="addCashierNotes" name="cashier_notes" class="form-control" placeholder="Contoh: Meja 4, bayar tunai tanpa struk, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <div style="margin-right: auto; font-size: 14px; font-weight: 700; color: #0284c7;">
                        Total Tagihan: <span id="addTotalTagihanDisplay">Rp 0</span>
                    </div>
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addSaleModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitAdd">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT STATUS -->
    <div class="modal-overlay" id="editStatusModal">
        <div class="modal-card" style="max-width: 400px;">
            <div class="modal-header">
                <div class="modal-title">⚙️ Update Status Transaksi</div>
                <button class="modal-close-btn" onclick="closeModal('editStatusModal')">&times;</button>
            </div>
            <form id="formEditStatus" onsubmit="submitEditStatus(event)">
                <input type="hidden" id="editStatusSaleId">
                <div class="modal-body">
                    <p style="font-size: 13px; margin-bottom: 12px;">
                        Ubah status untuk transaksi <strong id="editStatusInvoice">INV-...</strong>:
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="selectNewStatus">Pilih Status Baru</label>
                        <select id="selectNewStatus" class="form-control">
                            <option value="paid">🟢 Lunas (Paid)</option>
                            <option value="pending">🟡 Menunggu Pembayaran (Pending)</option>
                            <option value="cancelled">🔴 Dibatalkan / Void (Cancelled)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editStatusModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal-overlay" id="deleteSaleModal">
        <div class="modal-card" style="max-width: 420px; border-top-color: #dc2626;">
            <div class="modal-header">
                <div class="modal-title" style="color: #dc2626;">🗑️ Hapus Transaksi Penjualan</div>
                <button class="modal-close-btn" onclick="closeModal('deleteSaleModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13px; line-height: 1.5; color: #333333;">
                    Apakah Anda yakin ingin menghapus transaksi <strong id="deleteSaleInvoiceText" style="color: #dc2626;"></strong>?
                </p>
                <div style="margin-top: 10px; padding: 8px 12px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; font-size: 12px; color: #856404;">
                    ⚠️ Menghapus transaksi akan menghapus rincian item pesanan dan ledger pembayaran terkait dari database!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('deleteSaleModal')">Batal</button>
                <button type="button" class="btn-modal btn-modal-danger" id="btnConfirmDelete" onclick="confirmDeleteSale()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>

    <script>
        // Data dari Server
        let allSales = @json($sales);
        let availableVariants = @json($variants);
        let currentStatusFilter = 'all';
        let currentMethodFilter = 'all';
        let currentCashierFilter = 'all';
        let currentSearchQuery = '';
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

        // FORMAT RUPIAH
        function formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // TOAST
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
            toastTimeout = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        // RENDER TABEL PENJUALAN
        function renderSalesTable() {
            const tbody = document.getElementById('salesTableBody');
            if (!tbody) return;

            const q = currentSearchQuery.toLowerCase();

            const filtered = allSales.filter(sale => {
                // Search query
                const matchesSearch = !q ||
                    (sale.invoice_number && sale.invoice_number.toLowerCase().includes(q)) ||
                    (sale.customer_name && sale.customer_name.toLowerCase().includes(q)) ||
                    (sale.employee && sale.employee.name && sale.employee.name.toLowerCase().includes(q)) ||
                    (sale.items && sale.items.some(it => it.item_name && it.item_name.toLowerCase().includes(q)));

                // Status
                let matchesStatus = true;
                if (currentStatusFilter !== 'all') {
                    matchesStatus = sale.payment_status === currentStatusFilter;
                }

                // Method
                let matchesMethod = true;
                if (currentMethodFilter !== 'all') {
                    matchesMethod = sale.payment_method === currentMethodFilter;
                }

                // Cashier
                let matchesCashier = true;
                if (currentCashierFilter !== 'all') {
                    matchesCashier = String(sale.employee_id) === String(currentCashierFilter);
                }

                return matchesSearch && matchesStatus && matchesMethod && matchesCashier;
            });

            tbody.innerHTML = '';

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px 20px; color: #888;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🛒</div>
                            <div style="font-weight: 600; font-size: 14px;">Tidak ada transaksi penjualan yang cocok</div>
                            <div style="font-size: 12px; color: #aaa; margin-top: 4px;">Coba ubah kata kunci pencarian atau sesuaikan filter di atas.</div>
                        </td>
                    </tr>
                `;
                return;
            }

            filtered.forEach((sale, index) => {
                const tr = document.createElement('tr');

                // Tanggal format
                const d = new Date(sale.created_at);
                const dateStr = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
                                d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                // Method badge
                let methodClass = 'badge-method-cash';
                let methodIcon = '💵';
                if (sale.payment_method === 'qris') { methodClass = 'badge-method-qris'; methodIcon = '📱'; }
                else if (sale.payment_method === 'transfer') { methodClass = 'badge-method-transfer'; methodIcon = '💳'; }
                else if (sale.payment_method === 'debit') { methodClass = 'badge-method-debit'; methodIcon = '🏧'; }

                // Status badge
                let statusClass = 'badge-status-paid';
                let statusText = 'Lunas';
                if (sale.payment_status === 'pending') { statusClass = 'badge-status-pending'; statusText = 'Pending'; }
                else if (sale.payment_status === 'cancelled') { statusClass = 'badge-status-cancelled'; statusText = 'Batal'; }

                // Items list chips
                let itemsHtml = '';
                if (sale.items && sale.items.length > 0) {
                    sale.items.slice(0, 3).forEach(it => {
                        const chipClass = it.item_type === 'billing' ? 'item-chip-mini item-chip-billing' : 'item-chip-mini';
                        itemsHtml += `<span class="${chipClass}" title="${escapeHtml(it.item_name)} (${it.qty}x)">${escapeHtml(it.item_name)} (${it.qty}x)</span>`;
                    });
                    if (sale.items.length > 3) {
                        itemsHtml += `<span class="item-chip-mini" style="background:#e2e8f0;">+${sale.items.length - 3} lagi</span>`;
                    }
                } else {
                    itemsHtml = '<span style="color:#aaa; font-size:11px;">-</span>';
                }

                // Station
                const stationText = sale.pc_station ? `<span style="font-size:11px; color:#0284c7; font-weight:600;">🖥️ ${escapeHtml(sale.pc_station.station_code)}</span>` : '<span style="font-size:11px; color:#64748b;">Walk-in</span>';

                tr.innerHTML = `
                    <td style="text-align: center; color: #64748b; font-weight: 500;">${index + 1}</td>
                    <td>
                        <div class="td-invoice">${escapeHtml(sale.invoice_number)}</div>
                        <div class="td-datetime">${dateStr}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #1e293b;">${escapeHtml(sale.customer_name)}</div>
                        <div>${stationText}</div>
                    </td>
                    <td>
                        <div style="font-size: 12px; font-weight: 500;">${sale.employee ? escapeHtml(sale.employee.name) : '-'}</div>
                    </td>
                    <td>
                        <div>${itemsHtml}</div>
                    </td>
                    <td style="text-align: right; font-family: monospace; font-size: 12px; color: #0369a1;">
                        ${formatRupiah(sale.total_billing)}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-size: 12px; color: #c2410c;">
                        ${formatRupiah(sale.total_fnb)}
                    </td>
                    <td style="text-align: right; font-family: monospace; font-size: 12.5px; font-weight: 700; color: #15803d;">
                        ${formatRupiah(sale.net_amount)}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-method ${methodClass}">${methodIcon} ${escapeHtml(sale.payment_method)}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-status ${statusClass}">${statusText}</span>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-action-sm" title="Lihat Struk Penjualan" onclick="openDetailModal(${sale.id})">
                            👁️
                        </button>
                        <button type="button" class="btn-action-sm" title="Ubah Status" onclick="openEditStatusModal(${sale.id})">
                            ⚙️
                        </button>
                        <button type="button" class="btn-action-sm btn-del" title="Hapus Transaksi" onclick="openDeleteSaleModal(${sale.id}, '${escapeHtml(sale.invoice_number)}')">
                            🗑️
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);
            });
        }

        // SEARCH & FILTER HANDLERS
        function handleSearch(val) {
            currentSearchQuery = val ? val.trim() : '';
            renderSalesTable();
        }

        function filterStatus(val) {
            currentStatusFilter = val;
            renderSalesTable();
        }

        function filterMethod(val) {
            currentMethodFilter = val;
            renderSalesTable();
        }

        function filterCashier(val) {
            currentCashierFilter = val;
            renderSalesTable();
        }

        // MODAL HELPERS
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // DETAIL MODAL & RECEIPT PREVIEW
        let currentDetailSale = null;
        function openDetailModal(id) {
            const sale = allSales.find(s => s.id === id);
            if (!sale) return;

            currentDetailSale = sale;
            document.getElementById('recInvoice').innerText = sale.invoice_number;
            document.getElementById('recDate').innerText = new Date(sale.created_at).toLocaleString('id-ID');
            document.getElementById('recCashier').innerText = sale.employee ? sale.employee.name : 'Kasir MGK';
            document.getElementById('recCustomer').innerText = sale.customer_name;
            document.getElementById('recStation').innerText = sale.pc_station ? sale.pc_station.station_code : 'Walk-in Kasir';

            const tbody = document.getElementById('recItemsBody');
            tbody.innerHTML = '';
            if (sale.items && sale.items.length > 0) {
                sale.items.forEach(it => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(it.item_name)}</td>
                        <td style="text-align: center;">${it.qty}</td>
                        <td style="text-align: right;">${formatRupiah(it.unit_price)}</td>
                        <td style="text-align: right; font-weight: 600;">${formatRupiah(it.subtotal)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('recTotalBilling').innerText = formatRupiah(sale.total_billing);
            document.getElementById('recTotalFnb').innerText = formatRupiah(sale.total_fnb);
            document.getElementById('recGrandTotal').innerText = formatRupiah(sale.net_amount);
            document.getElementById('recMethod').innerText = (sale.payment_method || 'CASH').toUpperCase();
            document.getElementById('recAmountPaid').innerText = formatRupiah(sale.amount_paid || sale.net_amount);
            document.getElementById('recChange').innerText = formatRupiah(sale.change_amount || 0);

            openModal('detailModal');
        }

        function printReceiptPopup() {
            if (!currentDetailSale) return;
            const printUrl = `{{ url('admin/penjualan') }}/${currentDetailSale.id}/print`;
            const w = window.open(printUrl, '_blank', 'width=450,height=650');
            if (w) w.focus();
        }

        // TAMBAH TRANSAKSI PENJUALAN BARU
        let itemRowIndex = 0;
        function openAddSaleModal() {
            document.getElementById('formAddSale').reset();
            document.getElementById('saleItemsContainer').innerHTML = '';
            itemRowIndex = 0;
            addSaleItemRow(); // Tambah 1 baris awal
            calculateTotalSale();
            openModal('addSaleModal');
        }

        function addSaleItemRow() {
            const container = document.getElementById('saleItemsContainer');
            const rowId = `item-row-${itemRowIndex}`;

            let variantOptions = '<option value="">— Ketik Manual atau Pilih Katalog Produk —</option>';
            availableVariants.forEach(v => {
                const prodName = v.product ? v.product.name : '';
                const fullName = `${prodName} - ${v.name}`;
                variantOptions += `<option value="${v.id}" data-name="${escapeHtml(fullName)}" data-price="${v.base_price}">${escapeHtml(fullName)} (${formatRupiah(v.base_price)})</option>`;
            });

            const row = document.createElement('div');
            row.id = rowId;
            row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 70px 100px 30px; gap: 6px; align-items: center; margin-bottom: 6px;';

            row.innerHTML = `
                <div>
                    <select class="form-control" style="font-size: 12px;" onchange="onSelectVariant(this, '${rowId}')">
                        ${variantOptions}
                    </select>
                    <input type="text" name="items[${itemRowIndex}][name]" class="form-control item-name-input" placeholder="Nama Item / Jasa" required style="font-size: 11px; margin-top: 3px;">
                    <input type="hidden" name="items[${itemRowIndex}][variant_id]" class="item-variant-id">
                </div>
                <div>
                    <select name="items[${itemRowIndex}][item_type]" class="form-control" style="font-size: 11.5px;">
                        <option value="fnb">🍔 F&B / Snack</option>
                        <option value="billing">🖥️ Billing PC</option>
                        <option value="voucher">🎟️ Voucher Game</option>
                    </select>
                </div>
                <div>
                    <input type="number" name="items[${itemRowIndex}][qty]" class="form-control item-qty-input" value="1" min="1" required style="font-size: 12px; text-align: center;" oninput="calculateTotalSale()">
                </div>
                <div>
                    <input type="number" name="items[${itemRowIndex}][price]" class="form-control item-price-input" value="0" min="0" required style="font-size: 12px; text-align: right;" oninput="calculateTotalSale()">
                </div>
                <div>
                    <button type="button" class="btn-action-sm btn-del" onclick="removeSaleItemRow('${rowId}')" title="Hapus Baris">✕</button>
                </div>
            `;

            container.appendChild(row);
            itemRowIndex++;
        }

        function removeSaleItemRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) row.remove();
            calculateTotalSale();
        }

        function onSelectVariant(selectEl, rowId) {
            const row = document.getElementById(rowId);
            if (!row) return;

            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            const nameInput = row.querySelector('.item-name-input');
            const priceInput = row.querySelector('.item-price-input');
            const variantInput = row.querySelector('.item-variant-id');

            if (selectedOpt.value) {
                nameInput.value = selectedOpt.getAttribute('data-name');
                priceInput.value = selectedOpt.getAttribute('data-price');
                variantInput.value = selectedOpt.value;
            }
            calculateTotalSale();
        }

        let currentComputedGrandTotal = 0;
        function calculateTotalSale() {
            let total = 0;
            const rows = document.querySelectorAll('#saleItemsContainer > div');
            rows.forEach(r => {
                const qty = Number(r.querySelector('.item-qty-input')?.value || 1);
                const price = Number(r.querySelector('.item-price-input')?.value || 0);
                total += (qty * price);
            });

            currentComputedGrandTotal = total;
            document.getElementById('addTotalTagihanDisplay').innerText = formatRupiah(total);
            calculateChange();
        }

        function calculateChange() {
            const paid = Number(document.getElementById('addAmountPaid').value || 0);
            const change = paid > currentComputedGrandTotal ? paid - currentComputedGrandTotal : 0;
            document.getElementById('addChangeDisplay').value = formatRupiah(change);
        }

        function submitAddSale(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitAdd');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            const form = document.getElementById('formAddSale');
            const formData = new FormData(form);

            // Bangun payload items array
            const items = [];
            const rows = document.querySelectorAll('#saleItemsContainer > div');
            rows.forEach(r => {
                const name = r.querySelector('.item-name-input').value.trim();
                const variantId = r.querySelector('.item-variant-id').value;
                const itemType = r.querySelector('select[name$="[item_type]"]').value;
                const qty = parseInt(r.querySelector('.item-qty-input').value) || 1;
                const price = parseFloat(r.querySelector('.item-price-input').value) || 0;

                if (name) {
                    items.push({
                        name: name,
                        variant_id: variantId ? parseInt(variantId) : null,
                        item_type: itemType,
                        qty: qty,
                        price: price
                    });
                }
            });

            if (items.length === 0) {
                btn.disabled = false;
                btn.innerText = 'Simpan Transaksi';
                showToast('Mohon masukkan minimal 1 item pesanan!', 'error');
                return;
            }

            const payload = {
                employee_id: parseInt(document.getElementById('addCashier').value),
                customer_name: document.getElementById('addCustomer').value.trim(),
                pc_station_id: document.getElementById('addPcStation').value ? parseInt(document.getElementById('addPcStation').value) : null,
                payment_method: document.getElementById('addPaymentMethod').value,
                amount_paid: document.getElementById('addAmountPaid').value ? parseFloat(document.getElementById('addAmountPaid').value) : null,
                cashier_notes: document.getElementById('addCashierNotes').value.trim() || null,
                items: items
            };

            fetch("{{ route('admin.penjualan.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan Transaksi';

                if (data.success && data.sale) {
                    allSales.unshift(data.sale);
                    closeModal('addSaleModal');
                    renderSalesTable();
                    updateBadgeCount();
                    showToast(data.message || 'Transaksi penjualan berhasil dicatat!', 'success');
                } else {
                    showToast(data.message || 'Gagal menyimpan transaksi', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerText = 'Simpan Transaksi';
                showToast('Terjadi kesalahan koneksi', 'error');
            });
        }

        // UPDATE STATUS
        function openEditStatusModal(id) {
            const sale = allSales.find(s => s.id === id);
            if (!sale) return;

            document.getElementById('editStatusSaleId').value = sale.id;
            document.getElementById('editStatusInvoice').innerText = sale.invoice_number;
            document.getElementById('selectNewStatus').value = sale.payment_status;
            openModal('editStatusModal');
        }

        function submitEditStatus(event) {
            event.preventDefault();
            const id = document.getElementById('editStatusSaleId').value;
            const newStatus = document.getElementById('selectNewStatus').value;

            fetch(`{{ url('admin/penjualan') }}/${id}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const sale = allSales.find(s => s.id == id);
                    if (sale) sale.payment_status = newStatus;
                    closeModal('editStatusModal');
                    renderSalesTable();
                    showToast(data.message || 'Status transaksi berhasil diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui status', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan jaringan', 'error');
            });
        }

        // HAPUS TRANSAKSI
        function openDeleteSaleModal(id, invoice) {
            pendingDeleteId = id;
            document.getElementById('deleteSaleInvoiceText').innerText = `"${invoice}"`;
            openModal('deleteSaleModal');
        }

        function confirmDeleteSale() {
            if (!pendingDeleteId) return;

            const btn = document.getElementById('btnConfirmDelete');
            btn.disabled = true;
            btn.innerText = 'Menghapus...';

            fetch(`{{ url('admin/penjualan') }}/${pendingDeleteId}`, {
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
                    allSales = allSales.filter(s => s.id != pendingDeleteId);
                    closeModal('deleteSaleModal');
                    pendingDeleteId = null;
                    renderSalesTable();
                    updateBadgeCount();
                    showToast(data.message || 'Transaksi berhasil dihapus.', 'success');
                } else {
                    showToast(data.message || 'Gagal menghapus transaksi', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerText = 'Ya, Hapus';
                showToast('Terjadi kesalahan koneksi', 'error');
            });
        }

        function updateBadgeCount() {
            const b = document.getElementById('menuBadgeCount');
            if (b) b.innerText = allSales.length;
        }

        // INIT
        document.addEventListener('DOMContentLoaded', function() {
            renderSalesTable();
        });
    </script>
</body>
</html>
