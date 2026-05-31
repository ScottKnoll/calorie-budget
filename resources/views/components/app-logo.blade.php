@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Knoll Fit Hub" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <flux:icon.biceps-flexed class="size-5 text-accent-foreground" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Knoll Fit Hub" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <flux:icon.biceps-flexed class="size-5 text-accent-foreground" />
        </x-slot>
    </flux:brand>
@endif
