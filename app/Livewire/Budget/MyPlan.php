<?php

namespace App\Livewire\Budget;

use App\Models\ClientPlan;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Plan')]
class MyPlan extends Component
{
    public function mount(): void
    {
        if (! auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    #[Computed]
    public function plan(): ?ClientPlan
    {
        return auth()->user()->clientPlans()->with('sections')->first();
    }

    public function render(): View
    {
        return view('livewire.budget.my-plan');
    }
}
