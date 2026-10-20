<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MasterData Hub — Everest ERP 2.0 Lean Enterprise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-subtle: #f1f5f9;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --emerald: #10b981;
            --emerald-light: #ecfdf5;
            --amber: #f59e0b;
            --amber-light: #fffbeb;
            --rose: #ef4444;
            --rose-light: #fef2f2;
            --indigo: #6366f1;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.07), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.08), 0 4px 6px -4px rgb(0 0 0 / 0.04);
            --radius-md: 10px;
            --radius-lg: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Header */
        .topbar {
            background: #0f172a;
            color: #ffffff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #1e293b;
        }

        .brand-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #6366f1, #3b82f6);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.4);
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .topbar-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-db {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-coretax {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .badge-iso {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #10b981;
        }

        /* Container Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 28px;
        }

        /* Hero Banner */
        .module-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
            border-radius: var(--radius-lg);
            padding: 26px 30px;
            color: white;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .module-header::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, transparent 70%);
            border-radius: 50%;
        }

        .module-header h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .module-header p {
            color: #cbd5e1;
            font-size: 14px;
            max-width: 800px;
        }

        /* KPI Cards Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .metric-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .metric-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .metric-value {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
        }

        .metric-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Tabs Navigation */
        .tabs-nav {
            display: flex;
            gap: 6px;
            background: #e2e8f0;
            padding: 5px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .tab-btn {
            background: transparent;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.5);
        }

        .tab-btn.active {
            background: var(--bg-card);
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        /* Card Content Area */
        .content-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .section-subtitle {
            font-size: 13px;
            color: var(--text-muted);
        }

        .search-box {
            padding: 8px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            width: 260px;
            outline: none;
        }

        .search-box:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        thead th {
            background: var(--bg-subtle);
            color: var(--text-secondary);
            font-weight: 700;
            padding: 12px 14px;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.04em;
        }

        tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges & Tags */
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .badge-success {
            background: var(--emerald-light);
            color: #065f46;
        }

        .badge-warning {
            background: var(--amber-light);
            color: #92400e;
        }

        .badge-danger {
            background: var(--rose-light);
            color: #991b1b;
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        .badge-muted {
            background: var(--bg-subtle);
            color: var(--text-secondary);
        }

        /* Credit limit progress bar */
        .credit-bar-container {
            width: 140px;
            height: 7px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        .credit-bar {
            height: 100%;
            border-radius: 4px;
        }

        /* Interactive Simulator Panel */
        .simulator-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 900px) {
            .simulator-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-calc {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s ease;
            width: 100%;
        }

        .btn-calc:hover {
            background: var(--primary-hover);
        }

        /* Receipt Card */
        .receipt-card {
            background: #0f172a;
            color: white;
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .receipt-header {
            border-bottom: 1px dashed #334155;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: 700;
            color: #e2e8f0;
        }

        .receipt-subtitle {
            font-size: 12px;
            color: #94a3b8;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 10px;
            color: #cbd5e1;
        }

        .receipt-row.bold {
            font-weight: 700;
            color: white;
        }

        .receipt-total {
            border-top: 1px dashed #334155;
            padding-top: 14px;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .receipt-total-label {
            font-size: 14px;
            font-weight: 700;
            color: #e2e8f0;
        }

        .receipt-total-value {
            font-size: 24px;
            font-weight: 800;
            color: #38bdf8;
            letter-spacing: -0.02em;
        }

        .receipt-credit-box {
            margin-top: 18px;
            padding: 14px;
            border-radius: 8px;
            background: #1e293b;
            border-left: 4px solid #10b981;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="topbar">
        <div class="brand-area">
            <div class="brand-logo">E</div>
            <div>
                <div class="brand-title">EVEREST ERP 2.0</div>
                <div class="brand-subtitle">Lean DDD Architecture — Modul MasterData</div>
            </div>
        </div>
        <div class="topbar-badges">
            <span class="status-badge badge-db"><span class="pulse-dot"></span> PostgreSQL 16 (192.168.5.10)</span>
            <span class="status-badge badge-coretax">DJP Coretax NIK/NPWP 16 & PPN 11% Floor</span>
            <span class="status-badge badge-iso">ISO 27001 Immutability Ready</span>
        </div>
    </header>

    <div class="container">

        <!-- Module Header Hero -->
        <div class="module-header">
            <h1>Pusat Entitas & Rujukan (MasterData Hub)</h1>
            <p>Single source of truth untuk katalog produk, varian SKU, pricelist tiering, konsumen ber-NPWP 16 digit, plafon kredit atomik, pemasok, cabang DC, dan gudang penyimpanan & in-transit.</p>
        </div>

        <!-- Metric KPI Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Katalog Produk & SKU</div>
                <div class="metric-value">{{ $metrics['total_variants'] }} <span style="font-size: 15px; font-weight: normal; color: var(--text-muted);">SKU ({{ $metrics['total_products'] }} Produk)</span></div>
                <div class="metric-hint">Moving Average HPP & Base Price</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Konsumen & Mitra B2B</div>
                <div class="metric-value">{{ $metrics['total_customers'] }} <span style="font-size: 15px; font-weight: normal; color: var(--text-muted);">Mitra</span></div>
                <div class="metric-hint">DJP Coretax 16 Digit Validated</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Plafon Kredit B2B</div>
                <div class="metric-value">Rp {{ number_format($metrics['total_credit_limit'], 0, ',', '.') }}</div>
                <div class="metric-hint">Terpakai: Rp {{ number_format($metrics['total_credit_used'], 0, ',', '.') }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Cabang & Logistik</div>
                <div class="metric-value">{{ $metrics['total_branches'] }} Cabang / {{ $metrics['total_warehouses'] }} Gudang</div>
                <div class="metric-hint">Fisik Storage & In-Transit Handshake</div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('tab-variants', this)">📦 Katalog SKU</button>
            <button class="tab-btn" onclick="switchTab('tab-pricelists', this)">🏷️ Pricelist & Tiering</button>
            <button class="tab-btn" onclick="switchTab('tab-customers', this)">👥 Konsumen & Credit Guard</button>
            <button class="tab-btn" onclick="switchTab('tab-suppliers', this)">🚚 Pemasok (Suppliers)</button>
            <button class="tab-btn" onclick="switchTab('tab-branches', this)">🏢 Cabang & Gudang</button>
            <button class="tab-btn" onclick="switchTab('tab-employees', this)">👔 Staf & Otorisator</button>
            <button class="tab-btn" onclick="switchTab('tab-simulator', this)" style="background: rgba(99, 102, 241, 0.15); color: var(--primary);">⚡ Live Engine Simulator</button>
        </div>

        <!-- TAB 1: Katalog SKU -->
        <div id="tab-variants" class="tab-panel active">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Katalog Produk & Varian Fisik (Tangible SKU)</div>
                        <div class="section-subtitle">Setiap SKU memiliki barcode unik, berat fisik untuk logistik, dan HPP bergerak (PSAK 14).</div>
                    </div>
                    <input type="text" class="search-box" id="searchVariant" placeholder="Cari SKU atau nama produk..." onkeyup="filterTable('searchVariant', 'tableVariants')">
                </div>
                <div class="table-responsive">
                    <table id="tableVariants">
                        <thead>
                            <tr>
                                <th>SKU & Barcode</th>
                                <th>Nama Varian Produk</th>
                                <th>Kategori & Brand</th>
                                <th>Satuan / Berat</th>
                                <th>Harga Dasar</th>
                                <th>HPP Rata-rata (Cost)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($variants as $v)
                            <tr>
                                <td>
                                    <strong>{{ $v->sku }}</strong><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">EAN: {{ $v->barcode ?? '-' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $v->name }}</strong><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">Parent: {{ $v->product->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-muted">{{ $v->product->category->name ?? '-' }}</span>
                                    <span class="badge badge-muted">{{ $v->product->brand->name ?? '-' }}</span>
                                </td>
                                <td>
                                    {{ $v->product->uom->name ?? '-' }}<br>
                                    <span style="font-size: 11px; color: var(--text-muted);">{{ number_format($v->weight_grams, 0, ',', '.') }} gram</span>
                                </td>
                                <td><strong>Rp {{ number_format($v->base_price, 0, ',', '.') }}</strong></td>
                                <td style="color: #64748b;">Rp {{ number_format($v->cost_price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $v->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $v->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada data varian SKU.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: Pricelists & Tiering -->
        <div id="tab-pricelists" class="tab-panel">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Matriks Harga & Diskon Tiering Kuantiti</div>
                        <div class="section-subtitle">Daftar harga otomatis terselesaikan berdasarkan tipe konsumen dan volume pembelian.</div>
                    </div>
                </div>

                @foreach($pricelists as $pl)
                <div style="background: var(--bg-subtle); border-radius: var(--radius-md); padding: 18px; margin-bottom: 20px; border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div>
                            <span class="badge badge-primary" style="margin-right: 6px;">{{ $pl->code }}</span>
                            <strong style="font-size: 15px;">{{ $pl->name }}</strong>
                            <span class="badge badge-muted" style="margin-left: 8px;">Tipe: {{ $pl->customer_type?->label() ?? 'Umum' }}</span>
                        </div>
                        <span class="badge badge-success">Mata Uang: {{ $pl->currency }}</span>
                    </div>

                    <div class="table-responsive">
                        <table style="background: white; border-radius: 8px; overflow: hidden;">
                            <thead>
                                <tr>
                                    <th>SKU Varian</th>
                                    <th>Nama Produk</th>
                                    <th>Min. Kuantiti (Tier)</th>
                                    <th>Harga Satuan Tier</th>
                                    <th>Diskon Khusus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pl->items as $item)
                                <tr>
                                    <td><code>{{ $item->variant->sku ?? '-' }}</code></td>
                                    <td>{{ $item->variant->name ?? '-' }}</td>
                                    <td><strong>&ge; {{ $item->min_qty }}</strong></td>
                                    <td style="color: var(--primary); font-weight: 700;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td>{{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada item tiering di pricelist ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 3: Konsumen & Credit Guard -->
        <div id="tab-customers" class="tab-panel">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Konsumen, Kepatuhan Coretax, & Batas Kredit</div>
                        <div class="section-subtitle">Pencegahan fraud piutang dengan validasi plafon kredit atomik dan format 16 digit DJP Coretax.</div>
                    </div>
                    <input type="text" class="search-box" id="searchCustomer" placeholder="Cari konsumen atau NPWP..." onkeyup="filterTable('searchCustomer', 'tableCustomers')">
                </div>
                <div class="table-responsive">
                    <table id="tableCustomers">
                        <thead>
                            <tr>
                                <th>Kode & Nama Mitra</th>
                                <th>Tipe Konsumen</th>
                                <th>Identitas Pajak (DJP Coretax)</th>
                                <th>TOP Tenor</th>
                                <th>Plafon Kredit</th>
                                <th>Penggunaan Plafon</th>
                                <th>Sisa Plafon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $c)
                            @php
                                $usedPct = $c->credit_limit > 0 ? min(100, round(($c->current_credit_used / $c->credit_limit) * 100)) : 0;
                                $barColor = $usedPct > 80 ? 'var(--rose)' : ($usedPct > 50 ? 'var(--amber)' : 'var(--emerald)');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $c->name }}</strong><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">{{ $c->code }} &bull; {{ $c->phone ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-muted">{{ $c->customer_type?->label() }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $c->tax_id_type?->label() }}</span><br>
                                    <code style="font-size: 12px;">{{ $c->tax_id_number ?? '(Non Wajib Pajak)' }}</code>
                                </td>
                                <td>{{ $c->payment_term_days > 0 ? $c->payment_term_days . ' Hari' : 'Tunai / 0 Hari' }}</td>
                                <td><strong>Rp {{ number_format($c->credit_limit, 0, ',', '.') }}</strong></td>
                                <td>
                                    <span>Rp {{ number_format($c->current_credit_used, 0, ',', '.') }} ({{ $usedPct }}%)</span>
                                    <div class="credit-bar-container">
                                        <div class="credit-bar" style="width: {{ $usedPct }}%; background: {{ $barColor }};"></div>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: {{ $c->remainingCreditLimit() > 0 ? 'var(--emerald)' : 'var(--rose)' }};">
                                        Rp {{ number_format($c->remainingCreditLimit(), 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada data konsumen.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 4: Pemasok (Suppliers) -->
        <div id="tab-suppliers" class="tab-panel">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Mitra Pemasok & Rekening Bank (Suppliers)</div>
                        <div class="section-subtitle">Data pemasok untuk pengadaan barang (PO) dan pembayaran tagihan hutang (AP) di Modul Kas.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode & Nama Pemasok</th>
                                <th>NPWP 16 Digit</th>
                                <th>Tenor Pembayaran (TOP)</th>
                                <th>Rekening Bank Pembayaran AP</th>
                                <th>Kontak & Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $s)
                            <tr>
                                <td>
                                    <strong>{{ $s->name }}</strong><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">{{ $s->code }}</span>
                                </td>
                                <td><code>{{ $s->tax_id_number ?? '-' }}</code></td>
                                <td><strong>{{ $s->payment_term_days }} Hari</strong></td>
                                <td>
                                    <strong>{{ $s->bank_name ?? '-' }}</strong><br>
                                    <span style="font-size: 12px; color: var(--text-secondary);">No: {{ $s->bank_account_number ?? '-' }} a/n {{ $s->bank_account_holder ?? '-' }}</span>
                                </td>
                                <td>
                                    <span>{{ $s->phone ?? '-' }} &bull; {{ $s->email ?? '-' }}</span><br>
                                    <span style="font-size: 11px; color: var(--text-muted);">{{ $s->address ?? '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada data pemasok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 5: Cabang & Gudang -->
        <div id="tab-branches" class="tab-panel">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Struktur Cabang & Gudang Logistik</div>
                        <div class="section-subtitle">Mendukung arsitektur mutasi Two-Step Handshake (Storage vs In-Transit).</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode Cabang</th>
                                <th>Nama Cabang Fisik</th>
                                <th>Alamat Cabang</th>
                                <th>Daftar Gudang Tertaut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $b)
                            <tr>
                                <td>
                                    <span class="badge {{ $b->is_headquarters ? 'badge-primary' : 'badge-muted' }}">{{ $b->code }}</span>
                                    @if($b->is_headquarters) <span style="font-size: 11px; color: var(--primary); font-weight: 700;">(Pusat / DC)</span> @endif
                                </td>
                                <td><strong>{{ $b->name }}</strong><br><span style="font-size: 11px; color: var(--text-muted);">Telp: {{ $b->phone ?? '-' }}</span></td>
                                <td>{{ $b->address ?? '-' }}</td>
                                <td>
                                    @foreach($b->warehouses as $wh)
                                    <div style="margin-bottom: 6px;">
                                        <span class="badge {{ $wh->type === 'in_transit' ? 'badge-warning' : 'badge-success' }}">
                                            {{ $wh->type === 'in_transit' ? 'In-Transit' : 'Fisik Gudang' }}
                                        </span>
                                        <strong>{{ $wh->name }}</strong> <code>({{ $wh->code }})</code>
                                    </div>
                                    @endforeach
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Belum ada data cabang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 6: Staf & Pegawai -->
        <div id="tab-employees" class="tab-panel">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Karyawan & Batas Otorisasi Diskon</div>
                        <div class="section-subtitle">Pengaturan peran kasir retail, salesman, driver pengiriman, dan otorisator kredit.</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Staf</th>
                                <th>Nama Pegawai</th>
                                <th>Penempatan Cabang</th>
                                <th>Peran (Role)</th>
                                <th>Batas Approval Diskon</th>
                                <th>Kontak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $e)
                            <tr>
                                <td><code>{{ $e->code }}</code></td>
                                <td><strong>{{ $e->name }}</strong></td>
                                <td>{{ $e->branch->name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $e->role->value === 'otorisator' ? 'badge-danger' : ($e->role->value === 'kasir' ? 'badge-primary' : 'badge-muted') }}">
                                        {{ $e->role->label() }}
                                    </span>
                                </td>
                                <td>
                                    @if($e->max_discount_approval_percent > 0)
                                    <strong style="color: var(--rose);">Maksimal {{ $e->max_discount_approval_percent }}%</strong>
                                    @else
                                    <span style="color: var(--text-muted);">Tanpa Wewenang</span>
                                    @endif
                                </td>
                                <td>{{ $e->phone ?? '-' }} &bull; {{ $e->email ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada data pegawai.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 7: Live Interactive Simulator -->
        <div id="tab-simulator" class="tab-panel">
            <div class="content-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">⚡ Interactive Pricing & DJP Coretax Calculator</div>
                        <div class="section-subtitle">Simulasikan perhitungan real-time PriceResolverEngine, PricingEngine (diskon bertingkat & pembulatan floor PPN 11%), serta CustomerCreditGuard.</div>
                    </div>
                </div>

                <div class="simulator-grid">
                    <!-- Form Input -->
                    <div>
                        <form id="simulatorForm" onsubmit="runSimulation(event)">
                            <div class="form-group">
                                <label class="form-label">1. Pilih Produk & Varian (SKU)</label>
                                <select class="form-control" id="simVariant" required>
                                    @foreach($variants as $v)
                                    <option value="{{ $v->id }}">
                                        {{ $v->sku }} — {{ $v->name }} (Base: Rp {{ number_format($v->base_price, 0, ',', '.') }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">2. Pilih Konsumen (Mitra)</label>
                                <select class="form-control" id="simCustomer">
                                    <option value="">-- Pembeli Umum (Tanpa Akun) --</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} ({{ $c->customer_type?->label() }}) — Plafon: Rp {{ number_format($c->credit_limit, 0, ',', '.') }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                <div class="form-group">
                                    <label class="form-label">3. Jumlah Kuantiti (Qty)</label>
                                    <input type="number" class="form-control" id="simQty" value="1" min="1" step="1" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">4. Diskon Bertingkat</label>
                                    <input type="text" class="form-control" id="simDiscount" placeholder="Contoh: 10+5% atau 5" value="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" id="simTaxable" checked>
                                    <strong>Kenakan PPN 11% (DJP Coretax Floor Rounding)</strong>
                                </label>
                            </div>

                            <button type="submit" class="btn-calc" id="btnRunSim">
                                <span>Hitung Harga & Cek Plafon Kredit</span>
                            </button>
                        </form>
                    </div>

                    <!-- Live Breakdown Receipt -->
                    <div>
                        <div class="receipt-card">
                            <div class="receipt-header">
                                <div class="receipt-title">Rincian Perhitungan Atomik</div>
                                <div class="receipt-subtitle" id="receiptSource">Sumber Harga: Menunggu simulasi...</div>
                            </div>

                            <div class="receipt-row">
                                <span>Harga Satuan Terpilih:</span>
                                <strong id="resUnitPrice">Rp 0</strong>
                            </div>
                            <div class="receipt-row">
                                <span>Total Bruto (Gross):</span>
                                <span id="resGross">Rp 0</span>
                            </div>
                            <div class="receipt-row" style="color: #f87171;">
                                <span>Potongan Diskon:</span>
                                <span id="resDiscount">- Rp 0</span>
                            </div>
                            <div class="receipt-row bold" style="border-top: 1px solid #334155; padding-top: 8px;">
                                <span>Dasar Pengenaan Pajak (DPP):</span>
                                <span id="resDpp">Rp 0</span>
                            </div>
                            <div class="receipt-row" style="color: #6ee7b7;">
                                <span>PPN 11% (Floor Coretax):</span>
                                <span id="resPpn">+ Rp 0</span>
                            </div>

                            <div class="receipt-total">
                                <div class="receipt-total-label">TOTAL NETTO:</div>
                                <div class="receipt-total-value" id="resNet">Rp 0</div>
                            </div>

                            <!-- Credit Guard Status Box -->
                            <div class="receipt-credit-box" id="creditBox" style="display: none;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <strong style="font-size: 13px;" id="creditVerdictText">Status Kredit: Lolos</strong>
                                    <span class="badge" id="creditVerdictBadge">AMAN</span>
                                </div>
                                <div style="font-size: 12px; color: #94a3b8;" id="creditDetailText">
                                    Plafon mencukupi untuk melanjutkan transaksi kredit B2B.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }

        function filterTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const filter = input.value.toLowerCase();
            const table = document.getElementById(tableId);
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const text = tr[i].textContent || tr[i].innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }
        }

        async function runSimulation(e) {
            e.preventDefault();
            const btn = document.getElementById('btnRunSim');
            btn.disabled = true;
            btn.innerText = 'Menghitung...';

            const payload = {
                variant_id: document.getElementById('simVariant').value,
                customer_id: document.getElementById('simCustomer').value || null,
                quantity: document.getElementById('simQty').value,
                discounts: document.getElementById('simDiscount').value,
                is_taxable: document.getElementById('simTaxable').checked,
            };

            try {
                const response = await fetch('{{ route("master-data.simulate-pricing") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (response.ok) {
                    const calc = data.calculation;
                    const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');

                    document.getElementById('receiptSource').innerText = 'Resolusi: ' + data.resolved_source;
                    document.getElementById('resUnitPrice').innerText = formatRupiah(data.resolved_unit_price);
                    document.getElementById('resGross').innerText = formatRupiah(calc.gross_amount);
                    document.getElementById('resDiscount').innerText = '- ' + formatRupiah(calc.discount_amount);
                    document.getElementById('resDpp').innerText = formatRupiah(calc.dpp);
                    document.getElementById('resPpn').innerText = '+ ' + formatRupiah(calc.ppn_amount);
                    document.getElementById('resNet').innerText = formatRupiah(calc.net_amount);

                    const creditBox = document.getElementById('creditBox');
                    if (data.credit_status) {
                        creditBox.style.display = 'block';
                        const cs = data.credit_status;
                        const verdictText = document.getElementById('creditVerdictText');
                        const verdictBadge = document.getElementById('creditVerdictBadge');
                        const detailText = document.getElementById('creditDetailText');

                        if (cs.can_proceed) {
                            creditBox.style.borderLeftColor = '#10b981';
                            verdictText.innerText = 'Status Plafon Kredit: DISETUJUI';
                            verdictBadge.className = 'badge badge-success';
                            verdictBadge.innerText = 'LOLOS';
                            detailText.innerText = `Sisa Plafon: ${formatRupiah(cs.remaining)} | Total sesudah order: ${formatRupiah(cs.after_transaction)} dari plafon ${formatRupiah(cs.credit_limit)}`;
                        } else {
                            creditBox.style.borderLeftColor = '#ef4444';
                            verdictText.innerText = 'Status Plafon Kredit: TERKUNCI (DITOLAK)';
                            verdictBadge.className = 'badge badge-danger';
                            verdictBadge.innerText = 'OVERLIMIT';
                            detailText.innerText = `Order melebihi batas plafon! Dibutuhkan Rp ${formatRupiah(cs.after_transaction)}, sedangkan plafon tersedia hanya ${formatRupiah(cs.credit_limit)}. Transaksi wajib persetujuan otorisator atau dibayar tunai.`;
                        }
                    } else {
                        creditBox.style.display = 'none';
                    }
                } else {
                    alert('Gagal menghitung: ' + (data.message || 'Error'));
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Hitung Harga & Cek Plafon Kredit';
            }
        }
    </script>
</body>
</html>
