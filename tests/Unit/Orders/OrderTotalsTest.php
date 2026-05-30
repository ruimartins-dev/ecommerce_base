<?php

declare(strict_types=1);

namespace Tests\Unit\Orders;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCheckout;
use Tests\TestCase;

/**
 * Pins the monetary maths the checkout performs when building an order from the
 * cart: per-line totals, the aggregate subtotal, and the grand total (tax and
 * discounts are out of scope for this phase and must therefore be zero).
 */
class OrderTotalsTest extends TestCase
{
    use InteractsWithCheckout;
    use RefreshDatabase;

    public function test_subtotal_and_total_are_summed_from_the_lines(): void
    {
        [, $customer, $address] = $this->checkoutActor();

        $cheap = $this->purchasableProduct(['price' => 10.00, 'stock' => 10]);
        $pricey = $this->purchasableProduct(['price' => 5.50, 'stock' => 10]);

        $order = $this->placeOrder($customer, $address, [
            $cheap->id => 3,   // 30.00
            $pricey->id => 2,  // 11.00
        ]);

        $this->assertSame('41.00', (string) $order->subtotal);
        $this->assertSame('0.00', (string) $order->discount_amount);
        $this->assertSame('0.00', (string) $order->tax_amount);
        $this->assertSame('41.00', (string) $order->total);
    }

    public function test_each_line_total_is_unit_price_times_quantity(): void
    {
        [, $customer, $address] = $this->checkoutActor();

        $product = $this->purchasableProduct(['price' => 12.50, 'stock' => 10]);

        $order = $this->placeOrder($customer, $address, [$product->id => 4]);
        $item = $order->items()->firstOrFail();

        $this->assertSame('12.50', (string) $item->unit_price);
        $this->assertSame(4, $item->quantity);
        $this->assertSame('50.00', (string) $item->line_total);
    }

    public function test_totals_round_to_two_decimals(): void
    {
        [, $customer, $address] = $this->checkoutActor();

        // 19.99 * 3 = 59.97 — guards against float drift in the aggregate.
        $product = $this->purchasableProduct(['price' => 19.99, 'stock' => 10]);

        $order = $this->placeOrder($customer, $address, [$product->id => 3]);

        $this->assertSame('59.97', (string) $order->total);
    }

    public function test_order_persists_with_the_computed_total(): void
    {
        [, $customer, $address] = $this->checkoutActor();

        $product = $this->purchasableProduct(['price' => 8.00, 'stock' => 10]);
        $order = $this->placeOrder($customer, $address, [$product->id => 2]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total' => '16.00',
        ]);
        $this->assertSame(1, Order::query()->count());
    }
}

