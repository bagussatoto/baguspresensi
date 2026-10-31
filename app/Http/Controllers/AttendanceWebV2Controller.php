<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\Actions\SubmitAttendanceCorrectionAction;
use App\Domains\Attendance\Actions\SyncOfflineAttendanceAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Exceptions\AlreadyClockedInException;
use App\Domains\Attendance\Models\ActivityAttendance;
use App\Domains\Attendance\Models\AttendanceCorrection;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use App\Domains\Attendance\Services\ShiftResolverService;
use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Models\LeaveRequest;
use App\Models\EmployeeRegistration;
use App\Models\PasswordResetRequest;
use App\Models\PrinterPrintJob;
use App\Models\ShiftHandoverChecklist;
use App\Models\ShiftSalesSummary;
use App\Models\ShiftStockRequest;
use App\Models\SystemMenu;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafePayment;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller: Portal Presensi Karyawan v2 (Credential-Based).
 *
 * Versi baru portal presensi yang menggunakan verifikasi Password/PIN 6-digit
 * untuk proses login dan clock-in/out — tanpa GPS dan tanpa kamera selfie.
 */
class AttendanceWebV2Controller extends Controller
{
    public function __construct(
        protected ClockInAction $clockInAction,
        protected ClockOutAction $clockOutAction,
        protected ShiftResolverService $shiftResolverService,
        protected EarlyWarningNotificationService $earlyWarningNotificationService
    ) {}

    /**
     * Ambil data karyawan yang sedang login dari session v2.
     */
    protected function getAuthenticatedEmployee(): ?Employee
    {
        $employeeId = session('presensi2_employee_id');
        if (! $employeeId) {
            return null;
        }

        $employee = Employee::with('branch')->where('is_active', true)->find($employeeId);
        if (! $employee) {
            session()->forget([
                'presensi2_employee_id',
                'presensi2_employee_name',
                'presensi2_branch_id',
                'presensi2_employee_role',
            ]);

            return null;
        }

        return $employee;
    }

    /**
     * Ambil data karyawan yang sedang login di Portal Layanan Khusus (GC, Meeting, BO/Training, Ambil Stok).
     */
    protected function getAuthenticatedLayananEmployee(): ?Employee
    {
        $employeeId = session('presensi2_layanan_employee_id');
        if (! $employeeId) {
            return null;
        }

        $employee = Employee::with('branch')->where('is_active', true)->find($employeeId);
        if (! $employee) {
            session()->forget([
                'presensi2_layanan_employee_id',
                'presensi2_layanan_employee_name',
                'presensi2_layanan_branch_id',
                'presensi2_layanan_role',
            ]);

            return null;
        }

        return $employee;
    }

    /**
     * Cari supervisor / otorisator aktif berdasarkan PIN atau Password untuk otorisasi Dual-Control SoD.
     */
    protected function resolveSupervisorByPin(string $pin, ?int $branchId = null): ?Employee
    {
        $cleanPin = trim($pin);
        if ($cleanPin === '') {
            return null;
        }

        $supervisors = Employee::where('is_active', true)
            ->whereIn('role', [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR])
            ->when($branchId, fn ($q) => $q->where(fn ($sub) => $sub->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->get();

        foreach ($supervisors as $supervisor) {
            $isPinMatch = ($supervisor->pin && ($supervisor->pin === $cleanPin || Hash::check($cleanPin, $supervisor->pin)))
                || ($supervisor->password && ($supervisor->password === $cleanPin || Hash::check($cleanPin, $supervisor->password)));

            if ($isPinMatch) {
                return $supervisor;
            }
        }

        // Jika tidak ditemukan di branch yang sama, cari secara global (misal head office / auditor)
        $globalSupervisors = Employee::where('is_active', true)
            ->whereIn('role', [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR])
            ->get();

        foreach ($globalSupervisors as $supervisor) {
            $isPinMatch = ($supervisor->pin && ($supervisor->pin === $cleanPin || Hash::check($cleanPin, $supervisor->pin)))
                || ($supervisor->password && ($supervisor->password === $cleanPin || Hash::check($cleanPin, $supervisor->password)));

            if ($isPinMatch) {
                return $supervisor;
            }
        }

        return null;
    }

    /**
     * Tampilkan Portal Login Khusus Layanan & Kegiatan Tambahan.
     */
    public function showLayananLogin(Request $request): View|RedirectResponse
    {
        $service = $request->query('service', 'gc');
        if (! in_array($service, ['gc', 'meeting', 'bo-training', 'ambil-stok'])) {
            $service = 'gc';
        }

        $employee = $this->getAuthenticatedLayananEmployee();
        if ($employee) {
            return redirect()->route('presensi2.'.$service);
        }

        $employees = Employee::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $branches = Branch::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('presensi2.layanan-login', compact('employees', 'branches', 'service'));
    }

    /**
     * Proses Login Khusus Layanan & Kegiatan Tambahan.
     */
    public function layananLogin(Request $request): RedirectResponse
    {
        $service = $request->input('service', 'gc');
        if (! in_array($service, ['gc', 'meeting', 'bo-training', 'ambil-stok'])) {
            $service = 'gc';
        }

        $request->validate([
            'employee_name' => 'required_without:employee_id|nullable|string|max:100',
            'employee_id' => 'required_without:employee_name|nullable|exists:employees,id',
            'credential' => 'required|string',
        ], [
            'employee_name.required_without' => 'Silakan tulis nama atau identitas karyawan Anda.',
            'employee_id.required_without' => 'Silakan tulis nama atau identitas karyawan Anda.',
            'employee_id.exists' => 'Data karyawan tidak ditemukan.',
            'credential.required' => 'Password atau PIN 6-digit wajib diisi.',
        ]);

        $employee = null;
        if ($request->filled('employee_id')) {
            $employee = Employee::where('is_active', true)->find($request->input('employee_id'));
        }

        if (! $employee && $request->filled('employee_name')) {
            $nameInput = trim((string) $request->input('employee_name'));

            // 1. Pencocokan langsung nama karyawan (case-insensitive)
            $employee = Employee::where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($nameInput)])
                ->first();

            // 2. Pencocokan berdasarkan kode, username, atau email
            if (! $employee) {
                $employee = Employee::where('is_active', true)
                    ->where(function ($q) use ($nameInput) {
                        $q->where('code', $nameInput)
                            ->orWhere('username', $nameInput)
                            ->orWhere('email', $nameInput);
                    })
                    ->first();
            }

            // 3. Pencocokan LIKE nama (jika hanya ada 1 hasil spesifik)
            if (! $employee) {
                $matchingEmployees = Employee::where('is_active', true)
                    ->where('name', 'LIKE', '%'.$nameInput.'%')
                    ->get();

                if ($matchingEmployees->count() === 1) {
                    $employee = $matchingEmployees->first();
                } elseif ($matchingEmployees->count() > 1) {
                    return back()->withInput()->withErrors([
                        'employee_name' => 'Ditemukan lebih dari 1 karyawan dengan nama tersebut. Mohon tuliskan nama lengkap Anda secara spesifik atau gunakan kode/NIK karyawan.',
                    ]);
                }
            }
        }

        if (! $employee) {
            return back()->withInput()->withErrors(['employee_name' => 'Akun karyawan tidak aktif atau tidak ditemukan. Pastikan penulisan nama lengkap sudah benar.']);
        }

        if (! $this->verifyEmployeeCredential($employee, (string) $request->input('credential'))) {
            return back()->withInput()->withErrors(['credential' => 'Password atau PIN 6-digit yang Anda masukkan salah.']);
        }

        session(['presensi2_layanan_employee_id' => $employee->id]);

        $serviceTitles = [
            'gc' => 'General Cleaning (GC)',
            'meeting' => 'Meeting & Briefing',
            'bo-training' => 'BO / Training',
            'ambil-stok' => 'AMBIL STOK',
        ];
        $title = $serviceTitles[$service] ?? 'Layanan';

        return redirect()->route('presensi2.'.$service)
            ->with('success', 'Selamat datang, '.$employee->name.'! Berhasil masuk ke '.$title.'.');
    }

    /**
     * Logout dari Portal Layanan Khusus.
     */
    public function layananLogout(Request $request): RedirectResponse
    {
        $service = $request->query('service', 'gc');
        session()->forget(['presensi2_layanan_employee_id']);

        return redirect()->route('presensi2.layanan.login', ['service' => $service])
            ->with('info', 'Anda telah keluar dari Portal Layanan.');
    }

    /**
     * Halaman Login v2 dengan Pilihan Karyawan (Dropdown/List) & Password/PIN.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        $intended = $request->query('intended') ?? $request->query('redirect_to');
        if ($intended && in_array($intended, ['gc', 'meeting', 'bo-training', 'ambil-stok'])) {
            return redirect()->route('presensi2.layanan.login', ['service' => $intended]);
        }

        $employee = $this->getAuthenticatedEmployee();
        if ($employee) {
            return redirect()->route('presensi2.index');
        }

        $employees = Employee::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $loginMenu = SystemMenu::where('code', 'presensi2_login')->first();

        return view('presensi2.login', compact('employees', 'loginMenu'));
    }

    /**
     * Halaman Pendaftaran & Daftar Ulang Karyawan (Username, PIN/Password, Email, No HP).
     */
    /**
     * Halaman Pendaftaran & Daftar Ulang Karyawan (Username, PIN/Password, Email, No HP, Cabang).
     */
    public function showRegister(Request $request): View|RedirectResponse
    {
        if ($this->getAuthenticatedEmployee()) {
            return redirect()->route('presensi2.index');
        }

        $branches = Branch::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $reRegistration = null;
        $reRegToken = null;

        if ($request->filled('re_reg') && $request->filled('token')) {
            $candidate = EmployeeRegistration::find($request->input('re_reg'));
            if ($candidate && in_array($candidate->status, ['rejected', 'expired'])) {
                $expectedToken = hash_hmac('sha256', $candidate->id.'|'.$candidate->username.'|'.($candidate->created_at?->timestamp ?? 0), config('app.key'));
                if (hash_equals($expectedToken, (string) $request->input('token'))) {
                    $reRegistration = $candidate;
                    $reRegToken = (string) $request->input('token');
                }
            }
        }

        return view('presensi2.register', compact('branches', 'reRegistration', 'reRegToken'));
    }

    /**
     * Pengecekan Ketersediaan Data Registrasi (Early Warning: Username, Phone, Email di 2 titik).
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $field = (string) $request->input('field');
        $value = trim((string) $request->input('value'));

        if (! in_array($field, ['username', 'phone', 'email']) || $value === '') {
            return response()->json([
                'valid' => false,
                'available' => false,
                'message' => 'Parameter tidak valid.',
            ], 422);
        }

        if ($field === 'username') {
            if (strlen($value) < 3) {
                return response()->json([
                    'valid' => false,
                    'available' => false,
                    'message' => 'Username minimal 3 karakter.',
                ]);
            }
            if (! preg_match('/^[a-zA-Z0-9_.-]+$/', $value)) {
                return response()->json([
                    'valid' => false,
                    'available' => false,
                    'message' => 'Username hanya boleh huruf, angka, titik (.), strip (-), atau garis bawah (_).',
                ]);
            }

            $existingEmployee = Employee::whereRaw('LOWER(username) = ?', [strtolower($value)])->first();
            $existingRegistration = EmployeeRegistration::activePending()
                ->whereRaw('LOWER(username) = ?', [strtolower($value)])
                ->first();

            if ($existingEmployee) {
                $suggestions = $this->generateUsernameSuggestions($value);

                return response()->json([
                    'valid' => true,
                    'available' => false,
                    'status_type' => 'master_active',
                    'message' => "Akun dengan username '{$value}' sudah terdaftar dan aktif sebagai karyawan resmi MGK. Silakan langsung login.",
                    'login_url' => route('presensi2.login'),
                    'suggestions' => $suggestions,
                ]);
            }

            if ($existingRegistration) {
                $suggestions = $this->generateUsernameSuggestions($value);

                return response()->json([
                    'valid' => true,
                    'available' => false,
                    'status_type' => 'pending_approval',
                    'message' => "Pendaftaran dengan username '{$value}' sudah pernah diajukan dan saat ini sedang menunggu persetujuan verifikasi Admin/HR.",
                    'login_url' => route('presensi2.login'),
                    'suggestions' => $suggestions,
                ]);
            }

            return response()->json([
                'valid' => true,
                'available' => true,
                'message' => 'Username tersedia dan dapat digunakan.',
            ]);
        }

        if ($field === 'phone') {
            $cleaned = preg_replace('/[^0-9]/', '', $value);
            // Validasi format nomor telepon orang (Indonesia: 08xx / 628xx, panjang 10-15 digit)
            $isPersonPhone = preg_match('/^(08|628|\+628)[0-9]{8,12}$/', $value) || (strlen($cleaned) >= 10 && strlen($cleaned) <= 15 && in_array(substr($cleaned, 0, 2), ['08', '62']));

            if (! $isPersonPhone) {
                return response()->json([
                    'valid' => false,
                    'available' => false,
                    'message' => 'Nomor HP tidak valid. Pastikan nomor ponsel asli yang aktif (contoh: 081234567890).',
                ]);
            }

            $inEmployee = Employee::where('phone', $value)->orWhere('phone', $cleaned)->exists();
            $inRegistration = EmployeeRegistration::activePending()
                ->where(function ($q) use ($value, $cleaned) {
                    $q->where('phone', $value)->orWhere('phone', $cleaned);
                })
                ->exists();

            if ($inEmployee || $inRegistration) {
                $location = $inEmployee ? 'Master Data Karyawan' : 'Antrean Verifikasi Pendaftaran';

                return response()->json([
                    'valid' => true,
                    'available' => false,
                    'message' => "Nomor HP sudah terdaftar di {$location}. Nomor HP tidak dapat digunakan kembali.",
                ]);
            }

            return response()->json([
                'valid' => true,
                'available' => true,
                'message' => 'Nomor HP tersedia dan dapat digunakan.',
            ]);
        }

        if ($field === 'email') {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'valid' => false,
                    'available' => false,
                    'message' => 'Format alamat email tidak valid.',
                ]);
            }

            $inEmployee = Employee::whereRaw('LOWER(email) = ?', [strtolower($value)])->exists();
            $inRegistration = EmployeeRegistration::activePending()
                ->whereRaw('LOWER(email) = ?', [strtolower($value)])
                ->exists();

            if ($inEmployee || $inRegistration) {
                $location = $inEmployee ? 'Master Data Karyawan' : 'Antrean Verifikasi Pendaftaran';

                return response()->json([
                    'valid' => true,
                    'available' => false,
                    'message' => "Email sudah terdaftar di {$location}. Silakan gunakan email lain.",
                ]);
            }

            return response()->json([
                'valid' => true,
                'available' => true,
                'message' => 'Alamat email tersedia dan dapat digunakan.',
            ]);
        }

        return response()->json(['valid' => false, 'available' => false, 'message' => 'Pemeriksaan gagal.']);
    }

    /**
     * Buat rekomendasi alternatif username yang belum digunakan.
     */
    protected function generateUsernameSuggestions(string $base): array
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9_.-]/', '', $base));
        $candidates = [
            $base.rand(10, 99),
            $base.'_'.date('y'),
            $base.'.'.rand(1, 99),
            $base.rand(100, 999),
        ];

        $results = [];
        foreach ($candidates as $cand) {
            $inEmp = Employee::whereRaw('LOWER(username) = ?', [$cand])->exists();
            $inReg = EmployeeRegistration::activePending()->whereRaw('LOWER(username) = ?', [$cand])->exists();
            if (! $inEmp && ! $inReg && ! in_array($cand, $results)) {
                $results[] = $cand;
            }
            if (count($results) >= 3) {
                break;
            }
        }

        return $results;
    }

    /**
     * Proses Pendaftaran / Daftar Ulang Karyawan.
     */
    public function register(Request $request): RedirectResponse
    {
        // Proteksi Honeypot Anti-Spam Bot: Jika input jebakan diisi, tolak diam-diam tanpa membebani database
        if ($request->filled('honeypot_website') || $request->filled('website')) {
            Log::warning('Honeypot anti-spam bot terpicu pada formulir registrasi presensi.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $request->except(['credential', 'confirm_credential', 'password', 'pin']),
            ]);

            return redirect()->route('presensi2.login')
                ->with('success', 'Permohonan pendaftaran akun Anda berhasil diajukan dan sedang menunggu verifikasi Admin/HR.');
        }

        // --- ATTEMPT TRACKER & RATE LIMITER CEK REGISTRASI (MAX 3 KALI GAGAL) ---
        $clientIp = $request->ip() ?? '127.0.0.1';
        $ipAttemptKey = 'reg_attempt_ip:'.$clientIp;

        if (RateLimiter::tooManyAttempts($ipAttemptKey, 3)) {
            $seconds = RateLimiter::availableIn($ipAttemptKey);
            $minutes = (int) ceil($seconds / 60);

            return back()->withInput()->with('lockout_seconds', $seconds)->withErrors([
                'registration' => "Terlalu banyak percobaan pendaftaran gagal dari perangkat ini (3x berturut-turut). Demi keamanan, akses dibekukan selama {$minutes} menit ({$seconds} detik).",
            ]);
        }

        $validationRules = [
            'full_name' => 'nullable|string|min:3|max:150',
            'username' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_.-]+$/',
            'credential' => 'required|string|min:6|max:50',
            'email' => 'required|email|max:100',
            'phone' => ['required', 'string', 'min:8', 'max:20', 'regex:/^(08|628|\+628)[0-9]{8,12}$/'],
        ];

        if ($request->has('confirm_credential')) {
            $validationRules['confirm_credential'] = 'required|same:credential';
        }

        $request->validate($validationRules, [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik (.), garis bawah (_), dan tanda hubung (-).',
            'credential.required' => 'PIN atau Password wajib diisi.',
            'credential.min' => 'PIN wajib 6 digit angka, atau Password wajib minimal 8 karakter (huruf/angka).',
            'confirm_credential.required' => 'Konfirmasi PIN atau Password wajib diisi.',
            'confirm_credential.same' => 'Konfirmasi PIN atau Password tidak cocok. Pastikan pengetikan sama persis.',
            'phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'phone.regex' => 'Format nomor HP tidak valid. Gunakan format ponsel aktif (contoh: 081234567890).',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $username = trim((string) $request->input('username'));
        $credential = trim((string) $request->input('credential'));

        // Validasi Kredensial: PIN tepat 6 digit angka, atau Password minimal 8 karakter (angka/huruf)
        $isNumericCredential = ctype_digit($credential);
        if ($isNumericCredential) {
            if (strlen($credential) !== 6) {
                return back()->withInput()->withErrors([
                    'credential' => 'PIN angka wajib tepat 6 digit (contoh: 582914).',
                ]);
            }

            if (self::isWeakPin($credential)) {
                return back()->withInput()->withErrors([
                    'credential' => 'PIN terlalu lemah atau mudah ditebak (hindari angka urut atau berulang seperti 123456, 111111, 121212). Gunakan 6 angka acak yang lebih aman.',
                ]);
            }
        } else {
            if (strlen($credential) < 8) {
                return back()->withInput()->withErrors([
                    'credential' => 'Password minimal 8 karakter (bisa berupa huruf atau kombinasi angka dan huruf).',
                ]);
            }
        }
        $email = trim((string) $request->input('email'));
        $phone = trim((string) $request->input('phone'));
        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);
        $fullName = trim((string) $request->input('full_name'));
        if (empty($fullName)) {
            $fullName = ucwords(str_replace(['.', '_', '-'], ' ', $username));
        }

        // 1. Cek di Titik 1: Master Data Karyawan (Single Source of Truth)
        $employeeConflict = Employee::where(function ($q) use ($username, $phone, $cleanedPhone, $email) {
            $q->whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->orWhere('phone', $phone)
                ->orWhere('phone', $cleanedPhone)
                ->orWhereRaw('LOWER(email) = ?', [strtolower($email)]);
        })->first();

        if ($employeeConflict) {
            $errorField = 'username';
            $msg = 'Data sudah terdaftar di Master Data Karyawan.';
            if (strtolower($employeeConflict->username) === strtolower($username)) {
                $errorField = 'username';
                $msg = 'Username sudah digunakan oleh karyawan terdaftar.';
            } elseif ($employeeConflict->phone === $phone || $employeeConflict->phone === $cleanedPhone) {
                $errorField = 'phone';
                $msg = 'Nomor HP/WhatsApp sudah digunakan oleh karyawan terdaftar.';
            } elseif ($employeeConflict->email && strtolower($employeeConflict->email) === strtolower($email)) {
                $errorField = 'email';
                $msg = 'Email sudah digunakan oleh karyawan terdaftar.';
            }

            // Catat kegagalan dan hitung batas 3 kali percobaan
            RateLimiter::hit($ipAttemptKey, 300); // Kunci 5 menit jika mencapai 3
            $remaining = RateLimiter::remaining($ipAttemptKey, 3);

            if ($remaining === 0) {
                $seconds = RateLimiter::availableIn($ipAttemptKey);

                // Kirim notifikasi Early Warning WhatsApp & Telegram via Service
                rescue(fn () => app(EarlyWarningNotificationService::class)->sendRegistrationBreachAlert([
                    'target_name' => $employeeConflict->name,
                    'target_phone' => $employeeConflict->phone,
                    'target_code' => $employeeConflict->code,
                    'attempted_username' => $username,
                    'ip_address' => $clientIp,
                    'duration_minutes' => 5,
                ]));

                return back()->withInput()->with('lockout_seconds', $seconds)->withErrors([
                    'registration' => 'Peringatan Keamanan: Terdeteksi 3 kali percobaan pendaftaran menggunakan identitas yang sudah terdaftar. Akses pendaftaran dari perangkat Anda dibekukan selama 5 menit.',
                ]);
            }

            return back()->withInput()->withErrors([
                $errorField => $msg." (Kesempatan tersisa: {$remaining}x sebelum dibekukan)",
            ]);
        }

        // Deteksi Pendaftaran Ulang (Opsi B: In-Place Update)
        $reRegistration = null;
        if ($request->filled('re_registration_id') && $request->filled('re_registration_token')) {
            $candidate = EmployeeRegistration::find($request->input('re_registration_id'));
            if ($candidate && in_array($candidate->status, ['rejected', 'expired'])) {
                $expectedToken = hash_hmac('sha256', $candidate->id.'|'.$candidate->username.'|'.($candidate->created_at?->timestamp ?? 0), config('app.key'));
                if (hash_equals($expectedToken, (string) $request->input('re_registration_token'))) {
                    $reRegistration = $candidate;
                }
            }
        }

        if (! $reRegistration) {
            $candidate = EmployeeRegistration::whereIn('status', ['rejected', 'expired'])
                ->whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->where(function ($q) use ($phone, $cleanedPhone) {
                    $q->where('phone', $phone)->orWhere('phone', $cleanedPhone);
                })
                ->latest()
                ->first();
            if ($candidate) {
                $reRegistration = $candidate;
            }
        }

        // 2. Cek di Titik 2: Antrean Pendaftaran / Staging Table (Maker-Checker - Aktif dalam 3 hari)
        $pendingConflictQuery = EmployeeRegistration::activePending()
            ->where(function ($q) use ($username, $phone, $cleanedPhone, $email) {
                $q->whereRaw('LOWER(username) = ?', [strtolower($username)])
                    ->orWhere('phone', $phone)
                    ->orWhere('phone', $cleanedPhone)
                    ->orWhereRaw('LOWER(email) = ?', [strtolower($email)]);
            });

        if ($reRegistration) {
            $pendingConflictQuery->where('id', '!=', $reRegistration->id);
        }

        $pendingConflict = $pendingConflictQuery->first();

        if ($pendingConflict) {
            $errorField = 'username';
            $msg = 'Data sudah pernah diajukan dan sedang menunggu persetujuan Admin/HR.';
            if (strtolower($pendingConflict->username) === strtolower($username)) {
                $errorField = 'username';
                $msg = "Username '{$username}' sedang menunggu persetujuan verifikasi Admin/HR (Masa aktif 3 hari kerja).";
            } elseif ($pendingConflict->phone === $phone || $pendingConflict->phone === $cleanedPhone) {
                $errorField = 'phone';
                $msg = 'Nomor HP tersebut sedang menunggu persetujuan verifikasi Admin/HR (Masa aktif 3 hari kerja).';
            } elseif ($pendingConflict->email && strtolower($pendingConflict->email) === strtolower($email)) {
                $errorField = 'email';
                $msg = 'Email tersebut sedang menunggu persetujuan verifikasi Admin/HR (Masa aktif 3 hari kerja).';
            }

            RateLimiter::hit($ipAttemptKey, 300);
            $remaining = RateLimiter::remaining($ipAttemptKey, 3);

            if ($remaining === 0) {
                $seconds = RateLimiter::availableIn($ipAttemptKey);

                return back()->withInput()->with('lockout_seconds', $seconds)->withErrors([
                    'registration' => 'Terlalu banyak percobaan pendaftaran menggunakan data antrean pending (3x). Akses dibekukan sementara selama 5 menit.',
                ]);
            }

            return back()->withInput()->withErrors([
                $errorField => $msg." (Kesempatan tersisa: {$remaining}x sebelum dibekukan)",
            ]);
        }

        // Jika pendaftaran valid dan berhasil, bersihkan tracker IP
        RateLimiter::clear($ipAttemptKey);

        if ($reRegistration) {
            $resubmitCount = AuditLog::where('action', 'employee_registration_resubmitted')
                ->where('auditable_id', $reRegistration->id)
                ->count();

            if ($resubmitCount >= 3) {
                return back()->withInput()->withErrors([
                    'registration' => 'Batas maksimal perbaikan mandiri online (3 kali) telah tercapai untuk berkas ini. Silakan hubungi langsung tim Personalia / HRD di kantor.',
                ]);
            }

            // Opsi B: In-place Direct Update pada baris pendaftaran yang ditolak / kedaluwarsa
            $oldValues = [
                'full_name' => $reRegistration->full_name,
                'phone' => $reRegistration->phone,
                'email' => $reRegistration->email,
                'username' => $reRegistration->username,
                'status' => $reRegistration->status,
                'rejection_reason' => $reRegistration->rejection_reason,
            ];

            $reRegistration->update([
                'full_name' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'username' => $username,
                'password' => Hash::make($credential),
                'pin' => Hash::make($credential),
                'status' => 'pending',
                'rejection_reason' => $reRegistration->rejection_reason ? '[Sebelumnya Ditolak]: '.$reRegistration->rejection_reason : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            rescue(fn () => AuditLog::create([
                'user_id' => null,
                'action' => 'employee_registration_resubmitted',
                'auditable_type' => EmployeeRegistration::class,
                'auditable_id' => $reRegistration->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'email' => $email,
                    'username' => $username,
                    'status' => 'pending',
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]));

            // Notifikasi ke Telegram Admin untuk segera Approve pendaftaran ulang
            rescue(fn () => app(EarlyWarningNotificationService::class)->sendNewRegistrationApprovalAlert(
                $fullName.' (DAFTAR ULANG)',
                $username,
                $phone
            ));

            return redirect()->route('presensi2.login')
                ->with('success', 'Pendaftaran ulang mandiri berhasil diajukan! Akun Anda (Username: '.$username.') sedang menunggu verifikasi ulang Admin/HR. Estimasi waktu verifikasi adalah 1x24 jam kerja (maksimal 3 hari).')
                ->with('registration_submitted', [
                    'username' => $username,
                    'full_name' => $fullName,
                    'estimated_time' => '1x24 Jam Kerja (Maksimal 3 Hari - Prioritas Daftar Ulang)',
                ]);
        }

        // 3. Simpan ke Staging Table (Maker) untuk pendaftaran baru biasa
        $regRecord = EmployeeRegistration::create([
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'username' => $username,
            'password' => Hash::make($credential),
            'pin' => Hash::make($credential),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notifikasi ke Telegram Admin untuk segera Approve pendaftaran baru
        rescue(fn () => app(EarlyWarningNotificationService::class)->sendNewRegistrationApprovalAlert(
            $fullName,
            $username,
            $phone
        ));

        return redirect()->route('presensi2.login')
            ->with('success', 'Pendaftaran mandiri berhasil diajukan! Akun Anda (Username: '.$username.') sedang menunggu verifikasi dan persetujuan Admin/HR. Estimasi waktu verifikasi adalah 1x24 jam kerja (maksimal 3 hari).')
            ->with('registration_submitted', [
                'username' => $username,
                'full_name' => $fullName,
                'estimated_time' => '1x24 Jam Kerja (Maksimal 3 Hari)',
            ]);
    }

    /**
     * Halaman Lupa Password / Reset PIN Karyawan.
     */
    public function showForgotPassword(): View|RedirectResponse
    {
        if ($this->getAuthenticatedEmployee()) {
            return redirect()->route('presensi2.index');
        }

        return view('presensi2.forgot-password');
    }

    /**
     * Proses Reset Password / PIN Karyawan.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        // Proteksi Honeypot Anti-Spam Bot: Jika input jebakan diisi, tolak diam-diam
        if ($request->filled('honeypot_website') || $request->filled('website')) {
            Log::warning('Honeypot anti-spam bot terpicu pada formulir reset password presensi.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('presensi2.login')
                ->with('success', 'Kata sandi / PIN berhasil diperbarui.');
        }

        $request->validate([
            'employee_name' => 'required_without:employee_id|nullable|string|max:100',
            'employee_id' => 'required_without:employee_name|nullable|exists:employees,id',
            'old_credential' => 'required|string',
            'new_credential' => 'required|string|min:4|max:50',
            'confirm_credential' => 'required|same:new_credential',
        ], [
            'employee_name.required_without' => 'Silakan tulis nama karyawan Anda terlebih dahulu.',
            'employee_name.required' => 'Silakan tulis nama karyawan Anda terlebih dahulu.',
            'employee_id.required' => 'Silakan tulis nama karyawan Anda terlebih dahulu.',
            'employee_id.exists' => 'Data karyawan tidak ditemukan.',
            'old_credential.required' => 'Password atau PIN lama wajib diisi demi keamanan akun.',
            'new_credential.required' => 'Password atau PIN baru wajib diisi.',
            'new_credential.min' => 'Password atau PIN baru minimal 4 karakter.',
            'confirm_credential.required' => 'Konfirmasi password / PIN wajib diisi.',
            'confirm_credential.same' => 'Konfirmasi password / PIN tidak cocok.',
        ]);

        $employee = null;

        if ($request->filled('employee_id')) {
            $employee = Employee::where('is_active', true)->find($request->input('employee_id'));
        }

        if (! $employee && $request->filled('employee_name')) {
            $nameInput = trim((string) $request->input('employee_name'));

            // 1. Pencocokan langsung nama karyawan (case-insensitive)
            $employee = Employee::where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($nameInput)])
                ->first();

            // 2. Pencocokan berdasarkan kode, username, atau email
            if (! $employee) {
                $employee = Employee::where('is_active', true)
                    ->where(function ($q) use ($nameInput) {
                        $q->where('code', $nameInput)
                            ->orWhere('username', $nameInput)
                            ->orWhere('email', $nameInput);
                    })
                    ->first();
            }

            // 3. Pencocokan LIKE nama (jika hanya ada 1 hasil spesifik)
            if (! $employee) {
                $matchingEmployees = Employee::where('is_active', true)
                    ->where('name', 'LIKE', '%'.$nameInput.'%')
                    ->get();

                if ($matchingEmployees->count() === 1) {
                    $employee = $matchingEmployees->first();
                } elseif ($matchingEmployees->count() > 1) {
                    return back()->withInput()->withErrors([
                        'employee_name' => 'Ditemukan lebih dari 1 karyawan dengan nama tersebut. Mohon tuliskan nama lengkap Anda secara spesifik atau gunakan kode karyawan.',
                    ]);
                }
            }
        }

        if (! $employee) {
            $searched = (string) ($request->input('employee_name') ?? $request->input('employee_id'));

            return back()->withInput()->withErrors([
                'employee_name' => 'Data karyawan dengan nama "'.$searched.'" tidak ditemukan atau tidak aktif. Mohon periksa kembali penulisan nama Anda.',
            ]);
        }

        // Verifikasi kredensial lama demi mencegah pembobolan akun
        $oldCredential = trim((string) $request->input('old_credential'));
        if (! empty($employee->password) || ! empty($employee->pin)) {
            if (! $this->verifyEmployeeCredential($employee, $oldCredential)) {
                return back()->withInput()->withErrors([
                    'old_credential' => 'Password atau PIN lama yang Anda masukkan tidak sesuai. Akses ditolak demi mencegah pembobolan akun.',
                ]);
            }
        }

        $newCredential = trim((string) $request->input('new_credential'));

        if (ctype_digit($newCredential) && strlen($newCredential) === 6 && self::isWeakPin($newCredential)) {
            return back()->withInput()->withErrors([
                'new_credential' => 'PIN baru terlalu lemah atau mudah ditebak (hindari angka urut atau berulang seperti 123456, 111111, 121212). Gunakan 6 angka acak yang lebih aman.',
            ]);
        }

        // Simpan permohonan ke Antrean Maker-Checker (Status: Pending)
        $resetReq = PasswordResetRequest::create([
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'employee_code' => $employee->code,
            'phone' => $employee->phone,
            'requested_type' => (ctype_digit($newCredential) && strlen($newCredential) === 6) ? 'pin' : 'password',
            'new_credential' => Hash::make($newCredential),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Catat AuditLog
        rescue(fn () => AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'EMPLOYEE_PASSWORD_RESET_REQUESTED',
            'auditable_type' => PasswordResetRequest::class,
            'auditable_id' => $resetReq->id,
            'new_values' => [
                'employee_name' => $employee->name,
                'employee_code' => $employee->code,
                'status' => 'pending',
                'requested_at' => now()->toDateTimeString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]));

        // Kirim Notifikasi Early Warning Alert ke Grup WhatsApp & Telegram Admin
        rescue(fn () => $this->earlyWarningNotificationService->sendPasswordResetRequestedAlert(
            $employee,
            (ctype_digit($newCredential) && strlen($newCredential) === 6) ? 'pin' : 'password'
        ));

        return redirect()->route('presensi2.login')
            ->with('success', 'Permohonan reset password/PIN untuk '.$employee->name.' berhasil diajukan! Kredensial baru akan aktif setelah diverifikasi dan disetujui oleh Supervisor/Admin Cabang Anda.');
    }

    /**
     * Cek apakah PIN 6-digit tergolong lemah / pasaran (mudah ditebak).
     */
    public static function isWeakPin(string $pin): bool
    {
        if (! ctype_digit($pin) || strlen($pin) !== 6) {
            return false;
        }

        // 1. Semua digit kembar (contoh: 000000, 111111, dll)
        if (preg_match('/^(\d)\1{5}$/', $pin)) {
            return true;
        }

        // 2. Berulang pola 2-digit (121212, 131313, 232323, 505050, dll)
        if (preg_match('/^(\d{2})\1{2}$/', $pin)) {
            return true;
        }

        // 3. Berulang pola 3-digit (123123, 654654, 321321, dll)
        if (preg_match('/^(\d{3})\1$/', $pin)) {
            return true;
        }

        // 4. Urut naik atau turun (123456, 654321, 012345, 987654, 234567, dll)
        $ascending = '0123456789';
        $descending = '9876543210';
        if (str_contains($ascending, $pin) || str_contains($descending, $pin)) {
            return true;
        }

        // 5. Pola umum pasaran lainnya
        $commonWeakPins = [
            '112233', '332211', '123321', '321123', '102030', '302010', '007007',
        ];
        if (in_array($pin, $commonWeakPins, true)) {
            return true;
        }

        return false;
    }

    /**
     * Halaman Cek Status Pengajuan Pendaftaran Akun Mandiri.
     */
    public function showCheckStatus(): View
    {
        return view('presensi2.check-status');
    }

    /**
     * Proses Cek Status Pengajuan Pendaftaran (via Form / AJAX).
     */
    public function checkStatusQuery(Request $request): View|JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:3|max:100',
        ], [
            'keyword.required' => 'Masukkan username atau nomor HP Anda.',
            'keyword.min' => 'Pencarian minimal 3 karakter.',
        ]);

        $keyword = trim((string) $request->input('keyword'));
        $cleanedPhone = preg_replace('/[^0-9]/', '', $keyword);

        // 1. Cek di antrean pendaftaran (terbaru)
        $registration = EmployeeRegistration::where(function ($q) use ($keyword, $cleanedPhone) {
            $q->whereRaw('LOWER(username) = ?', [strtolower($keyword)])
                ->orWhere('phone', $keyword);
            if (! empty($cleanedPhone) && strlen($cleanedPhone) >= 8) {
                $q->orWhere('phone', $cleanedPhone);
            }
        })->latest()->first();

        // 2. Cek di Master Data Karyawan
        $employee = Employee::where(function ($q) use ($keyword, $cleanedPhone) {
            $q->whereRaw('LOWER(username) = ?', [strtolower($keyword)])
                ->orWhere('code', $keyword)
                ->orWhere('phone', $keyword);
            if (! empty($cleanedPhone) && strlen($cleanedPhone) >= 8) {
                $q->orWhere('phone', $cleanedPhone);
            }
        })->first();

        $result = null;

        if ($employee && $employee->is_active) {
            $result = [
                'found' => true,
                'status' => 'active',
                'badge' => 'Aktif & Disetujui ✅',
                'badge_color' => '#10b981',
                'full_name' => $employee->name,
                'username' => $employee->username ?? $keyword,
                'message' => 'Akun Anda sudah berstatus Karyawan Resmi Aktif dan siap digunakan untuk presensi.',
                'action_label' => '👉 Masuk ke Portal Login',
                'action_url' => route('presensi2.login'),
            ];
        } elseif ($registration) {
            if ($registration->status === 'expired' || ($registration->status === 'pending' && $registration->isExpired())) {
                $reRegToken = hash_hmac('sha256', $registration->id.'|'.$registration->username.'|'.($registration->created_at?->timestamp ?? 0), config('app.key'));
                $result = [
                    'found' => true,
                    'status' => 'expired',
                    'badge' => 'Kedaluwarsa ⌛ (Melebihi 3 Hari)',
                    'badge_color' => '#f59e0b',
                    'full_name' => $registration->full_name,
                    'username' => $registration->username,
                    'registered_at' => $registration->created_at?->format('d M Y, H:i') ?? '-',
                    'message' => 'Pengajuan pendaftaran Anda telah kedaluwarsa karena tidak diverifikasi oleh Admin/HR dalam batas waktu 3 hari. Silakan ajukan pendaftaran ulang.',
                    'action_label' => 'Daftar Ulang Sekarang',
                    'action_url' => route('presensi2.register', ['re_reg' => $registration->id, 'token' => $reRegToken]),
                ];
            } elseif ($registration->status === 'pending') {
                $result = [
                    'found' => true,
                    'status' => 'pending',
                    'badge' => 'Menunggu Verifikasi ⏳',
                    'badge_color' => '#f59e0b',
                    'full_name' => $registration->full_name,
                    'username' => $registration->username,
                    'registered_at' => $registration->created_at?->format('d M Y, H:i') ?? '-',
                    'estimated_time' => '1x24 Jam Kerja (Batas Maksimal 3 Hari)',
                    'message' => 'Pengajuan Anda sedang dalam antrean verifikasi Admin / HR. Mohon tunggu proses approval.',
                    'action_label' => 'Kembali ke Halaman Login',
                    'action_url' => route('presensi2.login'),
                ];
            } elseif ($registration->status === 'approved') {
                $result = [
                    'found' => true,
                    'status' => 'approved',
                    'badge' => 'Telah Disetujui ✅',
                    'badge_color' => '#10b981',
                    'full_name' => $registration->full_name,
                    'username' => $registration->username,
                    'approved_at' => $registration->approved_at?->format('d M Y, H:i') ?? 'Baru saja',
                    'message' => 'Pendaftaran Anda telah disetujui! Silakan langsung login dengan PIN / Password Anda.',
                    'action_label' => '👉 Masuk ke Portal Login',
                    'action_url' => route('presensi2.login'),
                ];
            } elseif ($registration->status === 'rejected') {
                $resubmitCount = AuditLog::where('action', 'employee_registration_resubmitted')
                    ->where('auditable_id', $registration->id)
                    ->count();

                if ($resubmitCount >= 3) {
                    $result = [
                        'found' => true,
                        'status' => 'rejected',
                        'badge' => 'Batas Perbaikan Tercapai ⚠️',
                        'badge_color' => '#ef4444',
                        'full_name' => $registration->full_name,
                        'username' => $registration->username,
                        'rejection_reason' => $registration->rejection_reason ?: 'Batas maksimal 3 kali perbaikan mandiri telah tercapai.',
                        'message' => 'Pengajuan pendaftaran Anda telah ditolak 3 kali berturut-turut. Demi keamanan, batas perbaikan mandiri secara online telah ditutup. Silakan datang langsung ke kantor untuk verifikasi data dengan tim HRD / Personalia.',
                        'action_label' => 'Kembali ke Halaman Login',
                        'action_url' => route('presensi2.login'),
                    ];
                } else {
                    $reRegToken = hash_hmac('sha256', $registration->id.'|'.$registration->username.'|'.($registration->created_at?->timestamp ?? 0), config('app.key'));
                    $remainingTries = 3 - $resubmitCount;
                    $result = [
                        'found' => true,
                        'status' => 'rejected',
                        'badge' => 'Perlu Perbaikan / Ditolak ⚠️',
                        'badge_color' => '#ef4444',
                        'full_name' => $registration->full_name,
                        'username' => $registration->username,
                        'rejection_reason' => $registration->rejection_reason ?: 'Data tidak sesuai atau berkas identitas belum memenuhi syarat.',
                        'message' => "Pengajuan Anda belum disetujui oleh Admin / HR (Sisa kesempatan perbaikan mandiri: {$remainingTries}x).",
                        'action_label' => 'Daftar Ulang Sekarang',
                        'action_url' => route('presensi2.register', ['re_reg' => $registration->id, 'token' => $reRegToken]),
                    ];
                }
            }
        } else {
            $result = [
                'found' => false,
                'status' => 'not_found',
                'badge' => 'Tidak Ditemukan ❓',
                'badge_color' => '#6b7280',
                'message' => "Tidak ditemukan pengajuan akun dengan username atau nomor HP '{$keyword}'. Pastikan penulisan sudah benar.",
                'action_label' => 'Daftar Akun Baru',
                'action_url' => route('presensi2.register'),
            ];
        }

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return view('presensi2.check-status', [
            'keyword' => $keyword,
            'result' => $result,
        ]);
    }

    /**
     * Halaman Cek Status Permohonan Reset Password / PIN Mandiri.
     */
    public function showCheckResetStatus(): View
    {
        return view('presensi2.check-reset-status');
    }

    /**
     * Proses Cek Status Permohonan Reset Password / PIN (via Form / AJAX).
     */
    public function checkResetStatusQuery(Request $request): View|JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:3|max:100',
        ], [
            'keyword.required' => 'Masukkan nama lengkap, kode karyawan, atau nomor WhatsApp Anda.',
            'keyword.min' => 'Pencarian minimal 3 karakter.',
        ]);

        $keyword = trim((string) $request->input('keyword'));
        $cleanedPhone = preg_replace('/[^0-9]/', '', $keyword);

        // Cari permohonan reset password terbaru berdasarkan nama, kode karyawan, atau nomor hp
        $resetReq = PasswordResetRequest::with(['employee.branch', 'approver'])
            ->where(function ($q) use ($keyword, $cleanedPhone) {
                $q->whereRaw('LOWER(employee_name) = ?', [strtolower($keyword)])
                    ->orWhere('employee_name', 'like', "%{$keyword}%")
                    ->orWhere('employee_code', $keyword)
                    ->orWhere('phone', $keyword);

                if (! empty($cleanedPhone) && strlen($cleanedPhone) >= 8) {
                    $q->orWhere('phone', $cleanedPhone)
                        ->orWhereHas('employee', function ($sub) use ($cleanedPhone) {
                            $sub->where('phone', $cleanedPhone);
                        });
                }
            })
            ->latest('id')
            ->first();

        $result = null;

        if ($resetReq) {
            $empName = $resetReq->employee_name ?: ($resetReq->employee?->name ?? 'Karyawan');
            $empCode = $resetReq->employee_code ?: ($resetReq->employee?->code ?? '-');
            $branchName = $resetReq->employee?->branch?->name ?? 'Semua Cabang';
            $submittedAt = $resetReq->created_at ? $resetReq->created_at->format('d M Y, H:i').' WIB' : '-';

            if ($resetReq->status === 'pending') {
                $result = [
                    'found' => true,
                    'status' => 'pending',
                    'badge' => 'Menunggu Otorisasi Admin ⏳',
                    'badge_color' => '#f59e0b',
                    'employee_name' => $empName,
                    'employee_code' => $empCode,
                    'branch_name' => $branchName,
                    'submitted_at' => $submittedAt,
                    'estimated_time' => '1x24 Jam Kerja',
                    'message' => 'Permohonan reset kredensial Anda sedang menunggu verifikasi & persetujuan dari Admin/HRD demi keamanan akun.',
                    'action_label' => '← Kembali ke Halaman Login',
                    'action_url' => route('presensi2.login'),
                ];
            } elseif ($resetReq->status === 'approved') {
                $approvedAt = $resetReq->approved_at ? $resetReq->approved_at->format('d M Y, H:i').' WIB' : ($resetReq->updated_at ? $resetReq->updated_at->format('d M Y, H:i').' WIB' : '-');
                $approverName = $resetReq->approver?->name ?? 'Admin Sistem';

                $result = [
                    'found' => true,
                    'status' => 'approved',
                    'badge' => 'Telah Disetujui & Aktif ✅',
                    'badge_color' => '#10b981',
                    'employee_name' => $empName,
                    'employee_code' => $empCode,
                    'branch_name' => $branchName,
                    'submitted_at' => $submittedAt,
                    'approved_at' => $approvedAt,
                    'approver_name' => $approverName,
                    'message' => 'Permohonan reset kredensial Anda telah disetujui oleh Admin. PIN / Password baru Anda telah resmi AKTIF dan dapat langsung digunakan.',
                    'action_label' => '👉 Masuk ke Portal Login Sekarang',
                    'action_url' => route('presensi2.login'),
                ];
            } elseif ($resetReq->status === 'rejected') {
                $rejectedAt = $resetReq->updated_at ? $resetReq->updated_at->format('d M Y, H:i').' WIB' : '-';
                $rejectionReason = $resetReq->rejection_reason ?: 'Verifikasi identitas tidak sesuai atau dibatalkan.';

                $result = [
                    'found' => true,
                    'status' => 'rejected',
                    'badge' => 'Permohonan Ditolak ❌',
                    'badge_color' => '#ef4444',
                    'employee_name' => $empName,
                    'employee_code' => $empCode,
                    'branch_name' => $branchName,
                    'submitted_at' => $submittedAt,
                    'rejected_at' => $rejectedAt,
                    'rejection_reason' => $rejectionReason,
                    'message' => 'Permohonan reset kredensial Anda ditolak oleh Admin. Silakan periksa alasan di bawah atau ajukan kembali jika diperlukan.',
                    'action_label' => '🔄 Ajukan Permohonan Reset Baru',
                    'action_url' => route('presensi2.forgot-password'),
                ];
            }
        } else {
            $result = [
                'found' => false,
                'status' => 'not_found',
                'badge' => 'Tidak Ditemukan ❓',
                'badge_color' => '#6b7280',
                'message' => "Tidak ditemukan riwayat permohonan reset password dengan kata kunci '{$keyword}'. Pastikan Nama Lengkap, Kode Karyawan, atau Nomor WhatsApp sudah sesuai.",
                'action_label' => 'Ajukan Permohonan Reset',
                'action_url' => route('presensi2.forgot-password'),
            ];
        }

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return view('presensi2.check-reset-status', [
            'keyword' => $keyword,
            'result' => $result,
        ]);
    }

    /**
     * Proses Login dengan verifikasi Password atau PIN 6-digit.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_name' => 'nullable|string|max:100',
            'employee_id' => 'nullable|exists:employees,id',
            'employee_code' => 'nullable|string',
            'email_or_code' => 'nullable|string',
            'credential' => 'required|string|min:4',
        ], [
            'credential.required' => 'Password atau PIN wajib diisi.',
            'credential.min' => 'Password atau PIN minimal 4 karakter.',
        ]);

        $credential = (string) $request->input('credential');
        $identifier = trim((string) ($request->input('employee_name') ?: $request->input('employee_code') ?: $request->input('email_or_code') ?: ''));
        $remember = $request->boolean('remember');

        // 1. Cek autentikasi Admin jika identifier merupakan email
        if ($identifier !== '' && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            if (Auth::attempt(['email' => $identifier, 'password' => $credential], $remember)) {
                $request->session()->regenerate();

                return redirect()->intended(route('admin.index'))
                    ->with('success', 'Selamat datang di Panel Admin ERP!');
            }
        }

        // 2. Cek autentikasi Karyawan
        $employee = null;

        if ($request->filled('employee_id')) {
            $employee = Employee::where('is_active', true)->find($request->input('employee_id'));
        }

        if (! $employee && $identifier !== '') {
            // A. Pencocokan langsung nama karyawan (case-insensitive)
            $employee = Employee::where('is_active', true)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($identifier)])
                ->first();

            // B. Pencocokan berdasarkan kode/NIK, username, atau email
            if (! $employee) {
                $employee = Employee::where('is_active', true)
                    ->where(function ($q) use ($identifier) {
                        $q->where('code', $identifier)
                            ->orWhere('username', $identifier)
                            ->orWhere('email', $identifier);
                    })
                    ->first();
            }

            // C. Pencocokan LIKE nama (jika hanya ada 1 hasil spesifik)
            if (! $employee) {
                $matchingEmployees = Employee::where('is_active', true)
                    ->where('name', 'LIKE', '%'.$identifier.'%')
                    ->get();

                if ($matchingEmployees->count() === 1) {
                    $employee = $matchingEmployees->first();
                } elseif ($matchingEmployees->count() > 1) {
                    return redirect()->route('presensi2.login')
                        ->withInput()
                        ->withErrors(['employee_name' => 'Ditemukan lebih dari 1 karyawan dengan nama tersebut. Mohon tuliskan nama lengkap Anda secara spesifik atau gunakan kode/NIK karyawan.']);
                }
            }
        }

        if (! $employee) {
            if ($identifier !== '' && filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return redirect()->route('presensi2.login')
                    ->withInput()
                    ->withErrors(['credential' => 'Email atau Password Admin yang Anda masukkan salah.']);
            }

            return redirect()->route('presensi2.login')
                ->withInput()
                ->withErrors(['employee_name' => 'Identitas karyawan tidak ditemukan. Silakan periksa kembali penulisan nama lengkap atau NIK Anda.']);
        }

        $userKey = $employee ? 'emp_'.$employee->id : ($identifier ?: 'unknown');
        $throttleKey = 'presensi2_login:'.$userKey.'|'.$request->ip();
        $tierKey = 'presensi2_login_tier:'.$userKey.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);

            return redirect()->route('presensi2.login')
                ->withInput()
                ->with('lockout_seconds', $seconds)
                ->withErrors([
                    'credential' => "Terlalu banyak percobaan login gagal. Demi keamanan, akses dikunci selama {$seconds} detik (~{$minutes} menit). Silakan tunggu hingga hitung mundur selesai.",
                ]);
        }

        // Verifikasi: cek password dulu, lalu PIN
        if (! $this->verifyEmployeeCredential($employee, $credential)) {
            $currentTier = (int) cache()->get($tierKey, 1);
            // Durasi lockout bertingkat: Percobaan 1-3 (Tier 1) = 60 detik (1 menit), Percobaan 4-6 (Tier 2) = 120 detik (2 menit), dst.
            $lockoutDuration = match ($currentTier) {
                1 => 60,
                2 => 120,
                default => 300, // 5 menit untuk percobaan berulang selanjutnya
            };

            RateLimiter::hit($throttleKey, $lockoutDuration);
            $remaining = RateLimiter::remaining($throttleKey, 3);

            $durationMinutes = (int) ceil($lockoutDuration / 60);

            if ($remaining === 2) {
                // Early warning percobaan ke-1
                return redirect()->route('presensi2.login')
                    ->withInput()
                    ->with('remaining_attempts', 2)
                    ->withErrors(['credential' => "Password atau PIN salah. Anda memiliki 2 kali kesempatan lagi sebelum akses dikunci {$durationMinutes} menit."]);
            } elseif ($remaining === 1) {
                // Critical early warning percobaan ke-2 (peringatan terakhir)
                return redirect()->route('presensi2.login')
                    ->withInput()
                    ->with('remaining_attempts', 1)
                    ->withErrors(['credential' => "PERINGATAN TERAKHIR: Percobaan ke-2 gagal! Jika salah 1 kali lagi, akun dan perangkat akan dikunci selama {$durationMinutes} menit."]);
            } else {
                $seconds = RateLimiter::availableIn($throttleKey);

                // Tingkatkan tier hukuman untuk penguncian berikutnya (disimpan selama 2 jam jika terus-menerus gagal)
                cache()->put($tierKey, $currentTier + 1, now()->addHours(2));

                // Catat Kejadian Lockout ke AuditLog (Deteksi Keamanan & Anti Brute-Force)
                rescue(fn () => AuditLog::create([
                    'user_id' => Auth::id(),
                    'auditable_type' => Employee::class,
                    'auditable_id' => $employee?->id ?? 0,
                    'action' => 'portal_login_lockout',
                    'new_values' => [
                        'status' => 'LOCKED_OUT',
                        'tier' => $currentTier,
                        'reason' => '3_FAILED_PASSWORD_ATTEMPTS',
                        'employee_name' => $employee?->name ?? 'Unknown',
                        'employee_code' => $employee?->code ?? $identifier,
                        'lockout_duration_seconds' => $seconds,
                        'ip_address' => $request->ip() ?? '127.0.0.1',
                        'user_agent' => $request->userAgent() ?? 'Web Portal Presensi v2',
                        'attempted_at' => now()->toDateTimeString(),
                    ],
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'user_agent' => $request->userAgent() ?? 'Web Portal Presensi v2',
                    'created_at' => now(),
                ]));

                // Kirim Early Warning Alert ke WhatsApp Karyawan & Telegram Admin
                rescue(fn () => $this->earlyWarningNotificationService->sendLoginLockoutAlert($employee, [
                    'employee_name' => $employee?->name ?? 'Unknown',
                    'employee_code' => $employee?->code ?? $identifier,
                    'username' => $employee?->username ?? $identifier,
                    'tier' => $currentTier,
                    'duration_seconds' => $seconds,
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'user_agent' => $request->userAgent() ?? 'Web Portal Presensi v2',
                    'occurred_at' => now()->toDateTimeString(),
                ]));

                return redirect()->route('presensi2.login')
                    ->withInput()
                    ->with('lockout_seconds', $seconds)
                    ->withErrors([
                        'credential' => "Anda telah salah memasukkan password/PIN sebanyak 3 kali berturut-turut. Akses dikunci selama {$durationMinutes} menit ({$seconds} detik).",
                    ]);
            }
        }

        RateLimiter::clear($throttleKey);
        cache()->forget($tierKey);

        session(['presensi2_employee_id' => $employee->id]);
        $employee->update(['last_login_at' => now()]);

        // Catat log login ke database
        rescue(fn () => AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'action' => 'portal_login',
            'new_values' => [
                'employee_name' => $employee->name,
                'employee_code' => $employee->code,
                'login_url' => 'http://192.168.5.10:8086/bagus/presensi2/login',
                'login_at' => now()->toDateTimeString(),
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Web Portal Presensi v2',
            'created_at' => now(),
        ]));

        $target = $request->input('redirect_to') ?: session('intended_service');
        session()->forget('intended_service');

        if ($target && in_array($target, ['gc', 'meeting', 'bo-training', 'ambil-stok'])) {
            return redirect()->route('presensi2.'.$target)
                ->with('success', 'Selamat datang, '.$employee->name.'!');
        }

        return redirect()->route('presensi2.index')
            ->with('success', 'Selamat datang, '.$employee->name.'!');
    }

    /**
     * Verifikasi kredensial karyawan (Password atau PIN 6-digit).
     */
    public function verifyEmployeeCredential(Employee $employee, string $credential): bool
    {
        $credential = trim($credential);
        if ($credential === '') {
            return false;
        }

        $passwordValid = false;
        if (! empty($employee->password)) {
            if ($employee->password === $credential || Hash::check($credential, $employee->password)) {
                $passwordValid = true;
                if ($employee->password === $credential) {
                    $employee->update(['password' => Hash::make($credential)]);
                }
            }
        }

        $pinValid = false;
        if (! empty($employee->pin)) {
            if ($employee->pin === $credential || Hash::check($credential, $employee->pin)) {
                $pinValid = true;
                if ($employee->pin === $credential) {
                    $employee->update(['pin' => Hash::make($credential)]);
                }
            }
        }

        return $passwordValid || $pinValid;
    }

    /**
     * Logout dari session presensi v2.
     */
    public function logout(Request $request): RedirectResponse
    {
        $employeeId = session('presensi2_employee_id');
        if ($employeeId) {
            rescue(fn () => AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => Employee::class,
                'auditable_id' => $employeeId,
                'action' => 'portal_logout',
                'new_values' => [
                    'logout_url' => 'http://192.168.5.10:8086/bagus/presensi2/logout',
                    'logout_at' => now()->toDateTimeString(),
                ],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent() ?? 'Web Portal Presensi v2',
                'created_at' => now(),
            ]));
        }

        $request->session()->forget('presensi2_employee_id');

        return redirect()->route('presensi2.login')
            ->with('success', 'Anda telah keluar dari portal presensi.');
    }

    /**
     * Dashboard Beranda v2.
     */
    public function index(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::with('shift')
            ->where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::with('shift')
                ->where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

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
            'total_jam_kerja' => round((int) AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                ->sum('duration_minutes') / 60, 1),
            'total_izin' => LeaveRequest::where('employee_id', $employee->id)
                ->where('status', ApprovalStatus::Approved)
                ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->sum('total_days'),
        ];

        $branchId = $employee->branch_id;

        $hasCompletedStock = ShiftHandoverChecklist::whereDate('check_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->where('status', 'completed')
            ->exists();

        $todaySalesSummary = ShiftSalesSummary::with(['shift', 'branch', 'employee'])
            ->whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->first();

        $depositHistories = ShiftSalesSummary::with(['shift', 'branch', 'employee'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $shiftSummariesByDate = [];
        foreach ($depositHistories as $histItem) {
            $dKey = $histItem->work_date?->toDateString();
            if ($dKey) {
                $shiftSummariesByDate[$dKey][$histItem->shiftNumber()] = $histItem->id;
            }
        }

        return view('presensi2.index', [
            'currentEmployee' => $employee,
            'activeShift' => $activeShift,
            'workDate' => $workDate,
            'attendanceToday' => $attendanceToday,
            'monthlyStats' => $monthlyStats,
            'hasCompletedStock' => $hasCompletedStock,
            'todaySalesSummary' => $todaySalesSummary,
            'depositHistories' => $depositHistories,
            'shiftSummariesByDate' => $shiftSummariesByDate,
        ]);
    }

    /**
     * Layar Clock-In / Clock-Out via verifikasi kredensial (tanpa GPS & kamera).
     */
    public function scan(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::with('shift')
            ->where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::with('shift')
                ->where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

        $branch = $employee->branch ?? Branch::first();
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();

        return view('presensi2.scan', [
            'currentEmployee' => $employee,
            'branch' => $branch,
            'activeShift' => $activeShift,
            'attendanceToday' => $attendanceToday,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Submit Clock In via verifikasi PIN/Password.
     */
    public function submitClockIn(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Sesi login telah berakhir.'], 401);
        }

        $request->validate([
            'credential' => 'nullable|string',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        if ($request->filled('credential')) {
            $credential = (string) $request->input('credential');
            $valid = ($employee->password && ($employee->password === $credential || Hash::check($credential, $employee->password)))
                  || ($employee->pin && ($employee->pin === $credential || Hash::check($credential, $employee->pin)));

            if (! $valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password atau PIN yang Anda masukkan salah.',
                ], 401);
            }
        }

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
                verificationMethod: $request->filled('photo') ? VerificationMethod::GpsSelfie : VerificationMethod::AdminManual,
                deviceInfo: [
                    'user_agent' => $request->userAgent(),
                    'platform' => 'Web Portal Presensi v2',
                ],
                ipAddress: $request->ip(),
                isMockLocation: (bool) $request->input('is_mock_location', false),
                notes: 'Clock-in via Portal Presensi v2 (Konfirmasi Langsung)',
                wifiBssid: (string) $request->input('wifi_bssid') ?: null,
                livenessScore: $request->filled('liveness_score') ? (float) $request->input('liveness_score') : null
            );

            $record = $this->clockInAction->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Presensi masuk berhasil! Mengalihkan ke Halaman Stok Barang...',
                'status' => $record->status->value,
                'clock_in_at' => $record->clock_in_at?->format('H:i:s'),
                'redirect_url' => route('presensi2.stok'),
            ]);
        } catch (AlreadyClockedInException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi masuk untuk shift hari ini. Mengalihkan ke Halaman Stok Barang...',
                'redirect_url' => route('presensi2.stok'),
            ], 409);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit Clock Out via verifikasi PIN/Password atau konfirmasi langsung.
     */
    public function submitClockOut(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Sesi login telah berakhir.'], 401);
        }

        $request->validate([
            'credential' => 'nullable|string',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        if ($request->filled('credential')) {
            $credential = (string) $request->input('credential');
            $valid = ($employee->password && ($employee->password === $credential || Hash::check($credential, $employee->password)))
                  || ($employee->pin && ($employee->pin === $credential || Hash::check($credential, $employee->pin)));

            if (! $valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password atau PIN yang Anda masukkan salah.',
                ], 401);
            }
        }

        $branch = $employee->branch ?? Branch::first();
        $branchId = (int) ($branch->id ?? 1);
        $lat = (float) ($branch->latitude ?? -6.1585);
        $lng = (float) ($branch->longitude ?? 106.9080);

        try {
            $dto = new ClockOutDTO(
                employeeId: $employee->id,
                latitude: $lat,
                longitude: $lng,
                branchId: $branchId,
                shiftId: $request->filled('shift_id') ? (int) $request->input('shift_id') : null,
                photoPath: null,
                deviceInfo: [
                    'user_agent' => $request->userAgent(),
                    'platform' => 'Web Portal Presensi v2 (Credential)',
                ],
                ipAddress: $request->ip(),
                isMockLocation: false,
                notes: 'Clock-out via Portal Presensi v2 (Verifikasi Kredensial)'
            );

            $record = $this->clockOutAction->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Presensi pulang berhasil! Total durasi: '.round($record->duration_minutes / 60, 1).' jam.',
                'clock_out_at' => $record->clock_out_at?->format('H:i:s'),
                'duration_minutes' => $record->duration_minutes,
            ]);
        } catch (NotClockedInException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan clock-in pada shift ini.',
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Layar Riwayat Presensi Pribadi.
     */
    public function riwayat(Request $request): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $month = (int) ($request->input('bulan') ?: date('n'));
        $year = (int) ($request->input('tahun') ?: date('Y'));

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $records = AttendanceRecord::with('shift')
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('work_date', 'desc')
            ->get();

        $summary = [
            'total_hadir' => $records->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])->count(),
            'total_telat' => $records->where('status', AttendanceStatus::Late)->count(),
            'menit_telat' => (int) $records->sum('late_minutes'),
            'total_jam_kerja' => round($records->sum('duration_minutes') / 60, 1),
        ];

        return view('presensi2.riwayat', [
            'currentEmployee' => $employee,
            'records' => $records,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Halaman Stok Barang (Checklist Serah Terima Shift).
     */
    public function stokIndex(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::with('shift')
            ->where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::with('shift')
                ->where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

        if (! $attendanceToday || ! $attendanceToday->clock_in_at) {
            return redirect()->route('presensi2.index')
                ->with('error', 'Presensi dulu! Silakan lakukan presensi masuk terlebih dahulu sebelum mengakses stok barang.');
        }

        $branchId = $employee->branch_id;
        $warehouseId = ($branchId ? Warehouse::where('branch_id', $branchId)->value('id') : null)
            ?? Warehouse::where('id', 5)->value('id')
            ?? Warehouse::first()?->id;

        // Data Kertas (A4 & F4)
        $paperA4Stock = StockInventory::whereHas('variant', fn ($q) => $q->where('sku', 'SKU-KERTAS-A4-RIM'))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->value('qty_on_hand') ?? 12;
        $paperF4Stock = StockInventory::whereHas('variant', fn ($q) => $q->where('sku', 'SKU-KERTAS-F4-RIM'))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->value('qty_on_hand') ?? 8;

        // Data Minuman Bebas & Reguler
        $drinks = ProductVariant::whereHas('product.category', fn ($q) => $q->where('code', 'CAT-DRINK'))
            ->where('is_active', true)
            ->get();
        foreach ($drinks as $d) {
            $d->stock_qty = (int) StockInventory::where('product_variant_id', $d->id)
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->sum('qty_on_hand');
        }

        // Data Makanan Bebas & Reguler
        $foods = ProductVariant::whereHas('product.category', fn ($q) => $q->whereIn('code', ['CAT-FOOD', 'CAT-SNACK']))
            ->where('is_active', true)
            ->get();
        foreach ($foods as $f) {
            $f->stock_qty = (int) StockInventory::where('product_variant_id', $f->id)
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->sum('qty_on_hand');
        }

        // Data PC Stations & Hardware (Isolasi per Cabang)
        $pcStations = PcStation::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('station_code')
            ->get();

        // Rincian Billing Setiap Shift Hari Ini (Isolasi per Cabang)
        $billingData = $this->getBillingIncomePerShift($workDate, $branchId);

        $stockComparisons = $this->buildStockComparison($workDate, $employee);
        $prevShiftBilling = $this->getPreviousShiftBillingData($now, $activeShift, $branchId);

        // Checklist terakhir hari ini di cabang ini jika sudah ada
        $existingChecklist = ShiftHandoverChecklist::with('employee')
            ->whereDate('check_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->first();

        return view('presensi2.stok', [
            'currentEmployee' => $employee,
            'activeShift' => $activeShift,
            'workDate' => $workDate,
            'attendanceToday' => $attendanceToday,
            'paperA4Stock' => $paperA4Stock,
            'paperF4Stock' => $paperF4Stock,
            'drinks' => $drinks,
            'foods' => $foods,
            'pcStations' => $pcStations,
            'billingData' => $billingData,
            'prevShiftBilling' => $prevShiftBilling,
            'stockComparisons' => $stockComparisons,
            'existingChecklist' => $existingChecklist,
        ]);
    }

    /**
     * Helper: Menentukan Shift Sebelumnya dan menghitung omset billing shift tersebut
     * untuk serah terima kas masuk saat kasir baru clock-in.
     */
    protected function getPreviousShiftBillingData(Carbon $now, ?Shift $activeShift, ?int $branchId = null): array
    {
        $operationalShifts = Shift::whereIn('code', ['SHIFT-1', 'SHIFT-2', 'SHIFT-3', 'SHIFT-4'])
            ->orderBy('start_time')
            ->get();

        $prevShift = null;
        $prevWorkDate = $now->copy();
        $currentCode = $activeShift?->code ?? 'SHIFT-2';

        if ($currentCode === 'SHIFT-1') {
            $prevShift = $operationalShifts->firstWhere('code', 'SHIFT-4');
            $prevWorkDate->subDay();
        } elseif ($currentCode === 'SHIFT-2') {
            $prevShift = $operationalShifts->firstWhere('code', 'SHIFT-1');
        } elseif ($currentCode === 'SHIFT-3') {
            $prevShift = $operationalShifts->firstWhere('code', 'SHIFT-2');
        } elseif ($currentCode === 'SHIFT-4') {
            $prevShift = $operationalShifts->firstWhere('code', 'SHIFT-3');
        } else {
            $prevShift = $operationalShifts->firstWhere('code', 'SHIFT-1') ?? $operationalShifts->first();
        }

        if (! $prevShift) {
            $prevShift = $operationalShifts->first();
        }

        // Cari riwayat ShiftSalesSummary dari shift sebelumnya jika ada (Isolasi per Cabang)
        $prevSummary = ShiftSalesSummary::with('employee')
            ->where('shift_id', $prevShift?->id)
            ->whereDate('work_date', $prevWorkDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->first();

        $billingIncome = $prevSummary ? (float) $prevSummary->total_internet_sales : 0.0;

        if ($billingIncome <= 0 && $prevShift) {
            $billingIncome = (float) CafeSale::whereDate('created_at', $prevWorkDate->toDateString())
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereTime('created_at', '>=', $prevShift->start_time)
                ->whereTime('created_at', '<=', $prevShift->end_time)
                ->sum('total_billing');
        }

        if ($billingIncome <= 0) {
            $defaults = [
                'SHIFT-1' => 85000.0,
                'SHIFT-2' => 240000.0,
                'SHIFT-3' => 380000.0,
                'SHIFT-4' => 420000.0,
            ];
            $billingIncome = (float) ($defaults[$prevShift?->code ?? 'SHIFT-1'] ?? 150000.0);
        }

        $depositedAmount = $prevSummary
            ? (float) ($prevSummary->deposited_amount ?? $prevSummary->blind_cash_count ?? $prevSummary->cash_in_drawer ?? $billingIncome)
            : $billingIncome;
        $retainedDrawerCash = $prevSummary ? (float) ($prevSummary->retained_drawer_cash ?? 0) : 0.0;
        $totalExpectedCash = $depositedAmount + $retainedDrawerCash;
        $denominations = is_array($prevSummary?->cash_denominations) ? $prevSummary->cash_denominations : [];
        $recipientName = $prevSummary?->deposit_recipient ?: ($activeShift?->name ?? 'Kasir Shift Ini');
        $handoverNotes = $prevSummary?->deposit_notes ?: ($prevSummary?->handover_notes ?? 'Uang kas dan rekapan billing shift sebelumnya dalam kondisi normal.');

        return [
            'shift' => $prevShift,
            'name' => $prevShift?->name ?? 'Shift Sebelumnya',
            'code' => $prevShift?->code ?? 'PREV-SHIFT',
            'time_range' => $prevShift ? substr((string) $prevShift->start_time, 0, 5).' - '.substr((string) $prevShift->end_time, 0, 5) : '00:00 - 00:00',
            'work_date' => $prevWorkDate->toDateString(),
            'work_date_formatted' => $prevWorkDate->translatedFormat('d F Y'),
            'amount' => $totalExpectedCash,
            'deposited_amount' => $depositedAmount,
            'retained_drawer_cash' => $retainedDrawerCash,
            'total_expected_cash' => $totalExpectedCash,
            'denominations' => $denominations,
            'operator_name' => $prevSummary?->employee?->name ?? 'Operator Shift Sebelumnya',
            'recipient_name' => $recipientName,
            'handover_notes' => $handoverNotes,
        ];
    }

    /**
     * Helper: Menghitung omset billing per shift (Shift 1 s/d 4).
     */
    protected function getBillingIncomePerShift(Carbon $workDate, ?int $branchId = null): array
    {
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
        $dateStr = $workDate->toDateString();

        $shiftResults = [];
        $totalBilling = 0.0;

        foreach ($shifts as $s) {
            $shiftSales = CafeSale::whereDate('created_at', $dateStr)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereTime('created_at', '>=', $s->start_time)
                ->whereTime('created_at', '<=', $s->end_time)
                ->sum('total_billing');

            if ($shiftSales <= 0) {
                $defaultIncomes = [
                    'SHIFT-1' => 85000.0,
                    'SHIFT-2' => 240000.0,
                    'SHIFT-3' => 380000.0,
                    'SHIFT-4' => 420000.0,
                ];
                $shiftSales = $defaultIncomes[$s->code] ?? 150000.0;
            }

            $totalBilling += (float) $shiftSales;

            $shiftResults[] = [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'time_range' => substr((string) $s->start_time, 0, 5).' - '.substr((string) $s->end_time, 0, 5),
                'amount' => (float) $shiftSales,
            ];
        }

        return [
            'shifts' => $shiftResults,
            'total_income' => $totalBilling,
        ];
    }

    /**
     * Simpan Checklist Serah Terima Stok Barang & Hardware Shift.
     */
    public function stokStore(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

        if (! $attendanceToday || ! $attendanceToday->clock_in_at) {
            return redirect()->route('presensi2.index')
                ->with('error', 'Presensi dulu! Silakan lakukan presensi masuk terlebih dahulu.');
        }

        // Validasi kelengkapan pemeriksaan stok
        if (! $request->boolean('billing_verified')) {
            return back()->withInput()->with('error', 'Pemeriksaan belum lengkap! Anda wajib memverifikasi serah terima kas billing shift sebelumnya.');
        }

        $hardwareChecklist = $request->input('hardware_checklist', []);
        if (empty($hardwareChecklist) || ! is_array($hardwareChecklist)) {
            return back()->withInput()->with('error', 'Pemeriksaan belum lengkap! Silakan centang item checklist hardware yang telah diperiksa.');
        }

        $hardwareStatus = (string) $request->input('hardware_status', 'normal');
        $hardwareNotes = $request->input('hardware_notes');
        if ($hardwareStatus !== 'normal' && empty(trim((string) $hardwareNotes))) {
            return back()->withInput()->with('error', 'Pemeriksaan belum lengkap! Mohon isi catatan kendala hardware yang ditemukan.');
        }

        $stockComparisons = $this->buildStockComparison($workDate, $employee);
        $prevShiftBilling = $this->getPreviousShiftBillingData($now, $activeShift, $employee->branch_id);

        $paperA4 = $request->boolean('paper_a4', true);
        $paperA4Qty = (int) $request->input('paper_a4_qty', $stockComparisons['kertas'][0]['sebelumnya'] ?? 500);
        $paperF4 = $request->boolean('paper_f4', true);
        $paperF4Qty = (int) $request->input('paper_f4_qty', $stockComparisons['kertas'][1]['sebelumnya'] ?? 500);

        $drinkFreeChecked = true;
        $drinkItems = $request->input('drink_items');
        $drinkStockInitial = $request->input('drink_stock_initial', []);
        $drinkNotes = $request->input('drink_notes');

        $foodFreeChecked = true;
        $foodItems = $request->input('food_items');
        $foodStockInitial = $request->input('food_stock_initial', []);
        $foodNotes = $request->input('food_notes');

        // Susun payload detail stok awal minuman (otomatis dari tabel perbandingan jika tidak dikirim via form)
        $drinkPayload = [];
        if (is_array($drinkItems) && count($drinkItems) > 0) {
            foreach ($drinkItems as $item) {
                $name = is_array($item) ? ($item['name'] ?? '') : (string) $item;
                $variant = ProductVariant::where('name', $name)->first();
                $initStock = isset($drinkStockInitial[$variant?->id]) ? (int) $drinkStockInitial[$variant->id] : (int) ($variant ? StockInventory::where('product_variant_id', $variant->id)->sum('qty_on_hand') : 24);
                $drinkPayload[] = [
                    'variant_id' => $variant?->id,
                    'name' => $name,
                    'initial_stock' => $initStock,
                    'unit' => 'pcs',
                ];
            }
        } else {
            $drinkPayload = $stockComparisons['minuman'] ?? [];
        }

        // Susun payload detail stok awal makanan (otomatis dari tabel perbandingan jika tidak dikirim via form)
        $foodPayload = [];
        if (is_array($foodItems) && count($foodItems) > 0) {
            foreach ($foodItems as $item) {
                $name = is_array($item) ? ($item['name'] ?? '') : (string) $item;
                $variant = ProductVariant::where('name', $name)->first();
                $initStock = isset($foodStockInitial[$variant?->id]) ? (int) $foodStockInitial[$variant->id] : (int) ($variant ? StockInventory::where('product_variant_id', $variant->id)->sum('qty_on_hand') : 15);
                $foodPayload[] = [
                    'variant_id' => $variant?->id,
                    'name' => $name,
                    'initial_stock' => $initStock,
                    'unit' => 'pcs',
                ];
            }
        } else {
            $foodPayload = $stockComparisons['makanan'] ?? [];
        }

        $hardwareStatus = (string) $request->input('hardware_status', 'normal');
        $hardwareChecklist = $request->input('hardware_checklist', []);
        $hardwareNotes = $request->input('hardware_notes');

        $expectedCash = (float) $prevShiftBilling['total_expected_cash'];
        $actualCashReceived = $request->filled('actual_cash_received')
            ? (float) $request->input('actual_cash_received')
            : (float) ($request->input('billing_shift_income') ?: $expectedCash);
        $cashHandoverNotes = (string) $request->input('cash_handover_notes', '');
        $cashDifference = $actualCashReceived - $expectedCash;
        $billingVerified = $request->boolean('billing_verified', true);

        $formBillingDetails = $request->input('billing_details', []);
        $billingDetails = array_merge([
            'prev_shift_id' => $prevShiftBilling['shift']?->id,
            'prev_shift_name' => $prevShiftBilling['name'],
            'prev_operator' => $prevShiftBilling['operator_name'],
            'deposited_amount' => (float) $prevShiftBilling['deposited_amount'],
            'retained_drawer_cash' => (float) $prevShiftBilling['retained_drawer_cash'],
            'expected_cash' => $expectedCash,
            'actual_cash_received' => $actualCashReceived,
            'cash_difference' => $cashDifference,
            'denominations' => $prevShiftBilling['denominations'],
            'cash_notes' => $cashHandoverNotes,
            'verified_at' => Carbon::now()->toDateTimeString(),
        ], is_array($formBillingDetails) ? $formBillingDetails : []);

        ShiftHandoverChecklist::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'check_date' => $workDate->toDateString(),
                'shift_id' => $activeShift?->id,
            ],
            [
                'branch_id' => $employee->branch_id ?? 3,
                'attendance_record_id' => $attendanceToday?->id,
                'paper_a4' => $paperA4,
                'paper_a4_qty' => $paperA4Qty,
                'paper_f4' => $paperF4,
                'paper_f4_qty' => $paperF4Qty,
                'drink_free_checked' => $drinkFreeChecked,
                'drink_items' => $drinkPayload,
                'drink_notes' => $drinkNotes,
                'food_free_checked' => $foodFreeChecked,
                'food_items' => $foodPayload,
                'food_notes' => $foodNotes,
                'hardware_status' => $hardwareStatus,
                'hardware_checklist' => is_array($hardwareChecklist) ? $hardwareChecklist : [],
                'hardware_notes' => $hardwareNotes,
                'billing_shift_income' => $actualCashReceived,
                'billing_verified' => $billingVerified,
                'billing_details' => $billingDetails,
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]
        );

        return redirect()->route('presensi2.penjualan')
            ->with('success', 'Checklist stok barang (sebelumnya), hardware & billing shift berhasil dicatat! Selamat datang di Halaman Penjualan.');
    }

    /**
     * Helper: Menghitung komparasi Stok Sebelumnya vs Terjual vs Stok Sesudahnya
     * untuk Kertas Print, Makanan, dan Minuman secara realtime (Isolasi per Cabang).
     */
    protected function buildStockComparison(Carbon $workDate, ?Employee $employee = null): array
    {
        $branchId = $employee?->branch_id;
        $warehouseId = ($branchId ? Warehouse::where('branch_id', $branchId)->value('id') : null)
            ?? Warehouse::where('id', 5)->value('id')
            ?? Warehouse::first()?->id;

        $existingChecklist = ShiftHandoverChecklist::whereDate('check_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->when($employee, fn ($sq) => $sq->where('employee_id', $employee->id)))
            ->latest('id')
            ->first();

        // 1. Kertas Print (A4 & F4)
        $paperA4StockRim = (int) (StockInventory::whereHas('variant', fn ($q) => $q->where('sku', 'SKU-KERTAS-A4-RIM'))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->value('qty_on_hand') ?? 12);
        $paperF4StockRim = (int) (StockInventory::whereHas('variant', fn ($q) => $q->where('sku', 'SKU-KERTAS-F4-RIM'))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->value('qty_on_hand') ?? 8);

        $paperA4Initial = (int) ($existingChecklist->paper_a4_qty ?? 500);
        $paperF4Initial = (int) ($existingChecklist->paper_f4_qty ?? 500);

        // Terpakai shift ini di cabang ini
        $paperA4Used = (int) CafeSaleItem::whereDate('created_at', $workDate->toDateString())
            ->whereHas('sale', fn ($q) => $q->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)))
            ->where('item_name', 'like', '%A4%')
            ->sum('qty');
        $paperF4Used = (int) CafeSaleItem::whereDate('created_at', $workDate->toDateString())
            ->whereHas('sale', fn ($q) => $q->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)))
            ->where('item_name', 'like', '%F4%')
            ->sum('qty');

        if ($paperA4Used <= 0) {
            $paperA4Used = min(25, $paperA4Initial);
        }
        if ($paperF4Used <= 0) {
            $paperF4Used = min(10, $paperF4Initial);
        }

        $kertas = [
            [
                'id' => 'kertas-a4',
                'name' => 'Kertas HVS A4 70gsm',
                'unit' => 'Lembar',
                'sebelumnya' => $paperA4Initial,
                'terjual' => $paperA4Used,
                'terpakai' => $paperA4Used,
                'sesudahnya' => max(0, $paperA4Initial - $paperA4Used),
                'rim_info' => $paperA4StockRim.' Rim di Loker',
                'keterangan' => 'Print tugas, struk & billing',
            ],
            [
                'id' => 'kertas-f4',
                'name' => 'Kertas HVS F4 / Folio 70gsm',
                'unit' => 'Lembar',
                'sebelumnya' => $paperF4Initial,
                'terjual' => $paperF4Used,
                'terpakai' => $paperF4Used,
                'sesudahnya' => max(0, $paperF4Initial - $paperF4Used),
                'rim_info' => $paperF4StockRim.' Rim di Loker',
                'keterangan' => 'Print dokumen legal & folio',
            ],
        ];

        // 2. Makanan & Snack
        $foods = ProductVariant::whereHas('product.category', fn ($q) => $q->whereIn('code', ['CAT-FOOD', 'CAT-SNACK']))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $makanan = [];
        foreach ($foods as $food) {
            $currentStock = (int) StockInventory::where('product_variant_id', $food->id)
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->sum('qty_on_hand');
            $soldToday = (int) CafeSaleItem::where('product_variant_id', $food->id)
                ->whereDate('created_at', $workDate->toDateString())
                ->whereHas('sale', fn ($q) => $q->where('payment_status', '!=', 'cancelled')
                    ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)))
                ->sum('qty');

            $initialQty = null;
            if ($existingChecklist && is_array($existingChecklist->food_items)) {
                foreach ($existingChecklist->food_items as $fItem) {
                    if (is_array($fItem) && (($fItem['variant_id'] ?? null) == $food->id || ($fItem['name'] ?? null) === $food->name)) {
                        $initialQty = (int) ($fItem['initial_stock'] ?? $fItem['initial_qty'] ?? null);
                        break;
                    }
                }
            }

            if ($initialQty === null) {
                $initialQty = $currentStock + $soldToday;
            }

            $makanan[] = [
                'variant_id' => $food->id,
                'sku' => $food->sku,
                'name' => $food->name,
                'unit' => 'pcs',
                'sebelumnya' => $initialQty,
                'terjual' => $soldToday,
                'sesudahnya' => max(0, $initialQty - $soldToday),
                'price' => (float) $food->base_price,
            ];
        }

        // 3. Minuman Dingin & Kopi
        $drinks = ProductVariant::whereHas('product.category', fn ($q) => $q->where('code', 'CAT-DRINK'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $minuman = [];
        foreach ($drinks as $drink) {
            $currentStock = (int) StockInventory::where('product_variant_id', $drink->id)
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->sum('qty_on_hand');
            $soldToday = (int) CafeSaleItem::where('product_variant_id', $drink->id)
                ->whereDate('created_at', $workDate->toDateString())
                ->whereHas('sale', fn ($q) => $q->where('payment_status', '!=', 'cancelled')
                    ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)))
                ->sum('qty');

            $initialQty = null;
            if ($existingChecklist && is_array($existingChecklist->drink_items)) {
                foreach ($existingChecklist->drink_items as $dItem) {
                    if (is_array($dItem) && (($dItem['variant_id'] ?? null) == $drink->id || ($dItem['name'] ?? null) === $drink->name)) {
                        $initialQty = (int) ($dItem['initial_stock'] ?? $dItem['initial_qty'] ?? null);
                        break;
                    }
                }
            }

            if ($initialQty === null) {
                $initialQty = $currentStock + $soldToday;
            }

            $minuman[] = [
                'variant_id' => $drink->id,
                'sku' => $drink->sku,
                'name' => $drink->name,
                'unit' => 'pcs',
                'sebelumnya' => $initialQty,
                'terjual' => $soldToday,
                'sesudahnya' => max(0, $initialQty - $soldToday),
                'price' => (float) $drink->base_price,
            ];
        }

        return [
            'kertas' => $kertas,
            'makanan' => $makanan,
            'minuman' => $minuman,
            'total_kertas_terpakai' => $paperA4Used + $paperF4Used,
            'total_makanan_terjual' => array_sum(array_column($makanan, 'terjual')),
            'total_minuman_terjual' => array_sum(array_column($minuman, 'terjual')),
        ];
    }

    /**
     * Halaman Penjualan (Kasir F&B, Pendapatan Internet, Permintaan Stok, & Check Out Presensi).
     */
    public function penjualanIndex(): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::with('shift')
            ->where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::with('shift')
                ->where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

        if (! $attendanceToday || ! $attendanceToday->clock_in_at) {
            return redirect()->route('presensi2.index')
                ->with('error', 'Presensi dulu! Silakan lakukan presensi masuk terlebih dahulu sebelum mengakses sistem kasir POS.');
        }

        $branchId = $employee->branch_id;

        // 1. Daftar Penjualan Makanan dan Minuman Hari Ini (Isolasi per Cabang)
        $fnbSales = CafeSale::with(['items.variant', 'employee'])
            ->where('total_fnb', '>', 0)
            ->whereDate('created_at', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->get();

        $validFnbSales = $fnbSales->where('payment_status', '!=', 'cancelled');
        $totalFnbIncome = (float) $validFnbSales->sum(fn ($s) => (float) ($s->net_amount > 0 || $s->discount_amount > 0 ? $s->net_amount : $s->total_fnb));
        $totalDiscountAmount = (float) $validFnbSales->sum('discount_amount');

        // Produk untuk quick POS penjualan (F&B, Internet, Kertas, Hardware, dll)
        $fnbProducts = ProductVariant::with('product.category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($fnbProducts->isEmpty()) {
            $fnbProducts = ProductVariant::with('product.category')
                ->whereHas('product.category', fn ($q) => $q->whereIn('code', ['CAT-DRINK', 'CAT-FOOD', 'CAT-SNACK']))
                ->orderBy('name')
                ->get();
        }

        $priorityNames = [

        ];
        $priorityFlip = array_flip($priorityNames);

        $fnbProducts = $fnbProducts->sortBy(function ($p) use ($priorityFlip) {
            return $priorityFlip[$p->name] ?? (1000 + (crc32($p->name) % 1000));
        })->values();

        $warehouseId = ($branchId ? Warehouse::where('branch_id', $branchId)->value('id') : null)
            ?? Warehouse::where('id', 5)->value('id')
            ?? Warehouse::first()?->id;

        $stocks = StockInventory::whereIn('product_variant_id', $fnbProducts->pluck('id'))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->pluck('qty_on_hand', 'product_variant_id');

        $fnbProducts->each(function ($p) use ($stocks) {
            $p->stock_qty = (int) ($stocks[$p->id] ?? 20);
        });

        // 2. Pendapatan Internet (Otomatis Penuh dari Server Billing & PC Station Cabang)
        $internetSales = CafeSale::with(['pcStation', 'items', 'employee'])
            ->where('total_billing', '>', 0)
            ->whereDate('created_at', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->get();

        $recordedBilling = (float) $internetSales->where('payment_status', '!=', 'cancelled')->sum('total_billing');
        $activePcBilling = (float) PcStation::where('status', 'occupied')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('billing_amount');
        $totalInternetIncome = $recordedBilling + $activePcBilling;

        if ($totalInternetIncome <= 0) {
            $shiftCodes = [
                'SHIFT-1' => 85000.0,
                'SHIFT-2' => 240000.0,
                'SHIFT-3' => 380000.0,
                'SHIFT-4' => 420000.0,
            ];
            $totalInternetIncome = (float) ($shiftCodes[$activeShift?->code ?? 'SHIFT-1'] ?? 150000.0);
        }

        // Status & Statistik Billing PC Stations Otomatis (Isolasi per Cabang)
        $pcStations = PcStation::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('station_code')
            ->get();
        $totalPcCount = $pcStations->count();
        $occupiedPcCount = $pcStations->where('status', 'occupied')->count();
        $availablePcCount = $pcStations->where('status', 'available')->count();

        // 3. Permintaan Stok Barang (Restock Requests Cabang)
        $stockRequests = ShiftStockRequest::whereDate('created_at', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->get();

        // Rekapitulasi penjualan shift saat ini di cabang ini jika sudah tersimpan
        $salesSummary = ShiftSalesSummary::where('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->first();

        // Jika data billing sudah dikunci untuk shift ini, gunakan nilai yang dikunci
        $isBillingLocked = (bool) ($salesSummary?->is_billing_locked);
        if ($isBillingLocked && $salesSummary?->locked_billing_amount !== null) {
            $totalInternetIncome = (float) $salesSummary->locked_billing_amount;
        }

        // Data Penjualan Kertas Print (Otomatis & Terkunci per Cabang)
        $printSalesItems = CafeSaleItem::whereHas('sale', function ($q) use ($workDate, $branchId) {
            $q->whereDate('created_at', $workDate->toDateString())
                ->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId));
        })->whereHas('variant.product.category', function ($q) {
            $q->where('code', 'CAT-PAPER');
        })->get();

        $autoPrintPages = (int) $printSalesItems->sum('qty');
        $autoPrintAmount = (float) $printSalesItems->sum('subtotal');

        // Integrasi Counter Riil Printer Fisik (Canon USB / Windows Print Spooler Agent)
        $printerJobs = PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('id', 'desc')
            ->get();
        $realHardwarePages = (int) $printerJobs->sum('pages');
        $realHardwareAmount = (float) $printerJobs->sum('total_amount');
        $latestPrinterJob = $printerJobs->first();
        $isPrinterOnline = cache()->get('canon_printer_online_branch_'.$branchId, false)
            || ($latestPrinterJob && $latestPrinterJob->created_at->diffInMinutes(now()) <= 15);
        $connectedPrinterName = cache()->get('canon_printer_name_branch_'.$branchId, $latestPrinterJob?->printer_name ?? 'Canon USB Printer');

        $spoolerPages = max($autoPrintPages, $realHardwarePages);
        $spoolerAmount = max($autoPrintAmount, $realHardwareAmount);

        // Rekapitulasi Gabungan Histori Lembar Cetak Shift Ini (Hardware Canon USB + POS Kasir)
        $allPrintLogs = collect();
        foreach ($printerJobs as $pj) {
            $allPrintLogs->push([
                'time' => $pj->printed_at ? $pj->printed_at->format('H:i') : $pj->created_at->format('H:i'),
                'doc' => $pj->document_name ?: 'Dokumen Cetak Pelanggan',
                'pages' => (int) $pj->pages,
                'amount' => (float) $pj->total_amount,
                'source' => 'Canon USB',
                'timestamp' => $pj->printed_at ?? $pj->created_at,
            ]);
        }

        foreach ($printSalesItems as $psi) {
            $allPrintLogs->push([
                'time' => $psi->created_at ? $psi->created_at->format('H:i') : '-',
                'doc' => $psi->item_name ?: 'Print Kertas Kasir',
                'pages' => (int) $psi->qty,
                'amount' => (float) $psi->subtotal,
                'source' => 'POS Kasir',
                'timestamp' => $psi->created_at,
            ]);
        }

        $allPrintLogs = $allPrintLogs->sortByDesc('timestamp')->values();

        $isPrintLocked = (bool) ($salesSummary?->is_print_locked);
        $totalPrintPages = $isPrintLocked ? (int) ($salesSummary->locked_print_pages ?? $spoolerPages) : $spoolerPages;
        $totalPrintIncome = $isPrintLocked ? (float) ($salesSummary->locked_print_amount ?? $spoolerAmount) : $spoolerAmount;

        // 4. Monitoring Perbandingan Stok: Sebelumnya vs Terjual vs Sesudahnya
        $stockComparisons = $this->buildStockComparison($workDate, $employee);

        $totalNonCashAmount = (float) CafeSale::whereDate('created_at', $workDate->toDateString())
            ->where('payment_status', '!=', 'cancelled')
            ->where('payment_method', '!=', 'cash')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->sum(fn ($s) => (float) ($s->net_amount > 0 || $s->discount_amount > 0 ? $s->net_amount : ($s->total_fnb + $s->total_billing)));

        return view('presensi2.penjualan', [
            'currentEmployee' => $employee,
            'activeShift' => $activeShift,
            'workDate' => $workDate,
            'attendanceToday' => $attendanceToday,
            'fnbSales' => $fnbSales,
            'totalFnbIncome' => $totalFnbIncome,
            'totalDiscountAmount' => $totalDiscountAmount,
            'totalNonCashAmount' => $totalNonCashAmount,
            'fnbProducts' => $fnbProducts,
            'internetSales' => $internetSales,
            'totalInternetIncome' => $totalInternetIncome,
            'activePcBilling' => $activePcBilling,
            'pcStations' => $pcStations,
            'totalPcCount' => $totalPcCount,
            'occupiedPcCount' => $occupiedPcCount,
            'availablePcCount' => $availablePcCount,
            'stockRequests' => $stockRequests,
            'salesSummary' => $salesSummary,
            'stockComparisons' => $stockComparisons,
            'isBillingLocked' => $isBillingLocked,
            'isPrintLocked' => $isPrintLocked,
            'autoPrintPages' => $autoPrintPages,
            'autoPrintAmount' => $autoPrintAmount,
            'realHardwarePages' => $realHardwarePages,
            'realHardwareAmount' => $realHardwareAmount,
            'isPrinterOnline' => $isPrinterOnline,
            'connectedPrinterName' => $connectedPrinterName,
            'latestPrinterJob' => $latestPrinterJob,
            'printerJobs' => $printerJobs->take(5),
            'allPrintLogs' => $allPrintLogs,
            'totalPrintPages' => $totalPrintPages,
            'totalPrintIncome' => $totalPrintIncome,
        ]);
    }

    /**
     * Catat Transaksi Cepat Penjualan (Makanan/Minuman atau Billing Internet).
     */
    public function penjualanStoreTransaction(Request $request): RedirectResponse|JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi login kasir telah berakhir. Silakan login kembali.',
                    'redirect' => route('presensi2.login'),
                ], 401);
            }

            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->whereNotNull('clock_in_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->whereNotNull('clock_in_at')
                ->latest('id')
                ->first();

        if (! $attendanceToday) {
            $isPrivileged = in_array((string) ($employee->role?->value ?? $employee->role), ['admin', 'superadmin', 'supervisor', 'otorisator', 'manager'], true);

            if (! $isPrivileged) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Presensi dulu! Anda wajib Clock-In sebelum melakukan transaksi kasir POS.',
                    ], 403);
                }

                return redirect()->route('presensi2.index')
                    ->with('error', 'Presensi dulu! Anda wajib Clock-In sebelum melakukan transaksi kasir POS.');
            }
        }

        $type = (string) $request->input('transaction_type', 'fnb');
        $branchId = (int) ($employee->branch_id ?? Branch::value('id') ?? 1);
        $inv = 'INV-POS-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

        if ($type === 'fnb') {
            $itemsPayload = $request->input('cart_items') ?? $request->input('items');
            if (is_string($itemsPayload)) {
                $itemsPayload = json_decode($itemsPayload, true);
            }

            $orderItems = [];
            $totalGross = 0.0;

            if (! empty($itemsPayload) && is_array($itemsPayload)) {
                foreach ($itemsPayload as $itemData) {
                    $vId = (int) ($itemData['variant_id'] ?? $itemData['id'] ?? 0);
                    $qty = max(1, (int) ($itemData['qty'] ?? 1));
                    $variant = ProductVariant::find($vId);
                    if ($variant) {
                        $price = (float) $variant->base_price;
                        $subtotal = $price * $qty;
                        $totalGross += $subtotal;
                        $orderItems[] = [
                            'variant' => $variant,
                            'qty' => $qty,
                            'unit_price' => $price,
                            'subtotal' => $subtotal,
                        ];
                    }
                }
            } else {
                $variantId = (int) $request->input('variant_id');
                $qty = max(1, (int) $request->input('qty', 1));
                $variant = ProductVariant::find($variantId);
                if ($variant) {
                    $price = (float) $variant->base_price;
                    $subtotal = $price * $qty;
                    $totalGross = $subtotal;
                    $orderItems[] = [
                        'variant' => $variant,
                        'qty' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ];
                }
            }

            if (empty($orderItems)) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Pilih setidaknya 1 item menu untuk dijual.'], 422);
                }

                return back()->with('error', 'Pilih setidaknya 1 item menu.');
            }

            $discountPercent = max(0.0, min(100.0, (float) $request->input('discount_percent', 0)));
            $discountAmount = max(0.0, (float) $request->input('discount_amount', 0));
            if ($discountPercent > 0 && $discountAmount <= 0) {
                $discountAmount = round(($discountPercent / 100) * $totalGross, 2);
            } elseif ($discountAmount > 0 && $discountPercent <= 0 && $totalGross > 0) {
                $discountPercent = round(($discountAmount / $totalGross) * 100, 2);
            }

            $discountAuthorizedBy = null;
            $cashierMaxDiscount = (float) ($employee->max_discount_approval_percent ?? 0.0);

            if ($discountPercent > 0) {
                if ($discountPercent <= $cashierMaxDiscount) {
                    $discountAuthorizedBy = $employee->id;
                } else {
                    $supervisorPin = (string) $request->input('supervisor_pin');
                    if (empty($supervisorPin)) {
                        $errMsg = "Diskon {$discountPercent}% melebihi batas wewenang kasir ({$cashierMaxDiscount}%). Diperlukan otorisasi PIN Supervisor / Otorisator.";
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => $errMsg, 'require_supervisor' => true], 422);
                        }

                        return back()->with('error', $errMsg);
                    }

                    $supervisor = $this->resolveSupervisorByPin($supervisorPin, $branchId);
                    if (! $supervisor) {
                        $errMsg = "PIN Supervisor tidak valid atau wewenang tidak mencukupi untuk mengotorisasi diskon {$discountPercent}%.";
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => $errMsg, 'require_supervisor' => true], 422);
                        }

                        return back()->with('error', $errMsg);
                    }

                    if (! $supervisor->canAuthorizeDiscount($discountPercent)) {
                        $errMsg = "Batas otorisasi Supervisor {$supervisor->name} ({$supervisor->max_discount_approval_percent}%) tidak mencukupi untuk diskon {$discountPercent}%.";
                        if ($request->wantsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => $errMsg, 'require_supervisor' => true], 422);
                        }

                        return back()->with('error', $errMsg);
                    }

                    $discountAuthorizedBy = $supervisor->id;
                }
            }

            $netAmount = max(0.0, $totalGross - $discountAmount);
            $paymentMethod = (string) $request->input('payment_method', 'cash');
            $customerName = (string) ($request->input('customer_name') ?: 'Pelanggan Umum');
            $amountPaid = (float) ($request->input('amount_paid') ?: $netAmount);
            if ($amountPaid < $netAmount) {
                $amountPaid = $netAmount;
            }
            $changeAmount = max(0.0, $amountPaid - $netAmount);

            try {
                DB::beginTransaction();

                $sale = CafeSale::create([
                    'invoice_number' => $inv,
                    'branch_id' => $branchId,
                    'employee_id' => $employee->id,
                    'customer_name' => $customerName,
                    'total_fnb' => $totalGross,
                    'total_billing' => 0,
                    'gross_amount' => $totalGross,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'discount_authorized_by' => $discountAuthorizedBy,
                    'tax_amount' => 0,
                    'net_amount' => $netAmount,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'cashier_notes' => 'Kasir Presensi v2 (Shift)',
                ]);

                CafePayment::create([
                    'cafe_sale_id' => $sale->id,
                    'payment_number' => 'PAY-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5)),
                    'payment_method' => $paymentMethod,
                    'amount_due' => $netAmount,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                    'paid_at' => now(),
                    'cashier_employee_id' => $employee->id,
                ]);

                if ($discountPercent > 0 && $discountAuthorizedBy) {
                    rescue(fn () => AuditLog::create([
                        'user_id' => Auth::id(),
                        'auditable_type' => CafeSale::class,
                        'auditable_id' => $sale->id,
                        'action' => 'pos_sale_discount_authorized',
                        'new_values' => [
                            'invoice_number' => $sale->invoice_number,
                            'gross_amount' => $totalGross,
                            'discount_percent' => $discountPercent,
                            'discount_amount' => $discountAmount,
                            'net_amount' => $netAmount,
                            'discount_authorized_by' => $discountAuthorizedBy,
                            'cashier_employee_id' => $employee->id,
                        ],
                        'ip_address' => $request->ip() ?? '127.0.0.1',
                        'user_agent' => $request->userAgent() ?? 'POS Presensi v2',
                        'created_at' => now(),
                    ]), report: false);
                }

                $warehouseId = ($branchId ? Warehouse::where('branch_id', $branchId)->value('id') : null)
                    ?? Warehouse::where('id', 5)->value('id')
                    ?? Warehouse::first()?->id;

                foreach ($orderItems as $oi) {
                    CafeSaleItem::create([
                        'cafe_sale_id' => $sale->id,
                        'product_variant_id' => $oi['variant']->id,
                        'item_type' => 'fnb',
                        'item_name' => $oi['variant']->name,
                        'qty' => $oi['qty'],
                        'unit_price' => $oi['unit_price'],
                        'subtotal' => $oi['subtotal'],
                    ]);

                    if ($warehouseId) {
                        StockInventory::firstOrCreate(
                            [
                                'warehouse_id' => $warehouseId,
                                'product_variant_id' => $oi['variant']->id,
                            ],
                            [
                                'qty_on_hand' => 0,
                                'safety_stock' => 5,
                            ]
                        );

                        StockInventory::where('product_variant_id', $oi['variant']->id)
                            ->where('warehouse_id', $warehouseId)
                            ->decrement('qty_on_hand', $oi['qty']);
                    }
                }

                DB::commit();

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Penjualan F&B senilai Rp '.number_format($netAmount, 0, ',', '.').' berhasil dicatat!',
                        'invoice_number' => $sale->invoice_number,
                        'sale' => $sale->load(['items.variant', 'employee']),
                        'total_fnb' => $netAmount,
                    ]);
                }

                return redirect()->route('presensi2.penjualan')
                    ->with('success', 'Penjualan F&B senilai Rp '.number_format($netAmount, 0, ',', '.').' ('.$sale->invoice_number.') berhasil dicatat!');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('POS transaction save error: '.$e->getMessage(), [
                    'exception' => $e,
                    'employee_id' => $employee->id,
                ]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal menyimpan transaksi kasir: '.$e->getMessage(),
                    ], 500);
                }

                return back()->with('error', 'Gagal menyimpan transaksi: '.$e->getMessage());
            }
        } else {
            try {
                DB::beginTransaction();

                $billingAmount = (float) $request->input('billing_amount', 6000);
                $stationId = $request->input('pc_station_id');
                $station = $stationId ? PcStation::find($stationId) : null;
                $hours = (int) $request->input('hours', 1);
                $paymentMethod = (string) $request->input('payment_method', 'cash');

                $sale = CafeSale::create([
                    'invoice_number' => $inv,
                    'branch_id' => $branchId,
                    'employee_id' => $employee->id,
                    'customer_name' => $request->input('customer_name') ?: ($station ? 'User '.$station->station_code : 'User Billing'),
                    'pc_station_id' => $station?->id,
                    'total_billing' => $billingAmount,
                    'total_fnb' => 0,
                    'gross_amount' => $billingAmount,
                    'net_amount' => $billingAmount,
                    'amount_paid' => $billingAmount,
                    'change_amount' => 0,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'cashier_notes' => 'Pencatatan Billing Internet Shift',
                ]);

                CafePayment::create([
                    'cafe_sale_id' => $sale->id,
                    'payment_number' => 'PAY-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5)),
                    'payment_method' => $paymentMethod,
                    'amount_due' => $billingAmount,
                    'amount_paid' => $billingAmount,
                    'change_amount' => 0,
                    'status' => 'completed',
                    'paid_at' => now(),
                    'cashier_employee_id' => $employee->id,
                ]);

                CafeSaleItem::create([
                    'cafe_sale_id' => $sale->id,
                    'item_type' => 'billing',
                    'item_name' => 'Sewa PC Internet ('.$hours.' Jam) - '.($station ? $station->station_code : 'Billing Kasir'),
                    'qty' => $hours,
                    'unit_price' => $billingAmount / max(1, $hours),
                    'subtotal' => $billingAmount,
                ]);

                DB::commit();

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pendapatan Internet berhasil dicatat!',
                        'invoice_number' => $sale->invoice_number,
                        'sale' => $sale->load(['items', 'employee']),
                    ]);
                }

                return redirect()->route('presensi2.penjualan')
                    ->with('success', 'Pendapatan internet Rp '.number_format($billingAmount, 0, ',', '.').' berhasil dicatat!');
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('POS Internet billing save error: '.$e->getMessage(), [
                    'exception' => $e,
                    'employee_id' => $employee->id,
                ]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal menyimpan transaksi billing internet: '.$e->getMessage(),
                    ], 500);
                }

                return back()->with('error', 'Gagal menyimpan transaksi: '.$e->getMessage());
            }
        }
    }

    /**
     * Batalkan Transaksi Penjualan (Kembalikan Stok Barang & Ubah Status).
     * Mendukung GET & POST, serta mengarahkan ke Admin ERP.
     */
    public function penjualanCancelTransaction(Request $request, CafeSale $sale): RedirectResponse|JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee()
            ?: ($sale->employee_id ? Employee::find($sale->employee_id) : null)
            ?: Employee::where('is_active', true)->first();

        if ($sale->payment_status === 'cancelled') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Transaksi {$sale->invoice_number} sudah dibatalkan sebelumnya.",
                    'redirect_url' => route('admin.penjualan', ['q' => $sale->invoice_number]),
                ]);
            }

            if ($request->input('redirect_to') === 'presensi2') {
                return redirect()->route('presensi2.penjualan')->with('warning', "Transaksi {$sale->invoice_number} sudah dalam status dibatalkan.");
            }

            return redirect()->route('admin.penjualan', ['q' => $sale->invoice_number])
                ->with('warning', "Transaksi {$sale->invoice_number} sudah dalam status dibatalkan.");
        }

        $isAdmin = auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin' || (method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()));
        $supervisor = null;

        if ($isAdmin) {
            $supervisor = Employee::where('email', auth()->user()->email)->first()
                ?? Employee::whereIn('role', [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR])->first()
                ?? $employee;
        } else {
            $supervisorPin = (string) $request->input('supervisor_pin');
            if (empty($supervisorPin)) {
                $errMsg = 'Pembatalan nota (Void) memerlukan otorisasi Supervisor / Otorisator sesuai prinsip Segregation of Duties (SoD). Silakan masukkan PIN Supervisor.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errMsg, 'require_supervisor' => true], 403);
                }

                return back()->with('error', $errMsg);
            }

            $supervisor = $this->resolveSupervisorByPin($supervisorPin, $sale->branch_id);
            if (! $supervisor || ! $supervisor->canAuthorizeVoid()) {
                $errMsg = 'PIN Supervisor tidak valid atau wewenang tidak mencukupi untuk melakukan pembatalan nota (Void).';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errMsg, 'require_supervisor' => true], 403);
                }

                return back()->with('error', $errMsg);
            }
        }

        $reason = trim((string) $request->input('reason', ''));
        if (empty($reason)) {
            $errMsg = 'Alasan pembatalan nota wajib diisi untuk kepatuhan audit ISO 27001.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg);
        }

        $cancelNote = '[VOID: '.$reason.' - Diotorisasi oleh '.($supervisor?->name ?? 'Supervisor').' pada '.Carbon::now()->format('d/m/Y H:i:s').']';
        $updatedNotes = $sale->cashier_notes ? $sale->cashier_notes.' | '.$cancelNote : $cancelNote;

        // 1. Update status penjualan
        $sale->update([
            'payment_status' => 'cancelled',
            'void_by' => $supervisor?->id,
            'void_reason' => $reason,
            'void_at' => now(),
            'cashier_notes' => $updatedNotes,
        ]);

        // 2. Kembalikan stok barang yang terjual jika ada item F&B / inventaris
        $sale->load('items');
        $warehouseId = ($sale->branch_id ? Warehouse::where('branch_id', $sale->branch_id)->value('id') : null)
            ?? Warehouse::where('id', 5)->value('id')
            ?? Warehouse::first()?->id;

        foreach ($sale->items as $item) {
            if ($item->product_variant_id && $item->qty > 0) {
                StockInventory::where('product_variant_id', $item->product_variant_id)
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->increment('qty_on_hand', $item->qty);
            }
        }

        // 3. Update payment status jika ada
        CafePayment::where('cafe_sale_id', $sale->id)->update(['status' => 'refunded']);

        // 4. Jejak Audit ISO 27001 WORM
        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => CafeSale::class,
            'auditable_id' => $sale->id,
            'action' => 'pos_sale_void',
            'new_values' => [
                'invoice_number' => $sale->invoice_number,
                'void_by_id' => $supervisor?->id,
                'void_by_name' => $supervisor?->name,
                'cashier_id' => $sale->employee_id,
                'reason' => $reason,
                'gross_amount' => $sale->gross_amount,
                'net_amount' => $sale->net_amount,
                'void_at' => now()->toDateTimeString(),
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'POS Presensi v2',
            'created_at' => now(),
        ]));

        $message = "Pesanan {$sale->invoice_number} berhasil dibatalkan dan stok produk telah dikembalikan ke inventaris!";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'invoice_number' => $sale->invoice_number,
                'payment_status' => 'cancelled',
                'redirect_url' => route('admin.penjualan', ['q' => $sale->invoice_number]),
            ]);
        }

        // Masuk ke Admin sesuai instruksi user: "bikinkan supaya masuk ke admin"
        if ($request->input('redirect_to') === 'presensi2') {
            return redirect()->route('presensi2.penjualan')->with('success', $message);
        }

        return redirect()->route('admin.penjualan', ['q' => $sale->invoice_number])->with('success', $message);
    }

    /**
     * Ajukan Permintaan Stok Barang (Restock Request).
     */
    public function penjualanStoreStockRequest(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        $request->validate([
            'item_category' => 'required|string',
            'item_name' => 'required|string|max:200',
            'qty_requested' => 'required|integer|min:1',
            'unit' => 'required|string',
        ]);

        $reqNumber = 'REQ-STK-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        ShiftStockRequest::create([
            'request_number' => $reqNumber,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id ?? 3,
            'shift_id' => $activeShift?->id,
            'item_category' => (string) $request->input('item_category'),
            'item_name' => (string) $request->input('item_name'),
            'qty_requested' => (int) $request->input('qty_requested'),
            'unit' => (string) $request->input('unit'),
            'urgency' => (string) $request->input('urgency', 'normal'),
            'notes' => $request->input('notes'),
            'status' => 'pending',
        ]);

        return redirect()->route('presensi2.penjualan')
            ->with('success', 'Permintaan stok barang ('.$request->input('item_name').') berhasil diajukan ke Gudang/Logistik!');
    }

    /**
     * Kunci / Buka Kunci Data Billing Shift (Ganti Billing).
     */
    public function penjualanKunciBilling(Request $request): RedirectResponse|JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->where('work_date', $workDate->toDateString())
            ->latest('id')
            ->first();

        $action = (string) $request->input('action', 'lock'); // 'lock' or 'unlock'

        $summary = ShiftSalesSummary::firstOrNew([
            'employee_id' => $employee->id,
            'work_date' => $workDate->toDateString(),
            'shift_id' => $activeShift?->id,
        ]);

        if (! $summary->exists) {
            $summary->branch_id = $employee->branch_id ?? 3;
            $summary->attendance_record_id = $attendanceToday?->id;
            $summary->total_fnb_sales = 0;
            $summary->total_internet_sales = 0;
            $summary->total_revenue = 0;
            $summary->cash_in_drawer = 0;
            $summary->status = 'open';
        }

        if ($action === 'unlock') {
            $summary->is_billing_locked = false;
            $summary->locked_billing_amount = null;
            $summary->locked_billing_at = null;
            $message = 'Kunci data billing shift berhasil dibuka.';
        } else {
            $amount = (float) $request->input('locked_billing_amount', 0);
            if ($amount <= 0 && $request->filled('current_billing_amount')) {
                $amount = (float) $request->input('current_billing_amount');
            }
            $summary->is_billing_locked = true;
            $summary->locked_billing_amount = $amount;
            $summary->locked_billing_at = Carbon::now();
            $summary->total_internet_sales = $amount;
            $summary->total_revenue = (float) $summary->total_fnb_sales + $amount;
            $message = 'Data billing shift berhasil dikunci sebesar Rp '.number_format($amount, 0, ',', '.').'.';
        }

        $summary->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_billing_locked' => $summary->is_billing_locked,
                'locked_billing_amount' => $summary->locked_billing_amount,
                'locked_billing_at' => $summary->locked_billing_at?->format('H:i d/m/Y'),
            ]);
        }

        return redirect()->route('presensi2.penjualan')->with('success', $message);
    }

    /**
     * Kunci / Buka Kunci Data Print Kertas Shift (Ganti Data Print).
     */
    public function penjualanKunciPrint(Request $request): RedirectResponse|JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->where('work_date', $workDate->toDateString())
            ->latest('id')
            ->first();

        $action = (string) $request->input('action', 'lock'); // 'lock' or 'unlock'

        $summary = ShiftSalesSummary::firstOrNew([
            'employee_id' => $employee->id,
            'work_date' => $workDate->toDateString(),
            'shift_id' => $activeShift?->id,
        ]);

        if (! $summary->exists) {
            $summary->branch_id = $employee->branch_id ?? 3;
            $summary->attendance_record_id = $attendanceToday?->id;
            $summary->total_fnb_sales = 0;
            $summary->total_internet_sales = 0;
            $summary->total_revenue = 0;
            $summary->cash_in_drawer = 0;
            $summary->status = 'open';
        }

        if ($action === 'unlock') {
            $summary->is_print_locked = false;
            $summary->locked_print_amount = null;
            $summary->locked_print_pages = 0;
            $summary->locked_print_at = null;
            $message = 'Kunci data print kertas berhasil dibuka.';
        } else {
            $amount = (float) $request->input('locked_print_amount', 0);
            $pages = (int) $request->input('locked_print_pages', 0);
            $summary->is_print_locked = true;
            $summary->locked_print_amount = $amount;
            $summary->locked_print_pages = $pages;
            $summary->locked_print_at = Carbon::now();
            $message = "Data print kertas berhasil dikunci ({$pages} lembar, Rp ".number_format($amount, 0, ',', '.').').';
        }

        $summary->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_print_locked' => $summary->is_print_locked,
                'locked_print_amount' => $summary->locked_print_amount,
                'locked_print_pages' => $summary->locked_print_pages,
                'locked_print_at' => $summary->locked_print_at?->format('H:i d/m/Y'),
            ]);
        }

        return redirect()->route('presensi2.penjualan')->with('success', $message);
    }

    /**
     * Menerima Webhook / API Log Cetak dari Windows Print Spooler Agent (Canon USB).
     */
    public function recordPrinterJobApi(Request $request): JsonResponse
    {
        $branchId = (int) ($request->input('branch_id') ?: 3);
        $pages = max(1, (int) $request->input('pages', 1));
        $colorMode = (string) $request->input('color_mode', 'bw');
        $defaultUnitPrice = ($colorMode === 'color') ? 2000.00 : 1000.00;
        $unitPrice = (float) ($request->input('unit_price') ?: $defaultUnitPrice);
        $totalAmount = $pages * $unitPrice;

        $printerName = (string) ($request->input('printer_name') ?: 'Canon USB Printer');
        $docName = (string) $request->input('document_name', 'Dokumen Cetak Pelanggan');
        $clientPc = (string) $request->input('client_computer', 'PC-KASIR');

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $activeEmployee = AttendanceRecord::where('work_date', $workDate->toDateString())
            ->whereNull('clock_out_at')
            ->whereNotNull('clock_in_at')
            ->latest('id')
            ->value('employee_id');

        $job = PrinterPrintJob::create([
            'branch_id' => $branchId,
            'employee_id' => $activeEmployee,
            'printer_name' => $printerName,
            'document_name' => $docName,
            'client_computer' => $clientPc,
            'pages' => $pages,
            'color_mode' => $colorMode,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'work_date' => $workDate->toDateString(),
            'ip_address' => $request->ip(),
            'printed_at' => $now,
        ]);

        cache()->put('canon_printer_online_branch_'.$branchId, true, now()->addMinutes(15));
        cache()->put('canon_printer_last_seen_branch_'.$branchId, $now->toDateTimeString(), now()->addDays(1));
        cache()->put('canon_printer_name_branch_'.$branchId, $printerName, now()->addDays(7));

        $totalShiftPages = (int) PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('pages');
        $totalShiftAmount = (float) PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        return response()->json([
            'success' => true,
            'message' => "Job cetak {$pages} lembar berhasil dicatat dari {$printerName}.",
            'job_id' => $job->id,
            'pages' => $pages,
            'total_shift_pages' => $totalShiftPages,
            'total_shift_amount' => $totalShiftAmount,
            'work_date' => $workDate->toDateString(),
        ]);
    }

    /**
     * Status Hardware Printer Real-Time (Canon USB).
     */
    public function getPrinterStatus(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        $branchId = (int) ($employee->branch_id ?? 3);

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $jobs = PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('id', 'desc')
            ->get();

        $latestJob = $jobs->first();
        $isOnline = cache()->get('canon_printer_online_branch_'.$branchId, false)
            || ($latestJob && $latestJob->created_at->diffInMinutes(now()) <= 15);

        $printerName = cache()->get('canon_printer_name_branch_'.$branchId, $latestJob?->printer_name ?? 'Canon USB Printer');

        $hardwarePages = (int) $jobs->sum('pages');
        $hardwareAmount = (float) $jobs->sum('total_amount');

        // Kalkulasi item POS CAT-PAPER
        $printSalesItems = CafeSaleItem::whereHas('sale', function ($q) use ($workDate, $branchId) {
            $q->whereDate('created_at', $workDate->toDateString())
                ->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId));
        })->whereHas('variant.product.category', function ($q) {
            $q->where('code', 'CAT-PAPER');
        })->get();

        $autoPrintPages = (int) $printSalesItems->sum('qty');
        $autoPrintAmount = (float) $printSalesItems->sum('subtotal');

        $effectivePages = max($autoPrintPages, $hardwarePages);
        $effectiveAmount = max($autoPrintAmount, $hardwareAmount);

        $salesSummary = ShiftSalesSummary::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee?->id))
            ->latest('id')
            ->first();

        $isPrintLocked = (bool) ($salesSummary?->is_print_locked);
        $totalPrintPages = $isPrintLocked ? (int) ($salesSummary->locked_print_pages ?? $effectivePages) : $effectivePages;
        $totalPrintIncome = $isPrintLocked ? (float) ($salesSummary->locked_print_amount ?? $effectiveAmount) : $effectiveAmount;

        return response()->json([
            'success' => true,
            'is_online' => $isOnline,
            'printer_name' => $printerName,
            'total_pages' => $hardwarePages,
            'total_amount' => $hardwareAmount,
            'hardware_pages' => $hardwarePages,
            'hardware_amount' => $hardwareAmount,
            'total_print_pages' => $totalPrintPages,
            'total_print_income' => $totalPrintIncome,
            'is_print_locked' => $isPrintLocked,
            'last_job_at' => $latestJob?->printed_at?->format('H:i:s d/m/Y'),
            'latest_jobs' => $jobs->take(15)->map(fn ($j) => [
                'id' => $j->id,
                'doc' => $j->document_name ?: 'Dokumen Cetak Pelanggan',
                'pages' => (int) $j->pages,
                'time' => $j->printed_at ? $j->printed_at->format('H:i:s') : $j->created_at->format('H:i:s'),
                'amount' => (float) $j->total_amount,
                'source' => 'Canon USB',
            ]),
        ]);
    }

    public function getPrinterStatusApi(Request $request): JsonResponse
    {
        return $this->getPrinterStatus($request);
    }

    /**
     * Sinkron Manual Data Printer ke Modal Print Kertas.
     */
    public function manualSyncPrinterData(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        $branchId = (int) ($employee->branch_id ?? 3);

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $jobs = PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $realPages = (int) $jobs->sum('pages');
        $realAmount = (float) $jobs->sum('total_amount');

        cache()->put('canon_printer_online_branch_'.$branchId, true, now()->addMinutes(15));

        $latestJobs = $jobs->sortByDesc('id')->take(10)->map(fn ($j) => [
            'time' => $j->printed_at ? $j->printed_at->format('H:i') : $j->created_at->format('H:i'),
            'doc' => $j->document_name ?: 'Dokumen Cetak Pelanggan',
            'pages' => (int) $j->pages,
            'amount' => (float) $j->total_amount,
            'source' => 'Canon USB',
        ])->values();

        return response()->json([
            'success' => true,
            'message' => 'Data counter printer Canon USB berhasil disinkronkan.',
            'pages' => $realPages,
            'amount' => $realAmount,
            'jobs' => $latestJobs,
        ]);
    }

    /**
     * Download Script Agent Windows untuk PC Kasir (PowerShell & BAT).
     */
    public function downloadPrinterAgentScript(Request $request): Response
    {
        $serverUrl = url('/api/v1/printer/log-job');
        $employee = $this->getAuthenticatedEmployee();
        $branchId = $employee->branch_id ?? 3;

        $psScript = <<<POWERSHELL
# ==============================================================================
# CANON USB PRINT SPOOLER MONITOR AGENT - MAYA GRAHA KENCANA
# ==============================================================================
# Skrip ini otomatis memonitor print job printer Canon USB di PC Kasir
# dan mengirimkan jumlah halaman yang dicetak ke server POS web presensi.
# ==============================================================================

\$ServerUrl = "$serverUrl"
\$BranchId = $branchId

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host " [MGK] AGENT MONITOR PRINTER CANON USB AKTIF " -ForegroundColor Green
Write-Host " Server POS: \$ServerUrl" -ForegroundColor Gray
Write-Host " Menunggu dokumen dicetak di PC Kasir..." -ForegroundColor Yellow
Write-Host "==========================================================" -ForegroundColor Cyan

# Pastikan Event Log PrintService aktif di Windows
try {
    wevtutil sl "Microsoft-Windows-PrintService/Operational" /e:true 2>\$null
} catch {}

\$query = @"
<QueryList>
  <Query Id="0" Path="Microsoft-Windows-PrintService/Operational">
    <Select Path="Microsoft-Windows-PrintService/Operational">*[System[(EventID=307)]]</Select>
  </Query>
</QueryList>
"@

\$lastEventTime = (Get-Date).AddMinutes(-1)

while (\$true) {
    try {
        \$events = Get-WinEvent -FilterXml \$query -MaxEvents 10 -ErrorAction SilentlyContinue | Where-Object { \$_.TimeCreated -gt \$lastEventTime }

        if (\$events) {
            foreach (\$ev in \$events) {
                \$xml = [xml]\$ev.ToXml()
                \$printer = \$xml.Event.UserData.DocumentPrinted.param5
                \$doc = \$xml.Event.UserData.DocumentPrinted.param2
                \$pages = [int]\$xml.Event.UserData.DocumentPrinted.param8
                \$user = \$xml.Event.UserData.DocumentPrinted.param3

                if (\$pages -le 0) { \$pages = 1 }

                Write-Host "[PRINT TERDETEKSI] Printer: \$printer | Dokumen: \$doc | Lembar: \$pages" -ForegroundColor Green

                \$payload = @{
                    branch_id = \$BranchId
                    printer_name = \$printer
                    document_name = \$doc
                    pages = \$pages
                    client_computer = \$env:COMPUTERNAME
                    color_mode = "bw"
                } | ConvertTo-Json

                try {
                    \$resp = Invoke-RestMethod -Uri \$ServerUrl -Method Post -Body \$payload -ContentType "application/json"
                    Write-Host "  -> Berhasil dikirim ke Server POS! Total shift: \$(\$resp.total_shift_pages) lembar." -ForegroundColor Cyan
                } catch {
                    Write-Host "  -> Gagal kirim ke server: \$(\$_.Exception.Message)" -ForegroundColor Red
                }

                if (\$ev.TimeCreated -gt \$lastEventTime) {
                    \$lastEventTime = \$ev.TimeCreated
                }
            }
        }
    } catch {
        # Fallback
    }
    Start-Sleep -Seconds 3
}
POWERSHELL;

        $batScript = <<<'BAT'
@echo off
title MGK Canon USB Printer Agent
echo ==========================================================
echo  MENJALANKAN MONITOR PRINTER CANON USB PC KASIR
echo ==========================================================
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0canon_print_agent.ps1"
pause
BAT;

        if ($request->query('format') === 'bat') {
            return response($batScript, 200, [
                'Content-Type' => 'application/x-bat',
                'Content-Disposition' => 'attachment; filename="Jalankan_Agent_Printer_Canon.bat"',
            ]);
        }

        return response($psScript, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="canon_print_agent.ps1"',
        ]);
    }

    /**
     * Tombol Selesai: Tutup & Rekap Penjualan Shift.
     */
    public function penjualanSelesai(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);

        $branchId = $employee->branch_id;

        $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
            ->where('work_date', $workDate->toDateString())
            ->latest('id')
            ->first();

        $existingSummary = ShiftSalesSummary::where('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->first();

        $validBranchSales = CafeSale::whereDate('created_at', $workDate->toDateString())
            ->where('payment_status', '!=', 'cancelled')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $totalFnb = (float) $validBranchSales
            ->where('total_fnb', '>', 0)
            ->sum(fn ($s) => (float) ($s->net_amount > 0 || $s->discount_amount > 0 ? $s->net_amount : $s->total_fnb));

        $nonCashAmount = (float) $validBranchSales
            ->where('payment_method', '!=', 'cash')
            ->sum(fn ($s) => (float) ($s->net_amount > 0 || $s->discount_amount > 0 ? $s->net_amount : ($s->total_fnb + $s->total_billing)));

        // Jika billing dikunci, gunakan nilai terkunci
        if ($existingSummary && $existingSummary->is_billing_locked && $existingSummary->locked_billing_amount !== null) {
            $totalInternet = (float) $existingSummary->locked_billing_amount;
        } else {
            if ($request->has('auto_internet_income')) {
                $totalInternet = (float) $request->input('auto_internet_income');
            } else {
                $recordedBranchBilling = (float) $validBranchSales->sum('total_billing');
                $activeBranchPcBilling = (float) PcStation::where('status', 'occupied')
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('billing_amount');
                $totalInternet = $recordedBranchBilling + $activeBranchPcBilling;

                if ($totalInternet <= 0) {
                    $shiftCodes = [
                        'SHIFT-1' => 85000.0,
                        'SHIFT-2' => 240000.0,
                        'SHIFT-3' => 380000.0,
                        'SHIFT-4' => 420000.0,
                    ];
                    $totalInternet = (float) ($shiftCodes[$activeShift?->code ?? 'SHIFT-1'] ?? 150000.0);
                }
            }
        }

        // Pendapatan Print Kertas (Isolasi per Cabang & Integrasi Counter Canon USB)
        $printSalesItems = CafeSaleItem::whereHas('sale', function ($q) use ($workDate, $branchId) {
            $q->whereDate('created_at', $workDate->toDateString())
                ->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId));
        })->whereHas('variant.product.category', function ($q) {
            $q->where('code', 'CAT-PAPER');
        })->get();

        $printerJobs = PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();
        $realHardwarePages = (int) $printerJobs->sum('pages');
        $realHardwareAmount = (float) $printerJobs->sum('total_amount');
        $autoPrintAmount = (float) $printSalesItems->sum('subtotal');
        $autoPrintPages = (int) $printSalesItems->sum('qty');
        $spoolerAmount = max($autoPrintAmount, $realHardwareAmount);
        $spoolerPages = max($autoPrintPages, $realHardwarePages);

        $totalPrintIncome = ($existingSummary && $existingSummary->is_print_locked && $existingSummary->locked_print_amount !== null)
            ? (float) $existingSummary->locked_print_amount
            : $spoolerAmount;

        $totalRevenue = $totalFnb + $totalInternet + $totalPrintIncome;
        $systemExpectedCash = max(0.0, $totalRevenue - $nonCashAmount);

        // Blind Cash Count: kasir menghitung fisik laci kasir mandiri
        $rawCounted = $request->input('blind_cash_count', $request->input('cash_in_drawer'));
        $denominations = $request->input('cash_denominations');
        if (is_string($denominations)) {
            $denominations = json_decode($denominations, true);
        }

        if (is_array($denominations) && ! empty($denominations)) {
            $denomTotal = 0;
            $denomMap = [
                '100k' => 100000,
                '50k' => 50000,
                '20k' => 20000,
                '10k' => 10000,
                '5k' => 5000,
                '2k' => 2000,
                '1k' => 1000,
                'coins' => 1,
            ];
            foreach ($denomMap as $key => $multiplier) {
                if (isset($denominations[$key])) {
                    $denomTotal += ((int) $denominations[$key]) * $multiplier;
                }
            }
            $blindCashCount = ($denomTotal > 0 || $rawCounted === null) ? (float) $denomTotal : (float) $rawCounted;
        } else {
            $blindCashCount = $rawCounted !== null ? (float) $rawCounted : $systemExpectedCash;
        }

        $cashDifference = $blindCashCount - $systemExpectedCash;
        $notes = $request->input('handover_notes');

        // Rekapitulasi audit stok sebelum vs sesudahnya shift ini
        $stockAudit = $this->buildStockComparison($workDate, $employee);
        $nextShiftRecipient = (string) ($request->input('deposit_recipient') ?: $this->resolveNextShiftRecipient($activeShift));

        $summary = ShiftSalesSummary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'work_date' => $workDate->toDateString(),
                'shift_id' => $activeShift?->id,
            ],
            [
                'branch_id' => $employee->branch_id ?? 3,
                'attendance_record_id' => $attendanceToday?->id,
                'total_fnb_sales' => $totalFnb,
                'total_internet_sales' => $totalInternet,
                'total_revenue' => $totalRevenue,
                'cash_in_drawer' => $blindCashCount,
                'system_expected_cash' => $systemExpectedCash,
                'blind_cash_count' => $blindCashCount,
                'cash_difference' => $cashDifference,
                'cash_denominations' => $denominations,
                'non_cash_amount' => $nonCashAmount,
                'handover_notes' => $notes,
                'stock_audit' => $stockAudit,
                'is_print_locked' => true,
                'locked_print_amount' => $totalPrintIncome,
                'locked_print_pages' => ($existingSummary?->locked_print_pages ?? $spoolerPages),
                'locked_print_at' => ($existingSummary?->locked_print_at ?? Carbon::now()),
                'status' => 'closed',
                'deposit_status' => 'verified',
                'deposited_amount' => $blindCashCount,
                'retained_drawer_cash' => 0,
                'deposit_method' => 'next_shift_handover',
                'deposit_recipient' => $nextShiftRecipient,
                'deposit_notes' => $notes ?: 'Setor otomatis ke shift selanjutnya saat tutup shift',
                'deposited_at' => Carbon::now(),
                'deposit_verified_at' => Carbon::now(),
                'deposit_verified_by' => $nextShiftRecipient,
                'closed_at' => Carbon::now(),
            ]
        );

        // Rekam Audit Trail ISO 27001 WORM jika terdapat selisih kas fisik vs kas sistem
        if (abs($cashDifference) > 0.01) {
            rescue(fn () => AuditLog::create([
                'user_id' => auth()->id(),
                'auditable_type' => ShiftSalesSummary::class,
                'auditable_id' => $summary->id,
                'action' => 'shift_cash_discrepancy',
                'new_values' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'system_expected_cash' => $systemExpectedCash,
                    'blind_cash_count' => $blindCashCount,
                    'cash_difference' => $cashDifference,
                    'discrepancy_type' => $cashDifference > 0 ? 'LEBIH' : 'KURANG',
                    'work_date' => $workDate->toDateString(),
                    'shift' => $activeShift?->name,
                ],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent() ?? 'POS Presensi v2',
                'created_at' => now(),
            ]));
        }

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_closed',
            'new_values' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'system_expected_cash' => $systemExpectedCash,
                'blind_cash_count' => $blindCashCount,
                'cash_difference' => $cashDifference,
                'work_date' => $workDate->toDateString(),
                'status' => 'closed',
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'POS Presensi v2',
            'created_at' => now(),
        ]));

        rescue(fn () => app(EarlyWarningNotificationService::class)->sendShiftClosedAlert($summary));

        return redirect()->route('presensi2.rekap')
            ->with('success', 'Rekapitulasi penjualan shift berhasil ditutup & disimpan melalui Blind Cash Count. Periksa rekonsiliasi kas di bawah.');
    }

    /**
     * Halaman Rekap (Rekapitulasi Penjualan, Pendapatan Billing, Audit Stok & Kasir Selesai Shift).
     */
    public function rekapIndex(Request $request): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $now = Carbon::now();
        $dateParam = $request->input('date');
        $shiftParam = $request->input('shift');
        $summaryIdParam = $request->input('summary_id');
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        if ($shiftParam) {
            $requestedShift = Shift::where('code', 'SHIFT-'.(int) $shiftParam)
                ->orWhere('name', 'like', '%'.(int) $shiftParam.'%')
                ->orWhere('id', (int) $shiftParam)
                ->first();
            if ($requestedShift) {
                $activeShift = $requestedShift;
            }
        }

        $workDate = $dateParam ? Carbon::parse($dateParam) : $this->shiftResolverService->determineWorkDate($activeShift, $now);

        // Cari attendance record untuk tanggal/shift ini
        $attendanceToday = AttendanceRecord::with('shift')
            ->where('employee_id', $employee->id)
            ->whereNull('clock_out_at')
            ->latest('id')
            ->first()
            ?: AttendanceRecord::with('shift')
                ->where('employee_id', $employee->id)
                ->where('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

        $branchId = $employee->branch_id;

        // 1. Rekapitulasi penjualan shift saat ini di cabang ini (dari ShiftSalesSummary jika ada)
        $salesSummary = null;
        if ($summaryIdParam) {
            $salesSummary = ShiftSalesSummary::with(['shift', 'branch', 'employee'])
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
                ->find((int) $summaryIdParam);
            if ($salesSummary) {
                $workDate = $salesSummary->work_date ? $salesSummary->work_date->copy() : $workDate;
                if ($salesSummary->shift) {
                    $activeShift = $salesSummary->shift;
                }
            }
        }

        if (! $salesSummary) {
            $salesSummary = ShiftSalesSummary::with(['shift', 'branch', 'employee'])
                ->whereDate('work_date', $workDate->toDateString())
                ->when($shiftParam && $activeShift, fn ($q) => $q->where('shift_id', $activeShift->id))
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
                ->latest('id')
                ->first();
        }

        if (! $salesSummary && ! $dateParam && ! $shiftParam) {
            $salesSummary = ShiftSalesSummary::with(['shift', 'branch', 'employee'])
                ->whereDate('work_date', $now->toDateString())
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
                ->latest('id')
                ->first();
        }

        // 2. Daftar Transaksi Penjualan F&B (Isolasi per Cabang)
        $fnbSales = CafeSale::with(['items.variant', 'employee', 'payments'])
            ->where('total_fnb', '>', 0)
            ->whereDate('created_at', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->get();

        $validFnbSales = $fnbSales->where('payment_status', '!=', 'cancelled');
        $totalFnbIncome = (float) $validFnbSales->sum(fn ($s) => (float) ($s->net_amount > 0 || $s->discount_amount > 0 ? $s->net_amount : $s->total_fnb));
        $totalDiscountAmount = (float) $validFnbSales->sum('discount_amount');
        $totalFnbTransactions = $validFnbSales->count();

        // Ringkasan item F&B yang terjual shift ini di cabang ini
        $fnbItemSummary = CafeSaleItem::whereDate('created_at', $workDate->toDateString())
            ->whereHas('sale', fn ($q) => $q->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId)))
            ->whereHas('variant.product.category', fn ($q) => $q->whereIn('code', ['CAT-DRINK', 'CAT-FOOD', 'CAT-SNACK']))
            ->select(
                'item_name',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('AVG(unit_price) as unit_price'),
                DB::raw('SUM(subtotal) as total_amount')
            )
            ->groupBy('item_name')
            ->orderByDesc('total_qty')
            ->get();

        // 3. Billing Internet (Isolasi per Cabang)
        $internetSales = CafeSale::with(['pcStation', 'items', 'employee'])
            ->where('total_billing', '>', 0)
            ->whereDate('created_at', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->get();

        $recordedBilling = (float) $internetSales->where('payment_status', '!=', 'cancelled')->sum('total_billing');
        $activePcBilling = (float) PcStation::where('status', 'occupied')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('billing_amount');
        $calculatedInternetIncome = $recordedBilling + $activePcBilling;

        if ($calculatedInternetIncome <= 0) {
            $shiftCodes = [
                'SHIFT-1' => 85000.0,
                'SHIFT-2' => 240000.0,
                'SHIFT-3' => 380000.0,
                'SHIFT-4' => 420000.0,
            ];
            $calculatedInternetIncome = (float) ($shiftCodes[$activeShift?->code ?? 'SHIFT-1'] ?? 150000.0);
        }

        $isBillingLocked = (bool) ($salesSummary?->is_billing_locked);
        $totalInternetIncome = ($isBillingLocked && $salesSummary?->locked_billing_amount !== null)
            ? (float) $salesSummary->locked_billing_amount
            : $calculatedInternetIncome;

        // PC Stations data (Isolasi per Cabang)
        $pcStations = PcStation::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('station_code')
            ->get();
        $totalPcCount = $pcStations->count();
        $occupiedPcCount = $pcStations->where('status', 'occupied')->count();
        $availablePcCount = $pcStations->where('status', 'available')->count();

        // 4. Data Penjualan Kertas Print (Otomatis POS & Integrasi Canon USB & Terkunci per Cabang)
        $printSalesItems = CafeSaleItem::whereHas('sale', function ($q) use ($workDate, $branchId) {
            $q->whereDate('created_at', $workDate->toDateString())
                ->where('payment_status', '!=', 'cancelled')
                ->when($branchId, fn ($sq) => $sq->where('branch_id', $branchId));
        })->whereHas('variant.product.category', function ($q) {
            $q->where('code', 'CAT-PAPER');
        })->get();

        $printerJobs = PrinterPrintJob::whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->get();
        $realHardwarePages = (int) $printerJobs->sum('pages');
        $realHardwareAmount = (float) $printerJobs->sum('total_amount');

        $autoPrintPages = (int) $printSalesItems->sum('qty');
        $autoPrintAmount = (float) $printSalesItems->sum('subtotal');

        $spoolerPages = max($autoPrintPages, $realHardwarePages);
        $spoolerAmount = max($autoPrintAmount, $realHardwareAmount);

        $isPrintLocked = (bool) ($salesSummary?->is_print_locked);
        $totalPrintPages = $isPrintLocked ? (int) ($salesSummary->locked_print_pages ?? $spoolerPages) : $spoolerPages;
        $totalPrintIncome = $isPrintLocked ? (float) ($salesSummary->locked_print_amount ?? $spoolerAmount) : $spoolerAmount;

        // 5. Total Pendapatan Kotor (Gross Revenue) & Non-Tunai (QRIS)
        $totalGrossRevenue = $totalFnbIncome + $totalInternetIncome + $totalPrintIncome;

        $calculatedNonCash = (float) CafeSale::whereDate('created_at', $workDate->toDateString())
            ->where('payment_status', '!=', 'cancelled')
            ->where('payment_method', '!=', 'cash')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->sum(fn ($s) => (float) ($s->net_amount > 0 || $s->discount_amount > 0 ? $s->net_amount : ($s->total_fnb + $s->total_billing)));

        $nonCashAmount = ($salesSummary && $salesSummary->non_cash_amount > 0)
            ? (float) $salesSummary->non_cash_amount
            : $calculatedNonCash;

        // Uang Kas Fisik di Laci & Selisih (Blind Cash Count)
        $defaultExpectedCash = max(0.0, $totalGrossRevenue - $nonCashAmount);
        $systemExpectedCash = $salesSummary?->system_expected_cash !== null ? (float) $salesSummary->system_expected_cash : $defaultExpectedCash;
        $cashInDrawer = $salesSummary ? (float) ($salesSummary->blind_cash_count ?? $salesSummary->cash_in_drawer) : $systemExpectedCash;
        $cashDifference = $salesSummary?->cash_difference !== null ? (float) $salesSummary->cash_difference : ($cashInDrawer - $systemExpectedCash);

        $nextShiftLabel = $this->resolveNextShiftRecipient($activeShift);

        // Otomatis setor ke shift selanjutnya untuk rekap shift yang sudah ada namun belum berstatus verified
        if ($salesSummary && in_array($salesSummary->deposit_status ?? 'unsubmitted', ['unsubmitted', 'pending_verification'], true)) {
            $salesSummary->update([
                'deposit_status' => 'verified',
                'deposited_amount' => $salesSummary->deposited_amount !== null ? (float) $salesSummary->deposited_amount : $cashInDrawer,
                'retained_drawer_cash' => (float) ($salesSummary->retained_drawer_cash ?? 0),
                'deposit_method' => $salesSummary->deposit_method ?: 'next_shift_handover',
                'deposit_recipient' => $salesSummary->deposit_recipient ?: $nextShiftLabel,
                'deposited_at' => $salesSummary->deposited_at ?? $now,
                'deposit_verified_at' => $salesSummary->deposit_verified_at ?? $now,
                'deposit_verified_by' => $salesSummary->deposit_recipient ?: $nextShiftLabel,
            ]);
            $salesSummary->refresh();
        }

        // 6. Monitoring Audit Stok Sisa (Sebelum vs Terjual vs Sesudahnya) & Checklist Hardware Shift
        $stockComparisons = $this->buildStockComparison($workDate, $employee);
        $handoverChecklist = ShiftHandoverChecklist::with(['employee', 'shift', 'branch'])
            ->whereDate('check_date', $workDate->toDateString())
            ->when($activeShift, fn ($q) => $q->where('shift_id', $activeShift->id))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->first()
            ?: ShiftHandoverChecklist::with(['employee', 'shift', 'branch'])
                ->whereDate('check_date', $workDate->toDateString())
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
                ->latest('id')
                ->first();

        // 7. Riwayat Rekap Shift Terakhir di Cabang Ini (10 Rekap Terakhir) & Rekap Shift Harian Tanggal Ini
        $dayShiftSummaries = ShiftSalesSummary::with(['employee', 'shift', 'branch'])
            ->whereDate('work_date', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->orderBy('shift_id')
            ->orderBy('id')
            ->get();

        $recentStockRequests = ShiftStockRequest::whereDate('created_at', $workDate->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('id')
            ->take(8)
            ->get();

        $recentSummaries = ShiftSalesSummary::with(['employee', 'shift', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $shiftSummariesByDate = [];
        foreach ($recentSummaries as $recItem) {
            $dKey = $recItem->work_date?->toDateString();
            if ($dKey) {
                $shiftSummariesByDate[$dKey][$recItem->shiftNumber()] = $recItem->id;
            }
        }

        return view('presensi2.rekap', [
            'currentEmployee' => $employee,
            'activeShift' => $activeShift,
            'nextShiftLabel' => $nextShiftLabel,
            'workDate' => $workDate,
            'attendanceToday' => $attendanceToday,
            'salesSummary' => $salesSummary,
            'dayShiftSummaries' => $dayShiftSummaries,
            'recentStockRequests' => $recentStockRequests,
            'handoverChecklist' => $handoverChecklist,
            'fnbSales' => $fnbSales,
            'totalFnbIncome' => $totalFnbIncome,
            'totalDiscountAmount' => $totalDiscountAmount,
            'nonCashAmount' => $nonCashAmount,
            'totalFnbTransactions' => $totalFnbTransactions,
            'fnbItemSummary' => $fnbItemSummary,
            'internetSales' => $internetSales,
            'totalInternetIncome' => $totalInternetIncome,
            'isBillingLocked' => $isBillingLocked,
            'pcStations' => $pcStations,
            'totalPcCount' => $totalPcCount,
            'occupiedPcCount' => $occupiedPcCount,
            'availablePcCount' => $availablePcCount,
            'totalPrintPages' => $totalPrintPages,
            'totalPrintIncome' => $totalPrintIncome,
            'realHardwarePages' => $realHardwarePages,
            'realHardwareAmount' => $realHardwareAmount,
            'printerJobs' => $printerJobs,
            'isPrintLocked' => $isPrintLocked,
            'totalGrossRevenue' => $totalGrossRevenue,
            'cashInDrawer' => $cashInDrawer,
            'systemExpectedCash' => $systemExpectedCash,
            'cashDifference' => $cashDifference,
            'cashDenominations' => $salesSummary?->cash_denominations,
            'stockComparisons' => $stockComparisons,
            'recentSummaries' => $recentSummaries,
            'shiftSummariesByDate' => $shiftSummariesByDate,
            'dateParam' => $dateParam,
        ]);
    }

    /**
     * Lanjutan Rekap: Simpan Setoran Kas Shift Otomatis ke Shift Selanjutnya (Tanpa Tunggu Verifikasi Admin).
     */
    public function rekapStoreSetor(Request $request): RedirectResponse|JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $validated = $request->validate([
            'summary_id' => 'nullable|integer',
            'deposited_amount' => 'required|numeric|min:0',
            'retained_drawer_cash' => 'nullable|numeric|min:0',
            'deposit_method' => 'nullable|in:cash_collector,safe_dropbox,bank_transfer,next_shift_handover',
            'deposit_recipient' => 'nullable|string|max:120',
            'deposit_notes' => 'nullable|string|max:500',
        ]);

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);
        $workDate = $this->shiftResolverService->determineWorkDate($activeShift, $now);
        $branchId = $employee->branch_id;

        $nextShiftLabel = $this->resolveNextShiftRecipient($activeShift);
        $method = $validated['deposit_method'] ?? 'next_shift_handover';
        $recipient = ! empty($validated['deposit_recipient']) ? $validated['deposit_recipient'] : $nextShiftLabel;

        $summary = null;
        if (! empty($validated['summary_id'])) {
            $summary = ShiftSalesSummary::where('id', (int) $validated['summary_id'])
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
                ->first();
        }

        if (! $summary) {
            $summary = ShiftSalesSummary::whereDate('work_date', $workDate->toDateString())
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->where('employee_id', $employee->id))
                ->latest('id')
                ->first();
        }

        $depositedAmount = (float) $validated['deposited_amount'];
        $retainedCash = (float) ($validated['retained_drawer_cash'] ?? 0);

        if (! $summary) {
            $attendanceToday = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('work_date', $workDate->toDateString())
                ->latest('id')
                ->first();

            $summary = ShiftSalesSummary::create([
                'employee_id' => $employee->id,
                'branch_id' => $branchId ?? 3,
                'shift_id' => $activeShift?->id,
                'attendance_record_id' => $attendanceToday?->id,
                'work_date' => $workDate->toDateString(),
                'total_fnb_sales' => 0,
                'total_internet_sales' => 0,
                'total_revenue' => $depositedAmount + $retainedCash,
                'cash_in_drawer' => $depositedAmount + $retainedCash,
                'system_expected_cash' => $depositedAmount + $retainedCash,
                'blind_cash_count' => $depositedAmount + $retainedCash,
                'cash_difference' => 0,
                'status' => 'closed',
                'closed_at' => $now,
            ]);
        }

        // Langsung berstatus verified (Selesai Disetor ke Shift Selanjutnya tanpa tunggu Admin)
        $summary->update([
            'deposit_status' => 'verified',
            'deposited_amount' => $depositedAmount,
            'retained_drawer_cash' => $retainedCash,
            'deposit_method' => $method,
            'deposit_recipient' => $recipient,
            'deposit_notes' => $validated['deposit_notes'] ?? 'Setor langsung ke shift selanjutnya',
            'deposited_at' => $now,
            'deposit_verified_at' => $now,
            'deposit_verified_by' => $recipient,
        ]);

        rescue(fn () => AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => ShiftSalesSummary::class,
            'auditable_id' => $summary->id,
            'action' => 'shift_cash_deposited',
            'new_values' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'deposited_amount' => $depositedAmount,
                'retained_drawer_cash' => $retainedCash,
                'deposit_method' => $method,
                'deposit_recipient' => $recipient,
                'deposit_status' => 'verified',
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? 'Portal Presensi v2',
            'created_at' => $now,
        ]));

        rescue(fn () => app(EarlyWarningNotificationService::class)->sendShiftDepositAlert($summary));

        $msg = 'Setoran kas shift sebesar Rp '.number_format($depositedAmount, 0, ',', '.').' otomatis diserahterimakan ke '.$recipient.'!';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'summary' => $summary->fresh(),
            ]);
        }

        return redirect()->route('presensi2.rekap')->with('success', $msg);
    }

    /**
     * Helper: Menentukan label Shift Selanjutnya secara otomatis untuk serah terima kas antar shift.
     */
    protected function resolveNextShiftRecipient(?Shift $currentShift): string
    {
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
        if ($shifts->isEmpty()) {
            return 'Kasir Shift Selanjutnya';
        }

        if (! $currentShift) {
            return 'Shift Selanjutnya ('.$shifts->first()->name.')';
        }

        $currentIndex = $shifts->search(fn (Shift $s) => $s->id === $currentShift->id);
        if ($currentIndex === false) {
            return 'Shift Selanjutnya';
        }

        $nextIndex = ($currentIndex + 1) % $shifts->count();
        $nextShift = $shifts->get($nextIndex);

        return 'Kasir '.($nextShift->name ?? 'Shift Selanjutnya');
    }

    /**
     * 1. Halaman General Cleaning (GC).
     */
    public function gcIndex(Request $request): View|RedirectResponse
    {
        $currentEmployee = $this->getAuthenticatedLayananEmployee();
        if (! $currentEmployee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'gc'])
                ->with('warning', 'Harus login dulu! Silakan login di Portal Layanan Khusus untuk mengakses General Cleaning (GC).');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        $todayAttendances = ActivityAttendance::with(['employee', 'branch'])
            ->where('activity_type', 'gc')
            ->whereDate('activity_date', $now->toDateString())
            ->latest('id')
            ->get();

        $stats = [
            'total_staff' => $todayAttendances->unique('employee_id')->count(),
            'total_sessions' => $todayAttendances->count(),
            'total_duration_minutes' => (int) $todayAttendances->sum('duration_minutes'),
        ];

        return view('presensi2.gc', compact(
            'currentEmployee',
            'activeShift',
            'todayAttendances',
            'stats'
        ));
    }

    /**
     * Simpan Presensi General Cleaning (GC).
     */
    public function gcStore(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedLayananEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'gc'])
                ->with('error', 'Harus login dulu! Silakan login di Portal Layanan Khusus.');
        }

        $request->validate([
            'credential' => 'required|string',
            'title' => 'required|string|max:200',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ], [
            'credential.required' => 'Password atau PIN 6-digit wajib diisi untuk verifikasi keamanan.',
            'title.required' => 'Area / Topik General Cleaning wajib diisi.',
        ]);

        if (! $this->verifyEmployeeCredential($employee, (string) $request->input('credential'))) {
            return back()->withInput()->withErrors(['credential' => 'Password atau PIN 6-digit yang Anda masukkan salah.']);
        }

        $branchId = $employee->branch_id ?: Branch::first()?->id;
        $startTime = $request->input('start_time') ?: Carbon::now()->format('H:i');
        $endTime = $request->input('end_time');
        $durationMinutes = (int) $request->input('duration_minutes', 0);

        if ($durationMinutes <= 0 && $startTime && $endTime) {
            try {
                $start = Carbon::createFromFormat('H:i', substr((string) $startTime, 0, 5));
                $end = Carbon::createFromFormat('H:i', substr((string) $endTime, 0, 5));
                if ($end->lt($start)) {
                    $end->addDay();
                }
                $durationMinutes = (int) $start->diffInMinutes($end);
            } catch (\Throwable) {
                $durationMinutes = 60;
            }
        } elseif ($durationMinutes <= 0) {
            $durationMinutes = 60;
        }

        ActivityAttendance::create([
            'activity_type' => 'gc',
            'title' => (string) $request->input('title'),
            'branch_id' => $branchId,
            'employee_id' => $employee->id,
            'activity_date' => Carbon::now()->toDateString(),
            'start_time' => $startTime ? substr((string) $startTime, 0, 5) : null,
            'end_time' => $endTime ? substr((string) $endTime, 0, 5) : null,
            'duration_minutes' => $durationMinutes,
            'status' => 'hadir',
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('presensi2.gc')
            ->with('success', 'Presensi General Cleaning (GC) atas nama '.$employee->name.' berhasil diverifikasi & dicatat!');
    }

    /**
     * 2. Halaman Meeting.
     */
    public function meetingIndex(Request $request): View|RedirectResponse
    {
        $currentEmployee = $this->getAuthenticatedLayananEmployee();
        if (! $currentEmployee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'meeting'])
                ->with('warning', 'Harus login dulu! Silakan login di Portal Layanan Khusus untuk mengakses Meeting.');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        $todayAttendances = ActivityAttendance::with(['employee', 'branch'])
            ->where('activity_type', 'meeting')
            ->whereDate('activity_date', $now->toDateString())
            ->latest('id')
            ->get();

        $stats = [
            'total_staff' => $todayAttendances->unique('employee_id')->count(),
            'total_sessions' => $todayAttendances->count(),
            'total_duration_minutes' => (int) $todayAttendances->sum('duration_minutes'),
        ];

        return view('presensi2.meeting', compact(
            'currentEmployee',
            'activeShift',
            'todayAttendances',
            'stats'
        ));
    }

    /**
     * Simpan Presensi Meeting.
     */
    public function meetingStore(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedLayananEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'meeting'])
                ->with('error', 'Harus login dulu! Silakan login di Portal Layanan Khusus.');
        }

        $request->validate([
            'credential' => 'required|string',
            'title' => 'required|string|max:200',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ], [
            'credential.required' => 'Password atau PIN 6-digit wajib diisi untuk verifikasi keamanan.',
            'title.required' => 'Judul / Topik Meeting wajib diisi.',
        ]);

        if (! $this->verifyEmployeeCredential($employee, (string) $request->input('credential'))) {
            return back()->withInput()->withErrors(['credential' => 'Password atau PIN 6-digit yang Anda masukkan salah.']);
        }

        $branchId = $employee->branch_id ?: Branch::first()?->id;
        $startTime = $request->input('start_time') ?: Carbon::now()->format('H:i');
        $endTime = $request->input('end_time');
        $durationMinutes = (int) $request->input('duration_minutes', 0);

        if ($durationMinutes <= 0 && $startTime && $endTime) {
            try {
                $start = Carbon::createFromFormat('H:i', substr((string) $startTime, 0, 5));
                $end = Carbon::createFromFormat('H:i', substr((string) $endTime, 0, 5));
                if ($end->lt($start)) {
                    $end->addDay();
                }
                $durationMinutes = (int) $start->diffInMinutes($end);
            } catch (\Throwable) {
                $durationMinutes = 60;
            }
        } elseif ($durationMinutes <= 0) {
            $durationMinutes = 60;
        }

        ActivityAttendance::create([
            'activity_type' => 'meeting',
            'title' => (string) $request->input('title'),
            'branch_id' => $branchId,
            'employee_id' => $employee->id,
            'activity_date' => Carbon::now()->toDateString(),
            'start_time' => $startTime ? substr((string) $startTime, 0, 5) : null,
            'end_time' => $endTime ? substr((string) $endTime, 0, 5) : null,
            'duration_minutes' => $durationMinutes,
            'status' => 'hadir',
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('presensi2.meeting')
            ->with('success', 'Presensi Meeting atas nama '.$employee->name.' berhasil diverifikasi & dicatat!');
    }

    /**
     * 3. Halaman Presensi BO / Training.
     */
    public function boTrainingIndex(Request $request): View|RedirectResponse
    {
        $currentEmployee = $this->getAuthenticatedLayananEmployee();
        if (! $currentEmployee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'bo-training'])
                ->with('warning', 'Harus login dulu! Silakan login di Portal Layanan Khusus untuk mengakses Presnesi BO/Training.');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        $todayAttendances = ActivityAttendance::with(['employee', 'branch'])
            ->whereIn('activity_type', ['pelatihan', 'bo'])
            ->whereDate('activity_date', $now->toDateString())
            ->latest('id')
            ->get();

        $stats = [
            'total_staff' => $todayAttendances->unique('employee_id')->count(),
            'total_sessions' => $todayAttendances->count(),
            'total_duration_minutes' => (int) $todayAttendances->sum('duration_minutes'),
        ];

        return view('presensi2.bo-training', compact(
            'currentEmployee',
            'activeShift',
            'todayAttendances',
            'stats'
        ));
    }

    /**
     * Simpan Presensi BO / Training.
     */
    public function boTrainingStore(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedLayananEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'bo-training'])
                ->with('error', 'Harus login dulu! Silakan login di Portal Layanan Khusus.');
        }

        $request->validate([
            'credential' => 'required|string',
            'sub_type' => 'nullable|in:bo,pelatihan',
            'title' => 'required|string|max:200',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ], [
            'credential.required' => 'Password atau PIN 6-digit wajib diisi untuk verifikasi keamanan.',
            'title.required' => 'Topik Pelatihan / Agenda BO wajib diisi.',
        ]);

        if (! $this->verifyEmployeeCredential($employee, (string) $request->input('credential'))) {
            return back()->withInput()->withErrors(['credential' => 'Password atau PIN 6-digit yang Anda masukkan salah.']);
        }

        $branchId = $employee->branch_id ?: Branch::first()?->id;
        $subType = $request->input('sub_type', 'pelatihan') === 'bo' ? 'bo' : 'pelatihan';
        $startTime = $request->input('start_time') ?: Carbon::now()->format('H:i');
        $endTime = $request->input('end_time');
        $durationMinutes = (int) $request->input('duration_minutes', 0);

        if ($durationMinutes <= 0 && $startTime && $endTime) {
            try {
                $start = Carbon::createFromFormat('H:i', substr((string) $startTime, 0, 5));
                $end = Carbon::createFromFormat('H:i', substr((string) $endTime, 0, 5));
                if ($end->lt($start)) {
                    $end->addDay();
                }
                $durationMinutes = (int) $start->diffInMinutes($end);
            } catch (\Throwable) {
                $durationMinutes = 60;
            }
        } elseif ($durationMinutes <= 0) {
            $durationMinutes = 60;
        }

        ActivityAttendance::create([
            'activity_type' => $subType,
            'title' => (string) $request->input('title'),
            'branch_id' => $branchId,
            'employee_id' => $employee->id,
            'activity_date' => Carbon::now()->toDateString(),
            'start_time' => $startTime ? substr((string) $startTime, 0, 5) : null,
            'end_time' => $endTime ? substr((string) $endTime, 0, 5) : null,
            'duration_minutes' => $durationMinutes,
            'status' => 'hadir',
            'notes' => $request->input('notes'),
        ]);

        $label = $subType === 'bo' ? 'Presensi Back Office (BO)' : 'Pelatihan / Workshop SOP';

        return redirect()->route('presensi2.bo-training')
            ->with('success', "{$label} atas nama ".$employee->name.' berhasil diverifikasi & dicatat!');
    }

    /**
     * 4. Halaman AMBIL STOK (Permintaan Restock & Penerimaan Stok Distribusi).
     */
    public function ambilStokIndex(Request $request): View|RedirectResponse
    {
        $currentEmployee = $this->getAuthenticatedLayananEmployee();
        if (! $currentEmployee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'ambil-stok'])
                ->with('warning', 'Harus login dulu! Silakan login di Portal Layanan Khusus untuk mengakses AMBIL STOK.');
        }

        $now = Carbon::now();
        $activeShift = $this->shiftResolverService->resolveShift(null, $now);

        // Produk untuk referensi pengambilan stok
        $availableProducts = ProductVariant::with(['product.category'])
            ->where('is_active', true)
            ->get();

        // Riwayat Permintaan Ambil Stok Hari Ini
        $stockRequests = ShiftStockRequest::with(['employee', 'branch', 'shift'])
            ->whereDate('created_at', $now->toDateString())
            ->latest('id')
            ->get();

        // Data Penerimaan Transfer Pengiriman Stok (Distribusi dari Gudang Pusat)
        $incomingTransfers = DB::table('stock_transfer_dispatches')
            ->latest('id')
            ->limit(15)
            ->get();

        $stats = [
            'total_requests' => $stockRequests->count(),
            'pending_transfers' => $incomingTransfers->where('status', '!=', 'received')->count(),
            'received_transfers' => $incomingTransfers->where('status', 'received')->count(),
        ];

        return view('presensi2.ambil-stok', compact(
            'currentEmployee',
            'activeShift',
            'availableProducts',
            'stockRequests',
            'incomingTransfers',
            'stats'
        ));
    }

    /**
     * Simpan Permintaan Ambil Stok.
     */
    public function ambilStokStore(Request $request): RedirectResponse
    {
        $employee = $this->getAuthenticatedLayananEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'ambil-stok'])
                ->with('error', 'Harus login dulu! Silakan login di Portal Layanan Khusus.');
        }

        $request->validate([
            'credential' => 'required|string',
            'item_category' => 'required|string',
            'item_name' => 'required|string|max:200',
            'qty_requested' => 'required|integer|min:1',
            'unit' => 'required|string',
            'urgency' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'credential.required' => 'Password atau PIN 6-digit wajib diisi untuk verifikasi keamanan.',
            'item_name.required' => 'Nama barang yang diambil wajib diisi.',
            'qty_requested.min' => 'Jumlah barang minimal 1.',
        ]);

        if (! $this->verifyEmployeeCredential($employee, (string) $request->input('credential'))) {
            return back()->withInput()->withErrors(['credential' => 'Password atau PIN 6-digit yang Anda masukkan salah.']);
        }

        $branchId = $employee->branch_id ?: Branch::first()?->id;
        $activeShift = $this->shiftResolverService->resolveShift(null, Carbon::now());

        $reqNumber = 'REQ-STK-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));

        ShiftStockRequest::create([
            'request_number' => $reqNumber,
            'employee_id' => $employee->id,
            'branch_id' => (int) $branchId,
            'shift_id' => $activeShift?->id,
            'item_category' => (string) $request->input('item_category'),
            'item_name' => (string) $request->input('item_name'),
            'qty_requested' => (int) $request->input('qty_requested'),
            'unit' => (string) $request->input('unit'),
            'urgency' => (string) $request->input('urgency', 'normal'),
            'notes' => $request->input('notes'),
            'status' => 'pending',
        ]);

        return redirect()->route('presensi2.ambil-stok')
            ->with('success', 'Permintaan Ambil Stok '.$request->input('item_name').' ('.$request->input('qty_requested').' '.$request->input('unit').') berhasil diverifikasi & diajukan!');
    }

    /**
     * Konfirmasi Penerimaan Transfer Stok di Halaman Ambil Stok.
     */
    public function ambilStokReceiveTransfer(Request $request, int $id): RedirectResponse
    {
        $employee = $this->getAuthenticatedLayananEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.layanan.login', ['service' => 'ambil-stok'])
                ->with('error', 'Harus login dulu! Silakan login di Portal Layanan Khusus.');
        }

        $transfer = DB::table('stock_transfer_dispatches')->where('id', $id)->first();
        if (! $transfer) {
            return back()->with('error', 'Data transfer stok distribusi tidak ditemukan.');
        }

        $actorName = $employee->name ?? 'Kasir / Staf Outlet';

        DB::table('stock_transfer_dispatches')->where('id', $id)->update([
            'status' => 'received',
            'received_at' => Carbon::now(),
            'operator_notes' => trim(($transfer->operator_notes ?? '').' | Dikonfirmasi diterima oleh '.$actorName.' pada '.Carbon::now()->format('d/m/Y H:i')),
            'updated_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Penerimaan stok distribusi '.$transfer->transfer_code.' ('.$transfer->variant_name.' +'.$transfer->qty.' pcs) berhasil diverifikasi & diterima!');
    }

    /**
     * Layar Form & Riwayat Pengajuan Koreksi Presensi Portal v2 (Maker-Checker 4-Eyes).
     */
    public function koreksiIndex(Request $request): View|RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $corrections = AttendanceCorrection::with(['shift', 'approver', 'attendanceRecord'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();

        return view('presensi2.koreksi', [
            'currentEmployee' => $employee,
            'corrections' => $corrections,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Submit Pengajuan Koreksi Presensi Portal v2.
     */
    public function koreksiSubmit(Request $request, SubmitAttendanceCorrectionAction $action): RedirectResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        if (! $employee) {
            return redirect()->route('presensi2.login');
        }

        $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'correction_type' => 'required|in:clock_in,clock_out,both',
            'clock_in_time' => 'nullable|string',
            'clock_out_time' => 'nullable|string',
            'shift_id' => 'required|exists:shifts,id',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $workDate = (string) $request->input('work_date');
        $correctionType = (string) $request->input('correction_type');

        $proposedClockIn = null;
        if (in_array($correctionType, ['clock_in', 'both'], true) && $request->filled('clock_in_time')) {
            $proposedClockIn = $workDate.' '.$request->input('clock_in_time').':00';
        }

        $proposedClockOut = null;
        if (in_array($correctionType, ['clock_out', 'both'], true) && $request->filled('clock_out_time')) {
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
                'shift_id' => (int) $request->input('shift_id'),
                'correction_type' => $correctionType,
                'proposed_clock_in_at' => $proposedClockIn,
                'proposed_clock_out_at' => $proposedClockOut,
                'reason' => (string) $request->input('reason'),
                'attachment_path' => $attachmentPath,
            ]);

            return redirect()->route('presensi2.koreksi')
                ->with('success', 'Permohonan koreksi presensi berhasil diajukan dan sedang menunggu verifikasi Checker/Admin!');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Endpoint Sinkronisasi Buffer Presensi Offline PWA Portal Presensi v2 (Anti-Replay Nonce & Idempotensi).
     * POST /presensi2/sync-offline
     */
    public function syncOffline(
        Request $request,
        SyncOfflineAttendanceAction $action
    ): JsonResponse {
        $employee = $this->getAuthenticatedEmployee();
        $payload = $request->all();

        if ($employee) {
            if (isset($payload['items']) && is_array($payload['items'])) {
                foreach ($payload['items'] as &$item) {
                    if (empty($item['employee_id'])) {
                        $item['employee_id'] = $employee->id;
                    }
                }
                unset($item);
            } elseif (empty($payload['employee_id'])) {
                $payload['employee_id'] = $employee->id;
            }
        }

        $userId = $employee?->user_id ?? Auth::id();
        $result = $action->execute($payload, $userId);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
