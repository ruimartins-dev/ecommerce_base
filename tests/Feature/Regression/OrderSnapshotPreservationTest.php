<?php

declare(strict_types=1);

namespace Tests\Feature\Regression;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithCheckout;
use Tests\TestCase;

/**
 * Regression: an order is a historical record. Order items snapshot the product
 * name, SKU and unit price at purchase time, so later catalogue changes (price
 * edits, renames, even deletion) must never mutate an existing order.
 */
class OrderSnapshotPreservationTest extends TestCase
{
    use InteractsWithCheckout;
    use RefreshDatabase;

    public function test_price_change_after_checkout_does_not_alter_the_order(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct([
            'name' => 'Original Name',
            'sku' => 'SKU-ORIGINAL',
            'price' => 20.00,
            'stock' => 10,
        ]);

        $order = $this->placeOrder($customer, $address, [$product->id => 2]);
        $item = $order->items()->firstOrFail();

        // The catalogue changes drastically after the order was placed.
        $product->update([
            'name' => 'Renamed Product',
            'sku' => 'SKU-CHANGED',
            'price' => 99.99,
        ]);

        $item->refresh();
        $order->refresh();

        $this->assertSame('Original Name', $item->product_name_snapshot);
        $this->assertSame('SKU-ORIGINAL', $item->sku_snapshot);
        $this->assertSame('20.00', (string) $item->unit_price);
        $this->assertSame('40.00', (string) $item->line_total);
        $this->assertSame('40.00', (string) $order->total);
    }

    public function test_deleting_the_product_preserves_the_order_history(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct([
            'name' => 'Discontinued',
            'sku' => 'SKU-GONE',
            'price' => 15.00,
            'stock' => 10,
        ]);

        $order = $this->placeOrder($customer, $address, [$product->id => 1]);

        $product->delete();

        $item = $order->fresh()->items()->firstOrFail();

        $this->assertSame('Discontinued', $item->product_name_snapshot);
        $this->assertSame('SKU-GONE', $item->sku_snapshot);
        $this->assertSame('15.00', (string) $item->unit_price);
        $this->assertSame(1, Order::query()->count());
    }
}

