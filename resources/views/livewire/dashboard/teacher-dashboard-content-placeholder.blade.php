<div class="space-y-6 lg:space-y-8" aria-busy="true">
    <section role="status" aria-label="Carregando categorias">
        <x-skeleton.line size="xl" class="w-56" />
        <div class="mt-5 grid grid-cols-[2.25rem_minmax(0,1fr)_2.25rem] items-center gap-2 sm:grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] sm:gap-3">
            <x-skeleton.circle class="!size-9 sm:!size-10" />
            <div class="flex snap-x gap-3 overflow-hidden pb-2">
                @foreach (range(1, 3) as $card)
                    <x-dashboard.category-card-skeleton />
                @endforeach
            </div>
            <x-skeleton.circle class="!size-9 sm:!size-10" />
        </div>
        <span class="sr-only">Carregando…</span>
    </section>

    <div class="grid min-w-0 grid-cols-1 gap-8 xl:grid-cols-[minmax(0,3fr)_minmax(19rem,2fr)]">
        <section role="status" aria-label="Carregando materiais publicados recentemente">
            <div class="flex items-end justify-between gap-4">
                <x-skeleton.line size="xl" class="w-80 max-w-[75%]" />
                <x-skeleton.line size="sm" class="w-24" />
            </div>
            <div class="mt-5 flex snap-x gap-3 overflow-hidden pb-2 sm:grid sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:pb-0 lg:grid-cols-3">
                @foreach (range(1, 3) as $card)
                    <x-materials.card-skeleton variant="featured" />
                @endforeach
            </div>
            <span class="sr-only">Carregando…</span>
        </section>

        <section role="status" aria-label="Carregando materiais acessados recentemente">
            <x-skeleton.line size="xl" class="w-64" />
            <div class="mt-5 space-y-3 xl:grid xl:gap-3 xl:space-y-0">
                @foreach (range(1, 4) as $card)
                    <x-dashboard.recent-access-card-skeleton />
                @endforeach
            </div>
            <span class="sr-only">Carregando…</span>
        </section>
    </div>
</div>
