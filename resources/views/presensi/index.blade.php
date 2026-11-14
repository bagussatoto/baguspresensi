@extends('layouts.presensi')

@section('title', 'Beranda Presensi Karyawan')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Profile Card -->
    <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, #10b981, #06b6d4); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; flex-shrink: 0; box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);">
                👤
            </div>
            <div>
                <h2 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 2px;">
                    {{ $currentEmployee->name }}
                </h2>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted);">
                    <span>NIK: {{ $currentEmployee->code }}</span>
                    <span>•</span>
                    <span>📍 {{ $currentEmployee->branch->name ?? 'Maya Graha Pusat' }}</span>
                </div>
            </div>
        </div>
        <div>
            @if($attendanceToday)
                <span class="badge badge-present">Sudah Absen</span>
            @else
                <span class="badge badge-absent">Belum Melakukan Clock-In</span>
            @endif
        </div>
    </div>

    <!-- Shift Info -->
    <div class="glass-card">
        <h3 style="font-size: 14px; font-weight: 700; color: #e2e8f0; margin-bottom: 8px;">
            Informasi Shift Hari Ini ({{ \Carbon\Carbon::parse($workDate)->translatedFormat('l, d F Y') }})
        </h3>
        <p style="font-size: 13px; color: #94a3b8;">
            Shift: <strong style="color: #fff;">{{ $activeShift->name ?? 'Shift Normal' }}</strong>
            @if($activeShift)
                ({{ substr($activeShift->start_time, 0, 5) }} - {{ substr($activeShift->end_time, 0, 5) }})
            @endif
        </p>
    </div>

    <!-- Ringkasan Statistik Bulan Ini -->
    <div class="glass-card" style="padding: 16px;">
        <div style="font-size: 13px; font-weight: 700; color: #e2e8f0; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span>📊 Ringkasan Presensi Bulan Ini</span>
            <span style="font-size: 11px; color: #94a3b8; font-weight: normal;">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center;">
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #34d399;">{{ $monthlyStats['total_hadir'] }}</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Hadir</div>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #fbbf24;">{{ $monthlyStats['total_telat'] }}</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Telat</div>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #818cf8;">{{ $monthlyStats['total_lembur_jam'] }}j</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Lembur</div>
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 10px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
                <div style="font-size: 18px; font-weight: 800; color: #38bdf8;">{{ $monthlyStats['total_izin'] }}</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">Izin/Cuti</div>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi Permintaan Tukar Shift Masuk -->
    @if(!empty($pendingShiftSwapsCount) && $pendingShiftSwapsCount > 0)
        <div class="glass-card" style="margin-top: 14px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.15)); border: 1.5px solid #f59e0b;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🔔</span>
                    <div>
                        <div style="font-weight: 800; color: #fbbf24; font-size: 13.5px;">Permintaan Tukar Shift Masuk!</div>
                        <div style="font-size: 12px; color: #fde68a;">Ada {{ $pendingShiftSwapsCount }} rekan kerja mengajak bertukar jadwal shift dengan Anda.</div>
                    </div>
                </div>
                <a href="{{ route('presensi.tukar-shift') }}" style="background: #f59e0b; color: #0f172a; text-decoration: none; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; white-space: nowrap;">
                    Tanggapi →
                </a>
            </div>
        </div>
    @endif

    <!-- Menu Portal Layanan Mandiri Karyawan (Self-Service MGK) -->
    <div style="margin-top: 20px;">
        <div style="font-size: 13.5px; font-weight: 800; color: #e2e8f0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <span>📋</span> Portal Layanan & Permohonan Karyawan
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <!-- Menu 1: Izin & Cuti -->
            <a href="{{ route('presensi.izin') }}" class="glass-card" style="text-decoration: none; display: flex; flex-direction: column; gap: 8px; transition: transform 0.15s ease; border-color: rgba(56, 189, 248, 0.25); background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(15, 23, 42, 0.6));">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 26px;">🏖️</span>
                    <span style="font-size: 10px; font-weight: 700; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 2px 6px; border-radius: 4px;">4 Jenis</span>
                </div>
                <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #f8fafc;">Izin & Cuti</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Cuti, Sakit, Pribadi, & Kemalangan</div>
                </div>
            </a>

            <!-- Menu 2: Tukar Shift -->
            <a href="{{ route('presensi.tukar-shift') }}" class="glass-card" style="text-decoration: none; display: flex; flex-direction: column; gap: 8px; transition: transform 0.15s ease; border-color: rgba(16, 185, 129, 0.25); background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(15, 23, 42, 0.6));">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 26px;">🔄</span>
                    @if(!empty($pendingShiftSwapsCount) && $pendingShiftSwapsCount > 0)
                        <span style="font-size: 10px; font-weight: 800; background: #f59e0b; color: #0f172a; padding: 2px 6px; border-radius: 4px;">{{ $pendingShiftSwapsCount }} Baru</span>
                    @else
                        <span style="font-size: 10px; font-weight: 700; background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; border-radius: 4px;">Estafet</span>
                    @endif
                </div>
                <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #f8fafc;">Tukar Shift</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Tukar jadwal dengan rekan kerja</div>
                </div>
            </a>

            <!-- Menu 3: Koreksi Absensi -->
            <a href="{{ route('presensi.koreksi') }}" class="glass-card" style="text-decoration: none; display: flex; flex-direction: column; gap: 8px; transition: transform 0.15s ease; border-color: rgba(245, 158, 11, 0.25); background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(15, 23, 42, 0.6));">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 26px;">📝</span>
                    <span style="font-size: 10px; font-weight: 700; background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 2px 6px; border-radius: 4px;">Koreksi</span>
                </div>
                <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #f8fafc;">Koreksi Jam Hadir</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Lupa tap masuk/pulang kerja</div>
                </div>
            </a>

            <!-- Menu 4: Riwayat & Status -->
            <a href="{{ route('presensi.riwayat') }}" class="glass-card" style="text-decoration: none; display: flex; flex-direction: column; gap: 8px; transition: transform 0.15s ease; border-color: rgba(168, 85, 247, 0.25); background: linear-gradient(135deg, rgba(168, 85, 247, 0.12), rgba(15, 23, 42, 0.6));">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 26px;">📊</span>
                    <span style="font-size: 10px; font-weight: 700; background: rgba(168, 85, 247, 0.2); color: #c084fc; padding: 2px 6px; border-radius: 4px;">Laporan</span>
                </div>
                <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #f8fafc;">Riwayat Presensi</div>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Cek jam hadir & lembur bulanan</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Status Permohonan Aktif Karyawan -->
    @if(isset($activeLeaveRequests) && $activeLeaveRequests->count() > 0)
        <div class="glass-card" style="margin-top: 16px;">
            <div style="font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 10px;">
                ⏳ Status Permohonan Menunggu Konfirmasi:
            </div>
            @foreach($activeLeaveRequests as $req)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 12px;">
                    <div>
                        <strong style="color: #38bdf8;">{{ $req->leave_type?->label() ?? 'Izin' }}</strong>
                        <span style="color: #64748b; font-size: 11px; margin-left: 6px;">{{ $req->start_date?->format('d/m/Y') }}</span>
                    </div>
                    <span style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        Menunggu Admin
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
