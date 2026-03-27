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
            Goal::Cut => 'Cut (lose weight)',
            Goal::Maintain => 'Maintain',
            Goal::Bulk => 'Bulk (gain weight)',
        };
    }
}
