<?php

namespace App\Models;

use Database\Factories\EmployeeRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Employee;

class EmployeeRegistration extends Model
{
    /** @use HasFactory<EmployeeRegistrationFactory> */
    use HasFactory;

    protected $table = 'employee_registrations';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'username',
        'password',
        'pin',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'created_employee_id',
        'ip_address',
        'user_agent',
    ];

    protected $hidden = [
        'password',
        'pin',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_employee_id');
    }
}
