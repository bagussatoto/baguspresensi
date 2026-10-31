<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Distribusi\Domain\Enums\TransferStatus;
use Modules\MasterData\Domain\Models\Warehouse;

class StockTransfer extends Model
{
    protected $table = 'stock_transfers';

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'in_transit_warehouse_id',
        'status',
        'dispatch_date',
        'received_date',
        'driver_name',
        'vehicle_plate',
        'notes',
        'dispatched_by',
        'received_by',
        'snapshot_payload',
    ];

    protected $casts = [
        'status' => TransferStatus::class,
        'dispatch_date' => 'date',
        'received_date' => 'date',
        'snapshot_payload' => 'array',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function inTransitWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'in_transit_warehouse_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'stock_transfer_id');
    }
}
