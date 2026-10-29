<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Materiais publicados</h1>
        </div>

        {{-- Busca por material ou autor; a categoria é definida pela tela anterior. --}}
        <div @class([
            'grid grid-cols-1 items-end gap-4 md:grid-cols-2',
            'xl:grid-cols-3' => $isStaff,
        ])>
            <x-search-field model="search" label="Buscar material" placeholder="Título ou autor"
                list="material-search-suggestions" />
            <datalist id="material-search-suggestions">
                @foreach ($searchSuggestions as $suggestion)
                    <option value="{{ $suggestion }}"></option>
                @endforeach
            </datalist>
            <x-form.select id="material-author-filter" label="Autor" wire:model.live="authorFilter">
                <option value="">Todos os autores</option>
                @foreach ($authors as $author)
                    <option value="{{ $author }}">{{ $author }}</option>
                @endforeach
            </x-form.select>
            @if ($isStaff)
                <x-form.select id="material-category-filter" label="Categoria" wire:model.live="categoryFilter">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-form.select>
            @endif
        </div>

        @if ($recentSearches !== [])
            <div class="flex flex-wrap items-center gap-2" aria-label="Filtros e pesquisas recentes">
                @foreach ($recentSearches as $recentSearch)
                    <button type="button" wire:key="recent-search-{{ md5($recentSearch) }}"
                        wire:click="applyRecentSearch(@js($recentSearch))"
                        class="inline-flex h-9 items-center gap-2 rounded-full bg-slate-100 px-3 text-sm text-slate-700 transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <x-ts-icon icon="clock" outline class="size-4" />
                        {{ $recentSearch }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Resultados e ações disponíveis para cada material publicado. --}}
        <x-data-table :columns="[
            ['label' => 'Título', 'sortAction' => 'toggleTitleSort', 'sortDirection' => $titleSortDirection],
            ['label' => 'Categoria'],
            ['label' => 'Autor'],
            ['label' => 'Ação', 'align' => 'right'],
        ]">
            <x-data-table.loading :colspan="4" />
            @forelse ($materials as $material)
                <tr wire:key="published-material-{{ $material->id }}">
                    <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->author }}</td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex flex-row flex-nowrap items-center justify-end gap-2">
                            @if ($isTeacher)
                                <x-ts-button :color="$material->is_favorited ? 'yellow' : 'blue'"
                                    wire:click.stop="toggleFavorite({{ $material->id }})"
                                    :aria-label="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                    :title="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                    wire:loading.attr="disabled" wire:target="toggleFavorite"
                                    class="!size-12 !p-0 shrink-0 justify-center">
                                    @if ($material->is_favorited)
                                        <x-ts-icon icon="bookmark" solid class="size-7 text-amber-950" />
                                    @else
                                        <x-ts-icon icon="bookmark" outline class="size-7 text-white" />
                                    @endif
                                </x-ts-button>
                            @endif

                            @if ($material->currentVersion)
                                <x-icon-button icon="document-arrow-down" variant="success"
                                    :href="route('downloads.show', $material)"
                                    label="Baixar {{ $material->title }}" />
                            @endif

                            <x-icon-button icon="eye"
                                :href="route('painel.library.show', [
                                    'material' => $material,
                                    'categoryFilter' => $categoryFilter ?: null,
                                ])"
                                label="Ver detalhes de {{ $material->title }}" />
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
