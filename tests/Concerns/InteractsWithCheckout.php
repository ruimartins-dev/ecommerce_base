<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Session;

/**
 * Setup helpers for tests that drive the checkout workflow directly through the
 * {@see CheckoutService} (bypassing the HTTP/validation layer) so service
 * behaviour can be asserted in isolation without duplicating factory wiring.
 */
trait InteractsWithCheckout
{
    /**
     * Create a customer (with user + a default address ready for checkout).
     *
     * @return array{0: User, 1: Customer, 2: Address}
     */
    protected function checkoutActor(): array
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        $address = Address::factory()->default()->create([
            'customer_id' => $customer->id,
        ]);
        $customer->update(['default_address_id' => $address->id]);

        return [$user->refresh(), $customer->refresh(), $address];
    }

    /**
     * A purchasable product: active and in stock by default.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function purchasableProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'is_active' => true,
            'stock' => 10,
            'price' => 10.00,
        ], $attributes));
    }

    /**
     * Place an order for the given actor using a [product_id => quantity] cart.
     *
     * @param  array<int, int>  $cart
     */
    protected function placeOrder(Customer $customer, Address $address, array $cart): Order
    {
        Session::put('cart', $cart);

        return app(CheckoutService::class)->place($customer, $address);
    }
}

