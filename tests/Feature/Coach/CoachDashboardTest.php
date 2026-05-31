<?php

use App\Livewire\Coach\ClientProfile;
use App\Livewire\Coach\Dashboard;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

// --- Coach dashboard ---

it('allows a coach to access the coach dashboard', function () {
    $coach = User::factory()->asCoach()->create();

    $this->actingAs($coach)
        ->get(route('coach.dashboard'))
        ->assertSuccessful();
});

it('denies a member from accessing the coach dashboard', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('coach.dashboard'))
        ->assertForbidden();
});

it('denies a client from accessing the coach dashboard', function () {
    $client = User::factory()->withCompletedIntake()->create();

    $this->actingAs($client)
        ->get(route('coach.dashboard'))
        ->assertForbidden();
});

it('shows a list of clients on the coach dashboard', function () {
    $coach = User::factory()->asCoach()->create();
    $clients = User::factory()->asClient()->count(3)->create();

    $component = Livewire::actingAs($coach)->test(Dashboard::class);

    expect($component->instance()->clients)->toHaveCount(3);
});

it('shows only clients, not members or coaches, on the dashboard', function () {
    $coach = User::factory()->asCoach()->create();
    User::factory()->asClient()->count(2)->create();
    User::factory()->count(2)->create();

    $component = Livewire::actingAs($coach)->test(Dashboard::class);

    expect($component->instance()->clients)->toHaveCount(2);
});

// --- Client profile ---

it('allows a coach to view a client profile', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->withCompletedIntake()->create();

    $this->actingAs($coach)
        ->get(route('coach.clients.show', $client))
        ->assertSuccessful();
});

it('denies a member from viewing a client profile', function () {
    $member = User::factory()->create();
    $client = User::factory()->withCompletedIntake()->create();

    $this->actingAs($member)
        ->get(route('coach.clients.show', $client))
        ->assertForbidden();
});

it('returns 404 when trying to view a non-client user profile', function () {
    $coach = User::factory()->asCoach()->create();
    $member = User::factory()->create();

    $this->actingAs($coach)
        ->get(route('coach.clients.show', $member))
        ->assertNotFound();
});

it('allows a coach to delete a client', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(Dashboard::class)
        ->call('deleteClient', $client->id);

    expect(User::find($client->id))->toBeNull();
});

it('does not allow deleting a non-client user', function () {
    $coach = User::factory()->asCoach()->create();
    $member = User::factory()->create();

    expect(fn () => Livewire::actingAs($coach)
        ->test(Dashboard::class)
        ->call('deleteClient', $member->id)
    )->toThrow(ModelNotFoundException::class);

    expect(User::find($member->id))->not->toBeNull();
});

it('shows calorie profile data on the client profile page', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    $client->calorieProfile()->create(
        CalorieProfile::factory()->make(['user_id' => $client->id])->toArray()
    );

    $profile = $client->calorieProfile;

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->assertSee('Calorie Profile')
        ->assertSee(number_format($profile->tdee))
        ->assertSee(number_format($profile->daily_calorie_target))
        ->assertSee($profile->goal->label());
});

it('shows client intake data on the profile page', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->withCompletedIntake()->create();

    $client->intakeResponse()->create([
        'main_goal' => 'fat_loss',
        'work_schedule' => 'nine_to_five',
        'open_to_tracking_steps' => 'yes',
        'daily_steps' => 'moderate',
        'sleep_hours' => 'seven_to_eight',
        'stress_level' => 'moderate',
        'has_injuries' => 'no',
        'workout_days_per_week' => 'three_four',
        'tracks_currently' => 'no',
        'open_to_tracking' => 'yes_comfortable',
    ]);

    $component = Livewire::actingAs($coach)->test(ClientProfile::class, ['client' => $client]);

    $component->assertSee('Lose fat / lean out')
        ->assertSee($client->name);
});
