<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Exceptions;

use DomainException;

/**
 * Dilempar saat koordinat GPS berada di luar radius cabang yang ditentukan.
 */
class OutsideGeofenceException extends DomainException
{
    public function __construct(
        public readonly float $distanceMeters,
        public readonly float $allowedRadiusMeters,
        public readonly string $branchName,
        string $message = ''
    ) {
        $formattedDistance = number_format($this->distanceMeters, 1);
        $formattedRadius = number_format($this->allowedRadiusMeters, 1);

        $defaultMessage = "Lokasi Anda berada di luar radius kantor {$this->branchName}. ".
            "Jarak terdeteksi: {$formattedDistance} meter (Maksimal: {$formattedRadius} meter).";

        parent::__construct($message !== '' ? $message : $defaultMessage, 422);
    }
}
