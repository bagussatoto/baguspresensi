@extends('layouts.presensi')

@section('title', 'Profil Karyawan')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Profile Card -->
    <div class="glass-card" style="display: flex; align-items: center; gap: 16px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #06b6d4); display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; color: #fff;">
            {{ strtoupper(substr($currentEmployee->name, 0, 1)) }}
        </div>
        <div>
            <h2 style="font-size: 18px; font-weight: 700; color: #fff;">
                {{ $currentEmployee->name }}
            </h2>
            <div style="font-size: 13px; color: #94a3b8; display: flex; gap: 8px;">
                <span>NIK: {{ $currentEmployee->code }}</span>
                <span>•</span>
                <span>{{ $currentEmployee->position ?? 'Staff' }}</span>
            </div>
        </div>
    </div>

    <!-- Logout -->
    <div style="margin-top: 20px;">
        <form method="POST" action="{{ route('presensi.logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="color: #f87171; border-color: rgba(239, 68, 68, 0.3);">
                🚪 Keluar Sesi Presensi
            </button>
        </form>
    </div>
</div>
@endsection
