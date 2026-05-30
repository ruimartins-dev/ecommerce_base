<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_admin_can_create_a_catalog(): void
    {
        $this->actingAs($this->admin())->post(route('admin.catalogs.store'), [
            'name' => 'Spring Catalog',
            'description' => 'Seasonal products.',
            'is_active' => true,
        ])->assertRedirect(route('admin.catalogs.index'));

        $this->assertDatabaseHas('catalogs', ['name' => 'Spring Catalog']);
    }

    public function test_admin_can_assign_products_to_a_catalog(): void
    {
        $catalog = Catalog::factory()->create();
        $products = Product::factory()->count(2)->create();

        $this->actingAs($this->admin())->put(route('admin.catalogs.products.update', $catalog), [
            'products' => $products->pluck('id')->all(),
        ])->assertRedirect(route('admin.catalogs.index'));

        $this->assertCount(2, $catalog->fresh()->products);
    }

    public function test_admin_can_toggle_catalog_active_state(): void
    {
        $catalog = Catalog::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.catalogs.toggle', $catalog))
            ->assertRedirect();

        $this->assertFalse($catalog->fresh()->is_active);
    }
}

