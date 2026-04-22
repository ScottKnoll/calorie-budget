<?php

namespace App\Enums;

enum Goal: string
{
    case Cut = 'cut';
    case Maintain = 'maintain';
    case Bulk = 'bulk';

    public function label(): string
    {
        return match ($this) {
            Goal::Cut => 'Weight Loss',
            Goal::Maintain => 'Maintain',
            Goal::Bulk => 'Weight Gain',
        };
    }
}
