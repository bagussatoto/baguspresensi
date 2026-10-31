# Laporan Summary Harian, History Setor & Konfirmasi Serah Terima Shift

Dokumen ini merangkum penyelarasan tampilan **History Setor Shift**, cetak slip **Laporan Summary Harian** (dengan tabel tetap 4 shift serah terima), serta penyempurnaan alur **Konfirmasi Kas & Serah Terima Shift Sebelumnya** pada sistem Kasir POS Presensi v2.

---

## 1. Tampilan History Setor Shift (Sesuai Referensi Gambar 1)

Pada halaman Rekap (`http://192.168.5.10:8086/bagus/presensi2/rekap`), tabel riwayat setor shift telah diselaraskan dengan tata letak visual:
- **Header Card Oranye**: Banner judul `History setor` dengan gradien oranye elegan (`#f59e0b` ke `#d97706`) dan teks putih tebal.
- **Header Kolom Biru Lembut (`#e0f2fe`)**:
  - `No`: Nomor urut riwayat (1, 2, 3...).
  - `Tanggal`: Tanggal dan jam setoran shift (`YYYY-MM-DD HH:MM:SS`).
  - `Nomer`: Tautan nomor rekap (`46-49-2920` atau `YYYYMMDD-Bx-Sx-xxxx`) berwarna biru khas.
  - `Outlet`: Nama cabang/outlet dalam huruf kapital (contoh: `MAYA 1`).
  - `Shif`: 4 tombol kotak oranye `1`, `2`, `3`, `4`. Setiap tombol dapat langsung diklik untuk mencetak slip shift tersebut.
  - `Penyetor`: Nama operator kasir yang menyerahkan kas.
  - `Nilai`: Nominal uang setoran kasir dalam format ribuan tebal.
  - `Action`:
    - Tombol Oranye 🖨️: Print slip Laporan Summary / Rekap Shift.
    - Tombol Hijau 🖥️: Print checklist hardware shift.

---

## 2. Format Cetak Slip: Laporan Summary Harian (Sesuai Referensi Gambar 2)

Slip cetak `#print-table-report` (Thermal / Kertas Struk) telah disusun berurutan:
1. **Judul Utama**: `Laporan Summary Harian`
2. **Kotak Identitas Rekap**:
   - `Invoice : [Nomor Rekap]`
   - `Outlet : [Nama Cabang]`
   - `Tanggal Laporan : [Tanggal Rekap]`
   - `Dicetak : [Waktu Cetak]`
3. **Ringkasan KPI**:
   - `Total shif` (Jumlah shift yang beroperasi)
   - `Total Item` (Total seluruh item fisik terjual + lembar print + sesi warnet)
4. **Ringkasan Pembayaran**:
   - `TUNAI` (Jumlah transaksi & total nominal uang tunai)
   - `NON-TUNAI` (Jika ada QRIS / Bank)
   - `Total`
5. **Rekap Produk per Kategori**:
   - `KERTAS` (A4, F4) & Subtotal KERTAS
   - `SNACK` (Daftar makanan/snack terjual) & Subtotal SNACK
   - `SOFTDRINK` (Daftar minuman terjual) & Subtotal SOFTDRINK
   - `WARNET` (Billing internet) & Subtotal WARNET
   - `Grand Total`
6. **Summary Serah Terima (Format Tetap 4 Shift Lengkap)**:
   - Tabel: `No | Shif | Penyerah | Penerima | Nilai`
   - Selalu menampilkan 4 baris tetap:
     - **Baris 1**: Shift 1 (Penyerah Shift 1 ke Penerima Shift 2).
     - **Baris 2**: Shift 2 (Penyerah Shift 2 ke Penerima Shift 3).
     - **Baris 3**: Shift 3 (Penyerah Shift 3 ke Penerima Shift 4).
     - **Baris 4**: Shift 4 (Penyerah Shift 4 ke Penerima Shift 1, menutup siklus harian).
     - Shift yang belum berjalan hari ini otomatis menampilkan operator `-` dan nilai `0` sehingga struktur struk selalu utuh dan konsisten.
   - Total nilai serah terima kasir harian.
7. **Terbilang (Disetor)**:
   - Kalimat terbilang bahasa Indonesia bertanda strip (contoh: `--------------- EMPAT RATUS TIGA PULUH RIBU ENAM RATUS LIMA PULUH RUPIAH ---------------`).
8. **Tanda Tangan Serah Terima**:
   - `(Penyetor)` dengan nama kasir di bawahnya & `(Kasir)` untuk paraf serah terima.
9. **Klausul Pengesahan**:
   - `⚠️ Setoran dianggap sah setelah dibubuhi tanda tangan kedua pihak.`
10. **Request Stok**:
    - Daftar permintaan stok cabang (Nama barang, `stok: X | limit: Y`).

---

## 3. Konfirmasi Kas & Serah Terima di Stok Awal (Shift Berikutnya)

Halaman pemeriksaan awal (`http://192.168.5.10:8086/bagus/presensi2/stok`) Bagian 5 telah disempurnakan:
1. **Pemisahan 3 Angka Kas Masuk**:
   - `Kas Diserahkan`: Nominal kas yang disetor operator sebelumnya.
   - `Modal Laci Kasir`: Modal kembalian laci yang ditinggal.
   - `Total Fisik di Laci`: Jumlah total fisik uang yang wajib ada di laci saat shift dibuka.
2. **Rincian Pecahan Kas Shift Sebelumnya**:
   - Menampilkan chip pecahan uang kasir sebelumnya (`100k`, `50k`, `20k`, `10k`, `5k`, `2k`, `1k`, `koin`) untuk memudahkan pencocokan fisik lembaran uang.
3. **Penghitungan Fisik Aktual & Indikator Selisih Otomatis**:
   - Input `actual_cash_received` (Uang Fisik Diterima Aktual di Laci).
   - Badge kalkulasi realtime:
     - `✅ Fisik Uang di Laci PAS (Sesuai)` jika tidak ada selisih.
     - `ℹ️ Uang Fisik Lebih: +Rp ...` jika uang laci berlebih.
     - `⚠️ Uang Fisik Kurang (Selisih): -Rp ...` jika uang laci kurang.
4. **Catatan Serah Terima / Kondisi Laci**:
   - Input `cash_handover_notes` untuk mencatat kondisi fisik uang (rusak, selisih) agar kasir shift baru tidak terkena penalti atas ketidaksesuaian dari shift sebelumnya.
5. **Verifikasi Checkbox Lengkap**:
   - *"Saya telah menghitung fisik uang di laci dan memverifikasi serah terima kas dari shift sebelumnya ([Nama Shift]) sebesar Rp [Total] telah sesuai."*

---

## 4. Hasil Pengujian Otomatis

Semua pengujian fitur berhasil lulus 100%:
- `tests/Feature/ShiftStockAndSalesFlowTest.php`
- `tests/Feature/PosDualControlAndBlindCashCountTest.php`
- `tests/Feature/AttendanceWebV2PortalTest.php`
- **Total: 68 tests, 416 assertions (Passed)**.
- Kode telah diformat menggunakan **Laravel Pint** (`vendor/bin/pint --dirty --format agent`).
