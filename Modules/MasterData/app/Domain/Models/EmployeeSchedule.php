<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $table = 'employee_schedules';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_name',
        'shift_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'shift_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
