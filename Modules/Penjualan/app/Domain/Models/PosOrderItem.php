<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class PosOrderItem extends Model
{
    protected $table = 'pos_order_items';

    protected $fillable = [
        'pos_order_id',
        'product_variant_id',
        'qty',
        'unit_price',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'float',
        'discount_amount' => 'float',
        'subtotal' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
