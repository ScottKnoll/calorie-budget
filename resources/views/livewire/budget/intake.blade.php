<section class="w-full max-w-2xl">
    <div class="mb-8">
        <flux:heading size="xl">Welcome, {{ Str::of(auth()->user()->name)->before(' ') }} — Let's Get Started</flux:heading>
        <flux:text class="mt-1">Take a few minutes to fill out this intake form so we can understand your goals and build the right plan for you.</flux:text>
    </div>

    <form wire:submit="submit" class="space-y-10">

        {{-- GOAL --}}
        <div>
            <flux:heading size="lg" class="mb-4">Your Goal</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>What's your main goal?</flux:label>
                    <flux:select wire:model.live="main_goal">
                        <flux:select.option value="">— Select a goal —</flux:select.option>
                        @foreach ($this->mainGoalOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="main_goal" />
                </flux:field>

                @if ($main_goal === 'other')
                    <flux:field>
                        <flux:label>Please describe your goal</flux:label>
                        <flux:input wire:model="main_goal_other" type="text" placeholder="e.g. Improve my relationship with food..." maxlength="200" />
                        <flux:error name="main_goal_other" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Why now? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:textarea wire:model="why_now" placeholder="What's motivating you to make a change at this point in your life?" rows="3" />
                    <flux:error name="why_now" />
                </flux:field>
            </div>
        </div>

        <flux:separator />

        {{-- LIFESTYLE --}}
        <div>
            <flux:heading size="lg" class="mb-4">Lifestyle</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Work schedule</flux:label>
                    <flux:select wire:model.live="work_schedule">
                        @foreach ($this->workScheduleOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="work_schedule" />
                </flux:field>

                @if ($work_schedule === 'other')
                    <flux:field>
                        <flux:label>Please describe your work schedule</flux:label>
                        <flux:input wire:model="work_schedule_other" type="text" placeholder="e.g. Freelance, rotating shifts..." maxlength="200" />
                        <flux:error name="work_schedule_other" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Daily step count / general movement</flux:label>
                    <flux:select wire:model="daily_steps">
                        @foreach ($this->dailyStepsOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="daily_steps" />
                </flux:field>

                <flux:field>
                    <flux:label>Average sleep</flux:label>
                    <flux:select wire:model="sleep_hours">
                        @foreach ($this->sleepHoursOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="sleep_hours" />
                </flux:field>

                <flux:field>
                    <flux:label>Stress level</flux:label>
                    <flux:select wire:model="stress_level">
                        @foreach ($this->stressLevelOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="stress_level" />
                </flux:field>
            </div>
        </div>

        <flux:separator />

        {{-- WORKOUT / FITNESS --}}
        <div>
            <flux:heading size="lg" class="mb-4">Workout / Fitness</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Do you have access to any of the following?</flux:label>
                    <div class="mt-1 space-y-2">
                        @foreach ($this->fitnessAccessOptions() as $value => $label)
                            <flux:checkbox wire:model="fitness_access" value="{{ $value }}" label="{{ $label }}" />
                        @endforeach
                    </div>
                    <flux:error name="fitness_access" />
                </flux:field>

                <flux:field>
                    <flux:label>What does your current weekly activity look like? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:description>Doesn't have to be perfect — just a rough idea.</flux:description>
                    <flux:textarea wire:model="current_activity" placeholder="e.g. 2–3 workouts per week, walking most days (~6–8k steps), occasional activities like golf, yoga, or classes" rows="2" />
                    <flux:error name="current_activity" />
                </flux:field>

                <flux:field>
                    <flux:label>What types of workouts do you enjoy or are open to?</flux:label>
                    <div class="mt-1 space-y-2">
                        @foreach ($this->workoutPreferenceOptions() as $value => $label)
                            <flux:checkbox wire:model.live="workout_preferences" value="{{ $value }}" label="{{ $label }}" />
                        @endforeach
                    </div>
                    <flux:error name="workout_preferences" />
                </flux:field>

                @if (in_array('other', $workout_preferences))
                    <flux:field>
                        <flux:label>What else do you enjoy? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                        <flux:input wire:model="workout_preferences_other" type="text" placeholder="e.g. swimming, hiking, martial arts..." maxlength="200" />
                        <flux:error name="workout_preferences_other" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Do you have any injuries or limitations?</flux:label>
                    <flux:select wire:model.live="has_injuries">
                        @foreach ($this->hasInjuriesOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="has_injuries" />
                </flux:field>

                @if ($has_injuries === 'yes')
                    <flux:field>
                        <flux:label>Please describe any injuries or limitations</flux:label>
                        <flux:textarea wire:model="injury_description" placeholder="e.g. lower back pain, bad knees..." rows="2" maxlength="500" />
                        <flux:error name="injury_description" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>How many days per week can you realistically work out?</flux:label>
                    <flux:select wire:model="workout_days_per_week">
                        @foreach ($this->workoutDaysOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="workout_days_per_week" />
                </flux:field>
            </div>
        </div>

        <flux:separator />

        {{-- NUTRITION --}}
        <div>
            <flux:heading size="lg" class="mb-4">Nutrition</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Do you currently track your food?</flux:label>
                    <flux:select wire:model="tracks_currently">
                        @foreach ($this->tracksCurrentlyOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tracks_currently" />
                </flux:field>

                <flux:field>
                    <flux:label>Describe a typical day of eating <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:description>Doesn't have to be perfect — just a rough idea.</flux:description>
                    <flux:textarea wire:model="typical_day_of_eating" placeholder="e.g. Coffee and eggs in the morning, sandwich at lunch, pasta for dinner, snack on chips..." rows="3" />
                    <flux:error name="typical_day_of_eating" />
                </flux:field>

                <flux:field>
                    <flux:label>Dietary preference</flux:label>
                    <flux:select wire:model.live="dietary_preference">
                        @foreach ($this->dietaryPreferenceOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="dietary_preference" />
                </flux:field>

                @if ($dietary_preference === 'other')
                    <flux:field>
                        <flux:label>Please describe your dietary preference</flux:label>
                        <flux:input wire:model="dietary_preference_other" type="text" placeholder="e.g. Mostly plant-based but eat fish occasionally..." maxlength="200" />
                        <flux:error name="dietary_preference_other" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>Any dietary restrictions or allergies? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:textarea wire:model="dietary_restrictions" placeholder="e.g. Lactose intolerant, gluten-free..." rows="2" />
                    <flux:error name="dietary_restrictions" />
                </flux:field>
            </div>
        </div>

        <flux:separator />

        {{-- EXPECTATIONS --}}
        <div>
            <flux:heading size="lg" class="mb-4">Expectations</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Are you open to tracking calories?</flux:label>
                    <flux:select wire:model="open_to_tracking">
                        @foreach ($this->openToTrackingOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="open_to_tracking" />
                </flux:field>

                <flux:field>
                    <flux:label>What has made it hard to stay consistent in the past? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:textarea wire:model="past_consistency_struggles" placeholder="e.g. Busy schedule, travel, lack of motivation, not knowing what to eat..." rows="3" />
                    <flux:error name="past_consistency_struggles" />
                </flux:field>
            </div>
        </div>

        <div class="pt-2">
            <flux:button type="submit" variant="primary" class="w-full">
                Submit &amp; Continue to Setup
            </flux:button>
        </div>

    </form>
</section>
