<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Exceptions\CheckoutException;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Owns the entire "place order" workflow. Controllers stay thin: they hand the
 * service a (validated) customer + address and receive a persisted Order back,
 * or a {@see CheckoutException} carrying a user-facing message.
 *
 * Everything runs inside a single database transaction so an order, its items
 * and the stock decrements are committed atomically — a failure at any step
 * rolls the whole thing back, leaving the cart untouched.
 */
class CheckoutService
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /**
     * Validate the current cart against live product state and create the order.
     *
     * Stock and availability are re-checked server-side here (never trusting the
     * session/frontend): products are reloaded with a pessimistic lock so a
     * concurrent admin edit or another checkout cannot oversell stock.
     *
     * @throws CheckoutException when the cart is empty, a product became
     *                           unavailable, or stock is insufficient.
     */
    public function place(Customer $customer, Address $address): Order
    {
        $cartItems = $this->cart->items();

        if ($cartItems->isEmpty()) {
            throw CheckoutException::emptyCart();
        }

        return DB::transaction(function () use ($customer, $address, $cartItems): Order {
            // Lock the relevant product rows for the duration of the transaction
            // so stock validation and decrement are race-free.
            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product.id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0.0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product->getKey());

                // Deleted (soft-deleted rows are excluded by the query) or inactive.
                if ($product === null || ! $product->is_active) {
                    throw CheckoutException::unavailable($cartItem->product->name);
                }

                if ($product->stock < $cartItem->quantity) {
                    throw CheckoutException::insufficientStock($product->name);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $cartItem->quantity, 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discount = 0.0; // Discounts are out of scope for this phase.
            $tax = 0.0;      // Taxes are out of scope for this phase.
            $total = round($subtotal - $discount + $tax, 2);

            $order = Order::create([
                'customer_id' => $customer->getKey(),
                'address_id' => $address->getKey(),
                'order_number' => $this->generateOrderNumber(),
                'status' => OrderStatusEnum::Pending,
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => $discount,
                'total' => $total,
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                // Snapshot the product so order history never depends on the
                // live catalogue (price/name/sku may change or be deleted).
                $order->items()->create([
                    'product_id' => $product->getKey(),
                    'product_name_snapshot' => $product->name,
                    'sku_snapshot' => $product->sku,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);

                // Atomic decrement; the DB check constraint forbids negative stock.
                $product->decrement('stock', $line['quantity']);
            }

            $this->cart->clear();

            return $order;
        });
    }

    /**
     * Build a unique, human-readable order number in the form ORD-YYYYMMDD-NNNN.
     *
     * The daily sequence is derived from the highest existing number for the
     * day; the surrounding transaction's row lock serialises concurrent
     * checkouts, and the column's unique index is the final safety net.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.Carbon::now()->format('Ymd').'-';

        $lastNumber = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $lastNumber !== null
            ? ((int) substr($lastNumber, -4)) + 1
            : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}

