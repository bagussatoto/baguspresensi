<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Brand;
use Tests\TestCase;

class AdminBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_merk_page_can_be_rendered(): void
    {
        Brand::create([
            'name' => 'Logitech',
            'code' => 'LOGI',
            'slug' => 'logitech',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.merk'));

        $response->assertStatus(200);
        $response->assertSee('daftar merk');
        $response->assertSee('Logitech');
    }

    public function test_admin_can_store_new_brand(): void
    {
        $response = $this->postJson(route('admin.merk.store'), [
            'name' => 'Kingston Technology',
            'code' => 'KNG',
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Kingston Technology',
            'code' => 'KNG',
        ]);
    }

    public function test_admin_can_update_brand(): void
    {
        $brand = Brand::create([
            'name' => 'Old Brand Name',
            'code' => 'OLD',
            'slug' => 'old-brand-name',
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.merk.update', $brand), [
            'name' => 'New Brand Name',
            'code' => 'NEW',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'New Brand Name',
            'code' => 'NEW',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_brand_status(): void
    {
        $brand = Brand::create([
            'name' => 'Sandisk',
            'code' => 'SND',
            'slug' => 'sandisk',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.merk.toggle-status', $brand));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $this->assertFalse($brand->fresh()->is_active);
    }

    public function test_admin_can_delete_brand(): void
    {
        $brand = Brand::create([
            'name' => 'Brand To Delete',
            'code' => 'DEL',
            'slug' => 'brand-to-delete',
            'is_active' => true,
        ]);

        $response = $this->deleteJson(route('admin.merk.destroy', $brand));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('brands', [
            'id' => $brand->id,
        ]);
    }
}
