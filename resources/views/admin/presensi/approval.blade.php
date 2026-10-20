<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maya Graha Kencana — Persetujuan Cuti & Lembur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --header-blue: #2c7ea1;
            --header-dark-blue: #236784;
            --subnav-blue: #277799;
            --subnav-active: #195673;
            --sidebar-bg: #222d32;
            --sidebar-header: #1a2226;
            --sidebar-hover: #1e282c;
            --sidebar-text: #b8c7ce;
            --sidebar-active-text: #ffffff;
            --body-bg: #f4f6f9;
            --content-border: #d2d6de;
            --text-dark: #333333;
            --btn-blue: #286090;
            --btn-cyan: #00bcd4;
            --btn-green: #28a745;
            --btn-orange: #f0ad4e;
            --btn-red: #d9534f;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            font-size: 13px;
            overflow-x: hidden;
        }

        /* HEADER */
        .main-header {
            background: linear-gradient(to right, #246e8c, var(--header-blue));
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            color: #ffffff;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .header-brand-wrap { display: flex; align-items: center; gap: 14px; }
        .sidebar-toggle-btn { background: none; border: none; color: #fff; font-size: 18px; cursor: pointer; padding: 4px 8px; border-radius: 3px; }
        .sidebar-toggle-btn:hover { background: rgba(255,255,255,0.15); }
        .brand-title { font-size: 18px; font-weight: 700; letter-spacing: 0.3px; }
        .header-right-user { display: flex; align-items: center; gap: 12px; font-size: 12px; }
        .header-link-btn { color: #e0f2fe; text-decoration: none; padding: 4px 10px; border-radius: 3px; background: rgba(255,255,255,0.12); display: flex; align-items: center; gap: 5px; }
        .header-link-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }

        /* SUBNAV */
        .sub-navbar {
            background-color: var(--subnav-blue);
            height: 38px;
            position: fixed;
            top: 48px; left: 230px; right: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            overflow-x: auto;
            white-space: nowrap;
            padding: 0 10px;
            gap: 4px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            scrollbar-width: thin;
            transition: left 0.3s;
        }
        .subnav-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            color: #e8f4f8;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            border-radius: 3px;
            transition: background 0.15s, color 0.15s;
        }
        .subnav-item:hover { background: rgba(0,0,0,0.15); color: #ffffff; }
        .subnav-item.active { background-color: var(--subnav-active); color: #ffffff; font-weight: 700; box-shadow: inset 0 1px 2px rgba(0,0,0,0.2); }

        /* LAYOUT */
        .app-wrapper { display: flex; min-height: 100vh; padding-top: 86px; }
        .main-sidebar {
            width: 230px;
            background-color: var(--sidebar-bg);
            position: fixed;
            top: 48px; bottom: 0; left: 0;
            z-index: 1025;
            overflow-y: auto;
            transition: width 0.3s, transform 0.3s;
        }
        body.sidebar-collapse .main-sidebar { width: 50px; }
        body.sidebar-collapse .sub-navbar, body.sidebar-collapse .content-wrapper { left: 50px; margin-left: 50px; }
        .content-wrapper { flex: 1; margin-left: 230px; padding: 16px 20px 40px 20px; transition: margin-left 0.3s; }

        /* SIDEBAR USER */
        .sidebar-user-panel { padding: 12px 14px; display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.12); border-bottom: 1px solid #1a2226; }
        .user-avatar-circle { width: 34px; height: 34px; border-radius: 50%; background: #3c8dbc; color: #fff; font-weight: bold; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .user-info-name { color: #fff; font-weight: 600; font-size: 13px; }
        .user-info-role { color: #8aa4af; font-size: 11px; }

        /* INTERNAL TABS */
        .internal-nav-tabs {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid #cbd5e1;
            margin-bottom: 16px;
        }
        .internal-tab-link {
            padding: 8px 16px;
            text-decoration: none;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px 4px 0 0;
            background: #e2e8f0;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .internal-tab-link.active {
            background: #fff;
            color: var(--header-blue);
            border: 1px solid #cbd5e1;
            border-bottom: 2px solid #fff;
            margin-bottom: -2px;
        }

        /* KPI STATS CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 16px; }
        .stat-card {
            background: #fff;
            border-radius: 4px;
            padding: 14px 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-left: 4px solid var(--header-blue);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card.card-leave { border-left-color: #9c27b0; }
        .stat-card.card-overtime { border-left-color: #00bcd4; }
        .stat-card.card-correction { border-left-color: #f59e0b; }
        .stat-card.card-processed { border-left-color: #28a745; }
        .stat-value { font-size: 22px; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 12px; color: #64748b; margin-top: 2px; }
        .stat-icon { font-size: 28px; opacity: 0.8; }

        /* ALERT NOTIFICATION */
        .alert-box {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* SEGMENT TABS */
        .segment-container {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .segment-btn {
            background: #e2e8f0;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s, color 0.15s;
        }
        .segment-btn.active {
            background: var(--header-blue);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(44, 126, 161, 0.25);
        }
        .segment-count {
            background: rgba(0,0,0,0.15);
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 11px;
        }
        .segment-btn.active .segment-count {
            background: #ffffff;
            color: var(--header-blue);
        }

        /* CARD SECTION */
        .content-card {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border-top: 3px solid var(--header-blue);
            padding: 16px;
            margin-bottom: 20px;
        }
        .card-header-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* TABLES */
        .table-responsive { width: 100%; overflow-x: auto; }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }
        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 10px 12px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .table-custom td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .table-custom tbody tr:hover { background-color: #f1f5f9; }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-pending { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-approved { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-rejected { background-color: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }
        .badge-type { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

        /* ACTION BUTTONS */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-action:active { transform: scale(0.97); }
        .btn-approve { background: var(--btn-green); color: #fff; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: var(--btn-red); color: #fff; }
        .btn-reject:hover { background: #c82333; }

        /* MODAL STYLES */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.55);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 6px;
            max-width: 480px;
            width: 90%;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            animation: fadeIn 0.2s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            background: var(--header-blue);
            color: #fff;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
        }
        .modal-header.danger { background: var(--btn-red); }
        .modal-header.success { background: var(--btn-green); }
        .modal-body { padding: 18px 20px; font-size: 13px; }
        .modal-footer {
            padding: 12px 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .modal-close-btn { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
        .form-group { margin-bottom: 14px; text-align: left; }
        .form-label { display: block; font-weight: 600; font-size: 12px; color: #334155; margin-bottom: 5px; }
        .form-control {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
        }
        .form-control:focus { border-color: var(--header-blue); }
        .btn-secondary {
            background: #94a3b8;
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-secondary:hover { background: #64748b; }
    </style>
</head>
<body>

    @include('admin.partials.erp_navbar')

    <!-- APP WRAPPER -->
    <div class="app-wrapper">
        <!-- SIDEBAR -->
        <aside class="main-sidebar">
            <div class="sidebar-user-panel">
                <div class="user-avatar-circle">B</div>
                <div class="user-info-text">
                    <div class="user-info-name">Bagus</div>
                    <div class="user-info-role">manager @PUSAT</div>
                </div>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="content-wrapper">
            <!-- FLASH MESSAGES -->
            @if(session('success'))
                <div class="alert-box alert-success">
                    <span>✅</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-box alert-danger">
                    <span>⚠️</span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <!-- INTERNAL TABS -->
            <div class="internal-nav-tabs">
                <a href="{{ route('admin.presensi') }}" class="internal-tab-link">📅 Presensi Harian</a>
                <a href="{{ route('admin.presensi.rekap') }}" class="internal-tab-link">📊 Rekapitulasi Bulanan</a>
                <a href="{{ route('admin.presensi.approval') }}" class="internal-tab-link active">
                    📝 Persetujuan Cuti & Lembur
                </a>
            </div>

            @php
                $pendingLeavesCount = $leaves->where('status', App\Domains\Attendance\Enums\ApprovalStatus::Pending)->count();
                $pendingOvertimesCount = $overtimes->where('status', App\Domains\Attendance\Enums\ApprovalStatus::Pending)->count();
                $pendingCorrectionsCount = $corrections->where('status', App\Domains\Attendance\Enums\ApprovalStatus::Pending)->count();
                $pendingSwapsCount = $shiftSwaps->where('status', App\Domains\Attendance\Enums\ShiftSwapStatus::PendingSupervisor)->count();
                $processedCount = ($leaves->count() - $pendingLeavesCount) + ($overtimes->count() - $pendingOvertimesCount) + ($corrections->count() - $pendingCorrectionsCount) + ($shiftSwaps->count() - $pendingSwapsCount);
            @endphp

            <!-- KPI SUMMARY -->
            <div class="stats-grid">
                <div class="stat-card card-leave">
                    <div>
                        <div class="stat-value">{{ $pendingLeavesCount }}</div>
                        <div class="stat-label">Cuti Menunggu Approval</div>
                    </div>
                    <div class="stat-icon">🏖️</div>
                </div>
                <div class="stat-card card-overtime">
                    <div>
                        <div class="stat-value">{{ $pendingOvertimesCount }}</div>
                        <div class="stat-label">Lembur Menunggu Approval</div>
                    </div>
                    <div class="stat-icon">⏱️</div>
                </div>
                <div class="stat-card card-correction">
                    <div>
                        <div class="stat-value">{{ $pendingCorrectionsCount }}</div>
                        <div class="stat-label">Koreksi Absen Pending</div>
                    </div>
                    <div class="stat-icon">✏️</div>
                </div>
                <div class="stat-card card-processed">
                    <div>
                        <div class="stat-value">{{ $processedCount }}</div>
                        <div class="stat-label">Total Permohonan Selesai</div>
                    </div>
                    <div class="stat-icon">📋</div>
                </div>
            </div>

            <!-- SEGMENT BUTTONS -->
            <div class="segment-container">
                <button type="button" class="segment-btn active" id="tabLeavesBtn" onclick="switchSegment('leaves')">
                    🏖️ Permohonan Cuti & Izin
                    <span class="segment-count">{{ $pendingLeavesCount }} Pending</span>
                </button>
                <button type="button" class="segment-btn" id="tabOvertimesBtn" onclick="switchSegment('overtimes')">
                    ⏱️ Klaim Lembur Shift
                    <span class="segment-count">{{ $pendingOvertimesCount }} Pending</span>
                </button>
                <button type="button" class="segment-btn" id="tabCorrectionsBtn" onclick="switchSegment('corrections')">
                    ✏️ Koreksi Presensi Mandiri
                    <span class="segment-count">{{ $pendingCorrectionsCount }} Pending</span>
                </button>
                <button type="button" class="segment-btn" id="tabSwapsBtn" onclick="switchSegment('swaps')">
                    🔄 Tukar Shift Antar Karyawan
                    <span class="segment-count">{{ $pendingSwapsCount }} Pending</span>
                </button>
            </div>

            <!-- SECTION 1: PERMOHONAN CUTI & IZIN -->
            <div class="content-card" id="sectionLeaves">
                <div class="card-header-title">
                    <span>Daftar Permohonan Cuti, Sakit, & Izin Karyawan</span>
                    <span style="font-size:12px;font-weight:normal;color:#64748b;">Total: {{ $leaves->count() }} pengajuan</span>
                </div>

                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Karyawan</th>
                                <th>Cabang</th>
                                <th>Jenis Cuti</th>
                                <th>Rentang Tanggal</th>
                                <th>Durasi</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Catatan / Approver</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $index => $leave)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $leave->employee->name ?? '-' }}</strong><br>
                                        <small style="color:#64748b;">NIK: {{ $leave->employee->code ?? '-' }}</small>
                                    </td>
                                    <td>{{ $leave->employee->branch->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-type">{{ $leave->leave_type->label() }}</span>
                                    </td>
                                    <td>
                                        {{ $leave->start_date ? $leave->start_date->format('d/m/Y') : '-' }} s/d<br>
                                        {{ $leave->end_date ? $leave->end_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td><strong>{{ $leave->total_days }} Hari</strong></td>
                                    <td style="max-width:200px;">
                                        {{ $leave->reason }}
                                        @if($leave->attachment_path)
                                            <div style="margin-top:4px;">
                                                <a href="{{ asset($leave->attachment_path) }}" target="_blank" style="color:var(--header-blue);text-decoration:none;font-size:11px;">
                                                    📎 Lihat Bukti
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->status === App\Domains\Attendance\Enums\ApprovalStatus::Pending)
                                            <span class="badge badge-pending">Menunggu</span>
                                        @elseif($leave->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                                            <span class="badge badge-approved">Disetujui</span>
                                        @else
                                            <span class="badge badge-rejected">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                                            <small style="color:#059669;">Disetujui oleh: {{ $leave->approver->name ?? 'Admin' }}<br>{{ $leave->approved_at ? $leave->approved_at->format('d/m/Y H:i') : '' }}</small>
                                        @elseif($leave->status === App\Domains\Attendance\Enums\ApprovalStatus::Rejected)
                                            <small style="color:#e11d48;">Alasan: {{ $leave->rejection_note ?? '-' }}</small>
                                        @else
                                            <span style="color:#94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        @if($leave->status === App\Domains\Attendance\Enums\ApprovalStatus::Pending)
                                            <form action="{{ route('admin.presensi.leave.approve', $leave->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin menyetujui permohonan cuti karyawan ini?')">
                                                @csrf
                                                <button type="submit" class="btn-action btn-approve" title="Setujui Cuti">✓ Setujui</button>
                                            </form>

                                            <button type="button" class="btn-action btn-reject" onclick="openRejectLeaveModal('{{ $leave->id }}', '{{ addslashes($leave->employee->name ?? 'Karyawan') }}')">
                                                ✕ Tolak
                                            </button>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="text-align:center;padding:30px;color:#94a3b8;">
                                        Tidak ada permohonan cuti / izin yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 2: KLAIM LEMBUR -->
            <div class="content-card" id="sectionOvertimes" style="display:none;">
                <div class="card-header-title">
                    <span>Daftar Pengajuan Klaim Lembur Karyawan</span>
                    <span style="font-size:12px;font-weight:normal;color:#64748b;">Total: {{ $overtimes->count() }} pengajuan</span>
                </div>

                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Karyawan</th>
                                <th>Cabang</th>
                                <th>Tanggal & Shift</th>
                                <th>Menit Diajukan</th>
                                <th>Menit Disetujui</th>
                                <th>Alasan Lembur</th>
                                <th>Status</th>
                                <th>Catatan / Approver</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overtimes as $index => $ot)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $ot->employee->name ?? '-' }}</strong><br>
                                        <small style="color:#64748b;">NIK: {{ $ot->employee->code ?? '-' }}</small>
                                    </td>
                                    <td>{{ $ot->employee->branch->name ?? '-' }}</td>
                                    <td>
                                        @if($ot->attendanceRecord)
                                            <strong>{{ Carbon\Carbon::parse($ot->attendanceRecord->work_date)->format('d/m/Y') }}</strong><br>
                                            <small style="color:#475569;">{{ $ot->attendanceRecord->shift->name ?? 'Shift' }}</small>
                                        @else
                                            <span style="color:#94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color:var(--header-blue);">{{ $ot->requested_minutes }} Menit</strong>
                                        <small style="color:#64748b;">({{ round($ot->requested_minutes / 60, 1) }} Jam)</small>
                                    </td>
                                    <td>
                                        @if($ot->approved_minutes)
                                            <strong style="color:#059669;">{{ $ot->approved_minutes }} Menit</strong>
                                        @else
                                            <span style="color:#94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="max-width:220px;">{{ $ot->reason }}</td>
                                    <td>
                                        @if($ot->status === App\Domains\Attendance\Enums\ApprovalStatus::Pending)
                                            <span class="badge badge-pending">Menunggu</span>
                                        @elseif($ot->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                                            <span class="badge badge-approved">Disetujui</span>
                                        @else
                                            <span class="badge badge-rejected">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ot->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                                            <small style="color:#059669;">Disetujui oleh: {{ $ot->approver->name ?? 'Admin' }}<br>{{ $ot->approved_at ? $ot->approved_at->format('d/m/Y H:i') : '' }}</small>
                                        @elseif($ot->status === App\Domains\Attendance\Enums\ApprovalStatus::Rejected)
                                            <small style="color:#e11d48;">Alasan: {{ $ot->rejection_note ?? '-' }}</small>
                                        @else
                                            <span style="color:#94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        @if($ot->status === App\Domains\Attendance\Enums\ApprovalStatus::Pending)
                                            <button type="button" class="btn-action btn-approve" onclick="openApproveOvertimeModal('{{ $ot->id }}', '{{ addslashes($ot->employee->name ?? 'Karyawan') }}', '{{ $ot->requested_minutes }}')">
                                                ✓ Setujui
                                            </button>

                                            <button type="button" class="btn-action btn-reject" onclick="openRejectOvertimeModal('{{ $ot->id }}', '{{ addslashes($ot->employee->name ?? 'Karyawan') }}')">
                                                ✕ Tolak
                                            </button>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="text-align:center;padding:30px;color:#94a3b8;">
                                        Tidak ada klaim lembur yang diajukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 3: PERMOHONAN KOREKSI PRESENSI -->
            <div class="content-card" id="sectionCorrections" style="display:none;">
                <div class="card-header-title">
                    <span>Daftar Permohonan Koreksi Presensi (Lupa Absen / Kendala HP)</span>
                    <span style="font-size:12px;font-weight:normal;color:#64748b;">Total: {{ $corrections->count() }} pengajuan</span>
                </div>

                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Karyawan</th>
                                <th>Cabang</th>
                                <th>Tgl Dinas</th>
                                <th>Shift</th>
                                <th>Tipe Koreksi</th>
                                <th>Usulan Jam</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Catatan / Approver</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($corrections as $index => $corr)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $corr->employee->name ?? '-' }}</strong><br>
                                        <small style="color:#64748b;">NIK: {{ $corr->employee->code ?? '-' }}</small>
                                    </td>
                                    <td>{{ $corr->employee->branch->name ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $corr->work_date ? $corr->work_date->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;">
                                            {{ $corr->shift->name ?? ($corr->attendanceRecord->shift->name ?? 'Sesuai Jadwal') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($corr->correction_type === 'both')
                                            <span class="badge" style="background:#e0e7ff;color:#3730a3;">Masuk & Pulang</span>
                                        @elseif($corr->correction_type === 'clock_in')
                                            <span class="badge" style="background:#ecfdf5;color:#065f46;">Hanya Masuk</span>
                                        @else
                                            <span class="badge" style="background:#fef3c7;color:#92400e;">Hanya Pulang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($corr->proposed_clock_in_at)
                                            <div>Masuk: <strong style="color:#059669;">{{ $corr->proposed_clock_in_at->format('H:i') }}</strong></div>
                                        @endif
                                        @if($corr->proposed_clock_out_at)
                                            <div>Pulang: <strong style="color:#d97706;">{{ $corr->proposed_clock_out_at->format('H:i') }}</strong></div>
                                        @endif
                                    </td>
                                    <td style="max-width:220px;">
                                        {{ $corr->reason }}
                                        @if($corr->attachment_path)
                                            <br><a href="{{ asset($corr->attachment_path) }}" target="_blank" style="color:var(--header-blue);font-size:11px;">📎 Lihat Bukti</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Pending)
                                            <span class="badge badge-pending">Menunggu</span>
                                        @elseif($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                                            <span class="badge badge-approved">Disetujui</span>
                                        @else
                                            <span class="badge badge-rejected">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                                            <small style="color:#059669;">Disetujui oleh: {{ $corr->approver->name ?? 'Admin' }}<br>{{ $corr->approved_at ? $corr->approved_at->format('d/m/Y H:i') : '' }}</small>
                                        @elseif($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Rejected)
                                            <small style="color:#e11d48;">Alasan: {{ $corr->rejection_note ?? '-' }}</small>
                                        @else
                                            <span style="color:#94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        @if($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Pending)
                                            <form action="{{ route('admin.presensi.correction.approve', $corr->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui koreksi presensi ini? Data kehadiran karyawan akan langsung dimutakhirkan.')">
                                                @csrf
                                                <button type="submit" class="btn-action btn-approve">
                                                    ✓ Setujui
                                                </button>
                                            </form>

                                            <button type="button" class="btn-action btn-reject" onclick="openRejectCorrectionModal('{{ $corr->id }}', '{{ addslashes($corr->employee->name ?? 'Karyawan') }}')">
                                                ✕ Tolak
                                            </button>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;">
                                        Tidak ada permohonan koreksi presensi yang diajukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 4: PERMOHONAN TUKAR SHIFT ANTAR KARYAWAN -->
            <div class="content-card" id="sectionSwaps" style="display:none;">
                <div class="card-header-title">
                    <span>Permohonan Tukar Shift Antar Staf Cabang (Shift Swap)</span>
                    <span style="font-size:12px;font-weight:normal;color:#64748b;">Total: {{ $shiftSwaps->count() }} pengajuan</span>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;">No</th>
                                <th>Cabang</th>
                                <th>Staf Pengaju</th>
                                <th>Jadwal Pengaju</th>
                                <th>Rekan Dituju</th>
                                <th>Jadwal Rekan</th>
                                <th>Alasan Tukar</th>
                                <th>Persetujuan Rekan</th>
                                <th>Status</th>
                                <th>Verifikasi Supervisor</th>
                                <th style="text-align:center;width:160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shiftSwaps as $index => $swap)
                                <tr>
                                    <td style="text-align:center;color:#64748b;">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="branch-pill">
                                            🏢 {{ $swap->branch->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;color:#1e293b;">{{ $swap->requester->name ?? '-' }}</div>
                                        <div style="font-size:11px;color:#64748b;">{{ $swap->requester->code ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#0284c7;">{{ $swap->requester_date->format('d/m/Y') }}</div>
                                        <div style="font-size:11px;color:#475569;">{{ $swap->requesterShift->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:700;color:#1e293b;">{{ $swap->targetEmployee->name ?? '-' }}</div>
                                        <div style="font-size:11px;color:#64748b;">{{ $swap->targetEmployee->code ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#7c3aed;">{{ $swap->target_date->format('d/m/Y') }}</div>
                                        <div style="font-size:11px;color:#475569;">{{ $swap->targetShift->name ?? '-' }}</div>
                                    </td>
                                    <td style="max-width:180px;">
                                        <div style="font-size:12px;color:#334155;">{{ $swap->reason }}</div>
                                    </td>
                                    <td>
                                        @if($swap->peer_responded_at)
                                            <span style="color:#059669;font-weight:600;font-size:11px;">✓ Disetujui</span>
                                            <div style="font-size:10px;color:#64748b;">{{ $swap->peer_responded_at->format('d/m H:i') }}</div>
                                            @if($swap->peer_note)
                                                <div style="font-size:10px;color:#475569;font-style:italic;">"{{ $swap->peer_note }}"</div>
                                            @endif
                                        @elseif($swap->status === \App\Domains\Attendance\Enums\ShiftSwapStatus::PeerRejected)
                                            <span style="color:#dc2626;font-weight:600;font-size:11px;">✕ Ditolak Rekan</span>
                                            @if($swap->peer_note)
                                                <div style="font-size:10px;color:#dc2626;">"{{ $swap->peer_note }}"</div>
                                            @endif
                                        @else
                                            <span style="color:#d97706;font-size:11px;">⏳ Menunggu Rekan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $swap->status->badgeClass() }}" style="font-size:11px;padding:3px 8px;border-radius:999px;">
                                            {{ $swap->status->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($swap->status === \App\Domains\Attendance\Enums\ShiftSwapStatus::Approved)
                                            <div style="font-size:11px;color:#059669;font-weight:600;">✓ Disetujui</div>
                                            <div style="font-size:10px;color:#64748b;">{{ $swap->approved_at?->format('d/m/Y H:i') }}</div>
                                        @elseif($swap->status === \App\Domains\Attendance\Enums\ShiftSwapStatus::Rejected)
                                            <div style="font-size:11px;color:#dc2626;font-weight:600;">✕ Ditolak</div>
                                            <div style="font-size:10px;color:#dc2626;">{{ $swap->rejection_note }}</div>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;white-space:nowrap;">
                                        @if($swap->status === \App\Domains\Attendance\Enums\ShiftSwapStatus::PendingSupervisor)
                                            <form action="{{ route('admin.presensi.swap.approve', $swap->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda menyetujui pertukaran shift antar kedua karyawan ini?')">
                                                @csrf
                                                <button type="submit" class="btn-action btn-approve">
                                                    ✓ Setujui
                                                </button>
                                            </form>

                                            <button type="button" class="btn-action btn-reject" onclick="openRejectSwapModal('{{ $swap->id }}', '{{ addslashes($swap->requester->name ?? 'Karyawan') }}')">
                                                ✕ Tolak
                                            </button>
                                        @else
                                            <span style="color:#94a3b8;font-size:11px;">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" style="text-align:center;padding:30px;color:#94a3b8;">
                                        Tidak ada permohonan tukar shift.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL TOLAK CUTI -->
    <div class="modal-overlay" id="rejectLeaveModal">
        <div class="modal-box">
            <div class="modal-header danger">
                <span>Tolak Permohonan Cuti</span>
                <button type="button" class="modal-close-btn" onclick="closeModal('rejectLeaveModal')">&times;</button>
            </div>
            <form id="rejectLeaveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom:12px;color:#475569;">
                        Menolak permohonan cuti untuk <strong id="rejectLeaveEmpName"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="leaveRejectionNote">Alasan Penolakan <span style="color:#ef4444;">*</span></label>
                        <textarea class="form-control" name="rejection_note" id="leaveRejectionNote" rows="3" required placeholder="Contoh: Jadwal operasional cabang sedang padat atau kebutuhan staf mendesak."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('rejectLeaveModal')">Batal</button>
                    <button type="submit" class="btn-action btn-reject" style="padding:7px 14px;font-size:12px;">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SETUJUI LEMBUR -->
    <div class="modal-overlay" id="approveOvertimeModal">
        <div class="modal-box">
            <div class="modal-header success">
                <span>Setujui Klaim Lembur</span>
                <button type="button" class="modal-close-btn" onclick="closeModal('approveOvertimeModal')">&times;</button>
            </div>
            <form id="approveOvertimeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom:12px;color:#475569;">
                        Persetujuan klaim lembur untuk <strong id="approveOvertimeEmpName"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="approvedMinutesInput">Menit Lembur yang Disetujui <span style="color:#ef4444;">*</span></label>
                        <input type="number" class="form-control" name="approved_minutes" id="approvedMinutesInput" min="1" max="720" required>
                        <small style="color:#64748b;margin-top:4px;display:block;">Dapat disesuaikan jika durasi riil berbeda dengan estimasi karyawan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('approveOvertimeModal')">Batal</button>
                    <button type="submit" class="btn-action btn-approve" style="padding:7px 14px;font-size:12px;">Setujui Lembur</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TOLAK LEMBUR -->
    <div class="modal-overlay" id="rejectOvertimeModal">
        <div class="modal-box">
            <div class="modal-header danger">
                <span>Tolak Klaim Lembur</span>
                <button type="button" class="modal-close-btn" onclick="closeModal('rejectOvertimeModal')">&times;</button>
            </div>
            <form id="rejectOvertimeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom:12px;color:#475569;">
                        Menolak klaim lembur untuk <strong id="rejectOvertimeEmpName"></strong>.
                    </p>
                    <div class="form-group">
                        <label class="form-label" for="overtimeRejectionNote">Alasan Penolakan <span style="color:#ef4444;">*</span></label>
                        <textarea class="form-control" name="rejection_note" id="overtimeRejectionNote" rows="3" required placeholder="Contoh: Aktivitas di luar jam shift tidak memiliki instruksi supervisor."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('rejectOvertimeModal')">Batal</button>
                    <button type="submit" class="btn-action btn-reject" style="padding:7px 14px;font-size:12px;">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TOLAK KOREKSI PRESENSI -->
    <div class="modal-overlay" id="rejectCorrectionModal">
        <div class="modal-box">
            <div class="modal-header danger">
                <span>Tolak Permohonan Koreksi Presensi</span>
                <button type="button" class="modal-close-btn" onclick="closeModal('rejectCorrectionModal')">&times;</button>
            </div>
            <form id="rejectCorrectionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom:12px;color:#475569;">
                        Menolak permohonan koreksi presensi untuk <strong id="rejectCorrectionEmpName"></strong>.
                    </p>
                    <div class="form-group">
                        <label for="correctionRejectionNote" style="display:block;margin-bottom:6px;font-weight:500;">Alasan Penolakan:</label>
                        <textarea name="rejection_note" id="correctionRejectionNote" rows="3" class="form-control" placeholder="Contoh: Log jaringan membuktikan karyawan tidak berada di lokasi toko..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('rejectCorrectionModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-danger">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TOLAK TUKAR SHIFT -->
    <div class="modal-overlay" id="rejectSwapModal">
        <div class="modal-box">
            <div class="modal-header danger">
                <span>Tolak Permohonan Tukar Shift</span>
                <button type="button" class="modal-close-btn" onclick="closeModal('rejectSwapModal')">&times;</button>
            </div>
            <form id="rejectSwapForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p style="margin-bottom:12px;color:#475569;">
                        Menolak permohonan tukar shift yang diajukan oleh <strong id="rejectSwapEmpName"></strong>.
                    </p>
                    <div class="form-group">
                        <label for="swapRejectionNote" style="display:block;margin-bottom:6px;font-weight:500;">Alasan Penolakan:</label>
                        <textarea name="rejection_note" id="swapRejectionNote" rows="3" class="form-control" placeholder="Contoh: Kebutuhan operasional cabang memerlukan personil sesuai jadwal asli..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('rejectSwapModal')">Batal</button>
                    <button type="submit" class="btn-modal btn-modal-danger">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        sidebarToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapse');
        });

        // Tab Segment Switcher
        function switchSegment(type) {
            const btnLeaves = document.getElementById('tabLeavesBtn');
            const btnOvertimes = document.getElementById('tabOvertimesBtn');
            const btnCorrections = document.getElementById('tabCorrectionsBtn');
            const btnSwaps = document.getElementById('tabSwapsBtn');
            const secLeaves = document.getElementById('sectionLeaves');
            const secOvertimes = document.getElementById('sectionOvertimes');
            const secCorrections = document.getElementById('sectionCorrections');
            const secSwaps = document.getElementById('sectionSwaps');

            btnLeaves.classList.remove('active');
            btnOvertimes.classList.remove('active');
            btnCorrections.classList.remove('active');
            btnSwaps.classList.remove('active');
            secLeaves.style.display = 'none';
            secOvertimes.style.display = 'none';
            secCorrections.style.display = 'none';
            secSwaps.style.display = 'none';

            if (type === 'leaves') {
                btnLeaves.classList.add('active');
                secLeaves.style.display = 'block';
            } else if (type === 'overtimes') {
                btnOvertimes.classList.add('active');
                secOvertimes.style.display = 'block';
            } else if (type === 'corrections') {
                btnCorrections.classList.add('active');
                secCorrections.style.display = 'block';
            } else if (type === 'swaps') {
                btnSwaps.classList.add('active');
                secSwaps.style.display = 'block';
            }
        }

        function openRejectSwapModal(swapId, empName) {
            document.getElementById('rejectSwapEmpName').textContent = empName;
            document.getElementById('rejectSwapForm').action = '/admin/presensi/swap/' + swapId + '/reject';
            document.getElementById('swapRejectionNote').value = '';
            document.getElementById('rejectSwapModal').classList.add('show');
        }

        // Modal Helpers
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        function openRejectLeaveModal(leaveId, empName) {
            document.getElementById('rejectLeaveEmpName').textContent = empName;
            document.getElementById('rejectLeaveForm').action = '/admin/presensi/leave/' + leaveId + '/reject';
            document.getElementById('leaveRejectionNote').value = '';
            document.getElementById('rejectLeaveModal').classList.add('show');
        }

        function openApproveOvertimeModal(otId, empName, requestedMinutes) {
            document.getElementById('approveOvertimeEmpName').textContent = empName;
            document.getElementById('approvedMinutesInput').value = requestedMinutes;
            document.getElementById('approveOvertimeForm').action = '/admin/presensi/overtime/' + otId + '/approve';
            document.getElementById('approveOvertimeModal').classList.add('show');
        }

        function openRejectOvertimeModal(otId, empName) {
            document.getElementById('rejectOvertimeEmpName').textContent = empName;
            document.getElementById('rejectOvertimeForm').action = '/admin/presensi/overtime/' + otId + '/reject';
            document.getElementById('overtimeRejectionNote').value = '';
            document.getElementById('rejectOvertimeModal').classList.add('show');
        }

        function openRejectCorrectionModal(corrId, empName) {
            document.getElementById('rejectCorrectionEmpName').textContent = empName;
            document.getElementById('rejectCorrectionForm').action = '/admin/presensi/correction/' + corrId + '/reject';
            document.getElementById('correctionRejectionNote').value = '';
            document.getElementById('rejectCorrectionModal').classList.add('show');
        }

        // Close modal when clicking on overlay background
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
