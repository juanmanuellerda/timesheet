<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->word(),
            'name' => fake()->sentence(),
            'comment' => fake()->text(30),
            'date' => now(), 
            'duration' => fake()->numberBetween(1,50),
            'user' => fake()-> name(),
        ];
    }

}
