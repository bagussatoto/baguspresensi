<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Category;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_kategori_page_can_be_rendered(): void
    {
        Category::create([
            'name' => 'Aksesoris Komputer',
            'code' => 'CAT-ACC',
            'slug' => 'aksesoris-komputer',
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.kategori'));

        $response->assertStatus(200);
        $response->assertSee('DAFTAR KATEGORI PRODUK');
        $response->assertSee('Aksesoris Komputer');
    }

    public function test_admin_can_fetch_categories_via_json(): void
    {
        Category::create([
            'name' => 'Kategori JSON Test',
            'code' => 'CAT-JSON',
            'slug' => 'kategori-json-test',
            'is_active' => true,
        ]);

        $response = $this->getJson(route('admin.kategori'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'name' => 'Kategori JSON Test',
        ]);
    }

    public function test_admin_can_store_new_category(): void
    {
        $response = $this->postJson(route('admin.kategori.store'), [
            'name' => 'Minuman Dingin',
            'code' => 'CAT-COLD',
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Minuman Dingin',
            'code' => 'CAT-COLD',
        ]);
    }

    public function test_admin_can_store_sub_category_with_parent(): void
    {
        $parent = Category::create([
            'name' => 'Makanan Utama',
            'code' => 'CAT-FOOD-MAIN',
            'slug' => 'makanan-utama',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.kategori.store'), [
            'name' => 'Mie Instan',
            'code' => 'CAT-NOODLE',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Mie Instan',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create([
            'name' => 'Old Category Name',
            'code' => 'OLD-CAT',
            'slug' => 'old-category-name',
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.kategori.update', $category), [
            'name' => 'Updated Category Name',
            'code' => 'NEW-CAT',
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'code' => 'NEW-CAT',
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_set_category_itself_as_parent(): void
    {
        $category = Category::create([
            'name' => 'Self Parent Test',
            'code' => 'SELF-CAT',
            'slug' => 'self-parent-test',
            'is_active' => true,
        ]);

        $response = $this->putJson(route('admin.kategori.update', $category), [
            'name' => 'Self Parent Test',
            'parent_id' => $category->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['parent_id']);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $category = Category::create([
            'name' => 'Kategori Toggle',
            'code' => 'CAT-TOGGLE',
            'slug' => 'kategori-toggle',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('admin.kategori.toggle-status', $category));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::create([
            'name' => 'Category To Delete',
            'code' => 'CAT-DEL',
            'slug' => 'category-to-delete',
            'is_active' => true,
        ]);

        $response = $this->deleteJson(route('admin.kategori.destroy', $category));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
