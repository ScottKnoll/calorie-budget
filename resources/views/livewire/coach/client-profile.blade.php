@php
    $latestPlanForView = $client->clientPlans()->first();
    $profile = $client->calorieProfile;
    $intake = $client->intakeResponse;
    $checkIns = $client->checkIns;
@endphp

<div class="w-full max-w-4xl">
    {{-- Back link --}}
    <div class="mb-6">
        <flux:button :href="route('coach.dashboard')" wire:navigate variant="ghost" size="sm" icon="arrow-left">
            All clients
        </flux:button>
    </div>

    {{-- Client header --}}
    <div class="mb-8 flex flex-wrap items-center gap-4">
        <flux:avatar :name="$client->name" size="lg" />
        <div class="flex-1">
            <flux:heading size="xl">{{ $client->name }}</flux:heading>
            <flux:text class="text-zinc-500">
                {{ $client->email }}
                @if ($client->intake_completed_at)
                    &middot; Intake submitted {{ $client->intake_completed_at->format('M j, Y') }}
                @endif
            </flux:text>
        </div>
        <div class="flex items-center gap-2">
            @if ($client->intake_completed_at)
                <flux:badge color="green" size="sm">Intake complete</flux:badge>
            @else
                <flux:badge color="yellow" size="sm">Intake pending</flux:badge>
            @endif
            @if (! $latestPlanForView)
                <flux:button
                    :href="route('coach.clients.plans.create', $client)"
                    wire:navigate
                    icon="plus"
                    size="sm"
                    variant="primary"
                >
                    Create plan
                </flux:button>
            @endif
        </div>
    </div>

    {{-- CALORIE PROFILE --}}
    @if ($profile)
        <div class="mb-8">
            <flux:heading size="lg" class="mb-4">Calorie Profile</flux:heading>
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="grid grid-cols-2 gap-x-4 gap-y-5 px-6 py-5 sm:grid-cols-4">
                    <div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">TDEE</flux:text>
                        <p class="mt-0.5 text-xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ number_format($profile->tdee) }}</p>
                        <flux:text class="text-xs text-zinc-400">cal / day</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Goal</flux:text>
                        <p class="mt-0.5 text-xl font-bold text-zinc-900 dark:text-white">{{ $profile->goal->label() }}</p>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Starting Weight</flux:text>
                        <p class="mt-0.5 text-xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ $profile->weight_lbs }} <span class="text-sm font-normal text-zinc-400">lbs</span></p>
                    </div>
                    @if ($profile->goal_weight_lbs)
                        <div>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Goal Weight</flux:text>
                            <p class="mt-0.5 text-xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ $profile->goal_weight_lbs }} <span class="text-sm font-normal text-zinc-400">lbs</span></p>
                        </div>
                    @endif
                    @if ($this->latestWeightEntry)
                        <div>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Latest Weight</flux:text>
                            <p class="mt-0.5 text-xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ number_format($this->latestWeightEntry->weight_lbs, 1) }} <span class="text-sm font-normal text-zinc-400">lbs</span></p>
                            <flux:text class="text-xs text-zinc-400">{{ $this->latestWeightEntry->date->format('M j, Y') }}</flux:text>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- PRESCRIBED TARGETS --}}
    @if ($profile)
        <div class="mb-8">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">Prescribed Targets</flux:heading>
                @if (! $editingCalorieProfile)
                    <flux:button wire:click="startEditingCalorieProfile" variant="ghost" size="sm" icon="pencil">
                        Edit targets
                    </flux:button>
                @endif
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">

                    @if ($editingCalorieProfile)
                        {{-- Inline edit form --}}
                        <div class="px-6 py-5">
                            <div class="space-y-4">
                                <flux:field>
                                    <flux:label>Daily Calorie Target</flux:label>
                                    <flux:input wire:model.live="editCalorieTarget" type="number" min="500" max="9999" />
                                    <flux:error name="editCalorieTarget" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>Macro Preset <flux:badge size="sm" color="zinc">optional</flux:badge></flux:label>
                                    <flux:select wire:model.live="editMacroPreset">
                                        <flux:select.option value="">— None —</flux:select.option>
                                        @foreach ($this->macroPresetOptions() as $value => $label)
                                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="editMacroPreset" />
                                </flux:field>

                                <div class="grid grid-cols-3 gap-3">
                                    <flux:field>
                                        <div class="flex items-baseline justify-between">
                                            <flux:label>Carbs %</flux:label>
                                            @if ($this->editCarbGrams > 0)
                                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $this->editCarbGrams }}g</span>
                                            @endif
                                        </div>
                                        <flux:input wire:model.live="editCarbPct" type="number" min="0" max="100" />
                                    </flux:field>
                                    <flux:field>
                                        <div class="flex items-baseline justify-between">
                                            <flux:label>Protein %</flux:label>
                                            @if ($this->editProteinGrams > 0)
                                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $this->editProteinGrams }}g</span>
                                            @endif
                                        </div>
                                        <flux:input wire:model.live="editProteinPct" type="number" min="0" max="100" />
                                    </flux:field>
                                    <flux:field>
                                        <div class="flex items-baseline justify-between">
                                            <flux:label>Fat %</flux:label>
                                            @if ($this->editFatGrams > 0)
                                                <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $this->editFatGrams }}g</span>
                                            @endif
                                        </div>
                                        <flux:input wire:model.live="editFatPct" type="number" min="0" max="100" />
                                    </flux:field>
                                </div>

                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                    <div class="flex items-center justify-between">
                                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Total</flux:text>
                                        <span class="text-base font-bold {{ $this->editMacroTotal === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                                            {{ $this->editMacroTotal }}%
                                        </span>
                                    </div>
                                    @if ($this->editMacroTotal !== 100)
                                        <flux:text size="sm" class="mt-1 text-red-500">
                                            Must add up to 100%. Currently {{ $this->editMacroTotal > 100 ? 'over' : 'under' }} by {{ abs(100 - $this->editMacroTotal) }}%.
                                        </flux:text>
                                    @endif
                                </div>

                                <flux:error name="editCarbPct" />
                            </div>

                            <div class="mt-4 flex items-center justify-end gap-3">
                                <flux:button wire:click="cancelEditingCalorieProfile" variant="ghost" size="sm">Cancel</flux:button>
                                <flux:button wire:click="saveCalorieProfile" variant="primary" size="sm" wire:loading.attr="disabled">Save targets</flux:button>
                            </div>
                        </div>
                    @else
                        {{-- Daily calorie target --}}
                        <div class="grid grid-cols-2 gap-4 px-6 py-4 sm:grid-cols-4">
                            <div>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Daily Target</flux:text>
                                <p class="mt-0.5 text-xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ number_format($profile->daily_calorie_target) }}</p>
                                <flux:text class="text-xs text-zinc-400">cal / day</flux:text>
                            </div>
                        </div>

                        {{-- Macros --}}
                        @if ($profile->carb_pct && $profile->protein_pct && $profile->fat_pct)
                            @php
                                $proteinG = round(($profile->protein_pct / 100) * $profile->daily_calorie_target / 4);
                                $carbG    = round(($profile->carb_pct    / 100) * $profile->daily_calorie_target / 4);
                                $fatG     = round(($profile->fat_pct     / 100) * $profile->daily_calorie_target / 9);
                            @endphp
                            <div class="px-6 py-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Macros</p>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Carbs</flux:text>
                                        <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $carbG }}g <span class="font-normal text-zinc-400">({{ $profile->carb_pct }}%)</span></p>
                                    </div>
                                    <div>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Protein</flux:text>
                                        <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $proteinG }}g <span class="font-normal text-zinc-400">({{ $profile->protein_pct }}%)</span></p>
                                    </div>
                                    <div>
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Fat</flux:text>
                                        <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $fatG }}g <span class="font-normal text-zinc-400">({{ $profile->fat_pct }}%)</span></p>
                                    </div>
                                </div>
                                @if ($profile->macro_preset)
                                    <flux:text class="mt-2 text-xs text-zinc-400">Preset: {{ $profile->macro_preset->label() }}</flux:text>
                                @endif
                            </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    @endif

    {{-- TABBED CONTENT --}}
    <div
        x-data="{ tab: 'check-ins' }"
        x-cloak
    >
        {{-- Tab navigation --}}
        <div class="mb-6 border-b border-zinc-200 dark:border-zinc-700">
            <nav class="-mb-px flex gap-1">
                <button
                    @click="tab = 'check-ins'"
                    :class="tab === 'check-ins'
                        ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                        : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="flex items-center gap-2 px-3 py-3 text-sm font-medium transition-colors"
                >
                    Check-ins
                    @if ($checkIns->isNotEmpty())
                        <span
                            :class="tab === 'check-ins' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300'"
                            class="rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums transition-colors"
                        >{{ $checkIns->count() }}</span>
                    @endif
                </button>
                <button
                    @click="tab = 'plan'"
                    :class="tab === 'plan'
                        ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                        : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="px-3 py-3 text-sm font-medium transition-colors"
                >
                    Plan
                </button>
                <button
                    @click="tab = 'intake'"
                    :class="tab === 'intake'
                        ? 'border-b-2 border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                        : 'border-b-2 border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="px-3 py-3 text-sm font-medium transition-colors"
                >
                    Intake
                </button>
            </nav>
        </div>

        {{-- CHECK-INS tab --}}
        <div x-show="tab === 'check-ins'">
            @php
                $nextCheckInDate = $client->next_check_in_at;
                $isDue = $nextCheckInDate && $nextCheckInDate->lte(now());
            @endphp

            <div class="mb-4 overflow-hidden rounded-xl border {{ $isDue ? 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/20' : 'border-dashed border-zinc-300 dark:border-zinc-600' }}">
                @if ($editingNextCheckIn)
                    <div class="p-4">
                        <p class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">Set next check-in date</p>
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <flux:input
                                    wire:model="nextCheckInInput"
                                    type="datetime-local"
                                    :min="now()->format('Y-m-d\TH:i')"
                                />
                                <flux:error name="nextCheckInInput" class="mt-1" />
                            </div>
                            <flux:button wire:click="saveNextCheckInDate" variant="primary" size="sm">Save</flux:button>
                            <flux:button wire:click="cancelEditingNextCheckIn" variant="ghost" size="sm">Cancel</flux:button>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full {{ $isDue ? 'bg-amber-100 dark:bg-amber-900/40' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                                <flux:icon.calendar-days class="h-4 w-4 {{ $isDue ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-400 dark:text-zinc-500' }}" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-white">Next Check-In</span>
                                    @if ($nextCheckInDate)
                                        @if ($isDue)
                                            <flux:badge color="yellow" size="sm">Due</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm">Upcoming</flux:badge>
                                        @endif
                                    @endif
                                </div>
                                <flux:text class="mt-0.5 text-xs">
                                    @if ($nextCheckInDate)
                                        @if ($isDue)
                                            Was due {{ $nextCheckInDate->format('F j \a\t g:i A') }} &middot; {{ $nextCheckInDate->diffForHumans() }}
                                        @else
                                            {{ $nextCheckInDate->format('F j, Y \a\t g:i A') }} &middot; {{ $nextCheckInDate->diffForHumans() }}
                                        @endif
                                    @else
                                        Not scheduled yet
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                        <flux:button wire:click="startEditingNextCheckIn" variant="ghost" size="sm" icon="{{ $nextCheckInDate ? 'pencil' : 'plus' }}">
                            {{ $nextCheckInDate ? 'Edit' : 'Add date' }}
                        </flux:button>
                    </div>
                @endif
            </div>

            @if ($checkIns->isEmpty())
                <div class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" class="mb-1">No check-ins yet</flux:heading>
                    <flux:text class="text-zinc-400">This client hasn't submitted a weekly check-in.</flux:text>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($checkIns as $checkIn)
                        <div wire:key="check-in-{{ $checkIn->id }}" class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">

                            {{-- Client submission --}}
                            <div class="p-5">
                                <div class="mb-4 flex items-center justify-between">
                                    <flux:heading size="sm">{{ $checkIn->created_at->format('F j, Y') }}</flux:heading>
                                    <flux:badge color="zinc">{{ number_format($checkIn->weight, 1) }} lbs</flux:badge>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">How did the week feel overall?</p>
                                        <flux:text class="mt-1">{{ $checkIn->week_feeling }}</flux:text>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">What went well?</p>
                                        <flux:text class="mt-1">{{ $checkIn->went_well }}</flux:text>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">What felt hardest?</p>
                                        <flux:text class="mt-1">{{ $checkIn->felt_hardest }}</flux:text>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">Hunger, energy & sleep</p>
                                        <flux:text class="mt-1">{{ $checkIn->hunger_energy_sleep }}</flux:text>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">Activity consistency</p>
                                        <flux:text class="mt-1">{{ $checkIn->activity_consistency }}</flux:text>
                                    </div>
                                    @if ($checkIn->need_help)
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">Needs help with</p>
                                            <flux:text class="mt-1">{{ $checkIn->need_help }}</flux:text>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Coach notes section --}}
                            <div class="border-t border-zinc-100 dark:border-zinc-800">
                                @if ($editingCheckInId === $checkIn->id)

                                    {{-- Inline editing form --}}
                                    <div class="p-5">
                                        <div class="mb-4 flex items-center justify-between">
                                            <flux:heading size="sm" class="text-zinc-700 dark:text-zinc-300">Coach Notes</flux:heading>
                                            @if ($latestPlanForView)
                                                <flux:button
                                                    :href="route('coach.clients.plans.edit', [$client, $latestPlanForView])"
                                                    wire:navigate
                                                    variant="ghost"
                                                    size="sm"
                                                    icon="document-text"
                                                >
                                                    View plan
                                                </flux:button>
                                            @endif
                                        </div>

                                        <div class="space-y-4">
                                            <flux:field>
                                                <flux:label>Workout</flux:label>
                                                <flux:textarea wire:model="coachWorkout" rows="3" placeholder="Notes on training, activity, or workout adjustments..." maxlength="3000" />
                                                <flux:error name="coachWorkout" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Nutrition</flux:label>
                                                <flux:textarea wire:model="coachNutrition" rows="3" placeholder="Notes on calories, macros, meal timing, or food quality..." maxlength="3000" />
                                                <flux:error name="coachNutrition" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Habits</flux:label>
                                                <flux:textarea wire:model="coachHabits" rows="3" placeholder="Notes on sleep, stress, hydration, or daily habits..." maxlength="3000" />
                                                <flux:error name="coachHabits" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>General</flux:label>
                                                <flux:textarea wire:model="coachGeneral" rows="3" placeholder="Any general feedback or observations..." maxlength="3000" />
                                                <flux:error name="coachGeneral" />
                                            </flux:field>

                                            <flux:field>
                                                <flux:label>Focus for Next Week</flux:label>
                                                <flux:textarea wire:model="coachFocusNextWeek" rows="3" placeholder="Key priorities and intentions for the coming week..." maxlength="3000" />
                                                <flux:error name="coachFocusNextWeek" />
                                            </flux:field>
                                        </div>

                                        <div class="mt-4 flex items-center justify-end gap-3">
                                            <flux:button wire:click="cancelEditingNotes" variant="ghost" size="sm">Cancel</flux:button>
                                            <flux:button wire:click="saveNotes" variant="primary" size="sm" wire:loading.attr="disabled">Save notes</flux:button>
                                        </div>
                                    </div>

                                @else

                                    {{-- Coach notes display / add button --}}
                                    @if ($checkIn->hasCoachNotes())
                                        <div class="p-5">
                                            <div class="mb-3 flex items-center justify-between">
                                                <flux:text class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Coach Notes</flux:text>
                                                <flux:button wire:click="startEditingNotes({{ $checkIn->id }})" variant="ghost" size="sm" icon="pencil">Edit notes</flux:button>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach ([
                                                    ['value' => $checkIn->coach_workout, 'label' => 'Workout'],
                                                    ['value' => $checkIn->coach_nutrition, 'label' => 'Nutrition'],
                                                    ['value' => $checkIn->coach_habits, 'label' => 'Habits'],
                                                    ['value' => $checkIn->coach_general, 'label' => 'General'],
                                                    ['value' => $checkIn->coach_focus_next_week, 'label' => 'Focus for Next Week'],
                                                ] as $note)
                                                    @if ($note['value'])
                                                        <div>
                                                            <p class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200">{{ $note['label'] }}</p>
                                                            <flux:text class="mt-1 whitespace-pre-line">{{ $note['value'] }}</flux:text>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between px-5 py-3">
                                            <flux:text class="text-sm text-zinc-400 dark:text-zinc-500">No coach notes yet</flux:text>
                                            <flux:button wire:click="startEditingNotes({{ $checkIn->id }})" variant="ghost" size="sm" icon="plus">Add notes</flux:button>
                                        </div>
                                    @endif

                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- PLAN tab --}}
        <div x-show="tab === 'plan'">
            @if ($latestPlanForView)
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="lg">{{ $latestPlanForView->title }}</flux:heading>
                        <flux:button
                            :href="route('coach.clients.plans.edit', [$client, $latestPlanForView])"
                            wire:navigate
                            variant="ghost"
                            size="sm"
                            icon="pencil-square"
                        >
                            Edit plan
                        </flux:button>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="trix-content px-6 py-6 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                            {!! $latestPlanForView->body !!}
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" class="mb-1">No plan yet</flux:heading>
                    <flux:text class="mb-4 text-zinc-400">Create a coaching plan for this client.</flux:text>
                    <flux:button
                        :href="route('coach.clients.plans.create', $client)"
                        wire:navigate
                        icon="plus"
                        variant="primary"
                        size="sm"
                    >
                        Create plan
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- INTAKE tab --}}
        <div x-show="tab === 'intake'">
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
    </div>
</div>
