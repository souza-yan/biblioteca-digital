<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8"
    role="status" aria-busy="true" aria-label="Carregando biblioteca de materiais">
    <div class="mb-8 flex items-center justify-between gap-4">
        <x-skeleton.block class="h-10 w-72 max-w-[75%] rounded-xl" />
        <x-skeleton.block class="h-10 w-24 shrink-0 rounded-lg" />
    </div>

    <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach (range(1, 3) as $filter)
            <div class="space-y-2">
                <x-skeleton.line size="sm" class="w-24" />
                <x-skeleton.block class="h-11 rounded-xl" />
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach (range(1, 8) as $card)
            <x-materials.card-skeleton />
        @endforeach
    </div>

    <span class="sr-only">Carregando…</span>
</div>
