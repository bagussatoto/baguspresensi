<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Domain\Enums\CustomerType;

class Pricelist extends Model
{
    protected $table = 'pricelists';

    protected $fillable = [
        'code',
        'name',
        'customer_type',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'customer_type' => CustomerType::class,
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PricelistItem::class, 'pricelist_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'pricelist_id');
    }
}
