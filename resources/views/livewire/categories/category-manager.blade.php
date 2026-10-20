<div>
    @php
        $actor = auth()->user();
        $canManageCategories = $actor?->isAdmin() ?? false;
        $categoryColumns = [
            ['label' => 'Nome', 'class' => 'text-lg'],
            ['label' => 'Situação', 'class' => 'text-lg'],
        ];

        if ($canManageCategories) {
            $categoryColumns[] = ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'];
        }
    @endphp
        <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <a href="{{ route('painel.materials') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-blue-800 hover:underline">
                    <x-ts-icon icon="arrow-left" outline class="size-5" />
                    Voltar aos materiais
                </a>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Categorias</h1>
                @if ($actor?->isStaff())
                    <p class="mt-2 max-w-2xl text-base text-slate-500">
                        Cadastre novas categorias para revisão. Somente o administrador pode ativá-las ou editá-las.
                    </p>
                @endif
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

        <x-data-table :columns="$categoryColumns">
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

        @if ($showForm)
            <div
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                <section role="dialog" aria-modal="true" aria-labelledby="category-form-title"
                    class="w-full max-w-xl rounded-md bg-white p-6 shadow-xl">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <h2 id="category-form-title" class="text-2xl font-bold text-slate-900">
                            {{ $editingCategoryId === null ? 'Nova categoria' : 'Editar categoria' }}
                        </h2>
                        <button type="button" wire:click="closeForm" aria-label="Fechar formulário" title="Fechar"
                            class="inline-flex size-12 items-center justify-center rounded text-gray-500 hover:bg-gray-100">
                            <x-ts-icon icon="x-mark" outline class="size-7" />
                        </button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <x-ts-input wire:model="form.name" label="Nome *" required />
                        <x-ts-input wire:model="form.description" label="Descrição" />
                        @if ($actor?->isAdmin())
                            <label class="flex items-center gap-2 text-base text-gray-700">
                                <input type="checkbox" wire:model="form.is_active"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Categoria ativa
                            </label>
                        @else
                            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                <span class="font-semibold">Enviada como inativa</span>
                                <span class="mt-1 block">A categoria ficará aguardando revisão e ativação do administrador.</span>
                            </div>
                        @endif

                        @error('form.slug')
                            <p class="text-base text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <x-ts-button color="blue" type="submit">Salvar categoria</x-ts-button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
        </div>
</div>
