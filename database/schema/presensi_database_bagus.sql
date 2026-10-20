-- ==============================================================================
-- SKRIP SQL DATABASE PRESENSI & JADWAL SHIFT MAYA GRAHA KENCANA (MGK)
-- Database Target  : bagus
-- DBMS Engine      : PostgreSQL 14+ / 15+ / 16+
-- Tool Eksekusi    : Navicat Premium / DBeaver / pgAdmin / psql
-- Schema           : public
-- Tanggal Dibuat   : 13 Oktober 2026
-- ==============================================================================

-- ==============================================================================
-- BAGIAN 1: TABEL MASTER DATA (CABANG & KARYAWAN)
-- ==============================================================================

-- 1. Tabel Cabang Operasional & Geofencing GPS (branches)
CREATE TABLE IF NOT EXISTS public.branches (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    is_headquarters BOOLEAN NOT NULL DEFAULT FALSE,
    address TEXT NULL,
    latitude NUMERIC(10, 8) NULL,
    longitude NUMERIC(11, 8) NULL,
    radius_meters INTEGER NOT NULL DEFAULT 100,
    phone VARCHAR(50) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_branches_code ON public.branches(code);
CREATE INDEX IF NOT EXISTS idx_branches_is_active ON public.branches(is_active);

-- 2. Tabel Karyawan & Operator (employees)
CREATE TABLE IF NOT EXISTS public.employees (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NULL REFERENCES public.branches(id) ON DELETE SET NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'operator',
    max_discount_approval_percent NUMERIC(5, 2) NOT NULL DEFAULT 0.00,
    phone VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    username VARCHAR(100) NULL,
    position VARCHAR(100) NULL DEFAULT 'Kasir',
    jabatan VARCHAR(100) NULL DEFAULT 'sale',
    content_permission VARCHAR(50) NULL DEFAULT 'all',
    seniority VARCHAR(50) NULL DEFAULT 'none',
    evaluation VARCHAR(50) NULL DEFAULT 'none',
    employment_status VARCHAR(50) NULL DEFAULT 'tetap',
    access_rights JSONB NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_employees_branch_id ON public.employees(branch_id);
CREATE INDEX IF NOT EXISTS idx_employees_code ON public.employees(code);
CREATE INDEX IF NOT EXISTS idx_employees_is_active ON public.employees(is_active);


-- ==============================================================================
-- BAGIAN 2: MASTER SHIFT KERJA
-- ==============================================================================

-- 3. Tabel Master Template Shift Kerja (shifts)
CREATE TABLE IF NOT EXISTS public.shifts (
    id BIGSERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    start_time TIME WITHOUT TIME ZONE NOT NULL,
    end_time TIME WITHOUT TIME ZONE NOT NULL,
    is_overnight BOOLEAN NOT NULL DEFAULT FALSE,
    grace_period_minutes INTEGER NOT NULL DEFAULT 15,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_shifts_code ON public.shifts(code);
CREATE INDEX IF NOT EXISTS idx_shifts_is_active ON public.shifts(is_active);


-- ==============================================================================
-- BAGIAN 3: CATATAN PRESENSI & ANTI-FRAUD
-- ==============================================================================

-- 4. Tabel Transaksi Rekaman Kehadiran (attendance_records)
CREATE TABLE IF NOT EXISTS public.attendance_records (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL REFERENCES public.employees(id) ON DELETE CASCADE,
    branch_id BIGINT NOT NULL REFERENCES public.branches(id) ON DELETE CASCADE,
    shift_id BIGINT NULL REFERENCES public.shifts(id) ON DELETE SET NULL,
    work_date DATE NOT NULL,

    -- Data Clock-In (Masuk)
    clock_in_at TIMESTAMP WITH TIME ZONE NULL,
    clock_in_lat NUMERIC(10, 8) NULL,
    clock_in_lng NUMERIC(11, 8) NULL,
    clock_in_distance_meters NUMERIC(8, 2) NULL,
    clock_in_photo_path VARCHAR(255) NULL,

    -- Data Clock-Out (Pulang)
    clock_out_at TIMESTAMP WITH TIME ZONE NULL,
    clock_out_lat NUMERIC(10, 8) NULL,
    clock_out_lng NUMERIC(11, 8) NULL,
    clock_out_distance_meters NUMERIC(8, 2) NULL,
    clock_out_photo_path VARCHAR(255) NULL,

    -- Status & Kalkulasi Jam Kerja
    status VARCHAR(30) NOT NULL DEFAULT 'present', -- present, late, absent, on_leave
    late_minutes INTEGER NOT NULL DEFAULT 0,
    early_leave_minutes INTEGER NOT NULL DEFAULT 0,
    overtime_minutes INTEGER NOT NULL DEFAULT 0,
    duration_minutes INTEGER NOT NULL DEFAULT 0,

    -- Anti-Fraud & Metode Verifikasi
    verification_method VARCHAR(30) NOT NULL DEFAULT 'gps_selfie', -- gps_selfie, web_kiosk, admin_manual
    device_info JSONB NULL,
    ip_address VARCHAR(45) NULL,
    is_mock_location_detected BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT NULL,

    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_attendance_emp_date_shift UNIQUE (employee_id, work_date, shift_id)
);

CREATE INDEX IF NOT EXISTS idx_attendance_work_date ON public.attendance_records(work_date);
CREATE INDEX IF NOT EXISTS idx_attendance_status ON public.attendance_records(status);
CREATE INDEX IF NOT EXISTS idx_attendance_employee_date ON public.attendance_records(employee_id, work_date);


-- ==============================================================================
-- BAGIAN 4: WORKFLOW PENGAJUAN (CUTI, LEMBUR, KOREKSI, TUKAR SHIFT)
-- ==============================================================================

-- 5. Tabel Pengajuan Cuti / Izin / Sakit (leave_requests)
CREATE TABLE IF NOT EXISTS public.leave_requests (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL REFERENCES public.employees(id) ON DELETE CASCADE,
    leave_type VARCHAR(30) NOT NULL, -- annual, sick, permission, emergency
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INTEGER NOT NULL DEFAULT 1,
    reason TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, approved, rejected
    approved_by BIGINT NULL REFERENCES public.employees(id) ON DELETE SET NULL,
    approved_at TIMESTAMP WITHOUT TIME ZONE NULL,
    rejection_note TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_leave_employee ON public.leave_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_status ON public.leave_requests(status);
CREATE INDEX IF NOT EXISTS idx_leave_dates ON public.leave_requests(start_date, end_date);

-- 6. Tabel Klaim Jam Kerja Lembur (overtime_claims)
CREATE TABLE IF NOT EXISTS public.overtime_claims (
    id BIGSERIAL PRIMARY KEY,
    attendance_record_id BIGINT NOT NULL REFERENCES public.attendance_records(id) ON DELETE CASCADE,
    employee_id BIGINT NOT NULL REFERENCES public.employees(id) ON DELETE CASCADE,
    requested_minutes INTEGER NOT NULL,
    approved_minutes INTEGER NULL,
    reason TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, approved, rejected
    approved_by BIGINT NULL REFERENCES public.employees(id) ON DELETE SET NULL,
    approved_at TIMESTAMP WITHOUT TIME ZONE NULL,
    rejection_note TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_overtime_employee ON public.overtime_claims(employee_id);
CREATE INDEX IF NOT EXISTS idx_overtime_status ON public.overtime_claims(status);

-- 7. Tabel Pengajuan Koreksi Presensi Mandiri (attendance_corrections)
CREATE TABLE IF NOT EXISTS public.attendance_corrections (
    id BIGSERIAL PRIMARY KEY,
    employee_id BIGINT NOT NULL REFERENCES public.employees(id) ON DELETE CASCADE,
    attendance_record_id BIGINT NULL REFERENCES public.attendance_records(id) ON DELETE SET NULL,
    shift_id BIGINT NULL REFERENCES public.shifts(id) ON DELETE SET NULL,
    work_date DATE NOT NULL,
    correction_type VARCHAR(30) NOT NULL DEFAULT 'both', -- both, clock_in, clock_out
    proposed_clock_in_at TIMESTAMP WITH TIME ZONE NULL,
    proposed_clock_out_at TIMESTAMP WITH TIME ZONE NULL,
    reason TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending', -- pending, approved, rejected
    approved_by BIGINT NULL REFERENCES public.employees(id) ON DELETE SET NULL,
    approved_at TIMESTAMP WITHOUT TIME ZONE NULL,
    rejection_note TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_corr_employee ON public.attendance_corrections(employee_id);
CREATE INDEX IF NOT EXISTS idx_corr_work_date ON public.attendance_corrections(work_date);
CREATE INDEX IF NOT EXISTS idx_corr_status ON public.attendance_corrections(status);

-- 8. Tabel Permohonan Tukar Shift Antar Karyawan Dua Arah (shift_swap_requests)
CREATE TABLE IF NOT EXISTS public.shift_swap_requests (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL REFERENCES public.branches(id) ON DELETE CASCADE,
    requester_id BIGINT NOT NULL REFERENCES public.employees(id) ON DELETE CASCADE,
    target_employee_id BIGINT NOT NULL REFERENCES public.employees(id) ON DELETE CASCADE,
    requester_shift_id BIGINT NOT NULL REFERENCES public.shifts(id) ON DELETE CASCADE,
    requester_date DATE NOT NULL,
    target_shift_id BIGINT NOT NULL REFERENCES public.shifts(id) ON DELETE CASCADE,
    target_date DATE NOT NULL,
    reason TEXT NOT NULL,
    
    -- Respon Tahap 1 (Rekan Kerja)
    peer_note TEXT NULL,
    peer_responded_at TIMESTAMP WITHOUT TIME ZONE NULL,
    
    -- Status Alur Dua Arah
    status VARCHAR(30) NOT NULL DEFAULT 'pending_peer', -- pending_peer, peer_rejected, pending_supervisor, approved, rejected, cancelled
    
    -- Verifikasi Tahap 2 (Supervisor)
    approved_by BIGINT NULL REFERENCES public.employees(id) ON DELETE SET NULL,
    approved_at TIMESTAMP WITHOUT TIME ZONE NULL,
    rejection_note TEXT NULL,
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_swap_requester ON public.shift_swap_requests(requester_id);
CREATE INDEX IF NOT EXISTS idx_swap_target ON public.shift_swap_requests(target_employee_id);
CREATE INDEX IF NOT EXISTS idx_swap_status ON public.shift_swap_requests(status);
CREATE INDEX IF NOT EXISTS idx_swap_dates ON public.shift_swap_requests(requester_date, target_date);

-- 9. Tabel Bagan Gambar Jadwal Shift Operator (shift_schedule_images)
CREATE TABLE IF NOT EXISTS public.shift_schedule_images (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL DEFAULT 'Jadwal Shift',
    image_path VARCHAR(255) NOT NULL,
    period VARCHAR(100) NULL,
    notes TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_sched_img_is_active ON public.shift_schedule_images(is_active);


-- ==============================================================================
-- BAGIAN 5: DATA AWAL MASTER & SEEDER (INITIAL SEED DATA)
-- ==============================================================================

-- A. Data Cabang MGK (Tunggal)
INSERT INTO public.branches (code, name, is_headquarters, address, latitude, longitude, radius_meters, phone, is_active)
VALUES 
    ('NET-PST', 'Maya Graha Kencana (MGK Pusat)', TRUE, 'Jl. Boulevard Raya Blok QJ No. 1, Jakarta Utara', -6.15850000, 106.90800000, 100, '021-45878899', TRUE)
ON CONFLICT (code) DO UPDATE SET
    latitude = EXCLUDED.latitude,
    longitude = EXCLUDED.longitude,
    radius_meters = EXCLUDED.radius_meters;

-- B. Data 5 Template Master Shift MGK (24 Jam & Back Office)
INSERT INTO public.shifts (code, name, start_time, end_time, is_overnight, grace_period_minutes, is_active)
VALUES 
    ('SHIFT-1', 'Shift 1 (Dini Hari)', '01:00:00', '07:00:00', FALSE, 15, TRUE),
    ('SHIFT-2', 'Shift 2 (Pagi)', '07:00:00', '13:00:00', FALSE, 15, TRUE),
    ('SHIFT-3', 'Shift 3 (Siang / Sore)', '13:00:00', '19:00:00', FALSE, 15, TRUE),
    ('SHIFT-4', 'Shift 4 (Malam - Lintas Hari)', '19:00:00', '01:00:00', TRUE, 15, TRUE),
    ('SHIFT-BO', 'Shift BO (Back Office / General)', '08:30:00', '17:30:00', FALSE, 15, TRUE)
ON CONFLICT (code) DO UPDATE SET
    name = EXCLUDED.name,
    start_time = EXCLUDED.start_time,
    end_time = EXCLUDED.end_time,
    is_overnight = EXCLUDED.is_overnight,
    grace_period_minutes = EXCLUDED.grace_period_minutes;

-- C. Data Karyawan Contoh (Supervisor & Operator)
INSERT INTO public.employees (branch_id, code, name, role, username, position, is_active)
VALUES 
    ((SELECT id FROM public.branches WHERE code = 'NET-PST' LIMIT 1), 'SPV-001', 'Supervisor Hendra', 'supervisor', 'spv_hendra', 'Supervisor', TRUE),
    ((SELECT id FROM public.branches WHERE code = 'NET-PST' LIMIT 1), 'OP-001', 'Operator Budi Santoso', 'operator', 'budi', 'Operator Kasir', TRUE),
    ((SELECT id FROM public.branches WHERE code = 'NET-PST' LIMIT 1), 'OP-002', 'Operator Dimas Pratama', 'operator', 'dimas', 'Operator Kasir', TRUE),
    ((SELECT id FROM public.branches WHERE code = 'NET-PST' LIMIT 1), 'OP-003', 'Operator Rian Hidayat', 'operator', 'rian', 'Operator Kasir', TRUE)
ON CONFLICT (code) DO NOTHING;

-- Selesai
