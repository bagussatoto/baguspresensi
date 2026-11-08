@extends('layouts.presensi2')

@section('title', 'Cek Fisik Selesai — Beralih ke PC Desktop')

@section('content')
<div class="stok-selesai-page" style="max-width: 650px; margin: 0 auto; padding: 20px 16px 80px;">

    {{-- KARTU SUKSES UTAMA --}}
    <div class="glass-card" style="text-align: center; padding: 32px 20px; border-color: rgba(16, 185, 129, 0.4); background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(15, 23, 42, 0.95)); border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); margin-bottom: 20px;">
        <div style="width: 72px; height: 72px; margin: 0 auto 16px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; font-size: 36px; color: #fff; box-shadow: 0 0 25px rgba(16, 185, 129, 0.5);">
            ✓
        </div>

        <h1 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-bottom: 6px; letter-spacing: -0.3px;">
            Cek Fisik & Serah Terima Berhasil!
        </h1>
        <p style="font-size: 13px; color: var(--text-muted); max-width: 480px; margin: 0 auto; line-height: 1.5;">
            Seluruh data fisik kas laci, stok kertas print, minuman kulkas, makanan etalase, dan PC station telah terverifikasi resmi ke sistem.
        </p>

        {{-- PILL INFO SHIFT --}}
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-glass); border-radius: 30px; padding: 6px 16px; margin-top: 16px; font-size: 12px;">
            <span>👤 <strong style="color: #38bdf8;">{{ $currentEmployee->name }}</strong></span>
            <span style="color: var(--text-sub);">&bull;</span>
            <span>⏰ <strong style="color: #34d399;">{{ $activeShift?->name ?? 'Shift Aktif' }}</strong></span>
            <span style="color: var(--text-sub);">&bull;</span>
            <span>📅 {{ $workDate->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    {{-- KARTU PETUNJUK UTAMA: BERALIH KE PC KASIR DESKTOP --}}
    <div class="glass-card" style="padding: 24px 20px; border-color: rgba(56, 189, 248, 0.35); background: rgba(15, 23, 42, 0.85); border-radius: 18px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--border-glass);">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                🖥️
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #38bdf8; margin: 0;">
                    Langkah Selanjutnya: Silakan Beralih ke PC Meja Kasir
                </h3>
                <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                    HP Anda dapat disimpan di saku/loker selama bertugas melayani pelanggan
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 12.5px; line-height: 1.5; color: #cbd5e1;">
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <span style="background: #0284c7; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">1</span>
                <div>
                    <strong>Menuju Komputer Kasir (PC Desktop):</strong> Silakan duduk di meja kasir operasional cabang.
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <span style="background: #0284c7; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">2</span>
                <div>
                    <strong>Login PIN Kasir di PC Desktop:</strong> Buka layar kasir di PC, pilih nama Anda (<strong>{{ $currentEmployee->name }}</strong>), lalu masukkan PIN 4 digit untuk membuka workspace POS Desktop.
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <span style="background: #0284c7; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">3</span>
                <div>
                    <strong>Melayani Transaksi:</strong> Seluruh nota belanja F&B, billing warnet, dan cetak print kertas dijalankan melalui PC Desktop.
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <span style="background: #10b981; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">4</span>
                <div>
                    <strong>Nanti Saat Selesai Shift:</strong> Lakukan Rekapitulasi & Serah Terima Uang di PC Desktop. Setelah selesai, buka kembali HP ini untuk melakukan <strong>Foto Selfie Presensi Pulang (Clock-Out)</strong>.
                </div>
            </div>
        </div>
    </div>

    {{-- TOMBOL TINDAKAN --}}
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <a href="{{ route('presensi2.index') }}" class="btn" style="padding: 14px 20px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.4);">
            <span>🏠</span> Kembali ke Beranda Presensi
        </a>

        <div style="display: flex; gap: 10px; margin-top: 4px;">
            <a href="{{ route('presensi2.penjualan') }}" class="btn" style="flex: 1; padding: 12px 14px; font-size: 12px; font-weight: 600; background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-glass); color: var(--text-muted); border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;" title="Gunakan hanya jika PC Kasir mati lampu atau dalam kondisi darurat">
                <span>📱</span> POS Versi HP (Cadangan)
            </a>
            <a href="{{ route('presensi2.logout') }}" class="btn" style="flex: 1; padding: 12px 14px; font-size: 12px; font-weight: 600; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); color: #fca5a5; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;" onclick="return confirm('Keluar dari sesi HP?')">
                <span>🚪</span> Keluar Sesi HP
            </a>
        </div>
    </div>

</div>
@endsection

