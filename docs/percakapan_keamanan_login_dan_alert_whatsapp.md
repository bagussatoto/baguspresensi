# Rangkuman Percakapan & Evaluasi Keamanan Sistem Presensi MGK

**Waktu Sesi:** 29 Oktober 2026  
**Topik Utama:** Peningkatan Keamanan Login, Early Warning Alert Grup WhatsApp, Pengetikan Nama Manual, dan Validasi Sistem

---

## 1. Pokok Permintaan & Kebutuhan Pengguna
1. **Peniadaan Dropdown Nama Karyawan pada Halaman Login:**
   - Menghapus daftar pilihan nama (*dropdown/select*) di seluruh portal login agar identitas karyawan tidak terekspos ke publik atau pengunjung umum.
   - Menggantinya menjadi kolom isian teks bebas di mana karyawan mengetik sendiri Nama Lengkap atau NIK mereka.
2. **Evaluasi Keamanan & Notifikasi Peringatan Dini (Early Warning Alert):**
   - Mendeteksi upaya tebak PIN/Password atau percobaan pembobolan berulang (3x salah berturut-turut).
   - Membekukan sementara akun sasaran (*Lockout berjenjang*).
   - Menghilangkan tautan pembuka blokir instan (*Unlock URL*) dari pesan WhatsApp demi memperketat keamanan.
3. **Integrasi Notifikasi ke Grup WhatsApp:**
   - Setiap ada indikasi ancaman, percobaan pembobolan, maupun pengajuan reset PIN, pemberitahuan resmi wajib dikirimkan ke grup WhatsApp **🚨 Security & HRD Alert MGK**.
4. **Pemeriksaan Bebas Bug & Error:**
   - Memastikan seluruh modul tidak memiliki bug, lulus uji otomatis (Automated Test Suite), dan rapi sesuai standar linter.

---

## 2. Implementasi Teknis & Perubahan yang Diterapkan

### A. Penggantian Dropdown ke Input Teks Bebas
- **Halaman yang Diperbarui:**
  - Portal Presensi Utama (`/bagus/presensi2/login`)
  - Portal Layanan Staf (`/bagus/presensi2/layanan-login`)
  - Kios Kasir Warnet POS (`/bagus/warnet`)
  - Portal Login ERP Admin (`/bagus/auth/login`)
  - Portal Mobile PWA Legacy (`/bagus/presensi/login`)
- **Mekanisme Pencocokan:**
  - Mendukung input Nama Lengkap, Nama Panggilan, NIK (`code`), Username, maupun Email.
  - Pencarian bersifat *case-insensitive* (tidak terpengaruh huruf besar/kecil).

### B. Penghapusan Tautan Pembuka Blokir Cepat (*No Unlock Link*)
- Tautan buka blokir instan pada notifikasi WhatsApp dihapus sepenuhnya.
- Tujuannya adalah mencegah pihak tidak berwenang mengeklik tautan approval jika ponsel pengawas diakses orang lain.
- Pembukaan blokir akun dilakukan melalui otorisasi internal menu Admin atau menunggu masa tenggang pembekuan akun berakhir secara otomatis.

### C. Integrasi Grup WhatsApp 🚨 Security & HRD Alert MGK
Notifikasi otomatis dikirimkan ke grup WhatsApp untuk 3 kategori kejadian penting:
1. **Percobaan Login Tidak Sah (3x Gagal PIN/Password):**
   - Memuat data: Nama Karyawan Sasaran, NIK, Identitas Input, Alamat IP, Jam Kejadian, dan Status Pembekuan Akun.
2. **Upaya Registrasi Akun Mencurigakan (3x Percobaan):**
   - Memuat data: Identitas Sasaran, Username yang dicoba, Alamat IP Pelaku, dan Status Pemblokiran Perangkat.
3. **Permohonan Reset PIN / Password Karyawan (`/presensi2/lupa-password`):**
   - Memuat data: Nama Karyawan, NIK, Cabang, Tipe Kredensial (PIN/Password), dan waktu pengajuan untuk ditinjau oleh Admin HRD.

---

## 3. Format Notifikasi Grup WhatsApp yang Dibakukan

### Format 1: Peringatan Pembobolan Login
```text
🚨 [SECURITY & HRD ALERT MGK]
*PERINGATAN: PERCOBAAN LOGIN TIDAK SAH (3X)*
━━━━━━━━━━━━━━━━━━━━
Terdeteksi aktivitas mencurigakan pada sistem presensi:

👤 Karyawan Sasaran: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🔑 Identitas Input: [Input yang Diketik Pelaku]
🌐 Alamat IP: [Alamat IP Pelaku]
⏰ Waktu: [Tanggal & Waktu Kejadian]

🛡️ Tindakan Sistem: Akun telah dibekukan sementara (Tier [Level] - [Durasi] menit) demi mencegah akses tidak sah.
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

### Format 2: Permohonan Reset PIN Karyawan
```text
🔑 [SECURITY & HRD ALERT MGK]
*PERMOHONAN RESET [PIN / PASSWORD] KARYAWAN*
━━━━━━━━━━━━━━━━━━━━
Karyawan telah mengajukan permohonan pembaruan kredensial:

👤 Nama Karyawan: [Nama Karyawan]
🆔 NIP / NIK: [Kode Karyawan]
🏢 Cabang / Unit: [Nama Cabang]
📌 Tipe Kredensial: [PIN / PASSWORD]
⏰ Waktu Pengajuan: [Tanggal & Jam Pengajuan]

🛡️ Status: Menunggu Otorisasi / Verifikasi Admin
Silakan tinjau pada menu Admin: Master Karyawan > Reset PIN
━━━━━━━━━━━━━━━━━━━━
Notifikasi Otomatis Keamanan Sistem Maya Graha Kencana
```

---

## 4. Evaluasi Potensi Kebobolan (Analisis Keamanan)

- **Ketahanan Teknis:**
  - Serangan brute-force/bot otomatis tertahan oleh sistem *progressive lockout* 3x.
  - Enumerasi data tertutup karena ketiadaan dropdown nama karyawan.
  - Permintaan formulir dilindungi oleh token anti-CSRF dan audit log tersimpan permanen di database.
- **Faktor yang Perlu Diwaspadai:**
  - Rekan kerja yang mengintip saat karyawan mengetik PIN di kios umum (*shoulder surfing*).
  - Penggunaan PIN kombinasi yang terlalu mudah (seperti tanggal lahir).

---

## 5. Hasil Pengujian & Kerapian Kode

- **Pengujian Otomatis:**
  - `EmployeeLoginLockoutWhatsAppAlertTest` (8 tests / 42 assertions): **100% Passed**
  - `AttendanceWebV2PortalTest` (40 tests / 209 assertions): **100% Passed**
  - `AttendanceWebPortalTest` (8 tests / 31 assertions): **100% Passed**
  - `EmployeeAuthTest` (6 tests / 14 assertions): **100% Passed**
  - `AdminAuthMultiRoleTest` (8 tests / 40 assertions): **100% Passed**
  - `InternetCafeTest` (14 tests / 77 assertions): **100% Passed**
- **Kerapian Format:**
  - Kode telah diformat rapi menggunakan Laravel Pint (`vendor/bin/pint --dirty --format agent`).
- **Status Akhir:** Bersih dari bug dan error, siap digunakan di lingkungan produksi.
