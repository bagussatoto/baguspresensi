<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_variant_id',
        'qty_ordered',
        'qty_received',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'qty_ordered' => 'integer',
        'qty_received' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Hitung sisa qty yang belum diterima.
     */
    public function remainingQty(): int
    {
        return max(0, $this->qty_ordered - $this->qty_received);
    }
}
