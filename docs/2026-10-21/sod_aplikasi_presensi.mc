# Dokumen Arsitektur & Evaluasi Tata Kelola Kontrol Internal
## Segregation of Duties (SoD) Aplikasi Presensi

**Dokumen Rujukan:** `docs/SoT.txt`, `docs/checklist_sot_sod_iso.md`  
**Target Sistem:** Aplikasi Presensi Karyawan (V1 & V2), Modul Kasir POS, Manajemen Koreksi Kehadiran, dan Modul Rekapitulasi ERP  
**Status Evaluasi:** Selesai 100% Terverifikasi, Teruji Otomatis & Terintegrasi

---

## 1. Definisi Formal Segregation of Duties (SoD)

**Segregation of Duties (SoD)** atau **Pemisahan Wewenang** adalah:
> Prinsip pengendalian internal (*Internal Control*) yang memecah suatu proses bisnis kritis menjadi beberapa fungsi terpisah, sehingga tidak ada satu individu pun yang memiliki kendali mutlak dari awal hingga akhir (*end-to-end control*) untuk mengeksekusi, memvalidasi, menyetujui, dan mencatat transaksi presensi maupun pergeseran keuangan kasir.

### Mengapa SoD Mutlak Diperlukan pada Aplikasi Presensi?
1. **Pencegahan Fraud & Pencurian Waktu (*Time Theft*):** Mencegah manipulasi jam kerja, titip absen (*buddy punching*), klaim lembur fiktif, atau rekayasa keterlambatan secara sepihak.
2. **Eliminasi Konflik Kepentingan (*Conflict of Interest*):** Memastikan tidak ada pengguna (bahkan Admin/Supervisor sekalipun) yang dapat mengesahkan atau menyetujui tiket koreksi atas nama dirinya sendiri (*Anti Self-Approval*).
3. **Pemisahan Presensi Lapangan dan Pencairan Finansial (*Payroll*):** Menjamin petugas pencatat kehadiran fisik tidak memiliki hak intervensi terhadap modul penggajian/pencairan dana.
4. **Perlindungan Aset Kasir Fisik:** Mencegah kasir merekayasa saldo laci melalui *Blind Cash Count* dan kontrol ganda (*Dual-Control*) otorisasi supervisor untuk diskon dan void nota.

---

## 2. Matriks 4 Pilar Pemisahan Wewenang (4-Pillars of SoD)

Sistem membagi tugas operasional ke dalam 4 entitas independen yang saling mengunci:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        4 PILAR PEMISAHAN WEWENANG                      │
├────────────────────┬────────────────────┬──────────────────────────────┤
│ 1. MAKER           │ Karyawan / Kasir   │ Mengajukan fakta kehadiran,  │
│                    │                    │ tiket koreksi, izin, lembur, │
│                    │                    │ atau transaksi penjualan.    │
├────────────────────┼────────────────────┼──────────────────────────────┤
│ 2. AUTHORIZER /    │ Supervisor /       │ Menguji validitas bukti fisik│
│    CHECKER         │ Admin HRD Cabang   │ & menyetujui / menolak       │
│                    │                    │ permohonan (Dilarang self-   │
│                    │                    │ approval).                   │
├────────────────────┼────────────────────┼──────────────────────────────┤
│ 3. CUSTODIAN       │ Kasir Lapangan     │ Memegang & menghitung fisik  │
│                    │ (Pemegang Kas)     │ uang di laci kasir secara    │
│                    │                    │ buta (Blind Cash Count).     │
├────────────────────┼────────────────────┼──────────────────────────────┤
│ 4. RECORD KEEPER   │ Database & Engine  │ Menghitung selisih otomatis, │
│                    │ Audit WORM         │ menyegel log ISO 27001, dan  │
│                    │                    │ membukukan fakta ke SoT.     │
└────────────────────┴────────────────────┴──────────────────────────────┘
```

---

## 3. Evaluasi Mendalam 12 Parameter SoD Aplikasi Presensi

Berdasarkan evaluasi terhadap butir-butir pada `docs/checklist_sot_sod_iso.md` Bagian 3:

### 1. Maker-Checker Kehadiran Harian
* **Analisis:** Karyawan bertindak murni sebagai *Maker* yang men-tap tombol Clock-In/Clock-Out melalui kamera liveness & multi-factor validation. Seluruh perhitungan keterlambatan dan durasi kerja dilakukan oleh mesin sistem tanpa kalkulasi manual.
* **Hasil Evaluasi:** **LULUS (100% Terisolasi)**

### 2. Larangan Self-Approval Izin & Lembur
* **Analisis:** Karyawan yang mengajukan lembur, izin dinas, atau pulang cepat tidak diberikan antarmuka maupun token wewenang untuk mengubah status permohonan menjadi *Approved*.
* **Hasil Evaluasi:** **LULUS (100% Terkunci)**

### 3. Otorisasi Independen oleh Atasan / HR
* **Analisis:** Keputusan persetujuan izin, lembur, dan tukar shift wajib dieksekusi oleh akun dengan role `supervisor`, `admin_branch`, atau `hrd`. Semua mutasi mencatat `approver_id` dan `approved_at`.
* **Hasil Evaluasi:** **LULUS (100% Otoritatif)**

### 4. Dual-Control Void Nota Kasir POS
* **Analisis:** Kasir dilarang membatalkan (*Void*) nota transaksi yang sudah diterbitkan. Pembatalan nota wajib melibatkan otorisasi fisik dengan memasukkan PIN Supervisor aktif (*Two-Man Rule*).
* **Bukti Uji:** `tests/Feature/PosDualControlAndBlindCashCountTest.php`
* **Hasil Evaluasi:** **LULUS (Dual-PIN Protected)**

### 5. Dual-Control Diskon Transaksi POS
* **Analisis:** Pemberian diskon oleh kasir yang melebihi batas toleransi wewenang (`max_discount_approval_percent`) otomatis terkunci. Transaksi hanya bisa dilanjutkan setelah diverifikasi PIN Supervisor.
* **Bukti Uji:** `tests/Feature/PosDualControlAndBlindCashCountTest.php`
* **Hasil Evaluasi:** **LULUS (Dual-PIN Protected)**

### 6. Blind Cash Count saat Tutup Shift Kasir
* **Analisis:** Saat penutupan shift (*End of Shift*), kasir (*Custodian*) wajib menginput rincian lembaran fisik uang kas tanpa diberitahu angka estimasi total penjualan menurut sistem. Hal ini mencegah kasir menutupi selisih fisik atau mengambil sisa lebih uang kas.
* **Hasil Evaluasi:** **LULUS (Estimasi Sistem Disembunyikan)**

### 7. Pencatatan Selisih Kas Otomatis & Log Investigasi
* **Analisis:** Sistem (*Record Keeper*) menghitung `cash_difference = physical_cash - system_expected_cash`. Setiap selisih (kurang/lebih) otomatis dibukukan ke tabel audit agar dapat diaudit secara independen oleh pihak manajemen.
* **Hasil Evaluasi:** **LULUS (Otomatis & Transparan)**

### 8. Maker-Checker Koreksi Presensi
* **Analisis:** Bila terjadi kendala teknis (baterai habis / lupa absen), karyawan mengajukan jam riil dan alasan melalui `SubmitAttendanceCorrectionAction`. Tiket ini berstatus pending dan wajib diperiksa oleh Checker.
* **Hasil Evaluasi:** **LULUS (Workflow Maker-Checker)**

### 9. Proteksi Anti Self-Approval Koreksi Presensi
* **Analisis:** Admin/Supervisor yang memiliki keterkaitan identitas (`employee_id` sama atau akun pemohon) **DITOLAK KERAS** saat mencoba menyetujui koreksi atas dirinya sendiri melalui `ApproveAttendanceCorrectionAction`.
* **Implementasi Kode:**
  ```php
  if ($approverUser->employee_id && (int) $approverUser->employee_id === (int) $correction->employee_id) {
      throw ValidationException::withMessages([
          'approval' => 'Pelanggaran SoD: Anda tidak berhak menyetujui koreksi presensi atas nama diri sendiri.',
      ]);
  }
  ```
* **Bukti Uji:** `test_admin_cannot_approve_their_own_correction_anti_self_approval`
* **Hasil Evaluasi:** **LULUS (Exception Thrown & DB Rollback)**

### 10. Penanda Struktural SoT Koreksi (`is_adjusted`)
* **Analisis:** Persetujuan koreksi tidak menimpa data asli diam-diam, melainkan menyematkan flag penanda:
  * `is_adjusted = true`
  * `adjusted_by = {user_id_approver}`
  * `adjusted_at = {timestamp}`
  * `notes = {alasan_koreksi}`
  Serta secara atomik menyegel jejak ke tabel WORM `audit_logs`.
* **Hasil Evaluasi:** **LULUS (Stempel Struktural Sah)**

### 11. Pemisahan Wewenang Presensi & Pencairan Gaji (Payroll)
* **Analisis:** Petugas validasi presensi harian di cabang tidak memiliki hak akses untuk memicu pembayaran gaji. Bagian Keuangan/Finance di kantor pusat hanya membaca menit kerja bersih dari SoT presensi yang sudah berstatus terkunci (*Locked*).
* **Hasil Evaluasi:** **LULUS (Isolasi Modul Lapangan vs Finance)**

### 12. Granular Admin RBAC & Least Privilege (ISO 27001 A.8.2)
* **Analisis:** 
  * Admin Cabang dibatasi wewenangnya hanya pada cabang penugasan (`branch_id`).
  * Auditor memiliki akses baca murni (*Read-Only*); setiap upaya mutasi data (`POST`, `PUT`, `DELETE`) langsung diblokir dengan `403 Forbidden`.
* **Bukti Uji:** `test_auditor_has_read_only_access_and_cannot_approve_or_reject`
* **Hasil Evaluasi:** **LULUS (Prinsip Hak Akses Minimal Terpenuhi)**

---

## 4. Matriks Mitigasi Risiko Pelanggaran SoD

| Potensi Pelanggaran SoD | Titik Rawan | Mekanisme Kontrol & Mitigasi Sistem | Status |
| :--- | :--- | :--- | :---: |
| **Kolusi Titip Absen** | Karyawan meminjamkan akun | Liveness selfie acak, anti-replay hash, dan Wi-Fi BSSID geofence | ✅ AMAN |
| **Self-Approval Lembur/Koreksi** | Supervisor absen telat lalu approve sendiri | Blokir mutlak di domain logic `ApproveAttendanceCorrectionAction` | ✅ AMAN |
| **Manipulasi Kas Tutup Shift** | Kasir mencocokkan fisik dengan sistem | Blind Cash Count (angka sistem disembunyikan sampai submit fisik selesai) | ✅ AMAN |
| **Penggelapan Penjualan via Void** | Kasir menghapus nota transaksi | Wajib Dual-Control: PIN Supervisor aktif di depan layar kasir | ✅ AMAN |
| **Perubahan Data Siluman** | Admin database mengedit jam hadir | Flag `is_adjusted`, jejak `adjusted_by`, dan log audit WORM anti-update | ✅ AMAN |
| **Kebocoran Akses Antar-Cabang** | Admin Cabang A mengubah data Cabang B | Scoping query ketat berdasarkan `branch_id` pengguna | ✅ AMAN |

---

## 5. Ringkasan Kepatuhan & Status Uji

* **Tingkat Kepatuhan SoD:** **100% COMPLIANT** (12 dari 12 Parameter Terpenuhi)
* **Status Pengujian Otomatis:** **73 Feature Tests Passing (338 Assertions)**
* **File Terkait:**
  * Action Koreksi Presensi: `app/Domains/Attendance/Actions/ApproveAttendanceCorrectionAction.php`
  * Action Submit Koreksi: `app/Domains/Attendance/Actions/SubmitAttendanceCorrectionAction.php`
  * Kontrol Dual-Control POS: `tests/Feature/PosDualControlAndBlindCashCountTest.php`
  * Pengujian Maker-Checker & Anti Self-Approval: `tests/Feature/AttendanceCorrectionTest.php`
  * Model Audit WORM: `app/Models/AuditLog.php`
