@props([
    'lines' => 3,
    'class' => '',
])

<div {{ $attributes->class(['space-y-3', $class]) }} aria-hidden="true">
    @foreach (range(1, $lines) as $line)
        <div class="skeleton-shimmer h-5 rounded-lg"
            style="width: {{ 56 + (($line * 13) % 36) }}%"></div>
    @endforeach
</div>
