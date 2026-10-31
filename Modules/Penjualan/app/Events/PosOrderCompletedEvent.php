<?php

declare(strict_types=1);

namespace Modules\Penjualan\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Penjualan\Domain\Models\PosOrder;

class PosOrderCompletedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PosOrder $order
    ) {}
}
