@props(['material'])

<article {{ $attributes->class('flex min-h-0 min-w-0 items-stretch overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-blue-100 hover:bg-blue-50/40 xl:h-[111px]') }}>
    <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
        :alt="'Miniatura de '.$material->title" fit="contain"
        class="h-24 w-[68px] shrink-0 !aspect-auto !rounded-none !border-0 xl:h-full xl:w-[78px]" />
    <div class="flex min-w-0 flex-1 flex-col justify-center p-3">
        <p class="truncate font-semibold text-slate-800">{{ $material->title }}</p>
        <a href="{{ route('painel.library.show', $material) }}"
            class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-blue-800 hover:underline">
            Abrir material
            <x-ts-icon icon="arrow-right" outline class="size-4" />
        </a>
    </div>
</article>
