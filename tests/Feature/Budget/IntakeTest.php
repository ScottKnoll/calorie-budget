<?php

use App\Enums\UserType;
use App\Livewire\Budget\Intake;
use App\Models\IntakeResponse;
use App\Models\User;
use Livewire\Livewire;

// --- Registration flow ---

it('registers a new user as client when type=client is passed', function () {
    $this->post(route('register.store'), [
        'name' => 'Jane Client',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'type' => 'client',
    ])->assertSessionHasNoErrors();

    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->user_type)->toBe(UserType::Client);
});

it('registers a normal user as personal when no type is passed', function () {
    $this->post(route('register.store'), [
        'name' => 'John Personal',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors();

    $user = User::where('email', 'john@example.com')->first();
    expect($user->user_type)->toBe(UserType::Personal);
});

it('redirects a new client user to the intake page after registration', function () {
    $this->post(route('register.store'), [
        'name' => 'New Client',
        'email' => 'newclient@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'type' => 'client',
    ])->assertRedirect(route('budget.intake'));
});

it('redirects a personal user to the dashboard after registration', function () {
    $this->post(route('register.store'), [
        'name' => 'Personal User',
        'email' => 'personal@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));
});

// --- Guest redirect ---

it('redirects guests visiting /intake to the login page', function () {
    $this->get(route('budget.intake'))
        ->assertRedirect(route('login'));
});

// --- Middleware ---

it('redirects a client without a completed intake away from dashboard', function () {
    $client = User::factory()->asClient()->create();

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertRedirect(route('budget.intake'));
});

it('does not redirect a personal user away from dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('does not redirect a client who has completed their intake', function () {
    $client = User::factory()->withCompletedIntake()->create();

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

// --- Intake component access ---

it('redirects guests from the intake page', function () {
    $this->get(route('budget.intake'))->assertRedirect(route('login'));
});

it('shows the intake page to client users who have not completed it', function () {
    $client = User::factory()->asClient()->create();

    $this->actingAs($client)
        ->get(route('budget.intake'))
        ->assertSuccessful();
});

it('redirects non-client users away from the intake page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Intake::class)
        ->assertRedirect(route('dashboard'));
});

it('redirects a client who already completed intake away from the intake page', function () {
    $client = User::factory()->withCompletedIntake()->create();

    Livewire::actingAs($client)
        ->test(Intake::class)
        ->assertRedirect(route('budget.setup'));
});

// --- Form submission ---

it('saves the intake response and marks user intake as completed', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(Intake::class)
        ->set('main_goal', 'fat_loss')
        ->set('why_now', 'Ready to make a change.')
        ->set('current_weight_lbs', 200)
        ->set('current_height_feet', 5)
        ->set('current_height_inches', 10)
        ->set('activity_level', 'sedentary')
        ->set('workout_experience', 'beginner')
        ->set('work_schedule', 'nine_to_five')
        ->set('daily_steps', 'low')
        ->set('sleep_hours', 'seven_to_eight')
        ->set('stress_level', 'moderate')
        ->set('tracks_currently', 'no')
        ->set('typical_day_of_eating', 'Coffee, sandwich, pasta.')
        ->set('dietary_restrictions', 'None')
        ->set('workout_days_per_week', 'three_four')
        ->set('open_to_tracking', 'loosely')
        ->call('submit')
        ->assertRedirect(route('budget.setup'));

    $intake = IntakeResponse::where('user_id', $client->id)->first();
    expect($intake)->not->toBeNull();
    expect($intake->main_goal)->toBe('fat_loss');
    expect($intake->activity_level)->toBe('sedentary');
    expect($intake->current_weight_lbs)->toBe(200);

    expect($client->fresh()->hasCompletedIntake())->toBeTrue();
});

it('requires main_goal to be selected', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(Intake::class)
        ->set('main_goal', '')
        ->call('submit')
        ->assertHasErrors(['main_goal']);
});
