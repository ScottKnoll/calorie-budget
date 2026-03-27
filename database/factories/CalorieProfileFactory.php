<?php

namespace Database\Factories;

use App\Enums\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalorieProfile>
 */
class CalorieProfileFactory extends Factory
{
    public function definition(): array
    {
        $tdee = fake()->numberBetween(1600, 3000);

        return [
            'user_id' => User::factory(),
            'tdee' => $tdee,
            'goal' => fake()->randomElement(Goal::cases())->value,
            'daily_calorie_target' => $tdee,
        ];
    }

    public function forGoal(Goal $goal, int $adjustment = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'goal' => $goal->value,
            'daily_calorie_target' => $attributes['tdee'] + $adjustment,
        ]);
    }
}
