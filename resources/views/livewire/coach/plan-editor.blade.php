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
        <div>
            <flux:field>
                <flux:label>Plan title</flux:label>
                <flux:input
                    wire:model="title"
                    placeholder="e.g. Initial Plan — May 2026"
                />
                <flux:error name="title" />
            </flux:field>
        </div>

        {{-- Sections --}}
        @foreach ($sections as $index => $section)
            <div
                wire:key="section-{{ $index }}"
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-center gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:input
                        wire:model="sections.{{ $index }}.title"
                        placeholder="Section title (e.g. Nutrition, Activity, Sleep…)"
                        class="flex-1 border-0 bg-transparent p-0 text-sm font-semibold shadow-none focus:ring-0"
                    />
                    <flux:error name="sections.{{ $index }}.title" />
                    @if (count($sections) > 1)
                        <flux:button
                            wire:click="removeSection({{ $index }})"
                            wire:confirm="Remove this section?"
                            icon="trash"
                            variant="ghost"
                            size="sm"
                            class="shrink-0 text-zinc-400 hover:text-red-500"
                        />
                    @endif
                </div>

                {{-- Trix editor --}}
                <div
                    x-data="{
                        init() {
                            const inputEl = this.$refs.input;
                            const editorEl = this.$refs.editor;

                            inputEl.value = @js($section['body'] ?? '');

                            editorEl.addEventListener('trix-initialize', () => {
                                editorEl.editor.loadHTML(inputEl.value);
                            });

                            editorEl.addEventListener('trix-change', () => {
                                $wire.updateSectionBody({{ $index }}, editorEl.innerHTML);
                            });
                        }
                    }"
                >
                    <input
                        x-ref="input"
                        id="trix-input-{{ $index }}"
                        type="hidden"
                    >
                    <trix-editor
                        x-ref="editor"
                        input="trix-input-{{ $index }}"
                        class="trix-content"
                    ></trix-editor>
                </div>
            </div>
        @endforeach

        {{-- Add section --}}
        <flux:button wire:click="addSection" variant="ghost" icon="plus" class="w-full">
            Add section
        </flux:button>

        {{-- Save --}}
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
