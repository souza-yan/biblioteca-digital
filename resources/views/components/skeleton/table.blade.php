@props(['columns' => 4, 'rows' => 5])

<div {{ $attributes->class('overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm animate-pulse') }}
    role="status" aria-label="Carregando tabela">
    <div class="grid gap-4 bg-blue-800 px-5 py-4" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr))">
        @foreach (range(1, $columns) as $column)
            <div class="h-3 rounded-full bg-blue-200/50"></div>
        @endforeach
    </div>
    @foreach (range(1, $rows) as $row)
        <div class="grid gap-4 border-t border-slate-100 px-5 py-4"
            style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr))">
            @foreach (range(1, $columns) as $column)
                <div class="skeleton-shimmer h-4 rounded-full"
                    style="width: {{ 48 + ((($row + $column) * 11) % 45) }}%"></div>
            @endforeach
        </div>
    @endforeach
    <span class="sr-only">Carregando tabela...</span>
</div>
