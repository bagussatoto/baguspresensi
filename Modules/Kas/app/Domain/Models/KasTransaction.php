<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Supplier;

class KasTransaction extends Model
{
    protected $table = 'kas_transactions';

    protected $fillable = [
        'transaction_number',
        'kas_account_id',
        'branch_id',
        'transaction_type',
        'payment_method',
        'amount',
        'customer_id',
        'supplier_id',
        'user_id',
        'reference_number',
        'description',
        'transaction_date',
        'snapshot_payload',
    ];

    protected $casts = [
        'transaction_type' => KasTransactionType::class,
        'payment_method' => PaymentMethodType::class,
        'amount' => 'float',
        'transaction_date' => 'date',
        'snapshot_payload' => 'array',
    ];

    public function kasAccount(): BelongsTo
    {
        return $this->belongsTo(KasAccount::class, 'kas_account_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(KasTransactionLine::class, 'kas_transaction_id');
    }
}
