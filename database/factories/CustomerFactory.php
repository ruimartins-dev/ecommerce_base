<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->role(RoleEnum::Customer),
            'company_name' => fake()->company(),
            'vat_number' => fake()->optional()->numerify('PT#########'),
            'phone' => fake()->optional()->phoneNumber(),
            'is_blocked' => false,
            'default_address_id' => null,
        ];
    }

    /**
     * Indicate that the customer is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_blocked' => true,
        ]);
    }
}

