<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Exceptions;

use RuntimeException;

class OverdueInvoiceBlockedException extends RuntimeException
{
    public function __construct(
        public readonly int $customerId,
        string $message = 'Konsumen memiliki faktur piutang yang telah jatuh tempo (overdue). Transaksi kredit terkunci.'
    ) {
        parent::__construct($message);
    }
}
