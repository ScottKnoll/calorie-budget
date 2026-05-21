<?php

namespace App\Livewire\Coach;

use App\Enums\UserType;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Clients')]
class Dashboard extends Component
{
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
    public function clients(): Collection
    {
        return User::where('user_type', UserType::Client)
            ->withCount(['calorieEntries', 'weightEntries', 'workoutEntries'])
            ->orderBy('name')
            ->get();
    }

    public function deleteClient(int $clientId): void
    {
        $client = User::where('id', $clientId)
            ->where('user_type', UserType::Client)
            ->firstOrFail();

        $client->delete();

        Flux::modal('delete-client-'.$clientId)->close();

        unset($this->clients);
    }

    public function render(): View
    {
        return view('livewire.coach.dashboard');
    }
}
