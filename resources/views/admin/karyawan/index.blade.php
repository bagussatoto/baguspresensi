<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Pengaturan Karyawan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #277799;
            --subnav-active: #195673;
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
            --btn-red: #d9534f;
            --btn-green: #28a745;
            --btn-orange: #f0ad4e;
            --border-red: #dd4b39;
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
            height: 38px;
            position: fixed;
            top: 48px;
            left: 230px;
            right: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            overflow-x: auto;
            white-space: nowrap;
            padding: 0 10px;
            gap: 4px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            scrollbar-width: thin;
            transition: left 0.3s;
        }

        .subnav-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            color: #e8f4f8;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            border-radius: 3px;
            transition: background 0.15s, color 0.15s;
        }

        .subnav-item:hover {
            background: rgba(0,0,0,0.15);
            color: #ffffff;
        }

        .subnav-item.active {
            background: var(--subnav-active);
            color: #ffffff;
            font-weight: 700;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.2);
        }

        /* LAYOUT & SIDEBAR */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: 86px;
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
        }

        /* SIDEBAR NAVIGATION */
        .sidebar-nav-header {
            padding: 12px 16px 6px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #4b646f;
            font-weight: 700;
            background: #1a2226;
        }

        .sidebar-menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 12.5px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .sidebar-menu li a:hover {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-active-text);
            border-left-color: #3c8dbc;
        }

        .sidebar-menu li.active a {
            background-color: #1e282c;
            color: #ffffff;
            border-left-color: #3c8dbc;
            font-weight: 600;
        }

        .menu-item-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-badge {
            background: #dd4b39;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* MAIN CONTENT AREA */
        .content-wrapper {
            margin-left: 230px;
            flex: 1;
            padding: 16px 20px 40px;
            transition: margin-left 0.3s;
        }

        /* TITLE BANNER */
        .page-title-banner {
            background: #ffffff;
            border: 1px solid var(--content-border);
            text-align: center;
            padding: 8px 16px;
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
            border-radius: 3px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            margin-bottom: 14px;
            letter-spacing: 0.2px;
        }

        /* FILTER TOOLBAR */
        .filter-toolbar-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .search-input-group {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--content-border);
            border-radius: 3px;
            overflow: hidden;
            width: 320px;
            max-width: 100%;
        }

        .search-input-group input {
            border: none;
            padding: 7px 12px;
            font-size: 12px;
            width: 100%;
            outline: none;
        }

        .search-input-group button {
            background: #f8fafc;
            border: none;
            border-left: 1px solid var(--content-border);
            padding: 7px 12px;
            cursor: pointer;
            color: #555;
            transition: background 0.2s;
        }

        .search-input-group button:hover {
            background: #e2e8f0;
        }

        .toolbar-right-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .select-filter-kantor {
            background: #ffffff;
            border: 1px solid var(--content-border);
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 3px;
            color: #333;
            outline: none;
            cursor: pointer;
        }

        .tool-icon-btn {
            background: #ffffff;
            border: 1px solid var(--content-border);
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 3px;
            cursor: pointer;
            color: #444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s, border-color 0.15s;
        }

        .tool-icon-btn:hover {
            background: #eef2f7;
            border-color: #b0bec5;
        }

        .tool-icon-btn.btn-add {
            background: #00c0ef;
            border-color: #00acd6;
            color: #ffffff;
            font-weight: 700;
            padding: 6px 14px;
            gap: 4px;
        }

        .tool-icon-btn.btn-add:hover {
            background: #00acd6;
        }

        /* DATA TABLE CONTAINER */
        .table-responsive-box {
            background: #ffffff;
            border: 1px solid var(--content-border);
            border-top: 2px solid var(--border-red);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            overflow-x: auto;
            border-radius: 2px;
        }

        .erp-karyawan-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            white-space: nowrap;
        }

        .erp-karyawan-table th {
            background: #f9fafb;
            color: #333333;
            font-weight: 700;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #d2d6de;
            border-right: 1px solid #eef2f5;
            font-size: 11.5px;
            letter-spacing: 0.1px;
        }

        .erp-karyawan-table th.text-center,
        .erp-karyawan-table td.text-center {
            text-align: center;
        }

        .erp-karyawan-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #eef2f5;
            border-right: 1px solid #f1f5f9;
            color: #333333;
            vertical-align: middle;
        }

        .erp-karyawan-table tr:nth-child(even) td {
            background-color: #fdfdfd;
        }

        .erp-karyawan-table tr:hover td {
            background-color: #f1f8fc !important;
        }

        /* TABLE INLINE SELECTS */
        .table-select {
            background: #ffffff;
            border: 1px solid #ccd0d4;
            padding: 3px 6px;
            font-size: 11px;
            border-radius: 3px;
            color: #333;
            outline: none;
            width: 82px;
            cursor: pointer;
        }

        .table-select:focus {
            border-color: #3c8dbc;
        }

        /* BUTTONS INSIDE TABLE CELLS */
        .btn-table-action {
            border: none;
            border-radius: 3px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 500;
            color: #ffffff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.15s, transform 0.1s;
        }

        .btn-table-action:hover {
            opacity: 0.9;
        }

        .btn-table-action:active {
            transform: scale(0.97);
        }

        .btn-akses {
            background-color: var(--btn-blue);
        }

        .btn-custom {
            background-color: var(--btn-cyan);
        }

        .btn-landing {
            background-color: var(--btn-sky);
        }

        .btn-refresh {
            background-color: var(--btn-red);
            padding: 4px 7px;
        }

        .btn-status-ok {
            background-color: var(--btn-green);
            padding: 3px 7px;
        }

        .btn-status-off {
            background-color: #777777;
            padding: 3px 7px;
        }

        .btn-icon-subtle {
            background: #ffffff;
            border: 1px solid #ccc;
            padding: 3px 6px;
            font-size: 11px;
            border-radius: 3px;
            cursor: pointer;
            color: #555;
        }

        .btn-act-edit {
            background-color: var(--btn-orange);
            color: #ffffff;
            border: none;
            padding: 4px 7px;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-act-delete {
            background-color: var(--btn-red);
            color: #ffffff;
            border: none;
            padding: 4px 7px;
            border-radius: 3px;
            cursor: pointer;
        }

        /* MODAL STYLES */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-window {
            background: #ffffff;
            border-radius: 4px;
            width: 580px;
            max-width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-top: 3px solid #3c8dbc;
        }

        .modal-header {
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #888;
        }

        .modal-body {
            padding: 18px;
        }

        .modal-footer {
            padding: 12px 18px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-row {
            margin-bottom: 12px;
        }

        .form-row label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #475569;
        }

        .form-control {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            font-size: 12px;
            outline: none;
        }

        .form-control:focus {
            border-color: #3c8dbc;
        }

        .btn-modal-cancel {
            background: #e2e8f0;
            border: none;
            padding: 7px 14px;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            color: #334155;
        }

        .btn-modal-save {
            background: #3c8dbc;
            border: none;
            padding: 7px 16px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            color: #ffffff;
        }

        /* TOAST NOTIFICATION */
        .toast-notify {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            font-size: 12px;
            display: none;
            z-index: 3000;
            border-left: 4px solid #10b981;
        }

        /* COLLAPSE STATE */
        body.sidebar-collapse .main-sidebar {
            transform: translateX(-230px);
        }
        body.sidebar-collapse .sub-navbar,
        body.sidebar-collapse .content-wrapper {
            margin-left: 0;
            left: 0;
        }

        /* =========================================================
           PROFILE KARYAWAN ENHANCED UI STYLES
           ========================================================= */
        .profile-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .profile-top-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .profile-select-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-select-label {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }

        .profile-select-input {
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            background: #f8fafc;
            min-width: 270px;
            outline: none;
            cursor: pointer;
        }

        .profile-select-input:focus {
            border-color: #0284c7;
            background: #ffffff;
        }

        .profile-hero-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .profile-hero-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-avatar-large {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2c7ea1, #1e3a8a);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(44,126,161,0.3);
            position: relative;
            flex-shrink: 0;
            border: 3px solid #ffffff;
        }

        .profile-avatar-badge {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid #ffffff;
        }

        .profile-avatar-badge.inactive {
            background: #ef4444;
        }

        .profile-hero-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .profile-badges-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .badge-pill {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-code { background: #e0f2fe; color: #0369a1; }
        .badge-pos { background: #dbeafe; color: #1e40af; }
        .badge-branch { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-inactive { background: #fee2e2; color: #b91c1c; }
        .badge-senior { background: #fef3c7; color: #b45309; }
        .badge-eval { background: #ede9fe; color: #6d28d9; }

        .profile-contacts-pill {
            display: flex;
            gap: 14px;
            font-size: 12px;
            color: #64748b;
            flex-wrap: wrap;
        }

        .profile-contacts-pill span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .profile-hero-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .hero-stat-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
            min-width: 120px;
            text-align: center;
        }

        .hero-stat-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .hero-stat-val {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
        }

        /* INTERNAL TABS */
        .profile-subtabs-nav {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid #cbd5e1;
            background: transparent;
            margin-top: 6px;
        }

        .profile-subtab-btn {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            border-top: 3px solid transparent;
            background: transparent;
            cursor: pointer;
            border-radius: 3px 3px 0 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
            border-left: none;
            border-right: none;
            border-bottom: none;
        }

        .profile-subtab-btn:hover {
            color: #0284c7;
            background: rgba(2,132,199,0.06);
        }

        .profile-subtab-btn.active {
            color: #0284c7;
            background: #ffffff;
            border-top-color: #0284c7;
            border-left: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            font-weight: 700;
            margin-bottom: -2px;
            border-bottom: 2px solid #ffffff;
        }

        .profile-tab-pane {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0 0 4px 4px;
            padding: 22px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* FORM GRID */
        .profile-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 14px;
        }

        .form-label-custom {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }

        .form-control-custom {
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 7px 10px;
            font-size: 12px;
            color: #0f172a;
            background: #ffffff;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control-custom:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2,132,199,0.15);
        }

        /* ID CARD COMPONENT */
        .id-card-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 20px 0;
        }

        .mgk-id-card {
            width: 380px;
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            border-radius: 12px;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.18);
            overflow: hidden;
            position: relative;
            font-family: 'Roboto', sans-serif;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .id-card-header {
            background: rgba(0,0,0,0.22);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .id-card-body {
            padding: 20px 18px;
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .id-card-avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: #ffffff;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 700;
            border: 3px solid rgba(255,255,255,0.7);
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }

        .id-card-info {
            flex: 1;
        }

        .id-card-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #ffffff;
        }

        .id-card-role {
            font-size: 12px;
            color: #e0f2fe;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .id-card-code {
            font-family: monospace;
            font-size: 11px;
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: 700;
        }

        .id-card-footer {
            background: rgba(0,0,0,0.25);
            padding: 10px 18px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        @media print {
            .main-header, .sub-navbar, .main-sidebar, .profile-top-toolbar, .profile-subtabs-nav, .btn-print-hide {
                display: none !important;
            }
            .content-wrapper { margin-left: 0 !important; padding: 0 !important; }
            .app-wrapper { padding-top: 0 !important; }
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
            body.sidebar-open .main-sidebar {
                transform: translateX(0);
            }
            .profile-hero-card {
                flex-direction: column;
                align-items: flex-start;
            }
            .profile-hero-stats {
                grid-template-columns: repeat(2, 1fr);
                width: 100%;
            }
            .profile-form-grid {
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
        <aside class="main-sidebar">
            <!-- USER PANEL -->
            <div class="sidebar-user-panel">
                <div class="user-avatar-circle">B</div>
                <div class="user-info-text">
                    <div class="user-info-name">Bagus</div>
                    <div class="user-info-role">manager @PUSAT</div>
                    <div class="user-status-badge">
                        <span class="status-dot-blink"></span>
                        <span id="liveTimeBadge">03:31:51 ON</span>
                    </div>
                </div>
            </div>

            <!-- MENU -->
            <div class="sidebar-nav-header">MAIN NAVIGATION</div>
            <ul class="sidebar-menu">
                <li class="active" id="sidebarMenuKaryawan">
                    <a href="javascript:void(0)" onclick="switchMainTab('karyawan')">
                        <span class="menu-item-left">🗄️ <span>data karyawan</span></span>
                    </a>
                </li>
                <li id="sidebarMenuProfile">
                    <a href="javascript:void(0)" onclick="switchMainTab('profile')">
                        <span class="menu-item-left">👤 <span>profil karyawan</span></span>
                    </a>
                </li>
                <li>
                    <a href="#pembelian">
                        <span class="menu-item-left">💳 <span>pembelian</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('warnet.index') }}#stocks">
                        <span class="menu-item-left">📦 <span>stok gudang</span></span>
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
                <!-- <li>
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
                <li> -->
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

        <!-- MAIN CONTENT -->
        <main class="content-wrapper">
            <div id="sectionKaryawan" class="main-tab-panel">
                <!-- PAGE TITLE RIBBON -->
                <div class="page-title-banner">
                    pengaturan karyawan
                </div>

                <!-- FILTER & SEARCH TOOLBAR -->
                <div class="filter-toolbar-box">
                    <!-- SEARCH INPUT -->
                    <div class="search-input-group">
                        <input type="text" id="searchInput" placeholder="Search..." oninput="handleSearch(this.value)">
                        <button type="button" title="Cari">🔍</button>
                    </div>

                    <!-- RIGHT CONTROLS -->
                    <div class="toolbar-right-actions">
                        <select class="select-filter-kantor" id="kantorFilter" onchange="filterByKantor(this.value)">
                            <option value="all">-- filter by kantor --</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>

                        <button class="tool-icon-btn" onclick="toggleListView()" title="List View">☰</button>
                        <button class="tool-icon-btn" onclick="filterStatus('active')" title="Tampilkan Karyawan Aktif">✔</button>
                        <button class="tool-icon-btn" onclick="filterStatus('inactive')" title="Tampilkan Karyawan Non-Aktif">⊘</button>
                        <button class="tool-icon-btn" onclick="filterStatus('all')" title="Reset Filter">👎</button>
                        <button class="tool-icon-btn" onclick="filterStatus('tetap')" title="Karyawan Tetap">®</button>
                        <button class="tool-icon-btn btn-add" onclick="openAddModal()">➕ Tambah Karyawan</button>
                    </div>
                </div>

                <!-- TABLE PENGATURAN KARYAWAN -->
                <div class="table-responsive-box">
                    <table class="erp-karyawan-table" id="karyawanTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 35px;">no</th>
                                <th style="min-width: 140px;">nama</th>
                                <th style="min-width: 120px;">nama login</th>
                                <th style="min-width: 190px;">contact</th>
                                <th style="width: 90px;">posisi</th>
                                <th style="width: 80px;">jabatan</th>
                                <th style="width: 75px;">konten</th>
                                <th style="width: 75px;">senior</th>
                                <th style="width: 75px;">evaluasi</th>
                                <th style="width: 85px;">karyawan</th>
                                <th class="text-center" style="width: 105px;">📁 akses</th>
                                <th class="text-center" style="width: 80px;">👤 akses</th>
                                <th class="text-center" style="width: 80px;">🏠 landing</th>
                                <th class="text-center" style="width: 40px;">🔄</th>
                                <th class="text-center" style="width: 90px;">status</th>
                                <th class="text-center" style="width: 80px;">act</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            @forelse($employees as $index => $emp)
                            <tr id="row-{{ $emp->id }}" data-branch="{{ $emp->branch_id }}" data-status="{{ $emp->is_active ? 'active' : 'inactive' }}" data-employment="{{ $emp->employment_status }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td style="font-weight: 600;">
                                    <a href="javascript:void(0)" onclick="openEmployeeProfile({{ $emp->id }})" style="color: #0f172a; text-decoration: none;" onmouseover="this.style.color='#0284c7'" onmouseout="this.style.color='#0f172a'" title="Klik untuk lihat profil">
                                        {{ $emp->name }}
                                    </a>
                                </td>
                                <td style="color: #475569;">{{ $emp->username ?? '-' }}</td>
                                <td style="color: #1e40af;">{{ $emp->email ?? '-' }}</td>

                                <!-- POSISI -->
                                <td>
                                    <select class="table-select" onchange="quickUpdate({{ $emp->id }}, 'position', this.value)">
                                        <option value="--pilih--" {{ $emp->position == '--pilih--' ? 'selected' : '' }}>--pilih--</option>
                                        <option value="Kasir" {{ $emp->position == 'Kasir' ? 'selected' : '' }}>Kasir</option>
                                        <option value="Operator" {{ $emp->position == 'Operator' ? 'selected' : '' }}>Operator</option>
                                        <option value="Teknisi" {{ $emp->position == 'Teknisi' ? 'selected' : '' }}>Teknisi</option>
                                        <option value="Manager" {{ $emp->position == 'Manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="Admin" {{ $emp->position == 'Admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="Staff" {{ $emp->position == 'Staff' ? 'selected' : '' }}>Staff</option>
                                    </select>
                                </td>

                                <!-- JABATAN -->
                                <td>
                                    <select class="table-select" style="width: 70px;" onchange="quickUpdate({{ $emp->id }}, 'jabatan', this.value)">
                                        <option value="sale" {{ $emp->jabatan == 'sale' ? 'selected' : '' }}>sale</option>
                                        <option value="mana" {{ $emp->jabatan == 'mana' ? 'selected' : '' }}>mana</option>
                                        <option value="hold" {{ $emp->jabatan == 'hold' ? 'selected' : '' }}>hold</option>
                                        <option value="staff" {{ $emp->jabatan == 'staff' ? 'selected' : '' }}>staff</option>
                                    </select>
                                </td>

                                <!-- KONTEN -->
                                <td>
                                    <select class="table-select" style="width: 65px;" onchange="quickUpdate({{ $emp->id }}, 'content_permission', this.value)">
                                        <option value="--pi--" {{ $emp->content_permission == '--pi--' ? 'selected' : '' }}>--pi--</option>
                                        <option value="all" {{ $emp->content_permission == 'all' ? 'selected' : '' }}>all</option>
                                        <option value="basic" {{ $emp->content_permission == 'basic' ? 'selected' : '' }}>basic</option>
                                    </select>
                                </td>

                                <!-- SENIOR -->
                                <td>
                                    <select class="table-select" style="width: 65px;" onchange="quickUpdate({{ $emp->id }}, 'seniority', this.value)">
                                        <option value="none" {{ $emp->seniority == 'none' ? 'selected' : '' }}>none</option>
                                        <option value="junior" {{ $emp->seniority == 'junior' ? 'selected' : '' }}>junior</option>
                                        <option value="senior" {{ $emp->seniority == 'senior' ? 'selected' : '' }}>senior</option>
                                    </select>
                                </td>

                                <!-- EVALUASI -->
                                <td>
                                    <select class="table-select" style="width: 65px;" onchange="quickUpdate({{ $emp->id }}, 'evaluation', this.value)">
                                        <option value="none" {{ $emp->evaluation == 'none' ? 'selected' : '' }}>none</option>
                                        <option value="Passed" {{ $emp->evaluation == 'Passed' ? 'selected' : '' }}>Passed</option>
                                        <option value="A" {{ $emp->evaluation == 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ $emp->evaluation == 'B' ? 'selected' : '' }}>B</option>
                                    </select>
                                </td>

                                <!-- KARYAWAN -->
                                <td>
                                    <select class="table-select" style="width: 75px;" onchange="quickUpdate({{ $emp->id }}, 'employment_status', this.value)">
                                        <option value="--pil--" {{ $emp->employment_status == '--pil--' ? 'selected' : '' }}>--pil--</option>
                                        <option value="tetap" {{ $emp->employment_status == 'tetap' ? 'selected' : '' }}>tetap</option>
                                        <option value="kontrak" {{ $emp->employment_status == 'kontrak' ? 'selected' : '' }}>kontrak</option>
                                        <option value="training" {{ $emp->employment_status == 'training' ? 'selected' : '' }}>training</option>
                                    </select>
                                </td>

                                <!-- 📁 AKSES -->
                                <td class="text-center">
                                    <button class="btn-table-action btn-akses" onclick="openAksesModal({{ $emp->id }}, '{{ addslashes($emp->name) }}')">
                                        📁 atur hak akses
                                    </button>
                                </td>

                                <!-- 👤 AKSES -->
                                <td class="text-center">
                                    <button class="btn-table-action btn-custom" onclick="openCustomModal({{ $emp->id }}, '{{ addslashes($emp->name) }}')">
                                        👤 custom
                                    </button>
                                </td>

                                <!-- 🏠 LANDING -->
                                <td class="text-center">
                                    <a href="{{ route('warnet.index') }}" target="_blank" class="btn-table-action btn-landing" style="text-decoration: none;">
                                        🏠 landing
                                    </a>
                                </td>

                                <!-- 🔄 REFRESH ROW -->
                                <td class="text-center">
                                    <button class="btn-table-action btn-refresh" onclick="refreshRow({{ $emp->id }})" title="Sinkronisasi Data">
                                        🔄
                                    </button>
                                </td>

                                <!-- STATUS (TOGGLE) -->
                                <td class="text-center">
                                    <button class="btn-table-action {{ $emp->is_active ? 'btn-status-ok' : 'btn-status-off' }}" id="status-btn-{{ $emp->id }}" onclick="toggleStatus({{ $emp->id }})" title="Ubah Status Karyawan">
                                        {{ $emp->is_active ? '✔' : '⊘' }}
                                    </button>
                                    <button class="btn-icon-subtle" onclick="thumbsUp({{ $emp->id }})" title="Status Disiplin">👍</button>
                                </td>

                                <!-- ACT (PROFILE, EDIT, & DELETE) -->
                                <td class="text-center" style="white-space: nowrap;">
                                    <button class="btn-table-action" style="background: #0284c7; color: #fff; padding: 2px 6px; font-size: 11px;" onclick="openEmployeeProfile({{ $emp->id }})" title="Buka Profil Lengkap">
                                        👤
                                    </button>
                                    <button class="btn-act-edit" onclick="openEditModal({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ addslashes($emp->username ?? '') }}', '{{ addslashes($emp->email ?? '') }}', '{{ addslashes($emp->phone ?? '') }}', {{ $emp->branch_id ?? 'null' }})" title="Edit Karyawan">
                                        ✏
                                    </button>
                                    <button class="btn-act-delete" onclick="confirmDelete({{ $emp->id }}, '{{ addslashes($emp->name) }}')" title="Hapus Karyawan">
                                        ✖
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" style="text-align: center; padding: 30px; color: #888;">
                                    Belum ada data karyawan terdaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div> <!-- CLOSE #sectionKaryawan -->

            <!-- =========================================================
                 SECTION PROFILE KARYAWAN (Halaman #profile)
                 ========================================================= -->
            <div id="sectionProfile" class="main-tab-panel" style="display: none;">
                <!-- PAGE TITLE RIBBON -->
                <div class="page-title-banner" style="background: linear-gradient(to right, #1e3a8a, #0284c7); display: flex; justify-content: space-between; align-items: center;">
                    <span>👤 Profil Karyawan — Maya Graha Kencana</span>
                    <button type="button" class="tool-icon-btn btn-print-hide" onclick="switchMainTab('karyawan')" style="background: rgba(255,255,255,0.2); font-size: 11px; padding: 4px 10px; border-radius: 3px; border: none; color: #fff; cursor: pointer;">
                        ⬅ Kembali ke Tabel Karyawan
                    </button>
                </div>

                <div class="profile-container">
                    <!-- TOOLBAR SELECTOR -->
                    <div class="profile-top-toolbar">
                        <div class="profile-select-group">
                            <label class="profile-select-label">Pilih Karyawan:</label>
                            <select id="profileEmployeeSelect" class="profile-select-input" onchange="loadEmployeeProfile(this.value)">
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}">
                                        [{{ $e->code }}] {{ $e->name }} — {{ $e->position ?? 'Staff' }} ({{ $e->branch?->name ?? 'PUSAT' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display: flex; gap: 8px; align-items: center;" class="btn-print-hide">
                            <button type="button" class="tool-icon-btn btn-add" onclick="openAddModal()">
                                ➕ Tambah Karyawan Baru
                            </button>
                            <button type="button" class="tool-icon-btn" style="background: #0284c7; color: #fff;" onclick="window.print()">
                                🖨️ Cetak ID Card / Profil
                            </button>
                        </div>
                    </div>

                    <!-- HERO PROFILE CARD -->
                    <div class="profile-hero-card">
                        <div class="profile-hero-left">
                            <div class="profile-avatar-large" id="profileAvatarLetter">
                                B
                                <span class="profile-avatar-badge" id="profileAvatarBadge"></span>
                            </div>
                            <div>
                                <div class="profile-hero-title">
                                    <span id="profileHeroName">Bagus</span>
                                </div>
                                <div class="profile-badges-row">
                                    <span class="badge-pill badge-code" id="profileHeroCode">MGR-001</span>
                                    <span class="badge-pill badge-pos" id="profileHeroPosition">Manager</span>
                                    <span class="badge-pill badge-branch" id="profileHeroBranch">Kantor PUSAT</span>
                                    <span class="badge-pill badge-active" id="profileHeroStatusBadge">Aktif</span>
                                    <span class="badge-pill badge-senior" id="profileHeroSeniority">Senior</span>
                                    <span class="badge-pill badge-eval" id="profileHeroEvaluation">Passed</span>
                                </div>
                                <div class="profile-contacts-pill">
                                    <span>👤 Login: <strong id="profileHeroUsername">bagus</strong></span>
                                    <span>✉️ Email: <strong id="profileHeroEmail">-</strong></span>
                                    <span>📞 HP: <strong id="profileHeroPhone">-</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-hero-stats">
                            <div class="hero-stat-box">
                                <div class="hero-stat-label">Kehadiran Bln Ini</div>
                                <div class="hero-stat-val" id="statHadir" style="color: #166534;">0x</div>
                            </div>
                            <div class="hero-stat-box">
                                <div class="hero-stat-label">Terlambat</div>
                                <div class="hero-stat-val" id="statTelat" style="color: #b45309;">0x</div>
                            </div>
                            <div class="hero-stat-box">
                                <div class="hero-stat-label">Alpa / Absen</div>
                                <div class="hero-stat-val" id="statAlpa" style="color: #b91c1c;">0x</div>
                            </div>
                            <div class="hero-stat-box">
                                <div class="hero-stat-label">Jam Kerja Riil</div>
                                <div class="hero-stat-val" id="statJamKerja" style="color: #0284c7;">0 Jam</div>
                            </div>
                        </div>
                    </div>

                    <!-- SUB-TABS NAVIGATION -->
                    <div class="profile-subtabs-nav btn-print-hide">
                        <button type="button" class="profile-subtab-btn active" id="tabBtnBio" onclick="switchProfileSubtab('bio')">
                            📋 1. Biodata & Kepegawaian
                        </button>
                        <button type="button" class="profile-subtab-btn" id="tabBtnPresensi" onclick="switchProfileSubtab('presensi')">
                            📊 2. Riwayat Kehadiran & Shift
                        </button>
                        <button type="button" class="profile-subtab-btn" id="tabBtnAkses" onclick="switchProfileSubtab('akses')">
                            🔐 3. Hak Akses Modul ERP
                        </button>
                        <button type="button" class="profile-subtab-btn" id="tabBtnIdCard" onclick="switchProfileSubtab('idcard')">
                            💳 4. Kartu Pengenal (ID Card)
                        </button>
                    </div>

                    <!-- TAB PANE 1: BIODATA & KEPEGAWAIAN -->
                    <div class="profile-tab-pane" id="paneBio">
                        <form id="profileBioForm" onsubmit="saveProfileBio(event)">
                            <div class="profile-form-grid">
                                <!-- KOLOM KIRI -->
                                <div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Nama Lengkap Karyawan</label>
                                        <input type="text" id="bioName" class="form-control-custom" required>
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Nama Login / Username</label>
                                        <input type="text" id="bioUsername" class="form-control-custom">
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Kode Karyawan / NIK (Sistem)</label>
                                        <input type="text" id="bioCode" class="form-control-custom" readonly style="background: #f1f5f9; cursor: not-allowed;">
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Kantor Cabang Penempatan</label>
                                        <select id="bioBranchId" class="form-control-custom">
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Posisi Kerja</label>
                                        <select id="bioPosition" class="form-control-custom">
                                            <option value="--pilih--">--pilih--</option>
                                            <option value="Kasir">Kasir</option>
                                            <option value="Operator">Operator</option>
                                            <option value="Teknisi">Teknisi</option>
                                            <option value="Manager">Manager</option>
                                            <option value="Admin">Admin</option>
                                            <option value="Staff">Staff</option>
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Jabatan Operasional</label>
                                        <select id="bioJabatan" class="form-control-custom">
                                            <option value="sale">sale</option>
                                            <option value="mana">mana</option>
                                            <option value="hold">hold</option>
                                            <option value="staff">staff</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- KOLOM KANAN -->
                                <div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Email Resmi</label>
                                        <input type="email" id="bioEmail" class="form-control-custom">
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">No. Handphone / WhatsApp</label>
                                        <input type="text" id="bioPhone" class="form-control-custom">
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Status Ketenagakerjaan</label>
                                        <select id="bioEmploymentStatus" class="form-control-custom">
                                            <option value="--pil--">--pil--</option>
                                            <option value="tetap">tetap</option>
                                            <option value="kontrak">kontrak</option>
                                            <option value="training">training</option>
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Senioritas</label>
                                        <select id="bioSeniority" class="form-control-custom">
                                            <option value="none">none</option>
                                            <option value="junior">junior</option>
                                            <option value="senior">senior</option>
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Evaluasi Kinerja</label>
                                        <select id="bioEvaluation" class="form-control-custom">
                                            <option value="none">none</option>
                                            <option value="Passed">Passed</option>
                                            <option value="A">Grade A</option>
                                            <option value="B">Grade B</option>
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Maks. Diskon Approval (%)</label>
                                        <input type="number" id="bioMaxDiscount" class="form-control-custom" min="0" max="100" step="0.5">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 18px; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                                <button type="button" class="tool-icon-btn" onclick="refreshCurrentProfile()" style="background: #64748b; color: #fff;">
                                    🔄 Muat Ulang
                                </button>
                                <button type="submit" class="tool-icon-btn" style="background: #0284c7; color: #fff; padding: 7px 18px; font-weight: 700;">
                                    💾 Simpan Perubahan Biodata
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB PANE 2: RIWAYAT KEHADIRAN & SHIFT -->
                    <div class="profile-tab-pane" id="panePresensi" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: #0f172a;">Riwayat Presensi & Jam Kerja Terkini</h4>
                                <p style="font-size: 11px; color: #64748b;">Menampilkan catatan absensi operasional terakhir untuk karyawan ini.</p>
                            </div>
                            <a href="{{ route('admin.presensi.rekap') }}" class="tool-icon-btn" style="background: #28a745; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                📊 Rekapitulasi Presensi ERP
                            </a>
                        </div>

                        <div class="table-responsive-box" style="margin-bottom: 0;">
                            <table class="erp-karyawan-table">
                                <thead>
                                    <tr>
                                        <th style="width: 35px;" class="text-center">No</th>
                                        <th>Tanggal Kerja</th>
                                        <th>Shift Kerja</th>
                                        <th class="text-center">Jam Masuk</th>
                                        <th class="text-center">Jam Pulang</th>
                                        <th class="text-center">Durasi Riil</th>
                                        <th class="text-center">Status</th>
                                        <th>Verifikasi</th>
                                    </tr>
                                </thead>
                                <tbody id="profileAttendanceTableBody">
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 25px; color: #888;">
                                            Memuat data riwayat kehadiran...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB PANE 3: HAK AKSES MODUL ERP -->
                    <div class="profile-tab-pane" id="paneAkses" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: #0f172a;">Wewenang & Modul Akses ERP</h4>
                                <p style="font-size: 11px; color: #64748b;">Atur hak akses karyawan untuk setiap modul di sistem ERP Maya Graha Kencana.</p>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" id="profileToggleStatusBtn" class="tool-icon-btn" onclick="toggleCurrentProfileStatus()" style="background: #28a745; color: #fff;">
                                    ✔ Status: Aktif
                                </button>
                            </div>
                        </div>

                        <form id="profileAccessForm" onsubmit="saveProfileAccess(event)">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; font-size: 12px; margin-bottom: 20px;">
                                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" id="prof_perm_master_data" style="width: 16px; height: 16px;">
                                    <div>
                                        <strong>📁 Master Data, Merk, & Supplier</strong>
                                        <div style="color: #64748b; font-size: 11px;">Melihat dan mengelola data master supplier, merk, dan kategori.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" id="prof_perm_warnet_pos" style="width: 16px; height: 16px;">
                                    <div>
                                        <strong>🖥️ Terminal POS Kasir & Billing Warnet</strong>
                                        <div style="color: #64748b; font-size: 11px;">Melayani billing user, sewa PC, dan penjualan F&B kasir.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" id="prof_perm_inventory" style="width: 16px; height: 16px;">
                                    <div>
                                        <strong>📦 Manajemen Stok Gudang & Pembelian</strong>
                                        <div style="color: #64748b; font-size: 11px;">Mencatat restock barang gudang, transfer cabang, dan pembelian.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" id="prof_perm_attendance" style="width: 16px; height: 16px;">
                                    <div>
                                        <strong>📅 Monitoring Presensi & Persetujuan Cuti</strong>
                                        <div style="color: #64748b; font-size: 11px;">Mengakses panel presensi live, rekap bulanan, dan approval lembur.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" id="prof_perm_schedule" style="width: 16px; height: 16px;">
                                    <div>
                                        <strong>🕒 Pengaturan Jadwal Shift & Roster</strong>
                                        <div style="color: #64748b; font-size: 11px;">Mengunggah gambar jadwal shift dan mengatur jam kerja shift 1-4.</div>
                                    </div>
                                </label>

                                <label style="display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px; border-radius: 4px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" id="prof_perm_discount" style="width: 16px; height: 16px;">
                                    <div>
                                        <strong>💰 Otorisasi Diskon Transaksi</strong>
                                        <div style="color: #64748b; font-size: 11px;">Memberikan diskon harga sewa PC / produk hingga limit maksimal.</div>
                                    </div>
                                </label>
                            </div>

                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="tool-icon-btn" style="background: #0284c7; color: #fff; padding: 7px 18px; font-weight: 700;">
                                    💾 Simpan Hak Akses Modul
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB PANE 4: KARTU PENGENAL (ID CARD) -->
                    <div class="profile-tab-pane" id="paneIdCard" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;" class="btn-print-hide">
                            <div>
                                <h4 style="font-size: 14px; font-weight: 700; color: #0f172a;">Pratinjau Kartu Pengenal (ID Card) Karyawan</h4>
                                <p style="font-size: 11px; color: #64748b;">Siap dicetak sebagai ID Card resmi karyawan Maya Graha Kencana.</p>
                            </div>
                            <button type="button" class="tool-icon-btn" style="background: #28a745; color: #fff; padding: 6px 14px;" onclick="window.print()">
                                🖨️ Cetak ID Card Sekarang
                            </button>
                        </div>

                        <div class="id-card-wrapper">
                            <div class="mgk-id-card" id="printableIdCard">
                                <div class="id-card-header">
                                    <div style="font-weight: 700; font-size: 13px; letter-spacing: 0.5px;">🏢 MAYA GRAHA KENCANA</div>
                                    <div style="font-size: 9px; opacity: 0.85; text-transform: uppercase;">Employee Card</div>
                                </div>
                                <div class="id-card-body">
                                    <div class="id-card-avatar" id="idCardAvatarLetter">B</div>
                                    <div class="id-card-info">
                                        <div class="id-card-name" id="idCardName">Bagus</div>
                                        <div class="id-card-role" id="idCardPosition">Manager Operasional</div>
                                        <div class="id-card-code" id="idCardCode">MGR-001</div>
                                        <div style="font-size: 10px; margin-top: 6px; opacity: 0.9;" id="idCardBranch">
                                            Maya Graha Pusat
                                        </div>
                                    </div>
                                </div>
                                <div class="id-card-footer">
                                    <div>ID: <strong id="idCardBottomCode">MGR-001</strong></div>
                                    <div>STATUS: <strong>TETAP / AKTIF</strong></div>
                                    <div>MGK ERP SYSTEM</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- CLOSE #sectionProfile -->
        </main>
    </div>

    <!-- MODAL 1: ATUR HAK AKSES -->
    <div class="modal-overlay" id="aksesModal">
        <div class="modal-window">
            <div class="modal-header">
                <h3 id="aksesModalTitle">📁 Atur Hak Akses Karyawan</h3>
                <button class="modal-close-btn" onclick="closeModal('aksesModal')">✕</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="aksesEmpId">
                <p style="font-size: 12px; color: #64748b; margin-bottom: 14px;">
                    Centang modul & wewenang yang diizinkan untuk karyawan ini di ERP Maya Graha Kencana:
                </p>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; font-size: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_master_data" checked>
                        <span>📁 Master Data & Kategori</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_pos_cashier" checked>
                        <span>🛒 POS Kasir & Penjualan</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_billing_pc" checked>
                        <span>🖥️ Billing PC & Warnet Session</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_stock_kitchen" checked>
                        <span>📦 Distribusi & Mutasi Dapur</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_discount_approval">
                        <span>🏷️ Otorisasi Diskon Transaksi</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_attendance" checked>
                        <span>📅 Presensi & Rekap Jam Kerja</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_finance_report">
                        <span>📊 Laporan Keuangan & Omset</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="perm_employee_setting">
                        <span>⚙️ Manajemen Pengaturan Karyawan</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('aksesModal')">Batal</button>
                <button type="button" class="btn-modal-save" onclick="saveAksesRights()">Simpan Hak Akses</button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: CUSTOM AKSES -->
    <div class="modal-overlay" id="customModal">
        <div class="modal-window" style="width: 480px;">
            <div class="modal-header">
                <h3 id="customModalTitle">👤 Custom Akses Karyawan</h3>
                <button class="modal-close-btn" onclick="closeModal('customModal')">✕</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="customEmpId">
                <div class="form-row">
                    <label>Landing URL Khusus:</label>
                    <input type="text" class="form-control" id="customLandingUrl" value="/warnet" placeholder="Contoh: /warnet atau /admin">
                </div>
                <div class="form-row">
                    <label>Limit Diskon Maksimal (%):</label>
                    <input type="number" class="form-control" id="customDiscountLimit" value="10" min="0" max="100">
                </div>
                <div class="form-row">
                    <label>Catatan Otorisator / Khusus:</label>
                    <textarea class="form-control" id="customNotes" rows="3" placeholder="Hak akses khusus kasir shift malam..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('customModal')">Batal</button>
                <button type="button" class="btn-modal-save" onclick="saveCustomAkses()">Simpan Custom</button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: TAMBAH KARYAWAN -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-window">
            <div class="modal-header">
                <h3>➕ Tambah Karyawan Baru</h3>
                <button class="modal-close-btn" onclick="closeModal('addModal')">✕</button>
            </div>
            <form id="addEmployeeForm" onsubmit="submitAddEmployee(event)">
                <div class="modal-body">
                    <div class="form-row">
                        <label>Nama Lengkap Karyawan: *</label>
                        <input type="text" class="form-control" name="name" required placeholder="Contoh: Budi Santoso">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-row">
                            <label>Nama Login / Username: *</label>
                            <input type="text" class="form-control" name="username" required placeholder="budi">
                        </div>
                        <div class="form-row">
                            <label>Email Kontak: *</label>
                            <input type="email" class="form-control" name="email" required placeholder="budi@mayagrahakencana.com">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-row">
                            <label>Nomor Telepon / WhatsApp:</label>
                            <input type="text" class="form-control" name="phone" placeholder="0812-3456-7890">
                        </div>
                        <div class="form-row">
                            <label>Kantor / Cabang:</label>
                            <select class="form-control" name="branch_id">
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div class="form-row">
                            <label>Posisi:</label>
                            <select class="form-control" name="position">
                                <option value="Kasir">Kasir</option>
                                <option value="Operator">Operator</option>
                                <option value="Teknisi">Teknisi</option>
                                <option value="Staff">Staff</option>
                                <option value="Manager">Manager</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label>Jabatan:</label>
                            <select class="form-control" name="jabatan">
                                <option value="sale">sale</option>
                                <option value="mana">mana</option>
                                <option value="hold">hold</option>
                                <option value="staff">staff</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label>Status Karyawan:</label>
                            <select class="form-control" name="employment_status">
                                <option value="tetap">tetap</option>
                                <option value="kontrak">kontrak</option>
                                <option value="training">training</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('addModal')">Batal</button>
                    <button type="submit" class="btn-modal-save">Daftarkan Karyawan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: EDIT KARYAWAN -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-window">
            <div class="modal-header">
                <h3>✏ Edit Data Karyawan</h3>
                <button class="modal-close-btn" onclick="closeModal('editModal')">✕</button>
            </div>
            <form id="editEmployeeForm" onsubmit="submitEditEmployee(event)">
                <input type="hidden" id="editEmpId">
                <div class="modal-body">
                    <div class="form-row">
                        <label>Nama Lengkap:</label>
                        <input type="text" class="form-control" id="editEmpName" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-row">
                            <label>Nama Login / Username:</label>
                            <input type="text" class="form-control" id="editEmpUsername">
                        </div>
                        <div class="form-row">
                            <label>Email Kontak:</label>
                            <input type="email" class="form-control" id="editEmpEmail">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-row">
                            <label>Nomor HP:</label>
                            <input type="text" class="form-control" id="editEmpPhone">
                        </div>
                        <div class="form-row">
                            <label>Kantor / Cabang:</label>
                            <select class="form-control" id="editEmpBranch">
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('editModal')">Batal</button>
                    <button type="submit" class="btn-modal-save">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="toast-notify" id="toastBox">Notifikasi</div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // LIVE TIME CLOCK (Matching screenshot 03:31:51 ON)
        function updateLiveClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('liveTimeBadge');
            if (el) el.innerText = `${hours}:${minutes}:${seconds} ON`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // TOGGLE SIDEBAR
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapse');
        }

        // TOAST ALERT
        function showToast(message) {
            const toast = document.getElementById('toastBox');
            toast.innerText = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // QUICK AJAX UPDATE FOR TABLE DROPDOWNS
        async function quickUpdate(id, field, value) {
            try {
                const response = await fetch(`{{ url('/admin/karyawan') }}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ [field]: value })
                });
                const data = await response.json();
                if (data.success) {
                    showToast(`✓ ${data.message}`);
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal memperbarui data.');
            }
        }

        // TOGGLE STATUS
        async function toggleStatus(id) {
            try {
                const response = await fetch(`{{ url('/admin/karyawan') }}/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    const btn = document.getElementById(`status-btn-${id}`);
                    const row = document.getElementById(`row-${id}`);
                    if (data.is_active) {
                        btn.className = 'btn-table-action btn-status-ok';
                        btn.innerText = '✔';
                        row.setAttribute('data-status', 'active');
                    } else {
                        btn.className = 'btn-table-action btn-status-off';
                        btn.innerText = '⊘';
                        row.setAttribute('data-status', 'inactive');
                    }
                    showToast(data.message);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function thumbsUp(id) {
            showToast('Evaluasi kedisiplinan diverifikasi [Baik]');
        }

        function refreshRow(id) {
            showToast(`Data baris #${id} disinkronkan dengan server.`);
        }

        // MODAL TOGGLERS
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function openAksesModal(id, name) {
            document.getElementById('aksesEmpId').value = id;
            document.getElementById('aksesModalTitle').innerText = `📁 Atur Hak Akses: ${name}`;
            document.getElementById('aksesModal').classList.add('active');
        }

        async function saveAksesRights() {
            const id = document.getElementById('aksesEmpId').value;
            const rights = {
                master_data: document.getElementById('perm_master_data').checked,
                pos_cashier: document.getElementById('perm_pos_cashier').checked,
                billing_pc: document.getElementById('perm_billing_pc').checked,
                stock_kitchen: document.getElementById('perm_stock_kitchen').checked,
                discount_approval: document.getElementById('perm_discount_approval').checked,
                attendance: document.getElementById('perm_attendance').checked,
                finance_report: document.getElementById('perm_finance_report').checked,
                employee_setting: document.getElementById('perm_employee_setting').checked,
            };

            try {
                const res = await fetch(`{{ url('/admin/karyawan') }}/${id}/access-rights`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ access_rights: rights })
                });
                const data = await res.json();
                closeModal('aksesModal');
                showToast(`✓ Hak akses berhasil disimpan.`);
            } catch (err) {
                console.error(err);
            }
        }

        function openCustomModal(id, name) {
            document.getElementById('customEmpId').value = id;
            document.getElementById('customModalTitle').innerText = `👤 Custom Akses: ${name}`;
            document.getElementById('customModal').classList.add('active');
        }

        function saveCustomAkses() {
            closeModal('customModal');
            showToast('✓ Konfigurasi custom akses tersimpan.');
        }

        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }

        async function submitAddEmployee(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                const res = await fetch(`{{ route('admin.karyawan.store') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    closeModal('addModal');
                    showToast('✓ Karyawan baru berhasil didaftarkan. Memuat ulang...');
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                console.error(err);
            }
        }

        function openEditModal(id, name, username, email, phone, branchId) {
            document.getElementById('editEmpId').value = id;
            document.getElementById('editEmpName').value = name;
            document.getElementById('editEmpUsername').value = username;
            document.getElementById('editEmpEmail').value = email;
            document.getElementById('editEmpPhone').value = phone;
            if (branchId) document.getElementById('editEmpBranch').value = branchId;
            document.getElementById('editModal').classList.add('active');
        }

        async function submitEditEmployee(e) {
            e.preventDefault();
            const id = document.getElementById('editEmpId').value;
            const payload = {
                name: document.getElementById('editEmpName').value,
                username: document.getElementById('editEmpUsername').value,
                email: document.getElementById('editEmpEmail').value,
                phone: document.getElementById('editEmpPhone').value,
                branch_id: document.getElementById('editEmpBranch').value,
            };

            try {
                const res = await fetch(`{{ url('/admin/karyawan') }}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    closeModal('editModal');
                    showToast(`✓ Data karyawan diperbarui. Memuat ulang...`);
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                console.error(err);
            }
        }

        async function confirmDelete(id, name) {
            if (!confirm(`Apakah Anda yakin ingin menghapus karyawan "${name}" dari sistem?`)) {
                return;
            }

            try {
                const res = await fetch(`{{ url('/admin/karyawan') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById(`row-${id}`);
                    if (row) row.remove();
                    showToast(data.message);
                }
            } catch (err) {
                console.error(err);
            }
        }

        // REALTIME FILTERING
        function handleSearch(keyword) {
            const term = keyword.toLowerCase().trim();
            const rows = document.querySelectorAll('#employeeTableBody tr');
            rows.forEach(r => {
                const text = r.innerText.toLowerCase();
                r.style.display = text.includes(term) ? '' : 'none';
            });
        }

        function filterByKantor(branchId) {
            const rows = document.querySelectorAll('#employeeTableBody tr');
            rows.forEach(r => {
                if (branchId === 'all' || r.getAttribute('data-branch') === branchId) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function filterStatus(status) {
            const rows = document.querySelectorAll('#employeeTableBody tr');
            rows.forEach(r => {
                if (status === 'all') {
                    r.style.display = '';
                } else if (status === 'tetap') {
                    r.style.display = r.getAttribute('data-employment') === 'tetap' ? '' : 'none';
                } else {
                    r.style.display = r.getAttribute('data-status') === status ? '' : 'none';
                }
            });
            showToast(`Filter diterapkan: ${status}`);
        }

        function toggleListView() {
            showToast('Tampilan daftar diaktifkan');
        }

        // =========================================================
        // PROFILE KARYAWAN INTERACTIVE LOGIC & HASH ROUTING
        // =========================================================
        const allEmployeesData = @json($employees);
        let currentProfileEmpId = {{ $employees->first()?->id ?? 1 }};

        function switchMainTab(tabName, event) {
            if (event) {
                event.preventDefault();
            }

            const secKaryawan = document.getElementById('sectionKaryawan');
            const secProfile = document.getElementById('sectionProfile');
            const subnavKaryawan = document.getElementById('subnavKaryawan');
            const subnavProfile = document.getElementById('subnavProfile');
            const sideKaryawan = document.getElementById('sidebarMenuKaryawan');
            const sideProfile = document.getElementById('sidebarMenuProfile');

            if (tabName === 'profile') {
                secKaryawan.style.display = 'none';
                secProfile.style.display = '';

                if (subnavKaryawan) subnavKaryawan.classList.remove('active');
                if (subnavProfile) subnavProfile.classList.add('active');
                if (sideKaryawan) sideKaryawan.classList.remove('active');
                if (sideProfile) sideProfile.classList.add('active');

                history.replaceState(null, null, '#profile-' + currentProfileEmpId);
                loadEmployeeProfile(currentProfileEmpId);
            } else {
                secProfile.style.display = 'none';
                secKaryawan.style.display = '';

                if (subnavProfile) subnavProfile.classList.remove('active');
                if (subnavKaryawan) subnavKaryawan.classList.add('active');
                if (sideProfile) sideProfile.classList.remove('active');
                if (sideKaryawan) sideKaryawan.classList.add('active');

                history.replaceState(null, null, '#karyawan');
            }
        }

        function openEmployeeProfile(empId, event) {
            if (event) {
                event.preventDefault();
            }
            currentProfileEmpId = parseInt(empId);
            switchMainTab('profile');
        }

        function switchProfileSubtab(tabKey) {
            const panes = ['bio', 'presensi', 'akses', 'idcard'];
            panes.forEach(p => {
                const paneEl = document.getElementById('pane' + p.charAt(0).toUpperCase() + p.slice(1));
                const btnEl = document.getElementById('tabBtn' + p.charAt(0).toUpperCase() + p.slice(1));
                if (p === tabKey) {
                    if (paneEl) paneEl.style.display = '';
                    if (btnEl) btnEl.classList.add('active');
                } else {
                    if (paneEl) paneEl.style.display = 'none';
                    if (btnEl) btnEl.classList.remove('active');
                }
            });
        }

        function loadEmployeeProfile(empId) {
            currentProfileEmpId = parseInt(empId);
            const selectEl = document.getElementById('profileEmployeeSelect');
            if (selectEl) {
                selectEl.value = currentProfileEmpId;
            }

            // Cari di array lokal untuk render cepat
            const emp = allEmployeesData.find(e => e.id == currentProfileEmpId);
            if (emp) {
                renderProfileStaticData(emp);
            }

            // Ambil data detail & presensi riil dari server
            fetch(`/admin/karyawan/${currentProfileEmpId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.employee) {
                    renderProfileStaticData(res.employee);

                    // Render Stats Bulan Ini
                    if (res.stats) {
                        document.getElementById('statHadir').innerText = `${res.stats.total_hadir}x`;
                        document.getElementById('statTelat').innerText = `${res.stats.total_telat}x (${res.stats.menit_telat}m)`;
                        document.getElementById('statAlpa').innerText = `${res.stats.total_alpa}x`;
                        document.getElementById('statJamKerja').innerText = `${res.stats.total_jam_kerja} Jam`;
                    }

                    // Render Riwayat Presensi Terakhir
                    renderRecentAttendanceTable(res.recent_attendance || []);

                    // Sinkronisasi Hak Akses
                    const rights = res.employee.access_rights || [];
                    document.getElementById('prof_perm_master_data').checked = rights.includes('master_data');
                    document.getElementById('prof_perm_warnet_pos').checked = rights.includes('pos_cashier');
                    document.getElementById('prof_perm_inventory').checked = rights.includes('stock_kitchen');
                    document.getElementById('prof_perm_attendance').checked = rights.includes('attendance');
                    document.getElementById('prof_perm_schedule').checked = rights.includes('schedule');
                    document.getElementById('prof_perm_discount').checked = rights.includes('discount_approval');
                }
            })
            .catch(err => {
                console.warn('Gagal memuat detail AJAX, menggunakan fallback lokal:', err);
            });
        }

        function renderProfileStaticData(emp) {
            const letter = emp.name ? emp.name.charAt(0).toUpperCase() : 'U';
            document.getElementById('profileAvatarLetter').innerText = letter;
            document.getElementById('profileHeroName').innerText = emp.name;
            document.getElementById('profileHeroCode').innerText = emp.code || 'EMP-' + emp.id;
            document.getElementById('profileHeroPosition').innerText = emp.position || 'Staff';
            document.getElementById('profileHeroBranch').innerText = emp.branch ? emp.branch.name : 'Kantor PUSAT';
            document.getElementById('profileHeroUsername').innerText = emp.username || '-';
            document.getElementById('profileHeroEmail').innerText = emp.email || '-';
            document.getElementById('profileHeroPhone').innerText = emp.phone || '-';
            document.getElementById('profileHeroSeniority').innerText = emp.seniority || 'none';
            document.getElementById('profileHeroEvaluation').innerText = emp.evaluation || 'none';

            const statusBadge = document.getElementById('profileHeroStatusBadge');
            const avatarBadge = document.getElementById('profileAvatarBadge');
            const toggleBtn = document.getElementById('profileToggleStatusBtn');

            if (emp.is_active) {
                statusBadge.innerText = 'Aktif';
                statusBadge.className = 'badge-pill badge-active';
                avatarBadge.className = 'profile-avatar-badge';
                if (toggleBtn) {
                    toggleBtn.innerText = '✔ Status: Aktif';
                    toggleBtn.style.background = '#28a745';
                }
            } else {
                statusBadge.innerText = 'Non-Aktif';
                statusBadge.className = 'badge-pill badge-inactive';
                avatarBadge.className = 'profile-avatar-badge inactive';
                if (toggleBtn) {
                    toggleBtn.innerText = '⊘ Status: Non-Aktif';
                    toggleBtn.style.background = '#dc2626';
                }
            }

            // Form inputs
            document.getElementById('bioName').value = emp.name || '';
            document.getElementById('bioUsername').value = emp.username || '';
            document.getElementById('bioCode').value = emp.code || '';
            if (emp.branch_id) {
                document.getElementById('bioBranchId').value = emp.branch_id;
            }
            if (emp.position) {
                document.getElementById('bioPosition').value = emp.position;
            }
            if (emp.jabatan) {
                document.getElementById('bioJabatan').value = emp.jabatan;
            }
            document.getElementById('bioEmail').value = emp.email || '';
            document.getElementById('bioPhone').value = emp.phone || '';
            if (emp.employment_status) {
                document.getElementById('bioEmploymentStatus').value = emp.employment_status;
            }
            if (emp.seniority) {
                document.getElementById('bioSeniority').value = emp.seniority;
            }
            if (emp.evaluation) {
                document.getElementById('bioEvaluation').value = emp.evaluation;
            }
            document.getElementById('bioMaxDiscount').value = emp.max_discount_approval_percent || 0;

            // ID Card
            document.getElementById('idCardAvatarLetter').innerText = letter;
            document.getElementById('idCardName').innerText = emp.name;
            document.getElementById('idCardPosition').innerText = (emp.position || 'Staff') + (emp.branch ? ' @ ' + emp.branch.name : '');
            document.getElementById('idCardCode').innerText = emp.code || 'EMP-' + emp.id;
            document.getElementById('idCardBranch').innerText = emp.branch ? emp.branch.name : 'Maya Graha Pusat';
            document.getElementById('idCardBottomCode').innerText = emp.code || 'EMP-' + emp.id;
        }

        function renderRecentAttendanceTable(records) {
            const tbody = document.getElementById('profileAttendanceTableBody');
            if (!tbody) return;

            if (records.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 25px; color: #888;">
                            Belum ada riwayat kehadiran tercatat untuk karyawan ini.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = records.map((r, idx) => `
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td><strong>${r.work_date}</strong></td>
                    <td>${r.shift}</td>
                    <td class="text-center" style="font-weight: 600; color: #166534;">${r.clock_in}</td>
                    <td class="text-center" style="color: #475569;">${r.clock_out}</td>
                    <td class="text-center">${r.duration}</td>
                    <td class="text-center">
                        <span class="badge-pill" style="background: #e0f2fe; color: #0369a1; font-size: 10px;">${r.status_label}</span>
                    </td>
                    <td style="font-size: 11px; color: #64748b;">${r.verification}</td>
                </tr>
            `).join('');
        }

        function saveProfileBio(event) {
            event.preventDefault();
            const payload = {
                name: document.getElementById('bioName').value,
                username: document.getElementById('bioUsername').value,
                branch_id: document.getElementById('bioBranchId').value,
                position: document.getElementById('bioPosition').value,
                jabatan: document.getElementById('bioJabatan').value,
                email: document.getElementById('bioEmail').value,
                phone: document.getElementById('bioPhone').value,
                employment_status: document.getElementById('bioEmploymentStatus').value,
                seniority: document.getElementById('bioSeniority').value,
                evaluation: document.getElementById('bioEvaluation').value,
                max_discount_approval_percent: document.getElementById('bioMaxDiscount').value
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/admin/karyawan/${currentProfileEmpId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showToast('Biodata karyawan berhasil diperbarui!');
                    // Update array lokal
                    const localEmp = allEmployeesData.find(e => e.id == currentProfileEmpId);
                    if (localEmp) {
                        Object.assign(localEmp, payload);
                        renderProfileStaticData(localEmp);
                    }
                } else {
                    showToast('Gagal memperbarui data', 'error');
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan koneksi', 'error');
            });
        }

        function saveProfileAccess(event) {
            event.preventDefault();
            const rights = [];
            if (document.getElementById('prof_perm_master_data').checked) rights.push('master_data');
            if (document.getElementById('prof_perm_warnet_pos').checked) rights.push('pos_cashier');
            if (document.getElementById('prof_perm_inventory').checked) rights.push('stock_kitchen');
            if (document.getElementById('prof_perm_attendance').checked) rights.push('attendance');
            if (document.getElementById('prof_perm_schedule').checked) rights.push('schedule');
            if (document.getElementById('prof_perm_discount').checked) rights.push('discount_approval');

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(`/admin/karyawan/${currentProfileEmpId}/access-rights`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ access_rights: rights })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showToast('Hak akses modul ERP berhasil disimpan!');
                } else {
                    showToast('Gagal menyimpan hak akses', 'error');
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan jaringan', 'error');
            });
        }

        function toggleCurrentProfileStatus() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch(`/admin/karyawan/${currentProfileEmpId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showToast(res.message);
                    const localEmp = allEmployeesData.find(e => e.id == currentProfileEmpId);
                    if (localEmp) {
                        localEmp.is_active = res.is_active;
                        renderProfileStaticData(localEmp);
                    }
                }
            });
        }

        function refreshCurrentProfile() {
            loadEmployeeProfile(currentProfileEmpId);
            showToast('Data profil dimuat ulang');
        }

        function handleHashRouting() {
            const hash = window.location.hash;
            if (hash.startsWith('#profile')) {
                // Ekstrak ID jika ada e.g. #profile-5 atau #profile?id=5
                const match = hash.match(/#profile-(\d+)/) || hash.match(/id=(\d+)/);
                if (match && match[1]) {
                    currentProfileEmpId = parseInt(match[1]);
                }
                switchMainTab('profile');
            } else if (hash === '#jadwal') {
                window.location.href = "{{ route('admin.jadwal') }}#jadwal";
            } else if (hash === '#supplier') {
                window.location.href = "{{ route('admin.supplier') }}#supplier";
            } else if (hash === '#merk') {
                window.location.href = "{{ route('admin.merk') }}#merk";
            } else {
                switchMainTab('karyawan');
            }
        }

        window.addEventListener('hashchange', handleHashRouting);
        document.addEventListener('DOMContentLoaded', handleHashRouting);
    </script>
</body>
</html>
