<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Pipelines;

use Closure;
use Modules\MasterData\Domain\Models\Customer;

class FreezeJsonbSnapshotPipe
{
    /**
     * @param  array{customer?: Customer, items: array<int, mixed>, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $snapshot = [
            'frozen_at' => now()->toIso8601String(),
            'customer' => isset($payload['customer']) && $payload['customer'] instanceof Customer ? [
                'id' => $payload['customer']->id,
                'code' => $payload['customer']->code,
                'name' => $payload['customer']->name,
                'tax_id_type' => $payload['customer']->tax_id_type?->value ?? (string) $payload['customer']->tax_id_type,
                'tax_id_number' => $payload['customer']->tax_id_number,
                'payment_term_days' => $payload['customer']->payment_term_days,
            ] : null,
            'items' => $payload['items'],
            'total_amount' => $payload['total_amount'] ?? 0.0,
            'tax_amount' => $payload['tax_amount'] ?? 0.0,
            'net_amount' => $payload['net_amount'] ?? 0.0,
        ];

        $payload['snapshot_payload'] = $snapshot;

        return $next($payload);
    }
}
