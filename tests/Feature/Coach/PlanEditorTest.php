<?php

use App\Livewire\Budget\MyPlan;
use App\Livewire\Coach\ClientProfile;
use App\Livewire\Coach\PlanEditor;
use App\Models\ClientPlan;
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

it('creates a plan with a body', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client])
        ->set('title', 'Initial Plan — May 2026')
        ->set('body', '<p>Eat protein with every meal.</p>')
        ->call('save');

    $plan = ClientPlan::where('user_id', $client->id)->first();
    expect($plan)->not->toBeNull();
    expect($plan->title)->toBe('Initial Plan — May 2026');
    expect($plan->body)->toContain('Eat protein with every meal.');
});

it('requires a plan title', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client])
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title']);
});

it('allows an empty body', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client])
        ->set('title', 'My Plan')
        ->set('body', '')
        ->call('save')
        ->assertHasNoErrors();
});

// --- Edit ---

it('loads existing plan data into the editor', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $plan = ClientPlan::factory()->for($client, 'user')->create([
        'title' => 'Week 4 Plan',
        'body' => '<p>Keep up the great work.</p>',
    ]);

    $component = Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client, 'plan' => $plan]);

    expect($component->get('title'))->toBe('Week 4 Plan');
    expect($component->get('body'))->toContain('Keep up the great work.');
});

it('updates an existing plan', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    $plan = ClientPlan::factory()->for($client, 'user')->create(['title' => 'Old Title']);

    Livewire::actingAs($coach)
        ->test(PlanEditor::class, ['client' => $client, 'plan' => $plan])
        ->set('title', 'Updated Plan')
        ->set('body', '<p>New content here.</p>')
        ->call('save');

    expect($plan->fresh()->title)->toBe('Updated Plan');
    expect($plan->fresh()->body)->toContain('New content here.');
});

// --- Coach portal view ---

it('shows the plan body on the coach client profile', function () {
    $coach = User::factory()->asCoach()->create();
    $client = User::factory()->asClient()->create();
    ClientPlan::factory()->for($client, 'user')->create([
        'title' => 'Initial Plan',
        'body' => '<p>Eat 3 meals with protein.</p>',
    ]);

    Livewire::actingAs($coach)
        ->test(ClientProfile::class, ['client' => $client])
        ->assertSee('Initial Plan')
        ->assertSee('Eat 3 meals with protein.');
});

// --- Client read-only view ---

it('shows the latest plan to a client', function () {
    $client = User::factory()->asClient()->create();
    ClientPlan::factory()->for($client, 'user')->create([
        'title' => 'My Plan',
        'body' => '<p>Eat well.</p>',
    ]);

    Livewire::actingAs($client)
        ->test(MyPlan::class)
        ->assertSee('My Plan');
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
