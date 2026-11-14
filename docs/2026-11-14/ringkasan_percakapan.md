# 📋 Ringkasan Percakapan: Kunci Penjualan Shift, Penyesuaian Billing PLN, Mode Demo (`?demo=1`), Audit Stok, & Pemisahan Presensi vs Kasir

**Tanggal Sesi:** 12 – 14 November 2026  
**Ruang Lingkup:** Portal Presensi Karyawan V2 (`/presensi2/*`), Kasir POS Desktop (`/pos`), Serah Terima Shift Desktop (`/presensi2/serah-terima-v2`), & Dashboard Admin ERP (`/admin/ERP` & `/admin/penjualan/penyesuaian-billing`)

---

## 1. Alur Navigasi POS Desktop → Rekap Akhir Shift → Serah Terima

### Permasalahan
- Tombol nomor **5** pada menu **📋 Operasional & Pergantian Shift** di halaman POS Desktop (`/pos`) sebelumnya langsung melompat ke halaman Serah Terima, melewati halaman Rekap Akhir Shift (`/presensi2/rekap-desktop`).
- Setelah kasir klik **"Simpan Serah Terima Shift"**, kasir membutuhkan konfirmasi jelas apakah ingin langsung logout & mengunci PC Kasir serta pengingat untuk melakukan **Check-Out Presensi** melalui HP.

### Solusi & Implementasi
1. **Perbaikan Urutan Menu di POS Desktop (`resources/views/presensi2/pos_desktop.blade.php`)**:
   - Mengarahkan menu nomor **5** ke `route('presensi2.rekap.desktop')` (**Rekap Akhir Shift & Tutup Sesi**) dengan keterangan alur **POS ➔ Rekap ➔ Serah Terima**.
2. **Modal Konfirmasi Pasca Simpan Serah Terima (`resources/views/presensi2/serah_terima_desktop.blade.php`)**:
   - Menambahkan pop-up modal otomatis (`#modal-handover-saved`) saat serah terima berhasil disimpan (`session('success')`).
   - Menyediakan tombol **🚪 Ya, Logout & Kunci PC Kasir** (`route('presensi2.logout', ['mode' => 'pos'])`), **🖨️ Cetak Slip**, atau **Tetap di Halaman Ini**, disertai pengingat agar kasir melakukan Check-Out Presensi via HP.
   - Menonaktifkan centang default `auto_clock_out` agar kasir tidak tidak sengaja ter-Check-Out otomatis tanpa foto selfie/GPS di HP.

---

## 2. Validasi Check-Out Presensi & Proteksi Durasi Kerja (`AttendanceWebV2Controller`)

### Permasalahan
- Kasir yang baru saja Check-In bisa tidak sengaja menekan Check-Out atau pulang sebelum menyelesaikan Serah Terima Shift.
- Pada saat simulasi digunakan dengan jam `clock_in_at` di masa depan (mis. memilih jam simulasi lebih awal dari jam masuk), perhitungan selisih menit menghasilkan angka negatif besar: `⏳ Belum Bisa Check-Out (Baru -29908924 menit bekerja)`.

### Solusi & Implementasi
1. **Proteksi Durasi Minimal & Serah Terima (`app/Http/Controllers/AttendanceWebV2Controller.php`)**:
   - Menambahkan validasi durasi kerja minimal **30 menit** setelah Check-In dan kewajiban menyelesaikan **Serah Terima Shift** (`ShiftSalesSummary`) ketika `enforce_handover` aktif pada mode produksi.
   - Memperbaiki perhitungan durasi kerja menggunakan `max(0, (int) $attendanceToday->clock_in_at->diffInMinutes($now, false))` sehingga tidak pernah menghasilkan angka menit negatif.
2. **Bypass Otomatis pada Mode Simulasi (`simulation_mode=1`)**:
   - Saat dalam mode simulasi/testing, validasi 30 menit dan syarat serah terima dapat dilewati agar pengujian Check-In & Check-Out berjalan lancar tanpa hambatan waktu tunggu.

---

## 3. Penyembunyian Panel "🧪 Mode Simulasi" dengan Parameter `?demo=1`

### Permasalahan & Diskusi
- Panel **🧪 Mode Simulasi (Testing & Training)** yang tampil terus-menerus di halaman Presensi (`/presensi2/scan`), Cek Stok (`/presensi2/stok`), POS (`/pos`), dan Serah Terima membuat pengguna/karyawan bingung membedakan antara tampilan kerja nyata (**Produksi**) dan tampilan **Simulasi**.

### Solusi & Implementasi
- Menyembunyikan seluruh kotak kuning/biru **🧪 Mode Simulasi** secara *default* di 5 halaman utama sehingga tampilan harian 100% bersih layaknya sistem produksi:
  1. `resources/views/presensi2/scan.blade.php`
  2. `resources/views/presensi2/stok.blade.php`
  3. `resources/views/presensi2/pos_desktop.blade.php`
  4. `resources/views/presensi2/serah_terima.blade.php`
  5. `resources/views/presensi2/serah_terima_desktop.blade.php`
- Kotak kontrol simulasi hanya dimunculkan apabila Admin/Tester menambahkan parameter **`?demo=1`** atau **`?simulasi=1`** pada URL (contoh: `http://192.168.5.10:8086/bagus/presensi2/scan?demo=1`).

---

## 4. Badge Notifikasi Antrean (`X Baru`) di Sidebar Admin ERP

### Permasalahan
- Admin perlu langsung melihat apakah ada antrean persetujuan atau verifikasi yang menunggu tindakan di sidebar kiri `/admin/ERP` tanpa harus membuka satu per satu menu.

### Solusi & Implementasi
- Memperbarui `resources/views/admin/dashboard/layout.blade.php` untuk menghitung dan menampilkan badge **`X Baru`** secara *real-time* pada:
  - **1. Data Terpadu** (akumulasi pendaftaran karyawan baru + permintaan reset password/PIN).
  - **Verifikasi Pendaftaran Karyawan** (`EmployeeRegistration::where('status', 'pending')`).
  - **Otorisasi Reset Password/PIN** (`PasswordResetRequest::where('status', 'pending')`).
  - **2. Penerimaan Distribusi** (antrean pengiriman stok cabang berstatus `sent`/`in_transit`/`pending`).
  - **3. Penjualan & Kasir Shift** / **Persetujuan Penyesuaian Billing & Print** (antrean klaim koreksi billing warnet & counter printer).

---

## 5. Filter Kunci Penjualan POS per `shift_id` & Fitur "🔓 Buka Kunci Shift" di Admin

### Permasalahan
- Setelah kasir Shift 1 menutup dan menyimpan Serah Terima Shift, kasir Shift 2 di hari dan cabang yang sama ikut terkunci (`🔒 Penjualan Shift Sudah Ditutup`) karena kueri penguncian sebelumnya hanya memfilter berdasarkan `branch_id` dan `shift_date` tanpa memfilter `shift_id`.
- Selain itu, ketika kasir tidak sengaja mengunci serah terima lebih awal atau tombol `Reset Data Hari Ini` ditekan saat transaksi 0 item, rekap `ShiftSalesSummary` belum terhapus/terbuka kembali.

### Solusi & Implementasi
1. **Filter Spesifik per `shift_id` (`app/Http/Controllers/AttendanceWebV2Controller.php`)**:
   - Memperbarui `getPenjualanData()` dan `penjualanStore()` agar pengecekan `$salesSummary` memfilter berdasarkan `$currentShiftId` (`$attendanceToday?->shift_id ?? $activeShift?->id`). Dengan demikian, Shift 1 yang sudah tutup tidak mengunci Shift 2 atau Shift 3.
   - Memperbaiki aksi `reset_sales` agar selalu menghapus/membuka kembali `ShiftSalesSummary` pada shift terkait meskipun jumlah transaksi produk fisik yang dihapus adalah `0`.
2. **Fitur Buka Kunci Shift oleh Admin (`app/Http/Controllers/Admin/AdminSaleController.php` & `resources/views/admin/penjualan/penyesuaian-billing.blade.php`)**:
   - Menambahkan route `POST /admin/penjualan/rekap-shift/{summary}/unlock-sales` (`admin.penjualan.unlock-sales`) dan method `unlockShiftSales()`.
   - Menambahkan **Bagian 4: Buka Kunci Penjualan Shift (Re-Open Shift Terkunci)** di halaman `/admin/penjualan/penyesuaian-billing` agar Admin dapat membuka kembali kunci shift kasir yang salah klik beserta jejak audit (`ActivityLog`).

---

## 6. Perbaikan Bug Persetujuan Penyesuaian Billing (`⚡ Pemadaman Listrik PLN`)

### Permasalahan
- Saat Admin menekan tombol **✓ Setujui** pada antrean **⚡ Pemadaman Listrik PLN** (dengan nominal koreksi Rp 0 / laporan insiden pemadaman) di `/admin/penjualan/penyesuaian-billing`, muncul pesan gagal:
  > *"Pengajuan ini sudah diproses atau tidak dalam status menunggu persetujuan."*
- **Penyebab Teknis:** Method `ShiftSalesSummary::isBillingAdjustmentPending()` sebelumnya mengecek `$this->hasBillingAdjustment() && $this->billing_adjustment_status === 'pending_approval'`, di mana `hasBillingAdjustment()` mensyaratkan `billing_adjustment_amount > 0`. Akibatnya, klaim pemadaman listrik dengan potongan Rp 0 tampil di tabel antrean namun ditolak saat tombol Setujui diklik.

### Solusi & Implementasi
- Memperbarui `isBillingAdjustmentPending()` dan `isPrintAdjustmentPending()` pada `app/Models/ShiftSalesSummary.php` agar mengecek langsung status `'pending_approval'` tanpa mensyaratkan nominal `> 0`.
- Diverifikasi dengan pengujian otomatis `AttendanceWebV2PortalTest::test_admin_page_can_approve_billing_adjustment` (**PASS**).

---

## 7. Transparansi Selisih Kas Laci & Audit Cek Stok bagi Admin

### Pertanyaan & Kebutuhan
1. Apakah Admin mengetahui jika uang fisik di laci kasir **kurang / minus** saat Serah Terima Shift?
2. Apakah Admin bisa mengetahui hasil pengecekan stok kasir di `/presensi2/stok`?

### Penjelasan & Peningkatan Fitur
1. **Deteksi Selisih Kas Laci (`cash_difference`)**:
   - Sistem menghitung otomatis `cash_difference = actual_cash_amount - expected_cash_amount`.
   - Di halaman Admin `/admin/penjualan/penyesuaian-billing` (**Bagian 4: Tabel Rekap & Kunci Penjualan Shift**), ditambahkan kolom **Selisih Kas Laci** dengan indikator warna:
     - Hijau: `✓ Pas (Rp 0)`
     - Merah: `⚠️ Kurang -Rp ...` (beserta alasan kasir)
     - Biru: `+Rp ... (Lebih)`
2. **Monitoring Audit Stok Kasir (`/presensi2/stok`) oleh Admin**:
   - Setiap kali kasir menyimpan cek stok awal/akhir shift di `/presensi2/stok`, data tersimpan ke tabel `ShiftStockCheck` & `ShiftStockCheckItem` dan memicu log aktivitas.
   - Apabila ditemukan selisih fisik (`difference != 0`), sistem otomatis mengirim peringatan ke **Grup WhatsApp Admin/Security** dan menampilkannya di halaman Admin:
     - `/admin/distribusi/stok-cabang` (kartu ringkasan **Cek Stok Shift Hari Ini** & **Shift Ada Selisih**).
     - `/admin/penjualan/serah-terima` (kolom status **Audit Stok**: `⚠️ X Item Selisih` atau `✅ Sesuai`).

---

## 8. Rekomendasi Arsitektur: Presensi Kehadiran vs Modal Awal & Setoran Kasir

### Pertanyaan
- *"Ketika presensi apakah perlu ada modal dan setoran tidak?"*

### Kesimpulan & Best Practice (Segregation of Duties / SoD)
- **Tidak perlu dan sebaiknya tetap dipisahkan.**
- **Alasan Utama:**
  1. **Pemisahan Fungsi (HRD vs Keuangan/Kas):** Presensi (`/presensi2/scan`) hanya mencatat kehadiran fisik karyawan (Jam Masuk, Jam Pulang, Selfie, GPS) untuk perhitungan gaji/disiplin. Sedangkan Modal Awal & Setoran adalah transaksi kas laci outlet yang dicatat di modul **POS & Serah Terima Shift**.
  2. **Tidak Semua Karyawan Memegang Laci Kasir:** Dalam satu shift bisa terdapat lebih dari satu staf (mis. operator teknis, kebersihan, atau staf gudang) namun hanya ada **1 laci kasir per shift**. Jika form presensi mewajibkan input modal & setoran, data kas akan terduplikasi.
  3. **Kontinuitas Estafet Laci Kas:** Modal laci bersifat estafet antar-shift (`Uang Tinggal di Laci Shift 1` otomatis menjadi `Modal Awal Shift 2`), sementara uang yang disetor ke Owner/Admin dicatat sebagai `Setor Tunai / Masuk Brankas` pada saat **Serah Terima Shift** Sebelum Check-Out.
