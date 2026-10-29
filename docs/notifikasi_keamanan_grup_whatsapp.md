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

### D. Pengajuan Registrasi Karyawan Baru (Otorisasi Approve HRD)
Pemicu: Pendaftaran akun baru yang membutuhkan verifikasi berkas dan persetujuan:

```text
📋 [PENDAFTARAN KARYAWAN BARU - PERLU APPROVE]

Calon karyawan telah mengajukan pendaftaran mandiri:
• Nama Lengkap: [Nama Lengkap]
• Username: [Username]
• No. WhatsApp: [Nomor WhatsApp]
• Waktu: [Tanggal & Waktu Pendaftaran]

👉 Aksi Diperlukan:
Silakan buka menu Verifikasi Karyawan untuk meninjau data KTP/kontak dan menyetujui (Approve):
http://192.168.5.10:8086/bagus/admin/karyawan-verifikasi
```

---

## 4. Hasil Verifikasi & Pengujian

- **Unit & Feature Test:** Seluruh pengujian notifikasi lockout dan penanganan pesan alert diuji melalui pengujian otomatis dan berstatus **100% Passed**.
- **Standar Format:** Kode layanan notifikasi (`EarlyWarningNotificationService`) telah diformat rapi sesuai standar Laravel Pint.
- **Audit Log:** Seluruh peristiwa lockout tetap tercatat di tabel audit log database untuk keperluan investigasi keamanan lebih lanjut.
