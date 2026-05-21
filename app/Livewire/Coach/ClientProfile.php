<?php

namespace App\Livewire\Coach;

use App\Concerns\IntakeLabelOptions;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Client Profile')]
class ClientProfile extends Component
{
    use IntakeLabelOptions;

    public User $client;

    public function mount(User $client): void
    {
        if (! auth()->user()->isCoach()) {
            abort(403);
        }

        if (! $client->isClient()) {
            abort(404);
        }

        $this->client = $client;
    }

    public function render(): View
    {
        return view('livewire.coach.client-profile');
    }
}
