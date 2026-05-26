<?php

namespace App\Livewire\Budget;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Check-Ins')]
class CheckIns extends Component
{
    public function mount(): void
    {
        if (! auth()->user()->isClient()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function render(): View
    {
        $checkIns = auth()->user()->checkIns()->get();

        return view('livewire.budget.check-ins', compact('checkIns'));
    }
}
