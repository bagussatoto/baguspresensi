<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Customer;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_customer_page_can_be_rendered(): void
    {
        Customer::create([
            'name' => 'PT Graha Pratama',
            'code' => 'CUST-GRP',
            'customer_type' => 'proyek',
            'phone' => '081234567890',
            'email' => 'contact@grahapratama.id',
            'address' => 'Jl. Pemuda No. 10 Jakarta',
            'credit_limit' => 50000000,
            'current_credit_used' => 15000000,
            'payment_term_days' => 30,
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.konsumen'));

        $response->assertStatus(200);
        $response->assertSee('DAFTAR KONSUMEN');
        $response->assertSee('PT Graha Pratama');
        $response->assertSee('CUST-GRP');
    }

    public function test_admin_can_fetch_customers_via_json(): void
    {
        Customer::create([
            'name' => 'Budi Santoso',
            'code' => 'CUST-BUD',
            'customer_type' => 'retail',
            'is_active' => true,
        ]);

        Customer::create([
            'name' => 'CV Sumber Makmur',
            'code' => 'CUST-SBM',
            'customer_type' => 'grosir',
            'is_active' => true,
        ]);

        $response = $this->getJson(route('admin.konsumen'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment(['name' => 'CV Sumber Makmur']);
    }

    public function test_admin_can_store_new_customer(): void
    {
        $response = $this->postJson(route('admin.konsumen.store'), [
            'name' => 'Toko Komputer Sentosa',
            'code' => 'CUST-TKS',
            'customer_type' => 'reseller',
            'phone' => '082112345678',
            'email' => 'tks@gmail.com',
            'address' => 'Mangga Dua Lt. 3 No. 45',
            'credit_limit' => 20000000,
            'payment_term_days' => 14,
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Toko Komputer Sentosa',
            'code' => 'CUST-TKS',
            'customer_type' => 'reseller',
            'credit_limit' => 20000000,
        ]);
    }

    public function test_admin_can_update_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Pelanggan Biasa',
            'code' => 'CUST-OLD',
            'customer_type' => 'retail',
            'phone' => '0811111111',
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.konsumen.update', $customer), [
            'name' => 'Pelanggan VIP Prioritas',
            'customer_type' => 'grosir',
            'credit_limit' => 10000000,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Pelanggan VIP Prioritas',
            'customer_type' => 'grosir',
        ]);
    }

    public function test_admin_can_toggle_customer_status(): void
    {
        $customer = Customer::create([
            'name' => 'Member Nonaktif Target',
            'code' => 'CUST-DSB',
            'customer_type' => 'retail',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.konsumen.toggle-status', $customer));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $this->assertFalse($customer->fresh()->is_active);
    }

    public function test_admin_can_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Member Dihapus',
            'code' => 'CUST-RMV',
            'customer_type' => 'retail',
            'is_active' => true,
        ]);

        $response = $this->deleteJson(route('admin.konsumen.destroy', $customer));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }
}
