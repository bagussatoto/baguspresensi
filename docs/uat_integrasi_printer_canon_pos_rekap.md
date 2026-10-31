# 📋 Dokumen UAT (User Acceptance Testing)
## Integrasi Printer Canon USB, Auto-Refresh Real-Time POS, & Rekonsiliasi Slip Rekap Harian

**Target Sistem:** Aplikasi Presensi & POS Kasir v2  
**Modul Pengujian:** Monitoring Hardware Printer Canon USB, Auto-Refresh POS, dan Rekonsiliasi Rekap Kasir  
**Perangkat Diuji:** PC Kasir Windows, Printer Canon Kabel USB, Web POS, Printer Thermal Struk  
**Tanggal Pengujian:** 31 Oktober 2026  
**Status Uji Sistem:** Siap Diujicobakan oleh Pengguna (User Acceptance)  

---

## 🎯 1. Informasi Pengujian & Peserta UAT

| Peran | Nama Personel | Tanggal Uji | Tanda Tangan / Persetujuan |
| :--- | :--- | :--- | :--- |
| **Kasir Pelaksana (Tester 1)** | Staf Kasir Shift Aktif | .................... | [ ] Diterima / [ ] Ada Catatan |
| **Supervisor / Kepala Outlet** | Supervisor Cabang | .................... | [ ] Diterima / [ ] Ada Catatan |
| **Pemilik / Manajemen (Owner)** | Manajemen Operasional | .................... | [ ] Diterima / [ ] Ada Catatan |

---

## 🧪 2. Skenario & Lembar Checklist UAT

### Skenario 1: Kesiapan Perangkat & Indikator Status Online Printer
*Tujuan: Memastikan PC Kasir mengenali printer Canon USB dan status online tampil di halaman POS kasir.*

| No | Langkah Pengujian | Hasil yang Diharapkan | Status | Catatan Penguji |
| :-: | :--- | :--- | :-: | :--- |
| 1.1 | Nyalakan printer Canon dan sambungkan kabel USB ke PC Kasir. | Printer menyala normal dan lampu indikator printer siap. | [ ] Lulus<br>[ ] Gagal | |
| 1.2 | Jalankan skrip agent di PC Kasir (`Jalankan_Agent_Printer_Canon.bat`). | Jendela agent aktif dan menampilkan pesan siap memonitor dokumen. | [ ] Lulus<br>[ ] Gagal | |
| 1.3 | Buka halaman Penjualan POS di browser kasir. | Tombol `Data & Kunci Print` di navbar atas menampilkan lampu dot hijau menyala (Online). | [ ] Lulus<br>[ ] Gagal | |
| 1.4 | Klik tombol `Data & Kunci Print` untuk membuka modal. | Status box menampilkan ikon `🟢`, nama printer `Canon USB Printer`, dan keterangan `Tersambung (Online)`. | [ ] Lulus<br>[ ] Gagal | |

---

### Skenario 2: Auto-Detection & Auto-Refresh Real-Time Dokumen Cetak
*Tujuan: Memastikan setiap pencetakan dokumen fisik otomatis terdeteksi tanpa kasir me-reload halaman.*

| No | Langkah Pengujian | Hasil yang Diharapkan | Status | Catatan Penguji |
| :-: | :--- | :--- | :-: | :--- |
| 2.1 | Lakukan pencetakan dokumen uji coba (misal 3 halaman) dari PC Kasir ke printer Canon. | Printer mengeluarkan 3 lembar kertas fisik secara normal. | [ ] Lulus<br>[ ] Gagal | |
| 2.2 | Amati layar POS kasir tanpa menyentuh tombol reload / refresh browser. | Dalam waktu maksimal 10 detik, tombol navbar POS bergoyang/highlight lembut dan muncul notifikasi: *"Terdeteksi print dokumen baru: +3 lembar (Canon USB)!"*. | [ ] Lulus<br>[ ] Gagal | |
| 2.3 | Periksa angka counter di tombol navbar POS. | Jumlah lembar otomatis bertambah 3 dan nominal bertambah Rp 3.000. | [ ] Lulus<br>[ ] Gagal | |
| 2.4 | Buka modal `Data & Kunci Print`. | Counter Fisik menunjukkan penambahan 3 lembar, dan tabel histori menampilkan baris nama dokumen, waktu, dan jumlah 3 lembar dengan label `Canon USB`. | [ ] Lulus<br>[ ] Gagal | |

---

### Skenario 3: Penjualan Manual Kertas Print via Menu POS
*Tujuan: Memastikan transaksi cetak manual lewat menu POS tetap tercatat dan tidak bentrok dengan hardware.*

| No | Langkah Pengujian | Hasil yang Diharapkan | Status | Catatan Penguji |
| :-: | :--- | :--- | :-: | :--- |
| 3.1 | Pilih item `Print Kertas A4` dari menu produk POS kasir sebanyak 2 lembar. | Item masuk ke keranjang belanja POS kasir dengan total Rp 2.000. | [ ] Lulus<br>[ ] Gagal | |
| 3.2 | Tekan tombol `Simpan Transaksi (Tunai)`. | Transaksi berhasil tersimpan, struk kasir keluar, dan tidak terjadi pesan error. | [ ] Lulus<br>[ ] Gagal | |
| 3.3 | Buka modal `Data & Kunci Print`. | Sistem mengombinasikan data manual POS dan data printer spooler secara rekonsiliatif dan menampilkan riwayat transaksi POS. | [ ] Lulus<br>[ ] Gagal | |

---

### Skenario 4: Penguncian Data Shift & Tutup Shift (Blind Cash Count)
*Tujuan: Memastikan kasir dapat membekukan data print sebelum serah terima dan nilai terkunci secara permanen.*

| No | Langkah Pengujian | Hasil yang Diharapkan | Status | Catatan Penguji |
| :-: | :--- | :--- | :-: | :--- |
| 4.1 | Di dalam modal print, tekan tombol `Kunci Data Print Shift Ini`. | Status berubah menjadi badge merah `TERKUNCI`, nominal dan lembar dibekukan dengan stempel waktu kunci. | [ ] Lulus<br>[ ] Gagal | |
| 4.2 | Tekan tombol `Selesai Shift (Tutup Kasir)`. | Muncul formulir penghitungan fisik uang laci (*Blind Cash Count*). | [ ] Lulus<br>[ ] Gagal | |
| 4.3 | Masukkan pecahan uang fisik kas di laci kasir dan simpan. | Sistem berhasil memvalidasi setoran, sesi shift ditutup, dan kasir diarahkan ke halaman Rekap Harian. | [ ] Lulus<br>[ ] Gagal | |

---

### Skenario 5: Verifikasi Slip Rekap Harian & Cetak Struk
*Tujuan: Memastikan seluruh data cetak printer mengalir utuh ke slip serah terima dan pemakaian stok kertas.*

| No | Langkah Pengujian | Hasil yang Diharapkan | Status | Catatan Penguji |
| :-: | :--- | :--- | :-: | :--- |
| 5.1 | Pada layar Rekap Harian, periksa Card 3 `Pendapatan Print & Kertas`. | Menampilkan nominal pendapatan print, jumlah lembar, dan badge `Canon USB Live` / `Terkunci`. | [ ] Lulus<br>[ ] Gagal | |
| 5.2 | Periksa tabel `Rekap Produk per Kategori` pada bagian `KERTAS` di slip. | Menampilkan baris kertas dengan jumlah lembar yang sesuai, subtotal yang cocok dengan Grand Total, dan harga per lembar proporsional. | [ ] Lulus<br>[ ] Gagal | |
| 5.3 | Periksa tabel `4. Kertas Print & Perlengkapan` di slip rekap. | Kolom `Pakai` pada kertas A4 otomatis terisi sesuai jumlah lembar yang dicetak dari printer Canon. | [ ] Lulus<br>[ ] Gagal | |
| 5.4 | Periksa `Summary Serah Terima` dan nilai Grand Total setoran. | Nilai pendapatan cetak printer masuk ke total penerimaan kas yang diserahterimakan ke shift berikutnya. | [ ] Lulus<br>[ ] Gagal | |
| 5.5 | Tekan tombol `Cetak Slip Rekap Kasir` ke printer thermal. | Struk tercetak rapi, font jelas, tabel 4 shift serah terima proporsional, dan tidak ada angka yang terpotong. | [ ] Lulus<br>[ ] Gagal | |

---

## 🏁 3. Kesimpulan & Rekomendasi Hasil UAT

*(Diisi oleh Supervisor / Penanggung Jawab Outlet setelah seluruh skenario diuji)*

- [ ] **DITERIMA TANPA CATATAN**: Sistem siap digunakan penuh untuk operasional harian.
- [ ] **DITERIMA DENGAN CATATAN**: Ada penyesuaian minor namun tidak menghambat operasional.
- [ ] **DITOLAK / PERLU PERBAIKAN**: Ada fungsi kritis yang belum sesuai.

**Catatan Tambahan:**  
......................................................................................................................................  
......................................................................................................................................  
......................................................................................................................................  

**Disetujui Oleh:**  

*(..................................................)*  
Supervisor / Manajemen Operasional

