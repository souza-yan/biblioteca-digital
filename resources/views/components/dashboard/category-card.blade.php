@props(['category', 'icon'])

<a href="{{ route('painel.library', ['categoryFilter' => $category->getKey()]) }}"
    {{ $attributes->class('group flex w-full shrink-0 snap-start items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50 sm:w-[calc((100%_-_0.75rem)_/_2)] xl:w-[calc((100%_-_1.5rem)_/_3)]') }}>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
        <x-ts-icon :icon="$icon" outline class="size-6" />
    </span>
    <span class="min-w-0 flex-1">
        <span class="block truncate font-semibold text-slate-800 group-hover:text-blue-800">{{ $category->name }}</span>
        <span class="mt-0.5 block text-sm text-slate-500">
            {{ $category->published_materials_count }}
            {{ $category->published_materials_count === 1 ? 'material publicado' : 'materiais publicados' }}
        </span>
    </span>
</a>
