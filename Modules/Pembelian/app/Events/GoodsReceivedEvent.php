<?php

declare(strict_types=1);

namespace Modules\Pembelian\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Pembelian\Domain\Models\GoodsReceipt;

class GoodsReceivedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly GoodsReceipt $receipt
    ) {}
}
