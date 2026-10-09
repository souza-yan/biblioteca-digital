<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <section
            class="relative isolate overflow-hidden rounded-2xl bg-blue-800 px-6 py-8 text-white shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12"
            aria-labelledby="category-library-title">
            <p class="text-base font-bold uppercase tracking-[0.18em] text-blue-200">Biblioteca digital</p>
            <h1 id="category-library-title" class="mt-3 text-4xl font-bold leading-tight tracking-tight sm:text-5xl">
                Categorias
            </h1>
            <p class="mt-4 max-w-2xl text-lg leading-7 text-blue-100 sm:text-xl">
                Explore os temas e encontre materiais publicados para cada categoria.
            </p>
        </section>

        <section class="space-y-5" aria-labelledby="available-categories-title">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-base font-semibold uppercase tracking-[0.14em] text-blue-700">Acervo</p>
                    <h2 id="available-categories-title" class="mt-1 text-2xl font-bold text-black">
                        Categorias disponíveis
                    </h2>
                </div>
                <div class="w-full sm:max-w-md">
                    <x-search-field
                        model="search"
                        label="Pesquisar categorias"
                        placeholder="Nome, descrição ou slug"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($categories as $category)
                    <a
                        wire:key="category-library-{{ $category->id }}"
                        href="{{ route('painel.library', ['categoryFilter' => $category->getKey()]) }}"
                        class="group flex min-h-48 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md sm:p-6"
                    >
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />
                            </svg>
                        </span>
                        <span class="mt-4 block text-xl font-semibold text-black group-hover:text-blue-900">
                            {{ $category->name }}
                        </span>
                        <span class="mt-1 block text-base font-medium text-blue-700">{{ $category->slug }}</span>
                        <span class="mt-2 flex-1 text-base leading-6 text-black">
                            {{ $category->description ?: 'Consulte os materiais publicados nesta categoria.' }}
                        </span>
                        <span class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                            <span class="text-base font-medium text-black">
                                {{ $category->published_materials_count }}
                                {{ $category->published_materials_count === 1 ? 'material publicado' : 'materiais publicados' }}
                            </span>
                            <span class="text-base font-semibold text-blue-700 group-hover:text-blue-900">
                                Ver materiais <span aria-hidden="true">→</span>
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center md:col-span-2 xl:col-span-3">
                        <p class="text-lg font-semibold text-black">Nenhuma categoria encontrada.</p>
                        <p class="mt-2 text-base text-black">
                            Tente outro termo de pesquisa ou volte mais tarde para consultar novas categorias.
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($categories->hasPages())
                <x-pagination :paginator="$categories" />
            @endif
        </section>
    </div>
</div>
