<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcStation extends Model
{
    protected $table = 'pc_stations';

    protected $fillable = [
        'branch_id',
        'station_code',
        'station_type',
        'hourly_rate',
        'status',
        'current_user_name',
        'started_at',
        'duration_minutes',
        'billing_amount',
    ];

    protected $casts = [
        'hourly_rate' => 'float',
        'billing_amount' => 'float',
        'started_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }
}
