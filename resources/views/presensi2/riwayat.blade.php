@extends('layouts.presensi2')

@section('title', 'Riwayat Presensi')

@section('content')
{{-- FILTER BULAN/TAHUN --}}
<div class="glass-card" style="padding: 14px 16px;">
    <form method="GET" action="{{ route('presensi2.riwayat') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <select name="bulan" class="form-input" style="flex: 1; min-width: 100px; padding: 10px 14px; font-size: 13px;">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                </option>
            @endfor
        </select>
        <select name="tahun" class="form-input" style="flex: 0.7; min-width: 80px; padding: 10px 14px; font-size: 13px;">
            @for($y = date('Y'); $y >= 2024; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="btn btn-primary" style="flex: 0.5; min-width: 70px; padding: 10px; font-size: 13px;">
            🔍 Filter
        </button>
    </form>
</div>

{{-- RINGKASAN BULANAN --}}
<div class="glass-card">
    <h4 style="font-size: 13px; font-weight: 700; color: #e5e7eb; margin-bottom: 12px;">
        📊 Ringkasan {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
    </h4>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
        <div style="background: rgba(16, 185, 129, 0.08); border-radius: 10px; padding: 10px; text-align: center;">
            <div style="font-size: 20px; font-weight: 800; color: #34d399;">{{ $summary['total_hadir'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Hadir</div>
        </div>
        <div style="background: rgba(245, 158, 11, 0.08); border-radius: 10px; padding: 10px; text-align: center;">
            <div style="font-size: 20px; font-weight: 800; color: #fbbf24;">{{ $summary['total_telat'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Telat</div>
        </div>
        <div style="background: rgba(239, 68, 68, 0.08); border-radius: 10px; padding: 10px; text-align: center;">
            <div style="font-size: 20px; font-weight: 800; color: #fca5a5;">{{ $summary['menit_telat'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Menit Telat</div>
        </div>
        <div style="background: rgba(6, 182, 212, 0.08); border-radius: 10px; padding: 10px; text-align: center;">
            <div style="font-size: 20px; font-weight: 800; color: #22d3ee;">{{ $summary['total_jam_kerja'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted);">Jam Kerja</div>
        </div>
    </div>
</div>

{{-- DAFTAR RIWAYAT HARIAN --}}
<div style="margin-bottom: 8px;">
    <h4 style="font-size: 13px; font-weight: 700; color: #e5e7eb; padding: 0 4px;">📅 Riwayat Harian</h4>
</div>

@forelse($records as $record)
    <div class="glass-card" style="padding: 14px 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div>
                <span style="font-size: 13px; font-weight: 700; color: #fff;">
                    {{ $record->work_date?->translatedFormat('D, d M Y') ?? '-' }}
                </span>
            </div>
            @php
                $badgeClass = match($record->status->value) {
                    'present' => 'badge-present',
                    'late' => 'badge-late',
                    'absent' => 'badge-absent',
                    'on_leave' => 'badge-leave',
                    default => 'badge-present',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $record->status->label() }}</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; font-size: 12px;">
            <div style="text-align: center;">
                <div style="color: var(--text-muted); font-size: 11px;">Masuk</div>
                <div style="font-weight: 700; color: #34d399;">{{ $record->clock_in_at?->format('H:i') ?? '-' }}</div>
            </div>
            <div style="text-align: center;">
                <div style="color: var(--text-muted); font-size: 11px;">Pulang</div>
                <div style="font-weight: 700; color: #fca5a5;">{{ $record->clock_out_at?->format('H:i') ?? '-' }}</div>
            </div>
            <div style="text-align: center;">
                <div style="color: var(--text-muted); font-size: 11px;">Durasi</div>
                <div style="font-weight: 700; color: #22d3ee;">
                    {{ $record->duration_minutes ? round($record->duration_minutes / 60, 1).' Jam' : '-' }}
                </div>
            </div>
        </div>

        @if($record->shift)
        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--border-glass); font-size: 11px; color: var(--text-sub);">
            🔄 {{ $record->shift->name }} ({{ substr($record->shift->start_time, 0, 5) }} - {{ substr($record->shift->end_time, 0, 5) }})
            @if($record->late_minutes > 0)
                <span style="color: #fbbf24; margin-left: 8px;">⏰ Telat {{ $record->late_minutes }} menit</span>
            @endif
        </div>
        @endif
    </div>
@empty
    <div class="glass-card" style="text-align: center; padding: 30px 18px;">
        <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
        <p style="font-size: 13px; color: var(--text-muted);">
            Belum ada riwayat presensi pada bulan ini.
        </p>
    </div>
@endforelse
@endsection
