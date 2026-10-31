<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Pipelines;

use Closure;
use InvalidArgumentException;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;

class ValidateCustomerCreditPipe
{
    public function __construct(
        private readonly CustomerCreditGuard $creditGuard
    ) {}

    /**
     * @param  array{customer: Customer, total_amount: float, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $customer = $payload['customer'];
        $orderAmount = (float) $payload['total_amount'];

        if (! $customer->is_active) {
            throw new InvalidArgumentException("Pelanggan [{$customer->name}] tidak aktif.");
        }

        // Validasi kelayakan kredit (plafon dan piutang jatuh tempo)
        $this->creditGuard->validate($customer, $orderAmount);

        return $next($payload);
    }
}
