@extends('admin.dashboard.layout')

@section('title', 'Tinjauan Hak Akses Pengguna & De-provisioning (ISO 27001 Klausul A.8.5)')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">🔒</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">Tinjauan Hak Akses Pengguna &amp; De-provisioning</h1>
                <span class="section-subtitle">Pengendalian Akses Pengguna, Wewenang Khusus &amp; Pencabutan Akses Seketika (ISO/IEC 27001:2022 Klausul A.8.5)</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="btn-action-sm" onclick="openSignOffModal()" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-weight: 700; cursor: pointer;">
                ✍️ Sahkan Review (Sign-Off)
            </button>
            <a href="{{ route('admin.karyawan.access-review.export') }}" class="btn-action-sm" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                📥 Ekspor CSV Berita Acara
            </a>
            <a href="{{ route('admin.karyawan') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                👥 Master Karyawan
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            <div style="background: rgba(56, 189, 248, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">Total Akun Pengguna</div>
                <div style="font-size: 26px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin-top: 4px;">{{ $totalAccounts }} Akun</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Terdaftar di Master Data</div>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Akun Aktif</div>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $activeAccounts }} Akun</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Memiliki hak login &amp; presensi</div>
            </div>

            <div style="background: rgba(244, 63, 94, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(244, 63, 94, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #f43f5e; font-weight: 700;">De-provisioned (Non-Aktif)</div>
                <div style="font-size: 26px; font-weight: 800; color: #f43f5e; font-family: 'Outfit'; margin-top: 4px;">{{ $deprovisionedAccounts }} Akun</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Akses dicabut seketika</div>
            </div>

            <div style="background: rgba(245, 158, 11, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">Akun Privileged</div>
                <div style="font-size: 26px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $privilegedAccounts }} Akun</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Supervisor &amp; Otorisator POS</div>
            </div>

            <div style="background: rgba(139, 92, 246, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(139, 92, 246, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #a78bfa; font-weight: 700;">Review Periode Ini (90 Hari)</div>
                <div style="font-size: 26px; font-weight: 800; color: #a78bfa; font-family: 'Outfit'; margin-top: 4px;">{{ $reviewedThisPeriod }} / {{ $totalAccounts }}</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">
                    @if($unreviewedAccounts > 0)
                        <span style="color: #fbbf24;">⚠️ {{ $unreviewedAccounts }} belum ditinjau</span>
                    @else
                        <span style="color: #34d399;">✅ 100% Ditinjau Teratur</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- PRINCIPLE EXPLANATION BANNER -->
        <div style="margin-top: 18px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">🛡️</span>
                <div>
                    <div style="font-size: 13px; font-weight: 700; color: #e2e8f0;">Standar Audit ISO/IEC 27001:2022 Klausul A.8.5 (Access Rights Management)</div>
                    <div style="font-size: 12px; color: var(--text-sub); margin-top: 2px;">
                        Tinjauan hak akses pengguna wajib dilakukan berkala (minimal 90 hari sekali). Hak akses mantan karyawan atau staf mutasi wajib <strong>seketika dicabut (*Instant De-provisioning*)</strong> tanpa penundaan.
                    </div>
                </div>
            </div>
            @if($lastSignOffLog)
                <div style="font-size: 11px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 6px 12px; border-radius: 8px;">
                    ✓ Terakhir Disahkan: {{ $lastSignOffLog->created_at?->format('d M Y H:i') }} oleh Admin #{{ $lastSignOffLog->user_id }}
                </div>
            @endif
        </div>

        <!-- FILTER & SEARCH BAR -->
        <form method="GET" action="{{ route('admin.karyawan.access-review') }}" style="margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIK, Nama, Username, atau No HP..." 
                       style="width: 100%; padding: 10px 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px;">
            </div>

            <select name="branch_id" style="padding: 10px 14px; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px;">
                <option value="all">Semua Cabang</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>

            <select name="role" style="padding: 10px 14px; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px;">
                <option value="all">Semua Peran (Role)</option>
                <option value="kasir" {{ request('role') === 'kasir' ? 'selected' : '' }}>Kasir</option>
                <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor (Privileged)</option>
                <option value="otorisator" {{ request('role') === 'otorisator' ? 'selected' : '' }}>Otorisator (Privileged)</option>
                <option value="operator" {{ request('role') === 'operator' ? 'selected' : '' }}>Operator</option>
                <option value="salesman" {{ request('role') === 'salesman' ? 'selected' : '' }}>Salesman</option>
                <option value="driver" {{ request('role') === 'driver' ? 'selected' : '' }}>Driver</option>
            </select>

            <select name="status" style="padding: 10px 14px; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px;">
                <option value="all">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif Sah</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>De-provisioned (Non-Aktif)</option>
            </select>

            <button type="submit" class="btn-action-sm" style="background: #3b82f6; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                🔍 Filter
            </button>
            <a href="{{ route('admin.karyawan.access-review') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 10px 14px; border-radius: 8px;">
                Reset
            </a>
        </form>
    </div>

    <!-- MAIN ACCESS MATRIX TABLE -->
    <div style="padding: 20px 24px 30px;">
        <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.04); border-bottom: 1px solid rgba(255,255,255,0.1); color: var(--text-sub); text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">
                            <th style="padding: 12px 16px;">NIK &amp; Karyawan</th>
                            <th style="padding: 12px 14px;">Cabang</th>
                            <th style="padding: 12px 14px;">Peran &amp; Wewenang</th>
                            <th style="padding: 12px 14px;">Max Diskon</th>
                            <th style="padding: 12px 14px;">Status Akun</th>
                            <th style="padding: 12px 14px;">Login Terakhir</th>
                            <th style="padding: 12px 14px;">Tinjauan Terakhir</th>
                            <th style="padding: 12px 16px; text-align: right;">Aksi De-provisioning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); {{ ! $emp->is_active ? 'background: rgba(244, 63, 94, 0.04);' : '' }}">
                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 700; color: #fff; font-size: 13px;">{{ $emp->name }}</div>
                                    <div style="font-family: var(--font-mono); color: #38bdf8; font-size: 11px;">{{ $emp->code }} • {{ '@'.$emp->username }}</div>
                                    <div style="color: var(--text-sub); font-size: 11px;">{{ $emp->phone ?? '-' }}</div>
                                </td>

                                <td style="padding: 14px 14px;">
                                    <span style="display: inline-block; padding: 4px 10px; background: rgba(255,255,255,0.06); border-radius: 6px; font-weight: 600; color: #cbd5e1;">
                                        🏢 {{ $emp->branch?->name ?? 'Head Office' }}
                                    </span>
                                </td>

                                <td style="padding: 14px 14px;">
                                    <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                        <span class="badge-status" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                                            {{ strtoupper($emp->role?->value ?? (string) $emp->role) }}
                                        </span>
                                        @if($emp->isPrivileged())
                                            <span class="badge-status" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                                                ⭐ Privileged
                                            </span>
                                        @endif
                                    </div>
                                    <div style="color: var(--text-sub); font-size: 11px; margin-top: 3px;">
                                        Jabatan: {{ $emp->position ?? $emp->jabatan ?? 'Staff' }}
                                    </div>
                                </td>

                                <td style="padding: 14px 14px;">
                                    <span style="font-family: var(--font-mono); font-weight: 700; color: {{ (float)$emp->max_discount_approval_percent > 0 ? '#34d399' : 'var(--text-sub)' }};">
                                        {{ number_format((float)$emp->max_discount_approval_percent, 1) }}%
                                    </span>
                                </td>

                                <td style="padding: 14px 14px;">
                                    @if($emp->is_active)
                                        <span class="badge-status badge-emerald" style="display: inline-flex; align-items: center; gap: 4px;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #34d399;"></span>
                                            AKTIF
                                        </span>
                                    @else
                                        <div>
                                            <span class="badge-status badge-rose" style="display: inline-flex; align-items: center; gap: 4px;">
                                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #f43f5e;"></span>
                                                DE-PROVISIONED
                                            </span>
                                            @if($emp->deactivated_at)
                                                <div style="font-size: 10px; color: #f43f5e; margin-top: 3px;">
                                                    {{ $emp->deactivated_at->format('d/m/Y H:i') }}
                                                </div>
                                            @endif
                                            @if($emp->deactivation_reason)
                                                <div style="font-size: 10px; color: var(--text-sub); max-width: 160px; line-height: 1.2; margin-top: 2px;">
                                                    "{{ Str::limit($emp->deactivation_reason, 35) }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td style="padding: 14px 14px;">
                                    @if($emp->last_login_at)
                                        <div style="color: #cbd5e1; font-weight: 600;">{{ $emp->last_login_at->format('d M Y') }}</div>
                                        <div style="color: var(--text-sub); font-size: 11px;">{{ $emp->last_login_at->format('H:i') }} ({{ $emp->last_login_at->diffForHumans() }})</div>
                                    @else
                                        <span style="color: var(--text-sub); font-style: italic;">Belum login</span>
                                    @endif
                                </td>

                                <td style="padding: 14px 14px;">
                                    @if($emp->last_access_review_at)
                                        <div style="color: #34d399; font-weight: 600;">{{ $emp->last_access_review_at->format('d M Y') }}</div>
                                        <div style="color: var(--text-sub); font-size: 11px;">{{ $emp->last_access_review_at->diffForHumans() }}</div>
                                    @else
                                        <span style="color: #fbbf24; font-weight: 600; font-size: 11px;">⚠️ Belum Ditinjau</span>
                                    @endif
                                </td>

                                <td style="padding: 14px 16px; text-align: right;">
                                    @if($emp->is_active)
                                        <button type="button" 
                                                onclick="openDeprovisionModal({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ $emp->code }}')"
                                                class="btn-action-sm" 
                                                style="background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); cursor: pointer; font-weight: 600;">
                                            🚫 Cabut Akses (De-provision)
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('admin.karyawan.toggle-status', $emp) }}" style="display: inline-block;">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('Apakah Anda yakin ingin memulihkan kembali akses akun {{ addslashes($emp->name) }}?')"
                                                    class="btn-action-sm" 
                                                    style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); cursor: pointer; font-weight: 600;">
                                                ♻️ Pulihkan Akses
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-sub);">
                                    Tidak ada data karyawan yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($employees->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2);">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL DE-PROVISIONING (PENCABUTAN AKSES) -->
<div id="deprovisionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid rgba(244, 63, 94, 0.4); border-radius: 16px; width: 95%; max-width: 480px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">🚫</span>
                <h3 style="margin: 0; color: #f43f5e; font-size: 18px; font-weight: 700;">Pencabutan Akses Pengguna</h3>
            </div>
            <button type="button" onclick="closeDeprovisionModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <p style="color: #cbd5e1; font-size: 13px; line-height: 1.5; margin-bottom: 16px;">
            Anda akan menonaktifkan akun <strong id="deprovisionEmpName" style="color: #fff;"></strong> (<span id="deprovisionEmpCode" style="color: #38bdf8;"></span>). Detik ini juga:
            <br>• Karyawan ditolak login portal, presensi, dan kasir POS.
            <br>• Seluruh sesi aktif seketika dicabut (*Instant Revocation*).
        </p>

        <form id="deprovisionForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Alasan Resmi Penonaktifan (Wajib Dicatat ke Audit Trail ISO 27001):
                </label>
                <select name="reason" required style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px; margin-bottom: 8px;">
                    <option value="Resign / Mengundurkan Diri">Resign / Mengundurkan Diri</option>
                    <option value="Pemutusan Hubungan Kerja (PHK)">Pemutusan Hubungan Kerja (PHK)</option>
                    <option value="Akhir Kontrak Kerja / Selesai Masa Tugas">Akhir Kontrak Kerja / Selesai Masa Tugas</option>
                    <option value="Mutasi ke Entitas / Anak Usaha Lain">Mutasi ke Entitas / Anak Usaha Lain</option>
                    <option value="Pelanggaran Disiplin / Investigasi Internal">Pelanggaran Disiplin / Investigasi Internal</option>
                    <option value="Akun Dorman / Cuti di Luar Tanggungan">Akun Dorman / Cuti di Luar Tanggungan</option>
                    <option value="Lainnya">Lainnya (Tulis Catatan)</option>
                </select>
                <input type="text" name="deactivation_reason" placeholder="Keterangan tambahan (opsional)..." 
                       style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeDeprovisionModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn-action-sm" style="background: #e11d48; color: #fff; border: none; padding: 8px 18px; border-radius: 8px; cursor: pointer; font-weight: 700;">
                    Konfirmasi Cabut Akses
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SIGN-OFF REVIEW (PENGESAHAN TINJAUAN HAK AKSES) -->
<div id="signOffModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #0f172a; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 16px; width: 95%; max-width: 480px; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">✍️</span>
                <h3 style="margin: 0; color: #34d399; font-size: 18px; font-weight: 700;">Pengesahan Tinjauan Hak Akses</h3>
            </div>
            <button type="button" onclick="closeSignOffModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <p style="color: #cbd5e1; font-size: 13px; line-height: 1.5; margin-bottom: 16px;">
            Dengan melakukan Sign-Off, Anda selaku Administrator mengonfirmasi telah memeriksa seluruh hak akses, cabang, dan wewenang {{ $totalAccounts }} akun pengguna sistem. Tindakan ini akan dicatat ke dalam <strong>WORM Audit Trail ISO 27001 Klausul A.8.5</strong>.
        </p>

        <form method="POST" action="{{ route('admin.karyawan.access-review.sign-off') }}">
            @csrf
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Catatan Pengesahan Tinjauan Akses:
                </label>
                <textarea name="notes" rows="3" required style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #fff; font-size: 13px; resize: vertical;">Tinjauan berkala hak akses pengguna periode {{ now()->translatedFormat('F Y') }} telah diverifikasi sah sesuai prinsip Least Privilege &amp; Segregation of Duties (SoD).</textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeSignOffModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn-action-sm" style="background: #10b981; color: #fff; border: none; padding: 8px 18px; border-radius: 8px; cursor: pointer; font-weight: 700;">
                    Sahkan Berita Acara
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeprovisionModal(id, name, code) {
    document.getElementById('deprovisionEmpName').innerText = name;
    document.getElementById('deprovisionEmpCode').innerText = code;
    document.getElementById('deprovisionForm').action = "/admin/karyawan/" + id + "/toggle-status";
    const modal = document.getElementById('deprovisionModal');
    modal.style.display = 'flex';
}

function closeDeprovisionModal() {
    document.getElementById('deprovisionModal').style.display = 'none';
}

function openSignOffModal() {
    document.getElementById('signOffModal').style.display = 'flex';
}

function closeSignOffModal() {
    document.getElementById('signOffModal').style.display = 'none';
}
</script>
@endsection
