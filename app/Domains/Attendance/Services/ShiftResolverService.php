<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Exceptions\ShiftNotAssignedException;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Service untuk menangani pencocokan shift operasional warnet & kantor,
 * termasuk kalkulasi shift lintas hari (overnight shift 4).
 */
class ShiftResolverService
{
    /**
     * Resolusi shift aktif berdasarkan waktu saat ini dan/atau pilihan shift_id.
     */
    public function resolveShift(?int $preferredShiftId = null, ?CarbonInterface $currentTime = null): Shift
    {
        $time = $currentTime ? Carbon::instance($currentTime) : Carbon::now();

        if ($preferredShiftId !== null) {
            $shift = Shift::where('id', $preferredShiftId)
                ->where('is_active', true)
                ->first();

            if ($shift !== null) {
                return $shift;
            }
        }

        $shifts = Shift::where('is_active', true)->orderBy('start_time', 'asc')->get();
        if ($shifts->isEmpty()) {
            throw new ShiftNotAssignedException('Tidak ditemukan master shift aktif di sistem.');
        }

        $timeString = $time->format('H:i:s');

        // 1. Prioritas Utama: Cari shift yang jam operasionalnya sedang aktif saat ini [start_time, end_time)
        // Utamakan shift operasional utama (Shift 1, 2, 3, 4) dibandingkan shift Back Office jika jam beririsan
        $activeNonBoShift = null;
        $activeBoShift = null;

        foreach ($shifts as $shift) {
            if ($this->isCurrentTimeInShift($shift, $timeString)) {
                $isBo = str_contains(strtoupper((string) $shift->code), 'BO') ||
                        str_contains(strtoupper((string) $shift->name), 'BACK OFFICE');

                if (! $isBo) {
                    $activeNonBoShift ??= $shift;
                } else {
                    $activeBoShift ??= $shift;
                }
            }
        }

        if ($activeNonBoShift !== null) {
            return $activeNonBoShift;
        }

        if ($activeBoShift !== null) {
            return $activeBoShift;
        }

        // 2. Prioritas Kedua: Jendela early check-in (30 menit sebelum shift mulai)
        foreach ($shifts as $shift) {
            if ($this->isEarlyCheckInWindow($shift, $time)) {
                return $shift;
            }
        }

        // 3. Fallback toleransi jendela waktu
        foreach ($shifts as $shift) {
            if ($this->isTimeWithinShiftWindow($shift, $timeString)) {
                return $shift;
            }
        }

        // 4. Fallback shift pertama
        return $shifts->first();
    }

    /**
     * Tentukan work_date (tanggal dinas kerja) yang tepat.
     * Khusus shift lintas hari (overnight shift), scan setelah tengah malam (00:00-06:00)
     * diikat ke tanggal hari sebelumnya.
     */
    public function determineWorkDate(Shift $shift, ?CarbonInterface $time = null): Carbon
    {
        $scanTime = $time ? Carbon::instance($time) : Carbon::now();

        if ($shift->is_overnight) {
            $timeString = $scanTime->format('H:i:s');
            // Jika scan dilakukan antara 00:00 dan jam mulai shift (misal 19:00), maka itu milik dinas kemarin
            if ($timeString < $shift->start_time) {
                return $scanTime->copy()->subDay()->startOfDay();
            }
        }

        return $scanTime->copy()->startOfDay();
    }

    /**
     * Dapatkan timestamp Carbon penuh untuk jam mulai shift pada work_date tertentu.
     */
    public function getShiftStartTimestamp(Shift $shift, CarbonInterface $workDate): Carbon
    {
        return Carbon::parse($workDate->toDateString().' '.$shift->start_time);
    }

    /**
     * Dapatkan timestamp Carbon penuh untuk jam selesai shift pada work_date tertentu.
     * Untuk shift lintas hari, jam selesai ditambahkan 1 hari.
     */
    public function getShiftEndTimestamp(Shift $shift, CarbonInterface $workDate): Carbon
    {
        $end = Carbon::parse($workDate->toDateString().' '.$shift->end_time);

        if ($shift->is_overnight) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * Cek apakah waktu tertentu tepat berada dalam rentang jam operasional shift [start_time, end_time).
     */
    public function isCurrentTimeInShift(Shift $shift, string $timeString): bool
    {
        if ($shift->is_overnight) {
            // Shift lintas hari, misal 19:00:00 s/d 01:00:00
            // Aktif jika jam >= 19:00 ATAU jam < 01:00
            return $timeString >= $shift->start_time || $timeString < $shift->end_time;
        }

        // Shift normal satu hari, misal 01:00 - 07:00, 07:00 - 13:00, 13:00 - 19:00
        if ($shift->start_time < $shift->end_time) {
            return $timeString >= $shift->start_time && $timeString < $shift->end_time;
        }

        return true;
    }

    /**
     * Cek apakah waktu saat ini berada di rentang early check-in (30 menit sebelum jam mulai).
     */
    public function isEarlyCheckInWindow(Shift $shift, CarbonInterface $time): bool
    {
        $windowStart = Carbon::parse($shift->start_time)->subMinutes(30)->format('H:i:s');
        $timeString = $time->format('H:i:s');

        if ($windowStart <= $shift->start_time) {
            return $timeString >= $windowStart && $timeString < $shift->start_time;
        }

        // Jika start_time 00:15 lalu subMinutes(30) menjadi 23:45
        return $timeString >= $windowStart || $timeString < $shift->start_time;
    }

    /**
     * Cek apakah waktu tertentu masuk ke toleransi jendela waktu shift.
     */
    public function isTimeWithinShiftWindow(Shift $shift, string $timeString): bool
    {
        if ($shift->is_overnight) {
            return ($timeString >= '18:30:00' && $timeString <= '23:59:59') ||
                   ($timeString >= '00:00:00' && $timeString <= '01:30:00');
        }

        $startWindow = Carbon::parse($shift->start_time)->subMinutes(30)->format('H:i:s');
        $endWindow = Carbon::parse($shift->end_time)->addMinutes(30)->format('H:i:s');

        if ($startWindow <= $endWindow) {
            return $timeString >= $startWindow && $timeString <= $endWindow;
        }

        return $timeString >= $startWindow || $timeString <= $endWindow;
    }
}
