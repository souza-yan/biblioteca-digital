<div>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Painel</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Gerenciamento de usuários</h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $actor->isStaff() ? 'Professores sob sua gestão' : 'Contas cadastradas no sistema' }}
                </p>
            </div>

            <x-ts-button color="blue" wire:click="openCreate">
                Novo usuário
            </x-ts-button>
        </div>

        <div class="mb-5 max-w-xl">
            <x-ts-input
                wire:model.live.debounce.300ms="search"
                label="Buscar por nome ou e-mail"
                placeholder="Digite um nome ou endereço de e-mail"
            />
        </div>

        <div class="overflow-x-auto rounded-md border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Nome</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">E-mail</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Cargo</th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-600">Situação</th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ $user->role->label() }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm">
                                <span @class([
                                    'inline-flex rounded px-2 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-800' => $user->is_active,
                                    'bg-gray-100 text-gray-700' => ! $user->is_active,
                                ])>
                                    {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                <div class="flex justify-end gap-2">
                                    <x-ts-button color="slate" wire:click="editUser({{ $user->id }})">
                                        Editar
                                    </x-ts-button>
                                    @if ($user->is($actor))
                                        <span class="px-2 py-2 text-xs text-gray-500">Conta atual</span>
                                    @else
                                        <x-ts-button
                                            :color="$user->is_active ? 'red' : 'green'"
                                            wire:click="toggleActive({{ $user->id }})"
                                        >
                                            {{ $user->is_active ? 'Desativar' : 'Ativar' }}
                                        </x-ts-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>

        @if ($showForm)
            <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/50 px-4 py-8 sm:items-center">
                <section
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="user-form-title"
                    class="w-full max-w-xl rounded-md bg-white p-6 shadow-xl"
                >
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <h2 id="user-form-title" class="text-lg font-semibold text-gray-900">
                                {{ $editingUserId === null ? 'Novo usuário' : 'Editar usuário' }}
                            </h2>
                        </div>
                        <button
                            type="button"
                            wire:click="closeForm"
                            aria-label="Fechar formulário"
                            class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <x-ts-input wire:model="form.name" label="Nome" autocomplete="name" />
                        <x-ts-input wire:model="form.email" label="E-mail" type="email" autocomplete="email" />
                        <x-ts-select.styled
                            wire:model="form.role"
                            label="Cargo"
                            placeholder="Selecione um cargo"
                            :options="$roleOptions"
                            select="label:label|value:value"
                            :disabled="$editingUser?->is($actor) ?? false"
                            required
                        />
                        @if ($editingUser?->is($actor))
                            @cannot('changeRole', $editingUser)
                                <p class="text-sm text-gray-600">Você não pode alterar o seu próprio cargo.</p>
                            @endcannot
                        @endif
                        <x-ts-input
                            wire:model="form.password"
                            label="Senha"
                            type="password"
                            autocomplete="new-password"
                            :hint="$editingUserId === null ? null : 'Deixe em branco para manter a senha atual.'"
                        />
                        <x-ts-input
                            wire:model="form.password_confirmation"
                            label="Confirmar senha"
                            type="password"
                            autocomplete="new-password"
                        />

                        @error('user')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <x-ts-button color="slate" type="button" wire:click="closeForm">
                                Cancelar
                            </x-ts-button>
                            <x-ts-button color="blue" type="submit">
                                Salvar usuário
                            </x-ts-button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>
</div>
