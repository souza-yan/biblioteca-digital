<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div class="min-w-0">
                <x-breadcrumb :items="[
                    ['label' => 'Início', 'url' => route('dashboard')],
                    ['label' => 'Professores'],
                ]" />
                @if ($actor->isStaff())
                    <p class="text-sm font-semibold uppercase tracking-[0.14em] text-blue-700">Gestão escolar</p>
                @endif
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">
                    {{ $actor->isStaff() ? 'Professores da escola' : 'Professores' }}
                </h1>
                <p class="mt-2 max-w-2xl text-base text-slate-500">
                    {{ $actor->isStaff()
                        ? 'Gerencie somente o vínculo dos professores com a sua escola. Os dados da conta continuam globais.'
                        : 'Cadastre professores e gerencie seus dados e vínculos com as escolas.' }}
                </p>
            </div>

            <x-ts-button color="blue" wire:click="openCreate">
                <span class="text-white text-base">Novo professor</span>
            </x-ts-button>
        </div>

        <div class="relative mb-5 max-w-xl">
            <x-ts-input id="user-search" wire:model.live.debounce.300ms="search"
                label="Buscar por nome ou e-mail" placeholder="Digite um nome ou endereço de e-mail"
                autocomplete="off" aria-controls="user-search-suggestions" />

            <div wire:loading wire:target="search" role="status" class="mt-1 text-sm text-gray-600">
                Buscando usuários...
            </div>

            @if ($selectedUserId !== null)
                <div class="mt-2 flex items-center justify-between gap-3 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-900" role="status">
                    <span>Selecionado: {{ $selectedUser?->name ?? 'usuário indisponível' }}</span>
                    <button type="button" wire:click="clearSelectedUser" aria-label="Limpar usuário selecionado"
                        title="Limpar seleção" class="inline-flex size-9 items-center justify-center rounded hover:bg-blue-100">
                        <x-ts-icon icon="x-mark" outline class="size-5" />
                    </button>
                </div>
            @elseif (filled($search))
                <ul id="user-search-suggestions" role="listbox"
                    class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                    @forelse ($suggestions as $suggestion)
                        <li wire:key="user-suggestion-{{ $suggestion->id }}" role="presentation">
                            <button type="button" role="option" aria-selected="false"
                                aria-label="Selecionar {{ $suggestion->name }} ({{ $suggestion->email }})"
                                wire:click="selectUser({{ $suggestion->id }})"
                                class="flex w-full flex-col px-3 py-2 text-left hover:bg-blue-50 focus:bg-blue-50 focus:outline-none">
                                <span class="font-medium text-gray-900">{{ $suggestion->name }}</span>
                                <span class="text-sm text-gray-600">{{ $suggestion->email }}</span>
                            </button>
                        </li>
                    @empty
                        <li class="px-3 py-2 text-sm text-gray-600" role="status">Nenhum usuário encontrado.</li>
                    @endforelse
                </ul>
            @endif
        </div>

        <x-data-table :columns="$columns">
            @forelse ($users as $user)
                @php
                    $schoolMembership = $actor->isStaff() ? $user->schools->first() : null;
                    $localMembershipIsActive = (bool) ($schoolMembership?->pivot?->is_active ?? false);
                @endphp
                <tr wire:key="user-{{ $user->id }}">
                    <td class="whitespace-nowrap px-5 py-4 text-lg font-medium text-black">{{ $user->name }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-lg text-black">{{ $user->email }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-right text-lg">
                        <div class="flex justify-end gap-2">
                            @if ($actor->isAdmin())
                                <x-ts-button color="blue" wire:click="editUser({{ $user->id }})"
                                    aria-label="Editar usuário {{ $user->name }}" title="Editar usuário" class="!size-12 !p-0 justify-center">
                                    <x-ts-icon icon="pencil-square" outline class="size-7 text-white" />
                                </x-ts-button>
                            @endif
                            @can('resetPassword', $user)
                                <x-ts-button color="slate" wire:click="openPasswordResetConfirmation({{ $user->id }})"
                                    wire:loading.attr="disabled" wire:target="openPasswordResetConfirmation"
                                    aria-label="Redefinir a senha de {{ $user->name }}"
                                    title="Redefinir senha para o padrão" class="!size-12 !p-0 justify-center">
                                    <x-ts-icon icon="key" outline class="size-7 text-white" />
                                </x-ts-button>
                            @endcan
                            @if ($user->is($actor))
                            @else
                                @can('toggleActive', $user)
                                    <x-ts-button :color="($actor->isStaff() ? $localMembershipIsActive : $user->is_active) ? 'red' : 'emerald'" wire:click="toggleActive({{ $user->id }})"
                                        :aria-label="($actor->isStaff() ? $localMembershipIsActive : $user->is_active) ? 'Desativar usuário '.$user->name : 'Ativar usuário '.$user->name"
                                        :title="($actor->isStaff() ? $localMembershipIsActive : $user->is_active) ? 'Desativar usuário' : 'Ativar usuário'" class="!size-12 !p-0 justify-center">
                                        <x-ts-icon :icon="($actor->isStaff() ? $localMembershipIsActive : $user->is_active) ? 'no-symbol' : 'check-circle'" outline class="size-5 text-white" />
                                    </x-ts-button>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="count($columns)">
                    <span class="text-lg">Nenhum usuário encontrado.</span>
                </x-data-table.empty-state>
            @endforelse
        </x-data-table>

        <x-pagination :paginator="$users" class="mt-4" />

        @if ($showForm)
            <div
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                <section role="dialog" aria-modal="true" aria-labelledby="user-form-title"
                    class="w-full max-w-xl rounded-md bg-white p-6 shadow-xl">
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <h2 id="user-form-title" class="text-2xl font-bold text-slate-900">
                                {{ $editingUserId === null
                                    ? 'Novo professor'
                                    : 'Editar professor' }}
                            </h2>
                        </div>
                        <button type="button" wire:click="closeForm" aria-label="Fechar formulário" title="Fechar"
                            class="inline-flex size-12 items-center justify-center rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            <x-ts-icon icon="x-mark" outline class="size-7" />
                        </button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        @if ($actor->isAdmin() || $editingUserId === null)
                            <x-ts-input wire:model="form.name" label="Nome *" autocomplete="name" required />
                            <x-ts-input wire:model="form.email" label="E-mail *" type="email" autocomplete="email" required />
                        @else
                            <div class="rounded-md bg-gray-50 p-3 text-sm text-gray-700" role="note">
                                <p><span class="font-medium">Nome:</span> {{ $editingUser?->name }}</p>
                                <p><span class="font-medium">E-mail:</span> {{ $editingUser?->email }}</p>
                                <p><span class="font-medium">Cargo:</span> {{ $editingUser?->role->label() }}</p>
                                <p class="mt-2">A Gestão pode alterar somente o vínculo do professor com esta escola.</p>
                            </div>
                        @endif
                        @if ($actor->isAdmin())
                            <fieldset class="rounded-xl border border-slate-200 p-4">
                                <legend class="px-1 text-sm font-semibold text-slate-800">Escolas do professor</legend>
                                <div class="space-y-4">
                                    @foreach (range(0, max(1, count($form->school_ids)) - 1) as $index)
                                        <div wire:key="teacher-school-{{ $index }}-{{ md5(json_encode($teacherSchoolOptions[$index])) }}" class="space-y-2">
                                            <x-ts-select.styled wire:model.live="form.school_ids.{{ $index }}"
                                                searchable label="{{ $index === 0 ? 'Escola' : 'Outra escola' }}"
                                                placeholder="Busque e selecione uma escola"
                                                :options="$teacherSchoolOptions[$index]" select="label:label|value:value" />
                                            @if ($index > 0)
                                                <x-ts-button type="button" color="slate" wire:click="removeSchool({{ $index }})">
                                                    Remover escola
                                                </x-ts-button>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if (count($form->school_ids) < 2)
                                        <x-ts-button type="button" color="blue" wire:click="addSchool"
                                            wire:loading.attr="disabled" wire:target="addSchool">
                                            Adicionar outra escola
                                        </x-ts-button>
                                    @endif
                                </div>
                                @error('form.school_ids')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('form.school_ids.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        @endif
                        @error('user')
                            <p class="text-base text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            @if ($actor->isStaff() && $editingUserId !== null)
                                <x-ts-button color="slate" type="button" wire:click="closeForm">
                                    Fechar
                                </x-ts-button>
                            @else
                                <x-ts-button color="blue" type="submit">
                                    Salvar professor
                                </x-ts-button>
                            @endif
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>

    <x-ts-modal wire="showPasswordResetConfirmation" title="Redefinir senha" size="md" center>
        <p class="text-base font-semibold text-slate-900">
            Deseja redefinir a senha de {{ $passwordResetUser?->name ?? 'este usuário' }}?
        </p>
        <p class="mt-2 text-sm text-slate-600">
            A senha voltará ao padrão <strong>“password”</strong>. Todas as sessões abertas desse usuário serão encerradas.
        </p>

        <x-slot:footer>
            <x-ts-button color="slate" type="button" wire:click="closePasswordResetConfirmation">
                Cancelar
            </x-ts-button>
            <x-ts-button color="red" type="button" wire:click="resetPassword"
                wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">Redefinir senha</span>
                <span wire:loading wire:target="resetPassword">Redefinindo...</span>
            </x-ts-button>
        </x-slot:footer>
    </x-ts-modal>
</div>
