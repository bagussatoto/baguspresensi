<?php

declare(strict_types=1);

namespace App\Domains\Attendance\DataTransferObjects;

use App\Domains\Attendance\Enums\VerificationMethod;
use Carbon\CarbonInterface;

/**
 * Data Transfer Object untuk payload presensi masuk (Clock In).
 */
readonly class ClockInDTO
{
    public function __construct(
        public int $employeeId,
        public int $branchId,
        public float $latitude,
        public float $longitude,
        public ?int $shiftId = null,
        public ?string $photoPath = null,
        public VerificationMethod $verificationMethod = VerificationMethod::GpsSelfie,
        public ?array $deviceInfo = null,
        public ?string $ipAddress = null,
        public bool $isMockLocation = false,
        public ?string $notes = null,
        public ?CarbonInterface $clockInAt = null,
        public ?string $workType = null,
        public ?string $wifiBssid = null,
        public ?float $livenessScore = null,
    ) {}
}
