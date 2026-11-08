# Panduan Operasional Kasir Shift, Pemeriksaan Stok Awal, & Rekapitulasi (`presensi2`)

Dokumen ini merangkum alur kerja operasional, arsitektur kontrol keamanan (**ISO/IEC 27001:2022 & Segregation of Duties / SoD**), isolasi cabang, serta integrasi notifikasi WhatsApp pada modul **Stok Awal (`presensi2/stok`)**, **Kasir Penjualan Shift (`presensi2/penjualan`)**, **Rekapitulasi Shift (`presensi2/rekap`)**, dan **Monitoring Admin ERP (`admin/penjualan`)**.

---

## 1. Alur Operasional Kasir / Operator Shift

Setiap karyawan shift (Kasir / Operator) mengikuti siklus operasional terstruktur berikut:

1. **Presensi Masuk (`presensi2/scan`)**
   - Karyawan wajib melakukan **Clock-In Presensi** terlebih dahulu.
   - Sistem memblokir akses ke `presensi2/stok` dan `presensi2/penjualan` apabila karyawan belum melakukan presensi masuk pada tanggal kerja/shift aktif (Aturan *Single Source of Truth* Presensi).

2. **Pemeriksaan Stok Awal & Serah Terima Shift Masuk (`presensi2/stok`)**
   - **Kartu Terpadu Ber-Tab (`1–3. Pemeriksaan Stok Kertas, Minuman & Makanan`)**:
     - `📄 1. Stok Kertas Print`: Menampilkan stok shift sebelumnya, pemakaian hari ini, dan sisa stok lembar A4/F4.
     - `🥤 2. Stok Minuman`: Menampilkan stok awal kulkas/showcase vs terjual hari ini.
     - `🍜 3. Stok Makanan`: Menampilkan stok awal etalase makanan/snack vs terjual hari ini.
   - **4. Cek Kelayakan Hardware**: Pemeriksaan 15 unit PC Client, Server Billing, Printer Thermal, Printer HVS, LAN/WiFi, dan CCTV/AC. Jika status kendala dipilih (`warning` / `issue`), catatan kendala wajib diisi.
   - **5. Cek Daftar Billing Internet Shift Sebelumnya**: Verifikasi serah terima kas masuk dari operator shift sebelumnya.
   - **Mode Cepat (Fast-Track Banner)**: Apabila stok cabang pada hari operasional berjalan sudah diverifikasi (`status = 'completed'`), halaman menampilkan banner hijau **"✅ Stok Cabang Hari Ini Sudah Diverifikasi"** beserta tombol pintas **`🛒 Langsung ke Kasir Penjualan →`** tanpa perlu mengisi ulang form.

3. **Transaksi Kasir POS & Kontrol Shift (`presensi2/penjualan`)**
   - **2 Tombol & Modal Kontrol Terpadu di Header**:
     - `💻 Data & Kunci Billing`: Melihat status *real-time* unit PC cabang dan mengunci/membuka kunci nominal pendapatan billing shift (`is_billing_locked`).
     - `🖨️ Data & Kunci Print`: Melihat sisa kertas A4/F4, status printer, dan mengunci/membuka kunci jumlah lembar & nominal print shift (`is_print_locked`).
   - **Katalog & Keranjang Belanja Ringkas (`Orderan POS`)**:
     - Pemilih target pesanan (`Umum` atau `PC-01` s/d `PC-15`) terintegrasi dengan nama pelanggan.
     - **Dual-Control Diskon (`🏷️ + Diskon`)**: Kasir dapat memberikan diskon mandiri hingga batas wewenangnya (`max_discount_approval_percent`, default `5%`). Diskon di atas batas tersebut wajib diotorisasi menggunakan **PIN 6-Digit Supervisor** dan dicatat ke dalam WORM `audit_logs` (`pos_sale_discount_authorized`).
     - **Dual-Control Pembatalan Nota / Void**: Pembatalan transaksi yang sudah dibayar wajib menyertakan alasan dan **PIN 6-Digit Supervisor** (`pos_sale_void`), serta otomatis mengembalikan stok ke `stock_inventories` cabang.

4. **Penutupan Shift (`4. Selesai`) & Blind Cash Count**
   - Saat jam shift berakhir, kasir membuka modal **`4. Selesai (Rekap Shift)`** dan menginput uang fisik di laci melalui **Blind Cash Count** (input langsung atau kalkulator pecahan uang `Rp 100.000` s/d koin).
   - **Rekonsiliasi Kas Tunai Murni vs Non-Tunai (QRIS) & Diskon**:
     - Omset F&B dihitung berdasarkan nilai bersih (`net_amount` setelah diskon).
     - Transaksi non-tunai (`payment_method != 'cash'`, seperti `qris`) dipisahkan ke kolom `non_cash_amount`.
     - **Target Kas Tunai Sistem (`system_expected_cash`)** dihitung dari `(Total F&B Net + Total Internet + Total Print) - Non-Tunai (QRIS)`, sehingga kasir tidak dibebani selisih semu atas pembayaran QRIS maupun potongan diskon resmi.
   - **Notifikasi Otomatis ke Grup WhatsApp Admin/Supervisor**:
     - Saat shift ditutup, `EarlyWarningNotificationService::sendShiftClosedAlert()` otomatis mengirimkan ringkasan omset, non-tunai QRIS, fisik kas laci, dan status selisih (`PAS`, `LEBIH`, atau `KURANG`) ke Grup WhatsApp Admin (`WHATSAPP_ADMIN_GROUP_ID`).

5. **Laporan Rekapitulasi Shift & Check-Out 1-Klik (`presensi2/rekap`)**
   - Menampilkan 4 kartu metrik utama (Total Omset, F&B, Internet Billing, Print Kertas) dan kartu **Rekonsiliasi Kas Laci**.
   - **Kartu Laporan Detail Ber-Tab**:
     - `🍔 1. Rincian Item F&B`: Rekap kuantitas & subtotal per produk serta potongan diskon.
     - `🧾 2. Daftar Nota`: Daftar seluruh invoice dengan badge **`TUNAI`** / **`QRIS`** dan status **`LUNAS`** / **`VOID`**.
     - `📦 3. Audit Stok Barang`: Komparasi stok awal vs terjual/terpakai vs sisa akhir.
     - `📑 4. Riwayat Shift`: 10 riwayat penutupan shift terakhir di cabang tersebut.
   - **Tombol Check-Out Presensi 1-Klik**: Kasir dapat langsung melakukan **Check-Out Presensi Pulang** dari halaman `presensi2/rekap` atau `presensi2/penjualan` tanpa harus berpindah halaman.

---

## 2. Isolasi Data Transaksi per Cabang (`branch_id`)

Sesuai kebijakan operasional multi-cabang Maya Graha Kencana:
- Semua kasir yang bertugas di **cabang yang sama (`branch_id`)** pada tanggal operasional yang sama melihat dan menggabungkan transaksi, stok gudang/etalase, PC station, permintaan stok, serta rekapitulasi cabang tersebut.
- Transaksi antar cabang yang berbeda **terisolasi penuh** di seluruh endpoint:
  - `AttendanceWebV2Controller::stokIndex()` & `buildStockComparison()`
  - `AttendanceWebV2Controller::penjualanIndex()`
  - `AttendanceWebV2Controller::penjualanStoreTransaction()` & `penjualanCancelTransaction()`
  - `AttendanceWebV2Controller::penjualanSelesai()`
  - `AttendanceWebV2Controller::rekapIndex()`

---

## 3. Monitoring Admin & Supervisor ERP (`admin/penjualan`)

Pada halaman **Admin ERP — Penjualan & POS Kasir (`/admin/penjualan`)**, Manager/Supervisor memiliki akses ke:
1. **Tabel Transaksi Penjualan Lengkap**: Pencarian invoice, filter status (`paid`, `pending`, `cancelled`), filter metode bayar (`cash`, `qris`, `transfer`, `debit`), filter kasir, dan filter cabang (`branch_id`).
2. **Panel Audit Selisih Kas & Rekap Tutup Shift (Blind Cash Count)**:
   - Memantau hasil penutupan shift seluruh cabang beserta perbandingan **Omset Total**, **Non-Tunai (QRIS)**, **Target Kas Sistem**, **Fisik Laci**, dan badge **Selisih Kas** (`PAS`, `+Rp Lebih`, `-Rp Kurang`).
3. **Panel Persetujuan Permintaan Stok Kasir Shift (`POST /admin/penjualan/permintaan-stok/{stockRequest}/status`)**:
   - Menyetujui (`✓ Setujui` / `fulfilled`) atau menolak (`✕ Tolak` / `rejected`) pengajuan restock kertas/F&B dari kasir shift, lengkap dengan pencatatan jejak audit (`AuditLog`).

---

## 4. Perintah Pengujian Otomatis (PHPUnit)

Untuk memverifikasi seluruh alur Kasir POS, Dual-Control SoD, Blind Cash Count, Isolasi Cabang, Stok Awal, Rekapitulasi, dan Persetujuan Admin:

```bash
php artisan test --compact tests/Feature/PosDualControlAndBlindCashCountTest.php tests/Feature/ShiftStockAndSalesFlowTest.php tests/Feature/AttendanceWebV2PortalTest.php
```

