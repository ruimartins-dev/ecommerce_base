<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Enums\RoleEnum;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

/**
 * Shared storefront setup helpers for the customer feature tests so each test
 * reads intent ("a visible product", "a customer") instead of repeating factory
 * wiring.
 */
trait InteractsWithStorefront
{
    /**
     * Create a customer user (with a customer profile).
     */
    protected function customerUser(): User
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();
        Customer::factory()->create(['user_id' => $user->id]);

        return $user->refresh();
    }

    /**
     * Create a product that is visible in the storefront: active and attached
     * to an active catalog. Extra attributes/state can be overridden.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function visibleProduct(array $attributes = [], ?Category $category = null): Product
    {
        $product = Product::factory()->create(array_merge([
            'is_active' => true,
            'stock' => 10,
        ], $attributes));

        $catalog = Catalog::factory()->create(['is_active' => true]);
        $product->catalogs()->attach($catalog);

        if ($category !== null) {
            $product->categories()->attach($category);
        }

        return $product;
    }
}

