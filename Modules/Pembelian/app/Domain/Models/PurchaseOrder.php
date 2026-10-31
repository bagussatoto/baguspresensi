<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\Pembelian\Domain\Enums\PurchaseOrderStatus;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'branch_id',
        'status',
        'order_date',
        'expected_delivery_date',
        'total_amount',
        'tax_amount',
        'net_amount',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class, 'purchase_order_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class, 'purchase_order_id');
    }

    /**
     * Hitung total nilai pesanan dari seluruh items.
     */
    public function recalculateTotals(): void
    {
        $this->total_amount = (float) $this->items()->sum('subtotal');
        // Pajak PPN 11% jika ada atau disesuaikan
        $this->net_amount = $this->total_amount + $this->tax_amount;
        $this->save();
    }
}
