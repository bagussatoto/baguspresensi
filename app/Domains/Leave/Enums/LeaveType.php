<?php

declare(strict_types=1);

namespace App\Domains\Leave\Enums;

/**
 * Jenis Permohonan Izin / Cuti Karyawan.
 */
enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Permission = 'permission';
    case Emergency = 'emergency';

    /**
     * Dapatkan label human-readable dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Cuti Tahunan',
            self::Sick => 'Izin Sakit',
            self::Permission => 'Izin Keperluan Pribadi',
            self::Emergency => 'Izin Darurat / Kemalangan',
        };
    }
}
