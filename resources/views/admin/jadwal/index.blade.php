<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Jadwal Shift Operator</title>
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

        /* CENTER PAGE TITLE */
        .page-title-center {
            text-align: center;
            font-size: 20px;
            font-weight: 500;
            color: #333333;
            margin: 4px 0 16px;
            letter-spacing: 0.5px;
        }

        /* TOP ACTION TOOLBAR */
        .schedule-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .toolbar-left-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-active-schedule {
            background-color: #28a745;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .schedule-title-display {
            font-size: 15px;
            font-weight: 700;
            color: #2c3e50;
        }

        .toolbar-right-btns {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action-tool {
            background: #ffffff;
            border: 1px solid #ced4da;
            color: #495057;
            padding: 6px 14px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 3px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            text-decoration: none;
        }

        .btn-action-tool:hover {
            background: #e9ecef;
            color: #212529;
            border-color: #adb5bd;
        }

        .btn-upload-schedule {
            background: #00bcd4;
            border-color: #00acc1;
            color: #ffffff;
        }

        .btn-upload-schedule:hover {
            background: #00acc1;
            color: #ffffff;
        }

        /* SCHEDULE IMAGE CARD */
        .schedule-image-card {
            background: #ffffff;
            border-radius: 4px;
            border: 1px solid #d2d6de;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-header-bar {
            background: #f8fafc;
            padding: 10px 16px;
            border-bottom: 1px solid #edf1f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #555555;
        }

        .image-viewer-box {
            position: relative;
            background: #ffffff;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            overflow: auto;
        }

        .schedule-main-image {
            max-width: 100%;
            height: auto;
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease-in-out;
            cursor: zoom-in;
            border: 1px solid #e2e8f0;
        }

        /* ZOOM CONTROLS OVERLAY */
        .zoom-controls-floating {
            position: absolute;
            bottom: 24px;
            right: 24px;
            background: rgba(34, 45, 50, 0.85);
            backdrop-filter: blur(4px);
            padding: 6px 10px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            z-index: 10;
        }

        .btn-zoom {
            background: transparent;
            border: none;
            color: #ffffff;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }

        .btn-zoom:hover {
            background: rgba(255,255,255,0.25);
        }

        .zoom-level-text {
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
            font-family: monospace;
        }

        /* SHIFT HOURS & OPERATOR INFO BADGES */
        .schedule-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .detail-panel-box {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 3px;
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .detail-panel-title {
            font-size: 13px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid #edf1f5;
            padding-bottom: 6px;
        }

        .shift-times-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            list-style: none;
        }

        .shift-time-badge {
            background: #f1f5f9;
            border-left: 3px solid #2c7ea1;
            padding: 6px 10px;
            border-radius: 2px;
            font-size: 12px;
        }

        .shift-time-badge strong {
            color: #0f172a;
            display: block;
        }

        .operator-tags-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .op-tag {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .tag-andra { background-color: #8c4b18; }
        .tag-ridwan { background-color: #1e293b; }
        .tag-bagus { background-color: #c5b48e; color: #1e293b; }
        .tag-yulika { background-color: #f4b3c2; color: #4a1525; }
        .tag-vio { background-color: #882b34; }
        .tag-grace { background-color: #3b82f6; }
        .tag-aan { background-color: #68853b; }
        .tag-bo { background-color: #7dd3fc; color: #0369a1; }
        .tag-libur { background-color: #ef4444; }

        /* RIWAYAT / ARSIP JADWAL */
        .history-card-box {
            background: #ffffff;
            border: 1px solid #d2d6de;
            border-radius: 3px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .history-title {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .history-grid-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, min-width: 220px);
            gap: 14px;
        }

        .history-item-card {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            background: #ffffff;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .history-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .history-item-card.is-active-item {
            border-color: #28a745;
            box-shadow: 0 0 0 1px #28a745;
        }

        .history-thumb-wrap {
            height: 120px;
            background: #f8fafc;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #edf1f5;
            cursor: pointer;
        }

        .history-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s;
        }

        .history-thumb-wrap:hover .history-thumb-img {
            transform: scale(1.05);
        }

        .history-info-box {
            padding: 8px 10px;
        }

        .history-item-title {
            font-weight: 700;
            font-size: 12px;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-item-date {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .history-actions {
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .btn-set-active {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            cursor: pointer;
            color: #334155;
            flex: 1;
            transition: all 0.15s;
        }

        .btn-set-active:hover {
            background: #28a745;
            border-color: #28a745;
            color: #ffffff;
        }

        .btn-del-history {
            background: transparent;
            border: 1px solid transparent;
            padding: 3px 6px;
            border-radius: 3px;
            cursor: pointer;
            color: #ef4444;
            font-size: 12px;
        }

        .btn-del-history:hover {
            background: #fee2e2;
        }

        /* MODAL UPLOAD STYLES */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
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
            padding: 20px;
        }

        .dropzone-box {
            border: 2px dashed #94a3b8;
            background: #f8fafc;
            border-radius: 6px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            margin-bottom: 14px;
        }

        .dropzone-box:hover, .dropzone-box.dragover {
            border-color: #2c7ea1;
            background: #f0f9ff;
        }

        .dropzone-icon {
            font-size: 36px;
            margin-bottom: 6px;
        }

        .dropzone-text {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .dropzone-subtext {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        .image-preview-holder {
            display: none;
            margin-top: 10px;
            text-align: center;
        }

        .image-preview-holder img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
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

        /* LIGHTBOX FULLSCREEN MODAL */
        .lightbox-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.92);
            z-index: 3000;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .lightbox-modal.active {
            display: flex;
        }

        .lightbox-img-wrap {
            max-width: 95vw;
            max-height: 88vh;
            overflow: auto;
            text-align: center;
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 85vh;
            border-radius: 4px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.5);
        }

        .lightbox-close-btn {
            position: absolute;
            top: 16px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: #ffffff;
            font-size: 26px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .lightbox-close-btn:hover {
            background: rgba(255,255,255,0.4);
        }

        /* TOAST NOTIFIKASI */
        .toast-box {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 4000;
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

        /* PRINT STYLES */
        @media print {
            .main-header, .sub-navbar, .main-sidebar, .schedule-toolbar, .zoom-controls-floating, .history-card-box {
                display: none !important;
            }
            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }
            .schedule-image-card {
                border: none !important;
                box-shadow: none !important;
            }
            .schedule-main-image {
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
            }
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
            .schedule-details-grid {
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
                    <a href="{{ route('admin.jadwal') }}#jadwal">
                        <span class="menu-item-left">🕒 <span>jadwal shift</span></span>
                    </a>
                </li>
                <li>
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
            <!-- PAGE TITLE -->
            <h1 class="page-title-center">jadwal shift operator</h1>

            <!-- ACTION TOOLBAR -->
            <div class="schedule-toolbar">
                <div class="toolbar-left-info">
                    <span class="badge-active-schedule">● Jadwal Aktif</span>
                    <span class="schedule-title-display" id="activeScheduleTitle">
                        {{ $activeSchedule?->title ?? 'Jadwal Shift Operator Maya' }}
                    </span>
                    @if($activeSchedule?->period)
                    <span style="font-size: 12px; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 3px;">
                        📅 {{ $activeSchedule->period }}
                    </span>
                    @endif
                </div>

                <div class="toolbar-right-btns">
                    <button class="btn-action-tool" style="background: #0284c7; color: #fff;" onclick="openStaticUploadModal()">
                        ⚡ Unggah Cepat Statis (Tanpa DB)
                    </button>
                    <button class="btn-action-tool btn-upload-schedule" onclick="openUploadModal()">
                        ➕ Unggah Gambar Jadwal Baru
                    </button>
                    <button class="btn-action-tool" onclick="openLightbox()">
                        🔲 Layar Penuh
                    </button>
                    <button class="btn-action-tool" onclick="window.print()">
                        🖨️ Cetak
                    </button>
                    <a href="{{ $activeSchedule ? asset($activeSchedule->image_path) : asset('uploads/jadwal/jadwal-shift-current.jpg') }}" 
                       download="jadwal-shift-maya.jpg" 
                       class="btn-action-tool">
                        ⬇️ Unduh
                    </a>
                </div>
            </div>

            <!-- SCHEDULE IMAGE DISPLAY CARD -->
            <div class="schedule-image-card">
                <div class="card-header-bar">
                    <span>
                        📷 <strong>Gambar Jadwal Shift Kerja Aktif</strong> 
                        <span style="color: #94a3b8; margin-left: 6px;">(Klik gambar untuk melihat dalam ukuran penuh / zoom)</span>
                    </span>
                    <span style="font-size: 11px; color: #64748b;">
                        Terakhir Diperbarui: {{ $activeSchedule?->updated_at?->format('d M Y H:i') ?? date('d M Y') }} WIB
                    </span>
                </div>

                <div class="image-viewer-box" id="imageViewerBox">
                    @php
                        $imageSrc = $activeSchedule ? asset($activeSchedule->image_path) : asset('uploads/jadwal/jadwal-shift-current.jpg');
                    @endphp
                    <img src="{{ $imageSrc }}" 
                         alt="Jadwal Shift Operator Maya" 
                         class="schedule-main-image" 
                         id="mainScheduleImg"
                         onclick="openLightbox()">

                    <!-- FLOATING ZOOM CONTROLS -->
                    <div class="zoom-controls-floating">
                        <button class="btn-zoom" onclick="adjustZoom(-0.15)" title="Perkecil">−</button>
                        <span class="zoom-level-text" id="zoomLevelText">100%</span>
                        <button class="btn-zoom" onclick="adjustZoom(0.15)" title="Perbesar">+</button>
                        <button class="btn-zoom" onclick="resetZoom()" title="Reset Zoom" style="font-size: 11px;">↺</button>
                    </div>
                </div>
            </div>

            <!-- SHIFT DETAILS & OPERATOR LEGEND -->
            <div class="schedule-details-grid">
                <!-- SHIFT HOURS -->
                <div class="detail-panel-box">
                    <div class="detail-panel-title">
                        🕒 Jam Kerja 4 Shift Operator:
                    </div>
                    <ul class="shift-times-list">
                        <li class="shift-time-badge">
                            <strong>Shift 1 (Malam - Pagi):</strong>
                            01:00 - 07:00 WIB
                        </li>
                        <li class="shift-time-badge">
                            <strong>Shift 2 (Pagi - Siang):</strong>
                            07:00 - 13:00 WIB
                        </li>
                        <li class="shift-time-badge">
                            <strong>Shift 3 (Siang - Malam):</strong>
                            13:00 - 19:00 WIB
                        </li>
                        <li class="shift-time-badge">
                            <strong>Shift 4 (Malam):</strong>
                            19:00 - 01:00 WIB
                        </li>
                    </ul>
                </div>

                <!-- OPERATOR COLOR LEGEND -->
                <div class="detail-panel-box">
                    <div class="detail-panel-title">
                        👥 Keterangan Warna Operator:
                    </div>
                    <div class="operator-tags-wrap">
                        <span class="op-tag tag-andra">ANDRA</span>
                        <span class="op-tag tag-ridwan">RIDWAN</span>
                        <span class="op-tag tag-bagus">BAGUS</span>
                        <span class="op-tag tag-yulika">YULIKA</span>
                        <span class="op-tag tag-vio">VIO</span>
                        <span class="op-tag tag-grace">GRACE</span>
                        <span class="op-tag tag-aan">AAN</span>
                        <span class="op-tag tag-bo">BO (Back Office)</span>
                        <span class="op-tag tag-libur">LIBUR</span>
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 10px;">
                        *Data jadwal dikelola dalam format gambar tabel mingguan agar mudah dibagikan & dicetak.
                    </div>
                </div>
            </div>

            <!-- RIWAYAT GAMBAR JADWAL SHIFT -->
            <div class="history-card-box">
                <div class="history-title">
                    <span>🗂️ Arsip / Riwayat Gambar Jadwal Shift</span>
                    <button type="button" class="btn-action-tool" onclick="openUploadModal()" style="font-size: 11.5px; padding: 4px 10px;">
                        ➕ Upload Gambar Baru
                    </button>
                </div>

                <div class="history-grid-list">
                    @forelse($historySchedules as $item)
                    <div class="history-item-card {{ $item->is_active ? 'is-active-item' : '' }}" id="history-card-{{ $item->id }}">
                        <div class="history-thumb-wrap" onclick="previewHistoryImage('{{ asset($item->image_path) }}', '{{ $item->title }}')">
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->title }}" class="history-thumb-img">
                        </div>
                        <div class="history-info-box">
                            <div class="history-item-title" title="{{ $item->title }}">{{ $item->title }}</div>
                            <div class="history-item-date">{{ $item->created_at?->format('d M Y H:i') }}</div>
                            <div class="history-actions">
                                @if($item->is_active)
                                <span style="font-size: 11px; font-weight: 700; color: #28a745;">✔ Sedang Aktif</span>
                                @else
                                <button type="button" class="btn-set-active" onclick="setActiveSchedule({{ $item->id }})">
                                    Jadikan Aktif
                                </button>
                                @endif
                                <button type="button" class="btn-del-history" onclick="deleteScheduleImage({{ $item->id }})" title="Hapus Gambar">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: #94a3b8; padding: 20px;">
                        Belum ada arsip jadwal.
                    </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL UPLOAD GAMBAR JADWAL SHIFT -->
    <div class="modal-overlay" id="uploadScheduleModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">Unggah Gambar Jadwal Shift Baru</div>
                <button class="modal-close-btn" onclick="closeModal('uploadScheduleModal')">&times;</button>
            </div>
            <form id="formUploadSchedule" onsubmit="submitUploadSchedule(event)" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- DROPZONE -->
                    <div class="dropzone-box" id="dropzoneBox" onclick="document.getElementById('inputScheduleImage').click()">
                        <div class="dropzone-icon">🖼️</div>
                        <div class="dropzone-text">Klik untuk pilih gambar atau seret file ke sini</div>
                        <div class="dropzone-subtext">Mendukung format JPG, PNG, WEBP (Maksimal 10 MB)</div>
                        <input type="file" id="inputScheduleImage" name="image" accept="image/*" style="display: none;" onchange="handleFileSelect(this)">
                    </div>

                    <!-- PREVIEW IMAGE HOLDER -->
                    <div class="image-preview-holder" id="imagePreviewHolder">
                        <img src="" id="imgPreviewTarget" alt="Preview Gambar Jadwal">
                        <div style="margin-top: 4px; font-size: 11px; color: #64748b;" id="previewFileName"></div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label" for="scheduleTitle">Judul Jadwal</label>
                        <input type="text" id="scheduleTitle" name="title" class="form-control" placeholder="Contoh: Jadwal Shift Operator 12 - 18 Oktober">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="schedulePeriod">Periode / Tanggal</label>
                        <input type="text" id="schedulePeriod" name="period" class="form-control" placeholder="Contoh: 12 - 18 Oktober (Senin - Minggu)">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="scheduleNotes">Catatan Tambahan (Opsional)</label>
                        <textarea id="scheduleNotes" name="notes" class="form-control" rows="2" placeholder="Keterangan rotasi / shift..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('uploadScheduleModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitUpload">Unggah & Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL UPLOAD STATIS TANPA DATABASE -->
    <div class="modal-overlay" id="staticUploadModal">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <div class="modal-title">⚡ Unggah Gambar Statis (Tanpa Database)</div>
                <button class="modal-close-btn" onclick="closeModal('staticUploadModal')">&times;</button>
            </div>
            <form action="{{ route('admin.jadwal.upload-static') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;">
                        Mode ini akan <strong>langsung menimpa file fisik</strong> <code>public/uploads/jadwal/jadwal-shift-current.jpg</code> secara instan tanpa memerlukan query database baru.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Pilih Gambar Jadwal Baru (JPG / PNG / WEBP maks 10MB)</label>
                        <input type="file" name="image" accept="image/*" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('staticUploadModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" style="background: #0284c7;">Timpa File Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LIGHTBOX FULLSCREEN MODAL -->
    <div class="lightbox-modal" id="lightboxModal" onclick="closeLightboxOnBackdrop(event)">
        <button class="lightbox-close-btn" onclick="closeLightbox()" title="Tutup">&times;</button>
        <div class="lightbox-img-wrap">
            <img src="{{ $imageSrc }}" id="lightboxTargetImg" class="lightbox-img" alt="Layar Penuh Jadwal Shift">
        </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentZoom = 1.0;

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

        // ZOOM CONTROLS
        function adjustZoom(amount) {
            currentZoom = Math.max(0.5, Math.min(2.5, currentZoom + amount));
            applyZoom();
        }

        function resetZoom() {
            currentZoom = 1.0;
            applyZoom();
        }

        function applyZoom() {
            const img = document.getElementById('mainScheduleImg');
            const label = document.getElementById('zoomLevelText');
            if (img) {
                img.style.transform = `scale(${currentZoom})`;
            }
            if (label) {
                label.innerText = `${Math.round(currentZoom * 100)}%`;
            }
        }

        // LIGHTBOX
        function openLightbox() {
            const mainImg = document.getElementById('mainScheduleImg');
            const target = document.getElementById('lightboxTargetImg');
            if (mainImg && target) {
                target.src = mainImg.src;
            }
            document.getElementById('lightboxModal').classList.add('active');
        }

        function closeLightbox() {
            document.getElementById('lightboxModal').classList.remove('active');
        }

        function closeLightboxOnBackdrop(e) {
            if (e.target.id === 'lightboxModal') {
                closeLightbox();
            }
        }

        function previewHistoryImage(url, title) {
            const target = document.getElementById('lightboxTargetImg');
            if (target) {
                target.src = url;
            }
            document.getElementById('lightboxModal').classList.add('active');
        }

        // MODAL HELPERS
        function openUploadModal() {
            document.getElementById('formUploadSchedule').reset();
            document.getElementById('imagePreviewHolder').style.display = 'none';
            document.getElementById('uploadScheduleModal').classList.add('active');
        }

        function openStaticUploadModal() {
            document.getElementById('staticUploadModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // FILE PREVIEW & DRAG-DROP
        const dropzone = document.getElementById('dropzoneBox');
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                document.getElementById('inputScheduleImage').files = e.dataTransfer.files;
                handleFileSelect(document.getElementById('inputScheduleImage'));
            }
        });

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewHolder = document.getElementById('imagePreviewHolder');
                    const previewImg = document.getElementById('imgPreviewTarget');
                    const fileNameText = document.getElementById('previewFileName');
                    previewImg.src = e.target.result;
                    fileNameText.innerText = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                    previewHolder.style.display = 'block';

                    // Isi otomatis title jika kosong
                    const titleInput = document.getElementById('scheduleTitle');
                    if (!titleInput.value) {
                        const now = new Date();
                        titleInput.value = `Jadwal Shift ${now.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`;
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        // SUBMIT UPLOAD JADWAL BARU
        function submitUploadSchedule(e) {
            e.preventDefault();
            const fileInput = document.getElementById('inputScheduleImage');
            if (!fileInput.files || !fileInput.files[0]) {
                showToast('Pilih file gambar jadwal terlebih dahulu!', 'error');
                return;
            }

            const btn = document.getElementById('btnSubmitUpload');
            btn.disabled = true;
            btn.innerText = 'Mengunggah...';

            const formData = new FormData(document.getElementById('formUploadSchedule'));

            fetch("{{ route('admin.jadwal.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Unggah & Terapkan';

                if (data.success && data.schedule) {
                    closeModal('uploadScheduleModal');
                    showToast(data.message || 'Jadwal shift berhasil diperbarui!', 'success');
                    // Update tampilan gambar dan judul
                    const mainImg = document.getElementById('mainScheduleImg');
                    if (mainImg) {
                        mainImg.src = `{{ url('/') }}/${data.schedule.image_path}?t=${Date.now()}`;
                    }
                    const titleDisplay = document.getElementById('activeScheduleTitle');
                    if (titleDisplay) {
                        titleDisplay.innerText = data.schedule.title;
                    }
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast(data.message || 'Gagal mengunggah jadwal', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Unggah & Terapkan';
                showToast('Terjadi kesalahan jaringan saat mengunggah', 'error');
            });
        }

        // SET ACTIVE SCHEDULE
        function setActiveSchedule(id) {
            fetch(`{{ url('admin/jadwal') }}/${id}/set-active`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Jadwal aktif berhasil diubah', 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast('Gagal mengubah jadwal aktif', 'error');
                }
            })
            .catch(() => showToast('Kesalahan koneksi server', 'error'));
        }

        // DELETE SCHEDULE IMAGE
        function deleteScheduleImage(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus arsip gambar jadwal ini?')) {
                return;
            }

            fetch(`{{ url('admin/jadwal') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Arsip gambar jadwal berhasil dihapus', 'success');
                    const card = document.getElementById(`history-card-${id}`);
                    if (card) card.remove();
                } else {
                    showToast('Gagal menghapus gambar', 'error');
                }
            })
            .catch(() => showToast('Kesalahan server', 'error'));
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

        // ESCAPE TO CLOSE MODALS
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
                closeModal('uploadScheduleModal');
            }
        });
    </script>
</body>
</html>
