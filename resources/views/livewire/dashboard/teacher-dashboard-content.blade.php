<div class="space-y-6 lg:space-y-8" aria-busy="false">
    <section aria-labelledby="categories-title" x-data="{
        moveCategoryCarousel(direction) {
            const carousel = this.$refs.categoryCarousel;
            const maxScrollLeft = carousel.scrollWidth - carousel.clientWidth;
            const isAtStart = carousel.scrollLeft <= 1;
            const isAtEnd = carousel.scrollLeft >= maxScrollLeft - 1;
            let nextPosition = carousel.scrollLeft + (direction * carousel.clientWidth);

            if (direction < 0 && isAtStart) nextPosition = maxScrollLeft;
            if (direction > 0 && isAtEnd) nextPosition = 0;

            carousel.scrollTo({ left: nextPosition, behavior: 'smooth' });
        },
    }">
        <h2 id="categories-title" class="text-2xl font-bold tracking-tight text-slate-900">Explore por categoria</h2>

        <div class="mt-5">
            @if ($categories->isEmpty())
                <p class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-sm text-slate-500">Ainda não há categorias com materiais publicados.</p>
            @else
                @if ($categories->count() > 3)
                    <div class="grid grid-cols-[2.25rem_minmax(0,1fr)_2.25rem] items-center gap-2 sm:grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] sm:gap-3">
                        <button type="button" x-on:click="moveCategoryCarousel(-1)"
                            aria-label="Ver categorias anteriores" title="Ver categorias anteriores"
                            class="inline-flex size-9 items-center justify-center justify-self-center rounded-full border border-slate-200 bg-white text-blue-800 shadow-md transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 sm:size-10">
                            <x-ts-icon icon="chevron-left" outline class="size-5" />
                        </button>
                @endif

                <div x-ref="categoryCarousel" class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-2" aria-label="Categorias disponíveis">
                    @foreach ($categories as $category)
                        @php
                            $categoryName = \Illuminate\Support\Str::lower($category->name);
                            $categoryIcon = match (true) {
                                str_contains($categoryName, 'arduino') => 'cpu-chip',
                                str_contains($categoryName, 'eletr') => 'bolt',
                                str_contains($categoryName, 'lego') => 'cube',
                                str_contains($categoryName, 'program') => 'command-line',
                                default => 'folder-open',
                            };
                        @endphp
                        <x-dashboard.category-card wire:key="dashboard-category-{{ $category->getKey() }}"
                            :category="$category" :icon="$categoryIcon" />
                    @endforeach
                </div>

                @if ($categories->count() > 3)
                        <button type="button" x-on:click="moveCategoryCarousel(1)"
                            aria-label="Ver próximas categorias" title="Ver próximas categorias"
                            class="inline-flex size-9 items-center justify-center justify-self-center rounded-full border border-slate-200 bg-white text-blue-800 shadow-md transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 sm:size-10">
                            <x-ts-icon icon="chevron-right" outline class="size-5" />
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <div class="grid min-w-0 grid-cols-1 gap-8 xl:grid-cols-[minmax(0,3fr)_minmax(19rem,2fr)]">
        <section class="min-w-0" aria-labelledby="recent-materials-title">
            <div class="flex items-end justify-between gap-4">
                <h2 id="recent-materials-title" class="text-2xl font-bold tracking-tight text-slate-900">Materiais publicados recentemente</h2>
                <a href="{{ route('painel.library') }}" class="shrink-0 text-sm font-semibold text-blue-800 hover:underline">Ver materiais</a>
            </div>

            <div x-data="{
                movePublishedCarousel(direction) {
                    const carousel = this.$refs.publishedCarousel;
                    const maxScrollLeft = carousel.scrollWidth - carousel.clientWidth;
                    const isAtStart = carousel.scrollLeft <= 1;
                    const isAtEnd = carousel.scrollLeft >= maxScrollLeft - 1;
                    let nextPosition = carousel.scrollLeft + (direction * carousel.clientWidth);

                    if (direction < 0 && isAtStart) nextPosition = maxScrollLeft;
                    if (direction > 0 && isAtEnd) nextPosition = 0;

                    carousel.scrollTo({ left: nextPosition, behavior: 'smooth' });
                },
            }" class="mt-5">
                <div @class([
                    'grid grid-cols-[2.25rem_minmax(0,1fr)_2.25rem] items-center gap-2 sm:block' => $publishedMaterials->count() > 1,
                ])>
                    @if ($publishedMaterials->count() > 1)
                        <button type="button" x-on:click="movePublishedCarousel(-1)"
                            aria-label="Ver materiais publicados anteriores" title="Ver materiais publicados anteriores"
                            class="inline-flex size-9 items-center justify-center justify-self-center rounded-full border border-slate-200 bg-white text-blue-800 shadow-md transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 sm:hidden">
                            <x-ts-icon icon="chevron-left" outline class="size-5" />
                        </button>
                    @endif

                    <div x-ref="publishedCarousel" class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-2 sm:grid sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:pb-0 lg:grid-cols-3" aria-label="Materiais publicados recentemente">
                        @forelse ($publishedMaterials as $material)
                            <x-materials.card variant="featured" :material="$material" :version="$material->currentVersion"
                                :href="route('painel.library.show', $material)"
                                :date="$material->published_at?->format('d/m/Y') ?? 'Data não informada'" :type="$material->type">
                                <x-slot:titleAction>
                                    @livewire('materials.favorite-button', ['material' => $material, 'isFavorited' => $material->is_favorited], key('dashboard-favorite-'.$material->getKey()))
                                </x-slot:titleAction>
                            </x-materials.card>
                        @empty
                            <div class="w-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center sm:col-span-2 lg:col-span-3">
                                <x-ts-icon icon="document-text" outline class="mx-auto size-10 text-slate-400" />
                                <p class="mt-3 font-semibold text-slate-700">Ainda não há materiais publicados.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($publishedMaterials->count() > 1)
                        <button type="button" x-on:click="movePublishedCarousel(1)"
                            aria-label="Ver próximos materiais publicados" title="Ver próximos materiais publicados"
                            class="inline-flex size-9 items-center justify-center justify-self-center rounded-full border border-slate-200 bg-white text-blue-800 shadow-md transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-600 sm:hidden">
                            <x-ts-icon icon="chevron-right" outline class="size-5" />
                        </button>
                    @endif
                </div>
            </div>
        </section>

        <section class="flex min-w-0 flex-col" aria-labelledby="recently-accessed-title">
            <h2 id="recently-accessed-title" class="text-2xl font-bold tracking-tight text-slate-900">Continue de onde parou</h2>
            <div class="mt-5 space-y-3 xl:grid xl:gap-3 xl:space-y-0">
                @forelse ($recentlyAccessedMaterials as $material)
                    <x-dashboard.recent-access-card wire:key="recent-access-{{ $material->getKey() }}"
                        :material="$material" />
                @empty
                    <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Os materiais acessados ou baixados aparecerão aqui.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
