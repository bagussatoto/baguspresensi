<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Employee\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Model Rekaman Presensi Harian Karyawan.
 */
class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_id',
        'work_date',
        'clock_in_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_in_distance_meters',
        'clock_in_photo_path',
        'clock_out_at',
        'clock_out_lat',
        'clock_out_lng',
        'clock_out_distance_meters',
        'clock_out_photo_path',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'duration_minutes',
        'verification_method',
        'device_info',
        'ip_address',
        'is_mock_location_detected',
        'notes',
        'work_type',
        'effective_minutes',
        'cost_center',
        'roster_id',
        'overtime_status',
        'overtime_requested_minutes',
        'overtime_approved_minutes',
        'overtime_reason',
        'overtime_requested_at',
        'overtime_approved_at',
        'overtime_approved_by',
        'overtime_admin_note',
        'early_leave_approved',
        'early_leave_reason',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'clock_in_lat' => 'float',
        'clock_in_lng' => 'float',
        'clock_out_lat' => 'float',
        'clock_out_lng' => 'float',
        'clock_in_distance_meters' => 'float',
        'clock_out_distance_meters' => 'float',
        'status' => AttendanceStatus::class,
        'verification_method' => VerificationMethod::class,
        'device_info' => 'array',
        'is_mock_location_detected' => 'boolean',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'duration_minutes' => 'integer',
        'effective_minutes' => 'integer',
        'overtime_requested_minutes' => 'integer',
        'overtime_approved_minutes' => 'integer',
        'overtime_requested_at' => 'datetime',
        'overtime_approved_at' => 'datetime',
        'early_leave_approved' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function rosterJadwal(): BelongsTo
    {
        return $this->belongsTo(RosterJadwal::class, 'roster_id');
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overtime_approved_by');
    }

    public function overtimeClaim(): HasOne
    {
        return $this->hasOne(OvertimeClaim::class, 'attendance_record_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class, 'attendance_record_id');
    }

    public function isFlexible(): bool
    {
        return $this->work_type === 'flexible';
    }

    public function isShift(): bool
    {
        return $this->work_type === 'shift';
    }

    public function isOvertimeApproved(): bool
    {
        return $this->overtime_status === 'approved';
    }

    public function isOvertimePending(): bool
    {
        return $this->overtime_status === 'pending';
    }

    public function isOvertimeRejected(): bool
    {
        return $this->overtime_status === 'rejected';
    }

    public function isEarlyLeaveApproved(): bool
    {
        return (bool) $this->early_leave_approved;
    }
}
