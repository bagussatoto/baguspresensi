<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricelistItem extends Model
{
    protected $table = 'pricelist_items';

    protected $fillable = [
        'pricelist_id',
        'product_variant_id',
        'min_qty',
        'unit_price',
        'discount_percent',
    ];

    protected $casts = [
        'min_qty' => 'integer',
        'unit_price' => 'float',
        'discount_percent' => 'float',
    ];

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
