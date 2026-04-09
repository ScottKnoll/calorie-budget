<?php

namespace Database\Factories;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\FormulaType;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Models\CalorieProfile;
use App\Models\User;
use App\Services\TdeeCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalorieProfile>
 */
class CalorieProfileFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());
        $age = fake()->numberBetween(18, 65);
        $heightFeet = fake()->numberBetween(4, 6);
        $heightInches = fake()->numberBetween(0, 11);
        $weightLbs = fake()->numberBetween(110, 280);
        $activity = fake()->randomElement(ActivityFactor::cases());
        $exercise = fake()->randomElement(ExerciseFactor::cases());
        $goal = fake()->randomElement(Goal::cases());

        $tdee = TdeeCalculator::calculate($gender, $age, $heightFeet, $heightInches, $weightLbs, $activity, $exercise);
        $dailyTarget = TdeeCalculator::dailyTarget($tdee, $goal->value, 20);

        return [
            'user_id' => User::factory(),
            'gender' => $gender->value,
            'age' => $age,
            'height_feet' => $heightFeet,
            'height_inches' => $heightInches,
            'weight_lbs' => $weightLbs,
            'goal_weight_lbs' => fake()->optional(0.6)->numberBetween(100, 250),
            'start_date' => fake()->optional(0.5)->dateTimeBetween('-6 months', 'now')?->format('Y-m-d'),
            'calorie_deficit_pct' => 20,
            'activity_factor' => $activity->value,
            'exercise_factor' => $exercise->value,
            'formula' => FormulaType::Standard->value,
            'body_fat_pct' => null,
            'tdee' => $tdee,
            'goal' => $goal->value,
            'daily_calorie_target' => $dailyTarget,
        ];
    }

    public function forGoal(Goal $goal, int $deficitPct = 20): static
    {
        return $this->state(function (array $attributes) use ($goal, $deficitPct) {
            $tdee = $attributes['tdee'];

            return [
                'goal' => $goal->value,
                'calorie_deficit_pct' => $deficitPct,
                'daily_calorie_target' => TdeeCalculator::dailyTarget($tdee, $goal->value, $deficitPct),
            ];
        });
    }
}
