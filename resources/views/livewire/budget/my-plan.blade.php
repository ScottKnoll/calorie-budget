<div class="w-full max-w-3xl" x-data="{ tab: 'plan' }" x-cloak>

    {{-- Tab navigation --}}
    <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex gap-1">
            <button
                @click="tab = 'plan'"
                :class="tab === 'plan'
                    ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                    : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                class="px-3 py-3 text-sm font-medium transition-colors"
            >
                My Plan
            </button>
            <button
                @click="tab = 'intake'"
                :class="tab === 'intake'
                    ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                    : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                class="px-3 py-3 text-sm font-medium transition-colors"
            >
                My Intake
            </button>
        </nav>
    </div>

    {{-- MY PLAN tab --}}
    <div x-show="tab === 'plan'">
        @if (! $this->plan)
            <div class="rounded-xl border border-zinc-200 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <flux:icon.document-text class="size-6 text-zinc-400" />
                </div>
                <flux:heading size="lg" class="mb-1">No plan yet</flux:heading>
                <flux:text class="text-zinc-400">Your coach hasn't created a plan for you yet. Check back soon!</flux:text>
            </div>
        @else
            <div class="mb-8">
                <flux:heading size="xl" class="mb-1">{{ $this->plan->title }}</flux:heading>
                <flux:text class="text-zinc-500">Created {{ $this->plan->created_at->format('M j, Y') }}</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="trix-content px-6 py-6 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                    {!! $this->plan->body !!}
                </div>
            </div>
        @endif
    </div>

    {{-- MY INTAKE tab --}}
    <div x-show="tab === 'intake'">
        @if ($this->intake)
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    {{-- GOAL --}}
                    <div class="px-6 py-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Goal</p>
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Main goal</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->mainGoalOptions()[$this->intake->main_goal] ?? $this->intake->main_goal }}
                                    @if ($this->intake->main_goal === 'other' && $this->intake->main_goal_other)
                                        <span class="font-normal text-zinc-500"> — {{ $this->intake->main_goal_other }}</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($this->intake->why_now)
                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Why now</dt>
                                    <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $this->intake->why_now }}</dd>
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
                                    {{ $this->workScheduleOptions()[$this->intake->work_schedule] ?? $this->intake->work_schedule }}
                                    @if ($this->intake->work_schedule === 'other' && $this->intake->work_schedule_other)
                                        <span class="font-normal text-zinc-500"> — {{ $this->intake->work_schedule_other }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Step tracking</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->openToTrackingStepsOptions()[$this->intake->open_to_tracking_steps] ?? $this->intake->open_to_tracking_steps }}
                                </dd>
                            </div>
                            @if ($this->intake->daily_steps)
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Daily steps</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $this->dailyStepsOptions()[$this->intake->daily_steps] ?? $this->intake->daily_steps }}
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Sleep</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->sleepHoursOptions()[$this->intake->sleep_hours] ?? $this->intake->sleep_hours }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Stress level</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->stressLevelOptions()[$this->intake->stress_level] ?? $this->intake->stress_level }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- FITNESS --}}
                    <div class="px-6 py-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Fitness</p>
                        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @if ($this->intake->fitness_access)
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Fitness access</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ collect($this->intake->fitness_access)->map(fn ($v) => $this->fitnessAccessOptions()[$v] ?? $v)->implode(', ') }}
                                    </dd>
                                </div>
                            @endif
                            @if ($this->intake->workout_preferences)
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Workout preferences</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ collect($this->intake->workout_preferences)->map(fn ($v) => $this->workoutPreferenceOptions()[$v] ?? $v)->implode(', ') }}
                                        @if (in_array('other', $this->intake->workout_preferences) && $this->intake->workout_preferences_other)
                                            <span class="font-normal text-zinc-500"> — {{ $this->intake->workout_preferences_other }}</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Workout days / week</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $this->workoutDaysOptions()[$this->intake->workout_days_per_week] ?? $this->intake->workout_days_per_week }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500 dark:text-zinc-400">Injuries</dt>
                                <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                    @if ($this->intake->has_injuries === 'yes')
                                        Yes
                                        @if ($this->intake->injury_description)
                                            <span class="font-normal text-zinc-500"> — {{ $this->intake->injury_description }}</span>
                                        @endif
                                    @else
                                        No
                                    @endif
                                </dd>
                            </div>
                            @if ($this->intake->current_activity)
                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Current activity</dt>
                                    <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $this->intake->current_activity }}</dd>
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
                                    {{ $this->tracksCurrentlyOptions()[$this->intake->tracks_currently] ?? $this->intake->tracks_currently }}
                                </dd>
                            </div>
                            @if ($this->intake->meal_timing_pattern)
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Meal timing</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $this->mealTimingPatternOptions()[$this->intake->meal_timing_pattern] ?? $this->intake->meal_timing_pattern }}
                                        @if ($this->intake->meal_timing_pattern === 'other' && $this->intake->meal_timing_pattern_other)
                                            <span class="font-normal text-zinc-500"> — {{ $this->intake->meal_timing_pattern_other }}</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            @if ($this->intake->dietary_preference && $this->intake->dietary_preference !== 'none')
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Dietary preference</dt>
                                    <dd class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $this->dietaryPreferenceOptions()[$this->intake->dietary_preference] ?? $this->intake->dietary_preference }}
                                        @if ($this->intake->dietary_preference === 'other' && $this->intake->dietary_preference_other)
                                            <span class="font-normal text-zinc-500"> — {{ $this->intake->dietary_preference_other }}</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            @if ($this->intake->dietary_restrictions)
                                <div>
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Dietary restrictions</dt>
                                    <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $this->intake->dietary_restrictions }}</dd>
                                </div>
                            @endif
                            @if ($this->intake->typical_day_of_eating)
                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Typical day of eating</dt>
                                    <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $this->intake->typical_day_of_eating }}</dd>
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
                                    {{ $this->openToTrackingOptions()[$this->intake->open_to_tracking] ?? $this->intake->open_to_tracking }}
                                </dd>
                            </div>
                            @if ($this->intake->past_consistency_struggles)
                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-zinc-500 dark:text-zinc-400">Past consistency struggles</dt>
                                    <dd class="mt-0.5 text-sm text-zinc-700 dark:text-zinc-300">{{ $this->intake->past_consistency_struggles }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                </div>
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-1">No intake on file</flux:heading>
                <flux:text class="text-zinc-400">Your intake responses will appear here once submitted.</flux:text>
            </div>
        @endif
    </div>

</div>
