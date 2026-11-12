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
        <div class="mx-auto w-full max-w-screen-2xl {{ $embedded ? 'space-y-5' : 'space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8' }}">
        {{-- Cabeçalho da gestão de taxonomia do acervo. --}}
        @unless ($embedded)
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">{{ $actor?->isStaff() ? 'Minhas categorias' : 'Categorias' }}</h1>
            </div>
        </div>
        @endunless

        <x-skeleton.gate name="category-controls" label="Carregando busca de categorias">
            <x-slot:skeleton>
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="w-full max-w-xl"><x-skeleton.text class="w-fit text-sm font-medium">Buscar categoria</x-skeleton.text><x-skeleton.block class="mt-1 h-11 rounded-lg" /></div>
                    @if ($actor?->isAdmin())
                        <x-skeleton.block class="h-11 w-full rounded-lg sm:w-40" />
                    @endif
                </div>
            </x-slot:skeleton>
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="w-full max-w-xl">
                <x-search-field id="managed-category-search" model="search" label="Buscar categoria" placeholder="Digite o nome da categoria" />
            </div>
            @if ($actor?->isAdmin())
            @can('create', \App\Models\Category::class)
                <x-ts-button color="blue" wire:click="openCreate" class="w-full justify-center sm:w-auto">
                    <span class="text-white text-base">Nova categoria</span>
                </x-ts-button>
            @endcan
            @endif
        </div>

        {{-- Categorias e controles administrativos de ativação. --}}
        </x-skeleton.gate>
        <x-skeleton.gate name="category-list" label="Carregando listagem de categorias">
            <x-slot:skeleton>
                @include('livewire.categories.category-list-skeleton')
            </x-slot:skeleton>
        @if ($actor?->isStaff())
            <div class="space-y-3 md:hidden">
                @forelse ($categories as $category)
                    <article wire:key="managed-category-mobile-{{ $category->id }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h2 class="break-words text-base font-semibold text-slate-900">{{ $category->name }}</h2>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <x-categories.status-badge :active="$category->is_active" />
                            @include('livewire.categories.management-actions')
                        </div>
                    </article>
                @empty
                    <p class="rounded-xl bg-white px-4 py-8 text-center text-sm text-slate-600">Nenhuma categoria encontrada.</p>
                @endforelse
            </div>
        @endif
        <div @class(['hidden md:block' => $actor?->isStaff()])>
        <x-data-table :columns="$categoryColumns">
            @forelse ($categories as $category)
                <tr wire:key="category-{{ $category->id }}">
                    <td class="px-5 py-4 text-lg font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">
                        <x-categories.status-badge :active="$category->is_active" />
                    </td>
                    @if ($canManageCategories)
                        <td class="px-5 py-4 text-right text-lg">
                            @include('livewire.categories.management-actions')
                        </td>
                    @endif
                </tr>
            @empty
                <x-data-table.empty-state :colspan="count($categoryColumns)">
                    <span class="text-lg">Nenhuma categoria encontrada.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>
        </div>

        <x-pagination :paginator="$categories" class="mt-4" />
        </x-skeleton.gate>

        {{-- Formulário para criar ou atualizar categorias globais da biblioteca. --}}
        <x-ts-modal wire="showForm" :title="$editingCategoryId === null ? 'Nova categoria' : 'Editar categoria'" size="xl" center>
            <form id="category-form" wire:submit="save" class="space-y-4">
                <x-ts-input id="managed-category-name" wire:model="form.name" label="Nome *" required />
                <x-ts-input id="managed-category-description" wire:model="form.description" label="Descrição" />

                @error('form.slug')
                    <p class="text-base text-red-600">{{ $message }}</p>
                @enderror
            </form>

            <x-slot:footer>
                <x-ts-button color="red" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                <x-ts-button color="blue" type="submit" form="category-form" wire:loading.attr="disabled" wire:target="save">
                    Salvar categoria
                </x-ts-button>
            </x-slot:footer>
        </x-ts-modal>
        </div>
</div>
