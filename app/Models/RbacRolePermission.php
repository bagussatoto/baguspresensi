<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Matriks Hak Akses Granular (CRUD + Approve) per Jabatan & Modul ERP.
 */
class RbacRolePermission extends Model
{
    use HasFactory;

    protected $table = 'rbac_role_permissions';

    protected $fillable = [
        'role_type',
        'role_key',
        'module_key',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
        'can_approve',
        'updated_by_user_id',
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_read' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
        'can_approve' => 'boolean',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
