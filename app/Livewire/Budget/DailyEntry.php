<?php

namespace App\Livewire\Budget;

use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daily Log')]
class DailyEntry extends Component
{
    public string $date = '';

    public ?int $calories_consumed = null;

    public ?int $carbs_grams = null;

    public ?int $protein_grams = null;

    public ?int $fat_grams = null;

    public string $notes = '';

    public ?float $weight_lbs = null;

    public function mount(?string $date = null): void
    {
        try {
            $parsed = $date ? Carbon::parse($date) : Carbon::today();
        } catch (\Exception) {
            $parsed = Carbon::today();
        }

        // Prevent logging future dates.
        $this->date = $parsed->min(Carbon::today())->toDateString();

        $this->loadEntry();
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
        $this->loadEntry();
    }

    public function nextDay(): void
    {
        $next = Carbon::parse($this->date)->addDay();

        if ($next->isAfter(Carbon::today())) {
            return;
        }

        $this->date = $next->toDateString();
        $this->loadEntry();
    }

    #[Computed]
    public function isToday(): bool
    {
        return $this->date === Carbon::today()->toDateString();
    }

    private function loadEntry(): void
    {
        $this->calories_consumed = null;
        $this->carbs_grams = null;
        $this->protein_grams = null;
        $this->fat_grams = null;
        $this->notes = '';
        $this->weight_lbs = null;

        /** @var User $user */
        $user = Auth::user();

        $entry = $user->calorieEntries()
            ->whereDate('date', $this->date)
            ->first();

        if ($entry) {
            $this->calories_consumed = $entry->calories_consumed;
            $this->carbs_grams = $entry->carbs_grams;
            $this->protein_grams = $entry->protein_grams;
            $this->fat_grams = $entry->fat_grams;
            $this->notes = $entry->notes ?? '';
        }

        $weightEntry = $user->weightEntries()
            ->whereDate('date', $this->date)
            ->first();

        if ($weightEntry) {
            $this->weight_lbs = $weightEntry->weight_lbs;
        }
    }

    #[Computed]
    public function profile(): ?CalorieProfile
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->calorieProfile;
    }

    #[Computed]
    public function existingEntry(): ?CalorieEntry
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->calorieEntries()
            ->whereDate('date', $this->date)
            ->first();
    }

    /**
     * Sum of calories for each day from the start of the week through yesterday.
     * Days with no logged entry default to the user's daily calorie target,
     * so the weekly bank always reflects a realistic picture.
     */
    #[Computed]
    public function consumedThroughYesterday(): int
    {
        if (! $this->profile) {
            return 0;
        }

        $today = Carbon::parse($this->date);
        $weekStart = $today->copy()->startOfWeek(); // Monday

        // Nothing to count if today is the first day of the week.
        if ($today->isSameDay($weekStart)) {
            return 0;
        }

        $yesterday = $today->copy()->subDay();
        $daysToCount = $weekStart->diffInDays($yesterday) + 1;

        /** @var User $user */
        $user = Auth::user();

        $loggedEntries = $user->calorieEntries()
            ->whereDate('date', '>=', $weekStart->toDateString())
            ->whereDate('date', '<=', $yesterday->toDateString())
            ->get()
            ->keyBy(fn (CalorieEntry $entry) => $entry->date->toDateString());

        $total = 0;

        for ($i = 0; $i < $daysToCount; $i++) {
            $dayKey = $weekStart->copy()->addDays($i)->toDateString();
            $total += $loggedEntries->get($dayKey)?->calories_consumed
                ?? $this->profile->daily_calorie_target;
        }

        return $total;
    }

    /**
     * Number of days from today through the end of the week (Sunday), inclusive.
     */
    #[Computed]
    public function daysRemainingThisWeek(): int
    {
        $today = Carbon::parse($this->date);

        return $today->diffInDays($today->copy()->endOfWeek()) + 1;
    }

    /**
     * How many calories are available today based on the weekly bank.
     * Spreads the remaining weekly budget evenly across days left this week.
     */
    #[Computed]
    public function todaysAllowance(): ?int
    {
        if (! $this->profile) {
            return null;
        }

        $weeklyBudget = $this->profile->daily_calorie_target * 7;
        $remainingBudget = $weeklyBudget - $this->consumedThroughYesterday;

        return (int) round($remainingBudget / $this->daysRemainingThisWeek);
    }

    /**
     * Calories still available today after what has already been logged.
     * Positive = room left. Negative = over today's allowance.
     */
    #[Computed]
    public function remainingToday(): ?int
    {
        if ($this->todaysAllowance === null) {
            return null;
        }

        return $this->todaysAllowance - ($this->calories_consumed ?? 0);
    }

    /**
     * Over/under vs the static daily target — used in the weekly summary table.
     */
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
            'carbs_grams' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'protein_grams' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'fat_grams' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:500'],
            'weight_lbs' => ['nullable', 'numeric', 'min:50', 'max:999'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $existingCalorieEntry = $user->calorieEntries()
            ->whereDate('date', $this->date)
            ->first();

        if ($existingCalorieEntry) {
            $existingCalorieEntry->update([
                'calories_consumed' => $this->calories_consumed,
                'carbs_grams' => $this->carbs_grams,
                'protein_grams' => $this->protein_grams,
                'fat_grams' => $this->fat_grams,
                'notes' => $this->notes ?: null,
            ]);
        } else {
            $user->calorieEntries()->create([
                'date' => $this->date,
                'calories_consumed' => $this->calories_consumed,
                'carbs_grams' => $this->carbs_grams,
                'protein_grams' => $this->protein_grams,
                'fat_grams' => $this->fat_grams,
                'notes' => $this->notes ?: null,
            ]);
        }

        if ($this->weight_lbs !== null) {
            $existing = $user->weightEntries()
                ->whereDate('date', $this->date)
                ->first();

            if ($existing) {
                $existing->update(['weight_lbs' => $this->weight_lbs]);
            } else {
                $user->weightEntries()->create([
                    'date' => $this->date,
                    'weight_lbs' => $this->weight_lbs,
                ]);
            }
        }

        session()->flash('status', 'saved');
    }

    public function render(): View
    {
        return view('livewire.budget.daily-entry');
    }
}
