<?php

namespace App\Livewire\Budget;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Weekly Check-In')]
class CheckIn extends Component
{
    public string $weight = '';

    public string $week_feeling = '';

    public string $went_well = '';

    public string $felt_hardest = '';

    public string $hunger_energy_sleep = '';

    public string $activity_consistency = '';

    public string $need_help = '';

    public function mount(): void
    {
        if (! auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'weight' => ['required', 'numeric', 'min:50', 'max:999'],
            'week_feeling' => ['required', 'string', 'max:2000'],
            'went_well' => ['required', 'string', 'max:2000'],
            'felt_hardest' => ['required', 'string', 'max:2000'],
            'hunger_energy_sleep' => ['required', 'string', 'max:2000'],
            'activity_consistency' => ['required', 'string', 'max:2000'],
            'need_help' => ['nullable', 'string', 'max:2000'],
        ]);

        if (($validated['need_help'] ?? '') === '') {
            $validated['need_help'] = null;
        }

        auth()->user()->checkIns()->create($validated);

        $this->redirectRoute('budget.check-ins', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.budget.check-in');
    }
}
