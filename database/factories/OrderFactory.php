<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create a coherent customer + address pair so the address always
        // belongs to the ordering customer.
        $customer = Customer::factory()->create();
        $address = Address::factory()->create([
            'customer_id' => $customer->id,
            'is_default' => true,
        ]);

        $status = fake()->randomElement(OrderStatusEnum::cases());

        return [
            'customer_id' => $customer->id,
            'address_id' => $address->id,
            'order_number' => 'ORD-'.fake()->unique()->numerify('########'),
            'status' => $status,
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => 0,
            'notes' => fake()->optional()->sentence(),
            'placed_at' => $status === OrderStatusEnum::Pending
                ? null
                : fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }

    /**
     * Generate order items and recompute the monetary totals from them.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order): void {
            $items = OrderItem::factory()
                ->count(fake()->numberBetween(1, 4))
                ->create(['order_id' => $order->id]);

            $subtotal = (float) $items->sum('line_total');
            $discount = (float) $order->discount_amount;
            $tax = round(($subtotal - $discount) * 0.23, 2);

            $order->forceFill([
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'total' => round($subtotal - $discount + $tax, 2),
            ])->save();
        });
    }

    /**
     * Set a specific status for the order.
     */
    public function status(OrderStatusEnum $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}

