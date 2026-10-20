<?php

namespace Database\Seeders;

use App\Domains\Employee\Models\Shift;
use Illuminate\Database\Seeder;
use Modules\MasterData\Domain\Models\Branch;

class AttendanceMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Master Template Shifts Maya Graha Kencana
        $shifts = [
            [
                'code' => 'SHIFT-1',
                'name' => 'Shift 1 (Dini Hari)',
                'start_time' => '01:00:00',
                'end_time' => '07:00:00',
                'is_overnight' => false,
                'grace_period_minutes' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-2',
                'name' => 'Shift 2 (Pagi)',
                'start_time' => '07:00:00',
                'end_time' => '13:00:00',
                'is_overnight' => false,
                'grace_period_minutes' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-3',
                'name' => 'Shift 3 (Siang / Sore)',
                'start_time' => '13:00:00',
                'end_time' => '19:00:00',
                'is_overnight' => false,
                'grace_period_minutes' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-4',
                'name' => 'Shift 4 (Malam - Lintas Hari)',
                'start_time' => '19:00:00',
                'end_time' => '01:00:00',
                'is_overnight' => true,
                'grace_period_minutes' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'SHIFT-BO',
                'name' => 'Shift BO (Back Office / General)',
                'start_time' => '08:30:00',
                'end_time' => '17:30:00',
                'is_overnight' => false,
                'grace_period_minutes' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shiftData) {
            Shift::updateOrCreate(
                ['code' => $shiftData['code']],
                $shiftData
            );
        }

        // 2. Set Default Geofencing Coordinates untuk Cabang
        $branchMGK = Branch::where('code', 'NET-PST')->first();
        if ($branchMGK) {
            $branchMGK->update([
                'latitude' => -6.15850000,
                'longitude' => 106.90800000,
                'radius_meters' => 100,
            ]);
        }
    }
}
