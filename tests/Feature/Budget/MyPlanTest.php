<?php

use App\Livewire\Budget\MyPlan;
use App\Models\ClientPlan;
use App\Models\User;
use Livewire\Livewire;

it('redirects non-clients away from the my-plan page', function () {
    $member = User::factory()->create();

    Livewire::actingAs($member)
        ->test(MyPlan::class)
        ->assertRedirect(route('dashboard'));
});

it('shows the plan body when a plan exists', function () {
    $client = User::factory()->asClient()->create();
    ClientPlan::factory()->for($client, 'user')->create([
        'title' => 'My Coaching Plan',
        'body' => '<p>Train hard, eat well.</p>',
    ]);

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('My Coaching Plan')
        ->assertSee('Train hard, eat well.');
});

it('shows an empty state when no plan exists', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('No plan yet');
});

it('shows intake data on the my-plan page when intake is completed', function () {
    $client = User::factory()->asClient()->create();

    $client->intakeResponse()->create([
        'main_goal' => 'fat_loss',
        'work_schedule' => 'nine_to_five',
        'open_to_tracking_steps' => 'yes',
        'sleep_hours' => 'seven_to_eight',
        'stress_level' => 'moderate',
        'has_injuries' => 'no',
        'workout_days_per_week' => 'three_four',
        'tracks_currently' => 'no',
        'open_to_tracking' => 'yes_comfortable',
    ]);

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('Lose fat / lean out')
        ->assertSee('Goal');
});

it('shows an empty state on the intake tab when no intake exists', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('No intake on file');
});
