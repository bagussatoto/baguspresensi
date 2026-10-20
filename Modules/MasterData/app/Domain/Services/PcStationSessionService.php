<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

use Modules\MasterData\Domain\Models\PcStation;

/**
 * Domain Service: Menangani life cycle sesi billing unit PC warnet.
 */
class PcStationSessionService
{
    /**
     * Memulai sesi sewa billing PC.
     */
    public function startSession(PcStation $station, string $userName, int $durationMinutes): PcStation
    {
        $billingAmount = round(((float) $station->hourly_rate / 60) * $durationMinutes, 2);

        $station->update([
            'status' => 'occupied',
            'current_user_name' => $userName,
            'started_at' => now(),
            'duration_minutes' => $durationMinutes,
            'billing_amount' => $billingAmount,
        ]);

        return $station;
    }

    /**
     * Menyelesaikan atau menghentikan sesi PC (Stop).
     */
    public function stopSession(PcStation $station): PcStation
    {
        $station->update([
            'status' => 'available',
            'current_user_name' => null,
            'started_at' => null,
            'duration_minutes' => 0,
            'billing_amount' => 0.0,
        ]);

        return $station;
    }

    /**
     * Menyetel status perbaikan / maintenance pada PC Station.
     */
    public function setMaintenance(PcStation $station): PcStation
    {
        $station->update([
            'status' => 'maintenance',
            'current_user_name' => null,
            'started_at' => null,
            'duration_minutes' => 0,
            'billing_amount' => 0.0,
        ]);

        return $station;
    }
}
