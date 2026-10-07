<div>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <header>
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Administração</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Atividades</h1>
            <p class="mt-1 text-sm text-gray-600">Consulte downloads, alterações e acessos ao sistema.</p>
        </header>

        <nav class="flex flex-wrap gap-2 border-b border-gray-200" role="tablist" aria-label="Tipos de atividade">
            @foreach ([
                'downloads' => 'Downloads',
                'changes' => 'Cadastros e alterações',
                'accesses' => 'Acessos',
            ] as $tab => $label)
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                    wire:click="selectTab('{{ $tab }}')"
                    class="{{ $activeTab === $tab ? 'border-b-2 border-teal-600 text-teal-700' : 'text-gray-600 hover:text-gray-900' }} -mb-px px-4 py-3 text-sm font-medium"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        @if ($activeTab === 'downloads')
            <section role="tabpanel" aria-label="Downloads" class="space-y-4">
                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Usuário
                            <select wire:model.live="downloadsUserFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">Todos os usuários</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            Material
                            <select wire:model.live="downloadsMaterialFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">Todos os materiais</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}">{{ $material->title }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            De
                            <input wire:model.live="downloadsFromDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            Até
                            <input wire:model.live="downloadsUntilDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </label>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <x-ts-button color="slate" wire:click="clearDownloadsFilters">Limpar filtros</x-ts-button>
                    </div>
                    @foreach (['downloadsUserFilter', 'downloadsMaterialFilter', 'downloadsFromDate', 'downloadsUntilDate'] as $filter)
                        @error($filter)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-md border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                            <tr>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data e hora</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Usuário</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Material</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Versão</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Categoria</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($records as $download)
                                <tr wire:key="activity-download-{{ $download->id }}">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $download->downloaded_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $download->user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $download->material->title }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $download->version->version_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $download->material->category->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Nenhum registro encontrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>{{ $records->links() }}</div>
            </section>
        @elseif ($activeTab === 'changes')
            <section role="tabpanel" aria-label="Cadastros e alterações" class="space-y-4">
                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Usuário
                            <select wire:model.live="changesUserFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">Todos os usuários</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            Ação
                            <select wire:model.live="changesActionFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">Todas as ações</option>
                                @foreach ($changeActions as $action)
                                    <option value="{{ $action->value }}">{{ $action->label() }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            De
                            <input wire:model.live="changesFromDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            Até
                            <input wire:model.live="changesUntilDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </label>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <x-ts-button color="slate" wire:click="clearChangesFilters">Limpar filtros</x-ts-button>
                    </div>
                    @foreach (['changesUserFilter', 'changesActionFilter', 'changesFromDate', 'changesUntilDate'] as $filter)
                        @error($filter)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-md border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                            <tr>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data e hora</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Quem fez</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Ação</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">O que foi afetado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($records as $log)
                                <tr wire:key="activity-change-{{ $log->id }}">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $actionBadgeClasses[$log->action->value] }}">
                                            {{ $log->action->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">Nenhum registro encontrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>{{ $records->links() }}</div>
            </section>
        @else
            <section role="tabpanel" aria-label="Acessos" class="space-y-4">
                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Usuário
                            <select wire:model.live="accessesUserFilter" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">Todos os usuários</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            De
                            <input wire:model.live="accessesFromDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            Até
                            <input wire:model.live="accessesUntilDate" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </label>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <x-ts-button color="slate" wire:click="clearAccessesFilters">Limpar filtros</x-ts-button>
                    </div>
                    @foreach (['accessesUserFilter', 'accessesFromDate', 'accessesUntilDate'] as $filter)
                        @error($filter)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>

                <div class="max-h-[70vh] overflow-auto rounded-md border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="sticky top-0 z-10 bg-gray-50">
                            <tr>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Data e hora</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Usuário</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Ação</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">Descrição</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($records as $log)
                                <tr wire:key="activity-access-{{ $log->id }}">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $log->user->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $actionBadgeClasses[$log->action->value] }}">
                                            {{ $log->action->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">Nenhum registro encontrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div>{{ $records->links() }}</div>
            </section>
        @endif
    </div>
</div>
