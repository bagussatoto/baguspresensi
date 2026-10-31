<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\Penjualan\Domain\Enums\SalesOrderStatus;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';

    protected $fillable = [
        'order_number',
        'customer_id',
        'branch_id',
        'warehouse_id',
        'status',
        'order_date',
        'payment_term_days',
        'total_amount',
        'tax_amount',
        'net_amount',
        'notes',
        'created_by',
        'confirmed_by',
        'snapshot_payload',
    ];

    protected $casts = [
        'status' => SalesOrderStatus::class,
        'order_date' => 'date',
        'payment_term_days' => 'integer',
        'total_amount' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
        'snapshot_payload' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class, 'sales_order_id');
    }
}
