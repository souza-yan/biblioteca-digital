<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8" data-skeleton-variant="{{ $isStaff ? 'staff' : 'teacher' }}">
    <section @class([
        'relative isolate overflow-hidden rounded-2xl bg-blue-800 shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12',
        'px-5 py-6' => $isStaff, 'px-6 py-8' => ! $isStaff,
    ])>
        <x-skeleton.text @class(['w-fit font-bold uppercase tracking-[0.18em]', 'text-sm' => $isStaff, 'text-base' => ! $isStaff])>Biblioteca digital</x-skeleton.text>
        <x-skeleton.text @class(['w-fit font-bold leading-tight tracking-tight sm:text-5xl', 'mt-2 text-3xl' => $isStaff, 'mt-3 text-4xl' => ! $isStaff])>Materiais</x-skeleton.text>
        <x-skeleton.text @class(['max-w-2xl leading-7 sm:text-xl', 'mt-3 hidden text-base sm:block' => $isStaff, 'mt-4 text-lg' => ! $isStaff])>Explore os temas e encontre materiais publicados para cada categoria.</x-skeleton.text>
    </section>
    <section class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div @class(['hidden sm:block' => $isStaff])>
                <x-skeleton.text class="w-fit text-base font-semibold uppercase tracking-[0.14em]">Acervo</x-skeleton.text>
                <x-skeleton.text class="mt-1 text-2xl font-bold">Explore por categoria</x-skeleton.text>
            </div>
            <div class="flex w-full flex-col gap-3 sm:max-w-2xl sm:flex-row sm:items-end">
                <div class="w-full sm:flex-1"><x-skeleton.text class="w-fit text-base font-medium">Pesquisar categorias</x-skeleton.text><x-skeleton.block class="mt-1 h-11 rounded-lg" /></div>
                <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0">
                    @can('create', \App\Models\Category::class)<x-skeleton.button />@endcan
                    @can('create', \App\Models\Material::class)<x-skeleton.button />@endcan
                </div>
            </div>
        </div>
        @if ($isStaff)
            <div class="grid grid-cols-2 gap-3 md:hidden">
                @forelse ($categories as $category)
                    <x-categories.library-card-skeleton :category="$category" compact />
                @empty
                    <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10"><x-skeleton.line class="mx-auto w-60 max-w-full" /></div>
                @endforelse
            </div>
        @endif
        <div @class(['grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3', 'hidden md:grid' => $isStaff])>
            @forelse ($categories as $category)
                <x-categories.library-card-skeleton :category="$category" />
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 md:col-span-2 xl:col-span-3">
                    <x-skeleton.text class="mx-auto w-fit text-lg font-semibold">Nenhuma categoria encontrada.</x-skeleton.text>
                    <x-skeleton.text class="mx-auto mt-2 w-fit text-base">Tente outro termo de pesquisa ou volte mais tarde para consultar novas categorias.</x-skeleton.text>
                </div>
            @endforelse
        </div>
        <x-skeleton.pagination :paginator="$categories" />
    </section>
</div>
