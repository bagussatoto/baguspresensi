<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'total_shift_hours',
        'total_fnb_sales',
        'commission_fnb_rate',
        'commission_fnb_amount',
        'total_billing_sales',
        'commission_billing_rate',
        'commission_billing_amount',
        'bonus_shift_amount',
        'total_commission',
        'status',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'total_shift_hours' => 'integer',
        'total_fnb_sales' => 'float',
        'commission_fnb_rate' => 'float',
        'commission_fnb_amount' => 'float',
        'total_billing_sales' => 'float',
        'commission_billing_rate' => 'float',
        'commission_billing_amount' => 'float',
        'bonus_shift_amount' => 'float',
        'total_commission' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
