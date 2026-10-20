<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\MasterData\Domain\Models\Employee;

class SampleAttendanceHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::where('is_active', true)->get();
        if ($employees->isEmpty()) {
            return;
        }

        $shiftBO = Shift::where('code', 'SHIFT-BO')->first() ?? Shift::first();
        $shift2 = Shift::where('code', 'SHIFT-2')->first() ?? Shift::first();
        $shift3 = Shift::where('code', 'SHIFT-3')->first() ?? Shift::first();

        $today = Carbon::today();
        $startOfMonth = Carbon::create($today->year, $today->month, 1);

        foreach ($employees as $employee) {
            $shift = ($employee->id % 2 === 0) ? $shiftBO : $shift2;

            for ($date = $startOfMonth->copy(); $date->lte($today); $date->addDay()) {
                if ($date->isSunday()) {
                    continue; // Libur hari Minggu
                }

                $workDate = $date->toDateString();

                // Cek apakah sudah ada rekaman pada tanggal tersebut
                if (AttendanceRecord::where('employee_id', $employee->id)->where('work_date', $workDate)->exists()) {
                    continue;
                }

                $dayNumber = $date->day;
                $isLate = ($dayNumber % 5 === 0); // Setiap kelipatan 5 hari ada yang telat
                $isOvertime = ($dayNumber % 4 === 0 && ! $isLate); // Hari lembur

                if ($shift->code === 'SHIFT-BO') {
                    // Jam normal 08:30 - 17:30
                    if ($isLate) {
                        $clockInTime = '08:48:20';
                        $status = AttendanceStatus::Late;
                        $lateMinutes = 18;
                        $overtimeMinutes = 0;
                        $durationMinutes = 524;
                    } elseif ($isOvertime) {
                        $clockInTime = '08:22:15';
                        $status = AttendanceStatus::Present;
                        $lateMinutes = 0;
                        $overtimeMinutes = 120; // Lembur 2 jam
                        $durationMinutes = 665;
                    } else {
                        $clockInTime = '08:24:10';
                        $status = AttendanceStatus::Present;
                        $lateMinutes = 0;
                        $overtimeMinutes = 0;
                        $durationMinutes = 547;
                    }

                    $clockOutTime = $isOvertime ? '19:35:10' : '17:31:45';
                } else {
                    // Shift 2 (07:00 - 13:00)
                    if ($isLate) {
                        $clockInTime = '07:21:40';
                        $status = AttendanceStatus::Late;
                        $lateMinutes = 21;
                        $overtimeMinutes = 0;
                        $durationMinutes = 345;
                    } elseif ($isOvertime) {
                        $clockInTime = '06:53:10';
                        $status = AttendanceStatus::Present;
                        $lateMinutes = 0;
                        $overtimeMinutes = 60; // Lembur 1 jam
                        $durationMinutes = 430;
                    } else {
                        $clockInTime = '06:50:30';
                        $status = AttendanceStatus::Present;
                        $lateMinutes = 0;
                        $overtimeMinutes = 0;
                        $durationMinutes = 372;
                    }

                    $clockOutTime = $isOvertime ? '14:05:00' : '13:02:40';
                }

                AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id ?? 1,
                    'shift_id' => $shift->id,
                    'work_date' => $workDate,
                    'clock_in_at' => "{$workDate} {$clockInTime}",
                    'clock_in_lat' => -6.158520,
                    'clock_in_lng' => 106.908050,
                    'clock_in_distance_meters' => 15.50,
                    'clock_in_photo_path' => null,
                    'clock_out_at' => "{$workDate} {$clockOutTime}",
                    'clock_out_lat' => -6.158510,
                    'clock_out_lng' => 106.908040,
                    'clock_out_distance_meters' => 12.00,
                    'clock_out_photo_path' => null,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => 0,
                    'overtime_minutes' => $overtimeMinutes,
                    'duration_minutes' => $durationMinutes,
                    'verification_method' => VerificationMethod::GpsSelfie,
                    'is_mock_location_detected' => false,
                    'notes' => 'Presensi reguler via Portal Web Karyawan',
                ]);
            }
        }
    }
}
