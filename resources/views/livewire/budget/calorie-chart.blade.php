<section class="w-full max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Calorie Chart</flux:heading>
            <flux:text>Visualise your calorie intake over time.</flux:text>
        </div>
        <a href="{{ route('budget.log') }}" wire:navigate class="text-sm text-zinc-700 transition-colors hover:text-zinc-500 dark:text-zinc-300 dark:hover:text-zinc-400">
            &larr; Daily Log
        </a>
    </div>

    @if ($this->entries->isEmpty() && $this->chartPeriod === 'week')
        <flux:callout variant="info" icon="chart-bar">
            <flux:callout.heading>No entries logged yet.</flux:callout.heading>
            <flux:callout.text>
                Log your calories on the <a href="{{ route('budget.log') }}" wire:navigate class="font-medium text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400">Daily Log</a> page to start seeing your chart.
            </flux:callout.text>
        </flux:callout>
    @else

        {{-- Period toggle --}}
        <div class="mb-6 flex gap-1">
            <flux:button
                wire:click="$set('chartPeriod', 'week')"
                :variant="$chartPeriod === 'week' ? 'primary' : 'ghost'"
                size="sm"
            >Week</flux:button>
            <flux:button
                wire:click="$set('chartPeriod', 'month')"
                :variant="$chartPeriod === 'month' ? 'primary' : 'ghost'"
                size="sm"
            >Month</flux:button>
            <flux:button
                wire:click="$set('chartPeriod', 'year')"
                :variant="$chartPeriod === 'year' ? 'primary' : 'ghost'"
                size="sm"
            >Year</flux:button>
        </div>

        {{-- Summary stat cards --}}
        <div class="mb-6 grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Average</flux:text>
                @if ($this->averageCalories !== null)
                    <p class="text-2xl font-semibold tabular-nums">{{ number_format($this->averageCalories) }}</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">cal / day</flux:text>
                @else
                    <p class="text-2xl font-semibold tabular-nums text-zinc-400">—</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">no data</flux:text>
                @endif
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Days Logged</flux:text>
                <p class="text-2xl font-semibold tabular-nums">{{ $this->daysLogged }}</p>
                <flux:text class="mt-1 text-xs text-zinc-400">{{ $this->periodLabel }}</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">Over Target</flux:text>
                @if ($this->daysOverTarget !== null)
                    <p class="text-2xl font-semibold tabular-nums {{ $this->daysOverTarget > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $this->daysOverTarget }}
                    </p>
                    <flux:text class="mt-1 text-xs text-zinc-400">day{{ $this->daysOverTarget === 1 ? '' : 's' }}</flux:text>
                @else
                    <p class="text-2xl font-semibold tabular-nums text-zinc-400">—</p>
                    <flux:text class="mt-1 text-xs text-zinc-400">no target set</flux:text>
                @endif
            </div>
        </div>

        {{-- SVG Chart --}}
        @if ($this->chartData)
            @php $chart = $this->chartData; @endphp
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">Calories — {{ $this->periodLabel }}</flux:heading>
                    @if ($this->chartPeriod === 'year')
                        <flux:text class="text-xs text-zinc-400">Weekly averages</flux:text>
                    @endif
                </div>

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

                    {{-- Daily target line --}}
                    @if ($chart['targetY'] !== null)
                        <line
                            x1="0" y1="{{ $chart['targetY'] }}"
                            x2="600" y2="{{ $chart['targetY'] }}"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-dasharray="6,3"
                            class="text-amber-500"
                        />
                    @endif

                    {{-- Fill area under the calorie line --}}
                    <polyline
                        points="{{ $chart['points'] }} 600,200 0,200"
                        fill="currentColor"
                        stroke="none"
                        class="text-blue-500/10"
                    />

                    {{-- Calorie line --}}
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

                @if ($chart['targetY'] !== null)
                    <div class="mt-2 flex items-center gap-2">
                        <svg viewBox="0 0 24 4" class="h-1 w-6 shrink-0" aria-hidden="true">
                            <line x1="0" y1="2" x2="24" y2="2" stroke="currentColor" stroke-width="2" stroke-dasharray="4,2" class="text-amber-500" />
                        </svg>
                        <flux:text class="text-xs text-zinc-400">Target: {{ number_format($this->profile->daily_calorie_target) }} cal/day</flux:text>
                    </div>
                @endif
            </div>
        @elseif ($this->entries->isNotEmpty())
            <flux:callout variant="info" icon="chart-bar">
                <flux:callout.heading>Not enough data to chart.</flux:callout.heading>
                <flux:callout.text>Log at least 2 days in this period to see a chart.</flux:callout.text>
            </flux:callout>
        @else
            <flux:callout variant="info" icon="chart-bar">
                <flux:callout.heading>No entries for this period.</flux:callout.heading>
                <flux:callout.text>
                    Log your calories on the <a href="{{ route('budget.log') }}" wire:navigate class="font-medium text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400">Daily Log</a> page.
                </flux:callout.text>
            </flux:callout>
        @endif
    @endif
</section>
