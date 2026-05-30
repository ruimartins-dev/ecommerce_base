<?php

namespace Database\Factories;

use App\Models\Catalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Catalog>
 */
class CatalogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->optional()->dateTimeBetween('-1 month', '+1 month');

        return [
            'name' => ucwords(fake()->unique()->words(2, true)).' Catalog',
            'description' => fake()->optional()->sentence(),
            'is_active' => fake()->boolean(80),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt ? fake()->dateTimeBetween($startsAt, '+6 months') : null,
        ];
    }

    /**
     * Indicate that the catalog is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}

