<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Chart of Accounts (COA)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #287a9e;
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
            --btn-green: #00a65a;
            --btn-red: #dd4b39;
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
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
            padding-top: 48px;
        }

        /* MAIN CONTENT */
        .content-wrapper {
            flex: 1;
            padding: 18px 24px 60px 24px;
            max-width: 1440px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-desc {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 4px;
        }

        /* KPI SUMMARY CARDS */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 6px;
            border: 1px solid var(--content-border);
            padding: 14px;
            border-top: 3px solid #cbd5e1;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .kpi-card.cat-asset { border-top-color: #0284c7; }
        .kpi-card.cat-liability { border-top-color: #ea580c; }
        .kpi-card.cat-equity { border-top-color: #7c3aed; }
        .kpi-card.cat-revenue { border-top-color: #059669; }
        .kpi-card.cat-cogs { border-top-color: #d97706; }
        .kpi-card.cat-expense { border-top-color: #dc2626; }

        .kpi-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
            font-family: monospace;
        }

        .kpi-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* TOOLBAR */
        .toolbar-box {
            background: #ffffff;
            border: 1px solid var(--content-border);
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .form-control-sm {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 12.5px;
            outline: none;
            background: #fff;
        }

        .btn {
            padding: 7px 14px;
            border-radius: 4px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary { background: var(--header-blue); color: #fff; }
        .btn-primary:hover { background: var(--header-dark-blue); }
        .btn-success { background: var(--btn-green); color: #fff; }
        .btn-success:hover { background: #008d4c; }

        /* TABLE */
        .table-card {
            background: #ffffff;
            border: 1px solid var(--content-border);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .coa-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .coa-table thead th {
            background: #f8fafc;
            padding: 9px 12px;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 700;
            color: #475569;
            text-align: left;
        }

        .coa-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .coa-table tbody tr:hover {
            background: #f8fafc;
        }

        .coa-table tbody tr.row-header-1 {
            background: #f1f5f9;
            font-weight: 700;
        }

        .coa-table tbody tr.row-header-2 {
            background: #f8fafc;
            font-weight: 600;
        }

        .coa-table tbody td {
            padding: 8px 12px;
            vertical-align: middle;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 3px;
        }

        .badge-asset { background: #e0f2fe; color: #0369a1; }
        .badge-liability { background: #ffedd5; color: #c2410c; }
        .badge-equity { background: #f3e8ff; color: #7e22ce; }
        .badge-revenue { background: #dcfce7; color: #15803d; }
        .badge-cogs { background: #fef3c7; color: #b45309; }
        .badge-expense { background: #fee2e2; color: #b91c1c; }

        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-inactive { background: #fee2e2; color: #b91c1c; }

        /* MODAL */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(3px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-card {
            background: #fff;
            border-radius: 6px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-top: 4px solid var(--header-blue);
            overflow: hidden;
        }

        .modal-header {
            padding: 12px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 18px;
        }

        .modal-footer {
            padding: 12px 18px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <header class="main-header">
        <div class="header-brand-wrap">
            <a href="{{ route('admin.index') }}" class="brand-title">
                🏢 MAYA GRAHA KENCANA — ERP
            </a>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('admin.index') }}" style="color: #e0f2fe; text-decoration: none; font-size: 12px; font-weight: 600;">
                ← Dashboard ERP
            </a>
            <a href="{{ route('pos') }}" style="color: #fff; background: rgba(255,255,255,0.15); padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 700;">
                🛒 POS Kasir
            </a>
        </div>
    </header>

    <div class="app-wrapper">
        <main class="content-wrapper">

            <!-- PAGE TITLE -->
            <div class="page-header-box">
                <div>
                    <h1 class="page-title">
                        <span>📊</span> Bagan Akun / Chart of Accounts (COA)
                    </h1>
                    <p class="page-desc">
                        Struktur akun akuntansi dan buku besar standar (*Double-Entry*) Maya Graha Kencana.
                    </p>
                </div>
                <div>
                    <button type="button" onclick="openCreateModal()" class="btn btn-success">
                        <span>➕</span> Tambah Akun Baru
                    </button>
                </div>
            </div>

            <!-- FLASH ALERTS -->
            @if(session('success'))
                <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #15803d; padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-size: 12.5px;">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 6px; margin-bottom: 14px; font-size: 12.5px;">
                    ⚠ {{ session('error') }}
                </div>
            @endif

            <!-- KPI SUMMARY CARDS -->
            <div class="kpi-grid">
                @foreach($categorySummary as $catKey => $catData)
                    <div class="kpi-card cat-{{ $catKey }}">
                        <div class="kpi-label">{{ $catData['label'] }}</div>
                        <div class="kpi-value">Rp {{ number_format($catData['total_balance'], 0, ',', '.') }}</div>
                        <div class="kpi-sub">{{ $catData['count'] }} Akun Terdaftar</div>
                    </div>
                @endforeach
            </div>

            <!-- TOOLBAR / FILTER -->
            <div class="toolbar-box">
                <form method="GET" action="{{ route('admin.coa') }}" class="filter-group">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode atau nama akun..." class="form-control-sm" style="min-width: 220px;">
                    
                    <select name="category" class="form-control-sm" onchange="this.form.submit()">
                        <option value="all">Semua Kategori</option>
                        @foreach(\Modules\Kas\Domain\Enums\AccountCategory::cases() as $case)
                            <option value="{{ $case->value }}" {{ request('category') === $case->value ? 'selected' : '' }}>
                                {{ $case->label() }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="form-control-sm" onchange="this.form.submit()">
                        <option value="all">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px;">Cari</button>
                    @if(request()->hasAny(['q', 'category', 'status']))
                        <a href="{{ route('admin.coa') }}" class="btn" style="background: #e2e8f0; color: #475569; padding: 6px 10px;">Reset</a>
                    @endif
                </form>

                <div style="font-size: 12px; color: #64748b;">
                    Total: <strong>{{ $totalCount }} Akun</strong>
                </div>
            </div>

            <!-- COA TABLE -->
            <div class="table-card">
                <table class="coa-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Kode Akun</th>
                            <th>Nama Akun Rekening</th>
                            <th style="width: 160px;">Kategori</th>
                            <th style="width: 90px; text-align: center;">Saldo Normal</th>
                            <th style="width: 90px; text-align: center;">Tipe</th>
                            <th style="width: 140px; text-align: right;">Saldo Saat Ini</th>
                            <th style="width: 80px; text-align: center;">Status</th>
                            <th style="width: 110px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                            @php
                                $indent = ($acc->level - 1) * 22;
                                $rowClass = $acc->level == 1 ? 'row-header-1' : ($acc->level == 2 ? 'row-header-2' : '');
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="font-family: monospace; font-weight: bold; color: #0284c7;">
                                    <span style="display: inline-block; width: {{ $indent }}px;"></span>
                                    {{ $acc->account_code }}
                                </td>
                                <td>
                                    <span style="display: inline-block; width: {{ $indent }}px;"></span>
                                    @if($acc->is_header)
                                        📁 <strong>{{ $acc->account_name }}</strong>
                                    @else
                                        📄 {{ $acc->account_name }}
                                    @endif
                                    @if($acc->description)
                                        <div style="font-size: 11px; color: #94a3b8; margin-left: {{ $indent + 22 }}px;">
                                            {{ $acc->description }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $acc->account_category->value }}">
                                        {{ strtoupper($acc->account_category->value) }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #475569;">
                                    {{ $acc->normal_balance->value === 'debit' ? 'D (Debit)' : 'K (Kredit)' }}
                                </td>
                                <td style="text-align: center;">
                                    @if($acc->is_header)
                                        <span style="font-size: 11px; color: #64748b; font-weight: 700;">HEADER</span>
                                    @else
                                        <span style="font-size: 11px; color: #059669; font-weight: 600;">POSTING</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: bold; color: {{ $acc->current_balance < 0 ? '#dc2626' : '#0f172a' }};">
                                    Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $acc->is_active ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $acc->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" onclick='openEditModal(@json($acc))' style="padding: 3px 8px; font-size: 11px; border: 1px solid #cbd5e1; background: #fff; border-radius: 3px; cursor: pointer;">
                                        ✏️ Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 24px; text-align: center; color: #94a3b8;">
                                    Belum ada akun terdaftar yang sesuai kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- MODAL TAMBAH / EDIT AKUN COA -->
    <div id="modal-coa-form" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <strong id="modal-title" style="font-size: 14px; color: #1e293b;">➕ Tambah Akun COA Baru</strong>
                <button type="button" onclick="closeFormModal()" style="border: none; background: transparent; font-size: 18px; cursor: pointer; color: #64748b;">✕</button>
            </div>
            <form id="form-coa" method="POST" action="{{ route('admin.coa.store') }}">
                @csrf
                <div id="method-container"></div>

                <div class="modal-body">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Kategori Akun <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="account_category" id="modal-account-category" required class="form-control-sm" style="width: 100%;">
                            @foreach(\Modules\Kas\Domain\Enums\AccountCategory::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                                Kode Akun <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="account_code" id="modal-account-code" required placeholder="Contoh: 1-10005" class="form-control-sm" style="width: 100%; font-family: monospace;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                                Saldo Normal <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="normal_balance" id="modal-normal-balance" required class="form-control-sm" style="width: 100%;">
                                <option value="debit">Debit (D)</option>
                                <option value="credit">Kredit (K)</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Nama Akun Rekening <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="account_name" id="modal-account-name" required placeholder="Contoh: Kas Kecil Operasional" class="form-control-sm" style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Akun Induk (Header Parent)
                        </label>
                        <select name="parent_id" id="modal-parent-id" class="form-control-sm" style="width: 100%;">
                            <option value="">-- Tanpa Induk (Root Level 1) --</option>
                            @foreach($parentOptions as $p)
                                <option value="{{ $p->id }}">{{ $p->account_code }} - {{ $p->account_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
                            <input type="checkbox" name="is_header" id="modal-is-header" value="1">
                            <strong>Jadikan Akun Header</strong>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="modal-is-active" value="1" checked>
                            <strong>Status Aktif</strong>
                        </label>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            Catatan / Keterangan
                        </label>
                        <input type="text" name="description" id="modal-description" placeholder="Deskripsi peruntukan akun..." class="form-control-sm" style="width: 100%;">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeFormModal()" class="btn" style="background: #e2e8f0; color: #475569;">Batal</button>
                    <button type="submit" class="btn btn-primary" id="modal-btn-submit">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modal-title').innerText = '➕ Tambah Akun COA Baru';
            document.getElementById('form-coa').action = "{{ route('admin.coa.store') }}";
            document.getElementById('method-container').innerHTML = '';

            document.getElementById('modal-account-code').value = '';
            document.getElementById('modal-account-name').value = '';
            document.getElementById('modal-account-category').value = 'asset';
            document.getElementById('modal-normal-balance').value = 'debit';
            document.getElementById('modal-parent-id').value = '';
            document.getElementById('modal-is-header').checked = false;
            document.getElementById('modal-is-active').checked = true;
            document.getElementById('modal-description').value = '';

            document.getElementById('modal-coa-form').style.display = 'flex';
        }

        function openEditModal(acc) {
            document.getElementById('modal-title').innerText = `✏️ Edit Akun: ${acc.account_code}`;
            document.getElementById('form-coa').action = `{{ url('/admin/coa') }}/${acc.id}`;
            document.getElementById('method-container').innerHTML = `@method('PUT')`;

            document.getElementById('modal-account-code').value = acc.account_code;
            document.getElementById('modal-account-name').value = acc.account_name;
            document.getElementById('modal-account-category').value = acc.account_category;
            document.getElementById('modal-normal-balance').value = acc.normal_balance;
            document.getElementById('modal-parent-id').value = acc.parent_id || '';
            document.getElementById('modal-is-header').checked = !!acc.is_header;
            document.getElementById('modal-is-active').checked = !!acc.is_active;
            document.getElementById('modal-description').value = acc.description || '';

            document.getElementById('modal-coa-form').style.display = 'flex';
        }

        function closeFormModal() {
            document.getElementById('modal-coa-form').style.display = 'none';
        }
    </script>
</body>
</html>
