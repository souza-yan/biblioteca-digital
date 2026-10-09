<div>
    <div class="mx-auto w-full max-w-screen-2xl space-y-6 px-4 py-6 sm:px-6 lg:space-y-8 lg:px-8 lg:py-8">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-blue-800 sm:text-4xl">Gerenciamento de usuários
                </h1>
            </div>

            <x-ts-button color="blue" wire:click="openCreate">
                <span class="text-white text-base">Novo usuário</span>
            </x-ts-button>
        </div>

        <div class="mb-5 max-w-xl">
            <x-search-field model="search" label="Buscar por nome ou e-mail"
                placeholder="Digite um nome ou endereço de e-mail" />
        </div>

        <x-data-table :columns="[
            ['label' => 'Nome', 'class' => 'text-lg'],
            ['label' => 'E-mail', 'class' => 'text-lg'],
            ['label' => 'Cargo', 'class' => 'text-lg'],
            ['label' => 'Situação', 'class' => 'text-lg'],
            ['label' => 'Ações', 'align' => 'right', 'class' => 'text-lg'],
        ]">
            @forelse ($users as $user)
                <tr wire:key="user-{{ $user->id }}">
                    <td class="whitespace-nowrap px-5 py-4 text-lg font-medium text-black">{{ $user->name }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-lg text-black">{{ $user->email }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-lg text-black">{{ $user->role->label() }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-lg">
                        <span @class([
                            'inline-flex rounded px-2.5 py-1 text-base font-medium',
                            'bg-emerald-100 text-emerald-800' => $user->is_active,
                            'bg-gray-100 text-gray-700' => !$user->is_active,
                        ])>
                            {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right text-lg">
                        <div class="flex justify-end gap-2">
                            <x-ts-button color="blue" wire:click="editUser({{ $user->id }})">
                                <span class="text-white">Editar</span>
                            </x-ts-button>
                            @if ($user->is($actor))
                            @else
                                <x-ts-button :color="$user->is_active ? 'red' : 'emerald'" wire:click="toggleActive({{ $user->id }})">
                                    <span class="text-white">{{ $user->is_active ? 'Desativar' : 'Ativar' }}</span>
                                </x-ts-button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-data-table.empty-state :colspan="5">
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
                                {{ $editingUserId === null ? 'Novo usuário' : 'Editar usuário' }}
                            </h2>
                        </div>
                        <button type="button" wire:click="closeForm" aria-label="Fechar formulário"
                            class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800 text-xl">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        <x-ts-input wire:model="form.name" label="Nome" autocomplete="name" />
                        <x-ts-input wire:model="form.email" label="E-mail" type="email" autocomplete="email" />
                        <x-ts-select.styled wire:model="form.role" label="Cargo" placeholder="Selecione um cargo"
                            :options="$roleOptions" select="label:label|value:value" :disabled="$editingUser?->is($actor) ?? false" required />
                        @if ($editingUser?->is($actor))
                            @cannot('changeRole', $editingUser)
                                <p class="text-base text-gray-600">Você não pode alterar o seu próprio cargo.</p>
                            @endcannot
                        @endif
                        <x-ts-input wire:model="form.password" label="Senha" type="password"
                            autocomplete="new-password" :hint="$editingUserId === null ? null : 'Deixe em branco para manter a senha atual.'" />
                        <x-ts-input wire:model="form.password_confirmation" label="Confirmar senha" type="password"
                            autocomplete="new-password" />

                        @error('user')
                            <p class="text-base text-red-600">{{ $message }}</p>
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
