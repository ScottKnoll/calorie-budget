<?php

use App\Livewire\Budget\CheckIn;
use App\Livewire\Budget\CheckIns;
use App\Models\CheckIn as CheckInModel;
use App\Models\User;
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
