<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        @php
            $isLibraryContext = request()->routeIs('painel.library.*');
            $categoryFilter = request()->query('categoryFilter');
            $breadcrumbItems = $isLibraryContext
                ? [
                    ['label' => 'Início', 'url' => route('dashboard')],
                    ['label' => 'Materiais', 'url' => route('painel.library.categories')],
                    [
                        'label' => 'Categorias',
                        'url' => route('painel.library', ['categoryFilter' => $categoryFilter]),
                    ],
                    ['label' => $material->title],
                ]
                : [
                    ['label' => 'Início', 'url' => route('dashboard')],
                    ['label' => 'Materiais', 'url' => route('painel.materials')],
                    ['label' => $material->title],
                ];
        @endphp

        <x-breadcrumb :items="$breadcrumbItems" />

        <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <p class="text-base text-gray-500">{{ $material->category->name }} · {{ $material->type }}</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $material->title }}</h1>
                    <p class="mt-2 text-base text-gray-600">Por {{ $material->author }}</p>
                </div>

                {{-- Bloco de botões padronizados e alinhados --}}
                <div class="flex flex-row flex-nowrap items-center gap-2 shrink-0">
                    @if ($isTeacher)
                        <x-ts-button :color="$isFavorited ? 'yellow' : 'slate'"
                            wire:click.stop="toggleFavorite"
                            :aria-label="$isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                            :title="$isFavorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'"
                            wire:loading.attr="disabled" wire:target="toggleFavorite"
                            class="!size-12 !p-0 shrink-0 justify-center">
                            @if ($isFavorited)
                                <x-ts-icon icon="bookmark" solid class="size-7 text-amber-950" />
                            @else
                                <x-ts-icon icon="bookmark" outline class="size-7 text-slate-200" />
                            @endif
                        </x-ts-button>
                    @endif

                    @if ($material->currentVersion)
                        <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                            aria-label="Baixar {{ $material->title }}" title="Baixar material"
                            class="!size-12 !p-0 shrink-0 justify-center">
                            <x-ts-icon icon="document-arrow-down" outline class="size-7 text-white" />
                        </x-ts-button>
                    @endif

                    @if ($canManageVersions)
                        <x-ts-button color="blue" wire:click="openEditForm" aria-label="Editar material"
                            title="Editar material" class="!size-12 !p-0 shrink-0 justify-center">
                            <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
                        </x-ts-button>

                        @if ($material->status !== \App\Enums\MaterialStatus::PUBLISHED)
                            <x-ts-button color="emerald" wire:click="publish" aria-label="Publicar material"
                                wire:confirm="Publicar este material na biblioteca?"
                                title="Publicar material" class="!size-12 !p-0 shrink-0 justify-center">
                                <x-ts-icon icon="cloud-arrow-up" outline class="size-7 text-white" />
                            </x-ts-button>
                        @endif

                        @if ($material->status !== \App\Enums\MaterialStatus::ARCHIVED)
                            <x-ts-button color="red" wire:click="archive" aria-label="Arquivar material"
                                wire:confirm="Arquivar este material? Ele deixará de aparecer na biblioteca."
                                title="Arquivar material" class="!size-12 !p-0 shrink-0 justify-center">
                                <x-ts-icon icon="archive-box" outline class="size-7 text-white" />
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

        {{-- Edição dos metadados sem deslocar a leitura da ficha do material. --}}
        @if ($canEditMaterial)
            <x-ts-modal wire="showEditForm" title="Editar material" size="2xl" center>
                <form id="detail-material-form" wire:submit="saveMaterial" class="space-y-4">
                    <p class="text-sm text-slate-500">Atualize os dados do material mantendo suas versões.</p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-form.input id="detail-material-title" label="Título" :required="true"
                            error="materialForm.title" wire:model="materialForm.title" />
                        <x-form.input id="detail-material-author" label="Autor" :required="true"
                            error="materialForm.author" wire:model="materialForm.author" />
                        <x-form.select id="detail-material-category" label="Categoria" :required="true"
                            error="materialForm.category_id" wire:model="materialForm.category_id">
                            <option value="">Selecione uma categoria ativa</option>
                            @foreach ($activeCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-form.select>
                        <x-form.input id="detail-material-description" label="Descrição"
                            error="materialForm.description" wire:model="materialForm.description" />
                    </div>
                </form>

                <x-slot:footer>
                    <x-ts-button color="slate" type="button" wire:click="closeEditForm">
                        Cancelar
                    </x-ts-button>
                    <x-ts-button color="blue" type="submit" form="detail-material-form"
                        wire:loading.attr="disabled" wire:target="saveMaterial">
                        <span wire:loading.remove wire:target="saveMaterial">Salvar alterações</span>
                        <span wire:loading wire:target="saveMaterial">Salvando...</span>
                    </x-ts-button>
                </x-slot:footer>
            </x-ts-modal>
        @endif

        {{-- O restante do arquivo continua igual abaixo... --}}

        @if ($canViewVersionHistory)
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
                                        @if (in_array($version->mime_type, ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'text/plain', 'video/mp4', 'video/webm', 'audio/mpeg'], true))
                                            <x-ts-button color="blue"
                                                href="{{ route('previews.version', [$material, $version]) }}"
                                                aria-label="Ver prévia da versão {{ $version->version_number }}"
                                                title="Ver prévia" class="!size-12 !p-0 justify-center">
                                                <x-ts-icon icon="eye" outline class="size-7 text-white" />
                                            </x-ts-button>
                                        @endif
                                        <x-ts-button color="emerald"
                                            href="{{ route('downloads.version', [$material, $version]) }}"
                                            aria-label="Baixar versão {{ $version->version_number }}" title="Baixar versão"
                                            class="!size-12 !p-0 justify-center">
                                            <x-ts-icon icon="document-arrow-down" outline class="size-7 text-white" />
                                        </x-ts-button>
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

                @if ($canManageVersions)
                    <form wire:submit="saveVersion"
                    class="mt-8 space-y-5 rounded-2xl border border-slate-200 bg-slate-50/60 p-5 sm:p-6">

                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Enviar nova versão</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Selecione o arquivo e descreva o que mudou.
                        </p>
                    </div>

                    <div>
                        <label for="version-file" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Arquivo <span aria-hidden="true" class="font-bold text-red-600">*</span>
                            <span class="sr-only">(obrigatório)</span>
                        </label>
                        <input id="version-file" type="file" wire:model="versionForm.file" required
                            accept="{{ implode(',', array_map(fn($mime) => '.' . $mime, config('materials.upload.allowed_mimes'))) }}"
                            class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-700
                   shadow-xs transition
                   file:mr-4 file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:py-2.5
                   file:text-sm file:font-medium file:text-blue-700
                   hover:file:bg-blue-100
                   focus:border-blue-500 focus:outline-hidden focus:ring-2 focus:ring-blue-500/30">
                        @error('versionForm.file')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                        <x-ts-button color="blue" type="submit" class="w-full sm:w-auto">
                            Salvar versão
                        </x-ts-button>
                    </div>
                    </form>
                @endif
            </section>
        @endif
    </div>
</div>
