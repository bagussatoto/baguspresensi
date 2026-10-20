<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CafeSaleItem extends Model
{
    protected $table = 'cafe_sale_items';

    protected $fillable = [
        'cafe_sale_id',
        'product_variant_id',
        'item_type',
        'item_name',
        'qty',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(CafeSale::class, 'cafe_sale_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
