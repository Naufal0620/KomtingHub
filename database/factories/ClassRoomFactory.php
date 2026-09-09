<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->numberBetween(1, 12),
            'code' => strtoupper(fake()->unique()->bothify('??-####')),
            'description' => fake()->sentence(),
            'komting_id' => User::factory()->komting(),
        ];
    }
}
