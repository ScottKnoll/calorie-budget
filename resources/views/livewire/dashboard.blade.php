<div>
    <flux:heading size="xl" class="mb-1">Welcome back, {{ auth()->user()->name }}</flux:heading>
    <flux:text>Here's a quick look at where you stand today.</flux:text>

    <div class="mt-12 grid gap-4 sm:grid-cols-2">

        {{-- Daily Log --}}
        <a href="{{ route('budget.log') }}" wire:navigate
            class="group flex flex-col rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                        <flux:icon.pencil-square class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading>Daily Log</flux:heading>
                </div>
                <flux:icon.chevron-right class="size-4 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
            </div>

            @if (! $this->profile)
                <flux:text class="text-zinc-400">Complete your calorie setup to get started.</flux:text>
            @elseif ($this->remainingToday === null)
                <flux:text class="text-zinc-400">Not yet logged today.</flux:text>
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                    Target: <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ number_format($this->profile->daily_calorie_target) }} cal</span>
                </p>
            @else
                <div class="flex-1">
                    <p class="text-3xl font-bold tracking-tight {{ $this->remainingToday >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                        {{ $this->remainingToday >= 0 ? '' : '−' }}{{ number_format(abs($this->remainingToday)) }}
                    </p>
                    <flux:text size="sm" class="mt-0.5 text-zinc-400">cal remaining today</flux:text>
                </div>
                <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ number_format($this->todaysEntry->calories_consumed) }} consumed
                    &middot;
                    {{ number_format($this->profile->daily_calorie_target) }} target
                </div>
            @endif
        </a>

        {{-- Weekly Summary --}}
        <a href="{{ route('budget.summary') }}" wire:navigate
            class="group flex flex-col rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                        <flux:icon.chart-bar class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading>Weekly Summary</flux:heading>
                </div>
                <flux:icon.chevron-right class="size-4 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
            </div>

            @if (! $this->profile)
                <flux:text class="text-zinc-400">Complete your calorie setup to get started.</flux:text>
            @elseif ($this->daysLoggedThisWeek === 0)
                <flux:text class="text-zinc-400">No entries logged this week.</flux:text>
            @else
                <div class="flex-1">
                    <p class="text-3xl font-bold tracking-tight {{ ($this->weeklyBalance ?? 0) <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                        {{ ($this->weeklyBalance ?? 0) > 0 ? '+' : '' }}{{ number_format($this->weeklyBalance ?? 0) }}
                    </p>
                    <flux:text size="sm" class="mt-0.5 text-zinc-400">cal {{ ($this->weeklyBalance ?? 0) > 0 ? 'over' : 'under' }} budget this week</flux:text>
                </div>
                <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $this->daysLoggedThisWeek }} of 7 days logged
                </div>
            @endif
        </a>

        {{-- Calorie Setup --}}
        <a href="{{ route('budget.setup') }}" wire:navigate
            class="group flex flex-col rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                        <flux:icon.cog-6-tooth class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading>Calorie Setup</flux:heading>
                </div>
                <flux:icon.chevron-right class="size-4 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
            </div>

            @if (! $this->profile)
                <flux:text class="text-zinc-400">Complete your calorie setup to see your numbers.</flux:text>
            @else
                <dl class="space-y-2">
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Daily Target</flux:text>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($this->profile->daily_calorie_target) }} cal</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">TDEE</flux:text>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ number_format($this->profile->tdee) }} cal</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Goal</flux:text>
                        <flux:badge size="sm" variant="outline">{{ $this->goalLabel }}</flux:badge>
                    </div>
                </dl>
            @endif
        </a>

        {{-- Weight Log --}}
        <a href="{{ route('budget.weight') }}" wire:navigate
            class="group flex flex-col rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                        <flux:icon.scale class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading>Weight Log</flux:heading>
                </div>
                <flux:icon.chevron-right class="size-4 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
            </div>

            @if (! $this->latestWeightEntry)
                <flux:text class="text-zinc-400">No weight logged yet. Start tracking your progress.</flux:text>
            @else
                <div class="flex-1">
                    <p class="text-3xl font-bold tracking-tight">
                        {{ number_format($this->latestWeightEntry->weight_lbs, 1) }}
                    </p>
                    <flux:text size="sm" class="mt-0.5 text-zinc-400">lbs as of {{ $this->latestWeightEntry->date->format('M j') }}</flux:text>
                </div>
                @if ($this->weightToGoal !== null)
                    <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                        @if ($this->weightToGoal <= 0)
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Goal reached!</span>
                            Goal: {{ number_format($this->profile->goal_weight_lbs, 1) }} lbs
                        @else
                            {{ number_format($this->weightToGoal, 1) }} lbs to goal
                            <span class="text-zinc-400">({{ number_format($this->profile->goal_weight_lbs, 1) }} lbs)</span>
                        @endif
                    </div>
                @endif
            @endif
        </a>

        {{-- Workouts --}}
        <a href="{{ route('budget.workouts') }}" wire:navigate
            class="group flex flex-col rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                        <flux:icon.bolt class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading>Workouts</flux:heading>
                </div>
                <flux:icon.chevron-right class="size-4 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
            </div>

            @if ($this->todaysWorkouts->isEmpty())
                <flux:text class="text-zinc-400">No workout logged today.</flux:text>
                @if ($this->weeklyWorkoutCount > 0)
                    <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $this->weeklyWorkoutCount }} workout{{ $this->weeklyWorkoutCount === 1 ? '' : 's' }} this week
                        @if ($this->weeklyCaloriesBurned > 0)
                            &middot; {{ number_format($this->weeklyCaloriesBurned) }} cal burned
                        @endif
                    </div>
                @endif
            @else
                <dl class="flex-1 space-y-2">
                    @foreach ($this->todaysWorkouts as $workout)
                        <div class="flex items-center justify-between">
                            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $workout->typeLabel() }}</flux:text>
                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ $workout->duration_minutes }} min
                                @if ($workout->calories_burned)
                                    &middot; {{ number_format($workout->calories_burned) }} cal
                                @endif
                            </span>
                        </div>
                    @endforeach
                </dl>
                <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $this->weeklyWorkoutCount }} workout{{ $this->weeklyWorkoutCount === 1 ? '' : 's' }} this week
                    @if ($this->weeklyCaloriesBurned > 0)
                        &middot; {{ number_format($this->weeklyCaloriesBurned) }} cal burned
                    @endif
                </div>
            @endif
        </a>

        {{-- Macro Calculator --}}
        <a href="{{ route('budget.macros') }}" wire:navigate
            class="group flex flex-col rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:hover:border-zinc-500">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                        <flux:icon.chart-pie class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <flux:heading>Macro Calculator</flux:heading>
                </div>
                <flux:icon.chevron-right class="size-4 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
            </div>

            @if (! $this->profile)
                <flux:text class="text-zinc-400">Complete your calorie setup to see your macros.</flux:text>
            @else
                @php
                    $hasTodayMacros = $this->todaysEntry
                        && ($this->todaysEntry->carbs_grams !== null
                            || $this->todaysEntry->protein_grams !== null
                            || $this->todaysEntry->fat_grams !== null);
                @endphp
                <dl class="space-y-2">
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Carbs</flux:text>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            @if ($hasTodayMacros)
                                {{ $this->todaysEntry->carbs_grams ?? '—' }}g
                                <span class="font-normal text-zinc-400">/ {{ $this->computedCarbGrams }}g</span>
                            @else
                                {{ $this->profile->carb_pct }}%
                                <span class="font-normal text-zinc-400">→</span>
                                {{ $this->computedCarbGrams }}g
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Protein</flux:text>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            @if ($hasTodayMacros)
                                {{ $this->todaysEntry->protein_grams ?? '—' }}g
                                <span class="font-normal text-zinc-400">/ {{ $this->computedProteinGrams }}g</span>
                            @else
                                {{ $this->profile->protein_pct }}%
                                <span class="font-normal text-zinc-400">→</span>
                                {{ $this->computedProteinGrams }}g
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Fat</flux:text>
                        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                            @if ($hasTodayMacros)
                                {{ $this->todaysEntry->fat_grams ?? '—' }}g
                                <span class="font-normal text-zinc-400">/ {{ $this->computedFatGrams }}g</span>
                            @else
                                {{ $this->profile->fat_pct }}%
                                <span class="font-normal text-zinc-400">→</span>
                                {{ $this->computedFatGrams }}g
                            @endif
                        </span>
                    </div>
                </dl>
            @endif
        </a>

    </div>
</div>
