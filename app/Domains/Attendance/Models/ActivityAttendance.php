<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Model Rekaman Kehadiran Kegiatan Staf (Meeting, Pelatihan, General Cleaning GC).
 */
class ActivityAttendance extends Model
{
    use HasFactory;

    protected $table = 'activity_attendances';

    protected $fillable = [
        'activity_type',
        'title',
        'branch_id',
        'employee_id',
        'activity_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function isMeeting(): bool
    {
        return strtolower($this->activity_type) === 'meeting';
    }

    public function isPelatihan(): bool
    {
        return strtolower($this->activity_type) === 'pelatihan';
    }

    public function isGc(): bool
    {
        return strtolower($this->activity_type) === 'gc';
    }

    public function isBo(): bool
    {
        return strtolower($this->activity_type) === 'bo';
    }

    public function typeLabel(): string
    {
        return match (strtolower($this->activity_type)) {
            'meeting' => 'Rapat / Meeting',
            'pelatihan' => 'Pelatihan / Training',
            'bo' => 'Presensi Back Office (BO)',
            'gc' => 'General Cleaning (GC)',
            default => ucfirst($this->activity_type),
        };
    }

    public function typeBadgeClass(): string
    {
        return match (strtolower($this->activity_type)) {
            'meeting' => 'badge-sky',
            'pelatihan' => 'badge-emerald',
            'bo' => 'badge-indigo',
            'gc' => 'badge-amber',
            default => 'badge-sky',
        };
    }
}
