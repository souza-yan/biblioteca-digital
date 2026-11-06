@props(['category'])

<a href="{{ route('painel.library', ['categoryFilter' => $category->getKey()]) }}"
    {{ $attributes->class('group flex min-h-48 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md sm:p-6') }}>
    <span class="flex size-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />
        </svg>
    </span>
    <span class="mt-4 block text-xl font-semibold text-black group-hover:text-blue-900">{{ $category->name }}</span>
    <span class="mt-2 flex-1 text-base leading-6 text-black">
        {{ $category->description ?: 'Consulte os materiais publicados nesta categoria.' }}
    </span>
    <span class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
        <span class="text-base font-medium text-black">
            {{ $category->published_materials_count }}
            {{ $category->published_materials_count === 1 ? 'material publicado' : 'materiais publicados' }}
        </span>
        <span class="text-base font-semibold text-blue-700 group-hover:text-blue-900">
            Ver materiais <span aria-hidden="true">→</span>
        </span>
    </span>
</a>
