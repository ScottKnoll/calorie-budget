<?php

namespace Database\Factories;

use App\Models\CalorieEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CalorieEntry>
 */
class CalorieEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => Carbon::today(),
            'calories_consumed' => fake()->numberBetween(1200, 3500),
            'carbs_grams' => null,
            'protein_grams' => null,
            'fat_grams' => null,
            'notes' => null,
        ];
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(['date' => $date->toDateString()]);
    }
}
