<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\LeaveController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes V1 - Sistem Presensi Maya Graha Kencana
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    // Master Shifts
    Route::get('attendance/shifts', [AttendanceController::class, 'shifts'])->name('api.v1.attendance.shifts');

    // Presensi Kehadiran
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('api.v1.attendance.clock-in');
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('api.v1.attendance.clock-out');
    Route::get('attendance/today', [AttendanceController::class, 'today'])->name('api.v1.attendance.today');
    Route::get('attendance/history', [AttendanceController::class, 'history'])->name('api.v1.attendance.history');

    // Permohonan Cuti & Izin
    Route::post('leave/request', [LeaveController::class, 'requestLeave'])->name('api.v1.leave.request');
    Route::get('leave/history', [LeaveController::class, 'history'])->name('api.v1.leave.history');
});
