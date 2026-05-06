<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'modul' => fake()->word(),
            'task_url' => '-',
            'category' => fake()->randomElement(['Fitur Berbayar', 'Regulasi', 'Saran Fitur', 'Prioritas']),
            'priority' => fake()->randomElement(['urgent', 'high', 'medium', 'low']),
            'status' => 'open',
            'product_id' => Team::factory()->product(),
            'client_id' => Client::factory(),
            'created_by' => User::factory(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function withReleaseDate(): static
    {
        return $this->state(fn (array $attributes) => [
            'release_date' => now()->addDays(14),
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }
}
