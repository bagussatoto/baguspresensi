<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\MasterData\Domain\Models\Warehouse;

class GoodsReceipt extends Model
{
    protected $table = 'goods_receipts';

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'delivery_note_number',
        'receipt_date',
        'received_by',
        'notes',
        'snapshot_payload',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'snapshot_payload' => 'array',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptItem::class, 'goods_receipt_id');
    }
}
