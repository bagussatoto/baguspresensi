-- ==============================================================================
-- Maya Graha Kencana (MGK) - Skrip SQL Manajemen Gambar Jadwal Shift Operator
-- File: database/schema/shift_schedule_images.sql
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 1. DDL: STRUKTUR TABEL (PostgreSQL - Database Default Aplikasi)
-- ------------------------------------------------------------------------------
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

-- Indeks untuk mempercepat pencarian jadwal aktif dan urutan kronologis
CREATE INDEX IF NOT EXISTS idx_schedule_is_active ON shift_schedule_images (is_active);
CREATE INDEX IF NOT EXISTS idx_schedule_created_at ON shift_schedule_images (created_at DESC);


-- ------------------------------------------------------------------------------
-- 2. DDL ALTERNATIF: MySQL / MariaDB
-- ------------------------------------------------------------------------------
-- CREATE TABLE IF NOT EXISTS shift_schedule_images (
--     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     title VARCHAR(200) NOT NULL DEFAULT 'Jadwal Shift',
--     image_path VARCHAR(255) NOT NULL,
--     period VARCHAR(100) NULL,
--     notes TEXT NULL,
--     is_active TINYINT(1) NOT NULL DEFAULT 1,
--     created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     INDEX idx_schedule_is_active (is_active),
--     INDEX idx_schedule_created_at (created_at)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------------------------
-- 3. DML: OPERASI QUERY HARIAN SISTEM
-- ------------------------------------------------------------------------------

-- [A] Memasukkan Gambar Jadwal Shift Baru (Nonaktifkan jadwal lama terlebih dahulu)
-- Langkah 1:
UPDATE shift_schedule_images 
SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP 
WHERE is_active = TRUE;

-- Langkah 2:
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
    'Operator Shift 4 (Malam) harap koordinasi serah terima kasir tepat pukul 01:00 WIB.',
    TRUE,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);


-- [B] Mengambil Gambar Jadwal yang Sedang Aktif (Untuk POS Kasir & Karyawan)
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


-- [C] Mengganti / Mengembalikan Status Jadwal Aktif (Rollback ke Jadwal Tertentu)
-- Misal mengaktifkan kembali jadwal dengan ID = 1
UPDATE shift_schedule_images 
SET is_active = FALSE 
WHERE is_active = TRUE;

UPDATE shift_schedule_images 
SET is_active = TRUE, updated_at = CURRENT_TIMESTAMP 
WHERE id = 1;


-- [D] Menampilkan Riwayat Lengkap Gambar Jadwal Shift
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


-- [E] Menghapus Arsip Jadwal Tertentu
DELETE FROM shift_schedule_images 
WHERE id = 99;
