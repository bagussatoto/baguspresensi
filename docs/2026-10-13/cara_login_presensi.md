# 📱 Panduan Cara Login Presensi Karyawan

**Sistem Presensi Maya Graha Kencana (MGK)**  
Dokumen ini memuat panduan lengkap langkah demi langkah untuk melakukan login ke Portal Presensi Karyawan Mandiri (Mobile / PWA), Kios Kasir Warnet, maupun Panel Admin ERP.

---

## 📌 Daftar Isi
1. [URL & Akses Portal Presensi](#1-url--akses-portal-presensi)
2. [Langkah-Langkah Login Presensi Karyawan (PWA / Smartphone)](#2-langkah-langkah-login-presensi-karyawan-pwa--smartphone)
3. [Memasang ke Layar Utama Smartphone (Add to Home Screen)](#3-memasang-ke-layar-utama-smartphone-add-to-home-screen)
4. [Cara Logout dari Portal Presensi](#4-cara-logout-dari-portal-presensi)
5. [Metode Alternatif: Presensi via Kios Kasir Warnet](#5-metode-alternatif-presensi-via-kios-kasir-warnet)
6. [Login Administrator / HRD (Admin ERP)](#6-login-administrator--hrd-admin-erp)
7. [Troubleshooting & Solusi Masalah Login](#7-troubleshooting--solusi-masalah-login)

---

## 🌐 1. URL & Akses Portal Presensi

Sistem dapat diakses melalui browser perangkat (Google Chrome, Mozilla Firefox, Safari, atau Edge) melalui alamat berikut:

| Akses | URL / Endpoint | Target Pengguna |
| :--- | :--- | :--- |
| **Portal Mobile / PWA** | `http://<alamat-ip-atau-domain>/presensi/login` <br> *(Contoh lokal/LAN: `http://192.168.5.10:8086/bagus/presensi/login` atau `http://localhost:8000/presensi/login`)* | Seluruh staf & operator lapangan via smartphone |
| **Kios Kasir Warnet** | `http://<alamat-ip-atau-domain>/warnet` (Tab 6: Presensi) | Operator kasir warnet di komputer kasir |
| **Admin ERP MGK** | `http://<alamat-ip-atau-domain>/login` &rarr; `/admin/presensi` | Supervisor, Manajer, & HRD |

---

## 📲 2. Langkah-Langkah Login Presensi Karyawan (PWA / Smartphone)

Sistem Presensi Mandiri dirancang **cepat dan tanpa kerumitan kata sandi yang sering lupa**, menggunakan verifikasi identitas terdaftar yang diproteksi oleh **Geofencing GPS dan Foto Selfie Kamera**.

### Langkah 1: Buka Halaman Login
Buka browser di ponsel Anda, lalu ketik atau klik tautan:
```text
http://<host>/presensi/login
```
Anda akan diarahkan ke layar **Portal Presensi Karyawan**.

### Langkah 2: Pilih Identitas Karyawan
1. Pada bagian formulir, klik dropdown **"Pilih Identitas Karyawan"**.
2. Cari dan pilih nama Anda dari daftar karyawan aktif.
   - Format pilihan: `[Nama Karyawan] ([Kode]) — [Jabatan / Role]`
   - Contoh: `Budi Santoso (EMP001) — Operator Warnet`

### Langkah 3: Tekan Tombol Masuk
Klik tombol bertuliskan:
> **🚀 Masuk ke Portal Presensi**

### Langkah 4: Berhasil Masuk (Dashboard Presensi)
* Sistem akan menyimpan sesi aktif Anda di browser ponsel (`presensi_employee_id`).
* Anda akan langsung diarahkan ke halaman utama **Dashboard Presensi** (`/presensi`), yang menampilkan:
  - Kartu Profil & Cabang Penugasan.
  - Kartu Shift Hari Ini (Jam Mulai s/d Jam Selesai).
  - Status Presensi Hari Ini (Belum Hadir / Sudah Clock-In / Selesai).
  - Tombol **"Absen Masuk Sekarang"** atau **"Absen Pulang Sekarang"**.

> **Catatan:** Sesi login akan tetap aktif selama Anda tidak menekan tombol Logout atau membersihkan data browser. Jika Anda membuka kembali tautan `/presensi/login`, sistem otomatis mengalihkan Anda langsung ke dashboard presensi.

---

## 📲 3. Memasang ke Layar Utama Smartphone (Add to Home Screen)

Agar aplikasi presensi mudah dibuka setiap hari seperti aplikasi dari Play Store / App Store tanpa mengetik ulang URL:

### Pada Ponsel Android (Google Chrome):
1. Buka halaman `/presensi` di Google Chrome.
2. Ketuk ikon **titik tiga (⋮)** di pojok kanan atas browser.
3. Pilih opsi **"Tambahkan ke Layar Utama"** (*Add to Home screen*) atau **"Instal Aplikasi"** (*Install app*).
4. Tekan **"Tambah" / "Instal"**.
5. Ikon **Presensi MGK** akan muncul di layar utama smartphone Anda.

### Pada Ponsel iPhone (Apple Safari):
1. Buka halaman `/presensi` di Safari.
2. Ketuk tombol **Bagikan** (ikon kotak dengan panah mengarah ke atas) di bilah bawah.
3. Gulir ke bawah dan pilih **"Tambah ke Layar Utama"** (*Add to Home Screen*).
4. Beri nama (misal: *Presensi MGK*) lalu ketuk **"Tambah"** (*Add*).

---

## 🚪 4. Cara Logout dari Portal Presensi

Jika Anda meminjamkan perangkat kepada rekan kerja lain, atau ingin mengganti akun:

1. Buka halaman **Profil** di bilah navigasi bawah (`/presensi/profil`), atau klik ikon avatar di sudut atas.
2. Gulir ke bagian bawah menu profil.
3. Tekan tombol **"Keluar dari Sesi"** / **Logout** (mengarah ke `/presensi/logout`).
4. Sesi Anda akan dihapus, dan layar akan kembali ke halaman pemilihan karyawan (`/presensi/login`).

---

## 💻 5. Metode Alternatif: Presensi via Kios Kasir Warnet

Khusus operator yang bertugas di meja kasir warnet dan tidak membawa smartphone:

1. Buka browser komputer kasir menuju alamat `/warnet` atau halaman utama aplikasi.
2. Klik **Tab 6: Presensi Karyawan**.
3. Pilih nama karyawan dari daftar kasir/operator yang bertugas.
4. Masukkan PIN atau konfirmasi giliran shift.
5. Klik **"Absen Masuk (Clock-In)"** atau **"Absen Pulang (Clock-Out)"**.
6. Data presensi akan tercatat dengan metode `WebKiosk`.

---

## 🛡️ 6. Login Administrator / HRD (Admin ERP)

Bagi Supervisor, HRD, dan Administrator yang perlu memeriksa rekap presensi, persetujuan lembur, tukar shift, dan cuti:

1. Akses URL: `http://<host>/login`.
2. Masukkan **Username / Email** dan **Password** Admin ERP.
3. Klik tombol **Login**.
4. Setelah masuk, akses menu di bilah navigasi kiri:
   - **Presensi Harian**: `/admin/presensi`
   - **Rekap & Laporan**: `/admin/presensi/rekap`
   - **Persetujuan (Approval)**: `/admin/presensi/approval`

---

## ❓ 7. Troubleshooting & Solusi Masalah Login

### Q1: Nama saya tidak muncul di daftar pilihan karyawan?
* **Penyebab:** Status karyawan di database belum aktif (`is_active = false`), atau Anda belum terdaftar di modul data karyawan.
* **Solusi:** Hubungi Supervisor/HRD agar data karyawan diaktifkan melalui menu Admin ERP (`/admin/karyawan`).

### Q2: Saat membuka `/presensi/login`, layar langsung masuk ke nama orang lain?
* **Penyebab:** Perangkat sebelumnya digunakan oleh rekan kerja dan belum di-logout.
* **Solusi:** Buka menu **Profil** &rarr; klik **Keluar / Logout** (atau buka langsung `/presensi/logout`), kemudian pilih nama Anda sendiri.

### Q3: Setelah login, apakah langsung tercatat hadir?
* **Penyebab:** Login hanya bertindak untuk membuka portal mandiri akun Anda.
* **Solusi:** Untuk mencatat kehadiran, tekan tombol **"Absen Masuk Sekarang"** atau buka menu **"Scan"**, izinkan GPS & Kamera, lakukan foto selfie, lalu tekan **"Kirim Presensi Masuk"**.

### Q4: Muncul pesan error "Lokasi / Kamera tidak diizinkan" setelah login?
* **Penyebab:** Izin Geolocation atau Kamera di browser masih diblokir (*Blocked*).
* **Solusi:**
  - Buka pengaturan perizinan situs pada browser (ikon gembok/setelan di sebelah kiri kolom URL).
  - Ubah izin **Lokasi** dan **Kamera** menjadi **Izinkan (Allow)**.
  - Segarkan (*refresh*) kembali halaman.
