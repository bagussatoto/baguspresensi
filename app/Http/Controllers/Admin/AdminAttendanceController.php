<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Actions\ApproveAttendanceCorrectionAction;
use App\Domains\Attendance\Actions\ApproveShiftSwapSupervisorAction;
use App\Domains\Attendance\Actions\RejectAttendanceCorrectionAction;
use App\Domains\Attendance\Actions\RejectShiftSwapSupervisorAction;
use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\ActivityAttendance;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Models\OvertimeClaim;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use App\Domains\Attendance\Services\AttendanceSettingService;
use App\Domains\Attendance\Services\AuditLogService;
use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Models\LeaveRequest;
use App\Http\Controllers\Controller;
use App\Models\ShiftSalesSummary;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Admin Panel ERP: Monitoring Presensi, Rekapitulasi, & Approval Cuti/Lembur.
 */
class AdminAttendanceController extends Controller
{
    /**
     * Tampilkan Dashboard Monitoring Presensi Harian.
     * GET /admin/presensi
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedBranch = $request->input('branch_id', 'all');
        $selectedShift = $request->input('shift_id', 'all');
        $selectedStatus = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $query = AttendanceRecord::with(['employee', 'branch', 'shift'])
            ->whereDate('work_date', $selectedDate);

        if ($selectedBranch !== 'all' && $selectedBranch !== '') {
            $query->where('branch_id', (int) $selectedBranch);
        }

        if ($selectedShift !== 'all' && $selectedShift !== '') {
            $query->where('shift_id', (int) $selectedShift);
        }

        if ($selectedStatus !== 'all' && $selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        if ($search !== '') {
            $query->whereHas('employee', function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        $records = $query->orderBy('clock_in_at', 'desc')->get();

        // Ringkasan Statistik Hari Terpilih
        $statsQuery = AttendanceRecord::whereDate('work_date', $selectedDate);
        if ($selectedBranch !== 'all' && $selectedBranch !== '') {
            $statsQuery->where('branch_id', (int) $selectedBranch);
        }

        $totalHadir = (clone $statsQuery)->where('status', AttendanceStatus::Present->value)->count();
        $totalTelat = (clone $statsQuery)->where('status', AttendanceStatus::Late->value)->count();
        $totalMenitTelat = (int) (clone $statsQuery)->sum('late_minutes');
        $totalLemburMenit = (int) (clone $statsQuery)->sum('overtime_minutes');

        $pendingLeaves = LeaveRequest::where('status', ApprovalStatus::Pending->value)->count();
        $pendingOvertimes = OvertimeClaim::where('status', ApprovalStatus::Pending->value)->count();

        $branches = Branch::orderBy('name', 'asc')->get();
        $shifts = Shift::where('is_active', true)->orderBy('start_time', 'asc')->get();

        $employeeIds = $records->pluck('employee_id')->filter()->unique();
        $posSummaries = ShiftSalesSummary::whereIn('employee_id', $employeeIds)
            ->whereDate('work_date', $selectedDate)
            ->get()
            ->keyBy('employee_id');

        return view('admin.presensi.index', compact(
            'records',
            'branches',
            'shifts',
            'posSummaries',
            'selectedDate',
            'selectedBranch',
            'selectedShift',
            'selectedStatus',
            'search',
            'totalHadir',
            'totalTelat',
            'totalMenitTelat',
            'totalLemburMenit',
            'pendingLeaves',
            'pendingOvertimes'
        ));
    }

    /**
     * Detail 360° SoT Presensi Karyawan & Jejak Audit ISO 27001.
     * GET /admin/presensi/{record}/detail
     */
    public function showAuditDetail(Request $request, AttendanceRecord $record): JsonResponse|View
    {
        $record->load(['employee.branch', 'branch', 'shift']);

        $posSession = ShiftSalesSummary::where('employee_id', $record->employee_id)
            ->whereDate('work_date', $record->work_date->toDateString())
            ->latest('id')
            ->first();

        $auditLogs = AuditLog::where(function ($q) use ($record) {
            $q->where('auditable_type', AttendanceRecord::class)
                ->where('auditable_id', $record->id);
        })
            ->orWhere(function ($q) use ($record) {
                if ($record->employee_id) {
                    $q->where('new_values->employee_id', $record->employee_id)
                        ->whereDate('created_at', $record->work_date->toDateString());
                }
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        $maxRadius = (float) ($record->branch?->radius_meters ?? 100.0);
        $distance = $record->clock_in_distance_meters !== null ? (float) $record->clock_in_distance_meters : null;
        $isGeofenceValid = $distance !== null ? ($distance <= $maxRadius) : null;

        $data = [
            'record' => $record,
            'employee' => $record->employee,
            'branch' => $record->branch,
            'shift' => $record->shift,
            'pos_session' => $posSession,
            'audit_logs' => $auditLogs,
            'is_geofence_valid' => $isGeofenceValid,
            'max_radius' => $maxRadius,
            'clock_in_photo_url' => $record->clock_in_photo_path ? asset($record->clock_in_photo_path) : null,
            'clock_out_photo_url' => $record->clock_out_photo_path ? asset($record->clock_out_photo_path) : null,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('admin.presensi.detail', $data);
    }

    /**
     * Unduh Berita Acara Audit Harian Presensi (CSV) - Single Source of Truth.
     * GET /admin/presensi/export-audit
     */
    public function exportDailyAuditCsv(Request $request): StreamedResponse
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedBranch = $request->input('branch_id', 'all');
        $selectedShift = $request->input('shift_id', 'all');
        $selectedStatus = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));

        $query = AttendanceRecord::with(['employee', 'branch', 'shift'])
            ->whereDate('work_date', $selectedDate);

        if ($selectedBranch !== 'all' && $selectedBranch !== '') {
            $query->where('branch_id', (int) $selectedBranch);
        }

        if ($selectedShift !== 'all' && $selectedShift !== '') {
            $query->where('shift_id', (int) $selectedShift);
        }

        if ($selectedStatus !== 'all' && $selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        if ($search !== '') {
            $query->whereHas('employee', function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        $records = $query->orderBy('clock_in_at', 'asc')->get();

        $employeeIds = $records->pluck('employee_id')->filter()->unique();
        $posSummaries = ShiftSalesSummary::whereIn('employee_id', $employeeIds)
            ->whereDate('work_date', $selectedDate)
            ->get()
            ->keyBy('employee_id');

        $filename = sprintf('audit-sot-presensi-%s.csv', $selectedDate);

        return response()->stream(function () use ($records, $posSummaries, $selectedDate): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'TANGGAL AUDIT: '.$selectedDate,
                'SISTEM: SINGLE SOURCE OF TRUTH (SOT) PRESENSI ERP',
                'STANDAR: ISO 27001 AUDIT TRAIL',
            ]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No',
                'NIK / Kode',
                'Nama Karyawan',
                'Jabatan',
                'Cabang',
                'Shift',
                'Jam Shift',
                'Clock In',
                'Clock Out',
                'Status Kehadiran',
                'Menit Telat',
                'Pulang Cepat (m)',
                'Durasi Jam',
                'Jam Lembur',
                'Jarak Masuk (m)',
                'Radius Cabang (m)',
                'Status Geofence',
                'Deteksi Fake GPS',
                'Metode Verifikasi',
                'Alamat IP',
                'Sesi Kasir POS',
                'Kas Fisik Laci',
                'Selisih Kas',
            ]);

            foreach ($records as $idx => $r) {
                $pos = $posSummaries->get($r->employee_id);
                $branchRadius = $r->branch?->radius_meters ?? 100;
                $geofenceStatus = 'N/A';
                if ($r->clock_in_distance_meters !== null) {
                    $geofenceStatus = $r->clock_in_distance_meters <= $branchRadius ? 'Dalam Radius (Sah)' : 'Luar Radius (Warning)';
                }

                $posStatus = 'Bukan Kasir';
                $blindCash = '-';
                $cashDiff = '-';
                if ($pos) {
                    $posStatus = $pos->status === 'closed' ? 'Selesai Shift' : 'Sedang Buka';
                    $blindCash = number_format((float) $pos->blind_cash_count, 0, ',', '.');
                    $cashDiff = number_format((float) $pos->cash_difference, 0, ',', '.');
                }

                fputcsv($handle, [
                    $idx + 1,
                    $r->employee?->code ?? '-',
                    $r->employee?->name ?? '-',
                    $r->employee?->position ?? '-',
                    $r->branch?->name ?? '-',
                    $r->shift?->name ?? '-',
                    $r->shift ? substr($r->shift->start_time, 0, 5).' - '.substr($r->shift->end_time, 0, 5) : '-',
                    $r->clock_in_at ? $r->clock_in_at->format('H:i:s') : '-',
                    $r->clock_out_at ? $r->clock_out_at->format('H:i:s') : '-',
                    $r->status?->value ?? '-',
                    $r->late_minutes ?? 0,
                    $r->early_leave_minutes ?? 0,
                    round(($r->duration_minutes ?? 0) / 60, 2),
                    round(($r->overtime_minutes ?? 0) / 60, 2),
                    $r->clock_in_distance_meters !== null ? round($r->clock_in_distance_meters, 1) : '-',
                    $branchRadius,
                    $geofenceStatus,
                    $r->is_mock_location_detected ? 'TERDETEKSI FAKE GPS' : 'Aman (Normal)',
                    $r->verification_method?->value ?? 'gps_selfie',
                    $r->ip_address ?? '-',
                    $posStatus,
                    $blindCash,
                    $cashDiff,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Tampilkan Rekapitulasi Presensi Karyawan (Bulanan) & Export Payroll CSV.
     * GET /admin/presensi/rekap
     */
    public function rekap(Request $request): View|StreamedResponse
    {
        $selectedMonth = (int) $request->input('month', date('n'));
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedBranch = $request->input('branch_id', 'all');

        $employeeQuery = Employee::with('branch')->where('is_active', true);
        if ($selectedBranch !== 'all' && $selectedBranch !== '') {
            $employeeQuery->where('branch_id', (int) $selectedBranch);
        }
        $employees = $employeeQuery->orderBy('name', 'asc')->get();

        // Ambil data presensi pada bulan & tahun terpilih
        $attendanceData = AttendanceRecord::whereYear('work_date', $selectedYear)
            ->whereMonth('work_date', $selectedMonth)
            ->get()
            ->groupBy('employee_id');

        // Ambil data cuti yang disetujui pada bulan tersebut
        $leaveData = LeaveRequest::where('status', ApprovalStatus::Approved->value)
            ->whereYear('start_date', $selectedYear)
            ->whereMonth('start_date', $selectedMonth)
            ->get()
            ->groupBy('employee_id');

        $rekapKaryawan = $employees->map(function (Employee $emp) use ($attendanceData, $leaveData): array {
            $records = $attendanceData->get($emp->id, collect());
            $leaves = $leaveData->get($emp->id, collect());

            $hadir = $records->where('status', AttendanceStatus::Present)->count();
            $telat = $records->where('status', AttendanceStatus::Late)->count();
            $alpa = $records->where('status', AttendanceStatus::Absent)->count();
            $menitTelat = (int) $records->sum('late_minutes');
            $menitLembur = (int) $records->sum('overtime_minutes');
            $durasiKerjaMenit = (int) $records->sum('duration_minutes');
            $effectiveMenit = (int) $records->sum('effective_minutes');
            if ($effectiveMenit === 0 && $durasiKerjaMenit > 0) {
                $effectiveMenit = $durasiKerjaMenit;
            }
            $cogsMenit = (int) $records->where('cost_center', '5-1001')->sum(function ($r) {
                return $r->effective_minutes ?: $r->duration_minutes;
            });
            $opexMenit = (int) $records->where('cost_center', '5-1002')->sum(function ($r) {
                return $r->effective_minutes ?: $r->duration_minutes;
            });
            $totalHariCuti = (int) $leaves->sum('total_days');

            return [
                'employee' => $emp,
                'total_hadir' => $hadir,
                'total_telat' => $telat,
                'total_alpa' => $alpa,
                'menit_telat' => $menitTelat,
                'jam_lembur' => round($menitLembur / 60, 1),
                'menit_lembur' => $menitLembur,
                'total_jam_kerja' => round($durasiKerjaMenit / 60, 1),
                'jam_efektif' => round($effectiveMenit / 60, 1),
                'jam_cogs' => round($cogsMenit / 60, 1),
                'jam_opex' => round($opexMenit / 60, 1),
                'total_cuti' => $totalHariCuti,
                'total_kehadiran' => $hadir + $telat,
            ];
        });

        // Export Payroll CSV jika parameter export=csv disertakan
        if ($request->input('export') === 'csv') {
            $filename = sprintf('rekap-presensi-%04d-%02d.csv', $selectedYear, $selectedMonth);

            return response()->stream(function () use ($rekapKaryawan, $selectedMonth, $selectedYear): void {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM untuk Microsoft Excel & LibreOffice Calc
                fwrite($handle, "\xEF\xBB\xBF");

                // Header Kolom CSV
                fputcsv($handle, [
                    'NIK / Kode',
                    'Nama Karyawan',
                    'Cabang',
                    'Jabatan / Posisi',
                    'Bulan',
                    'Tahun',
                    'Total Kehadiran',
                    'Tepat Waktu (Hari)',
                    'Terlambat (Hari)',
                    'Total Menit Terlambat',
                    'Alpa / Tidak Hadir (Hari)',
                    'Cuti / Izin (Hari)',
                    'Total Jam Lembur',
                    'Total Menit Lembur',
                    'Total Jam Kerja Riil',
                    'Jam HPP Cabang / Pabrik (5-1001)',
                    'Jam OPEX Desain Kreatif (5-1002)',
                    'Total Jam Kerja Efektif',
                ]);

                foreach ($rekapKaryawan as $item) {
                    /** @var Employee $emp */
                    $emp = $item['employee'];
                    fputcsv($handle, [
                        $emp->code ?? '-',
                        $emp->name,
                        $emp->branch?->name ?? 'PUSAT',
                        $emp->position ?? $emp->jabatan ?? '-',
                        $selectedMonth,
                        $selectedYear,
                        $item['total_kehadiran'],
                        $item['total_hadir'],
                        $item['total_telat'],
                        $item['menit_telat'],
                        $item['total_alpa'],
                        $item['total_cuti'],
                        $item['jam_lembur'],
                        $item['menit_lembur'],
                        $item['total_jam_kerja'],
                        $item['jam_cogs'],
                        $item['jam_opex'],
                        $item['jam_efektif'],
                    ]);
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $branches = Branch::orderBy('name', 'asc')->get();

        return view('admin.presensi.rekap', compact(
            'rekapKaryawan',
            'branches',
            'selectedMonth',
            'selectedYear',
            'selectedBranch'
        ));
    }

    /**
     * Tampilkan Antarmuka Persetujuan Cuti & Lembur.
     * GET /admin/presensi/approval
     */
    public function approval(Request $request): View
    {
        $leaves = LeaveRequest::with(['employee.branch', 'approver'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        $overtimes = OvertimeClaim::with(['attendanceRecord.shift', 'employee.branch', 'approver'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        $corrections = AttendanceCorrection::with(['attendanceRecord.shift', 'shift', 'employee.branch', 'approver'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        $shiftSwaps = ShiftSwapRequest::with([
            'branch',
            'requester',
            'targetEmployee',
            'requesterShift',
            'targetShift',
            'approver',
        ])
            ->orderByRaw("CASE WHEN status = 'pending_supervisor' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.presensi.approval', compact('leaves', 'overtimes', 'corrections', 'shiftSwaps'));
    }

    /**
     * Setujui permohonan cuti.
     */
    public function approveLeave(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $leave->update([
            'status' => ApprovalStatus::Approved,
            'approved_at' => Carbon::now(),
            'approved_by' => $request->user()?->id ?? 1,
        ]);

        return redirect()->back()->with('success', 'Permohonan cuti berhasil disetujui.');
    }

    /**
     * Tolak permohonan cuti.
     */
    public function rejectLeave(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $mandatoryNote = (bool) app(AttendanceSettingService::class)
            ->get('attendance.approval.rejection_note_mandatory', true);

        if ($mandatoryNote && empty(trim((string) $request->input('rejection_note', '')))) {
            return redirect()->back()->withErrors(['rejection_note' => 'Alasan / catatan penolakan wajib diisi.']);
        }

        $note = $request->input('rejection_note', 'Tidak memenuhi kualifikasi atau kebutuhan operasional mendesak.');

        $leave->update([
            'status' => ApprovalStatus::Rejected,
            'rejection_note' => $note,
            'approved_at' => Carbon::now(),
            'approved_by' => $request->user()?->id ?? 1,
        ]);

        return redirect()->back()->with('success', 'Permohonan cuti berhasil ditolak.');
    }

    /**
     * Setujui klaim lembur.
     */
    public function approveOvertime(Request $request, OvertimeClaim $overtime): RedirectResponse
    {
        $approvedMinutes = $request->filled('approved_minutes')
            ? (int) $request->input('approved_minutes')
            : (int) $overtime->requested_minutes;

        $overtime->update([
            'status' => ApprovalStatus::Approved,
            'approved_minutes' => $approvedMinutes,
            'approved_at' => Carbon::now(),
            'approved_by' => $request->user()?->id ?? 1,
        ]);

        return redirect()->back()->with('success', "Klaim lembur ({$approvedMinutes} menit) berhasil disetujui.");
    }

    /**
     * Tolak klaim lembur.
     */
    public function rejectOvertime(Request $request, OvertimeClaim $overtime): RedirectResponse
    {
        $note = $request->input('rejection_note', 'Aktivitas di luar lingkup lembur yang disetujui.');

        $overtime->update([
            'status' => ApprovalStatus::Rejected,
            'rejection_note' => $note,
            'approved_at' => Carbon::now(),
            'approved_by' => $request->user()?->id ?? 1,
        ]);

        return redirect()->back()->with('success', 'Klaim lembur ditolak.');
    }

    /**
     * Setujui permohonan koreksi presensi.
     */
    public function approveCorrection(
        Request $request,
        AttendanceCorrection $correction,
        ApproveAttendanceCorrectionAction $action
    ): RedirectResponse {
        $approverId = (int) ($request->user()?->id ?? 1);
        $action->execute($correction, $approverId);

        return redirect()->back()->with('success', 'Permohonan koreksi presensi berhasil disetujui dan rekaman presensi telah dimutakhirkan.');
    }

    /**
     * Tolak permohonan koreksi presensi.
     */
    public function rejectCorrection(
        Request $request,
        AttendanceCorrection $correction,
        RejectAttendanceCorrectionAction $action
    ): RedirectResponse {
        $note = $request->input('rejection_note', 'Alasan atau bukti permohonan koreksi tidak memenuhi kualifikasi.');
        $approverId = (int) ($request->user()?->id ?? 1);
        $action->execute($correction, $approverId, $note);

        return redirect()->back()->with('success', 'Permohonan koreksi presensi telah ditolak.');
    }

    /**
     * Setujui permohonan tukar shift oleh Supervisor (Tahap 2).
     */
    public function approveShiftSwap(
        Request $request,
        ShiftSwapRequest $swap,
        ApproveShiftSwapSupervisorAction $action
    ): RedirectResponse {
        $approverId = (int) ($request->user()?->id ?? 1);
        $action->execute($swap, $approverId);

        return redirect()->back()->with('success', 'Permohonan tukar shift antar karyawan berhasil disetujui.');
    }

    /**
     * Tolak permohonan tukar shift oleh Supervisor.
     */
    public function rejectShiftSwap(
        Request $request,
        ShiftSwapRequest $swap,
        RejectShiftSwapSupervisorAction $action
    ): RedirectResponse {
        $note = $request->input('rejection_note', 'Kebutuhan operasional atau penjadwalan tidak memungkinkan tukar shift.');
        $approverId = (int) ($request->user()?->id ?? 1);
        $action->execute($swap, $approverId, $note);

        return redirect()->back()->with('success', 'Permohonan tukar shift telah ditolak.');
    }

    /**
     * Setujui permohonan lembur pada record presensi (Pre-Approved Overtime Stage 12).
     */
    public function approveRecordOvertime(Request $request, AttendanceRecord $record, AuditLogService $auditLogService): RedirectResponse
    {
        $approvedMinutes = $request->filled('approved_minutes')
            ? (int) $request->input('approved_minutes')
            : (int) ($record->overtime_requested_minutes ?: 60);

        $record->update([
            'overtime_status' => 'approved',
            'overtime_approved_minutes' => $approvedMinutes,
            'overtime_approved_at' => Carbon::now(),
            'overtime_approved_by' => $request->user()?->id ?? 1,
            'overtime_admin_note' => $request->input('admin_note', 'Disetujui oleh Supervisor/Admin'),
        ]);

        // Jika sudah clock-out sebelumnya pada moda fleksibel, mutakhirkan jam lembur & efektif
        if ($record->clock_out_at && $record->work_type === 'flexible') {
            $targetHours = $record->rosterJadwal?->contract_hours ?? $record->employee?->default_contract_hours ?? 8;
            $targetMinutes = $targetHours * 60;
            if ($record->duration_minutes > $targetMinutes) {
                $rawOvertime = $record->duration_minutes - $targetMinutes;
                $ov = min($rawOvertime, $approvedMinutes);
                $record->update([
                    'overtime_minutes' => $ov,
                    'effective_minutes' => $targetMinutes + $ov,
                ]);
            }
        }

        $auditLogService->log(
            action: 'approve_overtime',
            auditable: $record,
            oldValues: null,
            newValues: [
                'employee_id' => $record->employee_id,
                'approved_minutes' => $approvedMinutes,
                'notes' => $request->input('admin_note'),
            ],
            userId: $request->user()?->id
        );

        return redirect()->back()->with('success', "Permohonan lembur ({$approvedMinutes} menit) berhasil disetujui.");
    }

    /**
     * Tolak permohonan lembur pada record presensi.
     */
    public function rejectRecordOvertime(Request $request, AttendanceRecord $record, AuditLogService $auditLogService): RedirectResponse
    {
        $note = $request->input('rejection_note', 'Tidak memenuhi syarat lembur.');

        $record->update([
            'overtime_status' => 'rejected',
            'overtime_approved_minutes' => 0,
            'overtime_approved_at' => Carbon::now(),
            'overtime_approved_by' => $request->user()?->id ?? 1,
            'overtime_admin_note' => $note,
            'overtime_minutes' => 0,
        ]);

        $auditLogService->log(
            action: 'reject_overtime',
            auditable: $record,
            oldValues: null,
            newValues: [
                'employee_id' => $record->employee_id,
                'notes' => $note,
            ],
            userId: $request->user()?->id
        );

        return redirect()->back()->with('success', 'Permohonan lembur telah ditolak.');
    }

    /**
     * Setujui permohonan izin pulang awal pada record presensi.
     */
    public function approveRecordEarlyLeave(Request $request, AttendanceRecord $record, AuditLogService $auditLogService): RedirectResponse
    {
        $record->update([
            'early_leave_approved' => true,
        ]);

        $auditLogService->log(
            action: 'approve_early_leave',
            auditable: $record,
            oldValues: null,
            newValues: [
                'employee_id' => $record->employee_id,
                'reason' => $record->early_leave_reason,
            ],
            userId: $request->user()?->id
        );

        return redirect()->back()->with('success', 'Izin pulang awal berhasil disetujui. Karyawan kini dapat melakukan presensi pulang.');
    }

    /**
     * Tambah rekaman jam kerja karyawan (Manual Admin).
     */
    public function storeWorkHours(Request $request, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'work_date' => ['required', 'date'],
            'clock_in_time' => ['required', 'string'],
            'clock_out_time' => ['nullable', 'string'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $branchId = $employee->branch_id ?? Branch::first()?->id ?? 1;

        $workDate = Carbon::parse($validated['work_date'])->toDateString();
        $clockInAt = Carbon::parse($workDate.' '.$validated['clock_in_time']);
        $clockOutAt = null;
        $durationMinutes = 0;

        if (! empty($validated['clock_out_time'])) {
            $clockOutAt = Carbon::parse($workDate.' '.$validated['clock_out_time']);
            if ($clockOutAt->lessThan($clockInAt)) {
                $clockOutAt->addDay();
            }
            $durationMinutes = max(0, (int) $clockInAt->diffInMinutes($clockOutAt));
        }

        $shift = ! empty($validated['shift_id']) ? Shift::find($validated['shift_id']) : Shift::first();
        $lateMinutes = 0;
        if ($shift && $shift->start_time) {
            $shiftStartTime = Carbon::parse($workDate.' '.$shift->start_time);
            $grace = (int) ($shift->grace_period_minutes ?? 15);
            $lateThreshold = (clone $shiftStartTime)->addMinutes($grace);
            if ($clockInAt->greaterThan($lateThreshold)) {
                $lateMinutes = (int) $shiftStartTime->diffInMinutes($clockInAt);
            }
        }

        $statusVal = $validated['status'] ?? ($lateMinutes > 0 ? AttendanceStatus::Late->value : AttendanceStatus::Present->value);
        $status = AttendanceStatus::tryFrom($statusVal) ?? AttendanceStatus::Present;

        $record = AttendanceRecord::create([
            'employee_id' => $employee->id,
            'branch_id' => $branchId,
            'shift_id' => $shift?->id,
            'work_date' => $workDate,
            'clock_in_at' => $clockInAt,
            'clock_out_at' => $clockOutAt,
            'duration_minutes' => $durationMinutes,
            'effective_minutes' => $durationMinutes,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => (int) ($validated['overtime_minutes'] ?? 0),
            'status' => $status,
            'verification_method' => VerificationMethod::AdminManual,
            'notes' => $validated['notes'] ?? 'Input Manual Admin',
        ]);

        $auditLogService->log(
            action: 'work_hours_created',
            auditable: $record,
            oldValues: null,
            newValues: $record->toArray(),
            userId: $request->user()?->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data jam kerja berhasil ditambahkan.',
                'record' => $record,
            ]);
        }

        $redirectTo = $request->input('redirect_to', route('admin.index').'#data-jamkerja');

        return redirect($redirectTo)->with('success', 'Data jam kerja karyawan berhasil ditambahkan.');
    }

    /**
     * Update rekaman jam kerja karyawan (Edit Jam Kerja).
     */
    public function updateWorkHours(Request $request, AttendanceRecord $record, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'work_date' => ['required', 'date'],
            'clock_in_time' => ['required', 'string'],
            'clock_out_time' => ['nullable', 'string'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $oldValues = $record->only([
            'employee_id', 'shift_id', 'work_date', 'clock_in_at', 'clock_out_at',
            'duration_minutes', 'late_minutes', 'overtime_minutes', 'status', 'notes',
        ]);

        $workDate = Carbon::parse($validated['work_date'])->toDateString();
        $clockInAt = Carbon::parse($workDate.' '.$validated['clock_in_time']);
        $clockOutAt = null;
        $durationMinutes = 0;

        if (! empty($validated['clock_out_time'])) {
            $clockOutAt = Carbon::parse($workDate.' '.$validated['clock_out_time']);
            if ($clockOutAt->lessThan($clockInAt)) {
                $clockOutAt->addDay();
            }
            $durationMinutes = max(0, (int) $clockInAt->diffInMinutes($clockOutAt));
        }

        $shift = ! empty($validated['shift_id']) ? Shift::find($validated['shift_id']) : $record->shift;
        $lateMinutes = 0;
        if ($shift && $shift->start_time) {
            $shiftStartTime = Carbon::parse($workDate.' '.$shift->start_time);
            $grace = (int) ($shift->grace_period_minutes ?? 15);
            $lateThreshold = (clone $shiftStartTime)->addMinutes($grace);
            if ($clockInAt->greaterThan($lateThreshold)) {
                $lateMinutes = (int) $shiftStartTime->diffInMinutes($clockInAt);
            }
        }

        $statusVal = $validated['status'] ?? ($lateMinutes > 0 ? AttendanceStatus::Late->value : AttendanceStatus::Present->value);
        $status = AttendanceStatus::tryFrom($statusVal) ?? AttendanceStatus::Present;

        $overtimeMinutes = isset($validated['overtime_minutes']) ? (int) $validated['overtime_minutes'] : ($record->overtime_minutes ?? 0);

        $record->update([
            'employee_id' => $validated['employee_id'],
            'shift_id' => $validated['shift_id'] ?? $record->shift_id,
            'work_date' => $workDate,
            'clock_in_at' => $clockInAt,
            'clock_out_at' => $clockOutAt,
            'duration_minutes' => $durationMinutes,
            'effective_minutes' => $durationMinutes,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $status,
            'notes' => $validated['notes'] ?? $record->notes,
        ]);

        $auditLogService->log(
            action: 'work_hours_updated',
            auditable: $record,
            oldValues: $oldValues,
            newValues: $record->fresh()->toArray(),
            userId: $request->user()?->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data jam kerja berhasil diperbarui.',
                'record' => $record->fresh(),
            ]);
        }

        $redirectTo = $request->input('redirect_to', route('admin.index').'#data-jamkerja');

        return redirect($redirectTo)->with('success', 'Data jam kerja karyawan berhasil diperbarui.');
    }

    /**
     * Hapus rekaman jam kerja karyawan.
     */
    public function destroyWorkHours(Request $request, AttendanceRecord $record, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $employeeName = $record->employee?->name ?? 'Karyawan';
        $workDate = $record->work_date ? Carbon::parse($record->work_date)->format('d/m/Y') : '';

        $auditLogService->log(
            action: 'work_hours_deleted',
            auditable: null,
            oldValues: $record->toArray(),
            newValues: null,
            userId: $request->user()?->id
        );

        $record->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data jam kerja {$employeeName} ({$workDate}) berhasil dihapus.",
            ]);
        }

        $redirectTo = $request->input('redirect_to', route('admin.index').'#data-jamkerja');

        return redirect($redirectTo)->with('success', "Data jam kerja {$employeeName} ({$workDate}) berhasil dihapus.");
    }

    /**
     * Simpan rekaman kehadiran kegiatan (Meeting, Pelatihan, GC).
     */
    public function storeActivity(Request $request, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'activity_type' => ['required', 'string', 'in:meeting,pelatihan,gc'],
            'title' => ['required', 'string', 'max:200'],
            'employee_id' => ['required', 'exists:employees,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'end_time' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $duration = (int) ($validated['duration_minutes'] ?? 0);
        if ($duration <= 0 && ! empty($validated['start_time']) && ! empty($validated['end_time'])) {
            $st = Carbon::parse($validated['activity_date'].' '.$validated['start_time']);
            $en = Carbon::parse($validated['activity_date'].' '.$validated['end_time']);
            if ($en->lessThan($st)) {
                $en->addDay();
            }
            $duration = max(0, (int) $st->diffInMinutes($en));
        }

        $employee = Employee::findOrFail($validated['employee_id']);
        $branchId = $validated['branch_id'] ?? $employee->branch_id ?? Branch::first()?->id;

        $activity = ActivityAttendance::create([
            'activity_type' => $validated['activity_type'],
            'title' => $validated['title'],
            'employee_id' => $employee->id,
            'branch_id' => $branchId,
            'activity_date' => Carbon::parse($validated['activity_date'])->toDateString(),
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'duration_minutes' => $duration,
            'status' => $validated['status'] ?? 'hadir',
            'notes' => $validated['notes'] ?? null,
        ]);

        $auditLogService->log(
            action: 'activity_attendance_created',
            auditable: $activity,
            oldValues: null,
            newValues: $activity->toArray(),
            userId: $request->user()?->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kehadiran kegiatan {$activity->title} berhasil dicatat.",
                'activity' => $activity,
            ]);
        }

        $redirectTo = $request->input('redirect_to', route('admin.index').'?tab=tab-presensi#data-presensi');

        return redirect($redirectTo)->with('success', "Kehadiran kegiatan '{$activity->title}' berhasil ditambahkan.");
    }

    /**
     * Update rekaman kehadiran kegiatan (Meeting, Pelatihan, GC).
     */
    public function updateActivity(Request $request, ActivityAttendance $activity, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'activity_type' => ['required', 'string', 'in:meeting,pelatihan,gc'],
            'title' => ['required', 'string', 'max:200'],
            'employee_id' => ['required', 'exists:employees,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'end_time' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $duration = (int) ($validated['duration_minutes'] ?? 0);
        if ($duration <= 0 && ! empty($validated['start_time']) && ! empty($validated['end_time'])) {
            $st = Carbon::parse($validated['activity_date'].' '.$validated['start_time']);
            $en = Carbon::parse($validated['activity_date'].' '.$validated['end_time']);
            if ($en->lessThan($st)) {
                $en->addDay();
            }
            $duration = max(0, (int) $st->diffInMinutes($en));
        }

        $oldValues = $activity->toArray();

        $activity->update([
            'activity_type' => $validated['activity_type'],
            'title' => $validated['title'],
            'employee_id' => $validated['employee_id'],
            'branch_id' => $validated['branch_id'] ?? $activity->branch_id,
            'activity_date' => Carbon::parse($validated['activity_date'])->toDateString(),
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'duration_minutes' => $duration,
            'status' => $validated['status'] ?? $activity->status,
            'notes' => $validated['notes'] ?? $activity->notes,
        ]);

        $auditLogService->log(
            action: 'activity_attendance_updated',
            auditable: $activity,
            oldValues: $oldValues,
            newValues: $activity->fresh()->toArray(),
            userId: $request->user()?->id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data kegiatan {$activity->title} berhasil diperbarui.",
                'activity' => $activity->fresh(),
            ]);
        }

        $redirectTo = $request->input('redirect_to', route('admin.index').'?tab=tab-presensi#data-presensi');

        return redirect($redirectTo)->with('success', "Data kegiatan '{$activity->title}' berhasil diperbarui.");
    }

    /**
     * Hapus rekaman kehadiran kegiatan.
     */
    public function destroyActivity(Request $request, ActivityAttendance $activity, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $title = $activity->title;

        $auditLogService->log(
            action: 'activity_attendance_deleted',
            auditable: null,
            oldValues: $activity->toArray(),
            newValues: null,
            userId: $request->user()?->id
        );

        $activity->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data kegiatan {$title} berhasil dihapus.",
            ]);
        }

        $redirectTo = $request->input('redirect_to', route('admin.index').'?tab=tab-presensi#data-presensi');

        return redirect($redirectTo)->with('success', "Data kegiatan '{$title}' berhasil dihapus.");
    }
}
