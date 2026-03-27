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
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="pb-3 text-left font-medium text-zinc-500">Day</th>
                        <th class="pb-3 text-right font-medium text-zinc-500">Target</th>
                        <th class="pb-3 text-right font-medium text-zinc-500">Consumed</th>
                        <th class="pb-3 text-right font-medium text-zinc-500">Over / Under</th>
                        <th class="pb-3 text-right font-medium text-zinc-500">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = 0; @endphp

                    @foreach ($this->days as $day)
                        @php
                            if ($day['over_under'] !== null) {
                                $runningBalance += $day['over_under'];
                            }
                            $isToday = $day['date']->isToday();
                        @endphp

                        <tr class="border-b border-zinc-100 dark:border-zinc-800 {{ $isToday ? 'bg-zinc-50 dark:bg-zinc-800/50' : '' }}">
                            <td class="py-3 pr-4">
                                <span class="{{ $isToday ? 'font-semibold' : '' }}">
                                    {{ $day['date']->format('D, M j') }}
                                </span>
                                @if ($isToday)
                                    <flux:badge size="sm" color="blue" class="ml-2">Today</flux:badge>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-right text-zinc-500">
                                {{ number_format($this->profile->daily_calorie_target) }}
                            </td>

                            <td class="py-3 pr-4 text-right">
                                @if ($day['calories_consumed'] !== null)
                                    {{ number_format($day['calories_consumed']) }}
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-right">
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
                            </td>

                            <td class="py-3 text-right">
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-zinc-300 dark:border-zinc-600">
                        <td colspan="3" class="pt-3 font-medium">Weekly Total</td>
                        <td class="pt-3"></td>
                        <td class="pt-3 text-right font-semibold">
                            @if ($this->weeklyBalance > 0)
                                <span class="text-red-500">+{{ number_format($this->weeklyBalance) }}</span>
                            @elseif ($this->weeklyBalance < 0)
                                <span class="text-green-600 dark:text-green-400">{{ number_format($this->weeklyBalance) }}</span>
                            @else
                                <span class="text-zinc-400">0</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6">
            <flux:link href="{{ route('budget.log') }}" wire:navigate>
                &larr; Back to today's log
            </flux:link>
        </div>
    @endif
</section>
