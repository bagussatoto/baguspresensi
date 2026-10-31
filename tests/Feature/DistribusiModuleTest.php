<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Distribusi\Domain\Contexts\StockTransferDispatchContext;
use Modules\Distribusi\Domain\Contexts\StockTransferReceiveContext;
use Modules\Distribusi\Domain\Enums\TransferStatus;
use Modules\Distribusi\Domain\Services\StockTransferService;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\Warehouse;
use Tests\TestCase;

class DistribusiModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branchSurabaya;

    private Branch $branchMalang;

    private Warehouse $warehouseAsal;

    private Warehouse $warehouseTujuan;

    private Customer $customer;

    private ProductVariant $variant;

    private StockTransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->branchSurabaya = Branch::create([
            'code' => 'BR-SBY',
            'name' => 'Cabang Surabaya',
        ]);

        $this->branchMalang = Branch::create([
            'code' => 'BR-MLG',
            'name' => 'Cabang Malang',
        ]);

        $this->warehouseAsal = Warehouse::create([
            'branch_id' => $this->branchSurabaya->id,
            'code' => 'WH-SBY-MAIN',
            'name' => 'Gudang DC Surabaya',
            'type' => 'storage',
        ]);

        $this->warehouseTujuan = Warehouse::create([
            'branch_id' => $this->branchMalang->id,
            'code' => 'WH-MLG-MAIN',
            'name' => 'Gudang Hub Malang',
            'type' => 'storage',
        ]);

        $this->customer = Customer::create([
            'code' => 'CUST-MLG-01',
            'name' => 'Toko Bangunan Malang Jaya',
        ]);

        $product = Product::create([
            'code' => 'PRD-CAT',
            'name' => 'Cat Avian',
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'AVN-GLOSS-1KG',
            'name' => 'Avian Gloss 1kg Hitam',
            'base_price' => 75_000.0,
            'cost_price' => 50_000.0,
        ]);

        $this->transferService = app(StockTransferService::class);
    }

    public function test_two_step_logistics_handshake_complete_flow(): void
    {
        // 1. Setup stok awal di gudang asal: 100 kaleng
        StockInventory::create([
            'warehouse_id' => $this->warehouseAsal->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 100,
        ]);

        // 2. Step 1: Dispatch transfer 40 kaleng ke Malang
        $dispatchContext = new StockTransferDispatchContext(
            fromWarehouseId: $this->warehouseAsal->id,
            toWarehouseId: $this->warehouseTujuan->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 40,
                ],
            ],
            driverName: 'Pak Joko Driver',
            vehiclePlate: 'L 1234 AB',
            dispatchedBy: $this->user->id
        );

        $transfer = $this->transferService->dispatchTransfer($dispatchContext);

        $this->assertNotNull($transfer->id);
        $this->assertStringStartsWith('TRF-WH-', $transfer->transfer_number);
        $this->assertSame(TransferStatus::InTransit, $transfer->status);

        // Stok asal berkurang: 100 - 40 = 60
        $stockAsal = StockInventory::where('warehouse_id', $this->warehouseAsal->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(60, $stockAsal->qty_on_hand);

        // Stok in-transit bertambah: 40
        $stockTransit = StockInventory::where('warehouse_id', $transfer->in_transit_warehouse_id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(40, $stockTransit->qty_on_hand);

        // 3. Step 2: Penerimaan barang di gudang tujuan (Lengkap 40 kaleng)
        $receiveContext = new StockTransferReceiveContext(
            transferId: $transfer->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 40,
                ],
            ],
            receivedBy: $this->user->id
        );

        $receivedTransfer = $this->transferService->receiveTransfer($receiveContext);

        $this->assertSame(TransferStatus::Received, $receivedTransfer->status);

        // Stok in-transit berkurang kembali menjadi 0
        $stockTransit->refresh();
        $this->assertEquals(0, $stockTransit->qty_on_hand);

        // Stok tujuan bertambah menjadi 40
        $stockTujuan = StockInventory::where('warehouse_id', $this->warehouseTujuan->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertNotNull($stockTujuan);
        $this->assertEquals(40, $stockTujuan->qty_on_hand);
    }

    public function test_two_step_handshake_detects_selisih_discrepancy(): void
    {
        // Stok awal 50 pcs
        StockInventory::create([
            'warehouse_id' => $this->warehouseAsal->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 50,
        ]);

        // Dispatch 30 pcs
        $transfer = $this->transferService->dispatchTransfer(new StockTransferDispatchContext(
            fromWarehouseId: $this->warehouseAsal->id,
            toWarehouseId: $this->warehouseTujuan->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 30,
                ],
            ]
        ));

        // Terima fisik hanya 28 pcs (2 pcs rusak / hilang di jalan)
        $receivedTransfer = $this->transferService->receiveTransfer(new StockTransferReceiveContext(
            transferId: $transfer->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 28,
                    'notes' => '2 kaleng bocor di perjalanan',
                ],
            ]
        ));

        // Status otomatis menjadi Selisih
        $this->assertSame(TransferStatus::Selisih, $receivedTransfer->status);

        // Yang masuk fisik tujuan hanya 28 pcs
        $stockTujuan = StockInventory::where('warehouse_id', $this->warehouseTujuan->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(28, $stockTujuan->qty_on_hand);
    }

    public function test_cannot_dispatch_transfer_with_insufficient_stock(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Stok asal hanya 10 pcs, coba kirim 50 pcs
        StockInventory::create([
            'warehouse_id' => $this->warehouseAsal->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 10,
        ]);

        $this->transferService->dispatchTransfer(new StockTransferDispatchContext(
            fromWarehouseId: $this->warehouseAsal->id,
            toWarehouseId: $this->warehouseTujuan->id,
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 50,
                ],
            ]
        ));
    }

    public function test_can_create_delivery_order_and_deducts_warehouse_stock(): void
    {
        // Stok di gudang Malang 50 pcs
        StockInventory::create([
            'warehouse_id' => $this->warehouseTujuan->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 50,
        ]);

        // Terbitkan Surat Jalan (Delivery Order) 15 pcs ke Toko Bangunan Malang Jaya
        $do = $this->transferService->createDeliveryOrder([
            'warehouse_id' => $this->warehouseTujuan->id,
            'customer_id' => $this->customer->id,
            'reference_number' => 'SO-B2B-001',
            'recipient_name' => 'Ko Budi',
            'shipping_address' => 'Jl. Ijen No. 10, Malang',
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 15,
                ],
            ],
        ]);

        $this->assertNotNull($do->id);
        $this->assertStringStartsWith('DO-', $do->delivery_order_number);
        $this->assertSame('dispatched', $do->status);

        // Stok gudang Malang berkurang: 50 - 15 = 35 pcs
        $stock = StockInventory::where('warehouse_id', $this->warehouseTujuan->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();
        $this->assertEquals(35, $stock->qty_on_hand);
    }

    public function test_distribusi_api_endpoints(): void
    {
        StockInventory::create([
            'warehouse_id' => $this->warehouseAsal->id,
            'product_variant_id' => $this->variant->id,
            'qty_on_hand' => 80,
        ]);

        // POST dispatch via API
        $dispatchPayload = [
            'from_warehouse_id' => $this->warehouseAsal->id,
            'to_warehouse_id' => $this->warehouseTujuan->id,
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty' => 20,
                ],
            ],
            'driver_name' => 'Bambang',
        ];

        $response = $this->actingAs($this->user)->postJson(route('distribusi.transfers.store'), $dispatchPayload);
        $response->assertStatus(201)
            ->assertJsonPath('data.status', TransferStatus::InTransit->value);

        $transferId = $response->json('data.id');

        // POST receive via API
        $receivePayload = [
            'items' => [
                [
                    'product_variant_id' => $this->variant->id,
                    'qty_received' => 20,
                ],
            ],
        ];

        $receiveResponse = $this->actingAs($this->user)->postJson(route('distribusi.transfers.receive', $transferId), $receivePayload);
        $receiveResponse->assertStatus(200)
            ->assertJsonPath('data.status', TransferStatus::Received->value);
    }
}
