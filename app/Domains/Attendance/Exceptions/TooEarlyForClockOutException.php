<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Exception: Dilemparkan saat karyawan mode fleksibel mencoba clock-out sebelum memenuhi kuota kontrak jam kerja harian
 * tanpa persetujuan izin pulang awal dari supervisor.
 */
class TooEarlyForClockOutException extends DomainException
{
    public function __construct(
        string $message = 'Jam kerja belum memenuhi batas minimal kontrak harian. Silakan ajukan izin pulang awal terlebih dahulu.'
    ) {
        parent::__construct($message);
    }
}
