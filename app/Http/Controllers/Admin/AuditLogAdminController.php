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

        $lang = (string) $request->input('lang', 'id');
        if (! in_array($lang, ['id', 'en'], true)) {
            $lang = 'id';
        }

        $logs = $query->paginate(15)->withQueryString();

        $actionLabelsId = [
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
            'portal_login' => 'Login Karyawan Berhasil',
            'portal_login_lockout' => '🚨 Insiden Keamanan: Percobaan Login Gagal (Lockout)',
            'shift_handover_saved' => 'Serah Terima Kasir Shift',
            'shift_cash_deposited' => 'Setoran Kas Shift ke Bank/Kolektor',
            'pos_sale_void' => 'Pembatalan Nota Kasir (VOID)',
            'pos_sale_created' => 'Penjualan Kasir POS Baru',
            'billing_adjustment_approved' => 'Persetujuan Potongan Billing (PLN Mati)',
            'billing_adjustment_rejected' => 'Penolakan Potongan Billing',
            'print_adjustment_approved' => 'Persetujuan Potongan Kertas Rusak',
            'print_adjustment_rejected' => 'Penolakan Potongan Kertas Rusak',
            'shift_deposit_verified' => 'Verifikasi Setoran Shift Kasir',
            'employee_registration_resubmitted' => 'Pendaftaran Ulang Calon Karyawan',
            'employee_registration_approved' => 'Persetujuan Registrasi Karyawan',
            'employee_registration_rejected' => 'Penolakan Registrasi Karyawan',
            'access_rights_periodic_review' => 'Tinjauan Hak Akses Berkala (ISO 27001)',
            'account_unlocked' => 'Pembukaan Blokir Login Karyawan',
            'work_hours_stored' => 'Pencatatan Jam Kerja Manual',
            'work_hours_updated' => 'Pembaruan Jam Kerja Manual',
            'work_hours_destroyed' => 'Penghapusan Jam Kerja Manual',
            'activity_stored' => 'Pencatatan Aktivitas Lapangan',
            'activity_updated' => 'Pembaruan Aktivitas Lapangan',
            'activity_destroyed' => 'Penghapusan Aktivitas Lapangan',
        ];

        $actionLabelsEn = [
            'employee_created' => 'New Employee Created',
            'employee_updated' => 'Employee Details Updated',
            'employee_status_toggled' => 'Employee Active Status Toggled',
            'employee_password_reset' => 'Employee Password / PIN Reset',
            'leave_approved' => 'Leave / Permission Approved',
            'leave_rejected' => 'Leave / Permission Rejected',
            'overtime_approved' => 'Overtime Claim Approved',
            'overtime_rejected' => 'Overtime Claim Rejected',
            'overtime_approval_updated' => 'Overtime Approval Updated',
            'attendance_setting_updated' => 'Attendance Settings Updated',
            'portal_login' => 'Employee Login Succeeded',
            'portal_login_lockout' => '🚨 Security Incident: Failed Login Lockout',
            'shift_handover_saved' => 'Shift Cashier Handover Saved',
            'shift_cash_deposited' => 'Shift Cash Deposited',
            'pos_sale_void' => 'Cashier Receipt Voided',
            'pos_sale_created' => 'New POS Sale Created',
            'billing_adjustment_approved' => 'Billing Adjustment Approved',
            'billing_adjustment_rejected' => 'Billing Adjustment Rejected',
            'print_adjustment_approved' => 'Print Defect Adjustment Approved',
            'print_adjustment_rejected' => 'Print Defect Adjustment Rejected',
            'shift_deposit_verified' => 'Shift Deposit Verified',
            'employee_registration_resubmitted' => 'Candidate Registration Resubmitted',
            'employee_registration_approved' => 'Employee Registration Approved',
            'employee_registration_rejected' => 'Employee Registration Rejected',
            'access_rights_periodic_review' => 'Access Rights Periodic Review (ISO 27001)',
            'account_unlocked' => 'Employee Account Unlocked',
            'work_hours_stored' => 'Manual Work Hours Recorded',
            'work_hours_updated' => 'Manual Work Hours Updated',
            'work_hours_destroyed' => 'Manual Work Hours Deleted',
            'activity_stored' => 'Field Activity Recorded',
            'activity_updated' => 'Field Activity Updated',
            'activity_destroyed' => 'Field Activity Deleted',
        ];

        $actionLabels = $lang === 'en' ? $actionLabelsEn : $actionLabelsId;

        return view('admin.presensi.audit_logs', compact('logs', 'actionLabels', 'lang'));
    }
}
