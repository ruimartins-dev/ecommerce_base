<?php

declare(strict_types=1);

namespace Tests\Unit\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithCheckout;
use Tests\TestCase;

/**
 * The human-readable order number (ORD-YYYYMMDD-NNNN) must be unique and
 * sequential within a day. Uniqueness is also enforced by a DB index, but the
 * generation logic is the first line of defence and is pinned here.
 */
class OrderNumberGenerationTest extends TestCase
{
    use InteractsWithCheckout;
    use RefreshDatabase;

    public function test_order_number_uses_the_expected_format(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct(['stock' => 10]);

        $order = $this->placeOrder($customer, $address, [$product->id => 1]);

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $order->order_number);
        $this->assertStringStartsWith('ORD-'.Carbon::now()->format('Ymd').'-', $order->order_number);
    }

    public function test_daily_sequence_increments_for_each_order(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $prefix = 'ORD-'.Carbon::now()->format('Ymd').'-';

        $first = $this->placeOrder($customer, $address, [$this->purchasableProduct(['stock' => 5])->id => 1]);
        $second = $this->placeOrder($customer, $address, [$this->purchasableProduct(['stock' => 5])->id => 1]);
        $third = $this->placeOrder($customer, $address, [$this->purchasableProduct(['stock' => 5])->id => 1]);

        $this->assertSame($prefix.'0001', $first->order_number);
        $this->assertSame($prefix.'0002', $second->order_number);
        $this->assertSame($prefix.'0003', $third->order_number);
    }

    public function test_generated_numbers_are_unique(): void
    {
        [, $customer, $address] = $this->checkoutActor();

        $numbers = [];
        for ($i = 0; $i < 5; $i++) {
            $product = $this->purchasableProduct(['stock' => 5]);
            $numbers[] = $this->placeOrder($customer, $address, [$product->id => 1])->order_number;
        }

        $this->assertCount(5, array_unique($numbers));
    }
}

