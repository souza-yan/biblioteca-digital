<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <a href="{{ route('painel.library.categories') }}" class="inline-flex items-center gap-2 rounded text-sm font-semibold text-blue-800 hover:underline focus-visible:outline-2 focus-visible:outline-blue-600">
                <span aria-hidden="true">←</span> Materiais por categoria
            </a>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Materiais publicados</h1>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3 items-end">
            <x-search-field model="search" label="Buscar material" placeholder="Título, autor ou descrição" />
            <x-ts-select.styled wire:model.live="categoryFilter" label="Categoria" placeholder="Todas as categorias"
                :options="$categories
                    ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                    ->all()" select="label:label|value:value" />
            <div>
                <label for="material-sort-order" class="block text-sm font-semibold text-black mb-1">
                    Ordenar por
                </label>
                <select id="material-sort-order" wire:model.live="sortOrder"
                    class="block w-full rounded-lg border-slate-300 text-base shadow-sm py-2.5 px-3 focus:border-blue-500 focus:ring-blue-500">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-data-table :columns="[
            ['label' => 'Título'],
            ['label' => 'Categoria'],
            ['label' => 'Autor'],
            ['label' => 'Ação', 'align' => 'right'],
        ]">
            @forelse ($materials as $material)
                <tr wire:key="published-material-{{ $material->id }}">
                    <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->author }}</td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex flex-row flex-nowrap items-center justify-end gap-2">
                            @if ($isTeacher)
                                <x-ts-button :color="$material->is_favorited ? 'yellow' : 'slate'"
                                    wire:click.stop="toggleFavorite({{ $material->id }})"
                                    :aria-label="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                    :title="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                    wire:loading.attr="disabled" wire:target="toggleFavorite"
                                    class="!size-12 !p-0 shrink-0 justify-center">
                                    @if ($material->is_favorited)
                                        <x-ts-icon icon="bookmark" solid class="size-7 text-amber-950" />
                                    @else
                                        <x-ts-icon icon="bookmark" outline class="size-7 text-slate-200" />
                                    @endif
                                </x-ts-button>
                            @endif

                            @if ($material->currentVersion)
                                <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                                    aria-label="Baixar {{ $material->title }}" title="Baixar material"
                                    class="!size-12 !p-0 shrink-0 justify-center">
                                    <x-ts-icon icon="document-arrow-down" outline class="size-7 text-white" />
                                </x-ts-button>
                            @endif

                            <x-ts-button color="blue" href="{{ route('painel.library.show', $material) }}"
                                aria-label="Ver detalhes de {{ $material->title }}" title="Ver detalhes"
                                class="!size-12 !p-0 shrink-0 justify-center">
                                <x-ts-icon icon="eye" outline class="size-7 text-white" />
                            </x-ts-button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="4">
                    <span class="text-lg">Nenhum material publicado encontrado.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
