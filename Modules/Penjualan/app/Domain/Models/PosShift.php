<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\MasterData\Domain\Models\Branch;
use Modules\Penjualan\Domain\Enums\ShiftStatus;

class PosShift extends Model
{
    protected $table = 'pos_shifts';

    protected $fillable = [
        'branch_id',
        'kas_account_id',
        'cashier_id',
        'shift_number',
        'status',
        'opened_at',
        'closed_at',
        'opening_cash',
        'expected_cash',
        'actual_cash',
        'cash_difference',
        'total_sales',
        'total_cash_sales',
        'total_non_cash_sales',
        'notes',
    ];

    protected $casts = [
        'status' => ShiftStatus::class,
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'float',
        'expected_cash' => 'float',
        'actual_cash' => 'float',
        'cash_difference' => 'float',
        'total_sales' => 'float',
        'total_cash_sales' => 'float',
        'total_non_cash_sales' => 'float',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function kasAccount(): BelongsTo
    {
        return $this->belongsTo(KasAccount::class, 'kas_account_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'pos_shift_id');
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::Open;
    }
}
