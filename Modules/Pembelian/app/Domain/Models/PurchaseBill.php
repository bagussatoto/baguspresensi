<?php

declare(strict_types=1);

namespace Modules\Pembelian\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\Pembelian\Domain\Enums\PurchaseBillStatus;

class PurchaseBill extends Model
{
    protected $table = 'purchase_bills';

    protected $fillable = [
        'bill_number',
        'purchase_order_id',
        'supplier_id',
        'status',
        'bill_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'remaining_balance',
        'notes',
    ];

    protected $casts = [
        'status' => PurchaseBillStatus::class,
        'bill_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'remaining_balance' => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseBillItem::class, 'purchase_bill_id');
    }

    /**
     * Catat pembayaran terhadap tagihan ini.
     */
    public function recordPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->remaining_balance = max(0.0, $this->total_amount - $this->paid_amount);

        if ($this->remaining_balance <= 0.0) {
            $this->status = PurchaseBillStatus::Paid;
        } elseif ($this->paid_amount > 0.0) {
            $this->status = PurchaseBillStatus::Partial;
        }

        $this->save();
    }
}
