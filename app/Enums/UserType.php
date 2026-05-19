<?php

namespace App\Enums;

enum UserType: string
{
    case Coach = 'coach';
    case Client = 'client';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            UserType::Coach => 'Coach',
            UserType::Client => 'Client',
            UserType::Member => 'Member',
        };
    }
}
