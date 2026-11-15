<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RbacMatrixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Manajemen Matriks Hak Akses RBAC Granular (CRUD + Approve) per Jabatan.
 */
class AdminRbacController extends Controller
{
    public function __construct(
        protected RbacMatrixService $rbacService
    ) {}

    /**
     * Tampilkan layar Matriks Hak Akses RBAC atau ekspor ke CSV.
     */
    public function index(Request $request): View|JsonResponse|StreamedResponse
    {
        $modules = $this->rbacService->getModules();
        $roles = $this->rbacService->getRoles();
        $matrix = $this->rbacService->getMatrix();

        $adminRoles = array_filter($roles, fn ($r) => $r['type'] === 'admin');
        $employeeRoles = array_filter($roles, fn ($r) => $r['type'] === 'employee');

        if ($request->query('export') === 'csv') {
            $filename = 'matriks_rbac_erp_'.date('Ymd').'.csv';

            return response()->streamDownload(function () use ($roles, $modules, $matrix) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['MATRIKS HAK AKSES RBAC GRANULAR ERP MAYA GRAHA KENCANA']);
                fputcsv($handle, ['Tanggal Cetak', date('Y-m-d H:i:s')]);
                fputcsv($handle, []);
                fputcsv($handle, ['Tipe Role', 'Jabatan / Role', 'Kode Modul', 'Nama Modul', 'Create', 'Read', 'Update', 'Delete', 'Approve']);

                foreach ($roles as $roleKey => $roleMeta) {
                    foreach ($modules as $modKey => $modMeta) {
                        $p = $matrix[$roleKey][$modKey] ?? [];
                        fputcsv($handle, [
                            $roleMeta['badge'],
                            $roleMeta['label'],
                            $modMeta['code'],
                            $modMeta['label'],
                            ! empty($p['can_create']) ? 'YA' : '-',
                            ! empty($p['can_read']) ? 'YA' : '-',
                            ! empty($p['can_update']) ? 'YA' : '-',
                            ! empty($p['can_delete']) ? 'YA' : '-',
                            ! empty($p['can_approve']) ? 'YA' : '-',
                        ]);
                    }
                }

                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'modules' => $modules,
                'roles' => $roles,
                'matrix' => $matrix,
            ]);
        }

        return view('admin.erp.rbac_matrix', compact(
            'modules',
            'roles',
            'adminRoles',
            'employeeRoles',
            'matrix'
        ));
    }

    /**
     * Simpan pembaruan matriks hak akses RBAC.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $permissions = $request->input('permissions', []);
        if (! is_array($permissions)) {
            $permissions = [];
        }

        $this->rbacService->updateMatrix($permissions, auth()->id());

        $msg = 'Matriks Hak Akses RBAC Granular berhasil diperbarui & dicatat ke Audit Log WORM.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.erp.rbac-matrix')->with('success', $msg);
    }

    /**
     * Kembalikan matriks RBAC ke konfigurasi default ISO 27001 / SoD.
     */
    public function resetDefaults(Request $request): RedirectResponse|JsonResponse
    {
        $this->rbacService->resetToDefaults(auth()->id());

        $msg = 'Matriks Hak Akses RBAC berhasil dikembalikan ke standar default SoD & Least Privilege.';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.erp.rbac-matrix')->with('success', $msg);
    }
}
