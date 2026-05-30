<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_admin_can_apply_a_valid_status_transition(): void
    {
        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Confirmed->value,
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();
        $this->assertSame(OrderStatusEnum::Confirmed, $fresh->status);
        $this->assertNotNull($fresh->placed_at);
    }

    public function test_admin_cannot_apply_an_invalid_status_transition(): void
    {
        $order = Order::factory()->status(OrderStatusEnum::Completed)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Pending->value,
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatusEnum::Completed, $order->fresh()->status);
    }

    public function test_admin_can_view_an_order_detail(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_customer_cannot_change_order_status(): void
    {
        $order = Order::factory()->status(OrderStatusEnum::Pending)->create();
        $customer = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($customer)
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatusEnum::Confirmed->value,
            ])
            ->assertForbidden();
    }
}

