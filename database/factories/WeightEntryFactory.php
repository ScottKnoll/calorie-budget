<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeightEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WeightEntry>
 */
class WeightEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => Carbon::today(),
            'weight_lbs' => fake()->randomFloat(1, 120, 300),
            'notes' => null,
        ];
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(['date' => $date->toDateString()]);
    }
}
