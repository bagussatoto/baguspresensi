<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Attendance\Models\OvertimeClaim;
use App\Domains\Leave\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\MasterData\Domain\Enums\EmployeeRole;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'username',
        'role',
        'position',
        'jabatan',
        'content_permission',
        'seniority',
        'evaluation',
        'employment_status',
        'access_rights',
        'max_discount_approval_percent',
        'phone',
        'email',
        'password',
        'pin',
        'is_active',
        'deactivated_at',
        'deactivated_by',
        'deactivation_reason',
        'last_access_review_at',
        'last_login_at',
        'default_work_type',
        'default_contract_hours',
        'default_cost_center',
    ];

    protected $hidden = [
        'password',
        'pin',
    ];

    protected $casts = [
        'role' => EmployeeRole::class,
        'max_discount_approval_percent' => 'float',
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
        'last_access_review_at' => 'datetime',
        'last_login_at' => 'datetime',
        'access_rights' => 'array',
    ];

    public function deactivator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'deactivated_by');
    }

    public function isDeactivated(): bool
    {
        return ! $this->is_active;
    }

    public function isPrivileged(): bool
    {
        return in_array($this->role, [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR], true);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function overtimeClaims(): HasMany
    {
        return $this->hasMany(OvertimeClaim::class, 'employee_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class, 'employee_id');
    }

    /**
     * Cek apakah karyawan berwenang mengotorisasi diskon tertentu.
     */
    public function canAuthorizeDiscount(float $discountPercent): bool
    {
        if (! in_array($this->role, [EmployeeRole::OTORISATOR, EmployeeRole::SUPERVISOR], true)) {
            return false;
        }

        return $discountPercent <= (float) $this->max_discount_approval_percent;
    }

    /**
     * Cek apakah karyawan berwenang mengotorisasi pembatalan nota (void).
     */
    public function canAuthorizeVoid(): bool
    {
        return in_array($this->role, [EmployeeRole::SUPERVISOR, EmployeeRole::OTORISATOR], true);
    }
}
