<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Services\AuditLogService;
use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use App\Http\Controllers\Controller;
use App\Models\EmployeeRegistration;
use App\Models\PasswordResetRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Admin ERP Maya Graha Kencana: Pengaturan Karyawan & Hak Akses.
 */
class AdminEmployeeController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Karyawan ERP Maya Graha Kencana.
     */
    public function index(Request $request): View
    {
        $adminUser = Auth::user();
        $isBranchAdmin = $adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin();
        $assignedBranchId = $isBranchAdmin ? $adminUser->assigned_branch_id : null;

        $query = Employee::with('branch')->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        if ($assignedBranchId) {
            $query->where('branch_id', $assignedBranchId);
        } elseif ($request->filled('kantor') && $request->input('kantor') !== 'all') {
            $query->where('branch_id', $request->input('kantor'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $employees = $query->get();
        if ($assignedBranchId) {
            $branches = Branch::where('id', $assignedBranchId)->get();
        } else {
            $branches = Branch::orderBy('id', 'asc')->get();
        }

        return view('admin.karyawan.index', compact('employees', 'branches'));
    }

    /**
     * Simpan data karyawan baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'username' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'position' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'content_permission' => ['nullable', 'string', 'max:50'],
            'seniority' => ['nullable', 'string', 'max:50'],
            'evaluation' => ['nullable', 'string', 'max:50'],
            'employment_status' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', 'string'],
        ]);

        $code = 'EMP-'.str_pad((string) (Employee::max('id') + 1), 3, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'code' => $code,
            'name' => $validated['name'],
            'username' => $validated['username'] ?? strtolower(str_replace(' ', '', $validated['name'])),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'branch_id' => $validated['branch_id'] ?? Branch::where('code', 'NET-PST')->value('id') ?? Branch::first()?->id,
            'position' => $validated['position'] ?? 'Staff',
            'jabatan' => $validated['jabatan'] ?? 'sale',
            'content_permission' => $validated['content_permission'] ?? 'all',
            'seniority' => $validated['seniority'] ?? 'none',
            'evaluation' => $validated['evaluation'] ?? 'none',
            'employment_status' => $validated['employment_status'] ?? 'tetap',
            'role' => EmployeeRole::SALESMAN,
            'password' => ! empty($request->input('password')) ? bcrypt((string) $request->input('password')) : bcrypt('123456'),
            'pin' => ! empty($request->input('pin')) ? bcrypt((string) $request->input('pin')) : bcrypt('123456'),
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan.',
                'employee' => $employee,
            ]);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Karyawan baru berhasil didaftarkan.');
        }

        return redirect()->back()->with('success', 'Karyawan baru berhasil didaftarkan.');
    }

    /**
     * Update data atau field tertentu karyawan (mendukung AJAX quick update).
     */
    public function update(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        $adminUser = Auth::user();
        if ($adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin() && $adminUser->assigned_branch_id) {
            if ($employee->branch_id !== null && (int) $employee->branch_id !== (int) $adminUser->assigned_branch_id) {
                abort(403, 'Akses Ditolak (SoD): Anda tidak memiliki wewenang mengelola data karyawan cabang lain.');
            }
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'username' => ['sometimes', 'nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:150'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'branch_id' => ['sometimes', 'nullable', 'exists:branches,id'],
            'position' => ['sometimes', 'nullable', 'string', 'max:100'],
            'jabatan' => ['sometimes', 'nullable', 'string', 'max:100'],
            'content_permission' => ['sometimes', 'nullable', 'string', 'max:50'],
            'seniority' => ['sometimes', 'nullable', 'string', 'max:50'],
            'evaluation' => ['sometimes', 'nullable', 'string', 'max:50'],
            'employment_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'max_discount_approval_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin() && $adminUser->assigned_branch_id) {
            if (isset($validated['branch_id'])) {
                $validated['branch_id'] = $adminUser->assigned_branch_id;
            }
        }

        $employee->update($validated);

        if ($request->filled('password')) {
            $employee->update(['password' => bcrypt((string) $request->input('password'))]);
        }
        if ($request->filled('pin')) {
            $employee->update(['pin' => bcrypt((string) $request->input('pin'))]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data {$employee->name} berhasil diperbarui.",
                'employee' => $employee->fresh(),
            ]);
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', "Data {$employee->name} berhasil diperbarui.");
        }

        return redirect()->back()->with('success', "Data {$employee->name} berhasil diperbarui.");
    }

    /**
     * Dapatkan detail profil karyawan beserta rekaman presensi & statistik bulan ini (AJAX).
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee->load([
            'branch',
            'attendanceRecords' => fn ($q) => $q->with('shift')->latest('work_date')->limit(15),
            'leaveRequests' => fn ($q) => $q->latest('created_at')->limit(5),
        ]);

        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        $monthRecords = $employee->attendanceRecords()
            ->whereYear('work_date', $currentYear)
            ->whereMonth('work_date', $currentMonth)
            ->get();

        $totalDaysLeave = (int) $employee->leaveRequests()
            ->where('status', ApprovalStatus::Approved->value)
            ->whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->sum('total_days');

        $stats = [
            'total_hadir' => $monthRecords->where('status', AttendanceStatus::Present)->count(),
            'total_telat' => $monthRecords->where('status', AttendanceStatus::Late)->count(),
            'menit_telat' => (int) $monthRecords->sum('late_minutes'),
            'total_alpa' => $monthRecords->where('status', AttendanceStatus::Absent)->count(),
            'total_cuti' => $totalDaysLeave,
            'jam_lembur' => round($monthRecords->sum('overtime_minutes') / 60, 1),
            'total_jam_kerja' => round($monthRecords->sum('duration_minutes') / 60, 1),
        ];

        return response()->json([
            'success' => true,
            'employee' => $employee,
            'stats' => $stats,
            'recent_attendance' => $employee->attendanceRecords->map(fn ($rec) => [
                'id' => $rec->id,
                'work_date' => $rec->work_date?->format('d/m/Y') ?? '-',
                'shift' => $rec->shift?->name ?? 'Shift Reguler',
                'clock_in' => $rec->clock_in_at?->format('H:i') ?? '-',
                'clock_out' => $rec->clock_out_at?->format('H:i') ?? '-',
                'duration' => $rec->duration_minutes ? round($rec->duration_minutes / 60, 1).' Jam' : '-',
                'status_label' => $rec->status->label(),
                'status_value' => $rec->status->value,
                'badge_class' => $rec->status->badgeClass(),
                'verification' => $rec->verification_method?->label() ?? 'Terminal POS / Web Kiosk',
            ]),
        ]);
    }

    /**
     * Update pengaturan hak akses karyawan (Atur Hak Akses / Custom).
     */
    public function updateAccessRights(Request $request, Employee $employee): JsonResponse
    {
        $accessRights = $request->input('access_rights', []);

        $employee->update([
            'access_rights' => $accessRights,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Hak akses untuk {$employee->name} berhasil disimpan.",
            'access_rights' => $employee->access_rights,
        ]);
    }

    /**
     * Toggle status aktif/non-aktif karyawan (Instant De-provisioning ISO 27001 Klausul A.8.5).
     */
    public function toggleStatus(Request $request, Employee $employee, AuditLogService $auditLogService): JsonResponse|RedirectResponse
    {
        $adminUser = Auth::user();
        if ($adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin() && $adminUser->assigned_branch_id) {
            if ($employee->branch_id !== null && (int) $employee->branch_id !== (int) $adminUser->assigned_branch_id) {
                abort(403, 'Akses Ditolak (SoD): Anda tidak memiliki wewenang mengubah status karyawan cabang lain.');
            }
        }

        $isDeactivating = $employee->is_active;
        $reason = $request->input('reason')
            ?: $request->input('deactivation_reason')
            ?: ($isDeactivating ? 'Dinonaktifkan oleh Administrator' : 'Diaktifkan kembali oleh Administrator');

        if ($isDeactivating) {
            $employee->update([
                'is_active' => false,
                'deactivated_at' => now(),
                'deactivated_by' => $adminUser?->id,
                'deactivation_reason' => $reason,
            ]);

            $auditLogService->log(
                action: 'employee_deprovisioned',
                auditable: $employee,
                oldValues: ['is_active' => true],
                newValues: [
                    'is_active' => false,
                    'deactivated_at' => now()->toIso8601String(),
                    'deactivated_by' => $adminUser?->id,
                    'deactivation_reason' => $reason,
                ],
                userId: $adminUser?->id
            );
        } else {
            $employee->update([
                'is_active' => true,
                'deactivated_at' => null,
                'deactivated_by' => null,
                'deactivation_reason' => null,
            ]);

            $auditLogService->log(
                action: 'employee_reactivated',
                auditable: $employee,
                oldValues: ['is_active' => false],
                newValues: [
                    'is_active' => true,
                    'reactivated_at' => now()->toIso8601String(),
                    'reactivated_by' => $adminUser?->id,
                ],
                userId: $adminUser?->id
            );
        }

        $message = "Status {$employee->name} berhasil diubah menjadi ".($employee->is_active ? 'Aktif' : 'Non-Aktif (Akses Dicabut)');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $employee->is_active,
                'deactivated_at' => $employee->deactivated_at?->toDateTimeString(),
                'deactivation_reason' => $employee->deactivation_reason,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Hapus karyawan.
     */
    public function destroy(Employee $employee): JsonResponse|RedirectResponse
    {
        $adminUser = Auth::user();
        if ($adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin() && $adminUser->assigned_branch_id) {
            if ($employee->branch_id !== null && (int) $employee->branch_id !== (int) $adminUser->assigned_branch_id) {
                abort(403, 'Akses Ditolak (SoD): Anda tidak memiliki wewenang menghapus karyawan cabang lain.');
            }
        }

        // Accidental Data Loss Prevention & Forensic Audit Integrity (ISO 27001 Klausul A.8.15 & A.8.28)
        if ($employee->attendanceRecords()->exists()) {
            $msg = "Karyawan {$employee->name} memiliki riwayat presensi/audit trail dan tidak boleh dihapus demi integritas data & kepatuhan ISO 27001. Silakan gunakan fitur Nonaktifkan Akun (De-provisioning).";
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $name = $employee->name;
        $employee->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Karyawan {$name} berhasil dihapus dari sistem.",
            ]);
        }

        if (request()->filled('redirect_to')) {
            return redirect(request()->input('redirect_to'))->with('success', "Karyawan {$name} berhasil dihapus.");
        }

        return redirect()->back()->with('success', "Karyawan {$name} berhasil dihapus.");
    }

    /**
     * Halaman Verifikasi Antrean Pendaftaran Mandiri (Maker-Checker Staging).
     */
    public function verifikasiIndex(Request $request): View
    {
        $adminUser = Auth::user();
        $isBranchAdmin = $adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin();
        $assignedBranchId = $isBranchAdmin ? $adminUser->assigned_branch_id : null;

        $pendingRegistrations = EmployeeRegistration::where('status', 'pending')
            ->orderByRaw("CASE WHEN rejection_reason LIKE '%[Sebelumnya Ditolak]%' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $processedRegistrations = EmployeeRegistration::whereIn('status', ['approved', 'rejected'])
            ->with(['approver', 'createdEmployee'])
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get();

        $branchesQuery = Branch::where('is_active', true);
        if ($assignedBranchId) {
            $branchesQuery->where('id', $assignedBranchId);
        }
        $branches = $branchesQuery->orderBy('name', 'asc')->get();
        $profileData = AdminDashboardController::getProfileData($request);

        return view('admin.karyawan.verifikasi', compact(
            'pendingRegistrations',
            'processedRegistrations',
            'branches',
            'profileData'
        ));
    }

    /**
     * Otorisasi & Setujui Pendaftaran Mandiri Karyawan (Checker - SoD, SoT, ISO 27001).
     */
    public function approveRegistration(Request $request, EmployeeRegistration $registration): JsonResponse|RedirectResponse
    {
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Permohonan pendaftaran ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'role' => ['required', 'string'],
            'position' => ['nullable', 'string', 'max:100'],
            'max_discount_approval_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'approval_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'branch_id.required' => 'Silakan pilih cabang penempatan karyawan.',
            'branch_id.exists' => 'Cabang yang dipilih tidak valid.',
            'role.required' => 'Silakan tentukan peran / role karyawan.',
        ]);

        $adminUser = Auth::user();

        // Cek batasan hak akses Branch Admin (Least Privilege)
        if ($adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin() && $adminUser->assigned_branch_id) {
            if ((int) $validated['branch_id'] !== (int) $adminUser->assigned_branch_id) {
                return back()->with('error', 'Pelanggaran Hak Akses (Least Privilege): Branch Admin hanya dapat menempatkan karyawan pada cabang yang ditugaskan.');
            }
        }

        // Cek anti self-approval (Prinsip SoD)
        if ($adminUser && (
            strtolower((string) ($adminUser->email ?? '')) === strtolower($registration->username) ||
            strtolower((string) ($adminUser->name ?? '')) === strtolower($registration->full_name) ||
            strtolower((string) ($adminUser->username ?? '')) === strtolower($registration->username)
        )) {
            return back()->with('error', 'Pelanggaran SoD: Anda tidak diizinkan menyetujui akun pendaftaran Anda sendiri.');
        }

        // Generator kode karyawan collision-safe
        $nextId = (Employee::max('id') ?? 0) + 1;
        do {
            $code = 'EMP-'.str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);
            $nextId++;
        } while (Employee::where('code', $code)->exists());

        // Eksekusi atomik DB::transaction
        $employee = DB::transaction(function () use ($registration, $validated, $code, $adminUser) {
            $role = ($validated['role'] === 'cashier') ? EmployeeRole::KASIR : (EmployeeRole::tryFrom($validated['role']) ?? EmployeeRole::SALESMAN);

            // 1. Masukkan ke Master Data resmi (SoT)
            $emp = Employee::create([
                'branch_id' => $validated['branch_id'],
                'code' => $code,
                'name' => $registration->full_name,
                'username' => $registration->username,
                'phone' => $registration->phone,
                'email' => $registration->email,
                'role' => $role,
                'position' => $validated['position'] ?? 'Staff',
                'jabatan' => $validated['position'] ?? 'Staff',
                'max_discount_approval_percent' => $validated['max_discount_approval_percent'] ?? 0,
                'password' => $registration->password,
                'pin' => $registration->pin,
                'is_active' => true,
            ]);

            // 2. Update status staging
            $registration->update([
                'status' => 'approved',
                'approved_by' => $adminUser?->id,
                'approved_at' => now(),
                'created_employee_id' => $emp->id,
            ]);

            // 3. Catat Jejak Audit (ISO 27001)
            AuditLog::create([
                'user_id' => $adminUser?->id,
                'action' => 'EMPLOYEE_REGISTRATION_APPROVED',
                'auditable_type' => Employee::class,
                'auditable_id' => $emp->id,
                'old_values' => [
                    'registration_id' => $registration->id,
                    'status' => 'pending',
                ],
                'new_values' => [
                    'employee_id' => $emp->id,
                    'code' => $code,
                    'role' => $emp->role,
                    'branch_id' => $emp->branch_id,
                    'notes' => $validated['approval_notes'] ?? null,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $emp;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pendaftaran untuk {$employee->name} ({$employee->code}) berhasil disetujui & diaktifkan di Master Data.",
                'employee' => $employee,
            ]);
        }

        return redirect()->route('admin.karyawan.verifikasi')
            ->with('success', "Pendaftaran {$employee->name} ({$employee->code}) berhasil disetujui! Karyawan telah resmi aktif di Master Data.");
    }

    /**
     * Tolak Pendaftaran Mandiri Karyawan (Checker - SoD, ISO 27001).
     */
    public function rejectRegistration(Request $request, EmployeeRegistration $registration): JsonResponse|RedirectResponse
    {
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Permohonan pendaftaran ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'rejection_reason.required' => 'Wajib memberikan alasan penolakan pendaftaran.',
            'rejection_reason.min' => 'Alasan penolakan minimal 3 karakter.',
        ]);

        $adminUser = Auth::user();

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => $adminUser?->id,
            'approved_at' => now(),
        ]);

        // Catat jejak audit penolakan (ISO 27001)
        AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_REGISTRATION_REJECTED',
            'auditable_type' => EmployeeRegistration::class,
            'auditable_id' => $registration->id,
            'old_values' => ['status' => 'pending'],
            'new_values' => [
                'status' => 'rejected',
                'reason' => $validated['rejection_reason'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Permohonan pendaftaran {$registration->full_name} telah ditolak.",
            ]);
        }

        return redirect()->route('admin.karyawan.verifikasi')
            ->with('info', "Permohonan pendaftaran {$registration->full_name} telah ditolak.");
    }

    /**
     * Detail Berkas Pendaftaran & Log Audit ISO 27001 (Read).
     */
    public function showRegistration(EmployeeRegistration $registration): JsonResponse
    {
        $registration->load(['approver', 'createdEmployee.branch']);

        $auditTrail = AuditLog::where(function ($q) use ($registration) {
            $q->where('auditable_type', EmployeeRegistration::class)
                ->where('auditable_id', $registration->id);
            if ($registration->created_employee_id) {
                $q->orWhere(function ($sub) use ($registration) {
                    $sub->where('auditable_type', Employee::class)
                        ->where('auditable_id', $registration->created_employee_id);
                });
            }
        })->latest('id')->get();

        return response()->json([
            'success' => true,
            'registration' => $registration,
            'audit_trail' => $auditTrail,
        ]);
    }

    /**
     * Buka Ulang / Kembalikan Berkas Ditolak ke Status Pending untuk Ditinjau Ulang (Update).
     */
    public function resetRegistrationToPending(Request $request, EmployeeRegistration $registration): RedirectResponse
    {
        if ($registration->status === 'approved') {
            return back()->with('error', 'Berkas yang sudah disetujui resmi menjadi karyawan tidak dapat dikembalikan ke antrean.');
        }

        $adminUser = Auth::user();
        $oldReason = $registration->rejection_reason;

        $registration->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_REGISTRATION_RESET_TO_PENDING',
            'auditable_type' => EmployeeRegistration::class,
            'auditable_id' => $registration->id,
            'old_values' => ['status' => 'rejected', 'rejection_reason' => $oldReason],
            'new_values' => ['status' => 'pending', 'reset_by' => $adminUser?->name],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.karyawan.verifikasi')
            ->with('success', "Berkas pendaftaran {$registration->full_name} berhasil dikembalikan ke antrean pending untuk ditinjau ulang.");
    }

    /**
     * Koreksi Catatan Verifikasi / Catatan Audit (Update).
     */
    public function updateRegistrationNote(Request $request, EmployeeRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $adminUser = Auth::user();
        $oldReason = $registration->rejection_reason;

        $registration->update([
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_REGISTRATION_NOTE_UPDATED',
            'auditable_type' => EmployeeRegistration::class,
            'auditable_id' => $registration->id,
            'old_values' => ['rejection_reason' => $oldReason],
            'new_values' => ['rejection_reason' => $validated['rejection_reason']],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.karyawan.verifikasi')
            ->with('success', 'Catatan verifikasi berhasil diperbarui.');
    }

    /**
     * Hapus Arsip Berkas Pendaftaran Ditolak (Delete).
     */
    public function destroyRegistration(Request $request, EmployeeRegistration $registration): RedirectResponse
    {
        if ($registration->status === 'approved') {
            return back()->with('error', 'Berkas pendaftaran yang sudah disetujui tidak boleh dihapus demi integritas Master Data.');
        }

        $adminUser = Auth::user();
        $name = $registration->full_name;
        $username = $registration->username;

        AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_REGISTRATION_ARCHIVE_DELETED',
            'auditable_type' => EmployeeRegistration::class,
            'auditable_id' => $registration->id,
            'old_values' => [
                'full_name' => $name,
                'username' => $username,
                'phone' => $registration->phone,
                'email' => $registration->email,
                'status' => $registration->status,
            ],
            'new_values' => ['status' => 'DELETED'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        $registration->delete();

        return redirect()->route('admin.karyawan.verifikasi')
            ->with('success', "Arsip pendaftaran untuk {$name} ({$username}) telah dihapus dari sistem.");
    }

    /**
     * Tampilkan Matriks Tinjauan Hak Akses Pengguna (Periodic Access Rights Review - ISO 27001 Klausul A.8.5).
     */
    public function accessReviewIndex(Request $request): View
    {
        $adminUser = Auth::user();
        $isBranchAdmin = $adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin();
        $assignedBranchId = $isBranchAdmin ? $adminUser->assigned_branch_id : null;

        $query = Employee::with(['branch', 'deactivator'])->orderBy('is_active', 'desc')->orderBy('name', 'asc');

        if ($assignedBranchId) {
            $query->where('branch_id', $assignedBranchId);
        } elseif ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', $search)
                    ->orWhere('code', 'ilike', $search)
                    ->orWhere('username', 'ilike', $search)
                    ->orWhere('phone', 'ilike', $search);
            });
        }

        $employees = $query->paginate(20)->withQueryString();

        $branchesQuery = Branch::where('is_active', true);
        if ($assignedBranchId) {
            $branchesQuery->where('id', $assignedBranchId);
        }
        $branches = $branchesQuery->orderBy('name')->get();

        // Metrik Kepatuhan ISO 27001 Klausul A.8.5 (scoped to branch if BranchAdmin)
        $metricQuery = Employee::query();
        if ($assignedBranchId) {
            $metricQuery->where('branch_id', $assignedBranchId);
        }

        $totalAccounts = (clone $metricQuery)->count();
        $activeAccounts = (clone $metricQuery)->where('is_active', true)->count();
        $deprovisionedAccounts = (clone $metricQuery)->where('is_active', false)->count();
        $privilegedAccounts = (clone $metricQuery)->whereIn('role', [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR])->count();
        $reviewedThisPeriod = (clone $metricQuery)->whereNotNull('last_access_review_at')
            ->where('last_access_review_at', '>=', now()->subDays(90))
            ->count();
        $unreviewedAccounts = max(0, $totalAccounts - $reviewedThisPeriod);

        $lastSignOffLog = AuditLog::where('action', 'access_rights_periodic_review')
            ->latest('id')
            ->first();
        $profileData = AdminDashboardController::getProfileData($request);

        return view('admin.karyawan.access_review', compact(
            'employees',
            'branches',
            'totalAccounts',
            'activeAccounts',
            'deprovisionedAccounts',
            'privilegedAccounts',
            'reviewedThisPeriod',
            'unreviewedAccounts',
            'lastSignOffLog',
            'profileData'
        ));
    }

    /**
     * Sahkan Tinjauan Hak Akses Berkala (Sign-Off Berita Acara Review Akses).
     */
    public function signOffAccessReview(Request $request, AuditLogService $auditLogService): RedirectResponse|JsonResponse
    {
        $adminUser = Auth::user();
        $notes = $request->input('notes', 'Tinjauan berkala hak akses pengguna ISO 27001 telah diverifikasi dan disetujui.');

        $selectedIds = $request->input('employee_ids');
        $query = Employee::query();
        if (! empty($selectedIds) && is_array($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $count = $query->count();
        $query->update([
            'last_access_review_at' => now(),
        ]);

        $auditLogService->log(
            action: 'access_rights_periodic_review',
            auditable: null,
            oldValues: null,
            newValues: [
                'total_accounts_reviewed' => $count,
                'reviewer_id' => $adminUser?->id,
                'reviewer_name' => $adminUser?->name,
                'review_notes' => $notes,
                'reviewed_at' => now()->toIso8601String(),
                'iso_clause' => 'ISO/IEC 27001:2022 Klausul A.8.5',
            ],
            userId: $adminUser?->id
        );

        $msg = "Berita Acara Tinjauan Hak Akses Pengguna ({$count} akun) berhasil disahkan & dicatat ke Jejak Audit ISO 27001.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'reviewed_count' => $count,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Unduh Laporan Berita Acara Tinjauan Hak Akses Pengguna (CSV).
     */
    public function exportAccessReviewCsv(Request $request): StreamedResponse
    {
        $adminUser = Auth::user();
        $isBranchAdmin = $adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin();
        $assignedBranchId = $isBranchAdmin ? $adminUser->assigned_branch_id : null;

        $date = Carbon::now()->toDateString();
        $query = Employee::with(['branch', 'deactivator'])
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc');

        if ($assignedBranchId) {
            $query->where('branch_id', $assignedBranchId);
        }

        $employees = $query->get();

        $filename = sprintf('berita-acara-audit-akses-pengguna-%s.csv', $date);

        return response()->stream(function () use ($employees, $date): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'BERITA ACARA TINJAUAN HAK AKSES PENGGUNA (PERIODIC ACCESS RIGHTS REVIEW)',
                'STANDAR REGULASI: ISO/IEC 27001:2022 KLAUSUL A.8.5',
                'TANGGAL AUDIT: '.$date,
            ]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No',
                'NIK / Kode',
                'Nama Karyawan',
                'Username',
                'Cabang',
                'Peran / Role',
                'Jabatan',
                'Batas Diskon POS (%)',
                'Status Akun',
                'Wewenang Khusus (Privileged)',
                'Tanggal Penonaktifan',
                'Alasan Penonaktifan',
                'Login Terakhir',
                'Tinjauan Akses Terakhir',
            ]);

            foreach ($employees as $idx => $emp) {
                fputcsv($handle, [
                    $idx + 1,
                    $emp->code,
                    $emp->name,
                    $emp->username ?? '-',
                    $emp->branch?->name ?? 'Semua Cabang',
                    $emp->role?->value ?? (string) $emp->role,
                    $emp->position ?? $emp->jabatan ?? 'Staff',
                    number_format((float) $emp->max_discount_approval_percent, 1).'%',
                    $emp->is_active ? 'AKTIF' : 'NON-AKTIF (DE-PROVISIONED)',
                    $emp->isPrivileged() ? 'YA (SUPERVISOR/OTORISATOR)' : 'TIDAK (REGULER)',
                    $emp->deactivated_at ? $emp->deactivated_at->toDateTimeString() : '-',
                    $emp->deactivation_reason ?? '-',
                    $emp->last_login_at ? $emp->last_login_at->toDateTimeString() : 'Belum Pernah',
                    $emp->last_access_review_at ? $emp->last_access_review_at->toDateTimeString() : 'Belum Ditinjau',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Tampilkan antrean permohonan reset password/PIN karyawan (Maker-Checker & SoD).
     */
    public function resetPasswordIndex(Request $request): View
    {
        $adminUser = Auth::user();
        $isBranchAdmin = $adminUser && method_exists($adminUser, 'isBranchAdmin') && $adminUser->isBranchAdmin();
        $assignedBranchId = $isBranchAdmin ? $adminUser->assigned_branch_id : null;

        $pendingQuery = PasswordResetRequest::with(['employee.branch'])->where('status', 'pending');
        if ($assignedBranchId) {
            $pendingQuery->whereHas('employee', fn ($q) => $q->where('branch_id', $assignedBranchId));
        }
        $pendingRequests = $pendingQuery->orderBy('id', 'desc')->get();

        $processedQuery = PasswordResetRequest::with(['employee.branch', 'approver'])
            ->whereIn('status', ['approved', 'rejected']);
        if ($assignedBranchId) {
            $processedQuery->whereHas('employee', fn ($q) => $q->where('branch_id', $assignedBranchId));
        }
        $processedRequests = $processedQuery->orderBy('updated_at', 'desc')->limit(30)->get();

        $profileData = AdminDashboardController::getProfileData($request);

        return view('admin.karyawan.reset-password', compact(
            'pendingRequests',
            'processedRequests',
            'profileData'
        ));
    }

    /**
     * Setujui Permohonan Reset Password / PIN Karyawan.
     */
    public function approvePasswordReset(Request $request, PasswordResetRequest $resetRequest): RedirectResponse
    {
        if ($resetRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan reset ini sudah diproses sebelumnya.');
        }

        $employee = $resetRequest->employee;
        if (! $employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $adminUser = Auth::user();

        // Terapkan PIN/Password baru ke Master Data Karyawan
        $employee->update([
            'pin' => $resetRequest->new_credential,
            'password' => $resetRequest->new_credential,
        ]);

        $resetRequest->update([
            'status' => 'approved',
            'approved_by' => $adminUser?->id,
            'approved_at' => now(),
        ]);

        // Catat AuditLog
        rescue(fn () => AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_PASSWORD_RESET_APPROVED',
            'auditable_type' => PasswordResetRequest::class,
            'auditable_id' => $resetRequest->id,
            'new_values' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'status' => 'approved',
                'approved_by' => $adminUser?->name ?? 'Admin',
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]));

        return redirect()->route('admin.karyawan.reset-password')
            ->with('success', "Permohonan reset password/PIN untuk {$employee->name} ({$employee->code}) berhasil disetujui. Akun sudah dapat digunakan dengan kredensial baru.");
    }

    /**
     * Tolak Permohonan Reset Password / PIN Karyawan.
     */
    public function rejectPasswordReset(Request $request, PasswordResetRequest $resetRequest): RedirectResponse
    {
        if ($resetRequest->status !== 'pending') {
            return back()->with('error', 'Permohonan reset ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'rejection_reason.required' => 'Wajib memberikan alasan penolakan permohonan reset.',
            'rejection_reason.min' => 'Alasan penolakan minimal 3 karakter.',
        ]);

        $adminUser = Auth::user();

        $resetRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => $adminUser?->id,
            'approved_at' => now(),
        ]);

        rescue(fn () => AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_PASSWORD_RESET_REJECTED',
            'auditable_type' => PasswordResetRequest::class,
            'auditable_id' => $resetRequest->id,
            'new_values' => [
                'employee_id' => $resetRequest->employee_id,
                'employee_name' => $resetRequest->employee_name,
                'status' => 'rejected',
                'reason' => $validated['rejection_reason'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]));

        return redirect()->route('admin.karyawan.reset-password')
            ->with('info', "Permohonan reset password/PIN untuk {$resetRequest->employee_name} telah ditolak.");
    }

    /**
     * Edit / Perbarui Catatan atau Informasi Permohonan Reset Password (CRUD - Update).
     */
    public function updatePasswordReset(Request $request, PasswordResetRequest $resetRequest): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $adminUser = Auth::user();
        $oldNotes = $resetRequest->notes;
        $oldReason = $resetRequest->rejection_reason;

        $updateData = [];
        if ($request->has('notes')) {
            $updateData['notes'] = $validated['notes'];
        }
        if ($request->has('rejection_reason')) {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
        }

        $resetRequest->update($updateData);

        rescue(fn () => AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_PASSWORD_RESET_UPDATED',
            'auditable_type' => PasswordResetRequest::class,
            'auditable_id' => $resetRequest->id,
            'old_values' => [
                'notes' => $oldNotes,
                'rejection_reason' => $oldReason,
            ],
            'new_values' => $updateData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]));

        return redirect()->route('admin.karyawan.reset-password')
            ->with('success', "Catatan permohonan reset untuk {$resetRequest->employee_name} berhasil diperbarui.");
    }

    /**
     * Hapus Data / Arsip Permohonan Reset Password (CRUD - Delete).
     */
    public function destroyPasswordReset(Request $request, PasswordResetRequest $resetRequest): RedirectResponse
    {
        $adminUser = Auth::user();
        $employeeName = $resetRequest->employee_name;
        $status = $resetRequest->status;

        rescue(fn () => AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_PASSWORD_RESET_DELETED',
            'auditable_type' => PasswordResetRequest::class,
            'auditable_id' => $resetRequest->id,
            'old_values' => [
                'employee_id' => $resetRequest->employee_id,
                'employee_name' => $employeeName,
                'status' => $status,
                'phone' => $resetRequest->phone,
            ],
            'new_values' => ['status' => 'DELETED'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]));

        $resetRequest->delete();

        return redirect()->route('admin.karyawan.reset-password')
            ->with('success', "Data permohonan reset password/PIN {$employeeName} telah berhasil dihapus.");
    }

    /**
     * Halaman Index Daftar Akun Terkunci & Riwayat Otorisasi Pembukaan Blokir (Unlock Approval).
     */
    public function unlockIndex(Request $request): View
    {
        $query = AuditLog::where('auditable_type', Employee::class)
            ->whereIn('action', ['portal_login_lockout', 'EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN'])
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('new_values', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $employeeIds = $logs->pluck('auditable_id')->unique()->filter();
        $employees = Employee::with('branch')->whereIn('id', $employeeIds)->get()->keyBy('id');

        $totalLockout24h = AuditLog::where('auditable_type', Employee::class)
            ->where('action', 'portal_login_lockout')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $totalUnlocked = AuditLog::where('auditable_type', Employee::class)
            ->where('action', 'EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN')
            ->count();

        return view('admin.karyawan.unlock-index', [
            'logs' => $logs,
            'employees' => $employees,
            'totalLockout24h' => $totalLockout24h,
            'totalUnlocked' => $totalUnlocked,
        ]);
    }

    /**
     * Tampilkan Halaman Persetujuan Pembukaan Blokir (Unlock Approval) Akun Karyawan yang terkunci akibat 3x gagal login.
     */
    public function showUnlockApproval(Request $request, Employee $employee): View
    {
        $ip = (string) $request->query('ip', '');
        $expires = (int) $request->query('expires', 0);
        $token = (string) $request->query('token', '');

        // Jika dibuka langsung oleh Admin terautentikasi tanpa token di URL
        if ($token === '' && Auth::check()) {
            $ip = $ip ?: (string) (AuditLog::where('auditable_type', Employee::class)
                ->where('auditable_id', $employee->id)
                ->where('action', 'portal_login_lockout')
                ->latest()
                ->value('ip_address') ?? '127.0.0.1');
            $expires = now()->addMinutes(30)->timestamp;
            $token = hash_hmac('sha256', $employee->id.'|'.$ip.'|'.$expires, config('app.key'));
        }

        $expectedToken = hash_hmac('sha256', $employee->id.'|'.$ip.'|'.$expires, config('app.key'));

        $isExpired = $expires < now()->timestamp;
        $isValidToken = hash_equals($expectedToken, $token);

        // Ambil riwayat audit log lockout terbaru untuk employee ini
        $lastLockoutLog = AuditLog::where('auditable_type', Employee::class)
            ->where('auditable_id', $employee->id)
            ->where('action', 'portal_login_lockout')
            ->latest()
            ->first();

        return view('admin.karyawan.unlock-approval', [
            'employee' => $employee,
            'ip' => $ip,
            'expires' => $expires,
            'token' => $token,
            'isValidToken' => $isValidToken,
            'isExpired' => $isExpired,
            'lastLockoutLog' => $lastLockoutLog,
        ]);
    }

    /**
     * Proses Persetujuan Pembukaan Blokir Akun Karyawan (Maker-Checker Approval).
     */
    public function processUnlockApproval(Request $request, Employee $employee): RedirectResponse
    {
        $ip = (string) $request->input('ip', '');
        $expires = (int) $request->input('expires', 0);
        $token = (string) $request->input('token', '');

        $expectedToken = hash_hmac('sha256', $employee->id.'|'.$ip.'|'.$expires, config('app.key'));

        if (! hash_equals($expectedToken, $token) || $expires < now()->timestamp) {
            return redirect()->route('admin.karyawan.unlock.show', [
                'employee' => $employee->id,
                'ip' => $ip,
                'expires' => $expires,
                'token' => $token,
            ])->with('error', 'Token persetujuan tidak valid atau telah kedaluwarsa.');
        }

        $decision = (string) $request->input('decision', 'approve');
        $adminUser = Auth::user();

        // JIKA ADMIN MEMILIH TOLAK (Tolak / Biarkan Terkunci)
        if ($decision === 'reject') {
            rescue(fn () => AuditLog::create([
                'user_id' => $adminUser?->id,
                'action' => 'EMPLOYEE_LOCKOUT_REJECTED_BY_ADMIN',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'old_values' => [
                    'status' => 'LOCKED_OUT',
                    'target_ip' => $ip,
                ],
                'new_values' => [
                    'status' => 'REJECTED_STILL_LOCKED',
                    'rejected_by' => $adminUser?->name ?? 'Admin',
                    'rejected_at' => now()->toDateTimeString(),
                    'target_ip' => $ip,
                    'notes' => $request->input('notes', 'Pembukaan blokir akun ditolak oleh Admin. Akun tetap dalam keadaan terkunci.'),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]));

            return redirect()->route('admin.karyawan.unlock')
                ->with('info', "Permintaan pembukaan blokir akun {$employee->name} ({$employee->code}) telah DITOLAK. Akun tetap dalam keadaan terkunci demi keamanan.");
        }

        // 1. Reset RateLimiter & Tier Cache untuk membuka blokir
        $userKey = 'emp_'.$employee->id;
        RateLimiter::clear('presensi2_login:'.$userKey.'|'.$ip);
        cache()->forget('presensi2_login_tier:'.$userKey.'|'.$ip);

        // Reset juga untuk IP saat ini dan 127.0.0.1
        RateLimiter::clear('presensi2_login:'.$userKey.'|'.request()->ip());
        cache()->forget('presensi2_login_tier:'.$userKey.'|'.request()->ip());

        // Reset juga jika throttleKey menggunakan identifier username
        if ($employee->username) {
            RateLimiter::clear('presensi2_login:'.$employee->username.'|'.$ip);
            cache()->forget('presensi2_login_tier:'.$employee->username.'|'.$ip);
        }

        // 2. Catat Audit Log ISO 27001 (WORM)
        rescue(fn () => AuditLog::create([
            'user_id' => $adminUser?->id,
            'action' => 'EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => [
                'status' => 'LOCKED_OUT',
                'target_ip' => $ip,
            ],
            'new_values' => [
                'status' => 'UNLOCKED',
                'unlocked_by' => $adminUser?->name ?? 'Admin',
                'unlocked_at' => now()->toDateTimeString(),
                'target_ip' => $ip,
                'notes' => $request->input('notes', 'Pembukaan blokir akun disetujui oleh Admin melalui tautan WhatsApp Alert.'),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]));

        // 3. Notifikasi konfirmasi ke WhatsApp Karyawan jika nomor tersedia
        if ($employee->phone) {
            $msg = "✅ *[AKUN TELAH DIAKTIFKAN KEMBALI]*\n\n"
                ."Halo *{$employee->name}* ({$employee->code}),\n"
                ."Permohonan pembukaan blokir akun Anda telah disetujui (Approved) oleh Admin HRD/IT.\n\n"
                ."Status akun Anda saat ini sudah *Aktif Kembali*.\n"
                ."Silakan login ke portal presensi menggunakan username dan PIN/Password yang benar:\n"
                .'http://192.168.5.10:8086/bagus/presensi2/login';

            rescue(fn () => app(EarlyWarningNotificationService::class)->sendWhatsApp($employee->phone, $msg));
        }

        return redirect()->route('admin.karyawan.unlock')
            ->with('success', "Akun karyawan {$employee->name} ({$employee->code}) berhasil dibuka blokirnya! Status pada Riwayat Lockout telah diperbarui dan karyawan sudah dapat login kembali.");
    }
}
