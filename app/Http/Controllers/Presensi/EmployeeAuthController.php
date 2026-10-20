<?php

declare(strict_types=1);

namespace App\Http\Controllers\Presensi;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Controller: Autentikasi Mandiri Karyawan (Multi-Guard).
 *
 * @deprecated Digantikan oleh alur terpadu di AttendanceWebController / AttendanceWebV2Controller.
 */
class EmployeeAuthController extends Controller
{
    /**
     * Tampilkan formulir login presensi karyawan.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('employee')->check() || session('presensi_employee_id')) {
            return redirect()->route('presensi.index');
        }

        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        return view('presensi.login', compact('employees'));
    }

    /**
     * Proses autentikasi masuk karyawan (Guard Employee).
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'code' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        if ($request->filled('employee_id')) {
            $employee = Employee::where('is_active', true)->find($request->input('employee_id'));
            if ($employee) {
                Auth::guard('employee')->login($employee, $request->boolean('remember'));
                session(['presensi_employee_id' => $employee->id]);

                return redirect()->intended(route('presensi.index'))
                    ->with('success', 'Selamat datang, '.$employee->name.'!');
            }
        }

        return back()->withErrors(['employee_id' => 'Identitas karyawan tidak ditemukan.']);
    }

    /**
     * Proses keluar sesi presensi karyawan.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('employee')->logout();
        $request->session()->forget('presensi_employee_id');

        return redirect()->route('presensi.login')
            ->with('info', 'Anda telah berhasil keluar dari sistem presensi.');
    }
}
