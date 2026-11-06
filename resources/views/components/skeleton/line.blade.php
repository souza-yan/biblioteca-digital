@props(['size' => 'md'])

@php
    $heightClass = match ($size) {
        'xs' => 'h-3',
        'sm' => 'h-4',
        'lg' => 'h-6',
        'xl' => 'h-8',
        default => 'h-5',
    };
@endphp

<x-skeleton.block {{ $attributes->class([$heightClass, 'rounded-full']) }} />
