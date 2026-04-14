<?php

use App\Livewire\Budget\DailyEntry;
use App\Livewire\Budget\WeightLog;
use App\Models\CalorieProfile;
use App\Models\User;
use App\Models\WeightEntry;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('redirects guests away from the weight log page', function () {
    $this->get(route('budget.weight'))->assertRedirect(route('login'));
});

it('shows the weight log page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.weight'))
        ->assertSuccessful();
});

it('saves weight alongside a calorie entry on the daily log', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1800)
        ->set('weight_lbs', 185.5)
        ->call('save');

    expect(WeightEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->first())
        ->not->toBeNull()
        ->weight_lbs->toBe(185.5);
});

it('skips creating a weight entry when weight is not provided', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1800)
        ->call('save');

    expect(WeightEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->count())
        ->toBe(0);
});

it('updates an existing weight entry on the same day', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['daily_calorie_target' => 2000]);
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 190.0]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1800)
        ->set('weight_lbs', 188.5)
        ->call('save');

    expect(WeightEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->count())
        ->toBe(1);

    expect(WeightEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->first()->weight_lbs)
        ->toBe(188.5);
});

it('loads existing weight when navigating to a day with a weight entry', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 184.0]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('weight_lbs', 184.0);
});

it('clears weight when navigating to a day with no weight entry', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 184.0]);

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->assertSet('weight_lbs', 184.0)
        ->call('previousDay')
        ->assertSet('weight_lbs', null);
});

it('validates that weight must be between 50 and 999', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DailyEntry::class)
        ->set('calories_consumed', 1800)
        ->set('weight_lbs', 10)
        ->call('save')
        ->assertHasErrors(['weight_lbs']);
});

it('shows entries on the weight log page', function () {
    $user = User::factory()->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 183.0]);

    $component = Livewire::actingAs($user)->test(WeightLog::class);

    expect($component->instance()->entries)->toHaveCount(1);
    expect($component->instance()->latestEntry->weight_lbs)->toBe(183.0);
});

it('computes net change from starting to latest entry', function () {
    $user = User::factory()->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today()->subDays(7), 'weight_lbs' => 190.0]);
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    $component = Livewire::actingAs($user)->test(WeightLog::class);

    expect($component->instance()->netChange)->toBe(-5.0);
});

it('returns null net change when only one entry exists', function () {
    $user = User::factory()->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    $component = Livewire::actingAs($user)->test(WeightLog::class);

    expect($component->instance()->netChange)->toBeNull();
});

it('computes remaining to goal weight', function () {
    $user = User::factory()->create();
    CalorieProfile::factory()->for($user)->create(['goal_weight_lbs' => 175]);
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    $component = Livewire::actingAs($user)->test(WeightLog::class);

    expect($component->instance()->remainingToGoal)->toBe(10.0);
});

it('allows editing a weight entry from the weight log', function () {
    $user = User::factory()->create();
    $entry = WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    Livewire::actingAs($user)
        ->test(WeightLog::class)
        ->call('startEditing', $entry->id)
        ->assertSet('editingId', $entry->id)
        ->assertSet('editingWeight', 185.0)
        ->set('editingWeight', 183.5)
        ->call('updateEntry')
        ->assertSet('editingId', null);

    expect($entry->fresh()->weight_lbs)->toBe(183.5);
});

it('validates editingWeight during update', function () {
    $user = User::factory()->create();
    $entry = WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    Livewire::actingAs($user)
        ->test(WeightLog::class)
        ->call('startEditing', $entry->id)
        ->set('editingWeight', 10)
        ->call('updateEntry')
        ->assertHasErrors(['editingWeight']);
});

it('allows deleting a weight entry from the weight log', function () {
    $user = User::factory()->create();
    $entry = WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    Livewire::actingAs($user)
        ->test(WeightLog::class)
        ->call('deleteEntry', $entry->id);

    expect(WeightEntry::find($entry->id))->toBeNull();
});

it('cannot edit or delete another user\'s weight entry', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $entry = WeightEntry::factory()->for($owner)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    Livewire::actingAs($attacker)
        ->test(WeightLog::class)
        ->call('startEditing', $entry->id)
        ->assertSet('editingId', null);

    Livewire::actingAs($attacker)
        ->test(WeightLog::class)
        ->call('deleteEntry', $entry->id);

    expect(WeightEntry::find($entry->id))->not->toBeNull();
});

it('returns chart data when two or more entries exist', function () {
    $user = User::factory()->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today()->subDays(1), 'weight_lbs' => 190.0]);
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 188.0]);

    $component = Livewire::actingAs($user)->test(WeightLog::class);

    expect($component->instance()->chartData)->not->toBeNull();
});

it('returns null chart data when fewer than two entries exist', function () {
    $user = User::factory()->create();
    WeightEntry::factory()->for($user)->create(['date' => Carbon::today(), 'weight_lbs' => 185.0]);

    $component = Livewire::actingAs($user)->test(WeightLog::class);

    expect($component->instance()->chartData)->toBeNull();
});
