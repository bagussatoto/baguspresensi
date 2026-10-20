<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use App\Domains\Attendance\Models\RosterJadwal;
use Carbon\CarbonInterface;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Service: Menentukan Jadwal Roster ERP Karyawan.
 *
 * Mengakomodasi pemisahan 3 atom: Waktu, Konteks Roster, dan Alokasi Biaya (Cost Center).
 * Membaca penugasan harian di roster_jadwals dengan auto-fallback ke profil default karyawan.
 */
class RosterResolverService
{
    /**
     * Resolusi konteks roster dan alokasi cost center untuk karyawan pada tanggal tertentu.
     *
     * @return array{
     *     work_type: string,
     *     shift_id: ?int,
     *     contract_hours: int,
     *     cost_center: string,
     *     roster_id: ?int,
     *     roster: ?RosterJadwal
     * }
     */
    public function resolve(
        Employee $employee,
        CarbonInterface $workDate,
        ?string $preferredWorkType = null
    ): array {
        $dateString = $workDate->toDateString();

        $query = RosterJadwal::where('employee_id', $employee->id)
            ->whereDate('work_date', $dateString);

        if ($preferredWorkType !== null && $preferredWorkType !== '') {
            $query->where('work_type', $preferredWorkType);
        }

        /** @var RosterJadwal|null $roster */
        $roster = $query->first();

        if ($roster !== null) {
            return [
                'work_type' => $roster->work_type,
                'shift_id' => $roster->shift_id,
                'contract_hours' => $roster->contract_hours,
                'cost_center' => $roster->cost_center,
                'roster_id' => $roster->id,
                'roster' => $roster,
            ];
        }

        // Auto-fallback ke preferensi profil karyawan jika belum dibuatkan roster khusus
        $workType = $preferredWorkType ?? $employee->default_work_type ?? 'shift';
        $contractHours = (int) ($employee->default_contract_hours ?? 8);

        // Cost Center default: 5-1001 (Direct Labor COGS) untuk Shift, 5-1002 (OPEX) untuk Desain/Fleksibel
        $defaultCostCenter = $workType === 'flexible' ? '5-1002' : '5-1001';
        $costCenter = ($workType === ($employee->default_work_type ?? 'shift') && $employee->default_cost_center)
            ? $employee->default_cost_center
            : $defaultCostCenter;

        return [
            'work_type' => $workType,
            'shift_id' => null,
            'contract_hours' => $contractHours,
            'cost_center' => $costCenter,
            'roster_id' => null,
            'roster' => null,
        ];
    }
}
