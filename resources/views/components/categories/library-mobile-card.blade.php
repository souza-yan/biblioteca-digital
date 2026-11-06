@props(['category'])

<article {{ $attributes->class('flex min-h-40 min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm') }}>
    <a href="{{ route('painel.library', ['categoryFilter' => $category->getKey()]) }}"
        class="flex min-h-0 flex-1 flex-col rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600">
        <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
            <x-ts-icon icon="folder" outline class="size-5" />
        </span>
        <span class="mt-3 line-clamp-2 text-sm font-semibold leading-5 text-slate-900">{{ $category->name }}</span>
        <span class="mt-auto pt-3 text-xs font-medium text-slate-500">
            {{ $category->published_materials_count }}
            {{ $category->published_materials_count === 1 ? 'material' : 'materiais' }}
        </span>
    </a>

    @isset($actions)
        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">{{ $actions }}</div>
    @endisset
</article>
