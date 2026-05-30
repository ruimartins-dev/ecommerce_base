<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminIndexRoutes(): array
    {
        return [
            'catalogs' => ['admin.catalogs.index'],
            'categories' => ['admin.categories.index'],
            'products' => ['admin.products.index'],
            'customers' => ['admin.customers.index'],
            'addresses' => ['admin.addresses.index'],
            'orders' => ['admin.orders.index'],
        ];
    }

    #[DataProvider('adminIndexRoutes')]
    public function test_admin_can_access_every_crud_index(string $route): void
    {
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        $this->actingAs($admin)->get(route($route))->assertOk();
    }

    #[DataProvider('adminIndexRoutes')]
    public function test_customer_is_forbidden_from_admin_crud(string $route): void
    {
        $customer = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($customer)->get(route($route))->assertForbidden();
    }

    #[DataProvider('adminIndexRoutes')]
    public function test_guest_is_redirected_to_login(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('login'));
    }
}

