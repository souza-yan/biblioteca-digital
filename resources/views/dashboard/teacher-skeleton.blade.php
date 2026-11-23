<section class="overflow-hidden rounded-2xl bg-blue-800 px-5 py-7 shadow-sm sm:px-8 sm:py-9">
    <div class="max-w-2xl">
        <x-skeleton.text class="text-3xl font-bold tracking-tight sm:text-4xl">Encontre recursos para suas aulas</x-skeleton.text>
        <x-skeleton.text class="mt-3 text-base sm:text-lg">Explore projetos, atividades e materiais de robótica educacional.</x-skeleton.text>
    </div>
    <x-skeleton.block class="mt-6 h-12 rounded-xl" />
</section>
<section>
    <x-skeleton.text class="w-fit text-2xl font-bold tracking-tight">Explore por categoria</x-skeleton.text>
    <div class="mt-5">
        @if ($categories->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8"><x-skeleton.text class="w-fit text-sm">Ainda não há categorias com materiais publicados.</x-skeleton.text></div>
        @else
            <div @class(['grid grid-cols-[2.25rem_minmax(0,1fr)_2.25rem] items-center gap-2 sm:grid-cols-[2.5rem_minmax(0,1fr)_2.5rem] sm:gap-3' => $categories->count() > 3])>
                @if ($categories->count() > 3)<x-skeleton.block class="size-9 justify-self-center rounded-full sm:size-10" />@endif
                <div class="flex gap-3 overflow-hidden pb-2">
                    @foreach ($categories as $category)
                        <div class="flex w-full shrink-0 items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:w-[calc((100%_-_0.75rem)_/_2)] xl:w-[calc((100%_-_1.5rem)_/_3)]">
                            <x-skeleton.block class="size-11 shrink-0 rounded-xl" />
                            <div class="min-w-0 flex-1"><x-skeleton.text class="truncate font-semibold">{{ $category->name }}</x-skeleton.text><x-skeleton.text class="mt-0.5 text-sm">{{ $category->published_materials_count }} {{ $category->published_materials_count === 1 ? 'material publicado' : 'materiais publicados' }}</x-skeleton.text></div>
                        </div>
                    @endforeach
                </div>
                @if ($categories->count() > 3)<x-skeleton.block class="size-9 justify-self-center rounded-full sm:size-10" />@endif
            </div>
        @endif
    </div>
</section>
<div class="grid min-w-0 grid-cols-1 gap-8 xl:grid-cols-[minmax(0,3fr)_minmax(19rem,2fr)]">
    <section class="min-w-0">
        <div class="flex items-end justify-between gap-4"><x-skeleton.text class="text-2xl font-bold tracking-tight">Materiais publicados recentemente</x-skeleton.text><x-skeleton.text class="shrink-0 text-sm font-semibold">Ver materiais</x-skeleton.text></div>
        <div class="mt-5">
            <div @class(['grid grid-cols-[2.25rem_minmax(0,1fr)_2.25rem] items-center gap-2 sm:block' => $publishedMaterials->count() > 1])>
                @if ($publishedMaterials->count() > 1)<x-skeleton.block class="size-9 justify-self-center rounded-full sm:hidden" />@endif
                <div class="flex gap-3 overflow-hidden pb-2 sm:grid sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:pb-0 lg:grid-cols-3">
                    @forelse ($publishedMaterials as $material)
                        <x-materials.card-skeleton variant="featured" :material="$material" :date="$material->published_at?->format('d/m/Y') ?? 'Data não informada'" :type="$material->type" />
                    @empty
                        <div class="w-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 sm:col-span-2 lg:col-span-3"><x-skeleton.block class="mx-auto size-10 rounded" /><x-skeleton.text class="mx-auto mt-3 w-fit font-semibold">Ainda não há materiais publicados.</x-skeleton.text></div>
                    @endforelse
                </div>
                @if ($publishedMaterials->count() > 1)<x-skeleton.block class="size-9 justify-self-center rounded-full sm:hidden" />@endif
            </div>
        </div>
    </section>
    <section class="flex min-w-0 flex-col">
        <x-skeleton.text class="w-fit text-2xl font-bold tracking-tight">Continue de onde parou</x-skeleton.text>
        <div class="mt-5 space-y-3 xl:grid xl:gap-3 xl:space-y-0">
            @forelse ($recentlyAccessedMaterials as $material)
                <article class="flex min-h-0 min-w-0 items-stretch overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:h-[111px]">
                    <x-skeleton.block class="h-24 w-[68px] shrink-0 xl:h-full xl:w-[78px]" />
                    <div class="flex min-w-0 flex-1 flex-col justify-center p-3"><x-skeleton.text class="truncate font-semibold">{{ $material->title }}</x-skeleton.text><x-skeleton.text class="mt-2 w-fit text-sm font-semibold">Abrir material →</x-skeleton.text></div>
                </article>
            @empty
                <div class="rounded-xl bg-slate-50 px-4 py-8"><x-skeleton.text class="text-center text-sm">Os materiais acessados ou baixados aparecerão aqui.</x-skeleton.text></div>
            @endforelse
        </div>
    </section>
</div>
