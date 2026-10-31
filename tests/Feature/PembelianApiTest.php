<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\Pembelian\Domain\Enums\PurchaseOrderStatus;
use Tests\TestCase;

class PembelianApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Warehouse $warehouse;

    private Supplier $supplier;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->branch = Branch::create([
            'code' => 'BR-API-PEMBELIAN',
            'name' => 'Cabang Gudang Barat',
        ]);

        $this->warehouse = Warehouse::create([
            'branch_id' => $this->branch->id,
            'code' => 'WH-API-01',
            'name' => 'Gudang Utama Barat',
            'type' => 'storage',
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUPP-API-01',
            'name' => 'Distributor Utama Semen',
            'payment_term_days' => 14,
            'is_active' => true,
        ]);

        $product = Product::create([
            'code' => 'PRD-API-01',
            'name' => 'Cat Tembok Dulux',
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'DLX-WHITE-5KG',
            'name' => 'Dulux Brilliant White 5kg',
            'base_price' => 120_000.0,
            'cost_price' => 90_000.0,
        ]);
    }

    public function test_can_create_and_approve_po_via_api(): void
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_ordered' => 20,
                    'unit_price' => 95_000.0,
                ],
            ],
            'notes' => 'PO Urgent pengiriman besok',
        ];

        $response = $this->actingAs($this->user)->postJson(route('pembelian.po.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', PurchaseOrderStatus::Draft->value);

        $poId = $response->json('data.id');

        // Approve PO
        $approveResponse = $this->actingAs($this->user)->postJson(route('pembelian.po.approve', $poId));
        $approveResponse->assertStatus(200)
            ->assertJsonPath('data.status', PurchaseOrderStatus::Approved->value);
    }

    public function test_can_record_goods_receipt_via_api(): void
    {
        // Langsung terima fisik barang non-PO
        $payload = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'delivery_note_number' => 'SJ-DELIVERY-999',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 15,
                    'unit_cost' => 92_000.0,
                ],
            ],
            'create_bill' => true,
        ];

        $response = $this->actingAs($this->user)->postJson(route('pembelian.receipts.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.supplier_id', $this->supplier->id);

        $stock = StockInventory::where('warehouse_id', $this->warehouse->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(15, $stock->qty_on_hand);
    }
}
