# Panduan & Ringkasan Fitur: Portal Presensi Kasir v2 & Mode Simulasi Pengujian

**Dokumen**: Ringkasan Khusus Modul Presensi  
**Tanggal**: 3 November 2026  
**Aplikasi**: MGK Presensi v2 (Maya Graha Kencana)  
**Status**: Aktif & Terverifikasi (Mode Simulasi Pengujian)

---

## 1. Ikhtisar Modul Presensi v2
Modul Presensi v2 dirancang khusus untuk memvalidasi kehadiran operator dan kasir shift di unit cabang MGK secara akurat, aman, dan anti-kecurangan (*fraud prevention*).

Sistem presensi terhubung langsung dengan:
1. **Penjadwalan Shift Otomatis**: Mendeteksi Shift 1, Shift 2, Shift 3, dan Shift 4 berdasarkan jam operasional.
2. **Penentuan Tanggal Kerja Operasional (*Work Date*)**: Jam kerja yang melintasi tengah malam (misal Shift Malam) tetap diakui pada buku kas tanggal shift berjalan.
3. **Prinsip *Gatekeeper* Operasional**: Kasir **wajib melakukan Clock-In (Presensi Masuk)** terlebih dahulu sebelum diperbolehkan membuka checklist stok barang dan modul kasir POS.

---

## 2. Fitur Khusus Mode Simulasi Pengujian Presensi (Terbaru)

Untuk mempermudah pengujian alur presensi oleh kasir, admin, atau supervisor di PC Turet tanpa mengganggu alur penjualan kasir, diterapkan 2 fitur utama:

### A. Penyederhanaan Navigasi Bawah (3 Menu Fokus)
Tampilan navigasi bawah di portal `http://192.168.5.10:8086/bagus/presensi2` saat ini disederhanakan menjadi **3 menu esensial**:
1. **🏠 Beranda Presensi (`/bagus/presensi2`)**:
   - Menampilkan kartu profil operator yang sedang bertugas.
   - Indikator status kehadiran hari ini (Belum Presensi, Sedang Bertugas, atau Selesai).
   - Ringkasan shift aktif dan informasi cabang.
2. **🔐 Presensi Masuk & Keluar (`/bagus/presensi2/scan`)**:
   - Antarmuka verifikasi kehadiran untuk **Clock In (Masuk)** dan **Clock Out (Pulang)**.
   - Pilihan verifikasi kredensial cepat via PIN atau Password.
3. **📅 Riwayat Presensi (`/bagus/presensi2/riwayat`)**:
   - Rekam jejak presensi harian, mingguan, dan bulanan kasir.
   - Mencatat jam masuk, jam pulang, durasi kerja, status ketepatan waktu (Tepat Waktu / Terlambat), dan foto/lokasi verifikasi.

*Catatan: Menu Stok Barang, Penjualan POS, dan Rekap Penjualan disembunyikan sementara agar proses pengujian kehadiran tidak terdistraksi.*

---

### B. Sinkronisasi Jam Lokal PC Turet Kasir (`/bagus/presensi2/scan`)
- **Tantangan Sebelumnya**: Jam server hosting/container Linux mungkin berbeda dengan jam lokal Windows di PC kasir fisik, sehingga pengujian shift sulit disimulasikan.
- **Solusi yang Diterapkan**:
  - Ditambahkan widget live clock yang membaca langsung waktu lokal browser di PC Kasir.
  - Tombol **`⚡ Samakan ke Jam PC Turet`**: Membaca otomatis tanggal, jam, menit, dan detik dari PC kasir lalu menginjeksikannya ke dalam session pengujian (`presensi2_mock_time`).
  - Formulir penyesuaian tanggal & jam manual bagi penguji yang ingin menguji skenario shift malam, pergantian hari, atau keterlambatan.

---

## 3. Langkah Demi Langkah Skenario Pengujian Presensi

Bagi tim kasir atau tester yang ingin melakukan simulasi:

```
[1. Buka Login] ➔ [2. Pilih Operator] ➔ [3. Masuk ke Scan Presensi] ➔ [4. Samakan Jam Turet] ➔ [5. Clock-In] ➔ [6. Cek Riwayat]
```

1. **Langkah 1 (Login)**:
   - Akses: `http://192.168.5.10:8086/bagus/presensi2/login`
   - Pilih nama operator dari daftar atau ketik kode karyawan.
   - Masukkan PIN / Password.
2. **Langkah 2 (Buka Menu Presensi)**:
   - Klik menu **🔐 Presensi** di navigasi bawah (atau akses `/bagus/presensi2/scan`).
3. **Langkah 3 (Sinkronisasi Waktu Simulasi)**:
   - Periksa apakah waktu yang tampil sudah sesuai jam PC Turet.
   - Jika ingin menggunakan jam PC kasir saat ini, klik tombol **`⚡ Samakan ke Jam PC Turet`**.
   - Jika ingin menguji jam tertentu, ketik tanggal dan jam pada kolom simulasi waktu.
4. **Langkah 4 (Lakukan Presensi Masuk / Clock-In)**:
   - Klik tombol hijau **`⚡ Clock In Sekarang`**.
   - Sistem akan memvalidasi shift yang aktif dan mencatat presensi masuk ke database.
5. **Langkah 5 (Verifikasi di Riwayat)**:
   - Buka menu **📅 Riwayat** di navigasi bawah (atau akses `/bagus/presensi2/riwayat`).
   - Pastikan data kehadiran hari ini tercatat dengan jam masuk, shift, dan status yang benar.
6. **Langkah 6 (Lakukan Presensi Pulang / Clock-Out)**:
   - Saat jam shift berakhir, buka kembali menu **🔐 Presensi**.
   - Tombol akan berubah menjadi merah **`🔴 Clock Out Pulang`**.
   - Klik untuk mengakhiri shift bertugas.

---

## 4. Cara Mengembalikan Menu Lengkap (Pasca Simulasi)

Jika simulasi pengujian presensi telah selesai dan ingin mengembalikan kembali seluruh 6 menu (termasuk Stok, Penjualan POS, dan Rekap):

### Lokasi Pengaturan:
Buka berkas:  
`resources/views/layouts/presensi2.blade.php` pada **baris 478**.

### Nilai yang Diubah:
```php
// UBAH NILAI INI:
$modeSimulasiPresensi = true;

// MENJADI:
$modeSimulasiPresensi = false;
```

Setelah diubah menjadi `false`, keenam menu navigasi berikut akan otomatis aktif kembali secara penuh:
1. 🏠 Beranda Presensi
2. 📦 Stok Barang Shift
3. 🔐 Presensi Masuk & Keluar
4. 🛒 Penjualan POS (Mobile)
5. 📊 Rekap Penjualan Shift
6. 📅 Riwayat Presensi

---

## 5. Struktur Tabel Database Navicat yang Terkait

Setiap aksi pada portal presensi ini terhubung langsung ke tabel-tabel PostgreSQL di Navicat:

| Tabel di Navicat | Fungsi & Data yang Disimpan |
| :--- | :--- |
| **`attendance_records`** | Data kehadiran: `employee_id`, `branch_id`, `shift_id`, `work_date`, `clock_in_at`, `clock_out_at`, `status` (`present`/`late`), koordinat/foto. |
| **`attendance_shifts`** | Master jam kerja shift: nama shift (Shift 1 s/d 4), jam mulai, jam selesai, toleransi keterlambatan. |
| **`employees`** | Profil staf kasir: nama, kode pegawai, PIN terenkripsi, password, hak akses, cabang penugasan. |
| **`branches`** | Master cabang outlet tempat kasir bertugas (e.g. MAYA 1). |
| **`system_menus`** | Konfigurasi menu navigasi presensi (`presensi2_beranda`, `presensi2_stok`, `presensi2_penjualan`, dsb). |

---

## 6. Daftar Rute URL Terkait Presensi

- **Beranda Presensi**: [http://192.168.5.10:8086/bagus/presensi2](http://192.168.5.10:8086/bagus/presensi2)
- **Login Presensi**: [http://192.168.5.10:8086/bagus/presensi2/login](http://192.168.5.10:8086/bagus/presensi2/login)
- **Scan / Verifikasi Presensi**: [http://192.168.5.10:8086/bagus/presensi2/scan](http://192.168.5.10:8086/bagus/presensi2/scan)
- **Riwayat Presensi Kasir**: [http://192.168.5.10:8086/bagus/presensi2/riwayat](http://192.168.5.10:8086/bagus/presensi2/riwayat)
- **POS Desktop Baru**: [http://192.168.5.10:8086/bagus/pos](http://192.168.5.10:8086/bagus/pos)

