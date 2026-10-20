# 📖 Panduan Pengguna & Standar Operasional Prosedur (SOP) Sistem Presensi
**Organisasi:** Maya Graha Kencana (MGK)  
**Versi Dokumen:** 1.0 (Produksi)  
**Target Pengguna:** Karyawan/Operator Warnet, Kasir POS, Supervisor Cabang, dan HRD / Admin ERP  
**Dokumen Pendukung:** [docs/roadmap_presensi.md](file:///var/www/laravel_apps/bagus/docs/roadmap_presensi.md) & [docs/tahapan_eksekusi_presensi.md](file:///var/www/laravel_apps/bagus/docs/tahapan_eksekusi_presensi.md)

---

## 🎯 1. Pendahuluan & Gambaran Umum

Sistem Presensi Karyawan Maya Graha Kencana (MGK) dirancang untuk memfasilitasi pencatatan kehadiran yang akurat, transparan, dan anti-kecurangan di seluruh unit operasional (kantor cabang dan warnet 24 jam).

Sistem ini terdiri dari 3 akses antarmuka utama:
1. **Portal Web & PWA Karyawan Mandiri (`/presensi`)**: Antarmuka mobile-first untuk smartphone karyawan (Selfie camera + GPS geofencing + permohonan izin).
2. **Kios Presensi POS Kasir Warnet (`/warnet` Tab 6)**: Kios terintegrasi di meja kasir untuk operator warnet saat pergantian giliran shift.
3. **Panel Admin ERP & Rekapitulasi Presensi (`/admin/presensi`)**: Dashboard supervisor dan HRD untuk monitoring langsung, persetujuan cuti/lembur, dan ekspor data penggajian (payroll).

---

## 📱 2. Panduan Karyawan & Operator (Portal Mobile / PWA)

### 2.1 Mengakses Portal & Memasang Aplikasi (PWA)
Sistem ini berbasis **Progressive Web App (PWA)**, dapat diakses langsung melalui browser smartphone tanpa perlu mengunduh dari Play Store / App Store:
1. Buka browser smartphone (disarankan **Google Chrome** di Android atau **Safari** di iOS).
2. Kunjungi alamat portal: `http://<domain-server-mgk>/presensi`.
3. **Instal ke Layar Utama (Add to Home Screen):**
   - **Android (Chrome):** Ketuk menu titik tiga (⋮) di kanan atas &rarr; pilih **"Tambahkan ke Layar Utama" (Install App)**.
   - **iPhone (Safari):** Ketuk tombol Bagikan (ikon kotak dengan panah atas) &rarr; pilih **"Tambah ke Layar Utama" (Add to Home Screen)**.
4. Ikon aplikasi **Presensi MGK** akan muncul di layar depan smartphone dan dapat dibuka seperti aplikasi native.

### 2.2 Prosedur Login Mandiri
1. Pada layar login (`/presensi/login`), pilih nama Anda dari daftar karyawan aktif atau gunakan identifikasi akun.
2. Pastikan cabang yang tercantum sesuai dengan lokasi penempatan tugas Anda.
3. Tekan tombol **"Masuk ke Portal"**. Sesi Anda akan tersimpan otomatis di perangkat tersebut.

### 2.3 Prosedur Presensi Masuk (Clock-In)
1. Pada menu navigasi bawah, ketuk menu **"Scan"** atau tekan tombol **"Absen Masuk Sekarang"** di Beranda.
2. **Izinkan Akses Perangkat:**
   - Saat browser meminta izin:
     - Pilih **"Izinkan Saat Menggunakan Aplikasi"** untuk **Akses Lokasi (GPS)**.
     - Pilih **"Izinkan"** untuk **Akses Kamera**.
3. **Periksa Radar GPS:**
   - Perhatikan kartu **Status Radar GPS**:
     - 🟢 **Hijau (Dalam Radius):** Jarak Anda &le; 100 meter dari kantor. Presensi diizinkan.
     - 🔴 **Merah (Di Luar Jangkauan):** Jarak Anda melebihi batas radius kantor. Tombol absen akan dinonaktifkan hingga Anda berada di dalam area kantor.
4. **Pengambilan Foto Selfie:**
   - Arahkan wajah Anda ke dalam lingkaran pemandu (*reticle*).
   - Pastikan pencahayaan cukup dan wajah terlihat jelas (tidak tertutup masker tebal atau helm).
   - Tekan tombol **"Ambil Foto Selfie"**.
   - Periksa pratinjau foto. Jika buram, tekan **"Foto Ulang"**.
5. **Kirim Presensi:**
   - Tekan tombol **"Kirim Presensi Masuk"**.
   - Sistem akan memproses dan menampilkan notifikasi sukses beserta status kehadiran Anda (**Hadir Tepat Waktu** atau **Terlambat**).

### 2.4 Prosedur Presensi Pulang (Clock-Out)
1. Setelah menyelesaikan jam giliran dinas, buka kembali menu **"Scan"**.
2. Sistem otomatis mengalihkan mode ke **Presensi Pulang (Clock-Out)**.
3. Ambil foto selfie penutup di lokasi kantor (dengan radar GPS hijau).
4. Tekan tombol **"Kirim Presensi Pulang"**.
5. Sistem akan menampilkan rincian:
   - Jam kepulangan.
   - Total durasi jam kerja aktif.
   - Jam lembur otomatis jika bekerja melebihi batas toleransi akhir shift (>15 menit).

### 2.5 Jadwal Rotasi Shift Kerja (Operasional Warnet 24 Jam)
Maya Graha Kencana menerapkan 5 master template shift:
* **Shift 1 (Dini Hari):** `01:00 - 07:00 WIB` (Toleransi hadir: s/d 01:15 WIB).
* **Shift 2 (Pagi):** `07:00 - 13:00 WIB` (Toleransi hadir: s/d 07:15 WIB).
* **Shift 3 (Siang/Sore):** `13:00 - 19:00 WIB` (Toleransi hadir: s/d 13:15 WIB).
* **Shift 4 (Lintas Malam / Overnight):** `19:00 - 01:00 WIB` (Toleransi hadir: s/d 19:15 WIB).
* **Shift BO (Back Office / Staff):** `08:30 - 17:30 WIB` (Toleransi hadir: s/d 08:45 WIB).

> ⚠️ **Catatan Penting Shift 4 (Lintas Malam):**
> Karyawan yang bertugas di Shift 4 masuk jam 19:00 malam dan pulang jam 01:00 dini hari keesokan harinya. Sistem menggunakan konsep **Operational Work Date**, sehingga jam pulang jam 01:00 dini hari **tetap tercatat pada tanggal hari dinas yang sama**, bukan tanggal baru.

### 2.6 Pengajuan Izin / Cuti / Sakit Mandiri
1. Ketuk menu **"Izin"** pada navigasi bawah.
2. Tekan tombol **"Buat Pengajuan Baru"**.
3. Isi data permohonan:
   - **Jenis Izin:** Pilih Cuti Tahunan (*Annual*), Sakit (*Sick*), Izin Keperluan Pribadi (*Permission*), atau Darurat (*Emergency*).
   - **Tanggal Mulai & Tanggal Selesai**: Tentukan rentang hari tidak masuk.
   - **Alasan:** Tuliskan keterangan yang jelas dan valid.
   - **Lampiran Dokumen:** Upload foto surat dokter, surat undangan dinas, atau dokumen pendukung (format JPG, PNG, atau PDF).
4. Tekan **"Kirim Permohonan"**.
5. Status awal pengajuan adalah `Pending`. Karyawan dapat memantau apakah permohonan telah di-`Approved` atau di-`Rejected` oleh supervisor langsung di halaman riwayat izin.

### 2.7 Pengajuan Koreksi Presensi Mandiri (Lupa Absen / Kendala HP)
1. Pada Beranda portal mobile (`/presensi`), pilih menu **"✏️ Koreksi Presensi"** (`/presensi/koreksi`).
2. Isi formulir:
   - Tentukan **Tanggal Dinas**.
   - Pilih **Shift Kerja** yang bersangkutan.
   - Pilih opsi koreksi: **Masuk & Pulang**, **Hanya Masuk**, atau **Hanya Pulang**.
   - Masukkan estimasi **Jam Masuk/Pulang Riil**.
   - Tuliskan **Alasan Kendala** (misal: HP habis baterai, scan offline) dan unggah foto/lampiran bukti jika ada.
3. Tekan **"Kirim Pengajuan Koreksi"**. Status awal `Pending` menunggu persetujuan supervisor.

### 2.8 Permohonan Tukar Shift Antar Karyawan (Shift Swap)
Sistem memfasilitasi pertukaran giliran dinas antar operator/kasir di cabang yang sama melalui **persetujuan dua arah (*two-phase approval*)**:
1. Pada Beranda portal mobile, pilih menu **"🔄 Tukar Shift Rekan"** (`/presensi/tukar-shift`).
2. **Mengajukan Tukar Shift:**
   - Pilih nama rekan kerja yang dituju (hanya staf aktif satu cabang).
   - Tentukan tanggal dinas & shift asal Anda.
   - Tentukan tanggal dinas & shift rekan yang ingin ditukar.
   - Tuliskan alasan pertukaran dan tekan **"Ajukan Permohonan Tukar Shift"**.
3. **Tahap 1 — Respon Rekan Kerja:**
   - Rekan kerja yang dituju akan menerima notifikasi pada kartu **"Permintaan Tukar Shift Masuk"**.
   - Rekan kerja dapat menekan tombol **"Setujui Tukar"** atau **"Tolak"** (disertai catatan penolakan).
4. **Tahap 2 — Verifikasi Supervisor:**
   - Setelah rekan kerja menyetujui, status berubah menjadi `Menunggu Supervisor`.
   - Supervisor meninjau di Panel Admin ERP untuk menyetujui (*Approve*) atau menolak (*Reject*).

---

## 🖥️ 3. Panduan Operator Kasir (Kios Presensi POS Warnet)

Untuk operator yang bertugas di meja kasir warnet dan tidak menggunakan smartphone pribadi, presensi dapat dilakukan melalui komputer kasir:
1. Buka aplikasi POS Warnet di browser kasir (`/` atau `/warnet`).
2. Klik **Tab 6: Presensi & Absensi Operator**.
3. Pilih nama operator yang akan bertugas.
4. Masukkan kode PIN keamanan.
5. Klik **"Absen Masuk (Clock In)"** saat memulai giliran jaga atau **"Absen Pulang (Clock Out)"** saat serah terima kasir kepada operator shift berikutnya.
6. Catatan kehadiran langsung tercatat dengan metode `WebKiosk` dan otomatis tersinkronisasi ke laporan keuangan kasir dan panel admin HRD.

---

## 👔 4. Panduan Supervisor & Admin HRD (Panel Admin ERP)

### 4.1 Monitoring Kehadiran Harian (Live Feed)
1. Masuk ke Panel Admin ERP di menu **Presensi** (`/admin/presensi`).
2. Halaman ini menyajikan tabel status kehadiran real-time seluruh staf hari ini:
   - **Kartu Ringkasan:** Menampilkan total karyawan hadir, datang terlambat, sedang izin/cuti, dan alpa (belum hadir).
   - **Filter Cepat:** Anda dapat menyaring data berdasarkan **Kantor Cabang** atau **Shift Kerja**.
   - **Foto Selfie & Bukti GPS:** Klik tombol mata / foto pada baris karyawan untuk melihat foto wajah saat scan masuk dan jarak GPS terhadap kantor.

### 4.2 Rekapitulasi Presensi & Ekspor Penggajian (Payroll)
1. Buka menu **Presensi &rarr; Rekapitulasi** (`/admin/presensi/rekap`).
2. Pilih **Bulan**, **Tahun**, dan **Cabang** yang ingin direkap.
3. Tabel menyajikan agregasi data kehadiran setiap karyawan:
   - Jumlah hari hadir tepat waktu (`Present`).
   - Jumlah frekuensi terlambat (`Late`) dan akumulasi **Total Menit Keterlambatan**.
   - Total durasi kerja produktif riil (Jam & Menit).
   - Akumulasi jam lembur yang disetujui (*Overtime*).
   - Jumlah hari cuti/izin resmi dan jumlah **Alpa (Alpha)**.
4. **Ekspor File CSV untuk Payroll:**
   - Klik tombol hijau **"📥 Export Payroll CSV"**.
   - Sistem akan mengunduh file `.csv` berformat UTF-8 BOM yang dapat langsung dibuka dengan rapi di Microsoft Excel atau diimpor ke sistem penggajian payroll.

### 4.3 Persetujuan Cuti & Lembur (Approval Workflow)
1. Buka menu **Presensi &rarr; Approval Cuti & Lembur** (`/admin/presensi/approval`).
2. Halaman ini menyajikan 4 tab segmen:
   - **Daftar Permohonan Cuti:** Tinjau nama staf, tanggal, alasan, dan periksa lampiran surat dokter/bukti izin.
   - **Daftar Klaim Lembur:** Tinjau jam kerja lembur staf setelah jam operasional shift berakhir.
   - **Daftar Koreksi Presensi:** Tinjau usulan jam masuk/pulang riil staf yang lupa absen atau terkendala HP.
   - **Daftar Tukar Shift:** Tinjau permohonan tukar shift yang telah disetujui rekan kerja untuk verifikasi operasional cabang.
3. **Tindakan Persetujuan:**
   - Klik tombol hijau **"Approve"** untuk menyetujui.
   - Klik tombol merah **"Reject"** dan ketikkan alasan penolakan jika permohonan tidak memenuhi kualifikasi.
   - Keputusan akan langsung terupdate di dashboard portal mobile karyawan yang bersangkutan.

---

## 🔒 5. Ketentuan Disiplin & Mekanisme Anti-Kecurangan (Anti-Fraud)

Sistem presensi MGK dilengkapi dengan lapisan pengamanan domain:
1. **Toleransi Keterlambatan (Grace Period):**
   - Batas toleransi adalah **15 menit** sejak dimulainya shift kerja.
   - Contoh: Shift 2 dimulai pukul `07:00`. Hadir pukul `07:14` dihitung **Hadir Tepat Waktu**. Hadir pukul `07:16` dihitung **Terlambat** dengan catatan selisih 16 menit keterlambatan.
2. **Geofencing Radius Ketat:**
   - Karyawan tidak dapat melakukan presensi jika berada di luar radius kantor cabang yang ditugaskan (standar radius 100 meter).
3. **Pencegahan Manipulasi GPS (Fake GPS / Mock Location):**
   - Sistem mendeteksi adanya aplikasi pemalsu koordinat (Mock GPS). Jika terdeteksi, permohonan presensi **otomatis ditolak (HTTP 403 Forbidden)** dan dicatat dalam audit trail keamanan.
4. **Deteksi Anomali Jarak (Anti-Teleportasi):**
   - Sistem menolak presensi jika kecepatan perpindahan lokasi antar dua scan tercatat melebihi batas wajar (> 350 km/jam).
5. **Otomatis Tutup Buku Harian (Auto-Close Daily):**
   - Setiap pukul `23:59 WIB`, sistem secara otomatis menjalankan scheduler untuk menutup catatan harian. Karyawan yang terjadwal dinas tetapi tidak melakukan clock-in akan ditandai berstatus **Alpa (Absent)**.

---

## ❓ 6. Panduan Pemecahan Masalah (Troubleshooting & FAQ)

| Permasalahan | Penyebab Kemungkinan | Solusi & Tindakan |
|---|---|---|
| **GPS berstatus merah / Di luar radius padahal sudah di kantor** | Sinyal GPS smartphone sedang lemah, terhalang atap beton tebal, atau akurasi GPS rendah. | 1. Buka aplikasi Google Maps di smartphone, tekan tombol titik biru untuk mengkalibrasi lokasi.<br>2. Pastikan opsi *Google Location Accuracy / Tingkatkan Akurasi Lokasi* diaktifkan di setelan HP.<br>3. Tekan tombol **"🔄 Refresh GPS"** di layar scan presensi. |
| **Kamera tidak menyala / Layar hitam** | Akses kamera ditolak (*Permission Blocked*) di browser. | 1. Di bilah alamat browser, ketuk ikon gembok / setelan situs (di sebelah URL).<br>2. Ubah izin **Kamera** menjadi **Izinkan (Allow)**.<br>3. Muat ulang (*refresh*) halaman web presensi. |
| **Pesan eror: Terdeteksi manipulasi GPS** | Terdapat aplikasi Fake GPS aktif atau mode pengembang (*Mock Location*) menyala di smartphone Android. | 1. Matikan atau uninstall aplikasi Fake GPS.<br>2. Buka *Pengaturan HP &rarr; Opsi Pengembang (Developer Options)* &rarr; matikan *Pilih aplikasi lokasi palsu*. |
| **Lupa melakukan Clock-In saat masuk kerja** | Karyawan langsung bekerja tanpa scan masuk di awal shift. | 1. Segera lapor ke supervisor atau manajer cabang yang bertugas.<br>2. Supervisor dapat melakukan absensi manual via Tab POS Kasir atau melakukan penyesuaian di Panel Admin Presensi. |
| **HP Karyawan mati / Kehabisan baterai** | Smartphone tidak dapat digunakan di lokasi kerja. | Gunakan **Tab 6 Kios Presensi POS Kasir Warnet** di komputer kasir untuk melakukan clock-in/out darurat. |

---

*(Dokumen SOP ini berlaku untuk seluruh unit operasional Maya Graha Kencana. Perubahan aturan jam kerja dan radius geofencing harus melalui persetujuan manajemen).*
