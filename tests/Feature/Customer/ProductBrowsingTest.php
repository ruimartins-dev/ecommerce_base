<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\RoleEnum;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithStorefront;
use Tests\TestCase;

class ProductBrowsingTest extends TestCase
{
    use InteractsWithStorefront;
    use RefreshDatabase;

    public function test_customer_can_browse_visible_products(): void
    {
        $product = $this->visibleProduct(['name' => 'Visible Widget']);

        $this->actingAs($this->customerUser())
            ->get(route('customer.products.index'))
            ->assertOk()
            ->assertSee('Visible Widget')
            ->assertSee($product->sku);
    }

    public function test_inactive_products_are_hidden(): void
    {
        $this->visibleProduct(['name' => 'Available Item']);

        // Inactive product, even though attached to an active catalog.
        $inactive = Product::factory()->create(['is_active' => false, 'name' => 'Hidden Item']);
        $inactive->catalogs()->attach(Catalog::factory()->create(['is_active' => true]));

        $this->actingAs($this->customerUser())
            ->get(route('customer.products.index'))
            ->assertOk()
            ->assertSee('Available Item')
            ->assertDontSee('Hidden Item');
    }

    public function test_products_in_inactive_catalogs_are_hidden(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'name' => 'Catalog Hidden']);
        $product->catalogs()->attach(Catalog::factory()->create(['is_active' => false]));

        $this->actingAs($this->customerUser())
            ->get(route('customer.products.index'))
            ->assertOk()
            ->assertDontSee('Catalog Hidden');
    }

    public function test_out_of_stock_products_are_marked_and_not_purchasable(): void
    {
        $this->visibleProduct(['name' => 'Sold Out', 'stock' => 0]);

        $this->actingAs($this->customerUser())
            ->get(route('customer.products.index'))
            ->assertOk()
            ->assertSee('Sold Out')
            ->assertSee('Esgotado');
    }

    public function test_product_detail_page_is_reachable_for_visible_product(): void
    {
        $product = $this->visibleProduct(['name' => 'Detail Product']);

        $this->actingAs($this->customerUser())
            ->get(route('customer.products.show', $product))
            ->assertOk()
            ->assertSee('Detail Product');
    }

    public function test_hidden_product_detail_returns_not_found(): void
    {
        $product = Product::factory()->create(['is_active' => false]);
        $product->catalogs()->attach(Catalog::factory()->create(['is_active' => true]));

        $this->actingAs($this->customerUser())
            ->get(route('customer.products.show', $product))
            ->assertNotFound();
    }

    public function test_category_page_filters_products(): void
    {
        $category = Category::factory()->create(['is_active' => true, 'name' => 'Tools']);
        $this->visibleProduct(['name' => 'Hammer'], $category);
        $this->visibleProduct(['name' => 'Unrelated Product']);

        $this->actingAs($this->customerUser())
            ->get(route('customer.categories.show', $category))
            ->assertOk()
            ->assertSee('Hammer')
            ->assertDontSee('Unrelated Product');
    }

    public function test_guest_is_redirected_from_products_index(): void
    {
        $this->get(route('customer.products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_is_blocked_from_customer_products(): void
    {
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        $this->actingAs($admin)
            ->get(route('customer.products.index'))
            ->assertForbidden();
    }
}

