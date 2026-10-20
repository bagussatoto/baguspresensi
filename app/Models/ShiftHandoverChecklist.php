<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Attendance\Models\AttendanceRecord;
use App\Domains\Employee\Models\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

class ShiftHandoverChecklist extends Model
{
    use HasFactory;

    protected $table = 'shift_handover_checklists';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_id',
        'attendance_record_id',
        'check_date',
        'paper_a4',
        'paper_a4_qty',
        'paper_f4',
        'paper_f4_qty',
        'drink_free_checked',
        'drink_items',
        'drink_notes',
        'food_free_checked',
        'food_items',
        'food_notes',
        'hardware_status',
        'hardware_checklist',
        'hardware_notes',
        'billing_shift_income',
        'billing_verified',
        'billing_details',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'paper_a4' => 'boolean',
        'paper_a4_qty' => 'integer',
        'paper_f4' => 'boolean',
        'paper_f4_qty' => 'integer',
        'drink_free_checked' => 'boolean',
        'drink_items' => 'array',
        'food_free_checked' => 'boolean',
        'food_items' => 'array',
        'hardware_checklist' => 'array',
        'billing_shift_income' => 'decimal:2',
        'billing_verified' => 'boolean',
        'billing_details' => 'array',
        'check_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
