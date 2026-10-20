<?php

declare(strict_types=1);

namespace App\Domains\Attendance\DataTransferObjects;

use Carbon\CarbonInterface;

/**
 * Data Transfer Object untuk payload presensi pulang (Clock Out).
 */
readonly class ClockOutDTO
{
    public function __construct(
        public int $employeeId,
        public float $latitude,
        public float $longitude,
        public ?int $branchId = null,
        public ?int $shiftId = null,
        public ?string $photoPath = null,
        public ?array $deviceInfo = null,
        public ?string $ipAddress = null,
        public bool $isMockLocation = false,
        public ?string $notes = null,
        public ?CarbonInterface $clockOutAt = null,
        public bool $forceEarlyLeave = false,
    ) {}
}
