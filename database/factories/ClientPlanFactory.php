<?php

namespace Database\Factories;

use App\Models\ClientPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientPlan>
 */
class ClientPlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->asClient(),
            'title' => 'Initial Plan — '.fake()->date('F Y'),
        ];
    }
}
