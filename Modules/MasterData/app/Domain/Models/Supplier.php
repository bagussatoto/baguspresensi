<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Domain\Enums\TaxIdType;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'code',
        'name',
        'tax_id_type',
        'tax_id_number',
        'payment_term_days',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'phone',
        'email',
        'address',
        'city',
        'city_note',
        'contact_person',
        'npwp',
        'nik',
        'is_active',
    ];

    protected $casts = [
        'tax_id_type' => TaxIdType::class,
        'payment_term_days' => 'integer',
        'is_active' => 'boolean',
    ];
}
