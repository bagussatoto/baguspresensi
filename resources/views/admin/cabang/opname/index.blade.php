@extends('admin.dashboard.layout')

@section('title', 'Audit Fisik Persediaan & Stock Opname Multi-Cabang')

@section('content')
<style>
    .badge-opname-draft { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-opname-completed { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }
    .badge-opname-adjusted { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; }

    .modal-backdrop-custom {
        display: none; position: fixed; inset: 0; background: rgba(11, 17, 32, 0.85);
        backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-box-custom {
        background: #1e293b; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 16px;
        width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Audit Fisik & Stock Opname Berkala</h1>
        <p class="hero-desc-text">
            Siklus penghitungan fisik persediaan per rak (bin location) di seluruh titik cabang & distribution center (DC). Rekonsiliasi stok buku sistem terhadap stok riil fisik dan penyesuaian otomatis dengan audit trail terverifikasi.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" onclick="openStartOpnameModal()" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;">
            <span>➕</span> Buka Sesi Stock Opname Baru
        </button>
        <a href="{{ route('admin.cabang.konsolidasi') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📊</span> Dashboard Konsolidasi
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.cabang.nav')

<!-- STATS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Sesi Audit</div>
        <div style="font-family: monospace; font-size: 24px; font-weight: 800; color: #38bdf8; margin-top: 6px;">
            {{ number_format($stats['total_sessions']) }}
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Riwayat audit fisik cabang</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Audit Berjalan (Draft)</div>
        <div style="font-family: monospace; font-size: 24px; font-weight: 800; color: #fbbf24; margin-top: 6px;">
            {{ number_format($stats['draft_sessions']) }} Sesi
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Sedang proses hitung fisik</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Menunggu Pengesahan</div>
        <div style="font-family: monospace; font-size: 24px; font-weight: 800; color: #60a5fa; margin-top: 6px;">
            {{ number_format($stats['pending_approval']) }} Sesi
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Selesai hitung, siap adjust</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Stok Disesuaikan (Adjusted)</div>
        <div style="font-family: monospace; font-size: 24px; font-weight: 800; color: #34d399; margin-top: 6px;">
            {{ number_format($stats['adjusted_sessions']) }} Sesi
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Persediaan sistem sinkron</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Dampak Varian (Rp)</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: {{ $stats['total_variance_cost'] < 0 ? '#f43f5e' : '#38bdf8' }}; margin-top: 8px;">
            Rp {{ number_format($stats['total_variance_cost'], 0, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Net selisih persediaan riil</div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="data-table-container" style="padding: 16px 20px; margin-bottom: 20px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px;">
    <form method="GET" action="{{ route('admin.cabang.opname') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 220px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Filter Titik Cabang / DC</label>
            <select name="branch_id" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px 12px; border-radius: 8px; font-size: 13px;">
                <option value="all">Semua Cabang & DC</option>
                @foreach($branches as $b)
                    @php $bType = $b->type instanceof \BackedEnum ? $b->type->value : (string) $b->type; @endphp
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                        [{{ strtoupper($bType) }}] {{ $b->name }} ({{ $b->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="width: 180px;">
            <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Status Audit</label>
            <select name="status" style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 8px 12px; border-radius: 8px; font-size: 13px;">
                <option value="all">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Berjalan)</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Menunggu Approval)</option>
                <option value="adjusted" {{ request('status') === 'adjusted' ? 'selected' : '' }}>Adjusted (Selesai)</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 9px 16px; font-size: 13px;">
                <span>🔍</span> Terapkan Filter
            </button>
            <a href="{{ route('admin.cabang.opname') }}" class="btn-action-sm" style="padding: 9px 14px; font-size: 13px; background: rgba(255,255,255,0.06); color: #94a3b8; margin-left: 6px;">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- OPNAME SESSIONS TABLE -->
<div class="data-table-container" style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
            <span>📋</span> Riwayat & Jadwal Stock Opname Cabang
        </h3>
        <span style="font-size: 12px; color: #64748b;">Menampilkan {{ $opnames->total() }} sesi audit</span>
    </div>

    <div style="overflow-x: auto;">
        <table class="inventory-table" style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
                <tr style="background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid rgba(255,255,255,0.08); color: #94a3b8; font-size: 12px; text-transform: uppercase;">
                    <th style="padding: 12px 18px;">No. Dokumen Opname</th>
                    <th style="padding: 12px 18px;">Cabang & Gudang</th>
                    <th style="padding: 12px 18px;">Tanggal Audit</th>
                    <th style="padding: 12px 18px; text-align: center;">Status</th>
                    <th style="padding: 12px 18px; text-align: right;">SKU Dihitung</th>
                    <th style="padding: 12px 18px; text-align: right;">SKU Selisih</th>
                    <th style="padding: 12px 18px; text-align: right;">Total Varian Biaya</th>
                    <th style="padding: 12px 18px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opnames as $opname)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s ease;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 14px 18px;">
                            <div style="font-family: monospace; font-weight: 700; color: #38bdf8;">
                                {{ $opname->opname_number }}
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                Auditor: {{ $opname->conductor->name ?? 'Belum Ditentukan' }}
                            </div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 600; color: #f8fafc;">
                                {{ $opname->branch->name ?? '-' }}
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                🏢 Gudang: {{ $opname->warehouse->name ?? '-' }}
                            </div>
                        </td>
                        <td style="padding: 14px 18px; color: #cbd5e1;">
                            {{ $opname->opname_date->format('d M Y') }}
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            @if($opname->status === 'draft')
                                <span class="badge-opname-draft">📝 DRAFT AUDIT</span>
                            @elseif($opname->status === 'completed')
                                <span class="badge-opname-completed">⏳ MENUNGGU OTORISASI</span>
                            @else
                                <span class="badge-opname-adjusted">✅ ADJUSTED (SINKRON)</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; text-align: right; font-family: monospace; font-weight: 700; color: #f8fafc;">
                            {{ $opname->total_items_counted }} SKU
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            @if($opname->total_discrepancy_items > 0)
                                <span style="font-family: monospace; font-weight: 700; color: #f43f5e; background: rgba(244, 63, 94, 0.1); padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(244, 63, 94, 0.3);">
                                    ⚠️ {{ $opname->total_discrepancy_items }} Selisih
                                </span>
                            @else
                                <span style="font-family: monospace; font-size: 12px; color: #34d399;">
                                    ✓ Nihil (0)
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; text-align: right; font-family: monospace; font-weight: 700;">
                            @if($opname->total_variance_cost < 0)
                                <span style="color: #f43f5e;">Rp {{ number_format($opname->total_variance_cost, 0, ',', '.') }}</span>
                            @elseif($opname->total_variance_cost > 0)
                                <span style="color: #38bdf8;">+Rp {{ number_format($opname->total_variance_cost, 0, ',', '.') }}</span>
                            @else
                                <span style="color: #94a3b8;">Rp 0</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="{{ route('admin.cabang.opname.show', $opname->id) }}" class="btn-action-sm btn-action-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">
                                    <span>🔍</span> Lembar Audit
                                </a>
                                <a href="{{ route('admin.cabang.opname.export-csv', $opname->id) }}" class="btn-action-sm" style="padding: 6px 10px; font-size: 12px; background: rgba(255,255,255,0.08); color: #94a3b8; text-decoration: none;" title="Unduh Berita Acara CSV">
                                    <span>📥</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 40px; text-align: center; color: #64748b;">
                            <div style="font-size: 32px; margin-bottom: 10px;">📋</div>
                            <div style="font-size: 15px; font-weight: 600; color: #94a3b8;">Belum ada sesi stock opname yang tercatat.</div>
                            <div style="font-size: 12px; margin-top: 4px;">Klik tombol "Buka Sesi Stock Opname Baru" di atas untuk memulai audit fisik rak persediaan cabang.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($opnames->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.06);">
            {{ $opnames->links() }}
        </div>
    @endif
</div>

<!-- MODAL BUKA SESI OPNAME -->
<div id="startOpnameModal" class="modal-backdrop-custom">
    <div class="modal-box-custom">
        <div style="padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 16px; font-weight: 700; color: #f8fafc; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> Inisiasi Sesi Stock Opname Cabang Baru
            </h3>
            <button type="button" onclick="closeStartOpnameModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('admin.cabang.opname.store') }}" method="POST" style="padding: 24px;">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 18px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Pilih Titik Cabang / DC Target Audit *</label>
                    <select name="branch_id" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 14px; border-radius: 8px; font-size: 14px;">
                        <option value="">-- Pilih Cabang / DC --</option>
                        @foreach($branches as $b)
                            @php $bType = $b->type instanceof \BackedEnum ? $b->type->value : (string) $b->type; @endphp
                            <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }}) - {{ strtoupper($bType) }}</option>
                        @endforeach
                    </select>
                    <p style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Sistem akan memuat snapshot seluruh SKU dan stok saat ini di cabang tersebut.</p>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Tanggal Pelaksanaan Audit *</label>
                    <input type="date" name="opname_date" value="{{ date('Y-m-d') }}" required style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 14px; border-radius: 8px; font-size: 14px;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Catatan & Instruksi Audit</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Audit berkala akhir bulan shift 2, fokus rak display makanan & minuman." style="width: 100%; background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 14px; border-radius: 8px; font-size: 13px;"></textarea>
                </div>

                <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #fbbf24;">
                    💡 <strong>Prosedur Audit:</strong> Sesi opname akan dibuat berstatus <code>DRAFT</code>. Petugas auditor di toko dapat menginput hitungan fisik per rak. Setelah diverifikasi supervisor, stok sistem dapat disinkronkan otomatis.
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06);">
                <button type="button" onclick="closeStartOpnameModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #cbd5e1;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 20px; font-size: 13px; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;">
                    🚀 Buka Sesi & Ambil Snapshot Stok
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStartOpnameModal() {
        document.getElementById('startOpnameModal').classList.add('open');
    }
    function closeStartOpnameModal() {
        document.getElementById('startOpnameModal').classList.remove('open');
    }
</script>
@endsection
