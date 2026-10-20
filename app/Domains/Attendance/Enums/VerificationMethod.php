<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Enums;

/**
 * Metode Verifikasi Presensi Karyawan.
 */
enum VerificationMethod: string
{
    case GpsSelfie = 'gps_selfie';
    case Biometric = 'biometric';
    case AdminManual = 'admin_manual';
    case WebKiosk = 'web_kiosk';

    /**
     * Dapatkan label human-readable dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::GpsSelfie => 'GPS + Foto Selfie',
            self::Biometric => 'Biometrik / Fingerprint',
            self::AdminManual => 'Manual oleh Admin/Supervisor',
            self::WebKiosk => 'Terminal POS / Web Kiosk',
        };
    }
}
