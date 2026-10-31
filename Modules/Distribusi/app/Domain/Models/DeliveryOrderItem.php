<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class DeliveryOrderItem extends Model
{
    protected $table = 'delivery_order_items';

    protected $fillable = [
        'delivery_order_id',
        'product_variant_id',
        'qty',
        'notes',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
