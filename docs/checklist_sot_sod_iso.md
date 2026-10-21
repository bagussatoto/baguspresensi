# 📋 Checklist Standar Tata Kelola & Keamanan Sistem Presensi
## Kepatuhan Single Source of Truth (SoT), Segregation of Duties (SoD), dan ISO/IEC 27001:2022

**Target Sistem:** Aplikasi Presensi, Portal Karyawan, Staging Pendaftaran Mandiri, & Kasir POS  
**Standar Regulasi:** ISO/IEC 27001:2022, Prinsip 4-Eyes (Maker-Checker), WORM Audit Trail, Least Privilege  
**Status Implementasi:** 100% Terverifikasi, Teruji Otomatis & Terintegrasi

---

## 🏛️ 1. Checklist SoT Aplikasi Presensi (Single Source of Truth)

Pusat Data: Master Data `employees`, Transaksi `attendance_records`, Aturan `shifts` & `roster_jadwal`

- [x] **Buku Induk Master Tunggal (`employees`):**  
  Hanya ada 1 tabel data resmi karyawan di seluruh database. Modul presensi, kasir POS, jadwal kerja, dan penggajian (payroll) wajib merujuk ke tabel ini tanpa tabel tandingan.
- [x] **Pencabutan Akses Seketika (*Instant Revocation*):**  
  Jika status karyawan dinonaktifkan (`is_active = false`) di tabel `employees`, detik itu juga karyawan otomatis tertolak untuk absen, login PWA/portal, dan login kasir POS.
- [x] **Fakta Kehadiran Tunggal (`attendance_records`):**  
  Seluruh fakta kehadiran harian (jam masuk, jam pulang, durasi, keterlambatan) hanya disimpan di satu tabel resmi dengan foreign key terikat ke `employees`.
- [x] **Stempel Waktu Otoritatif Server:**  
  Pencatatan waktu masuk (`clock_in_at`) dan pulang (`clock_out_at`) murni bersumber dari jam server aplikasi yang tersinkronisasi NTP, bukan jam perangkat ponsel pengguna.
- [x] **Bukti Otentikasi Visual (*Non-Repudiation*):**  
  Foto selfie karyawan saat tap presensi tersimpan permanen di storage server (`clock_in_photo_path` dan `clock_out_photo_path`) untuk bukti verifikasi fisik.
- [x] **Audit Geofence Akurat:**  
  Koordinat GPS (`lat`, `lng`) serta kalkulasi jarak radius kantor cabang dihitung seragam menggunakan formula geodesi (Haversine).
- [x] **Deteksi Anti-Fraud (Fake GPS):**  
  Sistem secara otomatis mendeteksi dan menandai jika perangkat pengguna menggunakan aplikasi pemalsu lokasi (`is_mock_location_detected = true`).
- [x] **Pusat Konfigurasi Shift (`shifts`):**  
  Jam mulai, jam selesai, batas toleransi (*grace period*), dan penanda shift malam terpusat di tabel shift.
- [x] **Kalkulasi Standar Tanpa Rumus Mandiri:**  
  Menit keterlambatan dan lembur dihitung seragam; modul rekap dan penggajian (payroll) langsung membaca angka bersih dari data presensi tanpa rekap manual Excel.
- [x] **Validasi Presensi untuk Buka Kasir POS:**  
  Staf kasir toko/warnet hanya diizinkan membuka sesi transaksi kasir jika sudah tercatat hadir (Clock-In) di `attendance_records` pada hari bersangkutan.

---

## 📋 2. Checklist SoT Pendaftaran Mandiri

Pusat Data: Tabel Penyangga `employee_registrations` (*Staging Area*)

- [x] **Isolasi Ruang Penyangga (*Staging Area*):**  
  Seluruh formulir pendaftaran publik dari HP/web luar ditampung di tabel penyangga `employee_registrations` dengan status awal `pending`.
- [x] **Larangan Menulis Langsung ke Master:**  
  Formulir pendaftaran publik diblokir total dari menulis langsung ke tabel master `employees`.
- [x] **Validasi Keunikan Data Calon:**  
  Nomor telepon/WhatsApp dan username calon karyawan divalidasi unik, tidak boleh bertabrakan dengan antrean pending maupun karyawan aktif.
- [x] **Promosi Data Atomik (*Atomic Promotion*):**  
  Promosi dari data calon (staging) menjadi karyawan resmi (master) wajib berjalan dalam satu transaksi database tertutup (`DB::transaction`).
- [x] **Tautan Referensi Abadi:**  
  Kolom `created_employee_id` di tabel staging terikat permanen ke ID master `employees` yang baru terbentuk untuk kebutuhan riwayat audit.
- [x] **Eliminasi Data Liar:**  
  Pendaftar yang ditolak (*rejected*) tetap tersimpan di staging lengkap dengan alasan penolakan (`rejection_reason`), tanpa pernah mencemari master data `employees`.

---

## ⚖️ 3. Checklist SoD Aplikasi Presensi (Segregation of Duties)

Prinsip: Pemisahan 4 Wewenang Dasar (Maker, Custodian, Record Keeper, Authorizer)

- [x] **Maker-Checker Kehadiran:**  
  Karyawan bertindak sebagai *Maker* yang hanya berhak melakukan tap kehadiran (Clock-In / Clock-Out) dan mengajukan izin/lembur.
- [x] **Larangan Self-Approval Kehadiran/Lembur:**  
  Karyawan dilarang menyetujui sendiri izin kepulangan awal atau lembur atas dirinya sendiri.
- [x] **Otorisasi Independen oleh Atasan/HR:**  
  Persetujuan izin dan lembur mutlak menjadi wewenang supervisor atau HR Admin (*Checker / Authorizer*).
- [x] **Dual-Control Void Nota Kasir POS:**  
  Kasir (*Maker Transaksi*) dilarang membatalkan nota (*Void*) secara sepihak; pembatalan nota wajib diotorisasi ganda dengan PIN Supervisor/Otorisator aktif.
- [x] **Dual-Control Diskon Transaksi POS:**  
  Pemberian diskon transaksi yang melampaui batas wewenang kasir (`max_discount_approval_percent`) wajib memerlukan otorisasi fisik dengan PIN Supervisor.
- [x] **Blind Cash Count saat Penutupan Shift Kasir:**  
  Kasir (*Custodian* uang fisik) wajib menghitung uang fisik nyata per pecahan uang tanpa melihat angka estimasi total sistem terlebih dahulu saat tutup kasir.
- [x] **Pencatatan Selisih Kas Otomatis:**  
  Sistem menghitung selisih kas fisik vs pembukuan sistem (`cash_difference`) dan otomatis mencatat selisih tersebut ke log audit untuk diinvestigasi manajemen.
- [x] **Pemisahan Presensi & Pencairan Gaji:**  
  Petugas pencatat kehadiran harian terpisah dari otorisator pencairan dana gaji (bagian Keuangan/Finance).
- [x] **Granular Admin RBAC:**  
  Admin Cabang dibatasi wewenangnya hanya pada cabang yang ditugaskan (*Principle of Least Privilege*), dan Auditor dibatasi hak akses baca murni (*Read-Only*, mutasi diblokir 403 Forbidden).

---

## ⚖️ 4. Checklist SoD Pendaftaran Mandiri

Prinsip: Dual-Control & Maker-Checker Pattern (4-Eyes Principle)

- [x] **Batasan Wewenang Calon Karyawan (*Maker*):**  
  Calon karyawan hanya berwenang mengisi data identitas dasar (Nama Lengkap, Nomor WhatsApp, Username, dan PIN 6-digit).
- [x] **Larangan Intervensi Struktural oleh Pendaftar:**  
  Pendaftar dilarang menentukan sendiri penempatan cabang (`branch_id`), peran tugas (`role`), nama jabatan (`position`), batas diskon kasir, maupun Nomor Induk Karyawan (NIK).
- [x] **Pemeriksaan Independen oleh Checker:**  
  Admin/HRD bertindak sebagai pihak berwenang yang memverifikasi keabsahan fisik/KTP dan validasi kontak via WhatsApp.
- [x] **Penetapan Otoritatif oleh Admin:**  
  Admin/HRD yang secara sepihak menetapkan cabang penugasan, role, jabatan, batas diskon kasir, serta mengesahkan generate NIK resmi perusahaan.
- [x] **Proteksi Mutlak Anti Self-Approval:**  
  Admin yang memiliki hak akses verifikasi dilarang keras menyetujui (Approve) pendaftaran atas nama dirinya sendiri (pencegahan konflik kepentingan).
- [x] **Pencegahan Karyawan Fiktif (*Ghost Employee Prevention*):**  
  Akun yang masih berstatus pending diblokir total dari login portal, presensi, dan operasional kasir POS hingga verifikasi 4-Eyes selesai disetujui.

---

## 🔒 5. Checklist Kepatuhan ISO 27001 Aplikasi Presensi

Standar Regulasi: ISO/IEC 27001:2022 Sistem Manajemen Keamanan Informasi

- [x] **Prinsip Hak Akses Minimal (*Least Privilege* - Klausul A.8.2 & A.8.5):**  
  Setiap pengguna hanya diberikan hak akses yang paling minimal sesuai fungsi operasionalnya.
- [x] **Enkripsi Kredensial Kuat:**  
  Seluruh PIN 6-digit dan password dienkripsi satu arah menggunakan algoritma hashing standar industri (Bcrypt/Argon2id), tidak ada kredensial dalam bentuk teks terbuka (*plain text*).
- [x] **Sinkronisasi Waktu Otoritatif (Klausul A.8.17):**  
  Server aplikasi terhubung dan tersinkronisasi dengan server NTP resmi Indonesia (`id.pool.ntp.org`) guna menjamin kekuatan hukum stempel waktu kehadiran.
- [x] **Bukti Kehadiran Tak Terbantahkan (*Non-Repudiation* - Klausul A.8.28):**  
  Foto selfie dan koordinat GPS tersimpan sebagai bukti kehadiran fisik yang sah.
- [x] **Jejak Audit WORM (*Write Once, Read Many* - Klausul A.8.15):**  
  Tabel `audit_logs` bersifat *immutable* (tidak memiliki fungsi edit/update maupun hapus/delete) untuk menjamin integritas riwayat.
- [x] **Kelengkapan Forensik Log Audit:**  
  Setiap perubahan status kritis mencatat: `user_id`, `action`, `auditable_type`, `auditable_id`, data sebelum (`old_values` JSONB), data sesudah (`new_values` JSONB), alamat IP, User Agent, dan stempel waktu.
- [x] **Sanitasi Otomatis Data Sensitif:**  
  Layanan audit log secara otomatis membersihkan kata sandi, PIN, dan token keamanan sebelum dicatat ke log.
- [x] **Pertahanan Anti Brute-Force (Klausul A.8.5 & A.8.20):**  
  Pembatasan laju ketat (*Rate Limiting*) aktif pada endpoint scan presensi (`throttle:10,1`) dan endpoint login (`throttle:5,1`).
- [x] **Ekspor Berita Acara Audit:**  
  Fasilitas ekspor CSV Berita Acara Audit berkala tersedia di dashboard pemantauan admin untuk audit internal maupun eksternal.
- [x] **Perlindungan Data Pribadi / PII (Klausul A.8.10):**  
  Data pribadi staf (KTP, nomor telepon, foto selfie, koordinat GPS) dilindungi dengan pembatasan hak otorisasi ketat.

---

## 🔒 6. Checklist Kepatuhan ISO 27001 Pendaftaran Mandiri

Standar Regulasi: ISO/IEC 27001:2022 pada Gerbang Pendaftaran Publik

- [x] **Isolasi Lingkungan Publik (*Data Segregation* - Klausul A.8.31):**  
  Formulir pendaftaran publik diisolasi ke tabel staging `employee_registrations` guna melindungi master operasional dari data iseng dan bot internet.
- [x] **Verifikasi Formal Sebelum Hak Akses Diberikan (Klausul A.8.5):**  
  Akun tidak otomatis aktif; wajib diverifikasi identitas resminya sebelum hak akses sistem diterbitkan.
- [x] **Enkripsi Kredensial di Titik Awal Masuk:**  
  PIN dan password pendaftar langsung di-hash sejak pertama kali disimpan di tabel staging.
- [x] **Perekaman Forensik Registrasi (Klausul A.8.15):**  
  Sistem merekam alamat IP (`ip_address`) dan tanda pengenal perangkat (`user_agent`) pendaftar saat formulir dikirimkan.
- [x] **Pencatatan Audit Approval & Rejection:**  
  Sistem mencatat identitas verifikator, stempel waktu, serta alasan resmi penolakan (`rejection_reason`) ke dalam log audit perusahaan.
- [x] **Pertahanan Anti-Spam & Flooding (Klausul A.8.20):**  
  Validasi ketat pada nomor telepon dan username serta pembatasan laju submit (*rate limiting*) aktif untuk menangkal bot spam registrasi.
