<?php

namespace App\Models;

use Database\Factories\ClientPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title'])]
class ClientPlan extends Model
{
    /** @use HasFactory<ClientPlanFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PlanSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(PlanSection::class)->orderBy('position');
    }
}
