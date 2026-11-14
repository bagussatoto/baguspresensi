# 📊 Review Projek, Daftar Pekerjaan Hari Ini, & Rekomendasi Pengembangan

**Tanggal Review:** Sabtu, 14 November 2026  
**Proyek:** Sistem Presensi Karyawan V2, Kasir POS Desktop, Serah Terima Shift, & Admin ERP Multi-Cabang (PT Maya Graha Kencana)  
**Repository:** `https://github.com/bagussatoto/baguspresensi.git` (Branch: `main`)

---

## 1. Review Status Projek Saat Ini (Apa yang Sudah Selesai)

Secara keseluruhan, fondasi utama operasional cabang (Karyawan/Kasir) hingga pengawasan pusat (Admin ERP/Owner) telah terhubung dari hulu ke hilir (**End-to-End**):

| Modul / Area | Status | Ringkasan Capaian Hari Ini |
| :--- | :---: | :--- |
| **1. Portal Presensi Karyawan (`/presensi2/scan`)** | ✅ **Siap Pakai** | Check-In/Out dengan GPS & Selfie, validasi durasi kerja minimal 30 menit, proteksi wajib Serah Terima sebelum Check-Out, dan perbaikan bug menit negatif saat simulasi. |
| **2. Audit Cek Stok Shift (`/presensi2/stok`)** | ✅ **Siap Pakai** | Input fisik aktual *default* `0` (blind count) untuk Kertas A4/F4, Minuman, dan Makanan. Selisih fisik otomatis tercatat di database & mengirim alert WhatsApp ke Admin. |
| **3. Kasir POS Desktop (`/pos`)** | ✅ **Siap Pakai** | Transaksi FnB, Billing Warnet, & Counter Printer Canon USB. Penguncian shift kini difilter spesifik per `shift_id` (Shift 1 tutup tidak lagi mengunci Shift 2). |
| **4. Rekap & Serah Terima Desktop (`/rekap-desktop`, `/serah-terima-desktop`, `/serah-terima-v2`)** | ✅ **Siap Pakai** | Alur menu berurutan (**POS ➔ Rekap ➔ Serah Terima**), kalkulator denominasi uang laci, modal konfirmasi *Logout & Kunci PC Kasir*, serta 3 tabel Serah Terima V2 (*Serah Terima, Siap Setor, History Setor*). |
| **5. Pemisahan Tampilan Produksi vs Simulasi (`?demo=1`)** | ✅ **Siap Pakai** | Seluruh kotak **🧪 Mode Simulasi** disembunyikan secara *default* agar kasir tidak bingung; hanya muncul jika URL ditambahkan `?demo=1` atau `?simulasi=1`. |
| **6. Dashboard Admin ERP & Otorisasi (`/admin/ERP`)** | ✅ **Siap Pakai** | Badge notifikasi antrean real-time (`X Baru`) di sidebar, perbaikan tombol **✓ Setujui** untuk klaim **⚡ Pemadaman Listrik PLN (Rp 0)**, kolom **Selisih Kas Laci**, dan tombol **🔓 Buka Kunci Shift**. |

---

## 2. Apa yang Masih Perlu Dikerjakan (Remaining Tasks & Penyempurnaan)

Berdasarkan evaluasi kode dan alur operasional saat ini, berikut adalah beberapa bagian yang **masih perlu diselesaikan atau disempurnakan**:

1. **Sinkronisasi Penuh Alur "Siap Setor ➔ History Setor" pada Serah Terima V2 (`/presensi2/serah-terima-v2`)**:
   - Memastikan saat Supervisor/Kolektor menekan tombol **`Setor`** pada tabel *Siap Setor* (Header Biru Muda), seluruh shift yang dicentang otomatis berpindah ke tabel *History Setor* (Header Oranye), mencetak bukti kwitansi setoran gabungan (Shift `[1] [2] [3] [4]`), dan otomatis membuat **Jurnal Umum Kas/Bank (SAK)** di Admin ERP.
2. **Verifikasi Terima Setoran Kas di Dashboard Admin ERP (`/admin/kas` & `/admin/penjualan/serah-terima`)**:
   - Ketika kasir/kolektor mencatat *Setor Tunai / Transfer Bank*, Admin Keuangan di pusat perlu memiliki tombol **"✓ Konfirmasi Dana Masuk Rekening/Brankas Pusat"** agar status setoran berubah dari `Menunggu Verifikasi` menjadi `Diterima Pusat (Verified)`.
3. **Penanganan Tindak Lanjut Selisih Kas Minus & Selisih Stok di Admin**:
   - Saat ini Admin sudah **melihat** indikator merah jika kas laci kurang (`⚠️ Kurang -Rp ...`) atau stok fisik kurang (`⚠️ X Item Selisih`), namun belum ada tombol tindakan penyelesaian (*Resolution Action*) seperti:
     - *Potong Gaji / Kasbon Karyawan*
     - *Toleransi / Beban Kerugian Selisih Kas*
     - *Sudah Diganti Tunai oleh Kasir*
4. **Pembersihan Sisa Tampilan Mobile Penjualan (`/presensi2/penjualan`)**:
   - Memastikan halaman penjualan versi mobile (`/presensi2/penjualan`) dan rekap versi mobile (`/presensi2/rekap`) memiliki konsistensi penuh dengan versi Desktop terkait aturan `?demo=1` dan status kunci per `shift_id`.
5. **Pengujian Lapangan (UAT) Siklus Penuh 4 Shift Berurutan**:
   - Menguji transisi estafet **Shift 1 ➔ Shift 2 ➔ Shift 3 ➔ Shift 4 (Tutup Hari)** secara berkesinambungan untuk memastikan *Uang Tinggal di Laci (Modal Shift Berikutnya)* ter-oper secara akurat dari shift ke shift hingga penutupan hari.

---

## 3. Daftar Pekerjaan Hari Ini (To-Do List — 14 November 2026)

Berikut adalah daftar pekerjaan prioritas yang dapat kita eksekusi hari ini:

### 🔴 Prioritas Tinggi (Operasional Inti Kasir & Admin)
- [x] **Tugas 1 — Uji & Sempurnakan Eksekusi Tombol "Setor" di `/presensi2/serah-terima-v2`**:
  - Penggabungan multi-shift dalam satu nomor bukti setoran kwitansi resmi (`deposit_receipt_no` & `deposit_batch_id`) selesai, seleksi checkbox real-time berfungsi, badge kotak shift `[1] [2] [3] [4]` tampil akurat di *History Setor*. (Commit `0adf9e8`)
- [x] **Tugas 2 — Fitur Penyelesaian (Resolution) Selisih Kas & Stok di Admin ERP**:
  - Form & aksi resolusi selisih kas/stok shift ditambahkan pada halaman `/admin/penjualan/penyesuaian-billing` dengan 4 opsi keputusan resmi, status dinamis, dan audit log WORM ISO 27001. (Commit `c3cfb67`)
- [x] **Tugas 3 — Konsistensi Mode `?demo=1` di Halaman Mobile & Banner Peringatan Demo**:
  - Banner peringatan mode demo (`🧪 MODE DEMO / SIMULASI AKTIF`) telah dipasang terpusat di `resources/views/layouts/presensi2.blade.php`, dan seluruh halaman operasional mobile bersih dari kebocoran tombol simulasi pada mode produksi. (Commit `3b6e4f4`)

### 🟡 Prioritas Menengah (Integrasi Akuntansi & Pelaporan)
- [x] **Tugas 4 — Integrasi Otomatis Setoran Shift ke Jurnal Kas/Bank (`AutoJournalService`)**:
  - Saat setoran shift dikonfirmasi/disimpan via batch setor (`/presensi2/serah-terima-v2`) atau diverifikasi oleh Admin, otomatis menerbitkan jurnal akuntansi pembukuan berpasangan (Double-Entry GL) *Debit: Rekening Bank BCA (1-11001) / Kas Operasional (1-10002)* dan *Kredit: Kas Laci Kasir (1-10001)*.
- [ ] **Tugas 5 — Rapikan Hak Akses Direktori `docs/2026-11/`**:
  - Menyatukan folder dokumentasi November (`2026-11-03` s/d `2026-11-14`) ke dalam `docs/2026-11/` setelah izin kepemilikan folder (`chown`) diperbaiki.

### 🟢 Prioritas Pengujian (Quality Assurance)
- [x] **Tugas 6 — Automated Feature Test Siklus Estafet Multi-Shift**:
  - Pengujian otomatis PHPUnit menyeluruh (43 assertions green) di [ShiftEstafetMultiShiftLifecycleTest.php](file:///var/www/laravel_apps/bagus/tests/Feature/ShiftEstafetMultiShiftLifecycleTest.php) mencakup 6 langkah skenario end-to-end:
    1. *Shift 1 Presensi & Transaksi Kas POS/FnB*.
    2. *Shift 1 Closing, Blind Cash Count, dan Serah Terima*.
    3. *Shift 2 Masuk & Transaksi Independen (terbukti TIDAK terkunci oleh closing Shift 1)*.
    4. *Shift 2 Closing & Penggabungan Batch Setoran Bersama Shift 1 (`deposit_batch_id`)*.
    5. *Admin Pusat Verifikasi Penerimaan Setoran Kas Cabang & Terbit Jurnal GL Otomatis*.
    6. *Admin Otorisasi Penyesuaian Billing & Proteksi Buka Kunci Shift (24 Jam & Bank Deposit Guard)*.

---

## 4. Saran & Rekomendasi Strategis

Berikut adalah saran perbaikan untuk meningkatkan keandalan, keamanan, dan kemudahan operasional sistem ke depan:

### 💡 A. Saran Operasional & Antarmuka (UX)
1. **Mode Pelatihan Terpusat (Toggle Demo di Pengaturan Admin atau Banner Khusus)**:
   - Penggunaan `?demo=1` di URL sudah sangat tepat untuk menyembunyikan fitur simulasi dari kasir. Sebagai tambahan, **saat `?demo=1` aktif**, sebaiknya tampilkan **pita/watermark melayang di pojok atas layar** bertuliskan `"⚠️ MODE DEMO / SIMULASI AKTIF"` agar Admin/Tester yang sedang menguji tidak lupa bahwa mereka sedang berada di mode demo.
2. **Standarisasi Satu Halaman Serah Terima Desktop Utama**:
   - Saat ini terdapat **Serah Terima v1** (`/presensi2/serah-terima-desktop` — fokus hitung pecahan uang kasir) dan **Serah Terima v2** (`/presensi2/serah-terima-v2` — fokus tabel *Serah Terima, Siap Setor, History Setor*).
   - **Saran:** Jadikan `/presensi2/serah-terima-desktop` sebagai layar bagi **Kasir** saat tutup shift, dan ketika kasir selesai menyimpan, arahkan tombol lihat rekap setoran ke `/presensi2/serah-terima-v2` untuk **Supervisor/Kolektor Setoran**.

### 🔒 B. Saran Kontrol Internal & Keamanan (SoD / Audit)
1. **Batas Maksimal "Buka Kunci Shift" (Re-Open Shift)**:
   - Fitur **🔓 Buka Kunci Shift** di Admin sangat membantu jika kasir salah klik. Namun untuk menjaga integritas laporan keuangan, sebaiknya kunci shift **tidak bisa dibuka lagi apabila kas shift tersebut sudah disetorkan ke Bank (`History Setor`)** atau sudah melewati batas 1×24 jam tanpa otorisasi Owner.
2. **Foto Bukti Struk Transfer / Kwitansi Setor**:
   - Pada saat melakukan aksi **`Setor`** di tabel *Siap Setor* (`/presensi2/serah-terima-v2`), tambahkan opsi unggah foto bukti transfer ATM/m-Banking atau foto amplop setoran brankas agar Admin Keuangan dapat memverifikasi tanpa perlu meminta bukti manual lewat chat.

### ⚙️ C. Saran Teknis & Infrastruktur
1. **Pembersihan Otomatis Data Simulasi Sebelum Go-Live Cabang**:
   - Sediakan satu tombol khusus Super-Admin (**"🧹 Bersihkan Seluruh Data Simulasi/Testing"**) yang hanya menghapus transaksi dengan penanda `Simulasi:` atau catatan testing, tanpa menghapus Master Data Produk, Cabang, Jadwal Shift, maupun Akun Karyawan yang sudah terdaftar.
2. **Monitoring Jadwal Cron / Queue WhatsApp**:
   - Pastikan pengiriman notifikasi WhatsApp (saat ada selisih stok, selisih kas minus, atau pengajuan klaim mati lampu PLN) memiliki *fallback log* di dashboard Admin sehingga jika gateway WhatsApp sedang *offline*, Admin tetap melihat *alert* kuning di bagian atas halaman `/admin/ERP`.
