<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use App\Domains\Employee\Models\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Model Roster Jadwal ERP (Pemisahan Atom Waktu, Roster Konteks, dan Biaya).
 */
class RosterJadwal extends Model
{
    protected $table = 'roster_jadwals';

    protected $fillable = [
        'employee_id',
        'work_date',
        'work_type',
        'shift_id',
        'contract_hours',
        'cost_center',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'contract_hours' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'roster_id');
    }

    public function isFlexible(): bool
    {
        return $this->work_type === 'flexible';
    }

    public function isShift(): bool
    {
        return $this->work_type === 'shift';
    }
}
