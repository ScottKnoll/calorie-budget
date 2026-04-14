<?php

namespace App\Models;

use Database\Factories\WeightEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'date', 'weight_lbs', 'notes'])]
class WeightEntry extends Model
{
    /** @use HasFactory<WeightEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight_lbs' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
