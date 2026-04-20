<?php

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\FormulaType;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Livewire\Budget\Setup;
use App\Models\CalorieProfile;
use App\Models\User;
use Livewire\Livewire;

// Test measurements: Male, 30, 5'10", 170 lbs, sedentary, no exercise → TDEE=2085
const TEST_TDEE = 2085;

/** Sets standard body measurements on a Livewire test component. */
function withMeasurements($component): mixed
{
    return $component
        ->set('gender', 'male')
        ->set('age', 30)
        ->set('height_feet', 5)
        ->set('height_inches', 10)
        ->set('weight_lbs', 170)
        ->set('activity_factor', 'sedentary')
        ->set('exercise_factor', 'none');
}

it('shows the setup page to guests', function () {
    $this->get(route('budget.setup'))->assertSuccessful();
});

it('redirects guests to the register page when they try to save settings', function () {
    Livewire::test(Setup::class)
        ->call('save')
        ->assertRedirect(route('register'));
});

it('shows the setup page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.setup'))
        ->assertSuccessful();
});

it('creates a calorie profile on first save', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)->set('goal', Goal::Cut->value)->call('save');

    $profile = CalorieProfile::where('user_id', $user->id)->first();

    expect($profile)
        ->not->toBeNull()
        ->gender->toBe(Gender::Male)
        ->age->toBe(30)
        ->weight_lbs->toBe(170)
        ->tdee->toBe(TEST_TDEE)
        ->goal->toBe(Goal::Cut)
        ->daily_calorie_target->toBe((int) round(TEST_TDEE * 0.8));
});

it('updates an existing calorie profile', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('weight_lbs', 200)
        ->set('goal', Goal::Bulk->value)
        ->call('save');

    expect($user->fresh()->calorieProfile)
        ->weight_lbs->toBe(200)
        ->goal->toBe(Goal::Bulk);
});

it('suggests a 20% deficit when goal is set to cut', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)->set('goal', Goal::Cut->value)
        ->assertSet('daily_calorie_target', (int) round(TEST_TDEE * 0.8));
});

it('suggests a 20% surplus when goal is set to bulk', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)->set('goal', Goal::Bulk->value)
        ->assertSet('daily_calorie_target', (int) round(TEST_TDEE * 1.2));
});

it('suggests the same as TDEE when goal is maintain', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)->set('goal', Goal::Maintain->value)
        ->assertSet('daily_calorie_target', TEST_TDEE);
});

it('applies a custom deficit percentage', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)
        ->set('goal', Goal::Cut->value)
        ->set('calorie_deficit_pct', 15)
        ->assertSet('daily_calorie_target', (int) round(TEST_TDEE * 0.85));
});

it('updates the suggestion when body measurements change', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)->set('goal', Goal::Cut->value);

    $originalTarget = $component->get('daily_calorie_target');

    $component->set('weight_lbs', 220);

    expect($component->get('daily_calorie_target'))->not->toBe($originalTarget);
});

it('preserves a manually entered daily calorie target when inputs change', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)
        ->set('goal', Goal::Cut->value)
        ->set('daily_calorie_target', 1500)
        ->set('weight_lbs', 200)
        ->assertSet('daily_calorie_target', 1500);
});

it('mounts with existing profile values', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'gender' => Gender::Female->value,
        'age' => 28,
        'height_feet' => 5,
        'height_inches' => 4,
        'weight_lbs' => 140,
        'calorie_deficit_pct' => 15,
        'activity_factor' => ActivityFactor::ModeratelyActive->value,
        'exercise_factor' => ExerciseFactor::Light->value,
        'goal' => Goal::Cut->value,
        'daily_calorie_target' => 1500,
    ]);

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->assertSet('gender', Gender::Female->value)
        ->assertSet('age', 28)
        ->assertSet('height_feet', 5)
        ->assertSet('height_inches', 4)
        ->assertSet('weight_lbs', 140)
        ->assertSet('calorie_deficit_pct', 15)
        ->assertSet('activity_factor', ActivityFactor::ModeratelyActive->value)
        ->assertSet('exercise_factor', ExerciseFactor::Light->value)
        ->assertSet('goal', Goal::Cut->value)
        ->assertSet('daily_calorie_target', 1500);
});

it('validates that age is required and within range', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('age', 0)
        ->call('save')
        ->assertHasErrors(['age']);
});

it('validates that weight is within range', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('weight_lbs', 10)
        ->call('save')
        ->assertHasErrors(['weight_lbs']);
});

it('validates that height feet is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->call('save')
        ->assertHasErrors(['height_feet']);
});

it('validates that height inches is between 0 and 11', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)
        ->set('height_inches', 12)
        ->call('save')
        ->assertHasErrors(['height_inches']);
});

it('validates that deficit percentage is within range', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)
        ->set('calorie_deficit_pct', 60)
        ->call('save')
        ->assertHasErrors(['calorie_deficit_pct']);
});

it('saves all TDEE calculator fields to the profile', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('gender', Gender::Female->value)
        ->set('age', 25)
        ->set('height_feet', 5)
        ->set('height_inches', 5)
        ->set('weight_lbs', 130)
        ->set('goal_weight_lbs', 120)
        ->set('start_date', '2026-01-01')
        ->set('activity_factor', ActivityFactor::LightlyActive->value)
        ->set('exercise_factor', ExerciseFactor::Light->value)
        ->set('goal', Goal::Cut->value)
        ->set('calorie_deficit_pct', 20)
        ->call('save');

    $profile = $user->fresh()->calorieProfile;

    expect($profile)
        ->gender->toBe(Gender::Female)
        ->age->toBe(25)
        ->height_feet->toBe(5)
        ->height_inches->toBe(5)
        ->weight_lbs->toBe(130)
        ->goal_weight_lbs->toBe(120)
        ->activity_factor->toBe(ActivityFactor::LightlyActive)
        ->exercise_factor->toBe(ExerciseFactor::Light)
        ->goal->toBe(Goal::Cut)
        ->calorie_deficit_pct->toBe(20);

    expect($profile->start_date->toDateString())->toBe('2026-01-01');
    expect($profile->tdee)->toBeGreaterThan(0);
});

it('saves a lean mass profile and computes TDEE via Katch-McArdle', function () {
    $user = User::factory()->create();

    // 200 lbs, 15% body fat, sedentary, no exercise → TDEE = 2442
    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('formula', FormulaType::LeanMass->value)
        ->set('weight_lbs', 200)
        ->set('body_fat_pct', 15)
        ->set('activity_factor', ActivityFactor::Sedentary->value)
        ->set('exercise_factor', ExerciseFactor::None->value)
        ->set('goal', Goal::Maintain->value)
        ->call('save');

    $profile = $user->fresh()->calorieProfile;

    expect($profile)
        ->formula->toBe(FormulaType::LeanMass)
        ->body_fat_pct->toBe(15.0)
        ->weight_lbs->toBe(200)
        ->tdee->toBe(2442)
        ->daily_calorie_target->toBe(2442);
});

it('lean mass formula produces a higher TDEE than standard for a muscular user', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);

    $component
        ->set('formula', FormulaType::Standard->value)
        ->set('gender', Gender::Male->value)
        ->set('age', 35)
        ->set('height_feet', 6)
        ->set('height_inches', 0)
        ->set('weight_lbs', 200)
        ->set('activity_factor', ActivityFactor::Sedentary->value)
        ->set('exercise_factor', ExerciseFactor::None->value);

    $standardTdee = $component->get('computedTdee');

    $component
        ->set('formula', FormulaType::LeanMass->value)
        ->set('body_fat_pct', 10);

    $leanTdee = $component->get('computedTdee');

    // At 10% body fat, lean mass formula should yield more calories than standard
    expect($leanTdee)->toBeGreaterThan($standardTdee);
});

it('shows BMR in the preview when measurements are complete', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component);

    // Male, 30, 5'10", 170 lbs → BMR = 1737
    expect($component->get('computedBmr'))->toBe(1737);
});

it('computes days to goal when goal weight and a cut are set', function () {
    $user = User::factory()->create();

    // TDEE = 2085 (sedentary, no exercise), 20% cut → target ≈ 1668, deficit ≈ 417/day
    // 30 lbs to lose × 3500 / 417 = 251 days (ceil)
    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)
        ->set('goal', Goal::Cut->value)
        ->set('goal_weight_lbs', 140);

    expect($component->get('computedDaysToGoal'))->toBeGreaterThan(0);
    expect($component->get('computedTargetDate'))->not->toBeNull();
});

it('returns null for days to goal when no goal weight is set', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)->set('goal', Goal::Cut->value);

    expect($component->get('computedDaysToGoal'))->toBeNull();
});

it('returns null for days to goal on a maintain goal', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Setup::class);
    withMeasurements($component)
        ->set('goal', Goal::Maintain->value)
        ->set('goal_weight_lbs', 160);

    expect($component->get('computedDaysToGoal'))->toBeNull();
});

it('requires body_fat_pct when lean mass formula is selected', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('formula', FormulaType::LeanMass->value)
        ->set('weight_lbs', 200)
        ->call('save')
        ->assertHasErrors(['body_fat_pct']);
});
