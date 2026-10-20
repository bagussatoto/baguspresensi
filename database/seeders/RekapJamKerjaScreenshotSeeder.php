<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Commission;
use Modules\MasterData\Domain\Models\Employee;

class RekapJamKerjaScreenshotSeeder extends Seeder
{
    /**
     * Seed attendance and commission data matching the user's Rekap Jam Kerja table.
     */
    public function run(): void
    {
        $branch = Branch::first();
        if (! $branch) {
            $branch = Branch::create([
                'name' => 'Maya Graha Kencana',
                'code' => 'NET-PST',
                'is_headquarters' => true,
                'address' => 'Jl. Boulevard Kelapa Gading Blok M No. 18, Jakarta Utara',
                'phone' => '021-45879900',
                'is_active' => true,
            ]);
        }

        $shift1 = Shift::where('code', 'SHIFT-1')->first();
        $shift2 = Shift::where('code', 'SHIFT-2')->first();
        $shift3 = Shift::where('code', 'SHIFT-3')->first();
        $shift4 = Shift::where('code', 'SHIFT-4')->first();
        $shiftBO = Shift::where('code', 'SHIFT-BO')->first();

        if (! $shift1 || ! $shift2 || ! $shift3 || ! $shift4 || ! $shiftBO) {
            return;
        }

        // Definisi operator sesuai screenshot
        $operators = [
            'Aan' => [
                'code' => 'EMP-007',
                'username' => 'aan',
                'shift_1' => 6,
                'shift_2' => 0,
                'shift_3' => 3,
                'shift_4' => 35,
                'bo' => 20,
                'mepet' => 1,
                'telat' => 1,
                'komisi' => 2400,
            ],
            'Andra' => [
                'code' => 'EMP-008',
                'username' => 'andra',
                'shift_1' => 64,
                'shift_2' => 10,
                'shift_3' => 6,
                'shift_4' => 18,
                'bo' => 8,
                'mepet' => 1,
                'telat' => 0,
                'komisi' => 7800,
            ],
            'Bagus Satoto' => [
                'code' => 'EMP-BGS',
                'username' => 'bagus',
                'shift_1' => 7,
                'shift_2' => 35,
                'shift_3' => 24,
                'shift_4' => 14,
                'bo' => 44,
                'mepet' => 3,
                'telat' => 3,
                'komisi' => 1600,
            ],
            'Grace' => [
                'code' => 'EMP-009',
                'username' => 'grace',
                'shift_1' => 10,
                'shift_2' => 35,
                'shift_3' => 12,
                'shift_4' => 11,
                'bo' => 58,
                'mepet' => 2,
                'telat' => 4,
                'komisi' => 0,
            ],
            'Henbediona' => [
                'code' => 'EMP-010',
                'username' => 'henbediona',
                'shift_1' => 0,
                'shift_2' => 10,
                'shift_3' => 15,
                'shift_4' => 15,
                'bo' => 0,
                'mepet' => 0,
                'telat' => 5,
                'komisi' => 4800,
            ],
            'Ridwan' => [
                'code' => 'EMP-011',
                'username' => 'ridwan',
                'shift_1' => 48,
                'shift_2' => 1,
                'shift_3' => 9,
                'shift_4' => 28,
                'bo' => 0,
                'mepet' => 1,
                'telat' => 7,
                'komisi' => 4400,
            ],
            'Vio' => [
                'code' => 'EMP-VIO',
                'username' => 'vio',
                'shift_1' => 6,
                'shift_2' => 20,
                'shift_3' => 54,
                'shift_4' => 12,
                'bo' => 0,
                'mepet' => 4,
                'telat' => 4,
                'komisi' => 2800,
            ],
            'Yulika' => [
                'code' => 'EMP-YLK',
                'username' => 'yulika',
                'shift_1' => 3,
                'shift_2' => 33,
                'shift_3' => 12,
                'shift_4' => 5,
                'bo' => 71,
                'mepet' => 0,
                'telat' => 5,
                'komisi' => 3200,
            ],
        ];

        // Pastikan Bagus dan Yulk terupdate
        $existingBagus = Employee::where('name', 'Bagus')->first();
        if ($existingBagus) {
            $existingBagus->update(['name' => 'Bagus Satoto']);
        }
        $existingYulk = Employee::where('name', 'Yulk')->first();
        if ($existingYulk) {
            $existingYulk->update(['name' => 'Yulika']);
        }

        $rekapYear = 2026;
        $rekapMonth = 10;

        foreach ($operators as $name => $spec) {
            $emp = Employee::where('name', $name)->first();
            if (! $emp) {
                $emp = Employee::create([
                    'name' => $name,
                    'code' => $spec['code'],
                    'username' => $spec['username'],
                    'branch_id' => $branch->id,
                    'position' => 'Operator',
                    'is_active' => true,
                    'password' => bcrypt('password123'),
                    'pin' => '123456',
                ]);
            }

            // Hapus rekaman lama khusus bulan Oktober 2026 agar konsisten
            AttendanceRecord::where('employee_id', $emp->id)
                ->whereYear('work_date', $rekapYear)
                ->whereMonth('work_date', $rekapMonth)
                ->delete();

            // Masukkan sesi untuk shift 1
            $this->seedShiftRecords($emp->id, $branch->id, $shift1->id, $spec['shift_1'], '01:00', $rekapYear, $rekapMonth, 1);
            // Shift 2
            $this->seedShiftRecords($emp->id, $branch->id, $shift2->id, $spec['shift_2'], '07:00', $rekapYear, $rekapMonth, 7);
            // Shift 3
            $this->seedShiftRecords($emp->id, $branch->id, $shift3->id, $spec['shift_3'], '13:00', $rekapYear, $rekapMonth, 14);
            // Shift 4
            $this->seedShiftRecords($emp->id, $branch->id, $shift4->id, $spec['shift_4'], '19:00', $rekapYear, $rekapMonth, 21);
            // Shift BO
            $this->seedShiftRecords($emp->id, $branch->id, $shiftBO->id, $spec['bo'], '08:30', $rekapYear, $rekapMonth, 2);

            // Terapkan jumlah mepet dan telat yang tepat pada record yang ada
            $allEmpRecs = AttendanceRecord::where('employee_id', $emp->id)
                ->whereYear('work_date', $rekapYear)
                ->whereMonth('work_date', $rekapMonth)
                ->orderBy('id')
                ->get();

            $targetMepet = $spec['mepet'];
            $targetTelat = $spec['telat'];

            foreach ($allEmpRecs as $idx => $rec) {
                if ($targetTelat > 0) {
                    $rec->update([
                        'status' => AttendanceStatus::Late,
                        'late_minutes' => 25,
                    ]);
                    $targetTelat--;
                } elseif ($targetMepet > 0) {
                    $rec->update([
                        'status' => AttendanceStatus::Present,
                        'late_minutes' => 10,
                    ]);
                    $targetMepet--;
                } else {
                    $rec->update([
                        'status' => AttendanceStatus::Present,
                        'late_minutes' => 0,
                    ]);
                }
            }

            // Simpan Komisi
            Commission::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'period_month' => $rekapMonth,
                    'period_year' => $rekapYear,
                ],
                [
                    'total_shift_hours' => $spec['shift_1'] + $spec['shift_2'] + $spec['shift_3'] + $spec['shift_4'],
                    'total_commission' => $spec['komisi'],
                    'status' => 'approved',
                ]
            );
        }
    }

    private function seedShiftRecords(int $employeeId, int $branchId, int $shiftId, int $totalHours, string $startTime, int $year, int $month, int $startDay): void
    {
        if ($totalHours <= 0) {
            return;
        }

        $remainingHours = $totalHours;
        $day = $startDay;

        while ($remainingHours > 0) {
            $dayInMonth = min(31, max(1, ($day % 31) + 1));
            $durHours = min(6, $remainingHours);
            $durMinutes = $durHours * 60;
            $workDate = sprintf('%04d-%02d-%02d', $year, $month, $dayInMonth);

            $clockInTime = Carbon::parse("{$workDate} {$startTime}:00");
            $clockOutTime = $clockInTime->copy()->addMinutes($durMinutes);

            AttendanceRecord::create([
                'employee_id' => $employeeId,
                'branch_id' => $branchId,
                'shift_id' => $shiftId,
                'work_date' => $workDate,
                'clock_in_at' => $clockInTime,
                'clock_out_at' => $clockOutTime,
                'duration_minutes' => $durMinutes,
                'status' => AttendanceStatus::Present,
                'late_minutes' => 0,
                'verification_method' => 'admin_manual',
                'work_type' => 'shift',
                'effective_minutes' => $durMinutes,
                'notes' => 'Presensi shift operasional bulanan',
            ]);

            $remainingHours -= $durHours;
            $day++;
        }
    }
}
