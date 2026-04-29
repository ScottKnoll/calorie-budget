<?php

namespace App\Enums;

enum FormulaType: string
{
    case Standard = 'standard';
    case LeanMass = 'lean_mass';

    public function label(): string
    {
        return match ($this) {
            FormulaType::Standard => "Standard (I don't know my body fat %)",
            FormulaType::LeanMass => 'Lean Mass (I know my body fat %)',
        };
    }
}
