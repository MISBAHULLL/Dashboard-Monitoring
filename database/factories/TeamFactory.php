<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(['PRODUCT', 'ENGINEER']),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }

    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'PRODUCT',
        ]);
    }

    public function engineer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'ENGINEER',
        ]);
    }
}
