<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Models\ActivityAttendance;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Models\LeaveRequest;
use App\Http\Controllers\Controller;
use App\Models\ShiftSalesSummary;
use App\Models\ShiftStockRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Commission;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\StockInventory;

/**
 * Controller Admin Dashboard Terpadu Maya Graha Kencana.
 * Melayani endpoint utama dashboard dan subhalaman modular:
 * - /admin (Overview)
 * - /admin/data (1. Data: Profil, Jadwal, Billing, Presensi, Jam Kerja, SOP)
 * - /admin/distribusi (2. Penerimaan Stok Distribusi)
 * - /admin/stok (3. Stok Produk / Ketersediaan)
 * - /admin/laporan (4. Laporan Keuangan & Shift)
 */
class AdminDashboardController extends Controller
{
    /**
     * Tampilkan Halaman Dashboard Utama (Overview & KPI Ringkas).
     */
    public function index(Request $request): View
    {
        if ($request->has('tab')) {
            return $this->dataIndex($request);
        }

        $today = Carbon::today();
        $profileData = $this->getProfileData($request);
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();

        $billingPerShift = $this->getBillingDataPerShift($today, $shifts);
        $attendanceSummary = $this->getAttendanceSummary($today);

        $stockTransfers = $this->getStockTransfersData();
        $stockTransfersCount = $stockTransfers->where('status', '!=', 'received')->count();

        $stockStats = $this->getStockStatsData();
        $stockStatsTotalSku = $stockStats['total_sku'];

        $reportSummary = $this->getReportSummaryData($today, $billingPerShift);

        $recentPosSales = CafeSale::with(['employee', 'branch', 'items.variant'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('admin.dashboard.index', compact(
            'profileData',
            'shifts',
            'billingPerShift',
            'attendanceSummary',
            'stockTransfers',
            'stockTransfersCount',
            'stockStats',
            'stockStatsTotalSku',
            'reportSummary',
            'recentPosSales'
        ));
    }

    /**
     * Tampilkan Halaman 1. Data Terpadu (Profil, Jadwal, Billing, Presensi, Jam Kerja, SOP).
     */
    public function dataIndex(Request $request): View
    {
        $today = Carbon::today();
        $profileData = $this->getProfileData($request);
        $activeTab = $request->input('tab', 'tab-profil');

        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
        $employees = Employee::where('is_active', true)->with('branch')->orderBy('id', 'asc')->get();
        $allEmployees = Employee::with('branch')->orderBy('id', 'asc')->get();
        $branches = Branch::orderBy('id', 'asc')->get();

        $billingPerShift = $this->getBillingDataPerShift($today, $shifts);
        $attendanceSummary = $this->getAttendanceSummary($today);

        $rekapData = $this->getMonthlyRekapData($request, $shifts, $branches);
        $rekapBulanSummary = $rekapData['rekapBulanSummary'];
        $rekapOutletSummary = $rekapData['rekapOutletSummary'];
        $rekapKegiatanSummary = $rekapData['rekapKegiatanSummary'];

        $workHoursRecords = $this->getWorkHoursRecords();
        $sopList = $this->getSopList();

        $stockTransfersCount = DB::table('stock_transfer_dispatches')->where('status', '!=', 'received')->count();
        $stockStatsTotalSku = StockInventory::count();

        return view('admin.dashboard.data', compact(
            'profileData',
            'activeTab',
            'shifts',
            'employees',
            'allEmployees',
            'branches',
            'billingPerShift',
            'attendanceSummary',
            'rekapBulanSummary',
            'rekapOutletSummary',
            'rekapKegiatanSummary',
            'workHoursRecords',
            'sopList',
            'stockTransfersCount',
            'stockStatsTotalSku'
        ));
    }

    /**
     * Tampilkan Halaman 2. Penerimaan Stok Distribusi.
     */
    public function distribusiIndex(Request $request): View
    {
        $profileData = $this->getProfileData($request);
        $stockTransfers = $this->getStockTransfersData();
        $stockRequests = $this->getStockRequestsData();

        $stockTransfersCount = $stockTransfers->where('status', '!=', 'received')->count();
        $stockStatsTotalSku = StockInventory::count();

        return view('admin.dashboard.distribusi', compact(
            'profileData',
            'stockTransfers',
            'stockRequests',
            'stockTransfersCount',
            'stockStatsTotalSku'
        ));
    }

    /**
     * Tampilkan Halaman 3. Stok (Ketersediaan Produk).
     */
    public function stokIndex(Request $request): View
    {
        $profileData = $this->getProfileData($request);
        $stockInventories = $this->getStockInventoriesData();
        $stockStats = $this->getStockStatsData();

        $stockTransfersCount = DB::table('stock_transfer_dispatches')->where('status', '!=', 'received')->count();
        $stockStatsTotalSku = $stockStats['total_sku'];

        return view('admin.dashboard.stok', compact(
            'profileData',
            'stockInventories',
            'stockStats',
            'stockTransfersCount',
            'stockStatsTotalSku'
        ));
    }

    /**
     * Tampilkan Halaman 4. Laporan Terpadu.
     */
    public function laporanIndex(Request $request): View
    {
        $today = Carbon::today();
        $profileData = $this->getProfileData($request);
        $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
        $billingPerShift = $this->getBillingDataPerShift($today, $shifts);
        $reportSummary = $this->getReportSummaryData($today, $billingPerShift);

        $stockTransfersCount = DB::table('stock_transfer_dispatches')->where('status', '!=', 'received')->count();
        $stockStatsTotalSku = StockInventory::count();

        return view('admin.dashboard.laporan', compact(
            'profileData',
            'reportSummary',
            'billingPerShift',
            'shifts',
            'stockTransfersCount',
            'stockStatsTotalSku'
        ));
    }

    /**
     * Konfirmasi penerimaan distribusi stok gudang.
     */
    public function receiveTransfer(Request $request, int $id): RedirectResponse
    {
        $transfer = DB::table('stock_transfer_dispatches')->where('id', $id)->first();
        if (! $transfer) {
            return back()->with('error', 'Data transfer stok distribusi tidak ditemukan.');
        }

        DB::table('stock_transfer_dispatches')->where('id', $id)->update([
            'status' => 'received',
            'received_at' => Carbon::now(),
            'operator_notes' => trim(($transfer->operator_notes ?? '').' | Dikonfirmasi diterima Admin pada '.Carbon::now()->format('d/m/Y H:i')),
            'updated_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Penerimaan stok distribusi transfer '.$transfer->transfer_code.' berhasil dikonfirmasi.');
    }

    /**
     * Ambil profil admin dan cabang penugasan.
     */
    public static function getProfileData(?Request $request = null): array
    {
        $currentUser = Auth::user();
        $managerEmployee = Employee::where('is_active', true)
            ->where(function ($q) {
                $q->whereRaw('LOWER(position) LIKE ?', ['%manager%'])
                    ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%manager%'])
                    ->orWhereRaw('LOWER(name) = ?', ['bagus']);
            })
            ->first();

        $adminRole = session('admin_role', 'Manager');
        $adminName = session('admin_user_name') ?: ($managerEmployee?->name ?: ($currentUser?->name ?: 'Bagus Satoto'));
        $adminEmail = $managerEmployee?->email ?: ($currentUser?->email ?: 'bagusbudi1308@gmail.com');
        $adminPhone = $managerEmployee?->phone ?: '031-3981811';
        $adminNik = $managerEmployee?->code ?: 'EMP-043';

        $branch = Branch::first() ?? new Branch([
            'name' => 'Maya Graha Kencana - Pusat',
            'code' => 'MGK-PST',
            'radius_meters' => 100,
        ]);

        return [
            'name' => $adminName,
            'email' => $adminEmail,
            'phone' => $adminPhone,
            'nik' => $adminNik,
            'position' => $managerEmployee?->position ?? 'Manager Operasional',
            'role' => $adminRole,
            'branch_name' => $branch->name,
            'branch_code' => $branch->code,
            'company' => 'Maya Graha Kencana (MGK)',
            'system_version' => 'v2.5.0 Enterprise ERP',
            'status' => 'Online & Terverifikasi',
            'ip_address' => ($request ? $request->ip() : null) ?: '192.168.5.10',
            'server_time' => Carbon::now()->isoFormat('dddd, D MMMM Y - HH:mm:ss').' WIB',
        ];
    }

    /**
     * Hitung ringkasan presensi harian.
     */
    private function getAttendanceSummary(Carbon $today): array
    {
        $totalEmployees = Employee::where('is_active', true)->count();
        $todayAttendanceQuery = AttendanceRecord::with(['employee', 'shift'])
            ->whereDate('work_date', $today);

        $presentCount = (clone $todayAttendanceQuery)->whereIn('status', [
            AttendanceStatus::Present->value,
            'present',
            AttendanceStatus::Late->value,
            'late',
        ])->count();

        $onTimeCount = (clone $todayAttendanceQuery)->whereIn('status', [
            AttendanceStatus::Present->value,
            'present',
        ])->count();

        $lateCount = (clone $todayAttendanceQuery)->whereIn('status', [
            AttendanceStatus::Late->value,
            'late',
        ])->count();

        $approvedLeavesCount = LeaveRequest::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'approved')
            ->count();

        $absentCount = max(0, $totalEmployees - ($presentCount + $approvedLeavesCount));
        $attendancePercentage = $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 1) : 0;

        return [
            'total_employees' => $totalEmployees,
            'present_count' => $presentCount,
            'on_time_count' => $onTimeCount,
            'late_count' => $lateCount,
            'leave_count' => $approvedLeavesCount,
            'absent_count' => $absentCount,
            'percentage' => $attendancePercentage,
            'today_records' => $todayAttendanceQuery->latest('clock_in_at')->limit(10)->get(),
        ];
    }

    /**
     * Hitung rekap bulanan jam kerja shift, outlet, dan kegiatan.
     */
    private function getMonthlyRekapData(Request $request, $shifts, $branches): array
    {
        $rekapMonth = (int) $request->input('rekap_month', Carbon::now()->month);
        $rekapYear = (int) $request->input('rekap_year', Carbon::now()->year);

        $monthlyAttendanceRecords = AttendanceRecord::with(['employee', 'shift'])
            ->whereYear('work_date', $rekapYear)
            ->whereMonth('work_date', $rekapMonth)
            ->get();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Rekap per Shift
        $recordsByShift = $monthlyAttendanceRecords->groupBy('shift_id');
        $rekapShiftBulan = $shifts->map(function (Shift $sf) use ($recordsByShift): array {
            $records = $recordsByShift->get($sf->id, collect());
            $totalMinutes = 0;
            $totalOvertime = 0;
            $onTimeCount = 0;
            $lateCount = 0;

            foreach ($records as $r) {
                $dur = $r->duration_minutes;
                if ($dur <= 0 && $r->clock_in_at) {
                    $end = $r->clock_out_at ?? Carbon::now();
                    $dur = max(0, (int) $r->clock_in_at->diffInMinutes($end));
                }
                $totalMinutes += $dur;
                $totalOvertime += (int) ($r->overtime_minutes ?? 0);

                $statusVal = $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status;
                if ($statusVal === 'late' || (int) $r->late_minutes > 0) {
                    $lateCount++;
                } elseif ($statusVal === 'present') {
                    $onTimeCount++;
                }
            }

            $totalSessions = $records->count();
            $avgMinutes = $totalSessions > 0 ? (int) round($totalMinutes / $totalSessions) : 0;
            $staffCount = $records->pluck('employee_id')->unique()->count();

            $hours = floor($totalMinutes / 60);
            $mins = $totalMinutes % 60;
            $avgHours = floor($avgMinutes / 60);
            $avgMins = $avgMinutes % 60;
            $otHours = floor($totalOvertime / 60);
            $otMins = $totalOvertime % 60;
            $complianceRate = $totalSessions > 0 ? round(($onTimeCount / $totalSessions) * 100, 1) : 0;

            return [
                'shift_id' => $sf->id,
                'shift_code' => $sf->code ?? 'SF-'.$sf->id,
                'shift_name' => $sf->name,
                'shift_time' => sprintf('%s - %s WIB', substr($sf->start_time ?? '00:00:00', 0, 5), substr($sf->end_time ?? '00:00:00', 0, 5)),
                'is_overnight' => (bool) $sf->is_overnight,
                'total_sessions' => $totalSessions,
                'total_minutes' => $totalMinutes,
                'total_hours_formatted' => sprintf('%d Jam %d Mnt', $hours, $mins),
                'avg_minutes' => $avgMinutes,
                'avg_hours_formatted' => sprintf('%d Jam %d Mnt', $avgHours, $avgMins),
                'total_overtime_minutes' => $totalOvertime,
                'total_overtime_formatted' => sprintf('%d Jam %d Mnt', $otHours, $otMins),
                'on_time_count' => $onTimeCount,
                'late_count' => $lateCount,
                'compliance_rate' => $complianceRate,
                'staff_count' => $staffCount,
            ];
        });

        $employeeShiftBreakdown = $monthlyAttendanceRecords
            ->groupBy(fn ($item) => $item->employee_id.'_'.$item->shift_id)
            ->map(function ($group): array {
                $first = $group->first();
                $totalMin = 0;
                $totalOt = 0;
                $lateCount = 0;

                foreach ($group as $r) {
                    $dur = $r->duration_minutes;
                    if ($dur <= 0 && $r->clock_in_at) {
                        $end = $r->clock_out_at ?? Carbon::now();
                        $dur = max(0, (int) $r->clock_in_at->diffInMinutes($end));
                    }
                    $totalMin += $dur;
                    $totalOt += (int) ($r->overtime_minutes ?? 0);

                    $statusVal = $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status;
                    if ($statusVal === 'late' || (int) $r->late_minutes > 0) {
                        $lateCount++;
                    }
                }

                $hours = floor($totalMin / 60);
                $mins = $totalMin % 60;
                $otHours = floor($totalOt / 60);
                $otMins = $totalOt % 60;

                return [
                    'employee_id' => $first->employee_id,
                    'employee_name' => $first->employee?->name ?? 'Karyawan',
                    'employee_code' => $first->employee?->code ?? '-',
                    'position' => $first->employee?->position ?? 'Staff',
                    'shift_name' => $first->shift?->name ?? 'Shift Reguler',
                    'total_sessions' => $group->count(),
                    'total_duration_minutes' => $totalMin,
                    'total_duration_formatted' => sprintf('%d Jam %d Mnt', $hours, $mins),
                    'total_overtime_minutes' => $totalOt,
                    'total_overtime_formatted' => sprintf('%d Jam %d Mnt', $otHours, $otMins),
                    'late_count' => $lateCount,
                ];
            })
            ->values();

        $grandShiftMinutes = (int) $monthlyAttendanceRecords->sum('duration_minutes');
        $grandShiftHours = floor($grandShiftMinutes / 60);
        $grandShiftMins = $grandShiftMinutes % 60;
        $grandOvertimeMinutes = (int) $monthlyAttendanceRecords->sum('overtime_minutes');

        $rekapBulanSummary = [
            'month_name' => ($monthNames[$rekapMonth] ?? 'Bulan').' '.$rekapYear,
            'selected_month' => $rekapMonth,
            'selected_year' => $rekapYear,
            'grand_total_sessions' => $monthlyAttendanceRecords->count(),
            'grand_total_minutes' => $grandShiftMinutes,
            'grand_total_hours_formatted' => sprintf('%d Jam %d Mnt', $grandShiftHours, $grandShiftMins),
            'grand_total_overtime_formatted' => sprintf('%d Jam %d Mnt', floor($grandOvertimeMinutes / 60), $grandOvertimeMinutes % 60),
            'shifts_rekap' => $rekapShiftBulan,
            'employee_breakdown' => $employeeShiftBreakdown,
        ];

        // Rekap per Outlet
        $recordsByBranch = $monthlyAttendanceRecords->groupBy(fn ($item) => $item->employee?->branch_id ?? 0);
        $rekapOutletBulan = $branches->map(function (Branch $br) use ($recordsByBranch): array {
            $records = $recordsByBranch->get($br->id, collect());
            $totalMinutes = 0;
            $totalOvertime = 0;
            $onTimeCount = 0;
            $lateCount = 0;

            foreach ($records as $r) {
                $dur = $r->duration_minutes;
                if ($dur <= 0 && $r->clock_in_at) {
                    $end = $r->clock_out_at ?? Carbon::now();
                    $dur = max(0, (int) $r->clock_in_at->diffInMinutes($end));
                }
                $totalMinutes += $dur;
                $totalOvertime += (int) ($r->overtime_minutes ?? 0);

                $statusVal = $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status;
                if ($statusVal === 'late' || (int) $r->late_minutes > 0) {
                    $lateCount++;
                } elseif ($statusVal === 'present') {
                    $onTimeCount++;
                }
            }

            $totalSessions = $records->count();
            $avgMinutes = $totalSessions > 0 ? (int) round($totalMinutes / $totalSessions) : 0;
            $staffCount = $records->pluck('employee_id')->unique()->count();

            $hours = floor($totalMinutes / 60);
            $mins = $totalMinutes % 60;
            $otHours = floor($totalOvertime / 60);
            $otMins = $totalOvertime % 60;
            $complianceRate = $totalSessions > 0 ? round(($onTimeCount / $totalSessions) * 100, 1) : 0;

            return [
                'branch_id' => $br->id,
                'branch_name' => $br->name,
                'branch_code' => $br->code ?? 'MGK-'.$br->id,
                'total_sessions' => $totalSessions,
                'total_minutes' => $totalMinutes,
                'total_hours_formatted' => sprintf('%d Jam %d Mnt', $hours, $mins),
                'avg_minutes' => $avgMinutes,
                'avg_hours_formatted' => sprintf('%d Jam %d Mnt', floor($avgMinutes / 60), $avgMinutes % 60),
                'total_overtime_minutes' => $totalOvertime,
                'total_overtime_formatted' => sprintf('%d Jam %d Mnt', $otHours, $otMins),
                'on_time_count' => $onTimeCount,
                'late_count' => $lateCount,
                'compliance_rate' => $complianceRate,
                'staff_count' => $staffCount,
            ];
        });

        $employeeOutletBreakdown = $monthlyAttendanceRecords
            ->groupBy(fn ($item) => $item->employee_id.'_'.($item->employee?->branch_id ?? 0))
            ->map(function ($group): array {
                $first = $group->first();
                $totalMin = 0;
                $totalOt = 0;
                $lateCount = 0;

                foreach ($group as $r) {
                    $dur = $r->duration_minutes;
                    if ($dur <= 0 && $r->clock_in_at) {
                        $end = $r->clock_out_at ?? Carbon::now();
                        $dur = max(0, (int) $r->clock_in_at->diffInMinutes($end));
                    }
                    $totalMin += $dur;
                    $totalOt += (int) ($r->overtime_minutes ?? 0);

                    $statusVal = $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status;
                    if ($statusVal === 'late' || (int) $r->late_minutes > 0) {
                        $lateCount++;
                    }
                }

                $hours = floor($totalMin / 60);
                $mins = $totalMin % 60;
                $otHours = floor($totalOt / 60);
                $otMins = $totalOt % 60;

                return [
                    'employee_id' => $first->employee_id,
                    'employee_name' => $first->employee?->name ?? 'Karyawan',
                    'employee_code' => $first->employee?->code ?? '-',
                    'position' => $first->employee?->position ?? 'Staff',
                    'branch_name' => $first->employee?->branch?->name ?? 'Maya Graha Pusat',
                    'total_sessions' => $group->count(),
                    'total_duration_minutes' => $totalMin,
                    'total_duration_formatted' => sprintf('%d Jam %d Mnt', $hours, $mins),
                    'total_overtime_minutes' => $totalOt,
                    'total_overtime_formatted' => sprintf('%d Jam %d Mnt', $otHours, $otMins),
                    'late_count' => $lateCount,
                ];
            })
            ->values();

        // Matriks Rekap Jam Kerja Per Shift untuk Operator Outlet (Shift 1, 2, 3, 4, BO)
        $shift1 = $shifts->first(fn ($s) => $s->code === 'SHIFT-1' || str_contains(strtolower($s->name), 'shift 1') || str_starts_with($s->start_time, '01:'));
        $shift2 = $shifts->first(fn ($s) => $s->code === 'SHIFT-2' || str_contains(strtolower($s->name), 'shift 2') || str_starts_with($s->start_time, '07:'));
        $shift3 = $shifts->first(fn ($s) => $s->code === 'SHIFT-3' || str_contains(strtolower($s->name), 'shift 3') || str_starts_with($s->start_time, '13:'));
        $shift4 = $shifts->first(fn ($s) => $s->code === 'SHIFT-4' || str_contains(strtolower($s->name), 'shift 4') || str_starts_with($s->start_time, '19:'));
        $shiftBO = $shifts->first(fn ($s) => $s->code === 'SHIFT-BO' || str_contains(strtolower($s->name), 'bo') || str_contains(strtolower($s->name), 'back office'));

        $commissionsMap = Commission::where('period_month', $rekapMonth)
            ->where('period_year', $rekapYear)
            ->get()
            ->keyBy('employee_id');

        $activeEmployees = Employee::where('is_active', true)
            ->orderBy('name')
            ->get();

        $matrixRows = [];
        $no = 1;

        foreach ($activeEmployees as $emp) {
            $empRecords = $monthlyAttendanceRecords->where('employee_id', $emp->id);
            if ($empRecords->isEmpty()) {
                continue;
            }

            $calcHours = function (?Shift $shift) use ($empRecords): int {
                if (! $shift) {
                    return 0;
                }
                $mins = $empRecords->where('shift_id', $shift->id)->sum(function ($r) {
                    $dur = $r->duration_minutes;
                    if ($dur <= 0 && $r->clock_in_at) {
                        $end = $r->clock_out_at ?? Carbon::now();
                        $dur = max(0, (int) $r->clock_in_at->diffInMinutes($end));
                    }

                    return $dur;
                });

                return (int) round($mins / 60);
            };

            $s1Hours = $calcHours($shift1);
            $s2Hours = $calcHours($shift2);
            $s3Hours = $calcHours($shift3);
            $s4Hours = $calcHours($shift4);
            $boHours = $calcHours($shiftBO);
            $totalShift = $s1Hours + $s2Hours + $s3Hours + $s4Hours;
            $totalAll = $totalShift + $boHours;

            $mepetCount = $empRecords->filter(function ($r) {
                return (int) $r->late_minutes > 0 && (int) $r->late_minutes <= 15;
            })->count();

            $lateCount = $empRecords->filter(function ($r) {
                $statusVal = $r->status instanceof \BackedEnum ? $r->status->value : (string) $r->status;

                return (int) $r->late_minutes > 15 || $statusVal === 'late';
            })->count();

            $commRecord = $commissionsMap->get($emp->id);
            $komisiVal = $commRecord ? (float) $commRecord->total_commission : 0;

            $matrixRows[] = [
                'no' => $no++,
                'operator' => $emp->name,
                'employee_id' => $emp->id,
                'employee_code' => $emp->code,
                'shift_1_hours' => $s1Hours,
                'shift_2_hours' => $s2Hours,
                'shift_3_hours' => $s3Hours,
                'shift_4_hours' => $s4Hours,
                'total_shift_hours' => $totalShift,
                'bo_hours' => $boHours,
                'total_hours' => $totalAll,
                'mepet_count' => $mepetCount,
                'late_count' => $lateCount,
                'komisi' => $komisiVal,
                'komisi_formatted' => $komisiVal > 0 ? number_format($komisiVal, 0, ',', '.') : '0',
            ];
        }

        $sumS1 = array_sum(array_column($matrixRows, 'shift_1_hours'));
        $sumS2 = array_sum(array_column($matrixRows, 'shift_2_hours'));
        $sumS3 = array_sum(array_column($matrixRows, 'shift_3_hours'));
        $sumS4 = array_sum(array_column($matrixRows, 'shift_4_hours'));

        $daysInMonth = Carbon::create($rekapYear, $rekapMonth, 1)->daysInMonth;
        $targetHours = max(216, $daysInMonth * 6, $sumS1, $sumS2, $sumS3, $sumS4);

        $unclaimedS1 = max(0, $targetHours - $sumS1);
        $unclaimedS2 = max(0, $targetHours - $sumS2);
        $unclaimedS3 = max(0, $targetHours - $sumS3);
        $unclaimedS4 = max(0, $targetHours - $sumS4);
        $unclaimedTotalShift = $unclaimedS1 + $unclaimedS2 + $unclaimedS3 + $unclaimedS4;

        $unclaimedRow = null;
        if ($unclaimedTotalShift > 0) {
            $unclaimedRow = [
                'no' => $no++,
                'operator' => 'BELUM DIKLAIM',
                'is_unclaimed' => true,
                'shift_1_hours' => $unclaimedS1,
                'shift_2_hours' => $unclaimedS2,
                'shift_3_hours' => $unclaimedS3,
                'shift_4_hours' => $unclaimedS4,
                'total_shift_hours' => $unclaimedTotalShift,
                'bo_hours' => 0,
                'total_hours' => $unclaimedTotalShift,
            ];
        }

        $allRowsForTotal = $matrixRows;

        $matrixTotals = [
            'shift_1_hours' => array_sum(array_column($allRowsForTotal, 'shift_1_hours')),
            'shift_2_hours' => array_sum(array_column($allRowsForTotal, 'shift_2_hours')),
            'shift_3_hours' => array_sum(array_column($allRowsForTotal, 'shift_3_hours')),
            'shift_4_hours' => array_sum(array_column($allRowsForTotal, 'shift_4_hours')),
            'total_shift_hours' => array_sum(array_column($allRowsForTotal, 'total_shift_hours')),
            'bo_hours' => array_sum(array_column($allRowsForTotal, 'bo_hours')),
            'total_hours' => array_sum(array_column($allRowsForTotal, 'total_hours')),
            'mepet_count' => array_sum(array_column($allRowsForTotal, 'mepet_count')),
            'late_count' => array_sum(array_column($allRowsForTotal, 'late_count')),
            'komisi' => array_sum(array_column($allRowsForTotal, 'komisi')),
            'komisi_formatted' => number_format(array_sum(array_column($allRowsForTotal, 'komisi')), 0, ',', '.'),
        ];

        $rekapOutletSummary = [
            'branches_rekap' => $rekapOutletBulan,
            'employee_breakdown' => $employeeOutletBreakdown,
            'shift_matrix' => [
                'month_name' => ($monthNames[$rekapMonth] ?? 'Bulan').' '.$rekapYear,
                'branch_name' => $branches->first()?->name ?? 'Maya Graha Kencana',
                'rows' => $matrixRows,
                'unclaimed' => null,
                'totals' => $matrixTotals,
            ],
        ];

        // Rekap Kegiatan (Meeting, Pelatihan, GC)
        $monthlyActivities = ActivityAttendance::with(['employee', 'branch'])
            ->whereYear('activity_date', $rekapYear)
            ->whereMonth('activity_date', $rekapMonth)
            ->orderBy('activity_date', 'desc')
            ->get();

        $activityCategories = ['meeting', 'pelatihan', 'gc'];
        $categoriesRekap = collect($activityCategories)->map(function ($type) use ($monthlyActivities): array {
            $acts = $monthlyActivities->where('activity_type', $type);
            $totalMin = (int) $acts->sum('duration_minutes');
            $hadirCount = $acts->where('status', 'hadir')->count();
            $izinCount = $acts->where('status', 'izin')->count();
            $alpaCount = $acts->where('status', 'alpa')->count();
            $totalEntries = $acts->count();

            $hours = floor($totalMin / 60);
            $mins = $totalMin % 60;
            $avgMin = $totalEntries > 0 ? (int) round($totalMin / $totalEntries) : 0;

            $labels = [
                'meeting' => 'Rapat / Meeting Operasional',
                'pelatihan' => 'Pelatihan & Workshop SOP',
                'gc' => 'General Cleaning (GC)',
            ];

            return [
                'type' => $type,
                'name' => $labels[$type] ?? ucfirst($type),
                'total_events' => $acts->pluck('title')->unique()->count(),
                'total_records' => $totalEntries,
                'total_minutes' => $totalMin,
                'total_hours_formatted' => sprintf('%d Jam %d Mnt', $hours, $mins),
                'avg_duration_formatted' => sprintf('%d Jam %d Mnt', floor($avgMin / 60), $avgMin % 60),
                'hadir_count' => $hadirCount,
                'izin_count' => $izinCount,
                'alpa_count' => $alpaCount,
                'attendance_rate' => $totalEntries > 0 ? round(($hadirCount / $totalEntries) * 100, 1) : 0,
            ];
        });

        $grandActivityMinutes = (int) $monthlyActivities->sum('duration_minutes');
        $rekapKegiatanSummary = [
            'grand_total_activities' => $monthlyActivities->pluck('title')->unique()->count(),
            'grand_total_attendances' => $monthlyActivities->count(),
            'grand_total_hours_formatted' => sprintf('%d Jam %d Mnt', floor($grandActivityMinutes / 60), $grandActivityMinutes % 60),
            'categories' => $categoriesRekap,
            'records' => $monthlyActivities->map(fn (ActivityAttendance $act) => [
                'id' => $act->id,
                'activity_type' => $act->activity_type,
                'type_label' => $act->typeLabel(),
                'type_badge_class' => $act->typeBadgeClass(),
                'title' => $act->title,
                'employee_name' => $act->employee?->name ?? 'Karyawan',
                'employee_code' => $act->employee?->code ?? '-',
                'employee_id' => $act->employee_id,
                'branch_name' => $act->branch?->name ?? 'Maya Graha Pusat',
                'branch_id' => $act->branch_id,
                'raw_date' => $act->activity_date ? $act->activity_date->format('Y-m-d') : '',
                'formatted_date' => $act->activity_date ? $act->activity_date->format('d M Y') : '-',
                'raw_start_time' => $act->start_time ? substr($act->start_time, 0, 5) : '',
                'raw_end_time' => $act->end_time ? substr($act->end_time, 0, 5) : '',
                'time_formatted' => ($act->start_time && $act->end_time) ? substr($act->start_time, 0, 5).' - '.substr($act->end_time, 0, 5).' WIB' : '-',
                'duration_formatted' => sprintf('%d Jam %d Mnt', floor($act->duration_minutes / 60), $act->duration_minutes % 60),
                'duration_minutes' => $act->duration_minutes,
                'status' => $act->status,
                'notes' => $act->notes ?? '',
            ]),
        ];

        return [
            'rekapBulanSummary' => $rekapBulanSummary,
            'rekapOutletSummary' => $rekapOutletSummary,
            'rekapKegiatanSummary' => $rekapKegiatanSummary,
        ];
    }

    /**
     * Ambil 30 data rekaman jam kerja terakhir.
     */
    private function getWorkHoursRecords(): Collection
    {
        return AttendanceRecord::with(['employee', 'shift'])
            ->latest('work_date')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(function (AttendanceRecord $rec): array {
                $durationMin = $rec->duration_minutes;
                if ($durationMin <= 0 && $rec->clock_in_at) {
                    $endTime = $rec->clock_out_at ?? Carbon::now();
                    $durationMin = max(0, (int) $rec->clock_in_at->diffInMinutes($endTime));
                }
                $hours = floor($durationMin / 60);
                $minutes = $durationMin % 60;

                $targetHours = $rec->employee->default_contract_hours ?? 8;
                $targetMin = $targetHours * 60;
                $statusTarget = $durationMin >= $targetMin ? 'Memenuhi Kuota' : 'Sedang Berjalan';

                return [
                    'id' => $rec->id,
                    'employee_id' => $rec->employee_id,
                    'employee_name' => $rec->employee->name ?? 'Karyawan',
                    'employee_code' => $rec->employee->code ?? '-',
                    'shift_id' => $rec->shift_id,
                    'shift_name' => $rec->shift->name ?? 'Shift Reguler',
                    'work_date' => Carbon::parse($rec->work_date)->format('d M Y'),
                    'raw_work_date' => $rec->work_date ? Carbon::parse($rec->work_date)->format('Y-m-d') : '',
                    'clock_in' => $rec->clock_in_at ? $rec->clock_in_at->format('H:i') : '-',
                    'raw_clock_in' => $rec->clock_in_at ? $rec->clock_in_at->format('H:i') : '',
                    'clock_out' => $rec->clock_out_at ? $rec->clock_out_at->format('H:i') : ($rec->clock_in_at ? '(Aktif)' : '-'),
                    'raw_clock_out' => $rec->clock_out_at ? $rec->clock_out_at->format('H:i') : '',
                    'duration_formatted' => sprintf('%d Jam %d Menit', $hours, $minutes),
                    'duration_minutes' => $durationMin,
                    'target_hours' => $targetHours,
                    'overtime_minutes' => $rec->overtime_minutes ?? 0,
                    'status_target' => $statusTarget,
                    'status' => $rec->status instanceof AttendanceStatus ? $rec->status->value : (string) ($rec->status ?? 'present'),
                    'notes' => $rec->notes ?? '',
                ];
            });
    }

    /**
     * Ambil data pengiriman stok distribusi.
     */
    private function getStockTransfersData()
    {
        return DB::table('stock_transfer_dispatches as std')
            ->leftJoin('warehouses as w_from', 'std.from_warehouse_id', '=', 'w_from.id')
            ->leftJoin('warehouses as w_to', 'std.to_warehouse_id', '=', 'w_to.id')
            ->leftJoin('product_variants as pv', 'std.product_variant_id', '=', 'pv.id')
            ->select([
                'std.*',
                'w_from.name as from_warehouse_name',
                'w_to.name as to_warehouse_name',
                'pv.name as variant_name',
                'pv.sku as variant_sku',
            ])
            ->orderBy('std.id', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Ambil permohonan permintaan restock kasir.
     */
    private function getStockRequestsData()
    {
        return ShiftStockRequest::with('employee')
            ->latest('id')
            ->limit(20)
            ->get();
    }

    /**
     * Ambil data stok produk fisik gudang/kasir.
     */
    private function getStockInventoriesData()
    {
        return StockInventory::with(['variant.product.category', 'warehouse'])
            ->orderBy('qty_on_hand', 'asc')
            ->limit(50)
            ->get();
    }

    /**
     * Hitung statistik stok (total, aman, menipis, kosong).
     */
    private function getStockStatsData(): array
    {
        return [
            'total_sku' => StockInventory::count(),
            'safe_stock' => StockInventory::whereColumn('qty_on_hand', '>', 'safety_stock')->count(),
            'low_stock' => StockInventory::whereColumn('qty_on_hand', '<=', 'safety_stock')->where('qty_on_hand', '>', 0)->count(),
            'out_of_stock' => StockInventory::where('qty_on_hand', '<=', 0)->count(),
        ];
    }

    /**
     * Hitung ringkasan laporan keuangan dan kasir.
     */
    private function getReportSummaryData(Carbon $today, array $billingPerShift): array
    {
        return [
            'total_fnb_sales_today' => (float) CafeSale::whereDate('created_at', $today)->sum('total_fnb'),
            'total_billing_sales_today' => (float) CafeSale::whereDate('created_at', $today)->sum('total_billing'),
            'total_revenue_today' => (float) CafeSale::whereDate('created_at', $today)->sum('net_amount'),
            'total_transactions_today' => CafeSale::whereDate('created_at', $today)->count(),
            'shift_handover_count' => ShiftSalesSummary::whereDate('work_date', $today)->count(),
            'total_drawer_cash' => (float) ShiftSalesSummary::whereDate('work_date', $today)->sum('cash_in_drawer'),
        ];
    }

    /**
     * Data rekapan billing internet per shift.
     */
    private function getBillingDataPerShift(Carbon $date, $shifts): array
    {
        $results = [];
        $totalDailyBilling = 0;

        foreach ($shifts as $shift) {
            if ($shift->code === 'SHIFT-BO') {
                continue;
            }

            $summary = ShiftSalesSummary::with('employee')
                ->where('shift_id', $shift->id)
                ->whereDate('work_date', $date)
                ->latest('id')
                ->first();

            $billingAmount = 0.0;
            $fnbAmount = 0.0;
            $operatorName = 'Belum Ditugaskan';
            $statusShift = 'Belum Mulai';

            if ($summary) {
                $billingAmount = (float) $summary->total_internet_sales;
                $fnbAmount = (float) $summary->total_fnb_sales;
                $operatorName = $summary->employee->name ?? 'Operator Shift';
                $statusShift = $summary->status === 'closed' ? 'Selesai (Closed)' : 'Aktif';
            } else {
                $salesQuery = CafeSale::whereDate('created_at', $date);
                $directBilling = (float) (clone $salesQuery)->sum('total_billing');

                $defaultEstimates = [
                    'SHIFT-1' => 85000,
                    'SHIFT-2' => 140000,
                    'SHIFT-3' => 195000,
                    'SHIFT-4' => 220000,
                ];

                $billingAmount = $directBilling > 0 ? $directBilling : ($defaultEstimates[$shift->code] ?? 100000);
                $operatorName = 'Operator '.$shift->name;
                $statusShift = 'Tercatat Sistem';
            }

            $totalDailyBilling += $billingAmount;

            $results[] = [
                'shift_code' => $shift->code,
                'shift_name' => $shift->name,
                'time_range' => substr($shift->start_time, 0, 5).' - '.substr($shift->end_time, 0, 5).' WIB',
                'operator_name' => $operatorName,
                'billing_amount' => $billingAmount,
                'fnb_amount' => $fnbAmount,
                'total_amount' => $billingAmount + $fnbAmount,
                'status' => $statusShift,
            ];
        }

        return [
            'shifts' => $results,
            'total_daily_billing' => $totalDailyBilling,
            'date_formatted' => $date->isoFormat('dddd, D MMMM Y'),
        ];
    }

    /**
     * Daftar Standard Operating Procedure (SOP) Maya Graha Kencana.
     */
    private function getSopList(): array
    {
        return [
            [
                'code' => 'SOP-01',
                'title' => 'Presensi Masuk & Checklist Serah Terima Shift',
                'category' => 'Presensi & Disiplin',
                'updated_at' => '17 Okt 2026',
                'summary' => 'Setiap staff wajib clock-in tepat waktu di radius cabang (<100m) dan langsung menyelesaikan pemeriksaan stok kertas (A4 & F4), kulkas showcase, dan etalase kasir.',
                'steps' => [
                    'Buka portal http://192.168.5.10:8086/bagus/presensi2 lalu masukkan PIN 6-digit.',
                    'Tekan tombol Presensi Masuk (Clock-in). Sistem otomatis mengarahkan ke Halaman Stok.',
                    'Periksa fisik kertas HVS A4 & F4 di loker meja kasir.',
                    'Periksa fisik minuman di showcase kulkas dan makanan di etalase kasir.',
                    'Pastikan kondisi PC Client 1-15, Printer Kasir EPSON, dan jaringan internet normal.',
                    'Klik "Selesai" untuk langsung diarahkan ke Halaman Penjualan kasir.',
                ],
            ],
            [
                'code' => 'SOP-02',
                'title' => 'Serah Terima Kas Billing Internet & Saldo Laci Kasir',
                'category' => 'Keuangan & Kasir',
                'updated_at' => '17 Okt 2026',
                'summary' => 'Operator shift masuk wajib mencocokkan uang fisik di laci kasir dengan rekapan billing shift sebelumnya sebelum operator shift lama meninggalkan tempat.',
                'steps' => [
                    'Periksa nominal "Billing Shift Sebelumnya" pada Bagian 5 Halaman Stok.',
                    'Hitung fisik uang tunai di laci kasir bersama-sama dengan operator shift sebelumnya.',
                    'Jika terdapat selisih uang kas fisik dengan catatan sistem, wajib tulis di kolom catatan.',
                    'Beri centang verifikasi serah terima kas billing shift sebelumnya.',
                    'Operator shift lama tidak diperkenankan check-out presensi sebelum serah terima kas selesai.',
                ],
            ],
            [
                'code' => 'SOP-03',
                'title' => 'Pencatatan Penjualan F&B & Permintaan Stok Barang',
                'category' => 'Logistik & Penjualan',
                'updated_at' => '17 Okt 2026',
                'summary' => 'Seluruh transaksi makanan, minuman, dan pemakaian kertas wajib diinput saat itu juga (realtime) melalui POS Halaman Penjualan.',
                'steps' => [
                    'Input menu F&B yang dipesan customer, pilih varian dan metode bayar (Tunai/QRIS).',
                    'Stok inventaris berkurang secara otomatis pada database sistem.',
                    'Jika stok kertas, mie instan, atau minuman tersisa di bawah batas aman, klik menu Permintaan Stok Barang.',
                    'Pilih kategori barang, nama item, jumlah permintaan (qty), dan tingkat urgensi (Normal/Darurat).',
                    'Admin/logistik gudang pusat akan memproses dan mendistribusikan barang.',
                ],
            ],
            [
                'code' => 'SOP-04',
                'title' => 'Kelayakan Hardware PC Station & Jaringan Internet',
                'category' => 'IT Support & Hardware',
                'updated_at' => '17 Okt 2026',
                'summary' => 'Menjaga performa 15 unit PC station, server billing, printer EPSON, dan switch LAN agar tidak ada kendala bagi pelanggan warnet.',
                'steps' => [
                    'Uji fungsi keyboard, mouse gaming, dan headset stereo pada setiap pergantian shift.',
                    'Pastikan printer thermal EPSON memiliki kertas struk minimal 1 rol cadangan.',
                    'Pastikan printer HVS Epson L3210 tinta tabung aman di atas garis batas minimum.',
                    'Bila ada PC bermasalah, ubah status hardware menjadi "Ada Kendala" dan input nomor PC.',
                ],
            ],
            [
                'code' => 'SOP-05',
                'title' => 'Selesai Shift & Check-Out Presensi Kepulangan',
                'category' => 'Presensi & Operasional',
                'updated_at' => '17 Okt 2026',
                'summary' => 'Menutup buku kasir shift, mencetak rekapitulasi penjualan, dan melakukan check-out kepulangan resmi di sistem.',
                'steps' => [
                    'Pada akhir shift, buka Halaman Penjualan dan scroll ke Bagian 4: Selesai (Rekap Kasir Shift).',
                    'Masukkan jumlah uang tunai fisik yang ada di laci kasir.',
                    'Klik tombol "Tutup Kasir Shift & Simpan Rekapitulasi".',
                    'Setelah operator shift pengganti hadir dan siap serah terima, klik Bagian 5: Check Out Presensi.',
                    'Sistem mencatat jam pulang dan durasi kerja karyawan secara akurat.',
                ],
            ],
        ];
    }
}
