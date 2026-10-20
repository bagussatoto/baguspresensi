<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Daftar Harga</title>
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
            --sidebar-text: #b8c7ce;
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
        .sidebar-menu li a:hover { background-color: #1e282c; }
        .sidebar-menu li.active > a { color: #ffffff; background: #1e282c; border-left: 3px solid #3c8dbc; font-weight: 600; }
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
        .stat-chip.chip-green { background: #dcfce7; color: #15803d; }

        .btn-add-item { background: #00bcd4; border: 1px solid #00acc1; color: #ffffff; font-weight: 700; height: 32px; padding: 0 12px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; cursor: pointer; }

        /* PRICELIST CARDS */
        .pricelist-card {
            background: #ffffff; border-radius: 4px; border: 1px solid #d2d6de; margin-bottom: 16px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .pricelist-card-header {
            padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex;
            align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
        }
        .pl-title { font-size: 14px; font-weight: 700; color: var(--header-dark-blue); display: flex; align-items: center; gap: 8px; }
        .pl-code { font-family: monospace; font-size: 11px; color: #0369a1; background: #e0f2fe; padding: 2px 6px; border-radius: 3px; }

        .price-items-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .price-items-table th { background: #edf2f7; color: #475569; font-weight: 600; padding: 6px 10px; text-align: left; }
        .price-items-table td { padding: 6px 10px; border-bottom: 1px solid #edf1f5; }

        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.55); z-index: 2000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
        .modal-overlay.active { display: flex; }
        .modal-card { background: #ffffff; border-radius: 4px; width: 92%; max-width: 480px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border-top: 4px solid var(--header-blue); }
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
                <li class="active"><a href="{{ route('admin.harga') }}"><span class="menu-item-left">💰 Daftar Harga</span></a></li>
                <li><a href="{{ route('admin.stok-limit') }}"><span class="menu-item-left">📦 Stok Limit & Gudang</span></a></li>
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
            <h1 class="page-title-center">💰 DAFTAR HARGA & TIERING (PRICELISTS)</h1>
            <p class="page-subtitle-center">Manajemen matriks harga jual, tier retail / member / grosir, dan item diskon khusus Maya Graha Kencana</p>

            <div class="stats-ribbon-bar">
                <div style="font-size: 13px; font-weight: 600; color: var(--header-dark-blue);">
                    <span>🏢 Maya Graha Kencana</span> / <span>Matriks Harga</span>
                </div>
                <div style="display: flex; gap: 8px;">
                    <span class="stat-chip chip-blue">💰 Total: <strong>{{ $totalCount }} Daftar Harga</strong></span>
                    <button class="btn-add-item" onclick="openAddPricelistModal()">+ Tambah Daftar Harga</button>
                </div>
            </div>

            <!-- CARDS CONTAINER -->
            <div id="pricelistCardsContainer">
                @foreach ($pricelists as $pl)
                    <div class="pricelist-card">
                        <div class="pricelist-card-header">
                            <div class="pl-title">
                                <span>{{ $pl->name }}</span>
                                <span class="pl-code">{{ $pl->code }}</span>
                                <span style="font-size: 11px; background: #f3e8ff; color: #7e22ce; padding: 1px 6px; border-radius: 10px; font-weight: 600;">
                                    Tipe: {{ strtoupper($pl->customer_type->value ?? 'RETAIL') }}
                                </span>
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" class="btn-modal btn-modal-primary" style="padding: 4px 8px; font-size: 11.5px;" onclick="openAddItemModal({{ $pl->id }}, '{{ addslashes($pl->name) }}')">
                                    + Atur Harga Item
                                </button>
                            </div>
                        </div>
                        <div style="padding: 10px 14px;">
                            @if ($pl->items->isEmpty())
                                <div style="color: #94a3b8; font-size: 12px; font-style: italic;">Belum ada item harga kustom di daftar ini. Berlaku harga dasar produk standar.</div>
                            @else
                                <table class="price-items-table">
                                    <thead>
                                        <tr>
                                            <th>Item Produk</th>
                                            <th style="width: 100px; text-align: center;">Min. Qty</th>
                                            <th style="width: 140px; text-align: right;">Harga Khusus</th>
                                            <th style="width: 100px; text-align: right;">Diskon %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pl->items as $it)
                                            <tr>
                                                <td style="font-weight: 500;">
                                                    {{ $it->variant->product->name ?? 'Produk' }} - {{ $it->variant->name ?? '' }}
                                                </td>
                                                <td style="text-align: center;">{{ $it->min_qty }} Unit</td>
                                                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #15803d;">
                                                    Rp {{ number_format((float) $it->unit_price, 0, ',', '.') }}
                                                </td>
                                                <td style="text-align: right; color: #b45309;">
                                                    {{ $it->discount_percent }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH PRICELIST -->
    <div class="modal-overlay" id="addPricelistModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">💰 Tambah Daftar Harga Baru</div>
                <button class="modal-close-btn" onclick="closeModal('addPricelistModal')">&times;</button>
            </div>
            <form action="{{ route('admin.harga.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Daftar Harga <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Harga Member Gamers, Harga Grosir Warung">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipe Target Konsumen <span style="color:red;">*</span></label>
                        <select name="customer_type" class="form-control" required>
                            <option value="retail">Retail / Eceran</option>
                            <option value="grosir">Grosir / Distributor</option>
                            <option value="reseller">Reseller</option>
                            <option value="proyek">Proyek / Tender B2B</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addPricelistModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL ATUR HARGA ITEM -->
    <div class="modal-overlay" id="addItemModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">➕ Atur Harga Khusus Produk</div>
                <button class="modal-close-btn" onclick="closeModal('addItemModal')">&times;</button>
            </div>
            <form id="formAddItem" onsubmit="submitAddItem(event)">
                <input type="hidden" id="modalPricelistId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Target Pricelist</label>
                        <input type="text" id="modalPricelistName" class="form-control" readonly style="background:#f1f5f9; font-weight:600;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pilih Produk Varian <span style="color:red;">*</span></label>
                        <select id="modalVariantId" class="form-control" required>
                            @foreach ($variants as $v)
                                <option value="{{ $v->id }}">{{ $v->product->name ?? '' }} - {{ $v->name }} (Dasar: Rp {{ number_format($v->base_price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Khusus (Rp) <span style="color:red;">*</span></label>
                        <input type="number" id="modalUnitPrice" class="form-control" required min="0" placeholder="Masukkan nominal khusus...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addItemModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-primary">Simpan Item Harga</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        function openAddPricelistModal() { openModal('addPricelistModal'); }

        function openAddItemModal(plId, plName) {
            document.getElementById('modalPricelistId').value = plId;
            document.getElementById('modalPricelistName').value = plName;
            openModal('addItemModal');
        }

        function submitAddItem(event) {
            event.preventDefault();
            const plId = document.getElementById('modalPricelistId').value;
            const payload = {
                product_variant_id: document.getElementById('modalVariantId').value,
                unit_price: document.getElementById('modalUnitPrice').value
            };

            fetch(`{{ url('admin/harga') }}/${plId}/item`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>
