    <div>
        @php
            $actor = auth()->user();
            $canManageMaterials = $actor?->isAdmin() || $actor?->isStaff();
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
        <div class="mx-auto w-full max-w-screen-2xl {{ $embedded ? 'space-y-5' : 'space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8' }}">
            {{-- Cabeçalho e ações permitidas conforme o perfil autenticado. --}}
            @unless ($embedded)
            <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div class="min-w-0">
                    <h1 class="text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                        {{ $actor?->isStaff() ? 'Meus materiais' : 'Materiais' }}
                    </h1>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    @if ($canCreateMaterials && $actor?->isAdmin())
                        <x-ts-button color="blue" wire:click="openCreate" class="w-full justify-center sm:w-auto">
                            <x-ts-icon icon="plus" outline class="size-5" />
                            Novo material
                        </x-ts-button>
                    @endif
                </div>
            </div>
            @endunless

            @error('publish')
                <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-base text-red-700">{{ $message }}</p>
            @enderror

            {{-- Filtros reativos da listagem administrativa de materiais. --}}
            <x-skeleton.gate name="material-controls" label="Carregando filtros de materiais">
                <x-slot:skeleton>
                    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach (['Buscar material', 'Status', 'Categoria'] as $label)
                            <div><x-skeleton.text class="w-fit text-sm font-medium">{{ $label }}</x-skeleton.text><x-skeleton.block class="mt-1 h-11 rounded-lg" /></div>
                        @endforeach
                    </div>
                </x-slot:skeleton>
            <div @class([
                'mb-5 grid grid-cols-1 gap-4',
                'md:grid-cols-3',
            ])>
                <x-search-field id="managed-material-search" model="search" label="Buscar material" placeholder="Título ou autor" />
                <x-form.select id="managed-material-status" label="Status" wire:model.live="statusFilter">
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-form.select>
                <x-form.select id="managed-material-category" label="Categoria" wire:model.live="categoryFilter">
                    <option value="">Todas as categorias</option>
                    <option value="uncategorized">Sem categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-form.select>
            </div>

            {{-- Acervo gerenciável e ações de ciclo de vida do material. --}}
            </x-skeleton.gate>
            <x-skeleton.gate name="material-list" label="Carregando listagem de materiais">
                <x-slot:skeleton>
                    @include('livewire.materials.material-list-skeleton')
                </x-slot:skeleton>
            @if ($actor?->isStaff())
                <div class="space-y-3 md:hidden">
                    @forelse ($materials as $material)
                        <article wire:key="managed-material-mobile-{{ $material->id }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <h2 class="break-words text-base font-semibold text-slate-900">{{ $material->title }}</h2>
                            <p class="mt-1 break-words text-sm text-slate-500">{{ $material->category->name }}</p>
                            <div class="mt-3"><x-materials.status-badge :status="$material->status" /></div>
                            <div class="mt-4">@include('livewire.materials.partials.management-actions')</div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-white px-4 py-8 text-center text-sm text-slate-600">Nenhum material encontrado.</p>
                    @endforelse
                </div>
            @endif
            <div @class(['hidden md:block' => $actor?->isStaff()])>
            <x-data-table :columns="$materialColumns">
                @forelse ($materials as $material)
                    <tr wire:key="material-{{ $material->id }}">
                        <td class="px-5 py-4 text-lg font-medium text-black">{{ $material->title }}</td>
                        <td class="px-5 py-4 text-lg text-black">{{ $material->category->name }}</td>
                        <td class="px-5 py-4 text-lg text-black">
                            <x-materials.status-badge :status="$material->status" />
                        </td>
                        @if ($actor?->isAdmin() || $actor?->isStaff())
                            <td class="px-5 py-4 text-right text-lg">
                                @include('livewire.materials.partials.management-actions')
                            </td>
                        @endif
                    </tr>
                @empty
                    <x-data-table.empty-state :colspan="count($materialColumns)">
                        <span class="text-lg">Nenhum material encontrado.</span>
                    </x-data-table.empty-state>
                @endforelse
            </x-data-table>
            </div>

            <x-pagination :paginator="$materials" class="mt-4" />
            </x-skeleton.gate>

            {{-- Formulário compartilhado para criação e edição dos metadados. --}}
            @if ($showForm && $canCreateMaterials)
                <x-ts-modal wire="showForm" :title="$editingMaterialId === null ? 'Novo material' : 'Editar material'" size="2xl" center>
                <form id="managed-material-form" wire:submit="save" class="space-y-4"
                    x-data="{ uploading: false }"
                    x-on:livewire-upload-start="uploading = true"
                    x-on:livewire-upload-finish="uploading = false"
                    x-on:livewire-upload-error="uploading = false"
                    x-on:livewire-upload-cancel="uploading = false"
                    x-on:submit.capture="if (uploading) { $event.preventDefault(); $event.stopImmediatePropagation(); }">
                            <x-ts-input wire:model="form.title" label="Título *" required />
                            <x-ts-input id="managed-material-description" wire:model="form.description" label="Descrição" />
                            <x-ts-select.styled wire:model="form.category_id" label="Categoria *"
                                placeholder="Selecione uma categoria ativa" :options="$activeCategories
                                    ->map(fn($category) => ['label' => $category->name, 'value' => $category->id])
                                    ->all()"
                                select="label:label|value:value" required />
                            <x-ts-input wire:model="form.author" label="Autor *" required />

                            @if ($editingMaterialId === null)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <x-form.select id="managed-material-initial-status" label="Status inicial"
                                        :required="true" error="form.initialStatus" wire:model="form.initialStatus">
                                        @foreach ($creationStatuses as $status)
                                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </x-form.select>
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
                    <x-ts-button color="red" type="button" wire:click="closeForm">Cancelar</x-ts-button>
                    <x-ts-button color="blue" type="submit" form="managed-material-form" wire:loading.attr="disabled" wire:target="save,form.file">
                        <span wire:loading.remove wire:target="save">Salvar material</span>
                        <span wire:loading wire:target="save">Salvando...</span>
                    </x-ts-button>
                </x-slot:footer>
                </x-ts-modal>
            @endif
        </div>
    </div>
