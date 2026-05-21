<?php

namespace Database\Factories;

use App\Models\ClientPlan;
use App\Models\PlanSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanSection>
 */
class PlanSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_plan_id' => ClientPlan::factory(),
            'title' => fake()->randomElement(['Nutrition', 'Activity', 'Sleep', 'Progress', 'Guidelines']),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
