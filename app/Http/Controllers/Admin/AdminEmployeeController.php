<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Models\EmployeeRegistration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        if ($request->filled('kantor') && $request->input('kantor') !== 'all') {
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
        $branches = Branch::orderBy('id', 'asc')->get();

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
        $pendingRegistrations = EmployeeRegistration::where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        $processedRegistrations = EmployeeRegistration::whereIn('status', ['approved', 'rejected'])
            ->with(['approver', 'createdEmployee'])
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get();

        $branches = Branch::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('admin.karyawan.verifikasi', compact(
            'pendingRegistrations',
            'processedRegistrations',
            'branches'
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

        // Cek anti self-approval (Prinsip SoD)
        $adminUser = Auth::user();
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
     * Tampilkan Matriks Tinjauan Hak Akses Pengguna (Periodic Access Rights Review - ISO 27001 Klausul A.8.5).
     */
    public function accessReviewIndex(Request $request): View
    {
        $query = Employee::with(['branch', 'deactivator'])->orderBy('is_active', 'desc')->orderBy('name', 'asc');

        if ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
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
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Metrik Kepatuhan ISO 27001 Klausul A.8.5
        $totalAccounts = Employee::count();
        $activeAccounts = Employee::where('is_active', true)->count();
        $deprovisionedAccounts = Employee::where('is_active', false)->count();
        $privilegedAccounts = Employee::whereIn('role', [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR])->count();
        $reviewedThisPeriod = Employee::whereNotNull('last_access_review_at')
            ->where('last_access_review_at', '>=', now()->subDays(90))
            ->count();
        $unreviewedAccounts = max(0, $totalAccounts - $reviewedThisPeriod);

        $lastSignOffLog = AuditLog::where('action', 'access_rights_periodic_review')
            ->latest('id')
            ->first();

        return view('admin.karyawan.access_review', compact(
            'employees',
            'branches',
            'totalAccounts',
            'activeAccounts',
            'deprovisionedAccounts',
            'privilegedAccounts',
            'reviewedThisPeriod',
            'unreviewedAccounts',
            'lastSignOffLog'
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
        $date = Carbon::now()->toDateString();
        $employees = Employee::with(['branch', 'deactivator'])
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

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
}
