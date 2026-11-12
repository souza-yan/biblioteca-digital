@props(['category', 'compact' => false])

@if ($compact)
    <article class="flex min-h-40 min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex min-h-0 min-w-0 flex-1 flex-col">
            <x-skeleton.block class="size-10 rounded-xl" />
            <x-skeleton.text class="mt-3 line-clamp-2 break-words text-sm font-semibold leading-5">{{ $category->name }}</x-skeleton.text>
            <div class="mt-auto pt-3"><x-skeleton.text class="w-fit text-xs font-medium">{{ $category->published_materials_count }} {{ $category->published_materials_count === 1 ? 'material' : 'materiais' }}</x-skeleton.text></div>
        </div>
    </article>
@else
    <article class="flex min-h-48 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <x-skeleton.block class="size-12 rounded-2xl" />
        <x-skeleton.text class="mt-4 text-xl font-semibold">{{ $category->name }}</x-skeleton.text>
        <div class="mt-2 flex-1"><x-skeleton.text class="text-base leading-6">{{ $category->description ?: 'Consulte os materiais publicados nesta categoria.' }}</x-skeleton.text></div>
        <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <x-skeleton.text class="text-base font-medium">{{ $category->published_materials_count }} {{ $category->published_materials_count === 1 ? 'material publicado' : 'materiais publicados' }}</x-skeleton.text>
            <x-skeleton.text class="text-base font-semibold">Ver materiais →</x-skeleton.text>
        </div>
    </article>
@endif
