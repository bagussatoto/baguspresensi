<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Models;

use App\Domains\Attendance\Services\AttendanceSettingService;
use App\Models\User;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model Audit Trail Kepatuhan ISO 27001 & GRC.
 * Merekam seluruh jejak mutasi kredensial, perubahan status, dan approval presensi.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Proteksi WORM (Write Once, Read Many) - Immutability Guard
        static::updating(function (): void {
            $wormActive = (bool) app(AttendanceSettingService::class)
                ->get('audit.immutability_worm_guard', true);

            if ($wormActive) {
                throw new BadMethodCallException('AuditLog bersifat WORM (Write Once, Read Many). Mutasi data dilarang.');
            }
        });

        static::deleting(function (): void {
            $wormActive = (bool) app(AttendanceSettingService::class)
                ->get('audit.immutability_worm_guard', true);

            if ($wormActive) {
                throw new BadMethodCallException('AuditLog bersifat WORM (Write Once, Read Many). Penghapusan dilarang demi kepatuhan ISO 27001.');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
