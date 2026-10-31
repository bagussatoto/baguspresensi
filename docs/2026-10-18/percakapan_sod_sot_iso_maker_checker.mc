# Rangkuman Percakapan & Dokumentasi Arsitektur
## Pendaftaran Mandiri Karyawan: Standarisasi SoD, SoT, dan ISO 27001 (Maker-Checker Pattern)

**Waktu Sesi:** 18 Oktober 2026  
**Status Implementasi:** Fase 1 Selesai 100% & Teruji Otomatis (`8 tests passed, 48 assertions`)

---

## 1. Latar Belakang & Pertanyaan Kunci

### Pertanyaan Diskusi Awal:
> *"Jika ada fitur mandiri pendaftaran, apakah baik data tersebut langsung disimpan ke tabel karyawan?"*

### Analisis & Kesimpulan Diskusi:
* **Jika Langsung Disimpan ke Master `karyawan` (Langsung Aktif):**
  * ❌ **Pelanggaran SoD (Segregation of Duties):** Karyawan bertindak sebagai pembuat permohonan (*Maker*) sekaligus pengesah wewenang (*Authorizer*) atas dirinya sendiri. Membuka celah kecurangan karyawan fiktif (*Ghost Employee Fraud*).
  * ❌ **Pencemaran SoT (Single Source of Truth):** Data pendaftar iseng, typo, atau duplikasi langsung mencemari tabel master `employees`.
  * ❌ **Pelanggaran Audit ISO 27001 (Kontrol Akses A.8 / A.9):** Registrasi pengguna wajib melalui mekanisme formal yang disetujui pihak berwenang independen.

* **Keputusan Bersama:**
  * ✅ **WAJIB DI-APPROVE ADMIN (Maker-Checker / 4-Eyes Principle).**
  * Menggunakan **Tabel Penyangga (*Staging Area*)** `employee_registrations` dengan status awal `pending`.

---

## 2. Pemahaman Konsep Inti Tata Kelola

### A. Segregation of Duties (SoD)
* **Definisi:** Pemisahan wewenang agar tidak ada satu individu pun yang memiliki kendali penuh dari awal hingga akhir atas proses yang berdampak pada uang, data, dan aset perusahaan.
* **4 Fungsi Dasar yang Wajib Dipisah:**
  1. *Initiator / Maker* (Pemohon)
  2. *Custodian* (Pemegang Uang / Stok Fisik)
  3. *Record Keeper* (Pencatat Sistem / Master Data)
  4. *Authorizer / Checker* (Penyetuju / Auditor)

### B. Single Source of Truth (SoT)
* **Definisi:** Master data resmi (`employees`) adalah satu-satunya sumber kebenaran data karyawan perusahaan.
* **Prinsip:** Data yang masuk ke master data harus sudah 100% tervalidasi, unik, resmi, dan memiliki riwayat penanggung jawab.

### C. Kepatuhan ISO 27001
* **Kontrol Akses & Kredensial (A.8.2 / A.8.5):** Pembatasan hak akses minimal (*Least Privilege*) dan perlindungan kredensial PIN/Password.
* **Audit Trail WORM (A.8.15):** Pencatatan riwayat persetujuan, penolakan, IP Address, perangkat, dan waktu secara permanen (*Write Once, Read Many*) di tabel `audit_logs`.

---

## 3. Pemilihan Format Data: Opsi 1 (Minimalis & Cepat)

Disepakati menggunakan **Opsi 1** untuk meminimalkan friksi pendaftaran bagi staf di lapangan:

### Data yang Diisi Sendiri oleh Pendaftar (Maker):
1. **Nama Lengkap:** Sesuai identitas asli.
2. **Username Pilihan:** Unik untuk login akun.
3. **PIN 6-Digit Angka:** Kredensial clock-in/out cepat di portal presensi.
4. **Nomor WhatsApp / HP:** Kunci verifikasi utama (dilengkapi tombol chat WhatsApp langsung di dashboard admin).
5. **Email (Opsional):** Untuk pemulihan akun di masa depan.

### Data yang WAJIB Ditentukan oleh Admin saat Approval (Checker):
* **Cabang / Outlet Penempatan (`branch_id`)**
* **Peran Kerja (`role`):** Kasir (`kasir`), Salesman (`salesman`), Operator (`operator`), Supervisor (`supervisor`), Driver (`driver`), Otorisator (`otorisator`).
* **Nama Jabatan (`position`)**
* **Batas Maksimal Diskon Mandiri Kasir (`max_discount_approval_percent`)**
* **NIK / Kode Karyawan Resmi:** Digenerate otomatis sistem bebas tabrakan (contoh: `EMP-004`).

---

## 4. Rincian Teknis & Kode yang Telah Diimplementasikan (Fase 1)

### 1. Database & Model Staging
* **File Migrasi:** `database/migrations/2026_10_18_171854_create_employee_registrations_table.php`
* **Model Eloquent:** `app/Models/EmployeeRegistration.php`
* **Factory:** `database/factories/EmployeeRegistrationFactory.php`
* **Struktur Kolom:** `full_name`, `phone`, `username`, `password`, `pin`, `status` (`pending`, `approved`, `rejected`), `rejection_reason`, `approved_by`, `approved_at`, `created_employee_id`, `ip_address`, `user_agent`.

### 2. Sisi Pemohon (Maker Flow)
* **View Form Registrasi:** `resources/views/presensi2/register.blade.php`
  * Desain responsif, indikator titik PIN 6-digit, dukungan Dark/Light mode.
  * Pemilihan cabang dan penentuan role telah dilepaskan dari pendaftar (Kepatuhan SoD).
* **Controller Handler:** `app/Http/Controllers/AttendanceWebV2Controller.php` (Method `register`)
  * Validasi ketersediaan username di Master Data dan di antrean pending.
  * Menyimpan ke `employee_registrations` (status: `pending`).
  * Pendaftar **tidak otomatis login** dan tidak bisa presensi sampai di-approve.
  * Merekam IP Address & User-Agent perangkat (Syarat ISO 27001).

### 3. Sisi Pemeriksa (Checker Flow di Admin Dashboard)
* **Rute Baru di `routes/web.php`:**
  * `GET /admin/karyawan-verifikasi` (`admin.karyawan.verifikasi`)
  * `POST /admin/karyawan-verifikasi/{registration}/approve` (`admin.karyawan.verifikasi.approve`)
  * `POST /admin/karyawan-verifikasi/{registration}/reject` (`admin.karyawan.verifikasi.reject`)
* **Controller Admin:** `app/Http/Controllers/Admin/AdminEmployeeController.php`
  * `verifikasiIndex()`: Menampilkan antrean pending & riwayat verifikasi.
  * `approveRegistration()`: Proteksi Anti Self-Approval, generator NIK collision-safe, transaksi atomik `DB::transaction` ke master `employees`, dan pembuatan `AuditLog`.
  * `rejectRegistration()`: Mencatat alasan penolakan dan menyimpan jejak audit ISO 27001.
* **View Panel Admin:** `resources/views/admin/karyawan/verifikasi.blade.php`
  * Dilengkapi kartu metrik, tabel antrean pending, tombol WA, modal Maker-Checker split screen, dan tabel riwayat audit trail.
* **Header Navbar ERP:** `resources/views/admin/partials/erp_navbar.blade.php`
  * Badge notifikasi merah `🛡️ Verifikasi [ X ]` yang menyala otomatis jika ada permohonan baru.

---

## 5. Bukti Pengujian Otomatis (Feature Tests)

Seluruh 8 skenario pengujian di `tests/Feature/EmployeeRegistrationMakerCheckerTest.php` berhasil lolos 100%:

```text
PASS  Tests\Feature\EmployeeRegistrationMakerCheckerTest
✓ self registration creates staging record with pending status and does not create master employee
✓ pending registered employee cannot login or clock in
✓ registration rejects duplicate username already existing in master employees
✓ registration notifies if already pending in staging
✓ admin can access verification page and view pending queue
✓ admin can approve pending registration creating master employee and audit log
✓ admin cannot self approve own registration under sod rule
✓ admin can reject registration with audit reason

Tests:    8 passed (48 assertions)
Duration: 0.85s
Formatting: Laravel Pint Passed
```

---

## 6. Rencana Tahap Selanjutnya (Roadmap Masa Depan)

1. **Fase 2 (Pemisahan Kewenangan Transaksi Kasir POS):**
   * *Blind Cash Count* saat serah terima shift (`presensi2/rekap`).
   * Dual-Control PIN Supervisor saat pembatalan nota (*void*) dan diskon di atas batas ambang kasir.
2. **Fase 3 (Manajemen Siklus Akses Pengguna ISO 27001):**
   * *Instant De-provisioning* (pencabutan hak akses seketika saat staf resign/keluar).
   * Laporan berkala review hak akses (*access rights review*).
