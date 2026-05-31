<?php

namespace App\Livewire\Coach;

use App\Models\ClientPlan;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mews\Purifier\Facades\Purifier;

#[Title('Plan Editor')]
class PlanEditor extends Component
{
    public User $client;

    public ?ClientPlan $plan = null;

    public string $title = '';

    public string $body = '';

    public function mount(User $client, ?ClientPlan $plan = null): void
    {
        if (! auth()->user()->isCoach()) {
            abort(403);
        }

        if (! $client->isClient()) {
            abort(404);
        }

        $this->client = $client;

        if ($plan) {
            if ($plan->user_id !== $client->id) {
                abort(404);
            }

            $this->plan = $plan;
            $this->title = $plan->title;
            $this->body = $plan->body ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        $plan = $this->plan ?? new ClientPlan(['user_id' => $this->client->id]);
        $plan->title = $this->title;
        $plan->body = $this->body ? Purifier::clean($this->body) : null;
        $plan->save();

        $this->redirectRoute('coach.clients.show', $this->client, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.coach.plan-editor');
    }
}
