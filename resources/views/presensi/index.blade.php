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

    <!-- Ringkasan Statistik Bulan Ini -->
    <div class="glass-card" style="padding: 16px;">
        <div style="font-size: 13px; font-weight: 700; color: #e2e8f0; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span>📊 Ringkasan Presensi Bulan Ini</span>
            <span style="font-size: 11px; color: #94a3b8; font-weight: normal;">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center;">
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #34d399;">{{ $monthlyStats['total_hadir'] }}</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Hadir</div>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #fbbf24;">{{ $monthlyStats['total_telat'] }}</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Telat</div>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #818cf8;">{{ $monthlyStats['total_lembur_jam'] }}j</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Lembur</div>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #38bdf8;">{{ $monthlyStats['total_izin'] }}</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Izin/Cuti</div>
            </div>
        </div>
    </div>

    <!-- Banner Permohonan Cuti -->
    <div class="glass-card" style="margin-top: 16px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(14, 165, 233, 0.1)); border-color: rgba(99, 102, 241, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 4px;">
                    🏖️ Perlu Izin atau Cuti?
                </h4>
                <p style="font-size: 12px; color: #cbd5e1; margin: 0;">
                    Ajukan izin sakit, cuti tahunan, atau urusan penting resmi langsung dari portal mandiri Anda.
                </p>
            </div>
            <a href="{{ route('presensi.izin') }}" style="background: rgba(255, 255, 255, 0.15); color: #fff; text-decoration: none; padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 700; white-space: nowrap; border: 1px solid rgba(255, 255, 255, 0.25);">
                Buka Formulir →
            </a>
        </div>
    </div>
</div>
@endsection
