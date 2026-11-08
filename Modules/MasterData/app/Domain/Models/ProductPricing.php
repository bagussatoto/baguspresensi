<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model ProductPricing: Strategi Zonasi dan Penetapan Harga Multi-Cabang.
 * Memisahkan master fisik produk dengan zonasi harga pasar & estimasi HPP cabang.
 */
class ProductPricing extends Model
{
    protected $table = 'product_pricings';

    protected $fillable = [
        'product_variant_id',
        'branch_id',
        'zone_code',
        'base_cost',
        'sale_price',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'base_cost' => 'float',
        'sale_price' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Cek apakah kebijakan harga ini aktif pada tanggal tertentu.
     */
    public function isEffectiveOn(?Carbon $date = null): bool
    {
        $d = $date ?? Carbon::now();
        $dateStr = $d->toDateString();

        if ($this->start_date && $this->start_date->toDateString() > $dateStr) {
            return false;
        }

        if ($this->end_date && $this->end_date->toDateString() < $dateStr) {
            return false;
        }

        return true;
    }
}
