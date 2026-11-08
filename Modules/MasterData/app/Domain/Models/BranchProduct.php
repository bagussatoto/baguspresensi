<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model BranchProduct: The Spoke dalam Arsitektur MDM Hub-and-Spoke.
 * Menyimpan konfigurasi dan lokalisasi produk spesifik pada masing-masing cabang.
 */
class BranchProduct extends Model
{
    protected $table = 'branch_products';

    protected $fillable = [
        'branch_id',
        'product_variant_id',
        'bin_location',
        'min_stock',
        'max_stock',
        'reorder_point',
        'is_locally_active',
    ];

    protected $casts = [
        'min_stock' => 'float',
        'max_stock' => 'float',
        'reorder_point' => 'float',
        'is_locally_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Cek apakah stok fisik cabang saat ini menyentuh atau berada di bawah Reorder Point (ROP).
     */
    public function needsReorder(float $currentStock): bool
    {
        return $this->is_locally_active && $this->reorder_point > 0 && $currentStock <= $this->reorder_point;
    }
}
