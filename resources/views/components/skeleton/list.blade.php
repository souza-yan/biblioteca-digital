@props(['items' => 4])

<div {{ $attributes->class('divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 bg-white animate-pulse') }}
    role="status" aria-label="Carregando lista">
    @foreach (range(1, $items) as $item)
        <div class="flex items-center gap-4 p-4">
            <div class="skeleton-shimmer size-11 shrink-0 rounded-xl"></div>
            <div class="min-w-0 flex-1 space-y-2">
                <div class="skeleton-shimmer h-3 rounded-full" style="width: {{ 45 + (($item * 11) % 40) }}%"></div>
                <div class="skeleton-shimmer h-2.5 w-2/5 rounded-full"></div>
            </div>
            <div class="skeleton-shimmer h-9 w-20 shrink-0 rounded-lg"></div>
        </div>
    @endforeach
    <span class="sr-only">Carregando lista...</span>
</div>
