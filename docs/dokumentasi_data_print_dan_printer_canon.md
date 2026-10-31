# Dokumentasi Sistem Monitoring & Sinkronisasi Print Kertas (Canon USB)

## 1. Pendahuluan
Dokumentasi ini menjelaskan arsitektur, cara kerja, dan panduan operasional integrasi pencatatan print kertas dari printer fisik (Canon kabel USB) pada PC Kasir ke dalam sistem Web POS Presensi dan Slip Rekap Harian Kasir.

---

## 2. Latar Belakang & Tujuan
Sebelumnya, pencatatan transaksi kertas print hanya mengandalkan input manual kasir pada aplikasi POS. Hal ini menimbulkan potensi ketidaksesuaian antara:
- Jumlah fisik kertas yang keluar dari printer (kertas terpakai).
- Uang kas pembayaran cetak yang masuk ke laci kasir.
- Laporan serah terima shift dan inventaris stok sisa kertas di laci.

Integrasi ini bertujuan untuk:
1. **Otomatisasi Pendataan**: Setiap lembar dokumen yang dicetak oleh pelanggan/kasir otomatis terhitung oleh sistem secara akurat.
2. **Pencegahan Kebocoran Kas**: Menghilangkan selisih kas antara cetakan fisik dan setoran uang kasir.
3. **Audit Trail Lengkap**: Menyimpan histori dokumen, jumlah halaman, waktu cetak, dan nilai nominal per shift.

---

## 3. Komponen Sistem
Sistem ini terdiri dari 4 komponen utama:

1. **Agent Spooler Windows (PC Kasir)**
   - Berupa script ringan yang berjalan di latar belakang Windows pada PC Kasir.
   - Mengawasi Windows Print Spooler (`Win32_PrintJob`) setiap kali printer Canon USB menyelesaikan pencetakan dokumen.
   - Mengirimkan metadata cetak (nama printer, nama dokumen, jumlah halaman, waktu) ke server POS melalui koneksi jaringan lokal.

2. **Backend Server POS (Laravel)**
   - Menerima log cetak dan mencatatnya ke tabel riwayat print job.
   - Mengelompokkan data berdasarkan tanggal operasional dan shift kerja aktif cabang terkait.
   - Menyediakan endpoint status real-time untuk memantau status printer dan riwayat cetakan.

3. **Halaman Penjualan POS (Front-End Kasir)**
   - Tombol status terpadu di navbar atas: menampilkan jumlah lembar dan total nominal rupiah secara langsung.
   - Lampu indikator hijau: menandakan printer Canon USB dalam kondisi online dan terhubung.
   - Pembaruan otomatis setiap 10 detik tanpa perlu reload halaman.
   - Notifikasi mengambang (toast) yang muncul otomatis saat ada dokumen baru yang dicetak.
   - Modal detail: menampilkan counter fisik hardware, form penguncian data shift, dan tabel histori dokumen yang dicetak pada shift bersangkutan.

4. **Slip Rekap Harian & Cetak Struk Kasir**
   - Mengintegrasikan data cetak ke dalam kategori produk kertas pada struk rekap.
   - Mengalokasikan pengurangan pemakaian stok fisik kertas A4 di laci kasir sesuai jumlah lembar yang keluar dari printer.
   - Mengunci angka cetak secara permanen saat shift ditutup untuk keperluan audit serah terima shift.

---

## 4. Alur Kerja Operasional Kasir (SOP)

### A. Persiapan Awal Shift
1. Pastikan kabel USB printer Canon tertancap kokoh ke PC Kasir dan printer dalam keadaan menyala (*Power ON*).
2. Buka file Agent Printer di PC Kasir jika belum berjalan otomatis saat komputer menyala.
3. Pada halaman POS, pastikan tombol **Data & Kunci Print** di bagian atas menampilkan dot hijau (Online).

### B. Saat Pelanggan Melakukan Print
1. Pelanggan atau kasir mencetak dokumen seperti biasa dari aplikasi apapun (Word, PDF Reader, Web Browser).
2. Printer mencetak lembaran fisik.
3. Dalam hitungan detik, tombol di layar POS akan memberikan animasi getar lembut serta notifikasi:
   > *"Terdeteksi print dokumen baru: +N lembar (Canon USB)!"*
4. Angka lembar dan nominal rupiah di layar kasir otomatis bertambah secara real-time.

### C. Tutup Shift & Serah Terima
1. Klik tombol **Data & Kunci Print** di layar POS.
2. Kasir dapat memeriksa tabel histori untuk memastikan seluruh dokumen yang keluar sudah sesuai.
3. Tekan tombol **Kunci Data Print Shift Ini** untuk membekukan data sebelum serah terima kasir berikutnya.
4. Saat kasir melakukan proses **Selesai Shift** dan menghitung fisik uang di laci (*Blind Cash Count*), pendapatan print kertas otomatis masuk ke dalam total setoran yang wajib diserahkan.
5. Pada cetakan struk slip serah terima, subtotal kategori kertas dan pemakaian stok kertas akan tertera sesuai data aktual printer.

---

## 5. Lokasi File & Skrip Terkait
- **Dokumentasi Sistem**: `docs/dokumentasi_data_print_dan_printer_canon.md`
- **File Unduhan Agent Kasir**: Dapat diunduh langsung oleh kasir melalui tombol *Agent PC Kasir (.bat)* di dalam modal print halaman POS.
- **Pengaturan Tarif Default**: Rp 1.000 per lembar (dapat disesuaikan pada konfigurasi sistem).

---

## 6. Penanganan Masalah (Troubleshooting)

| Gejala Masalah | Penyebab | Solusi |
| :--- | :--- | :--- |
| Indikator printer berwarna abu-abu / offline | Komputer kasir belum menjalankan agent atau kabel USB terlepas | Pastikan kabel USB terpasang, lalu jalankan file agent bat di desktop PC kasir |
| Lembar print bertambah tapi uang laci tidak cocok | Ada print dokumen pribadi/gagal cetak yang belum dicatat | Periksa histori cetak di modal print untuk memverifikasi dokumen yang keluar |
| Tombol POS tidak bertambah saat print | Koneksi jaringan lokal PC kasir ke server sedang terputus | Klik tombol *Ambil Data dari Printer Canon* untuk menyinkronkan data secara manual |

