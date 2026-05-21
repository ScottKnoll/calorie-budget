<?php

namespace App\Livewire\Budget;

use App\Concerns\IntakeLabelOptions;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Intake Review')]
class IntakeReview extends Component
{
    use IntakeLabelOptions;

    public function mount(): void
    {
        if (! auth()->user()->isCoach()) {
            abort(403);
        }
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        return User::with('intakeResponse')
            ->whereNotNull('intake_completed_at')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.budget.intake-review');
    }
}
