@extends('admin.dashboard.layout')

@section('title', 'Lembar Audit Hitung Fisik Stock Opname: ' . $stockOpname->opname_number)

@section('content')
<style>
    .badge-opname-draft { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-opname-completed { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-opname-adjusted { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }

    .diff-match { color: #34d399; font-weight: 700; }
    .diff-minus { color: #f43f5e; font-weight: 700; background: rgba(244, 63, 94, 0.1); padding: 2px 6px; border-radius: 4px; }
    .diff-plus { color: #38bdf8; font-weight: 700; background: rgba(56, 189, 248, 0.1); padding: 2px 6px; border-radius: 4px; }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card" style="margin-bottom: 20px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <a href="{{ route('admin.cabang.opname') }}" style="color: #94a3b8; text-decoration: none; font-size: 13px;">&larr; Kembali ke Riwayat Opname</a>
            <span style="color: #64748b;">•</span>
            @if($stockOpname->status === 'draft')
                <span class="badge-opname-draft">📝 DRAFT (PROSES HITUNG)</span>
            @elseif($stockOpname->status === 'completed')
                <span class="badge-opname-completed">⏳ COMPLETED (MENUNGGU APPROVAL)</span>
            @else
                <span class="badge-opname-adjusted">✅ ADJUSTED (STOK SINKRON)</span>
            @endif
        </div>
        <h1 class="hero-title-text" style="font-family: monospace; font-size: 24px; color: #38bdf8;">
            {{ $stockOpname->opname_number }}
        </h1>
        <p class="hero-desc-text" style="margin-top: 4px;">
            Cabang: <strong>{{ $stockOpname->branch->name ?? '-' }}</strong> &bull; Gudang: <strong>{{ $stockOpname->warehouse->name ?? '-' }}</strong> &bull; Tanggal Audit: <strong>{{ $stockOpname->opname_date->format('d F Y') }}</strong>
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <a href="{{ route('admin.cabang.opname.export-csv', $stockOpname->id) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(255,255,255,0.08); color: #cbd5e1; text-decoration: none;">
            <span>📥</span> Unduh Berita Acara (CSV)
        </a>

        @if(! $stockOpname->isAdjusted())
            <form action="{{ route('admin.cabang.opname.adjust', $stockOpname->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengesahkan dan menyesuaikan stok buku sistem dengan hasil hitung fisik ini? Perubahan stok inventaris tidak dapat dibatalkan.');">
                @csrf
                <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px; background: linear-gradient(135deg, #10b981, #059669); border: none;">
                    <span>⚡</span> Otorisasi & Sesuaikan Stok Sistem
                </button>
            </form>
        @else
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                ✓ Disetujui oleh: {{ $stockOpname->approver->name ?? 'Supervisor' }} ({{ $stockOpname->approved_at ? $stockOpname->approved_at->format('d/m/Y H:i') : '-' }})
            </div>
        @endif
    </div>
</div>

<!-- AUDIT SUMMARY STATS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; padding: 16px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total SKU Dihitung</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #38bdf8; margin-top: 4px;">
            {{ count($stockOpname->items) }} Item
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Cakupan rak persediaan cabang</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 14px; padding: 16px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">SKU Terdapat Selisih</div>
        <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: {{ $stockOpname->total_discrepancy_items > 0 ? '#f43f5e' : '#34d399' }}; margin-top: 4px;">
            {{ $stockOpname->total_discrepancy_items }} SKU
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Varian fisik vs saldo sistem</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 14px; padding: 16px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Estimasi Dampak Nilai Varian</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: {{ $stockOpname->total_variance_cost < 0 ? '#f43f5e' : ($stockOpname->total_variance_cost > 0 ? '#38bdf8' : '#34d399') }}; margin-top: 6px;">
            Rp {{ number_format($stockOpname->total_variance_cost, 0, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Valuasi HPP zonasi cabang</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 16px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Catatan Audit Lapangan</div>
        <div style="font-size: 12px; color: #cbd5e1; margin-top: 4px; line-height: 1.4;">
            {{ $stockOpname->notes ?: 'Tidak ada catatan khusus.' }}
        </div>
    </div>
</div>

<!-- FORM AUDIT FISIK PER RAK -->
<form action="{{ route('admin.cabang.opname.update-counts', $stockOpname->id) }}" method="POST">
    @csrf
    <div class="data-table-container" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; overflow: hidden; margin-bottom: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>📍</span> Lembar Penghitungan Fisik per Rak (Bin Location)
                </h3>
                <span style="font-size: 12px; color: #64748b;">Masukkan kuantitas fisik riil hasil hitungan lapangan ke kolom "Hitung Fisik".</span>
            </div>

            @if(! $stockOpname->isAdjusted())
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 9px 20px; font-size: 13px;">
                    <span>💾</span> Simpan Hasil Hitung Fisik
                </button>
            @endif
        </div>

        <div style="overflow-x: auto;">
            <table class="inventory-table" style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.08); color: #94a3b8; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px 16px; width: 40px;">No</th>
                        <th style="padding: 12px 16px;">Lokasi Rak (Bin)</th>
                        <th style="padding: 12px 16px;">SKU & Produk</th>
                        <th style="padding: 12px 16px; text-align: right;">Stok Sistem (Buku)</th>
                        <th style="padding: 12px 16px; text-align: center; width: 140px;">Hasil Hitung Fisik</th>
                        <th style="padding: 12px 16px; text-align: right;">Varian (Selisih)</th>
                        <th style="padding: 12px 16px; text-align: right;">Estimasi HPP Satuan</th>
                        <th style="padding: 12px 16px; text-align: right;">Total Nilai Varian</th>
                        <th style="padding: 12px 16px; width: 200px;">Catatan Lapangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockOpname->items as $index => $item)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td style="padding: 12px 16px; color: #64748b; font-family: monospace;">
                                {{ $index + 1 }}
                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                            </td>
                            <td style="padding: 12px 16px;">
                                <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 11px;">
                                    📍 {{ $item->bin_location ?: 'Gudang Utama' }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #f8fafc;">
                                    {{ $item->variant->name ?? 'SKU #' . $item->product_variant_id }}
                                </div>
                                <div style="font-family: monospace; font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                    {{ $item->variant->sku ?? '-' }} &bull; {{ $item->variant->product->category->name ?? 'Produk Ritel' }}
                                </div>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-family: monospace; font-weight: 700; color: #cbd5e1;">
                                {{ $item->qty_system }}
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                @if(! $stockOpname->isAdjusted())
                                    <input type="number" 
                                           name="items[{{ $index }}][qty_physical]" 
                                           value="{{ $item->qty_physical }}" 
                                           min="0"
                                           data-system="{{ $item->qty_system }}"
                                           data-cost="{{ $item->unit_cost }}"
                                           data-index="{{ $index }}"
                                           oninput="recalcRow(this)"
                                           style="width: 100px; text-align: center; font-family: monospace; font-weight: 700; font-size: 14px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 6px 8px; border-radius: 6px;">
                                @else
                                    <span style="font-family: monospace; font-weight: 800; font-size: 14px; color: #38bdf8;">
                                        {{ $item->qty_physical }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-family: monospace;" id="var-col-{{ $index }}">
                                @if($item->qty_variance === 0)
                                    <span class="diff-match">✓ 0</span>
                                @elseif($item->qty_variance < 0)
                                    <span class="diff-minus">{{ $item->qty_variance }}</span>
                                @else
                                    <span class="diff-plus">+{{ $item->qty_variance }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-family: monospace; color: #94a3b8;">
                                Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-family: monospace; font-weight: 700;" id="cost-col-{{ $index }}">
                                @if($item->total_variance_cost < 0)
                                    <span style="color: #f43f5e;">Rp {{ number_format($item->total_variance_cost, 0, ',', '.') }}</span>
                                @elseif($item->total_variance_cost > 0)
                                    <span style="color: #38bdf8;">+Rp {{ number_format($item->total_variance_cost, 0, ',', '.') }}</span>
                                @else
                                    <span style="color: #94a3b8;">Rp 0</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
                                @if(! $stockOpname->isAdjusted())
                                    <input type="text" 
                                           name="items[{{ $index }}][notes]" 
                                           value="{{ $item->notes }}" 
                                           placeholder="Keterangan..."
                                           style="width: 100%; font-size: 12px; background: #0f172a; border: 1px solid #334155; color: #cbd5e1; padding: 6px 8px; border-radius: 6px;">
                                @else
                                    <span style="font-size: 12px; color: #94a3b8;">{{ $item->notes ?: '-' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 30px; text-align: center; color: #64748b;">
                                Tidak ada item stok yang tercatat di gudang cabang ini saat inisiasi sesi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(! $stockOpname->isAdjusted() && count($stockOpname->items) > 0)
            <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end; gap: 12px;">
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 24px; font-size: 14px;">
                    <span>💾</span> Simpan Hasil Hitung Fisik Lapangan
                </button>
            </div>
        @endif
    </div>
</form>

<script>
    function recalcRow(input) {
        const index = input.getAttribute('data-index');
        const system = parseInt(input.getAttribute('data-system')) || 0;
        const cost = parseFloat(input.getAttribute('data-cost')) || 0;
        const physical = parseInt(input.value) || 0;

        const diff = physical - system;
        const diffCost = diff * cost;

        const varCol = document.getElementById('var-col-' + index);
        const costCol = document.getElementById('cost-col-' + index);

        if (diff === 0) {
            varCol.innerHTML = '<span class="diff-match">✓ 0</span>';
            costCol.innerHTML = '<span style="color: #94a3b8;">Rp 0</span>';
        } else if (diff < 0) {
            varCol.innerHTML = '<span class="diff-minus">' + diff + '</span>';
            costCol.innerHTML = '<span style="color: #f43f5e;">Rp ' + diffCost.toLocaleString('id-ID') + '</span>';
        } else {
            varCol.innerHTML = '<span class="diff-plus">+' + diff + '</span>';
            costCol.innerHTML = '<span style="color: #38bdf8;">+Rp ' + diffCost.toLocaleString('id-ID') + '</span>';
        }
    }
</script>
@endsection
