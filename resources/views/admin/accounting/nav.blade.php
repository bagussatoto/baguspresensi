<div class="tab-nav-pills" style="margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 8px; padding: 10px 12px;">
    <a href="{{ route('admin.akuntansi.jurnal') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.jurnal*') || request()->is('*akuntansi/jurnal*') ? 'active' : '' }}">
        <span>📖</span> Jurnal Umum (General Journal)
    </a>
    <a href="{{ route('admin.akuntansi.buku-besar') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.buku-besar*') || request()->is('*akuntansi/buku-besar*') ? 'active' : '' }}">
        <span>📑</span> Buku Besar (General Ledger)
    </a>
    <a href="{{ route('admin.akuntansi.neraca-saldo') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.neraca-saldo*') || request()->is('*akuntansi/neraca-saldo*') ? 'active' : '' }}">
        <span>⚖️</span> Neraca Saldo (Trial Balance)
    </a>
    <a href="{{ route('admin.akuntansi.laba-rugi') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.laba-rugi*') || request()->is('*akuntansi/laba-rugi*') ? 'active' : '' }}">
        <span>📈</span> Laporan Laba Rugi (Income Statement)
    </a>
    <a href="{{ route('admin.akuntansi.neraca') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.neraca*') || request()->is('*akuntansi/neraca*') ? 'active' : '' }}">
        <span>🏛️</span> Neraca Keuangan (Balance Sheet)
    </a>
    <a href="{{ route('admin.akuntansi.arus-kas') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.arus-kas*') || request()->is('*akuntansi/arus-kas*') ? 'active' : '' }}">
        <span>💵</span> Arus Kas (Cash Flow)
    </a>
    <a href="{{ route('admin.akuntansi.calk') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.calk*') || request()->is('*akuntansi/calk*') ? 'active' : '' }}">
        <span>📑</span> CaLK &amp; Paket Cetak
    </a>
    <a href="{{ route('admin.akuntansi.pivot') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.pivot*') || request()->is('*akuntansi/pivot*') ? 'active' : '' }}">
        <span>📊</span> Pivot Analisis Transaksi SAK
    </a>
    <a href="{{ route('admin.akuntansi.tutup-buku') }}" class="tab-pill-btn {{ request()->routeIs('admin.akuntansi.tutup-buku*') || request()->is('*akuntansi/tutup-buku*') ? 'active' : '' }}">
        <span>🔒</span> Tutup Buku (Period Closing)
    </a>
    <a href="{{ route('admin.coa') }}" class="tab-pill-btn {{ request()->routeIs('admin.coa*') || request()->is('*admin/coa*') || request()->is('*coa*') ? 'active' : '' }}">
        <span>⚙️</span> Kelola COA
    </a>
</div>

