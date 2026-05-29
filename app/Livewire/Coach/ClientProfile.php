<?php

namespace App\Livewire\Coach;

use App\Concerns\IntakeLabelOptions;
use App\Models\CheckIn;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Client Profile')]
class ClientProfile extends Component
{
    use IntakeLabelOptions;

    public User $client;

    public ?int $editingCheckInId = null;

    public string $coachWorkout = '';

    public string $coachNutrition = '';

    public string $coachHabits = '';

    public string $coachGeneral = '';

    public string $coachFocusNextWeek = '';

    public function mount(User $client): void
    {
        if (! auth()->user()->isCoach()) {
            abort(403);
        }

        if (! $client->isClient()) {
            abort(404);
        }

        $this->client = $client;
    }

    public function startEditingNotes(int $checkInId): void
    {
        $checkIn = $this->resolveCheckIn($checkInId);

        $this->editingCheckInId = $checkIn->id;
        $this->coachWorkout = $checkIn->coach_workout ?? '';
        $this->coachNutrition = $checkIn->coach_nutrition ?? '';
        $this->coachHabits = $checkIn->coach_habits ?? '';
        $this->coachGeneral = $checkIn->coach_general ?? '';
        $this->coachFocusNextWeek = $checkIn->coach_focus_next_week ?? '';
    }

    public function cancelEditingNotes(): void
    {
        $this->editingCheckInId = null;
        $this->coachWorkout = '';
        $this->coachNutrition = '';
        $this->coachHabits = '';
        $this->coachGeneral = '';
        $this->coachFocusNextWeek = '';
    }

    public function saveNotes(): void
    {
        $validated = $this->validate([
            'coachWorkout' => ['nullable', 'string', 'max:3000'],
            'coachNutrition' => ['nullable', 'string', 'max:3000'],
            'coachHabits' => ['nullable', 'string', 'max:3000'],
            'coachGeneral' => ['nullable', 'string', 'max:3000'],
            'coachFocusNextWeek' => ['nullable', 'string', 'max:3000'],
        ]);

        $this->resolveCheckIn($this->editingCheckInId)->update([
            'coach_workout' => $validated['coachWorkout'] ?: null,
            'coach_nutrition' => $validated['coachNutrition'] ?: null,
            'coach_habits' => $validated['coachHabits'] ?: null,
            'coach_general' => $validated['coachGeneral'] ?: null,
            'coach_focus_next_week' => $validated['coachFocusNextWeek'] ?: null,
        ]);

        $this->cancelEditingNotes();
    }

    private function resolveCheckIn(int $checkInId): CheckIn
    {
        return $this->client->checkIns()->findOrFail($checkInId);
    }

    public function render(): View
    {
        return view('livewire.coach.client-profile');
    }
}
