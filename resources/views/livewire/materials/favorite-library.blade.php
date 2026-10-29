<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Meus favoritos</h1>
            <p class="mt-2 text-base text-slate-500">Seus materiais publicados salvos para consultar depois.</p>
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
                                <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                                    aria-label="Baixar {{ $material->title }}" title="Baixar material"
                                    class="!size-12 !p-0 justify-center">
                                    <x-ts-icon icon="document-arrow-down" outline class="size-7 text-white" />
                                </x-ts-button>
                            @endif
                            <x-ts-button color="blue" href="{{ route('painel.library.show', $material) }}"
                                aria-label="Ver detalhes de {{ $material->title }}" title="Ver detalhes"
                                class="!size-12 !p-0 justify-center">
                                <x-ts-icon icon="eye" outline class="size-7 text-white" />
                            </x-ts-button>
                            <x-ts-button color="yellow" wire:click="removeFavorite({{ $material->id }})"
                                wire:loading.attr="disabled" wire:target="removeFavorite({{ $material->id }})"
                                aria-label="Remover {{ $material->title }} dos favoritos" title="Remover dos favoritos"
                                class="!size-12 !p-0 justify-center">
                                <x-ts-icon icon="bookmark" solid class="size-7 text-amber-950" />
                            </x-ts-button>
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
