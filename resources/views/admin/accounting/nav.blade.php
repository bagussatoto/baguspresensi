<div class="tab-nav-pills" style="margin-bottom: 24px;">
    <a href="{{ route('admin.akuntansi.jurnal') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.jurnal*') ? 'active' : '' }}">
        <span>📖</span> Jurnal Umum (General Journal)
    </a>
    <a href="{{ route('admin.akuntansi.buku-besar') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.buku-besar*') ? 'active' : '' }}">
        <span>📑</span> Buku Besar (General Ledger)
    </a>
    <a href="{{ route('admin.akuntansi.neraca-saldo') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.neraca-saldo*') ? 'active' : '' }}">
        <span>⚖️</span> Neraca Saldo (Trial Balance)
    </a>
    <a href="{{ route('admin.akuntansi.laba-rugi') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.laba-rugi*') ? 'active' : '' }}">
        <span>📈</span> Laporan Laba Rugi (Income Statement)
    </a>
    <a href="{{ route('admin.akuntansi.neraca') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.neraca*') ? 'active' : '' }}">
        <span>🏛️</span> Neraca Keuangan (Balance Sheet)
    </a>
    <a href="{{ route('admin.coa') }}" class="tab-pill-btn {{ request()->routeIs('admin.coa*') ? 'active' : '' }}" style="margin-left: auto;">
        <span>⚙️</span> Kelola COA
    </a>
</div>

