<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Kontrak & Transaksi Konsinyasi Barang Multi-Cabang.
 * (Inward = Titipan Vendor/Supplier ke Toko, Outward = Titipan Toko ke Reseller/Mitra).
 */
class Consignment extends Model
{
    protected $table = 'consignments';

    protected $fillable = [
        'consignment_number',
        'branch_id',
        'type',
        'supplier_id',
        'customer_id',
        'partner_name',
        'start_date',
        'due_settlement_date',
        'status',
        'total_items_count',
        'total_consigned_value',
        'total_sold_value',
        'total_settled_amount',
        'remaining_settlement_due',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_settlement_date' => 'date',
        'total_items_count' => 'integer',
        'total_consigned_value' => 'float',
        'total_sold_value' => 'float',
        'total_settled_amount' => 'float',
        'remaining_settlement_due' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsignmentItem::class, 'consignment_id');
    }

    public function isInward(): bool
    {
        return $this->type === 'inward';
    }

    public function isOutward(): bool
    {
        return $this->type === 'outward';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settled' || $this->remaining_settlement_due <= 0.0;
    }
}
