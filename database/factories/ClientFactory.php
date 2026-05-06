<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Hospital',
            'address' => fake()->address(),
            'city' => fake()->city(),
            'type' => fake()->randomElement(['A', 'B', 'C', 'PRATAMA']),
            'pic_name' => fake()->name(),
            'pic_phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
