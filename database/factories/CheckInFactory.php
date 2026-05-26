<?php

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckIn>
 */
class CheckInFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'weight' => fake()->randomFloat(1, 120, 300),
            'week_feeling' => fake()->sentence(),
            'went_well' => fake()->sentence(),
            'felt_hardest' => fake()->sentence(),
            'hunger_energy_sleep' => fake()->sentence(),
            'activity_consistency' => fake()->sentence(),
            'need_help' => null,
        ];
    }
}
