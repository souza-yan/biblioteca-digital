@props([
    'model',
    'label',
    'placeholder',
])

<x-ts-input
    wire:model.live.debounce.300ms="{{ $model }}"
    :label="$label"
    :placeholder="$placeholder"
/>
