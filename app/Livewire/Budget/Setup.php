<?php

namespace App\Livewire\Budget;

use App\Enums\Goal;
use App\Models\CalorieProfile;
use Illuminate\Support\Facades\Auth;
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
        $profile = Auth::user()->calorieProfile;

        if ($profile) {
            $this->tdee = $profile->tdee;
            $this->goal = $profile->goal->value;
            $this->daily_calorie_target = $profile->daily_calorie_target;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'tdee' => ['required', 'integer', 'min:500', 'max:9999'],
            'goal' => ['required', 'in:cut,maintain,bulk'],
            'daily_calorie_target' => ['required', 'integer', 'min:500', 'max:9999'],
        ]);

        CalorieProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated,
        );

        $this->dispatch('profile-saved');

        session()->flash('status', 'saved');
    }

    public function goalOptions(): array
    {
        return collect(Goal::cases())
            ->mapWithKeys(fn (Goal $goal) => [$goal->value => $goal->label()])
            ->all();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.budget.setup');
    }
}
