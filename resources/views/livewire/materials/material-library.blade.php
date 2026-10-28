<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <x-breadcrumb :items="[
                ['label' => 'Início', 'url' => route('dashboard')],
                ['label' => 'Materiais', 'url' => route('painel.library.categories')],
                ['label' => 'Materiais publicados'],
            ]" />
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Materiais publicados</h1>
        </div>

        {{-- Busca facetada: todos os valores ficam na URL para permitir compartilhar a consulta. --}}
        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-3">
            <x-search-field model="search" label="Buscar material" placeholder="Título, autor ou descrição"
                list="material-search-suggestions" />
            <datalist id="material-search-suggestions">
                @foreach ($searchSuggestions as $suggestion)
                    <option value="{{ $suggestion }}"></option>
                @endforeach
            </datalist>
            <x-form.select id="material-category-filter" label="Categoria" wire:model.live="categoryFilter">
                <option value="">Todas as categorias</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-form.select>
            <x-form.select id="material-format-filter" label="Formato" wire:model.live="formatFilter">
                <option value="">Todos os formatos</option>
                @foreach ($formats as $format)
                    <option value="{{ $format }}">{{ Str::upper($format) }}</option>
                @endforeach
            </x-form.select>
            <x-form.select id="material-author-filter" label="Autor" wire:model.live="authorFilter">
                <option value="">Todos os autores</option>
                @foreach ($authors as $author)
                    <option value="{{ $author }}">{{ $author }}</option>
                @endforeach
            </x-form.select>
            <x-form.select id="material-period-filter" label="Publicação" wire:model.live="publishedPeriod">
                @foreach ($periodOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select id="material-sort-order" label="Ordenar por" wire:model.live="sortOrder">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
            </x-form.select>
        </div>

        @if ($recentSearches !== [] || $hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2" aria-label="Filtros e pesquisas recentes">
                @if ($hasActiveFilters)
                    <button type="button" wire:click="clearFilters"
                        class="inline-flex h-9 items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <x-ts-icon icon="x-mark" outline class="size-4" />
                        Limpar filtros
                    </button>
                @endif

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
