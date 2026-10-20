@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegação de páginas" class="flex items-center justify-between gap-3">
        <p class="hidden text-sm text-gray-600 sm:block">
            Exibindo {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} registros
        </p>

        <div class="flex items-center gap-1" aria-label="Páginas">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Página anterior" class="inline-flex size-10 items-center justify-center rounded-md text-gray-400">
                    <x-ts-icon icon="arrow-left" outline class="size-7" />
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    aria-label="Página anterior" title="Página anterior"
                    class="inline-flex size-10 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <x-ts-icon icon="arrow-left" outline class="size-7" />
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex size-10 items-center justify-center text-gray-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" aria-label="Página {{ $page }}" class="inline-flex size-10 items-center justify-center rounded-md bg-blue-800 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                wire:loading.attr="disabled" aria-label="Ir para página {{ $page }}"
                                class="inline-flex size-10 items-center justify-center rounded-md text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-600">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    aria-label="Próxima página" title="Próxima página"
                    class="inline-flex size-10 items-center justify-center rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <x-ts-icon icon="arrow-right" outline class="size-7" />
                </button>
            @else
                <span aria-disabled="true" aria-label="Próxima página" class="inline-flex size-10 items-center justify-center rounded-md text-gray-400">
                    <x-ts-icon icon="arrow-right" outline class="size-7" />
                </span>
            @endif
        </div>
    </nav>
@endif
