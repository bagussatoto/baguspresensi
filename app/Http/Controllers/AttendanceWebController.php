<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\Actions\RespondShiftSwapPeerAction;
use App\Domains\Attendance\Actions\SubmitAttendanceCorrectionAction;
use App\Domains\Attendance\Actions\SubmitShiftSwapAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Exceptions\AlreadyClockedInException;
use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Exceptions\NotClockedInException;
use App\Domains\Attendance\Exceptions\OutsideGeofenceException;
use App\Domains\Attendance\Exceptions\TooEarlyForClockOutException;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use App\Domains\Attendance\Services\RosterResolverService;
use App\Domains\Attendance\Services\ShiftResolverService;
use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Enums\LeaveType;
use App\Domains\Leave\Models\LeaveRequest;
use App\Rules\NoOverlappingLeaveRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Controller: Portal Web & PWA Presensi Karyawan Mandiri (Mobile-First).
 * Mengakomodasi alur login cepat karyawan, dashboard hari ini,
 * clock-in/out dengan kamera selfie dan GPS, serta permohonan cuti.
 */
class AttendanceWebController extends Controller
{
    public function __construct(
        protected ClockInAction $clockInAction,
        protected ClockOutAction $clockOutAction,
        protected ShiftResolverService $shiftResolverService,
        protected RosterResolverService $rosterResolverService
    ) {}

    /**
     * Ambil data karyawan yang sedang login dari session.
     */
    protected function getAuthenticatedEmployee(): ?Employee
    {
        $employeeId = session('presensi_employee_id');
        if (! $employeeId) {
            return null;
        }

        return Employee::with('branch')->where('is_active', true)->find($employeeId);
    }

    /**
     * Halaman Login / Pemilihan Karyawan Mandiri.
     */
    public function showLogin(): View|RedirectResponse
    {
        if ($this->getAuthenticatedEmployee()) {
            return redirect()->route('presensi.index');
        }

        $employees = Employee::with('branch')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('presensi.login', compact('employees'));
    }

    /**
     * Proses Login Karyawan Mandiri.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::where('is_active', true)->findOrFail($request->input('employee_id'));

        session(['presensi_employee_id' => $employee->id]);

        return redirect()->route('presensi.index')->with('success', 'Selamat datang, '.$employee->name.'!');
    }

    /**
     * Logout dari session presensi mobile.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('presensi_employee_id');
        $request->session()->regenerate();

        return redirect()->route('presensi.login')->with('success', 'Anda telah keluar dari portal presensi.');
    }

    /**
     * Dashboard Beranda Karyawan (Mobile Home).
     */
    public function index(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $today = Carbon::today();
        $now = Carbon::now();

        // Cari shift aktif saat ini
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        // Tentukan work_date operasional
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        // Rekaman presensi hari ini
        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->where('work_date', $workDate->toDateString())
            ->first();

        // Rekapitulasi bulan berjalan
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $monthlyStats = [
            'total_hadir' => AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
                ->count(),
            'total_telat' => AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                ->where('status', AttendanceStatus::Late)
                ->count(),
            'total_lembur_jam' => round((int) AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                ->sum('overtime_minutes') / 60, 1),
            'total_izin' => LeaveRequest::where('employee_id', $employee->id)
                ->where('status', ApprovalStatus::Approved)
                ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->sum('total_days'),
        ];

        $roster = $this->rosterResolverService->resolve($employee, $now);

        return view('presensi.index', [
            'currentEmployee' => $employee,
            'activeShift' => $activeShift,
            'workDate' => $workDate,
            'attendanceToday' => $attendanceToday,
            'monthlyStats' => $monthlyStats,
            'roster' => $roster,
        ]);
    }

    /**
     * Layar Mobile Clock-In & Clock-Out (Kamera Selfie & GPS).
     */
    public function scan(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->where('work_date', $workDate->toDateString())
            ->first();

        $branch = $employee->branch ?? Branch::first();
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
        $roster = $this->rosterResolverService->resolve($employee, $now);

        return view('presensi.scan', [
            'currentEmployee' => $employee,
            'branch' => $branch,
            'activeShift' => $activeShift,
            'attendanceToday' => $attendanceToday,
            'shifts' => $shifts,
            'roster' => $roster,
        ]);
    }

    /**
     * Submit Clock In via Web Camera & Geolocation.
     */
    public function submitClockIn(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Sesi login telah berakhir.'], 401);
        }

        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'photo' => 'required|string', // Base64 image
            'shift_id' => 'nullable|exists:shifts,id',
            'work_type' => 'nullable|string|in:shift,flexible',
            'is_mock' => 'nullable|boolean',
        ]);

        $branch = $employee->branch ?? Branch::first();
        $branchId = (int) ($branch->id ?? 1);
        $lat = $request->filled('latitude') ? (float) $request->input('latitude') : (float) ($branch->latitude ?? -6.1585);
        $lng = $request->filled('longitude') ? (float) $request->input('longitude') : (float) ($branch->longitude ?? 106.9080);

        try {
            $dto = new ClockInDTO(
                employeeId: $employee->id,
                branchId: $branchId,
                latitude: $lat,
                longitude: $lng,
                shiftId: $request->filled('shift_id') ? (int) $request->input('shift_id') : null,
                photoPath: $request->input('photo'),
                verificationMethod: VerificationMethod::GpsSelfie,
                deviceInfo: [
                    'user_agent' => $request->userAgent(),
                    'platform' => 'Web Mobile PWA',
                ],
                ipAddress: $request->ip(),
                isMockLocation: (bool) $request->input('is_mock', false),
                notes: 'Clock-in via Portal Web Karyawan',
                workType: $request->input('work_type')
            );

            $record = $this->clockInAction->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Presensi masuk berhasil dicatat! Status: '.$record->status->label(),
                'status' => $record->status->value,
                'clock_in_at' => $record->clock_in_at?->format('H:i:s'),
                'distance_meters' => $record->clock_in_distance_meters,
            ]);
        } catch (OutsideGeofenceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (AlreadyClockedInException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi masuk untuk shift hari ini.',
            ], 409);
        } catch (MockLocationDetectedException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak: Terdeteksi manipulasi GPS / Fake Location.',
            ], 403);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit Clock Out via Web Camera & Geolocation.
     */
    public function submitClockOut(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Sesi login telah berakhir.'], 401);
        }

        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'photo' => 'required|string',
            'shift_id' => 'nullable|exists:shifts,id',
            'is_mock' => 'nullable|boolean',
        ]);

        $branch = $employee->branch ?? Branch::first();
        $branchId = (int) ($branch->id ?? 1);
        $lat = $request->filled('latitude') ? (float) $request->input('latitude') : (float) ($branch->latitude ?? -6.1585);
        $lng = $request->filled('longitude') ? (float) $request->input('longitude') : (float) ($branch->longitude ?? 106.9080);

        try {
            $dto = new ClockOutDTO(
                employeeId: $employee->id,
                latitude: $lat,
                longitude: $lng,
                branchId: $branchId,
                shiftId: $request->filled('shift_id') ? (int) $request->input('shift_id') : null,
                photoPath: $request->input('photo'),
                deviceInfo: [
                    'user_agent' => $request->userAgent(),
                    'platform' => 'Web Mobile PWA',
                ],
                ipAddress: $request->ip(),
                isMockLocation: (bool) $request->input('is_mock', false),
                notes: 'Clock-out via Portal Web Karyawan',
                forceEarlyLeave: (bool) $request->input('force_early', false)
            );

            $record = $this->clockOutAction->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Presensi pulang berhasil dicatat! Total durasi: '.round($record->duration_minutes / 60, 1).' jam.',
                'clock_out_at' => $record->clock_out_at?->format('H:i:s'),
                'duration_minutes' => $record->duration_minutes,
                'effective_minutes' => $record->effective_minutes,
                'overtime_minutes' => $record->overtime_minutes,
            ]);
        } catch (NotClockedInException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan clock-in pada shift ini.',
            ], 400);
        } catch (TooEarlyForClockOutException $e) {
            return response()->json([
                'success' => false,
                'code' => 'TOO_EARLY',
                'message' => $e->getMessage(),
            ], 422);
        } catch (OutsideGeofenceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajukan Persetujuan Lembur (Overtime Request) untuk Sesi Aktif.
     */
    public function requestOvertime(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Sesi login telah berakhir.'], 401);
        }

        $request->validate([
            'requested_minutes' => 'required|integer|min:15|max:480',
            'reason' => 'required|string|max:500',
        ]);

        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ditemukan sesi kerja aktif yang sedang berjalan.',
            ], 404);
        }

        $record->update([
            'overtime_status' => 'pending',
            'overtime_requested_minutes' => (int) $request->input('requested_minutes'),
            'overtime_reason' => $request->input('reason'),
            'overtime_requested_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil dikirim ke supervisor untuk verifikasi.',
            'record' => $record->fresh(),
        ]);
    }

    /**
     * Ajukan Izin Pulang Awal (Early Leave Request) jika Durasi < Target Kontrak.
     */
    public function requestEarlyLeave(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Sesi login telah berakhir.'], 401);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ditemukan sesi kerja aktif yang sedang berjalan.',
            ], 404);
        }

        $record->update([
            'early_leave_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alasan izin pulang awal berhasil dicatat dan menunggu approval.',
            'record' => $record->fresh(),
        ]);
    }

    /**
     * Layar Form & Tracking Pengajuan Izin/Cuti.
     */
    public function izin(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $leaveTypes = LeaveType::cases();

        return view('presensi.izin', [
            'currentEmployee' => $employee,
            'leaveRequests' => $leaveRequests,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    /**
     * Submit Pengajuan Izin / Cuti.
     */
    public function submitLeave(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => ['required', 'date', 'after_or_equal:start_date', new NoOverlappingLeaveRule($employee->id)],
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:jpeg,png,pdf,jpg|max:4096',
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            try {
                $uploadDir = public_path('uploads/leaves');
                File::ensureDirectoryExists($uploadDir);
                $file = $request->file('attachment');
                $fileName = 'leave_'.$employee->id.'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move($uploadDir, $fileName);
                $attachmentPath = "uploads/leaves/{$fileName}";
            } catch (\Throwable $e) {
                report($e);
            }
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => LeaveType::from($request->input('leave_type')),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'reason' => (string) $request->input('reason'),
            'attachment_path' => $attachmentPath,
            'status' => ApprovalStatus::Pending,
        ]);

        return redirect()->route('presensi.izin')->with('success', 'Permohonan izin berhasil diajukan dan menunggu persetujuan manajemen.');
    }

    /**
     * Layar Riwayat Presensi & Log Kehadiran Karyawan.
     */
    public function riwayat(Request $request): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $records = AttendanceRecord::with(['shift', 'branch'])
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date', 'desc')
            ->get();

        $summary = [
            'hadir' => $records->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])->count(),
            'telat' => $records->where('status', AttendanceStatus::Late)->count(),
            'total_late_minutes' => $records->sum('late_minutes'),
            'total_overtime_minutes' => $records->sum('overtime_minutes'),
            'total_work_minutes' => $records->sum('duration_minutes'),
        ];

        return view('presensi.riwayat', [
            'currentEmployee' => $employee,
            'records' => $records,
            'month' => $month,
            'year' => $year,
            'summary' => $summary,
        ]);
    }

    /**
     * Layar Profil Karyawan & Pengaturan Sesi.
     */
    public function profile(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        return view('presensi.profile', [
            'currentEmployee' => $employee,
        ]);
    }

    /**
     * Layar Form & Riwayat Pengajuan Koreksi Presensi (Lupa Absen / HP Rusak).
     */
    public function koreksi(Request $request): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $corrections = AttendanceCorrection::with(['shift', 'approver', 'attendanceRecord'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();

        // Default tanggal dari query jika ada (misal dari riwayat absensi)
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        return view('presensi.koreksi', [
            'currentEmployee' => $employee,
            'corrections' => $corrections,
            'shifts' => $shifts,
            'selectedDate' => $selectedDate,
        ]);
    }

    /**
     * Submit Pengajuan Koreksi Presensi.
     */
    public function submitCorrection(Request $request, SubmitAttendanceCorrectionAction $action): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'correction_type' => 'required|in:clock_in,clock_out,both',
            'clock_in_time' => 'nullable|string',
            'clock_out_time' => 'nullable|string',
            'shift_id' => 'nullable|exists:shifts,id',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $workDate = $request->input('work_date');
        $correctionType = $request->input('correction_type');

        $proposedClockIn = null;
        if (in_array($correctionType, ['clock_in', 'both']) && $request->filled('clock_in_time')) {
            $proposedClockIn = $workDate.' '.$request->input('clock_in_time').':00';
        }

        $proposedClockOut = null;
        if (in_array($correctionType, ['clock_out', 'both']) && $request->filled('clock_out_time')) {
            $proposedClockOut = $workDate.' '.$request->input('clock_out_time').':00';
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            try {
                $uploadDir = public_path('uploads/corrections');
                File::ensureDirectoryExists($uploadDir);
                $file = $request->file('attachment');
                $fileName = 'corr_'.$employee->id.'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move($uploadDir, $fileName);
                $attachmentPath = "uploads/corrections/{$fileName}";
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            $action->execute([
                'employee_id' => $employee->id,
                'work_date' => $workDate,
                'correction_type' => $correctionType,
                'proposed_clock_in_at' => $proposedClockIn,
                'proposed_clock_out_at' => $proposedClockOut,
                'reason' => (string) $request->input('reason'),
                'attachment_path' => $attachmentPath,
                'shift_id' => $request->filled('shift_id') ? (int) $request->input('shift_id') : null,
            ]);

            return redirect()->route('presensi.koreksi')->with('success', 'Permohonan koreksi presensi berhasil diajukan dan menunggu persetujuan supervisor.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal mengajukan koreksi: '.$e->getMessage());
        }
    }

    /**
     * Halaman Modul Tukar Shift Antar Karyawan (Shift Swap).
     */
    public function shiftSwap(Request $request): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        // Rekan kerja satu cabang (aktif, selain diri sendiri)
        $colleagues = Employee::where('branch_id', $employee->branch_id)
            ->where('id', '!=', $employee->id)
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $shifts = Shift::where('is_active', true)->orderBy('start_time', 'asc')->get();

        // Permohonan masuk dari rekan kerja (butuh tanggapan)
        $incomingRequests = ShiftSwapRequest::with(['requester', 'requesterShift', 'targetShift'])
            ->where('target_employee_id', $employee->id)
            ->orderByRaw("CASE WHEN status = 'pending_peer' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        // Riwayat permohonan yang diajukan oleh karyawan ini
        $myRequests = ShiftSwapRequest::with(['targetEmployee', 'requesterShift', 'targetShift', 'approver'])
            ->where('requester_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('presensi.tukar-shift', [
            'currentEmployee' => $employee,
            'colleagues' => $colleagues,
            'shifts' => $shifts,
            'incomingRequests' => $incomingRequests,
            'myRequests' => $myRequests,
        ]);
    }

    /**
     * Submit Pengajuan Tukar Shift Antar Karyawan.
     */
    public function submitShiftSwap(Request $request, SubmitShiftSwapAction $action): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $request->validate([
            'target_employee_id' => 'required|exists:employees,id',
            'requester_shift_id' => 'required|exists:shifts,id',
            'requester_date' => 'required|date|after_or_equal:today',
            'target_shift_id' => 'required|exists:shifts,id',
            'target_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $action->execute([
                'requester_id' => $employee->id,
                'target_employee_id' => (int) $request->input('target_employee_id'),
                'requester_shift_id' => (int) $request->input('requester_shift_id'),
                'requester_date' => (string) $request->input('requester_date'),
                'target_shift_id' => (int) $request->input('target_shift_id'),
                'target_date' => (string) $request->input('target_date'),
                'reason' => (string) $request->input('reason'),
            ]);

            return redirect()->route('presensi.tukar-shift')->with('success', 'Permohonan tukar shift berhasil diajukan dan menunggu persetujuan rekan kerja.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal mengajukan tukar shift: '.$e->getMessage());
        }
    }

    /**
     * Respon Rekan Kerja Terhadap Permohonan Tukar Shift (Setuju / Tolak).
     */
    public function peerRespondShiftSwap(
        Request $request,
        ShiftSwapRequest $swap,
        RespondShiftSwapPeerAction $action
    ): RedirectResponse {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        $request->validate([
            'agree' => 'required|boolean',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $action->execute(
                $swap,
                $employee->id,
                (bool) $request->input('agree'),
                $request->input('note') ? (string) $request->input('note') : null
            );

            $msg = $request->input('agree')
                ? 'Anda telah menyetujui tukar shift. Permohonan kini menunggu verifikasi supervisor.'
                : 'Permohonan tukar shift telah Anda tolak.';

            return redirect()->route('presensi.tukar-shift')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses respon: '.$e->getMessage());
        }
    }

    /**
     * Batalkan Permohonan Tukar Shift oleh Pengaju.
     */
    public function cancelShiftSwap(Request $request, ShiftSwapRequest $swap): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi.login');
        }

        if ($swap->requester_id !== $employee->id) {
            return back()->with('error', 'Anda tidak berhak membatalkan permohonan ini.');
        }

        if ($swap->status !== ShiftSwapStatus::PendingPeer) {
            return back()->with('error', 'Hanya permohonan yang masih menunggu rekan kerja yang dapat dibatalkan.');
        }

        $swap->update(['status' => ShiftSwapStatus::Cancelled]);

        return redirect()->route('presensi.tukar-shift')->with('success', 'Permohonan tukar shift berhasil dibatalkan.');
    }
}
