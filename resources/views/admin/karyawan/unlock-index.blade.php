@extends('admin.dashboard.layout')

@section('title', 'Otorisasi Pembukaan Blokir Akun Karyawan (Unlock Approval) — Admin ERP')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">🔓</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">Otorisasi Pembukaan Blokir Akun (Unlock Approval)</h1>
                <span class="section-subtitle">Pusat Pemantauan Insiden Brute-Force &amp; Otorisasi Pembukaan Blokir Akun Karyawan (ISO 27001)</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.data') }}" class="btn-action-sm" style="background: rgba(2, 132, 199, 0.18); color: #38bdf8; border: 1px solid rgba(2, 132, 199, 0.35);">
                📊 Data Master ERP
            </a>
            <a href="{{ route('admin.karyawan.verifikasi') }}" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                🛡️ Verifikasi Pendaftaran
            </a>
            <a href="{{ route('admin.karyawan.reset-password') }}" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                🔑 Reset PIN
            </a>
            <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.08); color: #fff;">
                👥 Master Karyawan
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <div style="background: rgba(239, 68, 68, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #f87171; font-weight: 700;">Insiden Lockout (24 Jam)</div>
                <div style="font-size: 26px; font-weight: 800; color: #f87171; font-family: 'Outfit'; margin-top: 4px;">{{ $totalLockout24h }} Kejadian</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Percobaan gagal 3x terdeteksi sistem</div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Blokir Berhasil Dibuka</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $totalUnlocked }} Karyawan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Telah di-approve oleh Admin HRD/IT</div>
            </div>

            <div style="background: rgba(56, 189, 248, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">Standar Keamanan Gateway</div>
                <div style="font-size: 16px; font-weight: 800; color: #fff; margin-top: 6px;">WhatsApp Alert Opsi A</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Signed Token HMAC (Kedaluwarsa 30 Menit)</div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div style="margin: 18px 24px 0; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 12px 16px; color: #34d399; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
            <span>🎉</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div style="margin: 18px 24px 0; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 10px; padding: 12px 16px; color: #7dd3fc; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
            <span>ℹ️</span>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="margin: 18px 24px 0; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 10px; padding: 12px 16px; color: #f87171; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- FILTER & SEARCH BAR -->
    <div style="padding: 20px 24px 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <form action="{{ route('admin.karyawan.unlock') }}" method="GET" style="display: flex; gap: 8px; flex: 1; max-width: 480px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama karyawan, NIP, atau alamat IP..." style="width: 100%; padding: 8px 14px; background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 13px;">
            <button type="submit" class="btn-action-sm" style="background: #0284c7; color: #fff;">
                🔍 Cari
            </button>
            @if(request('q'))
                <a href="{{ route('admin.karyawan.unlock') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.1); color: var(--text-sub);">
                    Reset
                </a>
            @endif
        </form>

        <div style="font-size: 12px; color: var(--text-sub);">
            Menampilkan <strong>{{ $logs->total() }}</strong> catatan riwayat insiden keamanan
        </div>
    </div>

    <!-- TABLE -->
    <div style="padding: 10px 24px 24px; overflow-x: auto;">
        <table class="table-erp" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: rgba(255, 255, 255, 0.04); border-bottom: 1px solid var(--border-color); text-align: left;">
                    <th style="padding: 12px 14px; color: var(--text-sub); font-size: 11.5px; text-transform: uppercase;">Waktu &amp; Tanggal</th>
                    <th style="padding: 12px 14px; color: var(--text-sub); font-size: 11.5px; text-transform: uppercase;">Karyawan Sasaran</th>
                    <th style="padding: 12px 14px; color: var(--text-sub); font-size: 11.5px; text-transform: uppercase;">Cabang &amp; Kontak</th>
                    <th style="padding: 12px 14px; color: var(--text-sub); font-size: 11.5px; text-transform: uppercase;">Alamat IP Pelaku</th>
                    <th style="padding: 12px 14px; color: var(--text-sub); font-size: 11.5px; text-transform: uppercase;">Status Insiden</th>
                    <th style="padding: 12px 14px; color: var(--text-sub); font-size: 11.5px; text-transform: uppercase; text-align: center;">Tindakan Admin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $empId = $log->auditable_id;
                        $employee = $employees[$empId] ?? null;
                        $vals = $log->new_values ?? [];
                        $empName = $vals['employee_name'] ?? ($employee?->name ?? 'Karyawan #'.$empId);
                        $empCode = $vals['employee_code'] ?? ($employee?->code ?? '-');
                        $ip = $vals['ip_address'] ?? ($vals['target_ip'] ?? ($log->ip_address ?? '127.0.0.1'));
                        $isUnlock = ($log->action === 'EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN');
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: background 0.15s ease;">
                        <td style="padding: 12px 14px; white-space: nowrap;">
                            <div style="font-weight: 600; color: #fff;">{{ $log->created_at?->format('d M Y') }}</div>
                            <div style="font-size: 11.5px; color: var(--text-sub);">{{ $log->created_at?->format('H:i:s') }} WIB</div>
                        </td>

                        <td style="padding: 12px 14px;">
                            <div style="font-weight: 700; color: #fff;">{{ $empName }}</div>
                            <div style="font-size: 11.5px; color: #38bdf8;"><code>{{ $empCode }}</code></div>
                        </td>

                        <td style="padding: 12px 14px;">
                            <div style="color: var(--text-main);">{{ $employee?->branch?->name ?? 'Kantor Pusat' }}</div>
                            <div style="margin-top: 3px;">
                                @if($employee?->phone)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $employee->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62'.substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($empName) }},%20kami%20dari%20Tim%20Admin%20HRD%20ingin%20mengonfirmasi%20terkait%20status%20login%20akun%20Anda." target="_blank" style="font-size: 11px; color: #34d399; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>💬</span> {{ $employee->phone }}
                                    </a>
                                @else
                                    <span style="font-size: 11px; color: var(--text-sub);">-</span>
                                @endif
                            </div>
                        </td>

                        <td style="padding: 12px 14px;">
                            <code style="background: rgba(0, 0, 0, 0.3); padding: 3px 8px; border-radius: 6px; color: #f87171; font-size: 12px;">
                                {{ $ip }}
                            </code>
                        </td>

                        <td style="padding: 12px 14px;">
                            @if($isUnlock)
                                <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>🟢</span> Telah Dibuka Blokir
                                </span>
                                <div style="font-size: 10.5px; color: var(--text-sub); margin-top: 4px;">
                                    Oleh: <strong>{{ $vals['unlocked_by'] ?? 'Admin' }}</strong>
                                </div>
                            @elseif($log->action === 'EMPLOYEE_LOCKOUT_REJECTED_BY_ADMIN')
                                <span style="background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.35); padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>🚫</span> Ditolak (Tetap Terkunci)
                                </span>
                                <div style="font-size: 10.5px; color: var(--text-sub); margin-top: 4px;">
                                    Oleh: <strong>{{ $vals['rejected_by'] ?? 'Admin' }}</strong>
                                </div>
                            @else
                                <span style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); padding: 4px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>🔴</span> Terkunci (3x Gagal)
                                </span>
                                <div style="font-size: 10.5px; color: var(--text-sub); margin-top: 4px;">
                                    Tier {{ $vals['tier'] ?? 1 }} ({{ $vals['lockout_duration_seconds'] ?? 60 }} detik)
                                </div>
                            @endif
                        </td>

                        <td style="padding: 12px 14px; text-align: center;">
                            @if($employee)
                                @php
                                    $expires = now()->addMinutes(30)->timestamp;
                                    $token = hash_hmac('sha256', $employee->id.'|'.$ip.'|'.$expires, config('app.key'));
                                    $unlockUrl = route('admin.karyawan.unlock.show', [
                                        'employee' => $employee->id,
                                        'ip' => $ip,
                                        'expires' => $expires,
                                        'token' => $token,
                                    ]);
                                @endphp
                                <a href="{{ $unlockUrl }}" class="btn-action-sm" style="background: {{ $isUnlock ? 'rgba(255, 255, 255, 0.08)' : '#10b981' }}; color: #fff; padding: 6px 12px; font-size: 11.5px; font-weight: 700; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                                    <span>🔓</span>
                                    <span>{{ $isUnlock ? 'Tinjau / Buka Ulang' : 'Otorisasi Buka Blokir' }}</span>
                                </a>
                            @else
                                <span style="font-size: 11px; color: var(--text-sub);">Data Terhapus</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 36px 20px; text-align: center; color: var(--text-sub);">
                            <span style="font-size: 32px; display: block; margin-bottom: 8px;">🛡️</span>
                            <div style="font-size: 14.5px; font-weight: 700; color: #34d399; margin-bottom: 4px;">Tidak Ada Insiden Lockout</div>
                            <div style="font-size: 12.5px;">Seluruh akun karyawan dalam keadaan aman dan tidak ada penguncian aktif.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- PAGINATION -->
        @if($logs->hasPages())
            <div style="margin-top: 20px;">
                {{ $logs->links() }}
            </div>
        @endif

        <!-- TAUTAN NAVIGASI DI BAWAH TABEL -->
        <div style="margin-top: 24px; padding: 18px 20px; background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('admin.data') }}" 
                   class="btn-action-sm" 
                   style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: 1px solid rgba(56, 189, 248, 0.5); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);">
                    <span style="font-size: 15px;">📊</span>
                    <span>Buka Data Master ERP (admin/data)</span>
                    <span style="font-size: 13px;">&rarr;</span>
                </a>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('admin.karyawan.reset-password') }}" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); text-decoration: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">
                    🔑 Otorisasi Reset Password &amp; PIN
                </a>
                <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.08); color: #fff; text-decoration: none; padding: 8px 12px; border-radius: 8px; font-size: 12px;">
                    👥 Master Karyawan
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
