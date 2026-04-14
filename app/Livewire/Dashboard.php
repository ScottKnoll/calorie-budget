<?php

namespace App\Livewire;

use App\Enums\Goal;
use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\WeightEntry;
use App\Models\WorkoutEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
            ->whereDate('date', '>=', $weekStart)
            ->whereDate('date', '<=', Carbon::today()->toDateString())
            ->get()
            ->sum(fn (CalorieEntry $entry) => $entry->calories_consumed - $target);
    }

    #[Computed]
    public function daysLoggedThisWeek(): int
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        return auth()->user()->calorieEntries()
            ->whereDate('date', '>=', $weekStart)
            ->whereDate('date', '<=', Carbon::today()->toDateString())
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

    #[Computed]
    public function latestWeightEntry(): ?WeightEntry
    {
        return auth()->user()->weightEntries()
            ->orderBy('date', 'desc')
            ->first();
    }

    #[Computed]
    public function weightToGoal(): ?float
    {
        if (! $this->latestWeightEntry || ! $this->profile?->goal_weight_lbs) {
            return null;
        }

        return round($this->latestWeightEntry->weight_lbs - $this->profile->goal_weight_lbs, 1);
    }

    /**
     * Today's workout entries.
     *
     * @return Collection<int, WorkoutEntry>
     */
    #[Computed]
    public function todaysWorkouts(): Collection
    {
        return auth()->user()->workoutEntries()
            ->whereDate('date', Carbon::today())
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function weeklyWorkoutCount(): int
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

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
