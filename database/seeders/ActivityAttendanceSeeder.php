<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Attendance\Models\ActivityAttendance;
use Illuminate\Database\Seeder;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

class ActivityAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();
        $employees = Employee::where('is_active', true)->get();

        if ($employees->isEmpty()) {
            return;
        }

        $activities = [
            [
                'activity_type' => 'meeting',
                'title' => 'Rapat Evaluasi Operasional Shift & Omset Mingguan',
                'activity_date' => '2026-10-05',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'duration_minutes' => 120,
                'status' => 'hadir',
                'notes' => 'Evaluasi kepatuhan serah terima kasir dan penanganan keluhan pelanggan.',
            ],
            [
                'activity_type' => 'pelatihan',
                'title' => 'Pelatihan SOP Pelayanan Kasir & Pemeriksaan Stok ERP',
                'activity_date' => '2026-10-10',
                'start_time' => '13:30:00',
                'end_time' => '16:00:00',
                'duration_minutes' => 150,
                'status' => 'hadir',
                'notes' => 'Pelatihan modul inventory pos kasir dan audit fisik stok barang.',
            ],
            [
                'activity_type' => 'gc',
                'title' => 'General Cleaning (GC) Perangkat PC Gaming & Sanitasi Outlet',
                'activity_date' => '2026-10-14',
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
                'duration_minutes' => 180,
                'status' => 'hadir',
                'notes' => 'Pembersihan debu motherboard, pasta prosesor, dan pembersihan lantai outlet.',
            ],
            [
                'activity_type' => 'meeting',
                'title' => 'Briefing Koordinasi Jadwal Shift & Sosialisasi Aturan Baru',
                'activity_date' => '2026-10-17',
                'start_time' => '10:00:00',
                'end_time' => '11:30:00',
                'duration_minutes' => 90,
                'status' => 'hadir',
                'notes' => 'Penyesuaian shift malam dan alur tukar shift darurat.',
            ],
        ];

        foreach ($activities as $act) {
            foreach ($employees->take(3) as $emp) {
                ActivityAttendance::updateOrCreate(
                    [
                        'activity_type' => $act['activity_type'],
                        'title' => $act['title'],
                        'employee_id' => $emp->id,
                        'activity_date' => $act['activity_date'],
                    ],
                    [
                        'branch_id' => $emp->branch_id ?? $branch?->id,
                        'start_time' => $act['start_time'],
                        'end_time' => $act['end_time'],
                        'duration_minutes' => $act['duration_minutes'],
                        'status' => $act['status'],
                        'notes' => $act['notes'],
                    ]
                );
            }
        }
    }
}
