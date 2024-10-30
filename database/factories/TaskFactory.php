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
            'external_id' => fake()->name(),
            'name' => fake()->text(),
            'comment' => fake()->text(),
            'date' => now(), 
            'duration' => fake()->numberBetween(1,50),
            'user' => fake()-> name(),
        ];
    }

}
