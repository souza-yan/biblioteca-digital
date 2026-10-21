@props(['items' => []])

<nav aria-label="Navegação estrutural" {{ $attributes->merge(['class' => 'overflow-x-auto']) }}>
    <ol class="flex min-w-max items-center gap-2 text-sm font-medium text-slate-600">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->first)
                    <x-ts-icon icon="chevron-right" outline class="size-4 shrink-0 text-slate-400" aria-hidden="true" />
                @endif

                @if (! $loop->last && isset($item['url']))
                    <a href="{{ $item['url'] }}" class="rounded text-blue-800 hover:underline focus-visible:outline-2 focus-visible:outline-blue-600">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span @if ($loop->last) aria-current="page" @endif class="truncate text-slate-700">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
