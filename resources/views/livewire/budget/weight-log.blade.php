<section class="w-full max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Weight Log</flux:heading>
            <flux:text>Track your progress over time.</flux:text>
        </div>
        <a href="{{ route('budget.log') }}" wire:navigate class="text-sm text-zinc-700 transition-colors hover:text-zinc-500 dark:text-zinc-300 dark:hover:text-zinc-400">
            &larr; Daily Log
        </a>
    </div>

    @if ($this->entries->isEmpty())
        <flux:callout variant="info" icon="scale">
            <flux:callout.heading>No weight entries yet.</flux:callout.heading>
            <flux:callout.text>
                Log your weight on the <a href="{{ route('budget.log') }}" wire:navigate class="font-medium text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400">Daily Log</a> page to start tracking progress.
            </flux:callout.text>
        </flux:callout>
    @else

        {{-- Summary stat cards --}}
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Current</flux:text>
                <p class="text-2xl font-semibold tabular-nums">{{ number_format($this->latestEntry->weight_lbs, 1) }}</p>
                <flux:text class="mt-1 text-xs text-zinc-400">lbs as of {{ $this->latestEntry->date->format('M j') }}</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Change</flux:text>
                @php $change = $this->netChange; @endphp
                @if ($change !== null)
                    <p class="text-2xl font-semibold tabular-nums {{ $change < 0 ? 'text-emerald-600 dark:text-emerald-400' : ($change > 0 ? 'text-red-500' : '') }}">
                        {{ $change > 0 ? '+' : '' }}{{ number_format($change, 1) }}
                    </p>
                    <flux:text class="mt-1 text-xs text-zinc-400">lbs since start</flux:text>
                @else
                    <p class="text-2xl font-semibold tabular-nums text-zinc-400">—</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">need 2+ entries</flux:text>
                @endif
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">To Goal</flux:text>
                @php $remaining = $this->remainingToGoal; @endphp
                @if ($remaining !== null)
                    <p class="text-2xl font-semibold tabular-nums {{ $remaining <= 0 ? 'text-emerald-600 dark:text-emerald-400' : '' }}">
                        @if ($remaining <= 0)
                            Goal!
                        @else
                            {{ number_format($remaining, 1) }}
                        @endif
                    </p>
                    <flux:text class="mt-1 text-xs text-zinc-400">
                        @if ($remaining <= 0)
                            goal reached
                        @else
                            lbs to go ({{ number_format($this->profile->goal_weight_lbs, 1) }} lbs)
                        @endif
                    </flux:text>
                @else
                    <p class="text-2xl font-semibold tabular-nums text-zinc-400">—</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">set goal in setup</flux:text>
                @endif
            </div>
        </div>

        {{-- SVG Chart --}}
        @if ($this->chartData)
            @php $chart = $this->chartData; @endphp
            <div class="mb-8 overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">Weight Trend</flux:heading>
                <svg viewBox="0 0 600 200" class="w-full" preserveAspectRatio="none" aria-hidden="true">
                    {{-- Horizontal grid lines --}}
                    @foreach ([0, 50, 100, 150, 200] as $gridY)
                        <line
                            x1="0" y1="{{ $gridY }}"
                            x2="600" y2="{{ $gridY }}"
                            stroke="currentColor"
                            stroke-width="0.5"
                            class="text-zinc-200 dark:text-zinc-700"
                        />
                    @endforeach

                    {{-- Goal weight line --}}
                    @if ($chart['goalY'] !== null)
                        <line
                            x1="0" y1="{{ $chart['goalY'] }}"
                            x2="600" y2="{{ $chart['goalY'] }}"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-dasharray="6,3"
                            class="text-emerald-500"
                        />
                    @endif

                    {{-- Fill area under line --}}
                    <polyline
                        points="{{ $chart['points'] }} 600,200 0,200"
                        fill="currentColor"
                        stroke="none"
                        class="text-blue-500/10"
                    />

                    {{-- Weight line --}}
                    <polyline
                        points="{{ $chart['points'] }}"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linejoin="round"
                        stroke-linecap="round"
                        class="text-blue-500"
                    />

                    {{-- Data points --}}
                    @foreach (explode(' ', $chart['points']) as $point)
                        @php [$px, $py] = explode(',', $point); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="currentColor" class="text-blue-500" />
                    @endforeach
                </svg>

                @if ($chart['goalY'] !== null)
                    <div class="mt-2 flex items-center gap-2">
                        <svg viewBox="0 0 24 4" class="h-1 w-6 shrink-0" aria-hidden="true">
                            <line x1="0" y1="2" x2="24" y2="2" stroke="currentColor" stroke-width="2" stroke-dasharray="4,2" class="text-emerald-500" />
                        </svg>
                        <flux:text class="text-xs text-zinc-400">Goal: {{ number_format($this->profile->goal_weight_lbs, 1) }} lbs</flux:text>
                    </div>
                @endif
            </div>
        @endif

        {{-- History table --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Weight</flux:table.column>
                <flux:table.column>Notes</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->entries as $entry)
                    <flux:table.row wire:key="{{ $entry->id }}">
                        @if ($editingId === $entry->id)
                            <flux:table.cell colspan="4">
                                <form wire:submit="updateEntry" class="flex flex-wrap items-end gap-3 py-1">
                                    <flux:field class="w-28">
                                        <flux:label>Weight (lbs)</flux:label>
                                        <flux:input
                                            wire:model="editingWeight"
                                            type="number"
                                            step="0.1"
                                            min="50"
                                            max="999"
                                            required
                                        />
                                        <flux:error name="editingWeight" />
                                    </flux:field>

                                    <flux:field class="flex-1">
                                        <flux:label>Notes</flux:label>
                                        <flux:input
                                            wire:model="editingNotes"
                                            type="text"
                                            placeholder="optional"
                                            maxlength="500"
                                        />
                                    </flux:field>

                                    <div class="flex gap-2">
                                        <flux:button type="submit" variant="primary" size="sm">Save</flux:button>
                                        <flux:button type="button" wire:click="cancelEditing" variant="ghost" size="sm">Cancel</flux:button>
                                    </div>
                                </form>
                            </flux:table.cell>
                        @else
                            <flux:table.cell>
                                <a
                                    href="{{ route('budget.log', ['date' => $entry->date->toDateString()]) }}"
                                    wire:navigate
                                    class="text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400 {{ $entry->date->isToday() ? 'font-semibold' : '' }}"
                                >{{ $entry->date->format('D, M j, Y') }}</a>
                                @if ($entry->date->isToday())
                                    <flux:badge size="sm" color="blue" inset="top bottom" class="ml-2">Today</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell variant="strong" class="tabular-nums">
                                {{ number_format($entry->weight_lbs, 1) }} lbs
                            </flux:table.cell>

                            <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                                {{ $entry->notes ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="startEditing({{ $entry->id }})" variant="ghost" size="sm" icon="pencil" />
                                    <flux:button
                                        wire:click="deleteEntry({{ $entry->id }})"
                                        wire:confirm="Delete this weight entry?"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        class="text-red-500 hover:text-red-600"
                                    />
                                </div>
                            </flux:table.cell>
                        @endif
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</section>
