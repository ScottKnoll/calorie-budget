<?php

namespace App\Models;

use App\Enums\Goal;
use Database\Factories\CalorieProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'tdee', 'goal', 'daily_calorie_target'])]
class CalorieProfile extends Model
{
    /** @use HasFactory<CalorieProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'goal' => Goal::class,
            'tdee' => 'integer',
            'daily_calorie_target' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
