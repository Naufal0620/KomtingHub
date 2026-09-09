<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'due_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'type' => fake()->randomElement([Assignment::TYPE_INDIVIDUAL, Assignment::TYPE_GROUP]),
        ];
    }
}
