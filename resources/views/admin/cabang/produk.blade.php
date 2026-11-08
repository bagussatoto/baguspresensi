@extends('admin.dashboard.layout')

@section('title', 'Lokalisasi Produk & ROP Cabang')

@section('content')
<style>
    .rop-alert-badge {
        background: rgba(244, 63, 94, 0.2);
        color: #fb7185;
        border: 1px solid rgba(244, 63, 94, 0.4);
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        animation: pulse 2s infinite;
    }
    .rop-safe-badge {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
    }
    .modal-backdrop-custom {
        display: none; position: fixed; inset: 0; background: rgba(11, 17, 32, 0.85);
        backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-box-custom {
        background: #1e293b; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px;
        width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Lokalisasi Rak & Reorder Point (ROP) Cabang</h1>
        <p class="hero-desc-text">
            Sisi <em>Spoke</em> dari arsitektur MDM. Konfigurasikan penempatan rak (*bin location*), batas kapasitas simpan, dan ambang batas *Reorder Point* (ROP) pemicu pasokan ulang dari DC untuk setiap outlet.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        @if($selectedBranch)
            <form method="POST" action="{{ route('admin.cabang.sync-produk', $selectedBranch->id) }}" onsubmit="return confirm('Alokasikan seluruh katalog produk nasional ke cabang {{ $selectedBranch->name }}?');">
                @csrf
                <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
                    <span>⚡</span> Sinkronisasi Produk Nasional
                </button>
            </form>
        @endif
        <a href="{{ route('admin.distribusi') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>🚚</span> Buat Transfer dari DC
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.cabang.nav')

<!-- PILIH CABANG AKTIF & FILTER -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.cabang.produk') }}" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; font-weight: 700; color: #38bdf8;">🏢 Pilih Cabang / Outlet:</label>
            <select name="branch_id" onchange="this.form.submit()" class="form-input-custom" style="padding: 8px 14px; font-weight: 700;">
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                        [{{ $b->type?->value ?? 'STORE' }}] {{ $b->name }} ({{ $b->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama barang, SKU, atau barcode..." class="form-input-custom" style="width: 100%; padding: 8px 14px;">
        </div>

        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 16px;">
            <span>🔍</span> Cari
        </button>
        @if(request()->filled('q'))
            <a href="{{ route('admin.cabang.produk', ['branch_id' => $selectedBranchId]) }}" class="btn-action-sm" style="padding: 8px 12px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Reset</a>
        @endif
    </form>
</div>

<!-- STAT STATUS ROP -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Produk Teralokasi</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #38bdf8; margin-top: 6px;">
            {{ $branchProducts->total() }} Varian
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Tersedia di {{ $selectedBranch?->name }}</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid {{ $ropAlertCount > 0 ? 'rgba(244, 63, 94, 0.4)' : 'rgba(16, 185, 129, 0.2)' }}; border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Alert Reorder Point (ROP)</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: {{ $ropAlertCount > 0 ? '#fb7185' : '#34d399' }}; margin-top: 6px;">
            {{ $ropAlertCount }} Produk
        </div>
        <div style="font-size: 11px; color: {{ $ropAlertCount > 0 ? '#fb7185' : '#64748b' }}; margin-top: 4px;">
            {{ $ropAlertCount > 0 ? '⚠️ Butuh dibuatkan STO transfer dari DC' : 'Stok di cabang masih aman' }}
        </div>
    </div>
</div>

<!-- TABEL LOKALISASI PRODUK CABANG -->
<div class="dashboard-section-card" style="overflow: hidden;">
    <div class="section-card-header" style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 10px; border-radius: 10px; font-size: 18px;">📍</div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">Parameter Lokalisasi: {{ $selectedBranch?->name }}</h3>
                <span style="font-size: 12px; color: #94a3b8;">Penempatan rak, batas persediaan, dan ROP pemicu suplai DC</span>
            </div>
        </div>
    </div>

    @if($branchProducts->isEmpty())
        <div style="padding: 50px 20px; text-align: center; color: #94a3b8;">
            <div style="font-size: 40px; margin-bottom: 12px;">📭</div>
            <h4 style="color: #f1f5f9; font-size: 16px; margin-bottom: 6px;">Belum Ada Produk yang Dialokasikan ke Cabang Ini</h4>
            <p style="font-size: 13px; max-width: 460px; margin: 0 auto 16px auto;">
                Klik tombol di bawah untuk menyalin seluruh daftar katalog produk nasional (The Hub) ke cabang ini secara otomatis.
            </p>
            @if($selectedBranch)
                <form method="POST" action="{{ route('admin.cabang.sync-produk', $selectedBranch->id) }}">
                    @csrf
                    <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 20px;">
                        <span>⚡</span> Alokasikan Semua Produk Sekarang
                    </button>
                </form>
            @endif
        </div>
    @else
        <div class="table-responsive-wrapper">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode SKU & Barcode</th>
                        <th>Nama Produk (Varian)</th>
                        <th>Posisi Rak (*Bin*)</th>
                        <th style="text-align: right;">Min Stok</th>
                        <th style="text-align: right;">Max Stok</th>
                        <th style="text-align: right; color: #fbbf24;">ROP (*Reorder*)</th>
                        <th style="text-align: right; color: #38bdf8;">Stok Tersedia</th>
                        <th>Status Suplai</th>
                        <th style="text-align: right; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branchProducts as $idx => $bp)
                        @php
                            $variant = $bp->variant;
                            $stockInfo = $stockMap[$bp->product_variant_id] ?? ['qty_on_hand' => 0, 'qty_reserved' => 0, 'qty_available' => 0];
                            $currAvail = (float)$stockInfo['qty_available'];
                            $isRop = $bp->needsReorder($currAvail);
                        @endphp
                        <tr>
                            <td style="color: #64748b; font-family: monospace;">
                                {{ $branchProducts->firstItem() + $idx }}
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 12px; font-weight: 700; color: #38bdf8;">
                                    {{ $variant?->sku ?? '—' }}
                                </div>
                                <div style="font-family: monospace; font-size: 10px; color: #64748b;">
                                    {{ $variant?->barcode ?: 'No Barcode' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #f1f5f9;">
                                    {{ $variant?->name ?? '—' }}
                                </div>
                                <div style="font-size: 11px; color: #64748b;">
                                    {{ $variant?->product?->category?->name ?? 'Uncategorized' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 12px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(56, 189, 248, 0.2);">
                                    📍 {{ $bp->bin_location ?: 'Belum diatur' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 12px; color: #cbd5e1;">
                                {{ number_format($bp->min_stock, 0) }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 12px; color: #cbd5e1;">
                                {{ number_format($bp->max_stock, 0) }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 12px; font-weight: 700; color: #fbbf24;">
                                {{ number_format($bp->reorder_point, 0) }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 12px; font-weight: 800; color: {{ $currAvail > 0 ? '#38bdf8' : '#fb7185' }};">
                                {{ number_format($currAvail, 0) }}
                                <div style="font-size: 10px; color: #64748b;">(Fisik: {{ $stockInfo['qty_on_hand'] }})</div>
                            </td>
                            <td>
                                @if($isRop)
                                    <span class="rop-alert-badge">⚠️ Butuh Transfer DC</span>
                                @else
                                    <span class="rop-safe-badge">✅ Persediaan Aman</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <button type="button" onclick='openEditSpokeModal(@json($bp), @json($variant?->name))' class="btn-action-sm btn-action-primary" style="padding: 5px 10px; font-size: 11px;">
                                    ✏️ Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.06);">
            {{ $branchProducts->links() }}
        </div>
    @endif
</div>

<!-- MODAL EDIT PARAMETER LOKALISASI -->
<div class="modal-backdrop-custom" id="modalEditSpoke">
    <div class="modal-box-custom">
        <div style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">✏️ Konfigurasi Lokalisasi Produk</h3>
                <span id="modal_product_name" style="font-size: 12px; color: #38bdf8;"></span>
            </div>
            <button type="button" onclick="closeEditSpokeModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" id="formEditSpoke" style="padding: 24px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">Posisi Rak / Lokasi Penyimpanan (*Bin Location*):</label>
                <input type="text" name="bin_location" id="spoke_bin" placeholder="misal: Lorong A - Rak 03" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                <span style="font-size: 11px; color: #64748b;">Memudahkan rute pengambilan (*picking*) kasir & petugas gudang.</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Stok Minimum:</label>
                    <input type="number" step="1" name="min_stock" id="spoke_min" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Kapasitas Max:</label>
                    <input type="number" step="1" name="max_stock" id="spoke_max" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #fbbf24; font-weight: 700; margin-bottom: 4px;">Reorder Point (ROP):</label>
                    <input type="number" step="1" name="reorder_point" id="spoke_rop" required class="form-input-custom" style="width: 100%; padding: 8px 12px; border-color: rgba(251, 191, 36, 0.4);">
                </div>
            </div>

            <div style="background: rgba(30, 41, 59, 0.6); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 12px; color: #94a3b8;">
                💡 <strong>Catatan ROP:</strong> Ketika sisa unit stok di cabang ini menyentuh atau kurang dari angka Reorder Point, sistem otomatis menandai status <em>Alert</em> agar DC mengirimkan stok baru.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditSpokeModal()" class="btn-action-sm" style="padding: 10px 18px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 24px;">Simpan Parameter Cabang</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditSpokeModal(bp, name) {
        document.getElementById('formEditSpoke').action = `/admin/cabang/produk/${bp.id}`;
        document.getElementById('modal_product_name').innerText = name || 'Varian Produk';
        document.getElementById('spoke_bin').value = bp.bin_location || '';
        document.getElementById('spoke_min').value = bp.min_stock || 0;
        document.getElementById('spoke_max').value = bp.max_stock || 0;
        document.getElementById('spoke_rop').value = bp.reorder_point || 0;
        document.getElementById('modalEditSpoke').classList.add('open');
    }
    function closeEditSpokeModal() {
        document.getElementById('modalEditSpoke').classList.remove('open');
    }
</script>
@endsection

