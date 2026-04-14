<section class="w-full max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Daily Log</flux:heading>
            <flux:text>{{ \Illuminate\Support\Carbon::parse($date)->format('l, F j, Y') }}</flux:text>
        </div>
        <flux:link href="{{ route('budget.summary') }}" wire:navigate class="text-sm">
            Weekly Summary &rarr;
        </flux:link>
    </div>

    <div class="mb-6 flex items-center gap-2">
        <flux:button wire:click="previousDay" variant="ghost" icon="chevron-left" size="sm">Previous</flux:button>
        <flux:button
            wire:click="nextDay"
            variant="ghost"
            icon-trailing="chevron-right"
            size="sm"
            :disabled="$this->isToday"
        >Next</flux:button>
        @if (! $this->isToday)
            <flux:link href="{{ route('budget.log') }}" wire:navigate class="ml-2 text-sm">
                Jump to today
            </flux:link>
        @endif
    </div>

    @if (! $this->profile)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>No calorie setup found.</flux:callout.heading>
            <flux:callout.text>
                <flux:link href="{{ route('budget.setup') }}" wire:navigate>Complete your setup</flux:link> before logging entries.
            </flux:callout.text>
        </flux:callout>
    @else
        @if (session('status') === 'saved')
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                <flux:callout.heading>Entry saved.</flux:callout.heading>
            </flux:callout>
        @endif

        {{-- Weekly bank stat cards --}}
        <div class="mb-2 grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Today's Allowance</flux:text>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ $this->todaysAllowance !== null ? number_format($this->todaysAllowance) : '—' }}
                </p>
                <flux:text class="mt-1 text-xs text-zinc-400">cal budgeted today</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Consumed</flux:text>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ $calories_consumed !== null ? number_format($calories_consumed) : '—' }}
                </p>
                <flux:text class="mt-1 text-xs text-zinc-400">cal logged today</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Remaining</flux:text>
                @php $remaining = $this->remainingToday; @endphp
                <p class="text-2xl font-semibold tabular-nums {{ $remaining !== null && $remaining < 0 ? 'text-red-500' : ($remaining !== null && $remaining === 0 ? 'text-zinc-400' : '') }}">
                    @if ($remaining !== null)
                        {{ $remaining < 0 ? '-' : '' }}{{ number_format(abs($remaining)) }}
                    @else
                        —
                    @endif
                </p>
                <flux:text class="mt-1 text-xs text-zinc-400">
                    @if ($remaining !== null)
                        {{ $remaining < 0 ? 'cal over allowance' : 'cal left today' }}
                    @else
                        log to see remaining
                    @endif
                </flux:text>
            </div>
        </div>

        {{-- Weekly bank context --}}
        @if ($this->todaysAllowance !== null)
            <flux:text class="mb-6 text-xs text-zinc-400">
                Weekly bank: {{ number_format($this->profile->daily_calorie_target * 7 - $this->consumedThroughYesterday) }} cal remaining
                across {{ $this->daysRemainingThisWeek }} {{ Str::plural('day', $this->daysRemainingThisWeek) }}
                &mdash; target {{ number_format($this->profile->daily_calorie_target) }} cal/day
            </flux:text>
        @endif

        {{-- Log form --}}
        <form wire:submit="save" class="space-y-5">
            <flux:field>
                <flux:label>Calories Consumed</flux:label>
                <flux:input
                    wire:model="calories_consumed"
                    type="number"
                    min="0"
                    max="99999"
                    placeholder="e.g. 1,850"
                    required
                />
                <flux:error name="calories_consumed" />
            </flux:field>

            <div>
                <flux:text class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Macros (Optional)</flux:text>
                <div class="grid grid-cols-3 gap-3">
                    <flux:field>
                        <flux:label>Carbs</flux:label>
                        <flux:input
                            wire:model="carbs_grams"
                            type="number"
                            min="0"
                            max="9999"
                            placeholder="g"
                        />
                        <flux:error name="carbs_grams" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Protein</flux:label>
                        <flux:input
                            wire:model="protein_grams"
                            type="number"
                            min="0"
                            max="9999"
                            placeholder="g"
                        />
                        <flux:error name="protein_grams" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fat</flux:label>
                        <flux:input
                            wire:model="fat_grams"
                            type="number"
                            min="0"
                            max="9999"
                            placeholder="g"
                        />
                        <flux:error name="fat_grams" />
                    </flux:field>
                </div>
            </div>

            <flux:field>
                <flux:label>Weight (Optional)</flux:label>
                <flux:input
                    wire:model="weight_lbs"
                    type="number"
                    min="50"
                    max="999"
                    step="0.1"
                    placeholder="lbs"
                    suffix="lbs"
                />
                <flux:error name="weight_lbs" />
            </flux:field>

            <flux:field>
                <flux:label>Notes (Optional)</flux:label>
                <flux:input
                    wire:model="notes"
                    type="text"
                    placeholder="e.g. ate out for dinner"
                    maxlength="500"
                />
                <flux:error name="notes" />
            </flux:field>

            <flux:button type="submit" variant="primary">
                {{ $this->existingEntry ? 'Update Entry' : 'Log Entry' }}
            </flux:button>
        </form>
    @endif
</section>
