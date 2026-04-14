<?php

namespace App\Models;

use App\Enums\WorkoutType;
use Database\Factories\WorkoutEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'date', 'workout_type', 'custom_type', 'duration_minutes', 'calories_burned', 'notes'])]
class WorkoutEntry extends Model
{
    /** @use HasFactory<WorkoutEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'workout_type' => WorkoutType::class,
            'duration_minutes' => 'integer',
            'calories_burned' => 'integer',
        ];
    }

    /** Display label for the workout type, falling back to custom_type when applicable. */
    public function typeLabel(): string
    {
        if ($this->workout_type === WorkoutType::Custom) {
            return $this->custom_type ?? WorkoutType::Custom->label();
        }

        return $this->workout_type->label();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
