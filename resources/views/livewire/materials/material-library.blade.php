<div>
    <x-skeleton.gate name="material-library" label="Carregando biblioteca de materiais">
        <x-slot:skeleton>
            @include('livewire.materials.material-library-skeleton')
        </x-slot:skeleton>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8 flex items-center justify-between gap-4">
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
                <option value="uncategorized">Sem categoria</option>
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

        <x-skeleton.gate name="published-material-pages" label="Carregando materiais" :pagination-page="$materials->getPageName()" :initial="false">
            <x-slot:skeleton>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @forelse ($materials as $material)
                        <x-materials.card-skeleton :material="$material" :actions="$isTeacher || auth()->user()->can('update', $material) ? 2 : 1" />
                    @empty
                        <x-skeleton.line class="w-60" />
                    @endforelse
                </div>
            </x-slot:skeleton>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($materials as $material)
                <x-materials.card :material="$material" :version="$material->currentVersion"
                    wire:key="published-material-{{ $material->id }}">
                    <x-slot:actions>
                        @if ($isTeacher)
                            <x-ts-button color="yellow"
                                wire:click.stop="toggleFavorite({{ $material->id }})"
                                :aria-label="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                :title="$material->is_favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                                wire:loading.attr="disabled" wire:target="toggleFavorite"
                                class="!size-11 !p-0 shrink-0 justify-center">
                                @if ($material->is_favorited)
                                    <x-ts-icon icon="bookmark" solid class="size-5 text-amber-950" />
                                @else
                                    <x-ts-icon icon="bookmark" outline class="size-5 text-amber-950" />
                                @endif
                            </x-ts-button>
                        @endif

                        @if (! $isTeacher)
                            @can('update', $material)
                                <x-icon-button icon="arrow-up-tray" variant="primary"
                                    :href="route('painel.library.show', ['material' => $material, 'categoryFilter' => $categoryFilter ?: null])"
                                    label="Gerenciar versões de {{ $material->title }}" class="!size-11 [&_svg]:!size-5" />
                            @endcan
                            <x-icon-button icon="eye" variant="success" wire:click="openMobileMaterialDetail({{ $material->id }})"
                                wire:loading.attr="disabled"
                                wire:target="openMobileMaterialDetail({{ $material->id }})"
                                label="Ver detalhes de {{ $material->title }}" class="!size-11 [&_svg]:!size-5" />
                        @else
                            <x-icon-button icon="eye" wire:click="openMobileMaterialDetail({{ $material->id }})"
                                wire:loading.attr="disabled"
                                wire:target="openMobileMaterialDetail({{ $material->id }})"
                                label="Ver detalhes de {{ $material->title }}" class="!size-11 [&_svg]:!size-5" />
                        @endif
                    </x-slot:actions>
                </x-materials.card>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-4 py-10 text-center text-slate-600">
                    <span class="text-base">Nenhum material publicado encontrado.</span>
                </div>
            @endforelse
        </div>

        </x-skeleton.gate>

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
    </x-skeleton.gate>
</div>
