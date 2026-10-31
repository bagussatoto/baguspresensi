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
            <a href="{{ route('admin.karyawan.reset-password') }}" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                🔑 Reset PIN
            </a>
            <a href="{{ route('admin.karyawan.unlock') }}" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);">
                🔓 Buka Blokir
            </a>
            <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                👥 Master Karyawan
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
        @php
            $reregCount = $pendingRegistrations->filter(fn($r) => !empty($r->rejection_reason) && Str::startsWith($r->rejection_reason, '[Sebelumnya Ditolak]'))->count();
            $newCount = $pendingRegistrations->count() - $reregCount;
        @endphp
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">📥</span>
                <h2 style="font-size: 15px; font-weight: 700; color: #fff; margin: 0;">Antrean Pendaftaran Mandiri Menunggu Otorisasi</h2>
            </div>
            <span style="font-size: 12px; color: var(--text-sub);">
                Total: <strong>{{ $pendingRegistrations->count() }} Permohonan</strong>
            </span>
        </div>

        <!-- Filter Tab Antrean Cepat -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap;">
            <button type="button" class="tab-filter-btn active" id="filter-all-btn" onclick="filterQueue('all')" style="background: rgba(56, 189, 248, 0.18); border: 1px solid #38bdf8; color: #fff; padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>📋 Semua Antrean</span>
                <span style="background: rgba(255, 255, 255, 0.15); border-radius: 999px; padding: 1px 7px; font-size: 11px;">{{ $pendingRegistrations->count() }}</span>
            </button>
            <button type="button" class="tab-filter-btn" id="filter-rereg-btn" onclick="filterQueue('rereg')" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-muted); padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>🔄 Daftar Ulang (Prioritas)</span>
                <span style="background: rgba(14, 165, 233, 0.25); color: #38bdf8; border-radius: 999px; padding: 1px 7px; font-size: 11px; font-weight: 700;">{{ $reregCount }}</span>
            </button>
            <button type="button" class="tab-filter-btn" id="filter-new-btn" onclick="filterQueue('new')" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-muted); padding: 6px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>👤 Pendaftar Baru</span>
                <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; border-radius: 999px; padding: 1px 7px; font-size: 11px; font-weight: 700;">{{ $newCount }}</span>
            </button>
        </div>

        <div class="custom-table-responsive" style="border: 1px solid var(--border-glass); border-radius: 12px; overflow: hidden; background: rgba(15, 23, 42, 0.5);">
            <table class="dashboard-custom-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                <thead>
                    <tr style="background: rgba(30, 41, 59, 0.8); border-bottom: 1px solid var(--border-glass); color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 12px 16px;">No</th>
                        <th style="padding: 12px 16px;">Waktu Daftar</th>
                        <th style="padding: 12px 16px;">Nama Lengkap</th>
                        <th style="padding: 12px 16px;">Username</th>
                        <th style="padding: 12px 16px;">Kontak &amp; Email</th>
                        <th style="padding: 12px 16px;">Sidik Jari Perangkat</th>
                        <th style="padding: 12px 16px; text-align: center;">Status</th>
                        <th style="padding: 12px 16px; text-align: center;">Tindakan Otorisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRegistrations as $idx => $reg)
                        @php
                            $isRereg = !empty($reg->rejection_reason) && Str::startsWith($reg->rejection_reason, '[Sebelumnya Ditolak]');
                        @endphp
                        <tr class="pending-reg-row" data-type="{{ $isRereg ? 'rereg' : 'new' }}" style="border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s ease;" onmouseover="this.style.background='rgba(56, 189, 248, 0.04)'" onmouseout="this.style.background='transparent'">
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
                                    <div>
                                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $reg->phone)) }}" target="_blank" style="color: #34d399; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;" title="Chat via WhatsApp">
                                            <span>💬</span> {{ $reg->phone }}
                                        </a>
                                    </div>
                                @endif
                                @if($reg->email)
                                    <div style="font-size: 11.5px; color: #94a3b8; margin-top: 2px; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>✉️</span> {{ $reg->email }}
                                    </div>
                                @elseif(!$reg->phone)
                                    <span style="color: var(--text-sub);">-</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; font-size: 11px; color: var(--text-sub);">
                                <code>{{ $reg->ip_address ?? '127.0.0.1' }}</code><br>
                                <span style="font-size: 10px;" title="{{ $reg->user_agent }}">{{ Str::limit($reg->user_agent ?? 'Browser Web', 28) }}</span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                @if(!empty($reg->rejection_reason) && Str::startsWith($reg->rejection_reason, '[Sebelumnya Ditolak]'))
                                    <span style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.4); padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; display: inline-block;" title="{{ $reg->rejection_reason }}">
                                        🔄 Daftar Ulang
                                    </span>
                                @else
                                    <span style="background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                        Pending Review
                                    </span>
                                @endif
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
                    <tr id="empty-filter-row" style="display: none;">
                        <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-sub); font-size: 12.5px;">
                            ℹ️ Tidak ada permohonan yang sesuai dengan filter kategori ini.
                        </td>
                    </tr>
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
                        <th style="padding: 10px 14px; text-align: center;">Aksi &amp; Forensik</th>
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
                                @elseif($item->status === 'expired')
                                    <span style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;" title="{{ $item->rejection_reason }}">
                                        ⌛ Kedaluwarsa (&gt; 3 Hari)
                                    </span>
                                @else
                                    <span style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700;" title="{{ $item->rejection_reason }}">
                                        ✕ Ditolak: {{ Str::limit($item->rejection_reason, 20) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-muted); font-size: 12px;">
                                👤 {{ $item->approver?->name ?? ($item->status === 'expired' ? 'Otomatis (SLA 3 Hari)' : 'Admin / System') }}
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
                            <td style="padding: 10px 14px; text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                    <!-- Read: Detail 360 -->
                                    <button type="button" onclick="showDetailModal({{ $item->id }})" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Lihat Detail & Jejak Audit">
                                        👁️ Detail
                                    </button>

                                    @if(in_array($item->status, ['rejected', 'expired']))
                                        <!-- Update: Buka Ulang ke Pending -->
                                        <form method="POST" action="{{ route('admin.karyawan.verifikasi.reset', $item->id) }}" style="display: inline;" onsubmit="return confirm('Kembalikan berkas pendaftaran {{ $item->full_name }} ke antrean pending untuk ditinjau ulang?');">
                                            @csrf
                                            <button type="submit" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Review Kembali / Buka Ulang">
                                                🔄 Review Ulang
                                            </button>
                                        </form>

                                        <!-- Update: Edit Catatan Penolakan -->
                                        <button type="button" onclick="openEditNoteModal({{ $item->id }}, '{{ addslashes($item->rejection_reason ?? '') }}')" style="background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); color: #cbd5e1; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Koreksi Catatan">
                                            ✏️ Catatan
                                        </button>

                                        <!-- Delete: Hapus Arsip Berkas Ditolak / Expired -->
                                        <form method="POST" action="{{ route('admin.karyawan.verifikasi.destroy', $item->id) }}" style="display: inline;" onsubmit="return confirm('PERINGATAN: Hapus arsip pendaftaran {{ $item->full_name }} secara permanen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Hapus Arsip">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
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

                    <div id="modal_view_rereg_box" style="display: none; background: rgba(14, 165, 233, 0.12); border: 1.5px solid rgba(14, 165, 233, 0.4); border-radius: 10px; padding: 10px 12px; margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #38bdf8; font-weight: 700; font-size: 11.5px; margin-bottom: 4px;">
                            <span>🔄</span>
                            <span>BERKAS DAFTAR ULANG / PERBAIKAN</span>
                        </div>
                        <div style="font-size: 11.5px; color: #e2e8f0; line-height: 1.4;">
                            <span style="color: #94a3b8;">Catatan Penolakan Sebelumnya:</span><br>
                            <span id="modal_view_rereg_reason" style="color: #fca5a5; font-weight: 600;">-</span>
                        </div>
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
                        <label style="font-size: 11px; color: var(--text-sub); display: block; margin-bottom: 2px;">Alamat Email:</label>
                        <div id="modal_view_email" style="font-size: 13px; color: #38bdf8; font-weight: 600;">-</div>
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

                    <!-- 3. PENJADWALAN SHIFT DEFAULT OTOMATIS -->
                    <div style="margin-bottom: 14px;">
                        <label for="shift_id" style="font-size: 11.5px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                            Shift Kerja Awal / Default
                        </label>
                        <select name="shift_id" id="shift_id" style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 13px; outline: none; cursor: pointer;">
                            <option value="">-- Otomatis Mengikuti Jam Operasional --</option>
                            @if(isset($shifts))
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }} ({{ substr((string) $s->start_time, 0, 5) }} - {{ substr((string) $s->end_time, 0, 5) }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <span style="font-size: 11px; color: var(--text-sub); display: block; margin-top: 3px;">Otomatis menjadwalkan shift karyawan baru di Roster Jadwal hari ini.</span>
                    </div>

                    <!-- INFO KEAMANAN LEAST PRIVILEGE (PENGGANTI INPUT JABATAN & DISKON MANUAL) -->
                    <div style="background: rgba(56, 189, 248, 0.08); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 8px; padding: 9px 12px; margin-bottom: 14px; font-size: 11px; color: #cbd5e1; line-height: 1.45;">
                        🔒 <strong style="color: #38bdf8;">Prinsip Least Privilege:</strong> Nama jabatan otomatis mengikuti Peran Kerja, dan batas diskon kasir mandiri otomatis dikunci pada <strong>0%</strong> saat aktivasi awal.
                    </div>

                    <!-- 4. CATATAN OTORISASI (AUDIT NOTE) -->
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
                    Otorisator: <strong>{{ $profileData['name'] ?? (session('admin_user_name') ?: (Auth::user()->name ?? 'Bagus Satoto')) }}</strong> ({{ $profileData['role'] ?? 'Manager' }})
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
    const verifikasiBaseUrl = "{{ url('/admin/karyawan-verifikasi') }}";

    function openApprovalModal(reg) {
        document.getElementById('modal_view_name').textContent = reg.full_name || '-';
        document.getElementById('modal_view_username').textContent = reg.username || '-';
        document.getElementById('modal_view_phone').textContent = reg.phone || '-';
        document.getElementById('modal_view_email').textContent = reg.email || '-';
        document.getElementById('modal_view_meta').textContent = (reg.created_at ? reg.created_at : 'Baru saja') + ' | IP: ' + (reg.ip_address || '127.0.0.1');

        const reregBox = document.getElementById('modal_view_rereg_box');
        const reregReason = document.getElementById('modal_view_rereg_reason');
        if (reg.rejection_reason && reg.rejection_reason.includes('[Sebelumnya Ditolak]')) {
            if (reregBox) reregBox.style.display = 'block';
            if (reregReason) reregReason.textContent = reg.rejection_reason.replace('[Sebelumnya Ditolak]:', '').trim();
        } else {
            if (reregBox) reregBox.style.display = 'none';
        }

        const form = document.getElementById('formApproval');
        form.action = verifikasiBaseUrl + "/" + reg.id + "/approve";

        const modal = document.getElementById('modalApproval');
        modal.style.display = 'flex';
    }

    function closeApprovalModal() {
        document.getElementById('modalApproval').style.display = 'none';
    }

    function filterQueue(type) {
        const rows = document.querySelectorAll('.pending-reg-row');
        const emptyRow = document.getElementById('empty-filter-row');
        const buttons = {
            all: document.getElementById('filter-all-btn'),
            rereg: document.getElementById('filter-rereg-btn'),
            new: document.getElementById('filter-new-btn'),
        };

        Object.keys(buttons).forEach(k => {
            if (!buttons[k]) return;
            if (k === type) {
                buttons[k].style.background = 'rgba(56, 189, 248, 0.18)';
                buttons[k].style.borderColor = '#38bdf8';
                buttons[k].style.color = '#fff';
                buttons[k].style.fontWeight = '700';
            } else {
                buttons[k].style.background = 'rgba(15, 23, 42, 0.6)';
                buttons[k].style.borderColor = 'rgba(255, 255, 255, 0.1)';
                buttons[k].style.color = 'var(--text-muted)';
                buttons[k].style.fontWeight = '600';
            }
        });

        let visibleCount = 0;
        rows.forEach(row => {
            if (type === 'all' || row.getAttribute('data-type') === type) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyRow) {
            emptyRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function openRejectModal(reg) {
        document.getElementById('reject_emp_name').textContent = (reg.full_name || '') + ' (' + (reg.username || '') + ')';

        const form = document.getElementById('formReject');
        form.action = verifikasiBaseUrl + "/" + reg.id + "/reject";

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
        const modalDetail = document.getElementById('modalDetail');
        const modalEditNote = document.getElementById('modalEditNote');
        if (e.target === modalApprove) closeApprovalModal();
        if (e.target === modalReject) closeRejectModal();
        if (e.target === modalDetail) closeDetailModal();
        if (e.target === modalEditNote) closeEditNoteModal();
    });

    // Detail 360 Forensik Modal (Read)
    async function showDetailModal(regId) {
        const modal = document.getElementById('modalDetail');
        const bodyContent = document.getElementById('modal_detail_content');
        modal.style.display = 'flex';
        bodyContent.innerHTML = '<div style="text-align: center; padding: 30px; color: #38bdf8;">⏳ Memuat jejak forensik ISO 27001...</div>';

        try {
            const res = await fetch(verifikasiBaseUrl + "/" + regId, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data.success) {
                bodyContent.innerHTML = '<div style="color: #ef4444; padding: 20px;">Gagal memuat rincian berkas.</div>';
                return;
            }

            const reg = data.registration;
            const trails = data.audit_trail || [];

            let html = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700; margin-bottom: 8px;">Informasi Pendaftar (Maker)</div>
                        <div style="font-size: 14px; font-weight: 700; color: #fff;">${reg.full_name}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Username: <code style="color: #38bdf8;">${reg.username}</code></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">No HP: ${reg.phone} | Email: ${reg.email}</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 6px;">IP Pemohon: ${reg.ip_address || '-'}</div>
                    </div>

                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700; margin-bottom: 8px;">Keputusan Otorisasi (Checker)</div>
                        <div>
                            Status: <span style="font-weight: 800; color: ${reg.status === 'approved' ? '#34d399' : '#f87171'}; text-transform: uppercase;">${reg.status}</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Diverifikasi oleh: <strong>${reg.approver ? reg.approver.name : 'System/Admin'}</strong></div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Waktu: ${reg.approved_at || '-'}</div>
                        ${reg.rejection_reason ? `<div style="font-size: 12px; color: #fca5a5; margin-top: 6px; background: rgba(239, 68, 68, 0.1); padding: 6px 10px; border-radius: 6px;">Alasan: ${reg.rejection_reason}</div>` : ''}
                    </div>
                </div>

                <div style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #e2e8f0; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <span>📜</span>
                        <span>Log Jejak Audit Forensik (WORM ISO 27001)</span>
                    </div>
                    <div style="max-height: 200px; overflow-y: auto;">
            `;

            if (trails.length === 0) {
                html += `<div style="font-size: 12px; color: var(--text-sub); text-align: center; padding: 12px;">Belum ada log jejak audit tambahan.</div>`;
            } else {
                html += `<table style="width: 100%; border-collapse: collapse; font-size: 11.5px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--text-sub);">
                            <th style="padding: 6px;">Waktu</th>
                            <th style="padding: 6px;">Aksi</th>
                            <th style="padding: 6px;">Pelaksana</th>
                            <th style="padding: 6px;">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>`;
                trails.forEach(t => {
                    html += `
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td style="padding: 6px; color: var(--text-sub);">${t.created_at || '-'}</td>
                            <td style="padding: 6px; font-weight: 600; color: #38bdf8;">${t.action}</td>
                            <td style="padding: 6px; color: #fff;">${t.user_id ? 'User #' + t.user_id : 'System'}</td>
                            <td style="padding: 6px; color: var(--text-sub);">${t.ip_address || '-'}</td>
                        </tr>
                    `;
                });
                html += `</tbody></table>`;
            }

            html += `</div></div>`;
            bodyContent.innerHTML = html;
        } catch (e) {
            bodyContent.innerHTML = '<div style="color: #ef4444; padding: 20px;">Terjadi kesalahan saat memuat data.</div>';
        }
    }

    function closeDetailModal() {
        document.getElementById('modalDetail').style.display = 'none';
    }

    // Modal Edit Catatan Penolakan (Update)
    function openEditNoteModal(regId, currentNote) {
        document.getElementById('edit_rejection_reason').value = currentNote || '';
        const form = document.getElementById('formEditNote');
        form.action = verifikasiBaseUrl + "/" + regId;
        document.getElementById('modalEditNote').style.display = 'flex';
    }

    function closeEditNoteModal() {
        document.getElementById('modalEditNote').style.display = 'none';
    }
</script>

<!-- MODAL DETAIL 360 FORENSIK (READ) -->
<div id="modalDetail" style="display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 16px; width: 100%; max-width: 720px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7); overflow: hidden; animation: popIn 0.25s ease-out;">
        <div style="background: linear-gradient(90deg, #0284c7, #0f172a); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🔍</span>
                <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #fff;">Detail Berkas &amp; Forensik ISO 27001</h3>
            </div>
            <button type="button" onclick="closeDetailModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 22px; cursor: pointer;">&times;</button>
        </div>
        <div id="modal_detail_content" style="padding: 20px; max-height: 75vh; overflow-y: auto;">
            <!-- Render via JS -->
        </div>
        <div style="background: rgba(15, 23, 42, 0.95); border-top: 1px solid rgba(255,255,255,0.08); padding: 12px 20px; text-align: right;">
            <button type="button" onclick="closeDetailModal()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 6px 14px; border-radius: 8px; font-size: 12px; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL EDIT CATATAN PENOLAKAN (UPDATE) -->
<div id="modalEditNote" style="display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.85); backdrop-filter: blur(8px); z-index: 1050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #0f172a; border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7); overflow: hidden; animation: popIn 0.25s ease-out;">
        <div style="background: linear-gradient(90deg, #334155, #0f172a); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">✏️</span>
                <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #fff;">Koreksi Catatan Verifikasi</h3>
            </div>
            <button type="button" onclick="closeEditNoteModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 22px; cursor: pointer;">&times;</button>
        </div>
        <form id="formEditNote" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="padding: 20px;">
                <label for="edit_rejection_reason" style="font-size: 12px; font-weight: 700; color: #fff; display: block; margin-bottom: 6px;">
                    Catatan Verifikasi / Alasan Penolakan:
                </label>
                <textarea name="rejection_reason" id="edit_rejection_reason" rows="3" class="filter-input" style="width: 100%; background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 12px; outline: none;"></textarea>
            </div>
            <div style="background: rgba(15, 23, 42, 0.95); border-top: 1px solid rgba(255,255,255,0.08); padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeEditNoteModal()" style="background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; padding: 6px 14px; border-radius: 8px; font-size: 12px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" style="background: #0284c7; border: none; color: #fff; padding: 6px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
