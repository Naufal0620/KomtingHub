<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_room_id' => ClassRoom::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'description' => fake()->sentence(),
            'group_mode' => fake()->randomElement([Subject::GROUP_MODE_RANDOM, Subject::GROUP_MODE_SELECT]),
        ];
    }
}
