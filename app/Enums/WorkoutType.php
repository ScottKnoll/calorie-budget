<?php

namespace App\Enums;

enum WorkoutType: string
{
    case Lift = 'lift';
    case Cardio = 'cardio';
    case Hiit = 'hiit';
    case Walk = 'walk';
    case Run = 'run';
    case Yoga = 'yoga';
    case Kettlebell = 'kettlebell';
    case FoamRolling = 'foam_rolling';
    case Mobility = 'mobility';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            WorkoutType::Lift => 'Lift',
            WorkoutType::Cardio => 'Cardio',
            WorkoutType::Hiit => 'HIIT',
            WorkoutType::Walk => 'Walk',
            WorkoutType::Run => 'Run',
            WorkoutType::Yoga => 'Yoga',
            WorkoutType::Kettlebell => 'Kettlebell',
            WorkoutType::FoamRolling => 'Foam Rolling',
            WorkoutType::Mobility => 'Mobility',
            WorkoutType::Custom => 'Custom',
        };
    }

    /** Returns all cases except Custom, for use in the predefined dropdown. */
    public static function predefined(): array
    {
        return array_filter(self::cases(), fn (WorkoutType $type) => $type !== WorkoutType::Custom);
    }
}
