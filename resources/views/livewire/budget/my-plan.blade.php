<div class="w-full max-w-3xl">
    @if (! $this->plan)
        <div class="rounded-xl border border-zinc-200 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.document-text class="size-6 text-zinc-400" />
            </div>
            <flux:heading size="lg" class="mb-1">No plan yet</flux:heading>
            <flux:text class="text-zinc-400">Your coach hasn't created a plan for you yet. Check back soon!</flux:text>
        </div>
    @else
        <div class="mb-8">
            <flux:heading size="xl" class="mb-1">{{ $this->plan->title }}</flux:heading>
            <flux:text class="text-zinc-500">Created {{ $this->plan->created_at->format('M j, Y') }}</flux:text>
        </div>

        <div class="space-y-6">
            @foreach ($this->plan->sections as $section)
                <div
                    wire:key="section-{{ $section->id }}"
                    class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div class="border-b border-zinc-100 px-6 py-4 dark:border-zinc-800">
                        <flux:heading size="lg">{{ $section->title }}</flux:heading>
                    </div>
                    <div class="trix-content px-6 py-4 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                        {!! $section->body !!}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
