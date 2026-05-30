<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\Concerns\InteractsWithCheckout;
use Tests\TestCase;

/**
 * Service-level guarantees of the checkout workflow, asserted directly against
 * {@see \App\Services\CheckoutService} (the HTTP path is covered separately by
 * the feature suite). The service re-validates stock/availability server-side
 * and is fully transactional, so a failure must leave no partial order.
 */
class CheckoutServiceTest extends TestCase
{
    use InteractsWithCheckout;
    use RefreshDatabase;

    public function test_placing_an_order_decrements_stock_and_clears_the_cart(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct(['stock' => 10, 'price' => 10.00]);

        $order = $this->placeOrder($customer, $address, [$product->id => 3]);

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertFalse(Session::has('cart'));
        $this->assertTrue($order->customer->is($customer));
    }

    public function test_empty_cart_throws_and_creates_no_order(): void
    {
        [, $customer, $address] = $this->checkoutActor();

        try {
            $this->placeOrder($customer, $address, []);
            $this->fail('Expected a CheckoutException for an empty cart.');
        } catch (CheckoutException $e) {
            $this->assertSame('O carrinho está vazio', $e->getMessage());
        }

        $this->assertSame(0, Order::query()->count());
    }

    public function test_insufficient_stock_throws_and_rolls_back(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct(['stock' => 2]);

        try {
            $this->placeOrder($customer, $address, [$product->id => 5]);
            $this->fail('Expected a CheckoutException for insufficient stock.');
        } catch (CheckoutException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        $this->assertSame(0, Order::query()->count());
        // Transaction rolled back: stock untouched.
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_inactive_product_throws_and_rolls_back(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct(['stock' => 10, 'is_active' => false]);

        try {
            $this->placeOrder($customer, $address, [$product->id => 1]);
            $this->fail('Expected a CheckoutException for an unavailable product.');
        } catch (CheckoutException $e) {
            $this->assertStringContainsString('Produto indisponível', $e->getMessage());
        }

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_deleted_product_throws_and_rolls_back(): void
    {
        [, $customer, $address] = $this->checkoutActor();
        $product = $this->purchasableProduct(['stock' => 10]);
        $product->delete();

        $this->expectException(CheckoutException::class);

        try {
            $this->placeOrder($customer, $address, [$product->id => 1]);
        } finally {
            $this->assertSame(0, Order::query()->count());
        }
    }
}

