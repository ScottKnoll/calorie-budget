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
                <div wire:key="{{ $checkIn->id }}" class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">

                    {{-- Client submission --}}
                    <div class="p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <flux:heading size="sm">{{ $checkIn->created_at->format('F j, Y') }}</flux:heading>
                            <div class="flex items-center gap-2">
                                <flux:button :href="route('budget.check-in.edit', $checkIn)" wire:navigate size="sm" variant="ghost" icon="pencil-square">Edit</flux:button>
                                <flux:badge color="zinc">{{ number_format($checkIn->weight, 1) }} lbs</flux:badge>
                            </div>
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

                    {{-- Coach response --}}
                    <div class="border-t border-zinc-100 dark:border-zinc-800">
                        @if ($checkIn->hasCoachNotes())
                            <div class="p-5">
                                <flux:text class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Coach Notes</flux:text>
                                <div class="space-y-3">
                                    @foreach ([
                                        ['value' => $checkIn->coach_workout, 'label' => 'Workout'],
                                        ['value' => $checkIn->coach_nutrition, 'label' => 'Nutrition'],
                                        ['value' => $checkIn->coach_habits, 'label' => 'Habits'],
                                        ['value' => $checkIn->coach_general, 'label' => 'General'],
                                        ['value' => $checkIn->coach_focus_next_week, 'label' => 'Focus for Next Week'],
                                    ] as $note)
                                        @if ($note['value'])
                                            <div>
                                                <flux:text class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ $note['label'] }}</flux:text>
                                                <flux:text class="mt-0.5 whitespace-pre-line">{{ $note['value'] }}</flux:text>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="px-5 py-3">
                                <flux:text class="text-sm text-zinc-400 dark:text-zinc-500">Awaiting coach feedback</flux:text>
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</section>
