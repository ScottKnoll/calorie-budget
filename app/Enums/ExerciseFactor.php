<?php

namespace App\Enums;

enum ExerciseFactor: string
{
    case None = 'none';
    case Light = 'light';
    case Moderate = 'moderate';
    case Intense = 'intense';

    public function label(): string
    {
        return match ($this) {
            ExerciseFactor::None => 'None (no gym or workout sessions)',
            ExerciseFactor::Light => 'Light (1–2 sessions/week, low intensity)',
            ExerciseFactor::Moderate => 'Moderate (3–4 sessions/week)',
            ExerciseFactor::Intense => 'Intense (5–7 sessions/week, high effort)',
        };
    }

    /**
     * Gym/workout multiplier applied on top of the activity-adjusted BMR.
     * Combined with ActivityFactor::multiplier(), the two factors align with
     * Harris-Benedict reference values when activity and exercise are matched.
     */
    public function multiplier(): float
    {
        return match ($this) {
            ExerciseFactor::None => 1.00,
            ExerciseFactor::Light => 1.05,
            ExerciseFactor::Moderate => 1.10,
            ExerciseFactor::Intense => 1.15,
        };
    }
}
