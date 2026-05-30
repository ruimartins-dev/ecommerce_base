<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\RoleEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Security: order authorization. Verifies the ownership rules in OrderPolicy and
 * the global admin bypass (Gate::before) wired in the AppServiceProvider, so an
 * order can never be read or mutated across tenant boundaries.
 */
class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function customerUser(): User
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();
        Customer::factory()->create(['user_id' => $user->id]);

        return $user->refresh();
    }

    public function test_owner_can_view_their_order(): void
    {
        $order = Order::factory()->create();
        $owner = $order->customer->user;

        $this->assertTrue((new OrderPolicy)->view($owner, $order));
        $this->assertTrue(Gate::forUser($owner)->allows('view', $order));
    }

    public function test_other_customer_cannot_view_the_order(): void
    {
        $order = Order::factory()->create();
        $intruder = $this->customerUser();

        $this->assertFalse((new OrderPolicy)->view($intruder, $order));
        $this->assertTrue(Gate::forUser($intruder)->denies('view', $order));
    }

    public function test_admin_bypass_grants_view_on_any_order(): void
    {
        $order = Order::factory()->create();
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        // The policy itself describes only the customer perspective, but the
        // global Gate::before admin bypass authorizes admins for any order.
        $this->assertTrue(Gate::forUser($admin)->allows('view', $order));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $order));
    }

    public function test_owner_may_update_their_order_but_others_may_not(): void
    {
        $order = Order::factory()->create();

        $this->assertTrue((new OrderPolicy)->update($order->customer->user, $order));
        $this->assertFalse((new OrderPolicy)->update($this->customerUser(), $order));
    }

    public function test_only_users_with_a_customer_profile_may_create_orders(): void
    {
        $customer = $this->customerUser();
        $adminWithoutProfile = User::factory()->role(RoleEnum::Admin)->create();

        $this->assertTrue((new OrderPolicy)->create($customer));
        // No customer profile => the policy denies, even though the admin bypass
        // would still authorise the admin at the Gate level.
        $this->assertFalse((new OrderPolicy)->create($adminWithoutProfile));
    }
}

