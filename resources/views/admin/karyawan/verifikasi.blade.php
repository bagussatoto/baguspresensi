@extends('admin.dashboard.layout')

@section('title', 'Verifikasi Pendaftaran Mandiri Karyawan (SoD & ISO 27001)')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">🛡️</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">Verifikasi Pendaftaran Mandiri Karyawan</h1>
                <span class="section-subtitle">Alur Maker-Checker &amp; Staging Area Menuju Single Source of Truth (SoT) Karyawan Resmi</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <span class="badge-status {{ $pendingRegistrations->count() > 0 ? 'badge-amber' : 'badge-emerald' }}">
                {{ $pendingRegistrations->count() }} Menunggu Otorisasi Admin
            </span>
            <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                👥 Kelola Master Karyawan
            </a>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Dashboard
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="background: rgba(245, 158, 11, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Menunggu Review (Pending)</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $pendingRegistrations->count() }} Orang</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Tersimpan aman di staging area</div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Disetujui ke Master Data</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $processedRegistrations->where('status', 'approved')->count() }} Orang</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Resmi aktif dan memiliki NIK</div>
            </div>

            <div style="background: rgba(244, 63, 94, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(244, 63, 94, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #f43f5e; font-weight: 700;">Permohonan Ditolak</div>
                <div style="font-size: 26px; font-weight: 800; color: #f43f5e; font-family: 'Outfit'; margin-top: 4px;">{{ $processedRegistrations->where('status', 'rejected')->count() }} Berkas</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Tidak memenuhi syarat verifikasi</div>
            </div>

            <div style="background: rgba(56, 189, 248, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">Kepatuhan SoD &amp; ISO 27001</div>
                <div style="font-size: 26px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin-top: 4px;">100%</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Jejak audit terekam permanen</div>
            </div>
        </div>

        <!-- PRINCIPLE EXPLANATION BANNER -->
        <div style="margin-top: 18px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 22px;">⚖️</span>
                <div>
                    <strong style="color: #38bdf8; font-size: 13px;">Prinsip Segregation of Duties (SoD) &amp; Single Source of Truth (SoT):</strong>
                    <p style="margin: 2px 0 0; color: var(--text-muted); font-size: 12px; line-height: 1.4;">
                        Calon karyawan hanya berhak mengajukan identitas mandiri (Maker). Hanya Admin berwenang (Checker) yang dapat mengotorisasi, menetapkan cabang, dan memberikan hak akses resmi. Master Data Karyawan 100% steril dari pendaftar liar.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <div style="padding: 16px 24px 0;">
        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); color: #34d399; padding: 12px 16px; border-radius: 10px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <span>✅</span> <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #f87171; padding: 12px 16px; border-radius: 10px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span> <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); color: #38bdf8; padding: 12px 16px; border-radius: 10px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                <span>ℹ️</span> <span>{{ session('info') }}</span>
            </div>
        @endif
    </div>

    <!-- TABLE 1: ANTREAN MENUNGGU VERIFIKASI (PENDING) -->
    <div style="padding: 20px 24px 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">📥</span>
                <h2 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Antrean Pendaftaran Mandiri Menunggu Otorisasi</h2>
            </div>
            <span style="font-size: 12px; color: var(--text-sub);">
                Total: <strong>{{ $pendingRegistrations->count() }} Permohonan</strong>
            </span>
        </div>

        <div class="custom-table-responsive" style="border: 1px solid var(--border-glass); border-radius: 12px; overflow: hidden; background: rgba(15, 23, 42, 0.5);">
            <table class="dashboard-custom-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                <thead>
                    <tr style="background: rgba(30, 41, 59, 0.8); border-bottom: 1px solid var(--border-glass); color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 12px 16px;">No</th>
                        <th style="padding: 12px 16px;">Waktu Daftar</th>
                        <th style="padding: 12px 16px;">Nama Lengkap Pemohon</th>
                        <th style="padding: 12px 16px;">Username Pilihan</th>
                        <th style="padding: 12px 16px;">Kontak WhatsApp</th>
                        <th style="padding: 12px 16px;">Sidik Jari Perangkat (ISO Audit)</th>
                        <th style="padding: 12px 16px; text-align: center;">Status</th>
                        <th style="padding: 12px 16px; text-align: center;">Tindakan Otorisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRegistrations as $idx => $reg)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s ease;" onmouseover="this.style.background='rgba(56, 189, 248, 0.04)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 16px; color: var(--text-sub); font-weight: 600;">{{ $idx + 1 }}</td>
                            <td style="padding: 12px 16px; color: var(--text-muted); white-space: nowrap;">
                                {{ $reg->created_at->format('d M Y') }}<br>
                                <span style="font-size: 11px; color: var(--text-sub);">{{ $reg->created_at->format('H:i') }} WIB ({{ $reg->created_at->diffForHumans() }})</span>
                            </td>
                            <td style="padding: 12px 16px; font-weight: 700; color: #fff;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
                                        {{ strtoupper(substr($reg->full_name, 0, 1)) }}
                                    </span>
                                    <span>{{ $reg->full_name }}</span>
                                </div>
                            </td>
                            <td style="padding: 12px 16px; font-family: monospace; color: #38bdf8; font-weight: 600;">
                                {{ $reg->username }}
                            </td>
                            <td style="padding: 12px 16px; color: var(--text-muted);">
                                @if($reg->phone)
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $reg->phone)) }}" target="_blank" style="color: #34d399; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Chat via WhatsApp">
                                        <span>💬</span> {{ $reg->phone }}
                                    </a>
                                @else
                                    <span style="color: var(--text-sub);">-</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; font-size: 11px; color: var(--text-sub);">
                                <code>{{ $reg->ip_address ?? '127.0.0.1' }}</code><br>
                                <span style="font-size: 10px;" title="{{ $reg->user_agent }}">{{ Str::limit($reg->user_agent ?? 'Browser Web', 28) }}</span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span style="background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                    Pending Review
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center; white-space: nowrap;">
                                <button type="button" 
                                    class="btn-action-sm" 
                                    style="background: #10b981; color: #ffffff; font-weight: 700; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);"
                                    onclick="openApprovalModal({{ json_encode($reg) }})">
                                    <span>✓</span> Review &amp; Setujui
                                </button>
                                <button type="button" 
                                    class="btn-action-sm" 
                                    style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 600; padding: 6px 10px; border-radius: 6px; cursor: pointer; margin-left: 6px;"
                                    onclick="openRejectModal({{ json_encode($reg) }})">
                                    <span>✕</span> Tolak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-sub);">
                                <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                                <div style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Semua Bersih! Tidak Ada Antrean Pendaftaran Mandiri</div>
                                <div style="font-size: 12px; margin-top: 4px;">Pendaftar baru yang mengisi form akan otomatis muncul di sini untuk Anda otorisasi.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TABLE 2: RIWAYAT KEPUTUSAN VERIFIKASI (AUDIT TRAIL) -->
    <div style="padding: 24px 24px 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">📜</span>
                <h2 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Riwayat Keputusan Verifikasi Terakhir (Audit Trail ISO 27001)</h2>
            </div>
            <span style="font-size: 12px; color: var(--text-sub);">
                30 Keputusan Terkini
            </span>
        </div>

        <div class="custom-table-responsive" style="border: 1px solid var(--border-glass); border-radius: 12px; overflow: hidden; background: rgba(15, 23, 42, 0.5);">
            <table class="dashboard-custom-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                <thead>
                    <tr style="background: rgba(30, 41, 59, 0.8); border-bottom: 1px solid var(--border-glass); color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 10px 14px;">Tanggal Keputusan</th>
                        <th style="padding: 10px 14px;">Nama Pendaftar</th>
                        <th style="padding: 10px 14px;">Username</th>
                        <th style="padding: 10px 14px;">Status Akhir</th>
                        <th style="padding: 10px 14px;">Diverifikasi Oleh (Checker)</th>
                        <th style="padding: 10px 14px;">Hasil di Master Data (SoT)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processedRegistrations as $item)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td style="padding: 10px 14px; color: var(--text-muted); font-size: 11.5px;">
                                {{ $item->approved_at ? $item->approved_at->format('d M Y H:i') : $item->updated_at->format('d M Y H:i') }} WIB
                            </td>
                            <td style="padding: 10px 14px; font-weight: 600; color: #fff;">
                                {{ $item->full_name }}
                            </td>
                            <td style="padding: 10px 14px; font-family: monospace; color: var(--text-muted);">
                                {{ $item->username }}
                            </td>
                            <td style="padding: 10px 14px;">
                                @if($item->status === 'approved')
                                    <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                        ✓ Disetujui
                                    </span>
                                @else
                                    <span style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;" title="{{ $item->rejection_reason }}">
                                        ✕ Ditolak: {{ Str::limit($item->rejection_reason, 20) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-muted); font-size: 12px;">
                                👤 {{ $item->approver?->name ?? 'Admin / System' }}
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-muted); font-size: 12px;">
                                @if($item->createdEmployee)
                                    <a href="{{ route('admin.karyawan') }}#profile-{{ $item->createdEmployee->id }}" style="color: #38bdf8; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>🏷️</span> {{ $item->createdEmployee->name }} ({{ $item->createdEmployee->code }})
                                    </a>
                                @else
                                    <span style="color: var(--text-sub);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 24px; text-align: center; color: var(--text-sub); font-size: 12px;">
                                Belum ada riwayat approval pendaftaran mandiri.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL REVIEW & APPROVAL (MAKER VS CHECKER SPLIT - SoD & ISO 27001)        -->
<!-- ========================================================================= -->
<div id="modalApproval" style="display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 16px; width: 100%; max-width: 760px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6); overflow: hidden; animation: popIn 0.25s ease-out;">
        
        <!-- MODAL HEADER -->
        <div style="background: linear-gradient(90deg, #0284c7, #0f172a); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.08);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">🛡️</span>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #fff;">Otorisasi Pendaftaran Karyawan Mandiri</h3>
                    <span style="font-size: 11px; color: #e0f2fe;">Pemisahan Wewenang (SoD) &amp; Validasi Masuk ke Master Data (SoT)</span>
                </div>
            </div>
            <button type="button" onclick="closeApprovalModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formApproval" method="POST" action="">
            @csrf
            <div style="padding: 24px; display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px;">
                
                <!-- SISI KIRI: DATA PEMOHON (MAKER - READ ONLY) -->
                <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 12px; padding: 18px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 8px;">
                        <span style="font-size: 14px;">📋</span>
                        <strong style="font-size: 12px; color: #38bdf8; text-transform: uppercase;">1. Data Pengajuan (Terkunci)</strong>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 11px; color: var(--text-sub); display: block; margin-bottom: 2px;">Nama Lengkap:</label>
                        <div id="modal_view_name" style="font-size: 14px; font-weight: 700; color: #fff;">-</div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 11px; color: var(--text-sub); display: block; margin-bottom: 2px;">Username Akun:</label>
                        <div id="modal_view_username" style="font-family: monospace; font-size: 13px; color: #38bdf8; font-weight: 700;">-</div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 11px; color: var(--text-sub); display: block; margin-bottom: 2px;">Nomor WhatsApp:</label>
                        <div id="modal_view_phone" style="font-size: 13px; color: #34d399; font-weight: 600;">-</div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 11px; color: var(--text-sub); display: block; margin-bottom: 2px;">Waktu &amp; IP Pengajuan:</label>
                        <div id="modal_view_meta" style="font-size: 11px; color: var(--text-muted);">-</div>
                    </div>

                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 8px 10px; font-size: 11px; color: #34d399; margin-top: 14px;">
                        ✓ <strong>Integritas SoT:</strong> Kredensial telah diverifikasi belum pernah terdaftar di Master Data.
                    </div>
                </div>

                <!-- SISI KANAN: KEPUTUSAN OTORISASI (CHECKER - WAJIB DIISI ADMIN) -->
                <div>
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 8px;">
                        <span style="font-size: 14px;">⚖️</span>
                        <strong style="font-size: 12px; color: #34d399; text-transform: uppercase;">2. Keputusan Otorisasi (Admin)</strong>
                    </div>

                    <!-- 1. CABANG PENEMPATAN -->
                    <div style="margin-bottom: 14px;">
                        <label for="branch_id" style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                            Cabang / Outlet Penempatan <span style="color: #f43f5e;">*</span>
                        </label>
                        <select name="branch_id" id="branch_id" required style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 13px; outline: none; cursor: pointer;">
                            <option value="">-- Pilih Cabang Penempatan Karyawan --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code ?? 'Cabang' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. ROLE / PERAN KERJA -->
                    <div style="margin-bottom: 14px;">
                        <label for="role" style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                            Peran Kerja (Role) <span style="color: #f43f5e;">*</span>
                        </label>
                        <select name="role" id="role" required style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 13px; outline: none; cursor: pointer;">
                            <option value="kasir">Kasir / POS Penjualan (Akses Kasir &amp; Stok)</option>
                            <option value="salesman" selected>Staf Penjualan / Salesman</option>
                            <option value="operator">Operator Warnet / Kios Internet</option>
                            <option value="supervisor">Supervisor Outlet (Dual-Control Void &amp; Diskon)</option>
                            <option value="driver">Driver Logistik</option>
                            <option value="otorisator">Otorisator Diskon &amp; Kredit</option>
                        </select>
                    </div>

                    <!-- 3. POSISI / JABATAN -->
                    <div style="margin-bottom: 14px;">
                        <label for="position" style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                            Nama Jabatan
                        </label>
                        <input type="text" name="position" id="position" value="Staff" placeholder="Contoh: Kasir Shift Pagi" style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 13px; outline: none;">
                    </div>

                    <!-- 4. BATAS MAKSIMAL DISKON KASIR -->
                    <div style="margin-bottom: 14px;">
                        <label for="max_discount_approval_percent" style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                            Batas Maksimal Diskon Mandiri (%)
                        </label>
                        <input type="number" name="max_discount_approval_percent" id="max_discount_approval_percent" value="5" min="0" max="100" style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 13px; outline: none;">
                        <span style="font-size: 11px; color: var(--text-sub); display: block; margin-top: 3px;">Di atas batas ini butuh otorisasi PIN Supervisor saat transaksi.</span>
                    </div>

                    <!-- 5. CATATAN OTORISASI (AUDIT NOTE) -->
                    <div style="margin-bottom: 6px;">
                        <label for="approval_notes" style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                            Catatan Verifikasi (Jejak Audit ISO 27001)
                        </label>
                        <textarea name="approval_notes" id="approval_notes" rows="2" placeholder="Catatan interview atau bukti verifikasi fisik..." style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 12px; outline: none;"></textarea>
                    </div>
                </div>
            </div>

            <!-- MODAL FOOTER -->
            <div style="background: rgba(15, 23, 42, 0.95); border-top: 1px solid var(--border-glass); padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 11px; color: var(--text-sub);">
                    Otorisator: <strong>{{ Auth::user()->name ?? 'Administrator' }}</strong> (Super Admin)
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="closeApprovalModal()" style="background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; padding: 8px 16px; border-radius: 8px; font-size: 13px; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" style="background: linear-gradient(135deg, #10b981, #059669); border: none; color: #ffffff; padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                        <span>✓</span> Setujui &amp; Daftarkan ke Master Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL TOLAK (REJECT MODAL)                                                -->
<!-- ========================================================================= -->
<div id="modalReject" style="display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6); overflow: hidden; animation: popIn 0.25s ease-out;">
        
        <div style="background: linear-gradient(90deg, #dc2626, #991b1b); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">✕</span>
                <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #fff;">Tolak Pendaftaran Karyawan</h3>
            </div>
            <button type="button" onclick="closeRejectModal()" style="background: transparent; border: none; color: #fecaca; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form id="formReject" method="POST" action="">
            @csrf
            <div style="padding: 20px;">
                <p style="margin: 0 0 14px 0; font-size: 13px; color: var(--text-muted); line-height: 1.5;">
                    Apakah Anda yakin ingin menolak permohonan pendaftaran dari <strong id="reject_emp_name" style="color: #fff;">-</strong>?
                </p>

                <div>
                    <label for="rejection_reason" style="font-size: 12px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                        Alasan Penolakan (Wajib dicatat untuk Audit ISO) <span style="color: #f43f5e;">*</span>
                    </label>
                    <textarea name="rejection_reason" id="rejection_reason" required rows="3" placeholder="Contoh: Nomor HP tidak aktif saat diverifikasi / Bukan staf resmi outlet." style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 12px; outline: none;"></textarea>
                </div>
            </div>

            <div style="background: rgba(15, 23, 42, 0.95); border-top: 1px solid var(--border-glass); padding: 14px 20px; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRejectModal()" style="background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; padding: 7px 14px; border-radius: 8px; font-size: 12.5px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" style="background: #ef4444; border: none; color: #ffffff; padding: 7px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer;">
                    ✕ Ya, Tolak Pendaftaran
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openApprovalModal(reg) {
        document.getElementById('modal_view_name').textContent = reg.full_name;
        document.getElementById('modal_view_username').textContent = reg.username;
        document.getElementById('modal_view_phone').textContent = reg.phone || '-';
        document.getElementById('modal_view_meta').textContent = (reg.created_at ? reg.created_at : 'Baru saja') + ' | IP: ' + (reg.ip_address || '127.0.0.1');

        const form = document.getElementById('formApproval');
        form.action = "{{ url('/admin/karyawan-verifikasi') }}/" + reg.id + "/approve";

        const modal = document.getElementById('modalApproval');
        modal.style.display = 'flex';
    }

    function closeApprovalModal() {
        document.getElementById('modalApproval').style.display = 'none';
    }

    function openRejectModal(reg) {
        document.getElementById('reject_emp_name').textContent = reg.full_name + ' (' + reg.username + ')';

        const form = document.getElementById('formReject');
        form.action = "{{ url('/admin/karyawan-verifikasi') }}/" + reg.id + "/reject";

        const modal = document.getElementById('modalReject');
        modal.style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('modalReject').style.display = 'none';
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const modalApprove = document.getElementById('modalApproval');
        const modalReject = document.getElementById('modalReject');
        if (e.target === modalApprove) closeApprovalModal();
        if (e.target === modalReject) closeRejectModal();
    });
</script>
@endsection
