<section class="w-full max-w-2xl">
    <div class="mb-8">
        <flux:heading size="xl">Welcome — Let's Get Started</flux:heading>
        <flux:text class="mt-1">Take a few minutes to fill out this intake form so we can understand your goals and build the right plan for you.</flux:text>
    </div>

    <form wire:submit="submit" class="space-y-10">

        {{-- GOAL --}}
        <div>
            <flux:heading size="lg" class="mb-4">Your Goal</flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>What's your main goal?</flux:label>
                    <flux:select wire:model="main_goal">
                        <flux:select.option value="">— Select a goal —</flux:select.option>
                        @foreach ($this->mainGoalOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="main_goal" />
                </flux:field>

                <flux:field>
                    <flux:label>Why now? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:textarea wire:model="why_now" placeholder="What's motivating you to make a change at this point in your life?" rows="3" />
                    <flux:error name="why_now" />
                </flux:field>
            </div>
        </div>

        <flux:separator />

        {{-- CURRENT STATE --}}
        <div>
            <flux:heading size="lg" class="mb-1">Current State</flux:heading>
            <flux:text class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">Weight and height are optional but help us build a more accurate plan.</flux:text>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Weight (lbs) <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                        <flux:input wire:model="current_weight_lbs" type="number" min="50" max="1500" placeholder="e.g. 175" />
                        <flux:error name="current_weight_lbs" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Height (ft) <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                        <flux:input wire:model="current_height_feet" type="number" min="1" max="9" placeholder="e.g. 5" />
                        <flux:error name="current_height_feet" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Height (in)</flux:label>
                        <flux:input wire:model="current_height_inches" type="number" min="0" max="11" placeholder="e.g. 10" />
                        <flux:error name="current_height_inches" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Current activity level (outside the gym)</flux:label>
                    <flux:select wire:model="activity_level">
                        @foreach ($this->activityLevelOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="activity_level" />
                </flux:field>

                <flux:field>
                    <flux:label>Workout experience</flux:label>
                    <flux:select wire:model="workout_experience">
                        @foreach ($this->workoutExperienceOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="workout_experience" />
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
                    <flux:select wire:model="work_schedule">
                        @foreach ($this->workScheduleOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="work_schedule" />
                </flux:field>

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
                    <flux:textarea wire:model="typical_day_of_eating" placeholder="e.g. Coffee and eggs in the morning, sandwich at lunch, pasta for dinner, snack on chips..." rows="3" />
                    <flux:error name="typical_day_of_eating" />
                </flux:field>

                <flux:field>
                    <flux:label>Any dietary restrictions or allergies? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
                    <flux:textarea wire:model="dietary_restrictions" placeholder="e.g. lactose intolerant, vegetarian, gluten-free..." rows="2" />
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
                    <flux:label>How many days per week can you realistically work out?</flux:label>
                    <flux:select wire:model="workout_days_per_week">
                        @foreach ($this->workoutDaysOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="workout_days_per_week" />
                </flux:field>

                <flux:field>
                    <flux:label>Are you open to tracking calories?</flux:label>
                    <flux:select wire:model="open_to_tracking">
                        @foreach ($this->openToTrackingOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="open_to_tracking" />
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
