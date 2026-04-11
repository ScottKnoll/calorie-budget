<?php

namespace App\Models;

use App\Enums\ActivityFactor;
use App\Enums\ExerciseFactor;
use App\Enums\FormulaType;
use App\Enums\Gender;
use App\Enums\Goal;
use App\Enums\MacroPreset;
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
    'formula',
    'body_fat_pct',
    'tdee',
    'goal',
    'daily_calorie_target',
    'macro_preset',
    'carb_pct',
    'protein_pct',
    'fat_pct',
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
            'formula' => FormulaType::class,
            'goal' => Goal::class,
            'macro_preset' => MacroPreset::class,
            'start_date' => 'date',
            'age' => 'integer',
            'height_feet' => 'integer',
            'height_inches' => 'integer',
            'weight_lbs' => 'integer',
            'goal_weight_lbs' => 'integer',
            'calorie_deficit_pct' => 'integer',
            'body_fat_pct' => 'float',
            'tdee' => 'integer',
            'daily_calorie_target' => 'integer',
            'carb_pct' => 'integer',
            'protein_pct' => 'integer',
            'fat_pct' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
