# Dokumentasi Implementasi: Rekap Jam Kerja Per Outlet & Kehadiran Kegiatan (Meeting, Pelatihan, GC)

**Tanggal:** 18 Oktober 2026  
**Aplikasi:** Maya Graha Kencana (Sistem Presensi & Operasional)  
**URL Akses:** `http://192.168.5.10:8086/bagus/admin#data-presensi`  
**Database:** PostgreSQL (`bagus`) Port `5432` — Akses Navicat  

---

## 1. Ringkasan Kebutuhan Pengguna
Pengguna meminta penambahan dua modul pelaporan dan manajemen data baru yang diletakkan tepat di bawah **Rekap Jam Kerja Per Shift Bulan** pada menu **Data Presensi**:
1. **REKAP JAM KERJA PER OUTLET BULAN**
2. **REKAP KEHADIRAN KEGIATAN (MEETING, PELATIHAN, GC)** dengan kemampuan CRUD lengkap (Create, Read, Update, Delete).
3. Memastikan semua data tersimpan secara permanen dan terintegrasi ke database PostgreSQL yang dapat diakses melalui **Navicat**.
4. Menyimpan rangkuman percakapan dan spesifikasi teknis ke dalam file `.md`.

---

## 2. Fitur 1: Rekap Jam Kerja Per Outlet Bulanan

### Lokasi Tampilan
Terletak di tab presensi admin dashboard (`#rekap-outlet-section`), di bawah rekap shift bulanan.

### A. Kartu Statistik Utama
- **Total Outlet Terdaftar:** Jumlah cabang aktif yang terdata di sistem (`branches`).
- **Total Jam Kerja Seluruh Outlet:** Akumulasi jam kerja riil staf pada periode bulan berjalan (format: `X Jam Y Mnt`).
- **Total Sesi Masuk Outlet:** Total kedatangan staf di seluruh outlet pada bulan berjalan.
- **Total Lembur Outlet:** Akumulasi lembur tervalidasi cabang.

### B. Tabel 1: Akumulasi Total Jam Kerja Berdasarkan Outlet
Menampilkan data agregat per cabang:
- **Nama Outlet / Cabang & Kode:** Contoh: Maya Graha Kencana Pusat (`MGK-PST`).
- **Alamat / Lokasi:** Alamat cabang operasional.
- **Total Sesi Masuk:** Frekuensi shift kerja di outlet tersebut.
- **Total Jam Kerja:** Akumulasi durasi operasional staf di outlet.
- **Rata-Rata / Sesi:** Rata-rata jam kerja per sesi di outlet tersebut.
- **Total Lembur:** Akumulasi lembur staf di outlet.
- **Tingkat Disiplin:** Persentase ketepatan waktu hadir vs keterlambatan.
- **Jumlah Staf:** Jumlah personil yang bertugas di cabang tersebut.

### C. Tabel 2: Rincian Jam Kerja Staf per Outlet
Menampilkan rincian staf per cabang:
- **Karyawan:** Nama lengkap dan ID Karyawan (`code`).
- **Jabatan:** Posisi staf (Operator, Barista, Kasir, Teknisi, dll).
- **Penempatan Outlet:** Nama cabang penugasan.
- **Kehadiran:** Jumlah sesi masuk.
- **Total Jam Kerja:** Durasi jam kerja staf.
- **Total Lembur:** Durasi jam lembur staf.
- **Keterlambatan:** Status disiplin (Nol Telat / X Kali Telat).

---

## 3. Fitur 2: Rekap Kehadiran Kegiatan (Meeting, Pelatihan, GC)

### Lokasi Tampilan
Terletak di `#rekap-kegiatan-section`, di bawah Rekap Outlet pada tab presensi.

### A. Kategori Kegiatan
Sistem mendukung 3 kategori kegiatan operasional:
1. 🤝 **Rapat / Meeting Operasional (`meeting`):** Rapat evaluasi shift, rapat koordinasi bulanan, briefing operasional.
2. 🎓 **Pelatihan & Workshop SOP (`pelatihan`):** Pelatihan standar hospitality, pelatihan kasir POS, pelatihan penanganan kendala jaringan/PC.
3. 🧹 **General Cleaning / GC (`gc`):** Pembersihan menyeluruh outlet, pembersihan filter AC, pembersihan debu hardware & peripheral PC, serta sanitasi area cafe.

### B. Kemampuan CRUD (Create, Read, Update, Delete)
1. **Create (Tambah Kegiatan):**
   - Tombol `+ Tambah Catatan Kegiatan` membuka formulir collapsible secara instan.
   - Field input:
     - Kategori Kegiatan (`meeting`, `pelatihan`, `gc`)
     - Nama / Topik Agenda
     - Karyawan Peserta (dropdown dari `employees`)
     - Outlet Cabang (dropdown dari `branches`)
     - Tanggal Kegiatan
     - Jam Mulai & Jam Selesai
     - Durasi Kegiatan (Menit)
     - Status Kehadiran (`hadir`, `izin`, `alpa`)
     - Catatan Tambahan
   - Endpoint: `POST /admin/kegiatan` (`admin.kegiatan.store`).

2. **Read (Lihat Ringkasan & Log):**
   - **Kartu Statistik:** Total Agenda Terlaksana, Total Log Partisipasi, Total Jam Investasi Kegiatan.
   - **Tabel 1 (Ringkasan per Kategori):** Jumlah agenda unik, total partisipan, total jam durasi, rata-rata durasi per sesi, rasio status kehadiran, dan persentase kehadiran.
   - **Tabel 2 (Log & Riwayat Partisipasi Staf):** Menampilkan rincian setiap personil dengan badge visual kategori kegiatan dan status kehadiran.

3. **Update (Edit Kegiatan):**
   - Tombol **Edit (✏️)** pada setiap baris log kegiatan.
   - Membuka popup modal interaktif `#editActivityModal`.
   - Mengisi data lama secara otomatis (prefill) dan menyimpan perubahan via `PUT /admin/kegiatan/{id}` (`admin.kegiatan.update`).

4. **Delete (Hapus Kegiatan):**
   - Tombol **Hapus (🗑️)** dengan dialog konfirmasi keamanan browser.
   - Menghapus record secara permanen via `DELETE /admin/kegiatan/{id}` (`admin.kegiatan.destroy`).

---

## 4. Struktur Database & Navicat (PostgreSQL)

### Koneksi Navicat
- **Host / IP:** `192.168.5.10`
- **Port:** `5432`
- **Database:** `bagus`
- **Username:** `bagus`
- **Password:** `123456`

### Tabel Baru: `activity_attendances`
File Migrasi: `database/migrations/2026_10_18_094828_create_activity_attendances_table.php`

```sql
CREATE TABLE activity_attendances (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    branch_id BIGINT NULL REFERENCES branches(id) ON DELETE SET NULL,
    activity_type VARCHAR(50) NOT NULL, -- 'meeting', 'pelatihan', 'gc'
    title VARCHAR(255) NOT NULL,
    activity_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_minutes INTEGER NOT NULL DEFAULT 60,
    status VARCHAR(50) NOT NULL DEFAULT 'hadir', -- 'hadir', 'izin', 'alpa'
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Indeks performa query:
CREATE INDEX idx_activity_attendances_date ON activity_attendances(activity_date);
CREATE INDEX idx_activity_attendances_type ON activity_attendances(activity_type);
CREATE INDEX idx_activity_attendances_employee ON activity_attendances(employee_id);
CREATE INDEX idx_activity_attendances_branch ON activity_attendances(branch_id);
```

### Data Awal (Seeded Records)
File Seeder: `database/seeders/ActivityAttendanceSeeder.php`
- Telah diisi dengan **12 catatan riil** kegiatan untuk bulan Oktober 2026.
- Terdiri dari:
  - 4 record **Meeting Evaluasi Operasional & Rekap Shift**
  - 4 record **Pelatihan SOP Pelayanan, Kasir & Barista**
  - 4 record **General Cleaning (GC) Sanitasi & Pembersihan PC**

---

## 5. Rincian Perubahan File

| No | File | Deskripsi Perubahan |
|----|------|---------------------|
| 1 | `database/migrations/2026_10_18_094828_create_activity_attendances_table.php` | Membuat tabel `activity_attendances` di PostgreSQL. |
| 2 | `app/Domains/Attendance/Models/ActivityAttendance.php` | Model Eloquent dengan relasi ke `Employee` dan `Branch`, helper badge dan label. |
| 3 | `database/seeders/ActivityAttendanceSeeder.php` | Seeder untuk data awal kegiatan meeting, pelatihan, dan GC. |
| 4 | `routes/web.php` | Mendaftarkan route CRUD kegiatan: `POST /admin/kegiatan`, `PUT /admin/kegiatan/{id}`, `DELETE /admin/kegiatan/{id}`. |
| 5 | `app/Http/Controllers/Admin/AdminAttendanceController.php` | Menambahkan method `storeActivity()`, `updateActivity()`, dan `destroyActivity()` lengkap dengan validasi dan Audit Log. |
| 6 | `app/Http/Controllers/Admin/AdminDashboardController.php` | Menambahkan kalkulasi agregat `rekapOutletSummary` dan `rekapKegiatanSummary`, serta meneruskannya ke compact view. |
| 7 | `resources/views/admin/dashboard/index.blade.php` | Menambahkan template UI Rekap Outlet, Rekap Kegiatan (Tabel, Form Collapsible, Modal Edit, Script JS). |
| 8 | `tests/Feature/AdminDashboardRevampTest.php` | Menambahkan pengujian otomatis untuk rendering rekap outlet, kegiatan, dan CRUD kegiatan. |

---

## 6. Verifikasi & Pengujian

### Pengujian Otomatis (PHPUnit)
Jalankan perintah pengujian:
```bash
php artisan test --filter=AdminDashboardRevampTest --compact
```
**Hasil:**
- Tests: 7 passed
- Assertions: 57 passed
- Status: **100% HIJAU (PASSED)**

### Linting & Formatting (Laravel Pint)
```bash
vendor/bin/pint --format agent
```
**Hasil:**
- Status: **PASSED (Clean code style)**

---

## 7. Cara Mengakses di Browser
1. Buka browser dan arahkan ke:  
   **`http://192.168.5.10:8086/bagus/admin#data-presensi`**
2. Klik tab **"Presensi"** pada panel kiri "1. Data".
3. Gulir ke bawah:
   - Di bawah tabel presensi harian, Anda akan melihat **Rekap Jam Kerja Per Shift Bulanan**.
   - Tepat di bawahnya, Anda akan melihat **🏬 Rekap Jam Kerja Per Outlet Bulanan**.
   - Tepat di bawahnya lagi, Anda akan melihat **🎯 Rekap Kehadiran Kegiatan (Meeting, Pelatihan, GC)** lengkap dengan tombol **Tambah Catatan Kegiatan**, tabel ringkasan, dan tabel rincian dengan tombol **Edit** & **Hapus**.
