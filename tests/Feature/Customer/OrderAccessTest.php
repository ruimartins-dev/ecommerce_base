<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithStorefront;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use InteractsWithStorefront;
    use RefreshDatabase;

    public function test_customer_can_view_own_order(): void
    {
        $order = Order::factory()->create();
        $user = $order->customer->user;

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $ownOrder = Order::factory()->create();
        $otherOrder = Order::factory()->create();

        $this->actingAs($ownOrder->customer->user)
            ->get(route('customer.orders.show', $otherOrder))
            ->assertForbidden();
    }

    public function test_order_history_only_lists_own_orders(): void
    {
        $ownOrder = Order::factory()->create(['order_number' => 'ORD-OWN-0001']);
        Order::factory()->create(['order_number' => 'ORD-OTHER-0002']);

        $this->actingAs($ownOrder->customer->user)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('ORD-OWN-0001')
            ->assertDontSee('ORD-OTHER-0002');
    }

    public function test_order_detail_uses_snapshots(): void
    {
        $order = Order::factory()->create();
        $item = $order->items->first();

        $this->actingAs($order->customer->user)
            ->get(route('customer.orders.show', $order))
            ->assertOk()
            ->assertSee($item->product_name_snapshot)
            ->assertSee($item->sku_snapshot);
    }

    public function test_guest_cannot_view_orders(): void
    {
        $this->get(route('customer.orders.index'))
            ->assertRedirect(route('login'));
    }
}

