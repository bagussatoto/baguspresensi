@extends('layouts.presensi')

@section('title', 'Login Presensi Karyawan')

@section('content')
<div style="padding: 24px 16px; max-width: 480px; margin: 40px auto; padding-bottom: 90px;">
    <!-- Brand / Header -->
    <div style="text-align: center; margin-bottom: 28px;">
        <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #10b981, #06b6d4); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px rgba(6, 182, 212, 0.25); margin-bottom: 12px;">
            🏢
        </div>
        <h1 style="font-size: 22px; font-weight: 800; color: #f8fafc; margin-bottom: 6px;">
            Presensi Karyawan
        </h1>
        <p style="font-size: 13.5px; color: #94a3b8;">
            Maya Graha Kencana &bull; Masuk Portal Mandiri
        </p>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #f87171; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ⚠ {{ session('error') }}
        </div>
    @endif

    <!-- Login Form Card -->
    <div style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
        <form action="{{ route('presensi.login.post') }}" method="POST">
            @csrf

            <!-- Ketik Identitas Nama / NIK -->
            <div style="margin-bottom: 20px;">
                <label for="employee_name" style="display: block; font-size: 13px; font-weight: 600; color: #e2e8f0; margin-bottom: 8px;">
                    Ketik Nama Karyawan atau NIK
                </label>
                <input 
                    type="text" 
                    id="employee_name" 
                    name="employee_name" 
                    required 
                    autocomplete="off"
                    placeholder="Contoh: Bagus Karyawan atau EMP-001"
                    style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 14px 16px; color: #fff; font-size: 14px; outline: none; transition: all 0.2s;"
                >
                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">
                    🔒 Ketik nama Anda sendiri secara manual demi kerahasiaan identitas akun.
                </div>
            </div>

            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #10b981, #0d9488); border: none; color: #fff; font-weight: 700; padding: 14px 18px; border-radius: 12px; font-size: 15px; cursor: pointer; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
                Masuk Sesi Presensi →
            </button>
        </form>
    </div>

    <!-- Switch Portal Link -->
    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ route('presensi2.login') }}" style="color: #38bdf8; text-decoration: none; font-size: 13px; font-weight: 600;">
            Gunakan Portal Presensi v2 (Lengkap dengan PIN) →
        </a>
    </div>
</div>
@endsection
