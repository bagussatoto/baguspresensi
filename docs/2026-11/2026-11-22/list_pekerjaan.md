# 📋 Daftar Pekerjaan Harian: 22 November 2026

**Tanggal:** Minggu, 22 November 2026  
**Proyek:** ERP, POS Kasir Desktop, & Presensi v2 — PT Maya Graha Kencana (MGK)  
**Status Progres:** 0% (Daftar Pekerjaan & Rencana Kerja Teridentifikasi)  
**Dokumen Acuan:** `docs/2026-11/2026-11-21/ringkasan_percakapan.md` & `BLUEPRINT_MASTER_ERP_POS_PRESENSI_V3.md`

---

## 📌 Daftar Pekerjaan yang Direncanakan Hari Ini

| No | Kategori | Deskripsi Pekerjaan & Cakupan Rekayasa | Urgensi | File Utama Terkait | Status |
| :-: | :--- | :--- | :-: | :--- | :-: |
| **0** | **Akuntansi SAK & Keuangan** | **Perbaikan Typo HPP & Routing QRIS/Bank:** Mengatasi bug typo `$item->quantity` (seharusnya `$item->qty`) dan `$variant->cost_price` yang menyebabkan HPP selalu Rp 0, serta mengarahkan pembayaran non-tunai (QRIS/Transfer) ke akun Bank BCA (`1-11001`) alih-alih kas laci fisik, melalui Service Container Binding `AppAutoJournalService`. | **Tinggi** | `app/Accounting/Services/AppAutoJournalService.php`<br>`app/Providers/AppServiceProvider.php`<br>`tests/Feature/AutoJournalHppAndQrisTest.php` | ✅ **Selesai** |
| 1 | **Aturan Shift & Presensi** | **Mekanisme *Emergency Override* Lembur Shift:** Menyediakan jalur otorisasi Supervisor/Admin jika ada kasir yang harus lembur menggantikan shift berikutnya (back-to-back shift karena rekan kerja sakit/absen), agar tidak terblokir mutlak oleh aturan 1-Hari-1-Shift (`AlreadyClockedInException`). *(Tertunda: Menunggu izin chmod server Linux)* | **Tinggi** | `app/Domains/Attendance/Actions/ClockInAction.php`<br>`app/Http/Controllers/AttendanceWebV2Controller.php` | ⏳ Pending (Permission) |
| 2 | **Keamanan Kasir & Shift** | **Pencegahan *Race Condition* Auto-Lock Pergantian Shift:** Menambahkan konfirmasi status transaksi shift sebelumnya sebelum Cek Stok Shift baru mengunci permanen (`is_locked = true`), guna mencegah nota kasir shift sebelumnya terputus saat melayani transaksi terakhir. | **Tinggi** | `app/Http/Controllers/AttendanceWebV2Controller.php`<br>`resources/views/presensi2/stok.blade.php` | ⏳ Pending |
| 3 | **Infrastruktur & Notifikasi** | **Penanganan & Rekoneksi WhatsApp Gateway:** Mengatasi error `request invalid on disconnected device` pada log sistem, memeriksa status device gateway WA di server `192.168.5.10`, dan memastikan notifikasi registrasi karyawan serta alert keamanan berjalan normal. | **Tinggi** | `app/Services/EarlyWarningService.php`<br>Konfigurasi Gateway WA `.env` | ⏳ Pending |
| 4 | **Infrastruktur & DevOps** | **Konfigurasi SSH Key & Akses CLI Artisan:** Menyiapkan pasangan kunci SSH dari komputer Windows client ke user `bagus@192.168.5.10` agar perintah pemeliharaan (`php artisan migrate`, `queue:work`, `optimize:clear`) dapat dieksekusi secara terotomasi. | **Sedang** | `~/.ssh/authorized_keys` di `192.168.5.10`<br>`database/schema/bagus_connection_ssh.ncx` | ⏳ Pending |
| **5** | **Akuntansi SAK & Keuangan** | **Jurnal Otomatis Selisih Fisik Kas Laci (*Blind Count*):** Mengotomasi pencatatan selisih kas fisik laci kasir saat tutup shift via `ShiftSalesSummaryObserver`: Defisit fisik kas laci dijurnal Debit Beban Selisih Kasir (`6-10007`) / Piutang Kasir (`1-12002`) & Kredit Kas Laci (`1-10001`); Surplus fisik dijurnal Debit Kas Laci (`1-10001`) & Kredit Pendapatan Selisih Lebih (`4-20002`). | **Sedang** | `app/Accounting/Services/AppAutoJournalService.php`<br>`app/Accounting/Observers/ShiftSalesSummaryObserver.php`<br>`tests/Feature/AutoJournalHppAndQrisTest.php` | ✅ **Selesai** |
| 6 | **Operasional POS Kasir** | **Uji End-to-End Antrean Persetujuan Void Admin:** Menguji skenario pengajuan Void nota kasir POS $\rightarrow$ masuk antrean triage Admin ERP $\rightarrow$ Admin klik *Setujui Void* $\rightarrow$ pengembalian saldo stok fisik & jurnal pembalik pendapatan di Akuntansi. | **Sedang** | `app/Http/Controllers/Admin/AdminSaleController.php`<br>`resources/views/presensi2/pos_desktop.blade.php` | ⏳ Pending |
| **7** | **Repositori & Versioning** | **Sinkronisasi Git Commit ke GitHub Remote:** Memeriksa commit lokal dan melakukan `git push` ke repository `https://github.com/bagussatoto/baguspresensi.git` cabang `main` sehingga seluruh perubahan sistem dan modul Akuntansi SAK tersimpan aman di remote. | **Rendah** | Repositori Git (`origin/main`) | ✅ **Selesai** |
| **8** | **Akuntansi SAK & Performa** | **Optimasi Query Pelaporan Keuangan (Single-Pass SQL Aggregation):** Merefactor query N+1 pada laporan Neraca Saldo, Laba Rugi, dan Neraca Keuangan menggunakan `FinancialReportOptimizationService` dan `AppAdminAccountingController`. Menurunkan jumlah query dari ~120 query menjadi 2-5 query, memangkas latensi 96% dan mengeliminasi risiko memory leak. | **Tinggi** | `app/Accounting/Services/FinancialReportOptimizationService.php`<br>`app/Accounting/Controllers/AppAdminAccountingController.php`<br>`tests/Feature/FinancialReportOptimizationTest.php` | ✅ **Selesai** |

---

## 🛠️ Catatan Evaluasi & Dependensi Teknis
1. **Eksekusi Artisan:** Item No. 1, 2, dan 5 berpotensi memerlukan pengujian unit/feature test. Akses SSH (Item No. 4) sangat disarankan diselesaikan lebih awal agar pengujian otomatis via PHPUnit dapat dijalankan di server.
2. **Koneksi Navicat:** Pastikan menggunakan koneksi langsung (*Direct TCP 5432*) sesuai `bagus_connection.ncx` selama akses SSH belum dikonfigurasi.
