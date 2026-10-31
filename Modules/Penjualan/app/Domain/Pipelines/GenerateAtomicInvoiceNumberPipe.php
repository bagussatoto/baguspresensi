<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Pipelines;

use Carbon\Carbon;
use Closure;
use Modules\Penjualan\Domain\Models\SalesInvoice;

class GenerateAtomicInvoiceNumberPipe
{
    /**
     * @param  array{branch_id: int, invoice_date?: string, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $branchId = (int) $payload['branch_id'];
        $date = isset($payload['invoice_date']) ? Carbon::parse($payload['invoice_date']) : Carbon::today();

        $prefix = sprintf('INV-%03d-%s-', $branchId, $date->format('Ymd'));

        $lastSeq = SalesInvoice::query()
            ->where('invoice_number', 'LIKE', "{$prefix}%")
            ->lockForUpdate()
            ->count();

        $payload['invoice_number'] = sprintf('%s%04d', $prefix, $lastSeq + 1);

        return $next($payload);
    }
}
