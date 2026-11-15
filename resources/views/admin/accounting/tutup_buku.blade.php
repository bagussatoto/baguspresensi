@extends('admin.dashboard.layout')

@section('title', 'Tutup Buku & Kunci Periode Akuntansi')

@section('content')
<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Tutup Buku &amp; Kunci Periode Akuntansi</h1>
        <p class="hero-desc-text">
            Kontrol audit internal (Period Closing): Kunci periode pembukuan bulanan/tahunan agar tidak ada jurnal manual bertanggal mundur (backdated) yang mengubah angka laporan yang sudah diaudit, serta buat jurnal penutup otomatis ke Laba Ditahan.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.akuntansi.jurnal') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>📖</span> Buka Jurnal Umum
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

@if(session('success'))
<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
    ✅ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600;">
    ⚠️ {{ session('error') }}
</div>
@endif

@if($errors->any())
<div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fb7185; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px;">
    <ul style="margin: 0; padding-left: 18px;">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- KPI SUMMARY -->
<div class="stats-grid-row" style="margin-bottom: 22px;">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Periode Terkunci (Closed)</span>
            <div class="metric-stat-icon" style="background: rgba(244, 63, 94, 0.15); color: #fb7185;">🔒</div>
        </div>
        <div class="metric-stat-value" style="color: #fb7185;">{{ $closedCount }} Periode</div>
        <div class="metric-stat-desc">Terproteksi dari input jurnal mundur</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Periode Dibuka Kembali</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">🔓</div>
        </div>
        <div class="metric-stat-value" style="color: #38bdf8;">{{ $openCount }} Periode</div>
        <div class="metric-stat-desc">Riwayat pembukaan kembali tercatat di Audit Log</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Status Bulan Berjalan ({{ now()->format('M Y') }})</span>
            <div class="metric-stat-icon" style="background: {{ $currentMonthLocked ? 'rgba(244, 63, 94, 0.15); color: #fb7185;' : 'rgba(16, 185, 129, 0.15); color: #34d399;' }}">
                {{ $currentMonthLocked ? '🔒' : '🟢' }}
            </div>
        </div>
        <div class="metric-stat-value" style="color: {{ $currentMonthLocked ? '#fb7185' : '#34d399' }};">
            {{ $currentMonthLocked ? 'TERKUNCI' : 'TERBUKA (AKTIF)' }}
        </div>
        <div class="metric-stat-desc">{{ $currentMonthLocked ? 'Pembukuan bulan berjalan ditutup' : 'Siap menerima pencatatan transaksi' }}</div>
    </div>
</div>

<!-- FORM TUTUP BUKU PERIODE BARU -->
<div class="dashboard-section-card" style="margin-bottom: 24px;">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">🔐</div>
            <div>
                <h3 class="section-title">Kunci &amp; Tutup Periode Akuntansi</h3>
                <span class="section-subtitle">Pilih bulan &amp; cabang yang telah selesai diaudit untuk mengunci transaksi pembukuan</span>
            </div>
        </div>
    </div>

    <div style="padding: 20px;">
        <form method="POST" action="{{ route('admin.akuntansi.tutup-buku.close') }}" onsubmit="return confirm('Apakah Anda yakin ingin menutup dan mengunci periode akuntansi ini? Jurnal manual pada rentang tanggal periode ini tidak akan bisa diposting lagi.');">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">Bulan Periode</label>
                    <select name="month" class="form-input-custom" style="width: 100%; height: 40px; padding: 6px 12px;" required>
                        @php
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            $defaultMonth = (int) now()->month;
                        @endphp
                        @foreach($months as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ $defaultMonth === $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">Tahun</label>
                    <select name="year" class="form-input-custom" style="width: 100%; height: 40px; padding: 6px 12px;" required>
                        @for($y = (int) now()->year + 1; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ (int) now()->year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">Unit Cabang</label>
                    <select name="branch_id" class="form-input-custom" style="width: 100%; height: 40px; padding: 6px 12px;">
                        <option value="">🏢 Semua Cabang (Kunci Global)</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label-custom" style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px;">Catatan Tutup Buku (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Audit akhir bulan selesai & disetujui" class="form-input-custom" style="width: 100%; height: 40px; padding: 6px 12px;">
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.08);">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; color: #e2e8f0;">
                    <input type="checkbox" name="generate_closing_journal" value="1" style="width: 16px; height: 16px; accent-color: #10b981;">
                    <span>Buat <strong>Jurnal Penutup Otomatis</strong> (Nol-kan saldo Pendapatan, HPP, &amp; Beban ke akun Laba Ditahan <code style="color:#34d399;">3-20001</code>)</span>
                </label>

                <button type="submit" class="btn-action-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 10px 22px; font-weight: 700; cursor: pointer;">
                    <span>🔒</span> Tutup &amp; Kunci Periode Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL RIWAYAT TUTUP BUKU -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📜</div>
            <div>
                <h3 class="section-title">Daftar Status Periode Akuntansi</h3>
                <span class="section-subtitle">Riwayat periode yang pernah dikunci atau dibuka kembali lengkap dengan jejak audit</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Cabang</th>
                    <th>Rentang Tanggal</th>
                    <th>Status</th>
                    <th>Jurnal Penutup</th>
                    <th>Ditutup / Dibuka Oleh</th>
                    <th>Catatan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $p)
                <tr>
                    <td style="font-weight: 700; color: #f8fafc;">{{ $p->period_name }}</td>
                    <td>{{ $p->branch?->name ?? '🏢 Semua Cabang (Global)' }}</td>
                    <td style="font-family: monospace; font-size: 12px; color: #94a3b8;">
                        {{ $p->start_date->format('d/m/Y') }} - {{ $p->end_date->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($p->status === 'closed')
                        <span class="badge-status" style="background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.35);">
                            🔒 TERKUNCI
                        </span>
                        @else
                        <span class="badge-status" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35);">
                            🔓 TERBUKA
                        </span>
                        @endif
                    </td>
                    <td>
                        @if($p->closingJournalEntry)
                        <a href="{{ route('admin.akuntansi.jurnal', ['q' => $p->closingJournalEntry->entry_number]) }}" style="color: #38bdf8; font-family: monospace; text-decoration: none; font-weight: 600;">
                            {{ $p->closingJournalEntry->entry_number }} ↗
                        </a>
                        @else
                        <span style="color: #64748b; font-size: 12px;">-</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; color: #cbd5e1;">
                        @if($p->status === 'closed')
                        <div>Tutup: {{ $p->closedBy?->name ?? 'Admin' }}</div>
                        <div style="color: #64748b;">{{ $p->closed_at?->format('d M Y H:i') }}</div>
                        @else
                        <div>Buka: {{ $p->reopenedBy?->name ?? 'Admin' }}</div>
                        <div style="color: #64748b;">{{ $p->reopened_at?->format('d M Y H:i') }}</div>
                        @endif
                    </td>
                    <td style="font-size: 12px; color: #94a3b8; max-width: 240px;">
                        {{ $p->notes ?? '-' }}
                    </td>
                    <td style="text-align: right;">
                        @if($p->status === 'closed')
                        <button type="button" onclick="openReopenModal({{ $p->id }}, '{{ addslashes($p->period_name) }}')" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); cursor: pointer;">
                            🔓 Buka Kembali
                        </button>
                        @else
                        <span style="font-size: 11px; color: #64748b;">Periode Aktif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">
                        Belum ada riwayat periode yang ditutup. Semua periode akuntansi saat ini dalam status terbuka.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL BUKA KEMBALI PERIODE (REOPEN) -->
<div id="modalReopenPeriod" style="display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.8); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0f172a; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px; width: 100%; max-width: 460px; padding: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
        <h3 style="margin: 0 0 8px; color: #fff; font-size: 18px;">🔓 Buka Kembali Periode Akuntansi</h3>
        <p style="margin: 0 0 16px; font-size: 13px; color: #94a3b8;">
            Anda akan membuka kunci periode <strong id="reopenPeriodLabel" style="color: #38bdf8;"></strong>. Jika periode ini memiliki Jurnal Penutup otomatis, jurnal tersebut akan dibalik (reversed).
        </p>
        <form id="formReopenPeriod" method="POST" action="">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; color: #cbd5e1; margin-bottom: 6px;">Alasan Pembukaan Kembali (Wajib untuk Audit Log):</label>
                <textarea name="reason" rows="3" required minlength="5" placeholder="Contoh: Koreksi faktur pembelian supplier bulan lalu yang terlambat masuk" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 10px; color: #fff; font-size: 13px;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeReopenModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #cbd5e1; border: none; padding: 8px 16px; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 18px; cursor: pointer;">Konfirmasi Buka Periode</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReopenModal(periodId, periodName) {
    const baseUrl = "{{ url('/admin/akuntansi/tutup-buku') }}";
    document.getElementById('formReopenPeriod').action = `${baseUrl}/${periodId}/reopen`;
    document.getElementById('reopenPeriodLabel').textContent = periodName;
    document.getElementById('modalReopenPeriod').style.display = 'flex';
}

function closeReopenModal() {
    document.getElementById('modalReopenPeriod').style.display = 'none';
}
</script>
@endsection
