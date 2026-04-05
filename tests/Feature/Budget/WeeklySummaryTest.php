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
