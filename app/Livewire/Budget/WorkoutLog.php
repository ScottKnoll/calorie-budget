<?php

namespace App\Livewire\Budget;

use App\Enums\WorkoutType;
use App\Models\WorkoutEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Workout Log')]
class WorkoutLog extends Component
{
    public string $date = '';

    public string $workoutType = '';

    public string $customType = '';

    public ?int $durationMinutes = null;

    public ?int $caloriesBurned = null;

    public string $notes = '';

    public ?int $editingId = null;

    public string $editingWorkoutType = '';

    public string $editingCustomType = '';

    public ?int $editingDurationMinutes = null;

    public ?int $editingCaloriesBurned = null;

    public string $editingNotes = '';

    public function mount(): void
    {
        $this->date = Carbon::today()->toDateString();
        $this->workoutType = WorkoutType::Lift->value;
    }

    public function addEntry(): void
    {
        $this->validate([
            'date' => ['required', 'date'],
            'workoutType' => ['required', 'string'],
            'customType' => [$this->workoutType === WorkoutType::Custom->value ? 'required' : 'nullable', 'string', 'max:100'],
            'durationMinutes' => ['required', 'integer', 'min:1', 'max:600'],
            'caloriesBurned' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        auth()->user()->workoutEntries()->create([
            'date' => $this->date,
            'workout_type' => $this->workoutType,
            'custom_type' => $this->workoutType === WorkoutType::Custom->value ? $this->customType : null,
            'duration_minutes' => $this->durationMinutes,
            'calories_burned' => $this->caloriesBurned ?: null,
            'notes' => $this->notes ?: null,
        ]);

        $this->reset(['durationMinutes', 'caloriesBurned', 'notes', 'customType']);
        $this->workoutType = WorkoutType::Lift->value;
        unset($this->entries);
    }

    public function startEditing(int $id): void
    {
        $entry = $this->findOwnedEntry($id);

        if (! $entry) {
            return;
        }

        $this->editingId = $id;
        $this->editingWorkoutType = $entry->workout_type->value;
        $this->editingCustomType = $entry->custom_type ?? '';
        $this->editingDurationMinutes = $entry->duration_minutes;
        $this->editingCaloriesBurned = $entry->calories_burned;
        $this->editingNotes = $entry->notes ?? '';
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->editingWorkoutType = '';
        $this->editingCustomType = '';
        $this->editingDurationMinutes = null;
        $this->editingCaloriesBurned = null;
        $this->editingNotes = '';
    }

    public function updateEntry(): void
    {
        $this->validate([
            'editingWorkoutType' => ['required', 'string'],
            'editingCustomType' => [$this->editingWorkoutType === WorkoutType::Custom->value ? 'required' : 'nullable', 'string', 'max:100'],
            'editingDurationMinutes' => ['required', 'integer', 'min:1', 'max:600'],
            'editingCaloriesBurned' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'editingNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $entry = $this->findOwnedEntry($this->editingId);

        if (! $entry) {
            return;
        }

        $entry->update([
            'workout_type' => $this->editingWorkoutType,
            'custom_type' => $this->editingWorkoutType === WorkoutType::Custom->value ? $this->editingCustomType : null,
            'duration_minutes' => $this->editingDurationMinutes,
            'calories_burned' => $this->editingCaloriesBurned ?: null,
            'notes' => $this->editingNotes ?: null,
        ]);

        $this->cancelEditing();
        unset($this->entries);
    }

    public function deleteEntry(int $id): void
    {
        $entry = $this->findOwnedEntry($id);
        $entry?->delete();
        unset($this->entries);
    }

    private function findOwnedEntry(?int $id): ?WorkoutEntry
    {
        if ($id === null) {
            return null;
        }

        return auth()->user()->workoutEntries()->find($id);
    }

    /**
     * All workout entries for the user, newest first.
     *
     * @return Collection<int, WorkoutEntry>
     */
    #[Computed]
    public function entries(): Collection
    {
        return auth()->user()->workoutEntries()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    #[Computed]
    public function weeklyCount(): int
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        return auth()->user()->workoutEntries()
            ->whereDate('date', '>=', $weekStart)
            ->whereDate('date', '<=', Carbon::today()->toDateString())
            ->count();
    }

    #[Computed]
    public function weeklyCaloriesBurned(): int
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        return (int) auth()->user()->workoutEntries()
            ->whereDate('date', '>=', $weekStart)
            ->whereDate('date', '<=', Carbon::today()->toDateString())
            ->sum('calories_burned');
    }

    /** @return array<string, string> */
    public function workoutTypeOptions(): array
    {
        $options = [];

        foreach (WorkoutType::predefined() as $type) {
            $options[$type->value] = $type->label();
        }

        $options[WorkoutType::Custom->value] = WorkoutType::Custom->label();

        return $options;
    }

    public function render(): View
    {
        return view('livewire.budget.workout-log');
    }
}
