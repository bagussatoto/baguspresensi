# Dokumentasi & Rangkuman Percakapan ERP & Akuntansi
**Tanggal:** 7 November 2026  
**Aplikasi:** ERP & POS Presensi Maya Graha Kencana (MGK)  
**Status Sesi:** Tersimpan & Siap Dilanjutkan Besok  

---

## I. Ringkasan Pekerjaan yang Telah Diselesaikan Hari Ini

### 1. Modul Chart of Accounts (COA) / Bagan Akun Buku Besar SAK
- **Database & Model:**
  - Tabel `chart_of_accounts` dengan 6 kategori akun standar akuntansi: `ASSET` (1), `LIABILITY` (2), `EQUITY` (3), `REVENUE` (4), `COGS` (5), `EXPENSE` (6).
  - Enums `AccountCategory` dan `NormalBalance` (`DEBIT`, `CREDIT`).
  - Model [`ChartOfAccount.php`](file:///var/www/laravel_apps/bagus/Modules/Kas/app/Domain/Models/ChartOfAccount.php) dengan relasi hierarkis `parent()`, `children()`, dan relasi kas operasional.
- **Seeder Baku 30+ Akun Ritel & Warnet:**
  - Telah di-seed mencakup kas laci kasir, kas operasional, bank BCA/Mandiri, piutang, persediaan barang dagang & bahan baku FnB, peralatan PC station, hutang supplier, deposit member, modal pemilik, pendapatan penjualan F&B/Billing/Print, HPP, serta beban operasional (PLN, ISP, Gaji, dsb).
- **Antarmuka Admin:**
  - Akses: [`http://192.168.5.10:8086/bagus/admin/coa`](file:///var/www/laravel_apps/bagus/resources/views/admin/coa/index.blade.php) (CRUD akun, indikator level, toggle status aktif/non-aktif, dan proteksi akun induk).

---

### 2. Pusat Kendali ERP Terpadu (Unified ERP Hub)
- **Akses Langsung:**
  - [`http://192.168.5.10:8086/bagus/admin/ERP`](http://192.168.5.10:8086/bagus/admin/ERP) (dan alias `/admin/erp`).
- **Fitur Utama:**
  - Mengonsolidasikan **26 modul & sub-fitur ERP** ke dalam **6 Pilar Utama Bisnis MGK**:
    1. *Master Data & Katalog Terpadu* (Produk/SKU, Kategori, Brand, Supplier, Konsumen Member, Pricelist).
    2. *Operasional Kasir & Point of Sale (POS)* (POS Desktop Kasir, Audit Penjualan, Koreksi Billing/Print, Checklist Opname Kasir).
    3. *Persediaan, Gudang & Distribusi* (Stok Ketersediaan, Stok Limit/Ledger Fisik, Penerimaan Distribusi Antar-Gudang).
    4. *Sumber Daya Manusia (HRM) & Presensi* (Portal Presensi v2, Manajemen Karyawan, Roster Shift, Verifikasi Calon Staf, Reset PIN/Password, Buka Blokir Login, Review Hak Akses ISO 27001, Rekap Presensi & Audit Log).
    5. *Keuangan, Kas & Akuntansi* (Jurnal Umum, Buku Besar, Neraca Saldo, Laporan Laba Rugi, Neraca Keuangan, Bagan Akun COA, Z-Report Shift).
    6. *Keamanan, Tata Kelola & Audit Trail (GRC)* (Log Audit Forensik, Matriks RBAC 2-Lapis).
  - **Pencarian Fitur Instan (Live Feature Search):** Kasir/Admin dapat mengetik nama modul atau kata kunci apa saja (contoh: *kasir, coa, stok, jadwal, supplier*), modul langsung tersaring secara instan tanpa reload.
  - **Live Vitals Metrics Bar:** Ringkasan jumlah SKU, staf aktif, presensi hadir hari ini, omset penjualan hari ini, total fisik stok barang, dan permohonan pending approval.
  - **Navigasi Global Sidebar:** Ditempatkan menu mencolok paling atas: **"🏛️ Pusat Kendali ERP"** dengan badge `SEMUA FITUR`.

---

### 3. Perbaikan Bug Postgres (QueryException)
- **Kendala:** Error query Postgres saat mengakses `/admin/ERP`.
- **Solusi:**
  - Penyesuaian nama kolom database: `attendance_records.work_date` (bukan `date`), `cafe_sales.net_amount` (bukan `total_amount`), `stock_inventories.qty_on_hand` (bukan `quantity`).
  - Penggunaan model resmi `App\Models\PasswordResetRequest`.
  - HTTP status terverifikasi `200 OK`.

---

### 4. Implementasi Penuh Modul Akuntansi SAK (Double-Entry Bookkeeping)
- **Tabel & Relasi Jurnal:**
  - `journal_entries` dan `journal_entry_lines`.
- **Auto-Journaling Engine ([`AutoJournalService.php`](file:///var/www/laravel_apps/bagus/Modules/Kas/app/Domain/Services/AutoJournalService.php)):**
  - **Otomatis Saat Penjualan POS Kasir:** Begitu order disimpan di POS Desktop, otomatis terbentuk Jurnal Umum berpasangan:
    - *Debit:* Kas Laci Kasir (`1-10001`)
    - *Kredit:* Pendapatan Penjualan F&B (`4-10001`)
    - *Kredit:* Pendapatan Billing Warnet (`4-10002`)
    - *Debit:* HPP F&B (`5-10001`)
    - *Kredit:* Persediaan Barang Dagang (`1-13001`)
  - **Otomatis Saat VOID / Batal Nota:** Diterbitkan Jurnal Balik (*Reversal Entry*) yang memulihkan saldo kas dan stok barang dagang.
- **5 Laporan & Tampilan Akuntansi Formal SAK:**
  1. **Jurnal Umum (`/admin/akuntansi/jurnal`):** Buku harian kronologis seluruh mutasi debit/kredit dengan form input jurnal manual yang memvalidasi keseimbangan Debit == Kredit.
  2. **Buku Besar (`/admin/akuntansi/buku-besar`):** Kartu mutasi individual per akun COA (Saldo Awal, Mutasi Debit/Kredit, dan Saldo Akhir Berjalan).
  3. **Neraca Saldo (`/admin/akuntansi/neraca-saldo`):** Validasi keseimbangan seluruh akun pembukuan SAK (`Total Debit == Total Kredit`).
  4. **Laporan Laba Rugi (`/admin/akuntansi/laba-rugi`):** Pendapatan Usaha dikurangi HPP menghasilkan Laba Kotor, dikurangi Beban Operasional menghasilkan Laba Bersih Usaha.
  5. **Neraca Keuangan (`/admin/akuntansi/neraca`):** Format T-Account: Total Aset = Total Kewajiban + Total Ekuitas Modal (termasuk Laba Periode Berjalan).
- **Command Sinkronisasi Historis:**
  - `php artisan accounting:sync-sales`: Berhasil menjurnal 50 transaksi penjualan kasir POS yang sudah ada ke dalam Jurnal Umum.

---

### 5. Klarifikasi Arsitektur Keamanan (Bukan JWT, Mengadopsi ISO 27001)
- **Mekanisme Otentikasi:** Menggunakan *Stateful Session-based Authentication* (Cookie terenkripsi & HttpOnly), bukan JWT.
- **Standar ISO 27001:** Bukan nama teknologi token, melainkan standar tata kelola keamanan informasi yang diterapkan pada:
  - Perlindungan anti brute-force (kunci akun setelah 3x salah login).
  - Kontrol ganda / Maker-Checker (otorisasi Supervisor pada pembatalan nota kasir & reset PIN).
  - Tinjauan hak akses berkala & pencabutan hak akses staf resign (Deprovisioning Klausul A.8.5).
  - Rekaman jejak audit forensik permanen (Klausul A.8.15).

---

## II. Agenda & Rencana Kerja Lanjutan untuk Besok

Berdasarkan catatan pengguna pada file [`docs/2026-11-07/7-11-26`](file:///var/www/laravel_apps/bagus/docs/2026-11-07/7-11-26):

1. **Entity Relationship Diagram (ERD) Manajemen Stok & Gudang:**
   - Menyusun diagram visual relasi tabel: `products`, `product_variants`, `stock_inventories`, `warehouses`, `stock_transfer_dispatches`, `stock_ledger_mutations`, dan keterhubungannya dengan COA Persediaan (`1-13001`).
2. **Peningkatan Checklist Serah Terima Kasir Shift:**
   - Kasir shift berjalan harus dapat membaca dan memverifikasi data checklist serah terima shift sebelumnya.
3. **Closing Statement & Settlement Setoran Harian:**
   - Rekap jumlah shift harian, closing statement kasir, dan alur setor/settlement akhir hari.
4. **Penguncian Transaksi Penjualan Saat Rekap Shift:**
   - Saat proses rekapitulasi shift kasir dilakukan, transaksi penjualan shift tersebut harus otomatis dikunci (*locked/freeze*) agar data tidak dapat diubah lagi.
5. **Kemampuan Membaca Penjualan Shift Sebelumnya:**
   - Kasir atau supervisor dapat melihat rincian riwayat penjualan dari shift sebelumnya secara langsung.
6. **Pivot Table Data Transaksi Akuntansi:**
   - Menyediakan tampilan analisis pivot data transaksi keuangan berdasarkan akun COA, cabang, dan kategori transaksi.
7. **Standarisasi Bahasa Human-Readable pada Audit Trail:**
   - Format *Nilai Baru* & *Nilai Lama* pada log audit jangan lagi menampilkan kode raw JSON mentah, melainkan bahasa baku yang rapi (konsisten Bahasa Indonesia).

---

## III. Status Git & Repository
- Seluruh kode telah diformat menggunakan **Laravel Pint**.
- Seluruh rangkaian test feature (COA, ERP Hub, Akuntansi, POS Shift Flow) **LULUS 100%**.
- File perubahan telah siap diunggah ke GitHub remote: `https://github.com/bagussatoto/baguspresensi.git`.

