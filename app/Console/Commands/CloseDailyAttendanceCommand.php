<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Leave\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\MasterData\Domain\Models\Employee;

class CloseDailyAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:close-daily {--date= : Tanggal operasional (format YYYY-MM-DD, default hari ini)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup presensi operasional harian dan menandai karyawan yang tidak hadir sebagai Alpa (atau Cuti/Izin bila telah disetujui)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateInput = $this->option('date');
        $targetDate = $dateInput ? Carbon::parse($dateInput)->toDateString() : Carbon::today()->toDateString();

        $this->info("Memproses penutupan presensi operasional untuk tanggal: {$targetDate}");

        $employees = Employee::with('branch')->where('is_active', true)->get();
        if ($employees->isEmpty()) {
            $this->warn('Tidak ada data karyawan aktif.');

            return self::SUCCESS;
        }

        // Ambil data record presensi yang sudah ada pada tanggal tersebut
        $existingRecords = AttendanceRecord::whereDate('work_date', $targetDate)
            ->get()
            ->keyBy('employee_id');

        // Ambil cuti/izin yang disetujui yang mencakup tanggal tersebut
        $approvedLeaves = LeaveRequest::where('status', ApprovalStatus::Approved->value)
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->get()
            ->keyBy('employee_id');

        $alreadyPresentCount = 0;
        $markedLeaveCount = 0;
        $markedAbsentCount = 0;

        foreach ($employees as $employee) {
            if ($existingRecords->has($employee->id)) {
                $alreadyPresentCount++;

                continue;
            }

            // Jika ada pengajuan cuti yang disetujui untuk tanggal ini
            if ($approvedLeaves->has($employee->id)) {
                /** @var LeaveRequest $leave */
                $leave = $approvedLeaves->get($employee->id);
                AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'branch_id' => $employee->branch_id ?? 1,
                    'shift_id' => null,
                    'work_date' => $targetDate,
                    'status' => AttendanceStatus::OnLeave,
                    'verification_method' => VerificationMethod::AdminManual,
                    'notes' => 'Tutup harian sistem: Cuti/izin disetujui ('.$leave->leave_type->label().').',
                ]);
                $markedLeaveCount++;

                continue;
            }

            // Karyawan tidak presensi dan tidak memiliki izin/cuti -> Tandai Alpa
            AttendanceRecord::create([
                'employee_id' => $employee->id,
                'branch_id' => $employee->branch_id ?? 1,
                'shift_id' => null,
                'work_date' => $targetDate,
                'status' => AttendanceStatus::Absent,
                'verification_method' => VerificationMethod::AdminManual,
                'notes' => 'Tutup harian otomatis: Karyawan tidak melakukan presensi (Alpa).',
            ]);
            $markedAbsentCount++;
        }

        $this->table(
            ['Metrik', 'Jumlah Karyawan'],
            [
                ['Total Karyawan Aktif', $employees->count()],
                ['Sudah Memiliki Catatan Presensi', $alreadyPresentCount],
                ['Ditandai Cuti / Izin', $markedLeaveCount],
                ['Ditandai Alpa / Tidak Hadir', $markedAbsentCount],
            ]
        );

        $this->info("Penutupan presensi operasional tanggal {$targetDate} selesai.");

        return self::SUCCESS;
    }
}
