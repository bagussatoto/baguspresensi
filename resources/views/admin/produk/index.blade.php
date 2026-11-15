@extends('admin.dashboard.layout')

@section('title', 'Katalog Produk')

@section('extra-styles')
    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
            --table-header-teal: #33b0a6;
        }

        .erp-legacy-surface {
            font-family: 'Roboto', 'Plus Jakarta Sans', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            font-size: 13px;
            padding: 18px 20px 30px;
            border-radius: 12px;
            min-height: calc(100vh - 120px);
        }

        .page-title-center { text-align: center; font-size: 20px; font-weight: 500; color: #333333; margin: 6px 0 4px; letter-spacing: 0.5px; }
        .page-subtitle-center { text-align: center; font-size: 12px; color: #666666; margin-bottom: 14px; }

        .stats-ribbon-bar {
            background: #ffffff; border: 1px solid #d2d6de; border-left: 4px solid var(--header-dark-blue);
            border-radius: 4px; padding: 8px 14px; margin-bottom: 14px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .stats-ribbon-left { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--header-dark-blue); }
        .stats-ribbon-chips { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .stat-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 600; background: #f1f5f9; border: 1px solid #e2e8f0; color: #475569; }
        .stat-chip.chip-blue { background: #e0f2fe; border-color: #bae6fd; color: #0369a1; }
        .stat-chip.chip-green { background: #dcfce7; border-color: #bbf7d0; color: #15803d; }
        .stat-chip.chip-purple { background: #f3e8ff; border-color: #e9d5ff; color: #7e22ce; }

        /* TOOLBAR */
        .filter-toolbar-box { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-input-group { display: flex; align-items: center; background: #ffffff; border: 1px solid #ccd0d4; border-radius: 3px; overflow: hidden; max-width: 400px; flex: 1; }
        .search-input-group input { border: none; outline: none; padding: 7px 12px; font-size: 13px; width: 100%; color: #333333; background: #ffffff; }
        .search-input-group button { background: #f8f9fa; border: none; border-left: 1px solid #ccd0d4; padding: 7px 14px; color: #555555; cursor: pointer; }
        .toolbar-right-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .select-filter { height: 32px; padding: 4px 8px; font-size: 12px; border: 1px solid #ced4da; border-radius: 3px; background: #fff; color: #333333; outline: none; }
        .action-tool-btn { background: #ffffff; border: 1px solid #ced4da; color: #495057; padding: 6px 12px; font-size: 13px; border-radius: 3px; cursor: pointer; height: 32px; display: inline-flex; align-items: center; }
        .action-tool-btn.active-filter { background: #17a2b8; color: #ffffff; border-color: #138496; }
        .btn-add-item { background: #00bcd4; border: 1px solid #00acc1; color: #ffffff; font-weight: 700; width: 34px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; cursor: pointer; }

        /* COLUMNS CONTAINER */
        .product-columns-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; align-items: start; }
        .product-columns-container.layout-2col { grid-template-columns: repeat(2, 1fr); }
        .product-columns-container.layout-1col { grid-template-columns: 1fr; }
        .product-column-box { background: #ffffff; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #d2d6de; overflow: hidden; }

        .product-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: #333333; }
        .product-table thead th { background-color: var(--table-header-teal); color: #ffffff; font-weight: 700; padding: 8px 10px; text-align: left; font-size: 12px; }
        .product-table tbody tr { border-bottom: 1px solid #edf1f5; }
        .product-table tbody tr:hover { background-color: #eef7f8; }
        .product-table tbody td { padding: 7px 10px; vertical-align: middle; }

        .badge-cat { display: inline-block; font-size: 10px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 1px 5px; border-radius: 3px; font-weight: 500; }
        .badge-brand { display: inline-block; font-size: 10px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 1px 5px; border-radius: 3px; font-weight: 500; }
        .badge-price { font-family: monospace; font-size: 12px; font-weight: 700; color: #15803d; }
        .badge-inactive { font-size: 10px; background: #fee2e2; color: #dc2626; padding: 1px 4px; border-radius: 2px; }

        .btn-row-action { background: transparent; border: 1px solid transparent; padding: 3px 6px; border-radius: 3px; cursor: pointer; font-size: 13px; color: #6c757d; }
        .btn-row-action:hover { background: #e9ecef; color: #212529; }
        .btn-row-action.btn-del:hover { background: #fee2e2; color: #dc2626; }

        /* MODALS */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.55); z-index: 2000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }
        .modal-card { background: #ffffff; color: #333333; border-radius: 4px; width: 92%; max-width: 520px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border-top: 4px solid var(--header-blue); }
        .modal-header { padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #edf1f5; }
        .modal-title { font-size: 15px; font-weight: 700; color: var(--header-dark-blue); }
        .modal-close-btn { background: transparent; border: none; font-size: 20px; color: #999; cursor: pointer; }
        .modal-body { padding: 18px; max-height: 80vh; overflow-y: auto; }
        .modal-footer { padding: 12px 18px; background: #f8fafc; border-top: 1px solid #edf1f5; display: flex; justify-content: flex-end; gap: 8px; }
        .form-group { margin-bottom: 12px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px; color: #475569; }
        .form-control { width: 100%; padding: 7px 10px; font-size: 13px; border: 1px solid #ccd0d4; border-radius: 3px; outline: none; color: #333333; background: #ffffff; }
        .btn-modal { padding: 7px 14px; font-size: 12.5px; font-weight: 600; border-radius: 3px; cursor: pointer; border: 1px solid transparent; }
        .btn-modal-secondary { background: #e2e8f0; color: #475569; }
        .btn-modal-primary { background: #0284c7; color: #ffffff; }
        .btn-modal-danger { background: #dc2626; color: #ffffff; }

        /* TOAST */
        .toast-box { position: fixed; bottom: 24px; right: 24px; background: #1e293b; color: #ffffff; padding: 12px 18px; border-radius: 4px; display: flex; align-items: center; gap: 10px; font-size: 13px; z-index: 3000; transform: translateY(100px); opacity: 0; transition: all 0.3s; }
        .toast-box.show { transform: translateY(0); opacity: 1; }
        .toast-box.toast-success { border-left: 4px solid #22c55e; }
        .toast-box.toast-error { border-left: 4px solid #ef4444; }

        @media (max-width: 992px) { .product-columns-container { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .product-columns-container { grid-template-columns: 1fr; } }
    </style>
@endsection

@section('content')
    <div class="erp-legacy-surface">
        <h1 class="page-title-center">⭐ DAFTAR KATALOG PRODUK & VARIAN</h1>
            <p class="page-subtitle-center">Katalog Induk Produk Maya Graha Kencana: F&B, Paket Billing, Komputer, dan Merchandise</p>

            <!-- STATS RIBBON -->
            <div class="stats-ribbon-bar">
                <div class="stats-ribbon-left">
                    <span>🏢 Maya Graha Kencana</span>
                    <span style="color: #94a3b8;">/</span>
                    <span>Katalog Master Produk</span>
                </div>
                <div class="stats-ribbon-chips">
                    <span class="stat-chip chip-blue">⭐ Total: <strong id="statTotalCount">{{ $totalCount }} Produk</strong></span>
                    <span class="stat-chip chip-green">📁 Kategori: <strong>{{ $categories->count() }}</strong></span>
                    <span class="stat-chip chip-purple">🏷️ Merk: <strong>{{ $brands->count() }}</strong></span>
                </div>
            </div>

            <!-- TOOLBAR -->
            <div class="filter-toolbar-box">
                <div class="search-input-group">
                    <input type="text" id="searchInput" placeholder="Cari nama produk, kode, kategori, atau merk..." oninput="handleSearch(this.value)">
                    <button type="button" onclick="handleSearch(document.getElementById('searchInput').value)">🔍</button>
                </div>

                <div class="toolbar-right-actions">
                    <select class="select-filter" id="selectCatFilter" onchange="filterCategory(this.value)">
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select class="select-filter" id="selectBrandFilter" onchange="filterBrand(this.value)">
                        <option value="all">Semua Merk</option>
                        @foreach ($brands as $brd)
                            <option value="{{ $brd->id }}">{{ $brd->name }}</option>
                        @endforeach
                    </select>

                    <button class="action-tool-btn" id="btnToggleLayout" onclick="toggleColumnLayout()" title="Ganti Layout Kolom">
                        <strong>=</strong>
                    </button>
                    <button class="action-tool-btn" id="btnFilterActive" onclick="filterStatus('active')" title="Produk Aktif">👁️</button>
                    <button class="action-tool-btn active-filter" id="btnFilterAll" onclick="filterStatus('all')" title="Semua Produk">👁️‍🗨️</button>
                    <button class="btn-add-item" onclick="openAddProductModal()" title="Tambah Produk Baru">+</button>
                </div>
            </div>

            <!-- GRID CONTAINER -->
            <div class="product-columns-container" id="productGridContainer"></div>
    </div>
@endsection

@section('modals')
    <!-- MODAL TAMBAH PRODUK -->
    <div class="modal-overlay" id="addProductModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">⭐ Tambah Produk Baru</div>
                <button class="modal-close-btn" onclick="closeModal('addProductModal')">&times;</button>
            </div>
            <form id="formAddProduct" onsubmit="submitAddProduct(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Produk <span style="color:red;">*</span></label>
                        <input type="text" id="addProdName" name="name" class="form-control" required placeholder="Contoh: Indomie Goreng Spesial, Kopi Susu, Mouse Gaming">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label class="form-label">Kode Produk (Opsional)</label>
                            <input type="text" id="addProdCode" name="code" class="form-control" placeholder="PRD-XXXX (Otomatis jika kosong)">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori <span style="color:red;">*</span></label>
                            <select id="addProdCategory" name="category_id" class="form-control" required>
                                <option value="">— Pilih Kategori —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label class="form-label">Merk (Opsional)</label>
                            <select id="addProdBrand" name="brand_id" class="form-control">
                                <option value="">— Tanpa Merk / Umum —</option>
                                @foreach ($brands as $brd)
                                    <option value="{{ $brd->id }}">{{ $brd->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Jual Dasar (Rp) <span style="color:red;">*</span></label>
                            <input type="number" id="addProdPrice" name="base_price" class="form-control" required min="0" placeholder="Contoh: 12000">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi Produk (Opsional)</label>
                        <input type="text" id="addProdDesc" name="description" class="form-control" placeholder="Rincian informasi produk...">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="addProdActive" name="is_active" checked value="1">
                        <label for="addProdActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addProductModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitAdd">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PRODUK -->
    <div class="modal-overlay" id="editProductModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">✏️ Edit Produk</div>
                <button class="modal-close-btn" onclick="closeModal('editProductModal')">&times;</button>
            </div>
            <form id="formEditProduct" onsubmit="submitEditProduct(event)">
                <input type="hidden" id="editProdId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Produk <span style="color:red;">*</span></label>
                        <input type="text" id="editProdName" name="name" class="form-control" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label class="form-label">Kode Produk</label>
                            <input type="text" id="editProdCode" name="code" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori</label>
                            <select id="editProdCategory" name="category_id" class="form-control">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label class="form-label">Merk</label>
                            <select id="editProdBrand" name="brand_id" class="form-control">
                                <option value="">— Tanpa Merk —</option>
                                @foreach ($brands as $brd)
                                    <option value="{{ $brd->id }}">{{ $brd->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Jual (Rp)</label>
                            <input type="number" id="editProdPrice" name="base_price" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi Produk</label>
                        <input type="text" id="editProdDesc" name="description" class="form-control">
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="editProdActive" name="is_active" value="1">
                        <label for="editProdActive" style="font-size: 13px; font-weight: 500; cursor: pointer;">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editProductModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary" id="btnSubmitEdit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div class="modal-overlay" id="deleteProductModal">
        <div class="modal-card" style="max-width: 400px; border-top-color: #dc2626;">
            <div class="modal-header">
                <div class="modal-title" style="color: #dc2626;">🗑️ Hapus Produk</div>
                <button class="modal-close-btn" onclick="closeModal('deleteProductModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus produk <strong id="deleteProdNameText" style="color: #dc2626;"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('deleteProductModal')">Batal</button>
                <button type="button" class="btn-modal btn-modal-danger" onclick="confirmDeleteProduct()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast-box" id="toastBox">
        <span id="toastIcon">✔</span>
        <span id="toastMessage">Operasi berhasil</span>
    </div>
@endsection

@section('scripts')
    <script>
        let allProducts = @json($products);
        let currentFilterStatus = 'all';
        let currentCatFilter = 'all';
        let currentBrandFilter = 'all';
        let currentSearchQuery = '';
        let currentLayout = '3col';
        let pendingDeleteId = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // LIVE TIME CLOCK
        function updateLiveClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const b = document.getElementById('liveTimeBadge');
            if (b) b.innerText = `${h}:${m}:${s} ON`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        function formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        let toastTimeout = null;
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toastBox');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');
            toast.className = `toast-box show toast-${type}`;
            icon.innerText = type === 'success' ? '✔' : '✖';
            msg.innerText = message;
            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => toast.classList.remove('show'), 3500);
        }

        function renderProductGrid() {
            const container = document.getElementById('productGridContainer');
            if (!container) return;

            const q = currentSearchQuery.toLowerCase();

            const filtered = allProducts.filter(p => {
                const matchesSearch = !q ||
                    (p.name && p.name.toLowerCase().includes(q)) ||
                    (p.code && p.code.toLowerCase().includes(q)) ||
                    (p.category && p.category.name && p.category.name.toLowerCase().includes(q)) ||
                    (p.brand && p.brand.name && p.brand.name.toLowerCase().includes(q));

                let matchesStatus = true;
                if (currentFilterStatus === 'active') matchesStatus = p.is_active;
                else if (currentFilterStatus === 'inactive') matchesStatus = !p.is_active;

                let matchesCat = true;
                if (currentCatFilter !== 'all') matchesCat = String(p.category_id) === String(currentCatFilter);

                let matchesBrand = true;
                if (currentBrandFilter !== 'all') matchesBrand = String(p.brand_id) === String(currentBrandFilter);

                return matchesSearch && matchesStatus && matchesCat && matchesBrand;
            });

            container.innerHTML = '';

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: #fff; border-radius: 4px; border: 1px solid #d2d6de; color: #777;">
                        <div style="font-size: 32px; margin-bottom: 8px;">⭐</div>
                        <div style="font-size: 15px; font-weight: 600;">Tidak ada produk yang cocok</div>
                        <div style="font-size: 12px; color: #999; margin-top: 4px;">Coba ubah kata kunci pencarian atau reset filter.</div>
                    </div>
                `;
                return;
            }

            let numCols = currentLayout === '2col' ? 2 : (currentLayout === '1col' ? 1 : 3);
            const total = filtered.length;
            const itemsPerCol = Math.ceil(total / numCols);

            for (let c = 0; c < numCols; c++) {
                const startIdx = c * itemsPerCol;
                const endIdx = Math.min(startIdx + itemsPerCol, total);
                const colItems = filtered.slice(startIdx, endIdx);
                if (colItems.length === 0 && c > 0) continue;

                const colBox = document.createElement('div');
                colBox.className = 'product-column-box';

                let tableHtml = `
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 90px; text-align: center;">Kode</th>
                                <th>Nama & Spesifikasi</th>
                                <th style="width: 80px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                colItems.forEach((prod, idxInCol) => {
                    const globalNo = startIdx + idxInCol + 1;
                    const catBadge = prod.category ? `<span class="badge-cat">${escapeHtml(prod.category.name)}</span>` : '';
                    const brandBadge = prod.brand ? `<span class="badge-brand">${escapeHtml(prod.brand.name)}</span>` : '';
                    const inactiveBadge = !prod.is_active ? `<span class="badge-inactive">Non-Aktif</span>` : '';

                    // Price display from variants
                    let priceDisplay = 'Rp 0';
                    if (prod.variants && prod.variants.length > 0) {
                        priceDisplay = formatRupiah(prod.variants[0].base_price);
                    }

                    tableHtml += `
                        <tr id="prod-row-${prod.id}">
                            <td style="text-align: center; color: #555;">${globalNo}</td>
                            <td style="text-align: center; font-family: monospace; font-weight: 600; color: #2c7ea1;">
                                ${escapeHtml(prod.code)}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                    ${escapeHtml(prod.name)} ${inactiveBadge}
                                </div>
                                <div style="display: flex; gap: 4px; margin-top: 3px; align-items: center; flex-wrap: wrap;">
                                    ${catBadge} ${brandBadge}
                                    <span class="badge-price">${priceDisplay}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-row-action" title="Edit Produk" onclick="openEditProductModal(${prod.id})">👁️</button>
                                <button type="button" class="btn-row-action btn-del" title="Hapus Produk" onclick="openDeleteProductModal(${prod.id}, '${escapeHtml(prod.name)}')">🗑️</button>
                            </td>
                        </tr>
                    `;
                });

                tableHtml += `</tbody></table>`;
                colBox.innerHTML = tableHtml;
                container.appendChild(colBox);
            }
        }

        function toggleColumnLayout() {
            const container = document.getElementById('productGridContainer');
            if (currentLayout === '3col') { currentLayout = '2col'; container.className = 'product-columns-container layout-2col'; }
            else if (currentLayout === '2col') { currentLayout = '1col'; container.className = 'product-columns-container layout-1col'; }
            else { currentLayout = '3col'; container.className = 'product-columns-container'; }
            renderProductGrid();
        }

        function filterStatus(status) {
            currentFilterStatus = status;
            document.getElementById('btnFilterActive').classList.toggle('active-filter', status === 'active');
            document.getElementById('btnFilterAll').classList.toggle('active-filter', status === 'all');
            renderProductGrid();
        }

        function filterCategory(catId) { currentCatFilter = catId; renderProductGrid(); }
        function filterBrand(brandId) { currentBrandFilter = brandId; renderProductGrid(); }
        function handleSearch(val) { currentSearchQuery = val ? val.trim() : ''; renderProductGrid(); }

        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        function openAddProductModal() {
            document.getElementById('formAddProduct').reset();
            document.getElementById('addProdActive').checked = true;
            openModal('addProductModal');
        }

        function submitAddProduct(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitAdd');
            btn.disabled = true;

            const payload = {
                name: document.getElementById('addProdName').value.trim(),
                code: document.getElementById('addProdCode').value.trim(),
                category_id: document.getElementById('addProdCategory').value,
                brand_id: document.getElementById('addProdBrand').value || null,
                base_price: document.getElementById('addProdPrice').value,
                description: document.getElementById('addProdDesc').value.trim(),
                is_active: document.getElementById('addProdActive').checked ? 1 : 0
            };

            fetch("{{ route('admin.produk.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success && data.product) {
                    allProducts.unshift(data.product);
                    closeModal('addProductModal');
                    renderProductGrid();
                    document.getElementById('statTotalCount').innerText = `${allProducts.length} Produk`;
                    document.getElementById('menuBadgeCount').innerText = allProducts.length;
                    showToast(data.message || 'Produk berhasil ditambahkan!', 'success');
                } else {
                    showToast(data.message || 'Gagal menyimpan produk', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                showToast('Terjadi kesalahan koneksi', 'error');
            });
        }

        function openEditProductModal(id) {
            const prod = allProducts.find(p => p.id === id);
            if (!prod) return;

            document.getElementById('editProdId').value = prod.id;
            document.getElementById('editProdName').value = prod.name;
            document.getElementById('editProdCode').value = prod.code || '';
            document.getElementById('editProdCategory').value = prod.category_id || '';
            document.getElementById('editProdBrand').value = prod.brand_id || '';
            document.getElementById('editProdPrice').value = prod.variants && prod.variants[0] ? prod.variants[0].base_price : 0;
            document.getElementById('editProdDesc').value = prod.description || '';
            document.getElementById('editProdActive').checked = prod.is_active;

            openModal('editProductModal');
        }

        function submitEditProduct(event) {
            event.preventDefault();
            const btn = document.getElementById('btnSubmitEdit');
            btn.disabled = true;
            const id = document.getElementById('editProdId').value;

            const payload = {
                name: document.getElementById('editProdName').value.trim(),
                code: document.getElementById('editProdCode').value.trim(),
                category_id: document.getElementById('editProdCategory').value,
                brand_id: document.getElementById('editProdBrand').value || null,
                base_price: document.getElementById('editProdPrice').value,
                description: document.getElementById('editProdDesc').value.trim(),
                is_active: document.getElementById('editProdActive').checked ? 1 : 0
            };

            fetch(`{{ url('admin/produk') }}/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success && data.product) {
                    const idx = allProducts.findIndex(p => p.id == id);
                    if (idx !== -1) allProducts[idx] = data.product;
                    closeModal('editProductModal');
                    renderProductGrid();
                    showToast(data.message || 'Produk berhasil diperbarui!', 'success');
                } else {
                    showToast(data.message || 'Gagal memperbarui produk', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                showToast('Terjadi kesalahan koneksi', 'error');
            });
        }

        function openDeleteProductModal(id, name) {
            pendingDeleteId = id;
            document.getElementById('deleteProdNameText').innerText = `"${name}"`;
            openModal('deleteProductModal');
        }

        function confirmDeleteProduct() {
            if (!pendingDeleteId) return;
            fetch(`{{ url('admin/produk') }}/${pendingDeleteId}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allProducts = allProducts.filter(p => p.id != pendingDeleteId);
                    closeModal('deleteProductModal');
                    pendingDeleteId = null;
                    renderProductGrid();
                    document.getElementById('statTotalCount').innerText = `${allProducts.length} Produk`;
                    document.getElementById('menuBadgeCount').innerText = allProducts.length;
                    showToast(data.message || 'Produk berhasil dihapus.', 'success');
                } else {
                    showToast(data.message || 'Gagal menghapus produk', 'error');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', renderProductGrid);
    </script>
@endsection
