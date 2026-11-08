# Dokumentasi Fitur: Tombol & Halaman Presensi Khusus & Ambil Stok

**Tanggal:** 18 Oktober 2026  
**Aplikasi:** Maya Graha Kencana (Portal Presensi v2 & ERP)  
**URL Portal Login:** `http://192.168.5.10:8086/bagus/presensi2/login`  
**Database:** PostgreSQL (`bagus`) Port `5432` — Terintegrasi Navicat  

---

## 1. Ringkasan Kebutuhan & Implementasi
Pengguna meminta penambahan dan perbaikan pada halaman login portal presensi `http://192.168.5.10:8086/bagus/presensi2/login` dengan menambahkan 4 tombol aksi cepat beserta halaman mandiri di dalamnya yang didesain persis seperti halaman presensi utama:

1. 🧹 **General Cleaning (GC)**
2. 🤝 **Meeting**
3. 💼 **Presnesi BO/Training**
4. 📦 **AMBIL STOK**

---

## 2. Portal Login Khusus Layanan & Presensi Tambahan (Terpisah dari Login Karyawan)

Sesuai instruksi pengguna, login untuk 4 layanan tambahan **tidak lagi digabung dengan Portal Login Karyawan** (portal presensi shift reguler):
1. **Portal Login Mandiri (`/presensi2/layanan/login`):**
   - Halaman login khusus dan terpisah dengan desain modern cyber-glassmorphism bertema multi-warna.
   - Dilengkapi 4 tombol/tab switcher cepat untuk memilih layanan:
     - 🧹 **General Cleaning (GC):** Aksen Amber Glow
     - 🤝 **Meeting:** Aksen Sky Blue Glow
     - 💼 **BO / Training:** Aksen Emerald Green Glow
     - 📦 **AMBIL STOK:** Aksen Violet Purple Glow
   - Meminta pemilihan identitas karyawan dan verifikasi **Password atau PIN 6-digit** sebelum memberikan akses.
2. **Tidak Bisa Masuk Langsung / Tanpa Kredensial:**
   - Tamu yang belum login akan otomatis dialihkan ke Portal Login Layanan Khusus ini (`/presensi2/layanan/login?service=...`), **bukan** ke Login Karyawan reguler.
   - Dropdown bebas pilih nama orang lain di dalam halaman telah **dihapus**. Identitas langsung dikunci ke akun staf yang berhasil login (`$currentEmployee`).
3. **Verifikasi Password / PIN di Formulir:**
   - Saat submit kegiatan/permintaan, staf tetap mengonfirmasi kredensial keamanan (Password/PIN) untuk mencegah penyalahgunaan perangkat outlet yang ditinggalkan.
4. **Alur Logout / Ganti Akun:**
   - Tombol `🚪 Ganti Akun` di dalam halaman GC, Meeting, BO/Training, dan Ambil Stok akan logout khusus dari sesi Layanan dan mengembalikan pengguna ke `/presensi2/layanan/login`.

---

## 3. Rincian 4 Halaman Layanan Tambahan (Terautentikasi)

Setiap halaman menggunakan basis layout premium `layouts/presensi2.blade.php` lengkap dengan:
- **Kartu Identitas Karyawan Terautentikasi:** Menampilkan nama, NIK/kode karyawan, posisi, cabang tugas, badge status `🔒 Terverifikasi Login`, serta tombol `🚪 Ganti Akun`.
- **Field Kredensial Keamanan:** Input Password / PIN 6-Digit wajib diisi saat submit formulir.
- **Header Outlet / Cabang** & Badge kategori kegiatan.
- **Live Digital Clock (WIB):** Jam digital dinamis detik demi detik berukuran besar dengan penanggalan bahasa Indonesia.
- **Kartu Statistik Ringkasan Hari Ini:** Total staf terlibat, total sesi, total jam kegiatan/pengambilan.
- **Tombol Navigasi Cepat:** `← Portal Layanan` dan `🏠 Dashboard Presensi`.
- **Formulir Interaktif:** Quick preset chips 1-klik, jam mulai & selesai, durasi menit, catatan.
- **Log Riwayat Real-Time Hari Ini:** Menampilkan riwayat kehadiran/transaksi staf hari ini secara transparan.

### A. Halaman General Cleaning (GC) (`/presensi2/gc`)
- **Fokus:** Pembersihan menyeluruh outlet, debu PC Station & peripheral, filter AC, sanitasi meja cafe, kasir, dan toilet.
- **Verifikasi:** Password / PIN 6-digit staf yang sedang login di Portal Layanan.
- **Aksi Cepat (Chips):**
  - 🖥️ PC Station
  - ❄️ Filter AC
  - ☕ Area Cafe
  - 🧾 Kasir
  - 🚿 Toilet & Lantai
- **Tabel Database:** `activity_attendances` (`activity_type = 'gc'`).

### B. Halaman Meeting (`/presensi2/meeting`)
- **Fokus:** Presensi rapat evaluasi shift, briefing tim operasional, koordinasi kendala teknis jaringan/PC, dan evaluasi bulanan.
- **Verifikasi:** Password / PIN 6-digit staf yang sedang login di Portal Layanan.
- **Aksi Cepat (Chips):**
  - ⚡ Briefing Shift
  - 📅 Rapat Mingguan
  - ⭐ SOP & Layanan
  - 🖥️ Kendala Teknis
  - 📊 Rapat Bulanan
- **Tabel Database:** `activity_attendances` (`activity_type = 'meeting'`).

### C. Halaman Presensi BO/Training (`/presensi2/bo-training`)
- **Fokus:** Presensi kehadiran staf Back Office (BO) & Pelatihan / Workshop SOP.
- **Verifikasi:** Password / PIN 6-digit staf yang sedang login di Portal Layanan.
- **Kategori Radio Card:**
  - 🎓 **Pelatihan / Workshop SOP:** Hospitality, kasir POS, troubleshooting PC, sanitasi shift.
  - 💼 **Back Office (BO):** Tugas pembukuan, administrasi, rekapitulasi data.
- **Tabel Database:** `activity_attendances` (`activity_type = 'pelatihan'` atau `'bo'`).

### D. Halaman AMBIL STOK (`/presensi2/ambil-stok`)
- **Fokus:** Permintaan restock loker kasir & konfirmasi penerimaan batch transfer logistik dari Gudang Pusat.
- **Verifikasi:** Password / PIN 6-digit staf yang sedang login di Portal Layanan.
- **Fitur Utama:**
  1. **Formulir Ambil Stok:** Kategori barang (Kertas, Minuman, Makanan, Hardware, Sanitasi), Nama Barang (dengan quick chips seperti Kertas A4 Rim, Kertas F4 Rim, Thermal Kasir, Le Minerale, Teh Pucuk, Indomie, Mouse), Qty, Satuan, Tingkat Urgensi (Normal, Mendesak, Kritis), dan Catatan.
  2. **Penerimaan Pengiriman Stok (Transfer Gudang Pusat):** Daftar batch pengiriman dari gudang pusat dengan tombol 1-klik **"✅ Konfirmasi Terima"** untuk verifikasi barang yang sampai di kasir/outlet.
  3. **Log Permintaan Hari Ini:** Nomor permohonan request, nama staf, cabang, dan status.
- **Tabel Database:** `shift_stock_requests` & `stock_transfer_dispatches`.

---

## 4. Integrasi Database PostgreSQL & Navicat

Semua halaman dan menu telah terdaftar dan terintegrasi secara permanen pada database PostgreSQL:
- **Tabel `system_menus`:**
  - `presensi2_gc`
  - `presensi2_meeting`
  - `presensi2_bo_training`
  - `presensi2_ambil_stok`
- **Tabel `activity_attendances`:** Menyimpan data GC, Meeting, BO, dan Training. Terhubung langsung ke modul Admin Dashboard **Rekap Kehadiran Kegiatan (Meeting, Pelatihan, GC)**.
- **Tabel `shift_stock_requests`:** Menyimpan permintaan ambil stok dari kasir/staf outlet.
- **Tabel `stock_transfer_dispatches`:** Mengelola status penerimaan pengiriman barang dari gudang pusat.

---

## 5. Pengujian & Verifikasi (Automated Test Suite)

Pengujian komprehensif dilakukan menggunakan PHPUnit pada `tests/Feature/AttendanceWebV2PortalTest.php` (**33 tests, 175 assertions passed**):
- `test_login_page_displays_all_four_new_quick_buttons` &rarr; `PASSED`
- `test_guest_is_redirected_to_dedicated_layanan_login_when_accessing_services` &rarr; `PASSED`
- `test_can_access_dedicated_layanan_login_portal` &rarr; `PASSED`
- `test_employee_can_login_through_dedicated_layanan_portal` &rarr; `PASSED`
- `test_can_view_and_submit_general_cleaning_presensi_with_valid_credentials` &rarr; `PASSED`
- `test_can_view_and_submit_meeting_presensi_with_valid_credentials` &rarr; `PASSED`
- `test_can_view_and_submit_bo_and_training_presensi_with_valid_credentials` &rarr; `PASSED`
- `test_can_view_and_submit_ambil_stok_with_valid_credentials` &rarr; `PASSED`
- `test_employee_can_logout_from_layanan_portal` &rarr; `PASSED`

**Hasil Pengujian:**
```
Tests:    33 passed (175 assertions)
Duration: 1.86s
```
