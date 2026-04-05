<?php

use App\Enums\Goal;
use App\Livewire\Budget\Setup;
use App\Models\CalorieProfile;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the setup page', function () {
    $this->get(route('budget.setup'))->assertRedirect(route('login'));
});

it('shows the setup page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.setup'))
        ->assertSuccessful();
});

it('creates a calorie profile on first save', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('tdee', 2200)
        ->set('goal', Goal::Cut->value)
        ->set('daily_calorie_target', 1700)
        ->call('save');

    expect(CalorieProfile::where('user_id', $user->id)->first())
        ->not->toBeNull()
        ->tdee->toBe(2200)
        ->goal->toBe(Goal::Cut)
        ->daily_calorie_target->toBe(1700);
});

it('updates an existing calorie profile', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['tdee' => 2000, 'daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('tdee', 2500)
        ->set('goal', Goal::Bulk->value)
        ->set('daily_calorie_target', 2800)
        ->call('save');

    expect($user->fresh()->calorieProfile)
        ->tdee->toBe(2500)
        ->daily_calorie_target->toBe(2800);
});

it('validates that TDEE is required and within range', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('tdee', 100)
        ->call('save')
        ->assertHasErrors(['tdee']);
});

it('suggests a 20% deficit when goal is set to cut', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('tdee', 2000)
        ->set('goal', Goal::Cut->value)
        ->assertSet('daily_calorie_target', 1600);
});

it('suggests a 20% surplus when goal is set to bulk', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('tdee', 2000)
        ->set('goal', Goal::Bulk->value)
        ->assertSet('daily_calorie_target', 2400);
});

it('suggests the same as TDEE when goal is maintain', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('tdee', 2000)
        ->set('goal', Goal::Maintain->value)
        ->assertSet('daily_calorie_target', 2000);
});

it('updates the suggestion when TDEE changes', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('goal', Goal::Cut->value)
        ->set('tdee', 2500)
        ->assertSet('daily_calorie_target', 2000);
});

it('preserves a manually entered daily calorie target when TDEE changes', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->set('goal', Goal::Cut->value)
        ->set('daily_calorie_target', 1500)
        ->set('tdee', 2500)
        ->assertSet('daily_calorie_target', 1500);
});

it('mounts with existing profile values', function () {
    $user = User::factory()->create();
    $profile = CalorieProfile::factory()->for($user)->create([
        'tdee' => 2100,
        'goal' => Goal::Maintain->value,
        'daily_calorie_target' => 2100,
    ]);

    Livewire::actingAs($user)
        ->test(Setup::class)
        ->assertSet('tdee', 2100)
        ->assertSet('goal', Goal::Maintain->value)
        ->assertSet('daily_calorie_target', 2100);
});
