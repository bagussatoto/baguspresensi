<?php

declare(strict_types=1);

namespace App\Domains\Employee\Models;

use App\Domains\Attendance\Models\AttendanceRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Template Shift Kerja Karyawan & Operator Warnet.
 */
class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'code',
        'name',
        'start_time',
        'end_time',
        'is_overnight',
        'grace_period_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_overnight' => 'boolean',
        'grace_period_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'shift_id');
    }
}
