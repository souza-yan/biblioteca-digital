    <div>
        @php
            $actor = auth()->user();
            $canManageMaterials = $actor?->isAdmin() ?? false;
            $canCreateMaterials = $actor ? \Illuminate\Support\Facades\Gate::allows('create', \App\Models\Material::class) : false;
            $materialColumns = [
                ['label' => 'Título', 'class' => 'text-lg'],
                ['label' => 'Categoria', 'class' => 'text-lg'],
                ['label' => 'Status', 'class' => 'text-lg'],
            ];

            if ($actor?->isAdmin() || $actor?->isStaff()) {
                $materialColumns[] = ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'];
            }
        @endphp
        <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
            {{-- Cabeçalho e ações permitidas conforme o perfil autenticado. --}}
            <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div class="min-w-0">
                    <x-breadcrumb :items="[
                        ['label' => 'Início', 'url' => route('dashboard')],
                        ['label' => 'Materiais'],
                    ]" />
                    @if (! $canManageMaterials)
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">
                            Gestão de conteúdos
                        </p>
                    @endif
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                        Materiais
                    </h1>
                    @if (! $canManageMaterials)
                        <p class="mt-2 max-w-2xl text-base text-slate-500">
                            Consulte a biblioteca e envie novos materiais para revisão e publicação.
                        </p>
                    @endif
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

            {{-- Filtros reativos da listagem administrativa de materiais. --}}
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

            {{-- Acervo gerenciável e ações de ciclo de vida do material. --}}
            <x-data-table :columns="$materialColumns">
                <x-data-table.loading :colspan="count($materialColumns)" />
                @forelse ($materials as $material)
                    <tr wire:key="material-{{ $material->id }}">
                        <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                        <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
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
                                        wire:confirm="Publicar este material na biblioteca?"
                                        wire:loading.attr="disabled" wire:target="publish({{ $material->id }})"
                                        aria-label="Publicar material {{ $material->title }}" title="Publicar material"
                                        class="!size-12 !p-0 shrink-0 justify-center">
                                        <x-ts-icon icon="cloud-arrow-up" outline class="size-7 text-white" />
                                    </x-ts-button>
                                @endif

                                @if ($material->status->value !== \App\Enums\MaterialStatus::ARCHIVED->value)
                                    <x-ts-button color="red" wire:click="archive({{ $material->id }})"
                                        wire:confirm="Arquivar este material? Ele deixará de aparecer na biblioteca."
                                        wire:loading.attr="disabled" wire:target="archive({{ $material->id }})"
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

            {{-- Formulário compartilhado para criação e edição dos metadados. --}}
            @if ($showForm && $canCreateMaterials)
                <x-ts-modal wire="showForm" :title="$editingMaterialId === null ? 'Novo material' : 'Editar material'" size="2xl" center>
                <form id="managed-material-form" wire:submit="save" class="space-y-4">
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

                                <x-materials.file-upload id="material-file" model="form.file" :file="$form->file" />
                            @endif

                </form>

                <x-slot:footer>
                    <x-ts-button color="slate" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                    <x-ts-button color="blue" type="submit" form="managed-material-form" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Salvar material</span>
                        <span wire:loading wire:target="save">Salvando...</span>
                    </x-ts-button>
                </x-slot:footer>
                </x-ts-modal>
            @endif
        </div>
    </div>
