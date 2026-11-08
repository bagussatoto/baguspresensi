<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model StockOpnameItem: Rincian Hasil Hitung Fisik per Rak / SKU Produk.
 */
class StockOpnameItem extends Model
{
    protected $table = 'stock_opname_items';

    protected $fillable = [
        'stock_opname_id',
        'product_variant_id',
        'bin_location',
        'qty_system',
        'qty_physical',
        'qty_variance',
        'unit_cost',
        'total_variance_cost',
        'notes',
    ];

    protected $casts = [
        'qty_system' => 'integer',
        'qty_physical' => 'integer',
        'qty_variance' => 'integer',
        'unit_cost' => 'float',
        'total_variance_cost' => 'float',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function hasDiscrepancy(): bool
    {
        return $this->qty_variance !== 0;
    }
}
