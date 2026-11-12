@props([
    'material',
    'version' => null,
    'variant' => 'library',
    'href' => null,
    'date' => null,
    'type' => null,
])

@php
    $typeBadgeClass = match (true) {
        strtolower((string) $type) === 'pdf' => 'bg-red-50 text-red-700',
        in_array(strtolower((string) $type), ['png', 'jpg', 'jpeg', 'webp', 'gif'], true) => 'bg-violet-50 text-violet-700',
        in_array(strtolower((string) $type), ['xls', 'xlsx', 'csv'], true) => 'bg-emerald-50 text-emerald-700',
        in_array(strtolower((string) $type), ['ppt', 'pptx'], true) => 'bg-amber-50 text-amber-700',
        in_array(strtolower((string) $type), ['doc', 'docx'], true) => 'bg-blue-50 text-blue-700',
        default => 'bg-slate-100 text-slate-600',
    };
    $categoryName = $material->category?->name ?? 'Sem categoria';
@endphp

@if ($variant === 'featured')
    <article {{ $attributes->class('group relative w-full shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md sm:w-auto') }}>
        @if ($href)
            <a href="{{ $href }}" class="block p-3" aria-label="Abrir {{ $material->title }}">
                <x-materials.pdf-cover :material="$material" :version="$version" :alt="'Capa do material '.$material->title" fit="contain" class="mx-auto w-48 rounded-xl sm:w-full" />
            </a>
        @else
            <div class="p-3">
                <x-materials.pdf-cover :material="$material" :version="$version" :alt="'Capa do material '.$material->title" fit="contain" class="mx-auto w-48 rounded-xl sm:w-full" />
            </div>
        @endif

        <div class="p-4 pt-1">
            <p class="truncate text-sm font-medium text-blue-700">{{ $categoryName }}</p>
            <div class="mt-1 flex items-center gap-3">
                @if ($href)
                    <a href="{{ $href }}" class="min-w-0 flex-1 truncate text-base font-bold text-slate-900 hover:text-blue-800">{{ $material->title }}</a>
                @else
                    <h3 class="min-w-0 flex-1 truncate text-base font-bold text-slate-900">{{ $material->title }}</h3>
                @endif
                @isset($titleAction)
                    <div class="shrink-0 sm:absolute sm:right-5 sm:top-5">{{ $titleAction }}</div>
                @endisset
            </div>
            @if ($date || $type)
                <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                    <span>{{ $date }}</span>
                    @if ($type)
                        <span class="rounded-full px-2 py-1 font-semibold uppercase tracking-wide {{ $typeBadgeClass }}">{{ $type }}</span>
                    @endif
                </div>
            @endif
        </div>
    </article>
@else
    <article {{ $attributes->class('flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-3 shadow-sm') }}>
        <x-materials.pdf-cover :material="$material" :version="$version" :alt="'Prévia de '.$material->title" fit="contain" class="w-full rounded-lg" />

        <div class="min-w-0 pt-3">
            <h2 class="break-words text-sm font-semibold leading-5 text-slate-900">{{ $material->title }}</h2>
            <p class="mt-1 break-words text-xs text-slate-600">{{ $categoryName }}</p>
        </div>

        @isset($actions)
            <div class="mt-3 flex flex-wrap items-center justify-end gap-1.5 border-t border-slate-100 pt-3 sm:flex-nowrap">{{ $actions }}</div>
        @endisset
    </article>
@endif
