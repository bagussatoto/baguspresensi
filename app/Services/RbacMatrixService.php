<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Attendance\Enums\AdminRole;
use App\Models\RbacRolePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\MasterData\Domain\Enums\EmployeeRole;

/**
 * Service Pengelolaan Matriks Hak Akses RBAC Granular (CRUD + Approve) per Jabatan.
 * Mengacu pada dokumen standar Matriks_RBAC_ERP.md & ISO/IEC 27001.
 */
class RbacMatrixService
{
    /**
     * Daftar modul inti ERP beserta deskripsi fungsinya.
     *
     * @return array<string, array{label: string, code: string, desc: string}>
     */
    public function getModules(): array
    {
        return [
            'master_barang' => [
                'label' => 'Data Master Barang & Harga',
                'code' => 'MDM-PRD',
                'desc' => 'Pengelolaan SKU produk, varian, kategori, harga jual, dan batas stok minimum.',
            ],
            'stok_opname' => [
                'label' => 'Stok Opname & Mutasi Gudang',
                'code' => 'INV-OPN',
                'desc' => 'Pencatatan stok fisik berkala, transfer stok antar cabang, dan penyesuaian selisih.',
            ],
            'pembelian' => [
                'label' => 'Faktur Pembelian & Konsinyasi',
                'code' => 'PUR-INV',
                'desc' => 'Order pembelian supplier, penerimaan barang masuk, dan penyelesaian konsinyasi.',
            ],
            'penjualan_pos' => [
                'label' => 'Transaksi Kasir POS & VOID',
                'code' => 'POS-SLS',
                'desc' => 'Penjualan F&B/Billing/Print, pembatalan struk (VOID), serta setoran kas shift.',
            ],
            'jurnal_keuangan' => [
                'label' => 'Jurnal Keuangan & Buku Besar',
                'code' => 'ACC-JRN',
                'desc' => 'Jurnal umum, input jurnal penyesuaian manual, kartu buku besar, dan COA.',
            ],
            'laporan_keuangan' => [
                'label' => 'Laporan Keuangan SAK (L/R, Neraca, Arus Kas)',
                'code' => 'ACC-RPT',
                'desc' => 'Neraca Saldo, Laporan Laba Rugi, Neraca Keuangan, Arus Kas, dan Pivot SAK.',
            ],
            'tutup_buku' => [
                'label' => 'Tutup Buku & Kunci Periode',
                'code' => 'ACC-CLS',
                'desc' => 'Penguncian periode akuntansi bulanan, jurnal penutup, dan audit reopen.',
            ],
            'konfigurasi_erp' => [
                'label' => 'Konfigurasi Sistem, RBAC & Data Purge',
                'code' => 'GRC-SYS',
                'desc' => 'Matriks RBAC, tinjauan akses, audit log WORM, dan pembersihan data simulasi.',
            ],
        ];
    }

    /**
     * Daftar seluruh jabatan (Admin & Operasional Cabang).
     *
     * @return array<string, array{type: string, key: string, label: string, badge: string}>
     */
    public function getRoles(): array
    {
        $roles = [];

        foreach (AdminRole::cases() as $adminRole) {
            $roles[$adminRole->value] = [
                'type' => 'admin',
                'key' => $adminRole->value,
                'label' => $adminRole->label(),
                'badge' => 'Admin ERP',
            ];
        }

        foreach (EmployeeRole::cases() as $empRole) {
            $roles[$empRole->value] = [
                'type' => 'employee',
                'key' => $empRole->value,
                'label' => $empRole->label(),
                'badge' => 'Operasional',
            ];
        }

        return $roles;
    }

    /**
     * Matriks default sesuai prinsip Least Privilege & Segregation of Duties (SoD).
     *
     * @return array<string, array<string, array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool}>>
     */
    public function getDefaultPermissions(): array
    {
        $modules = array_keys($this->getModules());
        $matrix = [];

        foreach ($this->getRoles() as $roleKey => $roleMeta) {
            foreach ($modules as $modKey) {
                $matrix[$roleKey][$modKey] = $this->resolveDefaultRule($roleKey, $modKey);
            }
        }

        return $matrix;
    }

    /**
     * Pastikan tabel memiliki baris default jika masih kosong.
     */
    public function ensureSeeded(): void
    {
        if (! Schema::hasTable('rbac_role_permissions')) {
            return;
        }

        if (RbacRolePermission::query()->count() > 0) {
            return;
        }

        $this->seedDefaultsInternal(null);
    }

    /**
     * Ambil seluruh matriks hak akses terstruktur [role_key][module_key].
     *
     * @return array<string, array<string, array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool}>>
     */
    public function getMatrix(): array
    {
        $this->ensureSeeded();

        $matrix = $this->getDefaultPermissions();
        $rows = RbacRolePermission::query()->get();

        foreach ($rows as $row) {
            if (isset($matrix[$row->role_key][$row->module_key])) {
                $matrix[$row->role_key][$row->module_key] = [
                    'can_create' => (bool) $row->can_create,
                    'can_read' => (bool) $row->can_read,
                    'can_update' => (bool) $row->can_update,
                    'can_delete' => (bool) $row->can_delete,
                    'can_approve' => (bool) $row->can_approve,
                ];
            }
        }

        return $matrix;
    }

    /**
     * Periksa apakah suatu role memiliki izin spesifik pada modul tertentu.
     */
    public function hasPermission(string $roleKey, string $moduleKey, string $action): bool
    {
        $column = match (strtolower($action)) {
            'create', 'c' => 'can_create',
            'read', 'r' => 'can_read',
            'update', 'u' => 'can_update',
            'delete', 'd' => 'can_delete',
            'approve', 'a' => 'can_approve',
            default => null,
        };

        if (! $column) {
            return false;
        }

        if (Schema::hasTable('rbac_role_permissions')) {
            $row = RbacRolePermission::query()
                ->where('role_key', $roleKey)
                ->where('module_key', $moduleKey)
                ->first();

            if ($row) {
                return (bool) $row->{$column};
            }
        }

        return $this->resolveDefaultRule($roleKey, $moduleKey)[$column] ?? false;
    }

    /**
     * Simpan perubahan matriks hak akses ke database dengan proteksi SoD & Audit Log.
     *
     * @param  array<string, array<string, array<string, mixed>>>  $inputPermissions
     */
    public function updateMatrix(array $inputPermissions, ?int $userId = null): void
    {
        $roles = $this->getRoles();
        $modules = $this->getModules();

        DB::transaction(function () use ($inputPermissions, $roles, $modules, $userId): void {
            foreach ($roles as $roleKey => $roleMeta) {
                $isAuditor = $roleKey === AdminRole::Auditor->value;

                foreach ($modules as $modKey => $modMeta) {
                    $flags = $inputPermissions[$roleKey][$modKey] ?? [];

                    $canRead = ! empty($flags['can_read']);
                    $canCreate = $isAuditor ? false : ! empty($flags['can_create']);
                    $canUpdate = $isAuditor ? false : ! empty($flags['can_update']);
                    $canDelete = $isAuditor ? false : ! empty($flags['can_delete']);
                    $canApprove = $isAuditor ? false : ! empty($flags['can_approve']);

                    RbacRolePermission::updateOrCreate(
                        [
                            'role_type' => $roleMeta['type'],
                            'role_key' => $roleKey,
                            'module_key' => $modKey,
                        ],
                        [
                            'can_create' => $canCreate,
                            'can_read' => $canRead,
                            'can_update' => $canUpdate,
                            'can_delete' => $canDelete,
                            'can_approve' => $canApprove,
                            'updated_by_user_id' => $userId,
                        ]
                    );
                }
            }

            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'user_id' => $userId,
                    'auditable_type' => RbacRolePermission::class,
                    'auditable_id' => 1,
                    'action' => 'rbac_matrix_updated',
                    'new_values' => json_encode([
                        'roles_updated' => array_keys($roles),
                        'modules_count' => count($modules),
                        'timestamp' => now()->toIso8601String(),
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'Admin ERP System',
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * Kembalikan seluruh matriks ke standar awal ISO 27001 / SoD.
     */
    public function resetToDefaults(?int $userId = null): void
    {
        DB::transaction(function () use ($userId): void {
            $this->seedDefaultsInternal($userId);

            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'user_id' => $userId,
                    'auditable_type' => RbacRolePermission::class,
                    'auditable_id' => 1,
                    'action' => 'rbac_matrix_reset_defaults',
                    'new_values' => json_encode([
                        'timestamp' => now()->toIso8601String(),
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'Admin ERP System',
                    'created_at' => now(),
                ]);
            }
        });
    }

    private function seedDefaultsInternal(?int $userId): void
    {
        $roles = $this->getRoles();
        $defaults = $this->getDefaultPermissions();

        foreach ($defaults as $roleKey => $modules) {
            $roleType = $roles[$roleKey]['type'] ?? 'employee';
            foreach ($modules as $modKey => $perms) {
                RbacRolePermission::updateOrCreate(
                    [
                        'role_type' => $roleType,
                        'role_key' => $roleKey,
                        'module_key' => $modKey,
                    ],
                    [
                        'can_create' => $perms['can_create'],
                        'can_read' => $perms['can_read'],
                        'can_update' => $perms['can_update'],
                        'can_delete' => $perms['can_delete'],
                        'can_approve' => $perms['can_approve'],
                        'updated_by_user_id' => $userId,
                    ]
                );
            }
        }
    }

    /**
     * @return array{can_create: bool, can_read: bool, can_update: bool, can_delete: bool, can_approve: bool}
     */
    private function resolveDefaultRule(string $roleKey, string $modKey): array
    {
        $all = ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => true];
        $readOnly = ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false];
        $none = ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false];

        if ($roleKey === AdminRole::SuperAdmin->value) {
            return $all;
        }

        if ($roleKey === AdminRole::Auditor->value) {
            return $readOnly;
        }

        if ($roleKey === AdminRole::BranchAdmin->value) {
            return match ($modKey) {
                'konfigurasi_erp', 'tutup_buku' => $readOnly,
                default => ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => false, 'can_approve' => true],
            };
        }

        if ($roleKey === EmployeeRole::SUPERVISOR->value) {
            return match ($modKey) {
                'master_barang', 'stok_opname', 'pembelian', 'penjualan_pos' => ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => true],
                'laporan_keuangan' => $readOnly,
                default => $none,
            };
        }

        if ($roleKey === EmployeeRole::OTORISATOR->value) {
            return match ($modKey) {
                'penjualan_pos' => ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => true, 'can_approve' => true],
                'master_barang', 'stok_opname' => $readOnly,
                default => $none,
            };
        }

        if ($roleKey === EmployeeRole::KASIR->value || $roleKey === EmployeeRole::OPERATOR->value) {
            return match ($modKey) {
                'penjualan_pos' => ['can_create' => true, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false],
                'master_barang' => $readOnly,
                default => $none,
            };
        }

        if ($roleKey === EmployeeRole::SALESMAN->value || $roleKey === EmployeeRole::DRIVER->value) {
            return match ($modKey) {
                'stok_opname', 'master_barang' => $readOnly,
                default => $none,
            };
        }

        return $none;
    }
}
