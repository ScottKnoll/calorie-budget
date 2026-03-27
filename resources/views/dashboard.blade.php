<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4">
        <div>
            <flux:heading size="xl" class="mb-1">Welcome back, {{ auth()->user()->name }}.</flux:heading>
            <flux:text>Here's a quick look at where you stand today.</flux:text>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('budget.log') }}" wire:navigate
               class="group flex flex-col gap-2 rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                <flux:icon.pencil-square class="size-6 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                <flux:heading>Daily Log</flux:heading>
                <flux:text>Log today's calories and see your remaining budget.</flux:text>
            </a>

            <a href="{{ route('budget.summary') }}" wire:navigate
               class="group flex flex-col gap-2 rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                <flux:icon.chart-bar class="size-6 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                <flux:heading>Weekly Summary</flux:heading>
                <flux:text>Review your over/under balance for the week.</flux:text>
            </a>

            <a href="{{ route('budget.setup') }}" wire:navigate
               class="group flex flex-col gap-2 rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                <flux:icon.cog-6-tooth class="size-6 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                <flux:heading>Calorie Setup</flux:heading>
                <flux:text>Update your TDEE, goal, and daily calorie target.</flux:text>
            </a>
        </div>
    </div>
</x-layouts::app>
