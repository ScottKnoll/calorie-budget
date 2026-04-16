<?php

namespace App\Models;

use Database\Factories\IntakeResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'main_goal',
    'why_now',
    'current_weight_lbs',
    'current_height_feet',
    'current_height_inches',
    'activity_level',
    'workout_experience',
    'work_schedule',
    'daily_steps',
    'sleep_hours',
    'stress_level',
    'tracks_currently',
    'typical_day_of_eating',
    'dietary_restrictions',
    'workout_days_per_week',
    'open_to_tracking',
])]
class IntakeResponse extends Model
{
    /** @use HasFactory<IntakeResponseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'current_weight_lbs' => 'integer',
            'current_height_feet' => 'integer',
            'current_height_inches' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
