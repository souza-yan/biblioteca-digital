@props([
    'icon',
    'label',
    'href' => null,
    'variant' => 'primary',
])

@php
    $classes = [
        'primary' => 'bg-blue-800 text-white hover:bg-blue-900 focus:ring-blue-600',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-blue-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-600',
    ][$variant] ?? 'bg-blue-800 text-white hover:bg-blue-900 focus:ring-blue-600';

    $baseClasses = 'inline-flex size-11 shrink-0 items-center justify-center rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}"
        {{ $attributes->class([$baseClasses, $classes]) }}>
        <x-ts-icon :icon="$icon" outline class="size-6" />
    </a>
@else
    <button type="button" aria-label="{{ $label }}" title="{{ $label }}"
        {{ $attributes->class([$baseClasses, $classes]) }}>
        <x-ts-icon :icon="$icon" outline class="size-6" />
    </button>
@endif
