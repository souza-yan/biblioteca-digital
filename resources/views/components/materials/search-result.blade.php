@props(['material'])

<a href="{{ route('painel.library.show', $material) }}" role="option"
    {{ $attributes->class('group flex min-w-0 items-center gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-blue-50 focus:bg-blue-50 focus:outline-none sm:px-5 sm:odd:border-r') }}>
    <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
        :alt="'Prévia de '.$material->title" fit="contain"
        class="h-16 w-11 shrink-0 !aspect-auto rounded-lg" />
    <span class="min-w-0">
        <span class="block truncate font-semibold text-slate-900 group-hover:text-blue-800">{{ $material->title }}</span>
        <span class="mt-1 block truncate text-sm text-slate-500">{{ $material->category->name }}</span>
    </span>
</a>
