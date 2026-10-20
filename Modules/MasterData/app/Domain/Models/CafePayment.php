<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CafePayment extends Model
{
    protected $table = 'cafe_payments';

    protected $fillable = [
        'cafe_sale_id',
        'payment_number',
        'payment_method',
        'amount_due',
        'amount_paid',
        'change_amount',
        'payment_reference',
        'status',
        'paid_at',
        'cashier_employee_id',
    ];

    protected $casts = [
        'amount_due' => 'float',
        'amount_paid' => 'float',
        'change_amount' => 'float',
        'paid_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(CafeSale::class, 'cafe_sale_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'cashier_employee_id');
    }
}
