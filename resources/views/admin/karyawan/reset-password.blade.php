@extends('admin.dashboard.layout')

@section('title', 'Verifikasi & Otorisasi Reset Password Karyawan (Maker-Checker & SoD)')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">🔑</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">Otorisasi Reset Password &amp; PIN Karyawan</h1>
                <span class="section-subtitle">Alur Maker-Checker &amp; Segregation of Duties (SoD) — Perlindungan Akun Anti Pembajakan</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.data') }}" class="btn-action-sm" style="background: rgba(2, 132, 199, 0.18); color: #38bdf8; border: 1px solid rgba(2, 132, 199, 0.35);">
                📊 Data Master ERP
            </a>
            <span class="badge-status {{ $pendingRequests->count() > 0 ? 'badge-amber' : 'badge-emerald' }}">
                {{ $pendingRequests->count() }} Menunggu Otorisasi Admin
            </span>
            <a href="{{ route('admin.karyawan.verifikasi') }}" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                🛡️ Verifikasi Pendaftaran
            </a>
            <a href="{{ route('admin.karyawan.unlock') }}" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                🔓 Buka Blokir
            </a>
            <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                👥 Master Karyawan
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="background: rgba(245, 158, 11, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Menunggu Review (Pending)</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $pendingRequests->count() }} Permohonan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Memerlukan otorisasi Admin/HR</div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Reset Disetujui</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $processedRequests->where('status', 'approved')->count() }} Karyawan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Kredensial baru telah aktif</div>
            </div>

            <div style="background: rgba(244, 63, 94, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(244, 63, 94, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #f43f5e; font-weight: 700;">Permohonan Ditolak</div>
                <div style="font-size: 26px; font-weight: 800; color: #f43f5e; font-family: 'Outfit'; margin-top: 4px;">{{ $processedRequests->where('status', 'rejected')->count() }} Permohonan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Dibatalkan atau tidak valid</div>
            </div>

            <div style="background: rgba(56, 189, 248, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">Keamanan Maker-Checker</div>
                <div style="font-size: 26px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin-top: 4px;">AKTIF 🛡️</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Mencegah pengambilalihan akun</div>
            </div>
        </div>

        @if(session('success'))
            <div style="margin-top: 16px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: 10px; padding: 12px 16px; color: #34d399; font-size: 13px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div style="margin-top: 16px; background: rgba(56, 189, 248, 0.15); border: 1px solid #38bdf8; border-radius: 10px; padding: 12px 16px; color: #7dd3fc; font-size: 13px;">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div style="margin-top: 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 10px; padding: 12px 16px; color: #fca5a5; font-size: 13px;">
                ⚠️ {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- TABEL 1: ANTREAN PERMOHONAN PENDING -->
    <div style="padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 8px;">
                <span>⏳</span> Antrean Permohonan Reset (Menunggu Otorisasi Checker)
            </h3>
            <span style="font-size: 12px; color: var(--text-sub);">
                Verifikasi via WhatsApp/telepon sebelum memberikan persetujuan
            </span>
        </div>

        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 12px 16px; color: var(--text-sub); font-weight: 600;">Waktu Pengajuan</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); font-weight: 600;">Karyawan</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); font-weight: 600;">Cabang &amp; Role</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); font-weight: 600;">Kontak WhatsApp</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); font-weight: 600;">IP &amp; Perangkat</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); font-weight: 600; text-align: right;">Aksi Otorisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                        @php
                            $phoneRaw = $req->phone ?: ($req->employee?->phone ?? '');
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneRaw);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: background 0.2s;">
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <div style="font-weight: 600; color: #fff;">{{ $req->created_at->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-sub);">{{ $req->created_at->format('H:i') }} WIB ({{ $req->created_at->diffForHumans() }})</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <div style="font-weight: 700; color: #38bdf8;">{{ $req->employee_name }}</div>
                                <div style="font-size: 11px; color: var(--text-sub); font-family: monospace;">{{ $req->employee_code ?: ($req->employee?->code ?? '-') }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <div>{{ $req->employee?->branch?->name ?? 'Semua Cabang' }}</div>
                                <div style="font-size: 11px; color: #a78bfa; text-transform: uppercase;">{{ $req->employee?->role?->value ?? 'Staff' }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                @if($cleanPhone)
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Halo '.$req->employee_name.', kami dari Admin MGK ingin mengonfirmasi apakah benar Anda mengajukan permohonan reset PIN/Password presensi?') }}" 
                                       target="_blank" 
                                       style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); padding: 5px 10px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600;"
                                       title="Hubungi &amp; Verifikasi Karyawan via WhatsApp">
                                        <span>💬</span>
                                        <span>{{ $phoneRaw }}</span>
                                    </a>
                                @else
                                    <span style="color: var(--text-sub); font-size: 12px;">Tidak ada nomor</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle;">
                                <div style="font-family: monospace; font-size: 11px; color: #94a3b8;">{{ $req->ip_address ?? '127.0.0.1' }}</div>
                                <div style="font-size: 11px; color: var(--text-sub); max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $req->user_agent }}">
                                    {{ $req->user_agent ?? 'Web Portal' }}
                                </div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: middle; text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <!-- Form Setujui -->
                                    <form method="POST" action="{{ route('admin.karyawan.reset-password.approve', $req->id) }}" onsubmit="return confirm('Apakah Anda yakin telah memverifikasi permohonan {{ $req->employee_name }} dan ingin MENYETUJUI reset kredensial ini?');">
                                        @csrf
                                        <button type="submit" class="btn-action-sm" style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-weight: 700;">
                                            ✅ Setujui
                                        </button>
                                    </form>

                                    <!-- Tombol Tolak (Modal Trigger) -->
                                    <button type="button" 
                                        class="btn-action-sm btn-trigger-reject" 
                                        data-id="{{ $req->id }}" 
                                        data-name="{{ $req->employee_name }}" 
                                        style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-weight: 700;">
                                        ❌ Tolak
                                    </button>

                                    <!-- Tombol Edit Catatan Internal -->
                                    <button type="button" 
                                        class="btn-action-sm btn-trigger-edit" 
                                        data-id="{{ $req->id }}" 
                                        data-name="{{ $req->employee_name }}" 
                                        data-status="pending" 
                                        data-notes="{{ $req->notes ?? '' }}" 
                                        data-reason="" 
                                        style="background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); color: #cbd5e1; cursor: pointer; padding: 6px 10px; border-radius: 8px;" 
                                        title="Edit Catatan Internal">
                                        ✏️ Edit
                                    </button>

                                    <!-- Form Hapus Permohonan -->
                                    <form method="POST" action="{{ route('admin.karyawan.reset-password.destroy', $req->id) }}" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS permohonan reset password dari {{ $req->employee_name }}?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; cursor: pointer; padding: 6px 10px; border-radius: 8px;" title="Hapus Permohonan">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 32px 16px; text-align: center; color: var(--text-sub);">
                                <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                                <div style="font-size: 14px; font-weight: 600; color: #fff;">Tidak ada permohonan reset password yang tertunda</div>
                                <div style="font-size: 12px; margin-top: 4px;">Semua permohonan reset kredensial karyawan telah selesai diproses.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABEL 2: RIWAYAT PERMOHONAN SELESAI -->
    <div style="padding: 0 24px 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>📜</span> Riwayat Permohonan Terakhir (Audit Trail ISO 27001)
        </h3>

        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 10px 14px; color: var(--text-sub);">Waktu Selesai</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Karyawan</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Cabang</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Status</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Diputuskan Oleh</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Catatan / Alasan</th>
                        <th style="padding: 10px 14px; color: var(--text-sub); text-align: center;">Aksi CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processedRequests as $req)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                            <td style="padding: 10px 14px; color: var(--text-sub);">
                                {{ $req->updated_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td style="padding: 10px 14px;">
                                <span style="font-weight: 600; color: #fff;">{{ $req->employee_name }}</span>
                                <span style="font-size: 11px; color: var(--text-sub);">({{ $req->employee_code ?: ($req->employee?->code ?? '-') }})</span>
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-sub);">
                                {{ $req->employee?->branch?->name ?? 'Semua Cabang' }}
                            </td>
                            <td style="padding: 10px 14px;">
                                @if($req->status === 'approved')
                                    <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        ✅ Disetujui
                                    </span>
                                @else
                                    <span style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">
                                        ❌ Ditolak
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; color: #38bdf8;">
                                {{ $req->approver?->name ?? 'Admin Sistem' }}
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-sub); font-style: italic;">
                                @if($req->status === 'rejected')
                                    <span style="color: #fca5a5;">{{ $req->rejection_reason ?: 'Tidak ada alasan' }}</span>
                                @else
                                    <span>{{ $req->notes ?: 'Verifikasi identitas valid' }}</span>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; text-align: center;">
                                <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: center;">
                                    <!-- Tombol Edit Catatan / Alasan -->
                                    <button type="button" 
                                        class="btn-action-sm btn-trigger-edit" 
                                        data-id="{{ $req->id }}" 
                                        data-name="{{ $req->employee_name }}" 
                                        data-status="{{ $req->status }}" 
                                        data-notes="{{ $req->notes ?? '' }}" 
                                        data-reason="{{ $req->rejection_reason ?? '' }}" 
                                        style="background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); color: #cbd5e1; cursor: pointer; padding: 4px 8px; border-radius: 6px; font-size: 11px;" 
                                        title="Koreksi Catatan">
                                        ✏️ Edit
                                    </button>

                                    <!-- Form Hapus Riwayat -->
                                    <form method="POST" action="{{ route('admin.karyawan.reset-password.destroy', $req->id) }}" onsubmit="return confirm('PERINGATAN: Hapus arsip permohonan reset {{ $req->employee_name }} secara permanen?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; cursor: pointer; padding: 4px 8px; border-radius: 6px; font-size: 11px;" title="Hapus Riwayat">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center; color: var(--text-sub);">
                                Belum ada riwayat permohonan reset sebelumnya.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TAUTAN NAVIGASI DI BAWAH TABEL OTORISASI RESET PASSWORD & PIN -->
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
                <a href="{{ route('admin.karyawan.unlock') }}" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); text-decoration: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">
                    🔓 Pusat Buka Blokir
                </a>
                <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.08); color: #fff; text-decoration: none; padding: 8px 12px; border-radius: 8px; font-size: 12px;">
                    👥 Master Karyawan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TOLAK PERMOHONAN -->
<div id="modal-reject" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0f172a; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; width: 100%; max-width: 440px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #f87171; font-size: 16px;">
                <span>❌</span>
                <span>Tolak Permohonan Reset</span>
            </div>
            <button type="button" onclick="closeRejectModal()" style="background: none; border: none; color: var(--text-sub); cursor: pointer; font-size: 18px;">✕</button>
        </div>

        <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
            Anda akan menolak permohonan reset kredensial untuk <strong id="reject-employee-name" style="color: #fff;"></strong>. Silakan masukkan alasan penolakan:
        </p>

        <form id="form-reject" method="POST" action="">
            @csrf
            <div style="margin-bottom: 16px;">
                <label for="rejection_reason" style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Alasan Penolakan:
                </label>
                <textarea 
                    name="rejection_reason" 
                    id="rejection_reason" 
                    rows="3" 
                    required 
                    minlength="3" 
                    placeholder="Contoh: Nomor kontak tidak dapat dihubungi, atau karyawan membatalkan permohonan..." 
                    style="width: 100%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit; font-size: 13px; outline: none;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRejectModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn-action-sm" style="background: #ef4444; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    Tolak Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT CATATAN / DATA PERMOHONAN (CRUD - EDIT) -->
<div id="modal-edit" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #0f172a; border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 16px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #38bdf8; font-size: 16px;">
                <span>✏️</span>
                <span>Edit Catatan Permohonan Reset</span>
            </div>
            <button type="button" onclick="closeEditModal()" style="background: none; border: none; color: var(--text-sub); cursor: pointer; font-size: 18px;">✕</button>
        </div>

        <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
            Koreksi data/catatan permohonan untuk karyawan <strong id="edit-employee-name" style="color: #fff;"></strong>:
        </p>

        <form id="form-edit" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div id="edit-notes-group" style="margin-bottom: 14px;">
                <label for="edit_notes" style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Catatan Internal / Keterangan Verifikasi:
                </label>
                <textarea 
                    name="notes" 
                    id="edit_notes" 
                    rows="3" 
                    placeholder="Contoh: Sudah dikonfirmasi langsung dengan SPV cabang..." 
                    style="width: 100%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit; font-size: 13px; outline: none;"></textarea>
            </div>

            <div id="edit-reason-group" style="margin-bottom: 16px;">
                <label for="edit_rejection_reason" style="display: block; font-size: 12px; font-weight: 600; color: #fca5a5; margin-bottom: 6px;">
                    Alasan Penolakan (Jika Berkas Ditolak):
                </label>
                <textarea 
                    name="rejection_reason" 
                    id="edit_rejection_reason" 
                    rows="3" 
                    placeholder="Alasan penolakan..." 
                    style="width: 100%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 10px; color: #fff; font-family: inherit; font-size: 13px; outline: none;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn-action-sm" style="background: #38bdf8; color: #0f172a; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const baseUrlReset = "{{ url('admin/karyawan-reset-password') }}";

    function openRejectModal(id, name) {
        const nameEl = document.getElementById('reject-employee-name');
        const formEl = document.getElementById('form-reject');
        const modalEl = document.getElementById('modal-reject');
        if (nameEl) nameEl.textContent = name;
        if (formEl) formEl.action = `${baseUrlReset}/${id}/reject`;
        if (modalEl) modalEl.style.display = 'flex';
    }

    function closeRejectModal() {
        const modalEl = document.getElementById('modal-reject');
        if (modalEl) modalEl.style.display = 'none';
    }

    function openEditModal(id, name, status, notes, reason) {
        const nameEl = document.getElementById('edit-employee-name');
        const formEl = document.getElementById('form-edit');
        const notesEl = document.getElementById('edit_notes');
        const reasonEl = document.getElementById('edit_rejection_reason');
        const modalEl = document.getElementById('modal-edit');
        
        if (nameEl) nameEl.textContent = name;
        if (formEl) formEl.action = `${baseUrlReset}/${id}`;
        if (notesEl) notesEl.value = notes || '';
        if (reasonEl) reasonEl.value = reason || '';
        
        const reasonGroup = document.getElementById('edit-reason-group');
        if (reasonGroup) {
            reasonGroup.style.display = (status === 'rejected') ? 'block' : 'block';
        }

        if (modalEl) modalEl.style.display = 'flex';
    }

    function closeEditModal() {
        const modalEl = document.getElementById('modal-edit');
        if (modalEl) modalEl.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Handle klik tombol Tolak
        document.querySelectorAll('.btn-trigger-reject').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openRejectModal(this.dataset.id, this.dataset.name);
            });
        });

        // Handle klik tombol Edit
        document.querySelectorAll('.btn-trigger-edit').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openEditModal(
                    this.dataset.id, 
                    this.dataset.name, 
                    this.dataset.status, 
                    this.dataset.notes, 
                    this.dataset.reason
                );
            });
        });

        // Tutup modal jika klik di luar area dialog
        const modalReject = document.getElementById('modal-reject');
        if (modalReject) {
            modalReject.addEventListener('click', function (e) {
                if (e.target === this) closeRejectModal();
            });
        }

        const modalEdit = document.getElementById('modal-edit');
        if (modalEdit) {
            modalEdit.addEventListener('click', function (e) {
                if (e.target === this) closeEditModal();
            });
        }

        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeRejectModal();
                closeEditModal();
            }
        });
    });
</script>
@endsection
