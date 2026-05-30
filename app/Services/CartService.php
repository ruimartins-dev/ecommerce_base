<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed shopping cart. The cart is intentionally NOT persisted to the
 * database: it lives in the session as a simple [product_id => quantity] map.
 *
 * All read access rehydrates the products from the database so prices, stock
 * and visibility are always current; products that became unavailable are
 * silently pruned. Stock/visibility validation itself lives in the
 * add/update cart form requests, never in this service.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Add a quantity of a product to the cart (accumulating with any quantity
     * already present).
     */
    public function add(Product $product, int $quantity): void
    {
        $items = $this->raw();
        $items[$product->getKey()] = ($items[$product->getKey()] ?? 0) + max(1, $quantity);

        $this->persist($items);
    }

    /**
     * Set the absolute quantity for a product. A non-positive quantity removes
     * the line entirely.
     */
    public function update(Product $product, int $quantity): void
    {
        $items = $this->raw();

        if ($quantity <= 0) {
            unset($items[$product->getKey()]);
        } else {
            $items[$product->getKey()] = $quantity;
        }

        $this->persist($items);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(Product $product): void
    {
        $items = $this->raw();
        unset($items[$product->getKey()]);

        $this->persist($items);
    }

    /**
     * Empty the cart completely.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Quantity currently stored for the given product id.
     */
    public function quantityFor(int $productId): int
    {
        return (int) ($this->raw()[$productId] ?? 0);
    }

    /**
     * Hydrated cart lines. Products no longer present in the database are
     * dropped from both the result and the stored cart.
     *
     * @return Collection<int, CartItem>
     */
    public function items(): Collection
    {
        $raw = $this->raw();

        if ($raw === []) {
            return collect();
        }

        $products = Product::query()
            ->with('categories')
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        // Prune any ids that no longer resolve to a product.
        $pruned = array_intersect_key($raw, $products->all());

        if (count($pruned) !== count($raw)) {
            $this->persist($pruned);
        }

        return collect($pruned)
            ->map(fn (int $quantity, int $id): CartItem => new CartItem($products[$id], $quantity))
            ->values();
    }

    /**
     * Grand total of every line.
     */
    public function total(): float
    {
        return round(
            $this->items()->sum(fn (CartItem $item): float => $item->subtotal()),
            2,
        );
    }

    /**
     * Total number of units across all lines (used for the navbar badge).
     */
    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * Whether the cart has no lines.
     */
    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * The raw [product_id => quantity] map from the session.
     *
     * @return array<int, int>
     */
    private function raw(): array
    {
        /** @var array<int, int> $cart */
        $cart = Session::get(self::SESSION_KEY, []);

        return $cart;
    }

    /**
     * Persist the cart map back to the session.
     *
     * @param  array<int, int>  $items
     */
    private function persist(array $items): void
    {
        if ($items === []) {
            Session::forget(self::SESSION_KEY);

            return;
        }

        Session::put(self::SESSION_KEY, $items);
    }
}





