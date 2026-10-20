<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MGK CYBER LOUNGE — Warnet & Gaming Billing System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg-body: #090d16;
            --bg-surface: #0f172a;
            --bg-card: #151f32;
            --bg-card-hover: #1c2a44;
            --bg-input: #0b1120;
            --border-glass: rgba(255, 255, 255, 0.08);
            --border-glow: rgba(6, 182, 212, 0.25);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-sub: #64748b;
            --cyan: #06b6d4;
            --cyan-glow: rgba(6, 182, 212, 0.4);
            --emerald: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.35);
            --amber: #f59e0b;
            --amber-glow: rgba(245, 158, 11, 0.35);
            --rose: #f43f5e;
            --rose-glow: rgba(244, 63, 94, 0.35);
            --purple: #8b5cf6;
            --blue: #3b82f6;
            --font-main: 'Plus Jakarta Sans', -apple-system, sans-serif;
            --font-num: 'Outfit', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-body);
            color: var(--text-main);
            font-size: 13px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(6, 182, 212, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 85% 20%, rgba(139, 92, 246, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 50% 80%, rgba(16, 185, 129, 0.03) 0%, transparent 50%);
        }

        /* TOP NAVIGATION & TOOLBAR */
        .cyber-header {
            background: linear-gradient(180deg, #131c2e 0%, #0d1422 100%);
            border-bottom: 1px solid var(--border-glass);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 900;
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284c7, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            box-shadow: 0 0 16px var(--cyan-glow);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .brand-titles h1 {
            font-family: var(--font-num);
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-titles h1 span.edition {
            font-size: 10px;
            font-weight: 700;
            background: rgba(6, 182, 212, 0.15);
            color: var(--cyan);
            border: 1px solid rgba(6, 182, 212, 0.3);
            padding: 2px 8px;
            border-radius: 20px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .brand-titles p {
            font-size: 11px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tool-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            color: var(--text-main);
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .tool-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .tool-btn.primary {
            background: linear-gradient(135deg, #0284c7, #06b6d4);
            border: none;
            color: #fff;
            box-shadow: 0 2px 10px var(--cyan-glow);
        }

        .tool-btn.emerald {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .status-chip {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border-glass);
            padding: 6px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-dot.active {
            background-color: var(--emerald);
            box-shadow: 0 0 8px var(--emerald);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }

        /* NAVIGATION TABS */
        .tab-bar-container {
            background: #0f1626;
            border-bottom: 1px solid var(--border-glass);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            overflow-x: auto;
        }

        .nav-tabs {
            display: flex;
            gap: 4px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-muted);
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.03);
        }

        .tab-btn.active {
            color: var(--cyan);
            border-bottom-color: var(--cyan);
            background: rgba(6, 182, 212, 0.06);
        }

        .tab-badge {
            background: rgba(6, 182, 212, 0.2);
            color: var(--cyan);
            font-size: 11px;
            padding: 1px 7px;
            border-radius: 10px;
            font-family: var(--font-num);
            font-weight: 700;
        }

        /* METRICS SUMMARY STRIP */
        .metrics-strip {
            padding: 14px 20px 6px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .metric-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .metric-info h4 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-sub);
            margin-bottom: 4px;
        }

        .metric-info .value {
            font-family: var(--font-num);
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }

        .metric-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .metric-icon.cyan { background: rgba(6, 182, 212, 0.12); color: var(--cyan); }
        .metric-icon.emerald { background: rgba(16, 185, 129, 0.12); color: var(--emerald); }
        .metric-icon.purple { background: rgba(139, 92, 246, 0.12); color: var(--purple); }

        /* MAIN CONTENT AREA */
        .main-container {
            flex: 1;
            padding: 16px 20px 80px 20px;
        }

        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* STATION MONITORING */
        .station-toolbar {
            background: var(--bg-surface);
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .station-filters {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-pill:hover, .filter-pill.active {
            background: rgba(6, 182, 212, 0.15);
            color: var(--cyan);
            border-color: rgba(6, 182, 212, 0.35);
        }

        /* GRID VIEW (Interactive Gaming Rigs) */
        .station-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .pc-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 18px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }

        .pc-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }

        .pc-card.occupied {
            border-color: rgba(6, 182, 212, 0.45);
            background: linear-gradient(180deg, #132338 0%, #0e192a 100%);
            box-shadow: 0 0 24px rgba(6, 182, 212, 0.12);
        }

        .pc-card.available {
            border-color: rgba(16, 185, 129, 0.3);
            background: linear-gradient(180deg, #11221c 0%, #0d1b16 100%);
        }

        .pc-card.maintenance {
            border-color: rgba(244, 63, 94, 0.3);
            background: linear-gradient(180deg, #24141a 0%, #1a0e13 100%);
            opacity: 0.85;
        }

        .pc-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .pc-code-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pc-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .pc-card.occupied .pc-icon-box { background: rgba(6, 182, 212, 0.15); color: var(--cyan); border-color: rgba(6, 182, 212, 0.3); }
        .pc-card.available .pc-icon-box { background: rgba(16, 185, 129, 0.15); color: var(--emerald); border-color: rgba(16, 185, 129, 0.3); }
        .pc-card.maintenance .pc-icon-box { background: rgba(244, 63, 94, 0.15); color: var(--rose); border-color: rgba(244, 63, 94, 0.3); }

        .pc-title h3 {
            font-family: var(--font-num);
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }

        .pc-title .ip-spec {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text-sub);
        }

        .pc-status-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .pc-status-badge.available {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .pc-status-badge.occupied {
            background: rgba(6, 182, 212, 0.15);
            color: var(--cyan);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .pc-status-badge.maintenance {
            background: rgba(244, 63, 94, 0.15);
            color: #fb7185;
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        .pc-middle {
            margin: 10px 0 16px 0;
            padding: 12px;
            background: rgba(0, 0, 0, 0.35);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .user-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .user-name-tag {
            font-weight: 700;
            color: #f1f5f9;
            font-size: 14px;
        }

        .rate-tag {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text-muted);
        }

        .timer-line {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed rgba(255, 255, 255, 0.08);
        }

        .timer-val {
            font-family: var(--font-mono);
            font-size: 18px;
            font-weight: 700;
            color: var(--cyan);
        }

        .cost-val {
            font-family: var(--font-num);
            font-size: 17px;
            font-weight: 800;
            color: #34d399;
        }

        .pc-bottom-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 6px;
        }

        .btn-act {
            border: none;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-act.masuk {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
            grid-column: span 2;
            padding: 12px;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .btn-act.masuk:hover {
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.5);
            transform: translateY(-1px);
        }

        .btn-act.selesai {
            background: linear-gradient(135deg, #e11d48, #f43f5e);
            color: #fff;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
        }
        .btn-act.selesai:hover {
            box-shadow: 0 6px 18px rgba(244, 63, 94, 0.5);
            transform: translateY(-1px);
        }

        .btn-act.fnb {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .btn-act.fnb:hover {
            background: rgba(245, 158, 11, 0.25);
        }

        .btn-act.sub {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-muted);
            border: 1px solid var(--border-glass);
        }
        .btn-act.sub:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        /* TABLES */
        .cyber-table-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .cyber-table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .cyber-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }

        .cyber-table thead th {
            background: #111a2c;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-glass);
            white-space: nowrap;
        }

        .cyber-table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            transition: background 0.15s;
        }

        .cyber-table tbody tr:hover {
            background: rgba(6, 182, 212, 0.05);
        }

        .cyber-table td {
            padding: 12px 14px;
            vertical-align: middle;
            color: var(--text-main);
            white-space: nowrap;
        }

        /* REPORT DATABASE B-EXP */
        .report-section {
            background: var(--bg-surface);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            padding: 18px 20px;
        }

        .report-header-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-glass);
            margin-bottom: 16px;
        }

        .report-title-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .report-title-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            box-shadow: 0 4px 16px var(--cyan-glow);
        }

        .report-title-text h2 {
            font-family: var(--font-num);
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }

        .report-title-text p {
            font-size: 11px;
            color: var(--text-muted);
        }

        .report-filter-panel {
            background: #111a2b;
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .cyber-select, .cyber-input {
            background: var(--bg-input);
            border: 1px solid var(--border-glass);
            color: #fff;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-family: var(--font-main);
            outline: none;
            transition: all 0.2s;
        }

        .cyber-select:focus, .cyber-input:focus {
            border-color: var(--cyan);
            box-shadow: 0 0 10px var(--cyan-glow);
        }

        .radio-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .radio-pill-item {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-glass);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .radio-pill-item input[type="radio"] {
            accent-color: var(--cyan);
        }

        .radio-pill-item:has(input:checked) {
            background: rgba(6, 182, 212, 0.15);
            color: var(--cyan);
            border-color: rgba(6, 182, 212, 0.35);
        }

        .report-summary-footer {
            background: #111a2b;
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .summary-boxes {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .summary-box-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .summary-box-item span {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .summary-box-item .big-amount {
            font-family: var(--font-num);
            font-size: 24px;
            font-weight: 800;
            color: #34d399;
        }

        .summary-box-item .count-val {
            font-family: var(--font-num);
            font-size: 22px;
            font-weight: 800;
            color: var(--cyan);
        }

        /* MODALS */
        .cyber-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }
        .cyber-modal-overlay.active {
            display: flex;
            animation: fadeIn 0.2s ease;
        }

        .cyber-modal {
            background: #111a2c;
            border: 1px solid var(--border-glass);
            border-radius: 18px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .modal-header {
            background: #162238;
            border-bottom: 1px solid var(--border-glass);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            font-family: var(--font-num);
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 22px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .modal-close-btn:hover {
            background: rgba(244, 63, 94, 0.2);
            color: #fb7185;
        }

        .modal-body {
            padding: 22px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-footer {
            background: #0f1728;
            border-top: 1px solid var(--border-glass);
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        /* FORM ELEMENTS */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .form-group-full {
            grid-column: span 2;
        }
        .form-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
        }

        /* QUICK MONEY BUTTONS */
        .quick-money-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            color: var(--text-main);
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .quick-money-btn:hover {
            background: rgba(6, 182, 212, 0.15);
            color: var(--cyan);
            border-color: rgba(6, 182, 212, 0.4);
        }

        /* THERMAL RECEIPT PREVIEW (OPTIONAL ONLY) */
        .thermal-receipt {
            background: #fff;
            color: #111;
            font-family: 'Courier New', Courier, monospace;
            padding: 20px;
            border-radius: 6px;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            font-size: 12px;
            line-height: 1.4;
        }
        .thermal-receipt .center { text-align: center; }
        .thermal-receipt .line { border-bottom: 1px dashed #666; margin: 8px 0; }
        .thermal-receipt .double-line { border-bottom: 2px dashed #111; margin: 8px 0; }
        .thermal-receipt .flex-row { display: flex; justify-content: space-between; }

        /* TOAST NOTIFICATIONS */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1200;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-msg {
            background: #1e293b;
            border: 1px solid var(--border-glass);
            color: #fff;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        .toast-msg.success { border-color: rgba(16, 185, 129, 0.5); border-left: 5px solid var(--emerald); }
        .toast-msg.error { border-color: rgba(244, 63, 94, 0.5); border-left: 5px solid var(--rose); }
        .toast-msg.info { border-color: rgba(6, 182, 212, 0.5); border-left: 5px solid var(--cyan); }

        @keyframes slideIn {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <header class="cyber-header">
        <div class="header-brand">
            <div class="brand-logo-badge">⚡</div>
            <div class="brand-titles">
                <h1>
                    MGK CYBER LOUNGE
                    <span class="edition">v2026.1 ESPORTS</span>
                </h1>
                <p>
                    <span>Cabang: <strong>Maya Graha Kencana Warnet</strong></span>
                    <span>•</span>
                    <span id="headerLiveClock" style="font-family: var(--font-mono); color: var(--cyan);">--:--:-- WIB</span>
                </p>
            </div>
        </div>

        <div class="header-tools">
            <button type="button" class="tool-btn" id="soundToggleBtn" onclick="toggleAudioFeedback()">
                <span id="soundIcon">🔊</span>
                <span id="soundLabel">Sound : ON</span>
            </button>

            <button type="button" class="tool-btn primary" onclick="switchMainTab('tab-report', document.querySelectorAll('.tab-btn')[1])">
                <span>📊</span>
                <span>Report Database B-Exp</span>
            </button>

            <div class="status-chip">
                <span class="status-dot active"></span>
                <span>Operator: <strong>{{ $employees->first()->name ?? 'Operator Kasir' }}</strong></span>
            </div>

            <a href="{{ route('admin.index') }}" class="tool-btn" title="Buka Panel Admin ERP">
                <span>⚙️</span>
                <span>Admin ERP</span>
            </a>
        </div>
    </header>

    <!-- TABS BAR -->
    <div class="tab-bar-container">
        <div class="nav-tabs">
            <button type="button" class="tab-btn active" onclick="switchMainTab('tab-general', this)">
                <span>🖥️</span>
                <span>General (Station Monitoring)</span>
                <span class="tab-badge">{{ $pcStations->count() }}</span>
            </button>

            <button type="button" class="tab-btn" onclick="switchMainTab('tab-report', this)">
                <span>📊</span>
                <span>Report Database B-Exp</span>
                <span class="tab-badge">{{ $sales->count() }}</span>
            </button>

            <button type="button" class="tab-btn" onclick="switchMainTab('tab-fnb', this)">
                <span>🛒</span>
                <span>POS & F&B Meja</span>
            </button>

            <button type="button" class="tab-btn" onclick="switchMainTab('tab-client', this)">
                <span>⚡</span>
                <span>Client Connection & Network</span>
            </button>

            <button type="button" class="tab-btn" onclick="switchMainTab('tab-kios', this)">
                <span>⏱️</span>
                <span>Kios Presensi Operator</span>
            </button>
        </div>

        <div style="font-size: 12px; color: var(--text-muted);">
            Server Uptime: <strong style="color: var(--emerald); font-family: var(--font-mono);">04:18:22</strong>
        </div>
    </div>

    <!-- METRICS STRIP -->
    <div class="metrics-strip">
        <div class="metric-card">
            <div class="metric-info">
                <h4>Total Unit Station</h4>
                <div class="value">{{ $pcStations->count() }} PC</div>
            </div>
            <div class="metric-icon cyan">💻</div>
        </div>

        <div class="metric-card">
            <div class="metric-info">
                <h4>Unit Sedang Main</h4>
                <div class="value" style="color: var(--cyan);" id="kpiOccupiedPcs">{{ $pcStations->where('status', 'occupied')->count() }} PC</div>
            </div>
            <div class="metric-icon cyan">⚡</div>
        </div>

        <div class="metric-card">
            <div class="metric-info">
                <h4>Unit Kosong (Ready)</h4>
                <div class="value" style="color: var(--emerald);" id="kpiAvailablePcs">{{ $pcStations->where('status', 'available')->count() }} PC</div>
            </div>
            <div class="metric-icon emerald">🟢</div>
        </div>

        <div class="metric-card">
            <div class="metric-info">
                <h4>Total Omset Kasir</h4>
                <div class="value" style="color: #34d399;" id="kpiTodaySales">Rp {{ number_format($sales->sum('net_amount'), 0, ',', '.') }}</div>
            </div>
            <div class="metric-icon emerald">💰</div>
        </div>
    </div>

    <!-- MAIN BODY -->
    <main class="main-container">

        <!-- TAB 1: GENERAL (STATION MONITORING) -->
        <section id="tab-general" class="tab-pane active">
            <div class="station-toolbar">
                <div class="station-filters">
                    <span style="font-size: 12px; color: var(--text-sub); margin-right: 4px;">Filter:</span>
                    <button type="button" class="filter-pill active" onclick="filterStations('all', this)">Semua ({{ $pcStations->count() }})</button>
                    <button type="button" class="filter-pill" onclick="filterStations('occupied', this)">🟢 Sedang Main ({{ $pcStations->where('status', 'occupied')->count() }})</button>
                    <button type="button" class="filter-pill" onclick="filterStations('available', this)">Ready Kosong ({{ $pcStations->where('status', 'available')->count() }})</button>
                    <button type="button" class="filter-pill" onclick="filterStations('maintenance', this)">Maintenance ({{ $pcStations->where('status', 'maintenance')->count() }})</button>
                </div>

                <div>
                    <button type="button" class="tool-btn primary" onclick="openStartModalDirect(null)">
                        <span>⚡</span>
                        <span>Mulai Billing Baru</span>
                    </button>
                </div>
            </div>

            <!-- STATION GRID -->
            <div class="station-grid" id="mainStationGrid">
                @foreach($pcStations as $station)
                @php
                    $isOccupied = $station->status === 'occupied';
                    $isMaintenance = $station->status === 'maintenance';
                    $ipSuffix = (int)preg_replace('/[^0-9]/', '', $station->station_code) ?: ($station->id + 10);
                    $ipAddress = "192.168.1." . (100 + $ipSuffix);
                @endphp
                <div class="pc-card {{ $station->status }}" 
                     id="pc-card-{{ $station->id }}"
                     data-id="{{ $station->id }}" 
                     data-code="{{ $station->station_code }}"
                     data-status="{{ $station->status }}"
                     data-rate="{{ $station->hourly_rate }}"
                     data-user="{{ $station->current_user_name ?? '' }}"
                     data-started="{{ $station->started_at ? \Carbon\Carbon::parse($station->started_at)->timestamp : 0 }}"
                     data-duration="{{ $station->duration_minutes }}"
                     data-billing="{{ $station->billing_amount }}"
                     data-ip="{{ $ipAddress }}">

                    <div class="pc-top">
                        <div class="pc-code-wrap">
                            <div class="pc-icon-box">
                                @if(str_contains(strtolower($station->station_code), 'vip'))
                                    👑
                                @else
                                    🖥️
                                @endif
                            </div>
                            <div class="pc-title">
                                <h3>{{ $station->station_code }}</h3>
                                <div class="ip-spec">{{ $ipAddress }} • {{ Str::limit($station->station_type, 20) }}</div>
                            </div>
                        </div>

                        <span class="pc-status-badge {{ $station->status }}" id="badge-status-{{ $station->id }}">
                            <span class="status-dot {{ $isOccupied ? 'active' : '' }}"></span>
                            <span class="badge-text">{{ $isOccupied ? 'SEDANG MAIN' : ($isMaintenance ? 'MAINTENANCE' : 'READY KOSONG') }}</span>
                        </span>
                    </div>

                    <div class="pc-middle">
                        <div class="user-line">
                            <span class="user-name-tag" id="user-display-{{ $station->id }}">
                                {{ $station->current_user_name ?: 'Tamu / Walk-in' }}
                            </span>
                            <span class="rate-tag">
                                Rp {{ number_format($station->hourly_rate, 0, ',', '.') }}/jam
                            </span>
                        </div>

                        <div class="timer-line">
                            <div>
                                <span style="font-size: 10px; color: var(--text-sub); display: block;">DURASI BERJALAN:</span>
                                <span class="timer-val pc-timer-display" id="timer-{{ $station->id }}">
                                    {{ $isOccupied ? '00:00:00' : '--:--:--' }}
                                </span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 10px; color: var(--text-sub); display: block;">BIAYA SEMENTARA:</span>
                                <span class="cost-val pc-cost-display" id="cost-{{ $station->id }}">
                                    Rp {{ number_format($station->billing_amount, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS: MASUK VS SELESAI -->
                    <div class="pc-bottom-actions" id="actions-{{ $station->id }}">
                        @if($isOccupied)
                            <button type="button" class="btn-act selesai" onclick="openSelesaiModal({{ $station->id }})">
                                <span>⏹️</span> KLIK SELESAI
                            </button>
                            <button type="button" class="btn-act fnb" onclick="openOrderFnbModal({{ $station->id }})">
                                <span>🍔</span> Pesan F&B
                            </button>
                        @elseif($isMaintenance)
                            <button type="button" class="btn-act masuk" onclick="setStationReady({{ $station->id }})">
                                <span>🛠️</span> Selesai Perbaikan (Set Ready)
                            </button>
                        @else
                            <button type="button" class="btn-act masuk" onclick="openStartModalDirect({{ $station->id }})">
                                <span>🟢</span> MASUK / MULAI MAIN
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- TAB 2: REPORT DATABASE B-EXP -->
        <section id="tab-report" class="tab-pane">
            <div class="report-section">
                <div class="report-header-banner">
                    <div class="report-title-box">
                        <div class="report-title-icon">📊</div>
                        <div class="report-title-text">
                            <h2>Report Database B-Exp</h2>
                            <p>Laporan Riwayat Transaksi Billing, F&B, dan Rekap Pendapatan Maya Graha Kencana</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="tool-btn" onclick="exportReportToCSV()">
                            <span>📁</span> File Output (CSV)
                        </button>
                        <button type="button" class="tool-btn emerald" onclick="printReportDocument()">
                            <span>🖨️</span> Print Rekap Laporan
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="report-filter-panel">
                    <div class="filter-group">
                        <label>OPERATOR</label>
                        <select id="reportFilterOperator" class="cyber-select" onchange="applyReportFilters()">
                            <option value="ALL">ALL (Semua Operator)</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->name }}">{{ $emp->name }} ({{ $emp->code }})</option>
                            @endforeach
                            <option value="Fitri">Fitri (Kasir Shift)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>PERIODE</label>
                        <div class="radio-pills">
                            <label class="radio-pill-item">
                                <input type="radio" name="reportPeriodRadio" value="day" checked onchange="applyReportFilters()"> Per Hari
                            </label>
                            <label class="radio-pill-item">
                                <input type="radio" name="reportPeriodRadio" value="month" onchange="applyReportFilters()"> Per Bulan
                            </label>
                            <label class="radio-pill-item">
                                <input type="radio" name="reportPeriodRadio" value="all" onchange="applyReportFilters()"> Semua
                            </label>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>TANGGAL</label>
                        <input type="date" id="reportFilterDate" class="cyber-input" value="{{ date('Y-m-d') }}" onchange="applyReportFilters()">
                    </div>

                    <div class="filter-group">
                        <label>CARI USER / NOTA</label>
                        <input type="text" id="reportSearchInput" class="cyber-input" placeholder="Ketik nama atau nota..." onkeyup="applyReportFilters()">
                    </div>
                </div>

                <!-- Table -->
                <div class="cyber-table-card">
                    <div class="cyber-table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="cyber-table" id="reportBExpTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nomor Nota</th>
                                    <th>Tanggal</th>
                                    <th>Komp</th>
                                    <th>User Name</th>
                                    <th>Biaya</th>
                                    <th>Jenis</th>
                                    <th>Operator</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                @forelse($sales as $sIndex => $sale)
                                @php
                                    $pkgType = 'Personal';
                                    if ($sale->total_billing > 0 && $sale->total_fnb == 0) {
                                        if ($sale->total_billing >= 25000) { $pkgType = 'Pk-7'; }
                                        elseif ($sale->total_billing >= 12000) { $pkgType = 'Pk-3'; }
                                        elseif ($sale->total_billing >= 8000) { $pkgType = 'Pk-2'; }
                                    } elseif ($sale->total_fnb > 0 && $sale->total_billing == 0) {
                                        $pkgType = 'F&B Only';
                                    }
                                    $opName = $sale->employee->name ?? 'Fitri';
                                    $stationName = $sale->pcStation->station_code ?? ($sale->pc_station_id ? "PC-0{$sale->pc_station_id}" : 'W/S 2');
                                @endphp
                                <tr class="report-row" 
                                    data-id="{{ $sale->id }}"
                                    data-invoice="{{ $sale->invoice_number }}"
                                    data-date="{{ $sale->created_at ? $sale->created_at->format('Y-m-d') : date('Y-m-d') }}"
                                    data-station="{{ $stationName }}"
                                    data-user="{{ $sale->customer_name }}"
                                    data-biaya="{{ $sale->net_amount }}"
                                    data-operator="{{ $opName }}">
                                    
                                    <td>{{ $sIndex + 1 }}</td>
                                    <td><strong style="color: var(--cyan); font-family: var(--font-mono);">{{ $sale->invoice_number }}</strong></td>
                                    <td>{{ $sale->created_at ? $sale->created_at->format('d-m-Y H:i') : date('d-m-Y') }}</td>
                                    <td><span class="status-chip" style="padding: 2px 8px;">{{ $stationName }}</span></td>
                                    <td><strong>{{ $sale->customer_name }}</strong></td>
                                    <td style="font-family: var(--font-num); font-weight: 700; color: #34d399;">
                                        Rp {{ number_format($sale->net_amount, 0, ',', '.') }}
                                    </td>
                                    <td>{{ $pkgType }}</td>
                                    <td>{{ $opName }}</td>
                                    <td><span style="color: #34d399; font-weight: 700;">Lunas</span></td>
                                    <td>
                                        <button type="button" class="btn-act sub" style="padding: 4px 8px; font-size: 11px;" onclick="viewReceiptDirect({{ $sale->id }})">
                                            <span>🧾</span> Lihat Struk
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 30px; color: var(--text-sub);">
                                        Belum ada data transaksi di Database Report.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div class="report-summary-footer">
                    <div class="summary-boxes">
                        <div class="summary-box-item">
                            <span>TOTAL PENDAPATAN</span>
                            <div class="big-amount" id="repTotalIncomeDisplay">
                                Rp {{ number_format($sales->sum('net_amount'), 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="summary-box-item">
                            <span>JUMLAH NOTA</span>
                            <div class="count-val" id="repTotalNotaDisplay">
                                {{ $sales->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TAB 3: POS & F&B MEJA -->
        <section id="tab-fnb" class="tab-pane">
            <div class="station-toolbar">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #fff;">Pemesanan Menu F&B ke Meja / PC</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">Pesan kopi, mie instan, snack, minuman dingin langsung ke station</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px;">
                @foreach($variants as $variant)
                <div class="metric-card" style="flex-direction: column; align-items: flex-start; gap: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span class="status-chip" style="font-size: 10px; padding: 2px 6px;">
                            {{ $variant->product->category->name ?? 'F&B' }}
                        </span>
                        <span style="font-family: var(--font-mono); font-size: 10px; color: var(--text-sub);">
                            SKU: {{ $variant->sku }}
                        </span>
                    </div>

                    <div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 2px;">
                            {{ $variant->name }}
                        </h4>
                        <div style="font-family: var(--font-num); font-size: 16px; font-weight: 800; color: #34d399;">
                            Rp {{ number_format($variant->price, 0, ',', '.') }}
                        </div>
                    </div>

                    <button type="button" class="btn-act fnb" style="width: 100%;" onclick="openOrderFnbModalWithItem('{{ $variant->id }}', '{{ addslashes($variant->name) }}', {{ $variant->price }})">
                        <span>🛒</span> Pesan ke Meja PC
                    </button>
                </div>
                @endforeach
            </div>
        </section>

        <!-- TAB 4: CLIENT CONNECTION -->
        <section id="tab-client" class="tab-pane">
            <div class="station-toolbar">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #fff;">Status Jaringan & Koneksi Client</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">Monitoring status socket, IP Address, dan remote lock/unlock station</p>
                </div>
            </div>

            <div class="cyber-table-card">
                <div class="cyber-table-responsive">
                    <table class="cyber-table">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>IP Address</th>
                                <th>Status Client</th>
                                <th>Latency Ping</th>
                                <th>Aksi Remote</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pcStations as $pc)
                            @php
                                $ipSuffix = (int)preg_replace('/[^0-9]/', '', $pc->station_code) ?: ($pc->id + 10);
                                $ip = "192.168.1." . (100 + $ipSuffix);
                            @endphp
                            <tr>
                                <td><strong>{{ $pc->station_code }}</strong></td>
                                <td style="font-family: var(--font-mono);">{{ $ip }}</td>
                                <td>
                                    @if($pc->status === 'occupied')
                                        <span style="color: var(--cyan); font-weight: 700;">🟢 UNLOCKED (MAIN)</span>
                                    @else
                                        <span style="color: var(--rose); font-weight: 700;">🔒 LOCKED (STANDBY)</span>
                                    @endif
                                </td>
                                <td style="font-family: var(--font-mono); color: var(--emerald);">{{ rand(1, 4) }} ms</td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn-act sub" style="padding: 4px 8px;" onclick="remoteAction('{{ $pc->station_code }}', 'Unlock Client')">Unlock</button>
                                        <button type="button" class="btn-act sub" style="padding: 4px 8px;" onclick="remoteAction('{{ $pc->station_code }}', 'Lock Client')">Lock</button>
                                        <button type="button" class="btn-act sub" style="padding: 4px 8px;" onclick="remoteAction('{{ $pc->station_code }}', 'Restart Client')">Restart</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- TAB 5: KIOS PRESENSI -->
        <section id="tab-kios" class="tab-pane">
            <div class="report-section" style="max-width: 580px; margin: 0 auto;">
                <div class="report-title-box" style="margin-bottom: 16px;">
                    <div class="report-title-icon" style="background: linear-gradient(135deg, #059669, #10b981);">⏱️</div>
                    <div class="report-title-text">
                        <h2>Kios Presensi Operator Warnet</h2>
                        <p>Clock-In & Clock-Out Operator Kasir Terintegrasi Maya Graha Kencana</p>
                    </div>
                </div>

                <form id="attendanceClockForm" onsubmit="handleAttendanceSubmit(event)">
                    <div class="form-grid">
                        <div class="form-group-full">
                            <label class="form-label">Operator Kasir</label>
                            <select id="attEmployeeSelect" class="cyber-select" style="width: 100%;" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Shift</label>
                            <select id="attShiftSelect" class="cyber-select" style="width: 100%;">
                                @foreach($shifts as $shf)
                                    <option value="{{ $shf->id }}">{{ $shf->name }} ({{ $shf->start_time }} - {{ $shf->end_time }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Catatan</label>
                            <input type="text" id="attNotesInput" class="cyber-input" style="width: 100%;" placeholder="Catatan serah terima kasir...">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 18px;">
                        <button type="submit" name="att_type" value="in" class="btn-act masuk" style="padding: 12px;">
                            <span>☀️</span> Clock-In (Masuk)
                        </button>
                        <button type="submit" name="att_type" value="out" class="btn-act selesai" style="padding: 12px;">
                            <span>🌙</span> Clock-Out (Pulang)
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>

    <!-- ============================================================== -->
    <!-- MODAL 1: MASUK BILLING (Start Session)                        -->
    <!-- ============================================================== -->
    <div id="modalMasukBilling" class="cyber-modal-overlay">
        <div class="cyber-modal">
            <div class="modal-header">
                <h3><span>⚡</span> MASUK BILLING: <span id="masukPcTitle" style="color: var(--cyan);">PC-01</span></h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalMasukBilling')">×</button>
            </div>
            <form id="formMasukBilling" onsubmit="handleMasukBillingSubmit(event)">
                <input type="hidden" id="masukStationId" value="">
                
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group-full">
                            <label class="form-label">Pilih PC Station</label>
                            <select id="masukStationSelect" class="cyber-select" style="width: 100%;" onchange="updateMasukPcRate()" required>
                                @foreach($pcStations as $st)
                                    <option value="{{ $st->id }}" data-rate="{{ $st->hourly_rate }}" data-code="{{ $st->station_code }}">
                                        {{ $st->station_code }} ({{ $st->status === 'available' ? 'Ready' : $st->status }}) — Rp {{ number_format($st->hourly_rate, 0, ',', '.') }}/jam
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Nama Pelanggan / User</label>
                            <input type="text" id="masukUserName" class="cyber-input" style="width: 100%; font-size: 14px; font-weight: 700;" value="Tamu Walk-in" required placeholder="Masukkan nama pelanggan...">
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Pilih Paket Billing</label>
                            <div class="radio-pills">
                                <label class="radio-pill-item">
                                    <input type="radio" name="masukPackageRadio" value="60" checked onchange="setMasukDuration(60)"> Personal (1 Jam)
                                </label>
                                <label class="radio-pill-item">
                                    <input type="radio" name="masukPackageRadio" value="120" onchange="setMasukDuration(120)"> Pk-2 (2 Jam)
                                </label>
                                <label class="radio-pill-item">
                                    <input type="radio" name="masukPackageRadio" value="180" onchange="setMasukDuration(180)"> Pk-3 (3 Jam)
                                </label>
                                <label class="radio-pill-item">
                                    <input type="radio" name="masukPackageRadio" value="300" onchange="setMasukDuration(300)"> Pk-5 (5 Jam)
                                </label>
                                <label class="radio-pill-item">
                                    <input type="radio" name="masukPackageRadio" value="420" onchange="setMasukDuration(420)"> Pk-7 Malam (7 Jam)
                                </label>
                            </div>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Durasi Sewa (Menit)</label>
                            <input type="number" id="masukDurationInput" class="cyber-input" value="60" min="15" step="15" style="width: 100%; font-family: var(--font-mono); font-size: 15px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-act sub" onclick="closeModal('modalMasukBilling')">Batal</button>
                    <button type="submit" class="btn-act masuk" style="padding: 10px 24px;">
                        <span>🟢</span> MASUK / AKTIFKAN PC
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: SELESAI BILLING (Stop & Checkout)                     -->
    <!-- ============================================================== -->
    <div id="modalSelesaiBilling" class="cyber-modal-overlay">
        <div class="cyber-modal">
            <div class="modal-header">
                <h3><span>⏹️</span> SELESAI BILLING: <span id="selesaiPcTitle" style="color: var(--rose);">PC-01</span></h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalSelesaiBilling')">×</button>
            </div>
            <form id="formSelesaiBilling" onsubmit="handleSelesaiBillingSubmit(event)">
                <input type="hidden" id="selesaiStationId" value="">

                <div class="modal-body">
                    <!-- Billing Summary Box -->
                    <div style="background: rgba(0, 0, 0, 0.35); border-radius: 12px; border: 1px solid var(--border-glass); padding: 14px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--text-muted);">Pelanggan:</span>
                            <strong style="color: #fff; font-size: 14px;" id="selesaiCustomerName">Budi</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--text-muted);">Durasi Main:</span>
                            <span style="font-family: var(--font-mono); color: var(--cyan); font-weight: 700;" id="selesaiDurasiText">01:00:00 (60 Menit)</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--text-muted);">Biaya Billing Internet:</span>
                            <span style="font-family: var(--font-mono); color: #fff;" id="selesaiBiayaInternetText">Rp 6.000</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: var(--text-muted);">Biaya Pesanan F&B:</span>
                            <span style="font-family: var(--font-mono); color: #fbbf24;" id="selesaiBiayaFnbText">Rp 0</span>
                        </div>
                        <div style="border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 700; color: #fff; text-transform: uppercase;">Total Tagihan:</span>
                            <span style="font-family: var(--font-num); font-size: 22px; font-weight: 800; color: #34d399;" id="selesaiTotalTagihanText">Rp 6.000</span>
                        </div>
                    </div>

                    <!-- Payment Inputs -->
                    <div class="form-grid">
                        <div class="form-group-full">
                            <label class="form-label">Metode Pembayaran</label>
                            <select id="selesaiPaymentMethod" class="cyber-select" style="width: 100%;">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Uang Diterima (Besar Uang)</label>
                            <input type="number" id="selesaiAmountPaidInput" class="cyber-input" style="width: 100%; font-family: var(--font-mono); font-size: 16px; font-weight: 700;" value="10000" oninput="calculateSelesaiKembalian()">
                            
                            <div style="display: flex; gap: 6px; margin-top: 8px;">
                                <button type="button" class="quick-money-btn" onclick="setSelesaiExactMoney()">Uang Pas</button>
                                <button type="button" class="quick-money-btn" onclick="addSelesaiMoney(10000)">+10k</button>
                                <button type="button" class="quick-money-btn" onclick="addSelesaiMoney(20000)">+20k</button>
                                <button type="button" class="quick-money-btn" onclick="addSelesaiMoney(50000)">+50k</button>
                                <button type="button" class="quick-money-btn" onclick="addSelesaiMoney(100000)">+100k</button>
                            </div>
                        </div>

                        <div class="form-group-full">
                            <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 12px; font-weight: 700; color: #fbbf24;">SISA KEMBALIAN:</span>
                                <span style="font-family: var(--font-mono); font-size: 18px; font-weight: 800; color: #fbbf24;" id="selesaiKembalianText">Rp 4.000</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-act sub" onclick="closeModal('modalSelesaiBilling')">Batal</button>
                    <button type="submit" class="btn-act selesai" style="padding: 10px 24px;">
                        <span>✅</span> SELESAI & KOSONGKAN PC
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: PESAN F&B KE PC                                      -->
    <!-- ============================================================== -->
    <div id="modalOrderFnb" class="cyber-modal-overlay">
        <div class="cyber-modal">
            <div class="modal-header">
                <h3><span>🍔</span> Pesan Makanan / Minuman ke PC</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalOrderFnb')">×</button>
            </div>
            <form id="formOrderFnb" onsubmit="handleOrderFnbSubmit(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group-full">
                            <label class="form-label">Tujuan PC Station</label>
                            <select id="fnbTargetStation" class="cyber-select" style="width: 100%;">
                                @foreach($pcStations as $st)
                                    <option value="{{ $st->id }}">
                                        {{ $st->station_code }} ({{ $st->current_user_name ?: 'Ready' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Pilih Item Menu</label>
                            <select id="fnbVariantSelect" class="cyber-select" style="width: 100%;">
                                @foreach($variants as $vr)
                                    <option value="{{ $vr->id }}" data-price="{{ $vr->price }}" data-name="{{ $vr->name }}">
                                        {{ $vr->name }} — Rp {{ number_format($vr->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-full">
                            <label class="form-label">Jumlah (Qty)</label>
                            <input type="number" id="fnbQtyInput" class="cyber-input" value="1" min="1" max="50" style="width: 100%;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-act sub" onclick="closeModal('modalOrderFnb')">Batal</button>
                    <button type="submit" class="btn-act fnb" style="padding: 10px 20px;">
                        <span>🛒</span> Tambah Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: LIHAT STRUK NOTA (View Only - No auto print)          -->
    <!-- ============================================================== -->
    <div id="modalReceiptView" class="cyber-modal-overlay">
        <div class="cyber-modal">
            <div class="modal-header">
                <h3><span>🧾</span> Rincian Struk Nota</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalReceiptView')">×</button>
            </div>
            <div class="modal-body" style="background: #232d3f; text-align: center;">
                <div class="thermal-receipt" id="printableReceipt">
                    <div class="center">
                        <strong style="font-size: 15px;">MGK CYBER LOUNGE</strong><br>
                        Maya Graha Kencana Warnet<br>
                        Jl. Malioboro No. 88, Yogyakarta
                    </div>
                    <div class="double-line"></div>
                    <div class="flex-row"><span>No. Nota:</span><strong id="recInvoice">-</strong></div>
                    <div class="flex-row"><span>Tanggal:</span><span id="recDate">{{ date('d/m/Y H:i') }}</span></div>
                    <div class="flex-row"><span>Operator:</span><span id="recOperator">Fitri</span></div>
                    <div class="flex-row"><span>Station:</span><strong id="recStation">PC-01</strong></div>
                    <div class="flex-row"><span>Pelanggan:</span><span id="recUser">Tamu</span></div>
                    <div class="line"></div>
                    <div class="flex-row" style="font-size: 13px; font-weight: bold;">
                        <span>TOTAL BIAYA:</span>
                        <span id="recTotal">Rp 0</span>
                    </div>
                    <div class="double-line"></div>
                    <div class="center" style="font-size: 11px;">
                        Terima Kasih Atas Kunjungan Anda!
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-act sub" onclick="closeModal('modalReceiptView')">Tutup</button>
                <button type="button" class="tool-btn emerald" onclick="printReceiptDirect()">
                    <span>🖨️</span> Cetak Thermal
                </button>
            </div>
        </div>
    </div>

    <!-- TOAST CONTAINER -->
    <div id="toastContainer" class="toast-container"></div>

    <script>
        // CONFIG & ROUTES
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Exact routes with baseUrl
        const API_ROUTES = {
            pcToggle: "{{ route('warnet.pc-toggle') }}",
            checkout: "{{ route('warnet.checkout') }}",
            clock: "{{ route('warnet.clock') }}"
        };

        let isAudioActive = true;
        let audioCtx = null;
        let activeFnbExtra = {}; // { stationId: extraAmount }

        // AUDIO
        function getAudioContext() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            return audioCtx;
        }

        function playBeep(freq = 880, type = 'sine', duration = 0.12) {
            if (!isAudioActive) return;
            try {
                const ctx = getAudioContext();
                if (ctx.state === 'suspended') ctx.resume();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + duration);
            } catch (e) {
                console.warn(e);
            }
        }

        function playSuccessChime() {
            if (!isAudioActive) return;
            playBeep(523.25, 'triangle', 0.08);
            setTimeout(() => playBeep(659.25, 'triangle', 0.08), 80);
            setTimeout(() => playBeep(783.99, 'triangle', 0.15), 160);
        }

        function toggleAudioFeedback() {
            isAudioActive = !isAudioActive;
            document.getElementById('soundLabel').innerText = isAudioActive ? 'Sound : ON' : 'Sound : OFF';
            document.getElementById('soundIcon').innerText = isAudioActive ? '🔊' : '🔇';
            showToast(isAudioActive ? 'Audio feedback diaktifkan' : 'Audio feedback dinonaktifkan', 'info');
        }

        // TABS
        function switchMainTab(tabId, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

            if (btn) btn.classList.add('active');
            const target = document.getElementById(tabId);
            if (target) target.classList.add('active');
            playBeep(600, 'sine', 0.05);
        }

        // FILTER STATIONS
        function filterStations(status, btn) {
            document.querySelectorAll('.station-filters .filter-pill').forEach(p => p.classList.remove('active'));
            if (btn) btn.classList.add('active');

            document.querySelectorAll('.pc-card').forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // REALTIME TIMER
        setInterval(() => {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
            const clockEl = document.getElementById('headerLiveClock');
            if (clockEl) clockEl.innerText = timeStr;

            const currentTs = Math.floor(now.getTime() / 1000);
            document.querySelectorAll('.pc-card.occupied').forEach(card => {
                const id = card.dataset.id;
                const started = parseInt(card.dataset.started) || (currentTs - 1800);
                const rate = parseFloat(card.dataset.rate) || 6000;
                const elapsedSec = Math.max(0, currentTs - started);
                
                const hrs = Math.floor(elapsedSec / 3600);
                const mins = Math.floor((elapsedSec % 3600) / 60);
                const secs = elapsedSec % 60;
                const durStr = `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

                const timerEl = document.getElementById(`timer-${id}`);
                if (timerEl) timerEl.innerText = durStr;

                const elapsedMins = Math.max(1, Math.ceil(elapsedSec / 60));
                const currentCost = Math.round((rate / 60) * elapsedMins);
                const costEl = document.getElementById(`cost-${id}`);
                if (costEl) costEl.innerText = 'Rp ' + currentCost.toLocaleString('id-ID');
            });
        }, 1000);

        // ==============================================================
        // 1. MASUK BILLING (Start Session)
        // ==============================================================
        function openStartModalDirect(stationId = null) {
            if (stationId) {
                document.getElementById('masukStationSelect').value = stationId;
            }
            updateMasukPcRate();
            openModal('modalMasukBilling');
            playBeep(700, 'sine', 0.08);
        }

        function updateMasukPcRate() {
            const sel = document.getElementById('masukStationSelect');
            const opt = sel.options[sel.selectedIndex];
            const code = opt ? opt.dataset.code : 'PC';
            document.getElementById('masukPcTitle').innerText = code;
        }

        function setMasukDuration(mins) {
            document.getElementById('masukDurationInput').value = mins;
        }

        async function handleMasukBillingSubmit(e) {
            e.preventDefault();
            const stationId = document.getElementById('masukStationSelect').value;
            const userName = document.getElementById('masukUserName').value || 'Tamu Walk-in';
            const duration = document.getElementById('masukDurationInput').value || 60;

            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Memproses...';

            try {
                const res = await fetch(API_ROUTES.pcToggle, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        station_id: stationId,
                        action: 'start',
                        user_name: userName,
                        duration_minutes: duration
                    })
                });

                const data = await res.json();
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>🟢</span> MASUK / AKTIFKAN PC';

                if (res.ok) {
                    playSuccessChime();
                    closeModal('modalMasukBilling');
                    showToast(`✅ ${data.station?.station_code || 'PC'} berhasil diaktifkan untuk "${userName}"!`, 'success');

                    // Update DOM instantly without page reload
                    const card = document.getElementById(`pc-card-${stationId}`);
                    if (card) {
                        card.className = 'pc-card occupied';
                        card.dataset.status = 'occupied';
                        card.dataset.user = userName;
                        card.dataset.started = Math.floor(new Date().getTime() / 1000);
                        
                        document.getElementById(`badge-status-${stationId}`).className = 'pc-status-badge occupied';
                        document.getElementById(`badge-status-${stationId}`).innerHTML = '<span class="status-dot active"></span> <span>SEDANG MAIN</span>';
                        document.getElementById(`user-display-${stationId}`).innerText = userName;
                        document.getElementById(`timer-${stationId}`).innerText = '00:00:01';
                        document.getElementById(`cost-${stationId}`).innerText = 'Rp 0';

                        // Change button to KLIK SELESAI
                        document.getElementById(`actions-${stationId}`).innerHTML = `
                            <button type="button" class="btn-act selesai" onclick="openSelesaiModal(${stationId})">
                                <span>⏹️</span> KLIK SELESAI
                            </button>
                            <button type="button" class="btn-act fnb" onclick="openOrderFnbModal(${stationId})">
                                <span>🍔</span> Pesan F&B
                            </button>
                        `;
                    }
                    updateKpiCounts();
                } else {
                    showToast(data.message || 'Gagal memulai sesi billing.', 'error');
                }
            } catch (err) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>🟢</span> MASUK / AKTIFKAN PC';
                console.error(err);
                showToast('Terjadi kesalahan jaringan.', 'error');
            }
        }

        // ==============================================================
        // 2. KLIK SELESAI (Stop & Checkout) - NO AUTO PRINT
        // ==============================================================
        function openSelesaiModal(stationId) {
            const card = document.getElementById(`pc-card-${stationId}`);
            if (!card) return;

            const code = card.dataset.code;
            const user = card.dataset.user || 'Tamu Walk-in';
            const costStr = document.getElementById(`cost-${stationId}`)?.innerText || 'Rp 0';
            const costNum = parseInt(costStr.replace(/[^0-9]/g, '')) || 3000;
            const durStr = document.getElementById(`timer-${stationId}`)?.innerText || '00:00:00';
            const extraFnb = activeFnbExtra[stationId] || 0;
            const total = costNum + extraFnb;

            document.getElementById('selesaiStationId').value = stationId;
            document.getElementById('selesaiPcTitle').innerText = code;
            document.getElementById('selesaiCustomerName').innerText = user;
            document.getElementById('selesaiDurasiText').innerText = durStr;
            document.getElementById('selesaiBiayaInternetText').innerText = 'Rp ' + costNum.toLocaleString('id-ID');
            document.getElementById('selesaiBiayaFnbText').innerText = 'Rp ' + extraFnb.toLocaleString('id-ID');
            document.getElementById('selesaiTotalTagihanText').innerText = 'Rp ' + total.toLocaleString('id-ID');
            
            // Set initial money paid
            document.getElementById('selesaiAmountPaidInput').value = total;
            calculateSelesaiKembalian();

            openModal('modalSelesaiBilling');
            playBeep(650, 'sine', 0.1);
        }

        function calculateSelesaiKembalian() {
            const totalStr = document.getElementById('selesaiTotalTagihanText').innerText;
            const total = parseInt(totalStr.replace(/[^0-9]/g, '')) || 0;
            const paid = parseInt(document.getElementById('selesaiAmountPaidInput').value) || 0;
            const change = Math.max(0, paid - total);

            document.getElementById('selesaiKembalianText').innerText = 'Rp ' + change.toLocaleString('id-ID');
        }

        function setSelesaiExactMoney() {
            const totalStr = document.getElementById('selesaiTotalTagihanText').innerText;
            const total = parseInt(totalStr.replace(/[^0-9]/g, '')) || 0;
            document.getElementById('selesaiAmountPaidInput').value = total;
            calculateSelesaiKembalian();
        }

        function addSelesaiMoney(amount) {
            const cur = parseInt(document.getElementById('selesaiAmountPaidInput').value) || 0;
            document.getElementById('selesaiAmountPaidInput').value = cur + amount;
            calculateSelesaiKembalian();
        }

        async function handleSelesaiBillingSubmit(e) {
            e.preventDefault();
            const stationId = document.getElementById('selesaiStationId').value;
            const card = document.getElementById(`pc-card-${stationId}`);
            const code = card?.dataset.code || 'PC';
            const user = document.getElementById('selesaiCustomerName').innerText || 'Pelanggan';
            
            const totalStr = document.getElementById('selesaiTotalTagihanText').innerText;
            const totalBilling = parseInt(document.getElementById('selesaiBiayaInternetText').innerText.replace(/[^0-9]/g, '')) || 0;
            const totalFnb = parseInt(document.getElementById('selesaiBiayaFnbText').innerText.replace(/[^0-9]/g, '')) || 0;
            const amountPaid = parseInt(document.getElementById('selesaiAmountPaidInput').value) || (totalBilling + totalFnb);
            const method = document.getElementById('selesaiPaymentMethod').value;

            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Menyelesaikan...';

            const payload = {
                employee_id: {{ $employees->first()->id ?? 1 }},
                customer_name: user,
                pc_station_id: stationId,
                payment_method: method,
                amount_paid: amountPaid,
                finish_pc_session: true,
                items: [
                    {
                        variant_id: null,
                        item_type: 'billing',
                        name: 'Sewa Billing Internet ' + code,
                        qty: 1,
                        price: totalBilling
                    }
                ]
            };

            if (totalFnb > 0) {
                payload.items.push({
                    variant_id: null,
                    item_type: 'fnb',
                    name: 'Pesanan F&B Meja',
                    qty: 1,
                    price: totalFnb
                });
            }

            try {
                const res = await fetch(API_ROUTES.checkout, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>✅</span> SELESAI & KOSONGKAN PC';

                if (res.ok) {
                    playSuccessChime();
                    closeModal('modalSelesaiBilling');
                    
                    // Reset extra F&B
                    delete activeFnbExtra[stationId];

                    showToast(`✅ Sesi ${code} selesai & kasir disimpan. Total: Rp ${amountPaid.toLocaleString('id-ID')}. PC kembali ready!`, 'success');

                    // Reset PC Card to available instantly in DOM
                    if (card) {
                        card.className = 'pc-card available';
                        card.dataset.status = 'available';
                        card.dataset.user = '';
                        card.dataset.started = '0';
                        
                        document.getElementById(`badge-status-${stationId}`).className = 'pc-status-badge available';
                        document.getElementById(`badge-status-${stationId}`).innerHTML = '<span class="status-dot"></span> <span>READY KOSONG</span>';
                        document.getElementById(`user-display-${stationId}`).innerText = 'Tamu / Walk-in';
                        document.getElementById(`timer-${stationId}`).innerText = '--:--:--';
                        document.getElementById(`cost-${stationId}`).innerText = 'Rp 0';

                        // Change button back to MASUK
                        document.getElementById(`actions-${stationId}`).innerHTML = `
                            <button type="button" class="btn-act masuk" onclick="openStartModalDirect(${stationId})">
                                <span>🟢</span> MASUK / MULAI MAIN
                            </button>
                        `;
                    }
                    updateKpiCounts();

                    // NO AUTOMATIC PRINT DIALOG TRIGGERED!
                } else {
                    showToast(data.message || 'Gagal menyelesaikan sesi.', 'error');
                }
            } catch (err) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>✅</span> SELESAI & KOSONGKAN PC';
                console.error(err);
                showToast('Terjadi kesalahan jaringan.', 'error');
            }
        }

        // SET STATION READY (Maintenance finish)
        async function setStationReady(stationId) {
            try {
                const res = await fetch(API_ROUTES.pcToggle, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        station_id: stationId,
                        action: 'stop'
                    })
                });

                if (res.ok) {
                    playSuccessChime();
                    location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        }

        // UPDATE KPI COUNTS LIVE
        function updateKpiCounts() {
            const occ = document.querySelectorAll('.pc-card.occupied').length;
            const rdy = document.querySelectorAll('.pc-card.available').length;
            const elOcc = document.getElementById('kpiOccupiedPcs');
            const elRdy = document.getElementById('kpiAvailablePcs');
            if (elOcc) elOcc.innerText = occ + ' PC';
            if (elRdy) elRdy.innerText = rdy + ' PC';
        }

        // F&B ORDER MODAL
        function openOrderFnbModal(stationId = null) {
            if (stationId) document.getElementById('fnbTargetStation').value = stationId;
            openModal('modalOrderFnb');
        }

        function openOrderFnbModalWithItem(variantId, name, price) {
            document.getElementById('fnbVariantSelect').value = variantId;
            openModal('modalOrderFnb');
        }

        function handleOrderFnbSubmit(e) {
            e.preventDefault();
            const stationId = document.getElementById('fnbTargetStation').value;
            const sel = document.getElementById('fnbVariantSelect');
            const opt = sel.options[sel.selectedIndex];
            const name = opt ? opt.dataset.name : 'F&B';
            const price = parseInt(opt ? opt.dataset.price : 0) || 0;
            const qty = parseInt(document.getElementById('fnbQtyInput').value) || 1;
            const subtotal = price * qty;

            activeFnbExtra[stationId] = (activeFnbExtra[stationId] || 0) + subtotal;
            closeModal('modalOrderFnb');
            playSuccessChime();
            showToast(`✅ ${qty}x ${name} (Rp ${subtotal.toLocaleString('id-ID')}) ditambahkan ke tagihan PC!`, 'success');
        }

        // VIEW RECEIPT PREVIEW (View Only - No auto print)
        function viewReceiptDirect(saleId) {
            const row = document.querySelector(`.report-row[data-id="${saleId}"]`);
            if (row) {
                document.getElementById('recInvoice').innerText = row.dataset.invoice;
                document.getElementById('recStation').innerText = row.dataset.station;
                document.getElementById('recUser').innerText = row.dataset.user;
                document.getElementById('recOperator').innerText = row.dataset.operator;
                const biaya = parseInt(row.dataset.biaya) || 0;
                document.getElementById('recTotal').innerText = 'Rp ' + biaya.toLocaleString('id-ID');
            }
            openModal('modalReceiptView');
        }

        function printReceiptDirect() {
            const printContent = document.getElementById('printableReceipt').outerHTML;
            const win = window.open('', '_blank', 'width=380,height=550');
            win.document.write(`
                <html>
                <head>
                    <title>Print Struk</title>
                    <style>
                        body { font-family: monospace; font-size: 12px; margin: 0; padding: 10px; }
                        .center { text-align: center; }
                        .line { border-bottom: 1px dashed #000; margin: 6px 0; }
                        .double-line { border-bottom: 2px dashed #000; margin: 6px 0; }
                        .flex-row { display: flex; justify-content: space-between; }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    ${printContent}
                </body>
                </html>
            `);
            win.document.close();
        }

        function printReportDocument() {
            window.print();
        }

        function exportReportToCSV() {
            let csv = 'Nomor Nota,Tanggal,Komputer,User Name,Biaya,Operator\n';
            document.querySelectorAll('.report-row').forEach(row => {
                if (row.style.display !== 'none') {
                    csv += `"${row.dataset.invoice}","${row.dataset.date}","${row.dataset.station}","${row.dataset.user}","${row.dataset.biaya}","${row.dataset.operator}"\n`;
                }
            });

            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', `rekap_billing_${new Date().toISOString().split('T')[0]}.csv`);
            a.click();
            showToast('Laporan diekspor ke CSV!', 'success');
        }

        function applyReportFilters() {
            const op = document.getElementById('reportFilterOperator').value;
            const dateVal = document.getElementById('reportFilterDate').value;
            const searchVal = document.getElementById('reportSearchInput').value.toLowerCase();
            const period = document.querySelector('input[name="reportPeriodRadio"]:checked')?.value || 'day';

            let count = 0;
            let total = 0;

            document.querySelectorAll('.report-row').forEach(row => {
                const rowOp = row.dataset.operator;
                const rowDate = row.dataset.date;
                const rowInv = row.dataset.invoice.toLowerCase();
                const rowUser = row.dataset.user.toLowerCase();
                const rowBiaya = parseFloat(row.dataset.biaya) || 0;

                let matchOp = (op === 'ALL' || rowOp === op);
                let matchSearch = (!searchVal || rowInv.includes(searchVal) || rowUser.includes(searchVal));
                let matchDate = true;
                if (period === 'day' && dateVal) {
                    matchDate = (rowDate === dateVal);
                } else if (period === 'month' && dateVal) {
                    matchDate = rowDate.startsWith(dateVal.substring(0, 7));
                }

                if (matchOp && matchSearch && matchDate) {
                    row.style.display = '';
                    count++;
                    total += rowBiaya;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('repTotalIncomeDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('repTotalNotaDisplay').innerText = count;
        }

        // REMOTE CONTROL SIMULATOR
        function remoteAction(pcCode, act) {
            playBeep(800, 'sine', 0.1);
            showToast(`Perintah [${act}] berhasil dikirim ke ${pcCode}`, 'success');
        }

        // KIOS PRESENSI
        async function handleAttendanceSubmit(e) {
            e.preventDefault();
            const submitter = e.submitter;
            const attType = submitter ? submitter.value : 'in';

            const payload = {
                employee_id: document.getElementById('attEmployeeSelect').value,
                type: attType,
                shift_id: document.getElementById('attShiftSelect').value,
                notes: document.getElementById('attNotesInput').value
            };

            try {
                const res = await fetch(API_ROUTES.clock, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok) {
                    playSuccessChime();
                    showToast(data.message || 'Presensi kasir berhasil dicatat!', 'success');
                } else {
                    showToast(data.message || 'Gagal presensi kasir.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Terjadi kesalahan jaringan.', 'error');
            }
        }

        // MODAL HELPERS
        function openModal(id) {
            const m = document.getElementById(id);
            if (m) m.classList.add('active');
        }

        function closeModal(id) {
            const m = document.getElementById(id);
            if (m) m.classList.remove('active');
        }

        // TOAST HELPER
        function showToast(msg, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-msg ${type}`;
            toast.innerHTML = `<span>${type === 'success' ? '✅' : (type === 'error' ? '❌' : 'ℹ️')}</span> <span>${msg}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.cyber-modal-overlay.active').forEach(m => m.classList.remove('active'));
            }
        });
    </script>
</body>
</html>
