<section class="w-full">
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">Weekly Summary</flux:heading>
            <flux:text>
                {{ \Illuminate\Support\Carbon::parse($weekStart)->format('M j') }}
                &ndash;
                {{ $this->weekEnd->format('M j, Y') }}
            </flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button wire:click="previousWeek" variant="ghost" icon="chevron-left" size="sm">Previous</flux:button>
            <flux:button wire:click="nextWeek" variant="ghost" icon-trailing="chevron-right" size="sm">Next</flux:button>
        </div>
    </div>

    @if (! $this->profile)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>No calorie setup found.</flux:callout.heading>
            <flux:callout.text>
                <a href="{{ route('budget.setup') }}" wire:navigate class="font-medium text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400">Complete your setup</a> to see your weekly summary.
            </flux:callout.text>
        </flux:callout>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Day</flux:table.column>
                <flux:table.column>Target</flux:table.column>
                <flux:table.column>Consumed</flux:table.column>
                <flux:table.column>Over / Under</flux:table.column>
                <flux:table.column>Running Balance</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @php $runningBalance = 0; @endphp

                @foreach ($this->days as $day)
                    @php
                        if ($day['over_under'] !== null) {
                            $runningBalance += $day['over_under'];
                        }
                        $isToday = $day['date']->isToday();
                    @endphp

                    <flux:table.row>
                        <flux:table.cell>
                            @if ($day['date']->isFuture())
                                <span class="text-zinc-400 dark:text-zinc-500 {{ $isToday ? 'font-semibold' : '' }}">
                                    {{ $day['date']->format('D, M j') }}
                                </span>
                            @else
                                <a
                                    href="{{ route('budget.log', ['date' => $day['date']->toDateString()]) }}"
                                    wire:navigate
                                    class="text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400 {{ $isToday ? 'font-semibold' : '' }}"
                                >{{ $day['date']->format('D, M j') }}</a>
                                @if ($isToday)
                                    <flux:badge size="sm" color="blue" inset="top bottom" class="ml-2">Today</flux:badge>
                                @endif
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="tabular-nums">
                            {{ number_format($this->profile->daily_calorie_target) }}
                        </flux:table.cell>

                        <flux:table.cell variant="strong" class="tabular-nums">
                            @if ($day['calories_consumed'] !== null)
                                {{ number_format($day['calories_consumed']) }}
                            @else
                                <span class="font-normal text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="tabular-nums">
                            @if ($day['over_under'] !== null)
                                @if ($day['over_under'] > 0)
                                    <span class="font-medium text-red-500">+{{ number_format($day['over_under']) }}</span>
                                @elseif ($day['over_under'] < 0)
                                    <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($day['over_under']) }}</span>
                                @else
                                    <span class="text-zinc-400">0</span>
                                @endif
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="tabular-nums">
                            @if ($day['over_under'] !== null)
                                @if ($runningBalance > 0)
                                    <span class="font-medium text-red-500">+{{ number_format($runningBalance) }}</span>
                                @elseif ($runningBalance < 0)
                                    <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($runningBalance) }}</span>
                                @else
                                    <span class="text-zinc-400">0</span>
                                @endif
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach

                {{-- Weekly total row --}}
                <flux:table.row>
                    <flux:table.cell class="font-semibold" colspan="2">Weekly Total</flux:table.cell>
                    <flux:table.cell variant="strong" class="tabular-nums">
                        {{ number_format($this->weeklyConsumed) }}
                    </flux:table.cell>
                    <flux:table.cell></flux:table.cell>
                    <flux:table.cell class="tabular-nums font-semibold">
                        @if ($this->weeklyBalance > 0)
                            <span class="text-red-500">+{{ number_format($this->weeklyBalance) }}</span>
                        @elseif ($this->weeklyBalance < 0)
                            <span class="text-green-600 dark:text-green-400">{{ number_format($this->weeklyBalance) }}</span>
                        @else
                            <span class="text-zinc-400">0</span>
                        @endif
                    </flux:table.cell>
                </flux:table.row>

                {{-- Daily average row --}}
                @if ($this->averageDailyConsumed !== null)
                    <flux:table.row>
                        <flux:table.cell class="text-zinc-500" colspan="2">Avg / Day</flux:table.cell>
                        <flux:table.cell variant="strong" class="tabular-nums text-zinc-500">
                            {{ number_format($this->averageDailyConsumed) }}
                        </flux:table.cell>
                        <flux:table.cell colspan="2"></flux:table.cell>
                    </flux:table.row>
                @endif
            </flux:table.rows>
        </flux:table>

        @if ($this->weeklyMacroSummary)
            <div class="mt-8">
                <flux:heading size="lg" class="mb-4">Weekly Macros</flux:heading>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach (['carbs' => 'Carbs', 'protein' => 'Protein', 'fat' => 'Fat'] as $key => $label)
                        @php
                            $macro = $this->weeklyMacroSummary[$key];
                            $delta = $macro['delta'];
                        @endphp
                        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <flux:text class="mb-1 text-xs uppercase tracking-wide text-zinc-500">{{ $label }}</flux:text>
                            <p class="text-2xl font-semibold tabular-nums">{{ number_format($macro['consumed']) }}g</p>
                            <flux:text class="mt-1 text-xs text-zinc-400">of {{ number_format($macro['target']) }}g target</flux:text>
                            <p class="mt-2 text-sm font-medium tabular-nums
                                {{ $delta > 0 ? 'text-red-500' : ($delta < 0 ? 'text-green-600 dark:text-green-400' : 'text-zinc-400') }}">
                                @if ($delta > 0)
                                    +{{ number_format($delta) }}g over
                                @elseif ($delta < 0)
                                    {{ number_format($delta) }}g under
                                @else
                                    On target
                                @endif
                            </p>
                            @if ($macro['daily_average'] !== null)
                                <flux:text class="mt-2 text-xs text-zinc-400">
                                    avg {{ number_format($macro['daily_average']) }}g/day
                                </flux:text>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- SVG calorie chart for the week --}}
        @if ($this->chartData)
            @php $chart = $this->chartData; @endphp
            <div class="mt-8 max-w-2xl overflow-hidden rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3">Calories This Week</flux:heading>
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

                    {{-- Data point dots --}}
                    @foreach ($chart['dotPoints'] as $dot)
                        <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="3" fill="currentColor" class="text-blue-500" />
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
        @endif

        <div class="mt-6">
            <a href="{{ route('budget.log') }}" wire:navigate class="text-sm text-zinc-700 transition-colors hover:text-zinc-500 dark:text-zinc-300 dark:hover:text-zinc-400">
                &larr; Back to today's log
            </a>
        </div>
    @endif
</section>
