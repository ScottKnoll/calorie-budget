<?php

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\Gender;
use App\Services\TdeeCalculator;

// Male: 30 yrs, 5'10" (177.8 cm), 170 lbs (77.097 kg)
// BMR = (10 × 77.097) + (6.25 × 177.8) − (5 × 30) + 5 = 1737.22 → TDEE (sedentary ×1.2) = 2085
it('calculates TDEE for a sedentary male', function () {
    $result = TdeeCalculator::calculate(
        Gender::Male, 30, 5, 10, 170,
        ActivityFactor::Sedentary, ExerciseFactor::None,
    );

    expect($result)->toBe(2085);
});

// Female: 25 yrs, 5'5" (165.1 cm), 130 lbs (58.957 kg)
// BMR = (10 × 58.957) + (6.25 × 165.1) − (5 × 25) − 161 = 1335.445
// TDEE = BMR × 1.30 (lightly active) × 1.05 (light exercise) = 1823
it('calculates TDEE for a lightly active female with light exercise', function () {
    $result = TdeeCalculator::calculate(
        Gender::Female, 25, 5, 5, 130,
        ActivityFactor::LightlyActive, ExerciseFactor::Light,
    );

    expect($result)->toBe(1823);
});

it('applies the activity factor multiplier', function () {
    $sedentary = TdeeCalculator::calculate(Gender::Male, 30, 5, 10, 170, ActivityFactor::Sedentary, ExerciseFactor::None);
    $veryActive = TdeeCalculator::calculate(Gender::Male, 30, 5, 10, 170, ActivityFactor::VeryActive, ExerciseFactor::None);

    expect($veryActive)->toBeGreaterThan($sedentary);
});

// Sedentary (×1.2) + Intense (×1.15): BMR 1737.22 × 1.2 × 1.15 = 2397
it('applies the exercise factor multiplier on top of TDEE', function () {
    $noExercise = TdeeCalculator::calculate(Gender::Male, 30, 5, 10, 170, ActivityFactor::Sedentary, ExerciseFactor::None);
    $intense = TdeeCalculator::calculate(Gender::Male, 30, 5, 10, 170, ActivityFactor::Sedentary, ExerciseFactor::Intense);

    expect($intense)
        ->toBeGreaterThan($noExercise)
        ->toBe(2397);
});

it('computes a daily target with a cut deficit', function () {
    expect(TdeeCalculator::dailyTarget(2000, 'cut', 20))->toBe(1600);
});

it('computes a daily target with a bulk surplus', function () {
    expect(TdeeCalculator::dailyTarget(2000, 'bulk', 20))->toBe(2400);
});

it('returns the TDEE unchanged for a maintain goal', function () {
    expect(TdeeCalculator::dailyTarget(2000, 'maintain', 20))->toBe(2000);
});

it('rounds the daily target to the nearest integer', function () {
    // 2100 × 0.85 = 1785.0 exactly, no rounding needed
    expect(TdeeCalculator::dailyTarget(2100, 'cut', 15))->toBe(1785);
});

it('applies a custom deficit percentage', function () {
    expect(TdeeCalculator::dailyTarget(2000, 'cut', 10))->toBe(1800);
    expect(TdeeCalculator::dailyTarget(2000, 'cut', 30))->toBe(1400);
});
