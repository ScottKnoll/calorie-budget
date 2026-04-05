@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Cal Budget" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <flux:icon.piggy-bank class="size-5 text-accent-foreground" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Cal Budget" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <flux:icon.piggy-bank class="size-5 text-accent-foreground" />
        </x-slot>
    </flux:brand>
@endif
