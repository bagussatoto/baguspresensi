<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use App\Domains\Attendance\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Model Klaim / Pengajuan Lembur Karyawan.
 */
class OvertimeClaim extends Model
{
    use HasFactory;

    protected $table = 'overtime_claims';

    protected $fillable = [
        'attendance_record_id',
        'employee_id',
        'requested_minutes',
        'approved_minutes',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_note',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'approved_at' => 'datetime',
        'requested_minutes' => 'integer',
        'approved_minutes' => 'integer',
    ];

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
