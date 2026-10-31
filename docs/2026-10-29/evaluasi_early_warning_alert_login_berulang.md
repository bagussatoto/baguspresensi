# 🛡️ Laporan Evaluasi Fitur: Early Warning Alert pada Kegagalan Login Berulang

**Sistem Presensi Maya Graha Kencana (MGK)**  
*Dokumen Evaluasi Kesiapan & Status Implementasi Keamanan*  
**Tanggal:** 29 Oktober 2026  
**Status Fitur:** **SUDAH TERPASANG & AKTIF (PRODUCTION-READY)**

---

## 📌 1. Ringkasan Status

Berdasarkan audit langsung pada basis kode aplikasi, fitur **Integrasi Notifikasi Peringatan Dini (Early Warning Alert) pada Kegagalan Login Berulang** ternyata **sudah selesai diimplementasikan secara menyeluruh** dan telah dilengkapi unit/feature test otomatis.

Oleh karena itu, sistem tidak memerlukan pembuatan ulang dari awal, melainkan hanya pemeliharaan dan pemastian token gateway pada konfigurasi `.env`.

---

## ⚙️ 2. Komponen Teknis yang Telah Aktif

### A. Pengontrol Login (`app/Http/Controllers/AttendanceWebV2Controller.php`)
Di dalam method `login()`, alur mitigasi brute force bekerja secara bertahap:
1. **Kegagalan ke-1:**  
   Menampilkan alert peringatan dini: *"Password atau PIN salah. Anda memiliki 2 kali kesempatan lagi sebelum akses dikunci."*
2. **Kegagalan ke-2:**  
   Menampilkan alert kritis: *"PERINGATAN TERAKHIR: Percobaan ke-2 gagal! Jika salah 1 kali lagi, akun dan perangkat akan dikunci."*
3. **Kegagalan ke-3 (Lockout Dipicu):**  
   - Mengaktifkan penguncian bertingkat (*Tier 1: 60 detik, Tier 2: 120 detik, Tier 3+: 300 detik*).
   - Menampilkan layar hitung mundur visual (*Lockout Countdown Timer*).
   - Merekam data insiden ke tabel database `audit_logs` (`action = 'portal_login_lockout'`).
   - Memicu pemanggilan service `sendLoginLockoutAlert()`.

---

### B. Layanan Notifikasi Terpusat (`app/Domains/Attendance/Services/EarlyWarningNotificationService.php`)
Service ini mengelola pengiriman multi-channel otomatis:

1. **WhatsApp ke Karyawan Terkait:**
   - Dikirim ke nomor HP karyawan sah yang terdaftar di database.
   - Menginformasikan bahwa ada 3x percobaan login tidak sah terhadap akunnya dari IP tertentu.
   - Memberikan penegasan status penguncian akun untuk ketenangan karyawan.

2. **WhatsApp ke Admin / Grup HRD (Format Opsi A):**
   - Dikirim ke nomor WhatsApp HRD atau Grup WhatsApp Admin.
   - Berisi detail: Nama karyawan sasaran, NIP/Kode, username, alamat IP pelaku, waktu kejadian, dan tautan approval pembukaan blokir jika dibutuhkan darurat.

3. **Telegram Bot Alert:**
   - Dikirim secara real-time ke Channel/Grup Telegram Pengawas IT & HRD.
   - Berisi ringkasan level lockout (*Tier Lockout*), alamat IP, User-Agent perangkat, dan waktu kejadian.

---

## 🧪 3. Pengujian Otomatis (Feature Test)

Fitur ini memiliki suite pengujian khusus pada:
`tests/Feature/EmployeeLoginLockoutWhatsAppAlertTest.php`

Cakupan pengujian yang telah terbukti lolos uji:
* `test_three_failed_login_attempts_triggers_whatsapp_alert_to_admin_with_format_opsi_a`: Verifikasi pengiriman pesan WhatsApp ke admin saat 3x gagal.
* `test_three_failed_login_attempts_triggers_whatsapp_alert_to_employee`: Verifikasi pengiriman pesan peringatan ke WhatsApp karyawan yang bersangkutan.
* `test_lockout_tier_escalation`: Verifikasi eskalasi hukuman durasi lockout dari 1 menit ke level berikutnya.
* `test_audit_log_created_on_lockout`: Verifikasi pencatatan rekam jejak insiden ke tabel database.

---

## 📋 4. Parameter Konfigurasi yang Digunakan (`.env`)

Untuk operasional pengiriman di lingkungan produksi/server riil, sistem mengandalkan variabel lingkungan berikut:

| Kunci Konfigurasi | Deskripsi |
| :--- | :--- |
| `FONNTE_TOKEN` / `WHATSAPP_API_TOKEN` | Token autentikasi gateway WhatsApp (Fonnte) |
| `WHATSAPP_ADMIN_PHONE` | Nomor HP WhatsApp Admin / HRD penerima alert |
| `WHATSAPP_ADMIN_GROUP_ID` | ID Grup WhatsApp Admin (opsional jika dikirim ke grup) |
| `TELEGRAM_BOT_TOKEN` | Token Bot Telegram MGK Security |
| `TELEGRAM_CHAT_ID` | ID Chat / Grup Telegram penerima peringatan dini |

> **Catatan Keamanan:** Pemanggilan API gateway notifikasi telah dibungkus dengan fungsi `rescue()`. Jika internet server atau kuota gateway sedang bermasalah, aplikasi tetap berjalan normal dan aman tanpa memunculkan error 500 ke layar pengguna.
