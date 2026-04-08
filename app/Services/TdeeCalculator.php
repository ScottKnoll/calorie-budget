<?php

namespace App\Services;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\Gender;

class TdeeCalculator
{
    /**
     * Calculate TDEE (Total Daily Energy Expenditure) using Mifflin-St Jeor.
     *
     * BMR (male):   (10 × kg) + (6.25 × cm) − (5 × age) + 5
     * BMR (female): (10 × kg) + (6.25 × cm) − (5 × age) − 161
     *
     * TDEE = BMR × activity_multiplier + exercise_bonus
     */
    public static function calculate(
        Gender $gender,
        int $age,
        int $heightFeet,
        int $heightInches,
        int $weightLbs,
        ActivityFactor $activity,
        ExerciseFactor $exercise,
    ): int {
        $kg = $weightLbs / 2.205;
        $cm = ($heightFeet * 12 + $heightInches) * 2.54;

        $bmr = (10 * $kg) + (6.25 * $cm) - (5 * $age) + ($gender === Gender::Male ? 5 : -161);

        $tdee = ($bmr * $activity->multiplier()) + $exercise->bonus();

        return (int) round($tdee);
    }

    /**
     * Compute the daily calorie target from a TDEE and a goal deficit/surplus percentage.
     *
     * For Cut: target = TDEE × (1 − pct/100)
     * For Bulk: target = TDEE × (1 + pct/100)
     * For Maintain: target = TDEE
     */
    public static function dailyTarget(int $tdee, string $goal, int $deficitPct): int
    {
        return match ($goal) {
            'cut' => (int) round($tdee * (1 - $deficitPct / 100)),
            'bulk' => (int) round($tdee * (1 + $deficitPct / 100)),
            default => $tdee,
        };
    }
}
