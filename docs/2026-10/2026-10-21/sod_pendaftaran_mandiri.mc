# Dokumen Arsitektur & Evaluasi Tata Kelola Kontrol Internal
## Segregation of Duties (SoD) Pendaftaran Mandiri Karyawan

**Dokumen Rujukan:** `docs/SoT.txt`, `docs/checklist_sot_sod_iso.md`  
**Target Sistem:** Portal Pendaftaran Mandiri Karyawan V2 (`/presensi2/register`), Antrean Verifikasi Admin (`/admin/karyawan/verifikasi`), dan Master Data Karyawan ERP  
**Status Evaluasi:** Selesai 100% Terverifikasi, Teruji Otomatis & Terintegrasi

---

## 1. Definisi Formal Segregation of Duties (SoD) Pendaftaran Mandiri

**Segregation of Duties (SoD) Pendaftaran Mandiri** adalah:
> Prinsip pengendalian internal berbasis *Dual-Control* dan pola *Maker-Checker* (*4-Eyes Principle*), di mana proses penciptaan identitas karyawan dipecah secara tegas antara pengajuan data awal oleh pemohon (*Maker*) dan pemeriksaan keabsahan fisik serta pemberian hak akses struktural oleh HRD/Admin (*Checker/Authorizer*).

### Mengapa SoD Mutlak Diperlukan pada Pendaftaran Mandiri?
1. **Pencegahan Karyawan Fiktif (*Ghost Employee*):** Mencegah terciptanya akun bayangan yang disusupkan ke sistem untuk membobol kas penggajian (*payroll fraud*).
2. **Pencegahan Eskalasi Hak Akses (*Privilege Escalation*):** Memastikan pendaftar tidak dapat mengangkat dirinya sendiri menjadi Supervisor, Admin, atau mengatur batas diskon kasir sesuka hati.
3. **Pemberantasan Konflik Kepentingan (*Anti Self-Approval*):** Menolak secara sistem jika seorang Admin mencoba menyetujui akun pendaftaran mandiri miliknya sendiri.
4. **Perlindungan Integritas Master Data (*SoT Protection*):** Menjamin tabel master `employees` tetap murni, bersih, dan hanya berisi personel resmi yang telah terverifikasi KTP, kontak WhatsApp, dan SK penempatan cabang.

---

## 2. Matriks 4 Pilar Pemisahan Wewenang Pendaftaran Mandiri

```
[ CALON KARYAWAN ]                 [ ADMIN / HRD ]                    [ SISTEM & WORM AUDIT ]
     (Maker)                         (Checker)                            (Record Keeper)
        │                                 │                                      │
  1. Input Data                           │                                      │
     Dasar & PIN                          │                                      │
        │                                 │                                      │
        ▼                                 │                                      │
┌──────────────────────┐                  │                                      │
│ Tabel Staging        │                  │                                      │
│ employee_registrations                  │                                      │
│ (Status: PENDING)    │                  │                                      │
└──────────┬───────────┘                  │                                      │
           │                              │                                      │
           │ 2. Notifikasi Antrean        │                                      │
           └─────────────────────────────►│                                      │
                                          │ 3. Verifikasi KTP,                   │
                                          │    Tetapkan Cabang & Role            │
                                          │    (DILARANG SELF-APPROVAL)          │
                                          │                                      │
                                          ▼                                      ▼
                                ┌───────────────────┐                  ┌───────────────────┐
                                │ Approve / Reject  │─────────────────►│ Terbit Master     │
                                │                   │                  │ `employees` (SoT) │
                                └───────────────────┘                  │ + Log Audit WORM  │
                                                                       └───────────────────┘
```

| Peran SoD | Pelaku / Komponen | Batas Wewenang & Tanggung Jawab | Batasan yang Dilarang (*Hard Boundary*) |
| :--- | :--- | :--- | :--- |
| **1. Maker** | Calon Karyawan (Pendaftar) | Mengisi data identitas dasar (Nama, No HP/WhatsApp, Username, PIN 6-digit). | **DILARANG** menentukan cabang, role jabatan, batas diskon kasir, atau generate NIK. |
| **2. Checker / Authorizer** | HRD Admin / Manajer Cabang | Memeriksa keabsahan KTP/kontak WA, menetapkan cabang (`branch_id`), role, jabatan, limit diskon, dan NIK resmi. | **DILARANG** menyetujui (*Approve*) berkas pendaftaran atas nama dirinya sendiri (*Anti Self-Approval*). |
| **3. Custodian** | Pengelola Berkas Fisik / HRD | Memegang berkas fisik (fotokopi KTP, formulir lamaran kerja, buku tabungan payroll). | **DILARANG** mengaktifkan akun di sistem tanpa persetujuan alur checker elektronik. |
| **4. Record Keeper** | Database & Engine Audit WORM | Mengisolasi data pending di staging `employee_registrations`, mentransfer data sah ke master `employees`, menyegel audit log ISO 27001. | Menolak penulisan langsung ke master `employees` dari portal pendaftaran mandiri. |

---

## 3. Evaluasi Mendalam 6 Butir Parameter SoD Pendaftaran Mandiri

Berdasarkan butir-butir audit pada `docs/checklist_sot_sod_iso.md` Bagian 4:

### 1. Batasan Wewenang Calon Karyawan (*Maker Boundary*)
* **Analisis:** Pendaftar hanya memiliki akses ke formulir publik `/presensi2/register`. Kolom yang dapat diisi terbatas pada: Nama Lengkap, Nomor Telepon/WhatsApp, Username, dan PIN 6-digit.
* **Isolasi Staging:** Hasil submit pendaftaran disimpan di tabel staging terisolasi `employee_registrations` dengan `status = pending`. Tidak ada data yang ditulis ke tabel master `employees`.
* **Bukti Uji:** `tests/Feature/EmployeeRegistrationMakerCheckerTest.php` (`test_self_registration_creates_staging_record_with_pending_status_and_does_not_create_master_employee`)
* **Hasil Evaluasi:** **LULUS (100% Terisolasi di Staging)**

### 2. Larangan Intervensi Struktural oleh Pendaftar
* **Analisis:** Pendaftar dilarang menentukan sendiri penempatan cabang (`branch_id`), peran tugas (`role`), nama jabatan (`position`), batas diskon kasir (`max_discount_approval_percent`), maupun Nomor Induk Karyawan (NIK).
* **Proteksi Backend:** Form request dan controller menerapkan *strict whitelisting*, membuang semua manipulasi payload parameter administratif dari sisi pengguna.
* **Hasil Evaluasi:** **LULUS (Nol Intervensi Struktural)**

### 3. Pemeriksaan Independen oleh Checker (*Independent Review*)
* **Analisis:** Admin/HRD mengakses antrean verifikasi terpusat pada route `admin.karyawan.verifikasi`. Admin memeriksa keabsahan fisik/KTP dan validasi kontak via tautan WhatsApp resmi perusahaan sebelum memberikan persetujuan.
* **Hasil Evaluasi:** **LULUS (Gerbang Verifikasi 4-Eyes)**

### 4. Penetapan Otoritatif oleh Admin (*Authoritative Assignment*)
* **Analisis:** Admin/HRD yang secara sepihak menetapkan cabang penugasan, role, jabatan, batas diskon kasir, serta mengesahkan generate NIK resmi perusahaan.
* **Integritas SoT:** Saat disetujui, sistem mentransfer data staging secara atomik ke tabel master `employees` (SoT) dan mencatat stempel `approved_by` dan `approved_at`.
* **Bukti Uji:** `tests/Feature/EmployeeRegistrationMakerCheckerTest.php` (`test_admin_can_approve_pending_registration_creating_master_employee_and_audit_log`)
* **Hasil Evaluasi:** **LULUS (Otoritatif Sepihak)**

### 5. Proteksi Mutlak Anti Self-Approval (*Conflict of Interest Guard*)
* **Analisis:** Admin yang memiliki hak verifikasi dilarang keras menyetujui (Approve) pendaftaran atas nama dirinya sendiri (pencegahan konflik kepentingan).
* **Mekanisme Sistem:** Sistem mencocokkan identitas pengguna admin dengan data pendaftar. Jika terdapat indikasi kesamaan akun/nama, sistem menolak eksekusi dan membatalkan transaksi.
* **Bukti Uji:** `tests/Feature/EmployeeRegistrationMakerCheckerTest.php` (`test_admin_cannot_self_approve_own_registration_under_sod_rule`)
* **Hasil Evaluasi:** **LULUS (Zero Tolerance Self-Approval)**

### 6. Pencegahan Karyawan Fiktif (*Ghost Employee Prevention*)
* **Analisis:** Akun yang masih berstatus pending diblokir total dari login portal, presensi tap-in, dan operasional kasir POS hingga verifikasi 4-Eyes selesai disetujui.
* **Bukti Uji:** `tests/Feature/EmployeeRegistrationMakerCheckerTest.php` (`test_pending_registered_employee_cannot_login_or_clock_in`)
* **Hasil Evaluasi:** **LULUS (Lockdown Penuh saat Status Pending)**

---

## 4. Matriks Mitigasi Risiko SoD Pendaftaran Mandiri

| Skenario Ancaman (*Threat Scenario*) | Titik Rawan Serangan | Mekanisme Kontrol Mitigasi Sistem | Status |
| :--- | :--- | :--- | :---: |
| **Karyawan Siluman (*Ghost Employee*)** | Oknum menyusupkan akun palsu untuk memicu pencairan gaji | Data terisolasi di staging; wajib verifikasi fisik KTP/WA oleh HRD | ✅ TERLINDUNGI |
| **Eskalasi Hak Akses (*Privilege Escalation*)** | Pendaftar menyetel role menjadi `Admin` atau `Supervisor` | Kolom `role` ditiadakan di form publik; ditetapkan sepihak oleh Admin | ✅ TERLINDUNGI |
| **Persetujuan Diri Sendiri (*Self-Approval*)** | Admin mendaftar mandiri lalu menyetujui akunnya sendiri | Blokir identitas: Admin dilarang menyetujui permohonan dirinya | ✅ TERLINDUNGI |
| **Manipulasi Diskon Kasir** | Pendaftar kasir memberi limit diskon 100% pada dirinya | `max_discount_approval_percent` hanya bisa diset oleh Admin di backend | ✅ TERLINDUNGI |
| **Duplikasi Akun / Identity Hijacking** | Pendaftar merebut username karyawan resmi yang ada | Sistem menolak username yang sudah eksis di tabel `employees` & staging | ✅ TERLINDUNGI |
| **Penghapusan Jejak Penolakan** | Pelamar ditolak tanpa kejelasan alasan | Alasan penolakan wajib diisi dan disegel permanen ke `audit_logs` | ✅ TERLINDUNGI |

---

## 5. Ringkasan Kepatuhan & Status Uji Otomatis

* **Tingkat Kepatuhan SoD Pendaftaran Mandiri:** **100% COMPLIANT** (6 dari 6 Parameter Terpenuhi)
* **Status Pengujian Otomatis:** **8 Feature Tests Passing (48 Assertions)** pada `tests/Feature/EmployeeRegistrationMakerCheckerTest.php`
* **Jejak Audit ISO 27001:** Setiap tindakan persetujuan (`EMPLOYEE_REGISTRATION_APPROVED`) dan penolakan (`EMPLOYEE_REGISTRATION_REJECTED`) tercatat permanen di tabel WORM `audit_logs`.
