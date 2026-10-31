# Dokumentasi Integrasi Notifikasi Grup WhatsApp: 🚨 Security & HRD Alert MGK

Dokumen ini mencatat konfigurasi, standarisasi, dan format pesan notifikasi keamanan yang dikirimkan secara otomatis ke grup WhatsApp **🚨 Security & HRD Alert MGK** ketika terjadi indikasi ancaman, percobaan pembobolan, maupun aktivitas mencurigakan pada sistem presensi Maya Graha Kencana.

---

## 1. Latar Belakang & Kebijakan Keamanan

Untuk memperkuat pertahanan berlapis (*Defense-in-Depth*) dan pencegahan kebocoran akses:
1. **Peniadaan Tautan Pembuka Kunci (*No Direct Unlock Link*):**
   - Tautan URL pembuka blokir instan di pesan WhatsApp ditiadakan guna mencegah risiko penyalahgunaan jika ponsel pengawas diakses pihak tidak berwenang.
   - Pembukaan blokir akun dilakukan melalui otorisasi internal atau menunggu masa pembekuan akun selesai secara otomatis.
2. **Kerahasiaan Identitas Karyawan:**
   - Dropdown list nama pada semua portal login (Presensi Mobile, Kasir POS/Warnet, ERP Admin) telah dihapus dan diganti dengan input teks bebas.
3. **Peringatan Real-Time ke Grup:**
   - Setiap ada percobaan yang mencurigakan (3x gagal berturut-turut), notifikasi dikirim langsung ke grup pengawas keamanan.

---

## 2. Saluran Pengiriman Notifikasi

Notifikasi dikirim melalui integrasi WhatsApp Gateway resmi dengan memprioritaskan target grup:
- **Konfigurasi Target:** Berdasarkan variabel `WHATSAPP_ADMIN_GROUP_ID` pada berkas lingkungan (.env).
- **Fallback Target:** Jika grup tidak disetel, sistem mengalihkan pengiriman ke nomor kontak admin utama (`WHATSAPP_ADMIN_PHONE`).

---

## 3. Format Notifikasi yang Diterapkan

### A. Percobaan Login Berulang Tidak Sah (3x Salah Kredensial)
Pemicu: Terjadi 3 kali kegagalan memasukkan PIN/Password pada suatu akun karyawan. Sistem langsung mengunci akun dan memicu alert ke grup:

```text
🚨 [SECURITY & HRD ALERT MGK]
*PERINGATAN: PERCOBAAN LOGIN TIDAK SAH (3X)*
━━━━━━━━━━━━━━━━━━━━
Terdeteksi aktivitas mencurigakan pada sistem presensi:

👤 Karyawan Sasaran: [Nama Karyawan]
🆔 NIP / NIK: [Kode / NIP Karyawan]
🔑 Identitas Input: [Input yang Diketik Pelaku]
🌐 Alamat IP: [Alamat IP Pelaku]
⏰ Waktu: [Tanggal & Waktu Kejadian]

🛡️ Tindakan Sistem: Akun telah dibekukan sementara (Tier [Level] - [Durasi] menit) demi mencegah akses tidak sah.
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### B. Upaya Registrasi Duplikasi / Manipulasi Identitas (3x Percobaan)
Pemicu: Terdeteksi upaya pendaftaran ulang berulang kali menggunakan NIK/data karyawan aktif yang sudah terdaftar:

```text
🚨 [SECURITY & HRD ALERT MGK]
*PERINGATAN: UPAYA REGISTRASI MENCURIGAKAN (3X)*
━━━━━━━━━━━━━━━━━━━━
Terdeteksi upaya registrasi berulang menggunakan data identitas karyawan aktif:

👤 Sasaran Identitas: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🔑 Username Dicoba: [Username yang Dimasukkan]
🌐 Alamat IP: [Alamat IP Pelaku]
⏰ Waktu: [Tanggal & Waktu Kejadian]

🛡️ Tindakan Sistem: Perangkat IP penyerang telah diblokir sementara ([Durasi] menit).
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### C. Permohonan Reset PIN / Password Karyawan
Pemicu: Karyawan mengajukan permohonan pembaruan kata sandi/PIN mandiri melalui portal presensi (`/presensi2/lupa-password`):

```text
🔑 [SECURITY & HRD ALERT MGK]
*PERMOHONAN RESET [PIN / PASSWORD] KARYAWAN*
━━━━━━━━━━━━━━━━━━━━
Karyawan telah mengajukan permohonan pembaruan kredensial:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🏢 Cabang / Unit: [Nama Cabang]
📌 Tipe Kredensial: [PIN / PASSWORD]
⏰ Waktu Pengajuan: [Tanggal & Waktu Pengajuan]

🛡️ Status: Menunggu Otorisasi / Verifikasi Admin
Silakan tinjau pada menu Admin: Master Karyawan > Reset PIN
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### D. Pengajuan Registrasi Karyawan Baru / Daftar Ulang (Otorisasi Approve HRD)
Pemicu: Pendaftaran akun baru atau daftar ulang (revisi data) yang membutuhkan verifikasi berkas dan persetujuan Admin HRD:

```text
📋 [SECURITY & HRD ALERT MGK]
*PENGAJUAN REGISTRASI KARYAWAN (PERLU VERIFIKASI)*
━━━━━━━━━━━━━━━━━━━━
Calon karyawan telah mengajukan pendaftaran akun mandiri:

👤 Nama Lengkap: [Nama Calon Karyawan]
🔑 Username: [Username]
📱 No. WhatsApp: [Nomor WhatsApp]
✉️ Alamat Email: [Email Pendaftar]
🌐 Alamat IP: [IP Perangkat Pendaftar]
📌 Jenis Pengajuan: [Pendaftaran Baru / Daftar Ulang (Revisi Data)]
⏰ Waktu Pengajuan: [Tanggal & Waktu Pengajuan]

🛡️ Status: Menunggu Otorisasi / Verifikasi Admin HRD
Silakan tinjau pada menu Admin: Master Karyawan > Verifikasi Karyawan
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### E. Update Status: Registrasi Karyawan Disetujui (Approved)
Pemicu: Admin HRD / Checker menyetujui pendaftaran karyawan mandiri pada menu Verifikasi Karyawan. Selain mengirim detail NIK baru ke WhatsApp karyawan terkait, sistem mengirim pembaruan status ke grup:

```text
✅ [SECURITY & HRD ALERT MGK]
*UPDATE STATUS: REGISTRASI KARYAWAN DISETUJUI*
━━━━━━━━━━━━━━━━━━━━
Pengajuan pendaftaran karyawan telah diverifikasi dan diaktifkan:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK Baru: [Kode Karyawan]
🔑 Username: [Username]
🏢 Cabang Penempatan: [Nama Cabang]
💼 Peran / Jabatan: [Jabatan / Role]
👮 Disetujui Oleh: [Nama Admin Checker]
⏰ Waktu Otorisasi: [Tanggal & Waktu Otorisasi]

🛡️ Status Akun: AKTIF DI MASTER DATA 🟢
Karyawan yang bersangkutan sudah dapat melakukan login dan presensi.
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### F. Update Status: Registrasi Karyawan Ditolak / Perlu Revisi (Rejected)
Pemicu: Admin HRD / Checker menolak pengajuan pendaftaran mandiri dengan catatan perbaikan. Selain mengirim alasan dan tautan daftar ulang ber-token HMAC ke WhatsApp calon karyawan, sistem mengirim pembaruan status ke grup:

```text
🚫 [SECURITY & HRD ALERT MGK]
*UPDATE STATUS: REGISTRASI KARYAWAN DITOLAK / PERLU REVISI*
━━━━━━━━━━━━━━━━━━━━
Pengajuan pendaftaran karyawan telah ditolak oleh verifikator:

👤 Nama Pemohon: [Nama Calon Karyawan]
🔑 Username: [Username]
📱 No. WhatsApp: [Nomor WhatsApp]
📝 Alasan Penolakan: [Catatan Alasan dari Admin]
👮 Ditolak Oleh: [Nama Admin Checker]
⏰ Waktu Keputusan: [Tanggal & Waktu Keputusan]

🛡️ Status Berkas: MENUNGGU PERBAIKAN / DAFTAR ULANG ⚠️
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### G. Update Status: Akun Karyawan Dinonaktifkan (De-provisioned / Resign)
Pemicu: Admin menonaktifkan akun karyawan (mencabut hak akses) pada menu Master Karyawan:

```text
🔒 [SECURITY & HRD ALERT MGK]
*UPDATE STATUS: AKUN KARYAWAN DINONAKTIFKAN (DE-PROVISIONED)*
━━━━━━━━━━━━━━━━━━━━
Hak akses sistem presensi & operasional karyawan telah dicabut:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🏢 Cabang / Unit: [Nama Cabang]
📝 Alasan Penonaktifan: [Alasan / Keterangan Resign]
👮 Dieksekusi Oleh: [Nama Admin]
⏰ Waktu Eksekusi: [Tanggal & Waktu Eksekusi]

🛡️ Status Akun: NON-AKTIF / AKSES DICABUT 🔴
Karyawan yang bersangkutan tidak dapat lagi melakukan login di seluruh portal MGK.
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### H. Update Status: Akun Karyawan Diaktifkan Kembali (Reactivated)
Pemicu: Admin mengaktifkan kembali akun karyawan yang sebelumnya non-aktif:

```text
🔓 [SECURITY & HRD ALERT MGK]
*UPDATE STATUS: AKUN KARYAWAN DIAKTIFKAN KEMBALI*
━━━━━━━━━━━━━━━━━━━━
Hak akses sistem presensi & operasional karyawan telah dipulihkan:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🏢 Cabang / Unit: [Nama Cabang]
👮 Diaktifkan Oleh: [Nama Admin]
⏰ Waktu Eksekusi: [Tanggal & Waktu Eksekusi]

🛡️ Status Akun: AKTIF KEMBALI 🟢
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

### I. Update Status: Permohonan Reset PIN / Password Disetujui & Ditolak
Pemicu: Admin HRD menyetujui atau menolak permohonan pembaruan PIN/Password karyawan pada menu Reset PIN. Sistem mengirimkan pemberitahuan ke WhatsApp karyawan serta pembaruan status ke grup:

**1. Saat Disetujui (Approved):**
```text
✅ [SECURITY & HRD ALERT MGK]
*UPDATE STATUS: RESET [PIN / PASSWORD] KARYAWAN DISETUJUI*
━━━━━━━━━━━━━━━━━━━━
Permohonan pembaruan kredensial karyawan telah disetujui:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🏢 Cabang / Unit: [Nama Cabang]
📌 Tipe Kredensial: [PIN / PASSWORD]
👮 Disetujui Oleh: [Nama Admin Checker]
⏰ Waktu Otorisasi: [Tanggal & Waktu Otorisasi]

🛡️ Status: KREDENSIAL BARU AKTIF 🟢
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

**2. Saat Ditolak (Rejected):**
```text
🚫 [SECURITY & HRD ALERT MGK]
*UPDATE STATUS: RESET [PIN / PASSWORD] KARYAWAN DITOLAK*
━━━━━━━━━━━━━━━━━━━━
Permohonan pembaruan kredensial karyawan telah ditolak:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🏢 Cabang / Unit: [Nama Cabang]
📌 Tipe Kredensial: [PIN / PASSWORD]
📝 Alasan Penolakan: [Catatan Alasan dari Admin]
👮 Ditolak Oleh: [Nama Admin Checker]
⏰ Waktu Keputusan: [Tanggal & Waktu Keputusan]

🛡️ Status: DITOLAK / KREDENSIAL LAMA TETAP BERLAKU ⚠️
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

## 4. Panduan Konfigurasi Live `.env`, CLI Uji Coba (`whatsapp:test-alert`) & Troubleshooting Fonnte

### A. Konfigurasi Variabel `.env`
Pastikan variabel berikut terisi pada file `.env` aplikasi utama:

```dotenv
# ==============================================================================
# NOTIFIKASI EARLY WARNING & WHATSAPP GATEWAY
# ==============================================================================
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_API_TOKEN=ENZ1fdpjfM1deC4AkAfB
WHATSAPP_ADMIN_PHONE=085183324076
WHATSAPP_ADMIN_GROUP_ID=120363412767536329@g.us
```

### B. Perintah Artisan Uji Coba Kirim Pesan Nyata (`whatsapp:test-alert`)
Tersedia command [`app/Console/Commands/TestWhatsAppAlertCommand.php`](../app/Console/Commands/TestWhatsAppAlertCommand.php) untuk menguji pengiriman pesan langsung ke Grup WhatsApp **`🚨 Security & HRD Alert MGK`** maupun ke nomor tertentu:

```bash
# 1. Uji koneksi umum (Ping Gateway)
php artisan whatsapp:test-alert --type=ping

# 2. Simulasi Pengajuan Registrasi Karyawan Baru
php artisan whatsapp:test-alert --type=registration

# 3. Simulasi Registrasi Disetujui (Approved)
php artisan whatsapp:test-alert --type=approved

# 4. Simulasi Registrasi Ditolak / Perlu Revisi (Rejected)
php artisan whatsapp:test-alert --type=rejected

# 5. Simulasi Akun Karyawan Dinonaktifkan (De-provisioned / Resign)
php artisan whatsapp:test-alert --type=deactivated

# 6. Simulasi Akun Karyawan Diaktifkan Kembali (Reactivated)
php artisan whatsapp:test-alert --type=reactivated

# Opsional: Kirim uji coba ke nomor WA perorangan tertentu
php artisan whatsapp:test-alert --type=ping --target=085183324076
```

### C. Troubleshooting Error Fonnte: `"request invalid on disconnected device"`
Apabila saat menjalankan `php artisan whatsapp:test-alert` muncul pesan gagal dan pada file `storage/logs/laravel.log` tercatat respons:
```json
{"reason":"request invalid on disconnected device","status":false}
```

**Penyebab:**
Sesi perangkat WhatsApp pengirim yang terhubung dengan token API di dashboard Fonnte sedang terputus (*Disconnected*).

**Langkah Perbaikan:**
1. Buka dashboard **[https://md.fonnte.com](https://md.fonnte.com)** dan masuk ke menu **Device**.
2. Periksa status perangkat yang menggunakan token API terkait.
3. Jika berstatus **Disconnect**, klik tombol **Connect** lalu pindai (*scan*) QR Code menggunakan aplikasi WhatsApp pada HP pengirim (**Perangkat Tertaut / Linked Devices**).
4. Setelah status perangkat kembali **Connected**, jalankan ulang perintah `php artisan whatsapp:test-alert --type=ping`.

### D. Isolasi Lingkungan Pengujian Otomatis (`phpunit.xml`)
Agar saat menjalankan `php artisan test` sistem tidak mengirim pesan uji coba (*spam*) ke grup WhatsApp asli dan tidak terhambat *timeout* jaringan eksternal, variabel gateway dikosongkan secara otomatis di dalam [`phpunit.xml`](../phpunit.xml):

```xml
<env name="WHATSAPP_API_URL" value=""/>
<env name="WHATSAPP_API_TOKEN" value=""/>
<env name="TELEGRAM_BOT_TOKEN" value=""/>
<env name="TELEGRAM_ADMIN_CHAT_ID" value=""/>
```

---

## 5. Hasil Verifikasi & Pengujian

- **Unit & Feature Test:** Seluruh pengujian notifikasi lockout, registrasi, approval, rejection, de-provisioning, dan reset PIN diuji melalui pengujian otomatis (`RegistrationBreachAndApprovalNotificationTest`, `EmployeeRegistrationMakerCheckerTest`, `RegistrationEarlyWarningAndUniqueValidationTest`) dan berstatus **100% Passed (43 tests / 307 assertions)**.
- **Standar Format:** Kode layanan notifikasi (`EarlyWarningNotificationService`) dan command CLI (`TestWhatsAppAlertCommand`) telah diformat rapi sesuai standar Laravel Pint.
- **Audit Log & SLA Antrean:** Seluruh peristiwa tercatat di tabel `audit_logs` database, dan pengajuan `pending` yang melewati batas 3 hari otomatis ditandai sebagai `expired` (`Kedaluwarsa > 3 Hari`).
