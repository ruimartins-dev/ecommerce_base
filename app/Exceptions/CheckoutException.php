<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Raised by the {@see \App\Services\CheckoutService} when an order cannot be
 * placed. The message is always user-facing (Portuguese) so controllers can
 * surface it directly as a flash message without leaking internals. Throwing
 * inside the checkout transaction guarantees a full rollback, so the cart is
 * preserved and no partial order survives.
 */
class CheckoutException extends RuntimeException
{
    /**
     * The cart is empty (nothing to order).
     */
    public static function emptyCart(): self
    {
        return new self(__('O carrinho está vazio'));
    }

    /**
     * A product in the cart is no longer available (deleted or inactive).
     */
    public static function unavailable(string $productName): self
    {
        return new self(__('Produto indisponível: :name', ['name' => $productName]));
    }

    /**
     * A product no longer has enough stock to satisfy the requested quantity.
     */
    public static function insufficientStock(string $productName): self
    {
        return new self(__('Stock insuficiente para :name', ['name' => $productName]));
    }
}

