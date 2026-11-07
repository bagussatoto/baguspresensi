# Ringkasan Percakapan: Rekonsiliasi Selisih Stok Antar-Shift, Audit Transparan Admin & Simulasi Pengujian

**Tanggal:** 2026-11-07  
**Lokasi Proyek:** `/var/www/laravel_apps/bagus`  
**Halaman Terkait:**
- `http://192.168.5.10:8086/bagus/presensi2/stok` (Pemeriksaan Stok & Serah Terima Shift)
- `http://192.168.5.10:8086/bagus/admin/` (Dashboard Utama Admin ERP)
- `http://192.168.5.10:8086/bagus/admin/stok` (Modul 3. Ketersediaan Produk & Audit Stok)

---

## 1. Latar Belakang & Masalah Lapangan
- **Kasus Kasir:** Pada pemeriksaan stok awal shift (Seksi 1–3: Kertas, Minuman, & Makanan), sering terjadi perbedaan antara data sistem dengan kenyataan fisik (misal: di sistem tercatat Aqua ada 10 botol, namun kenyataan fisik di kulkas hanya ada 9 botol / kurang 1).
- **Asal Status "✓ Aman" Sebelumnya:** Status "✓ Aman" pada kode awal hanyalah indikator *Buffer / Safety Stock* (jika sisa $> 5\text{ pcs}$ dianggap aman, jika $\le 5\text{ pcs}$ dianggap kritis). Sistem belum mencocokkan fisik riil karena kasir belum diberi kolom input fisik aktual.
- **Kebutuhan Sistem:**
  1. Kasir shift baru membutuhkan kolom input **Fisik Aktual** dan **Hitung Selisih Otomatis Realtime**.
  2. Dibutuhkan perlindungan hukum/SOP agar kasir shift baru tidak dituduh menghilangkan barang yang sudah hilang dari shift sebelumnya.
  3. Laporan selisih wajib sampai ke Admin secara transparan lengkap dengan pasangan nama **Operator Penyerah (Shift Lalu)** vs **Operator Penerima (Shift Sekarang)**.

---

## 2. Implementasi Pemeriksaan Stok Fisik Aktual (`presensi2/stok`)
Pada formulir pemeriksaan stok ([`stok.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/stok.blade.php)):
1. **Kolom "Fisik Aktual (Kulkas/Rak/Laci)":**
   - Menambahkan input angka fisik riil pada Tab 1 (Kertas Print), Tab 2 (Minuman Showcase), dan Tab 3 (Makanan Etalase).
   - Nilai default otomatis terisi sisa stok sistem terkini.
2. **Kolom "Selisih" & Status Realtime Tanpa Reload:**
   - **Fisik == Sistem (Selisih 0):** Badge `0 pcs` (Hijau), status `✓ Aman (Cocok)`.
   - **Fisik < Sistem (Misal Kurang 1):** Kotak input otomatis berwarna merah, badge `⚠️ -1 pcs` (Merah), status `⚠️ Selisih Kurang`.
   - **Fisik > Sistem:** Kotak input berwarna biru, badge `+1 pcs`, status `ℹ️ Selisih Lebih`.
3. **Banner Peringatan & Validasi Catatan:**
   - Jika terdeteksi selisih kurang, muncul banner peringatan kuning-merah yang meminta kasir mengisi kronologi pada kolom catatan.
   - Form submit memvalidasi bahwa kasir tidak boleh mengabaikan catatan jika ada barang yang minus.
4. **Perekaman di Database (`ShiftHandoverChecklist`):**
   - Disimpan pada field JSON `drink_items` dan `food_items` dengan skema:
     `variant_id`, `name`, `system_stock`, `physical_stock`, `discrepancy`, `status`, dan `unit`.
   - Jika halaman dibuka kembali pada hari yang sama, data input fisik termuat otomatis (*pre-filled*).

---

## 3. Klarifikasi: "Restock Cepat" vs "Audit Selisih Barang"
- **"⚡ Ajukan Restock Cepat":** Berfungsi untuk pasokan pengadaan barang yang habis/menipis secara wajar karena laku terjual (misal Aqua sisa 2 botol karena laris).
- **"Status & Tindakan Selisih":** Berfungsi untuk audit kehilangan barang (misal Aqua di sistem 10, fisik 9). Selisih ini **bukan untuk di-restock**, melainkan untuk ditelusuri siapa yang bertanggung jawab atas hilangnya 1 botol tersebut.

---

## 4. Transparansi Antar-Shift & Laporan ke Admin
1. **Pemberitahuan ke Shift 2 (Shift Berikutnya):**
   - Kasir Shift 2 wajib mengetahui catatan sisa fisik riil dan kejadian dari Shift 1 sebagai *baseline* acuan serah terima agar tidak kaget atau saling tuduh.
2. **Kewajiban Laporan ke Admin:**
   - Admin bertindak sebagai wasit independen yang berwenang mengecek CCTV dan log kasir untuk memutuskan apakah selisih dipotongkan dari kas kasir shift sebelumnya atau di-write off.
3. **Pencantuman Dua Pihak (*Chain of Custody*):**
   - Laporan selisih wajib mencantumkan pasangan nama:
     - **⏮️ Operator Penyerah (Shift Sebelumnya):** Penanggung jawab toko sebelumnya.
     - **⏭️ Operator Pemeriksa (Shift Sekarang):** Kasir yang clock-in dan menemukan selisih.

---

## 5. Implementasi Dashboard Admin ERP (`admin/` & `admin/stok`)
Pada dashboard Admin ([`AdminDashboardController.php`](file:///var/www/laravel_apps/bagus/app/Http/Controllers/Admin/AdminDashboardController.php), [`index.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/admin/dashboard/index.blade.php), dan [`stok.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/admin/dashboard/stok.blade.php)):
- Ditambahkan kartu audit:
  **🛡️ Laporan Audit Serah Terima Shift & Rekonsiliasi Stok Fisik**
- **Data yang Ditampilkan:**
  1. Waktu & Tanggal Serah Terima (Jam WIB & Hari).
  2. Shift & Cabang Outlet MGK.
  3. **⏮️ Penyerah (Shift Lalu):** Nama operator kasir shift sebelumnya (Biru).
  4. **⏭️ Penerima (Shift Sekarang):** Nama operator kasir pemeriksa masuk (Ungu).
  5. **Status Rekonsiliasi Fisik:** Rincian barang yang selisih (misal: `Aqua 600ml: Sistem 10 • Fisik 9 (-1 pcs)`).
  6. **Selisih Kas Laci:** Status uang laci pas atau minus.
  7. **Catatan Kasir:** Kronologi yang dilaporkan kasir.
  8. **Tombol "🔍 Detail":** Membuka modal Berita Acara pop-up lengkap (kondisi fisik, serah terima uang, catatan kasir, kelayakan hardware).

---

## 6. Mode Simulasi & Pengujian Cepat di `presensi2/stok`
Menghindari *deadlock* saat pengujian sistem dan sarana pelatihan kasir baru:
- **Panel Khusus:** `🧪 Mode Simulasi & Pengujian Serah Terima`
- **4 Tombol Aksi:**
  1. **🟢 1. Preset Semua Cocok (Nihil):** Mengisi seluruh fisik sama dengan sistem (selisih 0).
  2. **🔴 2. Preset Selisih Kurang (Aqua -1):** Mensimulasikan Aqua kurang 1 botol, input berubah merah, badge `-1 pcs`, dan catatan otomatis terisi kronologi.
  3. **🟡 3. Preset Kritis (Restock):** Mensimulasikan stok $\le 3\text{ pcs}$ untuk menguji tombol Restock Cepat.
  4. **🔄 Reset Simulasi (`?action=reset_stok`):** Membersihkan database checklist testing hari ini sehingga form dapat diuji berulang kali.

---

## 7. Prinsip Lanjut ke Penjualan & Konfirmasi Asinkron
- **Apakah Shift 2 Harus Menunggu Konfirmasi Shift 1 Dulu?**
  - **TIDAK PERLU MENUNGGU (Unblocking):** Kasir Shift 2 langsung diarahkan ke POS Kasir Penjualan agar pelayanan pelanggan tidak mandek jika Kasir Shift 1 sudah pulang atau tidak bisa dihubungi.
  - Tanggung jawab Shift 2 sudah terkunci aman: dia hanya bertanggung jawab atas 9 botol yang dia terima.
- **Apakah Stok Sistem Boleh Langsung Dihapus/Ditambah?**
  - **TIDAK BOLEH:** Stok sistem tetap 10 pcs, fisik dipegang Shift 2 adalah 9 pcs, dan selisih -1 pcs tercatat sebagai *Tiket Selisih Menggantung (Pending Confirmation)* sampai Shift 1 / Admin menyelesaikan klarifikasinya (apakah diakui dan dipotong kas, atau dicek CCTV).

---

## 8. Verifikasi & Pengujian Kode
- **Feature Tests:**
  - `test_stok_checklist_records_physical_discrepancy_for_drink_and_food` ➡️ **PASSED** (10 assertions).
  - `test_stok_simulation_can_be_reset_via_action_reset_stok` ➡️ **PASSED** (5 assertions).
  - `test_stok_page_displays_previous_shift_physical_checklist_and_settlement_progress` ➡️ **PASSED** (10 assertions).
- **Code Styling:** Seluruh file PHP telah diformat dan lulus standar **Laravel Pint**.

---

## 9. Penambahan Fitur Serah Terima Fisik Shift Lalu & Kontrol Settlement 4 Shift

### A. Tampilan Sisa Fisik Shift Sebelumnya di Tabel Stok Barang (1–3)
1. **Banner Serah Terima Fisik:**
   - Ditampilkan di bagian atas pemeriksaan stok barang: memuat nama shift penyerah (misal: *Shift 1 Pagi*), nama operator kasir penyerah, serta catatan khusus jika ada minuman/makanan yang kurang dari shift sebelumnya.
2. **Badge Fisik Shift Lalu pada Kolom 'Stok Shift Sebelumnya':**
   - Pada setiap baris Kertas, Minuman, dan Makanan, sistem mengekstrak hitungan fisik riil terakhir dari `ShiftHandoverChecklist` shift sebelumnya.
   - Ditampilkan badge ungu: `⏮️ Fisik: X pcs` serta selisihnya jika ada (misal: `-1`).
   - Dengan ini kasir shift baru tahu pasti bahwa kekurangan barang tersebut telah tercatat sejak shift sebelumnya dan bukan tanggung jawab dirinya.

### B. Mini-Widget Status Settlement 4 Shift Hari Ini (Daily Closing Progress)
1. **4 Kartu Grid Shift (Shift 1 s/d Shift 4):**
   - Menampilkan status setiap shift dalam putaran operasional 24 jam:
     - `✅ Settlement Selesai`: Shift telah melakukan penutupan kasir & Z-Report (`ShiftSalesSummary`).
     - `🟢 Shift Anda (Aktif)`: Shift yang sedang dijalani operator yang login saat ini.
     - `🟡 Menunggu Closing`: Shift yang jam operasionalnya telah usai namun belum ditutup.
     - `⚪ Belum Mulai`: Shift mendatang.
   - Dilengkapi nama kasir yang bertugas, omset billing per shift, dan nominal kas fisik yang disetor/di laci.
2. **Bar Akumulasi Omset & Kas Harian:**
   - Menghitung progres penyelesaian shift harian: `X dari 4 Shift Ditutup`.
   - Mengakumulasikan Grand Total Omset Billing Toko Hari Ini & Total Kas Fisik yang terkumpul.

---

## 10. Kartu Ringkasan & Tombol Aksi Serah Terima Stok di Bawah Tabel 1–3

### A. Latar Belakang & Kebutuhan
Kasir membutuhkan tombol aksi dan ringkasan langsung di bawah Tabel 1–3 (Kertas, Minuman, Makanan) sebelum berlanjut ke pemeriksaan kas laci. Hal ini agar:
1. Rekonsiliasi fisik barang dapat ditinjau dan dikonfirmasi seketika.
2. Kasir shift sebelumnya dan Admin segera mendapatkan kepastian selisih secara transparan tanpa harus menunggu kasir scroll jauh ke bawah form.
3. Kasir terlindungi karena jika terdeteksi selisih kurang, sistem mewajibkan pengisian catatan kronologi sebelum kasir dapat melanjutkan ke tahap serah terima kas.

### B. Implementasi Komponen
1. **Kotak Rekonsiliasi Realtime:**
   - Menampilkan statistik live: *Total Item Diperiksa*, *Fisik Cocok*, *Selisih Kurang*, dan *Selisih Lebih*.
   - Badge status:
     - `✓ Semua Fisik Sesuai Sistem` jika selisih 0.
     - `⚠️ X Item Kurang (Dilaporkan ke Admin)` jika terdapat barang kurang.
2. **Tombol Aksi Pintar (*Smart Action Button*):**
   - **Kondisi Normal (Cocok):** Tombol hijau zamrud: `✅ Konfirmasi Serah Terima Stok & Lanjut ke Cek Kas Laci ⬇️`.
   - **Kondisi Selisih Kurang:** Tombol amber/oranye: `⚠️ Laporkan Selisih (X Item) ke Admin & Lanjut ke Kas ⬇️`.
   - **Validasi Otomatis:** Ketika diklik, jika kasir belum mengisi catatan pada barang yang minus, sistem memunculkan modal peringatan dan memfokuskan kursor ke input catatan. Jika valid, sistem menampilkan toast konfirmasi dan melakukan *smooth scroll* langsung ke Seksi Hardware & Kas Laci.



---

## 11. Pelaporan Status & Kelayakan Hardware Antar-Shift & Dashboard Admin ERP

### A. Latar Belakang & Urgensi
1. **Perlindungan Aset Periferal PC:** Headset, mouse, keyboard, monitor, printer kasir, dan server billing adalah komponen bisnis vital. Melaporkannya saat pergantian shift mencegah saling tuduh jika ada unit yang rusak/mati.
2. **Respon Cepat Teknisi:** Admin dan tim IT dapat langsung mengetahui nomor PC yang bermasalah (misal: *PC-04 headset putus*) sebelum jam ramai pengunjung (*peak hours*).

### B. Implementasi di Layar Kasir (`presensi2/stok`)
- **Banner Riwayat Hardware dari Shift Sebelumnya:**
  Di Seksi 4 (*Cek Kelayakan Hardware*), jika shift sebelumnya melaporkan kendala (`warning`/`issue`) atau meninggalkan catatan, otomatis muncul kotak info:
  `🖥️ Catatan Hardware Shift Lalu (Shift 1 • Operator Budi): PC-04 headset kiri kresek-kresek [STATUS: WARNING]`.
  Kasir shift baru tahu bahwa kerusakan tersebut bukan tanggung jawabnya.
- **Validasi Wajib Catatan:** Jika kasir memilih status kendala hardware, kasir wajib mengetik nomor PC atau deskripsi kendala sebelum submit.

### C. Implementasi di Layar Admin ERP (`admin/` & `admin/stok`)
- **Kolom Langsung "Kelayakan Hardware" di Tabel Utama Laporan Audit:**
  - `🟢 Normal (15 PC)` jika semua berfungsi normal.
  - `🟡 Ada Kendala` (dilengkapi cuplikan catatan nomor unit PC yang bermasalah).
  - `🔴 Butuh Maintenance` jika memerlukan teknisi segera.
- **Pendeteksi Isu Otomatis:** Checklist dengan status kendala hardware otomatis dihitung sebagai item perhatian audit (`has_hw_issue`).

---

## 12. Penyempurnaan Alur Settlement & Rekonsiliasi Harian (EOD Closing & Z-Report)

Menindaklanjuti instruksi *"lengkapi semua"*, seluruh 4 aspek kelengkapan settlement harian telah diimplementasikan penuh:

### A. Alur Khusus Shift 4 (Penutup Hari / End of Day Settlement)
1. **Banner SOP Shift 4:**
   - Pada halaman serah terima desktop ([`serah_terima_desktop.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/serah_terima_desktop.blade.php)) dan mobile ([`serah_terima.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/serah_terima.blade.php)), ketika shift aktif adalah Shift 4 (Malam), sistem menampilkan banner khusus penutup buku:
     `🌙 SHIFT 4: PENUTUP HARI & SETTLEMENT EOD (24 JAM)`.
   - Menginstruksikan kasir untuk menarik seluruh kas omset shift harian dan menyisakan uang modal kembalian untuk Shift 1 besok pagi.
2. **Pemisahan Modal Laci Ditinggal (*Retained Drawer Cash*):**
   - Nilai default otomatis diset ke **Rp 150.000** (bukan 0 atau 100.000 biasa).
   - Ditambahkan tombol pintas (*quick-presets*):
     `[ Rp 150.000 (SOP Shift 1) ]` `[ Rp 200.000 ]` `[ Rp 100.000 ]`.
   - Kas bersih yang diserahterimakan / disetor otomatis terkalkulasi: $\text{Fisik Laci} - \text{Modal Ditinggal}$.

### B. Opsi Metode Penyetoran & Unggah Bukti Setor Fisik
1. **Metode Penyetoran Lengkap:**
   - `🔒 Amplop Brankas Kasir (Safe Dropbox)` (Otomatis terpilih sebagai SOP Shift 4).
   - `🏦 Setor Tunai ATM / Rekening Bank`.
   - `🤝 Serah Terima Fisik ke Kasir Selanjutnya`.
   - `💼 Diserahkan ke Owner / Kolektor`.
2. **Unggah Bukti Setor (`deposit_proof`):**
   - Input upload foto struk ATM atau foto amplop brankas kasir.
   - Disimpan di `public/uploads/deposits/` dan direferensikan dalam `deposit_notes` / log audit dengan helper `$summary->depositProofUrl()`.

### C. Z-Report Harian Terpadu 24 Jam ([`modal_z_report.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/admin/dashboard/partials/modal_z_report.blade.php))
1. **Tombol "🖨️ Cetak Z-Report Harian (24 Jam)":**
   - Tersedia di Dashboard Utama Admin ([`index.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/admin/dashboard/index.blade.php)) dan Modul Stok ([`stok.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/admin/dashboard/stok.blade.php)).
2. **Format Dokumen Resmi Siap Cetak (A4 / Thermal / PDF):**
   - **Kop Surat Resmi:** Maya Graha Kencana (MGK), Cabang, dan Tanggal Transaksi 24 Jam.
   - **KPI 24 Jam:** Total Omset Kotor, Total Kas Disetor, Modal Laci Akhir untuk Shift 1, dan Net Selisih Kasir.
   - **Tabel Rekapitulasi 4 Shift:** Breakdown Billing, F&B, Print, Total Omset, Setoran Kas, Modal Ditinggal, Selisih Kas, Status Fisik Stok, Status Hardware, dan tautan foto bukti setor.
   - **Catatan Operasional:** Rangkuman total item selisih dan status 15 unit PC station warnet.
   - **Kolom Pengesahan Tanda Tangan 3 Pihak:**
     1. Kasir Penutup (Shift 4).
     2. Kasir Penerima (Shift 1 Besok Pagi).
     3. Supervisor / Owner MGK.
   - Dilengkapi CSS `@media print` sehingga saat dicetak dokumen bersih dari elemen navigasi website.

---

## 13. Hasil Verifikasi Otomatis
- **Total Pengujian:** 33 unit feature tests ([`ShiftStockAndSalesFlowTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/ShiftStockAndSalesFlowTest.php)) **LULUS 100% (241 assertions)**.
- **Standar Kode:** Seluruh berkas PHP telah diverifikasi bebas error syntax (`php -l`) dan rapi sesuai pedoman **Laravel Pint**.

---

## 14. Penyempurnaan Bagian 5: Konfirmasi Kas & Serah Terima Shift Sebelumnya

Berdasarkan tinjauan alur operasional kasir, 4 perbaikan kritis telah diterapkan pada Seksi 5 ([`stok.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/stok.blade.php) & [`AttendanceWebV2Controller.php`](file:///var/www/laravel_apps/bagus/app/Http/Controllers/AttendanceWebV2Controller.php)):

1. **Perbaikan Bug Ekspektasi Kas Laci (Khusus Shift 1 & Setoran Brankas):**
   - Sistem kini membedakan antara omset yang **sudah disetor ke brankas/bank** (`safe_dropbox`, `bank_transfer`, `cash_collector`) vs kas **estafet di laci** (`next_shift_handover`).
   - Jika shift sebelumnya (termasuk Shift 4 EOD) menyetor omset ke brankas, ekspektasi kas fisik yang wajib dihitung kasir baru **hanyalah Modal Laci Ditinggal (`drawer_expected_cash`)**, bukan omset + modal. Kasir Shift 1 tidak lagi terkena tuduhan selisih minus palsu.
2. **Kalkulator Mini Hitung Pecahan Lembaran Uang:**
   - Ditambahkan tombol `🧮 Buka Kalkulator Lembaran Uang` dengan grid pecahan (100k, 50k, 20k, 10k, 5k, 2k, 1k, koin).
   - Menghitung subtotal otomatis dan langsung mengisi kotak uang fisik kasir tanpa perlu hitung kalkulator manual.
3. **Pernyataan Checkbox Verifikasi Kas Dinamis:**
   - Kalimat persetujuan berubah otomatis:
     - Jika pas: *"Saya telah menghitung fisik uang di laci sebesar Rp X dan konfirmasi telah SESUAI."*
     - Jika selisih: *"Saya telah menghitung fisik uang di laci sebesar Rp X (Terdapat Kekurangan Kas -Rp Y) dan telah mencatat keterangannya untuk pertanggungjawaban audit Admin."*
4. **Validasi Wajib Catatan jika Terjadi Selisih Kas:**
   - Frontend (`stokForm.addEventListener('submit')`) dan backend (`stokStore`) memvalidasi bahwa jika kas fisik berbeda dari ekspektasi laci, kasir **wajib mengisi Catatan Serah Terima / Kondisi Laci (`cash_handover_notes`)** sebelum diizinkan masuk ke POS.

---

## 15. Pemisahan Alur Kerja Menjadi 2 Tahap (Step Wizard Workflow)

Menindaklanjuti instruksi untuk memisahkan antara pemeriksaan barang dengan konfirmasi kas laci shift:

1. **Bilah Panduan Tahapan (*Step Wizard Navigation*):**
   - Terdapat 2 tab visual di bagian atas halaman:
     - `[ 1. Tahap 1: Cek Stok Barang & Hardware ]`
     - `[ 2. Tahap 2: Konfirmasi Kas & Laci Shift ]`
2. **Tahap 1 (Fokus Barang & Hardware):**
   - Menampilkan khusus: Kertas Print, Minuman Showcase, Makanan Etalase, Rekonsiliasi Selisih Stok Fisik, dan Kelayakan 15 PC Station.
   - Tidak ada gangguan input uang kas.
   - Di bagian bawah terdapat tombol validasi:  
     `[ Lanjut ke Tahap 2: Konfirmasi Kas & Serah Terima Laci ➡️ ]`.
   - Melakukan validasi kelengkapan stok & hardware sebelum mengizinkan kasir melangkah ke Tahap 2.
3. **Tahap 2 (Fokus Uang Kas Laci Kasir):**
   - Menampilkan khusus: Rincian penyerahan kas dari shift lalu, status brankas/estafet laci, kalkulator lembaran uang, input uang fisik aktual, catatan kondisi uang, dan checkbox verifikasi dinamis.
   - Tombol navigasi: `⬅️ Kembali ke Tahap 1 (Cek Stok)` dan tombol final `🚀 6. Selesai & Buka POS Kasir Desktop`.
4. **Keamanan & Integrasi Data Atomik:**
   - Seluruh data kedua tahap tetap disubmit dalam 1 transaksi ke backend sehingga riwayat `ShiftHandoverChecklist` tersimpan utuh dan transparan bagi Admin ERP.

---

## 16. Perbaikan Bug Tampilan: "Tahap 2 Belum Ada Isinya"

1. **Akar Masalah (Root Cause):**
   - Pada file [`stok.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/stok.blade.php), wadah kartu *Mini Settlement 4 Shift* belum ditutup tag `</div>` sebelum `#step-1-container`, sehingga tag penutup yang ada menutup kartu mini settlement dan membiarkan `<div id="step-1-container">` tetap terbuka.
   - Akibatnya, `<div id="step-2-container">` bersarang (*nested*) di dalam `#step-1-container`. Ketika kasir berpindah ke Tahap 2, fungsi JavaScript mengeksekusi `step-1-container.style.display = 'none'`, yang menyebabkan seluruh anak elemennya termasuk `#step-2-container` ikut tersembunyi (*hidden*), sehingga layar tampak kosong.
   - Selain itu, validasi pada tab header Tahap 2 sebelumnya memblokir perpindahan tab jika hardware belum tercentang.

2. **Solusi yang Diterapkan:**
   - Menambahkan tag penutup `</div>` sehingga `#step-1-container` tertutup dengan benar dan `#step-2-container` berdiri independen sebagai sibling container.
   - Memperbarui fungsi `goToStep2(validate = false)`:
     - Klik langsung pada tab header Tahap 2 (`goToStep2(false)`) dapat berpindah langsung secara halus tanpa memblokir kasir yang ingin mengintip rincian kas.
     - Klik tombol *"Lanjut ke Tahap 2"* di bagian bawah Tahap 1 (`goToStep2(true)`) tetap menjalankan validasi kelengkapan fisik stok dan hardware.
   - Seluruh 33 automated tests di [`ShiftStockAndSalesFlowTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/ShiftStockAndSalesFlowTest.php) **LULUS 100% (247 assertions)**.


---

## 17. Implementasi Mode Simulasi & Reset Pengujian Transaksi POS Desktop (`/bagus/pos`)

Menjawab kebutuhan pengujian alur penjualan POS tanpa merusak data penjualan asli, pembukuan keuangan, atau mengotori stok:

1. **Panel Kontrol Simulasi & Tombol Aksi Cepat:**
   - Diletakkan di bawah header kasir dengan toggle yang bisa dibuka/tutup: `[ 🧪 Mode Simulasi ]`.
   - **Tombol Aksi Preset:**
     1. `⚡ 1. Beli Aqua (Tunai)`: Otomatis memasukkan Aqua 600ml ke keranjang, metode Tunai, langsung checkout & membuka struk thermal.
     2. `⚡ 2. Print + Minum (QRIS)`: Otomatis memasukkan 2 lbr kertas print + 1 minuman via pembayaran QRIS.
     3. `🛒 3. Isi Keranjang Uji Coba`: Mengisi keranjang dummy agar kasir bisa mencoba interaksi manual (ganti qty, custom diskon, nama pelanggan).
     4. `🔄 Reset Transaksi Testing`: Membersihkan riwayat transaksi testing hari ini.

2. **Backend Engine Reset Transaksi (`handleResetSalesToday`):**
   - Mendeteksi parameter `?action=reset_sales`.
   - **Pemulihan Stok Gudang Otomatis:** Menghitung seluruh item dari penjualan hari ini dan mengembalikan `qty_on_hand` di `StockInventory` secara atomik.
   - **Pembersihan Pembukuan:** Menghapus entri `CafeSale`, `CafeSaleItem`, dan `CafePayment` terkait, serta mereset `ShiftSalesSummary` sehingga omset shift kembali bersih Rp 0.

3. **Verifikasi Otomatis:**
   - Menambahkan 2 unit test baru di [`ShiftStockAndSalesFlowTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/ShiftStockAndSalesFlowTest.php).
   - Seluruh 35 automated tests **LULUS 100% (263 assertions)**.


---

## 18. Pembatasan 2 Opsi Pembayaran POS (Cash & QRIS) dan Penegasan Serah Terima Murni Uang Tunai (Cash Only)

Berdasarkan instruksi operasional kasir:

1. **Pembatasan 2 Opsi Pembayaran di Layar Kasir POS:**
   - Menghapus opsi *Transfer Bank/Debit* dari dropdown pembayaran di [`pos_desktop.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/pos_desktop.blade.php).
   - Kasir POS kini hanya memiliki 2 opsi resmi:
     1. 💵 **Tunai (Cash)**
     2. 📱 **QRIS / E-Wallet**
   - Di backend (`penjualanStoreTransaction`), input `payment_method` divalidasi ketat sehingga hanya menerima `cash` atau `qris`.

2. **Rekap & Serah Terima Shift Murni Berupa Uang Tunai Fisik (Cash Only):**
   - Transaksi QRIS otomatis masuk langsung ke rekening bank/merchant pusat (tidak ada wujud fisik lembaran uang di laci).
   - Pada perhitungan target kas sistem di [`AttendanceWebV2Controller.php`](file:///var/www/laravel_apps/bagus/app/Http/Controllers/AttendanceWebV2Controller.php) (`serahTerimaIndex`), seluruh penjualan non-tunai (QRIS) otomatis dipotongkan:
     $$\text{Target Kas Fisik Laci (Cash Only)} = (\text{Total Omset Shift} - \text{Non-Cash / QRIS}) + \text{Modal Awal Laci}$$
   - Pada antarmuka serah terima ([`serah_terima_desktop.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/serah_terima_desktop.blade.php) & [`serah_terima.blade.php`](file:///var/www/laravel_apps/bagus/resources/views/presensi2/serah_terima.blade.php)):
     - Menampilkan baris pemisah omset QRIS: `📱 Non-Tunai / QRIS (Masuk Rekening): -Rp X`.
     - Memberikan penegasan label bahwa perhitungan lembaran uang, target kas sistem, dan nominal serah terima adalah **murni uang fisik Cash di laci**. Kasir tidak lagi mengalami tuduhan selisih minus akibat uang QRIS.

3. **Verifikasi & Pengujian:**
   - Menambahkan 2 feature tests baru di [`ShiftStockAndSalesFlowTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/ShiftStockAndSalesFlowTest.php):
     - `test_pos_desktop_only_offers_cash_and_qris_options` ➡️ **PASSED**
     - `test_serah_terima_calculates_cash_only_excluding_qris_sales` ➡️ **PASSED**
   - Total **37 unit tests** lulus 100% (**273 assertions**) dan lulus formatting **Laravel Pint**.

---

## 19. Refactoring Formulir Serah Terima Shift: Kas Tunai Murni, Estafet Kasir, & Integrasi Cetak Struk

Berdasarkan permintaan penyempurnaan pada formulir Serah Terima Shift (`/presensi2/serah-terima` dan `/presensi2/serah-terima-desktop`):

1. **Metode Penyetoran Kas Murni Serah Terima Uang Tunai (Cash Only):**
   - Opsi setor ke luar (bank, brankas luar, kolektor) pada saat pergantian shift ditiadakan.
   - Pilihan metode penyetoran dikunci ke `next_shift_handover` (Serah Terima Kasir Shift Selanjutnya) secara tunai langsung di hadapan kasir penerima.
2. **Shift & Kasir Penerima Otomatis Sesuai Jadwal:**
   - Dropdown kasir penerima otomatis menampilkan daftar rekan kerja cabang aktif dengan label shift estafet berikutnya (misal: Shift 1 $\to$ Shift 2).
3. **Penyederhanaan Form (Penghapusan Foto Bukti Setor):**
   - Input unggah file `deposit_proof` dihapus karena serah terima uang fisik kasir dilakukan tatap muka langsung di depan laci kasir dan diverifikasi lewat perhitungan lembaran uang.
4. **Alur Clock-Out Tanpa Terlempar Keluar Halaman:**
   - Setelah kasir menekan tombol simpan serah terima dengan opsi *Clock Out Otomatis*, sistem tidak lagi me-redirect kasir ke halaman login/scan depan, melainkan **tetap berada di halaman serah terima** dengan status berhasil. Hal ini memungkinkan kasir memeriksa ringkasan akhir dan langsung mencetak bukti struk.
5. **Tombol Cetak Struk & Rekap Penjualan Cepat:**
   - Box setor kas eksternal diganti menjadi panel aksi terpadu:
     - `🖨️ Cetak Struk Bukti Serah Terima` (Cetak struk thermal untuk arsip laci).
     - `📊 Buka Tabel Rekap Penjualan (Z-Report)` (Membuka detail rekap dan history transaksi kasir).
6. **Tabel Riwayat Estafet Kasir (*Chain of Custody*):**
   - Menampilkan tabel riwayat serah terima yang telah tuntas (`completed`) secara persisten dan transparan.
   - Kolom memuat: Tanggal/Jam, Shift, Kasir Penyerah, Kasir Penerima, Kas Tunai Diserahkan, Modal Ditinggal, Status Kas (Pas/Selisih), dan tombol Cetak Ulang Struk.
7. **Verifikasi:**
   - Seluruh 37 automated tests di [`ShiftStockAndSalesFlowTest.php`](file:///var/www/laravel_apps/bagus/tests/Feature/ShiftStockAndSalesFlowTest.php) **LULUS 100% (273 assertions)**.
   - Kode divalidasi dengan **Laravel Pint**.
