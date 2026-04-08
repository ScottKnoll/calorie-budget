<?php

namespace App\Models;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\Gender;
use App\Enums\Goal;
use Database\Factories\CalorieProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'gender',
    'age',
    'height_feet',
    'height_inches',
    'weight_lbs',
    'goal_weight_lbs',
    'start_date',
    'calorie_deficit_pct',
    'activity_factor',
    'exercise_factor',
    'tdee',
    'goal',
    'daily_calorie_target',
])]
class CalorieProfile extends Model
{
    /** @use HasFactory<CalorieProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'activity_factor' => ActivityFactor::class,
            'exercise_factor' => ExerciseFactor::class,
            'goal' => Goal::class,
            'start_date' => 'date',
            'age' => 'integer',
            'height_feet' => 'integer',
            'height_inches' => 'integer',
            'weight_lbs' => 'integer',
            'goal_weight_lbs' => 'integer',
            'calorie_deficit_pct' => 'integer',
            'tdee' => 'integer',
            'daily_calorie_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
