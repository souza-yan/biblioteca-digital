<div>
    @php($isStaff = auth()->user()?->isStaff() ?? false)
    @php($listLoadingTargets = 'search,setPage,previousPage,nextPage,gotoPage')

    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <section @class([
            'relative isolate overflow-hidden rounded-2xl bg-blue-800 text-white shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12',
            'px-5 py-6' => $isStaff,
            'px-6 py-8' => ! $isStaff,
        ])
            aria-labelledby="category-library-title">
            <p @class([
                'font-bold uppercase tracking-[0.18em] text-blue-200',
                'text-sm' => $isStaff,
                'text-base' => ! $isStaff,
            ])>Biblioteca digital</p>
            <h1 id="category-library-title" @class([
                'font-bold leading-tight tracking-tight sm:text-5xl',
                'mt-2 text-3xl' => $isStaff,
                'mt-3 text-4xl' => ! $isStaff,
            ])>
                Materiais
            </h1>
            <p @class([
                'max-w-2xl leading-7 text-blue-100 sm:text-xl',
                'mt-3 hidden text-base sm:block' => $isStaff,
                'mt-4 text-lg' => ! $isStaff,
            ])>
                Explore os temas e encontre materiais publicados para cada categoria.
            </p>
        </section>

        <section class="space-y-5" aria-labelledby="available-categories-title">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div @class(['hidden sm:block' => $isStaff])>
                    <p class="text-base font-semibold uppercase tracking-[0.14em] text-blue-700">Acervo</p>
                    <h2 id="available-categories-title" class="mt-1 text-2xl font-bold text-black">
                        Explore por categoria
                    </h2>
                </div>
                <div class="flex w-full flex-col gap-3 sm:max-w-2xl sm:flex-row sm:items-end">
                    <div class="w-full sm:flex-1">
                        <x-search-field
                            model="search"
                            label="Pesquisar categorias"
                            placeholder="Nome ou descrição"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0">
                        @can('create', \App\Models\Category::class)
                            <x-ts-button color="blue" wire:click="openCategoryCreate" class="w-full justify-center sm:w-auto">
                                <x-ts-icon icon="folder-plus" outline class="size-5" />
                                Nova categoria
                            </x-ts-button>
                        @endcan
                        @can('create', \App\Models\Material::class)
                            <x-ts-button color="emerald" wire:click="openMaterialCreate" class="w-full justify-center sm:w-auto">
                                <x-ts-icon icon="document-plus" outline class="size-5" />
                                Novo material
                            </x-ts-button>
                        @endcan
                    </div>
                </div>
            </div>

            <div wire:loading.block wire:target="{{ $listLoadingTargets }}" role="status" aria-busy="true"
                class="hidden">
                @if ($isStaff)
                    <div class="grid grid-cols-2 gap-3 md:hidden">
                        @foreach (range(1, 6) as $card)
                            <x-categories.library-mobile-card-skeleton />
                        @endforeach
                    </div>
                @endif

                <div @class([
                    'grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3',
                    'hidden md:grid' => $isStaff,
                ])>
                    @foreach (range(1, 6) as $card)
                        <x-categories.library-card-skeleton />
                    @endforeach
                </div>
                <span class="sr-only">Carregando…</span>
            </div>

            <div wire:loading.remove wire:target="{{ $listLoadingTargets }}" aria-busy="false">
                @if ($isStaff)
                    <div class="grid grid-cols-2 gap-3 md:hidden">
                        @forelse ($categories as $category)
                            <x-categories.library-mobile-card wire:key="category-library-mobile-{{ $category->id }}" :category="$category">
                                @can('update', $category)
                                    <x-slot:actions>
                                        <x-categories.status-badge :active="$category->is_active" />
                                        <div class="flex gap-2">
                                            <x-ts-button color="blue" wire:click="editCategory({{ $category->id }})"
                                                aria-label="Editar categoria {{ $category->name }}" title="Editar categoria"
                                                class="!size-9 !p-0 justify-center">
                                                <x-ts-icon icon="pencil-square" outline class="size-5 text-white" />
                                            </x-ts-button>
                                            <x-ts-button :color="$category->is_active ? 'red' : 'emerald'"
                                                wire:click="toggleCategoryActive({{ $category->id }})"
                                                wire:loading.attr="disabled" wire:target="toggleCategoryActive({{ $category->id }})"
                                                :aria-label="$category->is_active ? 'Desativar categoria '.$category->name : 'Ativar categoria '.$category->name"
                                                :title="$category->is_active ? 'Desativar categoria' : 'Ativar categoria'"
                                                class="!size-9 !p-0 justify-center">
                                                <x-ts-icon :icon="$category->is_active ? 'no-symbol' : 'check-circle'" outline class="size-5 text-white" />
                                            </x-ts-button>
                                        </div>
                                    </x-slot:actions>
                                @endcan
                            </x-categories.library-mobile-card>
                        @empty
                            <div class="col-span-2 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-600">
                                Nenhuma categoria encontrada.
                            </div>
                        @endforelse
                    </div>
                @endif

                <div @class([
                    'grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3',
                    'hidden md:grid' => $isStaff,
                ])>
                    @forelse ($categories as $category)
                        <x-categories.library-card wire:key="category-library-{{ $category->id }}" :category="$category" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center md:col-span-2 xl:col-span-3">
                            <p class="text-lg font-semibold text-black">Nenhuma categoria encontrada.</p>
                            <p class="mt-2 text-base text-black">
                                Tente outro termo de pesquisa ou volte mais tarde para consultar novas categorias.
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($categories->hasPages())
                    <x-pagination :paginator="$categories" />
                @endif
            </div>
        </section>

        @can('create', \App\Models\Category::class)
            <x-ts-modal wire="showCategoryForm" :title="$editingCategoryId === null ? 'Nova categoria' : 'Editar categoria'" size="xl" center>
                <form id="category-library-form" wire:submit="saveCategory" class="space-y-4">
                    <x-ts-input wire:model="categoryForm.name" label="Nome *" required />
                    <x-ts-input wire:model="categoryForm.description" label="Descrição" />

                    @error('categoryForm.slug')
                        <p class="text-base text-red-600">{{ $message }}</p>
                    @enderror
                </form>

                <x-slot:footer>
                    <x-ts-button color="red" type="button" wire:click="closeCategoryForm">Cancelar</x-ts-button>
                    <x-ts-button color="blue" type="submit" form="category-library-form" wire:loading.attr="disabled" wire:target="saveCategory">
                        Salvar categoria
                    </x-ts-button>
                </x-slot:footer>
            </x-ts-modal>
        @endcan

        @can('create', \App\Models\Material::class)
            <x-ts-modal wire="showMaterialForm" title="Novo material" size="2xl" center>
                <form id="material-library-form" wire:submit="saveMaterial" class="min-w-0 space-y-4 pb-1">
                    <x-ts-input wire:model="materialForm.title" label="Título *" required />
                    <x-ts-input wire:model="materialForm.description" label="Descrição" />
                    <x-ts-select.styled wire:key="material-category-select-{{ $activeCategories->pluck('id')->join('-') }}"
                        wire:model="materialForm.category_id" label="Categoria *"
                        placeholder="Busque e selecione uma categoria" searchable :options="$activeCategories
                            ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                            ->all()"
                        select="label:label|value:value" required />
                    <x-ts-input wire:model="materialForm.author" label="Autor *" required />
                    <x-form.select id="category-library-material-status" label="Status inicial" :required="true"
                        error="materialForm.initialStatus" wire:model="materialForm.initialStatus">
                        @foreach ($creationStatuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </x-form.select>
                    <x-ts-input wire:model="materialForm.change_note" label="Nota da versão" />
                    <x-materials.file-upload id="category-library-material-file" model="materialForm.file" :file="$materialForm->file" />
                </form>

                <x-slot:footer>
                    <div class="grid w-full grid-cols-2 gap-3 sm:flex sm:justify-end">
                        <x-ts-button color="red" type="button" wire:click="closeMaterialForm" class="w-full justify-center sm:w-auto">Cancelar</x-ts-button>
                        <x-ts-button color="emerald" type="submit" form="material-library-form" wire:loading.attr="disabled" wire:target="saveMaterial" class="w-full justify-center sm:w-auto">
                            <span wire:loading.remove wire:target="saveMaterial">Salvar material</span>
                            <span wire:loading wire:target="saveMaterial">Salvando...</span>
                        </x-ts-button>
                    </div>
                </x-slot:footer>
            </x-ts-modal>
        @endcan
    </div>

</div>
