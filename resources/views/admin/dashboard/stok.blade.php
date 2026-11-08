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
            <button type="button" onclick="openErdModal()" class="btn-action-sm" style="background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(56,189,248,0.25)); border: 1px solid rgba(56,189,248,0.4); color: #7dd3fc; padding: 8px 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>📊</span> Diagram ERD Pergudangan
            </button>
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
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                            Belum ada data inventaris stok yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECTION 2: LAPORAN AUDIT SERAH TERIMA SHIFT & SELISIH STOK FISIK -->
<div class="dashboard-section-card" style="margin-top: 24px; border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);">
    <div class="section-card-header" style="background: rgba(139, 92, 246, 0.08); border-bottom: 1px solid rgba(139, 92, 246, 0.2);">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(139, 92, 246, 0.2); color: #c084fc; font-size: 20px;">🛡️</div>
            <div>
                <h3 class="section-title" style="display: flex; align-items: center; gap: 10px;">
                    Laporan Audit Serah Terima Shift & Rekonsiliasi Stok Fisik
                    @php $discToday = $stockAuditSummary['total_discrepancy_today'] ?? 0; @endphp
                    @if($discToday > 0)
                        <span class="badge-status badge-rose" style="font-size: 11px; padding: 3px 8px;">
                            ⚠️ {{ $discToday }} Selisih Hari Ini
                        </span>
                    @else
                        <span class="badge-status badge-emerald" style="font-size: 11px; padding: 3px 8px;">
                            ✓ Semua Stok & Kas Pas
                        </span>
                    @endif
                </h3>
                <span class="section-subtitle">
                    Bukti rekonsiliasi keaslian stok fisik antar-shift: mencantumkan nama operator penyerah (shift lalu) vs penerima (shift sekarang).
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="openDailyZReportModal()" class="btn-action-sm" style="padding: 8px 14px; font-size: 12px; background: #059669; border: 1px solid #10b981; color: #ffffff; cursor: pointer; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <span>🖨️</span> Cetak Z-Report Harian (24 Jam)
            </button>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Dashboard Utama
            </a>
        </div>
    </div>
    <div class="section-card-body" style="padding: 0; overflow-x: auto;">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 130px;">Waktu & Tanggal</th>
                    <th>Shift & Cabang</th>
                    <th>⏮️ Penyerah (Shift Lalu)</th>
                    <th>⏭️ Penerima (Shift Sekarang)</th>
                    <th>Status Rekonsiliasi Fisik Barang</th>
                    <th>Selisih Kas Laci</th>
                    <th>Kelayakan Hardware</th>
                    <th>Catatan Kasir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockAuditSummary['reports'] ?? [] as $audit)
                @php
                    $hasDisc = (bool) $audit['has_discrepancy'];
                    $discCount = (int) $audit['discrepancy_count'];
                    $hwSt = $audit['hardware_status'] ?? 'normal';
                @endphp
                <tr style="{{ $hasDisc ? 'background: rgba(239, 68, 68, 0.04);' : '' }}">
                    <td>
                        <div style="font-weight: 700; color: #fff; font-size: 12.5px;">
                            {{ $audit['completed_at_formatted'] }} WIB
                        </div>
                        <span style="font-size: 11px; color: var(--text-muted);">
                            {{ $audit['check_date_formatted'] }}
                        </span>
                    </td>
                    <td>
                        <span class="badge-status badge-sky" style="font-size: 10.5px; font-weight: 700;">
                            {{ $audit['shift_name'] }}
                        </span>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">
                            {{ $audit['branch_name'] }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #38bdf8; font-size: 12.5px; display: flex; align-items: center; gap: 5px;">
                            <span>👤</span> {{ $audit['prev_operator'] }}
                        </div>
                        <span style="font-size: 10.5px; color: var(--text-muted);">
                            Shift: {{ $audit['prev_shift_name'] }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #a855f7; font-size: 12.5px; display: flex; align-items: center; gap: 5px;">
                            <span>🔎</span> {{ $audit['recipient_cashier'] }}
                        </div>
                        <span style="font-size: 10.5px; color: #34d399;">
                            ✓ Pemeriksa Masuk
                        </span>
                    </td>
                    <td>
                        @if($discCount > 0)
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @foreach($audit['discrepancies'] as $discItem)
                                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); padding: 3px 8px; border-radius: 6px; font-size: 11px;">
                                    <strong style="color: #fff;">{{ $discItem['name'] }}:</strong>
                                    <span style="color: #94a3b8;">Sistem: {{ $discItem['system_qty'] }} &bull; Fisik: {{ $discItem['physical_qty'] }}</span>
                                    <span style="color: {{ $discItem['diff'] < 0 ? '#f87171' : '#38bdf8' }}; font-weight: 800;">
                                        ({{ $discItem['diff'] > 0 ? '+'.$discItem['diff'] : $discItem['diff'] }} {{ $discItem['unit'] }})
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <span class="badge-status badge-emerald" style="font-size: 10.5px;">
                                ✓ Semua Stok Fisik Cocok
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($audit['cash_diff'] == 0)
                            <span style="font-size: 11.5px; color: #34d399; font-weight: 600;">
                                ✓ Pas (Rp {{ number_format($audit['actual_cash'], 0, ',', '.') }})
                            </span>
                        @elseif($audit['cash_diff'] < 0)
                            <span style="font-size: 11.5px; color: #f87171; font-weight: 800;">
                                ⚠️ Minus Rp {{ number_format(abs($audit['cash_diff']), 0, ',', '.') }}
                            </span>
                        @else
                            <span style="font-size: 11.5px; color: #38bdf8; font-weight: 800;">
                                +Rp {{ number_format($audit['cash_diff'], 0, ',', '.') }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($hwSt === 'issue')
                            <span class="badge-status badge-rose" style="font-size: 10px; font-weight: 800;">
                                🔴 Butuh Maintenance
                            </span>
                        @elseif($hwSt === 'warning')
                            <span class="badge-status badge-amber" style="font-size: 10px; font-weight: 800;">
                                🟡 Ada Kendala
                            </span>
                        @else
                            <span class="badge-status badge-emerald" style="font-size: 10px;">
                                🟢 Normal (15 PC)
                            </span>
                        @endif
                        @if(!empty($audit['hardware_notes']))
                            <div style="font-size: 10px; color: #fbbf24; margin-top: 3px; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $audit['hardware_notes'] }}">
                                🖥️ {{ $audit['hardware_notes'] }}
                            </div>
                        @endif
                    </td>
                    <td style="max-width: 240px;">
                        @php
                            $notes = array_filter([$audit['drink_notes'], $audit['food_notes'], $audit['cash_notes'], $audit['hardware_notes']]);
                        @endphp
                        @if(!empty($notes))
                            <div style="font-size: 11.5px; color: #e2e8f0; background: rgba(15, 23, 42, 0.6); padding: 5px 8px; border-radius: 6px; border: 1px solid var(--border-glass); line-height: 1.35;">
                                {{ implode(' | ', $notes) }}
                            </div>
                        @else
                            <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">
                                Tidak ada catatan
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        Belum ada riwayat laporan serah terima shift atau audit stok yang tersimpan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.dashboard.partials.modal_z_report')
@include('admin.dashboard.partials.modal_erd_stok')
@endsection
