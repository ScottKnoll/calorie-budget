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
    'main_goal_other',
    'why_now',
    'current_weight_lbs',
    'current_height_feet',
    'current_height_inches',
    'activity_level',
    'workout_experience',
    'work_schedule',
    'work_schedule_other',
    'open_to_tracking_steps',
    'daily_steps',
    'sleep_hours',
    'stress_level',
    'fitness_access',
    'current_activity',
    'workout_preferences',
    'workout_preferences_other',
    'has_injuries',
    'injury_description',
    'tracks_currently',
    'meal_timing_pattern',
    'meal_timing_pattern_other',
    'typical_day_of_eating',
    'dietary_restrictions',
    'dietary_preference',
    'dietary_preference_other',
    'workout_days_per_week',
    'open_to_tracking',
    'past_consistency_struggles',
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
            'fitness_access' => 'array',
            'workout_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
