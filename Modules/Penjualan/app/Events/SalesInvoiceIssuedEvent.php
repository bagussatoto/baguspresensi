<?php

declare(strict_types=1);

namespace Modules\Penjualan\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Penjualan\Domain\Models\SalesInvoice;

class SalesInvoiceIssuedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SalesInvoice $invoice
    ) {}
}
