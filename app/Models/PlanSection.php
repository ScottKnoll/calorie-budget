<?php

namespace App\Models;

use Database\Factories\PlanSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_plan_id', 'title', 'body', 'position'])]
class PlanSection extends Model
{
    /** @use HasFactory<PlanSectionFactory> */
    use HasFactory;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ClientPlan::class, 'client_plan_id');
    }
}
