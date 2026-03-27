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
            <flux:description>Your total daily energy expenditure — the calories needed to maintain your current weight.</flux:description>
            <flux:input wire:model="tdee" type="number" min="500" max="9999" required />
            <flux:error name="tdee" />
        </flux:field>

        <flux:field>
            <flux:label>Goal</flux:label>
            <flux:description>The direction you're currently working towards.</flux:description>
            <flux:select wire:model="goal">
                @foreach ($this->goalOptions() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="goal" />
        </flux:field>

        <flux:field>
            <flux:label>Daily Calorie Target</flux:label>
            <flux:description>The number of calories you aim to eat each day. You control this — it doesn't have to match your TDEE exactly.</flux:description>
            <flux:input wire:model="daily_calorie_target" type="number" min="500" max="9999" required />
            <flux:error name="daily_calorie_target" />
        </flux:field>

        <flux:button type="submit" variant="primary">Save Settings</flux:button>
    </form>
</section>
