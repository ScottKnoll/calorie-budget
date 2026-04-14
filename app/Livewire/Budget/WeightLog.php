<?php

namespace App\Livewire\Budget;

use App\Models\CalorieProfile;
use App\Models\WeightEntry;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Weight Log')]
class WeightLog extends Component
{
    public ?int $editingId = null;

    public ?float $editingWeight = null;

    public string $editingNotes = '';

    public function startEditing(int $id): void
    {
        $entry = $this->findOwnedEntry($id);

        if (! $entry) {
            return;
        }

        $this->editingId = $id;
        $this->editingWeight = $entry->weight_lbs;
        $this->editingNotes = $entry->notes ?? '';
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->editingWeight = null;
        $this->editingNotes = '';
    }

    public function updateEntry(): void
    {
        $this->validate([
            'editingWeight' => ['required', 'numeric', 'min:50', 'max:999'],
            'editingNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $entry = $this->findOwnedEntry($this->editingId);

        if (! $entry) {
            return;
        }

        $entry->update([
            'weight_lbs' => $this->editingWeight,
            'notes' => $this->editingNotes ?: null,
        ]);

        $this->cancelEditing();
    }

    public function deleteEntry(int $id): void
    {
        $entry = $this->findOwnedEntry($id);
        $entry?->delete();
    }

    private function findOwnedEntry(?int $id): ?WeightEntry
    {
        if ($id === null) {
            return null;
        }

        return auth()->user()->weightEntries()->find($id);
    }

    #[Computed]
    public function profile(): ?CalorieProfile
    {
        return auth()->user()->calorieProfile;
    }

    /**
     * All weight entries for the user, newest first.
     *
     * @return Collection<int, WeightEntry>
     */
    #[Computed]
    public function entries(): Collection
    {
        return auth()->user()->weightEntries()
            ->orderBy('date', 'desc')
            ->get();
    }

    /** Most recent weight entry. */
    #[Computed]
    public function latestEntry(): ?WeightEntry
    {
        return $this->entries->first();
    }

    /** Starting weight entry (oldest). */
    #[Computed]
    public function startingEntry(): ?WeightEntry
    {
        return $this->entries->last();
    }

    /** Net change from starting to latest weight. */
    #[Computed]
    public function netChange(): ?float
    {
        if (! $this->latestEntry || ! $this->startingEntry || $this->latestEntry->id === $this->startingEntry->id) {
            return null;
        }

        return round($this->latestEntry->weight_lbs - $this->startingEntry->weight_lbs, 1);
    }

    /** Pounds remaining to goal weight. */
    #[Computed]
    public function remainingToGoal(): ?float
    {
        if (! $this->latestEntry || ! $this->profile?->goal_weight_lbs) {
            return null;
        }

        return round($this->latestEntry->weight_lbs - $this->profile->goal_weight_lbs, 1);
    }

    /**
     * Data points for the SVG chart, ordered oldest-to-newest.
     *
     * @return Collection<int, WeightEntry>
     */
    #[Computed]
    public function chartEntries(): Collection
    {
        return $this->entries->reverse()->values();
    }

    /**
     * Build an SVG polyline path from weight entries.
     * Returns ['points' => string, 'minY' => float, 'maxY' => float] or null when fewer than 2 entries.
     *
     * @return array{points: string, minY: float, maxY: float, goalY: float|null}|null
     */
    #[Computed]
    public function chartData(): ?array
    {
        $entries = $this->chartEntries;

        if ($entries->count() < 2) {
            return null;
        }

        $weights = $entries->pluck('weight_lbs')->map(fn ($w) => (float) $w);

        $minY = $weights->min();
        $maxY = $weights->max();

        // Include goal weight in the Y range if set, so the goal line is always visible.
        $goalWeight = $this->profile?->goal_weight_lbs ? (float) $this->profile->goal_weight_lbs : null;

        if ($goalWeight !== null) {
            $minY = min($minY, $goalWeight);
            $maxY = max($maxY, $goalWeight);
        }

        // Add a small padding so points don't sit on the exact edge.
        $padding = max(1, ($maxY - $minY) * 0.1);
        $minY -= $padding;
        $maxY += $padding;

        $viewWidth = 600;
        $viewHeight = 200;
        $count = $entries->count();

        $points = $entries->map(function (WeightEntry $entry, int $index) use ($count, $minY, $maxY, $viewWidth, $viewHeight) {
            $x = $count > 1 ? ($index / ($count - 1)) * $viewWidth : $viewWidth / 2;
            $range = $maxY - $minY;
            $y = $range > 0
                ? $viewHeight - (((float) $entry->weight_lbs - $minY) / $range * $viewHeight)
                : $viewHeight / 2;

            return round($x, 1).','.round($y, 1);
        })->implode(' ');

        $goalY = null;

        if ($goalWeight !== null) {
            $range = $maxY - $minY;
            $goalY = $range > 0
                ? round($viewHeight - (($goalWeight - $minY) / $range * $viewHeight), 1)
                : $viewHeight / 2;
        }

        return [
            'points' => $points,
            'minY' => $minY,
            'maxY' => $maxY,
            'goalY' => $goalY,
        ];
    }

    public function render(): View
    {
        return view('livewire.budget.weight-log');
    }
}
