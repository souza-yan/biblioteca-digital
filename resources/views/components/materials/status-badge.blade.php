@props(['status'])

@php
    $colorClasses = match ($status) {
        \App\Enums\MaterialStatus::PUBLISHED => 'bg-blue-100 text-blue-800',
        \App\Enums\MaterialStatus::DRAFT => 'bg-slate-100 text-slate-700',
        \App\Enums\MaterialStatus::ARCHIVED => 'bg-yellow-100 text-yellow-800',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold', $colorClasses]) }}>
    {{ $status->label() }}
</span>
