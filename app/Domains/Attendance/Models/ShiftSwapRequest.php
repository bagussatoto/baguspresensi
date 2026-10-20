<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Employee\Models\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

/**
 * Model Permohonan Tukar Shift Antar Karyawan (Dua Arah).
 */
class ShiftSwapRequest extends Model
{
    use HasFactory;

    protected $table = 'shift_swap_requests';

    protected $fillable = [
        'branch_id',
        'requester_id',
        'target_employee_id',
        'requester_shift_id',
        'requester_date',
        'target_shift_id',
        'target_date',
        'reason',
        'peer_note',
        'peer_responded_at',
        'status',
        'approved_by',
        'approved_at',
        'rejection_note',
    ];

    protected $casts = [
        'requester_date' => 'date',
        'target_date' => 'date',
        'peer_responded_at' => 'datetime',
        'approved_at' => 'datetime',
        'status' => ShiftSwapStatus::class,
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function targetEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'target_employee_id');
    }

    public function requesterShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'requester_shift_id');
    }

    public function targetShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'target_shift_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function isPendingPeer(): bool
    {
        return $this->status === ShiftSwapStatus::PendingPeer;
    }

    public function isPendingSupervisor(): bool
    {
        return $this->status === ShiftSwapStatus::PendingSupervisor;
    }

    public function isApproved(): bool
    {
        return $this->status === ShiftSwapStatus::Approved;
    }
}
