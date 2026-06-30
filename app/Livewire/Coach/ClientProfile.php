<?php

namespace App\Livewire\Coach;

use App\Concerns\IntakeLabelOptions;
use App\Enums\MacroPreset;
use App\Models\CheckIn;
use App\Models\User;
use App\Models\WeightEntry;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Client Profile')]
class ClientProfile extends Component
{
    use IntakeLabelOptions;

    public User $client;

    public ?int $editingCheckInId = null;

    public bool $editingNextCheckIn = false;

    public string $nextCheckInInput = '';

    public string $coachWorkout = '';

    public string $coachNutrition = '';

    public string $coachHabits = '';

    public string $coachGeneral = '';

    public string $coachFocusNextWeek = '';

    public bool $editingCalorieProfile = false;

    public int $editCalorieTarget = 0;

    public ?string $editMacroPreset = null;

    public int $editCarbPct = 50;

    public int $editProteinPct = 30;

    public int $editFatPct = 20;

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

    public function startEditingNextCheckIn(): void
    {
        $dt = $this->client->next_check_in_at;
        $this->nextCheckInInput = $dt ? $dt->format('Y-m-d\TH:i') : '';
        $this->editingNextCheckIn = true;
    }

    public function cancelEditingNextCheckIn(): void
    {
        $this->editingNextCheckIn = false;
        $this->nextCheckInInput = '';
    }

    public function saveNextCheckInDate(): void
    {
        $validated = $this->validate([
            'nextCheckInInput' => ['required', 'date', 'after_or_equal:now'],
        ], [
            'nextCheckInInput.required' => 'Please select a date and time.',
            'nextCheckInInput.after_or_equal' => 'The date and time must be in the future.',
        ]);

        $this->client->update(['next_check_in_at' => $validated['nextCheckInInput']]);
        $this->client->refresh();

        $this->cancelEditingNextCheckIn();
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

    public function startEditingCalorieProfile(): void
    {
        $profile = $this->client->calorieProfile;

        if (! $profile) {
            return;
        }

        $this->editCalorieTarget = $profile->daily_calorie_target;
        $this->editMacroPreset = $profile->macro_preset?->value;
        $this->editCarbPct = $profile->carb_pct;
        $this->editProteinPct = $profile->protein_pct;
        $this->editFatPct = $profile->fat_pct;
        $this->editingCalorieProfile = true;
    }

    public function cancelEditingCalorieProfile(): void
    {
        $this->editingCalorieProfile = false;
        $this->reset(['editCalorieTarget', 'editMacroPreset', 'editCarbPct', 'editProteinPct', 'editFatPct']);
    }

    #[Computed]
    public function editMacroTotal(): int
    {
        return $this->editCarbPct + $this->editProteinPct + $this->editFatPct;
    }

    #[Computed]
    public function editCarbGrams(): int
    {
        if ($this->editCalorieTarget === 0) {
            return 0;
        }

        return (int) round($this->editCarbPct / 100 * $this->editCalorieTarget / 4);
    }

    #[Computed]
    public function editProteinGrams(): int
    {
        if ($this->editCalorieTarget === 0) {
            return 0;
        }

        return (int) round($this->editProteinPct / 100 * $this->editCalorieTarget / 4);
    }

    #[Computed]
    public function editFatGrams(): int
    {
        if ($this->editCalorieTarget === 0) {
            return 0;
        }

        return (int) round($this->editFatPct / 100 * $this->editCalorieTarget / 9);
    }

    public function updatedEditMacroPreset(?string $value): void
    {
        if (! $value) {
            return;
        }

        $preset = MacroPreset::tryFrom($value);

        if ($preset && $preset->percentages() !== null) {
            [$this->editCarbPct, $this->editProteinPct, $this->editFatPct] = $preset->percentages();
        }
    }

    public function saveCalorieProfile(): void
    {
        $validated = $this->validate([
            'editCalorieTarget' => ['required', 'integer', 'min:500', 'max:9999'],
            'editMacroPreset' => ['nullable', new Enum(MacroPreset::class)],
            'editCarbPct' => ['required', 'integer', 'min:0', 'max:100'],
            'editProteinPct' => ['required', 'integer', 'min:0', 'max:100'],
            'editFatPct' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($this->editMacroTotal !== 100) {
            $this->addError('editCarbPct', 'Carb, protein, and fat percentages must add up to 100%.');

            return;
        }

        $this->client->calorieProfile->update([
            'daily_calorie_target' => $validated['editCalorieTarget'],
            'macro_preset' => $validated['editMacroPreset'],
            'carb_pct' => $validated['editCarbPct'],
            'protein_pct' => $validated['editProteinPct'],
            'fat_pct' => $validated['editFatPct'],
        ]);

        $this->cancelEditingCalorieProfile();
    }

    #[Computed]
    public function latestWeightEntry(): ?WeightEntry
    {
        return $this->client->weightEntries()->latest('date')->first();
    }

    public function macroPresetOptions(): array
    {
        return collect(MacroPreset::cases())
            ->mapWithKeys(fn (MacroPreset $p) => [$p->value => $p->label()])
            ->all();
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
