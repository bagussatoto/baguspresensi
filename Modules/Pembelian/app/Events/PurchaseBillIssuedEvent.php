<?php

declare(strict_types=1);

namespace Modules\Pembelian\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Pembelian\Domain\Models\PurchaseBill;

class PurchaseBillIssuedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PurchaseBill $bill
    ) {}
}
