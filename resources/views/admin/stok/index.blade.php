<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Stok Limit & Inventaris</title>
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
            --table-header-teal: #33b0a6;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--body-bg); color: var(--text-dark); font-size: 13px; overflow-x: hidden; }

        .main-header {
            background: linear-gradient(to right, #246e8c, var(--header-blue));
            height: 48px; display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px; color: #ffffff; position: fixed; top: 0; left: 0; right: 0; z-index: 1030;
        }
        .header-brand-wrap { display: flex; align-items: center; gap: 14px; }
        .brand-title { font-size: 18px; font-weight: 700; color: #ffffff; text-decoration: none; }
        .sidebar-toggle-btn { background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer; padding: 4px 8px; }
        .header-right-tools { display: flex; align-items: center; gap: 12px; font-size: 12px; }
        .header-link-btn { color: #e0f2fe; text-decoration: none; padding: 4px 10px; border-radius: 3px; background: rgba(255,255,255,0.12); }

        .sub-navbar {
            background-color: var(--subnav-blue); min-height: 40px; position: fixed;
            top: 48px; left: 230px; right: 0; z-index: 1020; display: flex; flex-wrap: wrap;
            align-items: center; padding: 4px 10px; gap: 4px; border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .subnav-item {
            display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px;
            color: #e8f4f8; text-decoration: none; font-size: 11px; font-weight: 500;
            border-radius: 3px; white-space: nowrap;
        }
        .subnav-item:hover { background: rgba(0,0,0,0.15); color: #ffffff; }
        .subnav-item.active { background: rgba(0, 0, 0, 0.25); color: #ffffff; font-weight: 700; border: 1px solid rgba(255,255,255,0.3); }

        .app-wrapper { display: flex; min-height: 100vh; padding-top: 96px; }
        .main-sidebar { width: 230px; background-color: var(--sidebar-bg); position: fixed; top: 48px; bottom: 0; left: 0; z-index: 1025; overflow-y: auto; }
        .sidebar-user-panel { padding: 16px 14px; background-color: var(--sidebar-header); display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #1a2226; }
        .user-avatar-circle { width: 42px; height: 42px; border-radius: 50%; background: #3c8dbc; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; }
        .user-info-name { font-size: 13px; font-weight: 700; color: #ffffff; }
        .user-info-role { font-size: 11px; color: #8aa4af; }
        .user-status-badge { font-size: 10px; color: #2ecc71; display: flex; align-items: center; gap: 4px; margin-top: 3px; }
        .status-dot-blink { width: 7px; height: 7px; background: #2ecc71; border-radius: 50%; display: inline-block; }
        .sidebar-nav-header { padding: 12px 16px 6px; font-size: 10px; text-transform: uppercase; color: #4b646f; background: #1a2226; font-weight: 700; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li a { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; color: var(--sidebar-text); text-decoration: none; font-size: 12.5px; }
        .sidebar-menu li a:hover { background-color: var(--sidebar-hover); color: var(--sidebar-active-text); }
        .sidebar-menu li.active > a { color: var(--sidebar-active-text); background: #1e282c; border-left: 3px solid #3c8dbc; font-weight: 600; }
        .menu-item-left { display: flex; align-items: center; gap: 10px; }

        .content-wrapper { margin-left: 230px; flex: 1; padding: 12px 20px 30px; min-height: calc(100vh - 96px); }
        .page-title-center { text-align: center; font-size: 20px; font-weight: 500; color: #333333; margin: 6px 0 4px; }
        .page-subtitle-center { text-align: center; font-size: 12px; color: #666666; margin-bottom: 14px; }

        .stats-ribbon-bar {
            background: #ffffff; border: 1px solid #d2d6de; border-left: 4px solid var(--header-dark-blue);
            border-radius: 4px; padding: 8px 14px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
        }
        .stat-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .stat-chip.chip-blue { background: #e0f2fe; color: #0369a1; }
        .stat-chip.chip-red { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
        .stat-chip.chip-green { background: #dcfce7; color: #15803d; }

        .filter-toolbar-box { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-input-group { display: flex; align-items: center; background: #ffffff; border: 1px solid #ccd0d4; border-radius: 3px; overflow: hidden; max-width: 400px; flex: 1; }
        .search-input-group input { border: none; outline: none; padding: 7px 12px; font-size: 13px; width: 100%; }
        .search-input-group button { background: #f8f9fa; border: none; border-left: 1px solid #ccd0d4; padding: 7px 14px; color: #555555; cursor: pointer; }
        .toolbar-right-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .select-filter { height: 32px; padding: 4px 8px; font-size: 12px; border: 1px solid #ced4da; border-radius: 3px; background: #fff; }
        .action-tool-btn { background: #ffffff; border: 1px solid #ced4da; color: #495057; padding: 6px 12px; font-size: 13px; border-radius: 3px; cursor: pointer; height: 32px; }
        .action-tool-btn.active-filter { background: #17a2b8; color: #ffffff; border-color: #138496; }

        .table-card-box { background: #ffffff; border-radius: 4px; border: 1px solid #d2d6de; overflow: hidden; }
        .stock-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .stock-table thead th { background-color: var(--table-header-teal); color: #ffffff; font-weight: 700; padding: 9px 10px; text-align: left; font-size: 12px; }
        .stock-table tbody tr { border-bottom: 1px solid #edf1f5; }
        .stock-table tbody tr:hover { background-color: #eef7f8; }
        .stock-table tbody td { padding: 8px 10px; vertical-align: middle; }

        .badge-status-safe { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        .badge-status-low { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 700; }
        .badge-status-out { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 700; }

        .btn-action-sm { background: transparent; border: 1px solid transparent; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-size: 13px; color: #475569; }
        .btn-action-sm:hover { background: #e2e8f0; color: #1e293b; }

        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.55); z-index: 2000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }
        .modal-card { background: #ffffff; border-radius: 4px; width: 92%; max-width: 440px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border-top: 4px solid var(--header-blue); }
        .modal-header { padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #edf1f5; }
        .modal-title { font-size: 15px; font-weight: 700; color: var(--header-dark-blue); }
        .modal-close-btn { background: transparent; border: none; font-size: 20px; color: #999; cursor: pointer; }
        .modal-body { padding: 18px; }
        .modal-footer { padding: 12px 18px; background: #f8fafc; border-top: 1px solid #edf1f5; display: flex; justify-content: flex-end; gap: 8px; }
        .form-group { margin-bottom: 12px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: #475569; }
        .form-control { width: 100%; padding: 7px 10px; font-size: 13px; border: 1px solid #ccd0d4; border-radius: 3px; outline: none; }
        .btn-modal { padding: 7px 14px; font-size: 12.5px; font-weight: 600; border-radius: 3px; cursor: pointer; border: 1px solid transparent; }
        .btn-modal-secondary { background: #e2e8f0; color: #475569; }
        .btn-modal-primary { background: #0284c7; color: #ffffff; }

        .toast-box { position: fixed; bottom: 24px; right: 24px; background: #1e293b; color: #ffffff; padding: 12px 18px; border-radius: 4px; display: flex; align-items: center; gap: 10px; font-size: 13px; z-index: 3000; transform: translateY(100px); opacity: 0; transition: all 0.3s; }
        .toast-box.show { transform: translateY(0); opacity: 1; border-left: 4px solid #22c55e; }

        @media (max-width: 768px) { .sub-navbar { left: 0; } .main-sidebar { transform: translateX(-100%); } .content-wrapper { margin-left: 0; } }
        body.sidebar-collapsed .main-sidebar { width: 0; transform: translateX(-100%); }
        body.sidebar-collapsed .sub-navbar, body.sidebar-collapsed .content-wrapper { margin-left: 0; left: 0; }
    </style>
</head>
<body>

    @include('admin.partials.erp_navbar')

    <div class="app-wrapper">
        <aside class="main-sidebar" id="mainSidebar">
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

            <div class="sidebar-nav-header">MAIN NAVIGATION</div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.index') }}"><span class="menu-item-left">📊 Dashboard Admin</span></a></li>
            </ul>

            <div class="sidebar-nav-header">MASTER DATA & INVENTARIS</div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.merk') }}"><span class="menu-item-left">🏷️ Merk Produk</span></a></li>
                <li><a href="{{ route('admin.kategori') }}"><span class="menu-item-left">📁 Kategori Produk</span></a></li>
                <li><a href="{{ route('admin.produk') }}"><span class="menu-item-left">⭐ Katalog Produk</span></a></li>
                <li class="active"><a href="{{ route('admin.stok-limit') }}"><span class="menu-item-left">📦 Stok Limit & Gudang</span></a></li>
                <li><a href="{{ route('admin.supplier') }}"><span class="menu-item-left">🏢 Supplier & Vendor</span></a></li>
                <li><a href="{{ route('admin.konsumen') }}"><span class="menu-item-left">👥 Konsumen & Member</span></a></li>
                <li><a href="{{ route('admin.jadwal') }}"><span class="menu-item-left">🕒 Jadwal Shift Kerja</span></a></li>
                <li><a href="{{ route('admin.karyawan') }}"><span class="menu-item-left">👤 Karyawan & Staff</span></a></li>
            </ul>

            <div class="sidebar-nav-header">TRANSAKSI & OPERASIONAL</div>
            <ul class="sidebar-menu">
                <li><a href="{{ route('admin.penjualan') }}"><span class="menu-item-left">🛒 Penjualan & POS Kasir</span></a></li>
                <li><a href="{{ route('admin.presensi') }}"><span class="menu-item-left">📅 Data Presensi Kerja</span></a></li>
                <li><a href="{{ route('warnet.index') }}"><span class="menu-item-left">🖥️ Billing & POS Warnet</span></a></li>
            </ul>
        </aside>

        <main class="content-wrapper">
            <h1 class="page-title-center">📦 DAFTAR STOK LIMIT & INVENTARIS GUDANG</h1>
            <p class="page-subtitle-center">Monitoring batas minimum stok (safety stock), kuantitas fisik per gudang / kasir, dan notifikasi stok menipis</p>

            <div class="stats-ribbon-bar">
                <div style="font-size: 13px; font-weight: 600; color: var(--header-dark-blue);">
                    <span>🏢 Maya Graha Kencana</span> / <span>Ledger Stok Gudang</span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <span class="stat-chip chip-blue">📦 Total: <strong>{{ $metrics['total_items'] }} Item</strong></span>
                    <span class="stat-chip chip-red">⚠️ Menipis: <strong>{{ $metrics['low_stock_count'] }} Item</strong></span>
                    <span class="stat-chip chip-green">Total Fisik: <strong>{{ $metrics['total_qty'] }} Unit</strong></span>
                </div>
            </div>

            <div class="filter-toolbar-box">
                <div class="search-input-group">
                    <input type="text" id="searchInput" placeholder="Cari nama produk, varian, atau SKU..." oninput="handleSearch(this.value)">
                    <button type="button" onclick="handleSearch(document.getElementById('searchInput').value)">🔍</button>
                </div>

                <div class="toolbar-right-actions">
                    <select class="select-filter" id="selectWarehouseFilter" onchange="filterWarehouse(this.value)">
                        <option value="all">Semua Gudang / Pos</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>

                    <select class="select-filter" id="selectStockStatusFilter" onchange="filterStockStatus(this.value)">
                        <option value="all">Semua Status Stok</option>
                        <option value="low">⚠️ Menipis (Di Bawah Limit)</option>
                        <option value="out">🔴 Habis (0)</option>
                        <option value="safe">🟢 Aman</option>
                    </select>
                </div>
            </div>

            <div class="table-card-box">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Lokasi Gudang</th>
                            <th>Nama Produk & Varian</th>
                            <th style="width: 100px; text-align: center;">SKU</th>
                            <th style="width: 110px; text-align: right;">Stok Fisik</th>
                            <th style="width: 120px; text-align: right;">Limit Minimum</th>
                            <th style="width: 110px; text-align: center;">Status</th>
                            <th style="width: 80px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody"></tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL EDIT STOK & LIMIT -->
    <div class="modal-overlay" id="editStockModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">📦 Penyesuaian Stok & Limit Minimum</div>
                <button class="modal-close-btn" onclick="closeModal('editStockModal')">&times;</button>
            </div>
            <form id="formEditStock" onsubmit="submitEditStock(event)">
                <input type="hidden" id="editStockId">
                <div class="modal-body">
                    <div style="font-size: 13px; margin-bottom: 12px; background: #f8fafc; padding: 10px; border-radius: 4px; border: 1px solid #e2e8f0;">
                        <div>Produk: <strong id="editStockProdName">-</strong></div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;">Lokasi: <span id="editStockWhName">-</span></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kuantitas Stok Fisik Saat Ini (Unit)</label>
                        <input type="number" id="editStockQty" name="qty_on_hand" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Batas Limit Minimum (Safety Stock)</label>
                        <input type="number" id="editStockSafety" name="safety_stock" class="form-control" required min="0">
                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;">Peringatan stok menipis akan muncul saat stok ≤ angka limit ini.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editStockModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitStock">Simpan Penyesuaian</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>

    <script>
        let allStocks = @json($stocks);
        let currentWhFilter = 'all';
        let currentStockFilter = 'all';
        let currentSearchQuery = '';

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        let toastTimeout = null;
        function showToast(message) {
            const toast = document.getElementById('toastBox');
            document.getElementById('toastMessage').innerText = message;
            toast.classList.add('show');
            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        function renderStockTable() {
            const tbody = document.getElementById('stockTableBody');
            if (!tbody) return;

            const q = currentSearchQuery.toLowerCase();

            const filtered = allStocks.filter(s => {
                const prodName = s.variant && s.variant.product ? s.variant.product.name.toLowerCase() : '';
                const varName = s.variant ? s.variant.name.toLowerCase() : '';
                const sku = s.variant ? (s.variant.sku || '').toLowerCase() : '';

                const matchesSearch = !q || prodName.includes(q) || varName.includes(q) || sku.includes(q);

                let matchesWh = true;
                if (currentWhFilter !== 'all') matchesWh = String(s.warehouse_id) === String(currentWhFilter);

                let matchesStock = true;
                if (currentStockFilter === 'low') matchesStock = s.qty_on_hand <= s.safety_stock && s.qty_on_hand > 0;
                else if (currentStockFilter === 'out') matchesStock = s.qty_on_hand <= 0;
                else if (currentStockFilter === 'safe') matchesStock = s.qty_on_hand > s.safety_stock;

                return matchesSearch && matchesWh && matchesStock;
            });

            tbody.innerHTML = '';

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 30px; color: #888;">Tidak ada data stok yang sesuai filter.</td></tr>`;
                return;
            }

            filtered.forEach((s, idx) => {
                const tr = document.createElement('tr');
                const prodTitle = s.variant && s.variant.product ? s.variant.product.name : 'Produk';
                const varTitle = s.variant ? s.variant.name : '';
                const skuTitle = s.variant ? s.variant.sku : '-';
                const whTitle = s.warehouse ? s.warehouse.name : 'Gudang';

                let statusBadge = '<span class="badge-status-safe">🟢 Aman</span>';
                if (s.qty_on_hand <= 0) {
                    statusBadge = '<span class="badge-status-out">🔴 Habis</span>';
                } else if (s.qty_on_hand <= s.safety_stock) {
                    statusBadge = '<span class="badge-status-low">⚠️ Menipis</span>';
                }

                tr.innerHTML = `
                    <td style="text-align: center; color: #64748b;">${idx + 1}</td>
                    <td style="font-weight: 600; color: #1e293b;">🏢 ${escapeHtml(whTitle)}</td>
                    <td>
                        <div style="font-weight: 600;">${escapeHtml(prodTitle)}</div>
                        <div style="font-size: 11px; color: #64748b;">Varian: ${escapeHtml(varTitle)}</div>
                    </td>
                    <td style="text-align: center; font-family: monospace; font-size: 11.5px; color: #0284c7;">
                        ${escapeHtml(skuTitle)}
                    </td>
                    <td style="text-align: right; font-weight: 700; font-size: 13px; color: #1e293b;">
                        ${s.qty_on_hand} Unit
                    </td>
                    <td style="text-align: right; color: #b45309; font-weight: 600;">
                        ${s.safety_stock} Unit
                    </td>
                    <td style="text-align: center;">${statusBadge}</td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-action-sm" onclick="openEditStockModal(${s.id})" title="Sesuaikan Stok & Limit">
                            ⚙️
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function handleSearch(val) { currentSearchQuery = val ? val.trim() : ''; renderStockTable(); }
        function filterWarehouse(val) { currentWhFilter = val; renderStockTable(); }
        function filterStockStatus(val) { currentStockFilter = val; renderStockTable(); }

        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        function openEditStockModal(id) {
            const s = allStocks.find(item => item.id === id);
            if (!s) return;

            document.getElementById('editStockId').value = s.id;
            document.getElementById('editStockProdName').innerText = (s.variant && s.variant.product ? s.variant.product.name : '') + ' (' + (s.variant ? s.variant.name : '') + ')';
            document.getElementById('editStockWhName').innerText = s.warehouse ? s.warehouse.name : '';
            document.getElementById('editStockQty').value = s.qty_on_hand;
            document.getElementById('editStockSafety').value = s.safety_stock;

            openModal('editStockModal');
        }

        function submitEditStock(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitStock');
            btn.disabled = true;

            const id = document.getElementById('editStockId').value;
            const payload = {
                qty_on_hand: parseInt(document.getElementById('editStockQty').value),
                safety_stock: parseInt(document.getElementById('editStockSafety').value)
            };

            fetch(`{{ url('admin/stok-limit') }}/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success && data.stock) {
                    const idx = allStocks.findIndex(item => item.id == id);
                    if (idx !== -1) allStocks[idx] = data.stock;
                    closeModal('editStockModal');
                    renderStockTable();
                    showToast('Stok & limit minimum berhasil diperbarui!');
                }
            })
            .catch(() => { btn.disabled = false; });
        }

        document.addEventListener('DOMContentLoaded', renderStockTable);
    </script>
</body>
</html>
