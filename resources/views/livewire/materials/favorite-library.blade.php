<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
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
                    <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                    <td class="px-5 py-4 text-lg text-black">{{ $material->author }}</td>
                    <td class="px-5 py-4 text-right text-lg">
                        <div class="flex justify-end gap-2">
                            @if ($material->currentVersion)
                                <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"><span
                                        class="text-white">Baixar</span></x-ts-button>
                            @endif
                            <x-ts-button color="blue" href="{{ route('painel.library.show', $material) }}"><span
                                    class="text-white">Detalhes</span></x-ts-button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="4">
                    <span class="text-lg">Você ainda não tem materiais favoritos publicados.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$materials" class="mt-4" />
    </div>
</div>
