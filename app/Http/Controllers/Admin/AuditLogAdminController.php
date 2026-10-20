<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Attendance\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogAdminController extends Controller
{
    /**
     * Tampilkan riwayat audit trail sistem presensi & kepatuhan GRC.
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->input('search').'%';
            $query->where(function ($q) use ($search): void {
                $q->where('action', 'ilike', $search)
                    ->orWhere('ip_address', 'ilike', $search)
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'ilike', $search)
                            ->orWhere('email', 'ilike', $search);
                    });
            });
        }

        $logs = $query->paginate(15)->withQueryString();

        $actionLabels = [
            'employee_created' => 'Pembuatan Karyawan Baru',
            'employee_updated' => 'Pembaruan Data Karyawan',
            'employee_status_toggled' => 'Perubahan Status Keaktifan Karyawan',
            'employee_password_reset' => 'Reset Kata Sandi / PIN Karyawan',
            'leave_approved' => 'Persetujuan Cuti / Izin',
            'leave_rejected' => 'Penolakan Cuti / Izin',
            'overtime_approved' => 'Persetujuan Klaim Lembur',
            'overtime_rejected' => 'Penolakan Klaim Lembur',
            'overtime_approval_updated' => 'Persetujuan Lembur',
            'attendance_setting_updated' => 'Pembaruan Pengaturan Presensi',
        ];

        return view('admin.presensi.audit_logs', compact('logs', 'actionLabels'));
    }
}
