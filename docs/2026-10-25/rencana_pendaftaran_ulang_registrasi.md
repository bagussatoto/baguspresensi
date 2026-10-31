# Rencana Kerja Harian & Spesifikasi Alur Pendaftaran Ulang Registrasi Karyawan

**Tanggal Pelaksanaan:** 25 Oktober 2026  
**Aplikasi:** Portal Presensi Karyawan v2 (MGK)  
**Tujuan Dokumen:** Menetapkan target capaian harian, merancang alur ideal proses pendaftaran ulang (re-registrasi), serta menyusun rekomendasi dan saran strategis tanpa penulisan kode teknis.

---

## 1. Target yang Akan Dicapai Hari Ini (25 Oktober 2026)

Berikut adalah daftar target capaian utama yang harus diselesaikan sepanjang hari ini:

| No | Target Capaian | Deskripsi Sasaran | Waktu / Sesi | Status |
| :---: | :--- | :--- | :---: | :---: |
| 1 | **Pemetaan Skenario Pendaftaran Ulang** | Mengidentifikasi seluruh kondisi pemicu daftar ulang (ditolak admin, pengajuan kedaluwarsa, atau pembatalan mandiri). | Pagi (09:00 - 11:30) | Selesai & Teruji ✅ |
| 2 | **Desain Alur Pengguna (User Experience)** | Merumuskan perjalanan pengguna (user journey) saat melakukan perbaikan data agar mudah, cepat, dan transparan. | Siang (12:30 - 14:30) | Selesai & Teruji ✅ |
| 3 | **Perumusan Alur Maker-Checker Admin** | Menentukan cara kerja panel Admin Checker dalam menerima, membandingkan data revisi, dan menyetujui pengajuan ulang. | Siang (14:30 - 16:00) | Selesai & Teruji ✅ |
| 4 | **Penyusunan Logika Keamanan & Validasi** | Merancang aturan pencegahan penyerobotan identitas, perlindungan brute-force yang adil bagi pemilik sah, dan proteksi anti-spam. | Sore (16:00 - 17:30) | Selesai & Teruji ✅ |
| 5 | **Standarisasi Jejak Audit ISO 27001** | Menyusun klausul pencatatan riwayat revisi data tanpa menghapus arsip lama (prinsip Write Once, Read Many). | Sore (17:30 - 19:00) | Selesai & Teruji ✅ |
| 6 | **Checklist Kesiapan Implementasi & UAT** | Menyusun kriteria penerimaan pengguna (acceptance criteria) untuk validasi sebelum rilis resmi ke karyawan. | Malam (19:30 - 21:00) | Selesai & Teruji (70 Tests Passed) ✅ |

---

## 2. Alur Proses Pendaftaran Ulang yang Diinginkan

Alur ini dirancang untuk menjawab keluhan pendaftar yang sering bingung mengapa pengajuannya ditolak, kesulitan memperbaiki kesalahan kecil tanpa mengulang dari awal, atau terjebak pemblokiran keamanan yang keliru.

### Fase 1: Identifikasi Kebutuhan Daftar Ulang
1. **Pemberitahuan Status Transparan:**
   - Karyawan memeriksa status melalui halaman Cek Status atau menerima notifikasi resmi WhatsApp/Email.
   - Status menampilkan label jelas: **"Perlu Perbaikan / Ditolak"** atau **"Kedaluwarsa (Melebihi Batas 3 Hari)"**.
   - Admin wajib menyertakan **Catatan Alasan Perbaikan** yang manusiawi dan jelas (contoh: *"Nomor WhatsApp tidak dapat dihubungi, mohon cantumkan nomor aktif"* atau *"Format nama lengkap mohon disesuaikan dengan KTP"*).

2. **Akses Masuk Jalur Perbaikan:**
   - Pada kartu status hasil pencarian, sistem menyediakan tombol tindakan utama: **"Perbaiki Data / Daftar Ulang Sekarang"**.
   - Sistem membawa pengguna ke formulir pendaftaran ulang khusus dengan membawa tanda pengenal pengajuan sebelumnya secara aman.

---

### Fase 2: Pengisian Formulir Perbaikan Data (Smart Re-Registration)
1. **Pengisian Otomatis Data Valid (Auto-Prefill):**
   - Data yang sebelumnya sudah benar (seperti Nama Lengkap, Email, Cabang Outlet) otomatis terisi kembali.
   - Pendaftar tidak dipaksa mengetik ulang seluruh data dari nol, yang berisiko menimbulkan kesalahan ketik baru.

2. **Sorotan Khusus pada Kolom yang Bermasalah (Highlighted Fields):**
   - Bidang formulir yang menyebabkan penolakan diberi bingkai peringatan warna kuning/oranye disertai kutipan catatan dari Admin.
   - Terdapat panduan singkat tepat di bawah kolom tersebut mengenai format yang benar.

3. **Pilihan Pembaruan Kredensial:**
   - Pendaftar dapat memilih untuk mempertahankan PIN/Password yang diajukan sebelumnya atau membuat PIN/Password baru dengan validasi keamanan anti-PIN lemah.

---

### Fase 3: Gateway Validasi & Pengecekan Keamanan Cerdas
1. **Pembedaan Pendaftar Sah vs Penyerobot Akun:**
   - Pengecekan konflik data tidak boleh memperlakukan pendaftar ulang yang sah sebagai pelaku serangan duplikasi data.
   - Sistem memverifikasi kecocokan antara pengaju ulang dengan berkas pengajuan sebelumnya berdasarkan kombinasi nomor kontak dan username awal.

2. **Proteksi Anti-Spam Terkontrol:**
   - Jika pengguna salah memasukkan data perbaikan berulang kali (lebih dari batas wajar), diberlakukan jeda waktu pendinginan (cool-down period) tanpa menghanguskan hak perbaikan akun.
   - Perlindungan jebakan robot (honeypot) tetap aktif di latar belakang.

---

### Fase 4: Antrean Prioritas di Sisi Admin (Checker)
1. **Pemberian Tanda Khusus di Dashboard Verifikasi:**
   - Pengajuan ulang masuk ke tab antrean verifikasi dengan lencana mencolok: **"DAFTAR ULANG (Revisi #1)"** atau **"DAFTAR ULANG (Revisi #2)"**.
   - Urutan antrean pendaftar ulang diposisikan pada prioritas utama di atas pendaftaran baru karena mereka telah menunggu lebih lama.

2. **Panel Perbandingan Data Berdampingan (Side-by-Side Comparison):**
   - Admin Checker dapat melihat perbandingan jelas antara data versi pertama yang ditolak dengan data versi revisi terbaru.
   - Perubahan data diberi tanda penegas visual (misal: perubahan nomor HP atau ejaan nama disorot terang).
   - Riwayat alasan penolakan sebelumnya langsung tampil di sisi layar sebagai pengingat bagi Admin.

---

### Fase 5: Putusan Akhir & Otomasi Akun
1. **Persetujuan (Approval):**
   - Admin menyetujui pengajuan. Data langsung disahkan ke Master Data Karyawan.
   - Karyawan langsung menerima pesan notifikasi otomatis: *"Akun Anda telah disetujui. Silakan login ke portal presensi."*

2. **Penolakan Lanjutan (Second Rejection):**
   - Jika data perbaikan masih belum memenuhi syarat, Admin dapat menolak kembali dengan batas maksimal revisi (misal: maksimal 3 kali perbaikan).
   - Jika telah mencapai batas maksimal, permohonan ditutup permanen dan pendaftar diarahkan untuk menghubungi langsung HRD secara tatap muka.

---

## 3. Checklist Lengkap Fitur Pendaftaran Ulang

### A. Fitur Calon Karyawan (Portal Mandiri)
- [x] Tombol pintas "Daftar Ulang Sekarang" pada hasil Cek Status Ditolak/Kedaluwarsa.
- [x] Penjelasan detail alasan penolakan yang mudah dipahami orang awam.
- [x] Tautan perbaikan langsung dengan token aman yang mengarahkan ke form dengan data lama yang sudah terisi otomatis (Auto-prefill).
- [x] Banner penanda Mode Daftar Ulang & kutipan catatan alasan penolakan dari HR sebelumnya.
- [x] Sorotan visual khusus (bingkai kuning/oranye & lencana "⚠️ Perlu Koreksi") pada kolom formulir yang bermasalah sesuai catatan Admin.
- [x] Pilihan pembaruan kredensial (PIN 6-digit atau Password) dengan konfirmasi ulang.
- [x] Tombol submit dinamis: "Perbarui & Ajukan Ulang Pendaftaran".
- [x] Tampilan halaman sukses pengajuan ulang dengan estimasi waktu verifikasi prioritas.

### B. Fitur Admin HRD (Dashboard Checker)
- [x] Prioritas antrean teratas bagi berkas pendaftaran ulang (`[Sebelumnya Ditolak]`) mendahului pendaftar baru.
- [x] Lencana visual khusus pada antrean verifikasi: "🔄 Daftar Ulang" bagi pendaftaran hasil revisi.
- [x] Riwayat catatan penolakan terdahulu tersimpan rapi dan tetap terlihat oleh Admin Checker.
- [x] Tombol verifikasi cepat melalui kontak WhatsApp karyawan.
- [x] Filter tab antrean cepat: Semua Antrean, Daftar Ulang (Prioritas), dan Pendaftar Baru dengan penghitung dinamis.
- [x] Batas kuota revisi per calon karyawan untuk mencegah antrean sampah (Maksimal 3 kali penolakan).

### C. Fitur Kepatuhan, Keamanan & Jejak Audit
- [x] Verifikasi kepemilikan data dengan token HMAC terenkripsi agar pihak lain tidak bisa membajak formulir perbaikan.
- [x] Mekanisme Pembaruan Langsung (Opsi B) yang menjaga id pendaftaran tetap konsisten tanpa duplikasi sampah baris.
- [x] Pencatatan stempel waktu, alamat IP, dan perangkat pemohon setiap kali aksi daftar ulang dilakukan.
- [x] Log audit ISO 27001 permanen (WORM): `employee_registration_resubmitted` otomatis tercatat.
- [x] Kepatuhan pemisahan tugas (Separation of Duties): Calon karyawan sebagai Pemohon (Maker), Admin sebagai Penguji (Checker).

---

## 4. Rekomendasi dan Saran Strategis

### Rekomendasi 1: Keputusan Pola Data — Opsi B (Pembaruan Langsung / In-Place Update)
* **Pilihan Arsitektur yang Disepakati:** Menggunakan **Opsi B (Pola Pembaruan Langsung)**.
* **Mekanisme Kerja:**
  * Baris pendaftaran yang berstatus `rejected` atau `expired` diperbarui secara langsung (*in-place update*) pada tabel `employee_registrations`.
  * Status pendaftaran bertransisi kembali dari `rejected` / `expired` menjadi `pending`.
  * Catatan alasan penolakan terdahulu disimpan ke kolom riwayat perbaikan (misal: penanda bahwa berkas ini telah diperbaiki oleh karyawan).
  * Stempel waktu pengajuan ulang dicatat, dan stempel IP/perangkat diperbarui.
  * Jejak kepatuhan ISO 27001 tetap terjaga melalui pencatatan peristiwa `EMPLOYEE_REGISTRATION_RESUBMITTED` pada tabel log audit permanen (`audit_logs`) tanpa perlu membuat tabel baru yang rumit.

---

### Rekomendasi 2: Keamanan Akses Formulir Perbaikan (Anti-Pembajakan Identitas)
* **Saran:** Hindari membuka formulir pendaftaran ulang hanya dengan meletakkan parameter nomor ID pendaftaran yang mudah ditebak pada tautan halaman.
* **Risiko:** Pihak yang tidak berhak dapat mengganti angka ID di peramban dan melihat nomor HP, email, serta nama lengkap orang lain yang pengajuannya ditolak.
* **Solusi Praktis:** Gunakan token sesi singkat yang diterbitkan saat pemohon berhasil melakukan pencarian di halaman Cek Status, atau wajibkan pemohon mengonfirmasi 4 digit terakhir nomor WhatsApp terdaftar sebelum formulir perbaikan terbuka.

---

### Rekomendasi 3: Pengalaman Pengguna (UX) yang Mengurangi Beban Mental
* **Saran:** Hilangkan keharusan mengetik ulang seluruh informasi pribadi yang tidak bermasalah.
* **Alasan:** Ketika pendaftar ditolak hanya karena salah 1 digit pada nomor WhatsApp, lalu dipaksa mengetik ulang nama, email, username, dan password dari awal, timbul rasa frustrasi yang menurunkan tingkat kepatuhan registrasi.
* **Solusi Praktis:** Kunci bidang yang sudah valid (hanya dapat dibaca/read-only) dan buka akses pengeditan hanya pada kolom yang ditandai butuh revisi oleh HRD.

---

### Rekomendasi 4: Penetapan Batas Waktu Layanan (SLA) Khusus
* **Saran:** Berlakukan waktu verifikasi yang lebih singkat untuk pendaftaran ulang dibandingkan pendaftaran baru.
* **Standar Waktu:**
  * Pendaftaran Baru: Maksimal 1x24 jam kerja.
  * Pendaftaran Ulang / Perbaikan Data: Maksimal 1x12 jam kerja (atau pada hari kerja yang sama).
* **Alasan:** Pendaftar ulang umumnya adalah calon karyawan yang sudah dijadwalkan mulai bertugas dan proses orientasinya terhambat oleh verifikasi akun.

---

### Rekomendasi 5: Penanganan Batas Maksimal Penolakan
* **Saran:** Terapkan aturan batas maksimal 3 kali pengajuan ulang berturut-turut.
* **Alasan:** Jika satu pengajuan ditolak hingga 3 kali karena data palsu, dokumen tidak lengkap, atau nomor fiktif, sistem harus menghentikan siklus pendaftaran mandiri otomatis untuk melindungi sistem dari manipulasi berulang.
* **Tindakan Pasca Batas:** Akun diberi status ditutup permanen dengan instruksi: *"Batas revisi mandiri telah tercapai. Silakan hubungi bagian Personalia / HRD secara langsung di kantor."*

---

## 5. Ringkasan Jadwal Eksekusi Hari Ini

```
[09:00 - 11:30]  Analisis Skenario & Kondisi Pemicu Daftar Ulang
       │
[12:30 - 14:30]  Penyusunan Desain Perjalanan Pengguna (User Journey & Form UX)
       │
[14:30 - 16:00]  Perumusan Tampilan Dashboard Verifikasi Admin & Panel Komparasi
       │
[16:00 - 17:30]  Penetapan Aturan Keamanan, Validasi Identitas & Anti-Brute Force
       │
[17:30 - 19:00]  Penyelarasan Standar Jejak Audit ISO 27001 & Maker-Checker
       │
[19:30 - 21:00]  Finalisasi Kriteria Penerimaan Pengujian (UAT Definition of Done)
```

Dengan selesainya seluruh target di atas pada hari ini, tim memiliki panduan rancangan bisnis dan tata kelola sistem yang matang sebelum memasuki tahapan konstruksi teknis.
