<?php

use App\Enums\Goal;
use App\Livewire\Dashboard;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('shows fallback state on all cards when no profile exists', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSeeText('Complete your calorie setup');
});

it('returns null for remainingToday when no profile exists', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('remainingToday'))->toBeNull();
});

it('returns null for remainingToday when no entry logged today', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('remainingToday'))->toBeNull();
    expect($component->get('todaysEntry'))->toBeNull();
});

it('computes remaining today correctly when entry exists', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 1200,
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('remainingToday'))->toBe(800);
});

it('shows a negative remaining when over the daily target', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 2300,
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('remainingToday'))->toBe(-300);
});

it('returns zero days logged and null balance when no entries this week', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('daysLoggedThisWeek'))->toBe(0);
    expect($component->get('weeklyBalance'))->toBe(0);
});

it('computes weekly balance and days logged from entries this week', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $monday = Carbon::now()->startOfWeek();

    CalorieEntry::factory()->for($user)->create([
        'date' => $monday->toDateString(),
        'calories_consumed' => 2200, // +200
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => $monday->copy()->addDay()->toDateString(),
        'calories_consumed' => 1800, // −200
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('daysLoggedThisWeek'))->toBe(2);
    expect($component->get('weeklyBalance'))->toBe(0);
});

it('computes macro grams from profile calorie target', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'daily_calorie_target' => 2000,
        'carb_pct' => 50,
        'protein_pct' => 30,
        'fat_pct' => 20,
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    // 50% of 2000 / 4 cal/g = 250g carbs
    // 30% of 2000 / 4 cal/g = 150g protein
    // 20% of 2000 / 9 cal/g = 44g fat
    expect($component->get('computedCarbGrams'))->toBe(250);
    expect($component->get('computedProteinGrams'))->toBe(150);
    expect($component->get('computedFatGrams'))->toBe(44);
});

it('returns zero for macro grams when no profile exists', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('computedCarbGrams'))->toBe(0);
    expect($component->get('computedProteinGrams'))->toBe(0);
    expect($component->get('computedFatGrams'))->toBe(0);
});

it('returns the goal label from the profile', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['goal' => Goal::Cut->value]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('goalLabel'))->toBe('Cut');
});

it('returns null for goalLabel when no profile exists', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->get('goalLabel'))->toBeNull();
});

it('shows macro targets when no entry logged today', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'daily_calorie_target' => 2000,
        'carb_pct' => 50,
        'protein_pct' => 30,
        'fat_pct' => 20,
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSeeText('50%')
        ->assertSeeText('250g');
});

it('shows consumed vs target grams when today has macro data', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'daily_calorie_target' => 2000,
        'carb_pct' => 50,
        'protein_pct' => 30,
        'fat_pct' => 20,
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 1800,
        'carbs_grams' => 180,
        'protein_grams' => 130,
        'fat_grams' => 55,
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSeeText('180g')
        ->assertSeeText('/ 250g')
        ->assertSeeText('130g')
        ->assertSeeText('/ 150g')
        ->assertSeeText('55g')
        ->assertSeeText('/ 44g');
});

it('shows macro targets (not consumed) when entry has no macro data', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'daily_calorie_target' => 2000,
        'carb_pct' => 50,
        'protein_pct' => 30,
        'fat_pct' => 20,
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 1800,
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSeeText('50%')
        ->assertSeeText('250g');
});
