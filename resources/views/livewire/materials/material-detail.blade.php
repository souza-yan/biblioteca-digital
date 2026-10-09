<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <a class="text-base font-medium text-blue-700 hover:underline"
            href="{{ $isTeacher ? route('painel.library') : route('painel.materials') }}">
            {{ $isTeacher ? 'Voltar à biblioteca' : 'Voltar aos materiais' }}
        </a>

        <article class="mt-5 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <p class="text-base text-gray-500">{{ $material->category->name }} · {{ $material->type }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $material->title }}</h1>
                    <p class="mt-2 text-base text-gray-600">Por {{ $material->author }}</p>
                </div>

                {{-- Bloco de botões padronizados e alinhados --}}
                <div class="flex flex-row flex-nowrap items-center gap-2 shrink-0">
                    @if ($isTeacher)
                        <x-ts-button :color="$isFavorited ? 'yellow' : 'red'" wire:click="toggleFavorite" class="shrink-0 w-36 justify-center">
                            <span class="text-white text-base font-medium whitespace-nowrap">
                                {{ $isFavorited ? '★ Favorito' : '☆ Favoritar' }}
                            </span>
                        </x-ts-button>
                    @endif

                    @if ($material->currentVersion)
                        <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                            class="shrink-0 w-32 justify-center">
                            <span class="text-white text-base font-medium whitespace-nowrap">Baixar</span>
                        </x-ts-button>
                    @endif

                    @if (!$isTeacher)
                        @if ($material->status !== \App\Enums\MaterialStatus::PUBLISHED)
                            <x-ts-button color="yellow" wire:click="publish" class="shrink-0 w-32 justify-center">
                                <span class="text-white text-base font-medium whitespace-nowrap">Publicar</span>
                            </x-ts-button>
                        @endif

                        @if ($material->status !== \App\Enums\MaterialStatus::ARCHIVED)
                            <x-ts-button color="red" wire:click="archive" class="shrink-0 w-32 justify-center">
                                <span class="text-white text-base font-medium whitespace-nowrap">Arquivar</span>
                            </x-ts-button>
                        @endif
                    @endif
                </div>
            </div>

            @error('publish')
                <p class="mt-4 rounded border border-red-200 bg-red-50 p-3 text-base text-red-700">{{ $message }}</p>
            @enderror

            <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-gray-100 pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Status</dt>
                    <dd class="mt-1 text-base text-gray-900">{{ $material->status->label() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Criado por</dt>
                    <dd class="mt-1 text-base text-gray-900">{{ $material->creator->name }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-gray-500">Descrição</dt>
                    <dd class="mt-1 whitespace-pre-line text-base text-gray-700">
                        {{ $material->description ?: 'Sem descrição.' }}</dd>
                </div>
                @if ($material->currentVersion)
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">Versão atual</dt>
                        <dd class="mt-1 text-base text-gray-900">{{ $material->currentVersion->version_number }} ·
                            {{ $material->currentVersion->original_name }}</dd>
                    </div>
                @endif
            </dl>

            @if ($material->currentVersion)
                @include('livewire.materials.partials.file-preview', [
                    'version' => $material->currentVersion,
                    'previewUrl' => route('previews.show', $material),
                ])
            @endif
        </article>

        {{-- O restante do arquivo continua igual abaixo... --}}

        @if ($canManageVersions)
            <section class="mt-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Versões</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Versão
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Arquivo
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tipo</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Tamanho
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Nota</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Publicado
                                    por</th>
                                <th scope="col"
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data</th>
                                <th scope="col"
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-600">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($versions as $version)
                                <tr wire:key="material-version-{{ $version->id }}">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $version->version_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $version->original_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $version->mime_type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ number_format($version->size / 1024, 1) }} KB</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $version->change_note ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $version->publisher?->name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                        {{ $version->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <details class="mb-2 text-left">
                                            <summary
                                                class="cursor-pointer text-sm font-medium text-blue-700 hover:underline">
                                                Ver prévia</summary>
                                            @include('livewire.materials.partials.file-preview', [
                                                'version' => $version,
                                                'previewUrl' => route('previews.version', [$material, $version]),
                                            ])
                                        </details>
                                        <x-ts-button color="slate"
                                            href="{{ route('downloads.version', [$material, $version]) }}">Baixar</x-ts-button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Nenhuma
                                        versão enviada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form wire:submit="saveVersion" class="mt-6 space-y-4 border-t border-gray-100 pt-5">
                    <h3 class="text-base font-semibold text-gray-900">Enviar nova versão</h3>
                    <div>
                        <label for="version-file" class="mb-1 block text-sm font-medium text-gray-700">Arquivo</label>
                        <input id="version-file" type="file" wire:model="versionForm.file"
                            accept="{{ implode(',', array_map(fn($mime) => '.' . $mime, config('materials.upload.allowed_mimes'))) }}"
                            class="block w-full text-sm text-gray-700">
                        @error('versionForm.file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ts-input wire:model="versionForm.change_note" label="Nota de alteração" />
                    @error('versionForm.change_note')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div wire:loading wire:target="versionForm.file" x-data="{ progress: 0 }"
                        x-on:livewire-upload-progress="progress = $event.detail.progress" class="space-y-1">
                        <progress class="h-2 w-full" max="100" x-bind:value="progress"></progress>
                        <p class="text-xs text-gray-600">Enviando arquivo: <span x-text="progress"></span>%</p>
                    </div>

                    <x-ts-button color="blue" type="submit">Salvar versão</x-ts-button>
                </form>
            </section>
        @endif
    </div>
</div>
