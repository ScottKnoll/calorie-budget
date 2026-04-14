<section class="w-full max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Workout Log</flux:heading>
            <flux:text>Track your workouts and activity.</flux:text>
        </div>
        <flux:link href="{{ route('budget.log') }}" wire:navigate class="text-sm">
            &larr; Daily Log
        </flux:link>
    </div>

    {{-- Add workout form --}}
    <form wire:submit="addEntry" class="mb-10 space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Date</flux:label>
                <flux:input type="date" wire:model="date" required />
                <flux:error name="date" />
            </flux:field>

            <flux:field>
                <flux:label>Type</flux:label>
                <flux:select wire:model.live="workoutType">
                    @foreach ($this->workoutTypeOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="workoutType" />
            </flux:field>
        </div>

        @if ($workoutType === 'custom')
            <flux:field>
                <flux:label>Custom Type</flux:label>
                <flux:input
                    wire:model="customType"
                    type="text"
                    placeholder="e.g. Rock Climbing, Swimming…"
                    maxlength="100"
                    required
                />
                <flux:error name="customType" />
            </flux:field>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Duration (min)</flux:label>
                <flux:input
                    wire:model="durationMinutes"
                    type="number"
                    min="1"
                    max="600"
                    placeholder="45"
                    required
                />
                <flux:error name="durationMinutes" />
            </flux:field>

            <flux:field>
                <flux:label>Calories Burned (optional)</flux:label>
                <flux:input
                    wire:model="caloriesBurned"
                    type="number"
                    min="1"
                    max="9999"
                    placeholder="e.g. 350"
                />
                <flux:error name="caloriesBurned" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Notes (optional)</flux:label>
            <flux:textarea
                wire:model="notes"
                placeholder="What did you do? Any PRs? How did it feel?"
                rows="2"
                maxlength="1000"
            />
            <flux:error name="notes" />
        </flux:field>

        <flux:button type="submit" variant="primary">Add Workout</flux:button>
    </form>

    <div class="mt-10 h-px bg-zinc-200 dark:bg-zinc-700"></div>

    <div class="mt-8">

    @if ($this->entries->isEmpty())
        <flux:callout variant="info" icon="bolt">
            <flux:callout.heading>No workouts logged yet.</flux:callout.heading>
            <flux:callout.text>Use the form above to log your first workout.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Summary stat cards --}}
        <div class="mb-6 grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">This Week</flux:text>
                <p class="text-2xl font-semibold tabular-nums">{{ $this->weeklyCount }}</p>
                <flux:text class="mt-1 text-xs text-zinc-400">workout{{ $this->weeklyCount === 1 ? '' : 's' }}</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Calories Burned</flux:text>
                @if ($this->weeklyCaloriesBurned > 0)
                    <p class="text-2xl font-semibold tabular-nums">{{ number_format($this->weeklyCaloriesBurned) }}</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">cal burned this week</flux:text>
                @else
                    <p class="text-2xl font-semibold tabular-nums text-zinc-400">—</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">no cal data this week</flux:text>
                @endif
            </div>
        </div>

        {{-- History table --}}
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Duration</flux:table.column>
                <flux:table.column>Cal Burned</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->groupedEntries as $dateString => $dayEntries)
                    {{-- Date group header --}}
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="pt-4 pb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                    {{ \Illuminate\Support\Carbon::parse($dateString)->format('D, M j, Y') }}
                                </span>
                                @if (\Illuminate\Support\Carbon::parse($dateString)->isToday())
                                    <flux:badge size="sm" color="blue" inset="top bottom">Today</flux:badge>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>

                    {{-- Workouts for this date --}}
                    @foreach ($dayEntries as $entry)
                        <flux:table.row wire:key="{{ $entry->id }}">
                            @if ($editingId === $entry->id)
                                <flux:table.cell colspan="4">
                                    <form wire:submit="updateEntry" class="space-y-3 py-1">
                                        <div class="grid grid-cols-2 gap-3">
                                            <flux:field>
                                                <flux:label>Type</flux:label>
                                                <flux:select wire:model.live="editingWorkoutType">
                                                    @foreach ($this->workoutTypeOptions() as $value => $label)
                                                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                                    @endforeach
                                                </flux:select>
                                                <flux:error name="editingWorkoutType" />
                                            </flux:field>

                                            @if ($editingWorkoutType === 'custom')
                                                <flux:field>
                                                    <flux:label>Custom Type</flux:label>
                                                    <flux:input
                                                        wire:model="editingCustomType"
                                                        type="text"
                                                        maxlength="100"
                                                        required
                                                    />
                                                    <flux:error name="editingCustomType" />
                                                </flux:field>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <flux:field>
                                                <flux:label>Duration (min)</flux:label>
                                                <flux:input
                                                    wire:model="editingDurationMinutes"
                                                    type="number"
                                                    min="1"
                                                    max="600"
                                                    required
                                                />
                                                <flux:error name="editingDurationMinutes" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Calories Burned</flux:label>
                                                <flux:input
                                                    wire:model="editingCaloriesBurned"
                                                    type="number"
                                                    min="1"
                                                    max="9999"
                                                    placeholder="optional"
                                                />
                                                <flux:error name="editingCaloriesBurned" />
                                            </flux:field>
                                        </div>

                                        <flux:field>
                                            <flux:label>Notes</flux:label>
                                            <flux:input
                                                wire:model="editingNotes"
                                                type="text"
                                                placeholder="optional"
                                                maxlength="1000"
                                            />
                                            <flux:error name="editingNotes" />
                                        </flux:field>

                                        <div class="flex gap-2">
                                            <flux:button type="submit" variant="primary" size="sm">Save</flux:button>
                                            <flux:button type="button" wire:click="cancelEditing" variant="ghost" size="sm">Cancel</flux:button>
                                        </div>
                                    </form>
                                </flux:table.cell>
                            @else
                                <flux:table.cell class="font-medium">
                                    {{ $entry->typeLabel() }}
                                    @if ($entry->notes)
                                        <flux:text class="mt-0.5 text-xs text-zinc-400">{{ $entry->notes }}</flux:text>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="tabular-nums">
                                    {{ $entry->duration_minutes }} min
                                </flux:table.cell>

                                <flux:table.cell class="tabular-nums text-zinc-500 dark:text-zinc-400">
                                    {{ $entry->calories_burned ? number_format($entry->calories_burned).' cal' : '—' }}
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <flux:button wire:click="startEditing({{ $entry->id }})" variant="ghost" size="sm" icon="pencil" />
                                        <flux:button
                                            wire:click="deleteEntry({{ $entry->id }})"
                                            wire:confirm="Delete this workout entry?"
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
                @endforeach
            </flux:table.rows>
        </flux:table>

        <flux:text class="mt-4 text-center text-xs text-zinc-400">
            {{ $this->entries->count() }} total workout{{ $this->entries->count() === 1 ? '' : 's' }} logged
        </flux:text>
    @endif

    </div>
</section>

