<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Services\AttendanceSettingService;
use App\Domains\Attendance\Services\AuditLogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Admin Panel: Pengaturan Presensi Dinamis Enterprise.
 */
class AdminAttendanceSettingController extends Controller
{
    /**
     * Tampilkan antarmuka manajemen pengaturan presensi.
     */
    public function index(AttendanceSettingService $settingService): View
    {
        $settingsGrouped = $settingService->getAllGrouped();

        return view('admin.presensi.pengaturan', compact('settingsGrouped'));
    }

    /**
     * Simpan pembaruan pengaturan presensi dan lakukan pencatatan audit trail.
     */
    public function update(
        Request $request,
        AttendanceSettingService $settingService,
        AuditLogService $auditLogService
    ): RedirectResponse {
        $settings = $request->input('settings', []);
        $updatedKeys = [];

        foreach ($settings as $key => $value) {
            $settingService->set((string) $key, $value);
            $updatedKeys[] = $key;
        }

        $auditLogService->log(
            action: 'attendance_setting_updated',
            auditable: null,
            oldValues: null,
            newValues: [
                'updated_keys' => $updatedKeys,
            ],
            userId: $request->user()?->id
        );

        return redirect()->back()->with('success', 'Pengaturan presensi berhasil disimpan dan cache sistem telah diperbarui.');
    }
}
