@props(['size' => 'md'])

@php
    $height = match ($size) {
        'xs' => 'h-3',
        'sm' => 'h-4',
        'lg' => 'h-6',
        'xl' => 'h-8',
        default => 'h-5',
    };
@endphp

<x-skeleton.block {{ $attributes->class([$height, 'rounded-full']) }} />
