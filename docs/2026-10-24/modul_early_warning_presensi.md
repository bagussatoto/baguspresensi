# MODUL SISTEM PERINGATAN DINI (EARLY WARNING SYSTEM)
## REGISTRASI & PRESENSI PEGAWAI

---

**Nomor Dokumen** : MOD-HRIS-EWS-001  
**Versi**         : 1.0  
**Tanggal Rilis** : 24 Oktober 2026  
**Status**        : Berlaku  

---

## DAFTAR ISI
1. [Latar Belakang & Urgensi](#1-latar-belakang--urgensi)
2. [Tujuan & Manfaat](#2-tujuan--manfaat)
3. [EWS Registrasi Pegawai & Mekanisme Pengecekan 2 Titik (Dual-Point Inspection)](#3-ews-registrasi-pegawai--mekanisme-pengecekan-2-titik-dual-point-inspection)
4. [Tingkatan Peringatan Presensi & Parameter Ambang Batas (Leveling Thresholds)](#4-tingkatan-peringatan-presensi--parameter-ambang-batas-leveling-thresholds)
5. [Arsitektur & Alur Kerja Sistem (Workflow)](#5-arsitektur--alur-kerja-sistem-workflow)
6. [Standar Format Pesan Notifikasi (Notification Templates)](#6-standar-format-pesan-notifikasi-notification-templates)
7. [Standar Operasional Prosedur (SOP) Penanganan & Tindak Lanjut](#7-standar-operasional-prosedur-sop-penanganan--tindak-lanjut)
8. [Dasbor Monitoring, Analitik, & Metrik KPI](#8-dasbor-monitoring-analitik--metrik-kpi)


---

## 1. Latar Belakang & Urgensi

Ketepatan waktu kehadiran dan kelengkapan catatan presensi merupakan indikator fundamental kepatuhan serta tata kelola kedisiplinan organisasi. Pola pengelolaan presensi manual atau reaktif sering kali menimbulkan berbagai kendala operasional:

- **Tingginya Angka Kelalaian Input**: Pegawai yang hadir secara fisik kerap terlambat atau lupa melakukan *clock-in* / *clock-out*, berujung pada status alpa keliru.
- **Beban Kerja Tambahan Divisi HRD**: HRD menghabiskan waktu signifikan untuk merekapitulasi, memverifikasi dokumen manual, dan melakukan klarifikasi kelalaian presensi di akhir bulan.
- **Distorsi Data Penggajian**: Keterlambatan dan ketidaklengkapan data memicu ketidakakuratan perhitungan tunjangan kehadiran, lembur, serta pemotongan sanksi keterlambatan.
- **Minimnya Intervensi Nyata**: Tanpa peringatan otomatis, atasan langsung tidak memiliki visibilitas langsung terhadap status ketersediaan personil pada jam awal kerja.

Modul *Early Warning System* (EWS) Registrasi & Presensi diimplementasikan untuk mentransformasi mitigasi kelalaian dari penanganan reaktif bulanan menjadi intervensi otomatis berbasis *real-time*, dimulai sejak gerbang validasi pendaftaran pegawai hingga operasional harian di lokasi kerja.

---

## 2. Tujuan & Manfaat

### 2.1 Tujuan
1. **Pencegahan Anomali Registrasi**: Mencegah akun ganda, bentrok data identitas, dan pendaftaran fiktif melalui deteksi dini saat pengisian form pendaftaran.
2. **Otomatisasi Pengingat Presensi**: Mencegah kelalaian pencatatan masuk maupun pulang sebelum dan sesaat melewati batas toleransi shift.
3. **Eskalasi Terukur**: Memberikan transparansi status ketidakhadiran berisiko kepada atasan unit kerja secara cepat.
4. **Optimasi Administrasi**: Mengurangi rasio pengajuan koreksi presensi manual hingga $\le 2\%$ per periode cut-off.

### 2.2 Manfaat
- **Bagi Pegawai**: Mencegah kerugian finansial/tunjangan akibat lupa *clock-in/out* melalui reminder multi-kanal serta kemudahan pendaftaran akun yang transparan.
- **Bagi Atasan / Supervisor**: Memperoleh konfirmasi ketersediaan sumber daya tim secara langsung untuk pembagian tugas harian.
- **Bagi Divisi HRD**: Rekapitulasi otomatis presensi tanpa beban konfirmasi berulang serta integritas data pegawai baru yang bebas duplikasi.

---

## 3. EWS Registrasi Pegawai & Mekanisme Pengecekan 2 Titik (Dual-Point Inspection)

Pondasi akurasi sistem presensi berakar pada kebersihan dan integritas data pada saat registrasi awal. Untuk mencegah manipulasi akun, pendaftaran ganda (*duplicate registration*), dan akun menggantung (*stale pending account*), sistem menerapkan **Mekanisme Pengecekan 2 Titik (Dual-Point Inspection)**.

### 3.1 Arsitektur Pengecekan 2 Titik (Dual-Point Database Architecture)

Pengecekan keunikan data identitas (`username`, `nomor telepon`, dan `email`) dilakukan secara silang ke 2 entitas database yang berbeda fungsi:

```
[ Form Pendaftaran Mandiri /presensi2/register ]
                       │
                       ▼
         [ Dual-Point Validation Engine ]
                       │
        ┌──────────────┴──────────────┐
        ▼                             ▼
   [ TITIK 1 ]                   [ TITIK 2 ]
Tabel Master Karyawan       Tabel Antrean Pendaftaran
    (`employees`)           (`employee_registrations`)
        │                             │
   Status: AKTIF                 Status: PENDING
(Pegawai Sah Organisasi)     (Menunggu Verifikasi Admin)
```

1. **Titik 1: Tabel Master Karyawan (`employees`)**
   - **Fungsi**: Bertindak sebagai *Single Source of Truth* (SSoT) untuk seluruh pegawai yang telah sah terdaftar dan aktif bertugas di unit/cabang.
   - **Tindakan EWS Jika Terdeteksi**:
     - Sistem langsung menerbitkan respon `status_type: master_active`.
     - Memberikan pesan peringatan: *"Akun dengan data tersebut sudah terdaftar dan aktif sebagai karyawan resmi."*
     - Menyediakan tombol pintas langsung: **`👉 Masuk ke Halaman Login`** tanpa membiarkan pendaftar mengajukan form ulang.

2. **Titik 2: Tabel Antrean Verifikasi Pendaftaran (`employee_registrations`)**
   - **Fungsi**: Bertindak sebagai area penampungan sementara (*Staging Area*) yang mematuhi prinsip *Segregation of Duties* (SoD) Maker-Checker sebelum disahkan Admin/HR.
   - **Tindakan EWS Jika Terdeteksi**:
     - Sistem menerbitkan respon `status_type: pending_approval`.
     - Memberikan pesan peringatan: *"Pendaftaran dengan data tersebut sudah pernah diajukan dan sedang menunggu verifikasi Admin/HR."*
     - Menghentikan proses pengajuan dobel dari pemohon yang sama.

---

### 3.2 Lapisan Waktu Eksekusi Pengecekan (Two-Tier Execution Timing)

Mekanisme pengecekan 2 titik ini dieksekusi dalam dua tingkatan lapisan untuk menjamin kecepatan respon antarmuka dan integritas mutlak data:

```
[ Pendaftar Mengetik Data ] ──► [ Lapisan 1: Real-Time Debounce (300ms) ] ──► API /check-availability (Throttle 30/mnt)
                                                                                  │
                                                                                  ├── Valid: Tampilkan Indikator Hijau
                                                                                  └── Bentrok: Tampilkan Peringatan Dini & Saran Username
                                                                                  
[ Pendaftar Klik Tombol Daftar ] ──► [ Lapisan 2: Pre-Commit Controller ] ──► POST /register (Throttle 6/mnt)
                                                                                  │
                                                                                  ├── Lolos: Simpan ke employee_registrations (Pending)
                                                                                  └── Race Condition: Rollback & Tolak Pendaftaran
```

1. **Lapisan 1: Pengecekan Asinkron Real-Time (*On-Typing / Live Feedback*)**
   - Berjalan di latar belakang menggunakan teknik *debounce* (300 ms) saat calon pegawai mengisi kolom `username`, `phone`, atau `email`.
   - Mengakses endpoint `/presensi2/register/check-availability` yang diproteksi pembatasan akses (*rate limiting*: `throttle:30,1`).
   - Memberikan umpan balik instan:
     - **Hijau**: Nilai tersedia dan memenuhi standar format.
     - **Merah/Kuning**: Nilai telah terdaftar di Titik 1 atau Titik 2 beserta informasi status akun.
   - **Fitur Rekomendasi Username Cerdas**: Jika username bentrok, sistem otomatis membangkitkan 3–4 alternatif username yang belum pernah dipakai di Titik 1 maupun Titik 2 (misal: penambahan angka acak atau variasi tanda baca sah), yang dapat langsung diklik oleh pengguna.

2. **Lapisan 2: Validasi Akhir di Sisi Server (*Server-Side Pre-Commit*)**
   - Dieksekusi secara ketat tepat saat form dikirimkan (*submit*) via `POST /presensi2/register`.
   - Diproteksi oleh *rate limiter anti-bom pendaftaran* (`throttle:6,1` / maksimal 6 submit per menit per IP).
   - Melakukan query ulang secara atomik ke Titik 1 dan Titik 2 untuk mengeliminasi potensi celah *race condition* (dua pengguna mendaftar dengan identitas yang sama di detik yang sama).

---

### 3.3 Matriks Parameter Pengecekan EWS Registrasi

| Parameter | Validasi Format & Keabsahan | Ruang Lingkup Pengecekan | Output Respon EWS | Status Tombol Daftar |
| :--- | :--- | :--- | :--- | :---: |
| **Username** | $\ge 3$ Karakter; alfanumerik, titik (`.`), strip (`-`), garis bawah (`_`). Case-insensitive (`LOWER`). | Titik 1 (`employees`) & Titik 2 (`employee_registrations`). | Pesan ketersediaan + alternatif saran username yang bebas dipakai. | **Disabled** jika bentrok. |
| **Nomor HP** | Nomor orang asli seluler Indonesia (`08xx`, `628xx`, `+628xx`, panjang 10–15 digit). Menolak nomor fiktif/bukan orang. | Titik 1 (`phone`) & Titik 2 (`phone`) dengan normalisasi digit. | Peringatan duplikasi di Master / Antrean atau peringatan format nomor bukan orang. | **Disabled** jika nomor fiktif / ganda. |
| **Alamat Email** | Format standar RFC email (`filter_var` RFC 822/5322). Case-insensitive (`LOWER`). | Titik 1 (`email`) & Titik 2 (`email`). | Konfirmasi ketersediaan atau peringatan email telah digunakan akun lain. | **Disabled** jika email ganda / tidak valid. |
| **PIN Keamanan** | Tepat 6-digit angka numerik (`0-9`). | Validasi integritas form. | Indikator visual 6-dot terisi penuh. | **Disabled** jika $< 6$ digit. |

---

### 3.4 Early Warning untuk Admin / HR (SLA Persetujuan Maker-Checker)

Sistem pendaftaran mandiri yang berbasis tata kelola *Segregation of Duties* (SoD) dan ISO/IEC 27001 mensyaratkan verifikasi berjenjang oleh pihak kedua (*Checker / Admin*). Namun, ketiadaan batas waktu respons berisiko memicu pendaftaran menggantung (*stale pending application*), keterlambatan penugasan personil operasional di outlet, atau berkas tertimbun tanpa kepastian.

Untuk memitigasi hal ini, EWS menerapkan pemantauan Service Level Agreement (SLA) otomatis terhadap antrean pendaftaran di `/admin/karyawan-verifikasi`:

#### A. Matriks Tingkatan Peringatan SLA Verifikasi

| Tingkat EWS | Ambang Batas Waktu | Status Indikator | Target Notifikasi | Tindakan Otomatis Sistem |
| :---: | :---: | :--- | :--- | :--- |
| **SLA-1** | $\ge 12$ Jam pasca registrasi diajukan | **Amber Warning (Siaga)** | Admin Cabang | Menampilkan badge kuning `Mendekati Batas SLA (12 Jam)` pada daftar antrean dan mengurutkan berkas ke urutan prioritas atas. |
| **SLA-2** | $\ge 24$ Jam pasca registrasi diajukan | **Red Alert (Kritis / Overdue)** | Admin Cabang & HR Manager | Menerbitkan banner merah peringatan di panel atas dashboard admin, memicu counter merah pada menu navigasi `Verifikasi Karyawan`, serta mengirim notifikasi pengingat evaluasi berkas. |
| **SLA-3** | $\ge 72$ Jam tanpa tindak lanjut | **Expired Alert (Kadaluarsa)** | HR Manager & Kepala Operasional | Menandai pendaftaran dengan status `Stale / Gantung`, memicu pencatatan insiden tata kelola di Audit Trail, serta mengaktifkan opsi investigasi manual / pembatalan terstruktur. |

---

#### B. Peringatan Dini Pelanggaran Tata Kelola Akses (Governance Early Warnings)

Sistem secara aktif mencegah dan memperingatkan potensi kecurangan (*fraud*) pada saat proses approval berlangsung:

1. **Peringatan Pencegahan Persetujuan Diri Sendiri (*Anti Self-Approval Warning*)**:
   - Jika pengguna admin yang login mencoba menyetujui pendaftaran yang memiliki kesamaan username, email, atau nama lengkap dengan dirinya:
   - **Respon EWS**: Sistem menolak tindakan secara mutlak (*Hard Block*) dengan pesan peringatan: *"Pelanggaran SoD: Anda tidak diizinkan menyetujui akun pendaftaran Anda sendiri."*

2. **Peringatan Batasan Cabang (*Least Privilege Branch Isolation*)**:
   - Branch Admin hanya memiliki wewenang menyetujui penempatan pegawai untuk cabang yang ditugaskan kepadanya (`assigned_branch_id`).
   - **Respon EWS**: Jika mencoba memilih cabang lain, sistem memblokir form dengan pesan peringatan: *"Pelanggaran Hak Akses: Branch Admin hanya dapat menempatkan karyawan pada cabang yang ditugaskan."*

---

#### C. Dasbor Monitoring & Timer SLA Real-Time

Panel verifikasi admin dilengkapi antarmuka visual pendukung EWS:
- **Timer Hitung Mundur SLA (*SLA Countdown*)**: Setiap kartu pendaftaran menampilkan waktu berjalan sejak data disubmit (format: `X Jam Y Menit yang lalu`) beserta indikator progres menuju ambang batas 24 jam.
- **Widget Ringkasan SLA Verifikasi**:
  1. *Total Antrean Pending*: Seluruh berkas yang menunggu tindakan.
  2. *Siaga SLA (12–24 Jam)*: Jumlah berkas yang perlu segera ditinjau hari ini.
  3. *Pelanggaran SLA (> 24 Jam)*: Berkas yang telah melewati target SLA dan memerlukan intervensi langsung HR Manager.
  4. *Rata-rata Waktu Verifikasi (Turnaround Time)*: Metrik kecepatan rata-rata admin dalam menyelesaikan berkas pendaftaran (KPI Divisi HR).

---

### 3.5 Titik Sambung EWS Registrasi ke EWS Presensi (Seamless Handoff Architecture)

Titik sambung (*integration handoff*) menghubungkan siklus pendaftaran pegawai mandiri (fase staging) dengan mesin pemantauan disiplin harian (fase presensi aktif). Transisi ini menjamin bahwa pegawai baru langsung terikat pada parameter tata kelola presensi tanpa jeda administratif dan tanpa risiko data yatim (*orphaned data*).

```
 [ EWS REGISTRASI ]                          [ PROSES TRANSISI (MAKER-CHECKER) ]                        [ EWS PRESENSI OPERASIONAL ]
┌─────────────────────────┐                 ┌──────────────────────────────────────┐                   ┌───────────────────────────────┐
│ Titik 2: Staging        │                 │ Persetujuan Admin (ISO 27001):       │                   │ Karyawan Login Portal v2:     │
│ `employee_registrations`│ ──────────────► │ 1. Penetapan Cabang (branch_id)      │ ────────────────► │ /presensi2/login (PIN 6-Digit)│
│ Status: PENDING         │                 │ 2. Penetapan Role & Jabatan          │                   └──────────────┬────────────────┘
└─────────────────────────┘                 │ 3. Generate Kode EMP-XXX (Collision) │                                  │
                                            │ 4. Atomic Commit ke Master `employees`│                                  ▼
                                            └──────────────────────────────────────┘                   ┌───────────────────────────────┐
                                                                                                       │ Mesin EWS Presensi Aktif:     │
                                                                                                       │ • Level 1: Pengingat Proaktif │
                                                                                                       │ • Level 0: On-Time + Cek Stok │
                                                                                                       │ • Level 2: Lewat Grace Period │
                                                                                                       │ • Level 3: Eskalasi SPV Cabang│
                                                                                                       │ • Level 4: Tutup Kas & Pulang │
                                                                                                       └───────────────────────────────┘
```

#### A. 4 Pilar Pengikatan Data Otomatis (Automated Binding Mechanisms)
Saat Admin/HR mengeksekusi persetujuan di `/admin/karyawan-verifikasi`, sistem menjalankan transaksi atomik (`DB::transaction`) yang secara serempak melakukan 4 pengikatan:

1. **Pengikatan Identitas & Kredensial Tunggal (Identity Binding)**:
   - Mentransfer nama lengkap, username, nomor telepon, dan email dari `employee_registrations` ke `employees`.
   - Menerbitkan Kode Karyawan collision-safe berformat `EMP-XXX` (contoh: `EMP-015`).
   - Menyalin sandi (*hash*) dan **PIN 6-digit** sehingga pegawai dapat langsung menggunakan akunnya di portal `/presensi2/login` tanpa perlu aktivasi ulang.
   - Status pendaftaran staging diubah menjadi `approved` dengan merekam `approved_by` dan `approved_at`.

2. **Pengikatan Wilayah Kerja & Radius Geofencing (Geographic Boundary Binding)**:
   - Mengikat pegawai ke `branch_id` resmi (misal: Cabang Pusat MGK / `NET-PST`).
   - Koordinat pusat kantor cabang (`latitude`, `longitude`), batas radius toleransi presensi (`radius_meters = 100m`), dan validasi jaringan WiFi cabang otomatis menjadi acuan validasi scan absensi bagi pegawai tersebut.

3. **Pengikatan Roster & Shift Kerja Dinamis (Shift Resolver Binding)**:
   - Menghubungkan profil pegawai dengan master shift aktif (`ShiftResolverService` & `RosterResolverService`).
   - Jam kerja pegawai dipetakan ke pola shift outlet (Shift 1: 01.00–07.00, Shift 2: 07.00–13.00, Shift 3: 13.00–19.00, Shift 4: 19.00–01.00 lintas hari, atau Shift BO: 08.30–17.30).

4. **Pengikatan Jalur Eskalasi Manajerial (Escalation Route Binding)**:
   - Menentukan atasan langsung berdasarkan cabang penugasan (`branch_id`).
   - Apabila di kemudian hari pegawai memicu EWS Level 3 (keterlambatan kritis > 60 menit), tiket eskalasi langsung diarahkan ke **Supervisor Cabang terkait**, bukan ke cabang lain atau langsung ke HR pusat.

---

#### B. Matriks Pemetaan Field Transisi (*Data Mapping Matrix*)

| Field Pendaftaran Staging (`employee_registrations`) | Field Master Karyawan (`employees`) | Objek Konsumsi EWS Presensi | Fungsi dalam EWS Presensi |
| :--- | :--- | :--- | :--- |
| `id` | `created_employee_id` (di staging) | Audit Log Ref | Menghubungkan riwayat asal usul pendaftaran untuk audit trail ISO 27001. |
| `full_name` | `name` | `AttendanceRecord` | Nama tampilan di Dashboard Monitoring & pesan pengingat notifikasi. |
| `username` & `pin` | `username` & `pin` | Portal Auth v2 | Kredensial otentikasi saat Clock-In/Clock-Out di mesin presensi. |
| `phone` | `phone` | SMS / WhatsApp Gateway | Nomor kontak tujuan pengiriman Peringatan Dini Level 1, 2, dan 4. |
| `email` | `email` | SMTP Mailer | Alamat pengiriman ringkasan eskalasi dan slip kehadiran bulanan. |
| *(Dipilih saat verifikasi)* | `branch_id` | `GeoValidationService` | Validasi radius GPS geofence saat tombol Clock-In ditekan. |
| *(Dihasilkan otomatis)* | `code` (`EMP-XXX`) | ID Unik Rekaman | Kode unik pada tabel `attendance_records` dan ekspor data payroll. |

---

#### C. Protokol Penanganan Hari Pertama Bertugas (*First-Day Onboarding Rule*)
Untuk mencegah kesalahan pemantauan palsu (*false alert*) pada hari pertama pegawai bergabung:
1. **Aturan Tanggal Efektif Mulai Dinas**:
   - Jika verifikasi disetujui pada siang hari, EWS tidak akan memicu pinalti Level 2/3 untuk shift pagi yang telah berlalu pada hari tersebut.
   - Pemantauan EWS Level 1 (Pengingat Proaktif) baru mulai berjalan efektif pada shift pertama yang ditugaskan dalam roster jadwal kerja.
2. **Uji Coba Geofence Perdana**:
   - Pegawai disarankan melakukan login percobaan di portal `/presensi2` di lokasi cabang penugasan untuk memastikan perangkat mobile dan izin GPS browser telah terkalibrasi dengan radius cabang.

---

#### D. Titik Integrasi Presensi v2: Alur Pasca Clock-In
Begitu pegawai baru dinyatakan sah di Master Data dan melakukan presensi perdananya:
1. **Gerbang Masuk**: Clock-in tervalidasi sukses (Status: **Level 0 On-Time**).
2. **Serah Terima Otomatis**: Sistem langsung mengarahkan pegawai ke `/presensi2/stok` untuk melakukan checklist fisik kertas A4/F4, makanan, minuman, dan hardware.
3. **Gerbang Keluar**: Di akhir shift, EWS **Level 4** memandu pegawai menutup kas billing di `/presensi2/penjualan` sebelum melakukan clock-out kepulangan resmi.

---

## 4. Tingkatan Peringatan Presensi & Parameter Ambang Batas (Leveling Thresholds)

Sistem menggunakan matriks parameter berikut untuk memicu trigger pengingat dan eskalasi:

| Level | Status Klasifikasi | Kondisi Waktu & Pemicu | Target Distribusi | Tindakan Otomatis Sistem |
| :---: | :--- | :--- | :--- | :--- |
| **0** | **Normal / Tepat Waktu** | Melakukan presensi $\le$ batas jam masuk resmi (contoh: sebelum 08.00). | Pegawai | Mencatat data ke database; mengirimkan bukti konfirmasi sukses presensi. |
| **1** | **Pengingat Proaktif** | 15–30 menit sebelum batas jam masuk resmi belum tercatat hadir. | Pegawai | Mengirimkan push notification / WhatsApp pengingat ramah (*friendly reminder*). |
| **2** | **Peringatan Keterlambatan** | 5–15 menit pasca jam masuk resmi belum ada transaksi presensi. | Pegawai | Mengirimkan notifikasi keterlambatan dan instruksi segera presensi / lapor izin. |
| **3** | **Peringatan Kritis / Eskalasi** | 60 menit pasca jam masuk resmi tanpa transaksi dan tanpa pengajuan izin sah. | Pegawai & Atasan Langsung (Tembusan HR) | Mengubah status menjadi *Unconfirmed Absence*; mengirim tiket eskalasi ke atasan. |
| **4** | **Pengingat Presensi Pulang** | 30 menit setelah jam kerja usai belum tercatat transaksi *clock-out*. | Pegawai | Mengirimkan notifikasi pengingat *check-out* sebelum auto-lock harian berjalan. |

---

## 5. Arsitektur & Alur Kerja Sistem (Workflow)

```
[Sumber Data Masuk]
   ├── Mesin Biometrik (Fingerprint/Face)
   ├── Mobile Apps (GPS Geofencing + Liveness)
   └── Portal Web HR (Izin, Sakit, Cuti, Dinas Luar)
              │
              ▼
    [EWS Event & Cron Engine]
              │
              ├── Memeriksa Master Jadwal Kerja Pegawai
              ├── Mencocokkan Transaksi Presensi Real-Time
              └── Mengidentifikasi Pelanggaran Ambang Batas (Level 1–4)
              │
              ▼
     [Kanal Notifikasi Terpadu]
              ├── Mobile App Push Notification (FCM / APNs)
              ├── Official WhatsApp Business API
              ├── Notifikasi Email SMTP
              └── Live Monitoring Dashboard (HR & Manajerial)
```

### Mekanisme Eksekusi:
1. **Background Job Scheduler (Cron)**: Dijalankan setiap 60 detik pada rentang jam sibuk presensi (07.00 - 09.30 dan 16.30 - 18.30).
2. **Sinkronisasi Data**: Data biometrik fisik di-push secara real-time ke cloud database menggunakan WebSocket / Webhook API.
3. **Filter Pengecualian**: Sistem secara otomatis mengecualikan pegawai yang telah memiliki status disetujui untuk Cuti, Izin Sakit, atau Surat Perintah Tugas (SPT) Dinas Luar.

---

## 6. Standar Format Pesan Notifikasi (Notification Templates)

### 6.1 Level 1: Pengingat Sebelum Jam Masuk
- **Media**: Mobile Push Notification & WhatsApp
- **Template**:
  > *"Halo **{{nama_pegawai}}**, jam kerja Anda dimulai pukul **{{jam_masuk}}**. Sistem mendeteksi Anda belum melakukan presensi masuk. Segera lakukan presensi sebelum batas toleransi pukul **{{batas_toleransi}}**. Tetap utamakan keselamatan dalam perjalanan."*

### 6.2 Level 2: Peringatan Keterlambatan
- **Media**: WhatsApp & Push Notification
- **Template**:
  > *"Pemberitahuan: Yth. **{{nama_pegawai}}**, hingga pukul **{{waktu_sekarang}}** Anda belum tercatat melakukan presensi (Jam Masuk: **{{jam_masuk}}**). Silakan segera lakukan registrasi presensi. Jika Anda bertugas di luar kantor atau mengalami kendala, segera ajukan form justifikasi presensi melalui HR Portal."*

### 6.3 Level 3: Peringatan Kritis & Eskalasi Atasan
- **Media**: Email Otomatis & WhatsApp (Ke Atasan & Pegawai)
- **Template**:
  > *"ESKALASI PRESENSI: Yth. Bapak/Ibu **{{nama_atasan}}**, disampaikan bahwa anggota tim Anda:\n\n- Nama: **{{nama_pegawai}}**\n- NIP: **{{nip}}**\n- Unit: **{{unit_kerja}}**\n\nBelum melakukan presensi hingga pukul **{{waktu_sekarang}}** (terlambat > 60 menit) tanpa permohonan izin/dinas yang terdaftar di sistem. Mohon konfirmasi ketersediaan anggota tim yang bersangkutan untuk pembaruan status kehadiran."*

### 6.4 Level 4: Pengingat Kelalaian Check-out
- **Media**: Mobile Push Notification
- **Template**:
  > *"Halo **{{nama_pegawai}}**, jam kerja reguler telah selesai pada pukul **{{jam_pulang}}**. Anda belum melakukan presensi kepulangan (*clock-out*). Segera lakukan *clock-out* sebelum pukul 23:59 agar kalkulasi durasi kerja Anda tidak terpotong."*

---

## 7. Standar Operasional Prosedur (SOP) Penanganan & Tindak Lanjut

### 7.1 Alur Penanganan Kendala Teknis Presensi
1. Jika terjadi kegagalan jaringan, perangkat mobile rusak, atau alat biometrik luring, pegawai wajib mengambil dokumentasi pembuktian (foto lokasi kerja / screenshot eror).
2. Melaporkan kendala ke Tim IT Support & Admin HR maksimal 1 jam pasca jam kerja dimulai.
3. Admin HR melakukan verifikasi log audit dan melakukan penyesuaian manual (*system override*) dengan menyertakan bukti lampiran.

### 7.2 Alur Pengajuan Justifikasi / Koreksi Mandiri
1. Pegawai membuka menu **Koreksi Presensi** pada aplikasi.
2. Memilih kategori: *Lupa Presensi*, *Dinas Luar Mendadak*, atau *Kendala Teknis Lapangan*.
3. Mengunggah surat tugas/bukti valid dan mengirimkan formulir.
4. Notifikasi persetujuan (*approval request*) otomatis terkirim ke atasan langsung. Status EWS Level 2/3 otomatis gugur setelah formulir disetujui atasan.

### 7.3 Tindakan Indisipliner Berulang
- **Akumulasi 3x Level 2 dalam 1 bulan**: Sistem menerbitkan Peringatan Otomatis Tingkat 1 melalui portal pegawai.
- **Akumulasi 3x Level 3 dalam 1 bulan**: Sistem memblokir sementara opsi koreksi mandiri dan mewajibkan sesi konseling dengan Atasan Langsung serta Divisi HRD.

---

## 8. Dasbor Monitoring, Analitik, & Metrik KPI

### 8.1 Formula Perhitungan Metrik

1. **Attendance Rate (Tingkat Kehadiran Keseluruhan)**:
   $$\text{Attendance Rate} = \left( \frac{\sum \text{Pegawai Hadir}}{\sum \text{Total Pegawai Wajib Hadir}} \right) \times 100\%$$

2. **Punctuality Rate (Tingkat Ketepatan Waktu)**:
   $$\text{Punctuality Rate} = \left( \frac{\sum \text{Presensi Level 0}}{\sum \text{Pegawai Hadir}} \right) \times 100\%$$

3. **EWS Escalation Index (Tingkat Peringatan Kritis)**:
   $$\text{Escalation Index} = \left( \frac{\sum \text{Pemicu Level 3}}{\sum \text{Total Log Presensi Masuk}} \right) \times 100\%$$

### 8.2 Komponen Tampilan Dasbor
- **Live Counter Widget**: Menampilkan jumlah pegawai: *On-Time*, *Reminder Active*, *Late (Level 2)*, *Critical Unconfirmed (Level 3)*.
- **Heatmap Keterlambatan**: Visualisasi tren keterlambatan per divisi dan hari kerja dalam satu bulan berjalan.
- **Daftar Aksi Cepat**: Tombol *One-Click Ping* bagi atasan untuk menghubungi langsung pegawai yang teridentifikasi Level 3.

---

