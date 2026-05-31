<div class="w-full max-w-4xl">
    {{-- Header --}}
    <div class="mb-6 flex items-center gap-4">
        <flux:button :href="route('coach.clients.show', $client)" wire:navigate variant="ghost" size="sm" icon="arrow-left">
            {{ $client->name }}
        </flux:button>
    </div>

    <div class="mb-8">
        <flux:heading size="xl" class="mb-1">{{ $plan ? 'Edit Plan' : 'New Plan' }}</flux:heading>
        <flux:text class="text-zinc-500">{{ $client->name }}</flux:text>
    </div>

    <div class="space-y-6">
        {{-- Plan title --}}
        <flux:field>
            <flux:label>Plan title</flux:label>
            <flux:input wire:model="title" placeholder="e.g. Initial Plan — May 2026" />
            <flux:error name="title" />
        </flux:field>

        {{-- Single Trix editor --}}
        <div
            wire:ignore
            x-data="{
                init() {
                    const inputEl = this.$refs.input;
                    const editorEl = this.$refs.editor;

                    inputEl.value = $wire.body;

                    editorEl.addEventListener('trix-initialize', () => {
                        editorEl.editor.loadHTML($wire.body);
                    });

                    editorEl.addEventListener('trix-change', () => {
                        $wire.body = editorEl.innerHTML;
                    });
                }
            }"
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <input x-ref="input" id="plan-body" type="hidden">
            <trix-editor
                x-ref="editor"
                input="plan-body"
                class="trix-content min-h-96"
            ></trix-editor>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
            <flux:button :href="route('coach.clients.show', $client)" wire:navigate variant="ghost">
                Cancel
            </flux:button>
            <flux:button wire:click="save" variant="primary" wire:loading.attr="disabled">
                {{ $plan ? 'Save changes' : 'Create plan' }}
            </flux:button>
        </div>
    </div>
</div>
