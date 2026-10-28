@props(['icon', 'active' => false])

@php
    $icons = [
        'home' => 'home',
        'book' => 'book-open',
        'folder' => 'folder',
        'users' => 'users',
        'school' => 'academic-cap',
        'activity' => 'chart-bar',
        'bookmark' => 'bookmark',
        'profile' => 'user-circle',
    ];
@endphp

<x-ts-icon :icon="$icons[$icon] ?? 'question-mark-circle'" outline
    {{ $attributes->class(['size-5 shrink-0 transition-colors', 'text-blue-800' => $active, 'text-white' => ! $active]) }} />
