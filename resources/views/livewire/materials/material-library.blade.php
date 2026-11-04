<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Materiais publicados</h1>
        </div>

        {{-- Filtros para localizar materiais publicados. --}}
        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-3">
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
            <x-form.select id="material-category-filter" label="Categoria" wire:model.live="categoryFilter">
                <option value="">Todas as categorias</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-form.select>
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

        {{-- Cartões no celular evitam que a tabela exija rolagem horizontal. --}}
        <div class="space-y-3 md:hidden">
            @forelse ($materials as $material)
                <article wire:key="published-material-mobile-{{ $material->id }}"
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex gap-4">
                        @if ($material->currentVersion)
                            <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
                                :alt="'Prévia de ' . $material->title" fit="contain"
                                class="h-24 w-16 shrink-0 !aspect-auto rounded-lg" />
                        @else
                            <div class="flex h-24 w-16 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400"
                                aria-label="Prévia indisponível">
                                <x-ts-icon icon="document" outline class="size-6" />
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h2 class="break-words text-base font-semibold leading-6 text-slate-900">
                                {{ $material->title }}
                            </h2>
                            <dl class="mt-3 space-y-1 text-sm">
                                <div>
                                    <dt class="inline font-medium text-slate-500">Categoria:</dt>
                                    <dd class="inline text-slate-700">{{ $material->category->name }}</dd>
                                </div>
                                <div>
                                    <dt class="inline font-medium text-slate-500">Autor:</dt>
                                    <dd class="inline text-slate-700">{{ $material->author }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        @if ($isTeacher)
                            <x-ts-button :color="$material->is_favorited ? 'yellow' : 'blue'"
                                wire:click.stop="toggleFavorite({{ $material->id }})"
                                :aria-label="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                :title="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                wire:loading.attr="disabled" wire:target="toggleFavorite"
                                class="!size-11 !p-0 shrink-0 justify-center">
                                @if ($material->is_favorited)
                                    <x-ts-icon icon="bookmark" solid class="size-6 text-amber-950" />
                                @else
                                    <x-ts-icon icon="bookmark" outline class="size-6 text-white" />
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
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-10 text-center text-slate-600">
                    <span class="text-base">Nenhum material publicado encontrado.</span>
                </div>
            @endforelse
        </div>

        {{-- A tabela completa permanece disponível a partir de telas médias. --}}
        <div class="hidden md:block">
            <x-data-table :columns="[
                ['label' => 'Prévia', 'nowrap' => true],
                ['label' => 'Título', 'sortAction' => 'toggleTitleSort', 'sortDirection' => $titleSortDirection],
                ['label' => 'Categoria'],
                ['label' => 'Autor'],
                ['label' => 'Ação', 'align' => 'right'],
            ]">
            @forelse ($materials as $material)
                <tr wire:key="published-material-{{ $material->id }}">
                    <td class="w-24 px-5 py-3">
                        @if ($material->currentVersion)
                            <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
                                :alt="'Prévia de ' . $material->title" fit="contain"
                                class="h-20 w-14 !aspect-auto rounded-lg" />
                        @else
                            <div class="flex h-20 w-14 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400"
                                aria-label="Prévia indisponível">
                                <x-ts-icon icon="document" outline class="size-6" />
                            </div>
                        @endif
                    </td>
                    <td class="px-8 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
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
                <x-data-table.empty-state :colspan="5">
                    <span class="text-lg">Nenhum material publicado encontrado.</span>
                </x-data-table.empty-state>
            @endforelse
            </x-data-table>
        </div>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
