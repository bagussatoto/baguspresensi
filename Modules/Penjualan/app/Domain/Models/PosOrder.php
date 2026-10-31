<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Models\KasTransaction;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Warehouse;

class PosOrder extends Model
{
    protected $table = 'pos_orders';

    protected $fillable = [
        'pos_shift_id',
        'branch_id',
        'warehouse_id',
        'order_number',
        'customer_id',
        'payment_method',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'cash_paid',
        'change_given',
        'cashier_id',
        'kas_transaction_id',
        'snapshot_payload',
    ];

    protected $casts = [
        'payment_method' => PaymentMethodType::class,
        'total_amount' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
        'cash_paid' => 'float',
        'change_given' => 'float',
        'snapshot_payload' => 'array',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(PosShift::class, 'pos_shift_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function kasTransaction(): BelongsTo
    {
        return $this->belongsTo(KasTransaction::class, 'kas_transaction_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class, 'pos_order_id');
    }
}
