
# Panduan & Dokumentasi Implementasi: Keamanan Registrasi, Otorisasi Reset Password & Cek Status Mandiri

**Tanggal Pengerjaan & Verifikasi:** 25 Oktober 2026  
**Aplikasi:** Portal Presensi Karyawan v2 (MGK)  
**Status Implementasi:** Selesai, Teruji (84 Test Cases Passed), & Siap Operasional ✅  
**Standar Kepatuhan:** ISO/IEC 27001:2022 (WORM Audit Log) & Segregation of Duties (Maker-Checker)  

Dokumen ini mencatat seluruh arsitektur, alur kerja (*workflow*), kepatuhan standar keamanan (ISO 27001, SoD, Maker-Checker), serta panduan pengoperasian teknis dan pengguna yang telah selesai diimplementasikan.

---

## 1. Ikhtisar Implementasi (*Executive Summary*)

Implementasi ini mencakup 5 pilar utama keamanan akun presensi:
1. **Perlindungan Anti-Brute Force & Anti-Spam Registrasi (Early Warning Gateway):**
   * Pelacakan upaya pendaftaran dengan data duplikat/konflik (*Attempt Tracker*).
   * Penguncian otomatis alamat IP selama 5 menit setelah 3 kali kegagalan berturut-turut.
   * Pengiriman peringatan dini (*Early Warning Security Alert*) via WhatsApp kepada pemilik kontak resmi dan Telegram ke Security Admin.
2. **Transformasi Reset Password / PIN (Maker-Checker & SoD):**
   * Menghilangkan mekanisme reset langsung (*direct self-reset*).
   * Karyawan bertindak sebagai **Maker** (mengajukan permohonan reset kredensial ke antrean `pending`).
   * Admin bertindak sebagai **Checker** (memverifikasi identitas sebelum menyetujui kredensial baru aktif).
3. **Menu Admin: Otorisasi & CRUD Reset Password:**
   * Halaman terpadu untuk memeriksa identitas pemohon (Nama, Kontak WhatsApp, Cabang, Role, IP Address, Perangkat).
   * Tombol verifikasi langsung ke WhatsApp karyawan (`https://wa.me/...`).
   * Fitur CRUD lengkap: **Approve (Setujui)**, **Reject (Tolak dengan Alasan)**, **Edit (Koreksi Catatan Internal / Alasan)**, dan **Delete (Hapus Arsip)**.
   * Pencatatan otomatis ke **Audit Log (ISO 27001 & WORM Guard)** di setiap tindakan Admin.
4. **Halaman Cek Status Permohonan Reset Mandiri Karyawan:**
   * Memungkinkan karyawan memantau status pengajuannya secara mandiri (Pending, Disetujui, atau Ditolak beserta alasan).
5. **Early Warning WhatsApp Alert 3x Gagal Akses & Tautan Approval Buka Blokir (Format Opsi A):**
   * Sistem mendeteksi 3x salah memasukkan password/PIN pada portal login, mengunci akun sementara, dan seketika mengirim WhatsApp peringatan darurat ke Admin HRD/IT.
   * Pesan WhatsApp menyertakan *Signed Magic Link* (HMAC SHA-256 berlaku 30 menit) bagi Admin untuk melakukan verifikasi identitas dan persetujuan pembukaan blokir (*Unlock Approval*) dengan 1 kali klik.

---

## 2. Rincian URL & Rute Sistem

| Modul | Method | URL / Path | Nama Rute | Akses |
| :--- | :--- | :--- | :--- | :--- |
| **Login Presensi v2** | `GET` | `/presensi2/login` | `presensi2.login` | Publik / Karyawan |
| **Form Lupa Password** | `GET` | `/presensi2/lupa-password` | `presensi2.forgot-password` | Publik / Karyawan |
| **Submit Permohonan Reset** | `POST` | `/presensi2/lupa-password` | `presensi2.forgot-password.post` | Publik (Throttle: 5/min) |
| **Cek Status Reset Mandiri** | `GET` | `/presensi2/cek-status-reset` | `presensi2.check-reset-status` | Publik / Karyawan |
| **Query Cek Status Reset** | `POST` | `/presensi2/cek-status-reset` | `presensi2.check-reset-status.query` | Publik (Throttle: 20/min) |
| **Cek Status Pendaftaran** | `GET` | `/presensi2/cek-status` | `presensi2.check-status` | Publik / Calon Karyawan |
| **Admin: Daftar & Otorisasi Reset**| `GET` | `/admin/karyawan-reset-password` | `admin.karyawan.reset-password` | Admin / Checker |
| **Admin: Setujui Reset** | `POST` | `/admin/karyawan-reset-password/{id}/approve` | `admin.karyawan.reset-password.approve` | Admin / Checker |
| **Admin: Tolak Reset** | `POST` | `/admin/karyawan-reset-password/{id}/reject` | `admin.karyawan.reset-password.reject` | Admin / Checker |
| **Admin: Update Catatan (Edit)** | `PUT` | `/admin/karyawan-reset-password/{id}` | `admin.karyawan.reset-password.update` | Admin / Checker |
| **Admin: Hapus Berkas (Delete)** | `DELETE` | `/admin/karyawan-reset-password/{id}` | `admin.karyawan.reset-password.destroy` | Admin / Checker |
| **Admin: Review Buka Blokir (Unlock)** | `GET` | `/admin/karyawan-unlock/{employee}` | `admin.karyawan.unlock.show` | Admin / Checker (HMAC, 30 Min) |
| **Admin: Eksekusi Buka Blokir (Approve)** | `POST` | `/admin/karyawan-unlock/{employee}` | `admin.karyawan.unlock.process` | Admin / Checker (HMAC Token) |

---

## 3. Logika Keamanan & Alur Kerja (*Workflow*)

### A. Alur Maker-Checker Pengajuan Reset Kredensial
```
[ Karyawan (Maker) ]
       │
       ▼
Mengisi Form /presensi2/lupa-password
       │
       ├─► Validasi PIN/Password (Anti Weak PIN: tolak 123456, 654321, dsb.)
       ├─► Simpan ke tabel 'password_reset_requests' (Status: PENDING)
       ├─► Kirim Notifikasi Telegram ke Admin Channel
       └─► Redirect ke Login dengan Notifikasi Sukses
       │
       ▼
[ Admin HRD / Checker ]
       │
       ▼
Buka /admin/karyawan-reset-password
       │
       ├─► Periksa 4 Pilar Identitas: Nama, Nomor HP, Cabang & Role, IP & Device
       ├─► Klik Tombol WhatsApp untuk konfirmasi dua arah ke karyawan
       │
       ├─► [ PILIHAN 1: SETUJUI ]
       │      ├─► Update kredensial baru ke Master Data Karyawan (tabel employees)
       │      ├─► Ubah status menjadi 'approved'
       │      └─► Catat EMPLOYEE_PASSWORD_RESET_APPROVED ke Audit Log
       │
       ├─► [ PILIHAN 2: TOLAK ]
       │      ├─► Isi alasan penolakan (misal: "Nomor kontak tidak aktif")
       │      ├─► Ubah status menjadi 'rejected'
       │      └─► Catat EMPLOYEE_PASSWORD_RESET_REJECTED ke Audit Log
       │
       ├─► [ PILIHAN 3: EDIT CATATAN ]
       │      ├─► Perbarui catatan verifikasi internal atau revisi alasan
       │      └─► Catat EMPLOYEE_PASSWORD_RESET_UPDATED ke Audit Log
       │
       └─► [ PILIHAN 4: HAPUS ARSIP ]
              ├─► Hapus permohonan yang tidak valid/spam
              └─► Catat EMPLOYEE_PASSWORD_RESET_DELETED ke Audit Log
```

### B. Alur Cek Status Permohonan Mandiri (Self-Service Status Tracking)
1. Karyawan membuka `/presensi2/cek-status-reset`.
2. Memasukkan Nama Lengkap, Kode/NIP, atau Nomor WhatsApp terdaftar.
3. Sistem merespons dengan kartu visual:
   * **Pending (Kuning):** *"Menunggu Otorisasi Admin ⏳"* lengkap dengan waktu pengajuan dan estimasi.
   * **Approved (Hijau):** *"Telah Disetujui & Aktif ✅"* lengkap dengan tombol langsung login.
   * **Rejected (Merah):** *"Permohonan Ditolak ❌"* menampilkan catatan alasan admin dan tombol ajukan ulang.

---

## 4. Struktur Database & Model

### Tabel `password_reset_requests`
* `id` (bigint, primary key)
* `employee_id` (foreign key ke `employees.id`, cascade delete)
* `employee_name` (string)
* `employee_code` (string, nullable)
* `phone` (string, nullable)
* `requested_type` (string, default: `credential`)
* `new_credential` (string, hashed bcrypt)
* `status` (string: `pending`, `approved`, `rejected`, `expired`)
* `notes` (text, nullable - catatan admin internal)
* `rejection_reason` (text, nullable - alasan penolakan)
* `approved_by` (foreign key ke `users.id`, nullable)
* `approved_at` (timestamp, nullable)
* `ip_address` (string, nullable)
* `user_agent` (text, nullable)
* `created_at` & `updated_at` (timestamps)

---

## 5. Kepatuhan Audit ISO 27001 & WORM Guard

Setiap kali terjadi perubahan pada modul kredensial dan kontrol penguncian akun, sistem memicu pencatatan audit log otomatis (`App\Domains\Attendance\Models\AuditLog`):
* **Immutability (WORM):** Tabel log audit dilindungi oleh *Immutability Guard* (`audit.immutability_worm_guard`), mencegah update/mutasi pada log audit yang sudah tersimpan.
* **Tindakan yang Dicatat:**
  1. `EMPLOYEE_PASSWORD_RESET_APPROVED` (Persetujuan reset kredensial oleh Admin)
  2. `EMPLOYEE_PASSWORD_RESET_REJECTED` (Penolakan reset kredensial oleh Admin)
  3. `EMPLOYEE_PASSWORD_RESET_UPDATED` (Pembaruan catatan internal permohonan)
  4. `EMPLOYEE_PASSWORD_RESET_DELETED` (Penghapusan arsip permohonan reset)
  5. `portal_login_lockout` (Perekaman insiden 3x gagal login, IP pelaku, dan durasi penguncian)
  6. `EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN` (Pembukaan blokir akun karyawan oleh Admin via otorisasi WhatsApp)
* **Data Log Forensik:** Menyimpan ID admin pemroses, IP address, user agent, waktu presisi, serta perbandingan nilai lama (`old_values`) dan nilai baru (`new_values`).

---

## 6. Verifikasi Pengujian Otomatis (*Automated Test Suite*)

Modul ini telah teruji dengan suite pengujian otomatis Laravel PHPUnit:
* **Test Suite 1:** `tests/Feature/EmployeePasswordResetApprovalTest.php`
  * Cakupan: Alur Maker-Checker Reset Kredensial, Verifikasi Admin, CRUD, dan Cek Status Mandiri.
  * Hasil: **8 Passed, 37 Assertions, 100% Green**.
* **Test Suite 2:** `tests/Feature/EmployeeLoginLockoutWhatsAppAlertTest.php`
  * Cakupan:
    1. `test_three_failed_login_attempts_triggers_whatsapp_alert_to_admin_with_format_opsi_a` (Pemicu 3x gagal login mengirim WhatsApp ke Admin HRD/IT dengan format Opsi A).
    2. `test_admin_can_view_unlock_approval_page_with_valid_hmac_token` (Admin dapat membuka halaman approval via link bertanda tangan kriptografi).
    3. `test_unlock_approval_rejects_expired_or_tampered_token` (Sistem menolak token yang telah kedaluwarsa >30 menit atau dimodifikasi).
    4. `test_admin_can_approve_unlock_and_employee_can_immediately_login` (Admin menyetujui pembukaan blokir, rate limiter ter-reset, audit log tercatat, dan karyawan langsung dapat login).
  * Hasil: **4 Passed, 27 Assertions, 100% Green**.

---

## 7. Peringatan Dini WhatsApp 3x Gagal Akses & Otorisasi Buka Blokir (Format Opsi A)

### A. Alur Kerja Deteksi & Pengiriman Peringatan
1. **Deteksi Percobaan Gagal:** Ketika seseorang (baik pihak lain maupun pemilik akun) salah memasukkan PIN/Password sebanyak 3 kali berturut-turut pada portal login presensi:
   * Akun dan perangkat IP terkait seketika dibekukan sementara (*Temporary Lockout*).
   * Sistem mencatat log audit `portal_login_lockout` ke tabel `audit_logs` (ISO 27001 WORM).
2. **Penerbitan Token HMAC Sekali Pakai (One-Time Magic Link):**
   * Dibuat token otorisasi kriptografi HMAC SHA-256 yang mengikat `employee_id`, `ip_address`, dan `expires` (berlaku maksimal 30 menit).
   * URL otorisasi dibentuk: `http://192.168.5.10:8086/bagus/admin/karyawan-unlock/{employee}?ip={ip}&expires={expires}&token={token}`.
3. **Pengiriman WhatsApp ke Admin HRD / IT (Format Opsi A):**
   * Layanan `EarlyWarningNotificationService` mengirimkan pesan peringatan darurat ke nomor WhatsApp Admin (`WHATSAPP_ADMIN_PHONE`).

### B. Format Resmi Pesan WhatsApp ke Admin HRD / IT
```text
🚨 *[SECURITY ALERT: PERCOBAAN TIDAK SAH 3X]*

Halo Admin HRD / IT,
Terdeteksi aktivitas mencurigakan pada sistem presensi:
• Karyawan Sasaran: *Dimas Wahyu Pratama* (NIP: *MGK-014*)
• Username: `dimas_wahyu`
• Indikasi: Percobaan akses gagal 3 kali berturut-turut
• Alamat IP: `192.168.1.45`
• Waktu: 25 Okt 2026, 13:40:00

Status Saat Ini: Akun DIBEKUKAN sementara demi keamanan.

👉 *Tindakan Admin:*
Jika karyawan telah mengonfirmasi bahwa ini adalah dirinya sendiri yang lupa PIN/password, silakan klik tautan di bawah untuk melakukan APPROVE / BUKA BLOKIR:
http://192.168.5.10:8086/bagus/admin/karyawan-unlock/14?ip=192.168.1.45&expires=1792911000&token=a8f93bc...

(Tautan berlaku selama 30 menit)
```

### C. Alur Otorisasi Admin & Notifikasi Balik ke Karyawan
1. **Pemeriksaan oleh Admin:** Admin mengklik tautan di pesan WhatsApp, lalu memeriksa identitas karyawan dan IP pelaku pada halaman `admin/karyawan-unlock/{employee}`.
2. **Konfirmasi 2 Arah:** Halaman menyediakan tombol langsung `💬 Hubungi WA` untuk menelepon/chat karyawan bersangkutan.
3. **Eksekusi Persetujuan (Approve):** Admin menekan tombol hijau **"SETUJUI & BUKA BLOKIR SEKARANG (APPROVE)"**.
4. **Pembukaan Akses Seketika:** Sistem membersihkan kunci pembatasan (*RateLimiter & Tier Cache*), mencatat log audit `EMPLOYEE_LOCKOUT_UNLOCKED_BY_ADMIN`, dan mengirim pesan konfirmasi WhatsApp ke karyawan bahwa akunnya telah diaktifkan kembali.

