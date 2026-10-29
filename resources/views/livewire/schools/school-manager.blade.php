<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        {{-- Cabeçalho da administração das unidades escolares. --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Escolas</h1>
            </div>

            <x-ts-button color="blue" wire:click="openCreate" class="w-full justify-center sm:w-auto">
                <x-ts-icon icon="plus" outline class="size-5" />
                Nova escola
            </x-ts-button>
        </header>

        <div class="max-w-xl">
            <x-search-field model="search" label="Buscar escola" placeholder="Nome ou endereço" />
        </div>

        {{-- Cards das escolas com os respectivos gestores e vínculos. --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @forelse ($schools as $school)
                <article wire:key="school-{{ $school->id }}"
                    class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="break-words text-xl font-bold text-slate-900">{{ $school->name }}</h2>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ $school->type->label() }} · Região {{ $school->region->label() }}
                            </p>
                            <p class="mt-1 break-words text-sm text-slate-500">{{ $school->address }}</p>
                            @if ($school->phone)
                                <p class="mt-1 text-sm text-slate-500">{{ $school->phone }}</p>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <x-ts-button color="blue" wire:click="editSchool({{ $school->id }})"
                                aria-label="Editar {{ $school->name }}" title="Editar escola"
                                class="!size-12 !p-0 justify-center">
                                <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
                            </x-ts-button>
                            <x-ts-button color="slate" wire:click="openManagerForm({{ $school->id }})"
                                aria-label="Adicionar gestor à escola {{ $school->name }}" title="Adicionar gestor"
                                class="!size-12 !p-0 justify-center">
                                <x-ts-icon icon="user-plus" outline class="size-7 text-white" />
                            </x-ts-button>
                        </div>
                    </div>

                    <div class="mt-5 flex-1 border-t border-slate-100 pt-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900">Gestão vinculada</h3>
                                <p class="text-sm text-slate-500">
                                    {{ $school->users->count() }}
                                    {{ $school->users->count() === 1 ? 'gestor vinculado' : 'gestores vinculados' }}
                                </p>
                            </div>

                        </div>

                        <div class="mt-4 space-y-2">
                            @forelse ($school->users as $manager)
                                <div class="flex min-w-0 items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                                    <span
                                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-900">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($manager->name, 0, 1)) }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block truncate text-sm font-semibold text-slate-800">{{ $manager->name }}</span>
                                        <span
                                            class="block truncate text-xs text-slate-500">{{ $manager->email }}</span>
                                        <span @class([
                                            'mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                            'bg-emerald-100 text-emerald-800' => $manager->is_active,
                                            'bg-slate-200 text-slate-700' => !$manager->is_active,
                                        ])>
                                            {{ $manager->is_active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </span>
                                    <x-ts-button :color="$manager->is_active ? 'red' : 'emerald'"
                                        wire:click="toggleManagerActive({{ $school->id }}, {{ $manager->id }})"
                                        wire:confirm="Alterar a situação deste gestor na escola?"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleManagerActive({{ $school->id }}, {{ $manager->id }})"
                                        :aria-label="$manager->is_active
                                            ? 'Desativar gestor ' . $manager->name
                                            : 'Ativar gestor ' . $manager->name" :title="$manager->is_active ? 'Desativar gestor' : 'Ativar gestor'"
                                        class="!size-12 !p-0 shrink-0 justify-center">
                                        <x-ts-icon :icon="$manager->is_active ? 'no-symbol' : 'check-circle'" outline class="size-7 text-white" />
                                    </x-ts-button>
                                </div>
                            @empty
                                <p
                                    class="rounded-xl border border-dashed border-slate-200 px-4 py-3 text-sm text-slate-500">
                                    Nenhum gestor ativo vinculado a esta escola.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center xl:col-span-2">
                    <x-ts-icon icon="building-office-2" outline class="mx-auto size-12 text-slate-400" />
                    <p class="mt-3 text-base font-semibold text-slate-700">Nenhuma escola encontrada.</p>
                    <p class="mt-1 text-sm text-slate-500">Cadastre uma escola ou ajuste o termo pesquisado.</p>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$schools" />
    </div>

    {{-- Formulário dos dados cadastrais da escola. --}}
    <x-ts-modal wire="showSchoolForm" :title="$editingSchoolId === null ? 'Nova escola' : 'Editar escola'" size="2xl" center>
        <form id="school-form" wire:submit="saveSchool" class="space-y-4">
            <x-form.input id="school-name" label="Nome" :required="true" error="form.name" wire:model="form.name"
                autocomplete="organization" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.select id="school-type" label="Tipo" :required="true" error="form.type"
                    wire:model="form.type">
                    @foreach ($schoolTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select id="school-region" label="Região" :required="true" error="form.region"
                    wire:model="form.region">
                    <option value="">Selecione a região</option>
                    @foreach ($schoolRegions as $region)
                        <option value="{{ $region->value }}">{{ $region->label() }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <x-form.input id="school-address" label="Endereço" :required="true" error="form.address"
                wire:model="form.address" autocomplete="street-address" />
            <x-form.input id="school-phone" label="Telefone" error="form.phone" wire:model="form.phone" inputmode="tel"
                autocomplete="tel" placeholder="(12) 3456-7890" />
        </form>

        <x-slot:footer>
            <x-ts-button color="slate" type="button" wire:click="closeSchoolForm">Cancelar</x-ts-button>
            <x-ts-button color="blue" type="submit" form="school-form" wire:loading.attr="disabled"
                wire:target="saveSchool">
                <span wire:loading.remove wire:target="saveSchool">Salvar escola</span>
                <span wire:loading wire:target="saveSchool">Salvando...</span>
            </x-ts-button>
        </x-slot:footer>
    </x-ts-modal>

    <x-ts-modal wire="showManagerForm" title="Adicionar gestor" size="lg" center>
        <form id="manager-form" wire:submit="saveManager" class="space-y-4">
            @if ($managerSchool)
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Escola</p>
                    <p class="mt-1 font-semibold text-blue-950">{{ $managerSchool->name }}</p>
                </div>
            @endif

            <p class="text-sm text-slate-600">
                O gestor será criado com o cargo Gestão e receberá a senha inicial padrão do sistema.
            </p>

            <x-form.input id="manager-name" label="Nome" :required="true" error="managerName"
                wire:model="managerName" autocomplete="name" />
            <x-form.input id="manager-email" label="E-mail" :required="true" error="managerEmail"
                wire:model="managerEmail" type="email" autocomplete="email" />
        </form>

        <x-slot:footer>
            <x-ts-button color="slate" type="button" wire:click="closeManagerForm">Cancelar</x-ts-button>
            <x-ts-button color="blue" type="submit" form="manager-form" wire:loading.attr="disabled"
                wire:target="saveManager">
                <span wire:loading.remove wire:target="saveManager">Cadastrar gestor</span>
                <span wire:loading wire:target="saveManager">Cadastrando...</span>
            </x-ts-button>
        </x-slot:footer>
    </x-ts-modal>
</div>
