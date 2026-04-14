<?php

use App\Enums\WorkoutType;
use App\Livewire\Budget\WorkoutLog;
use App\Livewire\Dashboard;
use App\Models\User;
use App\Models\WorkoutEntry;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('redirects guests away from the workouts page', function () {
    $this->get(route('budget.workouts'))->assertRedirect(route('login'));
});

it('shows the workouts page to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.workouts'))
        ->assertSuccessful();
});

it('adds a workout entry', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Lift->value)
        ->set('durationMinutes', 60)
        ->set('caloriesBurned', 400)
        ->set('notes', 'Heavy squats today')
        ->call('addEntry');

    $entry = WorkoutEntry::where('user_id', $user->id)->first();
    expect($entry)->not->toBeNull();
    expect($entry->workout_type)->toBe(WorkoutType::Lift);
    expect($entry->duration_minutes)->toBe(60);
    expect($entry->calories_burned)->toBe(400);
    expect($entry->notes)->toBe('Heavy squats today');
});

it('allows multiple workout entries on the same day', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Lift->value)
        ->set('durationMinutes', 60)
        ->call('addEntry');

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Cardio->value)
        ->set('durationMinutes', 30)
        ->call('addEntry');

    expect(WorkoutEntry::where('user_id', $user->id)->whereDate('date', Carbon::today())->count())
        ->toBe(2);
});

it('adds a workout with a custom type', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Custom->value)
        ->set('customType', 'Rock Climbing')
        ->set('durationMinutes', 90)
        ->call('addEntry');

    $entry = WorkoutEntry::where('user_id', $user->id)->first();
    expect($entry->workout_type)->toBe(WorkoutType::Custom);
    expect($entry->custom_type)->toBe('Rock Climbing');
    expect($entry->typeLabel())->toBe('Rock Climbing');
});

it('validates that duration is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Lift->value)
        ->set('durationMinutes', null)
        ->call('addEntry')
        ->assertHasErrors(['durationMinutes']);
});

it('validates that custom type is required when workout type is custom', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Custom->value)
        ->set('customType', '')
        ->set('durationMinutes', 45)
        ->call('addEntry')
        ->assertHasErrors(['customType']);
});

it('allows calories burned to be optional', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->set('date', Carbon::today()->toDateString())
        ->set('workoutType', WorkoutType::Run->value)
        ->set('durationMinutes', 30)
        ->set('caloriesBurned', null)
        ->call('addEntry');

    expect(WorkoutEntry::where('user_id', $user->id)->first()->calories_burned)->toBeNull();
});

it('shows entries on the workout log page', function () {
    $user = User::factory()->create();
    WorkoutEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'workout_type' => WorkoutType::Cardio,
        'duration_minutes' => 45,
    ]);

    $component = Livewire::actingAs($user)->test(WorkoutLog::class);

    expect($component->instance()->entries)->toHaveCount(1);
});

it('computes weekly workout count', function () {
    $user = User::factory()->create();

    WorkoutEntry::factory()->for($user)->create(['date' => Carbon::today()]);
    WorkoutEntry::factory()->for($user)->create(['date' => Carbon::now()->startOfWeek()]);
    WorkoutEntry::factory()->for($user)->create(['date' => Carbon::today()->subDays(10)]);

    $component = Livewire::actingAs($user)->test(WorkoutLog::class);

    expect($component->instance()->weeklyCount)->toBe(2);
});

it('computes weekly calories burned', function () {
    $user = User::factory()->create();

    WorkoutEntry::factory()->for($user)->create(['date' => Carbon::today(), 'calories_burned' => 300]);
    WorkoutEntry::factory()->for($user)->create(['date' => Carbon::now()->startOfWeek(), 'calories_burned' => 200]);
    WorkoutEntry::factory()->for($user)->create(['date' => Carbon::today()->subDays(10), 'calories_burned' => 500]);

    $component = Livewire::actingAs($user)->test(WorkoutLog::class);

    expect($component->instance()->weeklyCaloriesBurned)->toBe(500);
});

it('allows editing a workout entry', function () {
    $user = User::factory()->create();
    $entry = WorkoutEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'workout_type' => WorkoutType::Lift,
        'duration_minutes' => 60,
        'calories_burned' => 400,
        'notes' => 'Old notes',
    ]);

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->call('startEditing', $entry->id)
        ->assertSet('editingId', $entry->id)
        ->assertSet('editingDurationMinutes', 60)
        ->set('editingDurationMinutes', 75)
        ->set('editingNotes', 'New notes')
        ->call('updateEntry')
        ->assertSet('editingId', null);

    expect($entry->fresh()->duration_minutes)->toBe(75);
    expect($entry->fresh()->notes)->toBe('New notes');
});

it('validates editingDurationMinutes during update', function () {
    $user = User::factory()->create();
    $entry = WorkoutEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'workout_type' => WorkoutType::Lift,
        'duration_minutes' => 60,
    ]);

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->call('startEditing', $entry->id)
        ->set('editingDurationMinutes', 0)
        ->call('updateEntry')
        ->assertHasErrors(['editingDurationMinutes']);
});

it('allows deleting a workout entry', function () {
    $user = User::factory()->create();
    $entry = WorkoutEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'workout_type' => WorkoutType::Lift,
        'duration_minutes' => 60,
    ]);

    Livewire::actingAs($user)
        ->test(WorkoutLog::class)
        ->call('deleteEntry', $entry->id);

    expect(WorkoutEntry::find($entry->id))->toBeNull();
});

it('cannot edit or delete another user\'s workout entry', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $entry = WorkoutEntry::factory()->for($owner)->create([
        'date' => Carbon::today(),
        'workout_type' => WorkoutType::Lift,
        'duration_minutes' => 60,
    ]);

    Livewire::actingAs($attacker)
        ->test(WorkoutLog::class)
        ->call('startEditing', $entry->id)
        ->assertSet('editingId', null);

    Livewire::actingAs($attacker)
        ->test(WorkoutLog::class)
        ->call('deleteEntry', $entry->id);

    expect(WorkoutEntry::find($entry->id))->not->toBeNull();
});

it('shows today\'s workouts on the dashboard', function () {
    $user = User::factory()->create();
    WorkoutEntry::factory()->for($user)->create([
        'date' => Carbon::today(),
        'workout_type' => WorkoutType::Hiit,
        'duration_minutes' => 30,
        'calories_burned' => 350,
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->instance()->todaysWorkouts)->toHaveCount(1);
    expect($component->instance()->weeklyWorkoutCount)->toBe(1);
    expect($component->instance()->weeklyCaloriesBurned)->toBe(350);
});

it('excludes other users\' workouts from dashboard counts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    WorkoutEntry::factory()->for($other)->create(['date' => Carbon::today(), 'workout_type' => WorkoutType::Run, 'duration_minutes' => 30]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->instance()->todaysWorkouts)->toHaveCount(0);
    expect($component->instance()->weeklyWorkoutCount)->toBe(0);
});
