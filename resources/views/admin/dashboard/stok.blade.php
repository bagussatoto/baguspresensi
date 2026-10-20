@extends('admin.dashboard.layout')

@section('title', '3. Stok (Ketersediaan Produk)')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">📦</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">3. Stok (Ketersediaan Produk)</h1>
                <span class="section-subtitle">Ketersediaan Stok Kertas Print, Minuman Showcase, Makanan Etalase, & Hardware PC</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.stok-limit') }}" class="btn-action-sm btn-action-primary" style="padding: 8px 14px;">
                <span>⚙️</span> Kelola Stok Limit ERP
            </a>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="background: rgba(15, 23, 42, 0.6); padding: 16px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Varian SKU</div>
                <div style="font-size: 24px; font-weight: 800; color: #fff; font-family: 'Outfit'; margin-top: 4px;">{{ $stockStats['total_sku'] }} SKU</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Terdata dalam master katalog</div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Stok Aman (Di Atas Batas)</div>
                <div style="font-size: 24px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $stockStats['safe_stock'] }} SKU</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Persediaan aman melayani pelanggan</div>
            </div>

            <div style="background: rgba(245, 158, 11, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Stok Menipis (Warning)</div>
                <div style="font-size: 24px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $stockStats['low_stock'] }} SKU</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Segera lakukan restock distribusi</div>
            </div>

            <div style="background: rgba(244, 63, 94, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(244, 63, 94, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fb7185; font-weight: 700;">Stok Habis (Kosong)</div>
                <div style="font-size: 24px; font-weight: 800; color: #fb7185; font-family: 'Outfit'; margin-top: 4px;">{{ $stockStats['out_of_stock'] }} SKU</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Perlu pengiriman darurat</div>
            </div>
        </div>
    </div>

    <div class="section-card-body">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> Rincian Inventaris Ketersediaan Fisik Produk
            </h3>
        </div>

        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
            <table class="custom-data-table">
                <thead>
                    <tr>
                        <th>SKU / Kode</th>
                        <th>Nama Produk & Varian</th>
                        <th>Kategori</th>
                        <th>Lokasi Penyimpanan</th>
                        <th>Stok Tersedia (Qty)</th>
                        <th>Safety Stock (Batas Aman)</th>
                        <th>Status Ketersediaan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockInventories as $si)
                    <tr>
                        <td style="font-family: monospace; font-size: 11px; color: #94a3b8;">{{ $si->variant->sku ?? '-' }}</td>
                        <td style="font-weight: 600; color: #fff;">
                            {{ $si->variant->name ?? 'Produk' }}
                        </td>
                        <td>
                            <span class="badge-status badge-sky" style="font-size: 10px;">
                                {{ $si->variant->product->category->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td>{{ $si->warehouse->name ?? 'Etalase Kasir' }}</td>
                        <td style="font-weight: 800; font-family: 'Outfit'; font-size: 15px; color: {{ $si->qty_on_hand <= $si->safety_stock ? '#fbbf24' : '#34d399' }};">
                            {{ $si->qty_on_hand }}
                        </td>
                        <td style="color: var(--text-muted);">{{ $si->safety_stock }} pcs</td>
                        <td>
                            @if($si->qty_on_hand <= 0)
                                <span class="badge-status badge-rose">Habis (0 pcs)</span>
                            @elseif($si->qty_on_hand <= $si->safety_stock)
                                <span class="badge-status badge-amber">⚠️ Stok Menipis</span>
                            @else
                                <span class="badge-status badge-emerald">🟢 Stok Aman</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">
                            Belum ada data inventaris stok yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
