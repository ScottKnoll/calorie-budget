<?php

use App\Livewire\Budget\MyPlan;
use App\Livewire\Coach\PlanEditor;
use App\Models\ClientPlan;
use App\Models\PlanSection;
use App\Models\User;
use Livewire\Livewire;

// --- Access ---

it('allows a coach to access the plan editor for a client', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    $this->actingAs($coach)
        ->get(route('coach.clients.plans.create', $client))
        ->assertSuccessful();
});

it('denies a member from accessing the plan editor', function () {
    $member = User::factory()->create();
    $client = User::factory()->asClient()->create();

    $this->actingAs($member)
        ->get(route('coach.clients.plans.create', $client))
        ->assertForbidden();
});

it('returns 404 when creating a plan for a non-client user', function () {
    $coach = User::factory()->asCoach()->create();
    $member = User::factory()->create();

    $this->actingAs($coach)
        ->get(route('coach.clients.plans.create', $member))
        ->assertNotFound();
});

// --- Create ---

it('creates a plan with sections', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client])
        ->set('title', 'Initial Plan — May 2026')
        ->set('sections.0.title', 'Nutrition')
        ->set('sections.0.body', '<p>Eat protein with every meal.</p>')
        ->call('save');

    $plan = ClientPlan::where('user_id', $client->id)->first();
    expect($plan)->not->toBeNull();
    expect($plan->title)->toBe('Initial Plan — May 2026');
    expect($plan->sections)->toHaveCount(1);
    expect($plan->sections->first()->title)->toBe('Nutrition');
});

it('requires a plan title', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client])
        ->set('title', '')
        ->set('sections.0.title', 'Nutrition')
        ->call('save')
        ->assertHasErrors(['title']);
});

it('requires each section to have a title', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client])
        ->set('title', 'My Plan')
        ->set('sections.0.title', '')
        ->call('save')
        ->assertHasErrors(['sections.0.title']);
});

// --- Edit ---

it('loads existing plan data into the editor', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $plan = ClientPlan::factory()->for($client, 'user')->create(['title' => 'Week 4 Plan']);
    PlanSection::factory()->for($plan, 'plan')->create(['title' => 'Activity', 'position' => 0]);

    $component = Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client, 'plan' => $plan]);

    expect($component->get('title'))->toBe('Week 4 Plan');
    expect($component->get('sections.0.title'))->toBe('Activity');
});

it('updates an existing plan', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $plan = ClientPlan::factory()->for($client, 'user')->create(['title' => 'Old Title']);
    PlanSection::factory()->for($plan, 'plan')->create(['title' => 'Sleep', 'position' => 0]);

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client, 'plan' => $plan])
        ->set('title', 'Updated Plan')
        ->call('save');

    expect($plan->fresh()->title)->toBe('Updated Plan');
});

// --- Client read-only view ---

it('shows the latest plan to a client', function () {
    $client = User::factory()->asClient()->create();
    $plan = ClientPlan::factory()->for($client, 'user')->create(['title' => 'My Plan']);
    PlanSection::factory()->for($plan, 'plan')->create(['title' => 'Nutrition', 'body' => '<p>Eat well.</p>', 'position' => 0]);

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('My Plan')
        ->assertSee('Nutrition');
});

it('shows an empty state when no plan exists', function () {
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('No plan yet');
});

it('redirects non-clients away from my-plan', function () {
    $member = User::factory()->create();

    Livewire::actingAs($member)
        ->test(MyPlan::class)
        ->assertRedirect(route('dashboard'));
});
