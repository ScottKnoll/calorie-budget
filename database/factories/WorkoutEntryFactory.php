<?php

namespace Database\Factories;

use App\Enums\WorkoutType;
use App\Models\User;
use App\Models\WorkoutEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WorkoutEntry>
 */
class WorkoutEntryFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(WorkoutType::predefined());

        return [
            'user_id' => User::factory(),
            'date' => Carbon::today(),
            'workout_type' => $type,
            'custom_type' => null,
            'duration_minutes' => fake()->numberBetween(15, 90),
            'calories_burned' => fake()->optional(0.7)->numberBetween(100, 800),
            'notes' => null,
        ];
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(['date' => $date->toDateString()]);
    }

    public function custom(string $customType): static
    {
        return $this->state([
            'workout_type' => WorkoutType::Custom,
            'custom_type' => $customType,
        ]);
    }
}
