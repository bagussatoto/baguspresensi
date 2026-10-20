<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeSale extends Model
{
    protected $table = 'cafe_sales';

    protected $fillable = [
        'invoice_number',
        'branch_id',
        'employee_id',
        'customer_name',
        'pc_station_id',
        'total_billing',
        'total_fnb',
        'gross_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'amount_paid',
        'change_amount',
        'payment_reference',
        'cashier_notes',
        'payment_method',
        'payment_status',
        'void_by',
        'void_reason',
        'void_at',
        'discount_percent',
        'discount_authorized_by',
    ];

    protected $casts = [
        'total_billing' => 'float',
        'total_fnb' => 'float',
        'gross_amount' => 'float',
        'discount_amount' => 'float',
        'discount_percent' => 'float',
        'tax_amount' => 'float',
        'net_amount' => 'float',
        'amount_paid' => 'float',
        'change_amount' => 'float',
        'void_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function voidBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'void_by');
    }

    public function discountAuthorizedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'discount_authorized_by');
    }

    public function pcStation(): BelongsTo
    {
        return $this->belongsTo(PcStation::class, 'pc_station_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CafeSaleItem::class, 'cafe_sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CafePayment::class, 'cafe_sale_id');
    }
}
