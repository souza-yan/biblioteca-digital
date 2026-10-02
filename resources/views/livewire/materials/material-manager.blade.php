<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Painel</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Materiais</h1>
            </div>
            <x-ts-button color="blue" wire:click="openCreate">Novo material</x-ts-button>
        </div>

        @error('publish')
            <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
        @enderror

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-ts-input wire:model.live.debounce.300ms="search" label="Buscar material" placeholder="Título ou autor" />
            <x-ts-select.styled
                wire:model.live="statusFilter"
                label="Status"
                placeholder="Todos os status"
                :options="collect($statuses)->map(fn ($status) => ['label' => $status->label(), 'value' => $status->value])->all()"
                select="label:label|value:value"
            />
            <x-ts-select.styled
                wire:model.live="categoryFilter"
                label="Categoria"
                placeholder="Todas as categorias"
                :options="$categories->map(fn ($category) => ['label' => $category->name, 'value' => $category->id])->all()"
                select="label:label|value:value"
            />
        </div>

        <div class="overflow-x-auto rounded-md border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Título</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Categoria</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tipo</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($materials as $material)
                        <tr wire:key="material-{{ $material->id }}">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">
                                <a class="text-blue-700 hover:underline" href="{{ route('painel.materials.show', $material) }}">{{ $material->title }}</a>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $material->category->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $material->type }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $material->status->label() }}</td>
                            <td class="px-5 py-4 text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <x-ts-button color="slate" wire:click="editMaterial({{ $material->id }})">Editar</x-ts-button>
                                    @if ($material->status !== \App\Enums\MaterialStatus::PUBLISHED)
                                        <x-ts-button color="green" wire:click="publish({{ $material->id }})">Publicar</x-ts-button>
                                    @endif
                                    @if ($material->status !== \App\Enums\MaterialStatus::ARCHIVED)
                                        <x-ts-button color="red" wire:click="archive({{ $material->id }})">Arquivar</x-ts-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">Nenhum material encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $materials->links() }}</div>

        @if ($showForm)
            <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                <section role="dialog" aria-modal="true" aria-labelledby="material-form-title" class="w-full max-w-2xl rounded-md bg-white p-6 shadow-xl">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <h2 id="material-form-title" class="text-lg font-semibold text-gray-900">
                            {{ $editingMaterialId === null ? 'Novo material' : 'Editar material' }}
                        </h2>
                        <button type="button" wire:click="closeForm" aria-label="Fechar formulário" class="rounded p-1 text-gray-500 hover:bg-gray-100">&times;</button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <x-ts-input wire:model="form.title" label="Título" />
                        <x-ts-input wire:model="form.description" label="Descrição" />
                        <x-ts-select.styled
                            wire:model="form.category_id"
                            label="Categoria"
                            placeholder="Selecione uma categoria ativa"
                            :options="$activeCategories->map(fn ($category) => ['label' => $category->name, 'value' => $category->id])->all()"
                            select="label:label|value:value"
                            required
                        />
                        <x-ts-input wire:model="form.type" label="Tipo" placeholder="Ex.: PDF" />
                        <x-ts-input wire:model="form.author" label="Autor" />
                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <x-ts-button color="slate" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                            <x-ts-button color="blue" type="submit">Salvar material</x-ts-button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>
</div>
