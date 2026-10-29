@extends('layouts.presensi')

@section('title', 'Beranda Presensi Karyawan')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Profile Card -->
    <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, #10b981, #06b6d4); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; flex-shrink: 0; box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);">
                👤
            </div>
            <div>
                <h2 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 2px;">
                    {{ $currentEmployee->name }}
                </h2>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted);">
                    <span>NIK: {{ $currentEmployee->code }}</span>
                    <span>•</span>
                    <span>📍 {{ $currentEmployee->branch->name ?? 'Maya Graha Pusat' }}</span>
                </div>
            </div>
        </div>
        <div>
            @if($attendanceToday)
                <span class="badge badge-present">Sudah Absen</span>
            @else
                <span class="badge badge-absent">Belum Melakukan Clock-In</span>
            @endif
        </div>
    </div>

    <!-- Shift Info -->
    <div class="glass-card">
        <h3 style="font-size: 14px; font-weight: 700; color: #e2e8f0; margin-bottom: 8px;">
            Informasi Shift Hari Ini ({{ \Carbon\Carbon::parse($workDate)->translatedFormat('l, d F Y') }})
        </h3>
        <p style="font-size: 13px; color: #94a3b8;">
            Shift: <strong style="color: #fff;">{{ $activeShift->name ?? 'Shift Normal' }}</strong>
            @if($activeShift)
                ({{ substr($activeShift->start_time, 0, 5) }} - {{ substr($activeShift->end_time, 0, 5) }})
            @endif
        </p>
    </div>

    <!-- Quick Actions -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 14px;">
        <a href="{{ route('presensi.scan') }}" class="btn btn-emerald" style="padding: 14px;">
            📸 Masuk / Pulang
        </a>
        <a href="{{ route('presensi.riwayat') }}" class="btn btn-ghost" style="padding: 14px;">
            📅 Lihat Riwayat
        </a>
    </div>
</div>
@endsection
