<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Dilempar saat terdeteksi penggunaan aplikasi Fake GPS / Mock Location Provider.
 */
class MockLocationDetectedException extends DomainException
{
    public function __construct(string $message = 'Terdeteksi manipulasi lokasi (Mock Location / Fake GPS). Presensi ditolak.')
    {
        parent::__construct($message, 403);
    }
}
