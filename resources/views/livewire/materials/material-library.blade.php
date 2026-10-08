<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Biblioteca</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Materiais publicados</h1>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-search-field model="search" label="Buscar material" placeholder="Título, autor ou descrição" />
            <x-ts-select.styled
                wire:model.live="categoryFilter"
                label="Categoria"
                placeholder="Todas as categorias"
                :options="$categories->map(fn ($category) => ['label' => $category->name, 'value' => $category->id])->all()"
                select="label:label|value:value"
            />
            <label class="block text-sm font-medium text-gray-700">
                Ordenar por
                <select wire:model.live="sortOrder" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <x-data-table :columns="[
            ['label' => 'Título'],
            ['label' => 'Categoria'],
            ['label' => 'Autor'],
            ['label' => 'Ação', 'align' => 'right'],
        ]">
            @forelse ($materials as $material)
                <tr wire:key="published-material-{{ $material->id }}">
                    <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $material->title }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $material->author }}</td>
                    <td class="px-5 py-4 text-right text-sm">
                        <div class="flex justify-end gap-2">
                            @if ($isTeacher)
                                <x-ts-button
                                    :color="$material->is_favorited ? 'yellow' : 'slate'"
                                    wire:click="toggleFavorite({{ $material->id }})"
                                >
                                    {{ $material->is_favorited ? '★ Favorito' : '☆ Favoritar' }}
                                </x-ts-button>
                            @endif
                            @if ($material->currentVersion)
                                <x-ts-button color="slate" href="{{ route('downloads.show', $material) }}">Baixar</x-ts-button>
                            @endif
                            <x-ts-button color="blue" href="{{ route('painel.library.show', $material) }}">Detalhes</x-ts-button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="4">
                    Nenhum material publicado encontrado.
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
