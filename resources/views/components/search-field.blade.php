@props([
    'model',
    'label',
    'placeholder',
])

<x-form.input :id="'search-'.str_replace('.', '-', $model)" :label="$label" type="search"
    wire:model.live.debounce.300ms="{{ $model }}" :placeholder="$placeholder" autocomplete="off"
    {{ $attributes }} />
