<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Exceptions;

use RuntimeException;

class CreditLimitExceededException extends RuntimeException
{
    public function __construct(
        public readonly float $creditLimit,
        public readonly float $currentUsed,
        public readonly float $requestedAmount,
        string $message = 'Batas plafon kredit konsumen terlampaui.'
    ) {
        parent::__construct($message);
    }
}
