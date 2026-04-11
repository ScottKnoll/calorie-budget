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
                <flux:link href="{{ route('budget.setup') }}" wire:navigate>Complete your setup</flux:link> to see your weekly summary.
            </flux:callout.text>
        </flux:callout>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Day</flux:table.column>
                <flux:table.column class="text-right">Target</flux:table.column>
                <flux:table.column class="text-right">Consumed</flux:table.column>
                <flux:table.column class="text-right">Over / Under</flux:table.column>
                <flux:table.column class="text-right">Running Balance</flux:table.column>
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
                            <flux:link
                                href="{{ route('budget.log', ['date' => $day['date']->toDateString()]) }}"
                                wire:navigate
                                class="{{ $isToday ? 'font-semibold' : '' }}"
                            >{{ $day['date']->format('D, M j') }}</flux:link>
                            @if ($isToday)
                                <flux:badge size="sm" color="blue" inset="top bottom" class="ml-2">Today</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right tabular-nums text-zinc-500">
                            {{ number_format($this->profile->daily_calorie_target) }}
                        </flux:table.cell>

                        <flux:table.cell class="text-right tabular-nums">
                            @if ($day['calories_consumed'] !== null)
                                {{ number_format($day['calories_consumed']) }}
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right tabular-nums">
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

                        <flux:table.cell class="text-right tabular-nums">
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
                    <flux:table.cell class="font-semibold" colspan="4">Weekly Total</flux:table.cell>
                    <flux:table.cell class="text-right tabular-nums font-semibold">
                        @if ($this->weeklyBalance > 0)
                            <span class="text-red-500">+{{ number_format($this->weeklyBalance) }}</span>
                        @elseif ($this->weeklyBalance < 0)
                            <span class="text-green-600 dark:text-green-400">{{ number_format($this->weeklyBalance) }}</span>
                        @else
                            <span class="text-zinc-400">0</span>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6">
            <flux:link href="{{ route('budget.log') }}" wire:navigate>
                &larr; Back to today's log
            </flux:link>
        </div>
    @endif
</section>
