<section class="w-full max-w-lg">
    <flux:heading size="xl" class="mb-1">Calorie Setup</flux:heading>
    <flux:text class="mb-6">Set your maintenance calories, goal, and daily target.</flux:text>

    @if (session('status') === 'saved')
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            <flux:callout.heading>Settings saved.</flux:callout.heading>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Maintenance Calories (TDEE)</flux:label>
            <flux:description>
                The calories your body burns each day at your current activity level. Not sure?
                <flux:link href="https://tdeecalculator.net" target="_blank">Calculate it here.</flux:link>
            </flux:description>
            <flux:input wire:model.blur="tdee" type="number" min="500" max="9999" required />
            <flux:error name="tdee" />
        </flux:field>

        <flux:field>
            <flux:label>Goal</flux:label>
            <flux:description>Your daily target will be suggested automatically based on this.</flux:description>
            <flux:select wire:model.live="goal">
                @foreach ($this->goalOptions() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="goal" />
        </flux:field>

        <flux:field>
            <flux:label>Daily Calorie Target</flux:label>
            <flux:description>
                Auto-suggested based on your TDEE and goal
                @if ($goal === 'cut')
                    (TDEE &minus; 20%).
                @elseif ($goal === 'bulk')
                    (TDEE + 20%).
                @else
                    (same as TDEE).
                @endif
                Adjust freely.
            </flux:description>
            <flux:input wire:model="daily_calorie_target" type="number" min="500" max="9999" required />
            <flux:error name="daily_calorie_target" />
        </flux:field>

        <flux:button type="submit" variant="primary">Save Settings</flux:button>
    </form>
</section>
