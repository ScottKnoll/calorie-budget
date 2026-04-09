<?php

namespace App\Enums;

enum FormulaType: string
{
    case Standard = 'standard';
    case LeanMass = 'lean_mass';

    public function label(): string
    {
        return match ($this) {
            FormulaType::Standard => 'Standard (Mifflin-St Jeor)',
            FormulaType::LeanMass => 'Lean Mass (Katch-McArdle)',
        };
    }
}
