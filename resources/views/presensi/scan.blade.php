@extends('layouts.presensi')

@section('title', 'Presensi Masuk (Clock-In) — Maya Graha Kencana')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Location Card -->
    <div class="glass-card" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted);">Cabang / Penempatan</span>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-top: 2px;">
                    📍 {{ $branch->name ?? 'Maya Graha Pusat' }}
                </h3>
            </div>
            <div>
                <span class="badge badge-present">Radius 100m</span>
            </div>
        </div>
    </div>

    <!-- Clock Display -->
    <div class="glass-card" style="text-align: center; padding: 24px 16px; margin-bottom: 16px;">
        <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px;">
            Presensi Masuk (Clock-In)
        </h2>
        <div style="font-size: 36px; font-weight: 800; color: #34d399; font-family: 'Outfit', monospace; margin: 8px 0;">
            {{ now()->format('H:i:s') }}
        </div>
        <p style="font-size: 12px; color: #94a3b8;">
            Pastikan Anda berada di area kantor saat melakukan scan selfie.
        </p>
    </div>
</div>
@endsection
