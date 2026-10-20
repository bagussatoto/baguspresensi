<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;

class PaperAndOperationalInventorySeeder extends Seeder
{
    public function run(): void
    {
        $uomRim = UnitOfMeasurement::firstOrCreate(
            ['code' => 'RIM'],
            ['name' => 'Rim (500 Lembar)', 'symbol' => 'rim', 'is_active' => true]
        );

        $categoryAtk = Category::firstOrCreate(
            ['code' => 'CAT-PAPER'],
            ['name' => 'Kertas & Percetakan', 'slug' => 'kertas-percetakan', 'is_active' => true]
        );

        // 1. Kertas A4
        $productA4 = Product::firstOrCreate(
            ['code' => 'PRD-KERTAS-A4'],
            [
                'name' => 'Kertas HVS A4 70gsm',
                'category_id' => $categoryAtk->id,
                'uom_id' => $uomRim->id,
                'description' => 'Kertas HVS A4 untuk print billing & dokumen',
                'is_active' => true,
            ]
        );

        $variantA4 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-KERTAS-A4-RIM'],
            [
                'product_id' => $productA4->id,
                'name' => 'Kertas HVS A4 1 Rim (500 Lembar)',
                'barcode' => 'KRT-A4-01',
                'base_price' => 48000,
                'cost_price' => 42000,
                'is_active' => true,
            ]
        );

        // 2. Kertas F4
        $productF4 = Product::firstOrCreate(
            ['code' => 'PRD-KERTAS-F4'],
            [
                'name' => 'Kertas HVS F4 / Folio 70gsm',
                'category_id' => $categoryAtk->id,
                'uom_id' => $uomRim->id,
                'description' => 'Kertas HVS F4 / Folio untuk print dokumen & formulir',
                'is_active' => true,
            ]
        );

        $variantF4 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-KERTAS-F4-RIM'],
            [
                'product_id' => $productF4->id,
                'name' => 'Kertas HVS F4 / Folio 1 Rim (500 Lembar)',
                'barcode' => 'KRT-F4-01',
                'base_price' => 52000,
                'cost_price' => 46000,
                'is_active' => true,
            ]
        );

        // Stok di Gudang Kasir (ID 5)
        $kasirWarehouseId = 5;
        if (DB::table('warehouses')->where('id', $kasirWarehouseId)->exists()) {
            StockInventory::updateOrCreate(
                ['warehouse_id' => $kasirWarehouseId, 'product_variant_id' => $variantA4->id],
                ['qty_on_hand' => 12, 'safety_stock' => 3]
            );

            StockInventory::updateOrCreate(
                ['warehouse_id' => $kasirWarehouseId, 'product_variant_id' => $variantF4->id],
                ['qty_on_hand' => 8, 'safety_stock' => 2]
            );
        }
    }
}
