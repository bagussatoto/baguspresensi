# Dokumentasi Fitur Registrasi Mandiri, Lupa Password & Keamanan Akun Portal Presensi

**Tanggal Pembaruan:** 24 Oktober 2026  
**Aplikasi:** Portal Presensi Karyawan v2 (MGK)  
**Dokumen Rujukan Awal:** `docs/registrasi diperlukan.txt`

---

## 1. Ringkasan Implementasi Fitur

Seluruh kebutuhan dan peningkatan fitur yang diajukan telah selesai diimplementasikan, diintegrasikan, dan diverifikasi melalui pengujian otomatis (61 pengujian fitur lulus 100%).

| No | Modul / Fitur | Lokasi Berkas Utama | Status |
|---|---|---|---|
| 1 | **Lupa Password: Input Nama Manual** (Tanpa dropdown/tabel karyawan) | `resources/views/presensi2/forgot-password.blade.php`, `AttendanceWebV2Controller.php` | Selesai & Teruji |
| 2 | **Lupa Password: Wajib Input Password/PIN Lama** (Mencegah pengambilalihan akun) | `resources/views/presensi2/forgot-password.blade.php`, `AttendanceWebV2Controller.php` | Selesai & Teruji |
| 3 | **Checklist ✅ Ketersediaan Data & Notifikasi Detail Satu Persatu** | `resources/views/presensi2/register.blade.php` | Selesai & Teruji |
| 4 | **Aturan Kredensial: PIN 6 Digit vs Password 8 Karakter** | `register.blade.php`, `AttendanceWebV2Controller.php` | Selesai & Teruji |
| 5 | **Konfirmasi Kredensial (Ulangi PIN / Password)** | `register.blade.php`, `AttendanceWebV2Controller.php` | Selesai & Teruji |
| 6 | **Deteksi PIN Pasaran / Lemah (Anti-Tebakan Mudah)** | `AttendanceWebV2Controller.php` (`isWeakPin`), `register.blade.php`, `forgot-password.blade.php` | Selesai & Teruji |
| 7 | **Proteksi Honeypot Anti-Spam Bot** | `register.blade.php`, `forgot-password.blade.php`, `AttendanceWebV2Controller.php` | Selesai & Teruji |
| 8 | **Estimasi Waktu Verifikasi (1x24 Jam Kerja)** | `register.blade.php`, `login.blade.php`, `AttendanceWebV2Controller.php` | Selesai & Teruji |
| 9 | **Indikator Kekuatan Password Dinamis (Password Strength Meter)** | `register.blade.php`, `forgot-password.blade.php` | Selesai & Teruji |
| 10 | **Cek Status Pengajuan Akun Mandiri (`/presensi2/cek-status`)** | `resources/views/presensi2/check-status.blade.php`, `AttendanceWebV2Controller.php`, `routes/web.php` | Selesai & Teruji |

---

## 2. Rincian Teknis Per Fitur

### 1. Halaman Lupa Password (`/presensi2/lupa-password`)
- **Penulisan Nama Manual**: Elemen `<select>` yang sebelumnya menampilkan daftar nama seluruh karyawan dihapus. Digantikan dengan input teks manual agar privasi daftar karyawan internal tidak bocor ke publik.
- **Verifikasi Kredensial Lama**: Ditambahkan kolom `old_credential` di atas pembuatan PIN baru. Sistem memverifikasi kecocokan kredensial lama sebelum mengizinkan pembaruan kata sandi, mencegah akun dibobol oleh pihak yang hanya mengetahui nama rekan kerja.

### 2. Formulir Registrasi Mandiri (`/presensi2/register`)
- **Checklist Ketersediaan Data**: Kartu status visual yang menampilkan checklist kelengkapan dan ketersediaan data secara real-time (`Tersedia dan dapat digunakan`).
- **Pemberitahuan Satu Persatu**: Jika ada kolom yang belum diisi atau tidak valid saat tombol submit ditekan, sistem menampilkan kotak peringatan keamanan yang merinci nama kolom beserta alasan kesalahannya satu demi satu, sekaligus memfokuskan kursor ke input yang bermasalah.

### 3. Logika Kredensial (PIN vs Password)
- **PIN Angka Murni**: Jika kredensial hanya berisi digit angka, diwajibkan tepat 6 digit (panjang pas 6). Indikator bulatan 6-titik aktif melacak setiap ketikan.
- **Password Alfanumerik**: Jika kredensial menyertakan huruf atau karakter campuran, diwajibkan minimal 8 karakter.
- **Konfirmasi Kredensial**: Input konfirmasi kredensial mencocokkan input secara langsung dengan umpan balik hijau saat cocok dan merah saat belum sesuai.

### 4. Deteksi PIN Pasaran / Lemah (`isWeakPin`)
Kombinasi 6 angka yang mudah ditebak otomatis ditolak baik di frontend maupun backend:
- Angka kembar semua (`000000`, `111111`, `222222`, dst.).
- Urutan berurutan naik/turun (`123456`, `654321`, `012345`, `987654`, dst.).
- Pola berulang 2 digit atau 3 digit (`121212`, `123123`, `131313`, dst.).
- Pola pasaran umum (`112233`, `123321`, `102030`, dst.).

### 5. Proteksi Honeypot Anti-Spam Bot
- Input tersembunyi `honeypot_website` dipasang pada formulir registrasi dan lupa password.
- Tersembunyi dari penglihatan pengguna manusia (`aria-hidden="true"`, `tabindex="-1"`), namun akan otomatis diisi oleh robot perayap spam.
- Jika terisi, pengajuan langsung dialihkan seolah berhasil (*silent drop*) tanpa mencemari antrean database Maker-Checker.

### 6. Transparansi Estimasi Waktu Verifikasi
- Formulir pendaftaran menyertakan estimasi verifikasi oleh Admin/HR: **1x24 jam kerja (Senin – Sabtu)**.
- Setelah submit, halaman login menampilkan kartu notifikasi khusus yang menegaskan username yang didaftarkan serta waktu estimasi verifikasi.

### 7. Indikator Kekuatan Password Dinamis
- Saat pengguna memilih membuat password alfanumerik (bukan PIN angka), meter visual otomatis aktif menampilkan persentase dan warna bar:
  - *Kurang (< 8 Karakter)*: Merah.
  - *Cukup*: Kuning.
  - *Kuat*: Biru.
  - *Sangat Kuat*: Hijau zamrud dengan perlindungan penuh kombinasi huruf besar, kecil, angka, dan simbol.

### 8. Fitur Cek Status Pengajuan Mandiri (`/presensi2/cek-status`)
- Calon karyawan dapat memantau status verifikasi akunnya secara mandiri hanya dengan memasukkan Username atau Nomor HP/WhatsApp.
- Memberikan informasi akurat:
  - **Menunggu Verifikasi ⏳**: Menampilkan estimasi waktu dan tanggal pengajuan.
  - **Telah Disetujui / Aktif ✅**: Tautan langsung menuju halaman login presensi.
  - **Perlu Perbaikan / Ditolak ⚠️**: Menampilkan catatan alasan penolakan dari Admin/HR dan tombol daftar ulang.
  - **Tidak Ditemukan ❓**: Menampilkan arahan untuk mendaftar akun baru jika data belum pernah diajukan.

---

## 3. Daftar Berkas Terkait

- **Views:**
  - `resources/views/presensi2/register.blade.php`
  - `resources/views/presensi2/login.blade.php`
  - `resources/views/presensi2/forgot-password.blade.php`
  - `resources/views/presensi2/check-status.blade.php`
- **Controller & Routes:**
  - `app/Http/Controllers/AttendanceWebV2Controller.php`
  - `routes/web.php`
- **Database & Model:**
  - `app/Models/EmployeeRegistration.php`
  - `database/migrations/2026_10_24_113812_add_email_to_employee_registrations_table.php`
  - `database/migrations/2026_10_18_171854_create_employee_registrations_table.php`
- **Unit & Feature Tests:**
  - `tests/Feature/RegistrationEarlyWarningAndUniqueValidationTest.php` (15 skenario pengujian)
  - `tests/Feature/EmployeeRegistrationMakerCheckerTest.php` (8 skenario pengujian)
  - `tests/Feature/AttendanceWebV2PortalTest.php` (38 skenario pengujian)

---

## 4. Hasil Verifikasi Pengujian

Pengujian dijalankan melalui PHPUnit dengan hasil:
```
Tests: 61 passed (337 assertions)
Status: SUCCESS (100% Passed)
Code Formatter: Laravel Pint (Passed)
```
Semua fitur bekerja selaras dengan prinsip **Separation of Duties (SoD)**, **Single Source of Truth (SoT)**, dan kepatuhan standar **ISO 27001**.
