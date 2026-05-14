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

#[Title('Calorie Chart')]
class CalorieChart extends Component
{
    public string $chartPeriod = 'week';

    #[Computed]
    public function profile(): ?CalorieProfile
    {
        return auth()->user()->calorieProfile;
    }

    /**
     * Calorie entries for the selected period, oldest first.
     *
     * @return Collection<int, CalorieEntry>
     */
    #[Computed]
    public function entries(): Collection
    {
        $start = match ($this->chartPeriod) {
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfWeek(),
        };

        return auth()->user()->calorieEntries()
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', Carbon::today()->toDateString())
            ->orderBy('date', 'asc')
            ->get();
    }

    /** Average daily calories consumed in the selected period. */
    #[Computed]
    public function averageCalories(): ?int
    {
        if ($this->entries->isEmpty()) {
            return null;
        }

        return (int) round($this->entries->avg('calories_consumed'));
    }

    /** Number of days logged in the selected period. */
    #[Computed]
    public function daysLogged(): int
    {
        return $this->entries->count();
    }

    /** Number of days in the period where calories exceeded the daily target. */
    #[Computed]
    public function daysOverTarget(): ?int
    {
        $target = $this->profile?->daily_calorie_target;

        if ($target === null || $this->entries->isEmpty()) {
            return null;
        }

        return $this->entries->filter(fn (CalorieEntry $entry) => $entry->calories_consumed > $target)->count();
    }

    /**
     * Build SVG polyline data from calorie entries.
     * For the year period, data is aggregated into weekly averages.
     * Returns null when fewer than 2 data points exist.
     *
     * @return array{points: string, targetY: float|null}|null
     */
    #[Computed]
    public function chartData(): ?array
    {
        $entries = $this->entries;

        // Aggregate to weekly averages for the year view to keep the chart readable.
        $dataPoints = ($this->chartPeriod === 'year')
            ? $entries
                ->groupBy(fn (CalorieEntry $entry) => $entry->date->startOfWeek()->toDateString())
                ->map(fn (Collection $week) => (int) round($week->avg('calories_consumed')))
                ->values()
            : $entries->pluck('calories_consumed')->map(fn ($v) => (int) $v)->values();

        if ($dataPoints->count() < 2) {
            return null;
        }

        $minY = $dataPoints->min();
        $maxY = $dataPoints->max();

        $target = $this->profile?->daily_calorie_target;

        if ($target !== null) {
            $minY = min($minY, $target);
            $maxY = max($maxY, $target);
        }

        // Padding so data points never sit on the exact SVG edge.
        $padding = max(50, (int) round(($maxY - $minY) * 0.1));
        $minY -= $padding;
        $maxY += $padding;

        $viewWidth = 600;
        $viewHeight = 200;
        $count = $dataPoints->count();

        $points = $dataPoints->map(function (int $calories, int $index) use ($count, $minY, $maxY, $viewWidth, $viewHeight): string {
            $x = $count > 1 ? ($index / ($count - 1)) * $viewWidth : $viewWidth / 2;
            $range = $maxY - $minY;
            $y = $range > 0
                ? $viewHeight - (($calories - $minY) / $range * $viewHeight)
                : $viewHeight / 2;

            return round($x, 1).','.round($y, 1);
        })->implode(' ');

        $targetY = null;

        if ($target !== null) {
            $range = $maxY - $minY;
            $targetY = $range > 0
                ? round($viewHeight - (($target - $minY) / $range * $viewHeight), 1)
                : $viewHeight / 2;
        }

        return [
            'points' => $points,
            'targetY' => $targetY,
        ];
    }

    /** Human-readable label for the active period. */
    #[Computed]
    public function periodLabel(): string
    {
        return match ($this->chartPeriod) {
            'month' => 'This Month',
            'year' => 'This Year',
            default => 'This Week',
        };
    }

    public function render(): View
    {
        return view('livewire.budget.calorie-chart');
    }
}
