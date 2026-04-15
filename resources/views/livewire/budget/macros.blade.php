<section class="w-full max-w-2xl">
    <div class="mb-6">
        <flux:heading size="xl">Macro Calculator</flux:heading>
        <flux:text class="mt-1">Set your daily carbohydrate, protein, and fat targets based on your calorie goal.</flux:text>
    </div>

    @if (session('status') === 'saved')
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            <flux:callout.heading>Macro settings saved.</flux:callout.heading>
        </flux:callout>
    @endif

    @if ($this->dailyCalorieTarget === 0)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            <flux:callout.heading>No calorie target set.</flux:callout.heading>
            <flux:callout.text>
                Gram values will appear once you save a daily calorie target on the
                <a href="{{ route('budget.setup') }}" wire:navigate class="font-medium text-zinc-800 transition-colors hover:text-zinc-500 dark:text-zinc-200 dark:hover:text-zinc-400">Calorie Setup</a> page.
            </flux:callout.text>
        </flux:callout>
    @else
        <flux:card class="mb-6 bg-blue-50 border-blue-200 dark:bg-blue-950/40 dark:border-blue-800">
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Based on your daily target</flux:text>
            <p class="mt-0.5 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ number_format($this->dailyCalorieTarget) }} <span class="text-base font-normal text-zinc-400">cal / day</span>
            </p>
        </flux:card>
    @endif

    <form wire:submit="save" class="space-y-4">

        {{-- Preset Selector --}}
        <flux:card class="p-0 overflow-hidden">
            <div class="border-b border-zinc-200 bg-zinc-100 px-6 py-5 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">Preset</flux:heading>
            </div>
            <div class="p-6">
                <flux:field>
                    <flux:label>Macro Preset</flux:label>
                    <flux:description>Choose a preset to auto-fill the sliders below, or pick Custom to set your own.</flux:description>
                    <flux:select wire:model.live="macro_preset">
                        <flux:select.option value="">— Select a preset —</flux:select.option>
                        @foreach ($this->presetOptions() as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        </flux:card>

        {{-- Macro Sliders --}}
        <flux:card class="p-0 overflow-hidden">
            <div class="border-b border-zinc-200 bg-zinc-100 px-6 py-5 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">Breakdown</flux:heading>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-700">

                {{-- Carbohydrates --}}
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text class="font-semibold text-zinc-800 dark:text-zinc-100">Carbohydrates</flux:text>
                        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="text-base font-bold text-zinc-900 dark:text-white">{{ $carb_pct }}%</span>
                            <span>→</span>
                            @if ($this->dailyCalorieTarget > 0)
                                <span class="text-base font-bold text-zinc-900 dark:text-white">{{ $this->computedCarbGrams }}g</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>
                    </div>
                    <input
                        type="range"
                        wire:model.live="carb_pct"
                        min="0"
                        max="100"
                        step="1"
                        class="w-full h-2 rounded-lg appearance-none cursor-pointer accent-blue-500 bg-zinc-200 dark:bg-zinc-700"
                    />
                </div>

                {{-- Protein --}}
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text class="font-semibold text-zinc-800 dark:text-zinc-100">Protein</flux:text>
                        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="text-base font-bold text-zinc-900 dark:text-white">{{ $protein_pct }}%</span>
                            <span>→</span>
                            @if ($this->dailyCalorieTarget > 0)
                                <span class="text-base font-bold text-zinc-900 dark:text-white">{{ $this->computedProteinGrams }}g</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>
                    </div>
                    <input
                        type="range"
                        wire:model.live="protein_pct"
                        min="0"
                        max="100"
                        step="1"
                        class="w-full h-2 rounded-lg appearance-none cursor-pointer accent-blue-500 bg-zinc-200 dark:bg-zinc-700"
                    />
                </div>

                {{-- Fat --}}
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text class="font-semibold text-zinc-800 dark:text-zinc-100">Fat</flux:text>
                        <div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                            <span class="text-base font-bold text-zinc-900 dark:text-white">{{ $fat_pct }}%</span>
                            <span>→</span>
                            @if ($this->dailyCalorieTarget > 0)
                                <span class="text-base font-bold text-zinc-900 dark:text-white">{{ $this->computedFatGrams }}g</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>
                    </div>
                    <input
                        type="range"
                        wire:model.live="fat_pct"
                        min="0"
                        max="100"
                        step="1"
                        class="w-full h-2 rounded-lg appearance-none cursor-pointer accent-blue-500 bg-zinc-200 dark:bg-zinc-700"
                    />
                </div>

            </div>

            {{-- Total indicator --}}
            <div class="border-t border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">Total</flux:text>
                    <span class="text-base font-bold {{ $this->macroTotal === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                        {{ $this->macroTotal }}%
                    </span>
                </div>

                @if ($this->macroTotal !== 100)
                    <flux:text size="sm" class="mt-1 text-red-500">
                        Percentages must add up to 100%. Currently {{ $this->macroTotal > 100 ? 'over' : 'under' }} by {{ abs(100 - $this->macroTotal) }}%.
                    </flux:text>
                @endif

                @error('macro_total')
                    <flux:text size="sm" class="mt-1 text-red-500">{{ $message }}</flux:text>
                @enderror
            </div>
        </flux:card>

        <div class="flex justify-end pb-2">
            <flux:button
                type="submit"
                variant="primary"
                :disabled="$this->macroTotal !== 100"
            >
                Save Macros
            </flux:button>
        </div>
    </form>
</section>
