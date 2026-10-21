<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <section
            class="relative isolate overflow-hidden rounded-2xl bg-blue-800 px-6 py-8 text-white shadow-sm sm:px-9 sm:py-10 lg:px-12 lg:py-12"
            aria-labelledby="category-library-title">
            <p class="text-base font-bold uppercase tracking-[0.18em] text-blue-200">Biblioteca digital</p>
            <h1 id="category-library-title" class="mt-3 text-4xl font-bold leading-tight tracking-tight sm:text-5xl">
                Materiais
            </h1>
            <p class="mt-4 max-w-2xl text-lg leading-7 text-blue-100 sm:text-xl">
                Explore os temas e encontre materiais publicados para cada categoria.
            </p>
        </section>

        <section class="space-y-5" aria-labelledby="available-categories-title">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-base font-semibold uppercase tracking-[0.14em] text-blue-700">Acervo</p>
                    <h2 id="available-categories-title" class="mt-1 text-2xl font-bold text-black">
                        Explore por categoria
                    </h2>
                </div>
                <div class="flex w-full flex-col gap-3 sm:max-w-2xl sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <x-search-field
                            model="search"
                            label="Pesquisar categorias"
                            placeholder="Nome ou descrição"
                        />
                    </div>
                    @if ($isStaff)
                        <div class="flex gap-2">
                            <x-ts-button color="slate" wire:click="openCategoryForm">
                                <x-ts-icon icon="folder-plus" outline class="size-5" />
                                Nova categoria
                            </x-ts-button>
                            <x-ts-button color="blue" wire:click="openMaterialForm">
                                <x-ts-icon icon="plus" outline class="size-5" />
                                Novo material
                            </x-ts-button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($categories as $category)
                    <a
                        wire:key="category-library-{{ $category->id }}"
                        href="{{ route('painel.library', ['categoryFilter' => $category->getKey()]) }}"
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

            @if ($categories->hasPages())
                <x-pagination :paginator="$categories" />
            @endif
        </section>
    </div>

    @if ($showCategoryForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
            <section role="dialog" aria-modal="true" aria-labelledby="library-category-form-title"
                class="w-full max-w-xl rounded-md bg-white p-6 shadow-xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <h2 id="library-category-form-title" class="text-2xl font-bold text-slate-900">Nova categoria</h2>
                    <button type="button" wire:click="closeCategoryForm" aria-label="Fechar formulário"
                        class="rounded p-1 text-xl text-gray-500 hover:bg-gray-100">&times;</button>
                </div>

                <form wire:submit="saveCategory" class="space-y-4">
                    <x-ts-input wire:model="categoryForm.name" label="Nome *" required />
                    <x-ts-input wire:model="categoryForm.description" label="Descrição" />
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <span class="font-semibold">Enviada como inativa</span>
                        <span class="mt-1 block">A categoria ficará aguardando revisão e ativação do administrador.</span>
                    </div>
                    <div class="flex justify-end border-t border-gray-100 pt-4">
                        <x-ts-button color="blue" type="submit" wire:loading.attr="disabled" wire:target="saveCategory">
                            <span wire:loading.remove wire:target="saveCategory">Salvar categoria</span>
                            <span wire:loading wire:target="saveCategory">Salvando...</span>
                        </x-ts-button>
                    </div>
                </form>
            </section>
        </div>
    @endif

    @if ($showMaterialForm)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
            <section role="dialog" aria-modal="true" aria-labelledby="library-material-form-title"
                class="w-full max-w-2xl rounded-md bg-white p-6 shadow-xl">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <h2 id="library-material-form-title" class="text-2xl font-bold text-slate-900">Novo material</h2>
                    <button type="button" wire:click="closeMaterialForm" aria-label="Fechar formulário"
                        class="rounded p-1 text-xl text-gray-500 hover:bg-gray-100">&times;</button>
                </div>

                <form wire:submit="saveMaterial" class="space-y-4">
                    <x-ts-input wire:model="materialForm.title" label="Título *" required />
                    <x-ts-input wire:model="materialForm.description" label="Descrição" />
                    <x-ts-select.styled wire:model="materialForm.category_id" label="Categoria *"
                        placeholder="Selecione uma categoria ativa" :options="$activeCategories
                            ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                            ->all()"
                        select="label:label|value:value" required />
                    <x-ts-input wire:model="materialForm.author" label="Autor *" required />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <span class="font-semibold">Enviado como rascunho</span>
                            <span class="mt-1 block">O material ficará disponível para revisão do administrador.</span>
                        </div>
                        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                            <span class="font-semibold">Versão 1</span>
                            <span class="mt-1 block text-blue-700">Definida automaticamente pelo sistema.</span>
                        </div>
                    </div>

                    <x-ts-input wire:model="materialForm.change_note" label="Nota da versão (opcional)" />

                    <div>
                        <label for="library-material-file" class="mb-1 block text-base font-medium text-gray-700">
                            Arquivo <span aria-hidden="true" class="font-bold text-red-500">*</span>
                        </label>
                        <input id="library-material-file" type="file" wire:model="materialForm.file"
                            accept="{{ implode(',', array_map(fn($mime) => '.'.$mime, config('materials.upload.allowed_mimes'))) }}"
                            required
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-base file:font-semibold file:text-blue-700">
                        @error('materialForm.file')
                            <p class="mt-1 text-base text-red-600">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="materialForm.file" class="mt-2 text-base font-medium text-blue-700">
                            Enviando arquivo...
                        </div>
                        @if ($materialForm->file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                            <dl class="mt-3 grid grid-cols-1 gap-2 rounded-md bg-gray-50 p-3 text-base sm:grid-cols-2">
                                <div class="min-w-0">
                                    <dt class="font-medium text-gray-500">Nome</dt>
                                    <dd class="truncate text-gray-900">{{ $materialForm->file->getClientOriginalName() }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-gray-500">Tamanho</dt>
                                    <dd class="text-gray-900">{{ number_format($materialForm->file->getSize() / 1024, 1, ',', '.') }} KB</dd>
                                </div>
                            </dl>
                        @endif
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-4">
                        <x-ts-button color="blue" type="submit" wire:loading.attr="disabled" wire:target="saveMaterial">
                            <span wire:loading.remove wire:target="saveMaterial">Salvar material</span>
                            <span wire:loading wire:target="saveMaterial">Salvando...</span>
                        </x-ts-button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>
