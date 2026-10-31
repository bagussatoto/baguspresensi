# Dokumentasi Laporan Summary Harian, History Setor & Serah Terima 4 Shift

Dokumen ini merangkum penyelarasan tampilan antarmuka **History Setor Shift**, format cetak struk/slip **Laporan Summary Harian** (dengan tabel tetap 4 shift lengkap), serta implementasi **Konfirmasi Kas & Serah Terima Shift Sebelumnya** pada sistem Kasir POS Presensi v2.

---

## 1. Tampilan History Setor Shift (Sesuai Referensi Gambar 1)

Halaman Rekapitulasi (`http://192.168.5.10:8086/bagus/presensi2/rekap`) menyajikan tabel riwayat setoran shift dengan gaya visual yang selaras:

* **Header Card Oranye Solid**:
  * Judul: **History setor**
  * Latar belakang: Gradien oranye cerah (`#f59e0b` ke `#d97706`) dengan tipografi tebal putih.
  * Teks indikator jumlah: `Total X Riwayat Terakhir`.
* **Header Tabel Biru Lembut (`#e0f2fe`)**:
  * Warna teks: Biru gelap `#0369a1` dengan ketebalan font 700.
  * Susunan Kolom:
    1. `No`: Nomor urut baris (1, 2, 3...).
    2. `Tanggal`: Tanggal dan jam setoran shift (`YYYY-MM-DD HH:MM:SS`).
    3. `Nomer`: Tautan nomor rekap (`46-49-2920` atau `YYYYMMDD-Bx-Sx-xxxx`) berwarna biru khas.
    4. `Outlet`: Nama cabang/outlet huruf kapital (contoh: `MAYA 1`).
    5. `Shif`: 4 tombol kotak oranye **[1] [2] [3] [4]**.
    6. `Penyetor`: Nama operator kasir yang menyerahkan kas.
    7. `Nilai`: Nominal uang setoran kasir dalam format ribuan tebal.
    8. `Action`: Tombol aksi cetak ganda.
* **Tombol Angka Shift [1] [2] [3] [4]**:
  * Kotak oranye solid (`background: #f59e0b; color: #fff; border: 1px solid #d97706;`).
  * Saat diklik, langsung memicu cetak slip untuk shift terkait secara instan via parameter URL `auto_print=1`.
* **Tombol Aksi**:
  * 🖨️ **Tombol Oranye**: Cetak slip Laporan Summary Harian / Rekap Shift.
  * 🖥️ **Tombol Hijau**: Cetak checklist kelayakan hardware shift.

---

## 2. Format Cetak Slip: Laporan Summary Harian (Sesuai Referensi Gambar 2)

Slip cetak `#print-table-report` (Thermal 58mm / 80mm / Kertas Struk) dirancang mengikuti susunan:

1. **Judul Laporan**:
   * `Laporan Summary Harian` (tebal, rata kiri).
2. **Kotak Identitas Rekap**:
   * `Invoice          : [Nomor Rekap / Kode Invoice]`
   * `Outlet           : [Nama Cabang, misal: MAYA 1]`
   * `Tanggal Laporan  : [DD-MM-YYYY]`
   * `Dicetak          : [DD-MM-YYYY HH:mm]`
3. **Ringkasan KPI**:
   * `Total shif` : 4 (jumlah shift harian).
   * `Total Item` : Akumulasi seluruh lembar kertas print, item F&B terjual, dan sesi warnet.
4. **Ringkasan Pembayaran**:
   * Tabel: `Pembayaran | Jml Trx | Nilai`
   * `TUNAI` (Jumlah transaksi/shift & total penerimaan tunai).
   * `NON-TUNAI` (QRIS / Bank jika ada transaksi non-tunai).
   * `Total` : Grand total pendapatan kasir.
5. **Rekap Produk per Kategori**:
   * Tabel: `No | Kode | Total Qty | Total Nilai`
   * **KERTAS**: Rincian kertas A4 / F4 & `Subtotal KERTAS`.
   * **SNACK**: Rincian makanan & snack terjual beserta `Subtotal SNACK`.
   * **SOFTDRINK**: Rincian minuman terjual beserta `Subtotal SOFTDRINK`.
   * **WARNET**: Sesi billing `INET` & `Subtotal WARNET`.
   * **Grand Total**: Total kuantitas dan total nilai keseluruhan kategori.
6. **Summary Serah Terima (Format Tetap 4 Shift Lengkap)**:
   * Tabel: `No | Shif | Penyerah | Penerima | Nilai`
   * **Selalu menampilkan 4 baris shift secara utuh:**
     * **Baris 1**: Shift 1 (Penyerah: Operator Shift 1 $\rightarrow$ Penerima: Operator Shift 2).
     * **Baris 2**: Shift 2 (Penyerah: Operator Shift 2 $\rightarrow$ Penerima: Operator Shift 3).
     * **Baris 3**: Shift 3 (Penyerah: Operator Shift 3 $\rightarrow$ Penerima: Operator Shift 4).
     * **Baris 4**: Shift 4 (Penyerah: Operator Shift 4 $\rightarrow$ Penerima: Operator Shift 1, menutup siklus harian).
   * **Perilaku Cerdas**:
     * Shift yang telah tutup buku: menampilkan operator & nominal riil yang disetor.
     * Shift yang sedang aktif: menampilkan operator saat ini & nominal kas di laci.
     * Shift yang belum berjalan: menampilkan operator `-` dan nominal `0` agar format cetak tetap kokoh dan tidak terpotong.
   * `Total` : Penjumlahan serah terima kas seluruh shift.
7. **Terbilang (Disetor)**:
   * Kalimat rupiah bahasa Indonesia diapit tanda strip (contoh: `--------------- EMPAT RATUS TIGA PULUH RIBU ENAM RATUS LIMA PULUH RUPIAH ---------------`).
8. **Tanda Tangan Serah Terima**:
   * Kolom `(Penyetor)` dengan nama kasir di bawahnya & kolom `(Kasir)` untuk paraf serah terima.
9. **Klausul Pengesahan**:
   * `⚠️ Setoran dianggap sah setelah dibubuhi tanda tangan kedua pihak.`
10. **Request Stok**:
    * Daftar barang yang diminta stoknya oleh operator (`stok: X | limit: Y`).

---

## 3. Konfirmasi Kas & Serah Terima di Stok Awal (Shift Berikutnya)

Pada halaman pemeriksaan awal (`http://192.168.5.10:8086/bagus/presensi2/stok`) Bagian 5:

1. **Pemisahan 3 Metrik Kas**:
   * **Kas Diserahkan**: Nominal kas dari operator shift sebelumnya.
   * **Modal Laci Kasir**: Modal kembalian laci yang ditinggal.
   * **Total Fisik di Laci**: Jumlah total uang fisik yang wajib ada di laci saat shift dibuka.
2. **Rincian Pecahan Uang Kasir Sebelumnya**:
   * Chip visual: `100k`, `50k`, `20k`, `10k`, `5k`, `2k`, `1k`, dan `koin` untuk mempermudah pengecekan lembaran fisik.
3. **Input Fisik Aktual & Indikator Selisih Realtime**:
   * Input `actual_cash_received` (Uang Fisik Diterima Aktual di Laci).
   * Badge indikator otomatis:
     * `✅ Fisik Uang di Laci PAS (Sesuai)` jika nominal sama.
     * `ℹ️ Uang Fisik Lebih: +Rp ...` jika uang laci lebih.
     * `⚠️ Uang Fisik Kurang (Selisih): -Rp ...` jika uang laci kurang.
4. **Catatan Serah Terima / Kondisi Laci**:
   * Input `cash_handover_notes` untuk mendokumentasikan kondisi uang atau selisih agar kasir baru tidak terbebani selisih kasir lama.
5. **Penyimpanan Struktur Data Lengkap**:
   * Disimpan ke dalam kolom JSON `billing_details` pada tabel `shift_handover_checklists` (`expected_cash`, `actual_cash_received`, `cash_difference`, `denominations`, `cash_notes`, `verified_at`).

---

## 4. Alur Otomatisasi Antar-Shift (Tanpa Verifikasi Admin Manual)

* Kasir yang menutup shift langsung memilih shift berikutnya atau kasir penerima.
* Sistem menandai status serah terima sebagai `verified` secara otomatis (`deposit_method = 'next_shift_handover'`).
* Kasir shift berikutnya yang login dan membuka halaman Stok Awal langsung mendapati data serah terima shift sebelumnya terisi otomatis.

---

## 5. Ringkasan File & Pengujian Otomatis

* **File yang Dimodifikasi**:
  * Controller: `app/Http/Controllers/AttendanceWebV2Controller.php`
  * Model: `app/Models/ShiftSalesSummary.php`
  * View Rekap: `resources/views/presensi2/rekap.blade.php`
  * View Stok: `resources/views/presensi2/stok.blade.php`
  * Test: `tests/Feature/ShiftStockAndSalesFlowTest.php`
* **Hasil Pengujian**:
  * `php artisan test --compact tests/Feature/ShiftStockAndSalesFlowTest.php tests/Feature/PosDualControlAndBlindCashCountTest.php tests/Feature/AttendanceWebV2PortalTest.php`
  * **Hasil: 68 tests, 416 assertions — 100% Passed**.
* **Standar Kode**: Diformat dengan **Laravel Pint** (`vendor/bin/pint --dirty --format agent`).
