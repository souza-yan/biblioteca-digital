@props(['fields' => 4])

<div {{ $attributes->class('grid grid-cols-1 gap-4 sm:grid-cols-2 animate-pulse') }} role="status"
    aria-label="Carregando formulário">
    @foreach (range(1, $fields) as $field)
        <div class="space-y-2">
            <div class="skeleton-shimmer h-3 w-1/3 rounded-full"></div>
            <div class="skeleton-shimmer h-11 w-full rounded-lg"></div>
        </div>
    @endforeach
    <div class="flex justify-end gap-3 sm:col-span-2">
        <div class="skeleton-shimmer h-11 w-24 rounded-lg"></div>
        <div class="skeleton-shimmer h-11 w-36 rounded-lg"></div>
    </div>
    <span class="sr-only">Carregando formulário...</span>
</div>
