<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\DTOs\CartItem;
use App\Models\Product;
use Tests\TestCase;

/**
 * The cart line DTO always reads its unit price from the live product (never a
 * cached value) and rounds the subtotal to two decimals. These guarantees keep
 * the displayed cart total in sync with the catalogue.
 */
class CartItemTest extends TestCase
{
    private function cartItem(string|float $price, int $quantity): CartItem
    {
        return new CartItem(new Product(['price' => $price]), $quantity);
    }

    public function test_unit_price_is_read_from_the_product(): void
    {
        $item = $this->cartItem(12.50, 2);

        $this->assertSame(12.50, $item->unitPrice());
    }

    public function test_subtotal_multiplies_unit_price_by_quantity(): void
    {
        $item = $this->cartItem(12.50, 3);

        $this->assertSame(37.50, $item->subtotal());
    }

    public function test_subtotal_is_rounded_to_two_decimals(): void
    {
        // 0.10 * 3 = 0.30000000000000004 in IEEE-754; the DTO must round it.
        $item = $this->cartItem(0.10, 3);

        $this->assertSame(0.30, $item->subtotal());
    }

    public function test_single_unit_subtotal_equals_unit_price(): void
    {
        $item = $this->cartItem(99.99, 1);

        $this->assertSame(99.99, $item->subtotal());
    }
}

