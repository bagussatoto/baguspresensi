<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Enums;

/**
 * Status Kehadiran Karyawan pada Hari/Shift Operasional.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case HalfDay = 'half_day';
    case OnLeave = 'on_leave';

    /**
     * Dapatkan label human-readable dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir Tepat Waktu',
            self::Late => 'Terlambat',
            self::Absent => 'Alpa / Tidak Hadir',
            self::HalfDay => 'Setengah Hari',
            self::OnLeave => 'Cuti / Izin',
        };
    }

    /**
     * Dapatkan class badge untuk tampilan UI.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::Late => 'bg-amber-100 text-amber-800 border-amber-300',
            self::Absent => 'bg-rose-100 text-rose-800 border-rose-300',
            self::HalfDay => 'bg-blue-100 text-blue-800 border-blue-300',
            self::OnLeave => 'bg-purple-100 text-purple-800 border-purple-300',
        };
    }
}
