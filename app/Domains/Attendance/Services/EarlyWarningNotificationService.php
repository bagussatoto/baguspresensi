<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

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
     * Kirim notifikasi ke Admin bahwa ada pendaftaran baru yang memerlukan otorisasi (Approve).
     */
    public function sendNewRegistrationApprovalAlert(string $fullName, string $username, string $phone): bool
    {
        $verificationUrl = 'http://192.168.5.10:8086/bagus/admin/karyawan-verifikasi';
        $time = now()->toDateTimeString();

        $adminMessage = "📋 *[PENDAFTARAN KARYAWAN BARU - PERLU APPROVE]*\n\n"
            ."Calon karyawan telah mengajukan pendaftaran mandiri:\n"
            ."• Nama Lengkap: *{$fullName}*\n"
            ."• Username: `{$username}`\n"
            ."• No. WhatsApp: `{$phone}`\n"
            ."• Waktu: {$time}\n\n"
            ."👉 *Aksi Diperlukan:*\n"
            ."Silakan buka menu Verifikasi Karyawan untuk meninjau data KTP/kontak dan menyetujui (Approve):\n"
            ."{$verificationUrl}";

        // 1. Kirim via WhatsApp ke Admin / HRD jika nomor/grup terkonfigurasi
        $adminTarget = config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone');
        if ($adminTarget) {
            $this->sendWhatsApp($adminTarget, $adminMessage);
        }

        // 2. Kirim via Telegram Channel / Bot Admin
        return $this->sendTelegramAlert($adminMessage);
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
