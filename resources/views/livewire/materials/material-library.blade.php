<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <h1 class="mt-1 text-3xl font-bold text-black">Materiais publicados</h1>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3 items-end">
            <x-search-field model="search" label="Buscar material" placeholder="Título, autor ou descrição" />
            <x-ts-select.styled wire:model.live="categoryFilter" label="Categoria" placeholder="Todas as categorias"
                :options="$categories
                    ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                    ->all()" select="label:label|value:value" />
            <div>
                <label class="block text-sm font-semibold text-black mb-1">
                    Ordenar por
                </label>
                <select wire:model.live="sortOrder"
                    class="block w-full rounded-lg border-slate-300 text-base shadow-sm py-2.5 px-3 focus:border-blue-500 focus:ring-blue-500">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-data-table :columns="[
            ['label' => 'Título'],
            ['label' => 'Categoria'],
            ['label' => 'Autor'],
            ['label' => 'Ação', 'align' => 'right'],
        ]">
            @forelse ($materials as $material)
                <tr wire:key="published-material-{{ $material->id }}">
                    <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->author }}</td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex flex-row flex-nowrap items-center justify-end gap-2">
                            @if ($isTeacher)
                                <x-ts-button :color="$material->is_favorited ? 'yellow' : 'red'" wire:click="toggleFavorite({{ $material->id }})"
                                    class="shrink-0 text-center"
                                    style="width: 145px; min-width: 145px; max-width: 145px;">
                                    <span
                                        class="text-white text-base font-medium whitespace-nowrap w-full inline-block text-center">
                                        {{ $material->is_favorited ? '★ Favorito' : '☆ Favoritar' }}
                                    </span>
                                </x-ts-button>
                            @endif

                            @if ($material->currentVersion)
                                <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                                    class="shrink-0">
                                    <span class="text-white text-base font-medium whitespace-nowrap">Baixar</span>
                                </x-ts-button>
                            @endif

                            <x-ts-button color="blue" href="{{ route('painel.library.show', $material) }}"
                                class="shrink-0">
                                <span class="text-white text-base font-medium whitespace-nowrap">Detalhes</span>
                            </x-ts-button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="4">
                    <span class="text-lg">Nenhum material publicado encontrado.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
