<?php

declare(strict_types=1);

namespace Tests\Feature\Regression;

use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithStorefront;
use Tests\TestCase;

/**
 * Regression: a double-submitted checkout (e.g. a user double-clicking "place
 * order", or a replayed POST) must never create two orders. The cart is emptied
 * server-side as part of a successful checkout, so the replay is rejected with a
 * validation error instead of producing a duplicate order.
 */
class DuplicateOrderSubmissionTest extends TestCase
{
    use InteractsWithStorefront;
    use RefreshDatabase;

    public function test_replaying_the_checkout_post_does_not_create_a_second_order(): void
    {
        $user = $this->customerUser();
        /** @var Customer $customer */
        $customer = $user->customer;
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $customer->update(['default_address_id' => $address->id]);

        $product = $this->visibleProduct(['stock' => 10]);

        // First submission succeeds and creates exactly one order.
        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertRedirect(route('customer.orders.index'));

        $this->assertSame(1, Order::query()->count());

        // The cart was cleared by the first checkout. A replayed POST (reusing
        // the now-empty session) is blocked by cart validation.
        $this->actingAs($user)
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertSessionHasErrors('cart');

        $this->assertSame(1, Order::query()->count());
        // Stock was only decremented once.
        $this->assertSame(9, $product->fresh()->stock);
    }
}

