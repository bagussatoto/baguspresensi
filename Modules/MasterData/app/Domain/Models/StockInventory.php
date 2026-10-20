<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInventory extends Model
{
    protected $table = 'stock_inventories';

    protected $fillable = [
        'warehouse_id',
        'product_variant_id',
        'qty_on_hand',
        'safety_stock',
    ];

    protected $casts = [
        'qty_on_hand' => 'integer',
        'safety_stock' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function isLowStock(): bool
    {
        return $this->qty_on_hand <= $this->safety_stock;
    }
}
