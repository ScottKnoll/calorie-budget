<?php

namespace App\Enums;

enum ActivityFactor: string
{
    case Sedentary = 'sedentary';
    case LightlyActive = 'lightly_active';
    case ModeratelyActive = 'moderately_active';
    case VeryActive = 'very_active';
    case ExtraActive = 'extra_active';

    public function label(): string
    {
        return match ($this) {
            ActivityFactor::Sedentary => 'Sedentary (desk job, little or no exercise)',
            ActivityFactor::LightlyActive => 'Lightly Active (light exercise 1–3 days/week)',
            ActivityFactor::ModeratelyActive => 'Moderately Active (moderate exercise 3–5 days/week)',
            ActivityFactor::VeryActive => 'Very Active (hard exercise 6–7 days/week)',
            ActivityFactor::ExtraActive => 'Extra Active (very hard exercise + physical job)',
        };
    }

    public function multiplier(): float
    {
        return match ($this) {
            ActivityFactor::Sedentary => 1.2,
            ActivityFactor::LightlyActive => 1.375,
            ActivityFactor::ModeratelyActive => 1.55,
            ActivityFactor::VeryActive => 1.725,
            ActivityFactor::ExtraActive => 1.9,
        };
    }
}
