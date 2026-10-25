@extends('admin.dashboard.layout')

@section('title', 'Otorisasi Pembukaan Blokir Akun Karyawan (Maker-Checker)')

@section('content')
<div class="dashboard-section-card" style="max-width: 900px; margin: 0 auto;">
    <!-- HEADER -->
    <div class="section-card-header" style="border-bottom: 1px solid var(--border-color); padding: 20px 24px;">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(239, 68, 68, 0.15); color: #f87171; font-size: 22px;">🛡️</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">Otorisasi Pembukaan Blokir Akun (Unlock Approval)</h1>
                <span class="section-subtitle">Alur Maker-Checker — Menanggapi Peringatan Keamanan WhatsApp (Percobaan Gagal 3x)</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.data') }}" class="btn-action-sm" style="background: rgba(2, 132, 199, 0.18); color: #38bdf8; border: 1px solid rgba(2, 132, 199, 0.35); text-decoration: none;">
                📊 Data Master ERP
            </a>
            <a href="{{ route('admin.karyawan.unlock') }}" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); text-decoration: none;">
                📋 Riwayat Lockout
            </a>
            <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.08); color: #fff; text-decoration: none;">
                ← Master Karyawan
            </a>
        </div>
    </div>

    <div style="padding: 24px;">
        @if(session('success'))
            <div class="auth-alert-box auth-alert-success" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; color: #34d399; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">🎉</span>
                <div>
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; color: #f87171; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">⚠️</span>
                <div>
                    <strong>Gagal:</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        @if(!$isValidToken)
            <div style="background: rgba(239, 68, 68, 0.12); border: 1.5px solid rgba(239, 68, 68, 0.3); border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 20px;">
                <span style="font-size: 40px; display: block; margin-bottom: 12px;">🚫</span>
                <h3 style="color: #f87171; font-size: 18px; margin-bottom: 8px;">Token Tanda Tangan Tidak Valid</h3>
                <p style="color: var(--text-sub); font-size: 13.5px; line-height: 1.6; max-width: 600px; margin: 0 auto;">
                    Tautan otorisasi ini tidak valid atau telah dimodifikasi. Demi keamanan sistem (ISO 27001), tindakan pembukaan blokir melalui tautan ini dibatalkan.
                </p>
                <div style="margin-top: 20px;">
                    <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.1); color: #fff;">
                        Kembali ke Panel Karyawan
                    </a>
                </div>
            </div>
        @elseif($isExpired)
            <div style="background: rgba(245, 158, 11, 0.12); border: 1.5px solid rgba(245, 158, 11, 0.3); border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 20px;">
                <span style="font-size: 40px; display: block; margin-bottom: 12px;">⏱️</span>
                <h3 style="color: #fbbf24; font-size: 18px; margin-bottom: 8px;">Tautan Approval Telah Kedaluwarsa</h3>
                <p style="color: var(--text-sub); font-size: 13.5px; line-height: 1.6; max-width: 600px; margin: 0 auto;">
                    Masa berlaku token persetujuan darurat ini telah habis (batas waktu maksimal 30 menit). Jika akun karyawan masih terkunci, silakan verifikasi secara langsung melalui dashboard Admin.
                </p>
                <div style="margin-top: 20px;">
                    <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.1); color: #fff;">
                        Buka Master Data Karyawan
                    </a>
                </div>
            </div>
        @else
            <!-- STATUS WARNING BANNER -->
            <div style="background: rgba(239, 68, 68, 0.08); border: 1.5px solid rgba(239, 68, 68, 0.3); border-radius: 14px; padding: 18px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px;">
                <span style="font-size: 28px; line-height: 1;">🚨</span>
                <div>
                    <strong style="color: #f87171; font-size: 15px; display: block; margin-bottom: 4px;">Peringatan Keamanan: Terjadi Penguncian Akun (Lockout)</strong>
                    <p style="font-size: 13px; color: var(--text-sub); line-height: 1.5; margin: 0;">
                        Sistem mendeteksi 3 kali kegagalan login berturut-turut pada akun ini. Pesan peringatan telah diteruskan ke WhatsApp Admin. Sebagai <strong>Admin Checker</strong>, Anda berwenang memverifikasi keabsahan insiden ini dan membuka blokir akun.
                    </p>
                </div>
            </div>

            <!-- DETAIL GRID CARD -->
            <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px; margin-bottom: 24px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #38bdf8; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>📋</span>
                    <span>Profil Identitas Karyawan Sasaran</span>
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--text-sub); font-weight: 600;">Nama Lengkap</div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #fff; margin-top: 3px;">{{ $employee->name }}</div>
                    </div>

                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--text-sub); font-weight: 600;">NIP / Kode Karyawan</div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #38bdf8; margin-top: 3px;"><code>{{ $employee->code }}</code></div>
                    </div>

                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--text-sub); font-weight: 600;">Username Login</div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #fff; margin-top: 3px;"><code>{{ $employee->username ?? '-' }}</code></div>
                    </div>

                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--text-sub); font-weight: 600;">Cabang Penempatan</div>
                        <div style="font-size: 14px; color: var(--text-main); margin-top: 3px;">{{ $employee->branch?->name ?? 'Semua Cabang' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--text-sub); font-weight: 600;">Nomor Kontak WhatsApp</div>
                        <div style="font-size: 14px; color: var(--text-main); margin-top: 3px; display: flex; align-items: center; gap: 8px;">
                            <span>{{ $employee->phone ?? '-' }}</span>
                            @if($employee->phone)
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $employee->phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62'.substr($cleanPhone, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($employee->name) }},%20kami%20dari%20Tim%20Admin%20HRD%20ingin%20mengonfirmasi%20apakah%20tadi%20Anda%20mengalami%20kesalahan%20login%203x%20di%20portal%20presensi%3F" target="_blank" class="btn-action-sm" style="background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 2px 8px; font-size: 11px; text-decoration: none;">
                                    💬 Hubungi WA
                                </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; color: var(--text-sub); font-weight: 600;">Alamat IP Pelaku</div>
                        <div style="font-size: 14px; margin-top: 3px;">
                            <code style="color: #f87171; background: rgba(239, 68, 68, 0.12); padding: 3px 8px; border-radius: 6px;">
                                {{ $ip ?: ($lastLockoutLog?->ip_address ?? '127.0.0.1') }}
                            </code>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed var(--border-color); font-size: 12px; color: var(--text-sub); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div>
                        ⏱️ Waktu Terkunci: <strong>{{ $lastLockoutLog?->created_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }} WIB</strong>
                    </div>
                    <div>
                        ⏳ Token Berlaku Hingga: <strong>{{ \Carbon\Carbon::createFromTimestamp($expires)->format('H:i:s') }} WIB</strong> (30 Menit)
                    </div>
                </div>
            </div>

            <!-- ACTION FORM -->
            <form action="{{ route('admin.karyawan.unlock.process', $employee) }}" method="POST" style="background: rgba(16, 185, 129, 0.05); border: 1.5px solid rgba(16, 185, 129, 0.25); border-radius: 14px; padding: 22px;">
                @csrf
                <input type="hidden" name="ip" value="{{ $ip }}">
                <input type="hidden" name="expires" value="{{ $expires }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <h3 style="font-size: 15px; font-weight: 700; color: #34d399; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <span>✅</span>
                    <span>Tindakan Persetujuan Pembukaan Blokir (Approve)</span>
                </h3>

                <p style="font-size: 13px; color: var(--text-sub); line-height: 1.5; margin-bottom: 16px;">
                    Dengan menekan tombol persetujuan di bawah, sistem akan segera me-reset hitung mundur penguncian, membuka blokir akses akun untuk karyawan bersangkutan, dan mencatat jejak audit permanen ISO 27001.
                </p>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-main); margin-bottom: 6px;">
                        Catatan Verifikasi Admin (Opsional):
                    </label>
                    <input type="text" name="notes" placeholder="Contoh: Karyawan telah konfirmasi via telepon bahwa lupa PIN baru." style="width: 100%; padding: 10px 14px; background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <button type="submit" name="decision" value="approve" style="background: #10b981; color: #fff; font-weight: 700; font-size: 13.5px; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); transition: all 0.2s ease;">
                        <span>🔓</span>
                        <span>SETUJUI &amp; BUKA BLOKIR SEKARANG (APPROVE)</span>
                    </button>

                    <button type="submit" name="decision" value="reject" onclick="return confirm('Apakah Anda yakin ingin MENOLAK pembukaan blokir akun ini? Akun karyawan akan tetap dibekukan.');" style="background: rgba(239, 68, 68, 0.15); border: 1.5px solid rgba(239, 68, 68, 0.4); color: #f87171; font-weight: 700; font-size: 13px; padding: 12px 20px; border-radius: 10px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;">
                        <span>🚫</span>
                        <span>Tolak / Biarkan Terkunci</span>
                    </button>

                    <a href="{{ route('admin.karyawan.unlock') }}" style="background: rgba(255, 255, 255, 0.08); color: var(--text-sub); text-decoration: none; font-size: 13px; padding: 12px 18px; border-radius: 10px; display: inline-flex; align-items: center; gap: 6px;">
                        <span>📋</span>
                        <span>Riwayat Lockout</span>
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
