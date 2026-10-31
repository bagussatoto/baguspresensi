<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Domain\Models\ProductVariant;

class SalesInvoiceItem extends Model
{
    protected $table = 'sales_invoice_items';

    protected $fillable = [
        'sales_invoice_id',
        'product_variant_id',
        'qty',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
