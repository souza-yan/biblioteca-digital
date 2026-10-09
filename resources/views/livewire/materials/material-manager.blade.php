<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Materiais</h1>
            </div>
            <x-ts-button color="blue" wire:click="openCreate">
                <span class="text-white text-base">Novo material</span>
            </x-ts-button>
        </div>

        @error('publish')
            <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-base text-red-700">{{ $message }}</p>
        @enderror

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-search-field model="search" label="Buscar material" placeholder="Título ou autor" />
            <x-ts-select.styled wire:model.live="statusFilter" label="Status" :options="$statusOptions"
                select="label:label|value:value" required />
            <x-ts-select.styled wire:model.live="categoryFilter" label="Categoria" placeholder="Todas as categorias"
                :options="$categories
                    ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                    ->all()" select="label:label|value:value" />
        </div>

        <x-data-table :columns="[
            ['label' => 'Título', 'class' => 'text-lg'],
            ['label' => 'Categoria', 'class' => 'text-lg'],
            ['label' => 'Tipo', 'class' => 'text-lg'],
            ['label' => 'Status', 'class' => 'text-lg'],
            ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'],
        ]">
            @forelse ($materials as $material)
                <tr wire:key="material-{{ $material->id }}">
                    <td class="px-5 py-4 text-lg font-medium text-gray-900">
                        <a class="text-blue-700 hover:underline"
                            href="{{ route('painel.materials.show', $material) }}">{{ $material->title }}</a>
                    </td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->type }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->status->label() }}</td>
                    <td class="px-5 py-4 text-right text-lg">
                        <div class="flex flex-row flex-nowrap items-center justify-end gap-2">
                            <x-ts-button color="blue" wire:click="editMaterial({{ $material->id }})"
                                class="shrink-0 w-32 justify-center items-strech">
                                <span class="text-white text-base font-medium">Editar</span>
                            </x-ts-button>

                            @if ($material->status->value !== \App\Enums\MaterialStatus::PUBLISHED->value)
                                <x-ts-button color="emerald" wire:click="publish({{ $material->id }})"
                                    class="shrink-0 w-32 justify-center">
                                    <span class="text-white text-base font-medium">Publicar</span>
                                </x-ts-button>
                            @endif

                            @if ($material->status->value !== \App\Enums\MaterialStatus::ARCHIVED->value)
                                <x-ts-button color="yellow" wire:click="archive({{ $material->id }})"
                                    class="shrink-0 w-32 justify-center">
                                    <span class="text-white text-base font-medium">Arquivar</span>
                                </x-ts-button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="5">
                    <span class="text-lg">Nenhum material encontrado.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$materials" class="mt-4" />

        @if ($showForm)
            <div
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                <section role="dialog" aria-modal="true" aria-labelledby="material-form-title"
                    class="w-full max-w-2xl rounded-md bg-white p-6 shadow-xl">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <h2 id="material-form-title" class="text-2xl font-bold text-slate-900">
                            {{ $editingMaterialId === null ? 'Novo material' : 'Editar material' }}
                        </h2>
                        <button type="button" wire:click="closeForm" aria-label="Fechar formulário"
                            class="rounded p-1 text-gray-500 hover:bg-gray-100 text-xl">&times;</button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <x-ts-input wire:model="form.title" label="Título" />
                        <x-ts-input wire:model="form.description" label="Descrição" />
                        <x-ts-select.styled wire:model="form.category_id" label="Categoria"
                            placeholder="Selecione uma categoria ativa" :options="$activeCategories
                                ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                                ->all()"
                            select="label:label|value:value" required />
                        <x-ts-input wire:model="form.type" label="Tipo" placeholder="Ex.: PDF" />
                        <x-ts-input wire:model="form.author" label="Autor" />

                        @if ($editingMaterialId === null)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <x-ts-select.styled wire:model="form.initialStatus" label="Status inicial"
                                    :options="collect($creationStatuses)
                                        ->map(fn($status) => ['label' => $status->label(), 'value' => $status->value])
                                        ->all()" select="label:label|value:value" required />
                                <div class="flex items-end">
                                    <div
                                        class="w-full rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-base text-blue-900">
                                        <span class="font-semibold">Versão 1</span>
                                        <span class="mt-1 block text-sm text-blue-700">Definida automaticamente pelo
                                            sistema.</span>
                                    </div>
                                </div>
                            </div>

                            <x-ts-input wire:model="form.change_note" label="Nota da versão (opcional)" />

                            <div>
                                <label for="material-file"
                                    class="mb-1 block text-base font-medium text-gray-700">Arquivo</label>
                                <input id="material-file" type="file" wire:model="form.file"
                                    accept="{{ implode(',', array_map(fn($mime) => '.' . $mime, config('materials.upload.allowed_mimes'))) }}"
                                    required
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-base file:font-semibold file:text-blue-700">
                                @error('form.file')
                                    <p class="mt-1 text-base text-red-600">{{ $message }}</p>
                                @enderror
                                <div wire:loading wire:target="form.file"
                                    class="mt-2 text-base font-medium text-blue-700">
                                    Enviando arquivo...
                                </div>
                                @if ($form->file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                    <dl
                                        class="mt-3 grid grid-cols-1 gap-2 rounded-md bg-gray-50 p-3 text-base sm:grid-cols-3">
                                        <div class="min-w-0">
                                            <dt class="font-medium text-gray-500">Nome</dt>
                                            <dd class="truncate text-gray-900">
                                                {{ $form->file->getClientOriginalName() }}</dd>
                                        </div>
                                        <div>
                                            <dt class="font-medium text-gray-500">Tamanho</dt>
                                            <dd class="text-gray-900">
                                                {{ number_format($form->file->getSize() / 1024, 1, ',', '.') }} KB</dd>
                                        </div>
                                        <div class="min-w-0">
                                            <dt class="font-medium text-gray-500">Tipo detectado</dt>
                                            <dd class="break-all text-gray-900">
                                                {{ $form->file->getMimeType() ?? 'Não identificado' }}</dd>
                                        </div>
                                    </dl>
                                @endif
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <x-ts-button color="slate" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                            <x-ts-button color="blue" type="submit" wire:loading.attr="disabled"
                                wire:target="save">
                                <span wire:loading.remove wire:target="save">Salvar material</span>
                                <span wire:loading wire:target="save">Salvando...</span>
                            </x-ts-button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>
</div>
