<section class="w-full max-w-2xl">
    <div class="mb-6">
        <flux:heading size="xl">Calorie Setup</flux:heading>
        <flux:text class="mt-1">Enter your measurements to calculate your TDEE and daily calorie target.</flux:text>
    </div>

    @if (session('status') === 'saved')
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            <flux:callout.heading>Settings saved.</flux:callout.heading>
        </flux:callout>
    @endif

    {{-- Live TDEE Preview --}}
    @if ($this->computedTdee > 0)
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950/40">
            <flux:heading size="sm" class="mb-4">Your Estimated Numbers</flux:heading>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Maintenance (TDEE)</flux:text>
                    <p class="mt-0.5 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($this->computedTdee) }}</p>
                    <flux:text size="sm" class="text-zinc-400">cal / day</flux:text>
                </div>
                <div>
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Daily Target</flux:text>
                    <p class="mt-0.5 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($this->computedDailyTarget) }}</p>
                    <flux:text size="sm" class="text-zinc-400">cal / day</flux:text>
                </div>
                @if ($goal === 'cut' || $goal === 'bulk')
                    <div>
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $goal === 'cut' ? 'Deficit' : 'Surplus' }}</flux:text>
                        <p class="mt-0.5 text-3xl font-bold tracking-tight {{ $goal === 'cut' ? 'text-red-500' : 'text-emerald-500' }}">
                            {{ $goal === 'cut' ? '-' : '+' }}{{ number_format(abs($this->computedTdee - $this->computedDailyTarget)) }}
                        </p>
                        <flux:text size="sm" class="text-zinc-400">cal / day</flux:text>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">

        {{-- Personal Info --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-100 bg-zinc-50/70 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <flux:heading size="sm">Personal Info</flux:heading>
            </div>
            <div class="space-y-4 px-5 py-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Gender</flux:label>
                        <flux:select wire:model.live="gender">
                            @foreach ($this->genderOptions() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="gender" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Age</flux:label>
                        <flux:input wire:model.live="age" type="number" min="1" max="120" placeholder="30" />
                        <flux:error name="age" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Height</flux:label>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <flux:input wire:model.live="height_feet" type="number" min="1" max="9" placeholder="5" suffix="ft" />
                            </div>
                            <div class="flex-1">
                                <flux:input wire:model.live="height_inches" type="number" min="0" max="11" placeholder="0" suffix="in" />
                            </div>
                        </div>
                        <flux:error name="height_feet" />
                        <flux:error name="height_inches" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Current Weight</flux:label>
                        <flux:input wire:model.live="weight_lbs" type="number" min="50" max="1500" placeholder="170" suffix="lbs" />
                        <flux:error name="weight_lbs" />
                    </flux:field>
                </div>
            </div>
        </div>

        {{-- Activity --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-100 bg-zinc-50/70 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <flux:heading size="sm">Activity</flux:heading>
            </div>
            <div class="space-y-4 px-5 py-5">
                <flux:field>
                    <flux:label>Activity Level</flux:label>
                    <flux:description>Your typical daily activity outside of intentional exercise.</flux:description>
                    <flux:select wire:model.live="activity_factor">
                        @foreach ($this->activityFactorOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="activity_factor" />
                </flux:field>

                <flux:field>
                    <flux:label>Exercise Intensity</flux:label>
                    <flux:description>The intensity of your planned workout sessions. Adds an average daily calorie bonus.</flux:description>
                    <flux:select wire:model.live="exercise_factor">
                        @foreach ($this->exerciseFactorOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="exercise_factor" />
                </flux:field>
            </div>
        </div>

        {{-- Goals & Timeline --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-100 bg-zinc-50/70 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <flux:heading size="sm">Goals & Timeline</flux:heading>
            </div>
            <div class="space-y-4 px-5 py-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Goal</flux:label>
                        <flux:select wire:model.live="goal">
                            @foreach ($this->goalOptions() as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="goal" />
                    </flux:field>

                    @if ($goal !== 'maintain')
                        <flux:field>
                            <flux:label>{{ $goal === 'cut' ? 'Deficit' : 'Surplus' }} Percentage</flux:label>
                            <flux:description>Default is 20%. Range: 5–50%.</flux:description>
                            <flux:input wire:model.live="calorie_deficit_pct" type="number" min="5" max="50" suffix="%" />
                            <flux:error name="calorie_deficit_pct" />
                        </flux:field>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Goal Weight <flux:badge size="sm" variant="outline" class="ml-1">optional</flux:badge></flux:label>
                        <flux:input wire:model="goal_weight_lbs" type="number" min="50" max="1500" placeholder="150" suffix="lbs" />
                        <flux:error name="goal_weight_lbs" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Start Date <flux:badge size="sm" variant="outline" class="ml-1">optional</flux:badge></flux:label>
                        <flux:input wire:model="start_date" type="date" />
                        <flux:error name="start_date" />
                    </flux:field>
                </div>
            </div>
        </div>

        {{-- Daily Calorie Target --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-100 bg-zinc-50/70 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                <flux:heading size="sm">Daily Calorie Target</flux:heading>
            </div>
            <div class="px-5 py-5">
                <flux:field>
                    <flux:label>Daily Calorie Target</flux:label>
                    <flux:description>
                        Auto-suggested based on your TDEE
                        @if ($goal === 'cut')
                            minus a {{ $calorie_deficit_pct }}% deficit.
                        @elseif ($goal === 'bulk')
                            plus a {{ $calorie_deficit_pct }}% surplus.
                        @else
                            (same as your TDEE).
                        @endif
                        You can adjust this freely.
                    </flux:description>
                    <flux:input wire:model="daily_calorie_target" type="number" min="500" max="9999" />
                    <flux:error name="daily_calorie_target" />
                </flux:field>
            </div>
        </div>

        <div class="flex justify-end pb-2">
            <flux:button type="submit" variant="primary">Save Settings</flux:button>
        </div>
    </form>
</section>
