<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Dilempar saat nilai latitude atau longitude tidak valid.
 */
class InvalidCoordinateException extends DomainException
{
    public function __construct(string $message = 'Koordinat geografis tidak valid.')
    {
        parent::__construct($message);
    }
}
