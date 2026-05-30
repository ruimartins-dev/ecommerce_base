<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_admin_can_create_a_product_with_image_and_relations(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $catalog = Catalog::factory()->create();

        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'sku' => 'SKU-TEST-001',
            'name' => 'Test Widget',
            'description' => 'A solid test widget.',
            'price' => 19.99,
            'stock' => 25,
            'low_stock_threshold' => 5,
            'is_active' => true,
            'image' => UploadedFile::fake()->create('widget.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
            'catalogs' => [$catalog->id],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::firstWhere('sku', 'SKU-TEST-001');

        $this->assertNotNull($product);
        $this->assertSame('Test Widget', $product->name);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
        $this->assertTrue($product->categories->contains($category));
        $this->assertTrue($product->catalogs->contains($catalog));
    }

    public function test_product_creation_rejects_negative_price_and_stock(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'sku' => 'SKU-TEST-002',
            'name' => 'Invalid Widget',
            'description' => 'Should fail validation.',
            'price' => -5,
            'stock' => -1,
            'low_stock_threshold' => 5,
        ]);

        $response->assertSessionHasErrors(['price', 'stock']);
        $this->assertDatabaseMissing('products', ['sku' => 'SKU-TEST-002']);
    }

    public function test_admin_can_toggle_product_active_state(): void
    {
        $product = Product::factory()->active()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.products.toggle', $product))
            ->assertRedirect();

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_customer_cannot_create_products(): void
    {
        $customer = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($customer)
            ->post(route('admin.products.store'), [])
            ->assertForbidden();
    }
}

