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
            ActivityFactor::Sedentary => 'Sedentary (desk job, little movement outside gym)',
            ActivityFactor::LightlyActive => 'Lightly Active (some walking, light daily movement)',
            ActivityFactor::ModeratelyActive => 'Moderately Active (on your feet most of the day)',
            ActivityFactor::VeryActive => 'Very Active (physically demanding job)',
            ActivityFactor::ExtraActive => 'Extra Active (extremely demanding physical job)',
        };
    }

    /**
     * Lifestyle-only multiplier (excludes gym/workout activity).
     * Combine with ExerciseFactor::multiplier() for total TDEE.
     */
    public function multiplier(): float
    {
        return match ($this) {
            ActivityFactor::Sedentary => 1.20,
            ActivityFactor::LightlyActive => 1.30,
            ActivityFactor::ModeratelyActive => 1.40,
            ActivityFactor::VeryActive => 1.55,
            ActivityFactor::ExtraActive => 1.70,
        };
    }
}
