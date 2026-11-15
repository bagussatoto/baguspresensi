<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\RbacMatrixService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRbacMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_rbac_matrix_and_auto_seed_defaults(): void
    {
        $response = $this->get(route('admin.erp.rbac-matrix'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Matriks Hak Akses RBAC Granular');
        $response->assertSee('Data Master Barang &amp; Harga', false);
        $response->assertSee('Tutup Buku &amp; Kunci Periode', false);
        $response->assertSee('Super Administrator (Pusat)');
        $response->assertSee('Kasir Retail / Toko');
        $response->assertSee('Unduh CSV');

        $this->assertDatabaseHas('rbac_role_permissions', [
            'role_key' => 'super_admin',
            'module_key' => 'tutup_buku',
            'can_approve' => true,
        ]);

        $this->assertDatabaseHas('rbac_role_permissions', [
            'role_key' => 'kasir',
            'module_key' => 'penjualan_pos',
            'can_create' => true,
            'can_delete' => false,
        ]);
    }

    public function test_admin_can_update_rbac_matrix_and_enforce_auditor_readonly(): void
    {
        $this->get(route('admin.erp.rbac-matrix'));

        $payload = [
            'permissions' => [
                'kasir' => [
                    'penjualan_pos' => ['can_create' => '1', 'can_read' => '1'],
                    'laporan_keuangan' => ['can_read' => '1'],
                ],
                'auditor' => [
                    'tutup_buku' => [
                        'can_create' => '1',
                        'can_read' => '1',
                        'can_update' => '1',
                        'can_delete' => '1',
                        'can_approve' => '1',
                    ],
                ],
            ],
        ];

        $response = $this->post(route('admin.erp.rbac-matrix.update'), $payload);

        $response->assertRedirect(route('admin.erp.rbac-matrix'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rbac_role_permissions', [
            'role_key' => 'kasir',
            'module_key' => 'laporan_keuangan',
            'can_read' => true,
        ]);

        // Auditor wajib tetap Read-Only meskipun payload mencoba memberi izin tulis/hapus/approve
        $this->assertDatabaseHas('rbac_role_permissions', [
            'role_key' => 'auditor',
            'module_key' => 'tutup_buku',
            'can_read' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
        ]);

        $rbac = app(RbacMatrixService::class);
        $this->assertTrue($rbac->hasPermission('kasir', 'laporan_keuangan', 'read'));
        $this->assertFalse($rbac->hasPermission('auditor', 'tutup_buku', 'approve'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rbac_matrix_updated',
        ]);
    }

    public function test_admin_can_reset_rbac_matrix_to_defaults(): void
    {
        $this->post(route('admin.erp.rbac-matrix.update'), [
            'permissions' => [
                'kasir' => [
                    'laporan_keuangan' => ['can_read' => '1'],
                ],
            ],
        ]);

        $response = $this->post(route('admin.erp.rbac-matrix.reset'));
        $response->assertRedirect(route('admin.erp.rbac-matrix'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rbac_role_permissions', [
            'role_key' => 'kasir',
            'module_key' => 'laporan_keuangan',
            'can_read' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'rbac_matrix_reset_defaults',
        ]);
    }

    public function test_admin_can_export_rbac_matrix_csv(): void
    {
        $response = $this->get(route('admin.erp.rbac-matrix', ['export' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
