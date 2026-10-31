<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasTransfer extends Model
{
    protected $table = 'kas_transfers';

    protected $fillable = [
        'transfer_number',
        'from_kas_account_id',
        'to_kas_account_id',
        'amount',
        'admin_fee',
        'transfer_date',
        'reference_number',
        'description',
        'authorized_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'admin_fee' => 'float',
        'transfer_date' => 'date',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(KasAccount::class, 'from_kas_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(KasAccount::class, 'to_kas_account_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /**
     * Total pemotongan dari akun sumber (pokok transfer + biaya admin).
     */
    public function totalDeduction(): float
    {
        return $this->amount + $this->admin_fee;
    }
}
