<?php

namespace App\Livewire\Budget;

use App\Models\CheckIn as CheckInModel;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Check-In')]
class CheckIn extends Component
{
    public ?int $checkInId = null;

    public string $weight = '';

    public string $week_feeling = '';

    public string $went_well = '';

    public string $felt_hardest = '';

    public string $hunger_energy_sleep = '';

    public string $activity_consistency = '';

    public string $need_help = '';

    public function mount(?CheckInModel $checkIn = null): void
    {
        if (! auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        if ($checkIn) {
            if ($checkIn->user_id !== auth()->id()) {
                abort(403);
            }

            $this->checkInId = $checkIn->id;
            $this->weight = (string) $checkIn->weight;
            $this->week_feeling = $checkIn->week_feeling;
            $this->went_well = $checkIn->went_well;
            $this->felt_hardest = $checkIn->felt_hardest;
            $this->hunger_energy_sleep = $checkIn->hunger_energy_sleep;
            $this->activity_consistency = $checkIn->activity_consistency;
            $this->need_help = $checkIn->need_help ?? '';
        }
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->checkInId !== null;
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

        if ($this->checkInId) {
            CheckInModel::where('id', $this->checkInId)
                ->where('user_id', auth()->id())
                ->firstOrFail()
                ->update($validated);
        } else {
            auth()->user()->checkIns()->create($validated);
        }

        $this->redirectRoute('budget.check-ins', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.budget.check-in');
    }
}
