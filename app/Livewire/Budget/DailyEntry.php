<?php

namespace App\Livewire\Budget;

use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daily Log')]
class DailyEntry extends Component
{
    public string $date = '';

    public ?int $calories_consumed = null;

    public string $notes = '';

    public function mount(): void
    {
        $this->date = Carbon::today()->toDateString();

        $entry = $this->existingEntry();

        if ($entry) {
            $this->calories_consumed = $entry->calories_consumed;
            $this->notes = $entry->notes ?? '';
        }
    }

    #[Computed]
    public function profile(): ?CalorieProfile
    {
        return Auth::user()->calorieProfile;
    }

    #[Computed]
    public function existingEntry(): ?CalorieEntry
    {
        return Auth::user()->calorieEntries()
            ->whereDate('date', $this->date)
            ->first();
    }

    #[Computed]
    public function remaining(): ?int
    {
        if (! $this->profile || $this->calories_consumed === null) {
            return null;
        }

        return $this->profile->daily_calorie_target - $this->calories_consumed;
    }

    #[Computed]
    public function overUnder(): ?int
    {
        if (! $this->profile || $this->calories_consumed === null) {
            return null;
        }

        return $this->calories_consumed - $this->profile->daily_calorie_target;
    }

    public function save(): void
    {
        $this->validate([
            'calories_consumed' => ['required', 'integer', 'min:0', 'max:99999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Auth::user()->calorieEntries()->updateOrCreate(
            ['date' => $this->date],
            [
                'calories_consumed' => $this->calories_consumed,
                'notes' => $this->notes ?: null,
            ],
        );

        session()->flash('status', 'saved');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.budget.daily-entry');
    }
}
