<?php

namespace App\Services;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\FormulaType;
use App\Enums\Gender;

class TdeeCalculator
{
    /**
     * Calculate TDEE using the selected formula.
     *
     * Standard (Mifflin-St Jeor):
     *   BMR (male):   (10 × kg) + (6.25 × cm) − (5 × age) + 5
     *   BMR (female): (10 × kg) + (6.25 × cm) − (5 × age) − 161
     *
     * Lean Mass (Katch-McArdle):
     *   LBM (kg) = weight_kg × (1 − body_fat_pct / 100)
     *   BMR = 370 + (21.6 × LBM)
     *   No gender adjustment needed — fat mass is already excluded.
     *
     * TDEE = BMR × activity_multiplier × exercise_multiplier
     *
     * Activity describes lifestyle only (no gym). Exercise describes gym/workout
     * intensity only. When paired naturally the combined multiplier aligns with
     * Harris-Benedict reference values (e.g. Lightly Active × Light ≈ 1.365 vs
     * H-B 1.375, Moderately Active × Moderate ≈ 1.54 vs H-B 1.55).
     */
    public static function calculate(
        Gender $gender,
        int $age,
        int $heightFeet,
        int $heightInches,
        int $weightLbs,
        ActivityFactor $activity,
        ExerciseFactor $exercise,
        FormulaType $formula = FormulaType::Standard,
        ?float $bodyFatPct = null,
    ): int {
        $kg = $weightLbs / 2.205;

        $bmr = match ($formula) {
            FormulaType::LeanMass => self::katchMcArdleBmr($kg, $bodyFatPct ?? 20),
            FormulaType::Standard => self::mifflinStJeorBmr($gender, $age, $heightFeet, $heightInches, $kg),
        };

        $tdee = $bmr * $activity->multiplier() * $exercise->multiplier();

        return (int) round($tdee);
    }

    /**
     * Calculate BMR only (before activity/exercise multipliers).
     * Useful for displaying the raw basal metabolic rate in the UI.
     */
    public static function bmr(
        Gender $gender,
        int $age,
        int $heightFeet,
        int $heightInches,
        int $weightLbs,
        FormulaType $formula = FormulaType::Standard,
        ?float $bodyFatPct = null,
    ): int {
        $kg = $weightLbs / 2.205;

        $bmr = match ($formula) {
            FormulaType::LeanMass => self::katchMcArdleBmr($kg, $bodyFatPct ?? 20),
            FormulaType::Standard => self::mifflinStJeorBmr($gender, $age, $heightFeet, $heightInches, $kg),
        };

        return (int) round($bmr);
    }

    /**
     * Estimate days required to reach a goal weight based on the daily calorie deficit or surplus.
     * Uses the 3,500 cal ≈ 1 lb fat approximation.
     * Returns null when the calculation is not applicable (no goal weight, maintenance goal, no deficit).
     */
    public static function daysToGoal(
        int $currentWeightLbs,
        int $goalWeightLbs,
        int $tdee,
        int $dailyTarget,
    ): ?int {
        $weightDiff = abs($currentWeightLbs - $goalWeightLbs);
        $dailyDiff = abs($tdee - $dailyTarget);

        if ($weightDiff === 0 || $dailyDiff === 0) {
            return null;
        }

        return (int) ceil(($weightDiff * 3500) / $dailyDiff);
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

    private static function mifflinStJeorBmr(Gender $gender, int $age, int $heightFeet, int $heightInches, float $kg): float
    {
        $cm = ($heightFeet * 12 + $heightInches) * 2.54;

        return (10 * $kg) + (6.25 * $cm) - (5 * $age) + ($gender === Gender::Male ? 5 : -161);
    }

    private static function katchMcArdleBmr(float $kg, float $bodyFatPct): float
    {
        $lbmKg = $kg * (1 - $bodyFatPct / 100);

        return 370 + (21.6 * $lbmKg);
    }
}
