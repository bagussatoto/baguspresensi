<?php

declare(strict_types=1);

namespace Modules\MasterData\Domain\Services;

use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\PricelistItem;
use Modules\MasterData\Domain\Models\ProductVariant;

class PriceResolverEngine
{
    /**
     * Selesaikan harga satuan berdasarkan Customer, Varian SKU, Qty Tier, dan Cabang.
     */
    public function resolve(
        ProductVariant $variant,
        Customer $customer,
        int $quantity = 1,
        ?Branch $branch = null
    ): ResolvedPrice {
        // 1. Cek Pricelist Khusus yang Ditugaskan ke Customer
        if ($customer->pricelist_id) {
            $item = $this->findMatchingPricelistItem($customer->pricelist_id, $variant->id, $quantity);
            if ($item) {
                return new ResolvedPrice(
                    unitPrice: (float) $item->unit_price,
                    discountPercent: (float) $item->discount_percent,
                    source: 'customer_assigned_pricelist',
                    pricelistId: (int) $item->pricelist_id,
                    pricelistItemId: (int) $item->id
                );
            }
        }

        // 2. Cek Pricelist Default berdasarkan Tipe Customer (Retail, Grosir, Reseller, Proyek)
        $customerTypeValue = $customer->customer_type?->value ?? (string) $customer->customer_type;
        $defaultPricelist = Pricelist::query()
            ->where('is_active', true)
            ->where('customer_type', $customerTypeValue)
            ->first();

        if ($defaultPricelist) {
            $item = $this->findMatchingPricelistItem($defaultPricelist->id, $variant->id, $quantity);
            if ($item) {
                return new ResolvedPrice(
                    unitPrice: (float) $item->unit_price,
                    discountPercent: (float) $item->discount_percent,
                    source: 'customer_type_pricelist',
                    pricelistId: (int) $item->pricelist_id,
                    pricelistItemId: (int) $item->id
                );
            }
        }

        // 3. Fallback: Gunakan Base Price dari Master Product Variant
        return new ResolvedPrice(
            unitPrice: (float) $variant->base_price,
            discountPercent: 0.0,
            source: 'variant_base_price'
        );
    }

    /**
     * Cari baris item pricelist dengan tier min_qty tertinggi yang memenuhi syarat.
     */
    private function findMatchingPricelistItem(int $pricelistId, int $variantId, int $quantity): ?PricelistItem
    {
        return PricelistItem::query()
            ->where('pricelist_id', $pricelistId)
            ->where('product_variant_id', $variantId)
            ->where('min_qty', '<=', $quantity)
            ->orderBy('min_qty', 'desc')
            ->first();
    }
}
