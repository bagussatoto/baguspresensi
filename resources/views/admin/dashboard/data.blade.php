@extends('admin.dashboard.layout')

@section('title', '1. Data Terpadu')

@section('content')
<!-- SECTION HEADER CARD -->
<div class="dashboard-section-card" style="margin-bottom: 20px;">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">🗄️</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">1. Data Terpadu Maya Graha Kencana</h1>
                <span class="section-subtitle">Profil Cabang, Manajemen Karyawan, Jadwal Shift, Data Billing Shift, Presensi, Jam Kerja, & SOP</span>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="section-card-body">

        <!-- ============================================================== -->
        <!-- 1.1 PROFIL & CRUD DATA KARYAWAN -->
        <!-- ============================================================== -->
        <div id="tab-profil" class="data-sub-tab" style="{{ $activeTab == 'tab-profil' ? 'display: block;' : 'display: none;' }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 12px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
                        <div class="user-avatar-circle" style="width: 52px; height: 52px; font-size: 20px; background: linear-gradient(135deg, #0284c7, #38bdf8);">
                            {{ substr($profileData['name'], 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px;">👑 Admin Manager Operasional</div>
                            <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin-top: 2px;">{{ $profileData['name'] }}</h3>
                            <div style="font-size: 12px; color: #94a3b8;">
                                {{ $profileData['position'] }} &bull; <span style="font-family: monospace; color: #cbd5e1;">{{ $profileData['nik'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div style="font-size: 12px; display: flex; flex-direction: column; gap: 8px; border-top: 1px solid var(--border-glass); padding-top: 14px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Email Manager:</span>
                            <span style="color: #e2e8f0; font-weight: 600;">{{ $profileData['email'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">No. Telepon / WA:</span>
                            <span style="color: #e2e8f0; font-family: monospace;">{{ $profileData['phone'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Status Akun:</span>
                            <span class="badge-status badge-emerald">{{ $profileData['status'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">IP Address Sesi:</span>
                            <span style="font-family: monospace; color: #94a3b8;">{{ $profileData['ip_address'] }}</span>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 12px; padding: 20px;">
                    <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <span>🏢</span> Profil Cabang & Perusahaan
                    </h3>
                    <div style="font-size: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Nama Badan Usaha:</span>
                            <strong style="color: #fff;">{{ $profileData['company'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Cabang Penugasan:</span>
                            <strong style="color: #38bdf8;">{{ $profileData['branch_name'] }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Kode Cabang:</span>
                            <span style="font-family: monospace; color: #e2e8f0;">{{ $profileData['branch_code'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Radius Geofencing Presensi:</span>
                            <span class="badge-status badge-sky">100 Meter Presisi GPS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Host Server Database:</span>
                            <span style="font-family: monospace; color: #94a3b8;">192.168.5.10:5432 (PostgreSQL)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CRUD DATA KARYAWAN -->
            <div style="margin-top: 24px; border-top: 1px solid var(--border-glass); padding-top: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                            <span>👥</span> Manajemen Data Karyawan
                        </h3>
                        <span style="font-size: 12px; color: var(--text-muted);">
                            Kelola penambahan, pembaruan data, dan penghapusan staff aktif Maya Graha Kencana
                        </span>
                    </div>
                    <button type="button" onclick="toggleAddEmployeeForm()" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);">
                        <span>➕</span> Tambah Karyawan Baru
                    </button>
                </div>

                <!-- FORM TAMBAH KARYAWAN -->
                <div id="addEmployeeFormCard" style="display: none; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 14px; padding: 22px; margin-bottom: 22px; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                        <h4 style="font-size: 15px; font-weight: 700; color: #34d399; display: flex; align-items: center; gap: 8px;">
                            <span>📝</span> Form Tambah Karyawan Baru
                        </h4>
                        <button type="button" onclick="toggleAddEmployeeForm()" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px;">✕</button>
                    </div>

                    <form action="{{ route('admin.karyawan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-profil">

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Nama Lengkap Karyawan *</label>
                                <input type="text" name="name" required placeholder="Contoh: Rian Anggara" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Username Login</label>
                                <input type="text" name="username" placeholder="Contoh: rian (opsional)" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Posisi / Jabatan</label>
                                <select name="position" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                    <option value="Operator">Operator Warnet / Kasir</option>
                                    <option value="Staff">Staff Operasional</option>
                                    <option value="Teknisi">Teknisi Jaringan & Hardware</option>
                                    <option value="Supervisor">Supervisor Shift</option>
                                    <option value="Manager">Manager</option>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Cabang Penugasan</label>
                                <select name="branch_id" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Email</label>
                                <input type="email" name="email" placeholder="rian@maya.com" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">No. WhatsApp / HP</label>
                                <input type="text" name="phone" placeholder="08123456789" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">PIN Presensi (6 Digit)</label>
                                <input type="password" name="pin" maxlength="6" value="123456" placeholder="Default: 123456" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Password Login</label>
                                <input type="password" name="password" value="123456" placeholder="Default: 123456" style="width: 100%; padding: 10px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" onclick="toggleAddEmployeeForm()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 10px 18px;">Batal</button>
                            <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 10px 22px; font-weight: 700;">
                                <span>💾</span> Simpan & Daftarkan Karyawan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TABEL DAFTAR KARYAWAN -->
                <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                    <table class="custom-data-table">
                        <thead>
                            <tr>
                                <th>NIK / Kode</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Jabatan / Posisi</th>
                                <th>Cabang</th>
                                <th>Kontak (Email / HP)</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi CRUD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allEmployees as $emp)
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">{{ $emp->code }}</td>
                                <td style="font-weight: 700; color: #fff;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #334155; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #38bdf8;">
                                            {{ substr($emp->name, 0, 1) }}
                                        </div>
                                        <span>{{ $emp->name }}</span>
                                    </div>
                                </td>
                                <td style="font-family: monospace; color: #cbd5e1;">{{ $emp->username ?? '-' }}</td>
                                <td>
                                    <span class="badge-status {{ strtolower($emp->position) === 'manager' ? 'badge-sky' : 'badge-emerald' }}" style="font-size: 10px;">
                                        {{ $emp->position ?? 'Staff' }}
                                    </span>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">{{ $emp->branch->name ?? 'MGK Pusat' }}</td>
                                <td style="font-size: 11px;">
                                    <div>{{ $emp->email ?? '-' }}</div>
                                    <div style="color: var(--text-sub); font-family: monospace;">{{ $emp->phone ?? '-' }}</div>
                                </td>
                                <td>
                                    <form action="{{ route('admin.karyawan.toggle-status', $emp->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-profil">
                                        <button type="submit" style="background: transparent; border: none; cursor: pointer; padding: 0;" title="Klik untuk ubah status">
                                            @if($emp->is_active)
                                                <span class="badge-status badge-emerald">🟢 Aktif</span>
                                            @else
                                                <span class="badge-status badge-rose">🔴 Non-Aktif</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button type="button" onclick="openEditEmployeeModal({{ json_encode($emp) }})" class="btn-action-sm btn-action-primary" style="padding: 5px 10px; font-size: 11px;" title="Edit Data Karyawan">
                                        <span>✏️</span> Edit
                                    </button>

                                    <form action="{{ route('admin.karyawan.destroy', $emp->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS karyawan {{ addslashes($emp->name) }} ({{ $emp->code }}) secara permanen?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-profil">
                                        <button type="submit" class="btn-action-sm" style="background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); padding: 5px 10px; font-size: 11px;" title="Hapus Karyawan">
                                            <span>🗑️</span> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    Belum ada data karyawan terdaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1.2 JADWAL SHIFT KARYAWAN -->
        <!-- ============================================================== -->
        <div id="tab-jadwal" class="data-sub-tab" style="{{ $activeTab == 'tab-jadwal' ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #fff;">📅 Pengaturan & Alokasi Jadwal Shift Operasional</h3>
                <span class="badge-status badge-sky">{{ $shifts->count() }} Shift Terkonfigurasi</span>
            </div>
            <div style="overflow-x: auto;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Kode Shift</th>
                            <th>Nama Shift</th>
                            <th>Jam Kerja (Mulai - Selesai)</th>
                            <th>Toleransi (Grace Period)</th>
                            <th>Karyawan Aktif Terjadwal</th>
                            <th>Status Operasional</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shifts as $s)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">{{ $s->code }}</td>
                            <td style="font-weight: 600; color: #fff;">{{ $s->name }}</td>
                            <td>
                                <span style="font-family: monospace; color: #e2e8f0;">{{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }} WIB</span>
                            </td>
                            <td>{{ $s->grace_period_minutes ?? 15 }} Menit</td>
                            <td>
                                <span style="color: #cbd5e1;">{{ $employees->count() }} Staff Terdaftar</span>
                            </td>
                            <td>
                                <span class="badge-status badge-emerald">Aktif</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1.3 DATA BILLING SETIAP SHIFT -->
        <!-- ============================================================== -->
        <div id="tab-billing" class="data-sub-tab" style="{{ $activeTab == 'tab-billing' ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #fff;">💰 Rekapitulasi Pendapatan Billing Internet per Shift</h3>
                    <span style="font-size: 11px; color: var(--text-muted);">Tanggal: {{ $billingPerShift['date_formatted'] }}</span>
                </div>
                <div style="text-align: right; background: rgba(15, 23, 42, 0.7); padding: 8px 16px; border-radius: 10px; border: 1px solid var(--border-glass);">
                    <span style="font-size: 11px; color: var(--text-muted); display: block;">Total Billing Hari Ini:</span>
                    <strong style="font-size: 18px; color: #34d399; font-family: 'Outfit', monospace;">
                        Rp {{ number_format($billingPerShift['total_daily_billing'], 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Shift</th>
                            <th>Jam Operasional</th>
                            <th>Operator Bertugas</th>
                            <th>Pendapatan Billing Warnet</th>
                            <th>Penjualan F&B Kasir</th>
                            <th>Total Omset Shift</th>
                            <th>Status Serah Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingPerShift['shifts'] as $bps)
                        <tr>
                            <td style="font-weight: 700; color: #38bdf8;">{{ $bps['shift_name'] }}</td>
                            <td style="font-family: monospace;">{{ $bps['time_range'] }}</td>
                            <td style="color: #fff; font-weight: 600;">{{ $bps['operator_name'] }}</td>
                            <td style="color: #38bdf8; font-weight: 700; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['billing_amount'], 0, ',', '.') }}
                            </td>
                            <td style="color: #fbbf24; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['fnb_amount'], 0, ',', '.') }}
                            </td>
                            <td style="color: #34d399; font-weight: 800; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['total_amount'], 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge-status badge-sky">{{ $bps['status'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1.4 JUMLAH PRESENSI KARYAWAN & REKAP BULANAN -->
        <!-- ============================================================== -->
        <div id="tab-presensi" class="data-sub-tab" style="{{ $activeTab == 'tab-presensi' ? 'display: block;' : 'display: none;' }}">
            <h4 style="font-size: 15px; font-weight: 800; color: #fff; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> Jumlah Presensi Karyawan &amp; Monitoring Kehadiran
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
                <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border-radius: 10px; border: 1px solid var(--border-glass); text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Total Karyawan</div>
                    <div style="font-size: 22px; font-weight: 800; color: #fff; font-family: 'Outfit';">{{ $attendanceSummary['total_employees'] }} Staff</div>
                </div>
                <div style="background: rgba(16, 185, 129, 0.08); padding: 14px; border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.25); text-align: center;">
                    <div style="font-size: 11px; color: #34d399; text-transform: uppercase;">Tepat Waktu</div>
                    <div style="font-size: 22px; font-weight: 800; color: #34d399; font-family: 'Outfit';">{{ $attendanceSummary['on_time_count'] }}</div>
                </div>
                <div style="background: rgba(245, 158, 11, 0.08); padding: 14px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.25); text-align: center;">
                    <div style="font-size: 11px; color: #fbbf24; text-transform: uppercase;">Terlambat</div>
                    <div style="font-size: 22px; font-weight: 800; color: #fbbf24; font-family: 'Outfit';">{{ $attendanceSummary['late_count'] }}</div>
                </div>
                <div style="background: rgba(99, 102, 241, 0.08); padding: 14px; border-radius: 10px; border: 1px solid rgba(99, 102, 241, 0.25); text-align: center;">
                    <div style="font-size: 11px; color: #818cf8; text-transform: uppercase;">Cuti / Izin</div>
                    <div style="font-size: 22px; font-weight: 800; color: #818cf8; font-family: 'Outfit';">{{ $attendanceSummary['leave_count'] }}</div>
                </div>
                <div style="background: rgba(244, 63, 94, 0.08); padding: 14px; border-radius: 10px; border: 1px solid rgba(244, 63, 94, 0.25); text-align: center;">
                    <div style="font-size: 11px; color: #fb7185; text-transform: uppercase;">Belum Presensi / Alpa</div>
                    <div style="font-size: 22px; font-weight: 800; color: #fb7185; font-family: 'Outfit';">{{ $attendanceSummary['absent_count'] }}</div>
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 10px;">📋 Log Presensi Karyawan Hari Ini</h4>
            <div style="overflow-x: auto; margin-bottom: 30px;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Nama Karyawan</th>
                            <th>Shift</th>
                            <th>Jam Masuk (Clock-In)</th>
                            <th>Jam Pulang (Clock-Out)</th>
                            <th>Status Kehadiran</th>
                            <th>Metode / Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceSummary['today_records'] as $rec)
                        <tr>
                            <td style="color: #fff; font-weight: 600;">{{ $rec->employee->name ?? 'Karyawan' }}</td>
                            <td>{{ $rec->shift->name ?? '-' }}</td>
                            <td style="font-family: monospace; color: #34d399;">{{ $rec->clock_in_at ? $rec->clock_in_at->format('H:i') : '-' }} WIB</td>
                            <td style="font-family: monospace;">{{ $rec->clock_out_at ? $rec->clock_out_at->format('H:i').' WIB' : '(Sedang Berdinas)' }}</td>
                            <td>
                                @php
                                    $statusVal = $rec->status instanceof \BackedEnum ? $rec->status->value : (string) $rec->status;
                                @endphp
                                @if($statusVal === 'present')
                                    <span class="badge-status badge-emerald">Tepat Waktu</span>
                                @elseif($statusVal === 'late')
                                    <span class="badge-status badge-amber">Terlambat ({{ $rec->late_minutes }}m)</span>
                                @else
                                    <span class="badge-status badge-sky">{{ ucfirst($statusVal) }}</span>
                                @endif
                            </td>
                            <td style="font-size: 11px; color: var(--text-muted);">
                                Geofencing Valid (&lt; 100m)
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                Belum ada presensi yang tercatat untuk hari ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- REKAP JAM KERJA PER SHIFT BULANAN -->
            <div id="rekap-jam-kerja-section" style="border-top: 1px solid var(--border-glass); padding-top: 24px; margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 18px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 8px;">
                            <span>⏱️</span> Rekap Jam Kerja Per Shift Bulanan
                        </h4>
                        <span style="font-size: 12px; color: var(--text-muted);">
                            Ringkasan akumulasi jam operasional, lembur, dan rasio kehadiran per shift periode {{ $rekapBulanSummary['month_name'] }}
                        </span>
                    </div>

                    <!-- FILTER BULAN & TAHUN -->
                    <form method="GET" action="{{ route('admin.data') }}" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <input type="hidden" name="tab" value="tab-presensi">
                        <select name="rekap_month" style="padding: 7px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12px;">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $mNum => $mLabel)
                                <option value="{{ $mNum }}" {{ $rekapBulanSummary['selected_month'] == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                        <select name="rekap_year" style="padding: 7px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12px;">
                            @foreach(range(date('Y') - 1, date('Y') + 1) as $yr)
                                <option value="{{ $yr }}" {{ $rekapBulanSummary['selected_year'] == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 7px 14px; font-size: 12px; font-weight: 700;">
                            <span>🔍</span> Tampilkan
                        </button>
                    </form>
                </div>

                <!-- KARTU STATISTIK TOTAL BULAN INI -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
                    <div style="background: rgba(14, 165, 233, 0.08); padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(14, 165, 233, 0.25);">
                        <div style="font-size: 11px; color: #38bdf8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Total Jam Kerja Seluruh Shift</div>
                        <div style="font-size: 24px; font-weight: 800; color: #fff; font-family: 'Outfit'; margin-top: 4px;">{{ $rekapBulanSummary['grand_total_hours_formatted'] }}</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Akumulasi durasi riil bulan {{ $rekapBulanSummary['month_name'] }}</div>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.08); padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                        <div style="font-size: 11px; color: #34d399; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Total Sesi / Hari Masuk</div>
                        <div style="font-size: 24px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $rekapBulanSummary['grand_total_sessions'] }} Sesi</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Jumlah total kedatangan karyawan</div>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.08); padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                        <div style="font-size: 11px; color: #fbbf24; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Total Lembur Terakumulasi</div>
                        <div style="font-size: 24px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $rekapBulanSummary['grand_total_overtime_formatted'] }}</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Waktu lembur shift tervalidasi</div>
                    </div>
                </div>

                <!-- TABEL 1: REKAP AGREGAT PER SHIFT -->
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h5 style="font-size: 13px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px;">
                            📌 1. Akumulasi Total Jam Kerja Berdasarkan Shift ({{ $rekapBulanSummary['month_name'] }})
                        </h5>
                    </div>
                    <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                        <table class="custom-data-table">
                            <thead>
                                <tr>
                                    <th>Shift Operasional</th>
                                    <th>Jam Standar</th>
                                    <th style="text-align: center;">Total Sesi Kehadiran</th>
                                    <th>Total Jam Kerja</th>
                                    <th>Rata-Rata / Sesi</th>
                                    <th>Total Lembur</th>
                                    <th>Tingkat Disiplin</th>
                                    <th style="text-align: center;">Jumlah Staf</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapBulanSummary['shifts_rekap'] as $sRekap)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff;">{{ $sRekap['shift_name'] }}</div>
                                        <div style="font-size: 10px; color: var(--text-sub); font-family: monospace;">{{ $sRekap['shift_code'] }}</div>
                                    </td>
                                    <td>
                                        <span style="font-family: monospace; color: #cbd5e1;">{{ $sRekap['shift_time'] }}</span>
                                        @if($sRekap['is_overnight'])
                                            <span class="badge-status badge-amber" style="font-size: 9px; margin-left: 4px;">Lintas Hari</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-family: 'Outfit'; color: #38bdf8;">
                                        {{ $sRekap['total_sessions'] }} Kali
                                    </td>
                                    <td style="font-weight: 700; color: #34d399; font-family: 'Outfit';">
                                        {{ $sRekap['total_hours_formatted'] }}
                                    </td>
                                    <td style="color: #cbd5e1; font-family: 'Outfit';">
                                        {{ $sRekap['avg_hours_formatted'] }}
                                    </td>
                                    <td>
                                        @if($sRekap['total_overtime_minutes'] > 0)
                                            <span class="badge-status badge-amber">+{{ $sRekap['total_overtime_formatted'] }}</span>
                                        @else
                                            <span style="color: var(--text-sub);">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span class="badge-status {{ $sRekap['compliance_rate'] >= 80 ? 'badge-emerald' : 'badge-amber' }}">
                                                {{ $sRekap['compliance_rate'] }}% Tepat
                                            </span>
                                            @if($sRekap['late_count'] > 0)
                                                <span style="font-size: 10px; color: #fb7185;">({{ $sRekap['late_count'] }}x telat)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #cbd5e1;">
                                        {{ $sRekap['staff_count'] }} Orang
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        Tidak ada data shift yang terdaftar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL 2: RINCIAN JAM KERJA KARYAWAN PER SHIFT -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h5 style="font-size: 13px; font-weight: 700; color: #a78bfa; text-transform: uppercase; letter-spacing: 0.5px;">
                            👥 2. Rincian Jam Kerja Karyawan per Shift ({{ $rekapBulanSummary['month_name'] }})
                        </h5>
                    </div>
                    <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                        <table class="custom-data-table">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Jabatan</th>
                                    <th>Shift Penugasan</th>
                                    <th style="text-align: center;">Kehadiran</th>
                                    <th>Total Jam Kerja</th>
                                    <th>Total Lembur</th>
                                    <th>Keterlambatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapBulanSummary['employee_breakdown'] as $empBreak)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff;">{{ $empBreak['employee_name'] }}</div>
                                        <div style="font-size: 10px; color: var(--text-sub); font-family: monospace;">{{ $empBreak['employee_code'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-sky" style="font-size: 10px;">{{ $empBreak['position'] }}</span>
                                    </td>
                                    <td style="font-weight: 600; color: #cbd5e1;">
                                        {{ $empBreak['shift_name'] }}
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-family: 'Outfit'; color: #38bdf8;">
                                        {{ $empBreak['total_sessions'] }} Sesi
                                    </td>
                                    <td style="font-weight: 700; color: #34d399; font-family: 'Outfit';">
                                        {{ $empBreak['total_duration_formatted'] }}
                                    </td>
                                    <td>
                                        @if($empBreak['total_overtime_minutes'] > 0)
                                            <span class="badge-status badge-amber">+{{ $empBreak['total_overtime_formatted'] }}</span>
                                        @else
                                            <span style="color: var(--text-sub);">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($empBreak['late_count'] > 0)
                                            <span class="badge-status badge-rose">{{ $empBreak['late_count'] }} Kali Telat</span>
                                        @else
                                            <span class="badge-status badge-emerald">Nol Telat</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        Belum ada rincian jam kerja karyawan untuk periode {{ $rekapBulanSummary['month_name'] }}.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- REKAP JAM KERJA PER OUTLET -->
            <div id="rekap-outlet-section" style="border-top: 1px solid var(--border-glass); padding-top: 24px; margin-bottom: 30px;">
                <div style="margin-bottom: 14px;">
                    <h4 style="font-size: 16px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>🏢</span> Rekap Jam Kerja Per Outlet Bulanan
                    </h4>
                    <span style="font-size: 12px; color: var(--text-muted);">
                        Ringkasan total jam operasional dan kedisiplinan staf pada setiap cabang outlet periode {{ $rekapBulanSummary['month_name'] }}
                    </span>
                </div>

                <!-- TABEL 1: AKUMULASI PER OUTLET -->
                <div style="margin-bottom: 24px;">
                    <h5 style="font-size: 13px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                        📌 1. Akumulasi Total Jam Kerja Berdasarkan Outlet ({{ $rekapBulanSummary['month_name'] }})
                    </h5>
                    <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                        <table class="custom-data-table">
                            <thead>
                                <tr>
                                    <th>Outlet / Cabang</th>
                                    <th style="text-align: center;">Total Sesi Masuk</th>
                                    <th>Total Jam Kerja</th>
                                    <th>Rata-Rata / Sesi</th>
                                    <th>Total Lembur</th>
                                    <th>Tingkat Disiplin</th>
                                    <th style="text-align: center;">Jumlah Staf</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapOutletSummary['branches_rekap'] as $bRekap)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff;">{{ $bRekap['branch_name'] }}</div>
                                        <div style="font-size: 10px; color: var(--text-sub); font-family: monospace;">{{ $bRekap['branch_code'] }}</div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-family: 'Outfit'; color: #38bdf8;">
                                        {{ $bRekap['total_sessions'] }} Sesi
                                    </td>
                                    <td style="font-weight: 700; color: #34d399; font-family: 'Outfit';">
                                        {{ $bRekap['total_hours_formatted'] }}
                                    </td>
                                    <td style="color: #cbd5e1; font-family: 'Outfit';">
                                        {{ $bRekap['avg_hours_formatted'] }}
                                    </td>
                                    <td>
                                        @if($bRekap['total_overtime_minutes'] > 0)
                                            <span class="badge-status badge-amber">+{{ $bRekap['total_overtime_formatted'] }}</span>
                                        @else
                                            <span style="color: var(--text-sub);">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $bRekap['compliance_rate'] >= 80 ? 'badge-emerald' : 'badge-amber' }}">
                                            {{ $bRekap['compliance_rate'] }}% Tepat Waktu
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #cbd5e1;">
                                        {{ $bRekap['staff_count'] }} Orang
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        Belum ada data rekap per outlet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL 2: RINCIAN JAM KERJA STAF PER OUTLET (REKAP JAM KERJA PER SHIF BULANAN) -->
                <div id="printable-rekap-shift-card" style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <h4 style="font-size: 15px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
                                <span>📋</span> REKAP JAM KERJA PER SHIF BULAN {{ strtoupper($rekapOutletSummary['shift_matrix']['month_name'] ?? $rekapBulanSummary['month_name']) }}
                            </h4>
                            <div class="rekap-subtext" style="font-size: 11px; color: #a78bfa; font-weight: 600; margin-top: 3px;">
                                👥 2. Rincian Jam Kerja Staf per Outlet ({{ $rekapOutletSummary['shift_matrix']['branch_name'] ?? 'Maya Graha Kencana' }})
                            </div>
                        </div>

                        <!-- TOMBOL COPY & PRINT -->
                        <div class="no-print" style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" id="btnCopyRekapShift" onclick="copyRekapShiftTable()" class="btn-action-sm btn-action-primary" style="padding: 7px 14px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); cursor: pointer;" title="Salin data tabel ke clipboard (bisa langsung dipaste ke Excel / Google Sheets)">
                                <span>📋</span> <span id="btnCopyRekapShiftText">Copy Tabel</span>
                            </button>
                            <button type="button" onclick="printRekapShiftTable()" class="btn-action-sm" style="padding: 7px 14px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px; background: rgba(148, 163, 184, 0.15); color: #e2e8f0; border: 1px solid rgba(148, 163, 184, 0.35); cursor: pointer;" title="Cetak atau simpan sebagai PDF">
                                <span>🖨️</span> Print Data
                            </button>
                        </div>
                    </div>

                    <!-- TABEL MATRIKS REKAP SHIFT PERSIS SEPERTI GAMBAR CONTOH -->
                    <div style="overflow-x: auto; background: #ffffff; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <table id="table-rekap-shift" class="rekap-shift-table" style="width: 100%; border-collapse: collapse; font-family: 'Segoe UI', Inter, system-ui, sans-serif; font-size: 12px; color: #1e293b;">
                            <thead>
                                <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                    <th style="padding: 10px 8px; text-align: center; font-weight: 800; color: #334155; width: 45px; border-right: 1px solid #e2e8f0;">No</th>
                                    <th style="padding: 10px 14px; text-align: left; font-weight: 800; color: #334155; min-width: 140px; border-right: 1px solid #e2e8f0;">OPERATOR</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">shif 1: (01-07)</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">shif 2: (07-13)</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">shif 3: (13-19)</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">shif 4: (19-01)</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">Total Shift</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">BO (jam)</th>
                                    <th style="padding: 10px 10px; text-align: right; font-weight: 800; color: #334155; border-right: 1px solid #e2e8f0;">Total (jam)</th>
                                    <th style="padding: 10px 12px; text-align: center; font-weight: 800; color: #334155; width: 75px;">Telat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapOutletSummary['shift_matrix']['rows'] as $row)
                                <tr style="border-bottom: 1px solid #f1f5f9; {{ $loop->even ? 'background: #f8fafc;' : 'background: #ffffff;' }}">
                                    <td style="padding: 8px; text-align: center; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0;">{{ $row['no'] }}</td>
                                    <td style="padding: 8px 14px; text-align: left; font-weight: 700; color: #0f172a; border-right: 1px solid #e2e8f0;">{{ $row['operator'] }}</td>
                                    <td style="padding: 8px 10px; text-align: right; color: #334155; border-right: 1px solid #e2e8f0;">{{ $row['shift_1_hours'] }}</td>
                                    <td style="padding: 8px 10px; text-align: right; color: #334155; border-right: 1px solid #e2e8f0;">{{ $row['shift_2_hours'] }}</td>
                                    <td style="padding: 8px 10px; text-align: right; color: #334155; border-right: 1px solid #e2e8f0;">{{ $row['shift_3_hours'] }}</td>
                                    <td style="padding: 8px 10px; text-align: right; color: #334155; border-right: 1px solid #e2e8f0;">{{ $row['shift_4_hours'] }}</td>
                                    <td style="padding: 8px 10px; text-align: right; font-weight: 700; color: #0f172a; border-right: 1px solid #e2e8f0;">{{ $row['total_shift_hours'] }}</td>
                                    <td style="padding: 8px 10px; text-align: right; font-weight: 700; border-right: 1px solid #e2e8f0;">
                                        @if($row['bo_hours'] > 0)
                                            <span class="color-bo-print" style="color: #dc2626; font-weight: 800;">{{ $row['bo_hours'] }}</span>
                                        @else
                                            <span style="color: #94a3b8;">0</span>
                                        @endif
                                    </td>
                                    <td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #0f172a; border-right: 1px solid #e2e8f0;">{{ $row['total_hours'] }}</td>
                                    <td style="padding: 6px 8px; text-align: center;">
                                        @if($row['late_count'] > 0)
                                            <span class="badge-telat-print" style="background: #a2124d; color: #ffffff; font-weight: 800; border-radius: 6px; padding: 3px 8px; display: inline-block; min-width: 26px; text-align: center; box-shadow: 0 1px 2px rgba(162, 18, 77, 0.3);">{{ $row['late_count'] }}</span>
                                        @else
                                            <span style="color: #94a3b8; font-weight: 500;">0</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" style="text-align: center; color: #64748b; padding: 24px;">
                                        Belum ada rincian jam kerja staf outlet untuk periode {{ $rekapBulanSummary['month_name'] }}.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                @php $totals = $rekapOutletSummary['shift_matrix']['totals']; @endphp
                                <tr style="background: #f8fafc; border-top: 2px solid #64748b; font-weight: 800;">
                                    <td style="padding: 10px 8px; text-align: center; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">TOTAL</td>
                                    <td style="padding: 10px 14px; border-right: 1px solid #cbd5e1;"></td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">{{ $totals['shift_1_hours'] }}</td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">{{ $totals['shift_2_hours'] }}</td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">{{ $totals['shift_3_hours'] }}</td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">{{ $totals['shift_4_hours'] }}</td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">{{ $totals['total_shift_hours'] }}</td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; border-right: 1px solid #cbd5e1;">
                                        <span class="color-bo-print" style="color: #dc2626; font-weight: 900;">{{ $totals['bo_hours'] }}</span>
                                    </td>
                                    <td style="padding: 10px 10px; text-align: right; font-weight: 900; color: #0f172a; border-right: 1px solid #cbd5e1;">{{ $totals['total_hours'] }}</td>
                                    <td style="padding: 10px 8px; text-align: center; font-weight: 900; color: #0f172a;">{{ $totals['late_count'] }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- REKAP KEHADIRAN KEGIATAN (MEETING, PELATIHAN, GC) -->
            <!-- <div id="rekap-kegiatan-section" style="border-top: 1px solid var(--border-glass); padding-top: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 18px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 8px;">
                            <span>🎯</span> Rekap Kehadiran Kegiatan (Meeting, Pelatihan, GC)
                        </h4>
                        <span style="font-size: 12px; color: var(--text-muted);">
                            Monitoring kehadiran rapat, workshop SOP, dan General Cleaning (GC) periode {{ $rekapBulanSummary['month_name'] }}
                        </span>
                    </div>

                    <button type="button" onclick="toggleAddActivityForm()" class="btn-action-sm btn-action-primary" style="padding: 8px 16px; font-size: 12px; font-weight: 700;">
                        <span>➕</span> Tambah Catatan Kegiatan
                    </button>
                </div> -->

                <!-- FORM TAMBAH KEGIATAN -->
                <div id="addActivityFormCard" style="display: none; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <h5 style="font-size: 14px; font-weight: 700; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                            <span>📝</span> Formulir Pencatatan Kehadiran Kegiatan Baru
                        </h5>
                        <button type="button" onclick="toggleAddActivityForm()" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 16px;">✕</button>
                    </div>

                    <form method="POST" action="{{ route('admin.kegiatan.store') }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-presensi">

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Kategori Kegiatan *</label>
                                <select name="activity_type" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                    <option value="meeting">🤝 Rapat / Meeting Operasional</option>
                                    <option value="pelatihan">🎓 Pelatihan & Workshop SOP</option>
                                    <option value="gc">🧹 General Cleaning (GC)</option>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Nama / Topik Agenda *</label>
                                <input type="text" name="title" required placeholder="Contoh: Meeting Bulanan Evaluasi Outlet" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Karyawan Peserta *</label>
                                <select name="employee_id" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($allEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Lokasi Outlet / Cabang</label>
                                <select name="branch_id" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                    <option value="">-- Maya Graha Pusat --</option>
                                    @foreach($branches as $br)
                                        <option value="{{ $br->id }}">{{ $br->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Tanggal Kegiatan *</label>
                                <input type="date" name="activity_date" required value="{{ date('Y-m-d') }}" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Jam Mulai</label>
                                <input type="time" name="start_time" value="09:00" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Jam Selesai</label>
                                <input type="time" name="end_time" value="11:00" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Durasi (Menit)</label>
                                <input type="number" name="duration_minutes" value="120" min="0" step="15" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>

                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Status Kehadiran *</label>
                                <select name="status" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                    <option value="hadir">Hadir Penuh</option>
                                    <option value="izin">Izin / Sakit / Tugas Luar</option>
                                    <option value="alpa">Alpa / Tanpa Keterangan</option>
                                </select>
                            </div>

                            <div style="grid-column: 1 / -1;">
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Catatan Tambahan (Opsional)</label>
                                <input type="text" name="notes" placeholder="Catatan hasil meeting, materi pelatihan, atau checklist sanitasi GC" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" onclick="toggleAddActivityForm()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 8px 16px;">Batal</button>
                            <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 20px; font-weight: 700;">
                                <span>💾</span> Simpan Kegiatan
                            </button>
                        </div>
                    </form>
                <!-- KARTU STATISTIK KEGIATAN -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
                    <div style="background: rgba(56, 189, 248, 0.08); padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(56, 189, 248, 0.25);">
                        <div style="font-size: 11px; color: #38bdf8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Total Agenda Kegiatan</div>
                        <div style="font-size: 24px; font-weight: 800; color: #fff; font-family: 'Outfit'; margin-top: 4px;">{{ $rekapKegiatanSummary['grand_total_activities'] }} Agenda</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Rapat, Workshop & GC terlaksana</div>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.08); padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                        <div style="font-size: 11px; color: #34d399; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Total Log Partisipasi</div>
                        <div style="font-size: 24px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">{{ $rekapKegiatanSummary['grand_total_attendances'] }} Orang</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Kehadiran akumulatif staf</div>
                    </div>
                    <div style="background: rgba(168, 85, 247, 0.08); padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(168, 85, 247, 0.25);">
                        <div style="font-size: 11px; color: #c084fc; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Total Jam Investasi Kegiatan</div>
                        <div style="font-size: 24px; font-weight: 800; color: #c084fc; font-family: 'Outfit'; margin-top: 4px;">{{ $rekapKegiatanSummary['grand_total_hours_formatted'] }}</div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">Akumulasi waktu pengembangan & GC</div>
                    </div>
                </div>

                <!-- TABEL 1: RINGKASAN PER KATEGORI KEGIATAN -->
                <div style="margin-bottom: 24px;">
                    <h5 style="font-size: 13px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                        📊 1. Ringkasan per Kategori Kegiatan (Meeting, Pelatihan, GC)
                    </h5>
                    <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                        <table class="custom-data-table">
                            <thead>
                                <tr>
                                    <th>Kategori Kegiatan</th>
                                    <th style="text-align: center;">Jumlah Agenda</th>
                                    <th style="text-align: center;">Total Partisipan</th>
                                    <th>Total Durasi</th>
                                    <th>Rata-Rata / Sesi</th>
                                    <th>Komposisi Status</th>
                                    <th>Tingkat Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapKegiatanSummary['categories'] as $cat)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                                            @if($cat['type'] === 'meeting')
                                                <span>🤝</span>
                                            @elseif($cat['type'] === 'pelatihan')
                                                <span>🎓</span>
                                            @else
                                                <span>🧹</span>
                                            @endif
                                            <span>{{ $cat['name'] }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-family: 'Outfit'; color: #38bdf8;">
                                        {{ $cat['total_events'] }} Agenda
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-family: 'Outfit'; color: #cbd5e1;">
                                        {{ $cat['total_records'] }} Orang
                                    </td>
                                    <td style="font-weight: 700; color: #34d399; font-family: 'Outfit';">
                                        {{ $cat['total_hours_formatted'] }}
                                    </td>
                                    <td style="color: #cbd5e1; font-family: 'Outfit';">
                                        {{ $cat['avg_duration_formatted'] }}
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px; font-size: 11px;">
                                            <span class="badge-status badge-emerald">{{ $cat['hadir_count'] }} Hadir</span>
                                            @if($cat['izin_count'] > 0)
                                                <span class="badge-status badge-amber">{{ $cat['izin_count'] }} Izin</span>
                                            @endif
                                            @if($cat['alpa_count'] > 0)
                                                <span class="badge-status badge-rose">{{ $cat['alpa_count'] }} Alpa</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $cat['attendance_rate'] >= 80 ? 'badge-emerald' : 'badge-amber' }}">
                                            {{ $cat['attendance_rate'] }}% Partisipasi
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                        Belum ada rekap kegiatan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL 2: CATATAN KEHADIRAN KEGIATAN KARYAWAN -->
                <div>
                    <h5 style="font-size: 13px; font-weight: 700; color: #a78bfa; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                        📝 2. Catatan Kehadiran Kegiatan Karyawan (Meeting, Pelatihan, GC)
                    </h5>
                    <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                    <table class="custom-data-table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Agenda / Topik</th>
                                <th>Karyawan</th>
                                <th>Outlet</th>
                                <th>Tanggal & Waktu</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapKegiatanSummary['records'] as $act)
                            <tr>
                                <td>
                                    <span class="badge-status {{ $act['type_badge_class'] }}">
                                        {{ $act['type_label'] }}
                                    </span>
                                </td>
                                <td style="font-weight: 600; color: #fff;">
                                    {{ $act['title'] }}
                                    @if(!empty($act['notes']))
                                        <div style="font-size: 11px; color: var(--text-sub);">{{ $act['notes'] }}</div>
                                    @endif
                                </td>
                                <td>{{ $act['employee_name'] }}</td>
                                <td style="color: var(--text-muted);">{{ $act['branch_name'] }}</td>
                                <td>
                                    <div>{{ $act['formatted_date'] }}</div>
                                    <div style="font-size: 11px; color: var(--text-sub); font-family: monospace;">{{ $act['time_formatted'] }}</div>
                                </td>
                                <td style="font-weight: 700; color: #38bdf8; font-family: 'Outfit';">
                                    {{ $act['duration_formatted'] }}
                                </td>
                                <td>
                                    @if($act['status'] === 'hadir')
                                        <span class="badge-status badge-emerald">Hadir</span>
                                    @elseif($act['status'] === 'izin')
                                        <span class="badge-status badge-amber">Izin</span>
                                    @else
                                        <span class="badge-status badge-rose">Alpa</span>
                                    @endif
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button type="button" onclick="openEditActivityModal({{ json_encode($act) }})" class="btn-action-sm btn-action-primary" style="padding: 5px 10px; font-size: 11px;" title="Edit Kegiatan">
                                        <span>✏️</span> Edit
                                    </button>

                                    <form action="{{ route('admin.kegiatan.destroy', $act['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus catatan kegiatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-presensi">
                                        <button type="submit" class="btn-action-sm" style="background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); padding: 5px 10px; font-size: 11px;">
                                            <span>🗑️</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                    Belum ada data kegiatan untuk periode {{ $rekapBulanSummary['month_name'] }}.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1.5 JAM KERJA KARYAWAN (CRUD & MONITORING) -->
        <!-- ============================================================== -->
        <div id="tab-jamkerja" class="data-sub-tab" style="{{ $activeTab == 'tab-jamkerja' ? 'display: block;' : 'display: none;' }}">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>⏱️</span> Akumulasi & Monitoring Jam Kerja Karyawan
                    </h3>
                    <span style="font-size: 12px; color: var(--text-muted);">
                        Kelola rekaman presensi, jam kerja, lembur, serta edit dan delete jam kerja karyawan
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge-status badge-sky">Target Standar: 8 Jam / Shift</span>
                    <button type="button" onclick="toggleAddWorkHoursForm()" class="btn-action-sm btn-action-emerald" style="padding: 9px 16px; font-size: 12px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                        <span>➕</span> Tambah Jam Kerja
                    </button>
                </div>
            </div>

            <!-- FORM TAMBAH JAM KERJA -->
            <div id="addWorkHoursFormCard" style="display: none; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 14px; padding: 20px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: #34d399; display: flex; align-items: center; gap: 8px;">
                        <span>📝</span> Form Tambah Jam Kerja Karyawan
                    </h4>
                    <button type="button" onclick="toggleAddWorkHoursForm()" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px;">✕</button>
                </div>

                <form action="{{ route('admin.jamkerja.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-jamkerja">

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Pilih Karyawan *</label>
                            <select name="employee_id" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($allEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }}) - {{ $emp->position }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Shift Penugasan</label>
                            <select name="shift_id" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                <option value="">-- Shift Otomatis / Roster --</option>
                                @foreach($shifts as $sf)
                                    <option value="{{ $sf->id }}">{{ $sf->name }} ({{ substr($sf->start_time, 0, 5) }} - {{ substr($sf->end_time, 0, 5) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Tanggal Kerja *</label>
                            <input type="date" name="work_date" value="{{ date('Y-m-d') }}" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Jam Masuk *</label>
                            <input type="time" name="clock_in_time" value="08:00" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Jam Pulang</label>
                            <input type="time" name="clock_out_time" value="16:00" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Lembur (Menit)</label>
                            <input type="number" min="0" step="15" name="overtime_minutes" value="0" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Status Kehadiran</label>
                            <select name="status" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                                <option value="present">Hadir Tepat Waktu</option>
                                <option value="late">Terlambat</option>
                                <option value="half_day">Setengah Hari</option>
                                <option value="absent">Alpa / Tidak Hadir</option>
                                <option value="on_leave">Cuti / Izin</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">Catatan (Opsional)</label>
                            <input type="text" name="notes" placeholder="Contoh: Input presensi manual" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="toggleAddWorkHoursForm()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 9px 16px;">Batal</button>
                        <button type="submit" class="btn-action-sm btn-action-emerald" style="padding: 9px 20px; font-weight: 700;">
                            <span>💾</span> Simpan Jam Kerja
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABEL JAM KERJA -->
            <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Shift</th>
                            <th>Tanggal Kerja</th>
                            <th>Masuk - Pulang</th>
                            <th>Durasi Kerja</th>
                            <th>Lembur</th>
                            <th>Status Pemenuhan Kuota</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workHoursRecords as $wh)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #fff;">{{ $wh['employee_name'] }}</div>
                                <div style="font-size: 10px; color: var(--text-sub); font-family: monospace;">{{ $wh['employee_code'] }}</div>
                            </td>
                            <td>{{ $wh['shift_name'] }}</td>
                            <td>{{ $wh['work_date'] }}</td>
                            <td style="font-family: monospace;">{{ $wh['clock_in'] }} - {{ $wh['clock_out'] }}</td>
                            <td style="font-weight: 700; color: #38bdf8; font-family: 'Outfit';">
                                {{ $wh['duration_formatted'] }}
                            </td>
                            <td>
                                @if($wh['overtime_minutes'] > 0)
                                    <span class="badge-status badge-amber">+{{ $wh['overtime_minutes'] }} Menit</span>
                                @else
                                    <span style="color: var(--text-sub);">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $wh['status_target'] === 'Memenuhi Kuota' ? 'badge-emerald' : 'badge-sky' }}">
                                    {{ $wh['status_target'] }}
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" onclick="openEditWorkHoursModal({{ json_encode($wh) }})" class="btn-action-sm btn-action-primary" style="padding: 5px 10px; font-size: 11px;" title="Edit Jam Kerja">
                                    <span>✏️</span> Edit
                                </button>

                                <form action="{{ route('admin.jamkerja.destroy', $wh['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus rekaman jam kerja karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-jamkerja">
                                    <button type="submit" class="btn-action-sm" style="background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); padding: 5px 10px; font-size: 11px;" title="Hapus Jam Kerja">
                                        <span>🗑️</span> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Belum ada rekaman jam kerja karyawan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1.6 STANDARD OPERATING PROCEDURE (SOP) -->
        <!-- ============================================================== -->
        <div id="tab-sop" class="data-sub-tab" style="{{ $activeTab == 'tab-sop' ? 'display: block;' : 'display: none;' }}">
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #fff;">📜 Standard Operating Procedure (SOP) Maya Graha Kencana</h3>
                <span style="font-size: 12px; color: var(--text-muted);">Seluruh staff dan admin wajib mematuhi seluruh tahapan kerja di bawah ini.</span>
            </div>

            <div class="sop-grid-container">
                @foreach($sopList as $sop)
                <div class="sop-card-item">
                    <div class="sop-card-header">
                        <span class="sop-code-badge">{{ $sop['code'] }}</span>
                        <span style="font-size: 11px; color: var(--text-sub);">{{ $sop['category'] }}</span>
                    </div>
                    <div class="sop-title-text">{{ $sop['title'] }}</div>
                    <div class="sop-summary-text">{{ $sop['summary'] }}</div>
                    <div style="font-size: 11px; font-weight: 700; color: #38bdf8; margin-bottom: 6px; text-transform: uppercase;">Tahapan SOP:</div>
                    <ol class="sop-step-list">
                        @foreach($sop['steps'] as $st)
                        <li>{{ $st }}</li>
                        @endforeach
                    </ol>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- MODAL EDIT KARYAWAN -->
<div id="editEmployeeModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 540px; text-align: left;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>✏️</span> Edit Data Karyawan
            </h3>
            <button type="button" onclick="closeEditEmployeeModal()" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px;">✕</button>
        </div>

        <form id="editEmployeeForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-profil">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Nama Lengkap *</label>
                    <input type="text" id="edit_name" name="name" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Username</label>
                    <input type="text" id="edit_username" name="username" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Posisi / Jabatan</label>
                    <select id="edit_position" name="position" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        <option value="Operator">Operator Warnet / Kasir</option>
                        <option value="Staff">Staff Operasional</option>
                        <option value="Teknisi">Teknisi Jaringan & Hardware</option>
                        <option value="Supervisor">Supervisor Shift</option>
                        <option value="Manager">Manager</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Email</label>
                    <input type="email" id="edit_email" name="email" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">No. WhatsApp / HP</label>
                    <input type="text" id="edit_phone" name="phone" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Cabang Penugasan</label>
                    <select id="edit_branch_id" name="branch_id" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Status Aktif</label>
                    <select id="edit_is_active" name="is_active" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        <option value="1">🟢 Aktif</option>
                        <option value="0">🔴 Non-Aktif</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Ubah PIN Presensi (Opsional)</label>
                    <input type="password" name="pin" maxlength="6" placeholder="(Kosongkan jika tetap)" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Ubah Password Login (Opsional)</label>
                    <input type="password" name="password" placeholder="(Kosongkan jika tetap)" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditEmployeeModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 10px 18px;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 22px; font-weight: 700;">
                    <span>💾</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT JAM KERJA -->
<div id="editWorkHoursModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 560px; text-align: left;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>⏱️</span> Edit Jam Kerja Karyawan
            </h3>
            <button type="button" onclick="closeEditWorkHoursModal()" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px;">✕</button>
        </div>

        <form id="editWorkHoursForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-jamkerja">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Karyawan *</label>
                    <select id="edit_wh_employee_id" name="employee_id" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Shift Penugasan</label>
                    <select id="edit_wh_shift_id" name="shift_id" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        <option value="">-- Shift Otomatis / Roster --</option>
                        @foreach($shifts as $sf)
                            <option value="{{ $sf->id }}">{{ $sf->name }} ({{ substr($sf->start_time, 0, 5) }} - {{ substr($sf->end_time, 0, 5) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Tanggal Kerja *</label>
                    <input type="date" id="edit_wh_work_date" name="work_date" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Jam Masuk *</label>
                    <input type="time" id="edit_wh_clock_in" name="clock_in_time" required style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Jam Pulang</label>
                    <input type="time" id="edit_wh_clock_out" name="clock_out_time" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Lembur (Menit)</label>
                    <input type="number" id="edit_wh_overtime" min="0" step="15" name="overtime_minutes" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Status Kehadiran</label>
                    <select id="edit_wh_status" name="status" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        <option value="present">Hadir Tepat Waktu</option>
                        <option value="late">Terlambat</option>
                        <option value="half_day">Setengah Hari</option>
                        <option value="absent">Alpa / Tidak Hadir</option>
                        <option value="on_leave">Cuti / Izin</option>
                    </select>
                </div>

                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Catatan (Opsional)</label>
                    <input type="text" id="edit_wh_notes" name="notes" style="width: 100%; padding: 9px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditWorkHoursModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 10px 18px;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 22px; font-weight: 700;">
                    <span>💾</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT KEGIATAN -->
<div id="editActivityModal" class="modal-overlay">
    <div class="modal-box" style="max-width: 540px; text-align: left;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>✏️</span> Edit Catatan Kegiatan
            </h3>
            <button type="button" onclick="closeEditActivityModal()" style="background: transparent; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px;">✕</button>
        </div>

        <form id="editActivityForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}?tab=tab-presensi">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Kategori Kegiatan</label>
                    <select id="edit_act_type" name="activity_type" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        <option value="meeting">🤝 Rapat / Meeting Operasional</option>
                        <option value="pelatihan">🎓 Pelatihan & Workshop SOP</option>
                        <option value="gc">🧹 General Cleaning (GC)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Topik Agenda</label>
                    <input type="text" id="edit_act_title" name="title" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Karyawan Peserta</label>
                    <select id="edit_act_employee_id" name="employee_id" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Lokasi Cabang</label>
                    <select id="edit_act_branch_id" name="branch_id" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        @foreach($branches as $br)
                            <option value="{{ $br->id }}">{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Tanggal Kegiatan</label>
                    <input type="date" id="edit_act_date" name="activity_date" required style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Status Kehadiran</label>
                    <select id="edit_act_status" name="status" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                        <option value="hadir">Hadir Penuh</option>
                        <option value="izin">Izin / Tugas Luar</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Jam Mulai</label>
                    <input type="time" id="edit_act_start_time" name="start_time" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Jam Selesai</label>
                    <input type="time" id="edit_act_end_time" name="end_time" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Durasi (Menit)</label>
                    <input type="number" id="edit_act_duration" name="duration_minutes" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>

                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 4px;">Catatan Tambahan</label>
                    <input type="text" id="edit_act_notes" name="notes" style="width: 100%; padding: 8px 12px; background: rgba(30, 41, 59, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 13px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditActivityModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff; padding: 10px 18px;">Batal</button>
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 10px 22px; font-weight: 700;">
                    <span>💾</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchDataTab(evt, tabId) {
        if (evt) evt.preventDefault();

        document.querySelectorAll('.data-sub-tab').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-pill-btn').forEach(btn => btn.classList.remove('active'));

        const targetTab = document.getElementById(tabId);
        if (targetTab) {
            targetTab.style.display = 'block';
        }

        if (evt && evt.currentTarget) {
            evt.currentTarget.classList.add('active');
        }

        // Update URL query parameter without page reload
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', newUrl);
    }

    function toggleAddEmployeeForm() {
        const el = document.getElementById('addEmployeeFormCard');
        if (el) {
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
            if (el.style.display === 'block') {
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    function openEditEmployeeModal(emp) {
        document.getElementById('edit_name').value = emp.name || '';
        document.getElementById('edit_username').value = emp.username || '';
        document.getElementById('edit_email').value = emp.email || '';
        document.getElementById('edit_phone').value = emp.phone || '';
        document.getElementById('edit_position').value = emp.position || 'Staff';
        document.getElementById('edit_branch_id').value = emp.branch_id || '';
        document.getElementById('edit_is_active').value = emp.is_active ? '1' : '0';

        const form = document.getElementById('editEmployeeForm');
        form.action = "{{ url('admin/karyawan') }}/" + emp.id;

        document.getElementById('editEmployeeModal').style.display = 'flex';
    }

    function closeEditEmployeeModal() {
        document.getElementById('editEmployeeModal').style.display = 'none';
    }

    function toggleAddWorkHoursForm() {
        const el = document.getElementById('addWorkHoursFormCard');
        if (el) {
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
            if (el.style.display === 'block') {
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    function openEditWorkHoursModal(wh) {
        document.getElementById('edit_wh_employee_id').value = wh.employee_id || '';
        document.getElementById('edit_wh_shift_id').value = wh.shift_id || '';
        document.getElementById('edit_wh_work_date').value = wh.raw_work_date || '';
        document.getElementById('edit_wh_clock_in').value = wh.raw_clock_in || '';
        document.getElementById('edit_wh_clock_out').value = wh.raw_clock_out || '';
        document.getElementById('edit_wh_overtime').value = wh.overtime_minutes || 0;
        document.getElementById('edit_wh_status').value = wh.status || 'present';
        document.getElementById('edit_wh_notes').value = wh.notes || '';

        const form = document.getElementById('editWorkHoursForm');
        form.action = "{{ url('admin/jam-kerja') }}/" + wh.id;

        document.getElementById('editWorkHoursModal').style.display = 'flex';
    }

    function closeEditWorkHoursModal() {
        document.getElementById('editWorkHoursModal').style.display = 'none';
    }

    function toggleAddActivityForm() {
        const card = document.getElementById('addActivityFormCard');
        if (card) {
            card.style.display = card.style.display === 'none' ? 'block' : 'none';
            if (card.style.display === 'block') {
                card.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }

    function openEditActivityModal(act) {
        document.getElementById('edit_act_type').value = act.activity_type || 'meeting';
        document.getElementById('edit_act_title').value = act.title || '';
        document.getElementById('edit_act_employee_id').value = act.employee_id || '';
        document.getElementById('edit_act_branch_id').value = act.branch_id || '';
        document.getElementById('edit_act_date').value = act.raw_date || '';
        document.getElementById('edit_act_start_time').value = act.raw_start_time || '';
        document.getElementById('edit_act_end_time').value = act.raw_end_time || '';
        document.getElementById('edit_act_duration').value = act.duration_minutes || 60;
        document.getElementById('edit_act_status').value = act.status || 'hadir';
        document.getElementById('edit_act_notes').value = act.notes || '';

        const form = document.getElementById('editActivityForm');
        form.action = "{{ url('admin/kegiatan') }}/" + act.id;

        document.getElementById('editActivityModal').style.display = 'flex';
    }

    function closeEditActivityModal() {
        document.getElementById('editActivityModal').style.display = 'none';
    }

    // Hash or URL param listener on page load
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const btn = document.querySelector(`.tab-pill-btn[onclick*="${tabParam}"]`);
            if (btn) {
                switchDataTab({ currentTarget: btn, preventDefault: () => {} }, tabParam);
            }
        }
    });

    // Fitur Salin Data Rekap Jam Kerja ke Clipboard (Format TSV untuk Excel / Google Sheets)
    function copyRekapShiftTable() {
        const table = document.getElementById('table-rekap-shift');
        if (!table) return;

        let tsv = [];
        // Header
        const headerCells = [];
        table.querySelectorAll('thead th').forEach(th => {
            headerCells.push(th.innerText.trim().replace(/\s+/g, ' '));
        });
        tsv.push(headerCells.join('\t'));

        // Body rows
        table.querySelectorAll('tbody tr').forEach(tr => {
            const cells = [];
            tr.querySelectorAll('td').forEach(td => {
                cells.push(td.innerText.trim().replace(/\s+/g, ' '));
            });
            if (cells.length) {
                tsv.push(cells.join('\t'));
            }
        });

        // Footer
        table.querySelectorAll('tfoot tr').forEach(tr => {
            const cells = [];
            tr.querySelectorAll('td').forEach(td => {
                cells.push(td.innerText.trim().replace(/\s+/g, ' '));
            });
            if (cells.length) {
                tsv.push(cells.join('\t'));
            }
        });

        const tsvText = tsv.join('\n');

        const btnText = document.getElementById('btnCopyRekapShiftText');
        const originalText = btnText ? btnText.innerText : 'Copy Tabel';

        const doSuccess = () => {
            if (btnText) btnText.innerText = 'Tersalin!';
            const btn = document.getElementById('btnCopyRekapShift');
            if (btn) {
                btn.style.background = 'rgba(16, 185, 129, 0.25)';
                btn.style.borderColor = '#10b981';
                btn.style.color = '#34d399';
            }
            showToastPresensi('Data rekap jam kerja berhasil disalin ke clipboard! Siap dipaste ke Excel / Spreadsheet.');
            setTimeout(() => {
                if (btnText) btnText.innerText = originalText;
                if (btn) {
                    btn.style.background = 'rgba(56, 189, 248, 0.15)';
                    btn.style.borderColor = 'rgba(56, 189, 248, 0.35)';
                    btn.style.color = '#38bdf8';
                }
            }, 2500);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tsvText).then(doSuccess).catch(err => {
                fallbackCopy(tsvText, doSuccess);
            });
        } else {
            fallbackCopy(tsvText, doSuccess);
        }
    }

    function fallbackCopy(text, cb) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try {
            document.execCommand('copy');
            if (cb) cb();
        } catch (e) {
            alert('Gagal menyalin otomatis, silakan pilih teks tabel secara manual.');
        }
        document.body.removeChild(ta);
    }

    // Fitur Cetak / Print Rekap Jam Kerja
    function printRekapShiftTable() {
        window.print();
    }

    function showToastPresensi(msg) {
        let toast = document.getElementById('presensi-toast-notification');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'presensi-toast-notification';
            toast.style.position = 'fixed';
            toast.style.bottom = '24px';
            toast.style.right = '24px';
            toast.style.zIndex = '99999';
            toast.style.background = '#0f172a';
            toast.style.color = '#38bdf8';
            toast.style.border = '1px solid rgba(56, 189, 248, 0.4)';
            toast.style.padding = '12px 20px';
            toast.style.borderRadius = '10px';
            toast.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.5)';
            toast.style.fontSize = '13px';
            toast.style.fontWeight = '700';
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '10px';
            toast.style.transition = 'all 0.3s ease';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<span>✅</span> ' + msg;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
        }, 3000);
    }
</script>

<style>
/* CSS Styling Khusus Cetak / Print Rekap Jam Kerja */
@media print {
    @page {
        size: landscape;
        margin: 1cm;
    }
    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
    body * {
        visibility: hidden !important;
    }
    #printable-rekap-shift-card,
    #printable-rekap-shift-card * {
        visibility: visible !important;
    }
    #printable-rekap-shift-card {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }
    .no-print {
        display: none !important;
    }
    #printable-rekap-shift-card h4 {
        color: #000000 !important;
        font-size: 16pt !important;
        margin-bottom: 4px !important;
    }
    #printable-rekap-shift-card .rekap-subtext {
        color: #475569 !important;
        font-size: 10pt !important;
        margin-bottom: 12px !important;
    }
    .rekap-shift-table {
        width: 100% !important;
        border-collapse: collapse !important;
        color: #000000 !important;
        font-size: 9.5pt !important;
        border: 1px solid #334155 !important;
    }
    .rekap-shift-table th,
    .rekap-shift-table td {
        border: 1px solid #64748b !important;
        color: #000000 !important;
        padding: 6px 8px !important;
    }
    .rekap-shift-table thead th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 800 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .rekap-shift-table tfoot tr {
        background-color: #f8fafc !important;
        font-weight: 900 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .badge-mepet-print {
        background-color: #ea580c !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        padding: 2px 6px !important;
        border-radius: 3px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .badge-telat-print {
        background-color: #be185d !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        padding: 2px 6px !important;
        border-radius: 3px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .color-bo-print {
        color: #dc2626 !important;
        font-weight: 800 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
@endsection
