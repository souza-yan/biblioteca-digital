<div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl"><x-skeleton.text class="w-fit">Meus favoritos</x-skeleton.text></h1>
        <x-skeleton.text class="mt-2 w-fit text-base">Seus materiais publicados salvos para consultar depois.</x-skeleton.text>
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @forelse ($materials as $material)
            <x-materials.card-skeleton :material="$material" />
        @empty
            <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-4 py-10"><x-skeleton.line size="lg" class="mx-auto w-96 max-w-full" /></div>
        @endforelse
    </div>
    <div class="mt-4"><x-skeleton.pagination :paginator="$materials" /></div>
</div>
