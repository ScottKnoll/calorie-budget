<?php

namespace App\Enums;

enum UserType: string
{
    case Personal = 'personal';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            UserType::Personal => 'Personal',
            UserType::Client => 'Client',
        };
    }
}
