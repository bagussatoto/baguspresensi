<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domains\Attendance\Actions\ClockInAction;
use App\Domains\Attendance\Actions\ClockOutAction;
use App\Domains\Attendance\Actions\SyncOfflineAttendanceAction;
use App\Domains\Attendance\DataTransferObjects\ClockInDTO;
use App\Domains\Attendance\DataTransferObjects\ClockOutDTO;
use App\Domains\Attendance\Exceptions\AlreadyClockedInException;
use App\Domains\Attendance\Exceptions\MockLocationDetectedException;
use App\Domains\Attendance\Exceptions\NotClockedInException;
use App\Domains\Attendance\Exceptions\OutsideGeofenceException;
use App\Domains\Attendance\Exceptions\ShiftNotAssignedException;
use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Services\AntiFraudService;
use App\Domains\Employee\Models\Shift;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClockInRequest;
use App\Http\Requests\Api\V1\ClockOutRequest;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\ShiftResource;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller API RESTful V1: Presensi Kehadiran Karyawan & Operator.
 */
class AttendanceController extends Controller
{
    /**
     * Endpoint Presensi Masuk (Clock In).
     * POST /api/v1/attendance/clock-in
     */
    public function clockIn(
        ClockInRequest $request,
        ClockInAction $action,
        AntiFraudService $antiFraud
    ): JsonResponse {
        try {
            $dto = new ClockInDTO(
                employeeId: (int) $request->input('employee_id'),
                branchId: (int) $request->input('branch_id'),
                latitude: (float) $request->input('latitude'),
                longitude: (float) $request->input('longitude'),
                shiftId: $request->filled('shift_id') ? (int) $request->input('shift_id') : null,
                photoPath: $request->input('photo'),
                deviceInfo: $antiFraud->extractAuditInfo($request),
                ipAddress: $request->ip(),
                isMockLocation: (bool) $request->input('is_mock_location', false),
                notes: $request->input('notes'),
                clockInAt: $request->filled('clock_in_at') ? Carbon::parse($request->input('clock_in_at')) : null,
                wifiBssid: (string) $request->input('wifi_bssid') ?: null,
                livenessScore: $request->filled('liveness_score') ? (float) $request->input('liveness_score') : null
            );

            $record = $action->execute($dto);
            $record->load(['employee', 'branch', 'shift']);

            return (new AttendanceResource($record))
                ->additional([
                    'success' => true,
                    'message' => 'Presensi masuk (Clock-In) berhasil dicatat.',
                ])
                ->response()
                ->setStatusCode(201);
        } catch (OutsideGeofenceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'OUTSIDE_GEOFENCE',
                'distance_meters' => $e->distanceMeters,
                'allowed_radius_meters' => $e->allowedRadiusMeters,
            ], 422);
        } catch (AlreadyClockedInException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'ALREADY_CLOCKED_IN',
            ], 409);
        } catch (MockLocationDetectedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'MOCK_LOCATION_DETECTED',
            ], 403);
        } catch (ShiftNotAssignedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'SHIFT_NOT_ASSIGNED',
            ], 404);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Endpoint Presensi Pulang (Clock Out).
     * POST /api/v1/attendance/clock-out
     */
    public function clockOut(
        ClockOutRequest $request,
        ClockOutAction $action,
        AntiFraudService $antiFraud
    ): JsonResponse {
        try {
            $dto = new ClockOutDTO(
                employeeId: (int) $request->input('employee_id'),
                latitude: (float) $request->input('latitude'),
                longitude: (float) $request->input('longitude'),
                branchId: $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
                shiftId: $request->filled('shift_id') ? (int) $request->input('shift_id') : null,
                photoPath: $request->input('photo'),
                deviceInfo: $antiFraud->extractAuditInfo($request),
                ipAddress: $request->ip(),
                isMockLocation: (bool) $request->input('is_mock_location', false),
                notes: $request->input('notes'),
                clockOutAt: $request->filled('clock_out_at') ? Carbon::parse($request->input('clock_out_at')) : null
            );

            $record = $action->execute($dto);
            $record->load(['employee', 'branch', 'shift']);

            return (new AttendanceResource($record))
                ->additional([
                    'success' => true,
                    'message' => 'Presensi pulang (Clock-Out) berhasil dicatat.',
                ])
                ->response()
                ->setStatusCode(200);
        } catch (OutsideGeofenceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'OUTSIDE_GEOFENCE',
                'distance_meters' => $e->distanceMeters,
                'allowed_radius_meters' => $e->allowedRadiusMeters,
            ], 422);
        } catch (NotClockedInException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'NOT_CLOCKED_IN',
            ], 400);
        } catch (TooEarlyForClockOutException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'TOO_EARLY_FOR_CLOCK_OUT',
            ], 422);
        } catch (MockLocationDetectedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'MOCK_LOCATION_DETECTED',
            ], 403);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan status presensi hari ini untuk karyawan.
     * GET /api/v1/attendance/today?employee_id=X
     */
    public function today(Request $request): JsonResponse
    {
        $employeeId = $request->query('employee_id');
        if (! $employeeId) {
            return response()->json(['message' => 'Parameter employee_id wajib disertakan.'], 400);
        }

        $today = Carbon::today()->toDateString();
        $record = AttendanceRecord::with(['employee', 'branch', 'shift'])
            ->where('employee_id', $employeeId)
            ->whereDate('work_date', $today)
            ->orderBy('clock_in_at', 'desc')
            ->first();

        if (! $record) {
            return response()->json([
                'success' => true,
                'has_clocked_in' => false,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_clocked_in' => true,
            'has_clocked_out' => $record->clock_out_at !== null,
            'data' => new AttendanceResource($record),
        ]);
    }

    /**
     * Dapatkan riwayat presensi karyawan (default 30 hari terakhir).
     * GET /api/v1/attendance/history?employee_id=X&month=10&year=2026
     */
    public function history(Request $request): JsonResponse
    {
        $employeeId = $request->query('employee_id');
        if (! $employeeId) {
            return response()->json(['message' => 'Parameter employee_id wajib disertakan.'], 400);
        }

        $query = AttendanceRecord::with(['employee', 'branch', 'shift'])
            ->where('employee_id', $employeeId);

        if ($request->filled('month') && $request->filled('year')) {
            $month = (int) $request->query('month');
            $year = (int) $request->query('year');
            $query->whereMonth('work_date', $month)->whereYear('work_date', $year);
        }

        $records = $query->orderBy('work_date', 'desc')->paginate(20);

        return AttendanceResource::collection($records)->response();
    }

    /**
     * Dapatkan daftar master shift aktif Maya Graha Kencana.
     * GET /api/v1/attendance/shifts
     */
    public function shifts(): JsonResponse
    {
        $shifts = Shift::where('is_active', true)->orderBy('start_time', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => ShiftResource::collection($shifts),
        ]);
    }

    /**
     * Endpoint Sinkronisasi Presensi Offline PWA / Mobile (Anti-Replay Nonce & Idempotensi).
     * POST /api/v1/attendance/sync-offline
     */
    public function syncOffline(
        Request $request,
        SyncOfflineAttendanceAction $action
    ): JsonResponse {
        $result = $action->execute($request->all(), Auth::id());

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
