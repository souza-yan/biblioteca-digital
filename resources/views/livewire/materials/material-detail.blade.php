<div>
    <x-skeleton.gate name="material-detail" label="Carregando detalhes do material">
        <x-slot:skeleton>
            @include('livewire.materials.material-detail-skeleton')
        </x-slot:skeleton>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <article x-data="{ isPreviewOpen: false }" class="rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start sm:gap-4">
                <div>
                    @if (! $isTeacher)
                        <button type="button"
                            class="mb-3 inline-flex size-9 items-center justify-center rounded-lg text-blue-700 transition hover:bg-blue-50 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 sm:hidden"
                            aria-label="Voltar" title="Voltar"
                            @click="if (window.history.length > 1) { window.history.back() } else { window.location.assign(@js(route('painel.library'))) }">
                            <x-ts-icon icon="chevron-left" outline class="size-5" />
                        </button>
                    @endif
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $material->title }}</h1>
                    @if ($isTeacher)
                        <p class="mt-1 text-sm text-gray-600 sm:text-base">Por {{ $material->author }}</p>
                    @endif
                </div>

                {{-- Bloco de botões padronizados e alinhados --}}
                <div class="flex shrink-0 flex-wrap items-center gap-2 sm:flex-nowrap">
                    @if ($isTeacher)
                        <x-ts-button color="yellow"
                            wire:click.stop="toggleFavorite"
                            :aria-label="$isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                            :title="$isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                            wire:loading.attr="disabled" wire:target="toggleFavorite"
                            class="!size-10 sm:!size-12 !p-0 shrink-0 justify-center">
                            @if ($isFavorited)
                                <x-ts-icon icon="bookmark" solid class="size-6 sm:size-7 text-amber-950" />
                            @else
                                <x-ts-icon icon="bookmark" outline class="size-6 sm:size-7 text-amber-950" />
                            @endif
                        </x-ts-button>
                    @endif

                    @if ($isTeacher && $material->currentVersion)
                        <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                            aria-label="Baixar {{ $material->title }}" title="Baixar material"
                            class="!size-10 sm:!size-12 !p-0 shrink-0 justify-center">
                            <x-ts-icon icon="document-arrow-down" outline class="size-6 sm:size-7 text-white" />
                        </x-ts-button>

                        <button type="button"
                            class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-700 text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:hidden"
                            @click="isPreviewOpen = true" aria-label="Visualizar conteúdo"
                            title="Visualizar conteúdo">
                            <x-ts-icon icon="eye" outline class="size-6" />
                        </button>
                    @endif

                </div>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-2 border-t border-gray-100 pt-4 sm:mt-6 sm:gap-4 sm:pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <x-materials.status-badge :status="$material->status" />
                    </dd>
                </div>
                @if ($isTeacher)
                    <div class="hidden sm:block">
                    <dt class="text-xs font-semibold uppercase text-gray-500">Criado por</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:text-base">{{ $material->creator->name }}</dd>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase text-gray-500">Descrição</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-gray-700 sm:text-base">
                        {{ $material->description ?: 'Sem descrição.' }}</dd>
                </div>
                @if ($isTeacher && $material->currentVersion)
                    <div class="hidden sm:block">
                        <dt class="text-xs font-semibold uppercase text-gray-500">Versão atual</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:text-base">{{ $material->currentVersion->version_number }} ·
                            {{ $material->currentVersion->original_name }}</dd>
                    </div>
                @endif
            </dl>

            @if ($isTeacher && $material->currentVersion)
                @include('livewire.materials.partials.file-preview', [
                    'version' => $material->currentVersion,
                    'previewUrl' => $previewUrl,
                ])
            @endif
        </article>

        @if ($canViewVersionHistory)
            <section class="mt-6 flex flex-col rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                <h2 class="order-1 text-lg font-semibold text-gray-900">Versões</h2>

                <div class="order-3 mt-4 hidden overflow-x-auto sm:order-2 sm:block">
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
                                        @if ($version->isNot($material->currentVersion)
                                            && in_array($version->mime_type, ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'text/plain', 'video/mp4', 'video/webm', 'audio/mpeg'], true))
                                            <x-ts-button color="blue"
                                                href="{{ route('previews.version', [$material, $version]) }}"
                                                aria-label="Ver prévia da versão {{ $version->version_number }}"
                                                title="Ver prévia" class="!size-12 !p-0 justify-center">
                                                <x-ts-icon icon="eye" outline class="size-7 text-white" />
                                            </x-ts-button>
                                        @endif
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

                <div class="order-3 mt-4 space-y-3 sm:hidden">
                    @forelse ($versions as $version)
                        <article wire:key="material-version-mobile-{{ $version->id }}"
                            class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900">Versão {{ $version->version_number }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-600" title="{{ $version->original_name }}">
                                        {{ $version->original_name }}
                                    </p>
                                </div>
                                @if ($version->isNot($material->currentVersion)
                                    && in_array($version->mime_type, ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'text/plain', 'video/mp4', 'video/webm', 'audio/mpeg'], true))
                                    <x-ts-button color="blue" href="{{ route('previews.version', [$material, $version]) }}"
                                        aria-label="Ver prévia da versão {{ $version->version_number }}" title="Ver prévia"
                                        class="!size-10 !p-0 shrink-0 justify-center">
                                        <x-ts-icon icon="eye" outline class="size-5 text-white" />
                                    </x-ts-button>
                                @endif
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                <span class="rounded-full bg-slate-200 px-2.5 py-1 font-semibold text-slate-700">
                                    {{ strtoupper(pathinfo($version->original_name, PATHINFO_EXTENSION) ?: 'ARQ') }}
                                </span>
                                <span>{{ number_format($version->size / 1024, 1, ',', '.') }} KB</span>
                                <span>{{ $version->created_at?->format('d/m/Y') }}</span>
                            </div>
                            @if ($version->change_note)
                                <p class="mt-3 border-t border-slate-200 pt-3 text-sm leading-5 text-slate-600">
                                    {{ $version->change_note }}
                                </p>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">
                            Nenhuma versão enviada.
                        </p>
                    @endforelse
                </div>

                @if ($canManageVersions)
                    <form wire:submit="saveVersion"
                    class="order-2 mt-4 space-y-4 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:order-4 sm:mt-8 sm:space-y-5 sm:p-6">

                    <div class="sm:hidden"
                        x-data="{ selectedFileName: @js($versionForm->file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile ? $versionForm->file->getClientOriginalName() : null) }">
                        <input id="version-file-mobile" type="file" wire:model="versionForm.file" class="sr-only"
                            accept="{{ implode(',', array_map(fn ($mime) => '.'.$mime, config('materials.upload.allowed_mimes'))) }}"
                            required x-on:change="selectedFileName = $event.target.files[0]?.name ?? null">
                        <label for="version-file-mobile"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-left transition hover:border-blue-400 hover:bg-blue-100 focus-within:ring-2 focus-within:ring-blue-600">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white text-blue-700 shadow-sm">
                                <x-ts-icon icon="arrow-up-tray" outline class="size-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-base font-semibold text-slate-900">Enviar nova versão</span>
                                <span x-show="! selectedFileName" class="mt-0.5 block text-sm text-slate-600">
                                    Toque para selecionar o arquivo
                                </span>
                                <span x-show="selectedFileName" x-cloak class="mt-0.5 block truncate text-sm font-medium text-blue-800"
                                    x-text="selectedFileName"></span>
                            </span>
                        </label>
                        @error('versionForm.file')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="hidden sm:block">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                                <x-ts-icon icon="arrow-up-tray" outline class="size-5" />
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Enviar nova versão</h3>
                                <p class="mt-0.5 text-sm text-slate-600">O arquivo atual será substituído sem apagar o histórico.</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <x-materials.file-upload id="version-file" model="versionForm.file" :file="$versionForm->file" />
                        </div>
                    </div>

                    <div>
                        <x-ts-input wire:model="versionForm.change_note" label="Nota de alteração" />
                        @error('versionForm.change_note')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div wire:loading wire:target="versionForm.file" x-data="{ progress: 0 }"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                        class="space-y-1.5 rounded-lg border border-blue-100 bg-blue-50 p-3">
                        <progress class="h-2 w-full overflow-hidden rounded-full accent-blue-600" max="100"
                            x-bind:value="progress"></progress>
                        <p class="text-xs font-medium text-blue-800">
                            Enviando arquivo: <span x-text="progress"></span>%
                        </p>
                    </div>

                    <div class="flex justify-end border-t border-slate-200 pt-4">
                        <x-ts-button color="blue" type="submit" class="w-full justify-center sm:w-auto"
                            wire:loading.attr="disabled" wire:target="saveVersion">
                            Salvar versão
                        </x-ts-button>
                    </div>
                    </form>
                @endif
            </section>
        @endif
    </div>
    </x-skeleton.gate>
</div>
