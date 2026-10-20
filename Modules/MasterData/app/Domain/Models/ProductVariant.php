<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'name',
        'weight_grams',
        'base_price',
        'cost_price',
        'is_active',
    ];

    protected $casts = [
        'weight_grams' => 'integer',
        'base_price' => 'float',
        'cost_price' => 'float',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variant_attribute_values',
            'variant_id',
            'attribute_value_id'
        )->withTimestamps();
    }

    public function pricelistItems(): HasMany
    {
        return $this->hasMany(PricelistItem::class, 'product_variant_id');
    }
}
