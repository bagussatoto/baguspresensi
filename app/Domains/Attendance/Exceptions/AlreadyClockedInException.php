<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Dilempar saat karyawan mencoba Clock-In lebih dari sekali pada shift & hari kerja yang sama.
 */
class AlreadyClockedInException extends DomainException
{
    public function __construct(string $message = 'Karyawan sudah melakukan presensi masuk (clock-in) untuk shift ini.')
    {
        parent::__construct($message, 409);
    }
}
