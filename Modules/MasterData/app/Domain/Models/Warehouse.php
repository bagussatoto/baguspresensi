<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    protected $table = 'warehouses';

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'type', // storage, in_transit
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scopeStorage(Builder $query): Builder
    {
        return $query->where('type', 'storage');
    }

    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where('type', 'in_transit');
    }
}
