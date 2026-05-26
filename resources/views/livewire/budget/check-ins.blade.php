<section class="w-full max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">My Check-Ins</flux:heading>
            <flux:text>A record of your weekly reflections.</flux:text>
        </div>
        <flux:button :href="route('budget.check-in')" wire:navigate variant="primary" size="sm" icon="plus">
            New Check-In
        </flux:button>
    </div>

    @if ($checkIns->isEmpty())
        <flux:callout variant="info" icon="clipboard-document-list">
            <flux:callout.heading>No check-ins yet.</flux:callout.heading>
            <flux:callout.text>
                Submit your first <a href="{{ route('budget.check-in') }}" wire:navigate class="font-medium text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400">weekly check-in</a> to start tracking your progress.
            </flux:callout.text>
        </flux:callout>
    @else
        <div class="space-y-4">
            @foreach ($checkIns as $checkIn)
                <div wire:key="{{ $checkIn->id }}" class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="sm">{{ $checkIn->created_at->format('F j, Y') }}</flux:heading>
                        <flux:badge color="zinc">{{ number_format($checkIn->weight, 1) }} lbs</flux:badge>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">How did the week feel overall?</flux:text>
                            <flux:text class="mt-0.5">{{ $checkIn->week_feeling }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">What went well?</flux:text>
                            <flux:text class="mt-0.5">{{ $checkIn->went_well }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">What felt hardest?</flux:text>
                            <flux:text class="mt-0.5">{{ $checkIn->felt_hardest }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Hunger, energy & sleep</flux:text>
                            <flux:text class="mt-0.5">{{ $checkIn->hunger_energy_sleep }}</flux:text>
                        </div>

                        <div>
                            <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Activity consistency</flux:text>
                            <flux:text class="mt-0.5">{{ $checkIn->activity_consistency }}</flux:text>
                        </div>

                        @if ($checkIn->need_help)
                            <div>
                                <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Needs help with</flux:text>
                                <flux:text class="mt-0.5">{{ $checkIn->need_help }}</flux:text>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
