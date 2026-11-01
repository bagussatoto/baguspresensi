@extends('layouts.presensi')

@section('title', 'Login Presensi Karyawan')

@section('content')
<div style="padding: 24px 16px; max-width: 480px; margin: 30px auto; padding-bottom: 90px;">
    <!-- Brand / Header -->
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #10b981, #06b6d4); display: inline-flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px rgba(6, 182, 212, 0.25); margin-bottom: 12px;">
            🏢
        </div>
        <h1 style="font-size: 22px; font-weight: 800; color: #f8fafc; margin-bottom: 6px;">
            Pengajuan Izin Cuti
        </h1>
        <p style="font-size: 13.5px; color: #94a3b8;">
            Maya Graha Kencana 
        </p>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #f87171; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px; line-height: 1.45;">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- Panduan Pengajuan Izin & Cuti -->
    <div style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: 14px; padding: 12px 14px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 10px;">
        <span style="font-size: 18px; line-height: 1;">📝</span>
        <div style="font-size: 12px; color: #bae6fd; line-height: 1.45;">
            <strong style="color: #fff;">Pengajuan Izin & Cuti:</strong> Masukkan Nama/NIK dan Password/PIN Anda di bawah ini untuk masuk ke portal. Setelah masuk, Anda dapat langsung mengajukan cuti tahunan atau izin sakit melalui menu <strong>Izin / Cuti</strong>.
        </div>
    </div>

    <!-- Login Form Card -->
    <div style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.4);">
        <form action="{{ route('presensi.login.post') }}" method="POST">
            @csrf

            <!-- 1. Identitas Nama / NIK -->
            <div style="margin-bottom: 18px;">
                <label for="employee_name" style="display: block; font-size: 13px; font-weight: 600; color: #e2e8f0; margin-bottom: 8px;">
                    Nama Karyawan atau NIK <span style="color: #f43f5e;">*</span>
                </label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none; opacity: 0.7;">👤</span>
                    <input 
                        type="text" 
                        id="employee_name" 
                        name="employee_name" 
                        value="{{ old('employee_name') }}"
                        required 
                        autocomplete="username"
                        placeholder="Contoh: Bagus Karyawan atau EMP-001"
                        style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 13px 14px 13px 42px; color: #fff; font-size: 13.5px; outline: none; transition: border-color 0.2s;"
                    >
                </div>
            </div>

            <!-- 2. Password atau PIN -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <label for="credential" style="font-size: 13px; font-weight: 600; color: #e2e8f0;">
                        Password atau PIN 6-Digit <span style="color: #f43f5e;">*</span>
                    </label>
                    <a href="{{ route('presensi2.forgot-password') }}" style="font-size: 11.5px; color: #38bdf8; text-decoration: none;">
                        Lupa Password?
                    </a>
                </div>
                <div style="position: relative;">
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none; opacity: 0.7;">🔒</span>
                    <input 
                        type="password" 
                        id="credential" 
                        name="credential" 
                        required 
                        autocomplete="current-password"
                        placeholder="Ketik password atau PIN Anda"
                        style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 13px 42px 13px 42px; color: #fff; font-size: 13.5px; outline: none; transition: border-color 0.2s;"
                    >
                    <button 
                        type="button" 
                        id="btn-toggle-password" 
                        onclick="togglePasswordVisibility()" 
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 16px; padding: 4px;"
                        title="Tampilkan / Sembunyikan Password"
                    >
                        👁️
                    </button>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px; font-size: 11.5px; color: #64748b;">
                    <span>🛡️ Dilengkapi proteksi anti-brute force (3x batas gagal).</span>
                    <a href="{{ route('presensi2.check-reset-status') }}" style="color: #94a3b8; text-decoration: none;">Cek Status Reset</a>
                </div>
            </div>

            <!-- Tombol Masuk -->
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #10b981, #0d9488); border: none; color: #fff; font-weight: 700; padding: 14px 18px; border-radius: 12px; font-size: 15px; cursor: pointer; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3); transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span>🔐</span>
                <span>Masuk Sesi Presensi →</span>
            </button>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('credential');
        const btn = document.getElementById('btn-toggle-password');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerText = '🙈';
        } else {
            input.type = 'password';
            btn.innerText = '👁️';
        }
    }
</script>
@endsection
