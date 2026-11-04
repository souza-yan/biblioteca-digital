<div x-data="{ isOpen: false }" x-on:click.outside="isOpen = false" x-on:keydown.escape.window="isOpen = false"
    class="relative z-20 mt-6 transition-transform duration-300 ease-out"
    x-bind:class="isOpen ? '-translate-y-2' : 'translate-y-0'">
    <div class="relative">
        <label for="dashboard-material-search" class="sr-only">Buscar materiais por título ou autor</label>
        <x-ts-icon icon="magnifying-glass" outline
            class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400" />
        <input id="dashboard-material-search" type="search" wire:model.live.debounce.250ms="search"
            x-on:focus="isOpen = true" x-on:input="isOpen = true"
            x-bind:aria-expanded="isOpen.toString()" aria-controls="dashboard-material-results"
            aria-autocomplete="list" autocomplete="off" placeholder="Busque por título ou autor"
            class="h-12 w-full rounded-xl border-0 bg-white py-3 pl-12 pr-12 text-slate-900 shadow-sm transition-shadow placeholder:text-slate-400 focus:ring-2 focus:ring-blue-200" />

        <div wire:loading.flex wire:target="search"
            class="absolute right-4 top-1/2 hidden -translate-y-1/2 items-center text-blue-700"
            aria-label="Buscando materiais">
            <svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
                <path class="opacity-75" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3Z" />
            </svg>
        </div>
    </div>

    <div id="dashboard-material-results" x-cloak x-show="isOpen" x-collapse.duration.300ms
        class="overflow-hidden">
        <div class="mt-3 overflow-hidden rounded-2xl border border-blue-100 bg-white text-slate-900 shadow-xl">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 sm:px-5">
                <p class="text-sm font-semibold text-slate-700">
                    {{ $normalizedSearch === '' ? 'Materiais recentes' : 'Resultados encontrados' }}
                </p>
                @if ($normalizedSearch !== '')
                    <a href="{{ route('painel.library', ['q' => $normalizedSearch]) }}"
                        class="text-sm font-semibold text-blue-700 hover:underline">
                        Ver todos
                    </a>
                @endif
            </div>

            <div role="listbox" aria-label="Materiais encontrados"
                class="grid max-h-[24rem] grid-cols-1 overflow-y-auto sm:grid-cols-2">
                @forelse ($materials as $material)
                    <a wire:key="home-search-material-{{ $material->getKey() }}"
                        href="{{ route('painel.library.show', $material) }}" role="option"
                        class="group flex min-w-0 items-center gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-blue-50 focus:bg-blue-50 focus:outline-none sm:px-5 sm:odd:border-r">
                        <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
                            :alt="'Prévia de '.$material->title" fit="contain"
                            class="h-16 w-11 shrink-0 !aspect-auto rounded-lg" />
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-slate-900 group-hover:text-blue-800">
                                {{ $material->title }}
                            </span>
                            <span class="mt-1 block truncate text-sm text-slate-500">
                                {{ $material->author }} · {{ $material->category->name }}
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="px-5 py-8 text-center sm:col-span-2">
                        <x-ts-icon icon="magnifying-glass" outline class="mx-auto size-8 text-slate-400" />
                        <p class="mt-2 font-semibold text-slate-700">Nenhum material encontrado.</p>
                        <p class="mt-1 text-sm text-slate-500">Tente pesquisar outro título ou autor.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
