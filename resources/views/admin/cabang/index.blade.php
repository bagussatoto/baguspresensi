@extends('admin.dashboard.layout')

@section('title', 'Jaringan Cabang & Distribution Center (MDM)')

@section('content')
<style>
    .badge-hq { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-dc { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-store { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    
    .modal-backdrop-custom {
        display: none; position: fixed; inset: 0; background: rgba(11, 17, 32, 0.85);
        backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-box-custom {
        background: #1e293b; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px;
        width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Jaringan Cabang & Distribution Center (DC)</h1>
        <p class="hero-desc-text">
            Arsitektur Master Data Management (MDM) Hybrid Hub-and-Spoke. Kelola hierarki Kantor Pusat (HQ), Pusat Distribusi Logistik (DC), dan Jaringan Toko/Outlet Ritel (Store).
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.cabang.konsolidasi') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px; background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8;">
            <span>📊</span> Dashboard Konsolidasi
        </a>
        <button type="button" onclick="openAddBranchModal()" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
            <span>➕</span> Daftarkan Cabang / DC Baru
        </button>
        <a href="{{ route('admin.cabang.produk') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📍</span> Lokalisasi Rak & ROP
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.cabang.nav')

<!-- STAT CARDS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Titik Jaringan</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #38bdf8; margin-top: 6px;">
            {{ $stats['total'] }} Unit
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $stats['active'] }} cabang aktif operasional</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Kantor Pusat (HQ)</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #c084fc; margin-top: 6px;">
            {{ $stats['hq'] }} HQ
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Pusat kontrol finansial & MDM</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Distribution Center (DC)</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #38bdf8; margin-top: 6px;">
            {{ $stats['dc'] }} DC
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Gudang pasok masal ke cabang</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Toko / Outlet Ritel (Store)</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #34d399; margin-top: 6px;">
            {{ $stats['store'] }} Toko
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Titik penjualan POS & kasir</div>
    </div>
</div>

<!-- FILTER & SEARCH -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.cabang') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode cabang, nama outlet, atau alamat..." class="form-input-custom" style="width: 100%; padding: 8px 14px;">
        </div>
        <div>
            <select name="type" class="form-input-custom" style="padding: 8px 12px;">
                <option value="all" {{ request('type') === 'all' ? 'selected' : '' }}>Semua Tipe Entitas</option>
                <option value="HQ" {{ request('type') === 'HQ' ? 'selected' : '' }}>Kantor Pusat (HQ)</option>
                <option value="DC" {{ request('type') === 'DC' ? 'selected' : '' }}>Pusat Distribusi (DC)</option>
                <option value="STORE" {{ request('type') === 'STORE' ? 'selected' : '' }}>Toko Ritel (Store)</option>
            </select>
        </div>
        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 16px;">
            <span>🔍</span> Filter
        </button>
        @if(request()->hasAny(['q', 'type']))
            <a href="{{ route('admin.cabang') }}" class="btn-action-sm" style="padding: 8px 12px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Reset</a>
        @endif
    </form>
</div>

<!-- TABEL CABANG -->
<div class="dashboard-section-card" style="overflow: hidden;">
    <div class="section-card-header" style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 10px; border-radius: 10px; font-size: 18px;">🏢</div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">Daftar Titik Entitas Cabang & DC</h3>
                <span style="font-size: 12px; color: #94a3b8;">Struktur hierarki jaringan bisnis Maya Graha Kencana</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Kode & Nama Cabang</th>
                    <th>Tipe Entitas MDM</th>
                    <th>Gudang Fisik</th>
                    <th>Staf Aktif</th>
                    <th>Alokasi Produk (Spoke)</th>
                    <th>Alamat & Geofence</th>
                    <th style="text-align: right; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $index => $b)
                    <tr>
                        <td style="color: #64748b; font-family: monospace;">{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 700; color: #f1f5f9; font-size: 14px;">{{ $b->name }}</div>
                            <div style="font-family: monospace; font-size: 11px; color: #38bdf8;">{{ $b->code }}</div>
                        </td>
                        <td>
                            @php
                                $typeVal = $b->type?->value ?? (string)$b->type;
                            @endphp
                            @if($typeVal === 'HQ' || $b->is_headquarters)
                                <span class="badge-hq">🏛️ HQ - Kantor Pusat</span>
                            @elseif($typeVal === 'DC')
                                <span class="badge-dc">📦 DC - Distribution Center</span>
                            @else
                                <span class="badge-store">🏪 STORE - Outlet Ritel</span>
                            @endif
                        </td>
                        <td>
                            <span style="color: #cbd5e1; font-weight: 600;">{{ $b->warehouses->count() }} Gudang</span>
                            <div style="font-size: 11px; color: #64748b;">
                                {{ $b->warehouses->pluck('name')->take(2)->join(', ') ?: 'Belum ada gudang' }}
                            </div>
                        </td>
                        <td>
                            <span style="color: #cbd5e1; font-weight: 600;">{{ $b->employees_count }} Karyawan</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.cabang.produk', ['branch_id' => $b->id]) }}" style="color: #38bdf8; text-decoration: none; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                <span>📍</span> {{ $b->branch_products_count }} Produk Dialokasikan
                            </a>
                        </td>
                        <td>
                            <div style="font-size: 12px; color: #cbd5e1; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $b->address ?: 'Alamat belum diatur' }}
                            </div>
                            <div style="font-size: 10px; color: #64748b;">
                                {{ $b->latitude && $b->longitude ? "GPS: {$b->latitude}, {$b->longitude} (R={$b->radius_meters}m)" : "Geofence belum diatur" }}
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <button type="button" onclick='openEditBranchModal(@json($b))' class="btn-action-sm btn-action-primary" style="padding: 5px 10px; font-size: 11px;" title="Edit Profil Cabang">
                                    ✏️
                                </button>
                                <a href="{{ route('admin.cabang.produk', ['branch_id' => $b->id]) }}" class="btn-action-sm btn-action-emerald" style="padding: 5px 10px; font-size: 11px;" title="Kelola Lokalisasi Produk">
                                    📍
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                            Belum ada titik cabang atau DC yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH CABANG -->
<div class="modal-backdrop-custom" id="modalAddBranch">
    <div class="modal-box-custom">
        <div style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">➕ Daftarkan Cabang / DC Baru</h3>
            <button type="button" onclick="closeAddBranchModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.cabang.store') }}" style="padding: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Kode Cabang (Unique):</label>
                    <input type="text" name="code" placeholder="misal: DC-JKT01, BR-BDG01" required class="form-input-custom" style="width: 100%; padding: 8px 12px; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Nama Lengkap Cabang / Outlet:</label>
                    <input type="text" name="name" placeholder="misal: Distribution Center Pusat Jakarta" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; color: #38bdf8; font-weight: 600; margin-bottom: 4px;">Tipe Entitas MDM Multi-Cabang:</label>
                <select name="type" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="STORE">🏪 STORE — Toko Ritel Kasir / Outlet Warnet (Unit Penjualan Akhir)</option>
                    <option value="DC">📦 DC — Distribution Center (Pusat Pasokan Logistik Masal)</option>
                    <option value="HQ">🏛️ HQ — Kantor Pusat (Pusat Tata Kelola Data & Finansial)</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Alamat Fisik Bangunan:</label>
                <textarea name="address" rows="2" placeholder="Alamat jalan, nomor, kecamatan, kota..." class="form-input-custom" style="width: 100%; padding: 8px 12px;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Latitude GPS:</label>
                    <input type="number" step="any" name="latitude" placeholder="-6.200000" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Longitude GPS:</label>
                    <input type="number" step="any" name="longitude" placeholder="106.816666" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Radius Geofence (m):</label>
                    <input type="number" name="radius_meters" value="100" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">SSID Wi-Fi Resmi:</label>
                    <input type="text" name="wifi_ssid" placeholder="MGK-OFFICIAL-WIFI" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Nomor Telepon PIC:</label>
                    <input type="text" name="phone" placeholder="0812xxxxxxxx" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAddBranchModal()" class="btn-action-sm" style="padding: 10px 18px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 24px;">Simpan Titik Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT CABANG -->
<div class="modal-backdrop-custom" id="modalEditBranch">
    <div class="modal-box-custom">
        <div style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">✏️ Edit Data Titik Cabang / DC</h3>
            <button type="button" onclick="closeEditBranchModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" id="formEditBranch" style="padding: 24px;">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Kode Cabang:</label>
                    <input type="text" name="code" id="edit_code" required class="form-input-custom" style="width: 100%; padding: 8px 12px; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Nama Cabang / Outlet:</label>
                    <input type="text" name="name" id="edit_name" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; color: #38bdf8; font-weight: 600; margin-bottom: 4px;">Tipe Entitas MDM Multi-Cabang:</label>
                <select name="type" id="edit_type" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="STORE">🏪 STORE — Toko Ritel Kasir / Outlet Warnet</option>
                    <option value="DC">📦 DC — Distribution Center (Pusat Distribusi)</option>
                    <option value="HQ">🏛️ HQ — Kantor Pusat (Pusat Kontrol Finansial)</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Alamat Fisik:</label>
                <textarea name="address" id="edit_address" rows="2" class="form-input-custom" style="width: 100%; padding: 8px 12px;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Latitude GPS:</label>
                    <input type="number" step="any" name="latitude" id="edit_latitude" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Longitude GPS:</label>
                    <input type="number" step="any" name="longitude" id="edit_longitude" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Radius Geofence (m):</label>
                    <input type="number" name="radius_meters" id="edit_radius" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">SSID Wi-Fi Resmi:</label>
                    <input type="text" name="wifi_ssid" id="edit_wifi_ssid" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Nomor Telepon:</label>
                    <input type="text" name="phone" id="edit_phone" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditBranchModal()" class="btn-action-sm" style="padding: 10px 18px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 24px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddBranchModal() {
        document.getElementById('modalAddBranch').classList.add('open');
    }
    function closeAddBranchModal() {
        document.getElementById('modalAddBranch').classList.remove('open');
    }
    function openEditBranchModal(b) {
        document.getElementById('formEditBranch').action = `/admin/cabang/${b.id}`;
        document.getElementById('edit_code').value = b.code || '';
        document.getElementById('edit_name').value = b.name || '';
        document.getElementById('edit_type').value = b.type || (b.is_headquarters ? 'HQ' : 'STORE');
        document.getElementById('edit_address').value = b.address || '';
        document.getElementById('edit_latitude').value = b.latitude || '';
        document.getElementById('edit_longitude').value = b.longitude || '';
        document.getElementById('edit_radius').value = b.radius_meters || 100;
        document.getElementById('edit_wifi_ssid').value = b.wifi_ssid || '';
        document.getElementById('edit_phone').value = b.phone || '';
        document.getElementById('modalEditBranch').classList.add('open');
    }
    function closeEditBranchModal() {
        document.getElementById('modalEditBranch').classList.remove('open');
    }
</script>
@endsection

