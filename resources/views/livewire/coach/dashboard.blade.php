<div>
    <div class="mb-8">
        <flux:heading size="xl" class="mb-1">Clients</flux:heading>
        <flux:text>Manage and review your coaching clients.</flux:text>
    </div>

    @if ($this->clients->isEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.users class="size-6 text-zinc-400" />
            </div>
            <flux:heading size="lg" class="mb-1">No clients yet</flux:heading>
            <flux:text class="mb-4 text-zinc-400">Share your intake link to get started.</flux:text>
            <flux:badge variant="outline" size="lg" class="font-mono">
                {{ url('/register?type=client') }}
            </flux:badge>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($this->clients as $client)
                    <div wire:key="client-{{ $client->id }}" class="flex items-center gap-4 px-6 py-4">
                        <a
                            href="{{ route('coach.clients.show', $client) }}"
                            wire:navigate
                            class="group flex min-w-0 flex-1 items-center gap-4"
                        >
                            <flux:avatar :name="$client->name" size="sm" class="shrink-0" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-zinc-900 transition group-hover:text-zinc-600 dark:text-white dark:group-hover:text-zinc-300">{{ $client->name }}</p>
                                <p class="truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $client->email }}</p>
                            </div>

                            <div class="hidden shrink-0 items-center gap-6 text-sm text-zinc-400 sm:flex">
                                @if ($client->intake_completed_at)
                                    <flux:badge color="green" size="sm">Intake complete</flux:badge>
                                @else
                                    <flux:badge color="yellow" size="sm">Intake pending</flux:badge>
                                @endif
                                <span>{{ $client->calorie_entries_count }} logs</span>
                            </div>

                            <flux:icon.chevron-right class="size-4 shrink-0 text-zinc-400 transition group-hover:text-zinc-600 dark:group-hover:text-zinc-300" />
                        </a>

                        <flux:modal.trigger :name="'delete-client-' . $client->id">
                            <flux:button icon="trash" variant="ghost" size="sm" class="shrink-0 text-zinc-400 hover:text-red-500" />
                        </flux:modal.trigger>
                    </div>

                    <flux:modal :name="'delete-client-' . $client->id" class="max-w-sm">
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="lg">Delete client?</flux:heading>
                                <flux:text class="mt-1 text-zinc-500">
                                    This will permanently delete <span class="font-semibold text-zinc-800 dark:text-white">{{ $client->name }}</span> and all of their data. This cannot be undone.
                                </flux:text>
                            </div>
                            <div class="flex justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button variant="ghost">Cancel</flux:button>
                                </flux:modal.close>
                                <flux:button
                                    variant="danger"
                                    wire:click="deleteClient({{ $client->id }})"
                                    wire:loading.attr="disabled"
                                >
                                    Delete
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach
            </div>
        </div>

        <p class="mt-4 text-sm text-zinc-400">
            {{ $this->clients->count() }} {{ Str::plural('client', $this->clients->count()) }}
        </p>
    @endif
</div>
