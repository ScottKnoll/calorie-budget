<?php

use App\Livewire\Budget\DailyEntry;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

it('redirects guests away from the log page', function () {
    $this->get(route('budget.log'))->assertRedirect(route('login'));
});

it('shows the log page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.log'))
        ->assertSuccessful();
});

it('creates a new calorie entry for today', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1800)
        ->call('save');

    expect(CalorieEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->first())
        ->not->toBeNull()
        ->calories_consumed->toBe(1800);
});

it('updates an existing entry for the same day', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 1500]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1900)
        ->call('save');

    expect(CalorieEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->count())
        ->toBe(1);

    expect(CalorieEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->first()->calories_consumed)
        ->toBe(1900);
});

it('mounts with existing entry values', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();
    CalorieEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_consumed' => 1650]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('calories_consumed', 1650);
});

it('computes over/under correctly', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    $component = Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 2300);

    expect($component->instance()->overUnder)->toBe(300);
    expect($component->instance()->remaining)->toBe(-300);
});

it('validates that calories consumed must be a non-negative integer', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', -1)
        ->call('save')
        ->assertHasErrors(['calories_consumed']);
});
