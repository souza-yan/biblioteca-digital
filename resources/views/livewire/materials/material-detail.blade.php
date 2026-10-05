<div>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <a
            class="text-sm font-medium text-blue-700 hover:underline"
            href="{{ $isTeacher ? route('painel.library') : route('painel.materials') }}"
        >
            {{ $isTeacher ? 'Voltar à biblioteca' : 'Voltar aos materiais' }}
        </a>

        <article class="mt-5 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <p class="text-sm text-gray-500">{{ $material->category->name }} · {{ $material->type }}</p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900">{{ $material->title }}</h1>
                    <p class="mt-2 text-sm text-gray-600">Por {{ $material->author }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($isTeacher)
                        <x-ts-button
                            :color="$isFavorited ? 'yellow' : 'slate'"
                            wire:click="toggleFavorite"
                        >
                            {{ $isFavorited ? '★ Favorito' : '☆ Favoritar' }}
                        </x-ts-button>
                    @endif
                    @if ($material->currentVersion)
                        <x-ts-button color="slate" href="{{ route('downloads.show', $material) }}">Baixar</x-ts-button>
                    @endif
                </div>
                @if (! $isTeacher)
                    <div class="flex gap-2">
                        @if ($material->status !== \App\Enums\MaterialStatus::PUBLISHED)
                            <x-ts-button color="green" wire:click="publish">Publicar</x-ts-button>
                        @endif
                        @if ($material->status !== \App\Enums\MaterialStatus::ARCHIVED)
                            <x-ts-button color="red" wire:click="archive">Arquivar</x-ts-button>
                        @endif
                    </div>
                @endif
            </div>

            @error('publish')
                <p class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
            @enderror

            <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $material->status->label() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Criado por</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $material->creator->name }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-gray-500">Descrição</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $material->description ?: 'Sem descrição.' }}</dd>
                </div>
                @if ($material->currentVersion)
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Versão atual</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $material->currentVersion->version_number }} · {{ $material->currentVersion->original_name }}</dd>
                    </div>
                @endif
            </dl>
        </article>

        @if ($canManageVersions)
            <section class="mt-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Versões</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Versão</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Arquivo</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tipo</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tamanho</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Nota</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Publicado por</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($versions as $version)
                                <tr wire:key="material-version-{{ $version->id }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $version->version_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $version->original_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $version->mime_type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($version->size / 1024, 1) }} KB</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $version->change_note ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $version->publisher?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $version->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <x-ts-button color="slate" href="{{ route('downloads.version', [$material, $version]) }}">Baixar</x-ts-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Nenhuma versão enviada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form wire:submit="saveVersion" class="mt-6 space-y-4 border-t border-gray-100 pt-5">
                    <h3 class="text-base font-semibold text-gray-900">Enviar nova versão</h3>
                    <div>
                        <label for="version-file" class="mb-1 block text-sm font-medium text-gray-700">Arquivo</label>
                        <input
                            id="version-file"
                            type="file"
                            wire:model="versionForm.file"
                            accept="{{ implode(',', array_map(fn ($mime) => '.'.$mime, config('materials.upload.allowed_mimes'))) }}"
                            class="block w-full text-sm text-gray-700"
                        >
                        @error('versionForm.file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ts-input wire:model="versionForm.change_note" label="Nota de alteração" />
                    @error('versionForm.change_note')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div
                        wire:loading
                        wire:target="versionForm.file"
                        x-data="{ progress: 0 }"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="space-y-1"
                    >
                        <progress class="h-2 w-full" max="100" x-bind:value="progress"></progress>
                        <p class="text-xs text-gray-600">Enviando arquivo: <span x-text="progress"></span>%</p>
                    </div>

                    <x-ts-button color="blue" type="submit">Salvar versão</x-ts-button>
                </form>
            </section>
        @endif
    </div>
</div>
