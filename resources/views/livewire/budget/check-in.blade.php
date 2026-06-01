<section class="w-full max-w-2xl">
    <div class="mb-8">
        <flux:heading size="xl">{{ $this->isEditing ? 'Edit Check-In' : 'Weekly Check-In' }}</flux:heading>
        <flux:text class="mt-1">Take a few minutes to reflect on your week. Your coach will review your responses.</flux:text>
    </div>

    <form wire:submit="submit" class="space-y-6">

        <flux:field>
            <flux:label>Current weight (lbs)</flux:label>
            <flux:input wire:model="weight" type="number" step="0.1" min="50" max="999" placeholder="e.g. 185.5" />
            <flux:error name="weight" />
        </flux:field>

        <flux:separator />

        <flux:field>
            <flux:label>How did the week feel overall?</flux:label>
            <flux:textarea wire:model="week_feeling" rows="3" placeholder="Give us a general sense of how things went..." maxlength="2000" />
            <flux:error name="week_feeling" />
        </flux:field>

        <flux:field>
            <flux:label>What went well this week?</flux:label>
            <flux:textarea wire:model="went_well" rows="3" placeholder="Wins, habits you nailed, moments you're proud of..." maxlength="2000" />
            <flux:error name="went_well" />
        </flux:field>

        <flux:field>
            <flux:label>What felt hardest this week?</flux:label>
            <flux:textarea wire:model="felt_hardest" rows="3" placeholder="Challenges, slip-ups, or things that felt off..." maxlength="2000" />
            <flux:error name="felt_hardest" />
        </flux:field>

        <flux:field>
            <flux:label>How was hunger, energy, and sleep?</flux:label>
            <flux:textarea wire:model="hunger_energy_sleep" rows="3" placeholder="e.g. Energy was good mid-week, hunger spiked on Thursday, sleep has been solid..." maxlength="2000" />
            <flux:error name="hunger_energy_sleep" />
        </flux:field>

        <flux:field>
            <flux:label>How consistent were you with walks/workouts/activity?</flux:label>
            <flux:textarea wire:model="activity_consistency" rows="3" placeholder="e.g. Hit 3 out of 4 planned workouts, walked most days..." maxlength="2000" />
            <flux:error name="activity_consistency" />
        </flux:field>

        <flux:field>
            <flux:label>Anything specific you need help with? <flux:badge size="sm" color="zinc" class="ml-1">Optional</flux:badge></flux:label>
            <flux:textarea wire:model="need_help" rows="3" placeholder="Questions, concerns, or anything you'd like your coach to address..." maxlength="2000" />
            <flux:error name="need_help" />
        </flux:field>

        <div class="flex items-center justify-end gap-3 pt-2">
            @if ($this->isEditing)
                <flux:button :href="route('budget.check-ins')" wire:navigate variant="ghost">Cancel</flux:button>
            @endif
            <flux:button type="submit" variant="primary">
                {{ $this->isEditing ? 'Update Check-In' : 'Submit Check-In' }}
            </flux:button>
        </div>

    </form>
</section>
