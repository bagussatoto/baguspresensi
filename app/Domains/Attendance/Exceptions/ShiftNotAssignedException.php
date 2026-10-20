<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Dilempar saat karyawan tidak terdaftar atau tidak ditemukan jadwal shift yang sesuai.
 */
class ShiftNotAssignedException extends DomainException
{
    public function __construct(string $message = 'Jadwal shift aktif tidak ditemukan untuk waktu operasional saat ini.')
    {
        parent::__construct($message, 404);
    }
}
