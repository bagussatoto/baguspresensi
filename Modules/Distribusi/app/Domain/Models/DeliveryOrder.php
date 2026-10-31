<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Warehouse;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_orders';

    protected $fillable = [
        'delivery_order_number',
        'warehouse_id',
        'customer_id',
        'reference_number',
        'status',
        'delivery_date',
        'recipient_name',
        'shipping_address',
        'driver_name',
        'vehicle_plate',
        'notes',
        'dispatched_by',
        'snapshot_payload',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'snapshot_payload' => 'array',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class, 'delivery_order_id');
    }
}
