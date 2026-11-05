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
        <div @class([
            'grid md:hidden',
            'grid-cols-2 gap-3' => $isTeacher,
            'grid-cols-1 gap-3' => ! $isTeacher,
        ])>
            @forelse ($materials as $material)
                <article wire:key="published-material-mobile-{{ $material->id }}" @class([
                    'flex min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm',
                    'flex-col p-3' => $isTeacher,
                    'gap-4 p-4' => ! $isTeacher,
                ])>
                    @if ($material->currentVersion)
                        <x-materials.pdf-cover :material="$material" :version="$material->currentVersion"
                            :alt="'Prévia de ' . $material->title" fit="contain" @class([
                                'w-full rounded-lg' => $isTeacher,
                                'h-32 w-24 shrink-0 !aspect-auto rounded-lg' => ! $isTeacher,
                            ]) />
                    @else
                        <div @class([
                            'flex items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-400',
                            'aspect-[2480/3508] w-full' => $isTeacher,
                            'h-32 w-24 shrink-0' => ! $isTeacher,
                        ])
                            aria-label="Prévia indisponível">
                            <x-ts-icon icon="document" outline class="size-6" />
                        </div>
                    @endif

                    <div @class([
                        'min-w-0',
                        'pt-3' => $isTeacher,
                        'flex min-w-0 flex-1 flex-col py-1' => ! $isTeacher,
                    ])>
                        <h2 class="break-words text-sm font-semibold leading-5 text-slate-900">
                            {{ $material->title }}
                        </h2>
                        <p class="mt-1 break-words text-xs text-slate-600">{{ $material->category->name }}</p>
                    </div>

                    <div @class([
                        'mt-3 flex items-center border-t border-slate-100 pt-3',
                        'justify-end gap-1.5' => $isTeacher,
                        'mt-auto' => ! $isTeacher,
                    ])>
                        @if ($isTeacher)
                            <x-ts-button :color="$material->is_favorited ? 'yellow' : 'blue'"
                                wire:click.stop="toggleFavorite({{ $material->id }})"
                                :aria-label="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                :title="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                wire:loading.attr="disabled" wire:target="toggleFavorite"
                                class="!size-9 !p-0 shrink-0 justify-center">
                                @if ($material->is_favorited)
                                    <x-ts-icon icon="bookmark" solid class="size-5 text-amber-950" />
                                @else
                                    <x-ts-icon icon="bookmark" outline class="size-5 text-white" />
                                @endif
                            </x-ts-button>
                        @endif

                        @if ($isTeacher)
                            <x-icon-button icon="eye" wire:click="openMobileMaterialDetail({{ $material->id }})"
                                wire:loading.attr="disabled"
                                wire:target="openMobileMaterialDetail({{ $material->id }})"
                                label="Ver detalhes de {{ $material->title }}" class="!size-9 [&_svg]:!size-5" />
                        @else
                            <x-ts-button color="blue" wire:click="openMobileMaterialDetail({{ $material->id }})"
                                wire:loading.attr="disabled"
                                wire:target="openMobileMaterialDetail({{ $material->id }})"
                                class="w-full justify-center">
                                <x-ts-icon icon="eye" outline class="size-5" />
                                Ver detalhes
                            </x-ts-button>
                        @endif
                    </div>
                </article>
            @empty
                <div @class([
                    'rounded-2xl border border-slate-200 bg-white px-4 py-10 text-center text-slate-600',
                    'col-span-2' => $isTeacher,
                ])>
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

        <x-ts-modal wire="showMobileMaterialDetail" size="lg" center
            x-on:mobile-material-preview-closed.window="show = true">
            @include('livewire.materials.partials.mobile-material-detail', [
                'material' => $mobileMaterial,
                'previewUrl' => $mobilePreviewUrl,
            ])

            <x-slot:footer>
                <div class="flex w-full items-center justify-between">
                    @if ($mobileMaterial?->currentVersion && $mobilePreviewUrl)
                        <button type="button" x-on:click="$dispatch('mobile-material-preview')"
                            aria-label="Visualizar conteúdo" title="Visualizar conteúdo"
                            class="inline-flex size-11 items-center justify-center rounded-lg bg-blue-800 text-white shadow-sm transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                            <x-ts-icon icon="magnifying-glass" outline class="size-6" />
                        </button>
                    @else
                        <span></span>
                    @endif
                    @if ($mobileMaterial?->currentVersion)
                        <x-icon-button icon="document-arrow-down" variant="success"
                            :href="route('downloads.show', $mobileMaterial)"
                            label="Baixar {{ $mobileMaterial->title }}" />
                    @endif
                </div>
            </x-slot:footer>
        </x-ts-modal>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
