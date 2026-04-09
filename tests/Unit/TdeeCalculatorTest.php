<?php

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\FormulaType;
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

// BMR only (no activity/exercise multipliers):
// Male, 30 yrs, 5'10", 170 lbs → BMR = 1737
it('calculates BMR for a standard male without activity multiplier', function () {
    $result = TdeeCalculator::bmr(Gender::Male, 30, 5, 10, 170);

    expect($result)->toBe(1737);
});

// Female, 25 yrs, 5'5", 130 lbs → BMR = 1335
it('calculates BMR for a standard female without activity multiplier', function () {
    $result = TdeeCalculator::bmr(Gender::Female, 25, 5, 5, 130);

    expect($result)->toBe(1335);
});

// Katch-McArdle BMR: 200 lbs (90.703 kg), 15% body fat → LBM = 77.097 kg → BMR = 2035
it('calculates BMR using the lean mass formula', function () {
    $result = TdeeCalculator::bmr(
        Gender::Male, 0, 0, 0, 200,
        FormulaType::LeanMass, 15,
    );

    expect($result)->toBe(2035);
});

// Days to goal: 200 lbs → 185 lbs, 500 cal/day deficit → (15 × 3500) / 500 = 105 days
it('calculates days to goal correctly', function () {
    expect(TdeeCalculator::daysToGoal(200, 185, 2500, 2000))->toBe(105);
});

it('returns null for days to goal when weight difference is zero', function () {
    expect(TdeeCalculator::daysToGoal(200, 200, 2500, 2000))->toBeNull();
});

it('returns null for days to goal when there is no deficit or surplus', function () {
    expect(TdeeCalculator::daysToGoal(200, 185, 2500, 2500))->toBeNull();
});

// Katch-McArdle (lean mass formula):
// 200 lbs (90.703 kg), 15% body fat → LBM = 90.703 × 0.85 = 77.097 kg
// BMR = 370 + (21.6 × 77.097) = 370 + 1665.3 = 2035
// TDEE (sedentary ×1.2, none ×1.0) = 2035 × 1.2 = 2442
it('calculates TDEE using the Katch-McArdle lean mass formula', function () {
    $result = TdeeCalculator::calculate(
        Gender::Male, 0, 0, 0, 200,
        ActivityFactor::Sedentary, ExerciseFactor::None,
        FormulaType::LeanMass, 15,
    );

    expect($result)->toBe(2442);
});

it('produces a higher TDEE for leaner body composition at the same weight', function () {
    $lean = TdeeCalculator::calculate(
        Gender::Male, 0, 0, 0, 200,
        ActivityFactor::Sedentary, ExerciseFactor::None,
        FormulaType::LeanMass, 10,
    );

    $moreBodyFat = TdeeCalculator::calculate(
        Gender::Male, 0, 0, 0, 200,
        ActivityFactor::Sedentary, ExerciseFactor::None,
        FormulaType::LeanMass, 30,
    );

    expect($lean)->toBeGreaterThan($moreBodyFat);
});

it('ignores gender, height, and age when using the lean mass formula', function () {
    $male = TdeeCalculator::calculate(
        Gender::Male, 30, 5, 10, 200,
        ActivityFactor::Sedentary, ExerciseFactor::None,
        FormulaType::LeanMass, 15,
    );

    $female = TdeeCalculator::calculate(
        Gender::Female, 50, 4, 8, 200,
        ActivityFactor::Sedentary, ExerciseFactor::None,
        FormulaType::LeanMass, 15,
    );

    expect($male)->toBe($female);
});
