<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Attendance\Services\EarlyWarningNotificationService;
use Illuminate\Console\Command;

class TestWhatsAppAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test-alert
                            {--type=ping : Jenis skenario uji coba (ping|registration|approved|rejected|deactivated|reactivated|reset-approved|reset-rejected)}
                            {--target= : Target nomor WA / Group ID (kosongkan untuk memakai WHATSAPP_ADMIN_GROUP_ID dari .env)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uji coba pengiriman pesan nyata ke Grup WhatsApp Security & HRD Alert MGK via Fonnte Gateway';

    /**
     * Execute the console command.
     */
    public function handle(EarlyWarningNotificationService $notificationService): int
    {
        $apiUrl = (string) config('services.whatsapp.api_url');
        $apiToken = (string) config('services.whatsapp.api_token');
        $target = (string) ($this->option('target') ?: config('services.whatsapp.admin_group_id') ?: config('services.whatsapp.admin_phone'));
        $type = (string) $this->option('type');

        $this->info('=== UJI COBA LIVE WHATSAPP GATEWAY (FONNTE) ===');
        $this->line("API URL      : {$apiUrl}");
        $this->line('API Token    : '.($apiToken ? substr($apiToken, 0, 5).'***'.substr($apiToken, -3) : '(KOSONG)'));
        $this->line("Target Kirim : {$target}");
        $this->line("Skenario Uji : {$type}");
        $this->newLine();

        if (! $apiUrl || ! $apiToken || ! $target) {
            $this->error('Konfigurasi WHATSAPP_API_URL, WHATSAPP_API_TOKEN, atau WHATSAPP_ADMIN_GROUP_ID di .env belum lengkap!');

            return self::FAILURE;
        }

        $time = now()->toDateTimeString();

        $message = match ($type) {
            'registration' => "📋 *[SECURITY & HRD ALERT MGK]*\n"
                ."*PENGAJUAN REGISTRASI KARYAWAN (PERLU VERIFIKASI)*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Calon karyawan telah mengajukan pendaftaran akun mandiri:\n\n"
                ."👤 *Nama Lengkap:* Andi Pratama (SIMULASI UJI COBA)\n"
                ."🔑 *Username:* andi_pratama_test\n"
                ."📱 *No. WhatsApp:* 081234567890\n"
                ."✉️ *Alamat Email:* andi.test@mayanet.com\n"
                ."🌐 *Alamat IP:* 192.168.1.25\n"
                ."📌 *Jenis Pengajuan:* Pendaftaran Baru\n"
                ."⏰ *Waktu Pengajuan:* {$time}\n\n"
                ."🛡️ *Status:* Menunggu Otorisasi / Verifikasi Admin HRD\n"
                ."Silakan tinjau pada menu Admin: Master Karyawan > Verifikasi Karyawan\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_',

            'approved' => "✅ *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: REGISTRASI KARYAWAN DISETUJUI*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Pengajuan pendaftaran karyawan telah diverifikasi dan diaktifkan:\n\n"
                ."👤 *Nama Karyawan:* Andi Pratama (SIMULASI UJI COBA)\n"
                ."🆔 *NIP / NIK Baru:* EMP-2610099\n"
                ."🔑 *Username:* andi_pratama_test\n"
                ."🏢 *Cabang Penempatan:* Cabang Utama MGK\n"
                ."💼 *Peran / Jabatan:* Cashier\n"
                ."🕒 *Shift Kerja Awal:* Shift Pagi (08:00 - 16:00)\n"
                ."👮 *Disetujui Oleh:* Admin HRD MGK\n"
                ."⏰ *Waktu Otorisasi:* {$time}\n\n"
                ."🛡️ *Status Akun:* AKTIF DI MASTER DATA 🟢\n"
                ."Karyawan yang bersangkutan sudah dapat melakukan login dan presensi.\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_',

            'rejected' => "🚫 *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: REGISTRASI KARYAWAN DITOLAK / PERLU REVISI*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Pengajuan pendaftaran karyawan telah ditolak oleh verifikator:\n\n"
                ."👤 *Nama Pemohon:* Andi Pratama (SIMULASI UJI COBA)\n"
                ."🔑 *Username:* andi_pratama_test\n"
                ."📱 *No. WhatsApp:* 081234567890\n"
                ."📝 *Alasan Penolakan:* Penulisan nama lengkap belum sesuai KTP.\n"
                ."👮 *Ditolak Oleh:* Admin HRD MGK\n"
                ."⏰ *Waktu Keputusan:* {$time}\n\n"
                ."🛡️ *Status Berkas:* MENUNGGU PERBAIKAN / DAFTAR ULANG ⚠️\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_',

            'deactivated' => "🔒 *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: AKUN KARYAWAN DINONAKTIFKAN (DE-PROVISIONED)*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Hak akses sistem presensi & operasional karyawan telah dicabut:\n\n"
                ."👤 *Nama Karyawan:* Budi Santoso (SIMULASI UJI COBA)\n"
                ."🆔 *NIP / NIK:* EMP-2601012\n"
                ."🏢 *Cabang / Unit:* Cabang Utama MGK\n"
                ."📝 *Alasan Penonaktifan:* Selesai masa kontrak / Resign\n"
                ."👮 *Dieksekusi Oleh:* Admin HRD MGK\n"
                ."⏰ *Waktu Eksekusi:* {$time}\n\n"
                ."🛡️ *Status Akun:* NON-AKTIF / AKSES DICABUT 🔴\n"
                ."Karyawan yang bersangkutan tidak dapat lagi melakukan login di seluruh portal MGK.\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_',

            'reactivated' => "🔓 *[SECURITY & HRD ALERT MGK]*\n"
                ."*UPDATE STATUS: AKUN KARYAWAN DIAKTIFKAN KEMBALI*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Hak akses sistem presensi & operasional karyawan telah dipulihkan:\n\n"
                ."👤 *Nama Karyawan:* Budi Santoso (SIMULASI UJI COBA)\n"
                ."🆔 *NIP / NIK:* EMP-2601012\n"
                ."🏢 *Cabang / Unit:* Cabang Utama MGK\n"
                ."👮 *Diaktifkan Oleh:* Admin HRD MGK\n"
                ."⏰ *Waktu Eksekusi:* {$time}\n\n"
                ."🛡️ *Status Akun:* AKTIF KEMBALI 🟢\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_',

            default => "🟢 *[SECURITY & HRD ALERT MGK — UJI KONEKSI GATEWAY]*\n"
                ."*VERIFIKASI INTEGRASI WHATSAPP FONNTE*\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                ."Pesan ini dikirim secara otomatis untuk memverifikasi koneksi langsung antara server aplikasi *Maya Graha Kencana* dengan Grup WhatsApp Admin/HRD.\n\n"
                ."📡 *Status Gateway:* TERHUBUNG (CONNECTED)\n"
                ."🎯 *Target Group ID:* {$target}\n"
                ."⏰ *Waktu Pengujian:* {$time}\n\n"
                ."✅ *Modul Notifikasi Aktif:*\n"
                ."• Peringatan Lockout / Gagal Login Berulang\n"
                ."• Pengajuan, Persetujuan & Penolakan Registrasi Karyawan\n"
                ."• Penonaktifan (De-provision) & Pengaktifan Kembali Akun\n"
                ."• Permohonan, Persetujuan & Penolakan Reset PIN/Password\n"
                ."• Pengajuan Koreksi Presensi (Lupa Absen)\n"
                ."━━━━━━━━━━━━━━━━━━━━\n"
                .'_Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana_',
        };

        $this->info('Mengirim pesan ke Fonnte API...');

        $sent = $notificationService->sendWhatsApp($target, $message);

        if ($sent) {
            $this->info('✅ BERHASIL! Pesan telah dikirim ke WhatsApp Gateway Fonnte.');

            return self::SUCCESS;
        }

        $this->error('❌ GAGAL mengirim pesan ke WhatsApp Gateway. Periksa storage/logs/laravel.log untuk detail respons Fonnte.');

        return self::FAILURE;
    }
}
