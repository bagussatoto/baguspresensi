@extends('admin.dashboard.layout')

@section('title', 'Zonasi Harga & HPP Multi-Cabang')

@section('content')
<style>
    .modal-backdrop-custom {
        display: none; position: fixed; inset: 0; background: rgba(11, 17, 32, 0.85);
        backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-box-custom {
        background: #1e293b; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px;
        width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Zonasi Harga Jual & Estimasi HPP Multi-Cabang</h1>
        <p class="hero-desc-text">
            Memisahkan master fisik produk dengan kebijakan penetapan harga pasar. Atur harga jual dan estimasi HPP per kawasan geografis (zonasi) atau khusus untuk cabang tertentu.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" onclick="openAddPricingModal()" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
            <span>➕</span> Tetapkan Harga Zona / Cabang Baru
        </button>
        <a href="{{ route('admin.harga') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>🏷️</span> Pricelist Bertingkat
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.cabang.nav')

<!-- FILTER -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.cabang.zonasi-harga') }}" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <div>
            <select name="branch_id" class="form-input-custom" style="padding: 8px 12px;">
                <option value="all">🏢 Semua Cabang / Zona Umum</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="text" name="zone_code" value="{{ request('zone_code') }}" placeholder="Filter Kode Zona (misal: ZONE-PUSAT)..." class="form-input-custom" style="padding: 8px 12px; font-family: monospace;">
        </div>
        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 16px;">
            <span>🔍</span> Filter
        </button>
        @if(request()->hasAny(['branch_id', 'zone_code']))
            <a href="{{ route('admin.cabang.zonasi-harga') }}" class="btn-action-sm" style="padding: 8px 12px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Reset</a>
        @endif
    </form>
</div>

<!-- TABEL PRICINGS -->
<div class="dashboard-section-card" style="overflow: hidden;">
    <div class="section-card-header" style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 10px; border-radius: 10px; font-size: 18px;">💲</div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">Kebijakan Zonasi Harga Aktif</h3>
                <span style="font-size: 12px; color: #94a3b8;">Diferensiasi harga eceran berdasarkan lokasi operasional</span>
            </div>
        </div>
    </div>

    @if($pricings->isEmpty())
        <div style="padding: 50px 20px; text-align: center; color: #94a3b8;">
            <div style="font-size: 40px; margin-bottom: 12px;">🏷️</div>
            <h4 style="color: #f1f5f9; font-size: 16px; margin-bottom: 6px;">Belum Ada Kebijakan Zonasi Khusus</h4>
            <p style="font-size: 13px; max-width: 480px; margin: 0 auto 16px auto;">
                Sistem saat ini menggunakan harga dasar (*base price*) dari master katalog produk nasional. Anda dapat menambahkan harga khusus per zona wilayah atau cabang tertentu.
            </p>
            <button type="button" onclick="openAddPricingModal()" class="btn-action-sm btn-action-emerald" style="padding: 10px 20px;">
                <span>➕</span> Tetapkan Kebijakan Zonasi Sekarang
            </button>
        </div>
    @else
        <div class="table-responsive-wrapper">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Produk (SKU)</th>
                        <th>Kode Wilayah / Zona</th>
                        <th>Cabang Terkait</th>
                        <th style="text-align: right;">HPP Zona (*Base Cost*)</th>
                        <th style="text-align: right; color: #34d399;">Harga Jual (*Sale Price*)</th>
                        <th style="text-align: right;">Margin Estimasi</th>
                        <th>Periode Berlaku</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pricings as $idx => $p)
                        @php
                            $margin = $p->sale_price - $p->base_cost;
                            $marginPct = $p->sale_price > 0 ? ($margin / $p->sale_price) * 100 : 0;
                        @endphp
                        <tr>
                            <td style="color: #64748b; font-family: monospace;">{{ $pricings->firstItem() + $idx }}</td>
                            <td>
                                <div style="font-weight: 700; color: #f1f5f9;">{{ $p->variant?->name }}</div>
                                <div style="font-family: monospace; font-size: 11px; color: #38bdf8;">{{ $p->variant?->sku }}</div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 12px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                                    {{ $p->zone_code }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #cbd5e1;">
                                    {{ $p->branch?->name ?: '🌐 Berlaku Semua Cabang di Zona' }}
                                </span>
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 12px; color: #94a3b8;">
                                Rp {{ number_format($p->base_cost, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 13px; font-weight: 700; color: #34d399;">
                                Rp {{ number_format($p->sale_price, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 12px; color: {{ $margin >= 0 ? '#fbbf24' : '#fb7185' }};">
                                Rp {{ number_format($margin, 0, ',', '.') }} ({{ number_format($marginPct, 1) }}%)
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #f1f5f9;">Mulai: {{ $p->start_date ? date('d/m/Y', strtotime((string)$p->start_date)) : 'Langsung' }}</div>
                                <div style="font-size: 11px; color: #64748b;">Hingga: {{ $p->end_date ? date('d/m/Y', strtotime((string)$p->end_date)) : 'Seterusnya' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.06);">
            {{ $pricings->links() }}
        </div>
    @endif
</div>

<!-- MODAL TAMBAH ZONASI HARGA -->
<div class="modal-backdrop-custom" id="modalAddPricing">
    <div class="modal-box-custom">
        <div style="padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">➕ Tetapkan Kebijakan Harga Zona Baru</h3>
            <button type="button" onclick="closeAddPricingModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.cabang.zonasi-harga.store') }}" style="padding: 24px;">
            @csrf

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Pilih Produk / Varian:</label>
                <select name="product_variant_id" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    @foreach($variants as $v)
                        <option value="{{ $v->id }}">[{{ $v->sku }}] {{ $v->name }} (Rp {{ number_format($v->base_price, 0) }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Kode Zona Wilayah:</label>
                    <input type="text" name="zone_code" placeholder="misal: ZONE-JABODETABEK" required class="form-input-custom" style="width: 100%; padding: 8px 12px; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Cabang Khusus (Opsional):</label>
                    <select name="branch_id" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                        <option value="">🌐 Berlaku Semua Cabang di Zona Tersebut</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Estimasi HPP Zona (*Base Cost*):</label>
                    <input type="number" step="100" name="base_cost" placeholder="Rp..." required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #34d399; font-weight: 700; margin-bottom: 4px;">Harga Jual Konsumen (*Sale Price*):</label>
                    <input type="number" step="100" name="sale_price" placeholder="Rp..." required class="form-input-custom" style="width: 100%; padding: 8px 12px; border-color: rgba(52, 211, 153, 0.4);">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Tanggal Mulai Berlaku:</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">Tanggal Berakhir (Opsional):</label>
                    <input type="date" name="end_date" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAddPricingModal()" class="btn-action-sm" style="padding: 10px 18px; background: rgba(255,255,255,0.1); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 24px;">Simpan Kebijakan Harga</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddPricingModal() {
        document.getElementById('modalAddPricing').classList.add('open');
    }
    function closeAddPricingModal() {
        document.getElementById('modalAddPricing').classList.remove('open');
    }
</script>
@endsection
