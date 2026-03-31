<?php

namespace App\Livewire\Budget;

use App\Enums\Goal;
use App\Models\CalorieProfile;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Calorie Setup')]
class Setup extends Component
{
    public int $tdee = 2000;

    public string $goal = 'maintain';

    public int $daily_calorie_target = 2000;

    public function mount(): void
    {
        $profile = auth()->user()->calorieProfile;

        if ($profile) {
            $this->tdee = $profile->tdee;
            $this->goal = $profile->goal->value;
            $this->daily_calorie_target = $profile->daily_calorie_target;
        }
    }

    public function updatedTdee(): void
    {
        $this->suggestDailyTarget();
    }

    public function updatedGoal(): void
    {
        $this->suggestDailyTarget();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'tdee' => ['required', 'integer', 'min:500', 'max:9999'],
            'goal' => ['required', new Enum(Goal::class)],
            'daily_calorie_target' => ['required', 'integer', 'min:500', 'max:9999'],
        ]);

        CalorieProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated,
        );

        session()->flash('status', 'saved');
    }

    public function goalOptions(): array
    {
        return collect(Goal::cases())
            ->mapWithKeys(fn (Goal $goal) => [$goal->value => $goal->label()])
            ->all();
    }

    private function suggestDailyTarget(): void
    {
        if ($this->tdee < 500) {
            return;
        }

        $this->daily_calorie_target = match (Goal::tryFrom($this->goal)) {
            Goal::Cut => (int) round($this->tdee * 0.80),
            Goal::Bulk => (int) round($this->tdee * 1.20),
            default => $this->tdee,
        };
    }

    public function render(): View
    {
        return view('livewire.budget.setup');
    }
}
