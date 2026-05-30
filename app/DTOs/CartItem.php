<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Product;

/**
 * Immutable view of a single cart line. The quantity comes from the session
 * while the price is always read from the live product, so the cart can never
 * drift away from the current catalogue price.
 */
final readonly class CartItem
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {
    }

    /**
     * Current unit price taken from the product.
     */
    public function unitPrice(): float
    {
        return (float) $this->product->price;
    }

    /**
     * Line subtotal (unit price × quantity).
     */
    public function subtotal(): float
    {
        return round($this->unitPrice() * $this->quantity, 2);
    }
}

