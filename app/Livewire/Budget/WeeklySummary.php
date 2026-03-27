<?php

namespace App\Livewire\Budget;

use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Weekly Summary')]
class WeeklySummary extends Component
{
    /** The Monday that anchors the currently viewed week. */
    public string $weekStart = '';

    public function mount(): void
    {
        $this->weekStart = Carbon::now()->startOfWeek()->toDateString();
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->toDateString();
    }

    #[Computed]
    public function profile(): ?CalorieProfile
    {
        return Auth::user()->calorieProfile;
    }

    #[Computed]
    public function weekEnd(): Carbon
    {
        return Carbon::parse($this->weekStart)->endOfWeek();
    }

    /**
     * Returns a collection of 7 rows, one per day of the week.
     *
     * Each row has: date (Carbon), calories_consumed (int|null), over_under (int|null).
     * Null means no entry was logged for that day.
     *
     * @return Collection<int, array{date: Carbon, calories_consumed: int|null, over_under: int|null}>
     */
    #[Computed]
    public function days(): Collection
    {
        $target = $this->profile?->daily_calorie_target;

        $entries = Auth::user()->calorieEntries()
            ->whereBetween('date', [$this->weekStart, $this->weekEnd->toDateString()])
            ->get()
            ->keyBy(fn (CalorieEntry $entry) => $entry->date->toDateString());

        return collect(range(0, 6))->map(function (int $offset) use ($entries, $target) {
            $date = Carbon::parse($this->weekStart)->addDays($offset);
            $entry = $entries->get($date->toDateString());
            $consumed = $entry?->calories_consumed;

            return [
                'date' => $date,
                'calories_consumed' => $consumed,
                'over_under' => ($consumed !== null && $target !== null)
                    ? $consumed - $target
                    : null,
            ];
        });
    }

    /**
     * Running total of all logged over/under values for the week.
     * Positive = net over budget. Negative = net under budget.
     */
    #[Computed]
    public function weeklyBalance(): int
    {
        return $this->days
            ->sum(fn (array $day) => $day['over_under'] ?? 0);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.budget.weekly-summary');
    }
}
