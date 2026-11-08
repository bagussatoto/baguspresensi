@extends('admin.dashboard.layout')

@section('title', 'Laporan Aging Piutang Grosir & Saldo Konsinyasi Cabang')

@section('content')
<style>
    .badge-aging-current { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-aging-warn { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-aging-danger { background: rgba(244, 63, 94, 0.2); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }

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
        <h1 class="hero-title-text">Aging Piutang Grosir & Saldo Konsinyasi Cabang</h1>
        <p class="hero-desc-text">
            Jadwal umur piutang (*AR Aging Schedule*) pelanggan B2B/Grosir, pemantauan batas kredit, serta rekonsiliasi hak setor dan saldo fisik stok konsinyasi antar-cabang/vendor.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.cabang.aging.export-ar', request()->query()) }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">
            <span>📥</span> Unduh Aging Piutang (CSV)
        </a>
        <button type="button" onclick="openConsignmentModal()" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px; background: linear-gradient(135deg, #10b981, #059669); border: none;">
            <span>➕</span> Buka Titipan Konsinyasi Baru
        </button>
        <a href="{{ route('admin.cabang.consignments.export', request()->query()) }}" class="btn-action-sm" style="padding: 10px 18px; font-size: 13px; background: rgba(255,255,255,0.08); color: #cbd5e1; text-decoration: none;">
            <span>📥</span> Ekspor Konsinyasi (CSV)
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.cabang.nav')

<!-- SECTION 1: AGING PIUTANG GROSIR / B2B -->
<div style="margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>💰</span> Matriks Umur Piutang Usaha (AR Aging Schedule)
            </h2>
            <p style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                Posisi Tanggal Cut-off (As of): <strong style="color: #38bdf8;">{{ $asOfDate->format('d F Y') }}</strong>
            </p>
        </div>

        <!-- FILTER AGING -->
        <form method="GET" action="{{ route('admin.cabang.aging') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <select name="branch_id" onchange="this.form.submit()" style="background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 7px 12px; border-radius: 8px; font-size: 12px;">
                <option value="all">Semua Cabang & DC</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>

            <select name="customer_type" onchange="this.form.submit()" style="background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 7px 12px; border-radius: 8px; font-size: 12px;">
                <option value="all">Semua Tipe Pelanggan</option>
                <option value="grosir" {{ request('customer_type') === 'grosir' ? 'selected' : '' }}>Grosir / Distributor</option>
                <option value="reseller" {{ request('customer_type') === 'reseller' ? 'selected' : '' }}>Reseller</option>
                <option value="proyek" {{ request('customer_type') === 'proyek' ? 'selected' : '' }}>Proyek B2B</option>
                <option value="member" {{ request('customer_type') === 'member' ? 'selected' : '' }}>Member Tetap</option>
            </select>

            <input type="date" name="as_of_date" value="{{ $asOfDate->format('Y-m-d') }}" onchange="this.form.submit()" style="background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 7px 12px; border-radius: 8px; font-size: 12px;">
        </form>
    </div>

    <!-- AGING STAT CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Piutang Aktif</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #38bdf8; margin-top: 4px;">
                Rp {{ number_format($arKpi['total_receivable'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ $arKpi['total_customers'] }} Pelanggan B2B</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Lancar (Current)</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #34d399; margin-top: 4px;">
                Rp {{ number_format($arKpi['current_amount'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Belum jatuh tempo</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Tempo 31 - 60 Hari</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #fbbf24; margin-top: 4px;">
                Rp {{ number_format($arKpi['days_31_60'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Follow-up penagihan</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(249, 115, 22, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Tempo 61 - 90 Hari</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #fb923c; margin-top: 4px;">
                Rp {{ number_format($arKpi['days_61_90'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Peringatan limit kredit</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Macet (> 90 Hari)</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #f43f5e; margin-top: 4px;">
                Rp {{ number_format($arKpi['days_over_90'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Risiko Bad Debt (Kritis)</div>
        </div>
    </div>

    <!-- TABEL REKAP AGING PER PELANGGAN -->
    <div class="data-table-container" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; overflow: hidden; margin-bottom: 20px;">
        <div style="padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 14px; font-weight: 700; color: #f8fafc; margin: 0;">
                📊 Rekapitulasi Piutang Pelanggan per Kolom Umur (Aging Schedule)
            </h3>
            <span style="font-size: 12px; color: #64748b;">{{ count($customerAgingSummary) }} Konsumen</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="inventory-table" style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
                <thead>
                    <tr style="background: rgba(15, 23, 42, 0.7); border-bottom: 1px solid rgba(255,255,255,0.08); color: #94a3b8; font-size: 11px; text-transform: uppercase;">
                        <th style="padding: 10px 14px;">Nama Pelanggan</th>
                        <th style="padding: 10px 14px;">Kategori</th>
                        <th style="padding: 10px 14px;">Cabang</th>
                        <th style="padding: 10px 14px; text-align: right;">Total Piutang</th>
                        <th style="padding: 10px 14px; text-align: right; color: #34d399;">Lancar (Current)</th>
                        <th style="padding: 10px 14px; text-align: right; color: #fbbf24;">1 - 30 Hari</th>
                        <th style="padding: 10px 14px; text-align: right; color: #fb923c;">31 - 60 Hari</th>
                        <th style="padding: 10px 14px; text-align: right; color: #f87171;">61 - 90 Hari</th>
                        <th style="padding: 10px 14px; text-align: right; color: #f43f5e;">> 90 Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerAgingSummary as $row)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td style="padding: 11px 14px; font-weight: 600; color: #f8fafc;">
                                {{ $row['customer_name'] }}
                                <div style="font-size: 11px; color: #64748b; font-weight: normal;">{{ $row['invoices_count'] }} faktur belum lunas</div>
                            </td>
                            <td style="padding: 11px 14px;">
                                <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 700;">
                                    {{ $row['customer_type'] }}
                                </span>
                            </td>
                            <td style="padding: 11px 14px; color: #cbd5e1;">
                                {{ $row['branch_name'] }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; font-weight: 700; color: #38bdf8;">
                                Rp {{ number_format($row['total_outstanding'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: {{ $row['current'] > 0 ? '#34d399' : '#64748b' }};">
                                {{ $row['current'] > 0 ? 'Rp ' . number_format($row['current'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: {{ $row['days_1_30'] > 0 ? '#fbbf24' : '#64748b' }};">
                                {{ $row['days_1_30'] > 0 ? 'Rp ' . number_format($row['days_1_30'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: {{ $row['days_31_60'] > 0 ? '#fb923c' : '#64748b' }};">
                                {{ $row['days_31_60'] > 0 ? 'Rp ' . number_format($row['days_31_60'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: {{ $row['days_61_90'] > 0 ? '#f87171' : '#64748b' }};">
                                {{ $row['days_61_90'] > 0 ? 'Rp ' . number_format($row['days_61_90'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; font-weight: 700; color: {{ $row['days_over_90'] > 0 ? '#f43f5e' : '#64748b' }};">
                                {{ $row['days_over_90'] > 0 ? 'Rp ' . number_format($row['days_over_90'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 30px; text-align: center; color: #64748b;">
                                ✓ Tidak ada saldo piutang yang tertunggak pada posisi tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SECTION 2: SALDO KONSINYASI MULTI-CABANG -->
<div style="margin-bottom: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 18px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>📦</span> Monitoring Saldo Konsinyasi & Rekonsiliasi Hak Setor
            </h2>
            <p style="font-size: 12px; color: #94a3b8; margin-top: 4px;">
                Pencatatan barang titipan supplier (Inward) dan titipan ke outlet/mitra reseller (Outward) beserta sisa stok fisik dan hak setor.
            </p>
        </div>
    </div>

    <!-- KONSINYASI STAT CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Kontrak Aktif</div>
            <div style="font-family: monospace; font-size: 22px; font-weight: 800; color: #38bdf8; margin-top: 4px;">
                {{ $consignmentKpi['active_contracts'] }} Kontrak
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Total: {{ $consignmentKpi['total_contracts'] }} sesi titipan</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Nilai Titip Awal</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #c084fc; margin-top: 4px;">
                Rp {{ number_format($consignmentKpi['total_consigned_value'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Valuasi barang konsinyasi</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Laku Terjual</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #34d399; margin-top: 4px;">
                Rp {{ number_format($consignmentKpi['total_sold_value'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Omzet barang terjual</div>
        </div>

        <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Sisa Hak Setor Belum Lunas</div>
            <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #fbbf24; margin-top: 4px;">
                Rp {{ number_format($consignmentKpi['remaining_settlement_due'], 0, ',', '.') }}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Kewajiban setor ke/dari mitra</div>
        </div>
    </div>

    <!-- TABEL KONSINYASI -->
    <div class="data-table-container" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; overflow: hidden;">
        <div style="padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 14px; font-weight: 700; color: #f8fafc; margin: 0;">
                📋 Daftar Transaksi & Kontrak Konsinyasi Cabang
            </h3>
            <span style="font-size: 12px; color: #64748b;">{{ $consignments->total() }} Kontrak</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="inventory-table" style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
                <thead>
                    <tr style="background: rgba(15, 23, 42, 0.7); border-bottom: 1px solid rgba(255,255,255,0.08); color: #94a3b8; font-size: 11px; text-transform: uppercase;">
                        <th style="padding: 10px 14px;">No. Kontrak</th>
                        <th style="padding: 10px 14px;">Cabang</th>
                        <th style="padding: 10px 14px;">Jenis Titipan</th>
                        <th style="padding: 10px 14px;">Mitra / Vendor</th>
                        <th style="padding: 10px 14px; text-align: right;">Total Nilai Titip</th>
                        <th style="padding: 10px 14px; text-align: right;">Laku Terjual</th>
                        <th style="padding: 10px 14px; text-align: right;">Sudah Disetor</th>
                        <th style="padding: 10px 14px; text-align: right;">Sisa Hak Setor</th>
                        <th style="padding: 10px 14px; text-align: center;">Status</th>
                        <th style="padding: 10px 14px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consignments as $c)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td style="padding: 11px 14px; font-family: monospace; font-weight: 700; color: #38bdf8;">
                                {{ $c->consignment_number }}
                                <div style="font-size: 10.5px; color: #64748b; font-weight: normal;">{{ $c->start_date ? $c->start_date->format('d/m/Y') : '-' }}</div>
                                @if($c->journalEntries && $c->journalEntries->count() > 0)
                                    <div style="margin-top: 4px;">
                                        <a href="{{ route('admin.akuntansi.jurnal') }}" style="font-size: 10px; color: #38bdf8; text-decoration: none; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                            📖 Jurnal: {{ $c->journalEntries->last()->entry_number }}
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 11px 14px; color: #f8fafc;">
                                {{ $c->branch->name ?? '-' }}
                            </td>
                            <td style="padding: 11px 14px;">
                                @if($c->type === 'inward')
                                    <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 700;">
                                        📥 Titipan Supplier
                                    </span>
                                @else
                                    <span style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); padding: 2px 8px; border-radius: 4px; font-size: 10.5px; font-weight: 700;">
                                        📤 Titip ke Mitra
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 11px 14px; font-weight: 600; color: #f8fafc;">
                                {{ $c->partner_name }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: #cbd5e1;">
                                Rp {{ number_format($c->total_consigned_value, 0, ',', '.') }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: #34d399;">
                                Rp {{ number_format($c->total_sold_value, 0, ',', '.') }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; color: #cbd5e1;">
                                Rp {{ number_format($c->total_settled_amount, 0, ',', '.') }}
                            </td>
                            <td style="padding: 11px 14px; text-align: right; font-family: monospace; font-weight: 700; color: {{ $c->remaining_settlement_due > 0 ? '#fbbf24' : '#34d399' }};">
                                Rp {{ number_format($c->remaining_settlement_due, 0, ',', '.') }}
                            </td>
                            <td style="padding: 11px 14px; text-align: center;">
                                @if($c->status === 'settled')
                                    <span class="badge-aging-current">LUNAS / SETTLED</span>
                                @else
                                    <span class="badge-aging-warn">AKTIF BERJALAN</span>
                                @endif
                            </td>
                            <td style="padding: 11px 14px; text-align: center;">
                                <button type="button" onclick="openSettleModal({{ json_encode($c) }})" class="btn-action-sm btn-action-primary" style="padding: 5px 12px; font-size: 11.5px;">
                                    <span>💵</span> Setor / Settle
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding: 30px; text-align: center; color: #64748b;">
                                Belum ada transaksi kontrak konsinyasi yang tercatat. Klik tombol "Buka Titipan Konsinyasi Baru" di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($consignments->hasPages())
            <div style="padding: 14px 18px; border-top: 1px solid rgba(255,255,255,0.06);">
                {{ $consignments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL 1: BUKA KONSINYASI BARU -->
<div id="consignmentModal" class="modal-backdrop-custom">
    <div class="modal-box-custom">
        <div style="padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>📦</span> Inisiasi Titipan Konsinyasi Baru
            </h3>
            <button type="button" onclick="closeConsignmentModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.cabang.consignments.store') }}" method="POST" style="padding: 24px;">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Titik Cabang / DC *</label>
                        <select name="branch_id" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Jenis Konsinyasi *</label>
                        <select name="type" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;">
                            <option value="inward">Titipan Vendor / Supplier ke Toko (Inward)</option>
                            <option value="outward">Titipan Toko ke Mitra Reseller (Outward)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Nama Rekan / Mitra / Vendor *</label>
                    <input type="text" name="partner_name" placeholder="Contoh: CV Sumber Snack Lestari" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Tanggal Mulai Titip *</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Target Tanggal Setoran</label>
                        <input type="date" name="due_settlement_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;">
                    </div>
                </div>

                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid #334155; border-radius: 8px; padding: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #38bdf8; margin-bottom: 8px;">Pilih SKU Produk yang Dititipkan *</label>
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px; align-items: flex-end;">
                        <div>
                            <span style="font-size: 11px; color: #94a3b8;">Varian Produk</span>
                            <select name="items[0][product_variant_id]" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px; border-radius: 6px; font-size: 12px;">
                                @foreach($variants as $v)
                                    <option value="{{ $v->id }}">{{ $v->sku }} - {{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <span style="font-size: 11px; color: #94a3b8;">Harga Satuan Titip (Rp)</span>
                            <input type="number" name="items[0][unit_price]" value="10000" min="0" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px; border-radius: 6px; font-size: 12px;">
                        </div>
                        <div>
                            <span style="font-size: 11px; color: #94a3b8;">Qty Titip Awal</span>
                            <input type="number" name="items[0][qty_consigned]" value="50" min="1" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px; border-radius: 6px; font-size: 12px;">
                        </div>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Ketentuan retur barang tidak laku, bagi hasil, dll." style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;"></textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06);">
                <button type="button" onclick="closeConsignmentModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 20px; font-size: 13px;">
                    Simpan Kontrak Konsinyasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: SETORAN & REKONSILIASI KONSINYASI -->
<div id="settleModal" class="modal-backdrop-custom">
    <div class="modal-box-custom">
        <div style="padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>💵</span> Rekonsiliasi & Setoran Pembayaran Konsinyasi
            </h3>
            <button type="button" onclick="closeSettleModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form id="settleForm" method="POST" style="padding: 24px;">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: rgba(15, 23, 42, 0.6); padding: 12px 16px; border-radius: 8px; border: 1px solid #334155;">
                    <div style="font-size: 11px; color: #94a3b8;">No. Kontrak:</div>
                    <div id="settleNumber" style="font-family: monospace; font-weight: 700; color: #38bdf8; font-size: 14px;">-</div>
                    <div id="settlePartner" style="font-size: 12px; color: #cbd5e1; margin-top: 2px;">-</div>
                </div>

                <div id="settleItemsContainer">
                    <!-- Dynamic items rendered via JS -->
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Nominal Setoran Tunai / Transfer (Rp) *</label>
                    <input type="number" name="settled_amount" id="settleAmountInput" value="0" min="0" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #34d399; font-family: monospace; font-weight: 700; font-size: 16px; padding: 10px 14px; border-radius: 8px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Catatan Setoran</label>
                    <input type="text" name="notes" placeholder="Contoh: Setoran minggu ke-1 via transfer BCA." style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 9px 12px; border-radius: 8px; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06);">
                <button type="button" onclick="closeSettleModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 20px; font-size: 13px;">
                    Simpan Rekonsiliasi & Setoran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openConsignmentModal() {
        document.getElementById('consignmentModal').classList.add('open');
    }
    function closeConsignmentModal() {
        document.getElementById('consignmentModal').classList.remove('open');
    }

    function openSettleModal(consignment) {
        document.getElementById('settleNumber').innerText = consignment.consignment_number;
        document.getElementById('settlePartner').innerText = consignment.partner_name + ' (' + (consignment.type === 'inward' ? 'Titipan Supplier' : 'Titip ke Mitra') + ')';
        document.getElementById('settleForm').action = '/admin/cabang/consignments/' + consignment.id + '/settle';

        const container = document.getElementById('settleItemsContainer');
        container.innerHTML = '';

        if (consignment.items && consignment.items.length > 0) {
            consignment.items.forEach((item, idx) => {
                const varName = (item.variant && item.variant.name) ? item.variant.name : 'SKU #' + item.product_variant_id;
                const div = document.createElement('div');
                div.style.cssText = 'background: rgba(15, 23, 42, 0.4); border: 1px solid #334155; border-radius: 6px; padding: 10px; margin-bottom: 8px;';
                div.innerHTML = `
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <strong style="color: #f8fafc;">${varName}</strong>
                        <span style="color: #94a3b8;">Sisa Fisik: ${item.qty_remaining} unit</span>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="hidden" name="items[${idx}][id]" value="${item.id}">
                        <span style="font-size: 11px; color: #cbd5e1;">Qty Laku Baru:</span>
                        <input type="number" name="items[${idx}][qty_sold_now]" value="0" min="0" max="${item.qty_remaining}" 
                               style="width: 80px; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 5px; border-radius: 4px; text-align: center;">
                        <span style="font-size: 11px; color: #64748b;">@ Rp ${Number(item.unit_price).toLocaleString('id-ID')}</span>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        document.getElementById('settleAmountInput').value = consignment.remaining_settlement_due || 0;
        document.getElementById('settleModal').classList.add('open');
    }

    function closeSettleModal() {
        document.getElementById('settleModal').classList.remove('open');
    }
</script>
@endsection

