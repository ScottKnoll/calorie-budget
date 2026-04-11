<?php

use App\Livewire\Budget\WeeklySummary;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('redirects guests away from the summary page', function () {
    $this->get(route('budget.summary'))->assertRedirect(route('login'));
});

it('shows the summary page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.summary'))
        ->assertSuccessful();
});

it('shows 7 days in the current week', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);

    expect($component->instance()->days)->toHaveCount(7);
});

it('correctly computes the weekly balance from logged entries', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $monday = Carbon::now()->startOfWeek();

    CalorieEntry::factory()->for($user)->create(['date' => $monday, 'calories_consumed' => 2200]);
    CalorieEntry::factory()->for($user)->create(['date' => $monday->copy()->addDay(), 'calories_consumed' => 1800]);

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);

    expect($component->instance()->weeklyBalance)->toBe(0);
});

it('returns null over_under for days with no entry', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);
    $days = $component->instance()->days;

    $days->each(fn ($day) => expect($day['over_under'])->toBeNull());
});

it('includes macro grams in each day row', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $monday = Carbon::now()->startOfWeek();

    CalorieEntry::factory()->for($user)->create([
        'date' => $monday,
        'calories_consumed' => 1800,
        'carbs_grams' => 200,
        'protein_grams' => 150,
        'fat_grams' => 60,
    ]);

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);
    $firstDay = $component->instance()->days->first();

    expect($firstDay['carbs_grams'])->toBe(200);
    expect($firstDay['protein_grams'])->toBe(150);
    expect($firstDay['fat_grams'])->toBe(60);
});

it('returns null macro grams for days with no entry', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);

    $component->instance()->days->each(function (array $day) {
        expect($day['carbs_grams'])->toBeNull();
        expect($day['protein_grams'])->toBeNull();
        expect($day['fat_grams'])->toBeNull();
    });
});

it('returns null for weeklyMacroSummary when no macro data logged', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::now()->startOfWeek(),
        'calories_consumed' => 1800,
    ]);

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);

    expect($component->instance()->weeklyMacroSummary)->toBeNull();
});

it('computes weekly macro summary totals and deltas correctly', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create([
        'daily_calorie_target' => 2000,
        'carb_pct' => 50,
        'protein_pct' => 30,
        'fat_pct' => 20,
    ]);

    // Daily gram targets: carbs = 250g, protein = 150g, fat = 44g
    // Weekly targets: carbs = 1750g, protein = 1050g, fat = 308g
    $monday = Carbon::now()->startOfWeek();

    CalorieEntry::factory()->for($user)->create([
        'date' => $monday,
        'calories_consumed' => 1800,
        'carbs_grams' => 200,
        'protein_grams' => 140,
        'fat_grams' => 50,
    ]);

    $component = Livewire::actingAs($user)->test(WeeklySummary::class);
    $summary = $component->instance()->weeklyMacroSummary;

    expect($summary)->not->toBeNull();
    expect($summary['carbs']['consumed'])->toBe(200);
    expect($summary['carbs']['target'])->toBe(1750);
    expect($summary['carbs']['delta'])->toBe(200 - 1750);

    expect($summary['protein']['consumed'])->toBe(140);
    expect($summary['protein']['target'])->toBe(1050);
    expect($summary['protein']['delta'])->toBe(140 - 1050);

    expect($summary['fat']['consumed'])->toBe(50);
    expect($summary['fat']['target'])->toBe(308);
    expect($summary['fat']['delta'])->toBe(50 - 308);
});

it('navigates to the previous and next week', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    $thisMonday = Carbon::now()->startOfWeek()->toDateString();
    $lastMonday = Carbon::now()->startOfWeek()->subWeek()->toDateString();
    $nextMonday = Carbon::now()->startOfWeek()->addWeek()->toDateString();

    Livewire::actingAs($user)
        ->test(WeeklySummary::class)
        ->assertSet('weekStart', $thisMonday)
        ->call('previousWeek')
        ->assertSet('weekStart', $lastMonday)
        ->call('nextWeek')
        ->call('nextWeek')
        ->assertSet('weekStart', $nextMonday);
});
