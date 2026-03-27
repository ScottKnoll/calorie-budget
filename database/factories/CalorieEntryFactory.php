<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalorieEntry>
 */
class CalorieEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => Carbon::today(),
            'calories_consumed' => fake()->numberBetween(1200, 3500),
            'notes' => null,
        ];
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(['date' => $date->toDateString()]);
    }
}
