<?php

namespace Database\Factories;

use App\Models\IntakeResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntakeResponse>
 */
class IntakeResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'main_goal' => fake()->randomElement(['fat_loss', 'consistency', 'energy', 'strength', 'other']),
            'why_now' => fake()->optional()->sentence(),
            'current_weight_lbs' => fake()->optional()->numberBetween(100, 300),
            'current_height_feet' => fake()->optional()->numberBetween(4, 7),
            'current_height_inches' => fake()->optional()->numberBetween(0, 11),
            'activity_level' => fake()->randomElement(['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extra_active']),
            'workout_experience' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'work_schedule' => fake()->randomElement(['nine_to_five', 'shift_work', 'remote', 'stay_at_home', 'other']),
            'open_to_tracking_steps' => fake()->randomElement(['yes', 'open_to_it', 'prefer_not']),
            'daily_steps' => fake()->randomElement(['low', 'moderate', 'high', null]),
            'sleep_hours' => fake()->randomElement(['under_six', 'six_to_seven', 'seven_to_eight', 'eight_plus']),
            'stress_level' => fake()->randomElement(['low', 'moderate', 'high', 'very_high']),
            'tracks_currently' => fake()->randomElement(['yes', 'no', 'loosely']),
            'meal_timing_pattern' => fake()->randomElement(['no_pattern', 'skip_breakfast', 'intermittent_fasting', 'consistent_times', 'other']),
            'typical_day_of_eating' => fake()->optional()->paragraph(),
            'dietary_restrictions' => fake()->optional()->sentence(),
            'workout_days_per_week' => fake()->randomElement(['one_two', 'three_four', 'five_six', 'every_day']),
            'open_to_tracking' => fake()->randomElement(['yes_comfortable', 'open_to_trying', 'tried_struggled', 'simpler_approach']),
        ];
    }
}
