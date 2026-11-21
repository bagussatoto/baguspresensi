# 🎯 BLUEPRINT MASTER v3.0: ARSITEKTUR TERPADU ERP, POS KASIR DESKTOP, & PRESENSI v2
## PT Maya Graha Kencana (MGK) — Edisi Pembaruan Lengkap 21 November 2026

**Versi Blueprint:** `3.0.0-ENTERPRISE-INTEGRATED` (Pembaruan dari Blueprint v2.0 10 Oktober 2026)  
**Tanggal Rilis:** Sabtu, 21 November 2026  
**Stack Teknologi:** Laravel 12+ | PHP 8.5 | PostgreSQL 16 (`192.168.5.10:5432`)  
**Standar Tata Kelola:** Single Source of Truth (SoT), Segregation of Duties (SoD), WORM Audit Trail ISO/IEC 27001, & Akuntansi SAK Double-Entry  

---

## 1. 🧭 EVOLUSI ARSITEKTUR (DARI BLUEPRINT v2.0 KE v3.0)

Sejak **Blueprint Ramping 5 Modul v2.0** (10 Oktober 2026) dan **Roadmap Presensi** (13 Oktober 2026), sistem telah berkembang menjadi ekosistem terpadu yang menggabungkan **6 Pilar Utama ERP + Siklus Operasional Estafet 4 Shift 24 Jam**:

```mermaid
flowchart LR
    subgraph P1["1. Presensi v2 & Kontrol Shift"]
        CI["Clock-In (1 Hari 1 Shift)<br>(1 Shift 1 Petugas/Cabang)"]
        STK["Cek Stok Fisik Awal Shift<br>+ Auto-Lock Shift Lalu"]
        CO["Clock-Out + Auto-Logout Sesi"]
    end

    subgraph P2["2. POS Kasir Desktop"]
        POS["Orderan POS F&B + Billing PC<br>Jam Live & Badge Shift Aktif"]
        HIST["History Penjualan per Shift 1-4"]
        VOID["Pengajuan Void Tanpa PIN<br>(Masuk Antrean Admin)"]
    end

    subgraph P3["3. Rekap & Serah Terima Desktop"]
        RKP["Rekap Desktop (Terkunci Hari Ini)"]
        SRH["Serah Terima Kas & Pecahan Fisik<br>Slip Tabel Summary Sinkron DB"]
        STL["Status Settlement 4 Shift Hari Ini"]
    end

    subgraph P4["4. Pusat Kendali Admin ERP"]
        QVOID["Antrean Approval Void, Billing & Print"]
        VSET["Verifikasi Setoran Kas Bank/Brankas"]
        SAK["Auto-Journal SAK, Buku Besar,<br>Neraca Saldo & Laba Rugi Multi-Cabang"]
        MON["System Monitor: Cron, Queue & pg_dump"]
    end

    CI --> STK --> POS --> RKP --> SRH --> CO
    POS -.-> VOID --> QVOID
    SRH -.-> VSET --> SAK
    RKP -.-> STL
```

---

## 2. 🛡️ SPESIFIKASI TAMBAHAN & ATURAN BISNIS BARU (NOVEMBER 2026)

### 2.1. Aturan Ketat Presensi & Isolasi Shift (`ClockInAction` & `AttendanceWebV2Controller`)
1. **Aturan 1 Hari = 1 Karyawan = Maksimal 1 Shift (`1-Day-1-Shift Rule`)**:
   - Karyawan jalur operasional (`work_type = 'shift'`) yang sudah memiliki catatan presensi pada `work_date` hari ini (baik masih berjalan maupun sudah `clock_out_at`) **ditolak (`HTTP 409 Conflict`)** jika mencoba melakukan Presensi Masuk lagi pada shift berikutnya di hari yang sama.
2. **Aturan 1 Shift = Maksimal 1 Petugas Jaga per Cabang (`Single Shift Occupant Rule`)**:
   - Pada satu `branch_id`, `work_date`, dan `shift_id`, hanya boleh ada **1 karyawan aktif**. Jika Shift 2 sudah diisi oleh Petugas A, maka Petugas B yang mencoba Presensi Masuk di Shift 2 pada cabang yang sama otomatis ditolak (`HTTP 409 Conflict`).
3. **Transisi Cek Stok HP ➔ Auto-Logout ➔ POS Desktop (`stokStore`)**:
   - Setelah kasir menyelesaikan input Cek Stok Awal & Verifikasi Kas di HP, sesi HP otomatis di-logout (`session()->forget('presensi2_employee_id')`) agar kasir berpindah ke layar PC Kasir Desktop.
4. **Auto-Logout & Redirect Login Saat Presensi Keluar (`submitClockOut`)**:
   - Begitu kasir melakukan Check-Out pulang, sesi langsung dihapus otomatis dan browser diarahkan ke `/presensi2/login` (*Zero Residual Session*).

---

### 2.2. Mekanisme Auto-Lock Estafet Antar Shift (`autoLockPreviousShiftOnHandover`)
- **Masalah yang Diselesaikan:** Kasir Shift 1 selesai berjualan tetapi lupa menekan tombol *Kunci Billing* atau *Selesai Rekap*, sementara kasir Shift 2 sudah masuk presensi dan menyelesaikan Cek Stok.
- **Aturan Sistem Baru:**
  1. Saat kasir Shift 2 menyimpan form **Cek Stok (`stokStore`)**, sistem otomatis mengunci seluruh nota `CafeSale` milik kasir Shift 1 (`is_locked = true`, `locked_at = now()`).
  2. Sistem otomatis membuat/memperbarui `ShiftSalesSummary` milik kasir Shift 1 menjadi terkunci (`is_sales_locked = true`, `is_billing_locked = true`, `is_print_locked = true`) beserta *snapshot* nominal billing saat itu.
  3. Mencatat jejak audit `prev_shift_auto_locked_by_next_shift_handover` pada `AuditLog`.
  4. Jika kasir Shift 1 masih mencoba menginput penjualan di POS (`penjualanStoreTransaction`), sistem langsung menolak (`HTTP 422 Unprocessable Entity`).

---

### 2.3. Sistem Antrean Persetujuan Pembatalan Transaksi / Void (`pending_approval`)
- **Perubahan dari Blueprint Lama:** Pembatalan transaksi (Void) **tidak lagi** langsung mengubah status nota menjadi `cancelled` secara otomatis di kasir dan tidak lagi menggunakan PIN Supervisor di layar POS.
- **Alur Antrean Baru (`Maker-Checker`):**
  1. **Pengajuan Void (POS Desktop & Admin Penjualan):**
     - Kasir (atau Admin dari daftar penjualan) memilih alasan pembatalan dan mengajukan Void.
     - Kolom `void_status` diubah menjadi `'pending_approval'`, menyimpan `void_reason`, `void_requested_by`, dan `void_requested_at`.
     - Selama berstatus `pending_approval`, `payment_status` tetap `paid`, stok barang belum dikembalikan, dan jurnal void belum dicatat.
     - Di tabel riwayat POS tampil badge **`⏳ ANTREAN VOID`** dan indikator **`⏳ Menunggu Admin`**.
  2. **Notifikasi Pusat Kendali ERP & Dashboard Admin:**
     - Jumlah `CafeSale` dengan `void_status = 'pending_approval'` otomatis dihitung ke dalam `$pendingBillingAdjustments` dan `$totalTriagePending` di `/admin/ERP` serta Dashboard Admin.
  3. **Eksekusi Persetujuan / Penolakan (`/admin/penjualan/penyesuaian-billing`):**
     - **Setujui Void (`approveVoidRequest`):** Mengubah `payment_status = 'cancelled'`, `void_status = 'approved'`, mengembalikan stok ke `StockInventory`, mengubah `CafePayment` ke `refunded`, membuat jurnal pembalik (`AutoJournalService::journalVoidSale`), dan mencatat `AuditLog` (`pos_sale_void`).
     - **Tolak Void (`rejectVoidRequest`):** Mengubah `void_status = 'rejected'`, menyimpan `void_rejected_reason`, transaksi tetap sah (`paid`), dan di POS tampil indikator **`❌ Void Ditolak`**.

---

### 2.4. Standarisasi Full Desktop: POS, Rekapitulasi, & Serah Terima Kas
1. **Penghapusan Mode Penjualan/Rekap Versi HP:**
   - Seluruh rute penjualan (`/presensi2/penjualan`, `/pos`), rekapitulasi (`/presensi2/rekap`, `/presensi2/rekap-desktop`), dan serah terima (`/presensi2/serah-terima`, `/presensi2/serah-terima-desktop`) diarahkan ke antarmuka **Desktop Widescreen**.
2. **Header POS dengan Jam Digital Live & Badge Shift Aktif:**
   - Pojok kanan atas `/bagus/pos` menampilkan badge **`🕒 Shift Aktif (Jam Mulai - Selesai)`** dan jam digital realtime **`⏰ HH:MM:SS WIB`**.
3. **Pemetaan & Filter History Penjualan POS per Shift (`Shift 1` s/d `Shift 4`):**
   - Setiap transaksi `CafeSale` dipetakan ke `shift_number` (`1..4`) berdasarkan shift presensi kasir pembuat nota.
   - Tabel History Penjualan POS dilengkapi tombol filter **`Semua`**, **`Shift 1`**, **`Shift 2`**, **`Shift 3`**, **`Shift 4`** serta badge shift di setiap baris nota.
4. **Sidebar Toggle Seragam (`☰ Menu Operasional`):**
   - Tersedia di ketiga layar utama kasir (`pos_desktop`, `rekap_desktop`, `serah_terima_desktop`) untuk mengakses *Checklist Shift Lalu*, *Penjualan Shift Lalu*, *Settlement Harian (0/4)*, *Rekap Pendapatan*, *Serah Terima*, dan *Cek Stok*.
5. **Kunci Tanggal & Shift Hari Ini pada Rekap Desktop:**
   - Menghilangkan form filter tanggal/shift di `rekap-desktop` dan mengabaikan parameter URL `date`, `shift`, `summary_id` agar kasir hanya melihat dan memproses shift aktif hari ini.
6. **Sinkronisasi Penuh Database pada Slip `🖨️ Bukti Serah Terima Kasir` & `Status Settlement 4 Shift Hari Ini`:**
   - Menghapus seluruh nilai dummy hardcoded (`Rp 85.000` s/d `Rp 420.000` dan `+ Rp 100.000` modal awal palsu) sehingga seluruh angka di slip cetak dan kartu settlement 4 shift 100% mencerminkan isi tabel `cafe_sales`, `shift_sales_summaries`, dan `attendance_records` di PostgreSQL.

---

### 2.5. Modul Monitoring Infrastruktur, RBAC Granular, & Akuntansi SAK
1. **System Monitor (`/admin/ERP/system-monitor`):**
   - Pemantauan `cron_heartbeats`, antrean `jobs` & `failed_jobs` (Retry / Flush), serta backup otomatis PostgreSQL (`db:backup` pukul `06:00` dan `23:30` WIB dengan retensi 14 hari & checksum SHA-256).
2. **Matriks RBAC Granular (`/admin/pengaturan/hak-akses`):**
   - Pengaturan izin `can_create`, `can_read`, `can_update`, `can_delete`, `can_approve` per jabatan dan modul, ditambah penegakan middleware `auditor.readonly` untuk role Auditor.
3. **Laporan Keuangan SAK Multi-Cabang:**
   - Buku Besar (`1-1001` Kas Laci, `1-1002` Bank BCA, dll.), Neraca Saldo (*Trial Balance*), dan Laba Rugi Multi-Cabang dengan fitur ekspor CSV formal.

---

## 3. 🗂️ DAFTAR TABEL DATABASE TAMBAHAN (DI LUAR BLUEPRINT OKTOBER 2026)

| Nama Tabel / Kolom Baru | Fungsi Utama |
| :--- | :--- |
| `cafe_sales.void_status`, `void_requested_by`, `void_requested_at`, `void_reviewed_by`, `void_reviewed_at`, `void_rejected_reason` | Antrean persetujuan pembatalan transaksi (Void) *Maker-Checker* |
| `cafe_sales.is_locked`, `locked_at` | Penguncian mutlak nota penjualan setelah rekap shift / diambil alih shift berikutnya |
| `shift_sales_summaries.is_sales_locked`, `is_billing_locked`, `is_print_locked`, `deposit_proof` | Penguncian omzet F&B, billing, counter printer, serta unggah foto bukti setor kas |
| `shift_handover_checklists` | Pemeriksaan fisik stok etalase, kertas printer, hardware, dan uang laci awal shift |
| `printer_print_jobs` | Log counter perangkat keras printer Canon USB & sinkronisasi lembar cetak |
| `database_backups` & `cron_heartbeats` | Riwayat file dump PostgreSQL (`.sql.gz`) dan detak jantung Laravel Scheduler |
| `role_permissions` | Matriks hak akses RBAC granular (CRUD + Approve) per role |
