<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Painel</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Categorias</h1>
            </div>
            <x-ts-button color="blue" wire:click="openCreate">Nova categoria</x-ts-button>
        </div>

        <div class="mb-5 max-w-xl">
            <x-ts-input
                wire:model.live.debounce.300ms="search"
                label="Buscar por nome ou slug"
                placeholder="Digite um nome ou slug"
            />
        </div>

        <div class="overflow-x-auto rounded-md border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Nome</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Slug</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Situação</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $category->slug }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $category->is_active ? 'Ativa' : 'Inativa' }}</td>
                            <td class="px-5 py-4 text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <x-ts-button color="slate" wire:click="editCategory({{ $category->id }})">Editar</x-ts-button>
                                    <x-ts-button
                                        :color="$category->is_active ? 'red' : 'green'"
                                        wire:click="toggleActive({{ $category->id }})"
                                    >
                                        {{ $category->is_active ? 'Desativar' : 'Ativar' }}
                                    </x-ts-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Nenhuma categoria encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $categories->links() }}</div>

        @if ($showForm)
            <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                <section role="dialog" aria-modal="true" aria-labelledby="category-form-title" class="w-full max-w-xl rounded-md bg-white p-6 shadow-xl">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <h2 id="category-form-title" class="text-lg font-semibold text-gray-900">
                            {{ $editingCategoryId === null ? 'Nova categoria' : 'Editar categoria' }}
                        </h2>
                        <button type="button" wire:click="closeForm" aria-label="Fechar formulário" class="rounded p-1 text-gray-500 hover:bg-gray-100">&times;</button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <x-ts-input wire:model="form.name" label="Nome" />
                        <x-ts-input wire:model="form.slug" label="Slug" hint="Deixe vazio para gerar a partir do nome." />
                        <x-ts-input wire:model="form.description" label="Descrição" />
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model="form.is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Categoria ativa
                        </label>

                        @error('form.slug')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <x-ts-button color="slate" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                            <x-ts-button color="blue" type="submit">Salvar categoria</x-ts-button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>
</div>
