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
            ExerciseFactor::None => 'None',
            ExerciseFactor::Light => 'Light (walking, yoga, stretching)',
            ExerciseFactor::Moderate => 'Moderate (jogging, cycling, swimming)',
            ExerciseFactor::Intense => 'Intense (HIIT, heavy lifting, sprinting)',
        };
    }

    /** Average additional calories burned per day from workout sessions. */
    public function bonus(): int
    {
        return match ($this) {
            ExerciseFactor::None => 0,
            ExerciseFactor::Light => 150,
            ExerciseFactor::Moderate => 250,
            ExerciseFactor::Intense => 400,
        };
    }
}
