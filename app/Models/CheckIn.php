<?php

namespace App\Models;

use Database\Factories\CheckInFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'weight', 'week_feeling', 'went_well', 'felt_hardest', 'hunger_energy_sleep', 'activity_consistency', 'need_help', 'coach_workout', 'coach_nutrition', 'coach_habits', 'coach_general', 'coach_focus_next_week', 'coach_other'])]
class CheckIn extends Model
{
    /** @use HasFactory<CheckInFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'float',
        ];
    }

    public function hasCoachNotes(): bool
    {
        return ! empty($this->coach_workout)
            || ! empty($this->coach_nutrition)
            || ! empty($this->coach_habits)
            || ! empty($this->coach_general)
            || ! empty($this->coach_focus_next_week)
            || ! empty($this->coach_other);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
