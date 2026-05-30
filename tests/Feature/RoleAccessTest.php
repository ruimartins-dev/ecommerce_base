<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_customer_area(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_customer_can_access_customer_dashboard(): void
    {
        $customer = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk();
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_access_customer_dashboard(): void
    {
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        $this->actingAs($admin)
            ->get(route('customer.dashboard'))
            ->assertForbidden();
    }
}

