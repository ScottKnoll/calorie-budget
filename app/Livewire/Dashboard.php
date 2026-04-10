<?php

namespace App\Livewire;

use App\Enums\Goal;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function profile(): ?CalorieProfile
    {
        return auth()->user()->calorieProfile;
    }

    #[Computed]
    public function todaysEntry(): ?CalorieEntry
    {
        return auth()->user()->calorieEntries()
            ->whereDate('date', Carbon::today())
            ->first();
    }

    #[Computed]
    public function remainingToday(): ?int
    {
        if (! $this->profile) {
            return null;
        }

        $consumed = $this->todaysEntry?->calories_consumed ?? null;

        if ($consumed === null) {
            return null;
        }

        return $this->profile->daily_calorie_target - $consumed;
    }

    /**
     * Running over/under balance for logged days so far this week.
     * Days with no entry are excluded (not penalised on the summary).
     */
    #[Computed]
    public function weeklyBalance(): ?int
    {
        if (! $this->profile) {
            return null;
        }

        $target = $this->profile->daily_calorie_target;
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        return auth()->user()->calorieEntries()
            ->whereBetween('date', [$weekStart, Carbon::today()->toDateString()])
            ->get()
            ->sum(fn (CalorieEntry $entry) => $entry->calories_consumed - $target);
    }

    #[Computed]
    public function daysLoggedThisWeek(): int
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        return auth()->user()->calorieEntries()
            ->whereBetween('date', [$weekStart, Carbon::today()->toDateString()])
            ->count();
    }

    #[Computed]
    public function computedCarbGrams(): int
    {
        if (! $this->profile || $this->profile->daily_calorie_target === 0) {
            return 0;
        }

        return (int) round($this->profile->carb_pct / 100 * $this->profile->daily_calorie_target / 4);
    }

    #[Computed]
    public function computedProteinGrams(): int
    {
        if (! $this->profile || $this->profile->daily_calorie_target === 0) {
            return 0;
        }

        return (int) round($this->profile->protein_pct / 100 * $this->profile->daily_calorie_target / 4);
    }

    #[Computed]
    public function computedFatGrams(): int
    {
        if (! $this->profile || $this->profile->daily_calorie_target === 0) {
            return 0;
        }

        return (int) round($this->profile->fat_pct / 100 * $this->profile->daily_calorie_target / 9);
    }

    #[Computed]
    public function goalLabel(): ?string
    {
        return $this->profile?->goal instanceof Goal
            ? $this->profile->goal->label()
            : null;
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
