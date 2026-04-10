<?php

namespace App\Enums;

enum MacroPreset: string
{
    case HighCarb = 'high_carb';
    case Balanced = 'balanced';
    case HighProtein = 'high_protein';
    case Keto = 'keto';
    case LowCarb = 'low_carb';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            MacroPreset::HighCarb => 'High Carb – 50C / 30P / 20F',
            MacroPreset::Balanced => 'Balanced – 40C / 30P / 30F',
            MacroPreset::HighProtein => 'High Protein – 30C / 40P / 30F',
            MacroPreset::Keto => 'Keto – 5C / 25P / 70F',
            MacroPreset::LowCarb => 'Low Carb – 20C / 35P / 45F',
            MacroPreset::Custom => 'Custom',
        };
    }

    /**
     * Returns [carb_pct, protein_pct, fat_pct] for the preset.
     * Returns null for Custom (no auto-fill).
     *
     * @return array{int, int, int}|null
     */
    public function percentages(): ?array
    {
        return match ($this) {
            MacroPreset::HighCarb => [50, 30, 20],
            MacroPreset::Balanced => [40, 30, 30],
            MacroPreset::HighProtein => [30, 40, 30],
            MacroPreset::Keto => [5, 25, 70],
            MacroPreset::LowCarb => [20, 35, 45],
            MacroPreset::Custom => null,
        };
    }
}
