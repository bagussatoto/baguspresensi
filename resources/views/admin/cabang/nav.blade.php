<div class="tab-nav-pills" style="margin-bottom: 24px; display: flex; gap: 8px; flex-wrap: wrap;">
    <a href="{{ route('admin.cabang') }}" class="tab-pill-btn {{ request()->routeIs('admin.cabang') ? 'active' : '' }}">
        <span>🏢</span> Jaringan Cabang & DC
    </a>
    <a href="{{ route('admin.cabang.produk') }}" class="tab-pill-btn {{ request()->routeIs('admin.cabang.produk*') ? 'active' : '' }}">
        <span>📍</span> Lokalisasi Rak & ROP Cabang
    </a>
    <a href="{{ route('admin.cabang.zonasi-harga') }}" class="tab-pill-btn {{ request()->routeIs('admin.cabang.zonasi-harga*') ? 'active' : '' }}">
        <span>💲</span> Zonasi Harga Multi-Cabang
    </a>
    <a href="{{ route('admin.produk') }}" class="tab-pill-btn {{ request()->routeIs('admin.produk*') ? 'active' : '' }}">
        <span>🏷️</span> Master Produk (The Hub)
    </a>
    <a href="{{ route('admin.harga') }}" class="tab-pill-btn {{ request()->routeIs('admin.harga*') ? 'active' : '' }}" style="margin-left: auto;">
        <span>🏷️</span> Pricelist Tiering
    </a>
</div>
