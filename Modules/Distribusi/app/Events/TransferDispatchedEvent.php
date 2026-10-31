<?php

declare(strict_types=1);

namespace Modules\Distribusi\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Distribusi\Domain\Models\StockTransfer;

class TransferDispatchedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly StockTransfer $transfer
    ) {}
}
