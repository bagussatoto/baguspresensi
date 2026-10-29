<?php

declare(strict_types=1);

namespace Modules\MasterData\Application\Http\Controllers;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use App\Http\Controllers\Controller;
use App\Models\ShiftScheduleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Attendance;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Commission;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\EmployeeSchedule;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\StockTransferDispatch;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\MasterData\Domain\Services\CafeCheckoutService;
use Modules\MasterData\Domain\Services\PcStationSessionService;
use Modules\MasterData\Domain\Services\StockTransferService;

/**
 * Application Controller: Orkestrator interaksi HTTP dan Domain Services Maya Graha Kencana.
 */
class InternetCafeController extends Controller
{
    public function __construct(
        private readonly CafeCheckoutService $checkoutService,
        private readonly PcStationSessionService $pcSessionService,
        private readonly StockTransferService $stockTransferService,
        private readonly ClockInAction $clockInAction,
        private readonly ClockOutAction $clockOutAction,
    ) {}

    public function index(): View
    {
        // 1. Data Karyawan & Kasir
        $employees = Employee::with('branch')->orderBy('id', 'asc')->get();

        // 2 & 3. Produk & Kategori
        $categories = Category::withCount('products')->get();
        $products = Product::with(['category', 'brand', 'variants'])->get();
        $variants = ProductVariant::with(['product.category'])->orderBy('id', 'asc')->get();

        // 4. Stok Barang Fisik per Gudang / Kasir
        $stocks = StockInventory::with(['warehouse.branch', 'variant.product'])->get();

        // 5. Shift Kerja MGK & Jadwal
        $shifts = Shift::where('is_active', true)->orderBy('start_time', 'asc')->get();
        $schedules = EmployeeSchedule::with(['employee', 'branch'])
            ->orderBy('shift_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();
        $activeScheduleImage = ShiftScheduleImage::where('is_active', true)->latest()->first()
            ?? ShiftScheduleImage::latest()->first();

        // 5b. Jadwal Shift Fisik Statis (Tanpa Database)
        $staticSchedulePath = public_path('uploads/jadwal/jadwal-shift-current.jpg');
        $staticScheduleExists = file_exists($staticSchedulePath);
        $staticScheduleTime = $staticScheduleExists ? filemtime($staticSchedulePath) : null;
        $staticScheduleInfo = null;
        if (file_exists(public_path('uploads/jadwal/info.json'))) {
            $staticScheduleInfo = json_decode((string) file_get_contents(public_path('uploads/jadwal/info.json')), true);
        }

        // 6. Presensi Karyawan (Integrasi DDD AttendanceRecord)
        $attendanceRecords = AttendanceRecord::with(['employee', 'branch', 'shift'])
            ->orderBy('work_date', 'desc')
            ->orderBy('clock_in_at', 'desc')
            ->take(50)
            ->get();

        $attendances = $attendanceRecords->isNotEmpty()
            ? $attendanceRecords
            : Attendance::with(['employee', 'branch'])
                ->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_at', 'desc')
                ->get();

        // 7. Penjualan Kasir POS (Billing + F&B)
        $sales = CafeSale::with(['employee', 'pcStation', 'items.variant', 'payments'])
            ->orderBy('id', 'desc')
            ->take(200)
            ->get();

        // 8. Distribusi Stok Barang (Two-step Transfer)
        $transfers = StockTransferDispatch::with(['fromWarehouse', 'toWarehouse', 'variant.product'])
            ->orderBy('id', 'desc')
            ->get();

        // 9. Data PC Stations / Billing Units
        $pcStations = PcStation::orderBy('station_code', 'asc')->get();

        // 10. Komisi Karyawan
        $commissions = Commission::with('employee')->orderBy('id', 'desc')->get();

        // Warehouses & Branches
        $branches = Branch::all();
        $warehouses = Warehouse::all();

        $presentTodayCount = AttendanceRecord::whereDate('work_date', now()->toDateString())->count();
        if ($presentTodayCount === 0) {
            $presentTodayCount = Attendance::whereDate('attendance_date', now()->toDateString())->count();
        }

        // KPI Metrics
        $metrics = [
            'total_pcs' => $pcStations->count(),
            'occupied_pcs' => $pcStations->where('status', 'occupied')->count(),
            'available_pcs' => $pcStations->where('status', 'available')->count(),
            'today_sales' => $sales->sum('net_amount'),
            'today_fnb' => $sales->sum('total_fnb'),
            'today_billing' => $sales->sum('total_billing'),
            'present_employees' => $presentTodayCount,
            'total_commissions' => $commissions->sum('total_commission'),
        ];

        return view('warnet.index', compact(
            'employees',
            'categories',
            'products',
            'variants',
            'stocks',
            'shifts',
            'schedules',
            'activeScheduleImage',
            'attendances',
            'sales',
            'transfers',
            'pcStations',
            'commissions',
            'branches',
            'warehouses',
            'metrics',
            'staticScheduleExists',
            'staticScheduleTime',
            'staticScheduleInfo'
        ));
    }

    /**
     * POS Kasir Checkout: Mendelegasikan transaksi ke Domain Service CafeCheckoutService.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'pc_station_id' => ['nullable', 'exists:pc_stations,id'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,debit'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'cashier_notes' => ['nullable', 'string', 'max:500'],
            'finish_pc_session' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.item_type' => ['required', 'string', 'in:billing,fnb,voucher,makanan,minuman,snack'],
            'items.*.name' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $sale = $this->checkoutService->execute($validated);
        $sale->load(['items', 'payments', 'employee', 'pcStation']);

        return response()->json([
            'message' => 'Pembayaran kasir Maya Graha Kencana berhasil diproses & dicatat via Domain Service.',
            'invoice_number' => $sale->invoice_number,
            'payment_number' => $sale->payments->first()?->payment_number,
            'amount_paid' => $sale->amount_paid,
            'change_amount' => $sale->change_amount,
            'sale' => $sale,
        ], 201);
    }

    /**
     * Buka / Selesaikan Sesi Billing PC Station via Domain Service PcStationSessionService.
     */
    public function togglePcStation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'station_id' => ['required', 'exists:pc_stations,id'],
            'action' => ['required', 'string', 'in:start,stop,maintenance'],
            'user_name' => ['nullable', 'string', 'max:150'],
            'duration_minutes' => ['nullable', 'integer', 'min:15'],
        ]);

        $pc = PcStation::findOrFail($validated['station_id']);

        $updatedPc = match ($validated['action']) {
            'start' => $this->pcSessionService->startSession(
                $pc,
                $validated['user_name'] ?? 'Tamu Walk-in',
                (int) ($validated['duration_minutes'] ?? 60)
            ),
            'stop' => $this->pcSessionService->stopSession($pc),
            default => $this->pcSessionService->setMaintenance($pc),
        };

        return response()->json([
            'message' => "Status unit {$updatedPc->station_code} berhasil diperbarui menjadi {$updatedPc->status}.",
            'station' => $updatedPc,
        ]);
    }

    /**
     * Clock-In / Clock-Out Presensi Operator (Terintegrasi ke DDD AttendanceRecord & Legacy Attendance).
     */
    public function clockAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_name' => ['nullable', 'string', 'max:100'],
            'employee_id' => ['nullable'],
            'type' => ['required', 'string', 'in:in,out'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = null;
        $identifier = trim((string) ($request->input('employee_name') ?: $request->input('employee_id') ?: ''));

        if ($identifier !== '') {
            if (is_numeric($identifier)) {
                $employee = Employee::with('branch')->where('is_active', true)->find((int) $identifier);
            }

            if (! $employee) {
                $employee = Employee::with('branch')->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($identifier)])
                    ->first();
            }

            if (! $employee) {
                $employee = Employee::with('branch')->where('is_active', true)
                    ->where(function ($q) use ($identifier) {
                        $q->where('code', $identifier)
                            ->orWhere('username', $identifier)
                            ->orWhere('email', $identifier);
                    })
                    ->first();
            }

            if (! $employee) {
                $matchingEmployees = Employee::with('branch')->where('is_active', true)
                    ->where('name', 'LIKE', '%'.$identifier.'%')
                    ->get();

                if ($matchingEmployees->count() === 1) {
                    $employee = $matchingEmployees->first();
                } elseif ($matchingEmployees->count() > 1) {
                    return response()->json([
                        'message' => 'Ditemukan lebih dari 1 karyawan dengan nama tersebut. Tuliskan nama lengkap Anda secara spesifik atau gunakan NIK.',
                    ], 422);
                }
            }
        }

        if (! $employee) {
            return response()->json([
                'message' => 'Identitas operator kasir tidak ditemukan dalam data karyawan aktif.',
            ], 422);
        }

        $branch = $employee->branch ?? Branch::where('code', 'NET-PST')->first() ?? Branch::first();

        // Pastikan cabang memiliki koordinat GPS
        $latitude = $branch && $branch->latitude ? (float) $branch->latitude : -6.200000;
        $longitude = $branch && $branch->longitude ? (float) $branch->longitude : 106.816666;

        $today = now()->toDateString();
        $record = null;

        // 1. Eksekusi DDD Domain Action
        try {
            if ($validated['type'] === 'in') {
                $dto = new ClockInDTO(
                    employeeId: $employee->id,
                    branchId: $branch->id,
                    shiftId: ! empty($validated['shift_id']) ? (int) $validated['shift_id'] : null,
                    latitude: $latitude,
                    longitude: $longitude,
                    photoPath: null,
                    deviceInfo: ['client' => 'Warnet POS Web Terminal', 'ip' => $request->ip()],
                    isMockLocation: false,
                    verificationMethod: VerificationMethod::WebKiosk
                );

                $record = $this->clockInAction->execute($dto);
                if (! empty($validated['notes'])) {
                    $record->update(['notes' => $validated['notes']]);
                }
            } else {
                $dto = new ClockOutDTO(
                    employeeId: $employee->id,
                    latitude: $latitude,
                    longitude: $longitude,
                    photoPath: null,
                    branchId: $branch->id,
                    deviceInfo: ['client' => 'Warnet POS Web Terminal', 'ip' => $request->ip()],
                    isMockLocation: false
                );

                $record = $this->clockOutAction->execute($dto);
                if (! empty($validated['notes'])) {
                    $record->update(['notes' => ($record->notes ? $record->notes.' | ' : '').$validated['notes']]);
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // 2. Sinkronisasi ke model Attendance legacy agar test & modul lama tetap sinkron
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
        ]);
        $attendance->branch_id = $branch->id;

        if ($validated['type'] === 'in') {
            $attendance->check_in_at = $record->clock_in_at;
            $attendance->status = $record->status->value;
            $attendance->late_minutes = $record->late_minutes;
            $attendance->notes = $validated['notes'] ?? 'Presensi masuk via POS Web.';
        } else {
            $attendance->check_out_at = $record->clock_out_at;
            if (! empty($validated['notes'])) {
                $attendance->notes = ($attendance->notes ? $attendance->notes.' | ' : '').'Keluar: '.$validated['notes'];
            }
        }
        $attendance->save();

        $shiftName = $record->shift?->name ?? 'Shift';
        $lemburInfo = ($validated['type'] === 'out' && $record->overtime_minutes > 0)
            ? " (Lembur: {$record->overtime_minutes} mnt)"
            : '';

        return response()->json([
            'message' => "Presensi ({$validated['type']}) operator {$employee->name} [{$shiftName}] berhasil dicatat{$lemburInfo}.",
            'attendance' => $record,
        ]);
    }

    /**
     * Distribusi Stok Barang via Domain Service StockTransferService.
     */
    public function transferStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'operator_notes' => ['nullable', 'string'],
        ]);

        $transfer = $this->stockTransferService->transfer(
            (int) $validated['from_warehouse_id'],
            (int) $validated['to_warehouse_id'],
            (int) $validated['product_variant_id'],
            (int) $validated['qty'],
            $validated['operator_notes'] ?? null
        );

        return response()->json([
            'message' => 'Distribusi mutasi stok barang berhasil dicatat via Domain Service.',
            'transfer' => $transfer->load(['fromWarehouse', 'toWarehouse', 'variant']),
        ]);
    }

    /**
     * Jadwal Shift Karyawan.
     */
    public function storeSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'shift_name' => ['required', 'string'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ]);

        $employee = Employee::find($validated['employee_id']);
        $branchMgk = Branch::where('code', 'NET-PST')->first() ?? Branch::first();
        $branchId = $employee?->branch_id ?? $branchMgk?->id ?? 3;

        $schedule = EmployeeSchedule::create([
            'employee_id' => $validated['employee_id'],
            'branch_id' => $branchId,
            'shift_name' => $validated['shift_name'],
            'shift_date' => $validated['shift_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'scheduled',
        ]);

        return response()->json([
            'message' => 'Jadwal shift berhasil ditambahkan.',
            'schedule' => $schedule->load('employee'),
        ]);
    }

    /**
     * Update Jadwal Shift Karyawan.
     */
    public function updateSchedule(Request $request, EmployeeSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['sometimes', 'exists:employees,id'],
            'shift_name' => ['sometimes', 'string'],
            'shift_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes'],
            'end_time' => ['sometimes'],
            'status' => ['sometimes', 'string', 'in:scheduled,ongoing,completed,cancelled'],
        ]);

        $schedule->update($validated);

        return response()->json([
            'message' => 'Jadwal shift berhasil diperbarui.',
            'schedule' => $schedule->load('employee'),
        ]);
    }

    /**
     * Hapus Jadwal Shift Karyawan.
     */
    public function destroySchedule(EmployeeSchedule $schedule): JsonResponse
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Jadwal shift berhasil dihapus.',
            'id' => $schedule->id,
        ]);
    }

    /**
     * Unggah Gambar Jadwal Shift Statis Tanpa Database (Langsung Menimpa File Fisik).
     */
    public function uploadStaticSchedule(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $destinationPath = public_path('uploads/jadwal');
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file = $request->file('image');
        $filename = 'jadwal-shift-current.jpg';

        $file->move($destinationPath, $filename);

        $meta = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_at_formatted' => date('d M Y, H:i').' WIB',
            'filesize_kb' => round(filesize($destinationPath.'/'.$filename) / 1024, 1),
            'filename' => $filename,
        ];
        file_put_contents($destinationPath.'/info.json', json_encode($meta, JSON_PRETTY_PRINT));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar jadwal shift berhasil diperbarui (file statis ditimpa langsung).',
                'info' => $meta,
                'url' => asset('uploads/jadwal/'.$filename).'?v='.time(),
            ]);
        }

        return redirect()->back()->with('success', 'Gambar jadwal shift berhasil diperbarui (file statis langsung ditimpa).');
    }
}
