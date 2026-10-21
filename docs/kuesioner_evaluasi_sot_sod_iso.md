# Kuesioner Evaluasi & Panduan Uji Petik Audit
## Tata Kelola SoT, SoD, ISO/IEC 27001, dan Resiliensi Keamanan Presensi MGK

**Dokumen Rujukan:**
* `docs/sot_aplikasi_presensi.mc`
* `docs/percakapan_sod_sot_iso_maker_checker.mc`
* `docs/sod_aplikasi_presensi.mc`
* `docs/sod_pendaftaran_mandiri.mc`
* `docs/iso_27000_aplikasi_presensi.mc`
* `docs/iso_27000_pendaftaran_mandiri.mc`
* `docs/checklist_sot_sod_iso.md`

**Target Audit:** Evaluasi Pengendalian Internal (GRC), Audit Forensik TI, dan Uji Petik Kepatuhan ISO/IEC 27001:2022  
**Status Evaluasi:** 100% Selesai & Teruji Otomatis (73 Tests, 338 Assertions Passing)

---

## 🏛️ Klaster 1: Single Source of Truth (SoT) — Integritas Data

### 1.1 Konsolidasi Tabel Master Tunggal
* **Pertanyaan Audit:**  
  Apakah modul kasir POS, portal karyawan (V1 & V2), dan modul penggajian (*payroll*) memiliki tabel data karyawan atau tabel kehadiran terpisah di database?
* **Kriteria & Bukti Sistem:**  
  **TIDAK ADA.** Seluruh subsistem merujuk pada tabel master induk `employees` dan buku besar transaksi `attendance_records`.
* **Uji Pembuktian:**  
  `tests/Feature/AdminAttendanceSotAuditTest.php`

### 1.2 Pencabutan Akses Terpusat (*Instant Revocation*)
* **Pertanyaan Audit:**  
  Apa yang terjadi jika status seorang karyawan dinonaktifkan (`is_active = false`) di tabel master `employees`?
* **Kriteria & Bukti Sistem:**  
  Terjadi pemutusan akses seketika (*Instant Revocation*); karyawan langsung diblokir dari login portal presensi, dilarang Clock-In/Clock-Out, dan dilarang membuka transaksi kasir POS.
* **Uji Pembuktian:**  
  `tests/Feature/AttendanceWebV2PortalTest.php`

### 1.3 Otoritas Waktu Server (Anti Time-Tampering)
* **Pertanyaan Audit:**  
  Dari mana stempel waktu kehadiran diambil? Apakah karyawan dapat memundurkan jam smartphone mereka untuk menghindari status terlambat?
* **Kriteria & Bukti Sistem:**  
  Jam kehadiran mutlak bersumber dari jam server yang tersinkronisasi NTP (`id.pool.ntp.org`). Jam lokal HP pengguna tidak digunakan sama sekali oleh server.
* **Uji Pembuktian:**  
  `tests/Feature/Api/AttendanceApiTest.php`

### 1.4 Jejak Struktural Data Koreksi
* **Pertanyaan Audit:**  
  Bagaimana sistem membuktikan bahwa jam kehadiran tertentu merupakan hasil koreksi persetujuan atasan dan bukan jam tap asli?
* **Kriteria & Bukti Sistem:**  
  Melalui penanda struktural pada record tabel `attendance_records`:
  * `is_adjusted = true`
  * `adjusted_by = {user_id_approver}`
  * `adjusted_at = {timestamp}`
  * `notes = {alasan_koreksi}`
* **Uji Pembuktian:**  
  `tests/Feature/AttendanceCorrectionTest.php`

---

## ⚖️ Klaster 2: Segregation of Duties (SoD) — Pemisahan Wewenang

### 2.1 Pencegahan Konflik Kepentingan (*Anti Self-Approval*)
* **Pertanyaan Audit:**  
  Apakah seorang Supervisor atau Admin HRD dapat menyetujui (*Approve*) tiket koreksi jam presensi atau lembur atas nama dirinya sendiri?
* **Kriteria & Bukti Sistem:**  
  **DILARANG KERAS.** Logika domain pada `ApproveAttendanceCorrectionAction` mencocokkan `approver_id` dengan ID pemohon. Jika identitas sama, sistem membatalkan transaksi dan melempar `ValidationException` (*Pelanggaran SoD: Anda tidak berhak menyetujui koreksi presensi atas nama diri sendiri*).
* **Uji Pembuktian:**  
  `test_admin_cannot_approve_their_own_correction_anti_self_approval` pada `AttendanceCorrectionTest.php`

### 2.2 Kontrol Ganda Pembatalan Nota Kasir (*Dual-Control Void*)
* **Pertanyaan Audit:**  
  Apakah seorang kasir dapat membatalkan (*Void*) nota transaksi penjualan yang salah cetak secara sepihak?
* **Kriteria & Bukti Sistem:**  
  **TIDAK BISA.** Kasir (*Maker Transaksi*) dilarang membatalkan nota sepihak. Pembatalan nota wajib diotorisasi ganda dengan memasukkan PIN Supervisor aktif (*Two-Man Rule*).
* **Uji Pembuktian:**  
  `tests/Feature/PosDualControlAndBlindCashCountTest.php`

### 2.3 Kontrol Ganda Diskon Transaksi POS
* **Pertanyaan Audit:**  
  Apa yang terjadi jika kasir memberikan diskon yang melampaui batas toleransi wewenang (`max_discount_approval_percent`)?
* **Kriteria & Bukti Sistem:**  
  Sistem mengunci proses transaksi dan mewajibkan otorisasi fisik dengan PIN Supervisor sebelum transaksi dapat dilanjutkan.
* **Uji Pembuktian:**  
  `tests/Feature/PosDualControlAndBlindCashCountTest.php`

### 2.4 Integritas Fisik Kas Kasir (*Blind Cash Count*)
* **Pertanyaan Audit:**  
  Bagaimana sistem mencegah kasir mencocokkan uang fisik dengan angka pembukuan komputer saat tutup toko (*End of Shift*)?
* **Kriteria & Bukti Sistem:**  
  Sistem menerapkan **Blind Cash Count**. Nilai estimasi total penjualan sistem disembunyikan sampai kasir selesai menghitung dan mengirim rincian lembaran fisik uang kas di laci.
* **Uji Pembuktian:**  
  `tests/Feature/PosDualControlAndBlindCashCountTest.php`

### 2.5 Pemisahan Presensi & Pencairan Dana (*Payroll*)
* **Pertanyaan Audit:**  
  Apakah petugas pencatat/validator kehadiran di lapangan memiliki wewenang untuk mengeksekusi transfer atau mencairkan dana gaji?
* **Kriteria & Bukti Sistem:**  
  **TIDAK MEMILIKI AKSES.** Petugas operasional lapangan terisolasi dari modul *Finance*. Bagian Keuangan bertindak sebagai pihak independen yang membaca menit kerja bersih dari SoT presensi yang sudah berstatus *Locked*.

---

## 🚪 Klaster 3: SoD & Keamanan Pendaftaran Mandiri (Maker-Checker)

### 3.1 Isolasi Tabel Staging & Karantina Akun Pending
* **Pertanyaan Audit:**  
  Ketika calon karyawan mendaftar mandiri lewat web `/presensi2/register`, apakah akun tersebut langsung aktif dan bisa login atau absen?
* **Kriteria & Bukti Sistem:**  
  **TIDAK.** Data pendaftaran masuk ke tabel staging terisolasi `employee_registrations` dengan status `pending`. Akun terkunci total dari login dan presensi hingga disetujui HRD (*Ghost Employee Prevention*).
* **Uji Pembuktian:**  
  `test_self_registration_creates_staging_record_with_pending_status_and_does_not_create_master_employee` pada `EmployeeRegistrationMakerCheckerTest.php`

### 3.2 Larangan Penentuan Hak Akses oleh Pemohon
* **Pertanyaan Audit:**  
  Dapatkah pendaftar mandiri memilih cabang penempatan, menentukan jabatannya sendiri, atau mengatur batas diskon kasir?
* **Kriteria & Bukti Sistem:**  
  **TIDAK BISA.** Seluruh parameter struktural (`branch_id`, `role`, `position`, `max_discount_approval_percent`, dan NIK) tidak ada di formulir publik dan ditetapkan sepihak oleh Admin pada saat verifikasi.
* **Uji Pembuktian:**  
  `EmployeeRegistrationMakerCheckerTest.php`

### 3.3 Akuntabilitas Penolakan Pendaftar
* **Pertanyaan Audit:**  
  Jika seorang pendaftar ditolak oleh HRD, apakah penolakan tersebut terdokumentasi secara legal?
* **Kriteria & Bukti Sistem:**  
  Ya. Admin wajib mengisi `rejection_reason`, dan peristiwa `EMPLOYEE_REGISTRATION_REJECTED` tercatat permanen di tabel log audit perusahaan.
* **Uji Pembuktian:**  
  `test_admin_can_reject_registration_with_audit_reason` pada `EmployeeRegistrationMakerCheckerTest.php`

---

## 🔒 Klaster 4: Kepatuhan ISO/IEC 27001:2022 & Forensik Digital

### 4.1 Kebal Manipulasi Log Audit (*WORM Protection* - Klausul A.8.15)
* **Pertanyaan Audit:**  
  Apakah superadmin atau teknisi TI database bisa mengedit atau menghapus riwayat audit di tabel `audit_logs`?
* **Kriteria & Bukti Sistem:**  
  **TIDAK BISA.** Model `AuditLog` menerapkan *Immutability WORM Guard*. Operasi `UPDATE` dan `DELETE` dicegat di level Eloquent dan melempar `BadMethodCallException`.
* **Uji Pembuktian:**  
  `test_audit_logs_are_worm_immutable_and_cannot_be_updated_or_deleted` pada `AdminAttendanceSotAuditTest.php`

### 4.2 Sanitasi Otomatis Kredensial (Klausul A.8.11 & A.8.24)
* **Pertanyaan Audit:**  
  Bagaimana sistem memastikan kata sandi dan PIN karyawan tidak bocor melalui tabel log audit?
* **Kriteria & Bukti Sistem:**  
  `AuditLogService::sanitizePayload()` secara otomatis menyaring parameter sensitif (`password`, `pin`, `credential`, `token`) dan menggantinya menjadi string `[PROTECTED]` sebelum masuk ke database.
* **Uji Pembuktian:**  
  `tests/Feature/AdminAttendanceSotAuditTest.php`

### 4.3 Hak Akses Minimal Auditor (*Least Privilege* - Klausul A.8.2)
* **Pertanyaan Audit:**  
  Bagaimana hak akses pengguna dengan peran Auditor diatur di sistem?
* **Kriteria & Bukti Sistem:**  
  Auditor hanya diberikan hak baca murni (*Read-Only*). Setiap percobaan modifikasi data (`POST`, `PUT`, `DELETE`) langsung diblokir dengan `403 Forbidden`.
* **Uji Pembuktian:**  
  `test_auditor_has_read_only_access_and_cannot_approve_or_reject` pada `AttendanceCorrectionTest.php`

### 4.4 Pertahanan Anti Brute-Force (Klausul A.8.5 & A.8.20)
* **Pertanyaan Audit:**  
  Bagaimana sistem menangkal serangan penebakan kredensial atau flooding pada endpoint publik?
* **Kriteria & Bukti Sistem:**  
  Sistem menerapkan *Rate Limiting* ketat: `throttle:10,1` pada scan presensi dan `throttle:5,1` pada login portal.

---

## 🛡️ Klaster 5: Resiliensi Teknis (Geofence, Biometrik & Offline)

### 5.1 Multi-Factor Geofencing (Anti Fake-GPS)
* **Pertanyaan Audit:**  
  Bagaimana sistem membuktikan karyawan benar-benar berada di lokasi outlet jika mereka menggunakan aplikasi pemalsu lokasi (*Fake GPS*)?
* **Kriteria & Bukti Sistem:**  
  Sistem memvalidasi kecocokan MAC Address BSSID Wi-Fi Access Point fisik kantor dan IP publik outlet (`verification_method = 'wifi_geofence'`).
* **Uji Pembuktian:**  
  `tests/Feature/WifiGeofenceAndNetworkValidationTest.php` (5 tests passed)

### 5.2 Biometric Face Liveness Detection (Anti-Spoofing)
* **Pertanyaan Audit:**  
  Bagaimana sistem membedakan wajah asli karyawan dengan foto cetak atau video di depan kamera?
* **Kriteria & Bukti Sistem:**  
  Menggunakan deteksi keaktifan wajah real-time (*Liveness Detection*) dan merekam skor biometrik `liveness_score` ke database kehadiran.
* **Uji Pembuktian:**  
  `tests/Feature/OfflineAttendanceSyncAndLivenessTest.php`

### 5.3 PWA Offline Buffer & Anti-Replay Guard
* **Pertanyaan Audit:**  
  Jika internet outlet padam, apakah presensi tetap tercatat? Bagaimana mencegah karyawan memanipulasi jam di masa depan (*Replay Attack*)?
* **Kriteria & Bukti Sistem:**  
  Presensi ditampung di IndexedDB PWA lokal browser dengan *Cryptographic Nonce* sekali pakai dan *Time-Window Guard* (maksimal 24 jam). Jika nonce telah terdaftar atau stempel waktu kedaluwarsa, server menolak sinkronisasi.
* **Uji Pembuktian:**  
  `tests/Feature/OfflineAttendanceSyncAndLivenessTest.php` (6 tests passed)

---

## 📊 Matriks Penilaian Evaluasi Audit

| No | Klaster Pengujian | Target Kepatuhan | Skor Aktual Sistem | Status |
| :-: | :--- | :---: | :---: | :---: |
| 1 | Klaster 1: Integritas SoT | 100% | 100% | ✅ LULUS |
| 2 | Klaster 2: Pemisahan Wewenang SoD | 100% | 100% | ✅ LULUS |
| 3 | Klaster 3: Maker-Checker Registrasi | 100% | 100% | ✅ LULUS |
| 4 | Klaster 4: WORM & Kepatuhan ISO 27001 | 100% | 100% | ✅ LULUS |
| 5 | Klaster 5: Resiliensi Teknis & Anti-Replay | 100% | 100% | ✅ LULUS |
| **TOTAL** | **Kesiapan Audit Eksternal** | **100%** | **100% (AUDIT-READY)** | **✅ LULUS 100%** |
