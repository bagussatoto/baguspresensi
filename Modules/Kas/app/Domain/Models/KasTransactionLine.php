<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KasTransactionLine extends Model
{
    protected $table = 'kas_transaction_lines';

    protected $fillable = [
        'kas_transaction_id',
        'reference_type',
        'reference_id',
        'allocation_amount',
        'notes',
    ];

    protected $casts = [
        'allocation_amount' => 'float',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(KasTransaction::class, 'kas_transaction_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
