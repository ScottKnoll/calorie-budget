<?php

use App\Livewire\Budget\CalorieChart;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('defaults to the week period', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->chartPeriod)->toBe('week');
});

it('scopes entries to the current week by default', function () {
    $user = User::factory()->create();

    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 2000,
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today()->subDays(30),
        'calories_consumed' => 1800,
    ]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->entries)->toHaveCount(1);
});

it('scopes entries to the current month when period is month', function () {
    $user = User::factory()->create();

    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::now()->startOfMonth(),
        'calories_consumed' => 2000,
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::now()->startOfMonth()->subDay(),
        'calories_consumed' => 1800,
    ]);

    $component = Livewire::actingAs($user)
        ->test(CalorieChart::class)
        ->set('chartPeriod', 'month');

    expect($component->instance()->entries)->toHaveCount(1);
});

it('scopes entries to the current year when period is year', function () {
    $user = User::factory()->create();

    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::now()->startOfYear(),
        'calories_consumed' => 2000,
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::now()->startOfYear()->subDay(),
        'calories_consumed' => 1800,
    ]);

    $component = Livewire::actingAs($user)
        ->test(CalorieChart::class)
        ->set('chartPeriod', 'year');

    expect($component->instance()->entries)->toHaveCount(1);
});

it('computes average calories for the period', function () {
    $user = User::factory()->create();

    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'calories_consumed' => 2000,
    ]);
    CalorieEntry::factory()->for($user)->create([
        'date' => Carbon::today()->subDay(),
        'calories_consumed' => 1000,
    ]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->averageCalories)->toBe(1500);
});

it('returns null average when no entries exist', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->averageCalories)->toBeNull();
});

it('counts days logged in the period', function () {
    $user = User::factory()->create();

    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 2000]);
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today()->subDay(), 'calories_consumed' => 1800]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->daysLogged)->toBe(2);
});

it('counts days over target', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 2500]);
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today()->subDay(), 'calories_consumed' => 1800]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->daysOverTarget)->toBe(1);
});

it('returns null daysOverTarget when no profile exists', function () {
    $user = User::factory()->create();
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 2500]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->daysOverTarget)->toBeNull();
});

it('returns null chartData when fewer than 2 entries exist', function () {
    $user = User::factory()->create();
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 2000]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->chartData)->toBeNull();
});

it('returns chart data with points and targetY when 2+ entries exist', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today()->subDay(), 'calories_consumed' => 1800]);
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 2200]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    $chart = $component->instance()->chartData;
    expect($chart)->not->toBeNull();
    expect($chart['points'])->toBeString()->not->toBeEmpty();
    expect($chart['targetY'])->toBeFloat();
});

it('returns null targetY in chartData when no profile is set', function () {
    $user = User::factory()->create();

    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today()->subDay(), 'calories_consumed' => 1800]);
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 2200]);

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->chartData['targetY'])->toBeNull();
});

it('returns the correct period label for each period', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(CalorieChart::class);

    expect($component->instance()->periodLabel)->toBe('This Week');

    $component->set('chartPeriod', 'month');
    expect($component->instance()->periodLabel)->toBe('This Month');

    $component->set('chartPeriod', 'year');
    expect($component->instance()->periodLabel)->toBe('This Year');
});

