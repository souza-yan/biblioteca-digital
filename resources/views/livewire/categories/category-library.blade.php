<div>
    @php($isStaff = auth()->user()?->isStaff() ?? false)
    <x-skeleton.gate name="category-library" label="Carregando categorias">
        <x-slot:skeleton>
            @include('livewire.categories.category-library-skeleton')
        </x-slot:skeleton>

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

            <x-skeleton.gate name="category-library-pages" label="Carregando categorias" :pagination-page="$categories->getPageName()" :initial="false">
                <x-slot:skeleton>
                    @if ($isStaff)
                        <div class="grid grid-cols-2 gap-3 md:hidden">
                            @foreach ($categoryCards as $category)
                                <x-categories.library-card-skeleton :category="$category" compact />
                            @endforeach
                        </div>
                    @endif
                    <div @class(['grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3', 'hidden md:grid' => $isStaff])>
                        @forelse ($categoryCards as $category)
                            <x-categories.library-card-skeleton :category="$category" />
                        @empty
                            <x-skeleton.line class="w-60" />
                        @endforelse
                    </div>
                </x-slot:skeleton>
                <div class="space-y-5">
            @if ($isStaff)
                <div class="grid grid-cols-2 gap-3 md:hidden">
                    @forelse ($categoryCards as $category)
                        <article wire:key="category-library-mobile-{{ $category->id ?? 'uncategorized' }}"
                            class="flex min-h-40 min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <a href="{{ route('painel.library', ['categoryFilter' => $category->getKey() ?? 'uncategorized']) }}"
                                class="flex min-h-0 min-w-0 flex-1 flex-col rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
                                    <x-ts-icon icon="folder" outline class="size-5" />
                                </span>
                                <span class="mt-3 line-clamp-2 break-words text-sm font-semibold leading-5 text-slate-900">{{ $category->name }}</span>
                                <span class="mt-auto pt-3 text-xs font-medium text-slate-500">
                                    {{ $category->published_materials_count }}
                                    {{ $category->published_materials_count === 1 ? 'material' : 'materiais' }}
                                </span>
                            </a>
                        </article>
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
                @forelse ($categoryCards as $category)
                    <a
                        wire:key="category-library-{{ $category->id ?? 'uncategorized' }}"
                        href="{{ route('painel.library', ['categoryFilter' => $category->getKey() ?? 'uncategorized']) }}"
                        class="group flex min-h-48 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md sm:p-6"
                    >
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7.5A1.5 1.5 0 0 1 4.5 6H10l2 2h7.5A1.5 1.5 0 0 1 21 9.5v8a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-10Z" />
                            </svg>
                        </span>
                        <span class="mt-4 block text-xl font-semibold text-black group-hover:text-blue-900">
                            {{ $category->name }}
                        </span>
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
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center md:col-span-2 xl:col-span-3">
                        <p class="text-lg font-semibold text-black">Nenhuma categoria encontrada.</p>
                        <p class="mt-2 text-base text-black">
                            Tente outro termo de pesquisa ou volte mais tarde para consultar novas categorias.
                        </p>
                    </div>
                @endforelse
            </div>

                </div>
            </x-skeleton.gate>

            @if ($categories->hasPages())
                <x-pagination :paginator="$categories" />
            @endif
        </section>

        @can('create', \App\Models\Category::class)
            <x-ts-modal wire="showCategoryForm" title="Nova categoria" size="xl" center>
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
                <form id="material-library-form" wire:submit="saveMaterial" class="min-w-0 space-y-4 pb-1"
                    x-data="{ uploading: false }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-cancel="uploading = false"
                    x-on:submit.capture="if (uploading) { $event.preventDefault(); $event.stopImmediatePropagation(); }">
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
                        <x-ts-button color="emerald" type="submit" form="material-library-form" wire:loading.attr="disabled" wire:target="saveMaterial,materialForm.file" class="w-full justify-center sm:w-auto">
                            <span wire:loading.remove wire:target="saveMaterial">Salvar material</span>
                            <span wire:loading wire:target="saveMaterial">Salvando...</span>
                        </x-ts-button>
                    </div>
                </x-slot:footer>
            </x-ts-modal>
        @endcan
    </div>

    </x-skeleton.gate>
</div>
