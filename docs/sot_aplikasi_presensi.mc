# Dokumen Arsitektur & Evaluasi Tata Kelola Data
## Single Source of Truth (SoT) Aplikasi Presensi

**Dokumen Rujukan:** `docs/SoT.txt`, `docs/checklist_sot_sod_iso.md`  
**Target Sistem:** Aplikasi Presensi, Portal Karyawan V1 & V2, Kasir POS, dan Rekapitulasi ERP  
**Status Evaluasi:** Selesai 100% Terverifikasi, Teruji Otomatis & Terintegrasi

---

## 1. Definisi Formal Single Source of Truth (SoT)

**Single Source of Truth (SoT) Aplikasi Presensi** adalah:
> Satu-satunya rujukan data resmi dan otoritatif di seluruh sistem database perusahaan mengenai kehadiran karyawan, di mana semua modul lain (Kasir POS, Portal Karyawan, Dashboard Admin, dan Penggajian/Payroll) **WAJIB** mengacu ke data ini tanpa boleh ada tabel tandingan atau rekap manual Excel.

### Mengapa SoT Mutlak Diperlukan?
1. **Mencegah Ambiguitas Data Kehadiran:** Menghilangkan perdebatan jam kerja antara karyawan dan HRD karena hanya ada satu fakta jam kerja server yang tersimpan.
2. **Keterikatan Finansial yang Sah:** Menjamin perhitungan insentif, pemotongan keterlambatan, dan lembur bersumber dari angka bersih yang sama.
3. **Pencabutan Akses Terpusat (*Instant Revocation*):** Jika karyawan diberhentikan atau dinonaktifkan, seluruh hak akses operasional (absen, portal, dan kasir POS) mati seketika dari satu titik master.

---

## 2. Tiga (3) Pilar Entitas Otoritatif SoT Presensi

Arsitektur presensi dibangun di atas tiga tabel inti di database PostgreSQL/MySQL:

```
                          ┌────────────────────────────────┐
                          │   PILAR 1: MASTER DATA         │
                          │   Tabel: `employees`           │
                          │   (Satu-satunya Buku Induk)    │
                          └──────────────┬─────────────────┘
                                         │
                                         ▼
┌─────────────────────────┐       ┌────────────────────────────────┐
│   PILAR 3: ATURAN       │       │   PILAR 2: FAKTA TRANSAKSI     │
│   Tabel: `shifts`       │──────►│   Tabel: `attendance_records`  │
│   (Jadwal & Toleransi)  │       │   (Satu-satunya Fakta Hadir)   │
└─────────────────────────┘       └──────────────┬─────────────────┘
                                                 │
                   ┌─────────────────────────────┼─────────────────────────────┐
                   ▼                             ▼                             ▼
       [Modul Kasir POS]                 [Laporan Rekap]               [Penggajian / Payroll]
   (Hanya bisa buka kasir            (Membaca angka bersih         (Menghitung gaji murni
    jika tercatat hadir)             tanpa hitung ulang manual)    dari menit kerja SoT)
```

### 🏛️ Pilar 1: Master Karyawan Tunggal (`employees`)
* **Peran:** Buku induk resmi seluruh data identitas, penempatan cabang (`branch_id`), jabatan, role kasir, dan kredensial karyawan.
* **Prinsip SoT:** Tidak ada tabel user kasir atau user portal terpisah. Semua modul merujuk ke ID tabel ini.
* **Instant Revocation:** Jika `employees.is_active = false`, detik itu juga:
  * Gagal login ke portal presensi V1 & V2.
  * Ditolak untuk melakukan Clock-In dan Clock-Out.
  * Ditolak untuk membuka sesi transaksi kasir POS.

### 🏛️ Pilar 2: Fakta Transaksi Kehadiran Tunggal (`attendance_records`)
* **Peran:** Satu-satunya buku besar transaksi kehadiran harian karyawan.
* **Komponen yang Dicatat:**
  * Waktu Server Otoritatif: `clock_in_at` dan `clock_out_at` (bersumber dari jam server NTP, bukan jam lokal HP).
  * Bukti Fisik Tak Terbantahkan (*Non-Repudiation*): `clock_in_photo_path`, `clock_in_lat`, `clock_in_lng`, dan `clock_in_distance_meters`.
  * Metode Verifikasi: `verification_method` (`gps_selfie`, `wifi_geofence`, `offline_sync`, `admin_manual`).
  * Skor Biometrik: `liveness_score` (0.00 – 1.00) untuk menangkal pemalsuan foto cetak.
  * Status & Kalkulasi Bersih: `status` (`present`, `late`, `absent`), `late_minutes`, `duration_minutes`, `overtime_minutes`.
* **Integritas Koreksi & Offline Sync:**
  * **Koreksi Presensi Disetujui:** Memperbarui langsung rekaman yang sama dengan penanda `is_adjusted = true`, `adjusted_by = {approver_id}`, dan `adjusted_at = now()`.
  * **Sinkronisasi Offline PWA:** Diinsert dengan penanda `is_offline_sync = true`, `offline_nonce = {uuid}`, dan `sync_recorded_at = now()`.
  * **Dilarang Keras:** Membuat tabel rekapitulasi kehadiran tandingan.

### 🏛️ Pilar 3: Master Jadwal & Aturan Otoritatif (`shifts`)
* **Peran:** Pusat penentuan aturan kerja harian.
* **Komponen:**
  * Jam mulai (`start_time`), jam selesai (`end_time`).
  * Toleransi keterlambatan resmi (`grace_period_minutes = 15 menit`).
  * Penanda shift malam melintasi tengah malam (`is_overnight`).
* **Prinsip SoT:** Tidak ada rumus perhitungan keterlambatan yang ditulis terpisah di front-end atau aplikasi kasir; semua keterlambatan dihitung otomatis oleh server mengacu ke tabel ini.

---

## 3. Keterikatan Downstream Antar-Modul

1. **Modul Kasir POS (`ShiftSalesSummary` & Transaksi Penjualan):**
   * Sebelum kasir toko/warnet dapat membuka sesi penjualan, sistem memverifikasi bahwa karyawan kasir telah berstatus hadir (*Clock-In*) di `attendance_records` pada shift dan hari bersangkutan.
   * Tidak ada kasir bayangan yang dapat beroperasi tanpa bukti presensi sah di SoT.
2. **Dashboard Pemantauan & Rekapitulasi ERP:**
   * Laporan kehadiran bulanan membaca data bersih langsung dari `attendance_records`.
   * Setiap selisih jam atau koreksi dapat ditelusuri riwayatnya melalui relasi ke `audit_logs` (ISO 27001 WORM).
3. **Modul Penggajian (Payroll):**
   * Perhitungan gaji pokok, insentif kehadiran, potongan telat, dan upah lembur ditarik langsung dari akumulasi `duration_minutes`, `late_minutes`, dan `overtime_approved_minutes` di `attendance_records`.

---

## 4. Evaluasi 11 Parameter Kepatuhan SoT Presensi

| No | Parameter Checklist SoT Presensi | Status Evaluasi | Bukti Implementasi di Kode |
| :---: | :--- | :---: | :--- |
| 1 | Buku Induk Master Tunggal (`employees`) | ✅ **Lolos** | Model `Employee.php` terpusat; relasi FK terikat di seluruh modul |
| 2 | Pencabutan Akses Seketika (*Instant Revocation*) | ✅ **Lolos** | Middleware & Guard memblokir user jika `is_active = false` |
| 3 | Fakta Kehadiran Tunggal (`attendance_records`) | ✅ **Lolos** | Seluruh aksi clock-in, offline sync, dan koreksi bermuara ke sini |
| 4 | Stempel Waktu Otoritatif Server | ✅ **Lolos** | `clock_in_at` menggunakan `Carbon::now()` server, bukan jam HP klien |
| 5 | Bukti Visual (*Non-Repudiation*) | ✅ **Lolos** | File selfie tersimpan di server storage dan path terikat ke record |
| 6 | Audit Geofence Akurat | ✅ **Lolos** | Formula geodesi Haversine seragam di `GeoValidationService.php` |
| 7 | Multi-Factor Geofencing (Wi-Fi/IP) | ✅ **Lolos** | Toleransi GPS drift via Hardware MAC BSSID dan IP Gateway outlet |
| 8 | Deteksi Anti-Fraud (Fake GPS) | ✅ **Lolos** | Deteksi mock location aktif menandai `is_mock_location_detected` |
| 9 | Pusat Konfigurasi Shift (`shifts`) | ✅ **Lolos** | Master shift terpusat; penentuan shift via `ShiftResolverService` |
| 10 | Kalkulasi Standar Tanpa Rumus Mandiri | ✅ **Lolos** | Menit telat dan lembur dihitung otomatis di backend |
| 11 | Validasi Presensi untuk Buka Kasir POS | ✅ **Lolos** | Kasir diblokir buka transaksi jika belum Clock-In hari bersangkutan |

---

## 5. Bukti Pengujian Otomatis (Automated Tests)

Integritas SoT Aplikasi Presensi telah diverifikasi melalui rangkaian uji otomatis PHPUnit:
* **`AttendanceCorrectionTest` (9 tests passed):** Menjamin koreksi presensi memperbarui rekaman SoT asli tanpa membuat record duplikat.
* **`WifiGeofenceAndNetworkValidationTest` (5 tests passed):** Menjamin metode verifikasi jaringan dicatat transparan di SoT.
* **`OfflineAttendanceSyncAndLivenessTest` (7 tests passed):** Menjamin antrean offline disinkronkan ke SoT dengan token anti-replay nonce.
* **`PosDualControlAndBlindCashCountTest` (9 tests passed):** Menjamin kasir POS terikat mutlak ke data kehadiran presensi.
* **`UserAccessLifecycleAndDeprovisioningTest` (4 tests passed):** Menjamin pencabutan akses seketika (*Instant Revocation*).

**Total Uji Regresi:** 73 Feature Tests (338 Assertions) — **100% Green / Lulus**.

---

## 6. Kesimpulan & Rekomendasi Pemeliharaan

1. **Status SoT Presensi:** Selesai, terpadu, dan terlindungi penuh oleh kontrol keamanan ISO/IEC 27001:2022.
2. **Kaidah Pemeliharaan ke Depan:**
   * Dilarang menambahkan tabel transaksi kehadiran baru (misal: `attendance_summaries` independen tanpa FK ke `attendance_records`).
   * Setiap penyesuaian jam kerja wajib melalui alur Maker-Checker formal dan tercatat di WORM `audit_logs`.
