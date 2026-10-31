<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class GoodsReceiptItem extends Model
{
    protected $table = 'goods_receipt_items';

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_variant_id',
        'qty_received',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'qty_received' => 'integer',
        'unit_cost' => 'float',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Total nilai penerimaan item ini.
     */
    public function totalCost(): float
    {
        return (float) $this->qty_received * (float) $this->unit_cost;
    }
}
