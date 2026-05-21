<div class="w-full max-w-4xl">
    {{-- Back link --}}
    <div class="mb-6">
        <flux:button :href="route('coach.dashboard')" wire:navigate variant="ghost" size="sm" icon="arrow-left">
            All clients
        </flux:button>
    </div>

    {{-- Client header --}}
    <div class="mb-8 flex items-center gap-4">
        <flux:avatar :name="$client->name" size="lg" />
        <div>
            <flux:heading size="xl">{{ $client->name }}</flux:heading>
            <flux:text class="text-zinc-500">
                {{ $client->email }}
                @if ($client->intake_completed_at)
                    &middot; Intake submitted {{ $client->intake_completed_at->format('M j, Y') }}
                @endif
            </flux:text>
        </div>
        @if ($client->intake_completed_at)
            <flux:badge color="green" class="ml-auto shrink-0">Intake complete</flux:badge>
        @else
            <flux:badge color="yellow" class="ml-auto shrink-0">Intake pending</flux:badge>
        @endif
    </div>

    @php $intake = $client->intakeResponse; @endphp

    @if ($intake)
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">

                {{-- GOAL --}}
                <div class="px-6 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Goal</p>
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Main goal</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->mainGoalOptions()[$intake->main_goal] ?? $intake->main_goal }}
                                @if ($intake->main_goal === 'other' && $intake->main_goal_other)
                                    <span class="font-normal text-zinc-500"> — {{ $intake->main_goal_other }}</span>
                                @endif
                            </dd>
                        </div>
                        @if ($intake->why_now)
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Why now</dt>
                                <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $intake->why_now }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- LIFESTYLE --}}
                <div class="px-6 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Lifestyle</p>
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Work schedule</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->workScheduleOptions()[$intake->work_schedule] ?? $intake->work_schedule }}
                                @if ($intake->work_schedule === 'other' && $intake->work_schedule_other)
                                    <span class="font-normal text-zinc-500"> — {{ $intake->work_schedule_other }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Step tracking</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->openToTrackingStepsOptions()[$intake->open_to_tracking_steps] ?? $intake->open_to_tracking_steps }}
                            </dd>
                        </div>
                        @if ($intake->daily_steps)
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Daily steps</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->dailyStepsOptions()[$intake->daily_steps] ?? $intake->daily_steps }}
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Sleep</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->sleepHoursOptions()[$intake->sleep_hours] ?? $intake->sleep_hours }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Stress level</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->stressLevelOptions()[$intake->stress_level] ?? $intake->stress_level }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- FITNESS --}}
                <div class="px-6 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Fitness</p>
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @if ($intake->fitness_access)
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Fitness access</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ collect($intake->fitness_access)->map(fn ($v) => $this->fitnessAccessOptions()[$v] ?? $v)->implode(', ') }}
                                </dd>
                            </div>
                        @endif
                        @if ($intake->workout_preferences)
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Workout preferences</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ collect($intake->workout_preferences)->map(fn ($v) => $this->workoutPreferenceOptions()[$v] ?? $v)->implode(', ') }}
                                    @if (in_array('other', $intake->workout_preferences) && $intake->workout_preferences_other)
                                        <span class="font-normal text-zinc-500"> — {{ $intake->workout_preferences_other }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Workout days / week</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->workoutDaysOptions()[$intake->workout_days_per_week] ?? $intake->workout_days_per_week }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Injuries</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                @if ($intake->has_injuries === 'yes')
                                    Yes
                                    @if ($intake->injury_description)
                                        <span class="font-normal text-zinc-500"> — {{ $intake->injury_description }}</span>
                                    @endif
                                @else
                                    No
                                @endif
                            </dd>
                        </div>
                        @if ($intake->current_activity)
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Current activity</dt>
                                <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $intake->current_activity }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- NUTRITION --}}
                <div class="px-6 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Nutrition</p>
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Currently tracks</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->tracksCurrentlyOptions()[$intake->tracks_currently] ?? $intake->tracks_currently }}
                            </dd>
                        </div>
                        @if ($intake->meal_timing_pattern)
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Meal timing</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->mealTimingPatternOptions()[$intake->meal_timing_pattern] ?? $intake->meal_timing_pattern }}
                                    @if ($intake->meal_timing_pattern === 'other' && $intake->meal_timing_pattern_other)
                                        <span class="font-normal text-zinc-500"> — {{ $intake->meal_timing_pattern_other }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($intake->dietary_preference && $intake->dietary_preference !== 'none')
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Dietary preference</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->dietaryPreferenceOptions()[$intake->dietary_preference] ?? $intake->dietary_preference }}
                                    @if ($intake->dietary_preference === 'other' && $intake->dietary_preference_other)
                                        <span class="font-normal text-zinc-500"> — {{ $intake->dietary_preference_other }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($intake->dietary_restrictions)
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Dietary restrictions</dt>
                                <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $intake->dietary_restrictions }}</dd>
                            </div>
                        @endif
                        @if ($intake->typical_day_of_eating)
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Typical day of eating</dt>
                                <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $intake->typical_day_of_eating }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- EXPECTATIONS --}}
                <div class="px-6 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Expectations</p>
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-zinc-500 dark:text-zinc-400">Open to tracking</dt>
                            <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $this->openToTrackingOptions()[$intake->open_to_tracking] ?? $intake->open_to_tracking }}
                            </dd>
                        </div>
                        @if ($intake->past_consistency_struggles)
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Past consistency struggles</dt>
                                <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $intake->past_consistency_struggles }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

            </div>
        </div>
    @else
        <div class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-1">No intake response yet</flux:heading>
            <flux:text class="text-zinc-400">This client hasn't completed the intake form.</flux:text>
        </div>
    @endif
</div>
