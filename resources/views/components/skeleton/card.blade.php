@props(['withImage' => true])

<div {{ $attributes->class('rounded-2xl border border-slate-200 bg-white p-5 shadow-sm animate-pulse') }}
    role="status" aria-label="Carregando card">
    @if ($withImage)
        <x-skeleton.image height="h-36" class="mb-5" />
    @endif
    <x-skeleton.text :lines="4" />
    <div class="mt-5 flex items-center gap-3">
        <div class="skeleton-shimmer size-10 rounded-full"></div>
        <div class="min-w-0 flex-1 space-y-2">
            <div class="skeleton-shimmer h-2.5 w-1/3 rounded-full"></div>
            <div class="skeleton-shimmer h-2 w-1/2 rounded-full"></div>
        </div>
    </div>
    <span class="sr-only">Carregando card...</span>
</div>
