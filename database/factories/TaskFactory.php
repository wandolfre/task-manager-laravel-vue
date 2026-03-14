<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating realistic Task test data.
 *
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Generates a task with a realistic title, optional description,
     * random completion status, and a due date within the next 30 days.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'completed' => fake()->boolean(30), // ~30% chance of being completed
            'due_date' => fake()->optional(0.8)->dateTimeBetween('now', '+30 days'),
        ];
    }

    /**
     * Mark the task as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
        ]);
    }

    /**
     * Mark the task as incomplete.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
        ]);
    }
}
