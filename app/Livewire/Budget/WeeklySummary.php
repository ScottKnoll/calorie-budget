<?php

namespace App\Livewire\Budget;

use App\Models\CalorieEntry;
use App\Models\CalorieProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
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
        return auth()->user()->calorieProfile;
    }

    #[Computed]
    public function weekEnd(): Carbon
    {
        return Carbon::parse($this->weekStart)->endOfWeek();
    }

    /**
     * Returns a collection of 7 rows, one per day of the week.
     *
     * Each row has: date (Carbon), calories_consumed (int|null), over_under (int|null),
     * carbs_grams (int|null), protein_grams (int|null), fat_grams (int|null).
     * Null means no entry was logged for that day.
     *
     * @return Collection<int, array{date: Carbon, calories_consumed: int|null, over_under: int|null, carbs_grams: int|null, protein_grams: int|null, fat_grams: int|null}>
     */
    #[Computed]
    public function days(): Collection
    {
        $target = $this->profile?->daily_calorie_target;

        $entries = auth()->user()->calorieEntries()
            ->whereDate('date', '>=', $this->weekStart)
            ->whereDate('date', '<=', $this->weekEnd->toDateString())
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
                'carbs_grams' => $entry?->carbs_grams,
                'protein_grams' => $entry?->protein_grams,
                'fat_grams' => $entry?->fat_grams,
            ];
        });
    }

    /**
     * Number of days this week that have a calorie entry logged.
     */
    #[Computed]
    public function loggedDaysCount(): int
    {
        return $this->days
            ->filter(fn (array $day) => $day['calories_consumed'] !== null)
            ->count();
    }

    /**
     * Total calories consumed across all logged days this week.
     * Days with no entry are excluded from the sum.
     */
    #[Computed]
    public function weeklyConsumed(): int
    {
        return $this->days
            ->sum(fn (array $day) => $day['calories_consumed'] ?? 0);
    }

    /**
     * Average calories consumed per logged day. Null when no days have been logged.
     */
    #[Computed]
    public function averageDailyConsumed(): ?int
    {
        if ($this->loggedDaysCount === 0) {
            return null;
        }

        return (int) round($this->weeklyConsumed / $this->loggedDaysCount);
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

    /**
     * Weekly macro summary: total consumed grams, weekly gram targets, over/under delta, and daily average.
     * Only populated when the profile has macro percentages configured.
     *
     * @return array{carbs: array{consumed: int, target: int, delta: int, daily_average: int|null}, protein: array{consumed: int, target: int, delta: int, daily_average: int|null}, fat: array{consumed: int, target: int, delta: int, daily_average: int|null}}|null
     */
    #[Computed]
    public function weeklyMacroSummary(): ?array
    {
        if (! $this->profile || $this->profile->daily_calorie_target === 0) {
            return null;
        }

        $dailyTarget = $this->profile->daily_calorie_target;
        $dailyCarbTarget = (int) round($this->profile->carb_pct / 100 * $dailyTarget / 4);
        $dailyProteinTarget = (int) round($this->profile->protein_pct / 100 * $dailyTarget / 4);
        $dailyFatTarget = (int) round($this->profile->fat_pct / 100 * $dailyTarget / 9);

        $weeklyCarbTarget = $dailyCarbTarget * 7;
        $weeklyProteinTarget = $dailyProteinTarget * 7;
        $weeklyFatTarget = $dailyFatTarget * 7;

        $carbsConsumed = $this->days->sum(fn (array $day) => $day['carbs_grams'] ?? 0);
        $proteinConsumed = $this->days->sum(fn (array $day) => $day['protein_grams'] ?? 0);
        $fatConsumed = $this->days->sum(fn (array $day) => $day['fat_grams'] ?? 0);

        $hasAnyMacroData = $this->days->some(
            fn (array $day) => $day['carbs_grams'] !== null || $day['protein_grams'] !== null || $day['fat_grams'] !== null
        );

        if (! $hasAnyMacroData) {
            return null;
        }

        $loggedDays = $this->loggedDaysCount;

        return [
            'carbs' => [
                'consumed' => $carbsConsumed,
                'target' => $weeklyCarbTarget,
                'delta' => $carbsConsumed - $weeklyCarbTarget,
                'daily_average' => $loggedDays > 0 ? (int) round($carbsConsumed / $loggedDays) : null,
            ],
            'protein' => [
                'consumed' => $proteinConsumed,
                'target' => $weeklyProteinTarget,
                'delta' => $proteinConsumed - $weeklyProteinTarget,
                'daily_average' => $loggedDays > 0 ? (int) round($proteinConsumed / $loggedDays) : null,
            ],
            'fat' => [
                'consumed' => $fatConsumed,
                'target' => $weeklyFatTarget,
                'delta' => $fatConsumed - $weeklyFatTarget,
                'daily_average' => $loggedDays > 0 ? (int) round($fatConsumed / $loggedDays) : null,
            ],
        ];
    }

    /**
     * Build SVG polyline data for the week's calorie chart.
     * X positions are fixed to day-of-week index (0=Mon … 6=Sun) so gaps on
     * unlogged days are visible in the spacing between plotted points.
     * Returns null when fewer than 2 days have been logged.
     *
     * @return array{points: string, dotPoints: array<int, array{x: float, y: float}>, targetY: float|null}|null
     */
    #[Computed]
    public function chartData(): ?array
    {
        $target = $this->profile?->daily_calorie_target;
        $loggedDays = $this->days->filter(fn (array $day) => $day['calories_consumed'] !== null);

        if ($loggedDays->count() < 2) {
            return null;
        }

        $calories = $loggedDays->pluck('calories_consumed');
        $minY = $calories->min();
        $maxY = $calories->max();

        if ($target !== null) {
            $minY = min($minY, $target);
            $maxY = max($maxY, $target);
        }

        $padding = max(50, (int) round(($maxY - $minY) * 0.1));
        $minY -= $padding;
        $maxY += $padding;

        $viewWidth = 600;
        $viewHeight = 200;
        $weekStart = Carbon::parse($this->weekStart);

        $dotPoints = [];
        $pointStrings = [];

        foreach ($loggedDays as $day) {
            $dayIndex = $weekStart->diffInDays($day['date']);
            $x = round(($dayIndex / 6) * $viewWidth, 1);
            $range = $maxY - $minY;
            $y = $range > 0
                ? round($viewHeight - (($day['calories_consumed'] - $minY) / $range * $viewHeight), 1)
                : $viewHeight / 2;

            $dotPoints[] = ['x' => $x, 'y' => $y];
            $pointStrings[] = "{$x},{$y}";
        }

        $targetY = null;

        if ($target !== null) {
            $range = $maxY - $minY;
            $targetY = $range > 0
                ? round($viewHeight - (($target - $minY) / $range * $viewHeight), 1)
                : $viewHeight / 2;
        }

        return [
            'points' => implode(' ', $pointStrings),
            'dotPoints' => $dotPoints,
            'targetY' => $targetY,
        ];
    }

    public function render(): View
    {
        return view('livewire.budget.weekly-summary');
    }
}
