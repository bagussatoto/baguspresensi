<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Dilempar saat karyawan mencoba Clock-Out namun belum melakukan Clock-In aktif.
 */
class NotClockedInException extends DomainException
{
    public function __construct(string $message = 'Belum ditemukan catatan presensi masuk (clock-in) aktif untuk karyawan ini.')
    {
        parent::__construct($message, 400);
    }
}
