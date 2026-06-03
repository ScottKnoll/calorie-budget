<?php

namespace App\Livewire\Budget;

use App\Concerns\IntakeLabelOptions;
use App\Models\ClientPlan;
use App\Models\IntakeResponse;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Plan')]
class MyPlan extends Component
{
    use IntakeLabelOptions;

    public function mount(): void
    {
        if (! auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    #[Computed]
    public function plan(): ?ClientPlan
    {
        return auth()->user()->clientPlans()->first();
    }

    #[Computed]
    public function intake(): ?IntakeResponse
    {
        return auth()->user()->intakeResponse;
    }

    public function render(): View
    {
        return view('livewire.budget.my-plan');
    }
}
