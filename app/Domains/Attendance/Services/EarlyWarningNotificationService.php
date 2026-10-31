<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Models\EmployeeRegistration;
use App\Models\PasswordResetRequest;
use App\Models\ShiftSalesSummary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Service untuk mendistribusikan notifikasi peringatan dini (Early Warning Notification)
 * ke kanal WhatsApp dan Telegram (untuk Karyawan dan Administrator).
 */
class EarlyWarningNotificationService
{
    /**
     * Kirim notifikasi lockout (kegagalan login berulang) ke Karyawan dan/atau Admin.
     *
     * @param  array{
     *     employee_name: string,
     *     employee_code: string,
     *     tier: int,
     *     duration_seconds: int,
     *     ip_address: string,
     *     user_agent: string,
     *     occurred_at: string
     * } $details
     */
    public function sendLoginLockoutAlert(?Employee $employee, array $details): array
    {
        $tier = $details['tier'] ?? 1;
        $minutes = (int) ceil(($details['duration_seconds'] ?? 60) / 60);
        $empName = $details['employee_name'] ?? ($employee?->name ?? 'Karyawan');
        $empCode = $details['employee_code'] ?? ($employee?->code ?? '-');
        $username = $details['username'] ?? ($employee?->username ?? $empCode);
        $ip = $details['ip_address'] ?? '127.0.0.1';
        $time = $details['occurred_at'] ?? now()->toDateTimeString();

        // 1. Tautan Approval Pembukaan Blokir untuk Admin (Valid 30 menit)
        $expires = now()->addMinutes(30)->timestamp;
        $token = hash_hmac('sha256', ($employee?->id ?? 0).'|'.$ip.'|'.$expires, config('app.key'));
        $unlockUrl = route('admin.karyawan.unlock.show', [
            'employee' => $employee?->id ?? 0,
            'ip' => $ip,
            'expires' => $expires,
            'token' => $token,
        ]);

        // 2. Pesan WhatsApp ke Karyawan Sah (Pemberitahuan Akun Terkunci)
        $employeeMessage = "🚨 *[EARLY WARNING] PERCOBAAN LOGIN GAGAL BERULANG*\n\n"
            ."Halo *{$empName}* ({$empCode}),\n"
            ."Sistem mendeteksi percobaan login tidak sah sebanyak *3 kali berturut-turut*.\n\n"
            ."📌 *Detail Kejadian:*\n"
            ."• Status: Akun Terkunci (Tier {$tier})\n"
            ."• Durasi Penguncian: {$minutes} Menit ({$details['duration_seconds']} detik)\n"
            ."• Waktu: {$time}\n"
            ."• Alamat IP: {$ip}\n\n"
            ."⚠️ *Tindakan Pengamanan:*\n"
            .'Jika ini bukan Anda, akun Anda mungkin sedang dicoba diakses oleh pihak lain. Segera hubungi Supervisor Cabang atau IT/Admin HRD untuk pengamanan akun.';

        // 3. Format Pesan WhatsApp ke Grup Security & HRD Alert MGK
        $adminWaMessage = "🚨 *[SECURITY & HRD ALERT MGK]*\n"
            ."*PERINGATAN: PERCOBAAN LOGIN TIDAK SAH (3X)*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."Terdeteksi aktivitas mencurigakan pada sistem presensi:\n\n"
            ."👤 *Karyawan Sasaran:* {$empName}\n"
            ."🆔 *NIP / NIK:* {$empCode}\n"
            ."🔑 *Identitas Input:* {$username}\n"
            ."🌐 *Alamat IP:* {$ip}\n"
            ."⏰ *Waktu:* {$time}\n\n"
            ."🛡️ *Tindakan Sistem:* Akun telah dibekukan sementara (Tier {$tier} - {$minutes} menit) demi mencegah akses tidak sah.\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';

        $results = [
            'whatsapp' => false,
            'whatsapp_employee' => false,
            'whatsapp_admin' => false,
            'telegram' => false,
            'unlock_url' => $unlockUrl,
        ];

        // 1. Kirim ke WhatsApp Karyawan (jika ada nomor telepon)
        $phone = $employee?->phone;
        if ($phone) {
            $results['whatsapp_employee'] = $this->sendWhatsApp($phone, $employeeMessage);
            $results['whatsapp'] = $results['whatsapp_employee'];
        }

        // 2. Kirim ke WhatsApp Admin HRD / IT (Format Opsi A: Prioritas Grup WhatsApp jika terkonfigurasi)
        $adminTarget = config('services.whatsapp.admin_group_id') ?: (config('services.whatsapp.admin_phone') ?: '081234567890');
        if ($adminTarget) {
            $results['whatsapp_admin'] = $this->sendWhatsApp($adminTarget, $adminWaMessage);
            if (! $results['whatsapp']) {
                $results['whatsapp'] = $results['whatsapp_admin'];
            }
        }

        // 3. Kirim ke Telegram Admin / Security Channel
        $adminTelegramMessage = "🛡️ *[SECURITY ALERT] SISTEM PRESENSI MGK*\n"
            ."Terjadi Lockout Akun Karyawan:\n"
            ."• Karyawan: *{$empName}* ({$empCode})\n"
            ."• Level Lockout: *Tier {$tier}* ({$minutes} Menit)\n"
            ."• IP Address: `{$ip}`\n"
            ."• Waktu: {$time}\n"
            ."• User-Agent: {$details['user_agent']}";

        $results['telegram'] = $this->sendTelegramAlert($adminTelegramMessage);

        return $results;
    }

    /**
     * Kirim notifikasi peringatan dini ketika terdeteksi 3x percobaan gagal/mencurigakan pada registrasi.
     *
     * @param  array{
     *     target_name: string,
     *     target_phone: ?string,
     *     target_code: ?string,
     *     attempted_username: string,
     *     ip_address: string,
     *     duration_minutes: int
     * } $details
     */
    public function sendRegistrationBreachAlert(array $details): array
    {
        $targetName = $details['target_name'];
        $targetPhone = $details['target_phone'] ?? null;
        $targetCode = $details['target_code'] ?? '-';
        $ip = $details['ip_address'] ?? '127.0.0.1';
        $minutes = $details['duration_minutes'] ?? 5;
        $attemptedUser = $details['attempted_username'];
        $time = now()->toDateTimeString();

        $results = [
            'whatsapp' => false,
            'telegram' => false,
        ];

        // 1. Notifikasi ke WhatsApp pemilik sah akun / data
        if ($targetPhone) {
            $userMessage = "⚠️ *[PERINGATAN KEAMANAN PRESENSI MGK]*\n\n"
                ."Halo *{$targetName}* ({$targetCode}),\n"
                ."Sistem mendeteksi upaya pendaftaran berulang kali (3x) menggunakan data identitas Anda dari IP: `{$ip}`.\n\n"
                ."🛡️ *Status Akun Anda:* Tetap Aman.\n"
                ."Sistem telah memblokir sementara perangkat pelaku selama {$minutes} menit.\n"
                .'Jika Anda tidak merasa melakukan ini, mohon waspada dan koordinasikan dengan Supervisor / Admin HRD.';

            $results['whatsapp'] = $this->sendWhatsApp($targetPhone, $userMessage);
        }

        // 2. Alert ke Grup WhatsApp & Telegram Pengawas Admin
        $adminMessage = "🚨 *[SECURITY & HRD ALERT MGK]*\n"
            ."*PERINGATAN: UPAYA REGISTRASI MENCURIGAKAN (3X)*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."Terdeteksi upaya registrasi berulang menggunakan data identitas karyawan aktif:\n\n"
            ."👤 *Sasaran Identitas:* {$targetName}\n"
            ."🆔 *NIP / NIK:* {$targetCode}\n"
            ."🔑 *Username Dicoba:* {$attemptedUser}\n"
            ."🌐 *Alamat IP:* {$ip}\n"
            ."⏰ *Waktu:* {$time}\n\n"
            ."🛡️ *Tindakan Sistem:* Perangkat IP penyerang telah diblokir sementara ({$minutes} menit).\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        $results['telegram'] = $this->sendTelegramAlert($adminMessage);

        return $results;
    }

    /**
     * Kirim notifikasi ke Grup WhatsApp Admin & Telegram bahwa ada pendaftaran baru/ulang yang memerlukan otorisasi (Approve).
     */
    public function sendNewRegistrationApprovalAlert(
        string $fullName,
        string $username,
        string $phone,
        ?string $email = null,
        ?string $ipAddress = null
    ): bool {
        $time = now()->toDateTimeString();
        $isReReg = str_contains(strtoupper($fullName), 'DAFTAR ULANG');
        $cleanName = trim(str_ireplace('(DAFTAR ULANG)', '', $fullName));
        $submissionType = $isReReg ? 'Daftar Ulang (Revisi Data)' : 'Pendaftaran Baru';

        $regRecord = null;
        if ($email === null || $ipAddress === null) {
            $regRecord = EmployeeRegistration::whereRaw('LOWER(username) = ?', [strtolower($username)])
                ->latest('id')
                ->first();
        }

        $resolvedEmail = $email ?: ($regRecord?->email ?: (request()->input('email') ?: '-'));
        $resolvedIp = $ipAddress ?: ($regRecord?->ip_address ?: (request()->ip() ?: '127.0.0.1'));

        $adminMessage = "📋 *[SECURITY & HRD ALERT MGK]*\n"
            ."*PENGAJUAN REGISTRASI KARYAWAN (PERLU VERIFIKASI)*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."Calon karyawan telah mengajukan pendaftaran akun mandiri:\n\n"
            ."👤 *Nama Lengkap:* {$cleanName}\n"
            ."🔑 *Username:* {$username}\n"
            ."📱 *No. WhatsApp:* {$phone}\n"
            ."✉️ *Alamat Email:* {$resolvedEmail}\n"
            ."🌐 *Alamat IP:* {$resolvedIp}\n"
            ."📌 *Jenis Pengajuan:* {$submissionType}\n"
            ."⏰ *Waktu Pengajuan:* {$time}\n\n"
            ."🛡️ *Status:* Menunggu Otorisasi / Verifikasi Admin HRD\n"
            ."Silakan tinjau pada menu Admin: Master Karyawan > Verifikasi Karyawan\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';

        // 1. Kirim via WhatsApp ke Admin / HRD jika nomor/grup terkonfigurasi
        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        // 2. Kirim via Telegram Channel / Bot Admin
        return $this->sendTelegramAlert($adminMessage);
    }

    /**
     * Kirim notifikasi ke Karyawan & Pembaruan Status ke Grup WhatsApp Admin/HRD saat registrasi DISETUJUI (Approved).
     *
     * @return array{whatsapp_employee: bool, whatsapp_admin: bool, telegram: bool}
     */
    public function sendRegistrationApprovedAlert(
        Employee $employee,
        ?string $approverName = null,
        ?string $shiftLabel = null
    ): array {
        $time = now()->toDateTimeString();
        $branchName = $employee->branch?->name ?? 'Pusat';
        $roleValue = is_object($employee->role) && property_exists($employee->role, 'value')
            ? (string) $employee->role->value
            : (string) ($employee->role ?? 'Staff');
        $positionLabel = $employee->position ?: ucfirst($roleValue);
        $checker = $approverName ?: 'Admin HRD';
        $scheduleInfo = $shiftLabel ?: 'Mengikuti Jadwal Shift Operasional';

        $results = [
            'whatsapp_employee' => false,
            'whatsapp_admin' => false,
            'telegram' => false,
        ];

        // 1. Pesan ke Nomor WhatsApp Karyawan Baru
        if (! empty($employee->phone)) {
            $employeeMessage = "✅ *[PENDAFTARAN AKUN DISETUJUI — MGK]*\n\n"
                ."Halo *{$employee->name}*,\n"
                ."Selamat! Pengajuan pendaftaran akun presensi Anda telah *DISETUJUI (APPROVED)* oleh Admin HRD.\n\n"
                ."📌 *Detail Akun Resmi Anda:*\n"
                ."• NIP / NIK: *{$employee->code}*\n"
                ."• Username: `{$employee->username}`\n"
                ."• Cabang Penempatan: *{$branchName}*\n"
                ."• Jabatan / Peran: *{$positionLabel}*\n"
                ."• Jadwal Shift Awal: *{$scheduleInfo}*\n\n"
                ."🔐 *Panduan Login:*\n"
                .'Akun Anda sudah aktif di Master Data dan dapat langsung digunakan untuk login presensi menggunakan Nama Lengkap / NIK beserta PIN atau Password Anda.';

            $results['whatsapp_employee'] = $this->sendWhatsApp($employee->phone, $employeeMessage);
        }

        // 2. Pembaruan Status ke Grup WhatsApp 🚨 Security & HRD Alert MGK
        $adminGroupMessage = "✅ *[SECURITY & HRD ALERT MGK]*\n"
            ."*UPDATE STATUS: REGISTRASI KARYAWAN DISETUJUI*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."Pengajuan pendaftaran karyawan telah diverifikasi dan diaktifkan:\n\n"
            ."👤 *Nama Karyawan:* {$employee->name}\n"
            ."🆔 *NIP / NIK Baru:* {$employee->code}\n"
            ."🔑 *Username:* {$employee->username}\n"
            ."🏢 *Cabang Penempatan:* {$branchName}\n"
            ."💼 *Peran / Jabatan:* {$positionLabel}\n"
            ."🕒 *Shift Awal:* {$scheduleInfo}\n"
            ."👮 *Disetujui Oleh:* {$checker}\n"
            ."⏰ *Waktu Otorisasi:* {$time}\n\n"
            ."🛡️ *Status Akun:* AKTIF DI MASTER DATA 🟢\n"
            ."Karyawan yang bersangkutan sudah dapat melakukan login dan presensi.\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $results['whatsapp_admin'] = $this->sendWhatsApp($adminTarget, $adminGroupMessage);
        }

        // 3. Kirim ke Telegram Channel / Bot Admin
        $results['telegram'] = $this->sendTelegramAlert($adminGroupMessage);

        return $results;
    }

    /**
     * Kirim notifikasi ke Calon Karyawan & Pembaruan Status ke Grup WhatsApp Admin/HRD saat registrasi DITOLAK (Rejected).
     *
     * @return array{whatsapp_employee: bool, whatsapp_admin: bool, telegram: bool}
     */
    public function sendRegistrationRejectedAlert(
        EmployeeRegistration $registration,
        ?string $approverName = null,
        ?string $reRegistrationUrl = null
    ): array {
        $time = now()->toDateTimeString();
        $checker = $approverName ?: 'Admin HRD';
        $reason = $registration->rejection_reason ?: 'Data belum sesuai dengan ketentuan verifikasi HRD.';
        $phone = $registration->phone ?: '-';

        $results = [
            'whatsapp_employee' => false,
            'whatsapp_admin' => false,
            'telegram' => false,
        ];

        // 1. Pesan ke Nomor WhatsApp Calon Karyawan
        if (! empty($registration->phone)) {
            $reRegInstruction = $reRegistrationUrl
                ? "\n\n👉 *Perbaiki Data / Daftar Ulang:*\nSilakan klik tautan resmi berikut untuk memperbaiki data Anda tanpa mengetik ulang dari awal:\n{$reRegistrationUrl}"
                : "\n\n👉 *Perbaiki Data / Daftar Ulang:*\nSilakan buka menu *Cek Status Pengajuan* di portal presensi untuk melakukan perbaikan data.";

            $applicantMessage = "⚠️ *[PEMBERITAHUAN VERIFIKASI PENDAFTARAN — MGK]*\n\n"
                ."Halo *{$registration->full_name}* (`{$registration->username}`),\n"
                ."Pengajuan pendaftaran akun presensi Anda saat ini *BELUM DAPAT DISETUJUI (PERLU PERBAIKAN)*.\n\n"
                ."📝 *Catatan dari Admin / HRD:*\n"
                ."\"{$reason}\""
                .$reRegInstruction;

            $results['whatsapp_employee'] = $this->sendWhatsApp($registration->phone, $applicantMessage);
        }

        // 2. Pembaruan Status ke Grup WhatsApp 🚨 Security & HRD Alert MGK
        $adminGroupMessage = "🚫 *[SECURITY & HRD ALERT MGK]*\n"
            ."*UPDATE STATUS: REGISTRASI KARYAWAN DITOLAK / PERLU REVISI*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."Pengajuan pendaftaran karyawan telah ditolak oleh verifikator:\n\n"
            ."👤 *Nama Pemohon:* {$registration->full_name}\n"
            ."🔑 *Username:* {$registration->username}\n"
            ."📱 *No. WhatsApp:* {$phone}\n"
            ."📝 *Alasan Penolakan:* {$reason}\n"
            ."👮 *Ditolak Oleh:* {$checker}\n"
            ."⏰ *Waktu Keputusan:* {$time}\n\n"
            ."🛡️ *Status Berkas:* MENUNGGU PERBAIKAN / DAFTAR ULANG ⚠️\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $results['whatsapp_admin'] = $this->sendWhatsApp($adminTarget, $adminGroupMessage);
        }

        // 3. Kirim ke Telegram Channel / Bot Admin
        $results['telegram'] = $this->sendTelegramAlert($adminGroupMessage);

        return $results;
    }

    /**
     * Kirim notifikasi ke Grup WhatsApp Admin & Telegram ketika karyawan mengajukan permohonan reset PIN / Password.
     */
    public function sendPasswordResetRequestedAlert(Employee $employee, string $requestedType): bool
    {
        $time = now()->toDateTimeString();
        $branchName = $employee->branch?->name ?? 'Pusat';
        $typeLabel = strtoupper($requestedType);

        $adminMessage = "🔑 *[SECURITY & HRD ALERT MGK]*\n"
            ."*PERMOHONAN RESET {$typeLabel} KARYAWAN*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."Karyawan telah mengajukan permohonan pembaruan kredensial:\n\n"
            ."👤 *Nama Karyawan:* {$employee->name}\n"
            ."🆔 *NIP / NIK:* {$employee->code}\n"
            ."🏢 *Cabang / Unit:* {$branchName}\n"
            ."📌 *Tipe Kredensial:* {$typeLabel}\n"
            ."⏰ *Waktu Pengajuan:* {$time}\n\n"
            ."🛡️ *Status:* Menunggu Otorisasi / Verifikasi Admin\n"
            ."Silakan tinjau pada menu Admin: Master Karyawan > Reset PIN\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        return $this->sendTelegramAlert($adminMessage);
    }

    /**
     * Kirim notifikasi ke Karyawan & Pembaruan Status ke Grup WhatsApp Admin/HRD saat permohonan Reset PIN/Password Disetujui atau Ditolak.
     */
    public function sendPasswordResetDecisionAlert(
        PasswordResetRequest $resetRequest,
        bool $isApproved,
        ?string $adminName = null
    ): bool {
        $time = now()->toDateTimeString();
        $employee = $resetRequest->employee;
        $empName = $employee?->name ?? $resetRequest->employee_name ?? 'Karyawan';
        $empCode = $employee?->code ?? $resetRequest->employee_code ?? '-';
        $branchName = $employee?->branch?->name ?? 'Pusat';
        $typeLabel = strtoupper($resetRequest->requested_type ?: 'PIN');
        $checker = $adminName ?: 'Admin HRD';
        $phone = $employee?->phone ?? $resetRequest->phone;

        if ($isApproved) {
            if ($phone) {
                $empMsg = "✅ *[PERMOHONAN RESET {$typeLabel} DISETUJUI — MGK]*\n\n"
                    ."Halo *{$empName}* ({$empCode}),\n"
                    ."Permohonan pembaruan {$typeLabel} presensi Anda telah *DISETUJUI (APPROVED)* oleh Admin HRD.\n\n"
                    ."Silakan login ke portal presensi menggunakan {$typeLabel} baru Anda.";
                $this->sendWhatsApp($phone, $empMsg);
            }

            $adminMessage = "✅ *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: RESET {$typeLabel} KARYAWAN DISETUJUI*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Permohonan pembaruan kredensial karyawan telah disetujui:\n\n"
                ."👤 *Nama Karyawan:* {$empName}\n"
                ."🆔 *NIP / NIK:* {$empCode}\n"
                ."🏢 *Cabang / Unit:* {$branchName}\n"
                ."📌 *Tipe Kredensial:* {$typeLabel}\n"
                ."👮 *Disetujui Oleh:* {$checker}\n"
                ."⏰ *Waktu Otorisasi:* {$time}\n\n"
                ."🛡️ *Status:* KREDENSIAL BARU AKTIF 🟢\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';
        } else {
            $reason = $resetRequest->rejection_reason ?: 'Permohonan tidak memenuhi syarat verifikasi.';

            if ($phone) {
                $empMsg = "🚫 *[PERMOHONAN RESET {$typeLabel} DITOLAK — MGK]*\n\n"
                    ."Halo *{$empName}* ({$empCode}),\n"
                    ."Permohonan pembaruan {$typeLabel} presensi Anda telah *DITOLAK* oleh Admin HRD.\n\n"
                    ."📝 *Alasan:* \"{$reason}\"\n"
                    .'Kredensial lama Anda tetap berlaku.';
                $this->sendWhatsApp($phone, $empMsg);
            }

            $adminMessage = "🚫 *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: RESET {$typeLabel} KARYAWAN DITOLAK*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Permohonan pembaruan kredensial karyawan telah ditolak:\n\n"
                ."👤 *Nama Karyawan:* {$empName}\n"
                ."🆔 *NIP / NIK:* {$empCode}\n"
                ."🏢 *Cabang / Unit:* {$branchName}\n"
                ."📌 *Tipe Kredensial:* {$typeLabel}\n"
                ."📝 *Alasan Penolakan:* {$reason}\n"
                ."👮 *Ditolak Oleh:* {$checker}\n"
                ."⏰ *Waktu Keputusan:* {$time}\n\n"
                ."🛡️ *Status:* DITOLAK / KREDENSIAL LAMA TETAP BERLAKU ⚠️\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';
        }

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        return $this->sendTelegramAlert($adminMessage);
    }

    /**
     * Kirim notifikasi ke Grup WhatsApp Admin & Telegram ketika akun karyawan Dinonaktifkan (De-provisioned) atau Diaktifkan Kembali.
     */
    public function sendEmployeeStatusChangedAlert(
        Employee $employee,
        bool $isActive,
        ?string $adminName = null,
        ?string $reason = null
    ): bool {
        $time = now()->toDateTimeString();
        $branchName = $employee->branch?->name ?? 'Pusat';
        $executor = $adminName ?: 'Administrator';

        if (! $isActive) {
            $deactivationReason = $reason ?: 'Dinonaktifkan oleh Administrator';
            $adminMessage = "🔒 *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: AKUN KARYAWAN DINONAKTIFKAN (DE-PROVISIONED)*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Hak akses sistem presensi & operasional karyawan telah dicabut:\n\n"
                ."👤 *Nama Karyawan:* {$employee->name}\n"
                ."🆔 *NIP / NIK:* {$employee->code}\n"
                ."🏢 *Cabang / Unit:* {$branchName}\n"
                ."📝 *Alasan Penonaktifan:* {$deactivationReason}\n"
                ."👮 *Dieksekusi Oleh:* {$executor}\n"
                ."⏰ *Waktu Eksekusi:* {$time}\n\n"
                ."🛡️ *Status Akun:* NON-AKTIF / AKSES DICABUT 🔴\n"
                ."Karyawan yang bersangkutan tidak dapat lagi melakukan login di seluruh portal MGK.\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';
        } else {
            $adminMessage = "🔓 *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: AKUN KARYAWAN DIAKTIFKAN KEMBALI*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Hak akses sistem presensi & operasional karyawan telah dipulihkan:\n\n"
                ."👤 *Nama Karyawan:* {$employee->name}\n"
                ."🆔 *NIP / NIK:* {$employee->code}\n"
                ."🏢 *Cabang / Unit:* {$branchName}\n"
                ."👮 *Diaktifkan Oleh:* {$executor}\n"
                ."⏰ *Waktu Eksekusi:* {$time}\n\n"
                ."🛡️ *Status Akun:* AKTIF KEMBALI 🟢\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_';
        }

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        return $this->sendTelegramAlert($adminMessage);
    }

    /**
     * Kirim notifikasi Laporan Penutupan Shift & Rekonsiliasi Kas (Blind Cash Count) ke Grup WhatsApp Admin/Supervisor & Telegram.
     */
    public function sendShiftClosedAlert(ShiftSalesSummary $summary): bool
    {
        $summary->loadMissing(['employee', 'branch', 'shift']);

        $empName = $summary->employee?->name ?? 'Kasir Shift';
        $empCode = $summary->employee?->code ?? '-';
        $branchName = $summary->branch?->name ?? 'MGK Outlet';
        $shiftName = $summary->shift?->name ?? 'Shift Operasional';
        $workDate = $summary->work_date?->format('d/m/Y') ?? now()->format('d/m/Y');
        $closedTime = $summary->closed_at?->format('H:i:s') ?? now()->format('H:i:s');

        $fnb = number_format((float) $summary->total_fnb_sales, 0, ',', '.');
        $internet = number_format((float) $summary->total_internet_sales, 0, ',', '.');
        $totalRev = number_format((float) $summary->total_revenue, 0, ',', '.');
        $nonCash = number_format((float) $summary->non_cash_amount, 0, ',', '.');
        $expectedCash = number_format((float) $summary->system_expected_cash, 0, ',', '.');
        $countedCash = number_format((float) $summary->blind_cash_count, 0, ',', '.');
        $diff = (float) $summary->cash_difference;
        $absDiff = number_format(abs($diff), 0, ',', '.');

        if (abs($diff) < 0.01) {
            $headerIcon = '📊';
            $reconStatus = '✅ *PAS / SESUAI (Rp 0)*';
        } elseif ($diff > 0) {
            $headerIcon = '📈';
            $reconStatus = "⚠️ *SELISIH LEBIH (+Rp {$absDiff})*";
        } else {
            $headerIcon = '🚨';
            $reconStatus = "🔴 *SELISIH KURANG (-Rp {$absDiff})*";
        }

        $notes = $summary->handover_notes ?: '-';

        $adminMessage = "{$headerIcon} *[LAPORAN TUTUP SHIFT & KASIR MGK]*\n"
            ."*REKONSILIASI BLIND CASH COUNT*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."🏢 *Cabang / Outlet:* {$branchName}\n"
            ."🕒 *Shift & Tanggal:* {$shiftName} ({$workDate})\n"
            ."👤 *Kasir Bertugas:* {$empName} ({$empCode})\n"
            ."⏰ *Jam Tutup Shift:* {$closedTime}\n\n"
            ."💰 *RINCIAN OMSET SHIFT:*\n"
            ."• Omset F&B (Net): Rp {$fnb}\n"
            ."• Omset Billing Internet: Rp {$internet}\n"
            ."• *Total Omset Shift:* *Rp {$totalRev}*\n\n"
            ."💵 *REKONSILIASI KAS LACI:*\n"
            ."• Non-Tunai (QRIS/Bank): Rp {$nonCash}\n"
            ."• Target Kas Tunai Sistem: Rp {$expectedCash}\n"
            ."• Fisik Kas Laci Dihitung: *Rp {$countedCash}*\n"
            ."• Status Selisih: {$reconStatus}\n\n"
            ."📝 *Catatan Serah Terima:* {$notes}\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Sistem POS & Presensi Maya Graha Kencana_';

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        return $this->sendTelegramAlert($adminMessage);
    }

    /**
     * Kirim notifikasi Setoran Kas Shift (History Setor) ke Grup WhatsApp Admin/Supervisor & Telegram.
     */
    public function sendShiftDepositAlert(ShiftSalesSummary $summary): bool
    {
        $summary->loadMissing(['employee', 'branch', 'shift']);

        $empName = $summary->employee?->name ?? 'Kasir Shift';
        $empCode = $summary->employee?->code ?? '-';
        $branchName = $summary->branch?->name ?? 'MGK Outlet';
        $shiftName = $summary->shift?->name ?? 'Shift Operasional';
        $workDate = $summary->work_date?->format('d/m/Y') ?? now()->format('d/m/Y');
        $depositTime = $summary->deposited_at?->format('H:i:s') ?? now()->format('H:i:s');

        $deposited = number_format((float) $summary->deposited_amount, 0, ',', '.');
        $retained = number_format((float) $summary->retained_drawer_cash, 0, ',', '.');
        $methodLabel = $summary->depositMethodLabel();
        $recipient = $summary->deposit_recipient ?: '-';
        $notes = $summary->deposit_notes ?: '-';

        $adminMessage = "🏦 *[KONFIRMASI SETOR KAS SHIFT MGK]*\n"
            ."*LANJUTAN REKAPITULASI SHIFT*\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            ."🏢 *Cabang / Outlet:* {$branchName}\n"
            ."🕒 *Shift & Tanggal:* {$shiftName} ({$workDate})\n"
            ."👤 *Kasir Penyetor:* {$empName} ({$empCode})\n"
            ."⏰ *Waktu Setor:* {$depositTime}\n\n"
            ."💵 *RINCIAN SETORAN KAS:*\n"
            ."• Nominal Disetor: *Rp {$deposited}*\n"
            ."• Modal Laci Ditinggal: Rp {$retained}\n"
            ."• Metode Setor: {$methodLabel}\n"
            ."• Diserahkan Kepada: *{$recipient}*\n"
            ."• Status: ✅ *SELESAI (SETOR OTOMATIS KE SHIFT SELANJUTNYA)*\n\n"
            ."📝 *Catatan Setor:* {$notes}\n"
            ."━━━━━━━━━━━━━━━━━━━━\n"
            .'_Notifikasi Otomatis Sistem POS & Presensi Maya Graha Kencana_';

        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        return $this->sendTelegramAlert($adminMessage);
    }

    /**
     * Kirim notifikasi via WhatsApp Gateway.
     */
    public function sendWhatsApp(string $phoneNumber, string $message): bool
    {
        $target = trim($phoneNumber);

        // Jika target berupa Grup WhatsApp (misal: 120363xxx@g.us atau mengandung @g.us)
        if (str_ends_with($target, '@g.us') || str_contains($target, '@g.us')) {
            $cleanPhone = $target;
        } else {
            // Normalisasi nomor telepon perorangan: ubah 08xxx atau +628xxx menjadi 628xxx
            $cleanPhone = preg_replace('/[^0-9]/', '', $target);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62'.substr($cleanPhone, 1);
            }
        }

        $apiUrl = config('services.whatsapp.api_url');
        $apiToken = config('services.whatsapp.api_token');

        // Jika gateway belum disetel di .env, simulasikan pengiriman ke Application Log
        if (! $apiUrl || ! $apiToken) {
            Log::info("[EarlyWarning WhatsApp] Kirim pesan ke {$cleanPhone}: \n{$message}");

            return true;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => $apiToken,
                ])
                ->post($apiUrl, [
                    'target' => $cleanPhone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            $json = $response->json();
            $isSuccess = $response->successful() && (($json['status'] ?? true) === true);

            if ($isSuccess) {
                Log::info("[EarlyWarning WhatsApp] Berhasil terkirim ke {$cleanPhone}");

                return true;
            }

            Log::warning("[EarlyWarning WhatsApp] Gagal kirim ke {$cleanPhone}: HTTP {$response->status()} - ".json_encode($json));

            return false;
        } catch (\Throwable $e) {
            Log::error("[EarlyWarning WhatsApp] Exception saat kirim ke {$cleanPhone}: ".$e->getMessage());

            return false;
        }
    }

    /**
     * Kirim notifikasi alert ke Telegram Bot / Channel Admin.
     */
    public function sendTelegramAlert(string $message): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        // Jika bot token belum disetel di .env, simulasikan pengiriman ke Application Log
        if (! $botToken || ! $chatId) {
            Log::info("[EarlyWarning Telegram] Kirim pesan ke Admin Chat: \n{$message}");

            return true;
        }

        try {
            $telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $response = Http::timeout(5)->post($telegramUrl, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                Log::info('[EarlyWarning Telegram] Alert berhasil dikirim ke Telegram Admin');

                return true;
            }

            Log::warning("[EarlyWarning Telegram] Gagal kirim Telegram: HTTP {$response->status()}");

            return false;
        } catch (\Throwable $e) {
            Log::error('[EarlyWarning Telegram] Exception saat kirim Telegram: '.$e->getMessage());

            return false;
        }
    }
}
