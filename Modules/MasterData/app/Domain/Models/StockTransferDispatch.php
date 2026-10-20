<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferDispatch extends Model
{
    protected $table = 'stock_transfer_dispatches';

    protected $fillable = [
        'transfer_code',
        'from_warehouse_id',
        'to_warehouse_id',
        'product_variant_id',
        'qty',
        'status',
        'dispatched_at',
        'received_at',
        'operator_notes',
    ];

    protected $casts = [
        'qty' => 'integer',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
