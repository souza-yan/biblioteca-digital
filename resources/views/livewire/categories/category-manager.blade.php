<div>
    @php
        $actor = auth()->user();
        $canManageCategories = $actor?->isAdmin() || $actor?->isStaff();
        $categoryColumns = [
            ['label' => 'Nome', 'class' => 'text-lg'],
            ['label' => 'Situação', 'class' => 'text-lg'],
        ];

        if ($canManageCategories) {
            $categoryColumns[] = ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'];
        }
    @endphp
        <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        {{-- Cabeçalho da gestão de taxonomia do acervo. --}}
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Categorias</h1>
            </div>
            @can('create', \App\Models\Category::class)
                <x-ts-button color="blue" wire:click="openCreate">
                    <span class="text-white text-base">Nova categoria</span>
                </x-ts-button>
            @endcan
        </div>

        <div class="mb-5 max-w-xl">
            <x-search-field model="search" label="Buscar categoria" placeholder="Digite o nome da categoria" />
        </div>

        {{-- Categorias e controles administrativos de ativação. --}}
        <x-data-table :columns="$categoryColumns">
            <x-data-table.loading :colspan="count($categoryColumns)" />
            @forelse ($categories as $category)
                <tr wire:key="category-{{ $category->id }}">
                    <td class="px-5 py-4 text-lg font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $category->is_active ? 'Ativa' : 'Inativa' }}</td>
                    @if ($canManageCategories)
                        <td class="px-5 py-4 text-right text-lg">
                            @can('update', $category)
                            <div class="flex justify-end gap-2">
                                <x-ts-button color="blue"
                                    wire:click="editCategory({{ $category->id }})" aria-label="Editar categoria {{ $category->name }}"
                                    title="Editar categoria" class="!size-12 !p-0 justify-center">
                                    <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
                                </x-ts-button>
                                <x-ts-button :color="$category->is_active ? 'red' : 'emerald'" wire:click="toggleActive({{ $category->id }})"
                                    wire:confirm="{{ $category->is_active ? 'Desativar esta categoria?' : 'Ativar esta categoria?' }}"
                                    wire:loading.attr="disabled" wire:target="toggleActive({{ $category->id }})"
                                    :aria-label="$category->is_active ? 'Desativar categoria '.$category->name : 'Ativar categoria '.$category->name"
                                    :title="$category->is_active ? 'Desativar categoria' : 'Ativar categoria'" class="!size-12 !p-0 justify-center">
                                    <x-ts-icon :icon="$category->is_active ? 'no-symbol' : 'check-circle'" outline class="size-5 text-white" />
                                </x-ts-button>
                            </div>
                            @endcan
                        </td>
                    @endif
                </tr>
            @empty
                <x-data-table.empty-state :colspan="count($categoryColumns)">
                    <span class="text-lg">Nenhuma categoria encontrada.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$categories" class="mt-4" />

        {{-- Formulário para criar ou atualizar categorias globais da biblioteca. --}}
        <x-ts-modal wire="showForm" :title="$editingCategoryId === null ? 'Nova categoria' : 'Editar categoria'" size="xl" center>
            <form id="category-form" wire:submit="save" class="space-y-4">
                <x-ts-input wire:model="form.name" label="Nome *" required />
                <x-ts-input wire:model="form.description" label="Descrição" />
                <label class="flex items-center gap-2 text-base text-gray-700">
                    <input type="checkbox" wire:model="form.is_active"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Categoria ativa
                </label>

                @error('form.slug')
                    <p class="text-base text-red-600">{{ $message }}</p>
                @enderror
            </form>

            <x-slot:footer>
                <x-ts-button color="slate" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                <x-ts-button color="blue" type="submit" form="category-form" wire:loading.attr="disabled" wire:target="save">
                    Salvar categoria
                </x-ts-button>
            </x-slot:footer>
        </x-ts-modal>
        </div>
</div>
