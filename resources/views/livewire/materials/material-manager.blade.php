    <div>
        @php
            $actor = auth()->user();
            $canManageMaterials = $actor?->isAdmin() ?? false;
            $canCreateMaterials = $actor ? \Illuminate\Support\Facades\Gate::allows('create', \App\Models\Material::class) : false;
            $materialColumns = [
                ['label' => 'Título', 'class' => 'text-lg'],
                ['label' => 'Categoria', 'class' => 'text-lg'],
                ['label' => 'Tipo', 'class' => 'text-lg'],
                ['label' => 'Status', 'class' => 'text-lg'],
            ];

            if ($actor?->isAdmin() || $actor?->isStaff()) {
                $materialColumns[] = ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'];
            }
        @endphp
        <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
            <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div class="min-w-0">
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">
                        {{ $canManageMaterials ? 'Acervo global' : 'Gestão de conteúdos' }}
                    </p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                        Materiais
                    </h1>
                    <p class="mt-2 max-w-2xl text-base text-slate-500">
                        {{ $canManageMaterials
                            ? 'Cadastre, publique e mantenha os conteúdos da biblioteca digital.'
                            : 'Consulte a biblioteca e envie novos materiais para revisão e publicação.' }}
                    </p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    @if ($actor?->isStaff())
                        <x-ts-button color="slate" href="{{ route('painel.library.categories') }}"
                            class="w-full justify-center sm:w-auto">
                            <x-ts-icon icon="eye" outline class="size-5" />
                            Visualizar conteúdos
                        </x-ts-button>
                    @elseif ($canCreateMaterials)
                        <x-ts-button color="blue" wire:click="openCreate" class="w-full justify-center sm:w-auto">
                            <x-ts-icon icon="plus" outline class="size-5" />
                            Novo material
                        </x-ts-button>
                    @endif
                </div>
            </div>

            @error('publish')
                <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-base text-red-700">{{ $message }}</p>
            @enderror

            <div @class([
                'mb-5 grid grid-cols-1 gap-4',
                'md:grid-cols-3',
            ])>
                <x-search-field model="search" label="Buscar material" placeholder="Título ou autor" />
                <x-form.select id="managed-material-status" label="Status" wire:model.live="statusFilter">
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-form.select>
                <x-form.select id="managed-material-category" label="Categoria" wire:model.live="categoryFilter">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-data-table :columns="$materialColumns">
                @forelse ($materials as $material)
                    <tr wire:key="material-{{ $material->id }}">
                        <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                        <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                        <td class="px-5 py-4 text-lg text-black">{{ $material->type }}</td>
                        <td class="px-5 py-4 text-lg text-black">{{ $material->status->label() }}</td>
                        @if ($actor?->isAdmin() || $actor?->isStaff())
                            <td class="px-5 py-4 text-right text-lg">
                                <div class="flex flex-row flex-nowrap items-center justify-end gap-2">
                                @if ($actor?->isStaff() && $material->currentVersion)
                                    @can('download', $material)
                                        <x-ts-button color="emerald" href="{{ route('downloads.show', $material) }}"
                                            aria-label="Baixar {{ $material->title }}" title="Baixar material"
                                            class="!size-12 !p-0 shrink-0 justify-center">
                                            <x-ts-icon icon="document-arrow-down" outline class="size-7 text-white" />
                                        </x-ts-button>
                                    @endcan
                                @endif

                                @can('update', $material)
                                    <x-ts-button color="blue" href="{{ route('painel.materials.show', $material) }}"
                                        aria-label="Editar material {{ $material->title }}" title="Editar material"
                                        class="!size-12 !p-0 shrink-0 justify-center">
                                        <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
                                    </x-ts-button>

                                @if ($material->status->value !== \App\Enums\MaterialStatus::PUBLISHED->value)
                                    <x-ts-button color="emerald" wire:click="publish({{ $material->id }})"
                                        aria-label="Publicar material {{ $material->title }}" title="Publicar material"
                                        class="!size-12 !p-0 shrink-0 justify-center">
                                        <x-ts-icon icon="cloud-arrow-up" outline class="size-7 text-white" />
                                    </x-ts-button>
                                @endif

                                @if ($material->status->value !== \App\Enums\MaterialStatus::ARCHIVED->value)
                                    <x-ts-button color="red" wire:click="archive({{ $material->id }})"
                                        aria-label="Arquivar material {{ $material->title }}" title="Arquivar material"
                                        class="!size-12 !p-0 shrink-0 justify-center">
                                        <x-ts-icon icon="archive-box" outline class="size-7 text-white" />
                                    </x-ts-button>
                                @endif
                                @endcan
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <x-data-table.empty-state :colspan="count($materialColumns)">
                        <span class="text-lg">Nenhum material encontrado.</span>
                    </x-data-table.empty-state>
                @endforelse
            </x-data-table>

            <x-pagination :paginator="$materials" class="mt-4" />

            @if ($showForm && $canCreateMaterials)
                <div
                    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                    <section role="dialog" aria-modal="true" aria-labelledby="material-form-title"
                        class="w-full max-w-2xl rounded-md bg-white p-6 shadow-xl">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <h2 id="material-form-title" class="text-2xl font-bold text-slate-900">
                                {{ $editingMaterialId === null ? 'Novo material' : 'Editar material' }}
                            </h2>
                            <button type="button" wire:click="closeForm" aria-label="Fechar formulário"
                                class="rounded p-1 text-gray-500 hover:bg-gray-100 text-xl">&times;</button>
                        </div>

                        <form wire:submit="save" class="space-y-4">
                            <x-ts-input wire:model="form.title" label="Título *" required />
                            <x-ts-input wire:model="form.description" label="Descrição" />
                            <x-ts-select.styled wire:model="form.category_id" label="Categoria *"
                                placeholder="Selecione uma categoria ativa" :options="$activeCategories
                                    ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                                    ->all()"
                                select="label:label|value:value" required />
                            <x-ts-input wire:model="form.author" label="Autor *" required />

                            @if ($editingMaterialId === null)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    @if ($actor->isAdmin())
                                        <x-ts-select.styled wire:model="form.initialStatus" label="Status inicial *"
                                            :options="collect($creationStatuses)
                                                ->map(fn($status) => ['label' => $status->label(), 'value' => $status->value])
                                                ->all()" select="label:label|value:value" required />
                                    @else
                                        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                            <span class="font-semibold">Enviado como rascunho</span>
                                            <span class="mt-1 block">O material ficará disponível para revisão do administrador.</span>
                                        </div>
                                    @endif
                                    <div class="flex items-end">
                                        <div
                                            class="w-full rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-base text-blue-900">
                                            <span class="font-semibold">Versão 1</span>
                                            <span class="mt-1 block text-sm text-blue-700">Definida automaticamente pelo
                                                sistema.</span>
                                        </div>
                                    </div>
                                </div>

                                <x-ts-input wire:model="form.change_note" label="Nota da versão (opcional)" />

                                <div>
                                    <label for="material-file"
                                        class="mb-1 block text-base font-medium text-gray-700">
                                        Arquivo <span aria-hidden="true" class="font-bold text-red-500">*</span>
                                    </label>
                                    <input id="material-file" type="file" wire:model="form.file"
                                        accept="{{ implode(',', array_map(fn($mime) => '.' . $mime, config('materials.upload.allowed_mimes'))) }}"
                                        required
                                        class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-base text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-base file:font-semibold file:text-blue-700">
                                    @error('form.file')
                                        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div wire:loading wire:target="form.file"
                                        class="mt-2 text-base font-medium text-blue-700">
                                        Enviando arquivo...
                                    </div>
                                    @if ($form->file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                        <dl
                                            class="mt-3 grid grid-cols-1 gap-2 rounded-md bg-gray-50 p-3 text-base sm:grid-cols-2">
                                            <div class="min-w-0">
                                                <dt class="font-medium text-gray-500">Nome</dt>
                                                <dd class="truncate text-gray-900">
                                                    {{ $form->file->getClientOriginalName() }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-medium text-gray-500">Tamanho</dt>
                                                <dd class="text-gray-900">
                                                    {{ number_format($form->file->getSize() / 1024, 1, ',', '.') }} KB</dd>
                                            </div>
                                        </dl>
                                    @endif
                                </div>
                            @endif

                            <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                                <x-ts-button color="blue" type="submit" wire:loading.attr="disabled"
                                    wire:target="save">
                                    <span wire:loading.remove wire:target="save">Salvar material</span>
                                    <span wire:loading wire:target="save">Salvando...</span>
                                </x-ts-button>
                            </div>
                        </form>
                    </section>
                </div>
            @endif
        </div>
    </div>
