<section class="w-full max-w-lg">
    <flux:heading size="xl" class="mb-1">Daily Log</flux:heading>
    <flux:text class="mb-6">{{ \Illuminate\Support\Carbon::parse($date)->format('l, F j, Y') }}</flux:text>

    @if (! $this->profile)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
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

        {{-- Daily status summary --}}
        @if ($this->calories_consumed !== null)
            <div class="mb-6 grid grid-cols-3 gap-4 text-center">
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="xl">{{ number_format($this->calories_consumed) }}</flux:heading>
                    <flux:text>Consumed</flux:text>
                </div>
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:heading size="xl">{{ number_format($this->profile->daily_calorie_target) }}</flux:heading>
                    <flux:text>Target</flux:text>
                </div>
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    @php $remaining = $this->remaining; @endphp
                    <flux:heading size="xl" class="{{ $remaining < 0 ? 'text-red-500' : 'text-green-600 dark:text-green-400' }}">
                        {{ $remaining >= 0 ? number_format($remaining) : '-' . number_format(abs($remaining)) }}
                    </flux:heading>
                    <flux:text>Remaining</flux:text>
                </div>
            </div>

            @php $overUnder = $this->overUnder; @endphp
            @if ($overUnder > 0)
                <flux:callout variant="danger" icon="arrow-trending-up" class="mb-6">
                    <flux:callout.heading>{{ number_format($overUnder) }} calories over target today.</flux:callout.heading>
                </flux:callout>
            @elseif ($overUnder < 0)
                <flux:callout variant="success" icon="arrow-trending-down" class="mb-6">
                    <flux:callout.heading>{{ number_format(abs($overUnder)) }} calories under target today.</flux:callout.heading>
                </flux:callout>
            @else
                <flux:callout variant="success" icon="check-circle" class="mb-6">
                    <flux:callout.heading>Right on target today!</flux:callout.heading>
                </flux:callout>
            @endif
        @endif

        {{-- Log form --}}
        <form wire:submit="save" class="space-y-6">
            <flux:field>
                <flux:label>Calories Consumed</flux:label>
                <flux:input wire:model="calories_consumed" type="number" min="0" max="99999" placeholder="e.g. 1850" required />
                <flux:error name="calories_consumed" />
            </flux:field>

            <flux:field>
                <flux:label>Notes <flux:badge>optional</flux:badge></flux:label>
                <flux:input wire:model="notes" type="text" placeholder="e.g. ate out for dinner" maxlength="500" />
                <flux:error name="notes" />
            </flux:field>

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ $this->existingEntry ? 'Update Entry' : 'Log Entry' }}
                </flux:button>

                <flux:link href="{{ route('budget.summary') }}" wire:navigate class="text-sm">
                    View weekly summary &rarr;
                </flux:link>
            </div>
        </form>
    @endif
</section>
