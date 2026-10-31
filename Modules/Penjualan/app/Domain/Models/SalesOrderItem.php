<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';

    protected $fillable = [
        'sales_order_id',
        'product_variant_id',
        'qty',
        'unit_price',
        'discount_amount',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'float',
        'discount_amount' => 'float',
        'subtotal' => 'float',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
