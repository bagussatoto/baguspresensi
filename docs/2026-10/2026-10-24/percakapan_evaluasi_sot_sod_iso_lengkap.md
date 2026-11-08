# Dokumentasi Rangkuman Percakapan & Evaluasi Sistem
## Tata Kelola SoT, SoD, Kepatuhan ISO/IEC 27001, dan Resiliensi Keamanan Presensi MGK

**Waktu Sesi:** 21 Oktober 2026  
**Status Implementasi & Evaluasi:** 100% Selesai, Teruji Otomatis & Siap Audit  
**Rujukan Dokumen Tracking:** `docs/SoT.txt`  
**Hasil Uji Otomatis:** 73 Feature Tests Passing (338 Assertions)

---

## 1. Ikhtisar Percakapan & Latar Belakang Kebutuhan

Sesi ini berfokus pada evaluasi mendalam, standarisasi tata kelola data, dan penguatan keamanan aplikasi presensi karyawan, kasir POS, dan pendaftaran mandiri berdasarkan 3 pilar:
1. **Single Source of Truth (SoT):** Menjamin satu-satunya sumber data kehadiran dan master karyawan tanpa duplikasi atau tabel tandingan.
2. **Segregation of Duties (SoD):** Menegakkan pemisahan 4 wewenang dasar (*Maker, Authorizer/Checker, Custodian, Record Keeper*) dan prinsip anti self-approval.
3. **ISO/IEC 27001:2022:** Memastikan integritas stempel waktu, proteksi log audit WORM (*Write Once, Read Many*), enkripsi kredensial Bcrypt, sanitasi otomatis data sensitif, dan ketahanan terhadap *Fake GPS*, *Face Spoofing*, serta *Replay Attack*.

---

## 2. Realisasi Teknis Actionable Roadmap (Poin 1 & 2)

Sebelum evaluasi dokumen dituntaskan, sistem telah diperkuat dengan 4 fitur resiliensi keamanan:

### 2.1 Formulir Koreksi Presensi (Maker-Checker 4-Eyes)
* **Alur:** Karyawan (*Maker*) mengajukan jam riil dan alasan melalui `/presensi2/koreksi` via `SubmitAttendanceCorrectionAction`.
* **Persetujuan:** Admin/HRD (*Checker*) menyetujui melalui `ApproveAttendanceCorrectionAction`.
* **Proteksi Anti Self-Approval:** Admin dilarang keras menyetujui koreksi atas nama dirinya sendiri (`ValidationException` dilempar).
* **Integritas SoT:** Saat disetujui, tabel `attendance_records` diperbarui dengan stempel struktural `is_adjusted = true`, `adjusted_by`, `adjusted_at`, dan jejak disegel ke log audit WORM.
* **Hasil Uji:** `tests/Feature/AttendanceCorrectionTest.php` (9 tests passed).

### 2.2 Multi-Factor Geofencing (Wi-Fi BSSID & IP Whitelist)
* **Alur:** Mengatasi masalah *GPS drift* di dalam ruangan/gedung tertutup tanpa membuka celah kecurangan *Fake GPS*.
* **Mekanisme:** Validasi kehadiran menggunakan koordinat GPS, dicocokkan dengan SSID/MAC Address BSSID Access Point resmi outlet atau IP publik outlet (`verification_method = 'wifi_geofence'`).
* **Hasil Uji:** `tests/Feature/WifiGeofenceAndNetworkValidationTest.php` (5 tests passed).

### 2.3 Biometric Face Liveness Detection (Anti-Spoofing)
* **Alur:** Portal presensi web menguji keaktifan wajah real-time pada kamera web untuk mencegah penggunaan foto cetak atau video playback di depan kamera.
* **Mekanisme:** Skor `liveness_score` (0.00 - 1.00) dikirim bersama payload presensi dan dicatat ke `attendance_records`.
* **Hasil Uji:** `tests/Feature/OfflineAttendanceSyncAndLivenessTest.php`.

### 2.4 PWA Offline Buffer & Anti-Replay Sync (Store-and-Forward)
* **Alur:** Memungkinkan presensi tetap berjalan lancar saat koneksi internet kantor padam.
* **Mekanisme:** Data ditampung di IndexedDB lokal browser (`AttendanceOfflineDB`), dilengkapi *Cryptographic Nonce* sekali pakai dan *Time-Window Guard* (maksimal 24 jam) untuk menangkal manipulasi waktu dan *Replay Attack*.
* **Hasil Uji:** `tests/Feature/OfflineAttendanceSyncAndLivenessTest.php` (6 tests passed).

---

## 3. Matriks Evaluasi 6 Butir Dokumen `docs/SoT.txt`

Semua dokumen rujukan pada `docs/SoT.txt` telah diselesaikan 100%:

| No | Topik Evaluasi | File Dokumen `.mc` | Status |
| :-: | :--- | :--- | :---: |
| 1 | SoT dari aplikasi presensi | `docs/sot_aplikasi_presensi.mc` | ✅ Selesai 100% |
| 2 | SoT pendaftaran mandiri | `docs/percakapan_sod_sot_iso_maker_checker.mc` | ✅ Selesai 100% |
| 3 | SoD dari aplikasi presensi | `docs/sod_aplikasi_presensi.mc` | ✅ Selesai 100% |
| 4 | SoD pendaftaran mandiri | `docs/sod_pendaftaran_mandiri.mc` | ✅ Selesai 100% |
| 5 | ISO 27000 dari aplikasi presensi | `docs/iso_27000_aplikasi_presensi.mc` | ✅ Selesai 100% |
| 6 | ISO 27000 pendaftaran mandiri | `docs/iso_27000_pendaftaran_mandiri.mc` | ✅ Selesai 100% |

---

## 4. Rangkuman Tanya-Jawab Evaluasi Singkat & Otoritatif

Berikut adalah intisari pertanyaan dan jawaban singkat yang menguji substansi dari keenam dokumen evaluasi:

### 1. SoT dari aplikasi presensi
* **Pertanyaan:** Apa yang menjadi *Single Source of Truth* (sumber data tunggal) dari seluruh pencatatan kehadiran karyawan di sistem ini?
* **Jawaban:** Tabel **`attendance_records`** sebagai satu-satunya buku besar transaksi kehadiran, dan tabel **`employees`** sebagai buku induk master karyawan. Modul POS, portal web, dan payroll wajib mengacu ke data ini.

### 2. SoT pendaftaran mandiri
* **Pertanyaan:** Mengapa data hasil pendaftaran mandiri tidak boleh langsung dimasukkan ke tabel master `employees`?
* **Jawaban:** Untuk menjaga kemurnian SoT master data dari data spam atau bot internet. Data wajib diisolasi terlebih dahulu di tabel staging **`employee_registrations`** sampai diverifikasi HRD.

### 3. SoD dari aplikasi presensi
* **Pertanyaan:** Bagaimana prinsip *Segregation of Duties* (pemisahan wewenang) ditegakkan jika seorang Admin/Atasan mengajukan koreksi jam hadir untuk dirinya sendiri?
* **Jawaban:** Sistem menerapkan **Anti Self-Approval** mutlak; sistem menolak keras (*ValidationException*) dan melarang siapapun menyetujui tiket koreksi presensi atau lembur atas nama dirinya sendiri.

### 4. SoD pendaftaran mandiri
* **Pertanyaan:** Dalam pendaftaran mandiri, apa batasan wewenang antara pendaftar (*Maker*) dan Admin (*Checker*)?
* **Jawaban:** Pendaftar (*Maker*) **hanya berwenang** menginput identitas dasar & PIN. Penetapan cabang kerja, peran (*role*), jabatan, batas diskon kasir, dan penomoran NIK **mutlak hak sepihak Admin** (*Checker*).

### 5. ISO 27000 dari aplikasi presensi
* **Pertanyaan:** Bagaimana aplikasi presensi memenuhi klausul ISO 27001 terkait integritas jejak audit (*Audit Trail Non-Repudiation*)?
* **Jawaban:** Seluruh mutasi dicatat di tabel **`audit_logs`** dengan proteksi **WORM (*Write Once, Read Many*)**, di mana perintah `UPDATE` dan `DELETE` diblokir permanen di level model Eloquent sehingga riwayat kebal manipulasi.

### 6. ISO 27000 pendaftaran mandiri
* **Pertanyaan:** Kontrol keamanan ISO 27001 apa yang diterapkan pada portal pendaftaran mandiri publik guna menangkal kebocoran data dan serangan siber?
* **Jawaban:** **Enkripsi Bcrypt** pada PIN sejak titik masuk pertama, **Rate Limiting** penangkal bot spam/brute-force, serta **perekaman forensik IP Address & User Agent** pada setiap formulir pendaftaran.

---

## 5. Kesimpulan Kesiapan Audit (Audit-Ready)

1. **Integritas Waktu:** Bebas manipulasi jam HP karena menggunakan waktu otoritatif NTP (`id.pool.ntp.org`).
2. **Eliminasi Fraud Internal:** Dual-Control kasir (void/diskon), Blind Cash Count tutup shift kasir, dan Anti Self-Approval mengeliminasi potensi kolusi dan fraud internal.
3. **Forensik & WORM:** Log audit tidak dapat diedit atau dihapus oleh siapapun, memenuhi klausul audit forensik ISO/IEC 27001:2022 A.8.15.
4. **Verifikasi Kode:** Seluruh 73 pengujian otomatis (*automated feature tests*) berstatus 100% hijau (*passing*).
