<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    <div class="mb-8 flex items-center justify-between gap-4">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl"><x-skeleton.text>Materiais publicados</x-skeleton.text></h1>
    </div>
    <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach (['Buscar material', 'Autor', 'Categoria'] as $label)
            <div><x-skeleton.text class="w-fit text-base font-medium">{{ $label }}</x-skeleton.text><x-skeleton.block @class(['mt-1 rounded-lg', 'h-11' => $loop->first, 'h-10' => ! $loop->first]) /></div>
        @endforeach
    </div>
    @if ($recentSearches !== [])
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($recentSearches as $recentSearch)
                <x-skeleton.text class="h-9 rounded-full px-7 text-sm">{{ $recentSearch }}</x-skeleton.text>
            @endforeach
        </div>
    @endif
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @forelse ($materials as $material)
            <x-materials.card-skeleton :material="$material" :actions="$isTeacher ? 2 : (auth()->user()->can('update', $material) ? 2 : 1)" />
        @empty
            <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-4 py-10"><x-skeleton.line size="lg" class="mx-auto w-72 max-w-full" /></div>
        @endforelse
    </div>
    <div class="mt-4"><x-skeleton.pagination :paginator="$materials" /></div>
</div>
