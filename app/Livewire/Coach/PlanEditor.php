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

    /** @var array<int, array{id: int|null, title: string, body: string, position: int}> */
    public array $sections = [];

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
            $this->sections = $plan->sections->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'body' => $s->body ?? '',
                'position' => $s->position,
            ])->values()->toArray();
        }

        if (empty($this->sections)) {
            $this->addSection();
        }
    }

    public function addSection(): void
    {
        $this->sections[] = [
            'id' => null,
            'title' => '',
            'body' => '',
            'position' => count($this->sections),
        ];
    }

    public function removeSection(int $index): void
    {
        array_splice($this->sections, $index, 1);

        foreach ($this->sections as $i => &$section) {
            $section['position'] = $i;
        }
    }

    public function updateSectionBody(int $index, string $body): void
    {
        $this->sections[$index]['body'] = $body;
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.body' => ['nullable', 'string'],
        ]);

        $plan = $this->plan ?? new ClientPlan(['user_id' => $this->client->id]);
        $plan->title = $this->title;
        $plan->save();

        $keptIds = [];

        foreach ($this->sections as $index => $sectionData) {
            $section = isset($sectionData['id'])
                ? $plan->sections()->find($sectionData['id'])
                : null;

            $section ??= $plan->sections()->make();
            $section->title = $sectionData['title'];
            $section->body = $sectionData['body']
                ? Purifier::clean($sectionData['body'])
                : null;
            $section->position = $index;
            $section->save();

            $keptIds[] = $section->id;
        }

        $plan->sections()->whereNotIn('id', $keptIds)->delete();

        $this->redirectRoute('coach.clients.show', $this->client, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.coach.plan-editor');
    }
}
