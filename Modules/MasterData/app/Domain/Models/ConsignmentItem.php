<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Rincian SKU Barang pada Kontrak Konsinyasi Cabang.
 */
class ConsignmentItem extends Model
{
    protected $table = 'consignment_items';

    protected $fillable = [
        'consignment_id',
        'product_variant_id',
        'unit_price',
        'qty_consigned',
        'qty_sold',
        'qty_returned',
        'qty_remaining',
        'subtotal_consigned',
        'subtotal_sold',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'qty_consigned' => 'integer',
        'qty_sold' => 'integer',
        'qty_returned' => 'integer',
        'qty_remaining' => 'integer',
        'subtotal_consigned' => 'float',
        'subtotal_sold' => 'float',
    ];

    public function consignment(): BelongsTo
    {
        return $this->belongsTo(Consignment::class, 'consignment_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
