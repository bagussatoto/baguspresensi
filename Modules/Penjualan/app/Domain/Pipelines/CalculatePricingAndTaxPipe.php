<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Pipelines;

use Closure;
use Modules\MasterData\Domain\Models\ProductVariant;

class CalculatePricingAndTaxPipe
{
    /**
     * @param  array{items: array<int, array{product_variant_id: int, qty: int, unit_price?: float, discount_amount?: float}>, apply_tax?: bool, [key: string]: mixed}  $payload
     */
    public function handle(array $payload, Closure $next): mixed
    {
        $calculatedItems = [];
        $totalGross = 0.0;
        $totalDiscount = 0.0;

        foreach ($payload['items'] as $item) {
            $variant = ProductVariant::findOrFail($item['product_variant_id']);
            $qty = (int) $item['qty'];
            $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $variant->base_price;
            $discount = isset($item['discount_amount']) ? (float) $item['discount_amount'] : 0.0;

            $subtotal = max(0.0, ($qty * $unitPrice) - $discount);

            $totalGross += ($qty * $unitPrice);
            $totalDiscount += $discount;

            $calculatedItems[] = array_merge($item, [
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'subtotal' => $subtotal,
                'variant_sku' => $variant->sku,
                'variant_name' => $variant->name,
            ]);
        }

        $applyTax = (bool) ($payload['apply_tax'] ?? false);
        $dpp = max(0.0, $totalGross - $totalDiscount);
        // PPN 11% sesuai UU HPP & Coretax (pembulatan ke bawah / floor untuk baris pajak)
        $taxAmount = $applyTax ? floor($dpp * 0.11) : 0.0;
        $netAmount = $dpp + $taxAmount;

        $payload['items'] = $calculatedItems;
        $payload['total_amount'] = $totalGross;
        $payload['discount_amount'] = $totalDiscount;
        $payload['tax_amount'] = $taxAmount;
        $payload['net_amount'] = $netAmount;

        return $next($payload);
    }
}
