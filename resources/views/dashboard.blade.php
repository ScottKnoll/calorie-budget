<x-layouts::app :title="__('Dashboard')">
    <div>
        <flux:heading size="xl" class="mb-1">Welcome back, {{ auth()->user()->name }}.</flux:heading>
        <flux:text>Here's a quick look at where you stand today.</flux:text>

        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <a href="{{ route('budget.log') }}" wire:navigate class="group flex items-start gap-4 rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                    <flux:icon.pencil-square class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <flux:heading class="mb-1">Daily Log</flux:heading>
                    <flux:text>Log today's calories and see your remaining budget.</flux:text>
                </div>
            </a>

            <a href="{{ route('budget.summary') }}" wire:navigate class="group flex items-start gap-4 rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                    <flux:icon.chart-bar class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <flux:heading class="mb-1">Weekly Summary</flux:heading>
                    <flux:text>Review your over/under balance for the week.</flux:text>
                </div>
            </a>

            <a href="{{ route('budget.setup') }}" wire:navigate class="group flex items-start gap-4 rounded-xl border border-zinc-200 p-6 transition hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 transition group-hover:bg-zinc-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700">
                    <flux:icon.cog-6-tooth class="size-5 text-zinc-600 dark:text-zinc-400" />
                </div>
                <div>
                    <flux:heading class="mb-1">Calorie Setup</flux:heading>
                    <flux:text>Update your TDEE, goal, and daily calorie target.</flux:text>
                </div>
            </a>
        </div>
    </div>
</x-layouts::app>
