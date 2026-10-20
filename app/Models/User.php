<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Attendance\Enums\AdminRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\MasterData\Domain\Models\Branch;

#[Fillable(['name', 'email', 'password', 'role', 'assigned_branch_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => AdminRole::class,
        ];
    }

    public function assignedBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'assigned_branch_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === AdminRole::SuperAdmin || $this->role === null;
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === AdminRole::BranchAdmin;
    }

    public function isAuditor(): bool
    {
        return $this->role === AdminRole::Auditor;
    }
}
