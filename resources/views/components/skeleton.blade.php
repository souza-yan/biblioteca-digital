@props([
    'lines' => 3,
    'class' => '',
])

<div {{ $attributes->class(['space-y-3 animate-pulse', $class]) }} role="status" aria-label="Carregando conteúdo">
    @foreach (range(1, $lines) as $line)
        <div class="skeleton-shimmer h-5 rounded-lg"
            style="width: {{ 56 + (($line * 13) % 36) }}%"></div>
    @endforeach
    <span class="sr-only">Carregando...</span>
</div>
