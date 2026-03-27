<?php

namespace App\Models;

use Database\Factories\CalorieEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['user_id', 'date', 'calories_consumed', 'notes'])]
class CalorieEntry extends Model
{
    /** @use HasFactory<CalorieEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'calories_consumed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * How many calories over or under target for this entry.
     * Positive = over budget, negative = under budget.
     */
    public function overUnder(int $dailyTarget): int
    {
        return $this->calories_consumed - $dailyTarget;
    }
}
