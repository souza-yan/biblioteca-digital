<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Biblioteca</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Meus favoritos</h1>
        </div>

        <x-data-table :columns="[
            ['label' => 'Título'],
            ['label' => 'Categoria'],
            ['label' => 'Autor'],
            ['label' => 'Ação', 'align' => 'right'],
        ]">
            @forelse ($materials as $material)
                <tr wire:key="favorite-material-{{ $material->id }}">
                    <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $material->title }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $material->author }}</td>
                    <td class="px-5 py-4 text-right text-sm">
                        <div class="flex justify-end gap-2">
                            @if ($material->currentVersion)
                                <x-ts-button color="slate" href="{{ route('downloads.show', $material) }}">Baixar</x-ts-button>
                            @endif
                            <x-ts-button color="blue" href="{{ route('painel.library.show', $material) }}">Detalhes</x-ts-button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="4">
                    Você ainda não tem materiais favoritos publicados.
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
