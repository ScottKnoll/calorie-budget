<?php

use App\Livewire\Budget\CheckIn;
use App\Livewire\Budget\CheckIns;
use App\Livewire\Coach\ClientProfile;
use App\Models\CheckIn as CheckInModel;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

// --- Route access ---

it('redirects guests from the check-in form', function () {
    $this->get(route('budget.check-in'))->assertRedirect(route('login'));
});

it('redirects guests from the check-ins history', function () {
    $this->get(route('budget.check-ins'))->assertRedirect(route('login'));
});

it('redirects non-client users away from the check-in form', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CheckIn::class)
        ->assertRedirect(route('dashboard'));
});

it('redirects non-client users away from the check-ins history', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CheckIns::class)
        ->assertRedirect(route('dashboard'));
});

it('shows the check-in form to client users', function () {
    $client = User::factory()->asClient()->create();

    $this->actingAs($client)
        ->get(route('budget.check-in'))
        ->assertSuccessful();
});

it('shows the check-ins history to client users', function () {
    $client = User::factory()->asClient()->create();

    $this->actingAs($client)
        ->get(route('budget.check-ins'))
        ->assertSuccessful();
});

// --- Form submission ---

it('saves a check-in and redirects to history', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(CheckIn::class)
        ->set('weight', '185.5')
        ->set('week_feeling', 'Overall a solid week.')
        ->set('went_well', 'Hit all my workouts.')
        ->set('felt_hardest', 'Eating out on Friday.')
        ->set('hunger_energy_sleep', 'Energy was great, sleep solid.')
        ->set('activity_consistency', '4 out of 4 planned workouts.')
        ->set('need_help', '')
        ->call('submit')
        ->assertRedirect(route('budget.check-ins'));

    $checkIn = CheckInModel::where('user_id', $client->id)->first();
    expect($checkIn)->not->toBeNull();
    expect($checkIn->weight)->toBe(185.5);
    expect($checkIn->week_feeling)->toBe('Overall a solid week.');
    expect($checkIn->need_help)->toBeNull();
});

it('saves need_help when provided', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(CheckIn::class)
        ->set('weight', '200')
        ->set('week_feeling', 'Rough week.')
        ->set('went_well', 'Stayed hydrated.')
        ->set('felt_hardest', 'Cravings were bad.')
        ->set('hunger_energy_sleep', 'Hungry all the time.')
        ->set('activity_consistency', 'Only 1 workout.')
        ->set('need_help', 'How do I handle cravings?')
        ->call('submit')
        ->assertRedirect(route('budget.check-ins'));

    $checkIn = CheckInModel::where('user_id', $client->id)->first();
    expect($checkIn->need_help)->toBe('How do I handle cravings?');
});

// --- Validation ---

it('requires weight', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(CheckIn::class)
        ->set('weight', '')
        ->call('submit')
        ->assertHasErrors(['weight']);
});

it('requires weight to be numeric', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(CheckIn::class)
        ->set('weight', 'heavy')
        ->call('submit')
        ->assertHasErrors(['weight']);
});

it('requires all text fields', function (string $field) {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(CheckIn::class)
        ->set($field, '')
        ->call('submit')
        ->assertHasErrors([$field]);
})->with([
    'week_feeling',
    'went_well',
    'felt_hardest',
    'hunger_energy_sleep',
    'activity_consistency',
]);

it('does not require need_help', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(CheckIn::class)
        ->set('weight', '175')
        ->set('week_feeling', 'Good week.')
        ->set('went_well', 'Stayed consistent.')
        ->set('felt_hardest', 'Weekend meals.')
        ->set('hunger_energy_sleep', 'All good.')
        ->set('activity_consistency', '3 workouts.')
        ->set('need_help', '')
        ->call('submit')
        ->assertHasNoErrors(['need_help']);
});

// --- History view ---

it('shows past check-ins on the history page', function () {
    $client = User::factory()->asClient()->create();

    CheckInModel::factory()->create([
        'user_id' => $client->id,
        'week_feeling' => 'Great week overall.',
    ]);

    Livewire::actingAs($client)
        ->test(CheckIns::class)
        ->assertSee('Great week overall.');
});

it('does not show other clients check-ins', function () {
    $client = User::factory()->asClient()->create();
    $other = User::factory()->asClient()->create();

    CheckInModel::factory()->create([
        'user_id' => $other->id,
        'week_feeling' => 'Someone elses check-in.',
    ]);

    Livewire::actingAs($client)
        ->test(CheckIns::class)
        ->assertDontSee('Someone elses check-in.');
});

// --- Coach view ---

it('shows a clients check-ins on the coach client profile', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    CheckInModel::factory()->create([
        'user_id' => $client->id,
        'week_feeling' => 'Solid week of training.',
    ]);

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->assertSee('Solid week of training.');
});

it('shows an empty state when the client has no check-ins', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->assertSee('No check-ins yet');
});

// --- Coach notes ---

it('allows a coach to add notes to a check-in', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $checkIn = CheckInModel::factory()->create(['user_id' => $client->id]);

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->call('startEditingNotes', $checkIn->id)
        ->assertSet('editingCheckInId', $checkIn->id)
        ->set('coachWorkout', 'Keep up the 4x per week cadence.')
        ->set('coachNutrition', 'Hit your protein target daily.')
        ->set('coachFocusNextWeek', 'Prioritize sleep this week.')
        ->call('saveNotes');

    $checkIn->refresh();
    expect($checkIn->coach_workout)->toBe('Keep up the 4x per week cadence.');
    expect($checkIn->coach_nutrition)->toBe('Hit your protein target daily.');
    expect($checkIn->coach_focus_next_week)->toBe('Prioritize sleep this week.');
    expect($checkIn->coach_habits)->toBeNull();
});

it('resets editing state after saving notes', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $checkIn = CheckInModel::factory()->create(['user_id' => $client->id]);

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->call('startEditingNotes', $checkIn->id)
        ->call('saveNotes')
        ->assertSet('editingCheckInId', null);
});

it('cancels editing without saving', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $checkIn = CheckInModel::factory()->create(['user_id' => $client->id]);

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->call('startEditingNotes', $checkIn->id)
        ->set('coachWorkout', 'Some unsaved notes.')
        ->call('cancelEditingNotes')
        ->assertSet('editingCheckInId', null)
        ->assertSet('coachWorkout', '');

    expect($checkIn->fresh()->coach_workout)->toBeNull();
});

it('prevents a coach from editing another clients check-in', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $otherClient = User::factory()->asClient()->create();
    $otherCheckIn = CheckInModel::factory()->create(['user_id' => $otherClient->id]);

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->call('startEditingNotes', $otherCheckIn->id);
})->throws(ModelNotFoundException::class);

// --- Client sees coach notes ---

it('shows coach notes on the client check-ins page', function () {
    $client = User::factory()->asClient()->create();

    CheckInModel::factory()->create([
        'user_id' => $client->id,
        'coach_focus_next_week' => 'Focus on hitting your step goal.',
    ]);

    Livewire::actingAs($client)
        ->test(CheckIns::class)
        ->assertSee('Focus on hitting your step goal.');
});

it('shows awaiting feedback when coach has not responded', function () {
    $client = User::factory()->asClient()->create();

    CheckInModel::factory()->create(['user_id' => $client->id]);

    Livewire::actingAs($client)
        ->test(CheckIns::class)
        ->assertSee('Awaiting coach feedback');
});
