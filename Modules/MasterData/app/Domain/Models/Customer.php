<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Enums\TaxIdType;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'code',
        'name',
        'customer_type',
        'pricelist_id',
        'tax_id_type',
        'tax_id_number',
        'credit_limit',
        'current_credit_used',
        'payment_term_days',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'customer_type' => CustomerType::class,
        'tax_id_type' => TaxIdType::class,
        'credit_limit' => 'float',
        'current_credit_used' => 'float',
        'payment_term_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pricelist(): BelongsTo
    {
        return $this->belongsTo(Pricelist::class, 'pricelist_id');
    }

    /**
     * Sisa plafon kredit yang masih tersedia.
     */
    public function remainingCreditLimit(): float
    {
        return max(0.0, (float) $this->credit_limit - (float) $this->current_credit_used);
    }

    /**
     * Cek apakah penambahan transaksi baru melampaui plafon kredit.
     */
    public function isCreditExceeded(float $additionalAmount = 0.0): bool
    {
        if ((float) $this->credit_limit <= 0.0) {
            // Plafon 0 berarti transaksi kredit tidak diperbolehkan (hanya tunai)
            return true;
        }

        return ((float) $this->current_credit_used + $additionalAmount) > (float) $this->credit_limit;
    }
}
