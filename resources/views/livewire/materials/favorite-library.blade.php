<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Biblioteca</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Meus favoritos</h1>
        </div>

        <div class="overflow-x-auto rounded-md border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Título</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Categoria</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Autor</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
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
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Você ainda não tem materiais favoritos publicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $materials->links() }}</div>
    </div>
</div>
