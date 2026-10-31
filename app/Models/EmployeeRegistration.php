<?php

namespace App\Models;

use Database\Factories\EmployeeRegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'created_at',
    ];

    protected $hidden = [
        'password',
        'pin',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public const EXPIRATION_DAYS = 3;

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_employee_id');
    }

    /**
     * Scope untuk mengambil pendaftaran berstatus pending yang masih aktif (belum melebihi masa berlaku).
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActivePending($query, ?int $days = null)
    {
        $limitDays = $days ?? self::EXPIRATION_DAYS;

        return $query->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays($limitDays));
    }

    /**
     * Cek apakah pendaftaran pending sudah melewati batas masa berlaku.
     */
    public function isExpired(?int $days = null): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->status !== 'pending') {
            return false;
        }

        $limitDays = $days ?? self::EXPIRATION_DAYS;

        return $this->created_at ? $this->created_at->lt(now()->subDays($limitDays)) : false;
    }

    /**
     * Tandai seluruh pendaftaran pending yang telah melewati batas hari sebagai 'expired'.
     */
    public static function pruneExpired(?int $days = null): int
    {
        $limitDays = $days ?? self::EXPIRATION_DAYS;
        $threshold = now()->subDays($limitDays);

        return self::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->update([
                'status' => 'expired',
                'rejection_reason' => "Kedaluwarsa otomatis karena tidak diverifikasi dalam waktu {$limitDays} hari.",
            ]);
    }
}
