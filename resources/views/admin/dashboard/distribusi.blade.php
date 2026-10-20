@extends('admin.dashboard.layout')

@section('title', '2. Penerimaan Stok Distribusi')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">🚚</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">2. Penerimaan Stok Distribusi</h1>
                <span class="section-subtitle">Daftar Pengiriman Stok dari Gudang Pusat & Permintaan Restock dari Kasir</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <span class="badge-status {{ $stockTransfersCount > 0 ? 'badge-amber' : 'badge-emerald' }}">
                {{ $stockTransfersCount }} Batch Menunggu Penerimaan
            </span>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="background: rgba(15, 23, 42, 0.6); padding: 16px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Batch Pengiriman</div>
                <div style="font-size: 24px; font-weight: 800; color: #fff; font-family: 'Outfit'; margin-top: 4px;">{{ $stockTransfers->count() }} Batch</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Tercatat pada sistem logistik</div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Sudah Diterima Kasir</div>
                <div style="font-size: 24px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $stockTransfers->where('status', 'received')->count() }} Batch</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Fisik stok telah diverifikasi</div>
            </div>

            <div style="background: rgba(245, 158, 11, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Dalam Pengiriman (Pending)</div>
                <div style="font-size: 24px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $stockTransfersCount }} Batch</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Menunggu tombol konfirmasi terima</div>
            </div>

            <div style="background: rgba(56, 189, 248, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">Permintaan Restock Kasir</div>
                <div style="font-size: 24px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin-top: 4px;">{{ $stockRequests->count() }} Permohonan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Diajukan oleh staf shift</div>
            </div>
        </div>
    </div>

    <div class="section-card-body">
        <!-- TABEL 1: PENGIRIMAN STOK DARI GUDANG PUSAT -->
        <div style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                    <span>📦</span> Pengiriman Stok dari Gudang Pusat (Transfer Masuk)
                </h3>
            </div>

            <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Kode Transfer</th>
                            <th>Item / Varian Produk</th>
                            <th>Dari Gudang</th>
                            <th>Tujuan (Loker Kasir)</th>
                            <th>Jumlah (Qty)</th>
                            <th>Waktu Pengiriman</th>
                            <th>Status Penerimaan</th>
                            <th style="text-align: right;">Aksi Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockTransfers as $st)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">{{ $st->transfer_code }}</td>
                            <td style="font-weight: 600; color: #fff;">
                                {{ $st->variant_name ?? 'Item Produk' }}
                                <div style="font-size: 10px; color: var(--text-sub); font-family: monospace;">{{ $st->variant_sku ?? '-' }}</div>
                            </td>
                            <td>{{ $st->from_warehouse_name ?? 'Gudang Pusat' }}</td>
                            <td>{{ $st->to_warehouse_name ?? 'Loker Kasir' }}</td>
                            <td style="font-weight: 700; color: #34d399; font-family: 'Outfit'; font-size: 15px;">
                                +{{ $st->qty }} pcs
                            </td>
                            <td style="font-size: 11px; color: var(--text-muted);">
                                {{ $st->dispatched_at ? \Carbon\Carbon::parse($st->dispatched_at)->format('d M Y H:i') : '-' }}
                            </td>
                            <td>
                                @if($st->status === 'received')
                                    <span class="badge-status badge-emerald">✅ Diterima</span>
                                @else
                                    <span class="badge-status badge-amber">🚚 Dalam Pengiriman</span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                @if($st->status !== 'received')
                                <form action="{{ route('admin.distribusi.terima', $st->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-action-sm btn-action-emerald" onclick="return confirm('Konfirmasi bahwa fisik stok telah sampai dan diterima di kasir?')">
                                        <span>✓</span> Terima Distribusi
                                    </button>
                                </form>
                                @else
                                    <span style="font-size: 11px; color: var(--text-sub);">Diterima di Kasir</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Belum ada riwayat pengiriman stok distribusi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL 2: PERMINTAAN STOK BARANG DARI KASIR -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                    <span>📝</span> Permintaan Stok Barang dari Kasir (Shift Stock Requests)
                </h3>
            </div>

            <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>No. Request</th>
                            <th>Kategori</th>
                            <th>Nama Item Diminta</th>
                            <th>Qty Permintaan</th>
                            <th>Staff Pemohon</th>
                            <th>Urgensi</th>
                            <th>Status Permintaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockRequests as $req)
                        <tr>
                            <td style="font-family: monospace; color: #38bdf8;">{{ $req->request_number }}</td>
                            <td><span class="badge-status badge-sky">{{ strtoupper($req->item_category) }}</span></td>
                            <td style="font-weight: 600; color: #fff;">{{ $req->item_name }}</td>
                            <td style="font-weight: 700; color: #fbbf24; font-family: 'Outfit';">{{ $req->qty_requested }} {{ $req->unit }}</td>
                            <td>{{ $req->employee->name ?? 'Kasir' }}</td>
                            <td>
                                <span class="badge-status {{ $req->urgency === 'urgent' ? 'badge-rose' : 'badge-sky' }}">
                                    {{ ucfirst($req->urgency) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $req->status === 'fulfilled' ? 'badge-emerald' : 'badge-amber' }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Tidak ada permohonan permintaan stok yang tertunda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
