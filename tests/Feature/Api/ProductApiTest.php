<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    private function customer(): User
    {
        return User::factory()->role(RoleEnum::Customer)->create();
    }

    public function test_index_is_paginated_and_returns_resource_shape(): void
    {
        Product::factory()->count(20)->active()->create();

        Sanctum::actingAs($this->customer());

        $response = $this->getJson('/api/products')->assertOk();

        $response->assertJsonStructure([
            'data' => [['id', 'sku', 'name', 'price', 'stock', 'is_active', 'is_out_of_stock', 'is_low_stock']],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);

        $this->assertCount(15, $response->json('data'));
        $this->assertSame(20, $response->json('meta.total'));
    }

    public function test_show_returns_a_single_product(): void
    {
        $product = Product::factory()->active()->create();

        Sanctum::actingAs($this->customer());

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.sku', $product->sku);
    }

    public function test_admin_can_create_a_product(): void
    {
        $category = Category::factory()->create();

        Sanctum::actingAs($this->admin());

        $response = $this->postJson('/api/products', [
            'sku' => 'API-SKU-001',
            'name' => 'API Widget',
            'description' => 'Created through the API.',
            'price' => 49.90,
            'stock' => 30,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'categories' => [$category->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.sku', 'API-SKU-001')
            ->assertJsonPath('data.is_out_of_stock', false);

        $this->assertDatabaseHas('products', ['sku' => 'API-SKU-001', 'name' => 'API Widget']);
    }

    public function test_admin_can_update_and_delete_a_product(): void
    {
        $product = Product::factory()->active()->create();

        Sanctum::actingAs($this->admin());

        $this->putJson("/api/products/{$product->id}", [
            'sku' => $product->sku,
            'name' => 'Renamed Widget',
            'description' => 'Updated.',
            'price' => 10,
            'stock' => 3,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ])->assertOk()->assertJsonPath('data.name', 'Renamed Widget');

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();
        $this->assertSoftDeleted($product);
    }

    public function test_customer_cannot_create_products(): void
    {
        Sanctum::actingAs($this->customer());

        $this->postJson('/api/products', [
            'sku' => 'NOPE-001',
            'name' => 'Should fail',
            'description' => 'x',
            'price' => 5,
            'stock' => 1,
            'low_stock_threshold' => 1,
        ])->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'NOPE-001']);
    }

    public function test_creation_rejects_negative_price_and_stock(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/products', [
            'sku' => 'BAD-001',
            'name' => 'Invalid',
            'description' => 'Invalid.',
            'price' => -5,
            'stock' => -1,
            'low_stock_threshold' => 5,
        ])->assertUnprocessable()->assertJsonValidationErrors(['price', 'stock']);

        $this->assertDatabaseMissing('products', ['sku' => 'BAD-001']);
    }

    public function test_filters_by_sku_search_and_category(): void
    {
        $phones = Category::factory()->create(['slug' => 'phones']);

        $phone = Product::factory()->active()->create(['sku' => 'ABC123', 'name' => 'Smart Phone']);
        $phone->categories()->attach($phones);

        Product::factory()->active()->create(['sku' => 'ZZZ999', 'name' => 'Coffee Mug']);

        Sanctum::actingAs($this->customer());

        $bySku = $this->getJson('/api/products?sku=ABC')->assertOk();
        $this->assertCount(1, $bySku->json('data'));
        $this->assertSame('ABC123', $bySku->json('data.0.sku'));

        $bySearch = $this->getJson('/api/products?search=Phone')->assertOk();
        $this->assertCount(1, $bySearch->json('data'));

        $byCategory = $this->getJson('/api/products?category=phones')->assertOk();
        $this->assertCount(1, $byCategory->json('data'));
        $this->assertSame('ABC123', $byCategory->json('data.0.sku'));
    }

    public function test_inactive_products_are_hidden_from_customers(): void
    {
        $inactive = Product::factory()->create(['is_active' => false]);

        Sanctum::actingAs($this->customer());

        $this->getJson('/api/products')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/products/{$inactive->id}")->assertNotFound();
    }

    public function test_admin_can_see_inactive_products(): void
    {
        $inactive = Product::factory()->create(['is_active' => false]);

        Sanctum::actingAs($this->admin());

        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/products/{$inactive->id}")->assertOk();
    }
}

