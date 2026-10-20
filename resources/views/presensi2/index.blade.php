@extends('layouts.presensi2')

@section('title', 'Dashboard Presensi v2')

@section('content')
{{-- KARTU PROFIL KARYAWAN --}}
<div class="glass-card" style="border-color: rgba(139, 92, 246, 0.2);">
    <div style="display: flex; align-items: center; gap: 14px;">
        <div style="width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, #7c3aed, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; flex-shrink: 0; box-shadow: 0 6px 18px rgba(139, 92, 246, 0.35);">
            👤
        </div>
        <div style="flex: 1; min-width: 0;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $currentEmployee->name }}
            </h3>
            <div style="display: flex; align-items: center; gap: 8px; margin-top: 3px; flex-wrap: wrap;">
                <span style="font-size: 12px; color: var(--text-muted);">{{ $currentEmployee->code ?? 'OP' }}</span>
                <span style="font-size: 10px; color: var(--text-sub);">•</span>
                <span style="font-size: 12px; color: var(--accent-cyan);">📍 {{ $currentEmployee->branch->name ?? 'MGK' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- STATUS PRESENSI HARI INI --}}
<div class="glass-card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <div>
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-sub);">Presensi Hari Ini</span>
            <h4 style="font-size: 14px; font-weight: 700; color: #e5e7eb; margin-top: 2px;">
                {{ \Carbon\Carbon::parse($workDate)->translatedFormat('l, d F Y') }}
            </h4>
        </div>
        @if($attendanceToday)
            @if($attendanceToday->clock_out_at)
                <span class="badge badge-present">✅ Selesai Dinas</span>
            @elseif($attendanceToday->status->value === 'late')
                <span class="badge badge-late">⏰ Terlambat</span>
            @else
                <span class="badge badge-present">✅ Sudah Masuk</span>
            @endif
        @else
            <span class="badge badge-absent">⚠️ Belum Absen</span>
        @endif
    </div>

    {{-- Shift Info --}}
    @php
        $displayShift = $attendanceToday?->shift ?? $activeShift;
    @endphp
    @if($displayShift)
    <div style="background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.15); border-radius: 12px; padding: 12px 14px; margin-bottom: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 11px; color: var(--text-muted);">{{ $attendanceToday ? 'Shift Kehadiran' : 'Shift Aktif' }}</span>
                <h4 style="font-size: 15px; font-weight: 700; color: #c4b5fd; margin-top: 2px;">{{ $displayShift->name }}</h4>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 20px; font-weight: 800; color: #fff; font-family: 'Outfit', monospace;">
                    {{ substr($displayShift->start_time, 0, 5) }} — {{ substr($displayShift->end_time, 0, 5) }}
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- Detail Presensi --}}
    @if($attendanceToday)
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
        <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 10px; padding: 10px 12px; text-align: center;">
            <span style="font-size: 11px; color: var(--text-muted);">Jam Masuk</span>
            <div style="font-size: 18px; font-weight: 700; color: #34d399; margin-top: 2px;">
                {{ $attendanceToday->clock_in_at?->format('H:i') ?? '-' }}
            </div>
        </div>
        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 10px; padding: 10px 12px; text-align: center;">
            <span style="font-size: 11px; color: var(--text-muted);">Jam Pulang</span>
            <div style="font-size: 18px; font-weight: 700; color: {{ $attendanceToday->clock_out_at ? '#fca5a5' : 'var(--text-sub)' }}; margin-top: 2px;">
                {{ $attendanceToday->clock_out_at?->format('H:i') ?? '—' }}
            </div>
        </div>
    </div>
    @endif

    {{-- CTA Buttons --}}
    @if(!$attendanceToday)
        <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 22px;">⚠️</span>
            <div style="font-size: 12px; line-height: 1.4; color: #fca5a5;">
                <strong style="color: #fff; display: block; font-size: 13px;">Presensi Dulu!</strong>
                Anda belum melakukan presensi masuk untuk shift hari ini. Wajib presensi dulu sebelum membuka serah terima stok barang & kasir penjualan.
            </div>
        </div>

        <a href="{{ route('presensi2.scan') }}" class="btn btn-emerald" style="padding: 16px; font-size: 15px; width: 100%; margin-bottom: 8px;">
            <span>🔐</span> Presensi Masuk Sekarang
        </a>
        <div style="font-size: 11px; text-align: center; color: var(--text-muted); line-height: 1.4;">
            ℹ️ Begitu klik konfirmasi presensi masuk, Anda akan langsung diarahkan ke Halaman Stok Barang & Serah Terima Shift.
        </div>
    @elseif(!$attendanceToday->clock_out_at)
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('presensi2.stok') }}" class="btn" style="padding: 14px; font-size: 14px; width: 100%; background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff; box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4); display: flex; justify-content: center; align-items: center; gap: 8px;">
                <span>📦</span> Buka Checklist Stok Barang
                @if(!$hasCompletedStock)
                    <span class="badge" style="background: rgba(245, 158, 11, 0.25); color: #fbbf24; font-size: 10px; margin-left: 6px;">Wajib Diisi</span>
                @else
                    <span class="badge" style="background: rgba(16, 185, 129, 0.25); color: #34d399; font-size: 10px; margin-left: 6px;">✓ Sudah Lengkap</span>
                @endif
            </a>
            <a href="{{ route('presensi2.penjualan') }}" class="btn btn-emerald" style="padding: 14px; font-size: 14px; width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px;"
               @if(!$hasCompletedStock)
                   onclick="event.preventDefault(); showIncompleteModal('Pemeriksaan Stok Belum Lengkap!', 'Anda belum menyelesaikan checklist pemeriksaan stok barang & serah terima shift. Silakan lengkapi pemeriksaan stok terlebih dahulu.', '<span>📦</span> Lengkapi Stok Sekarang', '{{ route('presensi2.stok') }}');"
               @endif>
                <span>🛒</span> Buka Halaman Penjualan & Kasir Shift
                @if(!$hasCompletedStock)
                    <span class="badge" style="background: rgba(245, 158, 11, 0.25); color: #fbbf24; font-size: 10px; margin-left: 6px;">Lengkapi Stok Dulu</span>
                @endif
            </a>
            <a href="{{ route('presensi2.rekap') }}" class="btn" style="padding: 14px; font-size: 14px; width: 100%; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4); display: flex; justify-content: center; align-items: center; gap: 8px;">
                <span>📊</span> Buka Rekap Penjualan & Laporan Shift
            </a>
            <a href="{{ route('presensi2.scan') }}" class="btn btn-rose" style="padding: 12px; font-size: 13px; width: 100%; opacity: 0.85;">
                <span>🏁</span> Check Out Presensi (Ganti Shift)
            </a>
        </div>
    @else
        <div style="text-align: center; padding: 10px 0;">
            <span style="font-size: 13px; color: var(--accent-emerald); font-weight: 600;">
                ✅ Presensi hari ini telah selesai. Terima kasih atas kerja keras Anda!
            </span>
            <div style="margin-top: 12px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('presensi2.stok') }}" class="btn" style="padding: 8px 14px; font-size: 12px; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); width: auto;">
                    📦 Lihat Stok Barang
                </a>
                <a href="{{ route('presensi2.penjualan') }}" class="btn" style="padding: 8px 14px; font-size: 12px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); width: auto;">
                    🛒 Penjualan
                </a>
                <a href="{{ route('presensi2.rekap') }}" class="btn" style="padding: 8px 14px; font-size: 12px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); width: auto;">
                    📊 Rekap Shift & Keuangan
                </a>
            </div>
        </div>
    @endif
</div>

{{-- STATISTIK BULANAN --}}
<div class="glass-card">
    <h4 style="font-size: 13px; font-weight: 700; color: #e5e7eb; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
        <span>📊</span> Statistik Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
    </h4>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
        <div style="background: rgba(16, 185, 129, 0.08); border-radius: 12px; padding: 12px; text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: #34d399;">{{ $monthlyStats['total_hadir'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Hari Hadir</div>
        </div>
        <div style="background: rgba(245, 158, 11, 0.08); border-radius: 12px; padding: 12px; text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: #fbbf24;">{{ $monthlyStats['total_telat'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Kali Telat</div>
        </div>
        <div style="background: rgba(6, 182, 212, 0.08); border-radius: 12px; padding: 12px; text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: #22d3ee;">{{ $monthlyStats['total_jam_kerja'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Jam Kerja</div>
        </div>
        <div style="background: rgba(139, 92, 246, 0.08); border-radius: 12px; padding: 12px; text-align: center;">
            <div style="font-size: 22px; font-weight: 800; color: #c4b5fd;">{{ $monthlyStats['total_izin'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Hari Izin/Cuti</div>
        </div>
    </div>
</div>
@endsection
