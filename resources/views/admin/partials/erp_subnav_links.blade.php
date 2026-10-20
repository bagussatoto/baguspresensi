{{-- ============================================================== --}}
{{-- 22 ITEM SUB-NAVBAR ERP DENGAN DETEKSI STATUS AKTIF OTOMATIS    --}}
{{-- ============================================================== --}}

{{-- 1. MERK --}}
<a href="{{ route('admin.merk') }}" 
   class="subnav-item {{ request()->routeIs('admin.merk*') || request()->is('*admin/merk*') ? 'active' : '' }}" 
   title="Master Data Merk Produk">
    🏷️ merk
</a>

{{-- 2. SUPPLIER --}}
<a href="{{ route('admin.supplier') }}" 
   class="subnav-item {{ request()->routeIs('admin.supplier*') || request()->is('*admin/supplier*') ? 'active' : '' }}" 
   title="Daftar Supplier & Vendor">
    🏢 supplier
</a>

{{-- 3. KATEGORI --}}
<a href="{{ route('admin.kategori') }}" 
   class="subnav-item {{ request()->routeIs('admin.kategori*') || request()->is('*admin/kategori*') ? 'active' : '' }}" 
   title="Kategori Produk">
    📁 kategori
</a>

{{-- 4. PENJUALAN --}}
<a href="{{ route('admin.penjualan') }}" 
   class="subnav-item {{ request()->routeIs('admin.penjualan*') || request()->is('*admin/penjualan*') ? 'active' : '' }}" 
   title="Penjualan & Kasir ERP">
    🛒 penjualan
</a>

{{-- 5. PRODUK --}}
<a href="{{ route('admin.produk') }}" 
   class="subnav-item {{ request()->routeIs('admin.produk*') || request()->is('*admin/produk*') ? 'active' : '' }}" 
   title="Katalog Produk & Varian">
    ⭐ produk
</a>

{{-- 6. HARGA --}}
<a href="{{ route('admin.harga') }}" 
   class="subnav-item {{ request()->routeIs('admin.harga*') || request()->is('*admin/harga*') ? 'active' : '' }}" 
   title="Daftar Harga & Tiering">
    💰 harga
</a>

{{-- 7. STOK LIMIT --}}
<a href="{{ route('admin.stok-limit') }}" 
   class="subnav-item {{ request()->routeIs('admin.stok-limit*') || request()->is('*admin/stok-limit*') ? 'active' : '' }}" 
   title="Stok Limit & Inventaris Gudang">
    📦 stok limit
</a>

{{-- 8. KONSUMEN --}}
<a href="{{ route('admin.konsumen') }}" 
   class="subnav-item {{ request()->routeIs('admin.konsumen*') || request()->is('*admin/konsumen*') ? 'active' : '' }}" 
   title="Data Konsumen & Pelanggan Member">
    👥 konsumen
</a>

{{-- 9. SET REKAP --}}
<a href="{{ route('admin.presensi.pengaturan') }}" 
   class="subnav-item {{ request()->routeIs('admin.presensi.pengaturan*') || request()->is('*admin/presensi/pengaturan*') ? 'active' : '' }}" 
   title="Pengaturan Presensi & Rekapitulasi">
    ⚙️ set rekap
</a>

{{-- 10. KARYAWAN --}}
<a href="{{ route('admin.karyawan') }}" 
   class="subnav-item {{ (request()->routeIs('admin.karyawan*') || request()->is('*admin/karyawan*')) && !request()->is('*profile*') && request('tab') != 'tab-profil' ? 'active' : '' }}" 
   title="Kelola Karyawan">
    👤 karyawan
</a>

{{-- 11. PROFILE --}}
<a href="{{ route('admin.staff.profile') }}" 
   class="subnav-item {{ request()->routeIs('admin.staff.profile*') || request()->is('*staff/profile*') || request()->is('*karyawan/profile*') || request()->is('*admin/staff/profile*') || (request()->routeIs('admin.data') && request('tab') == 'tab-profil') ? 'active' : '' }}" 
   title="Profil Pengguna & Password">
    👤 profile
</a>

{{-- 12. DATA AC --}}
<a href="javascript:void(0)" 
   onclick="openErpModal('dataAcModal')" 
   class="subnav-item" 
   title="Data & Jadwal Perawatan AC">
    ❄️ data ac
</a>

{{-- 13. PRESENSI --}}
<a href="{{ route('admin.presensi') }}" 
   class="subnav-item {{ ((request()->routeIs('admin.presensi*') || request()->is('*admin/presensi*')) && !request()->routeIs('admin.presensi.pengaturan*') && !request()->routeIs('admin.presensi.approval*') && !request()->is('*admin/presensi/pengaturan*')) || (request()->routeIs('admin.data') && request('tab') == 'tab-presensi') ? 'active' : '' }}" 
   title="Presensi Harian Karyawan">
    📅 presensi
</a>

{{-- 14. REKAP LIST KLAIM JAM KERJA --}}
<a href="{{ route('admin.data', ['tab' => 'tab-jamkerja']) }}" 
   class="subnav-item {{ (request()->routeIs('admin.data') && request('tab') == 'tab-jamkerja') || request()->routeIs('admin.presensi.approval*') || request()->is('*jam-kerja*') ? 'active' : '' }}" 
   title="Rekapitulasi List Klaim Jam Kerja & Lembur">
    📝 rekap list klaim jam kerja
</a>

{{-- 15. DATA BILLING --}}
<a href="{{ route('admin.data', ['tab' => 'tab-billing']) }}" 
   class="subnav-item {{ (request()->routeIs('admin.data') && request('tab') == 'tab-billing') || (request()->routeIs('warnet*') && request()->is('*billing*')) ? 'active' : '' }}" 
   title="Data Billing Shift Warnet">
    🖥️ data billing
</a>

{{-- 16. JADWAL --}}
<a href="{{ route('admin.jadwal') }}" 
   class="subnav-item {{ request()->routeIs('admin.jadwal*') || request()->is('*admin/jadwal*') || (request()->routeIs('admin.data') && request('tab') == 'tab-jadwal') ? 'active' : '' }}" 
   title="Jadwal Shift Kerja">
    🕒 jadwal
</a>

{{-- 17. KOMISI --}}
<a href="{{ route('warnet.index') }}#commissions" 
   class="subnav-item {{ request()->routeIs('warnet*') && request()->is('*commission*') ? 'active' : '' }}" 
   title="Komisi Shift Operator Kasir">
    💵 komisi
</a>

{{-- 18. STOP CODE --}}
<a href="javascript:void(0)" 
   onclick="openErpModal('stopCodeModal')" 
   class="subnav-item" 
   title="Kode Otorisasi & Emergency Stop Kasir">
    🛑 stop code
</a>

{{-- 19. RATING --}}
<a href="javascript:void(0)" 
   onclick="openErpModal('ratingModal')" 
   class="subnav-item" 
   title="Rating & Evaluasi Kepuasan">
    ⭐ rating
</a>

{{-- 20. SOP --}}
<a href="{{ route('admin.data', ['tab' => 'tab-sop']) }}" 
   class="subnav-item {{ request()->routeIs('admin.data') && request('tab') == 'tab-sop' ? 'active' : '' }}" 
   title="Standard Operating Procedure (SOP)">
    📋 sop
</a>

{{-- 21. HOME --}}
<a href="{{ route('admin.index') }}" 
   class="subnav-item {{ request()->routeIs('admin.index') || (request()->routeIs('admin.data') && !request('tab')) || request()->routeIs('admin.distribusi') || (request()->routeIs('admin.stok') && !request()->routeIs('admin.stok-limit')) || request()->routeIs('admin.laporan') || request()->is('bagus/admin') || request()->is('admin') ? 'active' : '' }}" 
   title="Dashboard Utama Admin">
    🏠 home
</a>

{{-- 22. BAGUS --}}
<a href="{{ route('admin.staff.profile') }}" 
   class="subnav-item {{ request()->routeIs('admin.staff.profile*') || request()->is('*staff/profile*') ? 'active' : '' }}" 
   title="Profil Staf & Manager Bagus">
    👤 Bagus
</a>
