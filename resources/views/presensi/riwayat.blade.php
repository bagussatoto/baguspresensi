@extends('layouts.presensi')

@section('title', 'Riwayat Presensi')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">
            Riwayat Presensi
        </h1>
        <p style="font-size: 13px; color: #94a3b8;">
            Ringkasan Kehadiran dan riwayat absensi bulanan Anda.
        </p>
    </div>

    <!-- Ringkasan Kehadiran -->
    <div class="glass-card">
        <h2 style="font-size: 14px; font-weight: 700; color: #e2e8f0; margin-bottom: 12px;">
            Ringkasan Kehadiran
        </h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
            <div style="background: rgba(16, 185, 129, 0.1); border-radius: 12px; padding: 12px; text-align: center;">
                <div style="font-size: 24px; font-weight: 800; color: #34d399;">{{ $summary['hadir'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #94a3b8;">Total Hadir</div>
            </div>
            <div style="background: rgba(245, 158, 11, 0.1); border-radius: 12px; padding: 12px; text-align: center;">
                <div style="font-size: 24px; font-weight: 800; color: #fbbf24;">{{ $summary['telat'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #94a3b8;">Terlambat</div>
            </div>
        </div>
    </div>
</div>
@endsection
