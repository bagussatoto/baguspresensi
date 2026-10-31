<?php

declare(strict_types=1);

namespace Modules\Distribusi\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class StockTransferItem extends Model
{
    protected $table = 'stock_transfer_items';

    protected $fillable = [
        'stock_transfer_id',
        'product_variant_id',
        'qty_dispatched',
        'qty_received',
        'notes',
    ];

    protected $casts = [
        'qty_dispatched' => 'integer',
        'qty_received' => 'integer',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Hitung selisih qty antara dikirim vs diterima.
     */
    public function discrepancy(): int
    {
        return $this->qty_dispatched - $this->qty_received;
    }
}
