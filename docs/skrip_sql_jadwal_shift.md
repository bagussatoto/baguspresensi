# 🗄️ Skrip SQL Manajemen Gambar Jadwal Shift Operator
**Organisasi:** Maya Graha Kencana (MGK)  
**Tabel Terkait:** `shift_schedule_images`  
**Database Engine:** PostgreSQL (Default) / MySQL (Alternatif)  
**Dokumentasi Terkait:** [docs/sop_panduan_operasional_presensi.md](file:///var/www/laravel_apps/bagus/docs/sop_panduan_operasional_presensi.md) & [docs/tahapan_eksekusi_presensi.md](file:///var/www/laravel_apps/bagus/docs/tahapan_eksekusi_presensi.md)

---

## 📌 1. Definisi Struktur Tabel (Data Definition Language - DDL)

### 1.1 Versi PostgreSQL (Database Default Aplikasi MGK)
Gunakan skrip berikut untuk membuat tabel dan indeks pada PostgreSQL:

```sql
-- Buat tabel penyimpanan metadata gambar bagan jadwal shift
CREATE TABLE IF NOT EXISTS shift_schedule_images (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL DEFAULT 'Jadwal Shift',
    image_path VARCHAR(255) NOT NULL,
    period VARCHAR(100) NULL,
    notes TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indeks untuk mengoptimalkan performa pencarian jadwal aktif & pengurutan kronologis
CREATE INDEX IF NOT EXISTS idx_schedule_is_active ON shift_schedule_images (is_active);
CREATE INDEX IF NOT EXISTS idx_schedule_created_at ON shift_schedule_images (created_at DESC);
```

### 1.2 Versi MySQL / MariaDB (Alternatif)
Jika di masa depan menggunakan database engine MySQL/MariaDB:

```sql
CREATE TABLE IF NOT EXISTS shift_schedule_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL DEFAULT 'Jadwal Shift',
    image_path VARCHAR(255) NOT NULL,
    period VARCHAR(100) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_schedule_is_active (is_active),
    INDEX idx_schedule_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📝 2. Kueri Operasional Harian (Data Manipulation Language - DML)

### 2.1 Mengunggah & Menerapkan Jadwal Shift Baru
Ketika gambar jadwal shift baru diunggah oleh Admin, sistem menjalankan transaksi 2 langkah untuk memastikan hanya ada 1 jadwal yang berstatus aktif:

```sql
-- Langkah 1: Nonaktifkan jadwal yang saat ini aktif
UPDATE shift_schedule_images 
SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP 
WHERE is_active = TRUE;

-- Langkah 2: Tambahkan rekaman jadwal baru sebagai jadwal aktif
INSERT INTO shift_schedule_images (
    title, 
    image_path, 
    period, 
    notes, 
    is_active, 
    created_at, 
    updated_at
) VALUES (
    'Jadwal Shift Operator Minggu ke-2 Oktober',
    'uploads/jadwal/jadwal-shift-20261013_150000.jpg',
    '13 - 19 Oktober 2026',
    'Operator Shift 4 (Malam) harap standby 15 menit sebelum waktu serah terima kasir.',
    TRUE,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);
```

---

### 2.2 Mengambil Jadwal Aktif (Untuk Kasir POS Tab 5 & Portal Karyawan)
Kueri ini digunakan untuk menampilkan gambar jadwal yang sedang berlaku:

```sql
SELECT 
    id, 
    title, 
    image_path, 
    period, 
    notes, 
    updated_at
FROM shift_schedule_images
WHERE is_active = TRUE
ORDER BY id DESC
LIMIT 1;
```

---

### 2.3 Rollback / Mengaktifkan Kembali Jadwal Versi Terdahulu
Jika admin ingin mengembalikan jadwal ke versi minggu sebelumnya (misalnya jadwal dengan `id = 1`):

```sql
-- Langkah 1: Nonaktifkan jadwal yang sedang aktif saat ini
UPDATE shift_schedule_images 
SET is_active = FALSE 
WHERE is_active = TRUE;

-- Langkah 2: Aktifkan kembali rekaman jadwal yang diinginkan
UPDATE shift_schedule_images 
SET is_active = TRUE, updated_at = CURRENT_TIMESTAMP 
WHERE id = 1;
```

---

### 2.4 Menampilkan Riwayat Lengkap Seluruh Jadwal (History)
Kueri untuk menyajikan daftar arsip jadwal pada panel kontrol supervisor:

```sql
SELECT 
    id, 
    title, 
    period, 
    image_path, 
    is_active, 
    created_at,
    updated_at
FROM shift_schedule_images
ORDER BY id DESC;
```

---

### 2.5 Menghapus Arsip Jadwal Tertentu
Untuk menghapus arsip jadwal usang berdasarkan ID:

```sql
DELETE FROM shift_schedule_images 
WHERE id = 3;
```
