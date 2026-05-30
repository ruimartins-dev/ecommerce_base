<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\OrderStatusEnum;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithStorefront;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use InteractsWithStorefront;
    use RefreshDatabase;

    /**
     * Create a customer user together with a default address.
     *
     * @return array{0: User, 1: Customer, 2: Address}
     */
    private function customerWithAddress(): array
    {
        $user = $this->customerUser();
        /** @var Customer $customer */
        $customer = $user->customer;

        $address = Address::factory()->default()->create([
            'customer_id' => $customer->id,
        ]);
        $customer->update(['default_address_id' => $address->id]);

        return [$user, $customer, $address];
    }

    public function test_customer_can_complete_checkout_and_order_is_created(): void
    {
        [$user, $customer, $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10, 'price' => 12.50]);

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $response->assertRedirect(route('customer.orders.index'));
        $response->assertSessionHas('success', 'Encomenda criada com sucesso');

        $order = Order::query()->where('customer_id', $customer->id)->firstOrFail();

        $this->assertSame(OrderStatusEnum::Pending, $order->status);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $order->order_number);
        $this->assertSame('25.00', (string) $order->subtotal);
        $this->assertSame('0.00', (string) $order->tax_amount);
        $this->assertSame('0.00', (string) $order->discount_amount);
        $this->assertSame('25.00', (string) $order->total);
        $this->assertNotNull($order->placed_at);
    }

    public function test_checkout_creates_order_items_with_snapshots(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10, 'price' => 12.50, 'name' => 'Widget', 'sku' => 'SKU-123']);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name_snapshot' => 'Widget',
            'sku_snapshot' => 'SKU-123',
            'unit_price' => '12.50',
            'quantity' => 2,
            'line_total' => '25.00',
        ]);
    }

    public function test_checkout_decrements_stock(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 3]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_checkout_clears_the_cart(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $this->assertNull(session('cart'));
    }

    public function test_order_appears_in_history_after_checkout(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id]);

        $order = Order::query()->latest('id')->firstOrFail();

        $this->actingAs($user)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_cannot_open_checkout_with_empty_cart(): void
    {
        [$user] = $this->customerWithAddress();

        $this->actingAs($user)
            ->get(route('customer.checkout.index'))
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('error');
    }

    public function test_cannot_confirm_checkout_with_empty_cart(): void
    {
        [$user, , $address] = $this->customerWithAddress();

        $this->actingAs($user)
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cannot_checkout_with_another_customers_address(): void
    {
        [$user] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10]);

        // An address that belongs to a different customer.
        $otherAddress = Address::factory()->create([
            'customer_id' => Customer::factory()->create()->id,
        ]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('customer.checkout.store'), ['address_id' => $otherAddress->id])
            ->assertSessionHasErrors('address_id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_insufficient_stock_blocks_checkout(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        // Cart wants 5 but only 2 are in stock (e.g. an admin lowered it).
        $product = $this->visibleProduct(['stock' => 2]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 5]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_inactive_product_blocks_checkout(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10]);
        $product->update(['is_active' => false]);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_deleted_product_blocks_checkout(): void
    {
        [$user, , $address] = $this->customerWithAddress();
        $product = $this->visibleProduct(['stock' => 10]);
        $product->delete();

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('customer.checkout.store'), ['address_id' => $address->id])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_cannot_access_checkout(): void
    {
        $this->get(route('customer.checkout.index'))
            ->assertRedirect(route('login'));
    }
}

