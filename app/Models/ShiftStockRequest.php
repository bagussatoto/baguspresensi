<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Employee\Models\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;

class ShiftStockRequest extends Model
{
    use HasFactory;

    protected $table = 'shift_stock_requests';

    protected $fillable = [
        'request_number',
        'employee_id',
        'branch_id',
        'shift_id',
        'item_category',
        'item_name',
        'qty_requested',
        'unit',
        'urgency',
        'notes',
        'status',
        'fulfilled_at',
    ];

    protected $casts = [
        'qty_requested' => 'integer',
        'fulfilled_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
