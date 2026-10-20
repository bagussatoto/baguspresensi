<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Supplier;
use Tests\TestCase;

class AdminSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_supplier_page_can_be_rendered(): void
    {
        Supplier::create([
            'code' => '2',
            'name' => 'softdrink',
            'city' => 'yogyakarta',
            'city_note' => 'maya',
            'phone' => '123456',
            'contact_person' => '-',
            'npwp' => '-',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.supplier'));

        $response->assertStatus(200);
        $response->assertSee('daftar supplier');
        $response->assertSee('softdrink');
        $response->assertSee('yogyakarta');
    }

    public function test_admin_can_store_new_supplier(): void
    {
        $response = $this->postJson(route('admin.supplier.store'), [
            'code' => '3',
            'name' => 'PT Sumber Rejeki',
            'city' => 'Surabaya',
            'city_note' => 'Cabang 1',
            'phone' => '031-123456',
            'contact_person' => 'Budi',
            'npwp' => '01.234.567.8',
            'nik' => '3578000000000001',
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('suppliers', [
            'code' => '3',
            'name' => 'PT Sumber Rejeki',
            'city' => 'Surabaya',
        ]);
    }

    public function test_admin_can_update_supplier(): void
    {
        $supplier = Supplier::create([
            'code' => '10',
            'name' => 'Old Supplier Name',
            'city' => 'Bandung',
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.supplier.update', $supplier), [
            'name' => 'Updated Supplier Name',
            'city' => 'Jakarta',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier Name',
            'city' => 'Jakarta',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_supplier_status(): void
    {
        $supplier = Supplier::create([
            'code' => '20',
            'name' => 'Supplier Toggle Test',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.supplier.toggle-status', $supplier));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $this->assertFalse($supplier->fresh()->is_active);
    }

    public function test_admin_can_delete_supplier(): void
    {
        $supplier = Supplier::create([
            'code' => '99',
            'name' => 'Supplier To Delete',
            'is_active' => true,
        ]);

        $response = $this->deleteJson(route('admin.supplier.destroy', $supplier));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
