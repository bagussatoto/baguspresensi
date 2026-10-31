<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kas\Domain\Enums\KasAccountType;
use Modules\MasterData\Domain\Models\Branch;

class KasAccount extends Model
{
    protected $table = 'kas_accounts';

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'type',
        'current_balance',
        'bank_name',
        'account_number',
        'account_holder',
        'is_active',
    ];

    protected $casts = [
        'type' => KasAccountType::class,
        'current_balance' => 'float',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(KasTransaction::class, 'kas_account_id');
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(KasTransfer::class, 'from_kas_account_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(KasTransfer::class, 'to_kas_account_id');
    }

    /**
     * Memeriksa apakah saldo akun mencukupi untuk nominal yang diminta.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->current_balance >= $amount;
    }
}
