<?php

namespace App\Livewire\Budget;

use App\Enums\MacroPreset;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Macro Calculator')]
class Macros extends Component
{
    public ?string $macro_preset = null;

    public int $carb_pct = 50;

    public int $protein_pct = 30;

    public int $fat_pct = 20;

    public int $guestCalorieTarget = 0;

    public function mount(): void
    {
        $profile = auth()->user()?->calorieProfile;

        if ($profile && $profile->macro_preset !== null) {
            $this->macro_preset = $profile->macro_preset->value;
            $this->carb_pct = $profile->carb_pct;
            $this->protein_pct = $profile->protein_pct;
            $this->fat_pct = $profile->fat_pct;
        } elseif ($profile && session()->has('macros_prefill')) {
            $prefill = session()->pull('macros_prefill');

            $this->macro_preset = $prefill['macro_preset'];
            $this->carb_pct = $prefill['carb_pct'];
            $this->protein_pct = $prefill['protein_pct'];
            $this->fat_pct = $prefill['fat_pct'];
            $this->guestCalorieTarget = $prefill['guestCalorieTarget'];

            $this->save();
        }
    }

    #[Computed]
    public function dailyCalorieTarget(): int
    {
        if (! auth()->check()) {
            return $this->guestCalorieTarget;
        }

        return auth()->user()->calorieProfile?->daily_calorie_target ?? 0;
    }

    #[Computed]
    public function computedCarbGrams(): int
    {
        if ($this->dailyCalorieTarget === 0) {
            return 0;
        }

        return (int) round($this->carb_pct / 100 * $this->dailyCalorieTarget / 4);
    }

    #[Computed]
    public function computedProteinGrams(): int
    {
        if ($this->dailyCalorieTarget === 0) {
            return 0;
        }

        return (int) round($this->protein_pct / 100 * $this->dailyCalorieTarget / 4);
    }

    #[Computed]
    public function computedFatGrams(): int
    {
        if ($this->dailyCalorieTarget === 0) {
            return 0;
        }

        return (int) round($this->fat_pct / 100 * $this->dailyCalorieTarget / 9);
    }

    #[Computed]
    public function macroTotal(): int
    {
        return $this->carb_pct + $this->protein_pct + $this->fat_pct;
    }

    public function updatedMacroPreset(): void
    {
        if (! $this->macro_preset) {
            return;
        }

        $preset = MacroPreset::from($this->macro_preset);
        $percentages = $preset->percentages();

        if ($percentages !== null) {
            [$this->carb_pct, $this->protein_pct, $this->fat_pct] = $percentages;
        }
    }

    public function updatedCarbPct(): void
    {
        $this->macro_preset = MacroPreset::Custom->value;
    }

    public function updatedProteinPct(): void
    {
        $this->macro_preset = MacroPreset::Custom->value;
    }

    public function updatedFatPct(): void
    {
        $this->macro_preset = MacroPreset::Custom->value;
    }

    public function presetOptions(): array
    {
        return collect(MacroPreset::cases())
            ->mapWithKeys(fn (MacroPreset $p) => [$p->value => $p->label()])
            ->all();
    }

    public function save(): void
    {
        if (! auth()->check()) {
            session(['macros_prefill' => [
                'macro_preset' => $this->macro_preset,
                'carb_pct' => $this->carb_pct,
                'protein_pct' => $this->protein_pct,
                'fat_pct' => $this->fat_pct,
                'guestCalorieTarget' => $this->guestCalorieTarget,
            ]]);

            $this->redirect(route('register'), navigate: true);

            return;
        }

        $this->validate([
            'macro_preset' => ['nullable', new Enum(MacroPreset::class)],
            'carb_pct' => ['required', 'integer', 'min:0', 'max:100'],
            'protein_pct' => ['required', 'integer', 'min:0', 'max:100'],
            'fat_pct' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if ($this->macroTotal !== 100) {
            $this->addError('macro_total', 'Carb, protein, and fat percentages must add up to 100%.');

            return;
        }

        $profile = auth()->user()->calorieProfile;

        if (! $profile) {
            $this->addError('macro_total', 'Please complete your Calorie Setup before saving macro targets.');

            return;
        }

        $profile->update([
            'macro_preset' => $this->macro_preset,
            'carb_pct' => $this->carb_pct,
            'protein_pct' => $this->protein_pct,
            'fat_pct' => $this->fat_pct,
        ]);

        session()->flash('status', 'saved');
    }

    public function render(): View
    {
        return view('livewire.budget.macros');
    }
}
