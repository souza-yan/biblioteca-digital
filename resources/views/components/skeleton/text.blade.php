@props(['lines' => 4])

<div {{ $attributes->class('space-y-2.5 animate-pulse') }} role="status" aria-label="Carregando texto">
    @foreach (range(1, $lines) as $line)
        <div @class([
            'skeleton-shimmer h-2.5 rounded-full',
            'w-2/5' => $line === 1,
            'w-full' => $line > 1 && $line % 3 !== 0,
            'w-4/5' => $line > 1 && $line % 3 === 0,
        ])></div>
    @endforeach
    <span class="sr-only">Carregando texto...</span>
</div>
