# 📚 Direktori & Indeks Dokumentasi Sistem

Dokumentasi proyek **Bagus (Sistem Presensi, POS Kasir, & ERP MGK)** diorganisasi secara sistematis per tanggal pembuatan dan sesi evaluasi teknis.

---

## 🗂️ Struktur Folder Berdasarkan Tanggal

### 📁 `docs/referensi/` — E-Book & Referensi Teoretis
* [domain-driven-design-distilled.pdf](referensi/domain-driven-design-distilled.pdf) — E-Book ringkasan implementasi Domain-Driven Design (Vaughn Vernon).
* [Eric Evans 2003 - Domain-Driven Design](referensi/Eric%20Evans%202003%20-%20Domain-Driven%20Design%20-%20Tackling%20Complexity%20in%20the%20Heart%20of%20Software.pdf) — Buku fondasi utama DDD software architecture.
* [Monolith-to-Microservices.pdf](referensi/Monolith-to-Microservices.pdf) — Panduan transisi arsitektur monolit ke modular/microservices (Sam Newman).
* [the-data-model-a-library-of-universal-data-models-for-all-enterprises-pdf-free.pdf](referensi/the-data-model-a-library-of-universal-data-models-for-all-enterprises-pdf-free.pdf) — Pola desain model basis data universal (Len Silverston).
* [tren dan tangangan implementasi domain driven design dalam era digital.pdf](referensi/tren%20dan%20tangangan%20implementasi%20domain%20driven%20design%20dalam%20era%20digital.pdf) — Jurnal/Paper implementasi DDD.
* [Tren_dan_Tantangan_Implementasi_Domain_Driven_Desi.pdf](referensi/Tren_dan_Tantangan_Implementasi_Domain_Driven_Desi.pdf) — Paper komputasi implementasi DDD.
* [extracted_tren_ddd.txt](referensi/extracted_tren_ddd.txt) — Ekstraksi teks dari literatur tren DDD.

---

### 📁 `docs/2026-10-10/` — Perencanaan Fondasi Arsitektur
* [`BLUEPRINT_RAMPING_5_MODUL_DDD_PRAGMATIS.md`](2026-10-10/BLUEPRINT_RAMPING_5_MODUL_DDD_PRAGMATIS.md) — Blueprint ramping 5 modul utama ERP DDD (MasterData, Pembelian, Distribusi, Penjualan, Kas).

---

### 📁 `docs/2026-10-11/` — Integrasi API Presensi
* [`panduan_integrasi_api_presensi.md`](2026-10-11/panduan_integrasi_api_presensi.md) — Spesifikasi teknis integrasi REST API Mobile/Frontend v1 (Presensi & Cuti).

---

### 📁 `docs/2026-10-13/` — Arsitektur Presensi, Skema DB, & SOP
* [`arsitektur_ddd_presensi.md`](2026-10-13/arsitektur_ddd_presensi.md) — Arsitektur DDD Pragmatis Sistem Presensi PWA.
* [`arsitektur_logika_absensi.md`](2026-10-13/arsitektur_logika_absensi.md) — Logika & desain data absensi hybrid (Pekerja Pabrik Jadwal/Shift vs Pekerja Desain/Fleksibel).
* [`cara_login_presensi.md`](2026-10-13/cara_login_presensi.md) — Panduan login presensi karyawan.
* [`catatan_sesi_eksekusi_presensi.md`](2026-10-13/catatan_sesi_eksekusi_presensi.md) — Riwayat sesi percakapan & eksekusi presensi.
* [`roadmap_presensi.md`](2026-10-13/roadmap_presensi.md) — Roadmap komprehensif sistem presensi (Fase 1 s/d Fase 8).
* [`skrip_sql_database_presensi_bagus.md`](2026-10-13/skrip_sql_database_presensi_bagus.md) — Skrip SQL DDL/DML untuk Navicat Premium.
* [`skrip_sql_jadwal_shift.md`](2026-10-13/skrip_sql_jadwal_shift.md) — Skrip SQL tabel gambar jadwal shift operator.
* [`sop_panduan_operasional_presensi.md`](2026-10-13/sop_panduan_operasional_presensi.md) — Standar Operasional Prosedur (SOP) operasional presensi karyawan dan kiosk.

---

### 📁 `docs/2026-10-17/` — Tahapan Eksekusi, Pipeline, & Navicat
* [`alur_cara_presensi.md`](2026-10-17/alur_cara_presensi.md) — Alur lengkap dan visualisasi panduan presensi karyawan.
* [`alur_stok_barang_dan_penjualan_presensi.md`](2026-10-17/alur_stok_barang_dan_penjualan_presensi.md) — Panduan modul stok barang dan penjualan pada antarmuka presensi.
* [`panduan_koneksi_database_navicat.md`](2026-10-17/panduan_koneksi_database_navicat.md) — Panduan koneksi database PostgreSQL aplikasi Bagus ke Navicat Premium.
* [`pipeline.md`](2026-10-17/pipeline.md) — Pipeline arsitektur & eksekusi bertahap sistem presensi karyawan.
* [`rencana_implemantasi_karyawan.md`](2026-10-17/rencana_implemantasi_karyawan.md) — Rencana implementasi detail sistem presensi karyawan.
* [`tahapan_eksekusi_presensi.md`](2026-10-17/tahapan_eksekusi_presensi.md) — Master tracker 13 fase implementasi.

---

### 📁 `docs/2026-10-18/` — Presensi Khusus, Ambil Stok, & Postman
* [`halaman_presensi_khusus_dan_ambil_stok.md`](2026-10-18/halaman_presensi_khusus_dan_ambil_stok.md) — Dokumentasi fitur tombol presensi khusus & alur ambil stok barang.
* [`panduan_postman_dan_postgresql_api.md`](2026-10-18/panduan_postman_dan_postgresql_api.md) — Panduan pengujian endpoint API menggunakan Postman dan koneksi PostgreSQL.
* [`percakapan_sod_sot_iso_maker_checker.mc`](2026-10-18/percakapan_sod_sot_iso_maker_checker.mc) — Catatan arsitektur pendaftaran mandiri karyawan dengan pola maker-checker.
* [`rekap_jam_kerja_outlet_dan_kegiatan.md`](2026-10-18/rekap_jam_kerja_outlet_dan_kegiatan.md) — Fitur rekapitulasi jam kerja per outlet dan kehadiran kegiatan khusus.

---

### 📁 `docs/2026-10-21/` — Tata Kelola Standar: SoT, SoD, & Kepatuhan ISO 27001
* [`checklist_sot_sod_iso.md`](2026-10-21/checklist_sot_sod_iso.md) — Checklist kepatuhan Single Source of Truth, Segregation of Duties, dan ISO/IEC 27001:2022.
* [`iso_27000_aplikasi_presensi.mc`](2026-10-21/iso_27000_aplikasi_presensi.mc) — Evaluasi kepatuhan kontrol ISO 27001 untuk aplikasi presensi.
* [`iso_27000_pendaftaran_mandiri.mc`](2026-10-21/iso_27000_pendaftaran_mandiri.mc) — Evaluasi kepatuhan kontrol ISO 27001 untuk pendaftaran mandiri.
* [`kuesioner_evaluasi_sot_sod_iso.md`](2026-10-21/kuesioner_evaluasi_sot_sod_iso.md) — Kuesioner uji petik audit internal untuk tata kelola dan resiliensi sistem.
* [`percakapan_sod_sot_iso_maker_checker.md`](2026-10-21/percakapan_sod_sot_iso_maker_checker.md) — Dokumentasi arsitektur final maker-checker pendaftaran mandiri.
* [`sod_aplikasi_presensi.mc`](2026-10-21/sod_aplikasi_presensi.mc) — Evaluasi Segregation of Duties (SoD) pada aplikasi presensi.
* [`sod_pendaftaran_mandiri.mc`](2026-10-21/sod_pendaftaran_mandiri.mc) — Evaluasi Segregation of Duties (SoD) pada pendaftaran mandiri.
* [`sot_aplikasi_presensi.mc`](2026-10-21/sot_aplikasi_presensi.mc) — Evaluasi Single Source of Truth (SoT) aplikasi presensi.
* [`SoT.txt`](2026-10-21/SoT.txt) — Tracker status penyelesaian 6 butir dokumen tata kelola (100% selesai).

---

### 📁 `docs/2026-10-24/` — Early Warning System & Evaluasi Lengkap
* [`modul_early_warning_presensi.md`](2026-10-24/modul_early_warning_presensi.md) — Spesifikasi Modul Sistem Peringatan Dini (Early Warning System) registrasi dan presensi.
* [`percakapan_evaluasi_sot_sod_iso_lengkap.md`](2026-10-24/percakapan_evaluasi_sot_sod_iso_lengkap.md) — Rangkuman evaluasi kepatuhan SoT, SoD, dan ISO 27001 secara menyeluruh.
* [`registrasi diperlukan.txt`](2026-10-24/registrasi%20diperlukan.txt) — Catatan parameter unik registrasi (username unik & nomor HP unik).

---

### 📁 `docs/2026-10-25/` — Keamanan Registrasi Mandiri & Reset Password
* [`notifikasi keamanan.txt`](2026-10-25/notifikasi%20keamanan.txt) — Poin pengingat penambahan notifikasi celah keamanan registrasi.
* [`percakapan_implementasi_registrasi.md`](2026-10-25/percakapan_implementasi_registrasi.md) — Dokumentasi fitur registrasi mandiri, lupa password, dan penguatan akun portal.
* [`rencana_pendaftaran_ulang_registrasi.md`](2026-10-25/rencana_pendaftaran_ulang_registrasi.md) — Rencana kerja harian alur pendaftaran ulang karyawan.

---

### 📁 `docs/2026-10-29/` — Evaluasi Brute-Force, Early Warning, & Reset Password
* [`291026.txt`](2026-10-29/291026.txt) — Catatan cepat sesi pengetatan keamanan 29 Oktober 2026.
* [`evaluasi_early_warning_alert_login_berulang.md`](2026-10-29/evaluasi_early_warning_alert_login_berulang.md) — Laporan evaluasi fitur peringatan dini pada percobaan gagal login berulang.
* [`panduan_keamanan_reset_password_dan_cek_status.md`](2026-10-29/panduan_keamanan_reset_password_dan_cek_status.md) — Panduan otorisasi admin untuk reset password dan portal cek status mandiri.
* [`pembaruan_input_nama_karyawan_login_presensi.md`](2026-10-29/pembaruan_input_nama_karyawan_login_presensi.md) — Dokumentasi pembaruan input teks manual nama karyawan pada portal presensi v2.

---

### 📁 `docs/2026-10-31/` — Operasional Kasir POS, Notifikasi WhatsApp, Rekap Shift & Printer
* [`dokumentasi_data_print_dan_printer_canon.md`](2026-10-31/dokumentasi_data_print_dan_printer_canon.md) — Dokumentasi arsitektur monitoring counter cetak USB Printer Canon dan sinkronisasi ke server.
* [`notifikasi_keamanan_grup_whatsapp.md`](2026-10-31/notifikasi_keamanan_grup_whatsapp.md) — Standarisasi notifikasi otomatis ke grup WhatsApp *🚨 Security & HRD Alert MGK*.
* [`panduan_operasional_kasir_stok_rekap.md`](2026-10-31/panduan_operasional_kasir_stok_rekap.md) — Panduan operasional Kasir Shift, Stok Awal, Rekapitulasi Shift (`presensi2`), dan Admin ERP.
* [`percakapan_keamanan_login_dan_alert_whatsapp.md`](2026-10-31/percakapan_keamanan_login_dan_alert_whatsapp.md) — Rangkuman evaluasi keamanan brute-force login dan integrasi alert WhatsApp.
* [`rekap_dan_summary_serah_terima.md`](2026-10-31/rekap_dan_summary_serah_terima.md) — Dokumentasi History Setor Shift, cetak slip Laporan Summary Harian 4 Shift, dan konfirmasi serah terima kas.
* [`uat_integrasi_printer_canon_pos_rekap.md`](2026-10-31/uat_integrasi_printer_canon_pos_rekap.md) — UAT User Acceptance Testing Printer Canon USB, Auto-Refresh POS, & Rekonsiliasi Rekap Kasir.

---

## 📌 Standar Konvensi Pembuatan Dokumen Baru

Saat Anda membuat dokumen baru atau melakukan evaluasi teknis, ikuti konvensi berikut agar arsip selalu rapi dan mudah dicari:

1. **Buat Folder Sesuai Tanggal Hari Ini**:
   Gunakan format `YYYY-MM-DD` (contoh: `docs/2026-11-01/`).
2. **Penamaan File yang Deskriptif**:
   Gunakan huruf kecil dengan pemisah garis bawah (snake_case):
   - Catatan evaluasi: `evaluasi_[topik].md`
   - Hasil testing / UAT: `uat_[fitur].md`
   - Panduan operasional: `panduan_[fitur].md`
   - Ringkasan diskusi: `percakapan_[topik].md`
3. **Perbarui Indeks**:
   Tambahkan entri link dokumen baru ke dalam file `docs/README.md` ini.

